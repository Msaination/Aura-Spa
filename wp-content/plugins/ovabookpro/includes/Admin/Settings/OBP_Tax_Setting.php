<?php
namespace BookPro\Admin\Settings;

use BookPro\Abstracts\OBP_Settings;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Tax_Setting") ) {
	
	class OBP_Tax_Setting extends OBP_Settings {

		public $option_name = 'tax';
		public $title 		= null;
		public $is_tab 		= false;
		public $position 	= 30;

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->title = esc_html__( 'Tax', 'ovabookpro' );

	      	parent::__construct();
	   	}

	   	/**
		 * Rendor fields.
		 */
	   	public function render_fields() {
	   		$fields = apply_filters( 'obp_tax_setting_fields', array(
	   			array(
					'type' 		=> 'radio-inline',
					'label' 	=> esc_html__( 'Show Tax', 'ovabookpro' ),
					'name' 		=> 'show_tax',
					'options' 	=> array(
						'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
						'no' 	=> esc_html__( 'No', 'ovabookpro' ),
					),
					'default' 	=> 'yes'
	          	),
	          	array(
					'type' 		=> 'radio',
					'label' 	=> esc_html__( 'Prices entered with tax', 'ovabookpro' ),
					'name' 		=> 'prices_include_tax',
					'options' 	=> array(
						'yes' 	=> esc_html__( 'Yes, I will enter prices inclusive of tax', 'ovabookpro' ),
						'no' 	=> esc_html__( 'No, I will enter prices exclusive of tax', 'ovabookpro' ),
					),
					'default' 	=> 'yes'
	          	),
				array(
					'type'         => 'select',
					'label'     => esc_html__( 'Display prices of items', 'ovabookpro' ),
					'desc'         => '',
					'name'         => 'tax_display_item',
					'options'     => [
					'incl' => esc_html__( 'Including tax', 'ovabookpro' ),
					'excl' => esc_html__( 'Excluding tax', 'ovabookpro' ),
				],
					'default'     => 'incl',
				),
					array(
					'type'         => 'select',
					'label'     => esc_html__( 'Display prices during cart and checkout', 'ovabookpro' ),
					'desc'         => '',
					'name'         => 'tax_display_cart',
					'options'     => [
					'incl' => esc_html__( 'Including tax', 'ovabookpro' ),
					'excl' => esc_html__( 'Excluding tax', 'ovabookpro' ),
				],
					'default'     => 'incl',
				),
				array(
					'type'         => 'warning',
					'id'         => 'tax_warning',
					'is_hidden' => true,
					'mesg'         => esc_html__( 'To avoid possible rounding errors, prices should be entered and displayed consistently in all locations either including, or excluding taxes', 'ovabookpro' )
				),
			));

	   		return apply_filters( 'obp_tax_setting_render_fields', array(
	   			array(
		   			'title' 	=> esc_html__( 'Tax Settings', 'ovabookpro' ),
		   			'fields' 	=> $fields
		   		)
	   		));
	   	}
	}
}

