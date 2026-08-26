<?php defined( 'ABSPATH' ) || exit;
?>

<!-- business hours -->
<div class="obp-form-part form-part-business">
	<h2 class="obp-second-title">
		<?php echo esc_html__( 'Business Hours*','ovabookpro' );?>	
	</h2>

	<div class="business-hours-wrapper">
		<div class="business-hours-field-wrapper business-hours-monday-wrapper">
			<div class="business_hours_field business_hours_monday">
				<label>
					<?php echo esc_html__( 'Monday', 'ovabookpro' ); ?>
				</label>
				<div class="business_hours">
					<?php if ( isset( $business_hours['monday'] ) ) : foreach ( $business_hours['monday'] as $key => $value ) : ?>
						<div class="business-hour">
							<input type="text" class="business_hour" name="start_hour" placeholder="<?php echo esc_attr( $time_format );?>"
							 	value="<?php echo esc_attr($value['start_hour']);?>"
							>
							<i class="bookproicon-remove"></i>
							<input type="text" class="business_hour" name="end_hour" placeholder="<?php echo esc_attr( $time_format );?>"
								value="<?php echo esc_attr( $value['end_hour'] );?>"
							>
							<a href="#" class="remove_business_hour" data-tippy-content="<?php echo esc_attr__( 'Remove Business Hour', 'ovabookpro' ); ?>">
								<i class="icon-close bookproicon-close"></i>
							</a>
						</div>
					<?php endforeach; endif; ?>
				</div>
			</div>

			<a href="#" class="obp_button add_hour">
				<?php echo esc_html__( 'Add Hour', 'ovabookpro' ); ?>
			</a>
		</div>

		<div class="business-hours-field-wrapper business-hours-tuesday-wrapper">
			<div class="business_hours_field business_hours_tuesday">
				<label>
					<?php echo esc_html__( 'Tuesday', 'ovabookpro' ); ?>
				</label>
				<div class="business_hours">
					<?php if ( isset( $business_hours['tuesday'] ) ) : foreach ( $business_hours['tuesday'] as $key => $value ) : ?>
						<div class="business-hour">
							<input type="text" class="business_hour" name="start_hour" placeholder="<?php echo esc_attr( $time_format );?>"
							 	value="<?php echo esc_attr( $value['start_hour'] );?>"
							>
							<i class="bookproicon-remove"></i>
							<input type="text" class="business_hour" name="end_hour" placeholder="<?php echo esc_attr( $time_format );?>"
							 	value="<?php echo esc_attr( $value['end_hour'] );?>"
							>
							<a href="#" class="remove_business_hour" data-tippy-content="<?php echo esc_attr__( 'Remove Business Hour', 'ovabookpro' ); ?>">
								<i class="icon-close bookproicon-close"></i>
							</a>
						</div>
					<?php endforeach; endif; ?>
				</div>
			</div>

			<a href="#" class="obp_button add_hour">
				<?php echo esc_html__( 'Add Hour', 'ovabookpro' ); ?>
			</a>
		</div>

		<div class="business-hours-field-wrapper business-hours-wednesday-wrapper">
			<div class="business_hours_field business_hours_wednesday">
				<label>
					<?php echo esc_html__( 'Wednesday', 'ovabookpro' ); ?>
				</label>
				<div class="business_hours">
					<?php if ( isset( $business_hours['wednesday'] ) ) : foreach ( $business_hours['wednesday'] as $key => $value ) : ?>
						<div class="business-hour">
							<input type="text" class="business_hour" name="start_hour" placeholder="<?php echo esc_attr( $time_format );?>"
							 	value="<?php echo esc_attr( $value['start_hour'] );?>"
							>
							<i class="bookproicon-remove"></i>
							<input type="text" class="business_hour" name="end_hour" placeholder="<?php echo esc_attr($time_format);?>"
							 	value="<?php echo esc_attr( $value['end_hour'] );?>"
							>
							<a href="#" class="remove_business_hour" data-tippy-content="<?php echo esc_attr__( 'Remove Business Hour', 'ovabookpro' ); ?>">
								<i class="icon-close bookproicon-close"></i>
							</a>
						</div>
					<?php endforeach; endif; ?>
				</div>
			</div>

			<a href="#" class="obp_button add_hour">
				<?php echo esc_html__( 'Add Hour', 'ovabookpro' ); ?>
			</a>
		</div>

		<div class="business-hours-field-wrapper business-hours-thursday-wrapper">
			<div class="business_hours_field business_hours_thursday">
				<label>
					<?php echo esc_html__( 'Thursday', 'ovabookpro' ); ?>
				</label>
				<div class="business_hours">
					<?php if ( isset( $business_hours['thursday'] ) ) : foreach ( $business_hours['thursday'] as $key => $value ) : ?>
						<div class="business-hour">
							<input type="text" class="business_hour" name="start_hour" placeholder="<?php echo esc_attr( $time_format );?>"
							 	value="<?php echo esc_attr( $value['start_hour'] );?>"
							>
							<i class="bookproicon-remove"></i>
							<input type="text" class="business_hour" name="end_hour" placeholder="<?php echo esc_attr( $time_format );?>"
							 	value="<?php echo esc_attr( $value['end_hour'] );?>"
							>
							<a href="#" class="remove_business_hour" data-tippy-content="<?php echo esc_attr__( 'Remove Business Hour', 'ovabookpro' ); ?>">
								<i class="icon-close bookproicon-close"></i>
							</a>
						</div>
					<?php endforeach; endif; ?>
				</div>
			</div>

			<a href="#" class="obp_button add_hour">
				<?php echo esc_html__( 'Add Hour', 'ovabookpro' ); ?>
			</a>
		</div>

		<div class="business-hours-field-wrapper business-hours-friday-wrapper">
			<div class="business_hours_field business_hours_friday">
				<label>
					<?php echo esc_html__( 'Friday', 'ovabookpro' ); ?>
				</label>
				<div class="business_hours">
					<?php if ( isset( $business_hours['friday'] ) ) : foreach ( $business_hours['friday'] as $key => $value ) : ?>
						<div class="business-hour">
							<input type="text" class="business_hour" name="start_hour" placeholder="<?php echo esc_attr( $time_format );?>"
							 	value="<?php echo esc_attr( $value['start_hour'] );?>"
							>
							<i class="bookproicon-remove"></i>
							<input type="text" class="business_hour" name="end_hour" placeholder="<?php echo esc_attr( $time_format );?>"
							 	value="<?php echo esc_attr( $value['end_hour'] );?>"
							>
							<a href="#" class="remove_business_hour" data-tippy-content="<?php echo esc_attr__( 'Remove Business Hour', 'ovabookpro' ); ?>">
								<i class="icon-close bookproicon-close"></i>
							</a>
						</div>
					<?php endforeach; endif; ?>
				</div>
			</div>

			<a href="#" class="obp_button add_hour">
				<?php echo esc_html__( 'Add Hour', 'ovabookpro' ); ?>
			</a>
		</div>

		<div class="business-hours-field-wrapper business-hours-saturday-wrapper">
			<div class="business_hours_field business_hours_saturday">
				<label>
					<?php echo esc_html__( 'Saturday', 'ovabookpro' ); ?>
				</label>
				<div class="business_hours">
					<?php if ( isset( $business_hours['saturday'] ) ) : foreach ( $business_hours['saturday'] as $key => $value ) : ?>
						<div class="business-hour">
							<input type="text" class="business_hour" name="start_hour" placeholder="<?php echo esc_attr( $time_format );?>"
							 	value="<?php echo esc_attr( $value['start_hour'] );?>"
							>
							<i class="bookproicon-remove"></i>
							<input type="text" class="business_hour" name="end_hour" placeholder="<?php echo esc_attr( $time_format );?>"
							 	value="<?php echo esc_attr( $value['end_hour'] );?>"
							>
							<a href="#" class="remove_business_hour" data-tippy-content="<?php echo esc_attr__( 'Remove Business Hour', 'ovabookpro' ); ?>">
								<i class="icon-close bookproicon-close"></i>
							</a>
						</div>
					<?php endforeach; endif; ?>
				</div>
			</div>

			<a href="#" class="obp_button add_hour">
				<?php echo esc_html__( 'Add Hour', 'ovabookpro' ); ?>
			</a>
		</div>

		<div class="business-hours-field-wrapper business-hours-sunday-wrapper">
			<div class="business_hours_field business_hours_sunday">
				<label>
					<?php echo esc_html__( 'Sunday', 'ovabookpro' ); ?>
				</label>
				<div class="business_hours">
					<?php if ( isset( $business_hours['sunday'] ) ) : foreach ( $business_hours['sunday'] as $key => $value ) : ?>
						<div class="business-hour">
							<input type="text" class="business_hour" name="start_hour" placeholder="<?php echo esc_attr( $time_format );?>"
							 	value="<?php echo esc_attr( $value['start_hour'] );?>"
							>
							<i class="bookproicon-remove"></i>
							<input type="text" class="business_hour" name="end_hour" placeholder="<?php echo esc_attr( $time_format );?>"
							 	value="<?php echo esc_attr( $value['end_hour'] );?>"
							>
							<a href="#" class="remove_business_hour" data-tippy-content="<?php echo esc_attr__( 'Remove Business Hour', 'ovabookpro' ); ?>">
								<i class="icon-close bookproicon-close"></i>
							</a>
						</div>
					<?php endforeach; endif; ?>
				</div>
			</div>

			<a href="#" class="obp_button add_hour">
				<?php echo esc_html__( 'Add Hour', 'ovabookpro' ); ?>
			</a>
		</div>
	</div>
</div>