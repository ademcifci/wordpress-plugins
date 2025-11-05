<?php
/**
 * Builder preview for Duet Date field
 * Variables available: $field (array with html_id, html_name, default_value, placeholder)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$id    = isset( $field['html_id'] ) ? $field['html_id'] : '';
$name  = isset( $field['html_name'] ) ? $field['html_name'] : '';
$value = isset( $field['default_value'] ) && ! is_array( $field['default_value'] ) ? $field['default_value'] : '';
$ph    = isset( $field['placeholder'] ) && ! is_array( $field['placeholder'] ) ? $field['placeholder'] : 'YYYY-MM-DD';
?>
<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $ph ); ?>" class="frm_duet_date" />

