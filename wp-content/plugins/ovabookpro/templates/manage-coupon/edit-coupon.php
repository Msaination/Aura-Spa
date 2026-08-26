<?php defined( 'ABSPATH' ) || exit;
?>

<h1 class="obp-title">
	<?php
	if ( $coupon_id ) {
		esc_html_e( 'Edit Coupon', 'ovabookpro' );
	} else {
		esc_html_e( 'Add Coupon', 'ovabookpro' );
	} ?>
</h1>

<div class="obp-content obp-content-coupon">
	<form class="obp_edit_coupon_form" action="#" method="POST" novalidate autocomplete="off">
		<div class="obp-form-part">
			<input type="hidden" name="coupon_id" value="<?php echo esc_attr( $coupon_item->get_id() ); ?>" />
			<div class="obp_wrap_two_column">
				<div class="obp_column">
					<label for="coupon_code">
						<?php esc_html_e( 'Coupon Code*', 'ovabookpro' ); ?>	
					</label>
					<input type="text" id="coupon_code" name="coupon_code"
						placeholder="<?php esc_attr_e( 'Coupon Code', 'ovabookpro' ); ?>"
						value="<?php echo esc_attr( $coupon_item->get_coupon_code() ); ?>" />
				</div>

				<div class="obp_column">
					<label>
						<?php esc_html_e( 'Scope', 'ovabookpro' ); ?>
					</label>
					<select class="obp-select2" name="visibility">
						<option value="public" <?php selected( $coupon_item->get_visibility(), 'public' ); ?>>
							<?php esc_html_e( 'Public', 'ovabookpro' ); ?>
						</option>
						<option value="private" <?php selected( $coupon_item->get_visibility(), 'private' ); ?>>
							<?php esc_html_e( 'Private', 'ovabookpro' ); ?>
						</option>
					</select>
				</div>
			</div>

			<div class="obp_column">
				<label>
					<?php esc_html_e( 'Description', 'ovabookpro' ); ?>
				</label>
				<textarea name="description"><?php echo esc_html( $coupon_item->get_description() ); ?></textarea>
			</div>

			<div class="obp_wrap_four_column">
				<div class="obp_column">
					<label>
						<?php esc_html_e( 'Discount Type', 'ovabookpro' ); ?>
					</label>
					<select class="obp-select2" name="discount_type">
						<option value="percent" <?php selected( $coupon_item->get_discount_type(), 'percent' ); ?>>
							<?php esc_html_e( 'Percent', 'ovabookpro' ); ?>
						</option>
						<option value="fixed" <?php selected( $coupon_item->get_discount_type(), 'fixed' ); ?>>
							<?php esc_html_e( 'Fixed', 'ovabookpro' ); ?>
						</option>
					</select>
				</div>

				<div class="obp_column">
					<label>
						<?php esc_html_e( 'Amount*', 'ovabookpro' ); ?>
					</label>
					<input type="text" name="amount" placeholder="10" value="<?php echo esc_attr( obp_convert_price( $coupon_item->get_coupon_amount() ) ); ?>" />
				</div>

				<div class="obp_column">
					<label>
						<?php esc_html_e( 'Quantity*', 'ovabookpro' ); ?>
					</label>
					<input type="number" name="quantity" placeholder="100" value="<?php echo esc_attr( $coupon_item->get_coupon_qty() ); ?>" />
				</div>

				<div class="obp_column">
					<label>
						<?php esc_html_e( 'Minimum Total Order', 'ovabookpro' ); ?>
					</label>
					<div class="obp_coupon_group">
						<input type="text" name="order_from" placeholder="100"
						value="<?php echo esc_attr( obp_convert_price( $coupon_item->get_order_from() ) ); ?>" />
						<span class="coupon_currency"><?php echo esc_html( obp_get_currency_symbol() ); ?></span>
					</div>
				</div>
			</div>

			<div class="obp_wrap_two_column">
				<div class="obp_column">
					<label>
						<?php esc_html_e( 'Apply for', 'ovabookpro' ); ?>
					</label>

					<div class="obp_apply_to_wrap">
						<label for="all_services" class="obp_radio inline">
							<input type="radio" id="all_services" name="apply_to" value="all_services"
							<?php checked( $coupon_item->get_apply_to(), 'all_services' ); ?> />
							<span class="checkmark"></span>
							<?php esc_html_e( 'All Services', 'ovabookpro' ); ?>
						</label>
						<label for="custom_services" class="obp_radio inline">
							<input type="radio" id="custom_services" name="apply_to" value="custom_services"
							<?php checked( $coupon_item->get_apply_to(), 'custom_services' ); ?> />
							<span class="checkmark"></span>
							<?php esc_html_e( 'Some Services', 'ovabookpro' ); ?>
						</label>
					</div>

					<div class="obp_service_container">
						<select class="obp-select2" multiple name="apply_services"
							data-placeholder="<?php esc_attr_e( 'Choose services', 'ovabookpro' ); ?>">
							<?php if ( $services ): ?>
								<?php foreach ( $services as $key => $value ):?>
									<optgroup label="<?php echo esc_attr( $value['category'] ); ?>">
										<?php if ( isset( $value['services'] ) && count( $value['services'] ) ): ?>
											<?php foreach ( $value['services'] as $sv_id ):
												$service = obp_get_service( $sv_id );
												$selected = in_array($sv_id, $coupon_item->get_apply_services() ) ? $sv_id : '';
												?>
												<option value="<?php echo esc_attr( $sv_id ); ?>"
													<?php selected( $selected, $sv_id ); ?>>
													<?php echo esc_html( $service->get_title() ); ?>
												</option>
											<?php endforeach; ?>
										<?php endif; ?>
									</optgroup>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>
				</div>
			</div>

			<div class="obp_wrap_three_column obp_coupon_setup_date">
				<a href="#" class="obp_clear_date_coupon">
					<i class="flaticon bookproicon-close"></i>
				</a>

				<div class="obp_column">
					<label><?php esc_html_e( 'Date', 'ovabookpro' ); ?></label>
					<input type="text" name="coupon_date" class="coupon_date" placeholder="<?php esc_attr_e( 'Choose date', 'ovabookpro' ); ?>" />
					<input type="hidden" name="start_date" id="start_date"
					value="<?php echo esc_attr( obp_format_date_Ymd( $coupon_item->get_start_date() ) ); ?>" />
					<input type="hidden" name="end_date" id="end_date"
					value="<?php echo esc_attr( obp_format_date_Ymd( $coupon_item->get_end_date() ) ); ?>" />
				</div>

				<div class="obp_column">
					<div class="obp_coupon_time_wrap">
						<div class="coupon_time_field">
							<label>
								<?php esc_html_e( 'From', 'ovabookpro' ); ?>
							</label>
							<input type="text" name="from_time" id="from_time"
							placeholder="<?php esc_attr_e( 'Choose time', 'ovabookpro' ); ?>"
							value="<?php echo esc_attr( $coupon_item->get_from_time() ); ?>" />
						</div>
						<div class="coupon_time_field">
							<label>
								<?php esc_html_e( 'To', 'ovabookpro' ); ?>
							</label>
							<input type="text" name="to_time" id="to_time"
							placeholder="<?php esc_attr_e( 'Choose time', 'ovabookpro' ); ?>"
							value="<?php echo esc_attr( $coupon_item->get_to_time() ); ?>" />
						</div>
					</div>
				</div>
		
				<div class="obp_column">
					<label><?php esc_html_e( 'Only Use For', 'ovabookpro' ); ?></label>
					<select name="use_on">
						<option value="booking_date" <?php selected( $coupon_item->get_use_on(), 'booking_date' ); ?>>
							<?php esc_html_e( 'For the booking date', 'ovabookpro' ); ?>
						</option>
						<option value="scheduled_date" <?php selected( $coupon_item->get_use_on(), 'scheduled_date' ); ?>>
							<?php esc_html_e( 'For the service usage date', 'ovabookpro' ); ?>
						</option>
					</select>
				</div>

			</div>
		</div>
		<div class="obp-form-submit">
			<?php if ( $coupon_id ): ?>
				<input type="submit" name="obp_submit_coupon" class="obp_button" value="<?php esc_attr_e( 'Update', 'ovabookpro' ); ?>">
			<?php else: ?>
				<input type="submit" name="obp_submit_coupon" class="obp_button" value="<?php esc_attr_e( 'Add', 'ovabookpro' ); ?>">
			<?php endif; ?>
		</div>
	</form>
</div>