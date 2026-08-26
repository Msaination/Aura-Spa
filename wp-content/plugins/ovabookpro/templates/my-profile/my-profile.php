<?php defined( 'ABSPATH' ) || exit;?>

<h1 class="obp-title"><?php esc_html_e( 'My Profile', 'ovabookpro' ); ?></h1>

<div class="obp-content obp-content-profile">
	<div class="obp-accordion-enable">
		<?php do_action( 'obp_my_profile_main_content' ); ?>
	</div>
</div>