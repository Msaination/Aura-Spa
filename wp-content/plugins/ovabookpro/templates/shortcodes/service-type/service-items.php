<?php defined( 'ABSPATH' ) || exit;

$date_timestamp = strtotime( gmdate("Y-m-d", current_time( 'timestamp' ) ) );

if ( ! empty( $service_ids ) ): 
	$text_show_more = esc_html__('Show more','ovabookpro');
	$text_show_less = esc_html__('Show less','ovabookpro');
	$height_desc    = apply_filters('obp_service_height_description', '130');
?>
	<div class="list-service-items">
		<?php foreach ( $service_ids as $service_id ) : $service = obp_get_service( $service_id ); ?>
			<div class="service-item">
				<div class="service-info">
					<h4 class="service-name">
						<?php echo esc_html( $service->get_title() ); ?>
					</h4>
					<div class="service-description description-wrap" data-height="<?php echo esc_attr( $height_desc ); ?>"
						data-text_show_more="<?php echo esc_attr( $text_show_more ); ?>"
						data-text_show_less="<?php echo esc_attr( $text_show_less ); ?>"
					>
						<?php echo wp_kses_post( $service->get_description() ); ?>
						<div class="show_more_desc">
							<a href="#" class="btn_showmore">
								<span class="text">
									<?php echo esc_attr($text_show_more);?>
								</span>
								<i class="bookproicon-down"></i>
							</a>
						</div>
					</div>
					<?php if ( ! empty( $service->get_price_on_sale_off_date( $date_timestamp ) ) ): ?>
						<div class="service_save">
							<span><?php
							// translators: %d: percent sale off.
							echo sprintf( esc_html__( 'Save up to %d%%', 'ovabookpro' ), esc_html( $service->get_percent_sale_off() ) ); ?></span>
						</div>
					<?php endif; ?>
				</div>
				<div class="service-meta">
					<div class="price-and-duration">
						<div class="price">
							<?php if ( ! empty( $service->get_price_on_sale_off_date( $date_timestamp ) ) ): ?>
								<span class="old_price">
									<?php echo wp_kses_post( obp_get_price_html( obp_show_price_item( $service->get_price(), $service->get_rates() ), $service->get_price_type() ) ); ?>
								</span>
								<span class="sale_price">
									<?php echo wp_kses_post( obp_get_price_html( obp_show_price_item( $service->get_price_on_sale_off_date( $date_timestamp ), $service->get_rates() ) , $service->get_price_type() ) ); ?>
								</span>
							<?php else: ?>
								<?php echo wp_kses_post( obp_get_price_html( obp_show_price_item( $service->get_price(), $service->get_rates() ), $service->get_price_type(), $service->get_note_price() ) ); ?>
							<?php endif; ?>
						</div>
						<?php if ( ! empty( $service->get_short_duration_text() ) ): ?>
							<div class="duration">
								<?php echo esc_html( $service->get_short_duration_text() ); ?>
							</div>
						<?php endif; ?>
						
					</div>
					<a href="#" class="obp_button obp_booking_popup"
						data-nonce="<?php echo esc_attr( wp_create_nonce( 'obp_booking_popup' ) ); ?>"
						data-vendor-id="<?php echo esc_attr( $service->get_vendor_id() ); ?>"
						data-business-id="<?php echo esc_attr( $service->get_business_id() ); ?>"
						data-id="<?php echo esc_attr( $service_id ); ?>"
					>
						<?php esc_html_e( 'Book', 'ovabookpro' ); ?>
					</a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>		