<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class FFF_Fieldset_Start extends FrmFieldType {

    protected $type = 'ff-fieldset-start';
    protected $has_input = false;
    protected $has_for_label = false;

    public function default_html() {
        // No wrapper/label from Formidable; we output final HTML via front_field_input.
        return '[input]';
    }

    protected function field_settings_for_type() {
        $settings = parent::field_settings_for_type();
        // Keep just name (legend) and description; hide typical input settings.
        $settings['default']        = false;
        $settings['clear_on_focus'] = false;
        $settings['size']           = false;
        $settings['max']            = false;
        $settings['label']          = true;  // builder label used as legend text
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

    protected function extra_field_opts() {
        return array(
            'legend_visible' => 1,
            // '' means no heading tag; otherwise one of h2-h6
            'legend_heading' => '',
        );
    }

    protected function include_form_builder_file() {
        // Return empty so builder_text_field() can inject our preview
        // into Formidable's standard builder wrapper for reliable ordering.
        return '';
    }

    public function show_primary_options( $args ) {
        $field = $args['field'];
        include FFF_FIELDSET_DIR . 'views/builder-settings-start.php';
        parent::show_primary_options( $args );
    }

    /**
     * Render the builder preview inside the standard wrapper so drag/drop
     * order is tracked consistently (parity with Duet field approach).
     */
    protected function builder_text_field( $name = '' ) {
        $preview_html = '';
        try {
            ob_start();
            $field = is_array( $this->field ) ? $this->field : (array) $this->field;
            include FFF_FIELDSET_DIR . 'views/builder-field-start.php';
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

    public function validate( $args ) {
        return array(); // non-input field
    }

    public function front_field_input( $args, $shortcode_atts ) {
        // Resolve field data (works for array or object formats)
        $field      = isset( $args['field'] ) ? $args['field'] : $this->field;
        $field_id   = is_object( $field ) ? $field->id : ( isset( $field['id'] ) ? $field['id'] : 0 );
        $field_opts = is_object( $field ) ? ( isset( $field->field_options ) ? $field->field_options : array() ) : ( isset( $field['field_options'] ) ? $field['field_options'] : $field );

        $legend_visible = 1;
        if ( isset( $field_opts['legend_visible'] ) ) {
            $legend_visible = (int) $field_opts['legend_visible'];
        } elseif ( function_exists( 'FrmField' ) ) {
            // Fallback safety, though not expected here
            $legend_visible = (int) FrmField::get_option( $field, 'legend_visible' );
        }

        $legend_text = '';
        if ( is_object( $field ) ) {
            $legend_text = isset( $field->name ) ? $field->name : '';
        } else {
            $legend_text = isset( $field['name'] ) ? $field['name'] : '';
        }

        $description = '';
        if ( is_object( $field ) ) {
            $description = isset( $field->description ) ? $field->description : '';
        } else {
            $description = isset( $field['description'] ) ? $field['description'] : '';
        }

        $legend_class = $legend_visible ? ' class="frm_fieldset_field_legend"' : ' class="frm_screen_reader frm_fieldset_field_legend"';

        // Optional heading level inside legend (h2-h6) or none
        $legend_heading = '';
        if ( isset( $field_opts['legend_heading'] ) ) {
            $legend_heading = is_string( $field_opts['legend_heading'] ) ? strtolower( $field_opts['legend_heading'] ) : '';
        }
        $allowed_headings = array( 'h2', 'h3', 'h4', 'h5', 'h6' );
        if ( ! in_array( $legend_heading, $allowed_headings, true ) ) {
            $legend_heading = '';
        }

        $id_attr = $field_id ? ' id="frm_field_' . esc_attr( $field_id ) . '_container"' : '';
        $classes = 'frm_fieldset_container frm_fieldset_start frm_form_field form-field';

        // Append user-provided layout classes if present.
        $user_classes = '';
        $possible_keys = array( 'classes', 'custom_class', 'css_class', 'css' );
        foreach ( $possible_keys as $k ) {
            if ( isset( $field_opts[ $k ] ) && is_string( $field_opts[ $k ] ) && $field_opts[ $k ] !== '' ) {
                $user_classes .= ' ' . $field_opts[ $k ];
            }
        }
        if ( $user_classes ) {
            $parts = preg_split( '/\s+/', $user_classes );
            $san   = array();
            foreach ( $parts as $p ) {
                $p = trim( $p );
                if ( $p === '' ) { continue; }
                // Basic sanitization for class tokens
                $p = preg_replace( '/[^A-Za-z0-9_-]/', '', $p );
                if ( $p !== '' ) { $san[] = $p; }
            }
            if ( $san ) {
                $classes .= ' ' . implode( ' ', array_unique( $san ) );
            }
        }
        // Allow last-minute filter for class list
        $classes = apply_filters( 'fff_fieldset_start_classes', $classes, $field );

        // Build legend HTML
        $legend_html = '';
        if ( $legend_text !== '' ) {
            if ( $legend_heading ) {
                $legend_html = '<legend' . $legend_class . '><' . $legend_heading . '>' . esc_html( $legend_text ) . '</' . $legend_heading . '></legend>';
            } else {
                $legend_html = '<legend' . $legend_class . '>' . esc_html( $legend_text ) . '</legend>';
            }
        }

        $desc_html = '';
        if ( $description !== '' ) {
            $desc_html = '<div class="frm_description frm_fieldset_description">' . wp_kses_post( $description ) . '</div>';
        }

        $html  = '<fieldset' . $id_attr . ' class="' . esc_attr( $classes ) . '">';
        $html .= $legend_html . $desc_html;
        return $html;
    }
}

