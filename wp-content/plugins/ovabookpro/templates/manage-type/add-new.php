<?php defined( 'ABSPATH' ) || exit; ?>

<form class="obp_add_type_form" action="" method="POST" novalidate autocomplete="off">
	<div class="obp_form_messages"></div>
	<div class="obp_field_row">
		<label for="name_type">
			<?php echo esc_html__( 'Name Type', 'ovabookpro' ); ?>
		</label>
		<input type="text" name="name" id="name_type" placeholder="<?php echo esc_attr__( 'Name', 'ovabookpro' ); ?>" />
	</div>

	<div class="obp_field_row obp_footer_form">
		
		<button type="submit" class="obp_button">
			<?php echo esc_html__( 'Add Type', 'ovabookpro' ); ?>
		</button>
	</div>
</form>