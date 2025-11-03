# Formidable – Simple Progress Field

A lightweight, drag‑and‑drop “Simple Progress” field for Formidable Forms that shows a compact “Step X of Y” badge. It auto‑detects the current page and total pages in multi‑page forms, works with both Lite and Pro, and can auto‑inject itself so you don’t have to add a field on every page.

## Features
- Drop‑in field: adds a new field type named `Simple Progress`.
- Auto page detection: calculates current/total steps for multi‑page forms.
- Builder preview: live updates as you type the Step label and as you add/move page breaks.
- Customizable label: per‑field text for the word before numbers (e.g., “Step”, “Stage”, “Étape”).
- Auto‑inject mode (optional): render once per page automatically at a chosen position:
  - Below title (default)
  - Above title
  - Above submit button
  - Below submit button
- ARIA live region (optional): enable `role="status" aria-live="polite"` when you want step changes announced by screen readers.
- Clean styling: minimal scoped CSS pill; easy to override in your theme.
- Builder icon: custom SVG for the field’s “Add Fields” tile (CSS‑only, no core changes).
- No core modifications: ships as its own plugin; does not edit Formidable files.

## Requirements
- WordPress 5.8+
- Formidable Forms Pro
  - Pro is required for multi‑page forms and for the auto‑inject feature to render correct step counts on the front‑end.
  - Without Pro paging, forms are single‑page and the badge shows “1 of 1”.

## Installation
1. Download `formidable-simple-progress.zip`.
2. In WP Admin → Plugins → Add New → Upload Plugin → choose the zip → Install → Activate.

## Usage
### Add the field manually (per page)
1. Open your form in the Formidable builder.
2. Drag “Simple Progress” into the form where you want it to appear.
3. In Field Options:
   - Set “Step label” (e.g., Step, Stage, Étape). The builder preview updates immediately.
   - Optionally enable “Make ARIA live region (role=“status”)”.

### Auto‑inject once per page (no dragging on each page)
1. Add a single “Simple Progress” field anywhere in the form.
2. In Field Options, enable “Auto‑inject progress badge on all pages”.
3. Choose an “Auto‑inject position” (below/above title, above/below submit).
4. The field itself won’t render inline when auto‑inject is on (to avoid duplicates).

## Styling
- Base CSS: `assets/css/simple-progress.css`
  - Adjust colors, padding, and border‑radius for the badge.
- Builder icon CSS: `assets/css/admin.css`
  - The “Add Fields” tile icon uses the SVG at `assets/images/simple-progress.svg` via CSS.
  - Replace this SVG file with your own to customize the icon, then hard‑refresh the builder.

## Accessibility
- Enable the “Make ARIA live region” option if you want step changes announced by assistive tech.
- When off (default), the badge is purely presentational.

## Notes & Limitations
- Front‑end step counts and auto‑inject rely on Pro’s paging data. Without Pro paging, forms are single‑page and will show “1 of 1”.
- The Customize HTML box is intentionally hidden for this field since its inner markup is generated and very small; style via CSS instead.

## File Structure (high‑level)
```
formidable-simple-progress/
├─ formidable-simple-progress.php           # Plugin bootstrap & hooks
├─ includes/
│  └─ class-frm-simple-progress-field.php  # Field type implementation
├─ views/
│  ├─ field-simple-progress.php            # Builder preview markup
│  └─ settings-simple-progress.php         # Field settings (label, auto-inject, ARIA)
├─ assets/
│  ├─ css/
│  │  ├─ simple-progress.css               # Front-end badge styles
│  │  └─ admin.css                         # Builder icon CSS
│  ├─ js/
│  │  └─ builder-preview.js                # Live builder preview logic
│  └─ images/
│     └─ simple-progress.svg               # Builder tile icon
```

## Development
- Field registration: via `frm_get_field_type_class` and `frm_available_fields` filters.
- Hide Customize HTML UI: `frm_show_custom_html` returns false for `simple_progress`.
- Auto‑inject rendering: hooks `frm_before_title`, `frm_after_title`, `frm_before_submit_btn`, `frm_after_submit_btn`.
- Builder preview: JS observes page break changes and input updates; no core changes.

## Changelog
### 1.0.0
- Initial release: Simple Progress field, live builder preview, auto‑inject, ARIA option, CSS‑only builder icon.

## Support
Please open an issue on GitHub with details (WP + Formidable versions, screenshots/console output, and steps to reproduce).

