<?php defined( 'ABSPATH' ) || exit;?>

<h3 class="accordion-title">
	<?php esc_html_e( 'Update Profile', 'ovabookpro' ); ?>
	<i class="bookproicon-down"></i>
</h3>
<div class="accordion-panel">
	<div class="obp_update_profile_wrapper">
		<?php do_action( 'obp_update_profile_content' ); ?>
	</div>
</div>