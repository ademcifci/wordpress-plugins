<?php
/**
 * Plugin Name: Formidable Global A11y Enhancements
 * Description: Accessibility improvements for Formidable Forms outside of file uploads: focus management for global messages, cleanup for "Other" text inputs, and multi‑page focus. Admin settings to toggle features.
 * Version: 1.1.0
 * Author: Adem Cifcioglu
 * License: GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Formidable_Global_A11y_Enhancements {
    const OPTION_KEY = 'ff_globa11y';

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_menu', [ $this, 'register_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function enqueue_assets() {
        $ver = '1.1.0';

        wp_enqueue_script(
            'formidable-global-a11y-enhancements',
            plugins_url( 'assets/formidable-global-a11y-enhancements.js', __FILE__ ),
            [ 'jquery' ],
            $ver,
            true
        );

        $settings = $this->get_settings();
        // Detect Accessible Errors. We no longer disable message focus entirely;
        // JS will defer ONLY error focusing to Accessible Errors, while still
        // handling success-message focus when enabled.
        $accessible_errors_active = class_exists( 'FF_Accessible_Error_Summary' );
        wp_localize_script(
            'formidable-global-a11y-enhancements',
            'ff_globa11y',
            [
                'other_fields_fix'      => (bool) $settings['other_fields_fix'],
                'global_message_focus'  => (bool) $settings['global_message_focus'],
                'success_message_focus' => (bool) $settings['success_message_focus'],
                'multi_page_focus'      => (bool) $settings['multi_page_focus'],
                'has_accessible_errors' => (bool) $accessible_errors_active,
            ]
        );
    }

    public function register_settings_page() {
        add_options_page(
            'Formidable A11y Enhancements',
            'Formidable A11y',
            'manage_options',
            'formidable-global-a11y-enhancements',
            [ $this, 'render_settings_page' ]
        );
    }

    public function register_settings() {
        register_setting( self::OPTION_KEY, self::OPTION_KEY, [ $this, 'sanitize_settings' ] );

        add_settings_section(
            'fga11y_main',
            'Accessibility Enhancements',
            function () {
                echo '<p>Toggle accessibility features applied to Formidable Forms.</p>';
            },
            self::OPTION_KEY
        );

        add_settings_field(
            'other_fields_fix',
            'Other fields cleanup',
            function () {
                $settings = $this->get_settings();
                $checked  = ! empty( $settings['other_fields_fix'] ) ? 'checked' : '';
                echo '<label><input type="checkbox" name="' . esc_attr( self::OPTION_KEY ) . '[other_fields_fix]" value="1" ' . $checked . '> Enable</label>';
                echo '<p class="description">Hide duplicate screen-reader-only labels for "Other" text inputs, move that text into aria-label, and remove alert roles from inline error elements.</p>';
            },
            self::OPTION_KEY,
            'fga11y_main'
        );

        add_settings_field(
            'global_message_focus',
            'Global error message focus',
            function () {
                $settings   = $this->get_settings();
                $checked    = ! empty( $settings['global_message_focus'] ) ? 'checked' : '';
                $ae_active  = class_exists( 'FF_Accessible_Error_Summary' );
                $disabled   = $ae_active ? ' disabled="disabled" aria-disabled="true" ' : '';
                echo '<label><input type="checkbox" ' . $disabled . ' name="' . esc_attr( self::OPTION_KEY ) . '[global_message_focus]" value="1" ' . $checked . '> Enable</label>';
                echo '<p class="description">Focus the error summary after submission when Accessible Error Summary is not active.' . ( $ae_active ? ' <strong>Note:</strong> Disabled because Accessible Error Summary is active and will handle error focusing.' : '' ) . '</p>';
            },
            self::OPTION_KEY,
            'fga11y_main'
        );

        add_settings_field(
            'success_message_focus',
            'Success message focus',
            function () {
                $settings = $this->get_settings();
                $checked  = ! empty( $settings['success_message_focus'] ) ? 'checked' : '';
                echo '<label><input type="checkbox" name="' . esc_attr( self::OPTION_KEY ) . '[success_message_focus]" value="1" ' . $checked . '> Enable</label>';
                echo '<p class="description">After a successful submission, move focus to the success confirmation message.</p>';
            },
            self::OPTION_KEY,
            'fga11y_main'
        );

        add_settings_field(
            'multi_page_focus',
            'Multi‑page H1 focus',
            function () {
                $settings = $this->get_settings();
                $checked  = ! empty( $settings['multi_page_focus'] ) ? 'checked' : '';
                echo '<label><input type="checkbox" name="' . esc_attr( self::OPTION_KEY ) . '[multi_page_focus]" value="1" ' . $checked . '> Enable</label>';
                echo '<p class="description">On Next/Previous in multi-page forms, move focus to the first visible H1 on the page. If a global error appears, focus the error summary instead.</p>';
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
        echo '<h1>Formidable Global A11y Enhancements</h1>';
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
            'other_fields_fix'     => empty( $in['other_fields_fix'] ) ? 0 : 1,
            'global_message_focus' => array_key_exists( 'global_message_focus', $in ) ? ( empty( $in['global_message_focus'] ) ? 0 : 1 ) : ( isset( $current['global_message_focus'] ) ? (int) $current['global_message_focus'] : (int) $defaults['global_message_focus'] ),
            'success_message_focus' => array_key_exists( 'success_message_focus', $in ) ? ( empty( $in['success_message_focus'] ) ? 0 : 1 ) : ( isset( $current['success_message_focus'] ) ? (int) $current['success_message_focus'] : (int) $defaults['success_message_focus'] ),
            'multi_page_focus'     => array_key_exists( 'multi_page_focus', $in ) ? ( empty( $in['multi_page_focus'] ) ? 0 : 1 ) : ( isset( $current['multi_page_focus'] ) ? (int) $current['multi_page_focus'] : (int) $defaults['multi_page_focus'] ),
        ];

        // Ensure all keys exist, fall back to defaults if somehow missing
        $out = wp_parse_args( $out, $defaults );
        return $out;
    }

    private function defaults() {
        return [
            // Requirements: global message focus OFF by default
            'global_message_focus' => 0,
            // Other fields fix ON by default
            'other_fields_fix'     => 1,
            // Success message focus ON by default
            'success_message_focus'=> 1,
            // Multi‑page focus ON by default
            'multi_page_focus'     => 1,
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

// Set defaults on activation
register_activation_hook( __FILE__, function () {
    $key      = Formidable_Global_A11y_Enhancements::OPTION_KEY;
    $existing = get_option( $key );
    if ( false === $existing ) {
        add_option( $key, [
            'global_message_focus' => 0,
            'other_fields_fix'     => 1,
            'multi_page_focus'     => 1,
        ] );
    }
} );
