<?php defined( 'ABSPATH' ) || exit;
$order = obp_get_order( $order_id );
if ( ! empty( $cart_content ) ): ?>
			
	<?php foreach ( $cart_content as $key => $item ):

		$from 		= date_i18n( $time_format, $item->get_start_date() );
		$to 		= date_i18n( $time_format, $item->get_end_date() );
		$avatar_url = get_avatar_url( $item->get_staff_id() );
		$user 		= obp_get_user( $item->get_staff_id() );
		$avatar 	= $user->get_avatar();
		$staff_name = $user->get_nickname();
		$service 	= obp_get_service( $item->get_service_id() );
		$staff_ids  = $service->get_staff_ids();

		$package_ids = $item->get_package_ids();
		$next_timestamp = 0;
		$next_key = $key + 1;
		$next_item = isset( $cart_content[$next_key] ) ? $cart_content[$next_key] : "";
		if ( ! empty( $next_item ) ) {
			$next_timestamp = absint( $next_item->get_start_date() ) - absint( $item->get_end_date() );
		}
		?>
		
		<div class="obp-order-item">
			<a href="#" class="sort_item">
				<i class="bookproicon-all-directions"></i>
			</a>
			<div class="order-meta">
				<h5 class="service-name">
					<?php echo esc_html( $item->get_service_name() ); ?>
				</h5>
				<div class="service-info">
					<div class="price"><?php echo wp_kses_post( obp_get_price_html( $item->get_price(), $service->get_price_type() ) ); ?></div>
					<div class="times"><?php echo esc_html( $from.' - '.$to ); ?></div>
				</div>
			</div>
			<div class="staff-info">
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

				<?php if ( count( $staff_ids ) > 1 ): ?>
					<a href="#" class="edit-staff"
					data-service-id="<?php echo esc_attr( $item->get_service_id() ); ?>">
						<i class="bookproicon-edit"></i>
					</a>
				<?php endif; ?>

			</div>

			<?php if ( ! empty( $package_ids ) ): ?>
				<div class="service-packages" data-service-id="<?php echo esc_attr( $item->get_service_id() ); ?>">
					<?php foreach ( $package_ids as $key => $package_id ):
						obp_get_template( 'my-booking/popup-form/package-item.php', array( 'package_id' => $package_id ) );
						?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $next_timestamp > 0 ):?>
		
				<div class="obp-order-wait">
					<?php // translators: %s: number of minutes.
					echo sprintf( esc_html__( 'Wait %s', 'ovabookpro' ), esc_html( obp_timestamp_to_hour_minute( $next_timestamp ) ) ); ?>
				</div>

			<?php endif; ?>
			
		</div>
	<?php endforeach; ?>

<?php endif;?>