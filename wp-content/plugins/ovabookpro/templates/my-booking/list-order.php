<?php defined( 'ABSPATH' ) || exit; /* variables get from $args obp_get_template() */ ?>

<div class="obp-data-list order-list">

	<div class="obp-data-info order-info">
		<span class="s-key">
			<?php echo esc_html($order_id);?>
		</span>
		<h3 class="info_name obp-label-title">
			<?php echo esc_html($order->get_customer_name());?>
		</h3>
		<div class="info_column3">
			<?php echo wp_kses_post( obp_get_price_html($order->get_total()) );?>
		</div>
		<div class="info_column4">
			<?php echo wp_kses_post( obp_get_price_html($order->get_tax_amount()) ); ?>
		</div>
		<div class="info_column5">
			<?php if(!empty($order_meta_arr)) : foreach($order_meta_arr as $order_meta) :
				$service_info = esc_html($order_meta->get_time()) . ' ' . esc_html('-','ovabookpro') . ' ' . esc_html($order_meta->get_staff_fullname());
			?>
				<div class="service-info">
					<h3 class="obp-label-title">
						<?php echo esc_html($order_meta->get_service_name());?>
					</h3>
					<div class="small-text-field">
						<?php echo esc_html($service_info);?>
					</div>
				</div>
			<?php endforeach; endif; ?>
		</div>
	</div>

	<div class="obp-data-action order-action">
		<?php if($order->get_order_status() != 'cancelled') : ?>

			<a href="#" class="obp_action_download" title="<?php esc_attr_e('Download','ovabookpro');?>">
				<i class="bookproicon-download"></i>
			</a>

			<?php if ( $can_change_order === 'yes' ): ?>

				<a href="#" class="obp_action_change_schedule" title="<?php esc_attr_e('Change Schedule','ovabookpro');?>">
					<i class="bookproicon-calendar-1"></i>
				</a>
				
			<?php endif; ?>

			<?php if($cancel_order == 'yes' && $can_cancel_before == 'yes' ) { ?>

				<a href="#" class="obp_action_cancel_order" title="<?php esc_attr_e('Cancel','ovabookpro');?>">
					<i class="bookproicon-close"></i>
				</a>
				
			<?php } ?>
			
			<a href="#" class="obp_action_rating" title="<?php esc_attr_e('Rating','ovabookpro');?>">
				<i class="bookproicon-rating-stars"></i>
			</a>
		<?php else: ?>
			<span class="obp-danger-text">
				<?php esc_html_e('Cancelled','ovabookpro');?>
			</span>
		<?php endif; ?>
		<input type="hidden" class="comment_id" name="comment_id" value="<?php echo esc_attr( $comment_id ); ?>">
		<input type="hidden" class="order_id" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
		<input type="hidden" class="business_id" name="business_id" value="<?php echo esc_attr( $business_id ); ?>">
		<input type="hidden" class="vendor_id" name="vendor_id" value="<?php echo esc_attr( $vendor_id ); ?>">
		<input type="hidden" class="nonce" name="nonce" value="<?php echo esc_attr( wp_create_nonce('obp_order_nonce') ); ?>">
	</div>

</div>