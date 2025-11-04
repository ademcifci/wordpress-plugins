# Formidable Abandonment APG Modal

Enhances the Formidable Abandonment addon’s modal to follow the WAI-ARIA APG modal dialog pattern without modifying core files.

- Enqueues a small script that upgrades the modal’s accessibility when the addon is active
- Adds configurable strings for title, description, labels, and error message via a simple settings screen
- No-ops when the Abandonment addon is not active

## Requirements
- WordPress
- Formidable Forms Abandonment addon

## Installation
1. Upload this folder to `wp-content/plugins/`
2. Activate “Formidable Abandonment APG Modal” in Plugins

## Settings
Go to `Settings → Abandonment APG Modal` to customize:
- Modal title
- Modal description
- Email label
- Button text
- Close label (accessible name)
- Email error message text

All values are optional. Leave blank to keep the default strings.

## How It Works
- Hooks `wp_footer` at priority 12 to enqueue `assets/apg-modal.js` after the addon registers its own scripts
- Passes the configured strings to JS via `wp_localize_script('FrmAbdnApg', …)`
- Runs only on the frontend and only when the abandonment addon is detected

## Development Notes
- Version: 0.1.7
- PHP entry: `formidable-abandonment-apg-modal.php`

## License
GPL-2.0-or-later (see plugin header)

