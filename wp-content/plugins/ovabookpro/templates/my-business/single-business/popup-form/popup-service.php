<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp_booking_head">
	<a href="#" class="obp_booking_service_close"><i class="flaticon bookproicon-left"></i></a>
</div>
<div class="obp_booking_service_container"
data-nonce="<?php echo esc_attr( wp_create_nonce( 'obp_booking_another_service' ) ); ?>"
data-business-id="<?php echo esc_attr( $business_id ); ?>"
data-vendor-id="<?php echo esc_attr( $vendor_id ); ?>">
		
	<div class="obp_booking_search_service">
		<i class="flaticon bookproicon-search"></i>
		<input type="text" value="" id="obp_search_service" placeholder="<?php esc_attr_e( 'Search for service', 'ovabookpro' ); ?>" />
	</div>

	<div class="obp_booking_services">
		<?php do_action( 'obp_booking_services_content', $args ); ?>
	</div>
</div>