# PHP GitHub Site Generator

A lightweight PHP static site generator for GitHub projects that creates a professional documentation website with minimal configuration.

## Overview

This tool automatically generates a static website based on your GitHub repository content. It pulls README.md and other Markdown files from your repo, converts them to HTML, and creates a fully functioning website with navigation, download page, and support functionality.

## Features

- [x] Home page generated from `README.md`
- [x] Additional Markdown pages (e.g., `INSTALLATION.md`, `TROUBLESHOOTING.md`)
- [x] Download page with GitHub Releases information
- [x] Support page with:
  - List of open GitHub issues
  - Contact form with email notifications
- [x] Code syntax highlighting with highlight.js
- [ ] Donations page with payment integration (planned)

### Future Plans

- Local caching for faster generation
- Favicon customization
- Theme customization options

### See in action

- https://opensim-helpers.dev/

## Installation

1. Clone this repository:
```bash
git clone https://github.com/magicoli/php-site-generator.git
cd php-site-generator
```

2. Install dependencies:
```bash
composer install
```

3. Create configuration file:
```bash
cp config.json.example config.json
```

4. Edit `config.json` with your project details

5. Generate the site:
```bash
php generate.php
```

6. Set up a cron task to automatically update your site:
```bash
# Edit crontab
crontab -e

# Add a line to run the generator hourly (adjust the path as needed)
0 * * * * cd /home/path/to/php-site-generator && php generate.php >> /var/log/site-generator.log 2>&1
```

The cron task will automatically rebuild your site on a regular schedule, keeping the content in sync with your GitHub repository.

## Configuration

Edit `config.json` to configure your site:

```json
{
  "title": "My GitHub Project",
  "github_user": "user",
  "repo": "my-repo",
  "github_branch": "master",
  "github_token": "your_token_here", 
  "support_email": "support@example.com",
  "sender_email": "noreply@example.com",
  "output_folder": "output",
  "menu": [
    { "title": "About", "file": "/" },
    { "title": "Download", "file": "download" },
    { "title": "Installation", "file": "INSTALLATION.md" },
    { "title": "Troubleshooting", "file": "TROUBLESHOOTING.md" },
    { "title": "Support", "file": "support" }
  ]
}
```

Important configuration options:
- `github_token`: Optional, helps avoid API rate limitations
- `support_email`: Required for the support form to function
- `sender_email`: Optional, defaults to support_email if not set
- `output_folder`: your website root directory (e.g. /var/www/html)

## Project Structure

```
/php-site-generator/
├── config.json            // Configuration for the site and pages
├── generate.php           // Main script to generate the static site
├── template.php           // HTML template for the pages
├── vendor/                // Composer dependencies (Parsedown)
├── assets/                // CSS, images
│   └── style.css          // Custom styling
└── pages/                 // Local Markdown files and templates
    ├── download.md        // Template for download page
    ├── support.md         // Template for support page
    └── partials/          // Reusable page components
        └── support_form.php // Support form template

../www/                    // Generated static site (output folder defined in config.json)
├── assets
│   └── style.css
├── download.html
├── index.html
├── installation.html
├── support.php            // PHP for dynamic support form
├── support_form.php       // Included in support.php
└── troubleshooting.html
```

## How It Works

The `generate.php` script:
1. Loads your configuration
2. Fetches content from your GitHub repository
3. Processes Markdown files into HTML
4. Creates special pages like download and support
5. Assembles a complete website with navigation
6. Outputs everything to your specified folder

## License

AGPL-3.0

## Credits

- [Parsedown](https://github.com/erusev/parsedown) for Markdown parsing
- [Bootstrap](https://getbootstrap.com/) for responsive layout
- [highlight.js](https://highlightjs.org/) for code syntax highlighting
