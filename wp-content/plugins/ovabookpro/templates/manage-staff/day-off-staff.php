<?php defined( 'ABSPATH' ) || exit;
$list_day_off 	= BookPro\StaffDayOff\OBP_Day_Off::get_list_staff_day_off( $user->ID );
$data_date 		= BookPro\StaffDayOff\OBP_Day_Off::get_data_date( $list_day_off );
?>



<tr class="obp_staff_day_off" data-user-id="<?php echo esc_attr( $user->ID ); ?>">
	<td colspan="4">
		<div class="day-off-wrapper">
			<h2 class="obp-second-title"><?php esc_html_e('Day off','ovabookpro'); ?></h2>
			<input type="hidden" name="all_dayoff" value="<?php echo esc_attr( json_encode( $data_date ) ); ?>" />
			<!-- day off list -->
			<div class="day-off-list-wrapper">
				<table>
					<?php if ( $list_day_off ): ?>
						<?php foreach ( $list_day_off as $day_off ):
							$day_off_obj = obp_get_day_off( $day_off );
							?>
							<?php obp_get_template( "manage-staff/dayoff-item.php", array( 'day_off_obj' => $day_off_obj ) ); ?>
						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td>
								<?php esc_html_e( 'Days off not found.', 'ovabookpro' ); ?>
							</td>
						</tr>	
					<?php endif; ?>
				</table>
			</div>

			<div class="day-off-form"></div>

			<!-- add day off -->
			<div class="obp-button-wrapper align-right add-day-off"> 
				<input type="button" name="obp_add_day_off" class="obp_button" value="<?php esc_attr_e( 'Add', 'ovabookpro' ); ?>">
			</div>

		</div>
	</td>
</tr>