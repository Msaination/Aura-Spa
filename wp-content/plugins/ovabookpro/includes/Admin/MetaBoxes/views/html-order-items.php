<?php defined( 'ABSPATH' ) || exit;

use BookPro\Order\OBP_Order_Meta;
global $post;

$order 			= obp_get_order( $post->ID );
$order_items 	= OBP_Order_Meta::get_order_items( $post->ID );
$check_tax 		= $order->get_tax_amount();
?>

<div class="obp_order_items_wrap">

	<table class="obp_order_items">
		<tr>
			<th>
				<?php esc_html_e( 'Service Name', 'ovabookpro' ); ?>
			</th>
			<th>
				<?php esc_html_e( 'Time', 'ovabookpro' ); ?>
			</th>
			<th>
				<?php esc_html_e( 'Staff', 'ovabookpro' ); ?>
			</th>
			<th>
				<?php esc_html_e( 'Total', 'ovabookpro' ); ?>
			</th>
			<?php if ( $check_tax ): ?>
				<th><?php esc_html_e( 'Tax', 'ovabookpro' ); ?></th>
			<?php endif; ?>
		</tr>

		<?php if ( ! empty( $order_items ) ): ?>
			<?php foreach ( $order_items as $key => $item ):
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
						<?php echo wp_kses_post( obp_get_price_html( $order_item->get_price() , $service->get_price_type() ) ); ?>
					</td>
					
					<?php if ( $order_item->get_taxes_line() ): ?>
						<td>
							<?php echo wp_kses_post( $order_item->get_taxes_line() ); ?>
						</td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>

	</table>

</div>