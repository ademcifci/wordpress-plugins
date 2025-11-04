<?php
/*
Plugin Name: Formidable – Simple Progress Field
Description: Adds a lightweight “Simple Progress” field that shows “Step X of Y” for paged forms. Works with Formidable Forms Lite and Pro without modifying core.
Version: 1.0.0
Author: Your Team
Text Domain: formidable-simple-progress
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'FRM_SP_VERSION', '1.0.0' );
define( 'FRM_SP_PLUGIN_FILE', __FILE__ );
define( 'FRM_SP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FRM_SP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

add_action( 'plugins_loaded', function () {
    load_plugin_textdomain( 'formidable-simple-progress', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}, 1 );

// Initialize after Formidable loads.
add_action( 'plugins_loaded', function () {
    if ( ! class_exists( 'FrmFieldType' ) ) {
        // Formidable not active.
        return;
    }

    require_once FRM_SP_PLUGIN_DIR . 'includes/class-frm-simple-progress-field.php';

    // Show field in the builder palette (Lite & Pro filters for safety).
    add_filter( 'frm_pro_available_fields', [ 'Frm_Simple_Progress_Field', 'register_available_field' ] );

    // Map field type to our class when Formidable requests it.
    add_filter( 'frm_get_field_type_class', function ( $class, $field_type ) {
        if ( 'simple_progress' === $field_type ) {
            return 'Frm_Simple_Progress_Field';
        }
        return $class;
    }, 10, 2 );

    // Hide the Customize HTML box for this field type since output is generated.
    add_filter( 'frm_show_custom_html', function( $show, $field_type ){
        if ( 'simple_progress' === $field_type ) {
            return false;
        }
        return $show;
    }, 10, 2 );

    // Admin icon style for builder palette.
    add_action( 'admin_enqueue_scripts', function(){
        wp_enqueue_style( 'frm-sp-admin', FRM_SP_PLUGIN_URL . 'assets/css/admin.css', array(), FRM_SP_VERSION );
    } );
}, 20 );

// Auto-inject front-end output if a Simple Progress field is configured for it.
function frm_sp_maybe_output_progress_badge( $atts ) {
    if ( empty( $atts ) ) { $atts = array(); }

    // Determine current form object (mimic Pro's page_navigation).
    $defaults = array(
        'id'   => false,
        'form' => false,
    );
    $atts = shortcode_atts( $defaults, $atts );

    $form = $atts['form'];
    if ( ! is_object( $form ) && ! empty( $atts['id'] ) && class_exists( 'FrmForm' ) ) {
        $form       = FrmForm::getOne( $atts['id'] );
        $atts['id'] = $form ? $form->id : 0;
    }
    if ( ! $form ) { return; }

    if ( ! class_exists( 'FrmField' ) ) { return; }

    // Find a Simple Progress field in this form set to auto-inject.
    $fields = FrmField::get_all_for_form( $form->id );
    $sp_field = null;
    foreach ( (array) $fields as $f ) {
        if ( isset( $f->type ) && 'simple_progress' === $f->type ) {
            $auto = (int) FrmField::get_option( $f, 'sp_auto_inject' );
            if ( $auto ) { $sp_field = $f; break; }
        }
    }
    if ( ! $sp_field ) { return; }

    // Map selected position to current action.
    $pos = FrmField::get_option( $sp_field, 'sp_inject_position' );
    $action = current_action();
    $allowed_map = array(
        ''              => 'frm_after_title',
        'above_title'   => 'frm_before_title',
        'before_submit' => 'frm_before_submit_btn',
        'after_submit'  => 'frm_after_submit_btn',
    );
    if ( isset( $allowed_map[ $pos ] ) && $allowed_map[ $pos ] !== $action ) {
        return; // Not our selected position.
    }

    // Only show when paging exists.
    if ( ! class_exists( 'FrmProPageField' ) ) { return; }
    $pages = FrmProPageField::get_form_pages( $form );
    if ( empty( $pages['page_array'] ) ) { return; }

    $page_array = $pages['page_array'];
    $total = max( 1, count( $page_array ) );
    $current = 1;
    foreach ( $page_array as $page_number => $page ) {
        if ( isset( $page['aria-disabled'] ) ) { $current = $page_number; break; }
    }

    $step_label = FrmField::get_option( $sp_field, 'step_label' );
    if ( empty( $step_label ) ) { $step_label = __( 'Step', 'formidable-simple-progress' ); }
    $of_label = __( 'of', 'formidable-simple-progress' );

    if ( function_exists( 'wp_enqueue_style' ) ) {
        wp_enqueue_style( 'frm-simple-progress', FRM_SP_PLUGIN_URL . 'assets/css/simple-progress.css', array(), FRM_SP_VERSION );
    }

    $label = sprintf( '%1$s %2$d %3$s %4$d', esc_html( $step_label ), absint( $current ), esc_html( $of_label ), absint( $total ) );
    $live = (int) FrmField::get_option( $sp_field, 'sp_live_region' );
    $attrs = 'class="frm-simple-progress"';
    if ( $live ) { $attrs .= ' role="status" aria-live="polite"'; }
    echo '<div ' . $attrs . '><span class="frm-simple-progress-badge">' . esc_html( $label ) . '</span></div>';
}

add_action( 'frm_after_title', 'frm_sp_maybe_output_progress_badge', 10, 1 );
add_action( 'frm_before_title', 'frm_sp_maybe_output_progress_badge', 10, 1 );
add_action( 'frm_before_submit_btn', 'frm_sp_maybe_output_progress_badge', 10, 1 );
add_action( 'frm_after_submit_btn', 'frm_sp_maybe_output_progress_badge', 10, 1 );
