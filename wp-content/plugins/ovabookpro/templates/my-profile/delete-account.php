<?php defined( 'ABSPATH' ) || exit;?>

<h3 class="accordion-title">
	<?php esc_html_e( 'Delete Account', 'ovabookpro' ); ?>
	<i class="bookproicon-down"></i>
</h3>
<div class="accordion-panel">
	<div class="obp_delete_account_wrapper">
		<?php do_action( 'obp_delete_account_content' ); ?>
	</div>
</div>