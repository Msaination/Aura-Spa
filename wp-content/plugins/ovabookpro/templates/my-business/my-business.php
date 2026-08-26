<?php
use BookPro\Business\OBP_Business;
defined( 'ABSPATH' ) || exit;
$vendor_id 			= obp_get_vendor_id();
$business_id 		= OBP_Business::get_id( $vendor_id );

?>

<div class="obp-title-wrap">
	<h1 class="obp-title">
		<?php echo esc_html__( 'My Business', 'ovabookpro' ); ?>	
	</h1>

	<?php if ( $business_id ){ ?>
		<a href="<?php echo esc_url( get_permalink( $business_id ) );?>" class="view-business has-underline">
			<?php echo esc_html__('View Business', 'ovabookpro' );?>
		</a>
	<?php } ?>
</div>

<div class="obp-content obp-content-business">

	<form class="obp_my_business_form" enctype="multipart/form-data" method="post" autocomplete="off" novalidate >

		<?php 
			/**
			 * Hook: obp_my_business_main_content.
			 *
			 * @hooked obp_my_business_infomation - 10
			 * @hooked obp_my_business_work_hours - 20
			 * @hooked obp_my_business_business_hours - 30
			 * @hooked obp_my_business_media - 40
			 */
			do_action( 'obp_my_business_main_content', $args ); 
		?>

		<!-- submit -->
		<div class="obp-form-submit">
			<input type="submit" name="obp_update_business_profile" class="obp_button" value="<?php echo esc_attr__( 'Update Profile', 'ovabookpro' ); ?>">
			<input type="hidden" id="post_id" name="post_id" value="<?php echo esc_attr( $business_id ); ?>">
			<input type="hidden" name="current_language" value="<?php echo esc_attr( obp_get_current_language() ); ?>" />

			<?php wp_nonce_field( 'obp_edit_business_nonce', 'obp_edit_business_nonce' ); ?>
		</div>

	</form>

</div>