<?php defined( 'ABSPATH' ) || exit;
global $post;

if ( get_post_type( $post ) != 'obp_coupon' ) {
	return;
}
$endpoint 		= OBP()->endpoint->get_endpoint('edit-coupon');
$member_acc_url = obp_member_account_url();
$url 			= OBP()->endpoint->get_endpoint_url( $endpoint, $post->ID, $member_acc_url );
$coupon 		= obp_get_coupon( $post->ID );

?>


<tr>
	<td>
		<?php echo esc_html( $coupon->get_coupon_code() ); ?>
		<?php if ( $coupon->get_time_formated() ):
			echo '</br>';
			echo esc_html( $coupon->get_time_formated() );
		endif; ?>
	</td>
	<td>
		<?php echo wp_kses_post( $coupon->get_coupon_amount_formatted() ); ?>
	</td>
	<td>
		<?php echo esc_html( $coupon->get_apply_to_translate() ); ?>
	</td>
	<td>
		<div class="obp_coupon_action_wrap">
			<a href="<?php echo esc_url( $url ); ?>" class="obp_edit_coupon" data-tippy-content="<?php esc_attr_e( 'Edit Coupon', 'ovabookpro' ); ?>">
				<i class="flaticon bookproicon-edit"></i>
			</a>
			<a href="#" class="obp_delete_coupon" data-tippy-content="<?php esc_attr_e( 'Remove Coupon', 'ovabookpro' ); ?>">
				<i class="flaticon bookproicon-close"></i>
			</a>
			<input type="hidden" name="coupon_id" value="<?php echo esc_attr( $coupon->get_id() ); ?>" />
		</div>
	</td>
</tr>