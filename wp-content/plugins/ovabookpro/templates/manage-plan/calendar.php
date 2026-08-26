<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp_plan_calendar_wrap">
	<div id="obp_plan_calendar" data-calendar="<?php echo esc_attr( json_encode( $data_calendar ) ); ?>"></div>
	<ul class="status_info">
		<li class="status_item">
			<div class="box_color open_all_service"></div>
			<span><?php echo esc_html__( 'Open All Services', 'ovabookpro' ); ?></span>
		</li>
		<li class="status_item">
			<div class="box_color open_some_service"></div>
			<span><?php echo esc_html__( 'Open Some Services', 'ovabookpro' ); ?></span>
		</li>
		<li class="status_item">
			<div class="box_color closed_service"></div>
			<span><?php echo esc_html__( 'Closed All Services', 'ovabookpro' ); ?></span>
		</li>
		<li class="status_item">
			<div class="box_color closed_some_service"></div>
			<span><?php echo esc_html__( 'Closed Some Services', 'ovabookpro' ); ?></span>
		</li>
	</ul>
</div>