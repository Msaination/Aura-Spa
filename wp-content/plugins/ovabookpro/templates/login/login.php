<?php defined( 'ABSPATH' ) || exit;?>

<div class="obp-login-wrapper <?php echo esc_attr( $class ); ?>">

	<ul class="obp-messages">
		<?php if ( ! empty( $error_messages ) ): ?>
			<?php foreach ( $error_messages as $message ): ?>
				<li class="message error"><?php echo esc_html( $message ); ?></li>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php if ( ! empty( $success_messages ) ): ?>
			<?php foreach ( $success_messages as $message ): ?>
				<li class="message success"><?php echo esc_html( $message ); ?></li>
			<?php endforeach; ?>
		<?php endif; ?>

	</ul>

	<?php
	if ( ! is_user_logged_in() ) { // Display WordPress login form:
	    $args = array(
	        'redirect' 			=> $redirect, 
	        'form_id' 			=> 'obp-loginform',
	        'label_username' 	=> __( 'Username', 'ovabookpro' ),
	        'label_password' 	=> __( 'Password', 'ovabookpro' ),
	        'label_remember' 	=> __( 'Remember Me', 'ovabookpro' ),
	        'label_log_in' 		=> __( 'Login', 'ovabookpro' ),
	        'remember' 			=> true,
	        'value_username' 	=> $user_name,
	        'value_remember' 	=> $remember,
	    );
	    wp_login_form( $args );
	?>
	<p class="obp-login-footer">
		<a href="<?php echo esc_url( obp_register_user_url() ); ?>">
			<?php esc_html_e( 'Register', 'ovabookpro' ); ?>
		</a>
		<span class="slash">|</span>
		<a href="<?php echo esc_url( obp_forgot_password_url() ); ?>">
			<?php esc_html_e( 'Lost your password?', 'ovabookpro' ); ?>
		</a>
	</p>
	<?php } else { ?>
		<p class="obp-description">
			<?php esc_html_e( 'You are logged in', 'ovabookpro' ); ?>
		</p>
	<?php } ?>
</div>
