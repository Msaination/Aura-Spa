<?php defined( 'ABSPATH' ) || exit;

$multiple = false;

if ( isset( $field['atts'], $field['atts']['multiple'] ) && $field['atts']['multiple'] ) $multiple = true;

?>
<select id="<?php obp_esc_attr( $this->get_field_id( $field['name'] ) ); ?>" name="<?php obp_esc_attr( $this->get_field_name( $field['name'] ) . ( $multiple ? '[]' : '' ) ); ?>" <?php echo $this->render_atts( $field['atts'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    <?php if ( obp_array_exists( $field['options'] ) ):
    	$current_val = $this->get( $field['name'] ) ? $this->get( $field['name'] ) : '';

        if ( $current_val == '' && isset( $field['default'] ) ) {
            $current_val = $field['default'];
        }

    	foreach ( $field['options'] as $val => $label ): ?>
            <?php if ( $multiple ):
            	if ( ! is_array( $current_val ) ) $current_val = array();
            ?>
    			<!--Multi select-->
               <option value="<?php obp_esc_attr( $val ); ?>"<?php echo in_array( $val, $current_val ) ? ' selected' : ''; ?>>
               		<?php echo esc_html( $label ); ?>
               	</option>
            <?php else: ?>
             	<option value="<?php obp_esc_attr( $val ); ?>"<?php selected( $val, $current_val ); ?>>
             		<?php echo esc_html( $label ); ?>
             	</option>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</select>