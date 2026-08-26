<?php defined( 'ABSPATH' ) || exit;

$endpoint 	= OBP()->endpoint->get_endpoint('edit-staff');
$url 		= OBP()->endpoint->get_endpoint_url( $endpoint, $user->ID , obp_member_account_url() );
$timestep 	= OBP()->settings->general->get('time_step', '00:30:00');
$user_item  = obp_get_user( $user->ID );

?>

<tr class="obp_staff_item_info" data-id="<?php echo esc_attr( $user->ID ); ?>">
	<td>
		<?php echo esc_html( $key + 1 );?>
	</td>
	<td>
		<div class="obp_staff_name_wrap">

			<div class="staff-avatar">
				<?php if ( $user_item->get_avatar() ): ?>
					<?php echo wp_kses_post( $user_item->get_avatar() ); ?>
				<?php else: ?>
					<?php echo get_avatar( $user->ID ); ?>
				<?php endif; ?>
				
			</div>
			<h3 class="info_name obp-label-title">
				<?php echo esc_html( $user_item->get_nickname() );?>
			</h3>

		</div>
	</td>

	<td>
		<?php echo esc_html( $user_item->get_role_name() );?>
	</td>
	<td>
		<div class="obp-data-action staff-action" data-user-id="<?php echo esc_attr( $user->ID ); ?>" >
			<a href="#" class="show_calendar" data-tippy-content="<?php esc_attr_e( 'Schedule', 'ovabookpro' ); ?>">
				<i class="bookproicon-calendar"></i>
			</a>
			<a href="#" class="show_holidays" data-tippy-content="<?php esc_attr_e( 'Days Off', 'ovabookpro' ); ?>">
				<i class="bookproicon-holidays"></i>
			</a>
			<a href="<?php echo esc_url( $url );?>" data-tippy-content="<?php esc_attr_e( 'Edit Staff', 'ovabookpro' ); ?>">
				<i class="bookproicon-edit"></i>
			</a>
			<a href="#" class="obp_action_delete_staff" data-tippy-content="<?php esc_attr_e( 'Remove Staff', 'ovabookpro' ); ?>">
				<i class="bookproicon-close"></i>
			</a>
			<input type="hidden" id="user_id" name="user_id" value="<?php echo esc_attr( $user->ID ); ?>">
		</div>
	</td>
</tr>

<?php
	obp_get_template('manage-staff/schedule-staff.php', array( 'user' => $user, 'timestep' => $timestep ) );
	obp_get_template('manage-staff/day-off-staff.php', array('user' => $user ));
?>


