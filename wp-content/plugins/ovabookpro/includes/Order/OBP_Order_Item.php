<?php

namespace BookPro\Order;

use BookPro\Order\OBP_Order_PDF;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists("OBP_Order_Item") ) {
	
	class OBP_Order_Item {

		protected $id = null;

		public function __construct( $id ){
			$this->id = $id;
		}

		public function get_id(){
			$id = $this->id;
			return $id;
		}

		public function get_customer_id(){
			$id = $this->get_id();
			$customer_id = get_post_meta( $id, OBP_METABOX.'customer_id', true );
			return $customer_id;
		}

		public function get_customer_email(){
			$id = $this->get_id();
			$customer_email = get_post_meta( $id, OBP_METABOX.'customer_email', true );
			return $customer_email;
		}

		public function get_customer_name(){
			$id = $this->get_id();
			$customer_name = get_post_meta( $id, OBP_METABOX.'customer_name', true );
			return $customer_name;
		}

		public function get_inc_tax(){
			$id = $this->get_id();
			$inc_tax = OBP()->get_post_meta( $id, 'inc_tax', 'no' );
			return $inc_tax;
		}

		public function get_customer_phone(){
			$id = $this->get_id();
			$customer_phone = get_post_meta( $id, OBP_METABOX.'customer_phone', true );
			return $customer_phone;
		}

		public function get_has_varies(){
			$id = $this->get_id();
			$has_varies = OBP()->get_post_meta( $id, 'has_varies', 'no' );
			return $has_varies;
		}

		public function has_varies(){
			$has_varies = $this->get_has_varies();
			return $has_varies == 'yes' ? true : false;
		}

		public function get_customer_note(){
			$id = $this->get_id();
			$customer_note = get_post_meta( $id, OBP_METABOX.'customer_note', true );
			return $customer_note;
		}

		public function get_subtotal(){
			$id = $this->get_id();
			$subtotal = get_post_meta( $id, OBP_METABOX.'subtotal', true );
			return $subtotal;
		}

		public function get_order_status(){
			$id = $this->get_id();
			$status = get_post_meta( $id, OBP_METABOX.'order_status', true );
			return $status;
		}

		public function get_order_status_translate(){
			$order_status 		= $this->get_order_status();
			$translate_status 	= array(
				'obp_pending' 		=> esc_html__( 'Pending', 'ovabookpro' ),
				'obp_expired' 		=> esc_html__( 'Expired', 'ovabookpro' ),
				'obp_processing' 	=> esc_html__( 'Processing', 'ovabookpro' ),
				'obp_completed' 	=> esc_html__( 'Completed', 'ovabookpro' ),
				'obp_cancelled' 	=> esc_html__( 'Cancelled', 'ovabookpro' ),
				'obp_refunded' 		=> esc_html__( 'Refunded', 'ovabookpro' ),
			);
			return $translate_status[$order_status];
		}

		public function get_vendor_id(){
			$id = $this->get_id();
			$vendor_id = get_post_meta( $id, OBP_METABOX.'vendor_id', true );
			return $vendor_id;
		}

		public function get_business_id(){
			$id = $this->get_id();
			$business_id = get_post_meta( $id, OBP_METABOX.'business_id', true );
			return $business_id;
		}

		public function get_business_name(){
			$business_id = $this->get_business_id();
			$business = obp_get_business( $business_id );
			$name = $business->get_name();
			return $name;
		}

		public function get_key(){
			$key = OBP()->get_post_meta( $this->get_id(), 'key' );
			return $key;
		}

		public function set_key(){
			$key = $this->get_id().'_'.obp_generate_order_key( 12, false );
			update_post_meta( $this->get_id(), OBP_METABOX.'key', $key );
		}

		public function get_payment_method(){
			$id = $this->get_id();
			$payment_method = get_post_meta( $id, OBP_METABOX.'payment_method', true );
			return $payment_method;
		}

		public function get_woo_order_id(){
			$id = $this->get_id();
			$woo_order_id = get_post_meta( $id, OBP_METABOX.'woo_order_id', true );
			return $woo_order_id;
		}

		public function get_comment_id(){
			$id = $this->get_id();
			
			$args = array(
			    'number'  		=> '1',
			    'fields' 		=> 'ids',
			    'meta_key' 		=> OBP_METABOX.'order_id',
			    'meta_value' 	=> $id,
			);

			$comment_id = ( isset(get_comments($args)[0]) && !empty(get_comments($args)) ) ? get_comments($args)[0] : '';

			return $comment_id;
		}

		public function get_payment_gateway(){
			$payment_gateway = OBP()->get_post_meta( $this->get_id(), 'payment_gateway' );
			return $payment_gateway;
		}

		public function get_total(){
			$id = $this->get_id();
			$total = get_post_meta( $id, OBP_METABOX.'total', true );
			return $total;
		}

		public function get_system_fee(){
			$id = $this->get_id();
			$system_fee = get_post_meta( $id, OBP_METABOX.'system_fee', true );
			return $system_fee;
		}

		public function get_vendor_fee(){
			$id = $this->get_id();
			$vendor_fee = get_post_meta( $id, OBP_METABOX.'vendor_fee', true );
			return $vendor_fee;
		}

		public function get_tax_amount(){
			$id = $this->get_id();
			$tax_amount = get_post_meta( $id, OBP_METABOX.'tax_amount', true );
			return $tax_amount;
		}

		public function get_coupon_id(){
			$id = $this->get_id();
			$coupon_id = get_post_meta( $id, OBP_METABOX.'coupon_id', true );
			return $coupon_id;
		}

		public function get_coupon_code(){
			$id = $this->get_id();
			$coupon_code = get_post_meta( $id, OBP_METABOX.'coupon_code', true );
			return $coupon_code;
		}

		public function get_coupon_amount(){
			$id = $this->get_id();
			$coupon_amount = get_post_meta( $id, OBP_METABOX.'coupon_amount', true );
			return $coupon_amount;
		}

		public function get_discount(){
			$id = $this->get_id();
			$discount = get_post_meta( $id, OBP_METABOX.'discount', true );
			return $discount;
		}

		public function get_date_created(){

			$date_format = OBP()->settings->general->get('date_format','Y-m-d');
			$time_format = OBP()->settings->general->get('time_format', 'H:i');

			$date_created_timestamp = $this->get_date_created_timestamp();
			$date_created = '';
			if ( $date_created_timestamp ) {
				$date_created = date_i18n( $date_format.' '.$time_format, $date_created_timestamp );
			}
			
			return $date_created;
		}

		public function get_date_created_timestamp(){
			$id = $this->get_id();
			$date_created_timestamp = get_post_meta( $id, OBP_METABOX.'date_created', true );
			return $date_created_timestamp;
		}

		public function get_service_start_date_earliest(){ 
			$id = $this->get_id();
			$service_start_date_earliest = get_post_meta( $id, OBP_METABOX.'start_date', true );
			return $service_start_date_earliest;
		}

		public function get_vendor_total(){
			$id = $this->get_id();
			$vendor_total = get_post_meta( $id, OBP_METABOX.'vendor_total', true );
			return $vendor_total;
		}

		public function get_commission(){
			$id = $this->get_id();
			$commission = get_post_meta( $id, OBP_METABOX.'commission', true );
			return apply_filters( 'obp_order_commission', $commission, $this );
		}

		public function get_balance_status(){
			$id = $this->get_id();
			$balance_status = get_post_meta( $id, OBP_METABOX.'balance_status', true );
			return $balance_status;
		}

		public function get_balance_status_date(){
			$id = $this->get_id();
			$balance_status_date = get_post_meta( $id, OBP_METABOX.'balance_status_date', true );
			return $balance_status_date;
		}

		public function get_woo_order_permalink(){
			$woo_order_id = $this->get_woo_order_id();
			$order = wc_get_order( $woo_order_id );
			$permalink = $order->get_edit_order_url();
			return $permalink;
		}

		public function get_vendor_name(){
			$vendor_id = $this->get_vendor_id();
			$user = obp_get_user( $vendor_id );
			$vendor_name = $user->get_nickname();
			return $vendor_name;
		}

		public function business_permalink(){
			$business_id = $this->get_business_id();
			$business = obp_get_business( $business_id );
			$permalink = $business->get_permalink();
			return $permalink;
		}

		public function set_balance_status( $status = "obp_completed" ){
			$id = $this->get_id();
			update_post_meta( $id, OBP_METABOX.'balance_status', $status );
		}

		public function set_start_date( $start_date ){
			$id = $this->get_id();
			update_post_meta( $id, OBP_METABOX.'start_date', $start_date );
		}

		public function get_download_pdf_url(){
			$id = $this->get_id();
			$pdf = new OBP_Order_PDF();

			$pdf_url = $pdf->make_pdf_invoice( $id );

			$arr_upload      = wp_upload_dir();
			$base_url_upload = $arr_upload['baseurl'];

			if ( ! empty( $pdf_url ) ) {
				$position = strrpos($pdf_url, '/');
				$name     = substr($pdf_url, $position);

				$download_pdf_url = $base_url_upload . '/invoices'. $name;
			} else {
				$download_pdf_url = '#';
			}

			return $download_pdf_url;
		}

		public function get_number_change_order(){
			$id = $this->get_id();
			$number_change_order = absint( get_post_meta( $id, OBP_METABOX.'number_change_order', true ) );
			return $number_change_order;
		}

		public function set_number_change_order( $number ){
			$id = $this->get_id();
			update_post_meta( $id, OBP_METABOX.'number_change_order', $number );
		}

		public function get_allow_change(){
			$id = $this->get_id();
			$allow_change = get_post_meta( $id, OBP_METABOX.'allow_change', true );
			return $allow_change;
		}

		public function set_payout_status( $status = "obp_pending" ){
			$id = $this->get_id();
			update_post_meta( $id, OBP_METABOX.'payout_status', $status );
		}

		public function set_order_status( $status = "obp_completed" ){
			$id = $this->get_id();
			update_post_meta( $id, OBP_METABOX.'order_status', $status );
		}

		public function set_rated( $flag = true ){
			$id = $this->get_id();
			if ( $flag ) {
				update_post_meta( $id, OBP_METABOX.'rated', 'yes' );
			} else {
				update_post_meta( $id, OBP_METABOX.'rated', 'no' );
			}
		}

		public function is_rated(){
			$id = $this->get_id();
			$rated = get_post_meta( $id, OBP_METABOX.'rated', true );
			if ( $rated == 'yes' ) {
				return true;
			}
			return false;
		}

	}
}