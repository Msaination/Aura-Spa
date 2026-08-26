<?php defined( 'ABSPATH' ) || exit;
global $post;

$payout = obp_get_payout( $post->ID );
?>
<tr>
	<td>
		<a href="#" class="obp_show_payout" data-id="<?php echo esc_attr( $post->ID ); ?>">
			- <?php echo wp_kses_post( obp_get_price_html( $payout->get_amount() ) ); ?>
		</a>
	</td>
	<td>
		<?php echo esc_html( $payout->get_withdraw_date() ); ?>
	</td>
	<td>
		<?php echo esc_html( $payout->get_payout_status_translate() ); ?>
	</td>
	<td>
		<?php echo esc_html( $payout->get_payout_method() ); ?>
	</td>
</tr>