<div class="frm_fieldset_preview frm_fieldset_start_preview">
	<div class="frm_fieldset_icon">📦</div>
	<div class="frm_fieldset_info">
		<strong><?php echo esc_html__( 'Fieldset Start', 'ff-fieldset-container' ); ?></strong>
		<?php if ( ! empty( $field['name'] ) ) : ?>
			<div class="frm_fieldset_legend_preview">
				<?php echo esc_html__( 'Legend:', 'ff-fieldset-container' ); ?> 
				<em><?php echo esc_html( $field['name'] ); ?></em>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $field['description'] ) ) : ?>
			<div class="frm_description" style="margin-top: 5px;">
				<?php echo wp_kses_post( $field['description'] ); ?>
			</div>
		<?php endif; ?>
		<div class="frm_fieldset_instruction">
			<?php echo esc_html__( 'Drag fields below this to include them in the fieldset.', 'ff-fieldset-container' ); ?>
		</div>
	</div>
</div>

<style>
.frm_fieldset_preview {
	padding: 15px;
	background: #f0f8ff;
	border: 2px dashed #4a90e2;
	border-radius: 4px;
	margin: 10px 0;
}
.frm_fieldset_start_preview {
	border-bottom: 2px solid #4a90e2;
	border-radius: 4px 4px 0 0;
}
.frm_fieldset_icon {
	font-size: 24px;
	float: left;
	margin-right: 10px;
}
.frm_fieldset_info {
	overflow: hidden;
}
.frm_fieldset_legend_preview {
	margin-top: 5px;
	color: #666;
}
.frm_fieldset_instruction {
	margin-top: 8px;
	font-size: 12px;
	color: #666;
	font-style: italic;
}
</style>
