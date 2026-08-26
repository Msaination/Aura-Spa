<?php defined( 'ABSPATH' ) || exit;
		
	$default = array();

	if ( ! $this->get_options() ) {
		$default = $field['default'];
	}

	foreach ( $field['options'] as $key => $label ) {
		$checked = in_array( $key, $this->get( $field['name'], $default ) ) ? true : false;
?>
		<div class="obp-checkbox-checkmark">
			<input
				type="checkbox"
				id="<?php obp_esc_attr( $this->get_field_id( $field['name'].$key ) ); ?>"
				name="<?php obp_esc_attr( $this->get_field_name( $field['name'] ).'[]' ); ?>"
				value="<?php echo esc_attr( $key ); ?>" <?php checked( $checked, true ); ?>
				<?php echo $this->render_atts( $field['atts'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			/>
			<span class="obp-checkmark"></span>
			<span class="checkbox-label"><?php echo esc_html( $label ); ?></span>
		</div>
<?php }