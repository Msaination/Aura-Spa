<?php defined( 'ABSPATH' ) || exit; ?>


<div class="obp-register-user-wrapper <?php echo esc_attr( $class ); ?>">
	
	<ul class="obp-messages">
		<?php if ( ! empty( $error_messages ) ): ?>
			<?php foreach ( $error_messages as $message ): ?>
				<li class="message"><?php echo wp_kses_post( $message ); ?></li>
			<?php endforeach; ?>
		<?php endif; ?>
	</ul>
	<?php if ( empty( $checkemail ) ): ?>
	
		<?php if ( get_option( 'users_can_register' ) ): ?>
			<form class="obp-register-user-form" action="<?php echo esc_url( site_url( 'wp-login.php?action=register', 'login_post' ) ); ?>" method="POST">
				<input type="hidden" name="user_type" value="user" />
				<div class="obp-form-row">
					<label for="user_login"><?php esc_html_e( 'Username', 'ovabookpro' ); ?></label>
					<input type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr( $user_login ); ?>" size="20" autocapitalize="off" autocomplete="username" required="required" />
				</div>

				<div class="obp-form-row">
					<label for="user_email"><?php esc_html_e( 'Email', 'ovabookpro' ); ?></label>
					<input type="email" name="user_email" id="user_email" class="input" value="<?php echo esc_attr( $user_email ); ?>" size="25" autocomplete="email" required="required" />
				</div>
				<?php do_action( 'obp_register_user_form' ); ?>
				<?php do_action( 'register_form' ); ?>
				<p class="description"><?php esc_html_e( 'Registration confirmation will be emailed to you.', 'ovabookpro' ); ?></p>
				<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>" />
				<p class="obp-form-submit">
					<button type="submit" class=""><?php esc_html_e( 'Register', 'ovabookpro' ); ?></button>
				</p>
			</form>
			<p class="obp-register-footer">
				<a href="<?php echo esc_url( obp_login_url() ); ?>" class=""><?php esc_html_e( 'Login', 'ovabookpro' ); ?></a>
				<span class="slash">|</span>
				<a href="<?php echo esc_url( obp_forgot_password_url() ); ?>" class=""><?php esc_html_e( 'Lost your password?', 'ovabookpro' ); ?></a>
			</p>
		<?php else: ?>
			<p class="description"><?php esc_html_e( 'User registration is currently not allowed.', 'ovabookpro' ); ?></p>
		<?php endif; ?>

	<?php endif; ?>
</div>