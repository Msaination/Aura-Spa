<?php defined( 'ABSPATH' ) || exit;
	use BookPro\Business\OBP_Business;
	$enable_map_setting = OBP()->settings->general->get('enable_map', 'yes');
?>

	<div class="single-business-sidebar">	

		<?php if ( $enable_map_setting == 'yes' && $enable_map == 'yes' ): ?>
	        <div class="obp_map">
				<div id="obp_enable_map"></div>
				<input type="hidden" id="business_google_map" name="business_google_map" 
					value="<?php echo esc_attr( $map_address ); ?>"
					autocomplete="off" autocorrect="off" autocapitalize="none"
				>
				<input type="hidden" name="map_latitude" value="<?php echo esc_attr( $map_lat ); ?>">
		        <input type="hidden" name="map_longitude" value="<?php echo esc_attr( $map_lng ); ?>">
			</div>
		<?php endif; ?>

		<div class="sidebar-info">
			<?php if( ! empty( $description ) ) : ?>
				<div class="part-info business-description">
					<h2 class="obp-second-title">
						<?php echo esc_html__('About us','ovabookpro');?>	
					</h2>

					<div class="description-wrap" data-height="<?php echo esc_attr( $height_desc ); ?>"
						data-text_show_more="<?php echo esc_attr('Show more','ovabookpro'); ?>"
						data-text_show_less="<?php echo esc_attr('Show less','ovabookpro'); ?>"
					>
						<?php echo wp_kses_post( $description ); ?>
						<div class="show_more_desc">
							<a href="#" class="btn_showmore">
								<span class="text">
									<?php echo esc_attr('Show more','ovabookpro');?>
								</span>
								<i class="bookproicon-down"></i>
							</a>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<div class="part-info contact-and-business-hours">
				<h2 class="obp-second-title">
					<?php echo esc_html__('Contact & Business Hours','ovabookpro');?>	
				</h2>

				<div class="business-contact">
					<span class="phone-number">
						<?php echo esc_html__('&#128241;','ovabookpro') . ' ' . esc_html( $phone );?>
					</span>
					<a class="call-now" href="tel:<?php echo esc_attr( $phone );?>" title="<?php esc_attr_e( 'Call','ovabookpro' );?>">
						<?php esc_html_e( 'Call', 'ovabookpro' );?>
					</a>
				</div>

				<div class="business-hours-wrapper">
					<div class="business_hours_field business_hours_monday">
						<span class="name-day">
							<?php esc_html_e( 'Monday', 'ovabookpro' ); ?>
						</span>
						<div class="business_hours">
							<?php if ( isset( $business_hours['monday'] ) ) : foreach ( $business_hours['monday'] as $key => $value) : ?>
								<div class="business-hour">
									<?php echo esc_attr( $value['start_hour'] ) . ' - ' . esc_attr( $value['end_hour'] );?>
								</div>
							<?php endforeach; else: ?>
								<div class="business-hour">
									<?php echo esc_html__('Closed','ovabookpro');?>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<div class="business_hours_field business_hours_tuesday">
						<span class="name-day">
							<?php esc_html_e( 'Tuesday', 'ovabookpro' ); ?>
						</span>
						<div class="business_hours">
							<?php if ( isset( $business_hours['tuesday'] ) ) : foreach ( $business_hours['tuesday'] as $key => $value ) : ?>
								<div class="business-hour">
									<?php echo esc_attr( $value['start_hour'] ) . ' - ' . esc_attr( $value['end_hour'] );?>
								</div>
							<?php endforeach; else: ?>
								<div class="business-hour">
									<?php echo esc_html__('Closed','ovabookpro');?>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<div class="business_hours_field business_hours_wednesday">
						<span class="name-day">
							<?php esc_html_e( 'Wednesday', 'ovabookpro' ); ?>
						</span>
						<div class="business_hours">
							<?php if ( isset( $business_hours['wednesday'] ) ) : foreach ( $business_hours['wednesday'] as $key => $value ) : ?>
								<div class="business-hour">
									<?php echo esc_attr( $value['start_hour'] ) . ' - ' . esc_attr( $value['end_hour'] );?>
								</div>
							<?php endforeach; else: ?>
								<div class="business-hour">
									<?php echo esc_html__('Closed','ovabookpro');?>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<div class="business_hours_field business_hours_thursday">
						<span class="name-day">
							<?php esc_html_e( 'Thursday', 'ovabookpro' ); ?>
						</span>
						<div class="business_hours">
							<?php if ( isset( $business_hours['thursday'] ) ) : foreach ( $business_hours['thursday'] as $key => $value) : ?>
								<div class="business-hour">
									<?php echo esc_attr( $value['start_hour'] ) . ' - ' . esc_attr( $value['end_hour'] );?>
								</div>
							<?php endforeach; else: ?>
								<div class="business-hour">
									<?php echo esc_html__('Closed','ovabookpro');?>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<div class="business_hours_field business_hours_friday">
						<span class="name-day">
							<?php esc_html_e( 'Friday', 'ovabookpro' ); ?>
						</span>
						<div class="business_hours">
							<?php if ( isset( $business_hours['friday'] ) ) : foreach ( $business_hours['friday'] as $key => $value ) : ?>
								<div class="business-hour">
									<?php echo esc_attr( $value['start_hour'] ) . ' - ' . esc_attr($value['end_hour']);?>
								</div>
							<?php endforeach; else: ?>
								<div class="business-hour">
									<?php echo esc_html__('Closed','ovabookpro');?>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<div class="business_hours_field business_hours_saturday">
						<span class="name-day">
							<?php esc_html_e( 'Saturday', 'ovabookpro' ); ?>
						</span>
						<div class="business_hours">
							<?php if ( isset( $business_hours['saturday'] ) ) : foreach ( $business_hours['saturday'] as $key => $value ) : ?>
								<div class="business-hour">
									<?php echo esc_attr( $value['start_hour'] ) . ' - ' . esc_attr( $value['end_hour'] );?>
								</div>
							<?php endforeach; else: ?>
								<div class="business-hour">
									<?php echo esc_html__('Closed','ovabookpro');?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				
					<div class="business_hours_field business_hours_sunday">
						<span class="name-day">
							<?php esc_html_e( 'Sunday', 'ovabookpro' ); ?>
						</span>
						<div class="business_hours">
							<?php if ( isset( $business_hours['sunday'] ) ) : foreach ( $business_hours['sunday'] as $key => $value) : ?>
								<div class="business-hour">
									<?php echo esc_attr( $value['start_hour'] ) . ' - ' . esc_attr( $value['end_hour'] );?>
								</div>
							<?php endforeach; else: ?>
								<div class="business-hour">
									<?php echo esc_html__('Closed','ovabookpro');?>
								</div>
							<?php endif; ?>
						</div>
					</div>
					
				</div>
			</div>

			<?php if( !empty( $socials ) ) : ?>
				<div class="part-info business-socials">
					<h2 class="obp-second-title">
						<?php echo esc_html__('Social Media','ovabookpro');?>	
					</h2>

					<div class="social_list">
						<?php foreach ( $socials as $key => $value ) : ?>
							<div class="social_item">
								<a href="<?php echo esc_attr( $value['link_social'] ); ?>" target="_blank" rel="nofollow">
									<span class="social_icon">
										<i class="<?php echo esc_attr( OBP_Business::get_social_icon( $value['name_social'] ) ) ; ?>"></i>
									</span>

									<?php foreach ( OBP_Business::social_networks() as $key_name_social => $value_name_social ) : ?>
										<?php if( $value['name_social'] == $key_name_social ) echo wp_kses_post( $value_name_social ); ?>
									<?php endforeach; ?>
								</a>
							</div>
						<?php endforeach; ?>	
					</div>
				</div>
			<?php endif; ?>
		</div>

	</div>