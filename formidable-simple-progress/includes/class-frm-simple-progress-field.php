<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Simple Progress field for Formidable Forms
 */
class Frm_Simple_Progress_Field extends FrmFieldType {

    protected $type = 'simple_progress';
    protected $has_for_label = false;

    public function default_html() {
        $default_html = <<<HTML
<div id="frm_field_[id]_container" class="frm_form_field form-field">
    [input]
    [if description]<div class="frm_description" id="frm_desc_field_[key]">[description]</div>[/if description]
</div>
HTML;
        return $default_html;
    }

    protected function field_settings_for_type() {
        $settings = array(
            'required'       => false,
            'visibility'     => false,
            'label_position' => false,
            'default'        => false,
            'options'        => true,
        );
        if ( class_exists( 'FrmProFieldsHelper' ) ) {
            FrmProFieldsHelper::fill_default_field_display( $settings );
        }
        return $settings;
    }

    protected function extra_field_opts() {
        return array(
            'label'      => 'none',
            'step_label' => __( 'Step', 'formidable-simple-progress' ),
            'sp_auto_inject'  => 0,
            'sp_inject_position' => '',
            'sp_live_region' => 0,
        );
    }

    protected function include_form_builder_file() {
        return FRM_SP_PLUGIN_DIR . 'views/field-simple-progress.php';
    }

    /**
     * Add a simple setting to customize the Step label in the primary options section
     * so it’s visible for non-choice fields.
     *
     * @param array $args { 'field','display','values' }
     */
    public function show_primary_options( $args ) {
        $field = $args['field'];
        include FRM_SP_PLUGIN_DIR . 'views/settings-simple-progress.php';
        parent::show_primary_options( $args );
    }

    public function front_field_input( $args, $shortcode_atts ) {
        // Find the form object.
        $form = isset( $args['form'] ) && is_object( $args['form'] ) ? $args['form'] : null;
        if ( ! $form ) {
            $form_id = 0;
            if ( function_exists( 'FrmAppHelper' ) ) {
                $form_id = FrmAppHelper::get_post_param( 'form_id', '', 'absint' );
            }
            if ( ! $form_id && isset( $this->field['form_id'] ) ) {
                $form_id = absint( $this->field['form_id'] );
            }
            if ( $form_id && class_exists( 'FrmForm' ) ) {
                $form = FrmForm::getOne( $form_id );
            }
        }

        // Default to single step when pagination is unavailable.
        $current = 1;
        $total   = 1;

        if ( $form && class_exists( 'FrmProPageField' ) ) {
            $pages      = FrmProPageField::get_form_pages( $form );
            $page_array = isset( $pages['page_array'] ) && is_array( $pages['page_array'] ) ? $pages['page_array'] : array();
            if ( ! empty( $page_array ) ) {
                $total = max( 1, count( $page_array ) );
                foreach ( $page_array as $page_number => $page ) {
                    if ( isset( $page['aria-disabled'] ) ) {
                        $current = $page_number; // Keys are 1-based
                        break;
                    }
                }
                if ( $current < 1 ) {
                    $current = 1;
                }
            }
        }

        // If this field is configured to auto-inject, do not render inline here.
        $auto_inject = (int) FrmField::get_option( $this->field, 'sp_auto_inject' );
        if ( $auto_inject ) {
            return '';
        }

        // Enqueue minimal CSS.
        if ( function_exists( 'wp_enqueue_style' ) ) {
            wp_enqueue_style( 'frm-simple-progress', FRM_SP_PLUGIN_URL . 'assets/css/simple-progress.css', array(), FRM_SP_VERSION );
        }

        $step_label = FrmField::get_option( $this->field, 'step_label' );
        if ( empty( $step_label ) ) {
            $step_label = __( 'Step', 'formidable-simple-progress' );
        }
        $of_label = __( 'of', 'formidable-simple-progress' );
        $label = sprintf( '%1$s %2$d %3$s %4$d', esc_html( $step_label ), absint( $current ), esc_html( $of_label ), absint( $total ) );

        $live_enabled = (int) FrmField::get_option( $this->field, 'sp_live_region' );
        $attrs = 'class="frm-simple-progress"';
        if ( $live_enabled ) {
            $attrs .= ' role="status" aria-live="polite"';
        }
        $html  = '<div ' . $attrs . '>';
        $html .= '<span class="frm-simple-progress-badge">' . esc_html( $label ) . '</span>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Register field in builder palette.
     */
    public static function register_available_field( $field_types ) {
        $field_types['simple_progress'] = array(
            'name' => __( 'Simple Progress', 'formidable-simple-progress' ),
            // Customizable icon class; styled in admin CSS.
            'icon' => 'frm_icon_font frm_sp_icon',
        );
        return $field_types;
    }
}
