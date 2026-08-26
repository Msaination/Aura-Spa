<?php defined( 'ABSPATH' ) || exit;
$endpoint = OBP()->endpoint->get_endpoint( 'manage-plan' );
$member_account_url = obp_member_account_url();
$url = OBP()->endpoint->get_endpoint_url( $endpoint, '', $member_account_url );
?>

<div class="obp_booking_info">

	<p class="description">
		<?php esc_html_e( 'Plan has not been established.', 'ovabookpro' ); ?>
		<?php if ( BookPro\OBP_Permission::is_vendor() ): ?>
			<?php
			$link = '<a href="'.esc_url( $url ).'">'.esc_html__( 'here', 'ovabookpro' ).'</a>';
			// translators: %s: member account url.
			printf( esc_html__( 'Please click %s to add plan.', 'ovabookpro' ), wp_kses_post( $link ) ); ?>
		<?php endif; ?>
	</p>

</div>