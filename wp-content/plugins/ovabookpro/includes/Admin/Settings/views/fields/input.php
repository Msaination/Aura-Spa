<?php defined( 'ABSPATH' ) || exit; ?>
<?php if ( isset( $field['before'] ) && $field['before'] === 'line' ): ?>
	<span class="obp-line"></span>
<?php endif; ?>
<input
	name="<?php obp_esc_attr( $this->get_field_name( $field['name'] ) ); ?>"
	id="<?php obp_esc_attr( $this->get_field_id( $field['name'] ) ); ?>"
	value="<?php obp_esc_attr( $this->get( $field['name'], $field['default'] ) ); ?>"
	<?php echo $this->render_atts( $field['atts'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
/>
<?php if ( isset( $field['unit'] ) && $field['unit'] ): ?>
	<span class="obp-unit"><?php obp_esc_html( $field['unit'] ); ?></span>
<?php endif; ?>