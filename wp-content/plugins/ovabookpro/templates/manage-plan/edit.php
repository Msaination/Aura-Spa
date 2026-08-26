<?php defined( 'ABSPATH' ) || exit; ?>

	<form action="" class="obp_save_plan_form" method="POST" autocomplete="off">
		<?php wp_nonce_field( 'obp_save_plan', 'obp_save_plan_nonce', false );
		?>
		<div class="obp_save_plan_notice"></div>
		<span class="obp_remove_form">
			<i class="flaticon bookproicon-close"></i>
		</span>
		<table class="obp_save_plan_table">
			<tr>
				<td>
					<div class="obp_date_time">
						<input type="text" name="start_end" value="" class="no-margin"
						placeholder="<?php echo esc_attr__( 'Start Date - End Date', 'ovabookpro' ); ?>" id="obp_plan_date_time">
						<i class="bookproicon-calendar"></i>
					</div>
					<input type="hidden" name="start_date" id="start_date" value="<?php echo esc_attr( gmdate("Y-m-d", $plan->get_start_date() ) ); ?>" />
					<input type="hidden" name="end_date" id="end_date" value="<?php echo esc_attr( gmdate("Y-m-d", $plan->get_end_date() ) ); ?>" />
					<input type="hidden" name="plan_id" id="plan_id" value="<?php echo esc_attr( $post_id ); ?>" />
				</td>
				<td>
					<select name="status" id="status" class="obp-select2 no-margin">
						<option value="open" <?php selected( $plan->get_status(), 'open' ); ?> >
							<?php echo esc_html__( 'Open', 'ovabookpro' ); ?>
						</option>
						<option value="closed" <?php selected( $plan->get_status(), 'closed' ); ?> >
							<?php echo esc_html__( 'Closed', 'ovabookpro' ); ?>
						</option>
					</select>

				</td>
				<td>
					<div class="inline-flex-field choose_service_field">
						<span class="prefix"><?php esc_html_e( 'apply for', 'ovabookpro' ); ?></span>
						<input type="text" readonly id="service_label" placeholder="<?php echo esc_attr__( 'Choose Services', 'ovabookpro' ); ?>" value="<?php echo esc_attr( $service_label ); ?>" />
						<div class="service_ids_card">

							<label for="all_services" class="obp_checkbox">
								<input type="checkbox" name="service_type" id="all_services" <?php checked( $plan->get_service_type(), 'all_services' ); ?> value="all_services">
								<span><?php esc_html_e( 'All Service', 'ovabookpro' ); ?></span>
							</label>

							<label for="special_service" class="obp_checkbox">
								<input type="checkbox" name="special_service" id="special_service" <?php checked( $plan->has_special_service(), 'yes' ); ?> value="yes">
								<span><?php esc_html_e( 'Special Service', 'ovabookpro' ); ?></span>
							</label>

							<div class="custom_service_card">
								<select name="services_id" class="obp-select2 no-margin" id="services_id" multiple="multiple" data-placeholder="<?php echo esc_attr__( 'Choose Services', 'ovabookpro' ); ?>">
									<?php if ( ! empty( $category_service_groups ) ): ?>
										<?php foreach ( $category_service_groups as $key => $group ):
											$count = isset( $group['services'] ) ? count( $group['services'] ) : 0; 
											if ( $count > 0 ) {
											?>
												<optgroup label="<?php echo esc_attr( $group['category'] ); ?>">
													<?php if ( isset( $group['services'] ) ): ?>
													
														<?php foreach ( $group['services'] as $sv_id ):
															$selected = in_array( $sv_id, $plan->get_custom_service_ids() ) ? $sv_id : '';
															$service = obp_get_service( $sv_id );
															?>
															<option value="<?php echo esc_attr( $sv_id ); ?>" <?php selected( $selected, $sv_id ); ?>>
																<?php echo esc_attr( $service->get_title() ); ?>
															</option>
														<?php endforeach; ?>
													<?php endif; ?>
												</optgroup>
											<?php 
											}
											endforeach;
										?>
									<?php endif; ?>
								</select>

								<div class="obp_service_special">
									<?php if ( ! empty( $plan->get_special_services() ) ): ?>
										<?php foreach ( $plan->get_special_services() as $special_services ): 
											$service_id = isset( $special_services['id'] ) ? $special_services['id'] : '';
											$service_time = isset( $special_services['time'] ) ? $special_services['time'] : [];
											$service = obp_get_service( $service_id );
										?>
										<div class="obp_special_service_item" data-service-id="<?php echo esc_attr( $service->get_id() ); ?>">
											<div class="service_name_wrap">
												<p class="service_name"><?php echo esc_html( $service->get_title() ); ?></p>
											</div>
											<div class="service_times">
												<div class="list_service_time">
													<?php if ( ! empty( $service_time ) ): ?>
														<?php foreach ($service_time as $key => $time ):
															$start_time = isset( $time['start_hour'] ) ? $time['start_hour'] : '';
															$end_time = isset( $time['end_hour'] ) ? $time['end_hour'] : '';
															$start_time_hi = absint( obp_calendar_Hi_to_seconds( $start_time ) );
															$end_time_hi = absint( obp_calendar_Hi_to_seconds( $end_time ) );
															?>
														<div class="obp_service_time_item">
															<input type="text" class="start_time service_time" value="<?php echo esc_attr( $start_time ); ?>"
															data-time="<?php echo esc_attr( $start_time_hi ); ?>"/>
															<input type="text" class="end_time service_time" value="<?php echo esc_attr( $end_time ); ?>"
															data-time="<?php echo esc_attr( $end_time_hi ); ?>"/>
															<?php if ( $key > 0 ): ?>
																<a href="#" class="obp_remove_service_time">
																	<i class="flaticon bookproicon-close"></i>
																</a>
															<?php endif; ?>
														</div>
														<?php endforeach; ?>
													<?php endif; ?>
												</div>
												
												<div class="service_time_bottom">
													<a href="#" class="obp_add_service_time obp_button">
														<?php esc_html_e( 'Add', 'ovabookpro' ); ?>
													</a>
												</div>
											</div>
										</div>	
										<?php endforeach; ?>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</td>
				<td>
					<div class="business_hours_field">
						<span class="prefix"><?php echo esc_html__( 'at', 'ovabookpro' ); ?></span>
						<input type="text" id="business_hours_type" placeholder="<?php echo esc_attr__( 'Business Hours', 'ovabookpro' ); ?>" readonly value="<?php echo esc_attr( $business_label ); ?>">
						<div class="business_hours_card">
							<label for="full_time" class="obp_radio">
								
								<input type="radio" id="full_time" name="business_time" class="business_time" value="full_time" <?php checked( $plan->get_time_type(), 'full_time' ); ?> data-label="<?php echo esc_attr__( 'Full Time', 'ovabookpro' ); ?>" />
								<span class="checkmark"></span>
								<?php echo esc_html__( 'Full Time', 'ovabookpro' ); ?>
							</label>
							<label for="custom_time" class="obp_radio">
								<input type="radio" id="custom_time" name="business_time" class="business_time" value="custom_time" <?php checked( $plan->get_time_type(), 'custom_time' ); ?> data-label="<?php echo esc_attr__( 'Custom Time', 'ovabookpro' ); ?>" />
								<span class="checkmark"></span>
								<?php echo esc_html__( 'Custom Time', 'ovabookpro' ); ?>
							</label>
							<div class="custome_time_card">
								<ul class="custom_time_items">

									<?php if ( ! empty( $times ) ): ?>

										<?php foreach ( $times as $time ):
											$start_time = obp_calendar_Hi_to_seconds( $time['start_hour'] );
											$end_time 	= obp_calendar_Hi_to_seconds( $time['end_hour'] );

											$start_time_format 	= gmdate( $time_format, strtotime( $time['start_hour'] ) );
											$end_time_format 	= gmdate( $time_format, strtotime( $time['end_hour'] ) );
											?>
											
											<li class="custom_time_item">
												<input type="text" class="start_time business_custom_time" data-time="<?php echo esc_attr( $start_time ); ?>" value="<?php echo esc_attr( $start_time_format ); ?>" />
												<input type="text" class="end_time business_custom_time" data-time="<?php echo esc_attr( $end_time ); ?>" value="<?php echo esc_attr( $end_time_format ); ?>" />
												<span class="obp_remove_custom_time"><i class="flaticon bookproicon-close"></i></span>
											</li>
										<?php endforeach; ?>

									<?php endif; ?>

								</ul>
								<div class="card_button">
									<button type="button" class="obp_add_business_time" data-nonce="<?php echo esc_attr( wp_create_nonce('obp_add_business_time') ); ?>"><?php echo esc_html__( 'Add', 'ovabookpro' ); ?></button>
								</div>
								
							</div>
						</div>
					</div>
				</td>
			</tr>
		</table>

		<div class="obp-button-wrapper align-right">

			<input type="submit"
			name="obp_save_plan"
			class="obp_button"
			value="<?php echo esc_attr__( 'Update Plan', 'ovabookpro' ); ?>" />

		</div>
	</form>
	<div class="save_plan_errors" data-error="<?php echo esc_attr( json_encode( $errors ) ); ?>"></div>

