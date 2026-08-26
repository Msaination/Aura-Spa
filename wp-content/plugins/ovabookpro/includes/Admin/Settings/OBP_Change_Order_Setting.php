<?php
namespace BookPro\Admin\Settings;

use BookPro\Abstracts\OBP_Settings;

defined( 'ABSPATH' ) || exit;

class OBP_Cart_Order_Setting extends OBP_Settings {

	public $option_name = 'change_order';
	public $title 		= null;
	public $is_tab 		= false;
	public $position 	= 80;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->title = esc_html__( 'Change Booking', 'ovabookpro' );

      	parent::__construct();
   	}

   	/**
	 * Render fields.
	 */
   	public function render_fields() {
   		$fields = apply_filters( 'obp_change_order_setting_fields', array(
   			array(
				'type' 		=> 'radio-inline',
				'label' 	=> esc_html__( 'Allow to Change Booking', 'ovabookpro' ),
				'name' 		=> 'change_order_enable',
				'desc' 		=> esc_html__( 'Allow customers to change booking', 'ovabookpro' ),
				'options' 	=> array(
					'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
					'no' 	=> esc_html__( 'No', 'ovabookpro' ),
				),
				'default' 	=> 'yes'
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Limited number of times', 'ovabookpro' ),
				'name' 		=> 'change_order_limited',
				'default' 	=> 1,
				'atts' 		=> [
					'type' 			=> 'number',
					'class' 		=> 'small-text',
					'placeholder' 	=> '1',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Minimum Time Before Changing', 'ovabookpro' ),
				'name' 		=> 'change_order_before_time',
				'desc' 		=> esc_html__( 'Change an appointment at least x minutes before the appointment time', 'ovabookpro' ),
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


   		return apply_filters( 'obp_change_order_setting_render_fields', array(
   			array(
	   			'fields' 	=> $fields,
	   		)
   		));
   	}
}