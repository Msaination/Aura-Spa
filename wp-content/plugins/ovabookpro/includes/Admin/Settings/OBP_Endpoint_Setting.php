<?php
namespace BookPro\Admin\Settings;

use BookPro\Abstracts\OBP_Settings;

defined( 'ABSPATH' ) || exit;

class OBP_Endpoint_Setting extends OBP_Settings {

	public $option_name = 'endpoint';
	public $title 		= null;
	public $is_tab 		= false;
	public $position 	= 100;


	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->title = esc_html__( 'Endpoint', 'ovabookpro' );

      	parent::__construct();
   	}

   	/**
	 * Render fields.
	 */
   	public function render_fields() {

   		$fields = apply_filters( 'obp_endpoint_setting_fields', array(
   			array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'My Business', 'ovabookpro' ),
				'name' 		=> 'my_business',
				'default' 	=> 'my-business',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Manage Booking', 'ovabookpro' ),
				'name' 		=> 'manage_booking',
				'default' 	=> 'manage-booking',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Manage Type', 'ovabookpro' ),
				'name' 		=> 'manage_type',
				'default' 	=> 'manage-type',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Manage Plan', 'ovabookpro' ),
				'name' 		=> 'manage_plan',
				'default' 	=> 'manage-plan',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Manage Staff', 'ovabookpro' ),
				'name' 		=> 'manage_staff',
				'default' 	=> 'manage-staff',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Edit Staff', 'ovabookpro' ),
				'name' 		=> 'edit_staff',
				'default' 	=> 'edit-staff',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Manage Coupon', 'ovabookpro' ),
				'name' 		=> 'manage_coupon',
				'default' 	=> 'manage-coupon',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Edit Coupon', 'ovabookpro' ),
				'name' 		=> 'edit_coupon',
				'default' 	=> 'edit-coupon',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Manage Service', 'ovabookpro' ),
				'name' 		=> 'manage_service',
				'default' 	=> 'manage-service',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Edit Service', 'ovabookpro' ),
				'name' 		=> 'edit_service',
				'default' 	=> 'edit-service',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Overall Schedule', 'ovabookpro' ),
				'name' 		=> 'overall_schedule',
				'default' 	=> 'overall-schedule',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Staff Schedule', 'ovabookpro' ),
				'name' 		=> 'staff_schedule',
				'default' 	=> 'staff-schedule',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Manage Role', 'ovabookpro' ),
				'name' 		=> 'manage_role',
				'default' 	=> 'manage-role',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'My Wallet', 'ovabookpro' ),
				'name' 		=> 'my_wallet',
				'default' 	=> 'my-wallet',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'My Booking', 'ovabookpro' ),
				'name' 		=> 'my_booking',
				'default' 	=> 'my-booking',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'My Wishlist', 'ovabookpro' ),
				'name' 		=> 'my_wishlist',
				'default' 	=> 'my-wishlist',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'My Profile', 'ovabookpro' ),
				'name' 		=> 'my_profile',
				'default' 	=> 'my-profile',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Logout', 'ovabookpro' ),
				'name' 		=> 'logout',
				'default' 	=> 'logout',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Our Work', 'ovabookpro' ),
				'name' 		=> 'our_work',
				'default' 	=> 'our-work',
				'atts' 		=> [
					'type' 			=> 'text',
					'autocomplete' 	=> 'off'
				]
          	),
   		) );

   		return apply_filters( 'obp_endpoint_setting_render_fields', array(
   			array(
	   			'fields' 	=> $fields,
	   		)
   		));
   	}
}
