<?php defined( 'ABSPATH' ) || exit;


use BookPro\Service\OBP_Service;
$edit_service 		= OBP()->endpoint->get_endpoint('edit-service');
$url 				= OBP()->endpoint->get_endpoint_url( $edit_service );

$services = OBP_Service::get_service_ajax();
?>

<h1 class="obp-title">
	<?php echo esc_html__( 'Services', 'ovabookpro' ); ?>	
</h1>

<div class="obp-content obp-content-service">

	<div class="obp-form-part">

		<div class="obp-second-title-with-filter">
			<h2 class="obp-second-title">
				<?php echo esc_html__('Listing Services','ovabookpro' );?>	
			</h2>

			<div class="obp-filter-part obp_width_auto">
				
				<div class="obp-order">
					<label for="post_orderby" class="obp_nowrap">
						<?php echo esc_html__('Sort by:','ovabookpro' );?>	
					</label>
					<select name="post_orderby" id="post_orderby">
						<option value="title">
							<?php echo esc_html__('Name: A-Z','ovabookpro' );?>
						</option>
						<option value="title_desc">
							<?php echo esc_html__('Name: Z-A','ovabookpro' );?>
						</option>
						<option value="ID">
							<?php echo esc_html__('ID: 0-9','ovabookpro' );?>
						</option>
						<option value="ID_desc">
							<?php echo esc_html__('ID: 9-0','ovabookpro' );?>
						</option>
					</select>
				</div>
				<div class="search-name-wrapper">
					<input class="obp-search-name" type="text" placeholder="<?php echo esc_attr__('Service Name','ovabookpro' ); ?>">
					<i class="bookproicon-search" title="<?php echo esc_attr__('Search','ovabookpro' ); ?>"></i>
				</div>
				
				<?php wp_nonce_field( 'obp_filter_service_nonce', 'obp_filter_service_nonce' ); ?>
			</div>
		</div>

		<!-- list service -->
		<div class="obp-data-list-wrapper service-list-wrapper">
			<table class="obp-service-table-list">
				<thead>
					<tr>
						<th>
							<?php esc_html_e( 'ID', 'ovabookpro' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'Name', 'ovabookpro' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'Duration', 'ovabookpro' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'Price', 'ovabookpro' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'Type', 'ovabookpro' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'Action', 'ovabookpro' ); ?>
						</th>
					</tr>
				</thead>
				<tbody class="obp_service_table_container">
					<?php if ( $services->have_posts() ):
						?>
						<?php while ( $services->have_posts() ) {
							$services->the_post();
							$id = get_the_ID();
							obp_get_template( "manage-service/service-item.php", array( 'id' => $id ) );
						} ?>
						<?php wp_reset_postdata(); ?>

					<?php else: ?>
						<tr>
							<td colspan="6"><?php echo esc_html__( 'Services not found.', 'ovabookpro' ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<div class="obp-pagination-wrap">
			<?php obp_get_template("manage-service/service-pagination.php", array( 'services' => $services ) ); ?>
		</div>

		<!-- Add service -->
		<div class="obp-button-wrapper align-right">
			<a href="<?php echo esc_url( $url ); ?>">
				<input type="button" name="obp_add_service" class="obp_button" value="<?php echo esc_attr__( 'Add Service', 'ovabookpro'  ); ?>">
			</a>
		</div>

	</div>
</div>