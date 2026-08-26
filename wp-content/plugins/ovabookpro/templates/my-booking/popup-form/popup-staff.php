<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp_change_order_staff_container"
data-service-id="<?php echo esc_attr( $service_id ); ?>"
data-order-id="<?php echo esc_attr( $order_id ); ?>"
data-nonce="<?php echo esc_attr( wp_create_nonce( 'obp_order_staff' ) ); ?>">

	<h3 class="popup-title"><?php esc_html_e( 'Staff List', 'ovabookpro' ); ?></h3>

	<?php if ( ! empty( $staff_ids ) ): ?>
	
		<div class="list_staffs">
			<?php foreach ( $staff_ids as $key => $staff_id ):
				$avatar_url = get_avatar_url( $staff_id );
				$user 		= obp_get_user( $staff_id );
				$avatar 	= $user->get_avatar();
				$staff_name = $user->get_nickname();
				$is_active 	= $current_staff_id == $staff_id ? 'is-active' : '';
				?>
			
				<div class="staff-card <?php echo esc_attr( $is_active ); ?>" data-id="<?php echo esc_attr( $staff_id ); ?>">
					<div class="avatar">
						<?php if ( ! empty( $avatar ) ) {
							echo wp_kses_post( $avatar );
						} else { ?>
							<img src="<?php echo esc_url( $avatar_url ); ?>" alt="avatar">
						<?php } ?>
					</div>
					<h5 class="staff-name">
						<?php echo esc_html( $staff_name ); ?>
					</h5>
				</div>

			<?php endforeach; ?>
		</div>

	<?php endif; ?>

</div>