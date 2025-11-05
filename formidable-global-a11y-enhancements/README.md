# Formidable Global A11y Enhancements

Accessibility improvements for Formidable Forms outside of file uploads, with admin toggles.

- “Other” inputs cleanup: hides duplicate screen-reader labels, moves text into `aria-label`, removes redundant alert roles
- Global message focus: on submit, focuses the error summary or success message
- Multi-page focus: on next/previous, focuses the first visible H1; prefers error summary when present

## Requirements
- WordPress
- Formidable Forms (Lite or Pro)

## Installation
1. Upload this folder to `wp-content/plugins/`
2. Activate “Formidable Global A11y Enhancements” in Plugins

## Settings
Navigate to `Settings → Formidable A11y` to toggle features:
- Other fields cleanup (default: ON)
- Global message focus (default: OFF)
- Multi-page H1 focus (default: ON)

If “Formidable – Accessible Error Summary” is active, this plugin auto-disables its own global message focusing to avoid conflicts.

## Behavior Details
- Localized settings object: `ff_globa11y` controls runtime behavior in JS
- Multi-page transitions are managed via click handlers and short retries; full-page navigations use `sessionStorage` to focus after reload
- Optional debugging of focusable tabindex can be enabled with `?ff_globa11y_debug=1`

## Development Notes
- Version: 1.1.1
- Enqueues `assets/formidable-global-a11y-enhancements.js`
- PHP entry: `formidable-global-a11y-enhancements.php`

## Changelog
- 1.1.1
  - Focus H1 only after explicit multi-page navigation (Next/Prev) or when a session flag indicates navigation; avoid focusing H1 on first load or generic AJAX completions.

## License
GPL-2.0+ (see plugin header)
