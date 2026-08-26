<?php defined( 'ABSPATH' ) || exit;

global $wp_locale;

$business 		= obp_get_business( $business_id );
$business_hours = $business->get_business_hours();
$keys 			= array_keys( $business_hours );
$weekday 		= obp_get_weekday();

?>

<div class="obp_booking_calendar_step">
	<div class="obp-calendar-wrapper">
		<h4 class="month-year"><?php echo esc_html( date_i18n( "F Y", strtotime( $target_date ) ) ); ?></h4>
		<div class="obp-calendar-slider"
		data-target-date="<?php echo esc_attr( $target_date ); ?>">
			<div class="owl-carousel owl-theme" data-prev="<?php echo esc_attr( $data_prev ); ?>" data-start-date="<?php echo esc_attr( $days_arr[0] ); ?>" data-end-date="<?php echo esc_attr( $data_end_date ); ?>">
				<?php foreach ( $days_arr as $date ):
					$timestamp 		= strtotime( $date );
					$weekday_number = gmdate( 'w', $timestamp );
					$weekday_key 	= isset( $weekday[$weekday_number] ) ? $weekday[$weekday_number] : '';
					$weekday_name 	= $wp_locale->get_weekday( $weekday_number );
					$weekday_abbrev = $wp_locale->get_weekday_abbrev( $weekday_name );
					$is_active 		= $target_date == $date ? 'is-active': '';
					if ( in_array($weekday_key, $keys) ) {
					?>
					<div class="item">
						<div class="date-card <?php echo esc_attr( $is_active ); ?>"
							data-date="<?php echo esc_attr( $date ); ?>">
							<div class="day">
								<?php echo esc_html( date_i18n( 'j', $timestamp ) ); ?>
							</div>
							<div class="weekday">
								<?php echo esc_html( $weekday_abbrev ); ?>
							</div>
						</div>
					</div>
				<?php
					}
				endforeach; ?>
			    
			</div>
		</div> <!-- .obp-calendar-slider -->

	</div>

	<div class="obp-calendar-content">
		<?php do_action( 'obp_booking_form_calendar', $args ); ?>
	</div>

</div>