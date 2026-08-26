<?php defined( 'ABSPATH' ) || exit;
use BookPro\Service\OBP_Service;

?>

<h1 class="obp-title">
	<?php if ( $post_id ): ?>
		<?php echo esc_html__( 'Edit Service', 'ovabookpro' ); ?>	
	<?php else: ?>
		<?php echo esc_html__( 'Add Service', 'ovabookpro' ); ?>
	<?php endif; ?>
</h1>

<div class="obp-content obp-content-service">

	<form enctype="multipart/form-data" method="post" autocomplete="off">

		<div class="obp-form-part">
			<h2 class="obp-second-title">
				<?php echo esc_html__('General','ovabookpro' );?>	
			</h2>

			<!-- two_column -->
			<div class="obp_wrap_two_column">
				<div class="obp_column">
					<label for="service_name">
						<?php echo esc_html__('Name*','ovabookpro' );?>	
					</label>
					<input type="text" id="service_name" name="service_name"
						placeholder="<?php echo esc_attr__('Name service','ovabookpro' );?>"
						value="<?php echo esc_attr( $service_name ); ?>" required
					>
				</div>

				<div class="obp_column">
					<label for="service_type">
						<?php echo esc_html__('Type','ovabookpro' );?>	
					</label>

					<select class="service_type" id="service_type" name="service_type">
						<option value="">
							<?php echo esc_html__( 'Choose a type','ovabookpro' );?>
						</option>
						<?php if ( count( $all_types ) > 0 ): ?>
							<?php foreach ( $all_types as $type_id ) {
								$type_obj = obp_get_type( $type_id );
								if ( $type_obj ) {
									?>
									<option value="<?php echo esc_attr( $type_obj->get_id() ); ?>" <?php selected( $type, $type_obj->get_id() ); ?>>
										<?php echo esc_html( $type_obj->get_name() ); ?>
									</option>
									<?php
								}
							} ?>
						<?php endif;
						?>
					</select>

					<a href="<?php echo esc_url( $add_type_url );?>" class="add_new_type has-underline">
						<?php echo esc_html__('Add Type','ovabookpro' ); ?>
					</a>
				</div>
			</div>

			<!-- duration/pricing -->
			<div class="service-duration-pricing-wrapper">
				<label>
					<?php echo esc_html__('Duration/Pricing','ovabookpro' );?>	
				</label>

				<div class="service_duration_pricing">
					<div class="service_hours">
						<label for="service_hour">
							<?php echo esc_html__('Hour','ovabookpro');?>	
						</label>
						<select class="service_hour" id="service_hour" name="service_hour">
							<?php foreach ( OBP_Service::get_hours() as $key => $value ) : ?>
								<option value="<?php echo esc_attr($key); ?>"
									<?php echo esc_attr( $hour == $key ? 'selected' : ''); ?>
								>
									<?php echo esc_html( $value ); ?>	
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="service_minutes">
						<label for="service_minute">
							<?php echo esc_html__( 'Minutes','ovabookpro' );?>	
						</label>
						<select class="service_minute" id="service_minute" name="service_minute">
							<?php foreach ( OBP_Service::get_minutes() as $key => $value ) : ?>
								<option value="<?php echo esc_attr($key); ?>" 
									<?php echo esc_attr($minute == $key ? 'selected' : ''); ?>
								>
									<?php echo esc_html( $value ); ?>	
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="service_price_types">
						<label for="service_price_type">
							<?php echo esc_html__('Price Type','ovabookpro' );?>	
						</label>
						<select class="service_price_type" id="service_price_type" name="service_price_type">
							<?php foreach ( OBP_Service::get_price_types() as $key => $value ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>"
									<?php echo esc_attr( $price_type == $key ? 'selected' : ''); ?>
								>
									<?php echo esc_html( $value ); ?>	
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="service_price_wrap">
						<label for="service_price">
							<?php echo esc_html__('Price','ovabookpro' ); ?>	
						</label>
						<span class="dashicons dashicons-editor-help obp-icon-help" data-tippy-content="<?php echo esc_attr( $price_help_content ); ?>"></span>
						<input type="text" id="service_price" name="service_price" value="<?php echo esc_attr( $price ); ?>"
						>
					</div>
				</div>
			</div>


			<div class="note_price_wrapper">
				<textarea id="note_price"
				name="note_price"
				placeholder="<?php echo esc_attr__( 'Note for customer about price', 'ovabookpro' ); ?>"><?php echo esc_html( $note_price ); ?></textarea>
			</div>

			<div class="obp_service_extra_option">
				<div class="obp_service_package_group_container">
					<?php if ( ! empty( $packages ) ):
						?>
						<?php foreach ( $packages as $key => $package ): ?>
							<?php obp_get_template( 'manage-service/service-package-group.php', array( 'package' => $package ) ); ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

				<button type="button" class="obp_button" id="obp_add_extra_option">
					<?php echo esc_html__( 'Add Extra Option', 'ovabookpro' ); ?>
				</button>
			</div>

			<!-- description -->
			<div class="service_description">
				<label for="service_description">
					<?php echo esc_html__( 'Description','ovabookpro' ); ?>	
				</label>
				<?php wp_editor( wpautop( $service->get_description() ), 'service_description', $settings_editor ); ?>
			</div>

			<!-- color -->
			<div class="service_color">
				<label for="service_color">
					<?php echo esc_html__( 'Feature Color','ovabookpro' );?>	
				</label>
				<input type="text" id="service_color" name="service_color" value="<?php echo esc_attr( $color );?>" 
					data-default-color="<?php echo esc_attr($color);?>"
				>
			</div>

			<!-- employee: staff -->
			<div class="service_employee">
				<h2 class="obp-second-title">
					<?php echo esc_html__( 'Service Staff','ovabookpro' );?>	
				</h2>
				<div class="service_staff_wrapper">
					<?php obp_get_template( 'manage-service/service-staff-list.php', $args ); ?>
				</div>
				<div class="empty_staff">
					<a href="<?php echo esc_url( $add_staff_url ); ?>" class="obp_add_staff has-underline">
						<?php echo esc_html__( 'Add Staff', 'ovabookpro' ); ?>
					</a>
				</div>
			</div>

			<h2 class="obp-second-title">
				<?php echo esc_html__( 'Sale Off','ovabookpro' );?>	
			</h2>

			<div class="obp_service_sale_wrap">
				<a href="#" class="obp_clear_service_sale"><i class="flaticon bookproicon-close"></i></a>

				<div class="obp_column">
					<div class="service_sale_off_price">
						<label for="service_sale_price">
							<?php echo esc_html__( 'Sale price', 'ovabookpro' ); ?>
						</label>
						<input type="text" id="service_sale_price" name="service_sale_price" value="<?php echo esc_attr( $sale_price ); ?>" />
					</div>
				</div>
				<div class="obp_column">
					<div class="obp_applicable_time">
						<div class="service_sale_off_date">
							<label>
								<?php echo esc_html__( 'Applicable Time', 'ovabookpro' ); ?>
							</label>
							<input type="text" id="service_sale_off_start_date" name="service_sale_off_start_date" value="<?php echo esc_attr( $sale_off_start_date ); ?>" placeholder="<?php echo esc_attr__( 'Date', 'ovabookpro' ); ?>" />
							
						</div>

						<div class="service_sale_off_time">
							<input type="text" id="service_sale_off_from" name="service_sale_off_from" value="<?php echo esc_attr( $sale_off_from ); ?>" placeholder="<?php echo esc_attr__( 'Time', 'ovabookpro' ); ?>" />
						</div>
					</div>
				</div>
				<div class="obp_column">
					<label><?php echo esc_html__( 'To', 'ovabookpro' ); ?></label>
					<div class="obp_applicable_time">
						<input type="text" id="service_sale_off_end_date" name="service_sale_off_end_date" value="<?php echo esc_attr(  $sale_off_end_date ); ?>" placeholder="<?php echo esc_attr__( 'Date', 'ovabookpro' ); ?>" />

						<input type="text" id="service_sale_off_to" name="service_sale_off_to" value="<?php echo esc_attr( $sale_off_to ); ?>" placeholder="<?php echo esc_attr__( 'Time', 'ovabookpro' ); ?>" />
					</div>
				</div>
			</div>

			<div class="obp_service_use_on_wrap">
				<div class="obp_column">
					<label><?php echo esc_html__( 'Only Use:', 'ovabookpro' ); ?></label>
					<select name="use_on">
						<option value="booking_date" <?php selected( $use_on, 'booking_date' ); ?>>
							<?php echo esc_html__( 'For the booking date', 'ovabookpro' ); ?>
						</option>
						<option value="scheduled_date" <?php selected( $use_on, 'scheduled_date' ); ?>>
							<?php echo esc_html__( 'For the service usage date', 'ovabookpro' ); ?>
						</option>
					</select>
				</div>
			</div>

			<?php if ( $vendor_choose_tax == 'yes' ): ?>

				<h2 class="obp-second-title">
					<?php echo esc_html__( 'Tax','ovabookpro' );?>	
				</h2>

				<div class="obp_wrap_three_column">
					<div class="obp_column">
						<label for="tax_class"><?php echo esc_html__( 'Tax Class', 'ovabookpro' ); ?></label>
						<select name="tax_class" id="tax_class">
							<option value="">
								<?php echo esc_html__( 'None', 'ovabookpro' ); ?>
							</option>
							<?php if ( ! empty( $tax_classes ) ): ?>
								<?php foreach ( $tax_classes as $key => $obj ): ?>
									<option value="<?php echo esc_attr( $obj->term_id ); ?>" <?php selected( $tax_class, $obj->term_id ); ?>>
										<?php echo esc_html( $obj->name ); ?>
									</option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>
				</div>
			<?php endif; ?>

			<!-- submit -->
			<div class="obp-form-submit align-right">

				<?php if ( $post_id ): ?>
					<input type="submit"
					name="obp_update_service"
					class="obp_button"
					value="<?php echo esc_attr__( 'Update Service', 'ovabookpro' ); ?>">
				<?php else: ?>
					<input type="submit"
					name="obp_update_service"
					class="obp_button"
					value="<?php echo esc_attr__( 'Add Service', 'ovabookpro' ); ?>">
				<?php endif; ?>

				<input type="hidden" id="post_id" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
				<input type="hidden" name="current_language" value="<?php echo esc_attr( obp_get_current_language() ); ?>">
				<?php wp_nonce_field( 'obp_edit_service_nonce', 'obp_edit_service_nonce' ); ?>
			</div>

		</div>

	</form>

</div>