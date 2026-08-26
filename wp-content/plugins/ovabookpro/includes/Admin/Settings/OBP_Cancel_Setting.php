<?php
namespace BookPro\Admin\Settings;

use BookPro\Abstracts\OBP_Settings;

defined( 'ABSPATH' ) || exit;

class OBP_Cancel_Setting extends OBP_Settings {

	public $option_name = 'cancel';
	public $title 		= null;
	public $is_tab 		= false;
	public $position 	= 80;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->title = esc_html__( 'Cancel Booking', 'ovabookpro' );

      	parent::__construct();
   	}

   	/**
	 * Rendor fields.
	 */
   	public function render_fields() {

   		$fields = apply_filters( 'obp_cancel_setting_fields', array(
   			array(
				'type' 		=> 'radio-inline',
				'label' 	=> esc_html__( 'Allow to Cancel Bookings', 'ovabookpro' ),
				'name' 		=> 'cancel_order_enable',
				'desc' 		=> esc_html__( 'Allow customers to cancel booking', 'ovabookpro' ),
				'options' 	=> array(
					'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
					'no' 	=> esc_html__( 'No', 'ovabookpro' ),
				),
				'default' 	=> 'yes'
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Minimum Time Before Canceling', 'ovabookpro' ),
				'name' 		=> 'cancel_order_before_time',
				'desc' 		=> esc_html__( 'Cancel an appointment at least x minutes before the appointment time', 'ovabookpro' ),
				'unit' 		=> esc_html__( 'Minutes', 'ovabookpro' ),
				'default' 	=> 120,
				'atts' 		=> [
					'type' 			=> 'number',
					'class' 		=> 'small-text',
					'placeholder' 	=> '120',
					'autocomplete' 	=> 'off'
				]
          	),
   		) );

   		return apply_filters( 'obp_cancel_setting_render_fields', array(
   			array(
	   			'fields' 	=> $fields,
	   		)
   		));
   	}

}