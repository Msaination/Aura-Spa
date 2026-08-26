<?php defined( 'ABSPATH' ) || exit;

global $post;
$order_id 		= $post->ID;
$order 			= new BookPro\Order\OBP_Order_Item( $order_id );
$order_items 	= BookPro\Order\OBP_Order_Meta::get_order_items( $order_id );
$start_time 	= absint( $order->get_service_start_date_earliest() );

$settings 		= BookPro\OVABookPro::instance()->settings;

// Check Cancel Order
$allow_cancel 			= $settings->cancel->get('cancel_order_enable', 'yes');
$cancel_before_minutes 	= absint( $settings->cancel->get('cancel_order_before_time', 120 ) );

$check_cancel 			= $allow_cancel == 'yes' ? true : false;
$current_time 			= absint( current_time('timestamp') );
$cancel_before_seconds 	= $cancel_before_minutes * 60;

// Check Change Order
$allow_change 			= $order->get_allow_change();

$change_before_minutes 	= absint( $settings->change_order->get('change_order_before_time', 120 ) );
$change_limited 		= absint( $settings->change_order->get('change_order_limited', 1 ) );
$check_change 			= $allow_change == 'yes' ? true : false;
$number_changed 		= $order->get_number_change_order();
$change_before_seconds 	= $change_before_minutes * 60;

$business_id = $order->get_business_id();

// Check Order Rate

$order_status 	= $order->get_order_status();

if ( $current_time - ( $start_time - $cancel_before_seconds ) >= 0 ) {
	$check_cancel = false;
}

if ( $current_time - ( $start_time - $change_before_seconds ) >= 0 || $change_limited - $number_changed < 1 ) {
	$check_change = false;
}

?>

<tr>
	<td>
		<a href="#" class="obp_order_detail_popup"
		data-tippy-content="<?php esc_attr_e( 'View Booking', 'ovabookpro' ); ?>"
		data-order-id="<?php echo esc_attr( $order_id ); ?>">
			<?php echo esc_html( $order_id ); ?>
		</a>
	</td>
	<td>
		<?php echo esc_html( $order->get_customer_name() ); ?>
	</td>
	<td>
		<?php echo wp_kses_post( obp_show_booking_total( $order->get_total(), $order->has_varies() ) ); ?>
	</td>

	<td>
		<?php if ( $order_items ): ?>
			<ul class="order-items">
				<?php foreach ( $order_items as $order_item ):
					$obj = obp_get_order_meta( $order_item );
					?>
					<li class="item">
						<p class="service_name">
							<a href="<?php echo esc_attr( get_permalink( $business_id ) ); ?>" target="_blank"><?php echo esc_html( $obj->get_service_name() ); ?></a>
							<?php if ( $obj->get_package_names() ): ?>
								<br />
								<?php echo wp_kses_post( $obj->get_package_names() ); ?>
							<?php endif; ?>
						</p>
						<p class="info">
							<span class="time">
								<?php echo esc_html( $obj->get_time() ); ?>
							</span>
							<span class="staff_name">
								<?php echo esc_html( $obj->get_staff_name() ); ?>
							</span>
						</p>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<p class="order_created_on">
			<?php // translators: %s: date time.
			echo wp_kses_post( sprintf( __( 'created at %s', 'ovabookpro' ), $order->get_date_created() ) ); ?>
		</p>
	</td>

	<td>
		<div class="order_action_wrapper">
			<input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>" />

			<a href="#" class="order_download"
			data-tippy-content="<?php esc_attr_e( 'Download Invoice', 'ovabookpro' ); ?>">
				<i class="flaticon bookproicon-download"></i>
			</a>
			<?php if ( $order_status == 'obp_processing' ): ?>

				<?php if ( $check_change === true ): ?>
					<a href="#" class="order_change"
					data-tippy-content="<?php esc_attr_e( 'Change', 'ovabookpro' ); ?>">
						<i class="flaticon bookproicon-calendar-1"></i>
					</a>
				<?php endif; ?>

				<?php if ( $check_cancel === true ): ?>
					<a href="#" class="order_cancel"
					data-tippy-content="<?php esc_attr_e( 'Cancel', 'ovabookpro' ); ?>">
						<i class="flaticon bookproicon-close"></i>
					</a>
				<?php endif; ?>

			<?php endif; ?>

			<?php do_action( 'obp_my_booking_item_action_column', $order ); ?>

		</div>
	</td>

	<td>
		<span class="order_status <?php echo esc_attr( 'order_'.$order->get_order_status() ); ?>">
			<?php echo esc_html( $order->get_order_status_translate() ); ?>	
		</span>
	</td>

</tr>