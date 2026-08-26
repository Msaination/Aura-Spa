<?php
namespace BookPro\Admin\Settings;

use BookPro\Abstracts\OBP_Settings;

defined( 'ABSPATH' ) || exit;

class OBP_General_Setting extends OBP_Settings {
	public $option_name = 'general';
	public $title 		= null;
	public $is_tab 		= true;
	public $position 	= 10;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->title = esc_html__( 'General', 'ovabookpro' );

      	add_filter( 'obp_admin_setting_fields', array( $this, 'general_group' ), 10, 2 );
      	parent::__construct();
   	}

   	/**
	 * General group.
	 */
   	public function general_group( $groups = array(), $id = 'general' ) {
   		if ( $id === $this->option_name ) {
   			$groups[$id.'_setting'] = apply_filters( 'obp_general_settings_tab', $this->obp_general_settings_tab(), $this->option_name );
   			$groups[$id.'_google'] = apply_filters( 'obp_general_settings_google', $this->obp_general_settings_google(), $this->option_name );
   			$groups[$id.'_currency'] = apply_filters( 'obp_general_settings_currency', $this->obp_general_settings_currency(), $this->option_name );
   			$groups[$id.'_calendar'] = apply_filters( 'obp_general_settings_calendar', $this->obp_general_settings_calendar(), $this->option_name );
   			$groups[$id.'_cron_job'] = apply_filters( 'obp_general_settings_cron_job', $this->obp_general_settings_cron_job(), $this->option_name );

   		}

   		return $groups;
   	}

   	/**
   	 * General setting
   	 */
   	public function obp_general_settings_tab() {
   		$fields = apply_filters( 'obp_general_setting_fields', array(
			array(
				'type' 		=> 'select-page',
				'label' 	=> esc_html__( 'Member account page', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Include shortcode in content: [obp_member_account]', 'ovabookpro' ),
				'name' 		=> 'member_account_page_id',
				'atts' 		=> [
					'class' 			=> 'obp-select2',
					'data-placeholder' 	=> esc_html__( 'Choose a page', 'ovabookpro' )
				],
				'default' 	=> '',
          	),
          	array(
				'type' 		=> 'select-page',
				'label' 	=> esc_html__( 'Thank you page', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Redirect after booking successfully. Include shortcode in content: [obp_booking_info]', 'ovabookpro' ),
				'name' 		=> 'thank_page_id',
				'atts' 		=> [
					'class' 			=> 'obp-select2',
					'data-placeholder' 	=> esc_html__( 'Choose a page', 'ovabookpro' )
				],
				'default' 	=> '',
          	),
          	array(
				'type' 		=> 'select-page',
				'label' 	=> esc_html__( 'Login page', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Include shortcode in content: [obp_login]', 'ovabookpro' ),
				'name' 		=> 'login_page_id',
				'atts' 		=> [
					'class' 			=> 'obp-select2',
					'data-placeholder' 	=> esc_html__( 'Choose a page', 'ovabookpro' )
				],
				'default' 	=> '',
          	),
          	array(
				'type' 		=> 'select-page',
				'label' 	=> esc_html__( 'Register user page', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Include shortcode in content: [obp_register_user]', 'ovabookpro' ),
				'name' 		=> 'register_user_page_id',
				'atts' 		=> [
					'class' 			=> 'obp-select2',
					'data-placeholder' 	=> esc_html__( 'Choose a page', 'ovabookpro' )
				],
				'default' 	=> '',
          	),
          	array(
				'type' 		=> 'select-page',
				'label' 	=> esc_html__( 'Forgot password page', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Include shortcode in content: [obp_forgot_password]', 'ovabookpro' ),
				'name' 		=> 'forgot_password_page_id',
				'atts' 		=> [
					'class' 			=> 'obp-select2',
					'data-placeholder' 	=> esc_html__( 'Choose a page', 'ovabookpro' )
				],
				'default' 	=> '',
          	),
          	array(
				'type' 		=> 'select-page',
				'label' 	=> esc_html__( 'Reset password page', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Include shortcode in content: [obp_reset_password]', 'ovabookpro' ),
				'name' 		=> 'reset_password_page_id',
				'atts' 		=> [
					'class' 			=> 'obp-select2',
					'data-placeholder' 	=> esc_html__( 'Choose a page', 'ovabookpro' )
				],
				'default' 	=> '',
          	),

   			array(
				'type' 		=> 'select',
				'label' 	=> esc_html__( 'Default Staff Menu', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Choose the default menu after logging in successfully.', 'ovabookpro' ),
				'name' 		=> 'default_endpoint_staff',
				'options' 	=> obp_get_list_endpoint_title(),
				'default' 	=> 'my-profile',
				'atts' 		=> [
					'class' => 'obp-select2',
				]
   			),
   			array(
				'type' 		=> 'select',
				'label' 	=> esc_html__( 'Default Customer Menu', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Choose the default menu after logging in successfully.', 'ovabookpro' ),
				'name' 		=> 'default_endpoint_customer',
				'options' 	=> obp_get_list_endpoint_title(),
				'default' 	=> 'my-profile',
				'atts' 		=> [
					'class' => 'obp-select2',
				]
   			),
   		));
   		
   		return array(
   			'title' => esc_html__( 'General Settings', 'ovabookpro' ),
   			array(
   				'fields' => $fields
   			)
   		);
   	}

   	/**
   	 * Map setting
   	 */
   	public function obp_general_settings_google() {
   		return array(
   			'title' => esc_html__( 'Google Settings', 'ovabookpro' ),
   			array(
   				'fields' => array(
   					array(
						'type' 		=> 'input',
						'label' 	=> esc_html__( 'API Key', 'ovabookpro' ),
						'desc' 		=> __( 'You can make a API Key<a href="https://console.cloud.google.com/apis/credentials" target="_blank"> here</a>', 'ovabookpro' ),
						'name' 		=> 'google_api_key',
						'default' 	=> '',
						'atts' 		=> [
							'type' 			=> 'text',
							'autocomplete' 	=> 'off'
						]
                  	),
                  	array(
						'type' 		=> 'input',
						'label' 	=> esc_html__( 'Client ID', 'ovabookpro' ),
						'desc' 		=> __( 'You can make a Client ID<a href="https://console.cloud.google.com/auth/clients" target="_blank"> here</a>', 'ovabookpro' ),
						'name' 		=> 'google_client_id',
						'default' 	=> '',
						'atts' 		=> [
							'type' 			=> 'text',
							'autocomplete' 	=> 'off'
						]
                  	),
                  	array(
						'type' 		=> 'radio-inline',
						'label' 	=> esc_html__( 'Enable Calendar', 'ovabookpro' ),
						'name' 		=> 'google_calendar_enable',
						'options' 	=> array(
							'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
							'no' 	=> esc_html__( 'No', 'ovabookpro' ),
						),
						'default' 	=> 'no'
		          	),
   					array(
						'type' 		=> 'radio-inline',
						'label' 	=> esc_html__( 'Enable Map', 'ovabookpro' ),
						'name' 		=> 'enable_map',
						'options' 	=> array(
							'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
							'no' 	=> esc_html__( 'No', 'ovabookpro' ),
						),
						'default' 	=> 'yes'
		          	),
		          	
   					array(
						'type' 		=> 'radio-inline',
						'label' 	=> esc_html__( 'Map Platform', 'ovabookpro' ),
						'name' 		=> 'map_platform',
						'desc' 	=> esc_html__( 'Allow choose map platform', 'ovabookpro' ),
						'options' 	=> array(
							'google_map' 	=> esc_html__( 'Google Map', 'ovabookpro' ),
							'openstreetmap' => esc_html__( 'OpenStreetMap (OSM)', 'ovabookpro' ),
						),
						'default' 	=> 'google_map'
		          	),
		          	
   					
                  	array(
						'type' 		=> 'checkbox',
						'label' 	=> esc_html__( 'Bounds', 'ovabookpro' ),
						'desc' 		=> esc_html__( 'Use for Search Map', 'ovabookpro' ),
						'name' 		=> 'bounds',
						'default' 	=> '',
						'atts' 		=> [
							'type' => 'checkbox',
						]
                  	),
                  	array(
						'type' 		=> 'input',
						'label' 	=> esc_html__( 'Latitude', 'ovabookpro' ),
						'desc' 		=> '',
						'name' 		=> 'lat',
						'default' 	=> '',
						'is_hidden' => true,
						'atts' 		=> [
							'type' 			=> 'text',
							'placeholder' 	=> esc_html__( '40.730610', 'ovabookpro' ),
							'autocomplete' 	=> 'off'
						]
                  	),
                  	array(
						'type' 		=> 'input',
						'label' 	=> esc_html__( 'Longitude', 'ovabookpro' ),
						'desc' 		=> '',
						'name' 		=> 'lng',
						'default' 	=> '',
						'is_hidden' => true,
						'atts' 		=> [
							'type' 			=> 'text',
							'placeholder' 	=> esc_html__( '-73.935242', 'ovabookpro' ),
							'autocomplete' 	=> 'off'
						]
                  	),
                  	array(
						'type' 		=> 'input',
						'label' 	=> esc_html__( 'Radius(km)', 'ovabookpro' ),
						'desc' 		=> '',
						'name' 		=> 'radius',
						'default' 	=> '',
						'is_hidden' => true,
						'atts' 		=> [
							'type' 			=> 'number',
							'placeholder' 	=> esc_html__( '100', 'ovabookpro' ),
							'autocomplete' 	=> 'off'
						]
                  	),
                  	array(
						'type' 		=> 'select',
						'label' 	=> esc_html__( 'Restrictions', 'ovabookpro' ),
						'desc' 		=> esc_html__( 'Restrict the autocomplete search to a specific set of up to 5 countries.', 'ovabookpro' ),
						'name' 		=> 'restrictions',
						'options' 	=> obp_iso_alpha2(),
						'is_hidden' => true,
						'atts' 		=> [
							'multiple' 			=> true,
							'class' 			=> 'obp-select2',
							'data-placeholder' 	=> esc_html__( 'Select country', 'ovabookpro' ),
							'data-maximum' 		=> 5
						]
		   			)
   				)
   			)
   		);
   	}

   	/**
   	 * Currency setting
   	 */
   	public function obp_general_settings_currency() {
   		$currencies = obp_get_currencies();

   		if ( obp_array_exists( $currencies ) ) {
   			foreach ( $currencies as $currency => $name ) {
   				$currencies[$currency] = $name . ' ('. obp_get_currency_symbol( $currency ) .')';
   			}
   		}

   		return array(
   			'title' => esc_html__( 'Currency Options', 'ovabookpro' ),
   			array(
   				'fields' => array(
   					array(
						'type' 		=> 'select',
						'label' 	=> esc_html__( 'Currency', 'ovabookpro' ),
						'desc' 		=> esc_html__( 'Choosing currency in your country', 'ovabookpro' ),
						'name' 		=> 'currency',
						'options' 	=> $currencies,
						'default' 	=> 'USD',
						'atts' 		=> [
							'class' => 'obp-select2',
						]
		   			),
		   			array(
						'type' 		=> 'select',
						'label' 	=> esc_html__( 'Currency Position', 'ovabookpro' ),
						'desc' 		=> esc_html__( 'This controls the position of the currency symbol', 'ovabookpro' ),
						'name' 		=> 'currency_position',
						'options' 	=> [
							'left' 			=> esc_html__( 'Left', 'ovabookpro' ),
							'right' 		=> esc_html__( 'Right', 'ovabookpro' ),
							'left_space' 	=> esc_html__( 'Left with space', 'ovabookpro' ),
							'right_space' 	=> esc_html__( 'Right with space', 'ovabookpro' )
						],
						'default' 	=> 'left',
		   			),
		   			array(
						'type' 		=> 'input',
						'label' 	=> esc_html__( 'Thousand separator', 'ovabookpro' ),
						'desc' 		=> esc_html__( 'This sets the thousand separator of displayed prices', 'ovabookpro' ),
						'name' 		=> 'thousand_separator',
						'default' 	=> ',',
						'atts' 		=> [
							'type' 			=> 'text',
							'autocomplete' 	=> 'off'
						]
                  	),
                  	array(
						'type' 		=> 'input',
						'label' 	=> esc_html__( 'Decimal separator', 'ovabookpro' ),
						'desc' 		=> esc_html__( 'This sets the decimal separator of displayed prices', 'ovabookpro' ),
						'name' 		=> 'decimal_separator',
						'default' 	=> '.',
						'atts' 		=> [
							'type' 			=> 'text',
							'autocomplete' 	=> 'off'
						]
                  	),
                  	array(
						'type' 		=> 'input',
						'label' 	=> esc_html__( 'Number of decimals', 'ovabookpro' ),
						'desc' 		=> esc_html__( 'This sets the number of decimal points shown in displayed prices', 'ovabookpro' ),
						'name' 		=> 'price_num_decimals',
						'default' 	=> '2',
						'atts' 		=> [
							'type' 	=> 'number',
							'min' 	=> 0,
							'step' 	=> 1,
						]
                  	),
   				)
   			)
   		);
   	}

   	/**
   	 * Calendar setting
   	 */
   	public function obp_general_settings_calendar() {
   		return array(
   			'title' => esc_html__( 'Calendar Settings', 'ovabookpro' ),
   			array(
   				'fields' => array(
   					array(
						'type' 		=> 'select',
						'label' 	=> esc_html__( 'Date Format', 'ovabookpro' ),
						'desc' 		=> esc_html__( 'To be defined when choosing to input a date', 'ovabookpro' ),
						'name' 		=> 'date_format',
						'options' 	=> obp_get_date_formats(),
						'default' 	=> 'Y-m-d',
		   			),
		   			array(
		   				'type' 		=> 'html',
		   				'name' 		=> 'timezone',
		   				'label' 	=> esc_html__( 'Timezone', 'ovabookpro' ),
		   				'html' 		=> wp_timezone_string().'<br/><a href="'.get_admin_url().'options-general.php'.'">'.esc_html__( 'Change timezone', 'ovabookpro' ).'</a>',
		   			),
		   			array(
						'type' 		=> 'select',
						'label' 	=> esc_html__( 'Time Format', 'ovabookpro' ),
						'desc' 		=> '',
						'name' 		=> 'time_format',
						'options' 	=> obp_get_time_formats(),
						'default' 	=> 'H:i',
		   			),
		   			array(
						'type' 		=> 'select',
						'label' 	=> esc_html__( 'Time Step', 'ovabookpro' ),
						'desc' 		=> esc_html__( 'Apply to Overall Schedule, My Schedule, Staff Schedule.', 'ovabookpro' ),
						'name' 		=> 'time_step',
						'options' 	=> [
							'00:05:00' => esc_html__( '5 minutes', 'ovabookpro' ),
							'00:10:00' => esc_html__( '10 minutes', 'ovabookpro' ),
							'00:15:00' => esc_html__( '15 minutes', 'ovabookpro' ),
							'00:20:00' => esc_html__( '20 minutes', 'ovabookpro' ),
							'00:30:00' => esc_html__( '30 minutes', 'ovabookpro' ),
							'00:60:00' => esc_html__( '60 minutes', 'ovabookpro' ),
						],
						'default' 	=> '00:30:00',
		   			),

		   			array(
						'type' 		=> 'select',
						'label' 	=> esc_html__( 'Input date format language', 'ovabookpro' ),
						'name' 		=> 'datepicker_language',
						'options' 	=> obp_get_datepicker_languages(),
						'default' 	=> 'default',
						'atts' 		=> [
							'class' => 'obp-select2',
						]
		   			),
		   			array(
						'type' 		=> 'select',
						'label' 	=> esc_html__( 'Choose Weekend', 'ovabookpro' ),
						'desc' 		=> '',
						'name' 		=> 'weekend',
						'options' 	=> obp_get_weekend(),
						'default' 	=> array( 'saturday', 'sunday' ),
						'atts' 		=> [
							'multiple' 			=> true,
							'class' 			=> 'obp-select2',
							'data-placeholder' 	=> esc_html__( 'Choose weekend', 'ovabookpro' ),
						]
		   			),
		   			array(
						'type' 		=> 'select',
						'label' 	=> esc_html__( 'The First Day of Week', 'ovabookpro' ),
						'desc' 		=> '',
						'name' 		=> 'first_day',
						'options' 	=> obp_get_weekend(),
						'default' 	=> 'monday',
						'atts' 		=> [
							'data-placeholder' => esc_html__( 'Choose weekend', 'ovabookpro' ),
						]
		   			)
   				)
   			)
   		);
   	}

   	/**
   	 * Cron Job setting
   	 */
   	public function obp_general_settings_cron_job() {
   		return array(
   			'title' => esc_html__( 'Cron Job', 'ovabookpro' ),
   			array(
   				'fields' => array(
   					array(
						'type' 		=> 'input',
						'label' 	=> esc_html__( 'Frequency Update Order Holding Status', 'ovabookpro' ),
						'desc' 		=> esc_html__( 'Every X minutes, the system scans once for changes in booking statuses and automatically update the booking status from Pending to Expired.', 'ovabookpro' ),
						'name' 		=> 'order_holding',
						'default' 	=> '60',
						'unit' 		=> esc_html__( 'minutes', 'ovabookpro' ),
						'atts' 		=> [
							'type' 			=> 'text',
							'autocomplete' 	=> 'off',
							'placeholder' 	=> 60
						]
                  	),
   					array(
						'type' 		=> 'input',
						'label' 	=> esc_html__( 'Frequency Update Order Processing Status', 'ovabookpro' ),
						'desc' 		=> esc_html__( 'Every X minutes, the system scans once for changes in booking statuses and automatically update the booking status from Processing to Completed.', 'ovabookpro' ),
						'name' 		=> 'update_order_queue',
						'default' 	=> '60',
						'unit' 		=> esc_html__( 'minutes', 'ovabookpro' ),
						'atts' 		=> [
							'type' 			=> 'text',
							'autocomplete' 	=> 'off',
							'placeholder' 	=> 60
						]
                  	),
                  	
   				)
   			)
   		);
   	}
}