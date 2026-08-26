<?php defined( 'ABSPATH' ) || exit;
$date_format 	= OBP()->settings->general->get('date_format', 'Y-m-d');
$day_off 		= BookPro\StaffDayOff\OBP_Day_Off::get_day_off_item( $day_off_id );

$day_off_obj 	= obp_get_day_off( $day_off );
$time 			= $day_off_obj->get_time();
$start_date 	= gmdate( "Y-m-d", $day_off_obj->get_start_date() );
$end_date 		= gmdate( "Y-m-d", $day_off_obj->get_end_date() );
$hour_off 		= $day_off_obj->get_hour_off();
$time_format 	= OBP()->settings->general->get('time_format', 'H:i');
?>

<div class="obp_day_off_messages"></div>
<a href="#" role="button" class="obp_remove_form_day_off">
	<i class="bookproicon-close"></i>
</a>
<input type="hidden" name="day_off_id" value="<?php echo esc_attr( $day_off_id ); ?>" />
<div class="day-off-inner-form">

	<div class="day-off-start-field">
		<input type="text" class="day_off" name="day_off_start" 
		value="<?php echo esc_attr( $start_date ); ?>" placeholder="<?php echo esc_attr( $date_format ); ?>" />
	</div>

	<div class="day-off-end-field">
		<span class="text-separator">
			<?php echo esc_html__('to','ovabookpro');?>
		</span>
		<input type="text" class="day_off" name="day_off_end" 
			value="<?php echo esc_attr( $end_date ); ?>" placeholder="<?php echo esc_attr( $date_format ); ?>" />
	</div>
	<div class="off_hours_field">
		<span class="prefix"><?php esc_html_e( 'at', 'ovabookpro' ); ?></span>
		<input type="text" id="off_hours_type" placeholder="<?php echo esc_attr( $day_off_obj->get_time_translate() ) ?>" readonly value="">
		<div class="off_hours_card">
			<label for="full_time" class="obp_radio">
				
				<input type="radio" id="full_time" name="off_time" <?php checked( $time, 'full_time' ); ?> class="off_time" value="full_time" data-label="<?php esc_attr_e( 'Full Time', 'ovabookpro' ); ?>" />
				<span class="checkmark"></span>
				<?php esc_html_e( 'Full Time', 'ovabookpro' ); ?>
			</label>
			<label for="custom_time" class="obp_radio">
				<input type="radio" id="custom_time" name="off_time" class="off_time" <?php checked( $time, 'custom_time' ); ?> value="custom_time" data-label="<?php esc_attr_e( 'Custom Time', 'ovabookpro' ); ?>" />
				<span class="checkmark"></span>
				<?php esc_html_e( 'Custom Time', 'ovabookpro' ); ?>
			</label>
			<div class="custome_time_card">
				<ul class="custom_time_items">

					<?php if ( ! empty( $hour_off ) ): ?>

						<?php foreach ( $hour_off as $time ):
							$start_time = obp_calendar_Hi_to_seconds( $time['start_hour'] );
							$end_time 	= obp_calendar_Hi_to_seconds( $time['end_hour'] );

							$start_time_format 	= gmdate( $time_format, strtotime( $time['start_hour'] ) );
							$end_time_format 	= gmdate( $time_format, strtotime( $time['end_hour'] ) );
							?>
							
							<li class="custom_time_item">
								<input type="text" class="start_time off_custom_time" data-time="<?php echo esc_attr( $start_time ); ?>" value="<?php echo esc_attr( $start_time_format ); ?>" />
								<input type="text" class="end_time off_custom_time" data-time="<?php echo esc_attr( $end_time ); ?>" value="<?php echo esc_attr( $end_time_format ); ?>" />
								<span class="obp_remove_custom_time"><i class="flaticon bookproicon-close"></i></span>
							</li>
						<?php endforeach; ?>

					<?php endif; ?>

				</ul>
				<div class="card_button">
					<button type="button" class="obp_add_off_time"><?php esc_html_e( 'Add', 'ovabookpro' ); ?></button>
				</div>
				
			</div>
		</div>
	</div>

</div>

</div>

<div class="obp-button-wrapper align-right">
	<input type="button" name="obp_update_day_off" class="obp_button" value="<?php esc_attr_e( 'Update', 'ovabookpro' ); ?>" />
</div>
