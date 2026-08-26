<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp-order-wrap">

	<div class="obp-order-container">
		<?php do_action( 'obp_booking_form_order_content', $args ); ?>
	</div>

	<?php if ( count( $service_ids ) > 0 ): ?>
		<a href="#" class="obp_add_another_service">
			<span class="plus"><i class="bookproicon-plus-icon"></i></span>
			<?php esc_html_e( 'Add another services', 'ovabookpro' ); ?>
		</a>
	<?php endif; ?>

</div> <!-- .obp-order-container -->