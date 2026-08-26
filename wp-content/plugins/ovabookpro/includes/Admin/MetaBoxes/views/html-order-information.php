<?php defined( 'ABSPATH' ) || exit;

use BookPro\Order\OBP_Order_Meta;
global $post;

$order = obp_get_order( $post->ID );
$order_status = $order->get_order_status();

?>

<div class="obp_order_info_wrap">

	<?php do_action( 'obp_admin_before_order_info', $order ); ?>


	<div class="obp_order_info">
		<table class="obp_order_info_table form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Name', 'ovabookpro' ); ?></th>
				<td>
					<?php echo esc_html( $order->get_customer_name() ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Email', 'ovabookpro' ); ?></th>
				<td>
					<?php echo esc_html( $order->get_customer_email() ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Phone', 'ovabookpro' ); ?></th>
				<td>
					<?php echo esc_html( $order->get_customer_phone() ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Note', 'ovabookpro' ); ?></th>
				<td>
					<?php echo esc_html( $order->get_customer_note() ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Date Created', 'ovabookpro' ); ?></th>
				<td>
					<?php echo esc_html( $order->get_date_created() ); ?>
				</td>
			</tr>

			<?php do_action( 'obp_admin_order_info_middle', $order ); ?>

			<tr>
				<th scope="row"><?php esc_html_e( 'Business', 'ovabookpro' ); ?></th>
				<td>
					<a href="<?php echo esc_url( $order->business_permalink() ); ?>">
						<?php echo esc_html( $order->get_business_name() ); ?>
					</a>
				</td>
			</tr>

			<?php if ( $order->get_woo_order_id() ): ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Order Woocommerce', 'ovabookpro' ); ?></th>
					<td>
						<a href="<?php echo esc_url( $order->get_woo_order_permalink() ); ?>">
							<?php echo esc_html( '#'.$order->get_woo_order_id() ); ?>
						</a>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( $order->get_payment_gateway() ): ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Payment Gateway', 'ovabookpro' ); ?></th>
					<td>
						<?php echo esc_html( $order->get_payment_gateway() ); ?>
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<th scope="row"><?php esc_html_e( 'Payment Method', 'ovabookpro' ); ?></th>
				<td>
					<?php echo esc_html( $order->get_payment_method() ); ?>
				</td>
			</tr>

			<?php if ( $order->get_coupon_code() ): ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Coupon Code', 'ovabookpro' ); ?></th>
					<td>
						<?php echo esc_html( $order->get_coupon_code() ); ?>
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<th scope="row"><?php esc_html_e( 'Booking Status', 'ovabookpro' ); ?></th>
				<td>
					<select name="<?php echo esc_attr( OBP_METABOX.'order_status' ); ?>"
					id="<?php echo esc_attr( OBP_METABOX.'order_status' ); ?>">
						<option value="obp_pending" <?php selected( $order_status, 'obp_pending' ); ?>>
							<?php esc_html_e( 'Pending', 'ovabookpro' ); ?>
						</option>
						<option value="obp_processing" <?php selected( $order_status, 'obp_processing' ); ?>>
							<?php esc_html_e( 'Processing', 'ovabookpro' ); ?>
						</option>
						<option value="obp_completed" <?php selected( $order_status, 'obp_completed' ); ?>>
							<?php esc_html_e( 'Completed', 'ovabookpro' ); ?>
						</option>
						<option value="obp_cancelled" <?php selected( $order_status, 'obp_cancelled' ); ?>>
							<?php esc_html_e( 'Cancelled', 'ovabookpro' ); ?>
						</option>
						<option value="obp_refunded" <?php selected( $order_status, 'obp_refunded' ); ?>>
							<?php esc_html_e( 'Refunded', 'ovabookpro' ); ?>
						</option>
						<option value="obp_expired" <?php selected( $order_status, 'obp_expired' ); ?>>
							<?php esc_html_e( 'Expired', 'ovabookpro' ); ?>
						</option>
					</select>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Booking Items', 'ovabookpro' ); ?></th>
				<td>
					<?php OBP()->include("Admin/MetaBoxes/views/html-order-items.php", array( 'order' => $order ) ); ?>
				</td>
			</tr>

			<tr>
				<th></th>
				<td>
					<div class="obp_total_wrap">

						<?php do_action( 'obp_admin_before_order_total', $order ); ?>

						<table class="obp_total_table">

							<tr>
								<th scope="row">
									<?php esc_html_e( 'Subtotal', 'ovabookpro' ); ?>
								</th>
								<td>
									<?php echo wp_kses_post( obp_get_price_html( $order->get_subtotal() ) ); ?>
								</td>
							</tr>

							<?php if ( $order->get_discount() ): ?>
								<tr>
									<th scope="row">
										<?php esc_html_e( 'Discount', 'ovabookpro' ); ?>
									</th>
									<td>
										<?php echo '-'.wp_kses_post( obp_get_price_html( $order->get_discount() ) ); ?>
									</td>
								</tr>
							<?php endif; ?>

							<tr>
								<th scope="row">
									<?php esc_html_e( 'System Fee', 'ovabookpro' ); ?>
								</th>
								<td>
									<?php echo wp_kses_post( obp_get_price_html( $order->get_system_fee() ) ); ?>
								</td>
							</tr>
							
							<tr>
								<th scope="row">
									<?php esc_html_e( 'Tax', 'ovabookpro' ); ?>
								</th>
								<td>
									<?php echo wp_kses_post( obp_get_price_html( $order->get_tax_amount() ) ); ?>
								</td>
							</tr>

							<tr>
								<th scope="row" class="obp_border_top">
									<?php esc_html_e( 'Total', 'ovabookpro' ); ?>
								</th>
								<td class="obp_border_top">
									<?php echo wp_kses_post( obp_show_booking_total( $order->get_total(), $order->has_varies() ) ); ?>
								</td>
							</tr>

							<?php do_action( 'obp_admin_order_info_bottom', $order ); ?>
				
					</table>
					
					</div>
				</td>
			</tr>			

		</table>
	</div>

	<?php do_action( 'obp_admin_after_order_info', $order ); ?>
</div>