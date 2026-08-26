<?php defined( 'ABSPATH' ) || exit;?>

<div class="obp_checkout_wrapper">
	<?php do_action( 'obp_before_checkout_content' ); ?>
	<div class="obp_checkout_step_1">
		<div class="obp_checkout_message"></div>
		<div class="obp_checkout_inner">
			<div class="customer_info">
				<form class="obp_checkout_form" method="POST" autocomplete="off">
					<input type="hidden" name="order_id" value="<?php echo esc_attr( $order->get_id() ); ?>" />
					<div class="obp_form_row">
						<label>
							<?php esc_html_e( 'Full Name', 'ovabookpro' ); ?>
						</label>
						<input type="text" name="full_name" placeholder="<?php esc_attr_e( 'Your name', 'ovabookpro' ); ?>" />
					</div>
					<div class="obp_form_row">
						<label>
							<?php esc_html_e( 'Phone', 'ovabookpro' ); ?>
						</label>
						<input type="text" name="phone_number" placeholder="+123456789" />
					</div>
					<div class="obp_form_row">
						<label>
							<?php esc_html_e( 'Email', 'ovabookpro' ); ?>
						</label>
						<input type="email" name="customer_email" placeholder="<?php echo esc_attr( 'mail@example.com' );; ?>" />
					</div>
					<div class="obp_form_row">
						<label>
							<?php esc_html_e( 'Note', 'ovabookpro' ); ?>
						</label>
						<textarea name="customer_note" rows="5"></textarea>
					</div>

					<?php
					$class_show_payment = $order->get_total() > 0 ? 'show_payment' : '';
					?>
					<div class="obp_form_row obp_payment_field <?php echo esc_attr( $class_show_payment ); ?>">
						<label class="obp_payment_label">
							<?php esc_html_e( 'Payment options', 'ovabookpro' ); ?>
						</label>
						<div class="obp_payment_gateways_wrapper">
						
						<?php if ( $payment_gateways ): $k = 0; ?>
							<?php foreach ( $payment_gateways as $gateway ):
								$checked = $k == 0 ? true : false;
								?>
								<label for="<?php echo esc_attr( $gateway->get_id() ); ?>" class="obp_radio inline">
									<input type="radio" id="<?php echo esc_attr( $gateway->get_id() ); ?>" name="payment" value="<?php echo esc_attr( $gateway->get_id() ); ?>" <?php checked( $checked ); ?>>
									<span class="checkmark"></span>
									<?php echo esc_html( $gateway->get_title() ); ?>
								</label>
							<?php $k++; endforeach; ?>
						<?php endif; ?>
						</div>
					</div>

					<?php do_action( 'obp_checkout_form_before_submit_button', $args ); ?>
					
					<div class="obp_form_row">
						<button type="submit" class="obp_button obp_checkout">
							<?php esc_html_e( 'Booking', 'ovabookpro' ); ?>
						</button>
					</div>
				</form>
			</div>
			<div class="order_info">
				<p class="order_subtitle">
					<?php esc_html_e( 'Your Order', 'ovabookpro' ); ?>
				</p>
				<?php if ( $order_items ): ?>
					<?php foreach ( $order_items as $key => $item ):
						$order_item = obp_get_order_meta( $item );
						$service = obp_get_service( $order_item->get_service_id() );
						?>
						<div class="order_item">
							<div class="order_item_info">
								<span class="service_name">
									<?php echo esc_html( $order_item->get_service_name() ); ?>
								</span>
								<?php if ( $order_item->get_package_names() ): ?>
									<span class="package_name">
										<?php echo wp_kses_post( $order_item->get_package_names() ); ?>
									</span>
								<?php endif; ?>
								<span class="order_time">
									<?php echo esc_html( $order_item->get_time() ); ?>
								</span>
							</div>
							<div class="order_item_price">
								<?php echo wp_kses_post( obp_get_price_html( $order_item->get_price(), $service->get_price_type() ) ); ?>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
				<div class="order_info_footer">
					<div class="order_info_inner">
						<table class="obp_order_table">
							<?php if ( $order->get_coupon_code() ): ?>
								<tr>
									<td>
										<span class="order_label"><?php esc_html_e( 'Coupon', 'ovabookpro' ); ?></span>
									</td>
									<td>
										<span class="order_value">
											<?php echo esc_html( $order->get_coupon_code() ); ?>
										</span>
									</td>
								</tr>
							<?php endif; ?>

							<?php if ( $order->get_system_fee() ): ?>
								<tr>
									<td>
										<span class="order_label">
											<?php esc_html_e( 'System Fee', 'ovabookpro' ); ?>
										</span>
									</td>
									<td>
										<span class="order_value">
											<?php echo wp_kses_post( obp_get_price_html( $order->get_system_fee() ) ); ?>
										</span>
									</td>
								</tr>
							<?php endif; ?>

							<?php if ( $order->get_tax_amount() ): ?>
								<tr>
									<td>
										<span class="order_label">
											<?php esc_html_e( 'Tax', 'ovabookpro' ); ?>
										</span>
									</td>
									<td>
										<span class="order_value">
											<?php echo wp_kses_post( obp_get_price_html( $order->get_tax_amount() ) ); ?>
										</span>
									</td>
								</tr>
							<?php endif; ?>

							<tr>
								<td>
									<span class="order_label">
										<?php esc_html_e( 'Total', 'ovabookpro' ); ?>
									</span>
								</td>
								<td>
									<span class="order_value">
										<?php echo wp_kses_post( obp_show_booking_total( $order->get_total(), $order->has_varies() ) ); ?>
									</span>
								</td>
							</tr>
							
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="obp_checkout_step_2">
		<div class="obp_checkout_breadcrumb">
			<a href="#" class="obp_checkout_back">
				<i class="flaticon bookproicon-left"></i>
			</a>
		</div>
	</div>
	<?php do_action( 'obp_after_checkout_content' ); ?>
</div>