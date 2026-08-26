<?php
namespace BookPro\Admin\Settings;

use BookPro\Abstracts\OBP_Settings;

defined( 'ABSPATH' ) || exit;


class OBP_Recaptcha_Setting extends OBP_Settings {

	public $option_name = 'recaptcha';
	public $title 		= null;
	public $is_tab 		= false;
	public $position 	= 70;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->title = esc_html__( 'reCAPTCHA', 'ovabookpro' );

      	parent::__construct();
   	}

   	/**
	 * Render fields.
	 */
   	public function render_fields() {
   		$fields_1 = apply_filters( 'obp_recaptcha_setting_fields', array(

   			array(
				'type' 		=> 'radio-inline',
				'label' 	=> esc_html__( 'Show Recaptcha', 'ovabookpro' ),
				'name' 		=> 'recaptcha_enable',
				'desc' 		=> '',
				'options' 	=> array(
					'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
					'no' 	=> esc_html__( 'No', 'ovabookpro' ),
				),
				'default' 	=> 'no'
          	),
   			array(
				'type' 		=> 'radio-inline',
				'label' 	=> esc_html__( 'Type', 'ovabookpro' ),
				'name' 		=> 'type',
				'desc' 		=> '',
				'options' 	=> array(
					'v2' 	=> esc_html__( 'V2', 'ovabookpro' ),
					'v3' 	=> esc_html__( 'V3', 'ovabookpro' ),
				),
				'default' 	=> 'v2'
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Site Key', 'ovabookpro' ),
				'name' 		=> 'site_key',
				'default' 	=> '',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Secret Key', 'ovabookpro' ),
				'name' 		=> 'secret_key',
				'default' 	=> '',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	
   		) );

   		$fields_2 = apply_filters( 'obp_recaptcha_setting_fields_2', array(
   			array(
				'type' 		=> 'checkbox',
				'label' 	=> esc_html__( 'Login Form', 'ovabookpro' ),
				'name' 		=> 'login_form',
				'desc' 		=> '',
				'atts' 		=> [
					'type' 	=> 'checkbox',
				],
				'default' 	=> '',
   			),
   			array(
				'type' 		=> 'checkbox',
				'label' 	=> esc_html__( 'Register User Form', 'ovabookpro' ),
				'name' 		=> 'register_user_form',
				'desc' 		=> '',
				'atts' 		=> [
					'type' 	=> 'checkbox',
				],
				'default' 	=> '',
   			),
   			array(
				'type' 		=> 'checkbox',
				'label' 	=> esc_html__( 'Reset Password Form', 'ovabookpro' ),
				'name' 		=> 'reset_password_form',
				'desc' 		=> '',
				'atts' 		=> [
					'type' 	=> 'checkbox',
				],
				'default' 	=> '',
   			),
   			array(
				'type' 		=> 'checkbox',
				'label' 	=> esc_html__( 'Forgot Password Form', 'ovabookpro' ),
				'name' 		=> 'forgot_password_form',
				'desc' 		=> '',
				'atts' 		=> [
					'type' 	=> 'checkbox',
				],
				'default' 	=> '',
   			),
   			array(
				'type' 		=> 'checkbox',
				'label' 	=> esc_html__( 'Booking Service Form', 'ovabookpro' ),
				'name' 		=> 'booking_service_form',
				'desc' 		=> '',
				'atts' 		=> [
					'type' 	=> 'checkbox',
				],
				'default' 	=> '',
   			),
   		) );

   		return apply_filters( 'obp_recaptcha_setting_render_fields', array(
   			array(
   				'title' 	=> esc_html__( 'Google Recaptcha Settings', 'ovabookpro' ),
	   			'fields' 	=> $fields_1,
	   		),
	   		array(
	   			'title' 	=> esc_html__( 'Apply for:', 'ovabookpro' ),
	   			'fields' 	=> $fields_2,
	   		),
   		));
   	}
}
