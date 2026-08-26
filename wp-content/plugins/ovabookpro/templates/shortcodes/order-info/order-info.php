<?php defined( 'ABSPATH' ) || exit; ?>


<table class="obp_order">
	<tr>
		<th>
			<?php esc_html_e( 'Name', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $order->get_customer_name() ); ?>
		</td>
	</tr>

	<tr>
		<th>
			<?php esc_html_e( 'Email', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $order->get_customer_email() ); ?>
		</td>
	</tr>

	<tr>
		<th>
			<?php esc_html_e( 'Phone', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $order->get_customer_phone() ); ?>
		</td>
	</tr>

	<tr>
		<th>
			<?php esc_html_e( 'Note', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $order->get_customer_note() ); ?>
		</td>
	</tr>

	<tr>
		<th>
			<?php esc_html_e( 'Date Created', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $order->get_date_created() ); ?>
		</td>
	</tr>


	<?php do_action( 'obp_order_info_middle', $order ); ?>

	<?php if ( apply_filters('obp_vendor_plugin_activated', false) == true ): ?>
		<tr>
			<th>
				<?php esc_html_e( 'Vendor', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo esc_html( $order->get_vendor_name() ); ?>
			</td>
		</tr>
	<?php endif; ?>

	<tr>
		<th>
			<?php esc_html_e( 'Business', 'ovabookpro' ); ?>
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
				<?php esc_html_e( 'Payment Gateway', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo esc_html( $order->get_payment_gateway() ); ?>
			</td>
		</tr>
	<?php endif; ?>

	<tr>
		<th>
			<?php esc_html_e( 'Payment Method', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $order->get_payment_method() ); ?>
		</td>
	</tr>

	<?php if ( $order->get_coupon_code() ): ?>
	<tr>
		<th>
			<?php esc_html_e( 'Coupon Code', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $order->get_coupon_code() ); ?>
		</td>
	</tr>
	<?php endif; ?>

	<tr>
		<th>
			<?php esc_html_e( 'Booking Status', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $order->get_order_status_translate() ); ?>
		</td>
	</tr>

	<tr>
		<th>
			<?php esc_html_e( 'Booking Items', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php obp_get_template( 'manage-booking/order-items.php', array( 'order' => $order ) ); ?>
		</td>
	</tr>

</table>

<div class="obp_total_wrap">
	<table class="obp_total_table">
		<tr>
			<th>
				<?php esc_html_e( 'Subtotal', 'ovabookpro' ); ?>
			</th>
			<th>
				<?php echo wp_kses_post( obp_get_price_html( $order->get_subtotal() ) ); ?>
			</th>
		</tr>

		<?php if ( $order->get_discount() ): ?>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Discount', 'ovabookpro' ); ?>
				</th>
				<th>
					<?php echo '-'.wp_kses_post( obp_get_price_html( $order->get_discount() ) ); ?>
				</th>
			</tr>
		<?php endif; ?>

		<tr>
			<th>
				<?php esc_html_e( 'System Fee', 'ovabookpro' ); ?>
			</th>
			<th>
				<?php echo wp_kses_post( obp_get_price_html( $order->get_system_fee() ) ); ?>
			</th>
		</tr>

		<tr>
			<th>
				<?php esc_html_e( 'Tax', 'ovabookpro' ); ?>
			</th>
			<th>
				<?php echo wp_kses_post( obp_get_price_html( $order->get_tax_amount() ) ); ?>
			</th>
		</tr>

		<tr>
			<th>
				<?php esc_html_e( 'Total', 'ovabookpro' ); ?>
			</th>
			<th>
				<?php echo wp_kses_post( obp_show_booking_total( $order->get_total(), $order->has_varies() ) ); ?>
			</th>
		</tr>
	</table>
</div>

<div class="obp_order_footer">
	<a href="#" class="obp_download_order obp_button" data-order-id="<?php echo esc_attr( $order->get_id() ); ?>">
		<?php esc_html_e( 'Download Invoice', 'ovabookpro' ); ?>
	</a>
	<?php do_action( 'obp_order_info_footer', $order ); ?>
</div>