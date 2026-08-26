<?php defined( 'ABSPATH' ) || exit;
$date_format = OBP()->settings->general->get('date_format', 'Y-m-d');
?>

<div class="obp_day_off_messages"></div>
<a href="#" role="button" class="obp_remove_form_day_off">
	<i class="bookproicon-close"></i>
</a>
<div class="day-off-inner-form">

	<div class="day-off-start-field">
		<input type="text" class="obp_datepicker day_off" name="day_off_start" 
		value="" placeholder="<?php echo esc_attr( $date_format ); ?>" />
	</div>

	<div class="day-off-end-field">
		<span class="text-separator">
			<?php echo esc_html__('to','ovabookpro');?>
		</span>
		<input type="text" class="obp_datepicker day_off" name="day_off_end" 
			value="" placeholder="<?php echo esc_attr( $date_format ); ?>" />
	</div>
	<div class="off_hours_field">
		<span class="prefix"><?php esc_html_e( 'at', 'ovabookpro' ); ?></span>
		<input type="text" id="off_hours_type" placeholder="<?php esc_attr_e( 'Full Time', 'ovabookpro' ); ?>" readonly value="">
		<div class="off_hours_card">
			<label for="full_time" class="obp_radio">
				
				<input type="radio" id="full_time" name="off_time" checked class="off_time" value="full_time" data-label="<?php esc_attr_e( 'Full Time', 'ovabookpro' ); ?>" />
				<span class="checkmark"></span>
				<?php esc_html_e( 'Full Time', 'ovabookpro' ); ?>
			</label>
			<label for="custom_time" class="obp_radio">
				<input type="radio" id="custom_time" name="off_time" class="off_time" value="custom_time" data-label="<?php esc_attr_e( 'Custom Time', 'ovabookpro' ); ?>" />
				<span class="checkmark"></span>
				<?php esc_html_e( 'Custom Time', 'ovabookpro' ); ?>
			</label>
			<div class="custome_time_card">
				<ul class="custom_time_items">
					<li class="custom_time_item">
						<input type="text" class="start_time off_custom_time" data-time="0" />
						<input type="text" class="end_time off_custom_time" data-time="0" />
						<span class="obp_remove_custom_time"><i class="flaticon bookproicon-close"></i></span>
					</li>
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
