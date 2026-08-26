<?php defined( 'ABSPATH' ) || exit;

?>

<form class="obp_edit_type_form" action="" method="POST" novalidate autocomplete="off">
	<input type="hidden" name="type_id" value="<?php echo esc_attr( $type->get_id() ); ?>" />
	<a href="#" class="obp_close_edit_type">
		<i class="flaticon bookproicon-close"></i>
	</a>
	<div class="obp_field_row">
		<input type="text" name="name" id="name_type" value="<?php echo esc_attr( $type->get_name() ); ?>" placeholder="<?php echo esc_attr__( 'Name', 'ovabookpro' ); ?>" />

		<button type="submit" class="obp_button">
			<?php echo esc_html__( 'Update Type', 'ovabookpro' ); ?>
		</button>
	</div>
</form>