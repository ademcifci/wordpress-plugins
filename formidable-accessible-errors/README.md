# Formidable – Accessible Error Summary

Accessible, keyboard-friendly error summary for Formidable Forms.

- Replaces the default invalid message block with a focusable, linked error summary
- Clicking an error jumps to the related field and focuses a sensible target
- Disables Formidable’s default “focus first error” and per-field `role=alert` to prevent double announcements
- Works with AJAX and multi-page forms; adds optional inline error icon decoration

## Requirements
- WordPress
- Formidable Forms (Lite or Pro)

## Installation
1. Upload this folder to `wp-content/plugins/`
2. Activate “Formidable – Accessible Error Summary” in Plugins

The plugin runs automatically on pages with Formidable forms that produce validation errors.

## Usage
- On submission with validation errors, focus moves to a heading in the summary.
- Each error is a link; activating it scrolls to the field container and focuses a logical control.
- AJAX submits and multipage transitions are supported.

## Filters
Customize behavior via WordPress filters:

- `ff_accessible_errors_inline_icon_enabled` (bool, default `true`)
  - Toggle the inline error icon decoration inside per-field messages.
  - Example: `add_filter('ff_accessible_errors_inline_icon_enabled', '__return_false');`

- `ff_accessible_errors_inline_icon_aria_label` (string, default `'Error:'`)
  - Change the accessible label for the inline error icon.
  - Example:
    ```php
    add_filter('ff_accessible_errors_inline_icon_aria_label', function () { return 'Error message:'; });
    ```

- `ff_accessible_errors_load_css` (bool, default `true`)
  - Disable the included CSS if you prefer to style errors yourself.
  - Example: `add_filter('ff_accessible_errors_load_css', '__return_false');`

## Compatibility
- If you also use “Formidable Global A11y Enhancements”, that plugin will automatically disable its global message focusing to avoid conflicts, allowing this plugin to lead.

## Development Notes
- Version: 1.2.0
- Enqueues `assets/js/ff-accessible-errors.js` and, if enabled, `assets/css/ff-accessible-errors.css`.
- PHP entry: `formidable-accessible-errors.php`.

## License
GPL-2.0+ (see plugin header)

