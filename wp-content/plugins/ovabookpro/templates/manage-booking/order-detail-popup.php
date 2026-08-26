<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp_order_detail_popup_wrapper">
	<table>
		<tr>
			<th>
				<?php echo esc_html__( 'ID', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo esc_html( $order->get_id() ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php echo esc_html__( 'Name', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo esc_html( $order->get_customer_name() ); ?>
			</td>
		</tr>

		<tr>
			<th>
				<?php echo esc_html__( 'Email', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo esc_html( $order->get_customer_email() ); ?>
			</td>
		</tr>

		<tr>
			<th>
				<?php echo esc_html__( 'Phone', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo esc_html( $order->get_customer_phone() ); ?>
			</td>
		</tr>

		<tr>
			<th>
				<?php echo esc_html__( 'Note', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo esc_html( $order->get_customer_note() ); ?>
			</td>
		</tr>

		<tr>
			<th>
				<?php echo esc_html__( 'Date Created', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo esc_html( $order->get_date_created() ); ?>
			</td>
		</tr>


		<?php do_action( 'obp_order_detail_middle', $order ); ?>

		<?php if ( apply_filters('obp_vendor_plugin_activated', false) == true ): ?>
			<tr>
				<th>
					<?php echo esc_html__( 'Vendor', 'ovabookpro' ); ?>
				</th>
				<td>
					<?php echo esc_html( $order->get_vendor_name() ); ?>
				</td>
			</tr>
		<?php endif; ?>

		<tr>
			<th>
				<?php echo esc_html__( 'Business', 'ovabookpro' ); ?>
			</th>
			<td>
				<a href="<?php echo esc_url( $order->business_permalink() ); ?>">
					<?php echo esc_html( $order->get_business_name() ); ?>
				</a>
			</td>
		</tr>

		<?php if ( $order->get_payment_gateway() ): ?>
			<tr>
				<th>
					<?php echo esc_html__( 'Payment Gateway', 'ovabookpro' ); ?>
				</th>
				<td>
					<?php echo esc_html( $order->get_payment_gateway() ); ?>
				</td>
			</tr>
		<?php endif; ?>

		<tr>
			<th>
				<?php echo esc_html__( 'Payment Method', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo esc_html( $order->get_payment_method() ); ?>
			</td>
		</tr>

		<?php if ( $order->get_coupon_code() ): ?>
		<tr>
			<th>
				<?php echo esc_html__( 'Coupon Code', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo esc_html( $order->get_coupon_code() ); ?>
			</td>
		</tr>
		<?php endif; ?>

		<tr>
			<th>
				<?php echo esc_html__( 'Booking Status', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo esc_html( $order->get_order_status_translate() ); ?>
			</td>
		</tr>

		<tr>
			<th>
				<?php echo esc_html__( 'Booking Items', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php obp_get_template( 'manage-booking/order-items.php', array( 'order' => $order ) ); ?>
			</td>
		</tr>

		<tr>
			<td colspan="2">
				<div class="obp_total_wrap">

					<?php do_action( 'obp_order_detail_bottom', $order ); ?>

					<table class="obp_total_table">

						<tr>
							<th>
								<?php echo esc_html__( 'Subtotal', 'ovabookpro' ); ?>
							</th>
							<th>
								<?php echo wp_kses_post( obp_get_price_html( $order->get_subtotal() ) ); ?>
							</th>
						</tr>

						<?php if ( $order->get_discount() ): ?>
							<tr>
								<th scope="row">
									<?php echo esc_html__( 'Discount', 'ovabookpro' ); ?>
								</th>
								<th>
									<?php echo '-'.wp_kses_post( obp_get_price_html( $order->get_discount() ) ); ?>
								</th>
							</tr>
						<?php endif; ?>

						<tr>
							<th>
								<?php echo esc_html__( 'System Fee', 'ovabookpro' ); ?>
							</th>
							<th>
								<?php echo wp_kses_post( obp_get_price_html( $order->get_system_fee() ) ); ?>
							</th>
						</tr>

						<tr>
							<th>
								<?php echo esc_html__( 'Tax', 'ovabookpro' ); ?>
							</th>
							<th>
								<?php echo wp_kses_post( obp_get_price_html( $order->get_tax_amount() ) ); ?>
							</th>
						</tr>

						<tr>
							<th>
								<?php echo esc_html__( 'Total', 'ovabookpro' ); ?>
							</th>
							<th>
								<?php echo wp_kses_post( obp_show_booking_total( $order->get_total(), $order->has_varies() ) ); ?>
							</th>
						</tr>

					</table>
				</div>
			</td>
		</tr>

	</table>
</div>