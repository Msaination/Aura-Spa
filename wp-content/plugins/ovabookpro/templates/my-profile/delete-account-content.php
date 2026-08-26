<?php defined( 'ABSPATH' ) || exit;?>

<form action="" class="obp_delete_account_form" method="POST" autocomplete="off"
data-redirect="<?php esc_attr_e( 'Go to login page after 3 seconds', 'ovabookpro' ); ?>"
data-status="<?php echo esc_attr( $status ); ?>" >
	<?php wp_nonce_field( 'delete_account', 'delete_account_nonce' ); ?>
	
	<div class="obp-form-part">
		
		<div class="obp_delete_account_notice">
			<?php if ( ! empty( $messages ) ): ?>
				<?php foreach ( $messages as $key => $mess ): ?>
					<div class="obp_alert_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $mess ); ?></div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<label for="reason_delete_account"><?php esc_html_e( 'Reason Delete Account', 'ovabookpro' ); ?></label>
		<textarea name="reason_delete_account" id="reason_delete_account" rows="5"></textarea>

		<div class="obp-form-submit">
			<input type="submit" name="obp_delete_account_profile"
			class="obp_button" value="<?php esc_attr_e( 'Update', 'ovabookpro' ); ?>">
		</div>

	</div>

</form>