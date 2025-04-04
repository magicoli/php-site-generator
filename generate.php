<?php
require_once "vendor/autoload.php"; // load full Parsedown via Composer

$config = json_decode(file_get_contents("config.json"), true);
$parsedown = new Parsedown();

$github_user = $config["github_user"];
$repo = $config["repo"];

# Exit if GitHub user or repo is not set
if (empty($github_user) || empty($repo)) {
    die( "Error: GitHub user or repo not set in config.json.\n");
}

$site_title = $config["title"] ?? "Site";
$menu = $config["menu"];
$support_email = $config["support_email"] ?? null;
$sender_email = $config["sender_email"] ?? $config["support_email"] ?? null;

$github_token = $config["github_token"] ?? null;
$github_branch = $config["github_branch"] ?? 'master'; // default to master if not set

$output_folder = $config["output_folder"] ?? "output";
$output_folder = rtrim($output_folder, "/"); // Remove trailing slash if present

# Exit with error if output folder is not writable
if (!is_writable($output_folder)) {
    die( "Error: Output folder '$output_folder' does not exist or is not writable.\n");
}

function fetch_markdown($user, $repo, $file) {
    global $github_token;
    global $github_branch;
    if (!empty($github_token)) {
        // Use GitHub API with token
        $url = "https://api.github.com/repos/$user/$repo/contents/$file?ref=$github_branch";
        $opts = [
            "http" => [
                "header" => "User-Agent: PHP\r\n" .
                            "Authorization: token $github_token\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
        if ($result !== false) {
            $json = json_decode($result, true);
            if (isset($json["content"]) && $json["encoding"] === "base64") {
                return base64_decode($json["content"]);
            }
        }
    } else {
        // Use raw URL if no token is set
        $url = "https://raw.githubusercontent.com/$user/$repo/refs/heads/master/$file";
        return file_get_contents($url);
    }
    return "";
}

// New function: fetch latest stable release from GitHub
function fetch_latest_release($user, $repo) {
    global $github_token;
    $url = "https://api.github.com/repos/$user/$repo/releases/latest";
    $opts = [
        "http" => [
            "header" => "User-Agent: PHP\r\n"
        ]
    ];
    if (!empty($github_token)) {
        $opts['http']['header'] .= "Authorization: token $github_token\r\n";
    }
    $context = stream_context_create($opts);
    $result = file_get_contents($url, false, $context);
    if ($result !== false) {
        $release = json_decode($result, true);
        // Ensure it's a stable release
        if (isset($release['prerelease']) && $release['prerelease'] === false) {
            return $release;
        }
    }
    return null;
}

// New function: fetch open issues from GitHub
function fetch_issues($user, $repo) {
    global $github_token;
    $url = "https://api.github.com/repos/$user/$repo/issues?state=open";
    $opts = [
        "http" => [
            "header" => "User-Agent: PHP\r\n"
        ]
    ];
    if (!empty($github_token)) {
        $opts['http']['header'] .= "Authorization: token $github_token\r\n";
    }
    $context = stream_context_create($opts);
    $result = file_get_contents($url, false, $context);
    if ($result !== false) {
        $support = json_decode($result, true);
        $issues_markdown = "";
        if (is_array($support) && count($support) > 0) {
            foreach ($support as $issue) {
                // Exclude pull requests
                if (isset($issue['pull_request'])) {
                    continue;
                }
                $number = htmlspecialchars($issue['number']);
                $title = htmlspecialchars($issue['title']);
                $issue_url = htmlspecialchars($issue['html_url']);
                $issues_markdown .= "- [#$number $title]($issue_url)\n";
            }
            return $issues_markdown;
        }
    }
    return "No open issues found.";
}

function render_page($title, $content, $menu_html) {
    // Make $site_title, $github_user and $repo available in the template
    global $site_title, $github_user, $repo;
    ob_start();
    include "template.php";
    return ob_get_clean();
}

function build_menu_html($menu, $file = "", $slug = "") {
    // Build friendly menu with active highlighting
    $menu_html = "";
    foreach ($menu as $m) {
        if ( '/' === $m["file"] ) {
            $link = "/";
        } else if (stripos($m["file"], ".md") !== false) {
            $menu_slug = strtolower(pathinfo($m["file"], PATHINFO_FILENAME));
            $link = $menu_slug . ".html";
        } else if ( 'support' === $m["file"] ) {
            $link = $m["file"] . ".php";
        } else {
            $link = $m["file"] . ".html";
        }
        $active = ($m["file"] === $file) ? 'active strong' : '';
        $menu_html .= "<li class='nav-item $active'><a class='nav-link $active' href='{$link}'>" . htmlspecialchars($m["title"]) . "</a></li>";
    }
    return $menu_html;
}

foreach ($menu as $item) {
    $file = $item["file"];
    $slug = strtolower(pathinfo($file, PATHINFO_FILENAME));
    $title = $item["title"];
    $html = "";

    if (str_ends_with($file, ".md")) {
        $md = fetch_markdown($github_user, $repo, $file);
        $html = $parsedown->text($md); // Convert Markdown to HTML
    } else {
        if ($file === "downloads") {
            // Fetch latest stable release data
            $release = fetch_latest_release($github_user, $repo);
            // Load the Markdown template for the downloads page
            $template = file_get_contents("pages/downloads.md");
            
            if ($release) {
                $version = htmlspecialchars($release['tag_name']);
                $version = ltrim($version, 'v'); // Remove leading 'v' if present
                if (isset($release['assets']) && count($release['assets']) > 0) {
                    $download_link = htmlspecialchars($release['assets'][0]['browser_download_url']);
                } else {
                    $download_link = htmlspecialchars($release['zipball_url'] ?? $release['tarball_url'] ?? '');
                }
                $release_date = date("F j, Y", strtotime($release['published_at'] ?? ''));
                $release_notes = nl2br(htmlspecialchars($release['body'] ?? 'No details available.'));
                // Generate the download button HTML code
                $download_button = '<a href="' . $download_link . '" class="btn btn-primary" target="_blank">Download ' . $version . '</a>';
            } else {
                $version = "N/A";
                $download_link = "#";
                $release_date = "N/A";
                $release_notes = "No stable release found.";
                $download_button = "";
            }
            
            // Replace placeholders in the Markdown template
            $template = str_replace("{{version}}", $version, $template);
            $template = str_replace("{{release_date}}", $release_date, $template);
            $template = str_replace("{{release_notes}}", $release_notes, $template);
            $template = str_replace("{{download_link}}", $download_link, $template);
            $template = str_replace("{{download_button}}", $download_button, $template);
            $template = str_replace("{{github_user}}", htmlspecialchars($github_user), $template);
            $template = str_replace("{{repo}}", htmlspecialchars($repo), $template);
            
            // Convert the Markdown template to HTML
            $html = $parsedown->text($template);
        } elseif ($file === "support") {
            // Fetch open issues from GitHub
            $issues_content = fetch_issues($github_user, $repo);
            // Load the Markdown template for the issues page
            $template = file_get_contents("pages/support.md");
            $template = str_replace("{{issues_content}}", $issues_content, $template);
            $template = str_replace("{{github_user}}", htmlspecialchars($github_user), $template);
            $template = str_replace("{{repo}}", htmlspecialchars($repo), $template);
            // Replace the issue form placeholder with a marker that will later be replaced by the PHP include.
            // $template = str_replace("{{support_form}}", "{{issue_form_marker}}", $template);
            // Process the markdown
            $html = $parsedown->text($template);
            // Replace the marker with the desired PHP include code (avoid HTML-escaped PHP tags)
            $html = str_replace("{{support_form}}", "<?php include('support_form.php'); ?>", $html);
            // Set output filename as PHP
            $slug = "support";
            $ext = ".php";
        } else {
            $html = "<p>Special page: <strong>$file</strong> (to be implemented)</p>";
        }
    }

    $menu_html = build_menu_html($menu, $file);

    $output_filename = ($file === "support") ? "$output_folder/{$slug}.php" : "$output_folder/{$slug}.html";
    $output = render_page($title, $html, $menu_html);
    file_put_contents($output_filename, $output);
}

// Generate homepage from README.md
$readme = fetch_markdown($github_user, $repo, "README.md");
$home = $parsedown->text($readme); // Convert Markdown to HTML
$menu_html = build_menu_html($menu, '/');
$home_output = render_page("Home", $home, $menu_html);
file_put_contents("$output_folder/index.html", $home_output);

echo "Site generated in $output_folder\n";

// Recursively copy assets folder to output folder
function recursive_copy($src, $dst) {
    $dir = opendir($src);
    if (!is_dir($dst)) {
        mkdir($dst, 0777, true);
    }
    while(false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir("$src/$file")) {
                recursive_copy("$src/$file", "$dst/$file");
            } else {
                copy("$src/$file", "$dst/$file");
            }
        }
    }
    closedir($dir);
}

recursive_copy("assets", "$output_folder/assets");
echo "Assets copied to $output_folder/assets\n";

// NEW: Generate parsed support_form.php in the output folder using the template from pages/partials/support_form.php
$issueFormTemplate = file_get_contents("pages/partials/support_form.php");
$issueFormContent = str_replace(
    ['{{github_user}}', '{{github_repo}}', '{{github_token}}', '{{title}}', '{{support_email}}', '{{sender_email}}'],
    [
        htmlspecialchars($github_user), 
        htmlspecialchars($repo), 
        htmlspecialchars($github_token),
        htmlspecialchars($site_title),
        htmlspecialchars($support_email),
        htmlspecialchars($sender_email)
    ],
    $issueFormTemplate
);
file_put_contents("$output_folder/support_form.php", $issueFormContent);
echo "support_form.php generated in $output_folder\n";
