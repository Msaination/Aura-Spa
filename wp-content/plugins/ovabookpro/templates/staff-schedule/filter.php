<?php defined( 'ABSPATH' ) || exit;
$user = wp_get_current_user();
$user_item = obp_get_user( $user->ID );
?>

<div class="obp_staff_schedule_filter" data-nonce="<?php echo esc_attr( wp_create_nonce( 'obp_staff_schedule_nonce' ) ); ?>">
	<div class="filter_title"><?php echo esc_html( $user_item->get_nickname() ); ?></div>
</div>