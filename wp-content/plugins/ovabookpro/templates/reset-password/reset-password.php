<?php defined( 'ABSPATH' ) || exit; ?>


<div class="obp-reset-password-wrapper <?php echo esc_attr( $class ); ?>">
	
	<?php if ( $show_title == 'yes' ): ?>
		<h3 class="obp-heading">
			<?php esc_html_e( 'Reset Password', 'ovabookpro' ); ?>
		</h3>
	<?php endif; ?>

	<ul class="obp-messages">
		<?php if ( ! empty( $error_messages ) ): ?>
			<?php foreach ( $error_messages as $message ): ?>
				<li class="message"><?php echo wp_kses_post( $message ); ?></li>
			<?php endforeach; ?>
		<?php endif; ?>
	</ul>

	<?php if ( ! is_wp_error( $user ) && ! is_user_logged_in() ): ?>

		<form action="<?php echo esc_url( network_site_url( 'wp-login.php?action=resetpass', 'login_post' ) ); ?>" class="obp-reset-password-form" method="POST" autocomplete="off">
			<input type="hidden" name="login" value="<?php echo esc_attr( $rp_login ); ?>" />
			<div class="obp-form-row">
				<label for="pass1"><?php esc_html_e( 'New password', 'ovabookpro' ); ?></label>
				<div class="obp-password">
					<input type="password" name="pass1" id="pass1" class="" size="24" value="" />
					<i class="bookproicon-view"></i>
				</div>
			</div>
			<div class="obp-form-row">
				<label for="pass2"><?php esc_html_e( 'Confirm new password', 'ovabookpro' ); ?></label>
				<input type="password" name="pass2" id="pass2" class="" size="24" value="" />
			</div>
			<p class="description"><?php echo esc_html( wp_get_password_hint() ); ?></p>
			<?php do_action( 'resetpass_form', $user ); ?>
			<input type="hidden" name="key" value="<?php echo esc_attr( $rp_key ); ?>" />
			<div class="obp-form-submit">
				<button type="submit" class=""><?php esc_html_e( 'Save Password', 'ovabookpro' ); ?></button>
			</div>
		</form>

	<?php endif; ?>
</div>