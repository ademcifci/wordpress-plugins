<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FrmDuetDateField extends FrmFieldType {

    protected $type = 'duetdate';

    protected $array_allowed = false;

    protected function field_settings_for_type() {
        return array(
            'size'           => true,
            'clear_on_focus' => true,
            'invalid'        => true,
            'read_only'      => true,
        );
    }

    protected function extra_field_opts() {
        return array(
            'placeholder' => 'YYYY-MM-DD',
        );
    }

    /**
     * Ensure proper builder wrapper so field order/placement saves correctly.
     * Mirrors pattern used by core/pro fields.
     */
    protected function builder_text_field( $name = '' ) {
        if ( is_callable( 'FrmProFieldsHelper::builder_page_prepend' ) ) {
            $html  = FrmProFieldsHelper::builder_page_prepend( $this->field );
            $field = parent::builder_text_field( $name );
            return str_replace( '[input]', $field, $html );
        }
        return parent::builder_text_field( $name );
    }

    protected function include_form_builder_file() {
        // Return empty so the builder uses builder_text_field(), which
        // wraps the input with the standard Formidable builder markup.
        // This ensures the field order is tracked and preserved on save.
        return '';
    }

    /**
     * Front-end input HTML for Duet Date field.
     */
    public function front_field_input( $args, $shortcode_atts ) {
        $input_html = $this->get_field_input_html_hook( $this->field );
        $this->add_aria_description( $args, $input_html );
        $this->add_extra_html_atts( $args, $input_html );

        $attributes = array(
            'type'        => 'text',
            'id'          => $args['html_id'],
            'name'        => $args['field_name'],
            'value'       => esc_attr( $this->prepare_esc_value() ),
            'class'       => trim( 'frm_duet_date' ),
            'placeholder' => FrmField::get_option( $this->field, 'placeholder' ),
            'data-field-id' => absint( $this->field_id ),
        );

        // If this is an End field, set data-range-start-field-id for JS cross-constraints.
        if ( ! empty( $this->field['duet_is_range_end'] ) && ! empty( $this->field['duet_range_start_field'] ) ) {
            $attributes['data-range-start-field-id'] = absint( $this->field['duet_range_start_field'] );
        }

        // Output data attributes for builder options (min/max, locale, disabled dates/days).
        if ( ! empty( $this->field['duet_min'] ) ) {
            $attributes['data-duet-min'] = esc_attr( $this->field['duet_min'] );
        }
        if ( ! empty( $this->field['duet_max'] ) ) {
            $attributes['data-duet-max'] = esc_attr( $this->field['duet_max'] );
        }
        if ( ! empty( $this->field['duet_locale'] ) ) {
            $attributes['data-duet-locale'] = esc_attr( $this->field['duet_locale'] );
        }
        if ( ! empty( $this->field['duet_disabled_dates'] ) && is_array( $this->field['duet_disabled_dates'] ) ) {
            $attributes['data-duet-disabled-dates'] = esc_attr( wp_json_encode( array_values( $this->field['duet_disabled_dates'] ) ) );
        }
        if ( ! empty( $this->field['duet_days_disabled'] ) && is_array( $this->field['duet_days_disabled'] ) ) {
            $attributes['data-duet-days-disabled'] = esc_attr( implode( ',', array_map( 'absint' , $this->field['duet_days_disabled'] ) ) );
        }

        // Optional: Custom display/input format(s), e.g. "DD-MM-YYYY" or "DD-MM-YYYY|YYYY".
        if ( ! empty( $this->field['duet_format'] ) && is_string( $this->field['duet_format'] ) ) {
            $attributes['data-duet-format'] = esc_attr( $this->field['duet_format'] );
        }

        return '<input' . FrmAppHelper::array_to_html_params( $attributes ) . $input_html . ' />';
    }

    /**
     * Basic validation for ISO dates (YYYY-MM-DD).
     */
    public function validate( $args ) {
        $errors = array();
        $value  = (string) $args['value'];
        if ( $value === '' ) {
            return $errors;
        }
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
            $errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $this->field, 'invalid' );
            return $errors;
        }
        $parts = explode( '-', $value );
        if ( count( $parts ) !== 3 || ! checkdate( (int) $parts[1], (int) $parts[2], (int) $parts[0] ) ) {
            $errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $this->field, 'invalid' );
        }
        return $errors;
    }
}
