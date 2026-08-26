<?php defined( 'ABSPATH' ) || exit; ?>

<table class="obp_order_info">
	<tr>
		<td>
			<span class="label"><?php esc_html_e( 'Name', 'ovabookpro' ); ?></span>
			<p class="text"><?php echo esc_html( $order->get_customer_name() ); ?></p>
		</td>
		<td>
			<span class="label"><?php esc_html_e( 'Email', 'ovabookpro' ); ?></span>
			<p class="text"><?php echo esc_html( $order->get_customer_email() ); ?></p>
		</td>
	</tr>
	<tr>
		<td>
			<span class="label"><?php esc_html_e( 'Phone', 'ovabookpro' ); ?></span>
			<p class="text"><?php echo esc_html( $order->get_customer_phone() ); ?></p>
		</td>
		<td>
			<span class="label"><?php esc_html_e( 'Date Created', 'ovabookpro' ); ?></span>
			<p class="text"><?php echo esc_html( $order->get_date_created() ); ?></p>
		</td>
	</tr>

	<?php do_action( 'obp_order_body_mail_middle', $order ) ?>

	<?php if ( $order->get_customer_note() ): ?>
		<tr>
			<td>
				<span class="label"><?php esc_html_e( 'Note', 'ovabookpro' ); ?></span>
			</td>
			<td>
				<p class="text"><?php echo esc_html( $order->get_customer_note() ); ?></p>
			</td>
		</tr>
	<?php endif; ?>
	
</table>

<table class="obp_order_items">
	<tr>
		<th><?php esc_html_e( 'Service Name', 'ovabookpro' ); ?></th>
		<th><?php esc_html_e( 'Time', 'ovabookpro' ); ?></th>
		<th><?php esc_html_e( 'Staff', 'ovabookpro' ); ?></th>
		<th><?php esc_html_e( 'Total', 'ovabookpro' ); ?></th>
	</tr>
	<?php if ( $order_items ): ?>
		<?php foreach ( $order_items as $item ):
			$order_item = obp_get_order_meta( $item );
			$service = obp_get_service( $order_item->get_service_id() );
			?>
			<tr>
				<td>
					<?php echo esc_html( $order_item->get_service_name() ); ?>
					<?php if ( $order_item->get_package_names() ): ?>
						<br />
						<?php echo wp_kses_post( $order_item->get_package_names() ); ?>
					<?php endif; ?>
				</td>

				<td>
					<?php echo esc_html( $order_item->get_time() ); ?>
				</td>

				<td>
					<?php echo esc_html( $order_item->get_staff_name() ); ?>
				</td>
	
				<td>
					<?php echo wp_kses_post( obp_get_price_html( $order_item->get_price(), $service->get_price_type() ) ); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	
</table>

<table class="obp_order_total">
	<?php if ( $order->get_discount() ): ?>
		<tr>
			<th>
				<?php esc_html_e( 'Discount', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo wp_kses_post( '-'.obp_get_price_html( $order->get_discount() ) ); ?>
			</td>
		</tr>
	<?php endif; ?>
		
	<tr>
		<th>
			<?php esc_html_e( 'Subtotal', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo wp_kses_post( obp_get_price_html( $order->get_subtotal() ) ); ?>
		</td>
	</tr>

	<?php if ( $order->get_system_fee() ): ?>
		<tr>
			<th>
				<?php esc_html_e( 'System Fee', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo wp_kses_post( obp_get_price_html( $order->get_system_fee() ) ); ?>
			</td>
		</tr>
	<?php endif; ?>
	
	<?php if ( $order->get_tax_amount() ): ?>
		<tr>
			<th>
				<?php esc_html_e( 'Tax', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo wp_kses_post( obp_get_price_html( $order->get_tax_amount() ) ); ?>
			</td>
		</tr>
	<?php endif; ?>
	<tr>
		<th>
			<?php esc_html_e( 'Total', 'ovabookpro' ); ?>
		</th>
		<td>
			<?php echo wp_kses_post( obp_show_booking_total( $order->get_total(), $order->has_varies() ) ); ?>
		</td>
	</tr>
</table>