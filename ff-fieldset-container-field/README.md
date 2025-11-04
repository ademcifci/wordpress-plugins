# FF Fieldset Container Field

Fieldset Start/End non-input fields for Formidable Forms, with legend visibility and optional heading level. Replaces earlier variants while keeping backward compatibility with legacy slugs.

- Adds “Fieldset Start” and “Fieldset End” to the builder palette
- Optional legend visibility and optional heading level (h2–h6)
- Builder-friendly: hides irrelevant input settings; shows description where useful
- Frontend CSS for consistent rendering; admin CSS for palette icons
- Back-compat: maps legacy `fieldset-start`/`fieldset-end` types to the new classes

## Requirements
- WordPress
- Formidable Forms (Lite or Pro)

## Installation
1. Upload this folder to `wp-content/plugins/`
2. Activate “FF Fieldset Container Field” in Plugins

## Usage
In the Formidable builder:
- Add a “Fieldset Start” where a group should begin and optionally set legend text and description
- Choose whether the legend is visually visible or screen-reader only
- Optionally choose a heading level (h2–h6) for the legend text
- Add “Fieldset End” where the group should end; you can add a description for notes

These are non-input fields; they do not collect values and do not require validation.

## Filters
- `fff_fieldset_start_classes` — filter the class list on the opening fieldset container
  - Signature: `apply_filters( 'fff_fieldset_start_classes', $classes, $field )`

## Development Notes
- Version: 1.0.0
- PHP entry: `ff-fieldset-container-field.php`
- Key classes: `includes/class-fff-fieldset-start.php`, `includes/class-fff-fieldset-end.php`
- Assets: `assets/css/fieldset-container.css` (frontend), `assets/css/admin.css` (admin)

## Internationalization
- Text domain: `ff-fieldset-container-field`

## License
GPL-2.0+ (see plugin header)

