<?php
require_once "vendor/autoload.php"; // load full Parsedown via Composer

// Import the SCSS compiler
use ScssPhp\ScssPhp\Compiler;

$config = json_decode(file_get_contents("config.json"), true);
$parsedown = new Parsedown();

$github_user = $config["github_user"];
$repo = $config["repo"];

# Exit if GitHub user or repo is not set
if (empty($github_user) || empty($repo)) {
    die( "Error: GitHub user or repo not set in config.json.\n");
}

// Get domain name for fallback site title
$site_title =  $config["title"] ?? $repo ?? 'GitHub Project';
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

if (empty($support_email)) {
    error_log( "Error: support_email is not set in config.json, support form is disabled.");
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

// New function: fetch GitHub funding info from FUNDING.yml
function fetch_funding_info($user, $repo) {
    global $github_token;
    
    // Try to get the FUNDING.yml file from the .github directory
    $funding_path = ".github/FUNDING.yml";
    $url = "https://api.github.com/repos/$user/$repo/contents/$funding_path";
    
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
    
    if ($result === false) {
        return null;
    }
    
    $data = json_decode($result, true);
    if (!isset($data['content']) || $data['encoding'] !== 'base64') {
        return null;
    }
    
    $content = base64_decode($data['content']);
    return parse_funding_yaml($content);
}

// New function: Parse YAML funding file to extract sponsorship links
function parse_funding_yaml($yaml_content) {
    $funding_links = [];
    $lines = explode("\n", $yaml_content);
    
    foreach ($lines as $line) {
        // Skip comments and empty lines
        if (empty(trim($line)) || strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Match platform: value format
        if (preg_match('/^([a-zA-Z_]+):\s*(.+)$/', $line, $matches)) {
            $platform = trim($matches[1]);
            $value = trim($matches[2]);
            
            // Skip empty values or placeholders
            if (empty($value) || $value === '#' || strpos($value, '# Replace with') !== false) {
                continue;
            }
            
            $funding_links[$platform] = $value;
        }
    }
    
    return $funding_links;
}

// New function: Validate GitHub sponsors profile
function validate_github_sponsor($username) {
    $url = "https://github.com/sponsors/$username";
    $opts = [
        "http" => [
            "method" => "HEAD",
            "header" => "User-Agent: PHP\r\n",
            "follow_location" => 0,  // Don't follow redirects
            "ignore_errors" => true, // Get response even if it's an error
        ]
    ];
    
    $context = stream_context_create($opts);
    $headers = get_headers($url, 1, $context);
    
    // Check if the page exists and doesn't redirect to a 404 or other error page
    // GitHub returns 200 for sponsors page that exists, or redirects to 404 for non-existent ones
    if (isset($headers[0]) && strpos($headers[0], '200') !== false) {
        // Additional check to ensure the sponsors page has active tiers
        $page_content = file_get_contents($url, false, stream_context_create([
            "http" => ["header" => "User-Agent: PHP\r\n"]
        ]));
        
        // Look for indicators that sponsorship is enabled
        // These strings appear in active sponsor pages but not in profiles without sponsorship
        return (
            strpos($page_content, 'tier-') !== false || 
            strpos($page_content, 'Sponsor this') !== false ||
            strpos($page_content, 'sponsor-tier') !== false
        );
    }
    
    return false;
}

// Helper function to get favicon for a URL
function get_favicon_url($url) {
    // Parse the URL to get the domain
    $parsed_url = parse_url($url);
    if (!isset($parsed_url['host'])) {
        return null;
    }
    
    $domain = $parsed_url['host'];
    
    // Try Google's favicon service first (most reliable)
    $favicon_url = "https://www.google.com/s2/favicons?domain=$domain&sz=32";
    
    // Optional: You could try to fetch the direct favicon from the site
    // but Google's service is generally more reliable
    
    return $favicon_url;
}

// New function: Generate HTML for sponsor links
function generate_sponsor_links($funding_info) {
    if (empty($funding_info)) {
        return null;
    }
    
    $html = '<div class="sponsor-links d-flex flex-wrap gap-2 my-4">';
    
    foreach ($funding_info as $platform => $value) {
        $url = '';
        $button_text = ucfirst($platform);
        $button_class = 'btn btn-secondary mb-2 me-2 shadow-sm';
        $icon = '';
        $is_valid = true;
        
        switch ($platform) {
            case 'github':
                // Validate GitHub sponsors profile before adding it
                if (validate_github_sponsor($value)) {
                    $url = "https://github.com/sponsors/$value";
                    $button_text = "GitHub Sponsors";
                    $icon = '<i class="fab fa-github me-2"></i>';
                    // $button_class = 'btn mb-2 me-2 shadow-sm'; // No btn-primary, custom color in CSS
                } else {
                    // Skip this entry if GitHub sponsor profile is not valid
                    $is_valid = false;
                    echo "GitHub sponsor profile for '$value' is not valid or active, skipping.\n";
                }
                break;
            case 'patreon':
                $url = "https://www.patreon.com/$value";
                $icon = '<i class="fab fa-patreon me-2"></i>';
                break;
            case 'open_collective':
                $url = "https://opencollective.com/$value";
                $button_text = "Open Collective";
                $icon = '<img src="assets/opencollective-icon.svg" alt="Open Collective" class="icon me-2" style="width: 1em; height: 1em;" />';
                break;
            case 'ko_fi':
                $url = "https://ko-fi.com/$value";
                $button_text = "Ko-fi";
                $icon = '<i class="fa fa-coffee me-2"></i>';
                break;
            case 'buy_me_a_coffee':
                $url = "https://www.buymeacoffee.com/$value";
                $button_text = "Buy Me A Coffee";
                $icon = '<i class="fa fa-coffee me-2"></i>';
                break;
            case 'tidelift':
                $url = "https://tidelift.com/funding/github/$value";
                $icon = '<i class="fa fa-life-ring me-2"></i>';
                break;
            case 'community_bridge':
                $url = "https://funding.communitybridge.org/projects/$value";
                $button_text = "Community Bridge";
                $icon = '<i class="fa fa-bridge me-2"></i>';
                break;
            case 'liberapay':
                $url = "https://liberapay.com/$value";
                $icon = '<i class="fa fa-hand-holding-heart me-2"></i>';
                break;
            case 'issuehunt':
                $url = "https://issuehunt.io/r/$value";
                $icon = '<i class="fa fa-bug me-2"></i>';
                break;
            case 'lfx_crowdfunding':
                $url = "https://funding.communitybridge.org/projects/$value";
                $button_text = "LFX Crowdfunding";
                $icon = '<i class="fa fa-hand-holding-usd me-2"></i>';
                break;
            case 'custom':
                $url = $value;
                $button_text = "Donate";
                
                // Get favicon for custom URL
                $favicon_url = get_favicon_url($url);
                if ($favicon_url) {
                    $icon = "<img src=\"$favicon_url\" alt=\"Favicon\" class=\"icon me-2\" style=\"width: 1em; height: 1em;\" />";
                } else {
                    $icon = '<i class="fa fa-heart me-2"></i>';
                }
                
                // Extract domain name for the button text
                $parsed_url = parse_url($url);
                if (isset($parsed_url['host'])) {
                    $domain = $parsed_url['host'];
                    // Remove www. prefix if present
                    $domain = preg_replace('/^www\./', '', $domain);
                    $button_text = ucfirst($domain);
                }
                
                break;
            default:
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    $url = $value;
                }
                break;
        }
        
        if (!empty($url) && $is_valid) {
            $html .= "<a href=\"$url\" class=\"$button_class d-inline-flex align-items-center justify-content-center px-3 py-2 rounded\" 
                      style=\"min-width: 180px;\" 
                      target=\"_blank\" rel=\"noopener\">$icon$button_text</a>\n";
        }
    }
    
    $html .= '</div>';
    
    // If no valid sponsor links were found, return null
    if ($html === '<div class="sponsor-links d-flex flex-wrap gap-2 my-4"></div>') {
        return null;
    }
    
    return $html;
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

// Compile a single SCSS file to CSS
function compile_scss_file($scss_file, $css_file) {
    try {
        $scss_content = file_get_contents($scss_file);
        
        $compiler = new Compiler();
        $compiler->setOutputStyle(\ScssPhp\ScssPhp\OutputStyle::COMPRESSED);
        
        $css = $compiler->compileString($scss_content)->getCss();
        
        file_put_contents($css_file, $css);
        echo "Compiled $scss_file to $css_file\n";
        return true;
    } catch (Exception $e) {
        echo "Error compiling SCSS: " . $e->getMessage() . "\n";
        return false;
    }
}

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
                // Check if this is an SCSS file
                if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'scss') {
                    // Convert SCSS to CSS in the destination directory
                    $scss_file = "$src/$file";
                    $css_name = basename($file, '.scss') . '.css';
                    $css_file = "$dst/$css_name";
                    compile_scss_file($scss_file, $css_file);
                } else {
                    // For non-SCSS files, simply copy them
                    copy("$src/$file", "$dst/$file");
                }
            }
        }
    }
    closedir($dir);
}

