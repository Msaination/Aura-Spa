<?php
namespace BookPro\LoginRegister;

use BookPro\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Forgot_Password') ) {
	
	class OBP_Forgot_Password {

		use SingletonTrait;

		public function __construct(){
			add_shortcode( 'obp_forgot_password', array( $this, 'add_shortcode' ) );
			add_action( 'login_form_lostpassword', array( $this, 'obp_lostpassword_post' ) );
			add_action( 'login_form_retrievepassword', array( $this, 'obp_lostpassword_post' ) );
		}

		public function add_shortcode( $atts ){
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$atts = shortcode_atts( array(
				'class' 		=> '',
			), $atts );

			$class 		= isset( $atts['class'] ) ? sanitize_text_field( $atts['class'] ) : '';

			$redirect_to 	= isset( $_REQUEST['redirect_to'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['redirect_to'] ) ) : obp_forgot_password_url();
			$user_login 	= isset( $_REQUEST['user_login'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user_login'] ) ) : '';
			$errors 		= isset( $_REQUEST['error'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['error'] ) ) : '';
			$error_messages = array();
			$checkemail 	= isset( $_REQUEST['checkemail'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['checkemail'] ) ) : '';
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $errors ) ) {
				$errors = explode(',', $errors);
				foreach ( $errors as $code ) {
					switch ( $code ) {
						case 'invalid_email':
						case 'invalidcombo':
							$error_messages[] = esc_html__( 'There is no account with that username or email address.', 'ovabookpro' );
							break;
						case 'empty_username':
							$error_messages[] = esc_html__( 'Please enter a username or email address.', 'ovabookpro' );
							break;
						default:
							break;
					}
				}
			}

			if ( $checkemail == 'confirm' ) {
				// translators: %s: login url.
				$error_messages[] = sprintf( _x( 'Check your email for the confirmation link, then visit the <a href="%s">login page</a>.', 'login page link' , 'ovabookpro' ), obp_login_url() );
			}

			$error_messages = apply_filters( 'obp_forgot_password_error_messages', $error_messages );

			$args = array(
				'user_login' 		=> $user_login,
				'redirect_to' 		=> $redirect_to,
				'error_messages' 	=> $error_messages,
				'class' 			=> $class,
			);

			ob_start();

			$template = apply_filters( 'obp_forgot_password_template', 'forgot-password/forgot-password.php' );
			obp_get_template( $template, $args );

			return ob_get_clean();
		}

		public function obp_lostpassword_post(){
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$redirect_to = isset( $_REQUEST['redirect_to'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['redirect_to'] ) ) : obp_forgot_password_url();

			if ( isset( $_SERVER['REQUEST_METHOD'] ) && sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) === 'POST' ) {
				$errors = retrieve_password();
				$user_login = isset( $_REQUEST['user_login'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user_login'] ) ) : '';
				// phpcs:enable WordPress.Security.NonceVerification.Recommended
				if ( is_wp_error( $errors ) && $errors->has_errors() ) {
					$redirect_to = add_query_arg( 'error', implode( ',', $errors->get_error_codes() ), $redirect_to );

					if ( $user_login ) {
						$redirect_to = add_query_arg( 'user_login', $user_login , $redirect_to );
					}
					
					wp_safe_redirect( $redirect_to );
					exit();
				} else {
					$redirect_to = add_query_arg( 'checkemail', 'confirm', $redirect_to );
					wp_safe_redirect( $redirect_to );
					exit();
				}
			}
			
		}
	}
}