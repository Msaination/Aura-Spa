<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp-booking-footer">
	<div class="footer_wrapper">
		<div class="footer_inner">
			<div class="coupon-code-wrap">
				<p class="label">
					<?php esc_html_e( 'Do you have a coupon code ?', 'ovabookpro' ); ?>
				</p>
				<p class="coupon-container">
					<input type="text" name="coupon_code" class="coupon_code" value="<?php echo esc_attr( $coupon_code ); ?>"
					placeholder="<?php esc_attr_e( 'Coupon code', 'ovabookpro' ); ?>" />
					<button type="button" class="coupon_code_apply"><i class="bookproicon-right-arrow"></i></button>
				</p>
				<?php if ( $message ): ?>
					<p class="coupon_message"><?php echo wp_kses_post( $message ); ?></p>
				<?php endif; obp_clear_coupon_message(); ?>
			</div>
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

					<?php if ( $show_tax == 'yes' ): ?>
						<tr class="tax">
							<td class="first_column">
								<span class="label"><?php esc_html_e( 'Tax', 'ovabookpro' ); ?></span>
							</td>
							<td class="second_column">
								<span class="price"><?php echo wp_kses_post( obp_get_price_html( $tax_fee ) ); ?></span>
							</td>
						</tr>
					<?php endif; ?>
					
				</table>
				<div class="billing-total">
					<div class="total">
						<span class="label"><?php esc_html_e('Total:', 'ovabookpro'); ?></span>
						<span class="total-price"><?php echo wp_kses_post( obp_show_booking_total( $total, OBP()->cart->has_varies() ) ); ?></span>
					</div>
					<div class="total-time"><?php echo esc_html( $total_time ); ?></div>
				</div>
			</div>
		</div>

		<button type="button" class="obp_booking_continue">
			<?php esc_html_e('Continue','ovabookpro'); ?>
		</button>
	</div>
</div> <!-- .obp-booking-footer -->


