<?php
/**
 * Plugin Name:  FF Radio & Checkbox Fieldset Wrapper
 * Description:  Adds semantic <fieldset>/<legend> for Formidable Forms radio/checkbox groups by overriding the builder’s default templates (frm_custom_html). Includes settings for legend visibility and stripping ARIA roles. Note: This does not retrofit existing fields’ frontend HTML.
 * Version:      1.2.4
 * Author:       Adem Cifcioglu
 * License:      GPL-2.0+
 * Text Domain:  ff-fieldset
 * Domain Path:  /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class FF_Radio_Checkbox_Fieldset {

	const OPTION_KEY = 'ff_fieldset_options';

	private $opts = [
		'visible_legends'       => true,   // true = visible; false = screen-reader only
		'strip_roles'           => true,   // true = strip role="group|radiogroup" (recommended with real fieldsets)
		'use_template_override' => true,   // when true, use frm_custom_html override (builder/global default templates)
	];

	public function __construct() {
		// Load text domain for i18n.
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );

		// Load saved options.
		$saved = get_option( self::OPTION_KEY );
		if ( is_array( $saved ) ) {
			$this->opts = array_merge( $this->opts, $saved );
		}

		// Front-end: do nothing hook doesn't work for existing fields, only new ones and only in admin.
		if ( ! is_admin() ) {
			return;
		}

		// Admin: settings UI
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'add_settings_link' ] );

		// Filter to enable/disable the override.
		$use_override_filtered = apply_filters(
			'FF_fieldset/use_template_override',
			(bool) $this->opts['use_template_override']
		);

		if ( $use_override_filtered ) {
			add_filter( 'frm_custom_html', [ $this, 'override_custom_html_templates' ], 10, 2 );
		}
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'ff-fieldset', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/* --------------------------------------------------------------
	 * Template-level override (frm_custom_html)
	 * ------------------------------------------------------------ */

	/**
	 * Replaces Formidable's default templates for radio/checkbox with a version
	 * that includes fieldset/legend. Affects builder "Customize HTML".
	 * NOTE: frm_custom_html applies to ALL forms' default templates.
	 *
	 * @param string $custom_html The template string
	 * @param string $type        Field type being templated
	 * @return string
	 */
	public function override_custom_html_templates( $custom_html, $type ) {
		if ( ! in_array( $type, [ 'radio', 'checkbox' ], true ) ) {
			return $custom_html;
		}

		// Simple, boolean filters (toggle with __return_true / __return_false)
		$legend_visible = apply_filters(
			'FF_fieldset/legend_visible',
			(bool) $this->opts['visible_legends']
		);

		$keep_group_roles = apply_filters(
			'FF_fieldset/keep_group_roles',
			! (bool) $this->opts['strip_roles']
		);

		$include_required_label = apply_filters(
			'FF_fieldset/include_required_label',
			true
		);

		$legend_classes = $legend_visible ? '' : 'frm_screen_reader';

		// Build optional attributes for the inner options container.
		$opt_attrs = '';
		if ( $keep_group_roles ) {
			// Keep role AND keep name via aria-labelledby on the inner container.
			$role_attr = ( $type === 'radio' ) ? ' role="radiogroup"' : ' role="group"';
			$opt_attrs = ' aria-labelledby="field_[key]_label"' . $role_attr;
		}

		// aria-describedby when a description exists.
		$fieldset_desc_attr = '[if description] aria-describedby="frm_desc_field_[key]"[/if description]';

		// Required label token (controlled by filter).
		$required_piece = $include_required_label ? ' <span class="frm_required">[required_label]</span>' : '';

		$custom_html = '
	<div id="frm_field_[id]_container" class="frm_form_field form-field [required_class][error_class]">
		<fieldset class="FF_radio_checkbox_fieldset FF_radio_checkbox_fieldset--' . esc_attr( $type ) . '">
			<legend id="field_[key]_label" class="' . esc_attr( $legend_classes ) . ' frm_primary_label">[field_name]' . $required_piece . '</legend>
			<div class="frm_opt_container"' . $opt_attrs . '>[input ' . $fieldset_desc_attr . ']</div>
			[if description]<div class="frm_description" id="frm_desc_field_[key]">[description]</div>[/if description]
			[if error]<div class="frm_error" id="frm_error_field_[key]">[error]</div>[/if error]
		</fieldset>
	</div>';

		return $custom_html;
	}

	/* ---------------
	 * Admin Settings
	 * ------------- */

	public function add_settings_page() {
		add_options_page(
			__( 'FF Fieldset Settings', 'ff-fieldset' ),
			__( 'FF Fieldset', 'ff-fieldset' ),
			'manage_options',
			'ff-fieldset-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	public function register_settings() {
		register_setting(
			'ff_fieldset_settings_group',
			self::OPTION_KEY,
			[ 'sanitize_callback' => [ $this, 'sanitize_options' ] ]
		);

		add_settings_section(
			'ff_fieldset_main',
			__( 'Fieldset & Legend Options', 'ff-fieldset' ),
			function() {
				echo '<p>' . esc_html__( 'Configure how Formidable radio/checkbox groups are rendered in the builder’s default templates. This hook updates the default HTML that appears in “Customize HTML” and preview. It does not change the stored HTML for existing fields.', 'ff-fieldset' ) . '</p>';
			},
			'ff-fieldset-settings'
		);

		add_settings_field( 'use_template_override', __( 'Use template override (frm_custom_html)', 'ff-fieldset' ), [ $this, 'field_template_override' ], 'ff-fieldset-settings', 'ff_fieldset_main' );
		add_settings_field( 'visible_legends', __( 'Visible legends', 'ff-fieldset' ), [ $this, 'field_visible_legends' ], 'ff-fieldset-settings', 'ff_fieldset_main' );
		add_settings_field( 'strip_roles', __( 'Strip ARIA roles', 'ff-fieldset' ), [ $this, 'field_strip_roles' ], 'ff-fieldset-settings', 'ff_fieldset_main' );

	}

	public function sanitize_options( $input ) {
		$out = $this->opts;

		$out['visible_legends']       = ! empty( $input['visible_legends'] ) ? true : false;
		$out['strip_roles']           = ! empty( $input['strip_roles'] ) ? true : false;
		$out['use_template_override'] = ! empty( $input['use_template_override'] ) ? true : false;

		return $out;
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$this->opts = array_merge( $this->opts, (array) get_option( self::OPTION_KEY, [] ) );
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'FF Fieldset Settings', 'ff-fieldset' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'ff_fieldset_settings_group' );
				do_settings_sections( 'ff-fieldset-settings' );
				submit_button();
				?>
			</form>
			<hr>
			<div class="notice-inline">
				<p><strong><?php echo esc_html__( 'Template Override', 'ff-fieldset' ); ?></strong>
				<?php echo esc_html__( 'updates Formidable’s default templates for radio/checkbox fields, in the builder’s “Customize HTML” adds fieldset/legend. It applies to all forms. This does not retrofit existing fields on the frontend.', 'ff-fieldset' ); ?></p>
			</div>
		</div>
		<?php
	}

	public function field_visible_legends() {
		$checked = ! empty( $this->opts['visible_legends'] ) ? 'checked' : '';
		echo '<label><input type="checkbox" name="' . esc_attr( self::OPTION_KEY ) . '[visible_legends]" value="1" ' . $checked . '> ' . esc_html__( 'Show legends visually (instead of screen-reader only)', 'ff-fieldset' ) . '</label>';
	}

	public function field_strip_roles() {
		$checked = ! empty( $this->opts['strip_roles'] ) ? 'checked' : '';
		echo '<label><input type="checkbox" name="' . esc_attr( self::OPTION_KEY ) . '[strip_roles]" value="1" ' . $checked . '> ' . sprintf(
			/* translators: 1: role="group" 2: role="radiogroup" */
			esc_html__( 'Remove %1$s / %2$s', 'ff-fieldset' ),
			'<code>role="group"</code>',
			'<code>role="radiogroup"</code>'
		) . '</label>';
	}

	public function field_template_override() {
		$checked = ! empty( $this->opts['use_template_override'] ) ? 'checked' : '';
		echo '<label><input type="checkbox" name="' . esc_attr( self::OPTION_KEY ) . '[use_template_override]" value="1" ' . $checked . '> ' . wp_kses_post(
			sprintf(
				/* translators: 1: <fieldset> 2: <legend> */
				esc_html__( 'Replace Formidable’s default templates for radio/checkbox fields with a version that includes %1$s/%2$s', 'ff-fieldset' ),
				'<code>&lt;fieldset&gt;</code>',
				'<code>&lt;legend&gt;</code>'
			)
		) . '</label>';
	}

	/**
	 * Add a "Settings" shortcut link on the Plugins screen.
	 *
	 * @param array $links
	 * @return array
	 */
	public function add_settings_link( $links ) {
		$url = admin_url( 'options-general.php?page=ff-fieldset-settings' );
		$links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'ff-fieldset' ) . '</a>';
		return $links;
	}

	/**
	 * Uninstall
	 */
	public static function uninstall() {
		delete_option( self::OPTION_KEY );
	}
}

// Bootstrap instance in admin and front (front does nothing; safe to instantiate).
add_action( 'init', function () {
	new FF_Radio_Checkbox_Fieldset();
}, 20 );

/**
 * ------------------------------------------------------------------------
 * Filters
 * ------------------------------------------------------------------------
 *
 *   add_filter( 'FF_fieldset/legend_visible', '__return_false' );         // hide legends visually (SR-only)
 *   add_filter( 'FF_fieldset/keep_group_roles', '__return_true' );        // keep role="group|radiogroup"
 *   add_filter( 'FF_fieldset/include_required_label', '__return_false' ); // remove required indicator
 *   add_filter( 'FF_fieldset/use_template_override', '__return_false' );  // disable builder override entirely
 *
 * Notes:
 * - These operate at template-override time only (frm_custom_html).
 * - Defaults are driven by the plugin settings if the filters are not present.
 */

// Clean up on uninstall (must be a static method or named function — no closures).
register_uninstall_hook( __FILE__, [ 'FF_Radio_Checkbox_Fieldset', 'uninstall' ] );
