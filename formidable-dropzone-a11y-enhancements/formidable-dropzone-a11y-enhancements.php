<?php
/**
 * Plugin Name: Formidable Dropzone A11y Enhancements
 * Description: Accessibility fixes for Formidable Forms Dropzone upload fields — focus management, "Other" labels, and upload announcements.
 * Version: 1.0.3
 * Author: Adem Cifcioglu
 * License: GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Formidable_Dropzone_A11y_Enhancements {
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

    public function enqueue_assets() {
        $ver = '1.0.3';

		wp_enqueue_style(
			'formidable-dropzone-a11y-enhancements',
			plugins_url( 'assets/formidable-dropzone-a11y-enhancements.css', __FILE__ ),
			[],
			$ver
		);

		wp_enqueue_script(
			'formidable-dropzone-a11y-enhancements',
			plugins_url( 'assets/formidable-dropzone-a11y-enhancements.js', __FILE__ ),
			[ 'jquery' ],
			$ver,
			true
		);
		
	}
}

new Formidable_Dropzone_A11y_Enhancements();
