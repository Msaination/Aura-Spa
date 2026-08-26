<?php
namespace BookPro\LoginRegister;

use BookPro\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Reset_Password') ) {

	class OBP_Reset_Password {

		use SingletonTrait;

		public function __construct(){
			add_shortcode( 'obp_reset_password', array( $this, 'add_shortcode' ) );
			add_action( 'login_form_resetpass', array( $this, 'obp_handling_reset_password' ));
			add_action( 'login_form_rp', array( $this, 'obp_handling_reset_password' ));

			add_filter( 'retrieve_password_message', array( $this, 'obp_retrieve_password_message' ), 10, 4 );
		}

		public function add_shortcode( $atts ){

			$atts = shortcode_atts( array(
				'show_title' 	=> 'yes',
				'class' 		=> '',
			), $atts );
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$class 		= isset( $atts['class'] ) ? sanitize_text_field( $atts['class'] ) : '';
			$show_title 	= isset( $atts['show_title'] ) ? sanitize_text_field( $atts['show_title'] ) : 'yes';
			$rp_login 		= isset( $_REQUEST['login'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['login'] ) ) : '';
			$rp_key 		= isset( $_REQUEST['key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['key'] ) ) : '';
			$user 			= check_password_reset_key( $rp_key, $rp_login );
			$reset_status 	= isset( $_REQUEST['reset'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['reset']) ) : '';
			$error_messages = array();
			$errors 		= isset( $_REQUEST['error'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['error'] ) ) : '';
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $errors ) ) {
				$errors = explode( ',', $errors );
				foreach ( $errors as $code ) {
					switch ( $code ) {
						case 'password_reset_empty_space':
							$error_messages[] = esc_html__( 'The password cannot be a space or all spaces.', 'ovabookpro' );
							break;
						case 'password_reset_mismatch':
							$error_messages[] = esc_html__( 'The passwords do not match.', 'ovabookpro' );
							break;
						default:
							break;
					}
				}
			}

			if ( $reset_status == 'true' ) {
				$error_messages[] = sprintf( '%1$s <a href="%2$s">%3$s</a>', __( 'Your password has been reset.', 'ovabookpro' ), obp_login_url(), __( 'Login', 'ovabookpro' ) );
			}

			$error_messages = apply_filters( 'obp_reset_password_error_messages', $error_messages );

			$args = array(
				'rp_login' 			=> $rp_login,
				'rp_key' 			=> $rp_key,
				'user' 				=> $user,
				'error_messages' 	=> $error_messages,
				'show_title' 		=> $show_title,
				'class' 			=> $class,
			);

			ob_start();

			$template = apply_filters( 'obp_reset_password_template', 'reset-password/reset-password.php' );
			obp_get_template( $template, $args );

			return ob_get_clean();
		}

		public function obp_handling_reset_password(){
			// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
			$rp_login 	= isset( $_REQUEST['login'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['login'] ) ) : '';
			$rp_key 	= isset( $_REQUEST['key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['key'] ) ) : '';

			if ( $rp_login && $rp_key ) {
				$user = check_password_reset_key( $rp_key, $rp_login );

				if ( isset( $_POST['pass1'] ) && isset( $_POST['key'] ) && ! hash_equals( $rp_key, sanitize_text_field( wp_unslash( $_POST['key'] ) ) ) ) {
					$user = false;
				}
			} else {
				$user = false;
			}

			if ( ! $user || is_wp_error( $user ) ) {
				if ( $user && $user->get_error_code() === 'expired_key' ) {
					$redirect = obp_forgot_password_url();
					$redirect = add_query_arg( 'error', 'expiredkey', $redirect );
					wp_safe_redirect( $redirect );
				} else {
					$redirect = obp_forgot_password_url();
					$redirect = add_query_arg( 'error', 'invalidkey', $redirect );
					wp_safe_redirect( $redirect );
				}
				exit;
			}

			$errors = array();

			// Check if password is one or all empty spaces.
			if ( ! empty( $_POST['pass1'] ) ) {
				$_POST['pass1'] = sanitize_text_field( wp_unslash( $_POST['pass1'] ) );

				if ( empty( $_POST['pass1'] ) ) {
					$errors[] = 'password_reset_empty_space';
				}
			}

			// Check if password fields do not match.
			if ( ! empty( $_POST['pass1'] ) && isset( $_POST['pass2'] ) && sanitize_text_field( wp_unslash( $_POST['pass2'] ) ) !== $_POST['pass1'] ) {
				$errors[] = 'password_reset_mismatch';
			}

			$redirect = obp_reset_password_url();
			$redirect = add_query_arg( 'key', $rp_key, $redirect );
			$redirect = add_query_arg( 'login', $rp_login, $redirect );

			if ( count( $errors ) > 0 ) {
				$redirect = add_query_arg( 'error', implode( ',', $errors ), $redirect );
			}

			if ( ( count( $errors ) < 1 ) && isset( $_POST['pass1'] ) && ! empty( $_POST['pass1'] ) ) {
				reset_password( $user, sanitize_text_field( wp_unslash( $_POST['pass1'] ) ) );
				$redirect = add_query_arg( 'reset', 'true', $redirect );
				$redirect = remove_query_arg( 'key', $redirect );
				$redirect = remove_query_arg( 'login', $redirect );
			}
			
			wp_safe_redirect( $redirect );
			exit(); // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
		}

		public function obp_retrieve_password_message( $message, $key, $user_login, $user_data ){

			$locale = get_user_locale( $user_data );
			if ( is_multisite() ) {
				$site_name = get_network()->site_name;
			} else {
				/*
				 * The blogname option is escaped with esc_html on the way into the database
				 * in sanitize_option. We want to reverse this for the plain text arena of emails.
				 */
				$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			}

			$url = add_query_arg( 'key', $key, obp_reset_password_url() );
			$url = add_query_arg( 'login', rawurlencode( $user_login ), $url );

			$message = __( 'Someone has requested a password reset for the following account:', 'ovabookpro' ) . "\r\n\r\n";
			/* translators: %s: Site name. */
			$message .= sprintf( __( 'Site Name: %s', 'ovabookpro' ), $site_name ) . "\r\n\r\n";
			/* translators: %s: User login. */
			$message .= sprintf( __( 'Username: %s', 'ovabookpro' ), $user_login ) . "\r\n\r\n";
			$message .= __( 'If this was a mistake, ignore this email and nothing will happen.', 'ovabookpro' ) . "\r\n\r\n";
			$message .= __( 'To reset your password, visit the following address:', 'ovabookpro' ) . "\r\n\r\n";
			$message .= $url."\r\n\r\n";

			if ( ! is_user_logged_in() ) {
				$requester_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
				if ( $requester_ip ) {
					$message .= sprintf(
						/* translators: %s: IP address of password reset requester. */
						__( 'This password reset request originated from the IP address %s.', 'ovabookpro' ),
						$requester_ip
					) . "\r\n";
				}
			}

			return apply_filters( 'obp_retrieve_password_message', $message, $key, $user_login, $user_data );
		}

	}

}