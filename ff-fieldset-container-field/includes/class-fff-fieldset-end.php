<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class FFF_Fieldset_End extends FrmFieldType {

    protected $type = 'ff-fieldset-end';
    protected $has_input = false;
    protected $has_for_label = false;

    public function default_html() {
        // Suppress any default label/wrapper; only render our input.
        return '[input]';
    }

    protected function field_settings_for_type() {
        $settings = parent::field_settings_for_type();
        // Hide most settings - this is just a closing tag
        $settings['default']        = false;
        $settings['clear_on_focus'] = false;
        $settings['size']           = false;
        $settings['max']            = false;
        $settings['label']          = false;
        $settings['description']    = true; // Keep description for notes
        $settings['required']       = false;
        $settings['unique']         = false;
        $settings['read_only']      = false;
        $settings['invalid']        = false;
        return $settings;
    }

    protected function include_form_builder_file() {
        // Return empty so builder_text_field() injects preview into the
        // standard builder wrapper for correct ordering on save.
        return '';
    }

    public function validate( $args ) {
        return array(); // non-input field
    }

    public function front_field_input( $args, $shortcode_atts ) {
        return '</fieldset>';
    }

    /**
     * Render the builder preview inside the standard wrapper so drag/drop
     * order is tracked consistently (same pattern as Duet field).
     */
    protected function builder_text_field( $name = '' ) {
        $preview_html = '';
        try {
            ob_start();
            $field = is_array( $this->field ) ? $this->field : (array) $this->field;
            include FFF_FIELDSET_DIR . 'views/builder-field-end.php';
            $preview_html = (string) ob_get_clean();
        } catch ( \Throwable $e ) {
            if ( function_exists( 'ob_get_level' ) && ob_get_level() ) { ob_end_clean(); }
            $preview_html = '';
        }

        if ( is_callable( 'FrmProFieldsHelper::builder_page_prepend' ) ) {
            $html = \FrmProFieldsHelper::builder_page_prepend( $this->field );
            if ( strpos( $html, '[input]' ) !== false ) {
                return str_replace( '[input]', $preview_html, $html );
            }
        }
        return parent::builder_text_field( $name );
    }
}

