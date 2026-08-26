<?php defined( 'ABSPATH' ) || exit;
	$data_calendar = BookPro\Staff\OBP_Staff::get_data_calendar_by_user_id( $user->ID);
?>

<tr class="staff-schedule-wrapper" data-user-id="<?php echo esc_attr( $user->ID ); ?>" >
	<td colspan="4">
		<div id="obp_staff_calendar_<?php echo esc_attr( $user->ID );?>"
			data-timestep="<?php echo esc_attr( $timestep ); ?>"
			data-calendar="<?php echo esc_attr( json_encode( $data_calendar ) ); ?>"></div>
	</td>
</tr>