<?php
/**
 * Plugin Name: FF Fieldset Container Field
 * Description: Final unified Fieldset Start/End field types for Formidable Forms, replacing previous variants. Compatible with existing forms using legacy slugs.
 * Version: 1.0.0
 * Author: Adem Cifcioglu
 * Text Domain: ff-fieldset-container-field
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'FFF_FIELDSET_VERSION', '1.0.0' );
define( 'FFF_FIELDSET_FILE', __FILE__ );
define( 'FFF_FIELDSET_DIR', plugin_dir_path( __FILE__ ) );
define( 'FFF_FIELDSET_URL', plugin_dir_url( __FILE__ ) );

// Load translations early
add_action( 'plugins_loaded', function(){
    load_plugin_textdomain( 'ff-fieldset-container-field', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}, 1 );

// Initialize after Formidable loads.
add_action( 'plugins_loaded', function(){
    if ( ! class_exists( 'FrmFieldType' ) ) {
        return; // Formidable Forms not active.
    }

    require_once FFF_FIELDSET_DIR . 'includes/class-fff-fieldset-start.php';
    require_once FFF_FIELDSET_DIR . 'includes/class-fff-fieldset-end.php';

    // Register in the builder palette (Lite & Pro for safety)
    $register_cb = function( $fields ){
        // Keep stable slugs for back-compat in newly added fields
        $fields['ff-fieldset-start'] = array(
            'name' => __( 'Fieldset Start', 'ff-fieldset-container-field' ),
            'icon' => 'frm_icon_font frm_ff2_icon_start',
        );
        $fields['ff-fieldset-end'] = array(
            'name' => __( 'Fieldset End', 'ff-fieldset-container-field' ),
            'icon' => 'frm_icon_font frm_ff2_icon_end',
        );
        return $fields;
    };
    add_filter( 'frm_available_fields', $register_cb );

    // Map field types (legacy + current) to these classes
    add_filter( 'frm_get_field_type_class', function( $class, $field_type ){
        if ( in_array( $field_type, array( 'ff-fieldset-start', 'fieldset-start' ), true ) ) { return 'FFF_Fieldset_Start'; }
        if ( in_array( $field_type, array( 'ff-fieldset-end', 'fieldset-end' ), true ) ) { return 'FFF_Fieldset_End'; }
        return $class;
    }, 10, 2 );

    // Hide Customize HTML for these types since output is generated
    add_filter( 'frm_show_custom_html', function( $show, $field_type ){
        if ( in_array( $field_type, array( 'ff-fieldset-start', 'ff-fieldset-end', 'fieldset-start', 'fieldset-end' ), true ) ) {
            return false;
        }
        return $show;
    }, 10, 2 );

    // Admin icon styles for builder palette
    add_action( 'admin_enqueue_scripts', function(){
        wp_enqueue_style( 'fff-fieldset-admin', FFF_FIELDSET_URL . 'assets/css/admin.css', array(), FFF_FIELDSET_VERSION );
    } );
}, 20 );

// Save legend_visible and legend_heading from builder
add_filter( 'frm_update_field_options', function( $field_options, $field, $values ){
    $type = isset( $field->type ) ? $field->type : '';
    if ( ! in_array( $type, array( 'ff-fieldset-start', 'fieldset-start' ), true ) ) {
        return $field_options;
    }
    $option_key = 'legend_visible_' . $field->id;
    $field_options['legend_visible'] = isset( $values['field_options'][ $option_key ] ) ? $values['field_options'][ $option_key ] : '0';

    // Save optional heading level (''|h2|h3|h4|h5|h6)
    $heading_key = 'legend_heading_' . $field->id;
    $heading_val = isset( $values['field_options'][ $heading_key ] ) ? strtolower( $values['field_options'][ $heading_key ] ) : '';
    $allowed = array( '', 'h2', 'h3', 'h4', 'h5', 'h6' );
    if ( ! in_array( $heading_val, $allowed, true ) ) {
        $heading_val = '';
    }
    $field_options['legend_heading'] = $heading_val;
    return $field_options;
}, 10, 3 );

// Front-end styles
add_action( 'wp_enqueue_scripts', function(){
    if ( class_exists( 'FrmAppHelper' ) ) {
        wp_enqueue_style( 'fff-fieldset', FFF_FIELDSET_URL . 'assets/css/fieldset-container.css', array(), FFF_FIELDSET_VERSION );
    }
}, 20 );

?>

