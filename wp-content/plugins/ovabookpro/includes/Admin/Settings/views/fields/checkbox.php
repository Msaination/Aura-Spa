<?php defined( 'ABSPATH' ) || exit;

$default = '';

if ( ! $this->get_options() ) {
	$default = $field['default'];
}

?>

<div class="obp-checkbox-checkmark">
	<input
		id="<?php obp_esc_attr( $this->get_field_id( $field['name'] ) ); ?>"
		name="<?php obp_esc_attr( $this->get_field_name( $field['name'] ) ); ?>"
		value="1"
		type="checkbox"
		<?php echo $this->render_atts( $field['atts'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php checked( $this->get( $field['name'], $default ), 1 ); ?>
	/>
	<span class="obp-checkmark"></span>
</div>