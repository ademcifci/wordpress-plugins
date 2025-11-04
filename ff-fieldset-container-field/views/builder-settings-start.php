<?php
// Handle both object and array field formats
if ( is_object( $field ) ) {
    $legend_visible = isset( $field->field_options['legend_visible'] ) ? $field->field_options['legend_visible'] : 1;
    $legend_heading = isset( $field->field_options['legend_heading'] ) ? $field->field_options['legend_heading'] : '';
    $field_id = $field->id;
} else {
    $legend_visible = isset( $field['legend_visible'] ) ? $field['legend_visible'] : 1;
    $legend_heading = isset( $field['legend_heading'] ) ? $field['legend_heading'] : '';
    $field_id = $field['id'];
}
?>

<p class="frm-mt-xs">
    <label for="legend_heading_<?php echo esc_attr( $field_id ); ?>">
        <?php esc_html_e( 'Legend heading level', 'ff-fieldset-container-field' ); ?>
	<span class="frm_help frm_icon_font frm_tooltip_icon"
          title="<?php esc_attr_e( 'Optionally wrap the legend text in a heading level (H2–H6). Choose None to output plain legend text.', 'ff-fieldset-container-field' ); ?>"
          aria-label="<?php esc_attr_e( 'Optionally wrap the legend text in a heading level (H2–H6). Choose None to output plain legend text.', 'ff-fieldset-container-field' ); ?>">
    </span>
    </label>
    <select name="field_options[legend_heading_<?php echo esc_attr( $field_id ); ?>]" id="legend_heading_<?php echo esc_attr( $field_id ); ?>">
        <?php
        $options = array( '' => __( 'None', 'ff-fieldset-container-field' ), 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6' );
        foreach ( $options as $val => $label ) {
            echo '<option value="' . esc_attr( $val ) . '"' . selected( $legend_heading, $val, false ) . '>' . esc_html( $label ) . '</option>';
        }
        ?>
    </select>
    <input type="hidden" name="field_options[legend_heading]" value="<?php echo esc_attr( $legend_heading ); ?>" />
</p>

<p class="frm-mt-xs">
    <label for="legend_visible_<?php echo esc_attr( $field_id ); ?>">
        <input type="checkbox"
               name="field_options[legend_visible_<?php echo esc_attr( $field_id ); ?>]"
               id="legend_visible_<?php echo esc_attr( $field_id ); ?>"
               value="1"
               <?php checked( $legend_visible, 1 ); ?>
               <?php checked( $legend_visible, '1' ); ?> />
        <?php esc_html_e( 'Show legend visually', 'ff-fieldset-container-field' ); ?>
	<span class="frm_help frm_icon_font frm_tooltip_icon"
          title="<?php esc_attr_e( 'When unchecked, the legend will be hidden visually but remain accessible to screen readers.', 'ff-fieldset-container-field' ); ?>"
          aria-label="<?php esc_attr_e( 'When unchecked, the legend will be hidden visually but remain accessible to screen readers.', 'ff-fieldset-container-field' ); ?>">
    </span>
    </label>
    <input type="hidden" name="field_options[legend_visible]" value="<?php echo esc_attr( $legend_visible ); ?>" />
<br/>
</p>

<script>
(function(){
  function renamePrimaryLabel(fieldId){
    try {
      // Try to find the Name input by common id or name patterns
      var nameInput = document.querySelector('#field_name_' + fieldId + ', input[name="field_options[name_' + fieldId + ']"]');
      var label = null;
      if (nameInput && nameInput.id) {
        label = document.querySelector('label[for="' + nameInput.id + '"]');
      }
      if (!label) {
        // Fallback: the first primary label in the settings block
        var candidates = document.querySelectorAll('.frm_field_form .frm_primary_label label, .frm_primary_label label');
        if (candidates && candidates.length) { label = candidates[0]; }
      }
      if (label) {
        label.textContent = 'Legend Text';
      }
    } catch(e) { /* no-op */ }
  }
  // Run once now and also after a tick to allow builder to render
  renamePrimaryLabel(<?php echo json_encode( $field_id ); ?>);
  setTimeout(function(){ renamePrimaryLabel(<?php echo json_encode( $field_id ); ?>); }, 100);
})();
</script>

