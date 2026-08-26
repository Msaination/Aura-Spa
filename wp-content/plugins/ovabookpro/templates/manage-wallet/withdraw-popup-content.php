<?php defined( 'ABSPATH' ) || exit; ?>

<?php if ( ! empty( $payout_method ) ): ?>

	<h5 class="subtitle">
		<span class="label"><?php esc_html_e( 'Payout method:', 'ovabookpro' ); ?></span>
		<span class="text"><?php echo esc_html( $payout_method ); ?></span>
	</h5>

	<form action="" class="obp_withdraw_form" autocomplete="off" method="POST" data-max="<?php echo esc_attr( $balance_amount ); ?>" data-error="<?php echo esc_attr( json_encode( $errors ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce('obp_withdraw_request') ); ?>">
		<div class="withdraw_container">
			<input type="number" step="0.1" min="0.1" id="obp_withdraw_amount" value="" placeholder="<?php esc_attr_e( 'Enter amount', 'ovabookpro' ); ?>" />
			<button type="submit" class="obp_withdraw_submit"><?php esc_html_e( 'Withdraw', 'ovabookpro' ); ?></button>
		</div>
	</form>

	<h5 class="withdrawable">
		<span class="label"><?php esc_html_e( 'Withdrawable Balance:', 'ovabookpro' ); ?></span>
		<span class="text"><?php echo wp_kses_post( obp_get_price_html( $balance_amount ) ); ?></span>
	</h5>

	<div class="message">
		<?php
		if ( $status && $mess ) {
			?>
			<p class="<?php echo esc_attr( $status ); ?>"><?php echo wp_kses_post( $mess ); ?></p>
			<?php
		}
		?>
	</div>

<?php else: ?>
	<div class="message">
		<p class="info"><?php esc_html_e( 'Payment method has not been set up yet.', 'ovabookpro' ); ?></p>
	</div>
	
<?php endif; ?>