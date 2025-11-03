<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$current = isset( $field['step_label'] ) && $field['step_label'] !== ''
    ? $field['step_label']
    : __( 'Step', 'formidable-simple-progress' );
?>
<p>
    <label for="frm_sp_step_label_<?php echo esc_attr( $field['id'] ); ?>">
        <?php esc_html_e( 'Step label', 'formidable-simple-progress' ); ?>
    </label>
    <input type="text"
        id="frm_sp_step_label_<?php echo esc_attr( $field['id'] ); ?>"
        name="field_options[step_label_<?php echo esc_attr( $field['id'] ); ?>]"
        value="<?php echo esc_attr( $current ); ?>"
        class="regular-text"
        placeholder="<?php echo esc_attr__( 'Step', 'formidable-simple-progress' ); ?>" />
    <span class="howto"><?php esc_html_e( 'Customize the word used before the numbers. Leave blank to use your site language.', 'formidable-simple-progress' ); ?></span>
</p>

<p>
    <label for="frm_sp_auto_inject_<?php echo esc_attr( $field['id'] ); ?>">
        <input type="checkbox"
            id="frm_sp_auto_inject_<?php echo esc_attr( $field['id'] ); ?>"
            name="field_options[sp_auto_inject_<?php echo esc_attr( $field['id'] ); ?>]"
            value="1" <?php checked( ! empty( $field['sp_auto_inject'] ) ); ?> />
        <?php esc_html_e( 'Auto-inject progress badge on all pages', 'formidable-simple-progress' ); ?>
    </label>
</p>

<p class="frm_indent_opt">
    <label for="frm_sp_inject_position_<?php echo esc_attr( $field['id'] ); ?>">
        <?php esc_html_e( 'Auto-inject position', 'formidable-simple-progress' ); ?>
    </label>
    <select id="frm_sp_inject_position_<?php echo esc_attr( $field['id'] ); ?>"
        name="field_options[sp_inject_position_<?php echo esc_attr( $field['id'] ); ?>]">
        <option value="" <?php selected( empty( $field['sp_inject_position'] ) ); ?>><?php esc_html_e( 'Below form title (default)', 'formidable-simple-progress' ); ?></option>
        <option value="above_title" <?php selected( ( $field['sp_inject_position'] ?? '' ) === 'above_title' ); ?>><?php esc_html_e( 'Above form title', 'formidable-simple-progress' ); ?></option>
        <option value="before_submit" <?php selected( ( $field['sp_inject_position'] ?? '' ) === 'before_submit' ); ?>><?php esc_html_e( 'Above submit button', 'formidable-simple-progress' ); ?></option>
        <option value="after_submit" <?php selected( ( $field['sp_inject_position'] ?? '' ) === 'after_submit' ); ?>><?php esc_html_e( 'Below submit button', 'formidable-simple-progress' ); ?></option>
    </select>
    <span class="howto"><?php esc_html_e( 'Toggle on to place the badge automatically once per page. Leave off to place it manually as a field.', 'formidable-simple-progress' ); ?></span>
    </p>

<p>
    <label for="frm_sp_live_region_<?php echo esc_attr( $field['id'] ); ?>">
        <input type="checkbox"
            id="frm_sp_live_region_<?php echo esc_attr( $field['id'] ); ?>"
            name="field_options[sp_live_region_<?php echo esc_attr( $field['id'] ); ?>]"
            value="1" <?php checked( ! empty( $field['sp_live_region'] ) ); ?> />
        <?php esc_html_e( 'Make ARIA live region (role="status")', 'formidable-simple-progress' ); ?>
    </label>
</p>
