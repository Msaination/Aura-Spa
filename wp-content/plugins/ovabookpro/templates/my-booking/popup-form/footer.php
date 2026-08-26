<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp-change-order-footer">
	<div class="footer_wrapper">
		<div class="footer_inner">
			<div class="billing-container">
				<table class="billing-table">
					<tr class="subtotal">
						<td class="first_column">
							<span class="label"><?php esc_html_e( 'Subtotal', 'ovabookpro' ); ?></span>
						</td>
						<td class="second_column">
							<span class="price"><?php echo wp_kses_post( obp_get_price_html( $subtotal ) ); ?></span>
						</td>
					</tr>
					<tr class="system-fee">
						<td class="first_column">
							<span class="label"><?php esc_html_e( 'System Fee', 'ovabookpro' ); ?></span>
						</td>
						<td class="second_column">
							<span class="price"><?php echo wp_kses_post( obp_get_price_html( $system_fee ) ); ?></span>
						</td>
					</tr>
					<?php if ( $coupon_code && $discount ): ?>
						<tr class="discount">
							<td class="first_column">
								<span class="label"><?php esc_html_e( 'Discount', 'ovabookpro' ); ?></span>
							</td>
							<td class="second_column">
								<span class="price">
									<?php echo '-'.wp_kses_post( obp_get_price_html( $discount ) ).' ('.esc_html( $coupon_code ).')'; ?>
								</span>
							</td>
						</tr>
					<?php endif; ?>
					<tr class="tax">
						<td class="first_column">
							<span class="label"><?php esc_html_e( 'Tax', 'ovabookpro' ); ?></span>
						</td>
						<td class="second_column">
							<span class="price"><?php echo wp_kses_post( obp_get_price_html( $tax_fee ) ); ?></span>
						</td>
					</tr>
				</table>
				<div class="billing-total">
					<div class="total">
						<span class="label"><?php esc_html_e('Total:', 'ovabookpro'); ?></span>
						<span class="total-price"><?php echo wp_kses_post( obp_show_booking_total( $total, $has_varies ) ); ?></span>
					</div>
					<div class="total-time"><?php echo esc_html( $total_time ); ?></div>
				</div>
			</div>
		</div>

		<div class="messages"></div>

		<button type="button" class="obp_order_change_update"><?php esc_html_e('Update','ovabookpro'); ?></button>

	</div>

</div>