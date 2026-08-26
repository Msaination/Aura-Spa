<?php
namespace BookPro\Admin\Settings;

use BookPro\Abstracts\OBP_Settings;


defined( 'ABSPATH' ) || exit;


class OBP_Payment_Setting extends OBP_Settings {

	public $option_name = 'payment';
	public $title 		= null;
	public $is_tab 		= false;
	public $position 	= 40;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->title = esc_html__( 'Payments', 'ovabookpro' );

      	parent::__construct();
   	}

   	/**
	 * Rendor fields.
	 */
   	public function render_fields() {
   		$fields = apply_filters( 'obp_payment_setting_fields', array(
   			array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Maximum time to complete payment', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Booking will be deleted if the payment completion time is over x minutes', 'ovabookpro' ),
				'name' 		=> 'max_time_complete_checkout',
				'default' 	=> '60',
				'unit' 		=> esc_html__( 'minutes', 'ovabookpro' ),
				'atts' 		=> [
					'type' 	=> 'number',
					'min' 	=> 0,
					'step' 	=> 1,
				]
          	),
		));

   		return apply_filters( 'obp_payment_setting_render_fields', array(
   			array(
	   			'title' 	=> esc_html__( 'Payments Settings', 'ovabookpro' ),
	   			'fields' 	=> $fields,
	   		),
	   		array(
	   			'fields' 	=> array(
	   				array(
	   					'belong_to' => 'offline',
						'type' 		=> 'checkbox',
						'label' 	=> esc_html__( 'Enable', 'ovabookpro' ),
						'desc' 		=> '',
						'name' 		=> 'offline_enable',
						'default' 	=> '1',
		   			),
	   				array(
	   					'belong_to' => 'woocommerce',
						'type' 		=> 'checkbox',
						'label' 	=> esc_html__( 'Enable', 'ovabookpro' ),
						'desc' 		=> '',
						'name' 		=> 'woo_enable',
						'default' 	=> '1',
		   			),
	   				array(
	   					'belong_to' => 'woocommerce',
						'type' 		=> 'checkbox-multiple',
						'label' 	=> esc_html__( 'Booking is accepted when order status in WooCommerce is', 'ovabookpro' ),
						'desc' 		=> '',
						'name' 		=> 'woo_order_status',
						'options' 	=> array(
							'wc-completed' => __( 'Completed', 'ovabookpro' ),
							'wc-processing' => __( 'Processing', 'ovabookpro' ),
							'wc-on-hold' => __( 'On Hold', 'ovabookpro' ),
						),
						'default' 	=> array( 'wc-completed' ),
		   			),
	   			),
	   			'tabs' 		=> array(
	   				'offline' 		=> esc_html__( 'Offline', 'ovabookpro' ),
	   				'woocommerce' 	=> esc_html__( 'Woocommerce', 'ovabookpro' ),
	   			),
	   		),
   		));
   	}

}