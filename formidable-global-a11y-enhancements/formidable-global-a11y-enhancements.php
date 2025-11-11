<?php
/**
 * Plugin Name: Formidable Global A11y Enhancements
 * Description: Accessibility improvements for Formidable Forms outside of file uploads: focus management for global messages, cleanup for "Other" text inputs, and multi-page focus. Admin settings to toggle features.
 * Version: 1.2.4
 * Author: Adem Cifcioglu
 * License: GPL-2.0+
 * Text Domain: formidable-global-a11y-enhancements
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Formidable_Global_A11y_Enhancements {
    const OPTION_KEY = 'ff_globa11y';

    public function __construct() {
        add_action( 'init', [ $this, 'load_textdomain' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_menu', [ $this, 'register_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_filter( 'frm_replace_shortcodes', [ $this, 'inject_describedby_for_choice_inputs' ], 20, 3 );
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'formidable-global-a11y-enhancements', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    public function enqueue_assets() {
        $ver = '1.2.4';

        wp_enqueue_script(
            'formidable-global-a11y-enhancements',
            plugins_url( 'assets/formidable-global-a11y-enhancements.js', __FILE__ ),
            [ 'jquery' ],
            $ver,
            true
        );

        $settings = $this->get_settings();
        $accessible_errors_active = class_exists( 'FF_Accessible_Error_Summary' );
        wp_localize_script(
            'formidable-global-a11y-enhancements',
            'ff_globa11y',
            [
                'other_fields_fix'       => (bool) $settings['other_fields_fix'],
                'global_message_focus'   => (bool) $settings['global_message_focus'],
                'success_message_focus'  => (bool) $settings['success_message_focus'],
                'multi_page_focus'       => (bool) $settings['multi_page_focus'],
                'multi_page_focus_level' => isset( $settings['multi_page_focus_level'] ) ? (int) $settings['multi_page_focus_level'] : 1,
                'has_accessible_errors'  => (bool) $accessible_errors_active,
                // If a theme/plugin disables alert roles for field errors via the filter,
                // mirror that intent for choice fields on the front-end.
                'remove_choice_error_alert_role' => (bool) ( ! apply_filters( 'frm_include_alert_role_on_field_errors', true ) ),
                // New, generic flag (keep the old key for back-compat within this plugin).
                'remove_alert_role_on_field_errors' => (bool) ( ! apply_filters( 'frm_include_alert_role_on_field_errors', true ) ),
            ]
        );
    }

    public function register_settings_page() {
        add_options_page(
            __( 'Formidable A11y Enhancements', 'formidable-global-a11y-enhancements' ),
            __( 'FF A11y Enhancements', 'formidable-global-a11y-enhancements' ),
            'manage_options',
            'formidable-global-a11y-enhancements',
            [ $this, 'render_settings_page' ]
        );
    }

    public function register_settings() {
        register_setting( self::OPTION_KEY, self::OPTION_KEY, [ $this, 'sanitize_settings' ] );

        add_settings_section(
            'fga11y_main',
            __( 'Accessibility Enhancements', 'formidable-global-a11y-enhancements' ),
            function () {
                echo '<p>' . esc_html__( 'Toggle accessibility features applied to Formidable Forms.', 'formidable-global-a11y-enhancements' ) . '</p>';
            },
            self::OPTION_KEY
        );

        // General cleanup.
        add_settings_field(
            'other_fields_fix',
            __( 'General cleanup', 'formidable-global-a11y-enhancements' ),
            function () {
                $settings = $this->get_settings();
                $checked  = ! empty( $settings['other_fields_fix'] ) ? 'checked' : '';
                echo '<label><input type="checkbox" name="' . esc_attr( self::OPTION_KEY ) . '[other_fields_fix]" value="1" ' . $checked . '> ' . esc_html__( 'Enable', 'formidable-global-a11y-enhancements' ) . '</label>';
                echo "<p class=\"description\">Hide duplicate screen-reader-only labels for \"Other\" text inputs, move that text into aria-label, and remove alert roles from inline error elements that aren't removed by <code>add_filter( 'frm_include_alert_role_on_field_errors', '__return_false' );</code>. These fixes probably aren't needed any more due to changes in Formidable core, but will do no harm </p>";
            },
            self::OPTION_KEY,
            'fga11y_main'
        );

        // Global error message focus.
        add_settings_field(
            'global_message_focus',
            __( 'Global error message focus', 'formidable-global-a11y-enhancements' ),
            function () {
                $settings  = $this->get_settings();
                $checked   = ! empty( $settings['global_message_focus'] ) ? 'checked' : '';
                $ae_active = class_exists( 'FF_Accessible_Error_Summary' );
                $disabled  = $ae_active ? ' disabled="disabled" aria-disabled="true" ' : '';
                echo '<label><input type="checkbox" ' . $disabled . ' name="' . esc_attr( self::OPTION_KEY ) . '[global_message_focus]" value="1" ' . $checked . '> ' . esc_html__( 'Enable', 'formidable-global-a11y-enhancements' ) . '</label>';
                $desc = __( 'Focus the error summary after submission when Accessible Error Summary is not active.', 'formidable-global-a11y-enhancements' );
                if ( $ae_active ) {
                    $desc .= ' ' . sprintf('<strong>%s</strong> %s', esc_html__( 'Note:', 'formidable-global-a11y-enhancements' ), esc_html__( 'Disabled because Accessible Error Summary is active and will handle error focusing.', 'formidable-global-a11y-enhancements' )
);
                }
                echo '<p class="description">' . wp_kses( $desc, [ 'strong' => [] ] ) . '</p>';
            },
            self::OPTION_KEY,
            'fga11y_main'
        );

        // Success message focus.
        add_settings_field(
            'success_message_focus',
            __( 'Success message focus', 'formidable-global-a11y-enhancements' ),
            function () {
                $settings = $this->get_settings();
                $checked  = ! empty( $settings['success_message_focus'] ) ? 'checked' : '';
                echo '<label><input type="checkbox" name="' . esc_attr( self::OPTION_KEY ) . '[success_message_focus]" value="1" ' . $checked . '> ' . esc_html__( 'Enable', 'formidable-global-a11y-enhancements' ) . '</label>';
            },
            self::OPTION_KEY,
            'fga11y_main'
        );

        // Multi-page heading level.
        add_settings_field(
            'multi_page_focus_level',
            __( 'Multi-page heading level', 'formidable-global-a11y-enhancements' ),
            function () {
                $settings = $this->get_settings();
                $current  = isset( $settings['multi_page_focus_level'] ) ? (int) $settings['multi_page_focus_level'] : 1;
                $name     = esc_attr( self::OPTION_KEY ) . '[multi_page_focus_level]';
                echo '<select name="' . $name . '">';
                for ( $i = 1; $i <= 6; $i++ ) {
                    /* translators: %d is the heading level (1-6) */
                    echo '<option value="' . $i . '" ' . selected( $current, $i, false ) . '>' . sprintf( esc_html__( 'H%d', 'formidable-global-a11y-enhancements' ), $i ) . '</option>';
                }
                echo '</select>';
            },
            self::OPTION_KEY,
            'fga11y_main'
        );

        // Multi-page focus.
        add_settings_field(
            'multi_page_focus',
            __( 'Multi-page focus', 'formidable-global-a11y-enhancements' ),
            function () {
                $settings = $this->get_settings();
                $checked  = ! empty( $settings['multi_page_focus'] ) ? 'checked' : '';
                echo '<label><input type="checkbox" name="' . esc_attr( self::OPTION_KEY ) . '[multi_page_focus]" value="1" ' . $checked . '> ' . esc_html__( 'Enable', 'formidable-global-a11y-enhancements' ) . '</label>';
            },
            self::OPTION_KEY,
            'fga11y_main'
        );

        // Add aria-describedby to radio/checkbox inputs.
        add_settings_field(
            'choice_describedby',
            __( 'Add aria-describedby to radio/checkbox controls', 'formidable-global-a11y-enhancements' ),
            function () {
                $settings = $this->get_settings();
                $checked  = ! empty( $settings['choice_describedby'] ) ? 'checked' : '';
                echo '<label><input type="checkbox" name="' . esc_attr( self::OPTION_KEY ) . '[choice_describedby]" value="1" ' . $checked . '> ' . esc_html__( 'Enable', 'formidable-global-a11y-enhancements' ) . '</label>';
                echo '<p class="description">' . esc_html__( 'Adds aria-describedby to each radio/checkbox input, referencing the field description and inline error.', 'formidable-global-a11y-enhancements' ) . '</p>';
            },
            self::OPTION_KEY,
            'fga11y_main'
        );
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Formidable Global A11y Enhancements', 'formidable-global-a11y-enhancements' ) . '</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields( self::OPTION_KEY );
        do_settings_sections( self::OPTION_KEY );
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    public function sanitize_settings( $input ) {
        $defaults = $this->defaults();
        $current  = get_option( self::OPTION_KEY );
        if ( ! is_array( $current ) ) {
            $current = [];
        }
        $in = is_array( $input ) ? $input : [];

        $out = [
            'other_fields_fix'       => empty( $in['other_fields_fix'] ) ? 0 : 1,
            'global_message_focus'   => empty( $in['global_message_focus'] ) ? 0 : 1,
            'success_message_focus'  => empty( $in['success_message_focus'] ) ? 0 : 1,
            'multi_page_focus'       => empty( $in['multi_page_focus'] ) ? 0 : 1,
            'choice_describedby'     => empty( $in['choice_describedby'] ) ? 0 : 1,
            'multi_page_focus_level' => ( function() use ( $in, $current, $defaults ) {
                if ( array_key_exists( 'multi_page_focus_level', $in ) ) {
                    $val = (int) $in['multi_page_focus_level'];
                    if ( $val < 1 || $val > 6 ) { $val = (int) $defaults['multi_page_focus_level']; }
                    return $val;
                }
                return isset( $current['multi_page_focus_level'] ) ? (int) $current['multi_page_focus_level'] : (int) $defaults['multi_page_focus_level'];
            } )(),
        ];

        return wp_parse_args( $out, $defaults );
    }

    // Only input-level injection.
    public function inject_describedby_for_choice_inputs( $html, $field, $atts ) {
        $settings = $this->get_settings();
        if ( empty( $settings['choice_describedby'] ) ) {
            return $html;
        }

        $type      = is_array( $field ) ? ( $field['type'] ?? '' ) : ( isset( $field->type ) ? $field->type : '' );
        $field_id  = is_array( $field ) ? ( $field['id'] ?? 0 ) : ( isset( $field->id ) ? $field->id : 0 );
        $field_key = is_array( $field ) ? ( $field['field_key'] ?? '' ) : ( isset( $field->field_key ) ? $field->field_key : '' );
        $desc_text = is_array( $field ) ? ( $field['description'] ?? '' ) : ( isset( $field->description ) ? $field->description : '' );

        if ( ! in_array( $type, [ 'radio', 'checkbox' ], true ) || ! $field_id || ! $field_key ) {
            return $html;
        }

        $errors    = isset( $atts['errors'] ) && is_array( $atts['errors'] ) ? $atts['errors'] : [];
        $has_error = isset( $errors[ 'field' . $field_id ] );

        $html_id = apply_filters( 'frm_field_get_html_id', 'field_' . $field_key, $field );
        $desc_id = 'frm_desc_' . $html_id;
        $err_id  = 'frm_error_' . $html_id;

        $should_add_desc = ( $desc_text !== '' );
        if ( ! $has_error && ! $should_add_desc ) {
            return $html;
        }

        $pattern = '/<input\b([^>]*?)\s*(?:\/?)>/i';
        $html    = preg_replace_callback(
            $pattern,
            function ( $m ) use ( $should_add_desc, $desc_id, $has_error, $err_id ) {
                $inner = $m[1];
                if ( ! preg_match( '/\btype=(\"|\')?(radio|checkbox)\1?/i', $inner ) ) {
                    return $m[0];
                }

                $existing = [];
                if ( preg_match( '/aria-describedby\s*=\s*(["\'])(.*?)\1/i', $inner, $adb ) ) {
                    $existing = preg_split( '/\s+/', trim( $adb[2] ) );
                }

                $wanted = [];
                if ( $has_error ) { $wanted[] = $err_id; }
                if ( $should_add_desc ) { $wanted[] = $desc_id; }
                if ( empty( $wanted ) ) { return $m[0]; }

                $final = array_values( array_unique( array_merge( $existing, $wanted ) ) );
                $attr  = 'aria-describedby="' . esc_attr( implode( ' ', $final ) ) . '"';

                if ( preg_match( '/aria-describedby\s*=\s*(["\'])(.*?)\1/i', $inner ) ) {
                    $inner = preg_replace( '/aria-describedby\s*=\s*(["\'])(.*?)\1/i', $attr, $inner );
                } else {
                    $inner = rtrim( $inner ) . ' ' . $attr . ' ';
                }

                $selfclose = ( substr( trim( $m[0] ), -2 ) === '/>' ) ? '/>' : '>';
                return '<input ' . $inner . $selfclose;
            },
            $html
        );

        return $html;
    }

    private function defaults() {
        return [
            'global_message_focus'   => 0,
            'other_fields_fix'       => 1,
            'success_message_focus'  => 1,
            'multi_page_focus'       => 1,
            'multi_page_focus_level' => 1,
            'choice_describedby'     => 1,
        ];
    }

    private function get_settings() {
        $saved = get_option( self::OPTION_KEY );
        if ( ! is_array( $saved ) ) {
            $saved = [];
        }
        return wp_parse_args( $saved, $this->defaults() );
    }
}

new Formidable_Global_A11y_Enhancements();

register_activation_hook( __FILE__, function () {
    $key      = Formidable_Global_A11y_Enhancements::OPTION_KEY;
    $existing = get_option( $key );
    if ( false === $existing ) {
        add_option( $key, [
            'global_message_focus'   => 0,
            'other_fields_fix'       => 1,
            'success_message_focus'  => 1,
            'multi_page_focus'       => 1,
            'multi_page_focus_level' => 1,
            'choice_describedby'     => 1,
        ] );
    }
} );
