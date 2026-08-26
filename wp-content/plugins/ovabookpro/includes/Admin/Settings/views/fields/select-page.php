<?php defined( 'ABSPATH' ) || exit;

$multiple = false;
if ( isset( $field['atts'], $field['atts']['multiple'] ) && $field['atts']['multiple'] ) $multiple = true;

// Get page ids
$page_ids = obp_get_all_page_ids();

?>
<select id="<?php obp_esc_attr( $this->get_field_id( $field['name'] ) ); ?>" name="<?php obp_esc_attr( $this->get_field_name( $field['name'] ) . ( $multiple ? '[]' : '' ) ); ?>" <?php echo $this->render_atts( $field['atts'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<option value=""><?php esc_html_e( 'Choose a page', 'ovabookpro' ); ?></option>
    <?php if ( obp_array_exists( $page_ids ) ):
    	$current_page_id = $this->get( $field['name'] ) ? $this->get( $field['name'] ) : '';

    	foreach ( $page_ids as $page_id ):
            if ( $current_page_id == '' && isset( $field['default'] ) ) {
            	$current_page_id = $field['default'];
            }

            // Page title
            $page_title = get_the_title( $page_id );
        ?>
            <?php if ( $multiple ):
            	if ( ! is_array( $current_page_id ) ) $current_page_id = array();
            ?>
    			<!--Multi select-->
               <option value="<?php obp_esc_attr( $page_id ); ?>"<?php echo in_array( $page_id, $current_page_id ) ? ' selected' : ''; ?>>
               		<?php echo esc_html( $page_title ); ?>
               	</option>
            <?php else: ?>
             	<option value="<?php obp_esc_attr( $page_id ); ?>"<?php selected( $page_id, $current_page_id ); ?>>
             		<?php echo esc_html( $page_title ); ?>
             	</option>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</select>