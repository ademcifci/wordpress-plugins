<div class="frm_fieldset_preview frm_fieldset_end_preview">
	<div class="frm_fieldset_icon">📦</div>
	<div class="frm_fieldset_info">
		<strong><?php echo esc_html__( 'Fieldset End', 'ff-fieldset-container' ); ?></strong>
		<?php if ( ! empty( $field['description'] ) ) : ?>
			<div class="frm_description" style="margin-top: 5px;">
				<?php echo wp_kses_post( $field['description'] ); ?>
			</div>
		<?php endif; ?>
		<div class="frm_fieldset_instruction">
			<?php echo esc_html__( 'This closes the fieldset. All fields between Start and End will be grouped.', 'ff-fieldset-container' ); ?>
		</div>
	</div>
</div>

<style>
.frm_fieldset_end_preview {
	border-top: 2px solid #4a90e2;
	border-radius: 0 0 4px 4px;
	background: #f8f8f8;
}
</style>
