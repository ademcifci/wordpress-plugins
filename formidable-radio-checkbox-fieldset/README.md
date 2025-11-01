# FF Radio & Checkbox Fieldset Wrapper

A WordPress plugin that adds semantic `<fieldset>` and `<legend>` elements to Formidable Forms radio and checkbox field groups, improving accessibility and semantic HTML structure.

## Description

This plugin enhances Formidable Forms by wrapping radio and checkbox groups in proper `<fieldset>` and `<legend>` tags, which is a web accessibility best practice. It overrides Formidable's default templates in the form builder's "Customize HTML" feature.

### Key Features

- ✅ **Semantic HTML**: Adds `<fieldset>` and `<legend>` wrappers for radio/checkbox groups
- ♿ **Accessibility**: Improves form accessibility for screen readers and assistive technologies
- 🎨 **Flexible Styling**: Control legend visibility (visible or screen-reader only)
- 🔧 **ARIA Control**: Option to strip redundant ARIA roles when using native fieldsets
- ⚙️ **Filterable**: Multiple hooks for developer customization
- 🌍 **Translation Ready**: Fully internationalized

## Requirements

- WordPress 5.0 or higher
- Formidable Forms (Free or Pro)
- PHP 7.0 or higher

## Installation

### Via WordPress Admin

1. Download the plugin ZIP file
2. Go to **Plugins > Add New > Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Activate the plugin
5. Go to **Settings > FF Fieldset** to configure

### Manual Installation

1. Upload the `ff-fieldset` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings at **Settings > FF Fieldset**

## Usage

### Basic Setup

1. Activate the plugin
2. Configure your preferences in **Settings > FF Fieldset**:
   - **Use template override**: Enable/disable the feature
   - **Visible legends**: Show legends visually or hide them (screen-reader only)
   - **Strip ARIA roles**: Remove redundant `role="group"` and `role="radiogroup"` attributes

3. Create new radio or checkbox fields in Formidable Forms
   - The new default template will automatically include fieldset/legend markup
   - View in "Customize HTML" to see the updated structure

### Important Notes

- ⚠️ **This plugin affects the builder's default templates only**
- It does **not** retrofit existing fields' frontend HTML
- To apply changes to existing fields, you'll need to:
  - Resave the field settings, OR
  - Manually update the "Customize HTML" for each field

## Template Structure

The plugin generates the following HTML structure:

```html
<div id="frm_field_[id]_container" class="frm_form_field form-field [required_class][error_class]">
    <fieldset class="FF_radio_checkbox_fieldset FF_radio_checkbox_fieldset--radio">
        <legend id="field_[key]_label" class="frm_primary_label">
            Field Label <span class="frm_required">[required_label]</span>
        </legend>
        <div class="frm_opt_container">
            [input]
        </div>
        <div class="frm_description" id="frm_desc_field_[key]">[description]</div>
        <div class="frm_error" id="frm_error_field_[key]">[error]</div>
    </fieldset>
</div>
```

## Developer Hooks

### Filters

```php
// Hide legends visually (screen-reader only)
add_filter( 'FF_fieldset/legend_visible', '__return_false' );

// Keep ARIA roles (role="group" or role="radiogroup")
add_filter( 'FF_fieldset/keep_group_roles', '__return_true' );

// Remove required label indicator
add_filter( 'FF_fieldset/include_required_label', '__return_false' );

// Disable template override entirely
add_filter( 'FF_fieldset/use_template_override', '__return_false' );
```
## Accessibility Benefits

### Why Fieldsets Matter

Using `<fieldset>` and `<legend>` for radio and checkbox groups:

- ✅ Groups related form controls semantically
- ✅ Announces the group context to screen reader users
- ✅ Improves navigation with assistive technologies
- ✅ Meets WCAG 2.1 accessibility guidelines
- ✅ Provides better form structure for all users

### ARIA Considerations

When using native `<fieldset>` elements, ARIA roles like `role="group"` or `role="radiogroup"` are often redundant. This plugin allows you to strip these roles to avoid duplicate announcements for screen reader users.

## FAQ

### Does this work with existing forms?

The plugin updates the **default templates** used when creating new fields. Existing fields will retain their original HTML unless you:
- Resave the field settings, OR
- Manually update the field's "Customize HTML"

### Will this affect all my forms?

Yes, the `frm_custom_html` filter affects the default templates for **all forms** on your site. Use the filters provided if you need to disable it for specific cases.

### Can I customize the output?

Yes! Use the provided filters to customize legend visibility, ARIA roles, and more. You can also modify the CSS classes with custom styles.

### Does this work with Formidable Forms Lite?

Yes, this plugin works with both the free and Pro versions of Formidable Forms.

## Changelog

### 1.2.4
- Current stable release
- Semantic fieldset/legend wrapper for radio and checkbox groups
- Settings page for global configuration
- Multiple developer filters
- Translation ready

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This plugin is licensed under the GPL-2.0+ License.

## Credits

**Author**: Adem Cifcioglu

---

**Note**: This plugin is not affiliated with or endorsed by Formidable Forms or Strategy11.