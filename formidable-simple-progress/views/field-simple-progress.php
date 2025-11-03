<?php
/**
 * Builder preview for Simple Progress field
 *
 * @var Frm_Simple_Progress_Field $this
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'wp_enqueue_style' ) ) {
    wp_enqueue_style( 'frm-simple-progress', FRM_SP_PLUGIN_URL . 'assets/css/simple-progress.css', array(), FRM_SP_VERSION );
}
// Enqueue builder preview script (admin only context here).
if ( function_exists( 'wp_enqueue_script' ) ) {
    wp_enqueue_script( 'frm-simple-progress-builder', FRM_SP_PLUGIN_URL . 'assets/js/builder-preview.js', array( 'jquery' ), FRM_SP_VERSION, true );
}

$step_label = isset( $field['step_label'] ) && $field['step_label'] !== '' ? $field['step_label'] : __( 'Step', 'formidable-simple-progress' );
$of_label   = __( 'of', 'formidable-simple-progress' );
?>
<div class="frm-simple-progress frm-sp-preview" id="frm-sp-preview-<?php echo esc_attr( $field['id'] ); ?>" data-field-id="<?php echo esc_attr( $field['id'] ); ?>" data-default-step="<?php echo esc_attr( $step_label ); ?>" data-of-label="<?php echo esc_attr( $of_label ); ?>">
    <span class="frm-simple-progress-badge"><?php echo esc_html( sprintf( '%1$s %2$d %3$s %4$d', $step_label, 1, $of_label, 3 ) ); ?></span>
    <div class="frm_description" style="margin-top:6px;">
        <?php esc_html_e( 'Automatically shows current page and total pages.', 'formidable-simple-progress' ); ?>
    </div>
</div>

