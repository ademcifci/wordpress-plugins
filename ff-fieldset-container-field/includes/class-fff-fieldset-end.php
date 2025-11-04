<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class FFF_Fieldset_End extends FrmFieldType {

    protected $type = 'ff-fieldset-end';
    protected $has_input = false;
    protected $has_for_label = false;

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
        return FFF_FIELDSET_DIR . 'views/builder-field-end.php';
    }

    public function validate( $args ) {
        return array(); // non-input field
    }

    public function front_field_input( $args, $shortcode_atts ) {
        return '</fieldset>';
    }
}

