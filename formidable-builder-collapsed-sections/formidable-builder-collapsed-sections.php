<?php
/**
 * Plugin Name: Formidable Builder – Collapsed Sections
 * Description: Starts native Formidable Sections (divider/end_divider) collapsed in the form builder, with a toggle to expand.
 * Version: 0.1.0
 * Author: Adem Cifcioglu
 * License: GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'FBCS_VERSION', '0.1.0' );
define( 'FBCS_FILE', __FILE__ );
define( 'FBCS_DIR', plugin_dir_path( __FILE__ ) );
define( 'FBCS_URL', plugin_dir_url( __FILE__ ) );

// Enqueue only on Formidable builder screens.
add_action( 'admin_enqueue_scripts', function( $hook ){
    // Load broadly on Formidable admin; script guards will bail if not on builder.
    wp_enqueue_script( 'fbcs-admin', FBCS_URL . 'assets/admin.js', array( 'jquery' ), FBCS_VERSION, true );
    wp_enqueue_style( 'fbcs-admin', FBCS_URL . 'assets/admin.css', array(), FBCS_VERSION );
} );

