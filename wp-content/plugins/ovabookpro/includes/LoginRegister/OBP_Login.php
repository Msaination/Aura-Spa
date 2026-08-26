<?php
namespace BookPro\LoginRegister;

use BookPro\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Login') ) {
	class OBP_Login {

		use SingletonTrait;

		public function __construct(){
			add_shortcode( 'obp_login', array( $this, 'add_shortcode' ) );
			add_filter( 'authenticate', array( $this, 'obp_authenticate' ), 100, 3 );
			add_filter( 'wp_new_user_notification_email', array( $this, 'obp_new_user_notification_email' ), 10 , 3 );
		}

		public function add_shortcode( $atts ){
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$atts = shortcode_atts( array(
				'class' 		=> '',
			), $atts );

			$redirect 	= isset( $_GET['redirect_to'] ) ? sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) ) : '';

			$class 	= isset( $atts['class'] ) ? sanitize_text_field( $atts['class'] ) : '';

			if ( empty( $redirect ) ) {
				$redirect = obp_member_account_url();
			} else {
				$redirect = OBP()->endpoint->get_endpoint_url( $redirect, '', obp_member_account_url() );
			}
			// Handling error
			$errors = isset( $_REQUEST['error'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['error'] ) ) : '';
			$error_messages = array();
			if ( ! empty( $errors ) ) {
				$errors = explode(',', $errors);
				foreach ( $errors as $code ) {
					switch ( $code ) {
						case 'empty_username':
							$error_messages[] = esc_html__( 'The username field is empty.', 'ovabookpro' );
							break;
						case 'empty_password':
							$error_messages[] = esc_html__( 'The password field is empty.', 'ovabookpro' );
							break;
						case 'invalid_username':
							$error_messages[] = esc_html__( 'The username is invalid.', 'ovabookpro' );
							break;
						case 'incorrect_password':
							$error_messages[] = esc_html__( 'The password is incorrect.', 'ovabookpro' );
							break;
						default:
							break;
					}
				}
			}

			
			$success_messages = array();

			$user_status = isset( $_REQUEST['user_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user_status'] ) ) : '';
			if ( $user_status == 'locked' ) {
				$error_messages[] = esc_html__( 'Your account has been locked.', 'ovabookpro' );
			}

			$user_name 			= isset( $_REQUEST['username'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['username'] ) ) : '';
			$remember 			= ! empty( $_REQUEST['rememberme'] );
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
			$error_messages 	= apply_filters( 'obp_login_error_messages', $error_messages );
			$success_messages 	= apply_filters( 'obp_login_success_messages', $success_messages );

			$args = array(
				'error_messages' 	=> $error_messages,
				'success_messages' 	=> $success_messages,
				'user_name' 		=> $user_name,
				'remember' 			=> $remember,
				'redirect' 			=> $redirect,
				'class' 			=> $class,
			);

			$args = apply_filters( 'obp_login_args', $args );
			ob_start();
			obp_get_template( 'login/login.php', $args );

			return ob_get_clean();
		}

		public function obp_authenticate( $user, $username, $password ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$redirect_post = isset( $_REQUEST['redirect_to'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['redirect_to'] ) ) : '';

			if ( isset( $_SERVER['REQUEST_METHOD'] ) && sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) === 'POST' && $redirect_post != admin_url() ) {

				if ( is_wp_error( $user ) ) {
					$remember = ! empty( $_REQUEST['rememberme'] );
					// phpcs:enable WordPress.Security.NonceVerification.Recommended

					$redirect = obp_login_url();
					$redirect = add_query_arg( 'error', join( ',', $user->get_error_codes() ), $redirect );
					if ( ! empty( $username ) ) {
						$redirect = add_query_arg( 'username', $username, $redirect );
					}
					$redirect = add_query_arg( 'rememberme', $remember, $redirect );
					wp_safe_redirect( $redirect );
					exit();
				}

				$user_status = get_user_meta( $user->ID, OBP_METABOX.'user_status', true );

				if ( $user_status == 'locked' ) {
					wp_logout();
					$redirect_to = add_query_arg( 'user_status', 'locked', obp_login_url() );
					wp_safe_redirect( $redirect_to );
					exit();
				}

			}
			return apply_filters( 'obp_authenticate', $user, $username, $password );
		}

		public function obp_new_user_notification_email( $new_user_notification_email, $user, $blogname ){
			$key = get_password_reset_key( $user );
			// translators: %s: username.
			$message  = sprintf( _x( 'Username: %s', 'username' , 'ovabookpro' ), $user->user_login ) . "\r\n\r\n";
			$message .= __( 'To set your password, visit the following address:', 'ovabookpro' ) . "\r\n\r\n";
			$message .= obp_reset_password_url()."?key=$key&login=". rawurlencode( $user->user_login ) . "\r\n\r\n";
			$message .= obp_login_url() . "\r\n";

			$new_user_notification_email['message'] = $message;

			return apply_filters( 'obp_new_user_notification_email', $new_user_notification_email, $user, $blogname );
		}

	}

}