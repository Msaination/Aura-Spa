<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp_cards">
	<div class="obp_card withdraw">
		<div class="total"><?php echo wp_kses_post( obp_get_price_html( $user->get_balance_amount() ) ); ?></div>
		<p class="desc"><?php esc_html_e( 'Withdrawable Balance', 'ovabookpro' ); ?></p>
		<h3 class="label">
			<a href="#" class="obp_withdraw_popup" data-nonce="<?php echo esc_attr( wp_create_nonce( 'obp_withdraw_popup' ) ); ?>">
				<?php esc_html_e( 'Withdraw', 'ovabookpro' ); ?>
			</a>
		</h3>
	</div>

	<?php if ( BookPro\OBP_Permission::is_vendor() ): ?>
	
		<div class="obp_card pending">
			<div class="total"><?php echo wp_kses_post( obp_get_price_html( $total_pending ) ); ?></div>
			<p class="desc"><?php esc_html_e( 'Profit in Queue', 'ovabookpro' ); ?></p>
			<h3 class="label">
				<?php esc_html_e( 'Pending', 'ovabookpro' ); ?>
				<span class="dashicons dashicons-editor-help"
				data-tippy-content="<?php esc_attr_e( 'You can withdraw profits after the customer has used your service', 'ovabookpro' ); ?>"></span>
			</h3>
		</div>

	<?php endif; ?>
</div>