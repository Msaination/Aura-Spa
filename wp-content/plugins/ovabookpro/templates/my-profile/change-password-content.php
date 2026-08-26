<?php defined( 'ABSPATH' ) || exit; ?>

<form action="" class="obp_change_password_form" method="POST" autocomplete="off"
data-login-again="<?php esc_attr_e( 'Log in again after 3 seconds', 'ovabookpro' ); ?>"
data-status="<?php echo esc_attr( $status ); ?>">
	<?php wp_nonce_field( 'update_password', 'update_password_nonce' ); ?>
	
	<div class="obp-form-part">
		
		<div class="obp_update_password_notice">
			<?php if ( ! empty( $messages ) ): ?>
				<?php foreach ( $messages as $key => $mess ): ?>
					<div class="obp_alert_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $mess ); ?></div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		
		<div class="obp_wrap_two_column">

			<div class="obp_column">

				<label for="old_password">
						<?php esc_html_e( 'Old Password*', 'ovabookpro' ); ?>
				</label>
				<div class="obp-form-field">
					<div class="obp-password">
						<input type="password" name="old_password" id="old_password" class="" size="24" value="" tabindex="7" />
						<i class="bookproicon-view"></i>
					</div>
				</div>

			</div>

			<div class="obp_column">

				<label for="new_password">
						<?php esc_html_e( 'New Password*', 'ovabookpro' ); ?>
				</label>
				<div class="obp-form-field">
					<div class="obp-password">
						<input type="password" name="new_password" id="new_password" class="" size="24" value="" tabindex="8" />
						<i class="bookproicon-view"></i>
					</div>
				</div>

			</div>

			<div class="obp_column">
				<label for="confirm_password">
						<?php esc_html_e( 'Confirm Password*', 'ovabookpro' ); ?>
				</label>
				<div class="obp-form-field">
					<div class="obp-password">
						<input type="password" name="confirm_password" id="confirm_password" class="" size="24" value="" tabindex="9" />
						<i class="bookproicon-view"></i>
					</div>
				</div>
			</div>

		</div>

		<div class="obp-form-submit">
			<input type="submit" name="obp_update_password_profile"
			class="obp_button" value="<?php esc_attr_e( 'Update', 'ovabookpro' ); ?>">
		</div>

	</div>

</form>
<div class="update_password_errors" data-error="<?php echo esc_attr( json_encode( $errors ) ); ?>"></div>