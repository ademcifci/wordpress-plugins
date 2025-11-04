# Formidable Dropzone A11y Enhancements

Accessibility improvements for Formidable Forms Dropzone upload fields.

- Focus management after upload/remove and after native file dialog closes
- Live region announcements for file add/remove
- Clearer remove link labels (e.g. “Remove file example.pdf”)
- Reduces redundant roles/announcements on Dropzone containers

## Requirements
- WordPress
- Formidable Forms with Dropzone-enabled file uploads

## Installation
1. Upload this folder to `wp-content/plugins/`
2. Activate “Formidable Dropzone A11y Enhancements” in Plugins

The script auto-detects Dropzone instances and enhances them. No settings are required.

## What It Does
- Creates an assertive, scoped live region near each Dropzone to announce changes
- Adjusts remove link accessible names and removes tooltip `title` attributes
- Restores focus to the upload button after changes and after the native file chooser closes
- Avoids duplicate group roles on containers to reduce screen reader noise

## Notes
- Non-upload accessibility features were moved into the separate “Formidable Global A11y Enhancements” plugin.

## Development Notes
- Version: 1.0.2
- Enqueues `assets/formidable-dropzone-a11y-enhancements.js` and `assets/formidable-dropzone-a11y-enhancements.css`.
- PHP entry: `formidable-dropzone-a11y-enhancements.php`.

## License
GPL-2.0+ (see plugin header)

