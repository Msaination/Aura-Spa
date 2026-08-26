<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp-forgot-password-wrapper <?php echo esc_attr( $class ); ?>">
	<ul class="obp-messages">
		<?php if ( ! empty( $error_messages ) ): ?>
			<?php foreach ( $error_messages as $message ): ?>
				<li class="message"><?php echo wp_kses_post( $message ); ?></li>
			<?php endforeach; ?>
		<?php endif; ?>
	</ul>

	<form action="<?php echo esc_url( network_site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ); ?>" class="obp-forgot-password-form" method="POST">
		<div class="obp-form-row">
			<label for="user_login"><?php esc_html_e( 'Username or Email Address', 'ovabookpro' ); ?></label>
			<input type="text" name="user_login" id="user_login" class="" value="<?php echo esc_attr( $user_login ); ?>" size="20" required="required" />
		</div>
		<?php do_action( 'lostpassword_form' ); ?>
		<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
		<p class="obp-form-submit">
			<button type="submit" class=""><?php esc_html_e( 'Get New Password', 'ovabookpro' ); ?></button>
		</p>
	</form>
	<p class="obp-forgot-password-footer">
		<a href="<?php echo esc_url( obp_login_url() ); ?>">
			<?php esc_html_e( 'Login', 'ovabookpro' ); ?>
		</a>
		<span class="slash">|</span>
		<a href="<?php echo esc_url( obp_register_user_url() ); ?>">
			<?php esc_html_e( 'Register', 'ovabookpro' ); ?>
		</a>
	</p>
</div>