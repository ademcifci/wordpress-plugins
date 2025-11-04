# FF Fieldset Container (Formidable Forms)

Adds semantic fieldset grouping to Formidable Forms with two lightweight field types: Fieldset Start and Fieldset End. Use them to wrap related fields in a proper `<fieldset>` with a `<legend>` for better accessibility, structure, and styling.

## Features

- Two new field types in the builder: `Fieldset Start` and `Fieldset End`.
- Outputs accessible `<fieldset>` and optional visible/hidden `<legend>`.
- Uses the field label as legend text; supports field description.
- No data storage — purely structural; validation-safe.
- Builder preview + simple setting: “Show legend visually”.
- Includes minimal front‑end CSS (override-friendly).
- Localizable strings via `ff-fieldset-container` text domain.

## Requirements

- WordPress (tested with modern versions)
- Formidable Forms (Lite or Pro) installed and active

## Installation

1. Upload the plugin to `wp-content/plugins/ff-fieldset-container` or install the ZIP via the WordPress admin.
2. Activate “FF Fieldset Container” from Plugins.
3. Ensure Formidable Forms is active.

## Usage

1. In the Formidable builder, add a `Fieldset Start` where you want the group to begin.
2. Add all fields that belong inside the group.
3. Add a `Fieldset End` where the group should close.
4. Open the `Fieldset Start` settings:
   - Set the Label to control the `<legend>` text.
   - Optionally add a Description (renders below the legend).
   - Toggle “Show legend visually” to show/hide the legend visually while keeping it accessible for screen readers.

Tip: Always pair one Start with one End. Mis‑nesting or forgetting an End will break the markup order for following fields.

## Output Markup

The Start field prints the opening container with legend and description; the End field prints the closing tag. Example of rendered HTML (simplified):

```html
<fieldset id="frm_field_[id]_container" class="frm_fieldset_container frm_fieldset_start frm_form_field form-field [required_class][error_class]">
  <legend class="optional frm_screen_reader">Your Group Title</legend>
  <div class="frm_description">Optional description text</div>
  <!-- all fields placed between Start and End will appear here -->
</fieldset>
```

Classes you can target in CSS:
- `.frm_fieldset_container` — wrapper fieldset
- `.frm_fieldset_start` — applied on the Start field’s container
- `.frm_screen_reader` — visually-hidden utility applied to the legend when needed

## Styling

Basic front‑end styles are provided in `assets/css/fieldset-container.css`. You can override them in your theme or a custom plugin. Example tweaks:

```css
/* Remove borders for a minimal look */
.frm_fieldset_container { border: 0; background: transparent; padding: 0; }

/* Emphasize the legend */
.frm_fieldset_container legend { font-size: 1.2em; color: #222; }
```

## How It Works

- Registers field types and classes via an autoloader.
- Adds builder buttons and icons for the new field types.
- Overrides Formidable’s custom HTML for these types to emit only opening/closing fieldset markup.
- Persists the legend visibility setting as a custom field option.

Key files:
- `ff-fieldset-container.php` — plugin bootstrap, hooks, filters, styles
- `classes/FF_FieldsetStart.php` — Start field type, builder settings, front‑end output
- `classes/FF_FieldsetEnd.php` — End field type, front‑end output
- `views/builder-field-start.php` — Builder preview for Start
- `views/builder-field-end.php` — Builder preview for End
- `views/builder-settings-start.php` — “Show legend visually” toggle
- `assets/css/fieldset-container.css` — Front‑end CSS
- `assets/css/admin.css` — Builder palette icon CSS

## Accessibility

- Uses a proper `<fieldset>` and `<legend>` for grouping related fields.
- “Show legend visually” allows keeping the legend for screen readers while visually hiding it using a safe utility class.
- Description content is preserved and placed immediately after the legend.

## Localization

- Text domain: `ff-fieldset-container`
- Load path: `languages/`

## FAQ

- Does this store any data? No — these fields are structural only and do not submit values.
- Can I have multiple groups? Yes — add multiple Start/End pairs as needed. Ensure each pair is properly ordered.
- Can I style each fieldset differently? Yes — target by field/container IDs or add custom classes via theme filters.

## Development

- Autoloading targets classes matching `FF_Fieldset*` under `classes/`.
- Filters used:
  - `frm_available_fields` to register field buttons
  - `frm_get_field_type_class` to map type slugs to classes
  - `frm_custom_html` to output opening/closing markup
  - `frm_update_field_options` to persist the legend visibility

To change output, see:
- `classes/FF_FieldsetStart.php`
- `classes/FF_FieldsetEnd.php`

## Changelog

- 1.3.0
  - Start/End fields with builder previews
  - Legend visibility toggle
  - Front‑end and admin styles

## License

GPL-2.0+ — see the plugin header for details.

## Credits

Developed by Adem Cifcioglu. Not affiliated with Strategy11/Formidable Forms.

