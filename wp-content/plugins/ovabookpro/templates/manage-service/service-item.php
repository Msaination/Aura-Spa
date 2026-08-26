<?php defined( 'ABSPATH' ) || exit;

$service_item 	= obp_get_service( $id );

$endpoint 		= OBP()->endpoint->get_endpoint('edit-service');
$my_account_url = obp_member_account_url();

$url = OBP()->endpoint->get_endpoint_url( $endpoint, $id, $my_account_url );


$price_type 	= $service_item->get_price_type();
if ( $price_type == 'not_show' ) {
	$price_type = 'fixed';
}
?>

<tr>
	<td>
		<?php echo esc_html( $id ); ?>
	</td>
	<td>
		<?php echo esc_html( $service_item->get_title() ); ?>
	</td>
	<td>
		<?php echo esc_html( $service_item->get_duration_text() ); ?>
	</td>
	<td>
		<?php echo wp_kses_post( obp_get_price_html( $service_item->get_price(), $price_type ) ); ?>
	</td>
	<td>
		<?php echo esc_html( $service_item->get_type_name() ); ?>
	</td>
	<td>
		<div class="service_action_wrapper">
			<input type="hidden" name="service_id" value="<?php echo esc_attr( $id ); ?>" />
			<a href="<?php echo esc_url( $url ); ?>" class="obp_edit_service"
				data-tippy-content="<?php echo esc_attr__( 'Edit Service', 'ovabookpro' ); ?>">
				<i class="flaticon bookproicon-edit"></i>
			</a>
			<a href="#" class="obp_remove_service"
				data-tippy-content="<?php echo esc_attr__( 'Remove Service', 'ovabookpro' ); ?>">
				<i class="flaticon bookproicon-close"></i>
			</a>
		</div>
	</td>
</tr>