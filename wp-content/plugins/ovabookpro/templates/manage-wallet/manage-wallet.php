<?php defined( 'ABSPATH' ) || exit; ?>


<h1 class="obp-title">
	<?php esc_html_e( 'My Wallet', 'ovabookpro' ); ?>	
</h1>

<div class="obp-content obp-content-manage-wallet"
data-nonce="<?php echo esc_attr( wp_create_nonce('obp_manage_wallet') ); ?>">

	<?php do_action( 'obp_manage_wallet_main_content', $args ); ?>

</div>