<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class FF2_Fieldset_End extends FrmFieldType {

    protected $type = 'ff-fieldset-end';
    protected $has_input = false;
    protected $has_for_label = false;

    public function default_html() {
        return '[input]';
    }

    protected function field_settings_for_type() {
        $settings = parent::field_settings_for_type();
        // Minimal settings; keep description for notes if desired.
        $settings['default']        = false;
        $settings['clear_on_focus'] = false;
        $settings['size']           = false;
        $settings['max']            = false;
        $settings['label']          = false;
        $settings['description']    = true;
        $settings['required']       = false;
        $settings['unique']         = false;
        $settings['read_only']      = false;
        $settings['invalid']        = false;
        if ( class_exists( 'FrmProFieldsHelper' ) ) {
            FrmProFieldsHelper::fill_default_field_display( $settings );
        }
        return $settings;
    }

    protected function include_form_builder_file() {
        return FF2_FIELDSET_DIR . 'views/builder-field-end.php';
    }

    public function validate( $args ) {
        return array();
    }

    public function front_field_input( $args, $shortcode_atts ) {
        return '</fieldset>';
    }
}

?>
