<?php
defined( 'ABSPATH' ) || exit;
?>

<div class="obp_add_to_calendar_wrapper">
	<div class="obp_calendar_innner">
		<input type="text" id="calendar_start_date" name="calendar_start_date" placeholder="<?php esc_attr_e( 'Start date', 'ovabookpro' ) ?>" />
		<input type="text" id="calendar_end_date" name="calendar_end_date" placeholder="<?php esc_attr_e( 'End date', 'ovabookpro' ); ?>" />
		<input type="hidden" id="cal_start_date" name="cal_start_date" value="" />
		<input type="hidden" id="cal_end_date" name="cal_end_date" value="" />
	</div>
	
	<div class="obp_cal_mess"></div>

	<div class="obp_calendar_action">

		<button type="button" class="obp_button" id="obp_order_calendar_add_events">
			<?php esc_html_e( 'Add Google Calendar', 'ovabookpro' ); ?>
		</button>

		<button type="button" class="obp_button" id="obp_order_ical_add_events">
			<?php esc_html_e( 'Add Ical', 'ovabookpro' ); ?>
		</button>
	</div>
</div>