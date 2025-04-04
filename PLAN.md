# PLAN

PHP static site generator for GitHub projects

## Dependencies
```
composer require erusev/parsedown
```

## Objective
Generate a static site based on a GitHub repository containing a PHP library, without bloating the repository itself.

## Features

- [x] Home page generated from `README.md`
- [x] Additional Markdown pages (e.g., `INSTALLATION.md`, `TROUBLESHOOTING.md`)
- [x] Downloads page (fetch GitHub Releases info)
- [ ] Issues page (fetch GitHub Issues data; to be implemented)
- [ ] Donations page with Stripe / PayPal integration (to be implemented)

## Structure

```
/php-backend/
├── config.json            // Configuration for the site and pages
├── generate.php           // Main script to generate the static site
├── template.php           // HTML template for the pages
├── Parsedown.php          // Markdown parser → HTML
├── assets/                // CSS, images
│   └── style.css          // Custom styling (prefer Bootstrap classes)
└── pages/                 // Local Markdown files (if needed)

../output/                     // Generated static site (output folder defined in config.json)
├── assets
│   └── style.css
├── donate.html
├── downloads.html
├── index.html
├── installation.html
├── issues.html
├── troubleshooting.html
└── ...
```

## Sample Configuration (config.json)

````json
{
  "title": "Site Title",
  "github_user": "magicoli",
  "repo": "opensim-helpers",
  "github_branch": "master",
  "github_token": "your_token_here", // optional
  "output_folder": "output",
  "menu": [
    { "title": "OpenSim Helpers", "file": "README.md" },
    { "title": "Installation", "file": "INSTALLATION.md" },
    { "title": "Troubleshooting", "file": "TROUBLESHOOTING.md" },
    { "title": "Downloads", "file": "downloads" },
    { "title": "Issues", "file": "issues" },
    { "title": "Donate", "file": "donate" }
  ]
}
````

## Steps of the generate.php Script

1. Load the `config.json`.
2. Download Markdown files from GitHub using raw URLs or the GitHub API.
3. Convert Markdown to HTML using Parsedown.
4. Inject content into a common HTML template (`template.php`).
5. Dynamically generate navigation menus.
6. Generate special pages (Downloads, Issues, Donate) as placeholders or with API data.
7. Copy assets to the output folder.
8. Save all pages in the specified output folder.

## Tasks & To-Dos

- [x] Implement Markdown fetcher in `generate.php`.
- [ ] (Optional) Add local caching for fetched Markdown.
- [x] Create a responsive HTML template in `template.php`.
- [x] Generate dynamic navigation menus.
- Implement special pages
  - [x] Downloads
  - [ ] Issues
  - [ ] Donate
- [ ] Enable minimal customization options
  - [ ] favicon
  - [x] project name
  - ...
