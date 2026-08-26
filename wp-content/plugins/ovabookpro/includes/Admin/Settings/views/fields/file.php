<?php defined( 'ABSPATH' ) || exit;

$file_id = $this->get( $field['name'], $field['default'] );
$filename = '';

if ( $file_id ) {
	$filename = basename( get_attached_file( $file_id ) );
}

?>


<div class="setting_file_wrap">
	<input type="hidden" name="<?php obp_esc_attr( $this->get_field_name( $field['name'] ) ); ?>"
	class="file_input"
	value="<?php echo esc_attr( $file_id ) ?>" />
	<a href="#" class="button button-primary obp_setting_file"
	data-uploader_button_text="<?php esc_attr_e( 'Upload file', 'ovabookpro' ); ?>"
	data-uploader_title="<?php esc_attr_e( 'Choose file', 'ovabookpro' ); ?>">
		<?php esc_html_e( 'Upload file', 'ovabookpro' ); ?>
	</a>
	<div class="file_item">
		<?php if ( $filename ): ?>
			<span class="file_name"><?php echo esc_html( $filename ); ?></span>
			<span class="obp_remove_file"><span class="dashicons dashicons-no-alt"></span></span>
		<?php endif; ?>
	</div>
</div>