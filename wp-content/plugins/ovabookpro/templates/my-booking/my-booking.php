<?php defined( 'ABSPATH' ) || exit;
$orders = BookPro\Order\OBP_Order::instance()->get_orders_ajax();
?>

<h1 class="obp-title">
	<?php esc_html_e( 'My Booking', 'ovabookpro' ); ?>	
</h1>

<div class="obp-content obp-content-orders obp-content-my-orders">

	<div class="obp-form-part">

		<?php do_action( 'obp_my_booking_before_main_content' ); ?>

		<div class="obp-data-list-wrapper my-orders-list-wrapper obp-table-responsive">
			<table class="obp-order-table">
				<thead>
					<tr>
						<th class="obp_order_orderby_ID"
							data-tippy-content="<?php esc_attr_e( 'Sort By ID', 'ovabookpro' ); ?>">
							<?php esc_html_e('ID','ovabookpro');?>
							<span class="icon">
								<i class="flaticon bookproicon-down-arrow"></i>
							</span>
							<input type="hidden" name="orderby" value="DESC" />
						</th>
						<th class="obp_order_orderby_name"
							data-tippy-content="<?php esc_attr_e( 'Sort By Name', 'ovabookpro' ); ?>">
							<?php esc_html_e('Name','ovabookpro');?>
							<span class="icon">
								<i class="flaticon bookproicon-up-arrow"></i>
							</span>
							<input type="hidden" name="orderby" value="ASC" />
						</th>
						<th>
							<?php esc_html_e('Total','ovabookpro');?>
						</th>
						<th>
							<?php esc_html_e('Service','ovabookpro');?>
						</th>
						<th>
							<?php esc_html_e('Action','ovabookpro');?>
						</th>
						<th>
							<?php esc_html_e( 'Status', 'ovabookpro' ); ?>
						</th>
					</tr>
				</thead>
				
				<tbody class="order-table-body">
					<?php if ( $orders->have_posts() ): ?>
						<?php while ( $orders->have_posts() ) :
							$orders->the_post();
							obp_get_template( "my-booking/order-table-item.php" );
						?>
						<?php endwhile;
						else:
							?>
						<tr>
							<td colspan="6"><?php esc_html_e( 'Bookings not found.', 'ovabookpro' ); ?></td>
						</tr>
							<?php
						endif;
						wp_reset_postdata();
					?>
				</tbody>
			</table>
			<div class="obp-pagination-wrap">
				<?php obp_get_template( "my-booking/order-pagination.php", array( 'orders' => $orders ) ); ?>
			</div>
		</div>

		<?php do_action( 'obp_my_booking_after_main_content' ); ?>
	</div>
</div>