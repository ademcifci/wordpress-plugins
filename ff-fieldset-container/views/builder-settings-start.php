<?php
// Handle both object and array field formats
if ( is_object( $field ) ) {
	$legend_visible = isset( $field->field_options['legend_visible'] ) ? $field->field_options['legend_visible'] : 1;
	$field_id = $field->id;
} else {
	$legend_visible = isset( $field['legend_visible'] ) ? $field['legend_visible'] : 1;
	$field_id = $field['id'];
}
?>
<p class="frm6 frm_form_field">
	<label for="legend_visible_<?php echo esc_attr( $field_id ); ?>">
		<input type="checkbox" 
		       name="field_options[legend_visible_<?php echo esc_attr( $field_id ); ?>]" 
		       id="legend_visible_<?php echo esc_attr( $field_id ); ?>" 
		       value="1" 
		       <?php checked( $legend_visible, 1 ); ?>
		       <?php checked( $legend_visible, '1' ); ?> />
		<?php esc_html_e( 'Show legend visually', 'ff-fieldset-container' ); ?>
	</label>
	<span class="frm_help frm_icon_font frm_tooltip_icon" 
	      title="<?php esc_attr_e( 'When unchecked, the legend will be hidden visually but remain accessible to screen readers.', 'ff-fieldset-container' ); ?>" 
	      aria-label="<?php esc_attr_e( 'When unchecked, the legend will be hidden visually but remain accessible to screen readers.', 'ff-fieldset-container' ); ?>">
	</span>
</p>