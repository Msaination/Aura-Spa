<?php defined( 'ABSPATH' ) || exit;
	$user_query 	= BookPro\Staff\OBP_Staff::get_users_ajax();
	$endpoint 		= OBP()->endpoint->get_endpoint( 'edit-staff' );
	$url 			= OBP()->endpoint->get_endpoint_url( $endpoint );
	$date_init 		= gmdate("Y-m-d", current_time( 'timestamp' ) );
?>

<h1 class="obp-title">
	<?php esc_html_e( 'Manage Staff', 'ovabookpro' ); ?>	
</h1>

<div class="obp-content obp-content-staff">

	<div class="obp-form-part">

		<div class="obp-second-title-with-filter">
			<h2 class="obp-second-title">
				<?php echo esc_html__('Staff Listing','ovabookpro');?>	
			</h2>

			<div class="obp-filter-part obp_width_auto">
				<div class="obp-order">
					<label for="user_orderby" class="obp_nowrap">
						<?php echo esc_html__('Sort by:','ovabookpro');?>	
					</label>
					<select name="user_orderby" id="user_orderby">
						<option value="user_nicename">
							<?php echo esc_html__('Name: A-Z','ovabookpro');?>
						</option>
						<option value="user_nicename_desc">
							<?php echo esc_html__('Name: Z-A','ovabookpro');?>
						</option>
						<option value="ID">
							<?php echo esc_html__('ID: 0-9','ovabookpro');?>
						</option>
						<option value="ID_desc">
							<?php echo esc_html__('ID: 9-0','ovabookpro');?>
						</option>
					</select>
				</div>
				<div class="search-name-wrapper">
					<input class="obp-search-name" type="text" placeholder="<?php esc_attr_e('Name staff','ovabookpro');?>">
					<span class="obp_search_staff" role="button" aria-label="<?php esc_attr_e( 'Search Staff', 'ovabookpro' ); ?>">
						<i class="bookproicon-search"></i>
					</span>
				</div>
			</div>
		</div>

		<!-- list staff -->
		<div class="obp-data-list-wrapper staff-list-wrapper" data-date-init="<?php echo esc_attr( $date_init ); ?>">

			<table class="obp_table_staff_list">
				<thead>
					<tr class="obp_staff_table_heading">
						<th></th>
						<th><?php esc_html_e( 'Name', 'ovabookpro' ); ?></th>
						<th><?php esc_html_e( 'Role', 'ovabookpro' ); ?></th>
						<th><?php esc_html_e( 'Action', 'ovabookpro' ); ?></th>
					</tr>
				</thead>
				<tbody class="obp_staff_list_container">
					<?php 
					if ( ! empty( $user_query->get_results() ) ) {
						$i = 0;
						foreach ( $user_query->get_results() as $user ) {
							obp_get_template( 'manage-staff/staff-item.php', array( 'user' => $user, 'key' => $i ) );
							$i++;
						}
					} else { ?>
						<tr>
							<td colspan="4">
								<?php esc_html_e( 'No staff found.', 'ovabookpro' ); ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>

		<!-- Add staff -->
		<div class="obp-button-wrapper align-right">
			<a href="<?php echo esc_url( $url );?>" class="obp_button">
				<?php esc_html_e( 'Add staff', 'ovabookpro' ); ?>
			</a>
		</div>
		<div class="obp-pagination-wrap">
			<?php obp_get_template("manage-staff/staff-pagination.php", array( 'user_query' => $user_query ) ); ?>
		</div>
	</div>
</div>