<?php

class FF_FieldsetEnd extends FrmFieldType {

	/**
	 * @var string
	 */
	protected $type = 'fieldset-end';

	/**
	 * This field doesn't store data.
	 * @var bool
	 */
	protected $has_input = false;

	/**
	 * Don't show a label above this field in the form.
	 * @var bool
	 */
	protected $has_for_label = false;

	/**
	 * Which Formidable settings should be hidden or displayed?
	 */
	protected function field_settings_for_type() {
		$settings = parent::field_settings_for_type();
		
		// Hide most settings - this is just a closing tag
		$settings['default']      = false;
		$settings['clear_on_focus'] = false;
		$settings['size']         = false;
		$settings['max']          = false;
		$settings['label']        = false;
		$settings['description']  = true; // Keep description for notes
		$settings['required']     = false;
		$settings['unique']       = false;
		$settings['read_only']    = false;
		$settings['invalid']      = false;

		return $settings;
	}

	/**
	 * Builder preview file.
	 */
	protected function include_form_builder_file() {
		return dirname( dirname( __FILE__ ) ) . '/views/builder-field-end.php';
	}

	/**
	 * Don't validate this field - it doesn't store data.
	 */
	public function validate( $args ) {
		return array(); // No errors
	}

	/**
	 * Front-end output.
	 */
	public function front_field_input( $args, $shortcode_atts ) {
		return '</fieldset>';
	}
}
