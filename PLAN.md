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
- [x] Additional pages generated from `.md` files (INSTALLATION.md, TROUBLESHOOTING.md…)
- [ ] Downloads page (info on the latest stable release + changelog)
- [ ] Issues page (simplified view of open issues)
- [ ] Donations page with Stripe / PayPal links

## Structure

/php-backend/
├── config.json            // Simple configuration for the site and pages
├── generate.php           // Main script to generate the static site
├── template.php           // HTML template for the pages
├── Parsedown.php          // Markdown parser → HTML
├── /assets/               // CSS, images
└── /pages/                // Local Markdown files (if needed)

/www/                      // Result of generated static site, separate from the backend (set in config.json)
├── index.html
├── installation/
├── troubleshooting/
...

## Sample configuration

```php
return [
  'github_user' => 'magicoli',
  'repo' => 'opensim-helpers',
  'menu' => [
    ['title' => 'OpenSim Helpers', 'file' => 'README.md'],
    ['title' => 'Installation', 'file' => 'INSTALLATION.md'],
    ['title' => 'Troubleshooting', 'file' => 'TROUBLESHOOTING.md'],
    ['title' => 'Downloads', 'file' => 'downloads'],
    ['title' => 'Issues', 'file' => 'issues'],
    ['title' => 'Donate', 'file' => 'donate'],
  ],
];
```

## Steps of the `generate.php` script

1. Load the config
2. Download Markdown files from GitHub (via raw.githubusercontent.com)
3. Convert Markdown to HTML with Parsedown
4. Inject into a common HTML template
5. Generate special pages:
   - downloads: call to the GitHub Releases API
   - issues: call to the GitHub Issues API
   - donate: static HTML based on config
6. Save in output folder set in config output_folder paremeter

## Tasks to do

- [ ] Write the Markdown fetcher
- [ ] Handle local caching (optional)
- [ ] Create the HTML template
- [ ] Generate dynamic navigation/menu
- [ ] Add special pages
- [ ] Plan for minimal customization (favicon, project name, etc.)
