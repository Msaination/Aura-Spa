<?php defined( 'ABSPATH' ) || exit; ?>

<h1 class="obp-title">
	<?php esc_html_e( 'My Schedule', 'ovabookpro' ); ?>	
</h1>

<div class="obp-content obp-content-staff-schedule">

	<div class="obp-form-part">
		<div class="obp_staff_schedule_wrap">
			<?php do_action( 'obp_staff_schedule_main_content' ); ?>
		</div>
	</div>
</div>