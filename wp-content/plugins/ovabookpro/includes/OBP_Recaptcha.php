<?php
namespace BookPro;

use BookPro\Traits\SingletonTrait;
use BookPro\OBP_Data_Setting;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists('OBP_Recaptcha') ) {

	class OBP_Recaptcha {

		use SingletonTrait;

		public $enable = null;

		public $type = null;

		public $site_key = null;

		public $secret_key = null;

		public $settings = null;

		public $form_settings = null;

		public function __construct(){

			$this->settings 	= new OBP_Data_Setting();
			$this->enable 		= $this->settings->recaptcha->get('recaptcha_enable', 'no');
			$this->type 		= $this->settings->recaptcha->get('type','v2');
			$this->site_key 	= $this->settings->recaptcha->get('site_key','');
			$this->secret_key 	= $this->settings->recaptcha->get('secret_key','');

			$this->form_settings_init();
			// Hooks
			if ( $this->is_key_setup_complete() && $this->enable == 'yes' ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
				add_action( 'login_enqueue_scripts', array( $this, 'load_scripts' ) );
				$this->recaptcha_init();
				do_action( 'obp_recapcha_init', $this );
			}
			
		}

		public function form_settings_init(){
			$login_form_enable 			= $this->settings->recaptcha->get('login_form');
			$register_user_enable 		= $this->settings->recaptcha->get('register_user_form');
			$reset_password_enable 		= $this->settings->recaptcha->get('reset_password_form');
			$forgot_password_enable 	= $this->settings->recaptcha->get('forgot_password_form');
			$booking_service_enable 	= $this->settings->recaptcha->get( 'booking_service_form' );

			$data = array(
				'login_form_middle' => array(
					'enable' 	=> $login_form_enable,
					'echo' 		=> '',
				),
				'login_form' => array(
					'enable' 	=> $login_form_enable,
					'echo' 		=> '_echo',
				),
				'obp_register_user_form' => array(
					'enable' 	=> $register_user_enable,
					'echo' 		=> '_echo',
				),
				'resetpass_form' 	=> array(
					'enable' 		=> $reset_password_enable,
					'echo' 			=> '_echo',
				),
				'lostpassword_form' => array(
					'enable' 	=> $forgot_password_enable,
					'echo' 		=> '_echo',
				),
				'obp_booking_free_form_before_submit_button' => array(
					'enable' 	=> $booking_service_enable,
					'echo' 		=> '_echo',
				),
			);

			$this->form_settings = apply_filters( 'obp_recaptcha_form_settings' , $data, $this );
		}

		public function get_type(){
			return $this->type;
		}

		public function get_site_key(){
			return $this->site_key;
		}

		public function get_secret_key(){
			return $this->secret_key;
		}

		public function get_form_settings(){
			return $this->form_settings;
		}

		public function get_options(){
			return $this->options;
		}

		public function recaptcha_init(){
			$form_settings = $this->get_form_settings();

			foreach ( $form_settings as $hook => $item ) {
				if ( $item['enable'] ) {
					if ( $this->get_type() === 'v2' ) {
						add_action( $hook, array( $this, 'recaptcha_display_wrapper'.$item['echo'] ), 100 );
					} elseif ( $this->get_type() === 'v3' ) {
						add_action( $hook, array( $this, 'recaptcha_display_captcha_input'.$item['echo'] ), 100 );
					}

					switch ( $hook ) {
						case 'login_form_middle':
						case 'login_form':
							$this->recaptcha_login_form_init();
							break;
						case 'obp_register_user_form':
							$this->recaptcha_register_user_form_init();
						break;
						case 'resetpass_form':
							$this->recaptcha_reset_password_form_init();
						break;
						case 'lostpassword_form':
							$this->recaptcha_fotgot_password_form_init();
						break;
						default:
							break;
					}
				}
			}
		}

		public function recaptcha_display_wrapper(){
			return '<div class="obp-recaptcha-wrapper"></div>';
		}

		public function recaptcha_display_captcha_input(){
			return '<input type="hidden" name="g-recaptcha-response" class="obp-recaptcha-response">';
		}

		public function recaptcha_display_wrapper_echo(){
			echo '<div class="obp-recaptcha-wrapper"></div>';
		}

		public function recaptcha_display_captcha_input_echo(){
			echo '<input type="hidden" name="g-recaptcha-response" class="obp-recaptcha-response">';
		}

		public function recaptcha_fotgot_password_form_init(){
			add_action( 'lostpassword_post', array( $this, 'recaptcha_processing_forgot_password_form' ), 10 , 2 );
			add_filter( 'obp_forgot_password_error_messages', array( $this, 'obp_forgot_password_recaptcha_error_messages' ) );
		}

		public function recaptcha_processing_forgot_password_form( $errors, $user_data ){
			$err_code = '';
			if ( isset( $_SERVER['REQUEST_METHOD'] ) && sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) === 'POST' && isset( $_POST['g-recaptcha-response'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( true !== $this->recaptcha_validate_posted_captcha() ) {
					$err_code = 'reCAPTCHA';
				}
			} else {
				$err_code = 'reCAPTCHA';
			}

			if ( $err_code ) {
				$user_login = isset( $_REQUEST['user_login'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user_login'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$redirect_to = add_query_arg( 'error', $err_code , obp_forgot_password_url() );
				
				if ( $user_login ) {
					$redirect_to = add_query_arg( 'user_login', $user_login , $redirect_to );
				}
				wp_safe_redirect( $redirect_to );
				exit();
			}
		}

		public function obp_forgot_password_recaptcha_error_messages( $error_messages ){

			$errors = isset( $_REQUEST['error'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( $errors === 'reCAPTCHA' ) {
				$error_messages[] = esc_html__( 'Google reCAPTCHA verification failed.', 'ovabookpro' );
			}
			
			return apply_filters( 'obp_forgot_password_recaptcha_error_messages', $error_messages );
		}

		public function recaptcha_reset_password_form_init(){
			add_filter( 'obp_reset_password_error_messages', array( $this, 'obp_reset_password_recaptcha_error_messages' ) );
			add_action( 'login_form_resetpass', array( $this, 'recaptcha_processing_reset_password_form' ) );
			add_action( 'login_form_rp', array( $this, 'recaptcha_processing_reset_password_form' ) );
		}

		public function recaptcha_processing_reset_password_form(){
			$err_code = '';
			$rp_login 	= isset( $_REQUEST['login'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['login'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$rp_key 	= isset( $_REQUEST['key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) && isset( $_POST['g-recaptcha-response'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( true !== $this->recaptcha_validate_posted_captcha() ) {
					$err_code = 'reCAPTCHA';
				}
			} else {
				$err_code = 'reCAPTCHA';
			}

			if ( $err_code ) {
				$redirect_to = add_query_arg( 'error', $err_code , obp_reset_password_url() );
				if ( $rp_login ) {
					$redirect_to = add_query_arg( 'login', $rp_login, $redirect_to );
				}
				if ( $rp_key ) {
					$redirect_to = add_query_arg( 'key', $rp_key, $redirect_to );
				}

				wp_safe_redirect( $redirect_to );
				exit();
			}
		}

		public function obp_reset_password_recaptcha_error_messages( $error_messages ){
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$errors = isset( $_REQUEST['error'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['error'] ) ) : '';
			if ( $errors === 'reCAPTCHA' ) {
				$error_messages[] = esc_html__( 'Google reCAPTCHA verification failed.', 'ovabookpro' );
			}

			return apply_filters( 'obp_reset_password_recaptcha_error_messages', $error_messages );
		}

		public function recaptcha_register_user_form_init(){
			add_filter( 'registration_errors', array( $this, 'recaptcha_processing_register_user_form' ), 10, 3 );
			add_filter( 'obp_register_user_error_messages', array( $this, 'obp_register_user_recaptcha_error_messages' ) );
		}

		public function recaptcha_processing_register_user_form( $errors, $sanitized_user_login, $user_email ){

			$err_code = '';
			$user_type = isset( $_REQUEST['user_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended


			if ( $user_type === 'user' ) {

				if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) && isset( $_POST['g-recaptcha-response'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					if ( true !== $this->recaptcha_validate_posted_captcha() ) {
						$err_code = 'reCAPTCHA';
					}
				} else {
					$err_code = 'reCAPTCHA';
				}

				if ( $err_code ) {
					$redirect_to = add_query_arg( 'error', $err_code , obp_register_user_url() );
					$redirect_to = add_query_arg( 'user_login', $sanitized_user_login, $redirect_to );
					$redirect_to = add_query_arg( 'user_email', $user_email, $redirect_to );
					wp_safe_redirect( $redirect_to );
					exit();
				}

			}

			return apply_filters( 'obp_registration_errors', $errors, $sanitized_user_login, $user_email );
		}

		public function obp_register_user_recaptcha_error_messages( $error_messages ){
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$errors = isset( $_REQUEST['error'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['error'] ) ) : '';
			if ( $errors === 'reCAPTCHA' ) {
				$error_messages[] = esc_html__( 'Google reCAPTCHA verification failed.', 'ovabookpro' );
			}

			return apply_filters( 'obp_register_user_recaptcha_error_messages', $error_messages );
		}

		public function recaptcha_login_form_init(){
			add_filter( 'obp_authenticate', array( $this, 'recaptcha_processing_login_form' ), 10, 3 );
			add_filter( 'obp_login_error_messages', array( $this, 'obp_login_recaptcha_error_messages' ) );
		}

		public function recaptcha_processing_login_form( $user, $username, $password ){

			if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) {
				if ( isset( $_POST['g-recaptcha-response'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					
					if ( true !== $this->recaptcha_validate_posted_captcha() ) {
						$remember = ! empty( $_REQUEST['rememberme'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

						$redirect = obp_login_url();
						$redirect = add_query_arg( 'error', 'reCAPTCHA', $redirect );
						if ( ! empty( $username ) ) {
							$redirect = add_query_arg( 'username', $username, $redirect );
						}
						$redirect = add_query_arg( 'rememberme', $remember, $redirect );
						wp_safe_redirect( $redirect );
						exit();
					}

				} else {

					$remember = ! empty( $_REQUEST['rememberme'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

					$redirect = obp_login_url();
					$redirect = add_query_arg( 'error', 'reCAPTCHA', $redirect );
					if ( ! empty( $username ) ) {
						$redirect = add_query_arg( 'username', $username, $redirect );
					}
					$redirect = add_query_arg( 'rememberme', $remember, $redirect );
					wp_safe_redirect( $redirect );
					exit();

				}
			}

			return apply_filters( 'obp_authenticate_recaptcha', $user, $username, $password );
		}

		public function obp_login_recaptcha_error_messages( $error_messages ){
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$errors = isset( $_REQUEST['error'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['error'] ) ) : '';
			if ( $errors === 'reCAPTCHA' ) {
				$error_messages[] = esc_html__( 'Google reCAPTCHA verification failed.', 'ovabookpro' );
			}

			return apply_filters( 'obp_login_recaptcha_error_messages', $error_messages );
		}

		public function is_key_setup_complete(){
			$output = false;

			$site_key   = $this->get_site_key();
			$secret_key = $this->get_secret_key();

			if ( ! empty( $site_key ) && ! empty( $secret_key ) ) {
				$output = true;
			}

			return $output;
		}

		public function recaptcha_validate_posted_captcha(){
			$secret_key = $this->get_secret_key();
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			$grecaptcha_response = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';
			$remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
			// phpcs:enable WordPress.Security.NonceVerification.Missing
			$captcha_obj = new \ReCaptcha\ReCaptcha( $secret_key );

			$resp = $captcha_obj->verify( $grecaptcha_response, $remote_addr );

			return (bool) $resp->isSuccess();
		}

		public function get_captcha_api_url(){
			$output = '';

			if ( 'v2' === $this->get_type() ) {
				$output = 'https://www.google.com/recaptcha/api.js?hl=' . esc_attr( get_locale() ) . '&onload=obpRecaptchaV2&render=explicit';
			} elseif ( 'v3' === $this->get_type() ) {
				$output = 'https://www.google.com/recaptcha/api.js?onload=obpRecaptchaV3&render=' . esc_attr( $this->get_site_key() );
			}

			return $output;
		}

		public function load_scripts(){
			wp_enqueue_script('obp-recaptcha', OBP_PLUGIN_URI.'assets/js/frontend/recaptcha.js', array(), false);
			wp_localize_script( 'obp-recaptcha', 'obp_recaptcha', array(
				'site_key' 	=> $this->site_key,
				'type' 		=> $this->get_type(),
			),);
			wp_enqueue_script('obp-google-recaptcha', $this->get_captcha_api_url(), array(), false );
		}
	}
}