<?php
namespace BookPro\LoginRegister;

use BookPro\Traits\SingletonTrait;


defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Register_User') ) {

	class OBP_Register_User {

		use SingletonTrait;

		public function __construct(){
			add_shortcode( 'obp_register_user', array( $this, 'add_shortcode' ) );
			add_action( 'register_post', array( $this, 'register_new_user_validate' ), 10 , 3 );
			add_action( 'register_new_user', array( $this, 'register_new_user_success' ) );
		}

		public function add_shortcode( $atts ){
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$atts = shortcode_atts( array(
				'class' 		=> '',
			), $atts );

			$class 		= isset( $atts['class'] ) ? sanitize_text_field( $atts['class'] ) : '';
			$error_messages = array();
			$errors 		= isset( $_REQUEST['error'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['error'] ) ) : '';
			// Handling register
			$user_login 	= isset( $_REQUEST['user_login'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user_login'] ) ) : '';
			$user_email 	= isset( $_REQUEST['user_email'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user_email'] ) ) : '';
			$redirect_to 	= isset( $_REQUEST['redirect_to'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['redirect_to'] ) ) : obp_register_user_url();
			$checkemail 	= isset( $_REQUEST['checkemail'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['checkemail'] ) ) : '';
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $errors ) ) {
				$errors = explode(',', $errors);
				foreach ( $errors as $code ) {
					switch ( $code ) {
						case 'empty_username':
							$error_messages[] = esc_html__( 'Please enter a username.', 'ovabookpro' );
							break;

						case 'invalid_username':
							$error_messages[] = esc_html__( 'This username is invalid because it uses illegal characters. Please enter a valid username.', 'ovabookpro' );
							break;

						case 'username_exists':
							$error_messages[] = esc_html__( 'This username is already registered. Please choose another one.', 'ovabookpro' );
							break;

						case 'invalid_username':
							$error_messages[] = esc_html__( 'Sorry, that username is not allowed.', 'ovabookpro' );
							break;

						case 'empty_email':
							$error_messages[] = esc_html__( 'Please type your email address.', 'ovabookpro' );
							break;

						case 'invalid_email':
							$error_messages[] = esc_html__( 'The email address is not correct.', 'ovabookpro' );
							break;

						case 'email_exists':
							$error_messages[] = esc_html__( 'This email address is already registered.', 'ovabookpro' );
							break;
						
						default:
							break;
					}
				}
			}

			switch ( $checkemail ) {
				case 'registered':
					$error_messages[] = sprintf(
					// translators: %s: login url.
					_x( 'Registration complete. Please check your email, then visit the <a href="%s">login page</a>.', 'login page link' , 'ovabookpro' ),
					obp_login_url()
				);
					break;
				
				default:
					break;
			}

			$error_messages = apply_filters( 'obp_register_user_error_messages', $error_messages );

			$args = array(
				'user_login' 	=> $user_login,
				'user_email' 	=> $user_email,
				'redirect_to' 	=> $redirect_to,
				'error_messages'=> $error_messages,
				'checkemail'	=> $checkemail,
				'class' 		=> $class,
			);

			ob_start();

			$template = apply_filters( 'obp_register_user_template', 'register-user/register-user.php' );
			obp_get_template( $template, $args );

			return ob_get_clean();
		}

		public function register_new_user_validate( $sanitized_user_login, $user_email, $errors ){
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$user_type = isset( $_REQUEST['user_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user_type'] ) ) : '';

			if ( $user_type === 'user' ) {

				if ( $errors->has_errors() ) {
					$redirect_to = add_query_arg( 'error', implode( ',', $errors->get_error_codes() ), obp_register_user_url() );
					$redirect_to = add_query_arg( 'user_login', $sanitized_user_login, $redirect_to );
					$redirect_to = add_query_arg( 'user_email', $user_email, $redirect_to );
					wp_safe_redirect( $redirect_to );
					exit();
				}

			}
		}

		public function register_new_user_success( $user_id ){
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$user_type = isset( $_REQUEST['user_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user_type'] ) ) : '';

			if ( $user_type === 'user' ) {
			
				if ( ! is_wp_error( $user_id ) ) {
					update_user_option( $user_id, 'show_admin_bar_front', false );
					$redirect_to = add_query_arg( 'checkemail', 'registered', obp_register_user_url() );
					wp_safe_redirect( $redirect_to );
					exit();
				}

			}
		}
	}
}