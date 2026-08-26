<?php defined( 'ABSPATH' ) || exit; ?>

<table class="obp_order_items" style="border:1px solid #ddd; border-collapse: collapse;">
	<tr>
		<th style="border:1px solid #ddd; border-collapse: collapse; padding: 5px 10px;"><?php esc_html_e( 'Service Name', 'ovabookpro' ); ?></th>
		<th style="border:1px solid #ddd; border-collapse: collapse; padding: 5px 10px;"><?php esc_html_e( 'Time', 'ovabookpro' ); ?></th>
		<th style="border:1px solid #ddd; border-collapse: collapse; padding: 5px 10px;"><?php esc_html_e( 'Staff', 'ovabookpro' ); ?></th>
		<th style="border:1px solid #ddd; border-collapse: collapse; padding: 5px 10px;"><?php esc_html_e( 'Total', 'ovabookpro' ); ?></th>
	</tr>
	<?php if ( $order_items ): ?>
		<?php foreach ( $order_items as $item ):
			$order_item = obp_get_order_meta( $item );
			$service = obp_get_service( $order_item->get_service_id() );
			?>
			<tr>
				<td style="border:1px solid #ddd; border-collapse: collapse; padding: 5px 10px;">
					<?php echo esc_html( $order_item->get_service_name() ); ?>
					<?php if ( $order_item->get_package_names() ): ?>
						<br />
						<?php echo wp_kses_post( $order_item->get_package_names() ); ?>
					<?php endif; ?>
				</td>

				<td style="border:1px solid #ddd; border-collapse: collapse; padding: 5px 10px;">
					<?php echo esc_html( $order_item->get_time() ); ?>
				</td>

				<td style="border:1px solid #ddd; border-collapse: collapse; padding: 5px 10px;">
					<?php echo esc_html( $order_item->get_staff_name() ); ?>
				</td>
	
				<td style="border:1px solid #ddd; border-collapse: collapse; padding: 5px 10px;">
					<?php echo wp_kses_post( obp_get_price_html( $order_item->get_price(), $service->get_price_type()  ) ); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	
</table>