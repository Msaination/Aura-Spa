<?php defined( 'ABSPATH' ) || exit; ?>
<div class="obp-time-container">

	<?php if ( ! empty( $work_hours ) ): ?>
	<div class="times_day_container">
		<div class="times_day">
			<?php
			$time_day_active = false;
			foreach ( $work_hours as $key => $time ):
				$start 	= strtotime( $time['start_hour'], $date_timestamp );
				$end 	= strtotime( $time['end_hour'], $date_timestamp );

				$data_work_hour = array(
					'start' => $start,
					'end' 	=> $end,
				);

				$is_active = '';
				if ( $time_day_active == false && $target_time >= $start && $target_time <= $end ) {
					$is_active = 'is-active';
					$time_day_active = true;
				}

				?>
				<div class="time <?php echo esc_attr( $is_active ); ?>"
					data-work-hour="<?php echo esc_attr( json_encode( $data_work_hour ) ); ?>">
					<?php echo esc_html( $time['label'] ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	<div class="obp-time-slider">
		<div class="owl-carousel owl-theme">
			<?php if ( ! empty( $time_slots ) ): ?>
			
				<?php foreach ( $time_slots as $key => $time ):
					$is_active = $target_time == $time ? 'is-active' : '';
					?>
					<div class="item">
						<div class="time-card <?php echo esc_attr( $is_active ); ?>"
							data-time="<?php echo esc_attr( $time ); ?>">
							<?php echo esc_html( date_i18n( $time_format, $time ) ); ?>
						</div>
					</div>
				<?php endforeach; ?>

			<?php endif; ?>
		</div>
	</div>
</div>