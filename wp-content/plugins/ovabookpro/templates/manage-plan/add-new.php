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
				<input type="hidden" name="start_date" id="start_date" value="" />
				<input type="hidden" name="end_date" id="end_date" value="" />
			</td>
			<td>
				<select name="status" id="status" class="obp-select2 no-margin">
					<option value="open"><?php echo esc_html__( 'Open', 'ovabookpro' ); ?></option>
					<option value="closed"><?php echo esc_html__( 'Closed', 'ovabookpro' ); ?></option>
				</select>

			</td>
			<td>
				<div class="inline-flex-field choose_service_field">
					<span class="prefix">
						<?php echo esc_html__( 'apply for', 'ovabookpro' ); ?>
					</span>
					<input type="text" readonly id="service_label"
						placeholder="<?php echo esc_attr__( 'Choose Services', 'ovabookpro' ); ?>" value="" />

					<div class="service_ids_card">

						<label for="all_services" class="obp_checkbox">
							<input type="checkbox" name="service_type" id="all_services" value="all_services" checked >
							<span><?php esc_html_e( 'All Service', 'ovabookpro' ); ?></span>
						</label>

						<label for="special_service" class="obp_checkbox">
							<input type="checkbox" name="special_service" id="special_service" value="yes">
							<span><?php esc_html_e( 'Special Service', 'ovabookpro' ); ?></span>
						</label>

						<div class="custom_service_card">
							<select name="services_id" class="obp-select2 no-margin" id="services_id" multiple="multiple" data-placeholder="<?php echo esc_attr__( 'Choose Services', 'ovabookpro' ); ?>">
								<?php if ( ! empty( $category_service_groups ) ): ?>
									<?php foreach ( $category_service_groups as $key => $group ): ?>
										<optgroup label="<?php echo esc_attr( $group['category'] ); ?>">
											<?php if ( isset( $group['services'] ) ): ?>
												<?php foreach ( $group['services'] as $sv_id ): $service = obp_get_service( $sv_id ); ?>
													<option value="<?php echo esc_attr( $sv_id ); ?>"><?php echo esc_attr( $service->get_title() ); ?></option>
												<?php endforeach; ?>
											<?php endif; ?>
										</optgroup>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>

							<div class="obp_service_special"></div>
						</div>
					</div>
				</div>
			</td>
            <td>
                <div class="business_hours_field">
                    <span class="prefix"><?php echo esc_html__( 'at', 'ovabookpro' ); ?></span>
                    <input type="text" id="business_hours_type" placeholder="<?php echo esc_attr__( 'Business Hours', 'ovabookpro' ); ?>" readonly value="">
                    <div class="business_hours_card">
                        <label for="full_time" class="obp_radio">
                            
                            <input type="radio" id="full_time" name="business_time" checked class="business_time" value="full_time" data-label="<?php echo esc_attr__( 'Full Time', 'ovabookpro' ); ?>" />
                            <span class="checkmark"></span>
                            <?php echo esc_html__( 'Full Time', 'ovabookpro' ); ?>
                        </label>
                        <label for="custom_time" class="obp_radio">
                            <input type="radio" id="custom_time" name="business_time" class="business_time" value="custom_time" data-label="<?php esc_attr__( 'Custom Time', 'ovabookpro' ); ?>" />
                            <span class="checkmark"></span>
                            <?php echo esc_html__( 'Custom Time', 'ovabookpro' ); ?>
                        </label>
                        <div class="custome_time_card">
                            <ul class="custom_time_items">
                                <li class="custom_time_item">
                                    <input type="text" class="start_time business_custom_time" data-time="0" />
                                    <input type="text" class="end_time business_custom_time" data-time="0" />
                                    <span class="obp_remove_custom_time"><i class="flaticon bookproicon-close"></i></span>
                                </li>
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
		value="<?php echo esc_attr__( 'Save Plan', 'ovabookpro' ); ?>" />

	</div>
</form>
<div class="save_plan_errors" data-error="<?php echo esc_attr( json_encode( $errors ) ); ?>"></div>

