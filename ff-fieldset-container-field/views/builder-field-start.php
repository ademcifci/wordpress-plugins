<div class="frm_fieldset_preview frm_fieldset_start_preview">
	<div class="frm_fieldset_icon" aria-hidden="true">&LeftAngleBracket;&LeftAngleBracket;</div>
	<div class="frm_fieldset_info">
		<strong><?php echo esc_html__( 'Fieldset Start', 'ff-fieldset-container-field' ); ?></strong>
		<?php if ( ! empty( $field['name'] ) ) : ?>
			<div class="frm_fieldset_legend_preview">
				<?php echo esc_html__( 'Legend:', 'ff-fieldset-container-field' ); ?> 
				<em><?php echo esc_html( $field['name'] ); ?></em>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $field['description'] ) ) : ?>
			<div class="frm_description" style="margin-top: 5px;">
				<?php echo wp_kses_post( $field['description'] ); ?>
			</div>
		<?php endif; ?>
		<div class="frm_fieldset_instruction">
			<?php echo esc_html__( 'Drag fields below this to include them in the fieldset.', 'ff-fieldset-container-field' ); ?>
		</div>
	</div>
</div>

<style>
/* Make fieldset previews stand out clearly inside the builder wrapper */
.frm_fieldset_preview {
  padding: 16px;
  background: #eaf6ff; /* higher contrast than before */
  border: 2px dashed #0073aa;
  border-radius: 4px;
  margin: 10px 0;
}
.frm_fieldset_start_preview {
  border-bottom: 3px solid #0073aa;
  border-radius: 4px 4px 0 0;
  box-shadow: inset 0 6px 0 rgba(0,115,170,0.08);
}
.frm_fieldset_icon {
  font-size: 28px;
  line-height: 1;
  color: #0073aa;
  float: left;
  margin-right: 10px;
}
.frm_fieldset_info {
  overflow: hidden;
}
.frm_fieldset_info > strong {
  color: #0073aa;
}
.frm_fieldset_legend_preview {
  margin-top: 5px;
  color: #444;
}
.frm_fieldset_instruction {
  margin-top: 8px;
  font-size: 12px;
  color: #333;
  font-style: italic;
}
</style>