// First, pre-process the sponsor page to determine if we should include it in the menu
foreach ($menu as $key => $item) {
    if ($item["file"] === "sponsor") {
        // Process sponsor page
        $funding_info = fetch_funding_info($github_user, $repo);
        
        // Skip this page if no funding info is found
        if (empty($funding_info)) {
            echo "No sponsor information found, skipping sponsor page generation.\n";
            unset($menu[$key]); // Remove from menu
            continue;
        }
        
        // Generate sponsor links HTML
        $sponsor_links_html = generate_sponsor_links($funding_info);
        
        // Skip this page if no valid sponsor links are found
        if (empty($sponsor_links_html)) {
            echo "No valid sponsor links found, skipping sponsor page generation.\n";
            unset($menu[$key]); // Remove from menu
            continue;
        }
        
        // Store the sponsor links HTML for later use
        $GLOBALS['sponsor_links_html'] = $sponsor_links_html;
    }
}

// Now process all menu items to generate pages
foreach ($menu as $key => $item) {
    $file = $item["file"];
    $slug = strtolower(pathinfo($file, PATHINFO_FILENAME));
    $title = $item["title"];
    $html = "";

    if (str_ends_with($file, ".md")) {
        $md = fetch_markdown($github_user, $repo, $file);
        $html = $parsedown->text($md); // Convert Markdown to HTML
    } else {
        if ($file === "download") {
            // Fetch latest stable release data
            $release = fetch_latest_release($github_user, $repo);
            // Load the Markdown template for the download page
            $template = file_get_contents("pages/download.md");
            
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
        } elseif ($file === "sponsor") {
            // We already verified this page should be included, so generate it
            $template = file_get_contents("pages/sponsor.md");
            
            // Replace placeholders in the sponsor template
            $template = str_replace("{{sponsor_links}}", $GLOBALS['sponsor_links_html'], $template);
            $template = str_replace("{{github_user}}", htmlspecialchars($github_user), $template);
            $template = str_replace("{{repo}}", htmlspecialchars($repo), $template);
            
            // Convert the Markdown template to HTML
            $html = $parsedown->text($template);
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

