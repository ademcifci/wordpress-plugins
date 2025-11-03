<?php

class FF_FieldsetStart extends FrmFieldType {

	/**
	 * @var string
	 */
	protected $type = 'fieldset-start';

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
		
		// We only need the name (used as legend text) and description
		$settings['default']      = false;
		$settings['clear_on_focus'] = false;
		$settings['size']         = false;
		$settings['max']          = false;
		$settings['label']        = true;  // Show in builder settings so user can set legend text
		$settings['description']  = true;
		$settings['required']     = false;
		$settings['unique']       = false;
		$settings['read_only']    = false;
		$settings['invalid']      = false;

		return $settings;
	}

	/**
	 * Custom field options.
	 */
	protected function extra_field_opts() {
		return array(
			'legend_visible' => true,  // true = visible, false = screen-reader only
		);
	}

	/**
	 * Builder preview file.
	 */
	protected function include_form_builder_file() {
		return dirname( dirname( __FILE__ ) ) . '/views/builder-field-start.php';
	}

	/**
	 * Get the type of field being displayed - required for custom settings.
	 */
	public function displayed_field_type( $field ) {
		return array(
			$this->type => true,
		);
	}

	/**
	 * Add custom settings in the builder.
	 */
	public function show_extra_field_choices( $args ) {
		$field = $args['field'];
		include( dirname( dirname( __FILE__ ) ) . '/views/builder-settings-start.php' );
	}

	/**
	 * Don't validate this field - it doesn't store data.
	 */
	public function validate( $args ) {
		return array(); // No errors
	}

/**
	 * Front-end output with field-specific settings.
	 */
	public function front_field_input( $args, $shortcode_atts ) {
		$field = $args['field'];
		
		// Get legend_visible from field options, default to 1 (visible)
		$legend_visible = isset( $field->field_options['legend_visible'] ) ? $field->field_options['legend_visible'] : 1;
		
		// Convert to boolean - '0', 0, false should all be false
		$legend_visible = ! empty( $legend_visible ) && $legend_visible !== '0';
		
		$legend_class = $legend_visible ? '' : ' class="frm_screen_reader"';
		
		$html = '<fieldset  id="frm_field_[id]_container" class="frm_fieldset_container frm_fieldset_start frm_form_field form-field [required_class][error_class]">
			<legend' . $legend_class . '>[field_name]</legend>
			[if description]<div class="frm_description">[description]</div>[/if description]';
		
		return $html;
	}
}

