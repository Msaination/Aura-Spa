<?php defined( 'ABSPATH' ) || exit; ?>

<form class="obp_change_order_form"
action=""
method="POST"
autocomplete="off"
data-order-id="<?php echo esc_attr( $order_id ); ?>"
data-vendor-id="<?php echo esc_attr( $vendor_id ); ?>"
data-business-id="<?php echo esc_attr( $business_id ); ?>"
data-discard-title="<?php esc_attr_e( 'Discard booking?', 'ovabookpro' ); ?>"
data-discard-message="<?php esc_attr_e( 'Are you sure to want to abort the booking process? Unsaved Changes will be lost', 'ovabookpro' ); ?>"
data-discard-continue="<?php esc_attr_e( 'Continue booking', 'ovabookpro' ); ?>"
data-discard-agree="<?php esc_attr_e( 'Yes, discard', 'ovabookpro' ); ?>"
data-nonce="<?php echo esc_attr( wp_create_nonce( 'obp_order_nonce' ) ); ?>">

	<?php do_action( 'obp_change_order_form_content', $args ); ?>
	
</form>