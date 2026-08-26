<?php defined( 'ABSPATH' ) || exit;

get_header();

?>

<div class="obp-wrapper">

	<div class="obp-single-business-wrap">
		<div class="obp-main-content">
			<?php 
				if ( obp_check_business_our_work() ) {
					do_action( 'obp_single_business_portfolio', $args ); 
				} else {
					do_action( 'obp_single_business_main_content', $args ); 
				}
				
			?>
		</div>

		<div class="obp-sidebar">
			<?php do_action( 'obp_single_business_sidebar', $args ); ?>
		</div>
	</div>

</div>

<?php

get_footer();