<?php defined( 'ABSPATH' ) || exit; ?>


<table class="obp_info_schedule">
	<tr>
		<th>
			<?php esc_html_e( 'ID', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $order->get_id() ); ?>
		</td>
	</tr>

	<tr>
		<th>
			<?php esc_html_e( 'Name', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $order->get_customer_name() ); ?>
		</td>
	</tr>

	<tr>
		<th>
			<?php esc_html_e( 'Email', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $order->get_customer_email() ); ?>
		</td>
	</tr>

	<tr>
		<th>
			<?php esc_html_e( 'Phone', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $order->get_customer_phone() ); ?>
		</td>
	</tr>

	<tr>
		<th>
			<?php esc_html_e( 'Note', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $order->get_customer_note() ); ?>
		</td>
	</tr>

	<tr>
		<th>
			<?php esc_html_e( 'Service', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $service->get_title() ); ?>
		</td>
	</tr>

	<tr>
		<th>
			<?php esc_html_e( 'Time', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $from_to ); ?>
		</td>
	</tr>

	<tr>
		<th>
			<?php esc_html_e( 'Staff', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo esc_html( $staff->get_nickname() ); ?>
		</td>
	</tr>
</table>