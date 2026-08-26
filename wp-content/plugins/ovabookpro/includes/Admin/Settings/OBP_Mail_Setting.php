<?php
namespace BookPro\Admin\Settings;

use BookPro\Abstracts\OBP_Settings;

defined( 'ABSPATH' ) || exit;

class OBP_Mail_Setting extends OBP_Settings {
	public $option_name = 'mail';
	public $title 		= null;
	public $is_tab 		= true;
	public $position 	= 60;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->title = esc_html__( 'Mail', 'ovabookpro' );

      	add_filter( 'obp_admin_setting_fields', array( $this, 'mail_group' ), 10, 2 );
      	parent::__construct();
   	}

   	/**
	 * General group.
	 */
   	public function mail_group( $groups = array(), $id = 'mail' ) {
   		if ( $id === $this->option_name ) {
   			$groups[$id.'_new_order'] = apply_filters( 'obp_mail_settings_new_order', $this->obp_mail_settings_new_order(), $this->option_name );
   			$groups[$id.'_cancel_order'] = apply_filters( 'obp_mail_settings_cancel_order', $this->obp_mail_settings_cancel_order(), $this->option_name );
   			$groups[$id.'_withdraw'] = apply_filters( 'obp_mail_settings_withdraw', $this->obp_mail_settings_withdraw(), $this->option_name );

   			$groups[$id.'_change_order'] = apply_filters( 'obp_mail_settings_change_order', $this->obp_mail_settings_change_order(), $this->option_name );
   			$groups[$id.'_delete_account'] = apply_filters( 'obp_mail_settings_delete_account', $this->obp_mail_settings_delete_account(), $this->option_name );
   		}

   		return $groups;
   	}

   	/* New Vendor */

   	public function obp_mail_settings_new_order(){
   		$fields = apply_filters( 'obp_new_order_setting_fields', array(
			array(
				'type' 		=> 'radio-inline',
				'label' 	=> esc_html__( 'Enable', 'ovabookpro' ),
				'name' 		=> 'new_order_send_mail',
				'desc' 		=> esc_html__( 'Allow to send an email after a customer booked successfully', 'ovabookpro' ),
				'options' 	=> array(
					'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
					'no' 	=> esc_html__( 'No', 'ovabookpro' ),
				),
				'default' 	=> 'yes'
          	),
          	array(
				'type' 		=> 'select',
				'label' 	=> esc_html__( 'Send email to', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Send email to admin, customer, staff', 'ovabookpro' ),
				'name' 		=> 'new_order_send_email_to',
				'options' 	=> apply_filters( 'new_order_send_email_to_options', array(
					'customer' 	=> esc_html__( 'Customer', 'ovabookpro' ),
					'admin' 	=> esc_html__( 'Admin', 'ovabookpro' ),
					'staff' 	=> esc_html__( 'Staff', 'ovabookpro' ),
				) ),
				'default' 	=> array( 'customer' ),
				'atts' 		=> [
					'multiple' 			=> true,
					'class' 			=> 'obp-select2',
					'data-placeholder' 	=> esc_html__( 'Send email to', 'ovabookpro' ),
				]
   			),
   			
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Subject', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail list', 'ovabookpro' ),
				'name' 		=> 'new_order_subject',
				'default' 	=> esc_attr__( 'Booking Success', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Booking Success', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'From name', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail detail', 'ovabookpro' ),
				'name' 		=> 'new_order_from_name',
				'default' 	=> esc_attr__( 'Booking Success', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Booking Success', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Send from email', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Customers will know them to receive mail from which email address is', 'ovabookpro' ),
				'name' 		=> 'new_order_send_from_email',
				'default' 	=> get_option( 'admin_email', 'contact@ovatheme.com' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> 'contact@ovatheme.com',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Recipient(s)', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'ovabookpro' ),
				'name' 		=> 'new_order_recipient',
				'default' 	=> '',
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> '',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'checkbox',
				'label' 	=> esc_html__( 'Send email with invoice attached', 'ovabookpro' ),
				'name' 		=> 'new_order_send_mail_invoice',
				'desc' 		=> esc_html__( 'Allows sending emails with invoice attachments', 'ovabookpro' ),
				'atts' 		=> [
					'type' 	=> 'checkbox',
				],
				'default' 	=> '1',
   			),
          	array(
				'type' 		=> 'file',
				'label' 	=> esc_html__( 'Additional attachments', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Add attachments to send mail', 'ovabookpro' ),
				'name' 		=> 'new_order_additional_attach',
				'default' 	=> '',
          	),
          	array(
				'type' 		=> 'code-editor',
				'label' 	=> esc_html__( 'Custom Style', 'ovabookpro' ),
				'desc' 		=> '',
				'name' 		=> 'new_order_email_style',
				'default' 	=> '',
          	),
   		));
   		
   		return array(
   			'title' => esc_html__( 'New Booking', 'ovabookpro' ),
   			array(
   				'fields' => $fields,
   			)
   		);
   	}

   	/* Cancel Booking */
   	public function obp_mail_settings_cancel_order(){
   		$fields = apply_filters( 'obp_cancel_order_setting_fields', array(
   			/* Admin */
			array(
				'belong_to' => 'cancel_order_admin',
				'type' 		=> 'radio-inline',
				'label' 	=> esc_html__( 'Send mail to Admin:', 'ovabookpro' ),
				'name' 		=> 'cancel_admin_send_mail',
				'options' 	=> array(
					'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
					'no' 	=> esc_html__( 'No', 'ovabookpro' ),
				),
				'default' 	=> 'yes'
          	),
          	array(
				'belong_to' => 'cancel_order_admin',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Subject', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail list', 'ovabookpro' ),
				'name' 		=> 'cancel_admin_subject',
				'default' 	=> esc_attr__( 'Cancel Booking', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Cancel Booking', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'belong_to' => 'cancel_order_admin',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'From 	', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail detail', 'ovabookpro' ),
				'name' 		=> 'cancel_admin_from_name',
				'default' 	=> esc_attr__( 'Cancel Booking', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Cancel Booking', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'belong_to' => 'cancel_order_admin',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Send from email', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Customers will know them to receive mail from which email address is', 'ovabookpro' ),
				'name' 		=> 'cancel_admin_send_from_email',
				'default' 	=> get_option( 'admin_email', 'contact@ovatheme.com' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> 'contact@ovatheme.com',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'belong_to' => 'cancel_order_admin',
				'type' 		=> 'text-editor',
				'label' 	=> esc_html__( 'Email Content', 'ovabookpro' ),
				'desc' 		=> '[booking_id]',
				'name' 		=> 'cancel_admin_email_content',
				'default' 	=> __( 'The customer cancelled booking<br>#[booking_id]', 'ovabookpro' ),
          	),
          	/* Customer */
          	array(
				'belong_to' => 'cancel_order_customer',
				'type' 		=> 'radio-inline',
				'label' 	=> esc_html__( 'Send mail to Customer:', 'ovabookpro' ),
				'name' 		=> 'cancel_customer_send_mail',
				'options' 	=> array(
					'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
					'no' 	=> esc_html__( 'No', 'ovabookpro' ),
				),
				'default' 	=> 'yes'
          	),
          	array(
				'belong_to' => 'cancel_order_customer',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Subject', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail list', 'ovabookpro' ),
				'name' 		=> 'cancel_customer_subject',
				'default' 	=> esc_attr__( 'Cancel Booking successfully', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Cancel Booking successfully', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'belong_to' => 'cancel_order_customer',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'From name', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail detail', 'ovabookpro' ),
				'name' 		=> 'cancel_customer_from_name',
				'default' 	=> esc_attr__( 'Cancel Booking successfully', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Cancel Booking successfully', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'belong_to' => 'cancel_order_customer',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Send from email', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Customers will know them to receive mail from which email address is', 'ovabookpro' ),
				'name' 		=> 'cancel_customer_send_from_email',
				'default' 	=> get_option( 'admin_email', 'contact@ovatheme.com' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> 'contact@ovatheme.com',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
          		'belong_to' => 'cancel_order_customer',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Recipient(s)', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'ovabookpro' ),
				'name' 		=> 'cancel_customer_recipient',
				'default' 	=> '',
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> '',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'belong_to' => 'cancel_order_customer',
				'type' 		=> 'text-editor',
				'label' 	=> esc_html__( 'Email Content', 'ovabookpro' ),
				'desc' 		=> '[booking_id]',
				'name' 		=> 'cancel_customer_email_content',
				'default' 	=> __( 'You cancelled booking #[booking_id] successfully. We will refund you soon.', 'ovabookpro' ),
          	),
   		));
   		
   		return array(
   			'title' => esc_html__( 'Cancel Booking', 'ovabookpro' ),
   			array(
   				'fields' => $fields,
   				'accordion' => apply_filters( 'cancel_order_accordion', array(
					'cancel_order_admin' => esc_html__( 'Admin', 'ovabookpro' ),
					'cancel_order_customer' => esc_html__( 'Customer', 'ovabookpro' ),
				) ),
   			)
   		);

   		
   	}

   	public function obp_mail_settings_withdraw(){
   		$fields = apply_filters( 'obp_withdraw_setting_fields', array(
   			// Request
   			array(
				'belong_to' => 'withdraw_request',
				'type' 		=> 'radio-inline',
				'label' 	=> esc_html__( 'Enable', 'ovabookpro' ),
				'name' 		=> 'withdraw_request_send_mail',
				'desc' 		=> esc_html__( 'Allow to send an email after a user requests a withdrawal', 'ovabookpro' ),
				'options' 	=> array(
					'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
					'no' 	=> esc_html__( 'No', 'ovabookpro' ),
				),
				'default' 	=> 'yes'
          	),
   			
          	array(
          		'belong_to' => 'withdraw_request',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Subject', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail list', 'ovabookpro' ),
				'name' 		=> 'withdraw_request_subject',
				'default' 	=> esc_attr__( 'Withdrawal Request', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Withdrawal Request', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
          		'belong_to' => 'withdraw_request',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'From name', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail detail', 'ovabookpro' ),
				'name' 		=> 'withdraw_request_from_name',
				'default' 	=> esc_attr__( 'Withdrawal Request', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Withdrawal Request', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
          		'belong_to' => 'withdraw_request',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Send from email', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Customers will know them to receive mail from which email address is', 'ovabookpro' ),
				'name' 		=> 'withdraw_request_send_from_email',
				'default' 	=> get_option( 'admin_email', 'contact@ovatheme.com' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> 'contact@ovatheme.com',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
   				'belong_to' => 'withdraw_request',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Recipient(s)', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'ovabookpro' ),
				'name' 		=> 'withdraw_request_recipient',
				'default' 	=> '',
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> '',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'belong_to' => 'withdraw_request',
				'type' 		=> 'text-editor',
				'label' 	=> esc_html__( 'Email Content', 'ovabookpro' ),
				'desc' 		=> __( "Name: [obp_name]</br>Amount: [obp_amount]</br>Withdraw Date: [obp_withdraw_date]</br>Payout Method: [obp_payout_method]</br>Payout Status: [obp_payout_status]", 'ovabookpro' ),
				'name' 		=> 'withdraw_request_email_content',
				'default' 	=> __( "Name: [obp_name]\r\nAmount: [obp_amount]\r\nWithdraw Date: [obp_withdraw_date]\r\nPayout Method: [obp_payout_method]\r\nPayout Status: [obp_payout_status]", "ovabookpro" ),
          	),
   			// Success
			array(
				'belong_to' => 'withdraw_success',
				'type' 		=> 'radio-inline',
				'label' 	=> esc_html__( 'Enable', 'ovabookpro' ),
				'name' 		=> 'withdraw_success_send_mail',
				'desc' 		=> esc_html__( 'Allow to send an email after a user withdraw successfully', 'ovabookpro' ),
				'options' 	=> array(
					'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
					'no' 	=> esc_html__( 'No', 'ovabookpro' ),
				),
				'default' 	=> 'yes'
          	),
   			
          	array(
          		'belong_to' => 'withdraw_success',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Subject', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail list', 'ovabookpro' ),
				'name' 		=> 'withdraw_success_subject',
				'default' 	=> esc_attr__( 'Successful Withdrawal', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Successful Withdrawal', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
          		'belong_to' => 'withdraw_success',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'From name', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail detail', 'ovabookpro' ),
				'name' 		=> 'withdraw_success_from_name',
				'default' 	=> esc_attr__( 'Successful withdrawal', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Successful Withdrawal', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
          		'belong_to' => 'withdraw_success',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Send from email', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Customers will know them to receive mail from which email address is', 'ovabookpro' ),
				'name' 		=> 'withdraw_success_send_from_email',
				'default' 	=> get_option( 'admin_email', 'contact@ovatheme.com' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> 'contact@ovatheme.com',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
   				'belong_to' => 'withdraw_success',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Recipient(s)', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'ovabookpro' ),
				'name' 		=> 'withdraw_success_recipient',
				'default' 	=> '',
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> '',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'belong_to' => 'withdraw_success',
				'type' 		=> 'text-editor',
				'label' 	=> esc_html__( 'Email Content', 'ovabookpro' ),
				'desc' 		=> __( "Name: [obp_name]</br>Amount: [obp_amount]</br>Withdraw Date: [obp_withdraw_date]</br>Payout Method: [obp_payout_method]</br>Payout Status: [obp_payout_status]", 'ovabookpro' ),
				'name' 		=> 'withdraw_success_email_content',
				'default' 	=> __( "Name: [obp_name]\r\nAmount: [obp_amount]\r\nWithdraw Date: [obp_withdraw_date]\r\nPayout Method: [obp_payout_method]\r\nPayout Status: [obp_payout_status]", "ovabookpro" ),
          	),
          	// Cancelled
          	array(
				'belong_to' => 'withdraw_cancelled',
				'type' 		=> 'radio-inline',
				'label' 	=> esc_html__( 'Enable', 'ovabookpro' ),
				'name' 		=> 'withdraw_cancelled_send_mail',
				'desc' 		=> esc_html__( 'Allow to send an email after a user\'s withdrawal is cancelled', 'ovabookpro' ),
				'options' 	=> array(
					'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
					'no' 	=> esc_html__( 'No', 'ovabookpro' ),
				),
				'default' 	=> 'yes'
          	),
   			
          	array(
          		'belong_to' => 'withdraw_cancelled',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Subject', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail list', 'ovabookpro' ),
				'name' 		=> 'withdraw_cancelled_subject',
				'default' 	=> esc_attr__( 'Withdrawal Cancelled', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Withdrawal Cancelled', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
          		'belong_to' => 'withdraw_cancelled',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'From name', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail detail', 'ovabookpro' ),
				'name' 		=> 'withdraw_cancelled_from_name',
				'default' 	=> esc_attr__( 'Withdrawal Cancelled', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Withdrawal Cancelled', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
          		'belong_to' => 'withdraw_cancelled',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Send from email', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Customers will know them to receive mail from which email address is', 'ovabookpro' ),
				'name' 		=> 'withdraw_cancelled_send_from_email',
				'default' 	=> get_option( 'admin_email', 'contact@ovatheme.com' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> 'contact@ovatheme.com',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
   				'belong_to' => 'withdraw_cancelled',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Recipient(s)', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'ovabookpro' ),
				'name' 		=> 'withdraw_cancelled_recipient',
				'default' 	=> '',
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> '',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'belong_to' => 'withdraw_cancelled',
				'type' 		=> 'text-editor',
				'label' 	=> esc_html__( 'Email Content', 'ovabookpro' ),
				'desc' 		=> __( "Name: [obp_name]</br>Amount: [obp_amount]</br>Withdraw Date: [obp_withdraw_date]</br>Payout Method: [obp_payout_method]</br>Payout Status: [obp_payout_status]", 'ovabookpro' ),
				'name' 		=> 'withdraw_cancelled_email_content',
				'default' 	=> __( "Name: [obp_name]\r\nAmount: [obp_amount]\r\nWithdraw Date: [obp_withdraw_date]\r\nPayout Method: [obp_payout_method]\r\nPayout Status: [obp_payout_status]", "ovabookpro" ),
          	),
   		));

   		return array(
   			'title' => esc_html__( 'Withdraw', 'ovabookpro' ),
   			array(
   				'fields' => $fields,
   				'accordion' => apply_filters( 'withdraw_accordion', array(
					'withdraw_request' => esc_html__( 'Withdrawal Request', 'ovabookpro' ),
					'withdraw_success' => esc_html__( 'Successful Withdrawal', 'ovabookpro' ),
					'withdraw_cancelled' => esc_html__( 'Withdrawal Cancelled', 'ovabookpro' ),
				) ),
   			)
   		);
   	}
   	
   	public function obp_mail_settings_change_order(){
   		$fields = apply_filters( 'obp_change_order_setting_fields', array(
			/* Admin */
          	array(
				'belong_to' => 'change_order_admin',
				'type' 		=> 'radio-inline',
				'label' 	=> esc_html__( 'Send mail to Admin:', 'ovabookpro' ),
				'name' 		=> 'change_admin_send_mail',
				'options' 	=> array(
					'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
					'no' 	=> esc_html__( 'No', 'ovabookpro' ),
				),
				'default' 	=> 'yes'
          	),
          	array(
				'belong_to' => 'change_order_admin',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Subject', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail list', 'ovabookpro' ),
				'name' 		=> 'change_admin_subject',
				'default' 	=> esc_attr__( 'Change Schedule', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Change Schedule', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'belong_to' => 'change_order_admin',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'From name', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail detail', 'ovabookpro' ),
				'name' 		=> 'change_admin_from_name',
				'default' 	=> esc_attr__( 'Change Schedule', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Change Schedule', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'belong_to' => 'change_order_admin',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Send from email', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Customers will know them to receive mail from which email address is', 'ovabookpro' ),
				'name' 		=> 'change_admin_send_from_email',
				'default' 	=> get_option( 'admin_email', 'contact@ovatheme.com' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> 'contact@ovatheme.com',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'belong_to' => 'change_order_admin',
				'type' 		=> 'text-editor',
				'label' 	=> esc_html__( 'Email Content', 'ovabookpro' ),
				'desc' 		=> '[booking_id] [booking_schedule]',
				'name' 		=> 'change_admin_email_content',
				'default' 	=> __( "The customer changed schedule of booking #[booking_id].\r\nThis is new schedule\r\n[booking_schedule]", 'ovabookpro' ),
          	),
          	/* Customer */
          	array(
				'belong_to' => 'change_order_customer',
				'type' 		=> 'radio-inline',
				'label' 	=> esc_html__( 'Send mail to Customer:', 'ovabookpro' ),
				'name' 		=> 'change_customer_send_mail',
				'options' 	=> array(
					'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
					'no' 	=> esc_html__( 'No', 'ovabookpro' ),
				),
				'default' 	=> 'yes'
          	),
          	array(
				'belong_to' => 'change_order_customer',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Subject', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail list', 'ovabookpro' ),
				'name' 		=> 'change_customer_subject',
				'default' 	=> esc_attr__( 'Change Schedule Successfully', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Change Schedule Successfully', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'belong_to' => 'change_order_customer',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'From name', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail detail', 'ovabookpro' ),
				'name' 		=> 'change_customer_from_name',
				'default' 	=> esc_attr__( 'Change Schedule Successfully', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Change Schedule Successfully', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'belong_to' => 'change_order_customer',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Send from email', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Customers will know them to receive mail from which email address is', 'ovabookpro' ),
				'name' 		=> 'change_customer_send_from_email',
				'default' 	=> get_option( 'admin_email', 'contact@ovatheme.com' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> 'contact@ovatheme.com',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
          		'belong_to' => 'change_order_customer',
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Recipient(s)', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'ovabookpro' ),
				'name' 		=> 'change_customer_recipient',
				'default' 	=> '',
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> '',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'belong_to' => 'change_order_customer',
				'type' 		=> 'text-editor',
				'label' 	=> esc_html__( 'Email Content', 'ovabookpro' ),
				'desc' 		=> '[booking_id] [booking_schedule]',
				'name' 		=> 'change_customer_email_content',
				'default' 	=> __( "You changed schedule booking #[booking_id] successfully.\r\nThis is new schedule\r\n[booking_schedule]", 'ovabookpro' ),
          	),
   		));
   		
   		return array(
   			'title' => esc_html__( 'Change Booking', 'ovabookpro' ),
   			array(
   				'fields' => $fields,
   				'accordion' => apply_filters( 'change_order_accordion', array(
					'change_order_admin' => esc_html__( 'Admin', 'ovabookpro' ),
					'change_order_customer' => esc_html__( 'Customer', 'ovabookpro' ),
				) ),
   			)
   		);
   		
   	}

   	public function obp_mail_settings_delete_account(){
   		$fields = apply_filters( 'obp_delete_account_setting_fields', array(
   			array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Subject', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail list', 'ovabookpro' ),
				'name' 		=> 'delete_account_subject',
				'default' 	=> esc_attr__( 'Delete Account', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Delete Account', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'From name', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'The subject displays in mail detail', 'ovabookpro' ),
				'name' 		=> 'delete_account_from_name',
				'default' 	=> esc_attr__( 'Delete Account', 'ovabookpro' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> esc_attr__( 'Delete Account', 'ovabookpro' ),
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'select',
				'label' 	=> esc_html__( 'Send email to', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Send email to admin, customer', 'ovabookpro' ),
				'name' 		=> 'delete_account_send_email_to',
				'options' 	=> apply_filters( 'delete_account_send_email_to_options', array(
					'customer' 	=> esc_html__( 'Customer', 'ovabookpro' ),
					'admin' 	=> esc_html__( 'Admin', 'ovabookpro' ),
				) ),
				'default' 	=> array( 'customer', 'admin' ),
				'atts' 		=> [
					'multiple' 			=> true,
					'class' 			=> 'obp-select2',
					'data-placeholder' 	=> esc_html__( 'Send email to', 'ovabookpro' ),
				]
   			),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Send from email', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Customers will know them to receive mail from which email address is', 'ovabookpro' ),
				'name' 		=> 'delete_account_send_from_email',
				'default' 	=> get_option( 'admin_email', 'contact@ovatheme.com' ),
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> 'contact@ovatheme.com',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'input',
				'label' 	=> esc_html__( 'Recipient(s)', 'ovabookpro' ),
				'desc' 		=> esc_html__( 'Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'ovabookpro' ),
				'name' 		=> 'delete_account_recipient',
				'default' 	=> '',
				'atts' 		=> [
					'type' 			=> 'text',
					'placeholder' 	=> '',
					'autocomplete' 	=> 'off'
				]
          	),
          	array(
				'type' 		=> 'text-editor',
				'label' 	=> esc_html__( 'Email Content', 'ovabookpro' ),
				'desc' 		=> __( 'User information [user_info]<br>Reason [reason]', 'ovabookpro' ),
				'name' 		=> 'delete_account_email_content',
				'default' 	=> __( 'User information [user_info]<br>Reason [reason]', 'ovabookpro' ),
          	),
   		) );

   		return array(
   			'title' => esc_html__( 'Delete Account', 'ovabookpro' ),
   			array(
   				'fields' => $fields,
   			)
   		);
   	}
}