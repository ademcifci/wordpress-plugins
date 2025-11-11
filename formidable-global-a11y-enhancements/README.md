# Formidable Global A11y Enhancements

Accessibility improvements for Formidable Forms outside of file uploads, with admin toggles.

- Other inputs cleanup: hides duplicate screen-reader labels and moves text into `aria-label`.
- Optional removal of `role="alert"` from inline field errors when disabled via `frm_include_alert_role_on_field_errors`.
- Global message focus: focuses the error summary (when our summary is used and Accessible Error Summary is not active) and the success message after submit.
- Multi-page focus: on next/previous, focuses the first visible heading (configurable H1–H6); prefers the error summary when present.
- Adds `aria-describedby` to radio/checkbox controls to reference the field description and inline error.

## Requirements
- WordPress
- Formidable Forms (Lite or Pro)

## Installation
1. Upload this folder to `wp-content/plugins/`
2. Activate "Formidable Global A11y Enhancements" in Plugins

## Settings
Navigate to `Settings → Formidable A11y` to toggle features:
- Other fields cleanup (default: ON)
- Global error message focus (default: OFF)
- Success message focus (default: ON)
- Multi-page focus (default: ON)
- Multi-page heading level H1–H6 (default: H1)
- Add aria-describedby to radio/checkbox controls (default: ON)

If "Formidable – Accessible Error Summary" is active, this plugin auto-disables its own global error focusing to avoid conflicts.

## Behavior Details
- Localized settings object: `ff_globa11y` controls runtime behavior in JS.
- Multi-page transitions are managed via click handlers and short retries; full-page navigations use `sessionStorage` to focus after reload.
- AJAX handling is scoped to Formidable-related requests to avoid unrelated triggers.
- Inline error `role="alert"` removal respects the `frm_include_alert_role_on_field_errors` filter.
- Optional debugging of focusable tabindex can be enabled with `?ff_globa11y_debug=1`.

## Development Notes
- Version: 1.2.4
- Enqueues `assets/formidable-global-a11y-enhancements.js`
- PHP entry: `formidable-global-a11y-enhancements.php`

## Changelog
- 1.2.4
  - Add setting to choose heading level (H1–H6) for multi-page focus; H1 remains default.
  - Add option to inject `aria-describedby` on radio/checkbox inputs.
  - Scope AJAX hooks to Formidable-related requests.
  - Respect `frm_include_alert_role_on_field_errors` to optionally remove `role="alert"` from inline field errors.
  - Add i18n wrappers for admin settings and load text domain.

## License
GPL-2.0+ (see plugin header)

