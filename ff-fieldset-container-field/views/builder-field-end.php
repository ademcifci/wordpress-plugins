<div class="frm_fieldset_preview frm_fieldset_end_preview">
	<div class="frm_fieldset_icon" aria-hidden="true">&RightAngleBracket;&RightAngleBracket;</div>
	<div class="frm_fieldset_info">
		<strong><?php echo esc_html__( 'Fieldset End', 'ff-fieldset-container-field' ); ?></strong>
		<?php if ( ! empty( $field['description'] ) ) : ?>
			<div class="frm_description" style="margin-top: 5px;">
				<?php echo wp_kses_post( $field['description'] ); ?>
			</div>
		<?php endif; ?>
		<div class="frm_fieldset_instruction">
			<?php echo esc_html__( 'This closes the fieldset. All fields between Start and End will be grouped.', 'ff-fieldset-container-field' ); ?>
		</div>
	</div>
</div>

<style>
/* Match Start style but clearly denote the closing marker */
.frm_fieldset_preview {
  padding: 16px;
  background: #f7fbff;
  border: 2px dashed #0073aa;
  border-radius: 4px;
  margin: 10px 0;
}
.frm_fieldset_end_preview {
  border-top: 3px solid #0073aa;
  border-radius: 0 0 4px 4px;
  background: #f7fbff;
  box-shadow: inset 0 -6px 0 rgba(0,115,170,0.08);
}
.frm_fieldset_icon {
  font-size: 28px;
  line-height: 1;
  color: #0073aa;
  float: left;
  margin-right: 10px;
}
.frm_fieldset_info { overflow: hidden; }
.frm_fieldset_info > strong { color: #0073aa; }
.frm_fieldset_instruction {
  margin-top: 8px;
  font-size: 12px;
  color: #333;
  font-style: italic;
}
</style>

