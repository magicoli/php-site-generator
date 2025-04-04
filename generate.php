<?php
require_once "vendor/autoload.php"; // load full Parsedown via Composer

$config = json_decode(file_get_contents("config.json"), true);
$parsedown = new Parsedown();

$site_title = $config["title"] ?? "Site";

$github_user = $config["github_user"];
$repo = $config["repo"];

# Exit if GitHub user or repo is not set
if (empty($github_user) || empty($repo)) {
    die( "Error: GitHub user or repo not set in config.json.\n");
}

$github_token = $config["github_token"] ?? null;
$github_branch = $config["github_branch"] ?? 'master'; // default to master if not set
$menu = $config["menu"];
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

function render_page($title, $content, $menu_html) {
    // Make $site_title, $github_user and $repo available in the template
    global $site_title, $github_user, $repo;
    ob_start();
    include "template.php";
    return ob_get_clean();
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
        } else {
            $html = "<p>Special page: <strong>$file</strong> (to be implemented)</p>";
        }
    }

    // Build friendly menu with active highlighting
    $menu_html = "";
    foreach ($menu as $m) {
        if (stripos($m["file"], ".md") !== false) {
            $menu_slug = strtolower(pathinfo($m["file"], PATHINFO_FILENAME));
            $link = $menu_slug . ".html";
        } else {
            $link = $m["file"] . ".html";
        }
        $active = ($m["file"] === $file) ? 'active strong' : '';
        $menu_html .= "<li class='nav-item $active'><a class='nav-link $active' href='{$link}'>" . htmlspecialchars($m["title"]) . "</a></li>";
    }

    $output = render_page($title, $html, $menu_html);
    file_put_contents("$output_folder/{$slug}.html", $output);
}

// Generate homepage from README.md
$readme = fetch_markdown($github_user, $repo, "README.md");
$home = $parsedown->text($readme); // Convert Markdown to HTML
$menu_html = ""; // Ensure menu_html is defined for the homepage
foreach ($menu as $m) {
    if (stripos($m["file"], ".md") !== false) {
        $slug = strtolower(pathinfo($m["file"], PATHINFO_FILENAME));
        $link = $slug . ".html";
    } else {
        $link = $m["file"] . ".html";
    }
    // No active item for homepage
    $menu_html .= "<li class='nav-item'><a class='nav-link' href='{$link}'>" . htmlspecialchars($m["title"]) . "</a></li>";
}
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
