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

function render_page($title, $content, $menu_html) {
    ob_start();
    include "template.php";
    return ob_get_clean();
}

foreach ($menu as $item) {
    $file = $item["file"];
    $title = $item["title"];
    $html = "";

    if (str_ends_with($file, ".md")) {
        $md = fetch_markdown($github_user, $repo, $file);
        $html = $parsedown->text($md); // Convert Markdown to HTML
    } else {
        $html = "<p>Page spéciale : <strong>$file</strong> (à implémenter)</p>";
    }

    $menu_html = "";
    foreach ($menu as $m) {
        $menu_html .= "<li><a href='{$m["file"]}.html'>{$m["title"]}</a></li>";
    }

    $output = render_page($title, $html, $menu_html);
    file_put_contents("$output_folder/{$file}.html", $output);
}

// Générer page d'accueil à partir de README.md
$readme = fetch_markdown($github_user, $repo, "README.md");
$home = $parsedown->text($readme); // Convert Markdown to HTML
$menu_html = ""; // Ensure menu_html is defined for the homepage
foreach ($menu as $m) {
    $menu_html .= "<li><a href='{$m["file"]}.html'>{$m["title"]}</a></li>";
}
$home_output = render_page("Accueil", $home, $menu_html);
file_put_contents("$output_folder/index.html", $home_output);

echo "Site généré dans $output_folder\n";
