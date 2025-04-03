<?php
require_once "Parsedown.php";

$config = json_decode(file_get_contents("config.json"), true);
$parsedown = new Parsedown();

$github_user = $config["github_user"];
$repo = $config["repo"];
$menu = $config["menu"];

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
        $html = $parsedown->text($md);
    } else {
        $html = "<p>Page spéciale : <strong>$file</strong> (à implémenter)</p>";
    }

    $menu_html = "";
    foreach ($menu as $m) {
        $menu_html .= "<li><a href='{$m["file"]}.html'>{$m["title"]}</a></li>";
    }

    $output = render_page($title, $html, $menu_html);
    file_put_contents("../www/{$file}.html", $output);
}

// Générer page d'accueil à partir de README.md
$readme = fetch_markdown($github_user, $repo, "README.md");
$home = $parsedown->text($readme);
$home_output = render_page("Accueil", $home, $menu_html);
file_put_contents("../www/index.html", $home_output);

echo "Site généré dans /output\n";
