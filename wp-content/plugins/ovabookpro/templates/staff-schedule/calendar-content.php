<?php defined( 'ABSPATH' ) || exit; ?>

<div id="staff_schedule_calendar"
data-calendar="<?php echo esc_attr( json_encode( $data_calendar ) ); ?>"
data-timestep="<?php echo esc_attr( $timestep ); ?>"
data-init-date="<?php echo esc_attr( $first_day_of_month ); ?>"></div>