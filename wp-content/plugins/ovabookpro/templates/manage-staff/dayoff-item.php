<?php defined( 'ABSPATH' ) || exit;
	$date_format = OBP()->settings->general->get('date_format', 'Y-m-d');
?>
<tr>
	<td>
		<?php echo esc_html( date_i18n( $date_format, $day_off_obj->get_start_date() ) ); ?>
		<i class="flaticon bookproicon-remove"></i>
		<?php echo esc_html( date_i18n( $date_format, $day_off_obj->get_end_date() ) ); ?>
	</td>
	<td>
		<?php echo esc_html( $day_off_obj->get_time_translate() ); ?>
	</td>
	<td>
		<div class="day_off_action_wrap">
			<input type="hidden" name="dayoff_id" value="<?php echo esc_attr( $day_off_obj->get_id() ); ?>" />
			<a href="#" class="obp_action_edit_day_off">
				<i class="bookproicon-edit"></i>
			</a>
			<a href="#" class="obp_action_delete_day_off">
				<i class="bookproicon-close"></i>
			</a>
		</div>
	</td>
</tr>



	