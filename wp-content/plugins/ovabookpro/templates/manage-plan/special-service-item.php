<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp_special_service_item" data-service-id="<?php echo esc_attr( $service->get_id() ); ?>">
	<div class="service_name_wrap">
		<p class="service_name"><?php echo esc_html( $service->get_title() ); ?></p>
	</div>
	<div class="service_times">
		<div class="list_service_time">
			<div class="obp_service_time_item">
				<input type="text" class="start_time service_time" value=""
				data-time="0"/>
				<input type="text" class="end_time service_time" value=""
				data-time="0"/>
			</div>
		</div>
		
		<div class="service_time_bottom">
			<a href="#" class="obp_add_service_time obp_button">
				<?php esc_html_e( 'Add', 'ovabookpro' ); ?>
			</a>
		</div>
	</div>
</div>