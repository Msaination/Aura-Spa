<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp-form-part">

	<div class="obp_card_wrap">
		<?php obp_manage_wallet_cards( $args ); ?>
	</div>

	<div class="obp_content_wrap">
		<div class="transaction_history obp_box">
			<?php obp_manage_wallet_transaction_history( $args ); ?>
		</div>
		<div class="payout_method obp_box">
			<?php obp_manage_wallet_payout_method( $args ); ?>
		</div>
	</div>

</div>