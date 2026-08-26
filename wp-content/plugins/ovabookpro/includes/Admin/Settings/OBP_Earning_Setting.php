<?php
namespace BookPro\Admin\Settings;

use BookPro\Abstracts\OBP_Settings;

defined( 'ABSPATH' ) || exit;

class OBP_Earning_Setting extends OBP_Settings {

	public $option_name = 'earning';
	public $title 		= null;
	public $is_tab 		= false;
	public $position 	= 20;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->title = esc_html__( 'Earning Method', 'ovabookpro' );

      	parent::__construct();
   	}

   	/**
	 * Rendor fields.
	 */
   	public function render_fields() {
   		return apply_filters( 'obp_earning_setting_render_fields', array(
   			array(
	   			'title' 	=> esc_html__( 'The Customer pay for website owner (System Fee)', 'ovabookpro' ),
	   			'fields' 	=> array(
   					array(
						'type' 		=> 'input',
						'label' 	=> esc_html__( 'Fixed Amount per booking', 'ovabookpro' ),
						'name' 		=> 'system_fixed_fee',
						'before' 	=> 'line',
						'unit' 		=> obp_get_currency_symbol(),
						'atts' 		=> [
							'type' 			=> 'text',
							'class' 		=> 'obp-input-unit',
							'placeholder' 	=> esc_html__( '2', 'ovabookpro' ),
							'autocomplete' 	=> 'off'
						]
                  	),
                  	array(
						'type' 		=> 'input',
						'label' 	=> esc_html__( 'Percent Amount per booking', 'ovabookpro' ),
						'name' 		=> 'system_percent_fee',
						'before' 	=> 'line',
						'unit' 		=> '%',
						'atts' 		=> [
							'type' 			=> 'text',
							'class' 		=> 'obp-input-unit',
							'placeholder' 	=> esc_html__( '1', 'ovabookpro' ),
							'autocomplete' 	=> 'off'
						]
                  	),
   				)
	   		),
	   		array(
	   			'title' 	=> esc_html__( 'Transfer profit from Pending to Balance', 'ovabookpro' ),
	   			'fields' => array(
	   				array(
						'type' 		=> 'radio',
						'name' 		=> 'transfer_profit_to_balance',
						'options' 	=> array(
							'one_service' 	=> esc_html__( 'All profit from a booking will be transferred when the store completes the first service for the booking.', 'ovabookpro' ),
							'all_services' 	=> esc_html__( 'All profit from a booking will be transferred when the store has completed all services for the booking.', 'ovabookpro' ),
							'each_service' 	=> esc_html__( 'Only the profit from the completed services of that order will be transferred.', 'ovabookpro' ),
						),
						'default' 	=> 'one_service'
		          	),
	   			),
	   		)
   		));
   	}
}