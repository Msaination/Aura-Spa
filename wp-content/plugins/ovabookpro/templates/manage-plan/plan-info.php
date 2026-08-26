<?php defined( 'ABSPATH' ) || exit; ?>



<table class="obp_plan_info_table">
	<tr>
		<th>
			<?php esc_html_e( 'Time', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $plan->get_date_str() ); ?>
		</td>
	</tr>
	<tr>
		<th>
			<?php esc_html_e( 'Working hours', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $plan->get_working_hours() ); ?>
		</td>
	</tr>
	<tr>
		<th>
			<?php esc_html_e( 'Services', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $plan->get_service_str() ); ?>
		</td>
	</tr>
</table>