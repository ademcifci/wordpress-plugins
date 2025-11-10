<?php
/*
Plugin Name: Formidable Duet Date Picker
Description: Adds Formidable Forms date field using the Duet Date Picker (https://github.com/duetds/date-picker).
Author: Adem Cifcioglu
Version: 0.1.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Formidable_Duet_Date_Picker_Plugin {
    const HANDLE_INIT = 'frm-duet-init';
    const HANDLE_STYLE = 'frm-duet-style';
    const HANDLE_DUET_ESM = 'duet-date-picker-esm';
    const HANDLE_DUET_NOMODULE = 'duet-date-picker-nomodule';

    public static function init() {
        if ( ! class_exists( 'FrmFieldType' ) ) {
            // Formidable not active yet.
            return;
        }
        // Load field class on demand from map_duetdate_field_class to avoid early load errors.
        // Enqueue on frontend only when a form has at least one Duet Date field.
        add_action( 'frm_enqueue_form_scripts', [ __CLASS__, 'maybe_enqueue_assets_for_form' ], 5 );
        // Enqueue in admin for builder and entries pages.
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'maybe_enqueue_in_admin' ], 50 );

        // Global settings removed; the field loads assets only when present on a form.

        // Ensure proper attributes on script tags regardless of WP version/minifiers.
        add_filter( 'script_loader_tag', [ __CLASS__, 'script_loader_tag' ], 10, 3 );

        // Per-field settings for Duet Date field (range linking).
        add_action( 'frm_duetdate_field_options_form', [ __CLASS__, 'render_duetdate_field_options' ], 10, 3 );
        add_filter( 'frm_clean_duetdate_field_options_before_update', [ __CLASS__, 'save_duetdate_field_options' ], 10, 2 );

        // Register Duet Date as a new field type in the builder without core edits.
        add_filter( 'frm_pro_available_fields', [ __CLASS__, 'register_duetdate_field_in_builder' ] );
        // Also add to Lite list to ensure drag/drop targeting works consistently.
        add_filter( 'frm_available_fields', [ __CLASS__, 'register_duetdate_field_in_builder' ] );
        add_filter( 'frm_get_field_type_class', [ __CLASS__, 'map_duetdate_field_class' ], 10, 2 );
    }

    public static function enqueue_assets() {

        // Always load CSS; very lightweight.
        // Use Duet CDN assets to avoid hash-based internal imports failing on local copies.
        $css_url = 'https://cdn.jsdelivr.net/npm/@duetds/date-picker@1/dist/duet/themes/default.css';
        wp_register_style( self::HANDLE_STYLE, $css_url, [], '1' );
        wp_enqueue_style( self::HANDLE_STYLE );

        // Load Duet web component (module + nomodule for compatibility).
        $esm_url = 'https://cdn.jsdelivr.net/npm/@duetds/date-picker@1/dist/duet/duet.esm.js';
        wp_register_script( self::HANDLE_DUET_ESM, $esm_url, [], '1', true );
        if ( function_exists( 'wp_script_add_data' ) ) {
            wp_script_add_data( self::HANDLE_DUET_ESM, 'type', 'module' );
        }
        wp_enqueue_script( self::HANDLE_DUET_ESM );

        $nomodule_url = 'https://cdn.jsdelivr.net/npm/@duetds/date-picker@1/dist/duet/duet.js';
        wp_register_script( self::HANDLE_DUET_NOMODULE, $nomodule_url, [], '1', true );
        if ( function_exists( 'wp_script_add_data' ) ) {
            wp_script_add_data( self::HANDLE_DUET_NOMODULE, 'nomodule', true );
        }
        wp_enqueue_script( self::HANDLE_DUET_NOMODULE );

        // Init script to integrate with Formidable.
        wp_register_script(
            self::HANDLE_INIT,
            plugins_url( 'js/duet-init.js', __FILE__ ),
            [],
            '0.1.0',
            true
        );

        // Pass a few PHP-side defaults.
        $start_of_week = (int) get_option( 'start_of_week', 1 );
        wp_localize_script( self::HANDLE_INIT, 'FrmDuetPickerCfg', [
            'startOfWeek' => $start_of_week,
        ] );

        wp_enqueue_script( self::HANDLE_INIT );

        // Small styles to hide original inputs and layout range.
        wp_add_inline_style( self::HANDLE_STYLE, '.frm-duet-hidden{position:absolute!important;left:-9999px!important;width:1px!important;height:1px!important;overflow:hidden!important;} .frm-duet-range{display:flex;gap:.5rem;align-items:flex-start;flex-wrap:wrap}' );
    }

    /**
     * Enqueue assets when the current form has at least one Duet Date field.
     */
    public static function maybe_enqueue_assets_for_form( $params ) {
        if ( empty( $params['form_id'] ) ) {
            return;
        }
        $form_id = absint( $params['form_id'] );
        if ( ! $form_id ) {
            return;
        }
        $duet_fields = FrmField::get_all_types_in_form( $form_id, 'duetdate', '', 'include' );
        if ( ! empty( $duet_fields ) ) {
            self::enqueue_assets();
        }
    }

    /**
     * Enqueue in admin on builder and entries pages.
     */
    public static function maybe_enqueue_in_admin() {
        if ( is_callable( [ 'FrmAppHelper', 'is_form_builder_page' ] ) && FrmAppHelper::is_form_builder_page() ) {
            self::enqueue_assets();
            return;
        }
        $page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
        if ( 'formidable-entries' === $page ) {
            self::enqueue_assets();
        }
    }

    // Global settings removed – field loads assets only when present on a form.

    /**
     * Force add type="module" and nomodule attributes for our handles.
     *
     * @param string $tag    The `<script>` tag for the enqueued script.
     * @param string $handle The script's registered handle.
     * @param string $src    The script's source URL.
     * @return string
     */
    public static function script_loader_tag( $tag, $handle, $src ) {
        if ( self::HANDLE_DUET_ESM === $handle ) {
            // Replace type attribute and ensure module is set.
            $tag = sprintf( '<script type="module" src="%s" id="%s-js"></script>' . "\n", esc_url( $src ), esc_attr( $handle ) );
        } elseif ( self::HANDLE_DUET_NOMODULE === $handle ) {
            // Add nomodule boolean attribute.
            $tag = sprintf( '<script nomodule src="%s" id="%s-js"></script>' . "\n", esc_url( $src ), esc_attr( $handle ) );
        }
        return $tag;
    }

    // Previous overrides removed: we now use only the new Duet Date field type.

    /**
     * Add range options to Duet Date field settings in builder.
     */
    public static function render_duetdate_field_options( $field, $display, $values ) {
        if ( ! is_array( $field ) || ( $field['type'] ?? '' ) !== 'duetdate' ) {
            return;
        }
        $field_id = absint( $field['id'] );
        $is_end   = ! empty( $field['duet_is_range_end'] );
        $min_date = isset( $field['duet_min'] ) ? $field['duet_min'] : '';
        $max_date = isset( $field['duet_max'] ) ? $field['duet_max'] : '';
        $locale   = isset( $field['duet_locale'] ) ? $field['duet_locale'] : '';
        $disabled_dates = isset( $field['duet_disabled_dates'] ) && is_array( $field['duet_disabled_dates'] ) ? implode( "\n", $field['duet_disabled_dates'] ) : '';
        $days_disabled  = isset( $field['duet_days_disabled'] ) && is_array( $field['duet_days_disabled'] ) ? array_map( 'absint', $field['duet_days_disabled'] ) : array();

        // Build Start field choices from duetdate fields in this form.
        $form_id    = absint( $field['form_id'] );
        $duet_starts = array();
        foreach ( FrmField::get_all_types_in_form( $form_id, 'duetdate', '', 'include' ) as $f ) {
            if ( (int) $f->id === $field_id ) {
                continue;
            }
            $duet_starts[ $f->id ] = $f->name;
        }
        $selected_start = isset( $field['duet_range_start_field'] ) ? absint( $field['duet_range_start_field'] ) : 0;
        ?>
        <div class="frm_form_field">
            <label class="frm_left_label">
                <input type="checkbox" name="field_options[duet_is_range_end_<?php echo esc_attr( $field_id ); ?>]" value="1" <?php checked( $is_end ); ?> />
                <?php esc_html_e( 'This is the End Date (link to a Start Date)', 'formidable' ); ?>
            </label>
        </div>
        <div class="frm_form_field">
            <label for="duet_start_<?php echo esc_attr( $field_id ); ?>" class="frm_left_label"><?php esc_html_e( 'Start Date field', 'formidable' ); ?></label>
            <select id="duet_start_<?php echo esc_attr( $field_id ); ?>" name="field_options[duet_range_start_field_<?php echo esc_attr( $field_id ); ?>]" class="frm_form_field frm-12">
                <option value="0"><?php esc_html_e( 'Select a field', 'formidable' ); ?></option>
                <?php foreach ( $duet_starts as $id => $label ) { ?>
                    <option value="<?php echo esc_attr( $id ); ?>" <?php selected( $selected_start, $id ); ?>><?php echo esc_html( $label ); ?></option>
                <?php } ?>
            </select>
        </div>

        <hr />
        <div class="frm_grid_container">
            <div class="frm6 frm_form_field">
                <label for="duet_min_<?php echo esc_attr( $field_id ); ?>" class="frm_left_label"><?php esc_html_e( 'Minimum Date', 'formidable' ); ?></label>
                <input type="text" id="duet_min_<?php echo esc_attr( $field_id ); ?>" name="field_options[duet_min_<?php echo esc_attr( $field_id ); ?>]" value="<?php echo esc_attr( $min_date ); ?>" placeholder="YYYY-MM-DD or +3days" />
            </div>
            <div class="frm6 frm_form_field">
                <label for="duet_max_<?php echo esc_attr( $field_id ); ?>" class="frm_left_label"><?php esc_html_e( 'Maximum Date', 'formidable' ); ?></label>
                <input type="text" id="duet_max_<?php echo esc_attr( $field_id ); ?>" name="field_options[duet_max_<?php echo esc_attr( $field_id ); ?>]" value="<?php echo esc_attr( $max_date ); ?>" placeholder="YYYY-MM-DD or +1year" />
            </div>
        </div>

        <div class="frm_grid_container">
            <div class="frm6 frm_form_field">
                <label for="duet_locale_<?php echo esc_attr( $field_id ); ?>" class="frm_left_label"><?php esc_html_e( 'Locale', 'formidable' ); ?></label>
                <input type="text" id="duet_locale_<?php echo esc_attr( $field_id ); ?>" name="field_options[duet_locale_<?php echo esc_attr( $field_id ); ?>]" value="<?php echo esc_attr( $locale ); ?>" placeholder="en, fr, de..." />
            </div>
            <?php $format = isset( $field['duet_format'] ) ? (string) $field['duet_format'] : ''; ?>
            <div class="frm6 frm_form_field">
                <label for="duet_format_<?php echo esc_attr( $field_id ); ?>" class="frm_left_label"><?php esc_html_e( 'Display/Input Format', 'formidable' ); ?></label>
                <input type="text" id="duet_format_<?php echo esc_attr( $field_id ); ?>" name="field_options[duet_format_<?php echo esc_attr( $field_id ); ?>]" value="<?php echo esc_attr( $format ); ?>" placeholder="YYYY-MM-DD or DD-MM-YYYY|YYYY" />
                <p class="howto"><?php esc_html_e( 'Supported tokens: YYYY, MM, DD. If a user enters a year-only value (YYYY), it is stored as YYYY-01-01 to keep full ISO dates.', 'formidable' ); ?></p>
            </div>
        </div>

        <?php $year_only = ! empty( $field['duet_year_only'] ) && $field['duet_year_only'] === '1'; ?>
        <div class="frm_form_field">
            <label class="frm_left_label">
                <input type="checkbox" name="field_options[duet_year_only_<?php echo esc_attr( $field_id ); ?>]" value="1" <?php checked( $year_only ); ?> />
                <?php esc_html_e( 'Year only (show YYYY and disable calendar)', 'formidable' ); ?>
            </label>
            <p class="howto"><?php esc_html_e( 'Users can enter a 4-digit year. The stored value remains a full ISO date (YYYY-01-01) so validations and min/max still work.', 'formidable' ); ?></p>
        </div>

        <div class="frm_form_field">
            <label class="frm_left_label"><?php esc_html_e( 'Disabled Days of the Week', 'formidable' ); ?></label>
            <div class="frm_inline_box">
                <?php
                $day_labels = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );
                for ( $i = 0; $i <= 6; $i++ ) {
                    $checked = in_array( $i, $days_disabled, true );
                    echo '<label style="margin-right:8px"><input type="checkbox" name="field_options[duet_days_disabled_' . esc_attr( $field_id ) . '][]" value="' . esc_attr( $i ) . '" ' . checked( $checked, true, false ) . ' /> ' . esc_html( $day_labels[ $i ] ) . '</label>';
                }
                ?>
            </div>
        </div>

        <div class="frm_form_field">
            <label for="duet_disabled_dates_<?php echo esc_attr( $field_id ); ?>" class="frm_left_label"><?php esc_html_e( 'Disabled Dates (one per line, YYYY-MM-DD)', 'formidable' ); ?></label>
            <textarea id="duet_disabled_dates_<?php echo esc_attr( $field_id ); ?>" name="field_options[duet_disabled_dates_<?php echo esc_attr( $field_id ); ?>]" class="frm_form_field" rows="4" placeholder="2025-12-25&#10;2025-01-01"><?php echo esc_textarea( $disabled_dates ); ?></textarea>
        </div>
        <?php
    }

    /**
     * Save Duet Date field builder options.
     */
    public static function save_duetdate_field_options( $values, $field_id = 0 ) {
        $field_id = $field_id ? absint( $field_id ) : ( isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0 );
        $posted   = isset( $_POST['field_options'] ) ? (array) $_POST['field_options'] : array();
        if ( ! isset( $values['field_options'] ) || ! is_array( $values['field_options'] ) ) {
            $values['field_options'] = array();
        }

        // Save options inside field_options to avoid interfering with core columns like field_order.
        $values['field_options']['duet_is_range_end']      = isset( $posted[ 'duet_is_range_end_' . $field_id ] ) ? '1' : '0';
        $values['field_options']['duet_range_start_field'] = isset( $posted[ 'duet_range_start_field_' . $field_id ] ) ? absint( $posted[ 'duet_range_start_field_' . $field_id ] ) : 0;
        $values['field_options']['duet_min']               = isset( $posted[ 'duet_min_' . $field_id ] ) ? sanitize_text_field( $posted[ 'duet_min_' . $field_id ] ) : '';
        $values['field_options']['duet_max']               = isset( $posted[ 'duet_max_' . $field_id ] ) ? sanitize_text_field( $posted[ 'duet_max_' . $field_id ] ) : '';
        $values['field_options']['duet_locale']            = isset( $posted[ 'duet_locale_' . $field_id ] ) ? sanitize_text_field( $posted[ 'duet_locale_' . $field_id ] ) : '';
        $values['field_options']['duet_format']            = isset( $posted[ 'duet_format_' . $field_id ] ) ? sanitize_text_field( $posted[ 'duet_format_' . $field_id ] ) : '';
        $values['field_options']['duet_year_only']         = isset( $posted[ 'duet_year_only_' . $field_id ] ) ? '1' : '0';
        // Disabled dates -> array of lines matching YYYY-MM-DD (keep as-is; JS will normalize)
        if ( isset( $posted[ 'duet_disabled_dates_' . $field_id ] ) ) {
            $lines = preg_split( '/\r\n|\r|\n/', (string) $posted[ 'duet_disabled_dates_' . $field_id ] );
            $lines = array_filter( array_map( 'trim', $lines ) );
            $values['field_options']['duet_disabled_dates'] = array_values( $lines );
        } else {
            $values['field_options']['duet_disabled_dates'] = array();
        }
        // Disabled days of week
        $values['field_options']['duet_days_disabled'] = array();
        if ( isset( $posted[ 'duet_days_disabled_' . $field_id ] ) && is_array( $posted[ 'duet_days_disabled_' . $field_id ] ) ) {
            $values['field_options']['duet_days_disabled'] = array_values( array_map( 'absint', $posted[ 'duet_days_disabled_' . $field_id ] ) );
        }
        return $values;
    }

    /**
     * Add a "Duet Date" item to the builder field list.
     */
    public static function register_duetdate_field_in_builder( $fields ) {
        // Add beside other Pro fields (like Date). Avoid forcing a section to prevent mis-grouping under Pricing.
        $fields['duetdate'] = array(
            'name' => __( 'Duet Date', 'formidable' ),
            'icon' => 'frm_icon_font frm_calendar2_icon',
        );
        return $fields;
    }

    /**
     * Map duetdate type to our field class.
     */
    public static function map_duetdate_field_class( $class, $type ) {
        if ( 'duetdate' === $type ) {
            // Ensure base class exists and load our class file when needed.
            if ( class_exists( 'FrmFieldType' ) && ! class_exists( 'FrmDuetDateField' ) ) {
                require_once __DIR__ . '/classes/FrmDuetDateField.php';
            }
            return 'FrmDuetDateField';
        }
        return $class;
    }
}

add_action( 'plugins_loaded', array( 'Formidable_Duet_Date_Picker_Plugin', 'init' ), 20 );
