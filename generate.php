<?php
require_once "vendor/autoload.php"; // Changed: load full Parsedown via Composer

$config = json_decode(file_get_contents("config.json"), true);
$parsedown = new Parsedown();

$github_user = $config["github_user"];
$repo = $config["repo"];
$menu = $config["menu"];
$output_folder = empty($config["output_folder"]) ? "output" : $config["output_folder"];
$output_folder = rtrim($output_folder, "/"); // Remove trailing slash if present

# Exit with error if output folder is not writable
if (!is_writable($output_folder)) {
    echo "Error: Output folder '$output_folder' does not exist or is not writable.\n";
    exit(1);
}

function fetch_markdown($user, $repo, $file) {
    // $url = "https://raw.githubusercontent.com/$user/$repo/main/$file";
    // $url = "https://api.github.com/repos/$user/$repo/contents/$file";
    $url = "https://raw.githubusercontent.com/$user/$repo/refs/heads/master/$file";
    return file_get_contents($url);
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
    file_put_contents("$output_folder/{$file}.html", $output); // Use output_folder
}

// Générer page d'accueil à partir de README.md
$readme = fetch_markdown($github_user, $repo, "README.md");
$home = $parsedown->text($readme); // Convert Markdown to HTML
$menu_html = ""; // Ensure menu_html is defined for the homepage
foreach ($menu as $m) {
    $menu_html .= "<li><a href='{$m["file"]}.html'>{$m["title"]}</a></li>";
}
$home_output = render_page("Accueil", $home, $menu_html);
file_put_contents("$output_folder/index.html", $home_output); // Use output_folder

echo "Site généré dans $output_folder\n";
