<?php defined( 'ABSPATH' ) || exit; ?>

<div class="single-business-part service-wrap">
	
	<div class="obp-second-title-with-filter">
		<h2 class="obp-second-title">
			<?php esc_html_e( 'Services', 'ovabookpro' ); ?>
		</h2>
		<div class="obp-filter-part">
			<div class="search-name-wrapper">
				<input class="obp-search-name" type="text" placeholder="<?php echo esc_attr__('Search for service','ovabookpro');?>">
				<i class="bookproicon-search" title="<?php echo esc_attr__('Search','ovabookpro');?>"></i>
			</div>
		</div>
		<input type="hidden" name="service_vendor_id" value="<?php echo esc_attr( $vendor_id );?>">
	</div>
	
	<div class="service-results" data-vendor-id="<?php echo esc_attr( $vendor_id ); ?>"
		data-business-id="<?php echo esc_attr( $user->get_business_id() ); ?>">
		<?php obp_get_template( 'shortcodes/vendor/service-section.php', array( 'services' => $services ) ); ?>
	</div>

</div>