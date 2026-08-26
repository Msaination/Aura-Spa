<?php
namespace BookPro;

use Pelago\Emogrifier\CssInliner;
use Pelago\Emogrifier\HtmlProcessor\CssToAttributeConverter;
use Pelago\Emogrifier\HtmlProcessor\HtmlPruner;
use BookPro\Order\OBP_Order_PDF;
use BookPro\Order\OBP_Order_Meta;
use BookPro\User\OBP_User;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Mail') ) {
	

	class OBP_Mail {

		public static function obp_change_mail_charset( $charset ){
			return 'UTF-8';
		}

		public static function obp_mail_content_type( $content_type ){
			$content_type = 'text/html';
			return apply_filters( 'obp_mail_content_type', $content_type );
		}

		public static function obp_delete_account_mail(){

			switch_to_locale( obp_get_current_language() );
			load_plugin_textdomain( 'ovabookpro', false, plugin_basename( dirname( OBP_PLUGIN_FILE ) ) . '/languages' );

			$to_mails 	= [];
			$user 		= wp_get_current_user();
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$reason 	= isset( $_POST['reason_delete'] ) ? sanitize_text_field( wp_unslash( $_POST['reason_delete'] ) ) : '';
			$subject 	= obp_get_delete_account_subject();
			$message 	= obp_get_delete_account_email_content();
			$admin_email 	= get_option( 'admin_email' );
			$send_mail_to 	= OBP()->settings->mail->get('delete_account_send_email_to', ['customer'] );
			$recipients_mail = OBP()->settings->mail->get('delete_account_recipient','');
			

			if ( ! empty( $recipients_mail ) ) {
				$recipients_mail = explode( ",", $recipients_mail );
				$recipients_mail = array_map( 'trim', $recipients_mail );
			} else {
				$recipients_mail = array();
			}

			if ( $user ) {
				// translators: 1: id, 2: username, 3: email.
				$user_info = sprintf( __( 'ID: %1$s<br>Username: %2$s<br>Email: %3$s' , 'ovabookpro' ), $user->ID, $user->user_login, $user->user_email );
				$message = str_replace('[user_info]', $user_info, $message);
			}

			if ( in_array('customer', $send_mail_to ) ) {
				$to_mails[] = $user->user_email;
			}

			if ( in_array('admin', $send_mail_to ) ) {
				$to_mails[] = $admin_email;
			}

			$headers = array();
			
			if ( count( $recipients_mail ) > 0 ) {
				foreach ( $recipients_mail as $email ) {
					$headers[] = 'Bcc: '.$email."\r\n";
				}
			}

			$message = str_replace( '[reason]', $reason, $message );

			add_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			add_filter( 'wp_mail_from', array( __CLASS__, 'obp_delete_account_mail_from' ) );
			add_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_delete_account_mail_from_name' ) );
			add_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			$wp_mail = wp_mail( $to_mails, $subject, $message , $headers );

			restore_previous_locale();

			remove_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			remove_filter( 'wp_mail_from', array( __CLASS__, 'obp_delete_account_mail_from' ) );
			remove_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_delete_account_mail_from_name' ) );
			remove_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			return $wp_mail;
		}

		public static function obp_delete_account_mail_from( $from_email ){
			$from_email = OBP()->settings->mail->get('delete_account_send_from_email', get_option('admin_email') );
			return apply_filters( 'obp_delete_account_mail_from', $from_email );
		}

		public static function obp_delete_account_mail_from_name( $from_name ){
			$from_name = OBP()->settings->mail->get('delete_account_from_name', __( 'Delete Account', 'ovabookpro' ) );
			return apply_filters( 'obp_delete_account_mail_from_name', $from_name );
		}

		public static function obp_new_order_mail( $order ){
			// Get emails
			$send_mail_to 		= OBP()->settings->mail->get('new_order_send_email_to', array( 'customer' ) );
			$recipients_mail 	= OBP()->settings->mail->get('new_order_recipient','');
			$admin_email 		= get_option( 'admin_email' );
			$to_mails 			= array();

			switch_to_locale( obp_get_current_language() );
			load_plugin_textdomain( 'ovabookpro', false, plugin_basename( dirname( OBP_PLUGIN_FILE ) ) . '/languages' );

			if ( ! empty( $recipients_mail ) ) {
				$recipients_mail = explode( ",", $recipients_mail );
				$recipients_mail = array_map( 'trim', $recipients_mail );
			} else {
				$recipients_mail = array();
			}

			if ( count( $send_mail_to ) > 0 ) {

				if ( in_array( 'customer', $send_mail_to ) ) {
					$to_mails[] = $order->get_customer_email();
				}

				if ( in_array( 'admin', $send_mail_to ) ) {
					$recipients_mail[] = $admin_email;
				}

				if ( in_array( 'staff', $send_mail_to ) ) {
					$staff_ids 			= OBP_Order_Meta::get_staff_ids_by_order_id( $order->get_id() );
					$staff_emails 		= OBP_User::convert_staff_ids_to_staff_emails( $staff_ids );
					$recipients_mail 	= array_merge_recursive( $recipients_mail, $staff_emails );
				}
			}

			$to_mails = apply_filters( 'obp_new_order_to_mails', $to_mails, $send_mail_to, $order );

			$attachments = array();

			$send_invoice 		= OBP()->settings->mail->get('new_order_send_mail_invoice', '' );
			$additional_file_id = OBP()->settings->mail->get('new_order_additional_attach','');

			if ( $additional_file_id ) {
				$file_path = get_attached_file( $additional_file_id );
				if ( file_exists( $file_path ) ) {
					$attachments[] = $file_path;
				}
			}

			// Add Invoice
			$invoice_path = '';
			if ( $send_invoice ) {
				$order_pdf = new OBP_Order_PDF();
				$invoice_path = $order_pdf->make_pdf_invoice( $order->get_id() );
				if ( file_exists( $invoice_path ) ) {
					$attachments[] = $invoice_path;
				}
			}
			
			$attachments = apply_filters( 'obp_new_order_mail_attachments' , $attachments );

			$headers = array();

			if ( count( $recipients_mail ) > 0 ) {
				foreach ( $recipients_mail as $email ) {
					$headers[] = 'Bcc: '.$email."\r\n";
				}
			}

			
			$subject = obp_get_new_order_subject();
			$subject .= ' #'.$order->get_id();

			$args = apply_filters( 'new_order_mail_order_info', array( 'order' => $order ) );

			ob_start();
			obp_email_order_info( $args );
			$order_info = ob_get_clean();
			
			$message = self::obp_new_order_mail_style( $order_info );

			add_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			add_filter( 'wp_mail_from', array( __CLASS__, 'obp_new_order_mail_from' ) );
			add_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_new_order_mail_from_name' ) );
			add_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			$wp_mail = wp_mail( $to_mails, $subject, $message, $headers, $attachments );

			restore_previous_locale();

			remove_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			remove_filter( 'wp_mail_from', array( __CLASS__, 'obp_new_order_mail_from' ) );
			remove_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_new_order_mail_from_name' ) );
			remove_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			// Delete Files
			if ( file_exists( $invoice_path ) ) {
				wp_delete_file( $invoice_path );
			}

			return $wp_mail;
		}

		public static function obp_new_order_mail_from( $from_email ){
			$from_email = OBP()->settings->mail->get('new_order_send_from_email', get_option('admin_email') );
			return apply_filters( 'obp_new_order_mail_from', $from_email );
		}

		public static function obp_new_order_mail_from_name( $from_name ){
			$from_name = OBP()->settings->mail->get('new_order_from_name', esc_html__( 'Booking Success', 'ovabookpro' ) );
			return apply_filters( 'obp_new_order_mail_from_name', $from_name );
		}

		public static function obp_new_order_mail_style( $content ){
			ob_start();
			obp_get_template("emails/email-style.php");
			$css = ob_get_clean();
			$custom_css = OBP()->settings->mail->get('new_order_email_style','');
			$css .= $custom_css;

			$css_inliner_class = CssInliner::class;

			if ( class_exists( 'DOMDocument' ) && class_exists( $css_inliner_class ) ) {
				try {
					$css_inliner = CssInliner::fromHtml( $content )->inlineCss( $css );

					$dom_document = $css_inliner->getDomDocument();

					HtmlPruner::fromDomDocument( $dom_document )->removeElementsWithDisplayNone();
					$content = CssToAttributeConverter::fromDomDocument( $dom_document )
						->convertCssToVisualAttributes()
						->render();
				} catch ( Exception $e ) {
					echo esc_html( $e->getMessage() );
					wp_die();
				}
			} else {
				$content = '<style type="text/css">' . $css . '</style>' . $content;
			}

			return $content;
		}

		public static function obp_withdraw_request_mail( $payout_id ){
			$payout 	= obp_get_payout( $payout_id );
			$user_id 	= $payout->get_user_id();
			$user 		= obp_get_user( $user_id );

			$headers 	= array();
			$to 		= $user->get_user_email();

			$recipients_mail 	= OBP()->settings->mail->get('withdraw_request_recipient','');
			$admin_email 		= get_option( 'admin_email' );

			if ( ! empty( $recipients_mail ) ) {
				$recipients_mail = explode( ",", $recipients_mail );
				$recipients_mail = array_map( 'trim', $recipients_mail );
			} else {
				$recipients_mail = array();
			}

			$recipients_mail[] = $admin_email;

			if ( count( $recipients_mail ) > 0 ) {
				foreach ( $recipients_mail as $email ) {
					$headers[] = 'Bcc: '.$email."\r\n";
				}
			}

			switch_to_locale( obp_get_current_language() );
			load_plugin_textdomain( 'ovabookpro', false, plugin_basename( dirname( OBP_PLUGIN_FILE ) ) . '/languages' );

			$subject = obp_get_withdraw_request_subject();
			$subject .= ' #'.$payout->get_id();

			$fullname 		= $user->get_fullname();
			$amount 		= obp_get_price_html( $payout->get_amount() );
			$withdraw_date 	= $payout->get_withdraw_date();
			$payout_method 	= $payout->get_payout_method();
			$payout_status 	= $payout->get_payout_status_translate();

			$message = obp_get_withdraw_request_email_content();

			$message = str_replace( '[obp_name]', $fullname, $message );
			$message = str_replace( '[obp_amount]', $amount, $message );
			$message = str_replace( '[obp_withdraw_date]', $withdraw_date, $message );
			$message = str_replace( '[obp_payout_method]', $payout_method, $message );
			$message = str_replace( '[obp_payout_status]', $payout_status, $message );

			add_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			add_filter( 'wp_mail_from', array( __CLASS__, 'obp_withdraw_request_mail_from' ) );
			add_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_withdraw_request_mail_from_name' ) );
			add_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			$wp_mail = wp_mail( $to, $subject, $message, $headers );

			restore_previous_locale();

			remove_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			remove_filter( 'wp_mail_from', array( __CLASS__, 'obp_withdraw_request_mail_from' ) );
			remove_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_withdraw_request_mail_from_name' ) );
			remove_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			return $wp_mail;
		}

		public static function obp_withdraw_request_mail_from( $from_email ){
			$from_email = OBP()->settings->mail->get('withdraw_request_send_from_email', get_option('admin_email') );
			return apply_filters( 'obp_withdraw_request_mail_from', $from_email );
		}

		public static function obp_withdraw_request_mail_from_name( $from_name ){
			$from_name = OBP()->settings->mail->get('withdraw_request_from_name', esc_html__( 'Withdrawal Request', 'ovabookpro' ) );
			return apply_filters( 'obp_withdraw_request_mail_from_name', $from_name );
		}


		public static function obp_withdraw_completed_mail( $payout_id ){
			$payout 	= obp_get_payout( $payout_id );
			$user_id 	= $payout->get_user_id();
			$user 		= obp_get_user( $user_id );

			$headers 	= array();
			$to 		= $user->get_user_email();

			$recipients_mail 	= OBP()->settings->mail->get('withdraw_success_recipient','');
			$admin_email 		= get_option( 'admin_email' );

			if ( ! empty( $recipients_mail ) ) {
				$recipients_mail = explode( ",", $recipients_mail );
				$recipients_mail = array_map( 'trim', $recipients_mail );
			} else {
				$recipients_mail = array();
			}

			$recipients_mail[] = $admin_email;

			if ( count( $recipients_mail ) > 0 ) {
				foreach ( $recipients_mail as $email ) {
					$headers[] = 'Bcc: '.$email."\r\n";
				}
			}

			switch_to_locale( obp_get_current_language() );
			load_plugin_textdomain( 'ovabookpro', false, plugin_basename( dirname( OBP_PLUGIN_FILE ) ) . '/languages' );

			$subject = obp_get_withdraw_success_subject();
			$subject .= ' #'.$payout->get_id();

			$fullname 		= $user->get_fullname();
			$amount 		= obp_get_price_html( $payout->get_amount() );
			$withdraw_date 	= $payout->get_withdraw_date();
			$payout_method 	= $payout->get_payout_method();
			$payout_status 	= $payout->get_payout_status_translate();

			$message = obp_get_withdraw_success_email_content();

			$message = str_replace( '[obp_name]', $fullname, $message );
			$message = str_replace( '[obp_amount]', $amount, $message );
			$message = str_replace( '[obp_withdraw_date]', $withdraw_date, $message );
			$message = str_replace( '[obp_payout_method]', $payout_method, $message );
			$message = str_replace( '[obp_payout_status]', $payout_status, $message );

			add_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			add_filter( 'wp_mail_from', array( __CLASS__, 'obp_withdraw_success_mail_from' ) );
			add_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_withdraw_success_mail_from_name' ) );
			add_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			$wp_mail = wp_mail( $to, $subject, $message, $headers );

			restore_previous_locale();

			remove_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			remove_filter( 'wp_mail_from', array( __CLASS__, 'obp_withdraw_success_mail_from' ) );
			remove_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_withdraw_success_mail_from_name' ) );
			remove_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			return $wp_mail;
		}

		public static function obp_withdraw_success_mail_from( $from_email ){
			$from_email = OBP()->settings->mail->get('withdraw_success_send_from_email', get_option('admin_email') );
			return apply_filters( 'obp_withdraw_success_mail_from', $from_email );
		}

		public static function obp_withdraw_success_mail_from_name( $from_name ){
			$from_name = OBP()->settings->mail->get('withdraw_success_from_name', esc_html__( 'Successful Withdrawal', 'ovabookpro' ) );
			return apply_filters( 'obp_withdraw_success_mail_from_name', $from_name );
		}

		public static function obp_withdraw_cancelled_mail( $payout_id ){
			$payout 	= obp_get_payout( $payout_id );
			$user_id 	= $payout->get_user_id();
			$user 		= obp_get_user( $user_id );

			$headers 	= array();
			$to 		= $user->get_user_email();

			$recipients_mail 	= OBP()->settings->mail->get('withdraw_cancelled_recipient','');
			$admin_email 		= get_option( 'admin_email' );

			if ( ! empty( $recipients_mail ) ) {
				$recipients_mail = explode( ",", $recipients_mail );
				$recipients_mail = array_map( 'trim', $recipients_mail );
			} else {
				$recipients_mail = array();
			}

			$recipients_mail[] = $admin_email;

			if ( count( $recipients_mail ) > 0 ) {
				foreach ( $recipients_mail as $email ) {
					$headers[] = 'Bcc: '.$email."\r\n";
				}
			}

			switch_to_locale( obp_get_current_language() );
			load_plugin_textdomain( 'ovabookpro', false, plugin_basename( dirname( OBP_PLUGIN_FILE ) ) . '/languages' );

			$subject = obp_get_withdraw_cancelled_subject();
			$subject .= ' #'.$payout->get_id();

			$fullname 		= $user->get_fullname();
			$amount 		= obp_get_price_html( $payout->get_amount() );
			$withdraw_date 	= $payout->get_withdraw_date();
			$payout_method 	= $payout->get_payout_method();
			$payout_status 	= $payout->get_payout_status_translate();

			$message = obp_get_withdraw_cancelled_email_content();

			$message = str_replace( '[obp_name]', $fullname, $message );
			$message = str_replace( '[obp_amount]', $amount, $message );
			$message = str_replace( '[obp_withdraw_date]', $withdraw_date, $message );
			$message = str_replace( '[obp_payout_method]', $payout_method, $message );
			$message = str_replace( '[obp_payout_status]', $payout_status, $message );

			add_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			add_filter( 'wp_mail_from', array( __CLASS__, 'obp_withdraw_cancelled_mail_from' ) );
			add_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_withdraw_cancelled_mail_from_name' ) );
			add_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			$wp_mail = wp_mail( $to, $subject, $message, $headers );

			restore_previous_locale();

			remove_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			remove_filter( 'wp_mail_from', array( __CLASS__, 'obp_withdraw_cancelled_mail_from' ) );
			remove_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_withdraw_cancelled_mail_from_name' ) );
			remove_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			return $wp_mail;
		}

		public static function obp_withdraw_cancelled_mail_from( $from_email ){
			$from_email = OBP()->settings->mail->get('withdraw_cancelled_send_from_email', get_option('admin_email') );
			return apply_filters( 'obp_withdraw_cancelled_mail_from', $from_email );
		}

		public static function obp_withdraw_cancelled_mail_from_name( $from_name ){
			$from_name = OBP()->settings->mail->get('withdraw_cancelled_from_name', esc_html__( 'Withdrawal Cancelled', 'ovabookpro' ) );
			return apply_filters( 'obp_withdraw_cancelled_mail_from_name', $from_name );
		}

		public static function obp_change_order_admin_mail( $order ){
			// Get emails
			$admin_email 		= get_option( 'admin_email' );
			$to_mails 			= array( $admin_email );

			$attachments 	= apply_filters( 'obp_change_order_admin_mail_attachments' , array() );
			$headers 		= apply_filters( 'obp_change_order_admin_mail_headers', array() );

			switch_to_locale( obp_get_current_language() );
			load_plugin_textdomain( 'ovabookpro', false, plugin_basename( dirname( OBP_PLUGIN_FILE ) ) . '/languages' );
			
			$subject = obp_get_change_admin_subject();
			$subject .= ' #'.$order->get_id();

			$args = apply_filters( 'obp_change_order_schedule_mail', array( 'order' => $order ) );
			ob_start();
			obp_email_order_schedule( $args );
			$order_schedule = ob_get_clean();

			$message = obp_get_change_admin_email_content();

			$message = str_replace( '[booking_id]', $order->get_id(), $message );
			$message = str_replace('[booking_schedule]', $order_schedule, $message );

			add_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			add_filter( 'wp_mail_from', array( __CLASS__, 'obp_change_order_admin_mail_from' ) );
			add_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_change_order_admin_mail_from_name' ) );
			add_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			$wp_mail = wp_mail( $to_mails, $subject, $message, $headers, $attachments );

			restore_previous_locale();

			remove_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			remove_filter( 'wp_mail_from', array( __CLASS__, 'obp_change_order_admin_mail_from' ) );
			remove_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_change_order_admin_mail_from_name' ) );
			remove_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			return $wp_mail;
		}

		public static function obp_change_order_admin_mail_from( $from_email ){
			$from_email = OBP()->settings->mail->get('change_admin_send_from_email', get_option('admin_email') );
			return apply_filters( 'obp_change_order_admin_mail_from', $from_email );
		}

		public static function obp_change_order_admin_mail_from_name( $from_name ){
			$from_name = OBP()->settings->mail->get('change_admin_from_name', esc_html__( 'Change Schedule', 'ovabookpro' ) );
			return apply_filters( 'obp_change_order_admin_mail_from_name', $from_name );
		}

		public static function obp_change_order_customer_mail( $order ){
			// Get emails
			$email 				= $order->get_customer_email();
			$to_mails 			= array( $email );

			$attachments 	= apply_filters( 'obp_change_order_customer_mail_attachments' , array() );
			$headers 		= apply_filters( 'obp_change_order_customer_mail_headers', array() );
				
			$recipients_mail = OBP()->settings->mail->get('change_customer_recipient','');

			if ( ! empty( $recipients_mail ) ) {
				$recipients_mail = explode( ",", $recipients_mail );
				$recipients_mail = array_map( 'trim', $recipients_mail );
			} else {
				$recipients_mail = array();
			}

			if ( count( $recipients_mail ) > 0 ) {
				foreach ( $recipients_mail as $email ) {
					$headers[] = 'Bcc: '.$email."\r\n";
				}
			}

			switch_to_locale( obp_get_current_language() );
			load_plugin_textdomain( 'ovabookpro', false, plugin_basename( dirname( OBP_PLUGIN_FILE ) ) . '/languages' );

			$subject = obp_get_change_customer_subject();
			$subject .= ' #'.$order->get_id();

			$args = apply_filters( 'obp_change_order_schedule_mail', array( 'order' => $order ) );
			ob_start();
			obp_email_order_schedule( $args );
			$order_schedule = ob_get_clean();

			$message = obp_get_change_customer_email_content();

			$message = str_replace( '[booking_id]', $order->get_id(), $message );
			$message = str_replace('[booking_schedule]', $order_schedule, $message );

			add_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			add_filter( 'wp_mail_from', array( __CLASS__, 'obp_change_order_customer_mail_from' ) );
			add_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_change_order_customer_mail_from_name' ) );
			add_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			$wp_mail = wp_mail( $to_mails, $subject, $message, $headers, $attachments );

			restore_previous_locale();

			remove_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			remove_filter( 'wp_mail_from', array( __CLASS__, 'obp_change_order_customer_mail_from' ) );
			remove_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_change_order_customer_mail_from_name' ) );
			remove_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			return $wp_mail;
		}

		public static function obp_change_order_customer_mail_from( $from_email ){
			$from_email = OBP()->settings->mail->get('change_customer_send_from_email', get_option('admin_email') );
			return apply_filters( 'obp_change_order_customer_mail_from', $from_email );
		}

		public static function obp_change_order_customer_mail_from_name( $from_name ){
			$from_name = OBP()->settings->mail->get('change_customer_from_name', esc_html__( 'Change Schedule Successfully', 'ovabookpro' ) );
			return apply_filters( 'obp_change_order_customer_mail_from_name', $from_name );
		}

		public static function obp_cancel_order_admin_mail( $order ){
			// Get emails
			$admin_email 		= get_option( 'admin_email' );
			$to_mails 			= array( $admin_email );

			$attachments 	= apply_filters( 'obp_cancel_order_admin_mail_attachments' , array() );
			$headers 		= apply_filters( 'obp_cancel_order_admin_mail_headers', array() );

			switch_to_locale( obp_get_current_language() );
			load_plugin_textdomain( 'ovabookpro', false, plugin_basename( dirname( OBP_PLUGIN_FILE ) ) . '/languages' );
			
			$subject = obp_get_cancel_admin_subject();
			$subject .= ' #'.$order->get_id();

			$message = obp_get_cancel_admin_email_content();

			$message = str_replace( '[booking_id]', $order->get_id(), $message );

			add_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			add_filter( 'wp_mail_from', array( __CLASS__, 'obp_cancel_order_admin_mail_from' ) );
			add_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_cancel_order_admin_mail_from_name' ) );
			add_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			$wp_mail = wp_mail( $to_mails, $subject, $message, $headers, $attachments );

			restore_previous_locale();

			remove_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			remove_filter( 'wp_mail_from', array( __CLASS__, 'obp_cancel_order_admin_mail_from' ) );
			remove_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_cancel_order_admin_mail_from_name' ) );
			remove_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			return $wp_mail;
		}

		public static function obp_cancel_order_admin_mail_from( $from_email ){
			$from_email = OBP()->settings->mail->get('cancel_admin_send_from_email', get_option('admin_email') );
			return apply_filters( 'obp_cancel_order_admin_mail_from', $from_email );

		}

		public static function obp_cancel_order_admin_mail_from_name( $from_name ){
			$from_name = OBP()->settings->mail->get('cancel_admin_from_name', esc_html__( 'Cancel Booking', 'ovabookpro' ) );
			return apply_filters( 'obp_cancel_order_admin_mail_from_name', $from_name );
		}

		public static function obp_cancel_order_customer_mail( $order ){
			// Get emails
			$email 				= $order->get_customer_email();
			$to_mails 			= array( $email );

			$recipients_mail 	= OBP()->settings->mail->get('cancel_customer_recipient','');
			$attachments 		= apply_filters( 'obp_cancel_order_customer_mail_attachments' , array() );
			$headers 			= apply_filters( 'obp_cancel_order_customer_mail_headers', array() );

			switch_to_locale( obp_get_current_language() );
			load_plugin_textdomain( 'ovabookpro', false, plugin_basename( dirname( OBP_PLUGIN_FILE ) ) . '/languages' );
			
			$subject = obp_get_cancel_customer_subject();
			$subject .= ' #'.$order->get_id();

			$message = obp_get_cancel_customer_email_content();

			$message = str_replace( '[booking_id]', $order->get_id(), $message );

			if ( ! empty( $recipients_mail ) ) {
				$recipients_mail = explode( ",", $recipients_mail );
				$recipients_mail = array_map( 'trim', $recipients_mail );
			} else {
				$recipients_mail = array();
			}

			if ( count( $recipients_mail ) > 0 ) {
				foreach ( $recipients_mail as $email ) {
					$headers[] = 'Bcc: '.$email."\r\n";
				}
			}

			add_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			add_filter( 'wp_mail_from', array( __CLASS__, 'obp_cancel_order_customer_mail_from' ) );
			add_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_cancel_order_customer_mail_from_name' ) );
			add_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			$wp_mail = wp_mail( $to_mails, $subject, $message, $headers, $attachments );

			restore_previous_locale();

			remove_filter( 'wp_mail_charset', array( __CLASS__, 'obp_change_mail_charset' ) );
			remove_filter( 'wp_mail_from', array( __CLASS__, 'obp_cancel_order_customer_mail_from' ) );
			remove_filter( 'wp_mail_from_name', array( __CLASS__, 'obp_cancel_order_customer_mail_from_name' ) );
			remove_filter( 'wp_mail_content_type', array( __CLASS__, 'obp_mail_content_type' ) );

			return $wp_mail;
		}

		public static function obp_cancel_order_customer_mail_from( $from_email ){
			$from_email = OBP()->settings->mail->get('cancel_customer_send_from_email', get_option('admin_email') );
			return apply_filters( 'obp_cancel_order_customer_mail_from', $from_email );
		}

		public static function obp_cancel_order_customer_mail_from_name( $from_name ){
			$from_name = OBP()->settings->mail->get('cancel_customer_from_name', esc_html__( 'Cancel Booking', 'ovabookpro' ) );
			return apply_filters( 'obp_cancel_order_customer_mail_from_name', $from_name );
		}
		
	}
}