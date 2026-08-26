<?php defined( 'ABSPATH' ) || exit; 
$orders = BookPro\Order\OBP_Order::instance()->get_manager_orders_ajax();
?>

<h1 class="obp-title">
	<?php echo esc_html__( 'Manage Booking', 'ovabookpro' ); ?>	
</h1>

<div class="obp-content obp-content-orders obp-content-manage-orders">

	<div class="obp-form-part">

		<?php do_action( 'obp_manager_orders_before_main_content' ); ?>

		<div class="obp-data-list-wrapper my-orders-list-wrapper obp-table-responsive">
			<table class="obp-order-table">
				<thead>
					<tr>
						<th class="obp_order_orderby_ID"
							data-tippy-content="<?php echo esc_attr__( 'Sort By ID', 'ovabookpro' ); ?>">
							<?php echo esc_html__('ID','ovabookpro'); ?>
							<span class="icon">
								<i class="flaticon bookproicon-down-arrow"></i>
							</span>
							<input type="hidden" name="orderby" value="DESC" />
						</th>
						<th class="obp_order_orderby_name"
							data-tippy-content="<?php echo esc_attr__( 'Sort By Name', 'ovabookpro' ); ?>">
							<?php echo esc_html__('Name','ovabookpro'); ?>
							<span class="icon">
								<i class="flaticon bookproicon-up-arrow"></i>
							</span>
							<input type="hidden" name="orderby" value="ASC" />
						</th>
						<th>
							<?php echo esc_html__('Phone','ovabookpro'); ?>
						</th>
						<th>
							<?php echo esc_html__('Profit','ovabookpro'); ?>
						</th>

						<th>
							<?php echo esc_html__('Service','ovabookpro');?>
						</th>
						<th>
							<?php echo esc_html__( 'Action', 'ovabookpro' ); ?>
						</th>
						<th>
							<?php echo esc_html__( 'Status', 'ovabookpro' ); ?>
						</th>
					</tr>
				</thead>
				
				<tbody class="order-table-body">
					<?php if ( $orders->have_posts() ): ?>
						<?php while ( $orders->have_posts() ) :
							$orders->the_post();
							obp_get_template( "manage-booking/order-table-item.php" );
						?>
						<?php endwhile;
						else:
							?>
						<tr>
							<td colspan="7">
								<?php echo esc_html__( 'Bookings not found.', 'ovabookpro' ); ?>
							</td>
						</tr>
							<?php
						endif;
						wp_reset_postdata();
					?>
				</tbody>
			</table>
			<div class="obp_order_footer">
				<div class="obp_order_actions">
					<button type="button" class="obp_button order_export">
						<?php echo esc_html__( 'Export', 'ovabookpro' ); ?>
					</button>
					<?php do_action( 'obp_order_actions' ); ?>
				</div>
				<div class="obp-pagination-wrap">
					<?php obp_get_template( "manage-booking/order-pagination.php", array( 'orders' => $orders ) ); ?>
				</div>
			</div>
			
		</div>

		<?php do_action( 'obp_manager_orders_after_main_content' ); ?>
	</div>
</div>