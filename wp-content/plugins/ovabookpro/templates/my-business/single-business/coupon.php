<?php defined( 'ABSPATH' ) || exit;

global $post;

$service 	= obp_get_service( $post->ID );
$vendor_id 	= $service->get_vendor_id();
$coupons 	= \BookPro\Coupon\OBP_Coupon::get_coupon_active( $vendor_id );
?>
<?php if ( $coupons ): ?>
	<div class="obp-service-coupon-wrap">
		<h2 class="obp-second-title"><?php esc_html_e( 'Coupon', 'ovabookpro' ); ?></h2>
		<?php foreach ( $coupons as $key => $coupon ):
			$coupon_used = \BookPro\Order\OBP_Order::get_order_ids_by_coupon_id( $coupon->get_id(), $vendor_id );
			$remaining = absint( $coupon->get_coupon_qty() ) - count( $coupon_used );
			?>
			<div class="obp-coupon-item">
				<table class="obp_table">
					<tr>
						<th><?php echo esc_html( $coupon->get_coupon_code(), 'ovabookpro' ); ?></th>
						<th><?php esc_html_e( 'Remaining', 'ovabookpro' ); ?></th>

						<?php if ( $coupon->get_order_from() ): ?>
							<th><?php esc_html_e( 'Minimum Total Order', 'ovabookpro' ); ?></th>
						<?php endif; ?>
					</tr>
					<tr>
						<td>
							<?php echo wp_kses_post( $coupon->get_amount_discount_str() ); ?>
						</td>
						<td>
							<?php echo esc_html( absint( $remaining ) ); ?>
						</td>
						<?php if ( $coupon->get_order_from() ): ?>
							<td>
								<?php echo wp_kses_post( obp_get_price_html( $coupon->get_order_from() ) ); ?>
							</td>
						<?php endif; ?>
					</tr>
				</table>
				<ul class="obp_coupon_info">
					<?php if ( $coupon->get_time_formated() ): ?>
						<li class="info_item">
							<span class="obp_label">
								<?php esc_html_e( 'Time: ', 'ovabookpro' ); ?>
							</span>
							<span>
								<?php echo esc_html( $coupon->get_time_formated() ); ?>
							</span>
						</li>
					<?php endif; ?>
					<li class="info_item">
						<span class="obp_label">
							<?php esc_html_e( 'Apply For: ', 'ovabookpro' ); ?>
						</span>
						<span>
							<?php echo esc_html( $coupon->get_apply_for_str() ); ?>
						</span>
					</li>
					<li class="info_item">
						<span class="obp_label">
							<?php esc_html_e( 'Only Use For: ', 'ovabookpro' ); ?>
						</span>
						<span>
							<?php echo esc_html( $coupon->get_only_use_for() ); ?>
						</span>
					</li>
				</ul>
				<?php if ( $coupon->get_description() ): ?>
					<p class="description">
						<?php echo wp_kses_post( $coupon->get_description() ); ?>
					</p>
				<?php endif; ?>
				
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>