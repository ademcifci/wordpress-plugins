# Formidable Custom Plugins

A collection of custom accessibility and UX plugins that enhance Formidable Forms without modifying core. Each plugin is self‑contained and can be installed independently.

These are all a work in progress. While each plugin has been tested, and hasn't broken anything, you use at your own risk knowing they are in development.

Feel free to raise any issues you find, or contribute to fixes.

Note: These plugins are not affiliated with or endorsed by Formidable Forms or Strategy11.

## Contents
- formidable-global-a11y-enhancements — Global frontend accessibility helpers: message focus, multipage focus, “Other” input cleanup, and aria-describedby injection for choice inputs. Respects the Formidable alert‑role filter.
- formidable-accessible-errors — Accessible error summary that focuses on submit and links errors to fields; disables first‑error focus and per‑field alert roles.
- formidable-dropzone-a11y-enhancements — A11y fixes for Dropzone upload fields: focus management, better announcements, “Other” label cleanup.
- formidable-duet-datepicker — Adds a Duet Date Picker–powered field type; loads assets only when needed; integrates with builder and entries. (this is probably the most complex of the bunch, and while it works, I'm sure there are problems I haven't found yet...)
- ff-fieldset-container-field — Unified Fieldset Start/End field types for grouping form content; builder support and i18n.
- formidable-radio-checkbox-fieldset — Adds semantic fieldset/legend around radio/checkbox groups via builder template override, with options for legends and ARIA roles.
- formidable-builder-collapsed-sections — Collapses native Formidable Sections in the builder UI for easier navigation.
- formidable-simple-progress — Lightweight “Step X of Y” progress field for multipage forms; optional auto‑inject.

## Requirements
- WordPress with Formidable Forms (Lite or Pro) enabled.
- PHP and WP versions per your site environment (plugins are lightweight and use standard WP APIs).

## Installation
You can use any plugin independently:
1. Copy the desired plugin folder from this repo to your site’s `wp-content/plugins/` directory.
2. In WordPress Admin → Plugins, Activate the plugin(s).

Recommended pairs:
- Accessible error flows: install both `formidable-accessible-errors` and `formidable-global-a11y-enhancements`.
- Choice groups semantics: install `formidable-radio-checkbox-fieldset` (for builder templates) and/or `ff-fieldset-container-field` (fieldset start/end containers).

## Notable Interactions
- Accessible Error Summary vs Global A11y Enhancements:
  - When `formidable-accessible-errors` is active, Global A11y Enhancements detects it and skips its own error summary focusing to avoid conflicts.
  - Global A11y Enhancements respects the Formidable filter `frm_include_alert_role_on_field_errors`. If a theme/plugin disables alert roles (returns false), it removes `role="alert"` from inline field errors on the frontend.
- AJAX scope: Global A11y Enhancements scopes its AJAX listeners to Formidable‑related requests to avoid unrelated triggers.
- Duet Date Picker: loads Duet assets from a CDN to ensure module/nomodule compatibility; only enqueued when a form has a Duet field (and in relevant admin screens).

## Per‑Plugin Highlights
- Formidable Global A11y Enhancements
  - Focuses success messages and, when appropriate, error summaries.
  - On multipage navigation, focuses the first visible heading (configurable H1–H6) with retries; prefers error summary when present.
  - Tidies “Other” text inputs and injects `aria-describedby` on radio/checkbox inputs to reference descriptions and inline errors.
  - Settings in Admin → Settings → Formidable A11y; feature toggles per need.

- Formidable – Accessible Error Summary
  - Replaces default error block with an accessible summary list that links to fields.
  - Disables default first‑error focus and per‑field alert roles for a cleaner UX.

- Formidable Dropzone A11y Enhancements
  - Improves keyboard focus and announcements during file uploads and error states.

- Formidable Duet Date Picker
  - New field type powered by Duet Date Picker Web Component.
  - Integrates with builder (options, linking ranges) and entries; assets loaded on demand.

- FF Fieldset Container Field
  - Fieldset Start/End field types for grouping content; supports legacy slugs for back‑compat.

- FF Radio & Checkbox Fieldset Wrapper
  - Builder template override adds `<fieldset>/<legend>` around radio/checkbox groups; options for legend visibility and group roles.

- Formidable Builder – Collapsed Sections
  - Collapses section blocks in the builder UI; quick expand/collapse for large forms.

- Formidable – Simple Progress Field
  - “Step X of Y” badge for multipage forms; optional auto‑inject positions and live region announcements.

## Development
- Each plugin uses standard WordPress hooks and the Settings API (where applicable).
- Strings are prepared for translation when needed (text domains vary per plugin).
- No core edits; features are implemented with filters/actions and small JS/CSS helpers.

## License
- Custom plugins: GPL‑2.0+ (see individual plugin headers).
- Formidable Forms, Pro, and Dates are © Strategy11 and included here only for local testing.

## Support
These plugins are provided as‑is. For issues or suggestions, open an issue on the repo with the plugin name, WP version, Formidable version, and steps to reproduce.

