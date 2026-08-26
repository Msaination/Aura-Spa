<?php defined( 'ABSPATH' ) || exit;

$member_acc_url = obp_member_account_url();
$endpoint 		= OBP()->endpoint->get_endpoint('edit-coupon');
$url 			= OBP()->endpoint->get_endpoint_url( $endpoint, '', $member_acc_url );

$coupons = BookPro\Coupon\OBP_Coupon::get_coupon_ajax();
?>


<h1 class="obp-title"><?php esc_html_e( 'Manage Coupon', 'ovabookpro' ); ?></h1>

<div class="obp-content obp-content-coupon">

	<div class="obp-form-part">
		<div class="obp_coupon_table_wrapper">
			<table class="obp_coupon_list_table">
				
				<thead>
					<tr>
						<th>
							<?php esc_html_e( 'Code', 'ovabookpro' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'Amount', 'ovabookpro' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'Apply To', 'ovabookpro' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'Action', 'ovabookpro' ); ?>
						</th>
					</tr>
				</thead>
				<tbody class="obp_coupon_body">
					<?php if ( $coupons->have_posts() ): ?>
						<?php while ( $coupons->have_posts() ) {
							$coupons->the_post();
							obp_get_template("manage-coupon/coupon-item.php");
						}
						wp_reset_postdata();
						?>
					<?php else: ?>
						<tr>
							<td colspan="4">
								<?php esc_html_e( 'Coupons not found.', 'ovabookpro' ); ?>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>

			</table>

		</div>


		<div class="obp-pagination-wrap">
				<?php obp_get_template( "manage-coupon/coupon-pagination.php", array( 'coupons' => $coupons ) ); ?>
			</div>

		<div class="obp-button-wrapper align-right">
			<a href="<?php echo esc_url( $url ); ?>">
				<input type="button" name="obp_add_coupon" class="obp_button" value="<?php esc_attr_e( 'Add Coupon', 'ovabookpro' ); ?>" />
			</a>
		</div>
	</div>

</div>