<?php defined( 'ABSPATH' ) || exit; ?>

<!-- work hours -->
<div class="obp-form-part form-part-business">
	<h2 class="obp-second-title work-hours-title">
		<?php echo esc_html__('Define Time Periods for Each Part of the Day','ovabookpro'); ?>
		<span class="dashicons dashicons-info-outline obp-icon-help"
		data-tippy-content="<?php echo esc_attr__( "Please specify the time ranges for each part of the day according to your country's standard. Set the start and end times for: Morning, Noon, Afternoon, and Evening.", 'ovabookpro' ); ?>"></span>
	</h2>

	<div class="work-hours-wrapper">
		<?php if( !empty( $work_hours ) && is_array( $work_hours ) ) : foreach ( $work_hours as $key => $value ) : ?>
			<div class="work_hours_field">
				<input type="text" class="work_hour_label" name="work_hour_label" required
				 	value="<?php echo esc_attr( isset( $value['label'] ) ? $value['label'] : '' );?>" 
				>
				<div class="work_hours">
					<input type="text" class="work_hour" name="start_hour" placeholder="<?php echo esc_attr( $time_format );?>" required
						value="<?php echo esc_attr( isset( $value['start_hour'] ) ? $value['start_hour'] : '' );?>"
					>
					<i class="bookproicon-remove"></i>
					<input type="text" class="work_hour" name="end_hour" placeholder="<?php echo esc_attr( $time_format );?>" required
						value="<?php echo esc_attr( isset( $value['end_hour'] ) ? $value['end_hour'] : '' );?>"
					>

					<a href="#" class="remove_work_hour"
					data-tippy-content="<?php echo esc_attr__( 'Remove Work Hour', 'ovabookpro' ); ?>">
						<i class="icon-close bookproicon-close"></i>
					</a>
				</div>
			</div>
		<?php endforeach; endif; ?>		
	</div>

	<a href="#" class="obp_button add_work_hour">
		<?php echo esc_html__( 'Add Time Period', 'ovabookpro' ); ?>
	</a>
</div>