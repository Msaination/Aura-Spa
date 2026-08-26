<?php defined( 'ABSPATH' ) || exit; ?>

<?php if ( ! empty( $services ) ): ?>
	<?php foreach ( $services as $value ):
		$service_number = isset( $value['services'] ) ? count( $value['services'] ) : 0;
		?>
		<?php if ( $service_number > 0 ): ?>
		
			<div class="service_section">
				<h4 class="service_title">
					<i class="flaticon bookproicon-right-arrow"></i>
					<span class="service_category">
						<?php echo esc_html( $value['category'] ); ?>
					</span>
					<span class="service_counter">
						<?php // translators: %s: number of services.
						echo wp_kses_post( sprintf( _n( '%s Service', '%s Services', $service_number, 'ovabookpro' ), number_format_i18n( $service_number ) ) ); ?>
					</span>
				</h4>
				<div class="service_section_content">

					<?php if ( ! empty( $value['services'] ) ): ?>
						<?php foreach ( $value['services'] as $service_id ):
							$service = obp_get_service( $service_id );
							?>
							<div class="service-card">
								<div class="service_entry">
									<h5 class="service_name">
										<?php echo esc_html( $service->get_title() ); ?>
									</h5>
									<?php if ( ! empty( $service->get_price_on_sale_off_date( $date_timestamp ) ) ): ?>
										<div class="service_save">
											<span><?php // translators: %d: percent sale off.
											echo sprintf( esc_html__( 'Save up to %d%%', 'ovabookpro' ), esc_html( $service->get_percent_sale_off() ) ); ?></span>
										</div>
									<?php endif; ?>
								</div>
								
								<div class="service_meta">
									<div class="service_info">
										<div class="price">
											<?php if ( ! empty( $service->get_price_on_sale_off_date( $date_timestamp ) ) ): ?>
												<span class="old_price">

													<?php echo wp_kses_post( obp_get_price_html( obp_show_price_item( $service->get_price(), $service->get_rates() ), $service->get_price_type() ) ); ?>
												</span>
												<span class="sale_price">
													<?php echo wp_kses_post( obp_get_price_html( obp_show_price_item( $service->get_price_on_sale_off_date( $date_timestamp ), $service->get_rates() ), $service->get_price_type() ) ); ?>
												</span>
											<?php else: ?>
												<?php echo wp_kses_post( obp_get_price_html( obp_show_price_item( $service->get_price(), $service->get_rates() ), $service->get_price_type(), $service->get_note_price() ) ); ?>
											<?php endif; ?>
											
										</div>
										<div class="times">
											<?php echo esc_html( $service->get_duration_text() ); ?>
										</div>
									</div>
								
									<a href="#" class="obp_add_service" data-id="<?php echo esc_attr( $service->get_id() ); ?>">
										<?php esc_html_e( 'Book', 'ovabookpro' ); ?>
									</a>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
					
				</div>
			</div>

		<?php endif; ?>
		
	<?php endforeach; ?>
<?php endif; ?>