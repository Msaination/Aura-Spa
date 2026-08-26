<?php defined( 'ABSPATH' ) || exit; ?>

<?php if ( obp_array_exists( $field['options'] ) ):
	$current_val = $this->get( $field['name'] ) ? $this->get( $field['name'] ) : '';

	if ( $current_val == '' && isset( $field['default'] ) ) {
    	$current_val = $field['default'];
    }
?>
	<div class="obp-radio-checkmark inline-block">
		<?php foreach ( $field['options'] as $val => $label ): ?>
			<label class="obp-radio-label">
				<input
					type="radio"
					name="<?php obp_esc_attr( $this->get_field_name( $field['name'] ) ); ?>"
					value="<?php obp_esc_attr( $val ); ?>"
					<?php checked( $val, $current_val ); ?>
				/>
				<span class="obp-checkmark"></span>
				<?php echo esc_html( $label ); ?>
			</label>
		<?php endforeach; ?>
	</div>	
<?php endif; ?>