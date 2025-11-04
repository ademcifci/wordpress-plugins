<?php
/**
 * Plugin Name: FF Fieldset Container
 * Description: Adds fieldset start/end fields for Formidable Forms to wrap groups of fields in semantic fieldset elements
 * Version: 1.3.0
 * Author: Adem Cifcioglu
 * License: GPL-2.0+
 * Text Domain: ff-fieldset-container
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Reusable plugin version constant (keeps enqueue versions DRY)
if ( ! defined( 'FF_FIELDSET_CONTAINER_VERSION' ) ) {
    define( 'FF_FIELDSET_CONTAINER_VERSION', '1.3.0' );
}

/**
 * Add the autoloader.
 */
function ff_fieldset_container_load() {
    spl_autoload_register( 'ff_fieldset_container_autoloader' );
}
add_action( 'plugins_loaded', 'ff_fieldset_container_load' );

/**
 * Autoloader for plugin classes
 */
function ff_fieldset_container_autoloader( $class_name ) {
	// Only load FF_Fieldset classes
	if ( ! preg_match( '/^FF_Fieldset.+$/', $class_name ) ) {
		return;
	}

	$filepath = dirname( __FILE__ );
	$filepath .= '/classes/' . $class_name . '.php';

	if ( file_exists( $filepath ) ) {
		require( $filepath );
	}
}

/**
 * Tell Formidable where to find the new field types.
 */
function ff_fieldset_get_field_type_class( $class, $field_type ) {
	if ( $field_type === 'fieldset-start' ) {
		$class = 'FF_FieldsetStart';
	} elseif ( $field_type === 'fieldset-end' ) {
		$class = 'FF_FieldsetEnd';
	}
	return $class;
}
add_filter( 'frm_get_field_type_class', 'ff_fieldset_get_field_type_class', 20, 2 );

/**
 * Add the field buttons to the form builder.
 */
function ff_fieldset_add_fields( $fields ) {
    // Register namespaced slugs for new inserts
    $fields['ff-fieldset-start'] = array(
        'name' => __( 'Fieldset Start', 'ff-fieldset-container' ),
        'icon' => 'frm_icon_font frm-square-o',
        'defaults' => array(
            'name' => '',
            'description' => '',
        ),
    );
    
    $fields['ff-fieldset-end'] = array(
        'name' => __( 'Fieldset End', 'ff-fieldset-container' ),
        'icon' => 'frm_icon_font frm-square',
        'defaults' => array(
            'name' => '',
            'description' => '',
        ),
    );
	
	return $fields;
}
add_filter( 'frm_available_fields', 'ff_fieldset_add_fields' );

/**
 * Load text domain for translations.
 */
function ff_fieldset_container_load_textdomain() {
	load_plugin_textdomain( 'ff-fieldset-container', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'ff_fieldset_container_load_textdomain' );

/**
 * Enqueue front-end styles.
 */
function ff_fieldset_container_enqueue_styles() {
    // Only enqueue if Formidable Forms is active and we're viewing a form
    if ( class_exists( 'FrmAppHelper' ) ) {
        wp_enqueue_style(
            'ff-fieldset-container',
            plugins_url( 'assets/css/fieldset-container.css', __FILE__ ),
            array(),
            FF_FIELDSET_CONTAINER_VERSION
        );
    }
}
add_action( 'wp_enqueue_scripts', 'ff_fieldset_container_enqueue_styles' );


	// Admin icon style for builder palette — scope to Formidable builder screens only.
    function ff_fieldset_container_enqueue_admin_styles( $hook_suffix ){
        if ( ! class_exists( 'FrmAppHelper' ) ) {
            return; // Only load when Formidable Forms is active
        }

		// Ensure we're on the Formidable top-level page
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		if ( 'formidable' !== $page ) {
			return;
		}

        // Enqueue admin CSS for builder palette icons
        wp_enqueue_style( 'ff_fieldset_container-admin', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), FF_FIELDSET_CONTAINER_VERSION );
    }
    add_action( 'admin_enqueue_scripts', 'ff_fieldset_container_enqueue_admin_styles', 20 );

/**
 * 🔑 The important part: override the rendered HTML for our types.
 *
 * We accept up to 4 args to be compatible with different FF versions.
 * If your version only passes ($custom_html, $type), the defaults handle it.
 */
add_filter( 'frm_custom_html', function( $custom_html, $type = null, $field = null, $atts = null ) {

	// Only intercept our two custom types.
    if ( in_array( $type, array( 'fieldset-start', 'ff-fieldset-start' ), true ) ) {
		
		/**
		 * Use Formidable placeholders so values are auto-substituted:
		 *   [id], [field_key], [field_name], [description], conditionals like [if description]...[/if description]
		 *
		 * No container wrappers, no [input] — just the opening <fieldset>.
		 * If you have a “show legend visually” toggle in your Start class/settings,
		 * you can switch the <legend> line on/off here based on $field settings.
		 */
		// Generate markup using the class method with proper args
		$instance = new FF_FieldsetStart();
		return $instance->front_field_input( array( 'field' => $field ), $atts );
	}

    if ( in_array( $type, array( 'fieldset-end', 'ff-fieldset-end' ), true ) ) {
		// Just the closing tag, nothing else.
		$instance = new FF_FieldsetEnd();
		return $instance->front_field_input( array( 'field' => $field ), $atts );
	}

	return $custom_html;
}, 20, 4 );

/**
 * Save custom field option for legend_visible and regenerate custom HTML using the class
 */
add_filter( 'frm_update_field_options', 'ff_fieldset_update_field_options', 10, 3 );
function ff_fieldset_update_field_options( $field_options, $field, $values ) {
    if ( $field->type !== 'fieldset-start' && $field->type !== 'ff-fieldset-start' ) {
        return $field_options;
    }
	
	// The posted value comes as 'legend_visible_FIELDID'
	$option_key = 'legend_visible_' . $field->id;
	
	// Save the legend_visible setting
	$field_options['legend_visible'] = isset( $values['field_options'][ $option_key ] ) ? $values['field_options'][ $option_key ] : '0';
	
	// Regenerate the custom HTML using the class method
	$field->field_options = $field_options; // Update field object with new options
	$instance = new FF_FieldsetStart();
	$field_options['custom_html'] = $instance->front_field_input( array( 'field' => $field ), array() );
	
	return $field_options;
}

/**
 * Map additional alias slugs to our classes to reduce collision risk while
 * keeping backward compatibility with existing forms.
 */
function ff_fieldset_container_extend_type_mapping( $class, $field_type ) {
    if ( $field_type === 'ff-fieldset-start' ) {
        return 'FF_FieldsetStart';
    }
    if ( $field_type === 'ff-fieldset-end' ) {
        return 'FF_FieldsetEnd';
    }
    return $class;
}
add_filter( 'frm_get_field_type_class', 'ff_fieldset_container_extend_type_mapping', 21, 2 );

/**
 * Admin warning: detect mis-nested or unmatched fieldset Start/End pairs on the builder screen.
 */
// Builder validation/notice logic has been removed per user request.
