<?php defined( 'ABSPATH' ) || exit;

$classes = isset( $field['atts']['class'] ) ? $field['atts']['class'] : '';

$settings  = array(
	'textarea_name' => $this->get_field_name( $field['name'] ),
	'textarea_rows' => 10,
	'editor_height' => 230,
	'wpautop' 		=> false,
	'quicktags' 	=> true,
	'editor_class' 	=> $classes,
);
$id = $this->option_name.'_'.$field['name'];
$content = $this->get( $field['name'], $field['default'] );
wp_editor( wpautop( $content ), $id, $settings );
?>