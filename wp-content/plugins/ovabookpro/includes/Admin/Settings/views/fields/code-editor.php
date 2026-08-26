<?php defined( 'ABSPATH' ) || exit;
$code_value = $this->get( $field['name'], $field['default'] );
?>

<textarea rows="5" cols="55" name="<?php obp_esc_attr( $this->get_field_name( $field['name'] ) ); ?>" id="obp_code_editor"><?php echo esc_textarea( $code_value ); ?></textarea>