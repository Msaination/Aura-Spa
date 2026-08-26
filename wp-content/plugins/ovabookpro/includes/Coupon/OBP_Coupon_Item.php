<?php
namespace BookPro\Coupon;
defined( 'ABSPATH' ) || exit;
use BookPro\Service\OBP_Service;

class OBP_Coupon_Item {

	public $coupon = null;

	public function __construct( $id = null ){
		if ( $id ) {
			$this->coupon = get_post( $id );
		}
	}

	public function get_id(){

		if ( ! is_null( $this->coupon ) ) {
			return $this->coupon->ID;
		}

		return '';
	}

	public function get_coupon_code(){

		$id = $this->get_id();
		$value = OBP()->get_post_meta( $id, 'coupon_code' );
		return $value;
	}

	public function get_visibility(){
		$id = $this->get_id();
		$value = OBP()->get_post_meta( $id, 'visibility', 'public' );
		return $value;
	}

	public function get_description(){
		if ( $this->coupon ) {
			return $this->coupon->post_content;
		}
		return '';
	}

	public function get_coupon_amount(){
		$id = $this->get_id();
		$value = OBP()->get_post_meta( $id, 'coupon_amount' );
		return $value;
	}

	public function get_discount_type(){
		$id = $this->get_id();
		$value = OBP()->get_post_meta( $id, 'discount_type', 'percent' );
		return $value;
	}

	public function get_coupon_qty(){
		$id = $this->get_id();
		$value = OBP()->get_post_meta( $id, 'coupon_qty' );
		return $value;
	}

	public function get_coupon_amount_formatted(){
		$amount = $this->get_coupon_amount();
		$discount_type = $this->get_discount_type();
		$result = '';
		if ( $amount ) {
			switch ( $discount_type ) {
				case 'percent':
					$result = $amount.'%';
					break;
				case 'fixed':
					$result = obp_get_price_html( $amount );
					break;
				default:
					break;
			}
		}
		return $result;
	}

	public function get_apply_to_translate(){
		$result = '';
		$apply_to = $this->get_apply_to();
		switch ( $apply_to ) {
			case 'all_services':
				$result = esc_html__( 'All Services', 'ovabookpro' );
				break;
			case 'custom_services':
				$result = esc_html__( 'Some Services', 'ovabookpro' );
				break;
			default:
				break;
		}
		return $result;
	}

	public function is_active(){
		$visibility = $this->get_visibility();
		if ( $visibility != 'public' ) {
			return false;
		}
		$start_date = $this->get_start_date();
		$end_date 	= $this->get_end_date();
		$from_time 	= $this->get_from_time();
		$to_time 	= $this->get_to_time();
		$use_on 	= $this->get_use_on();
		$current_time = absint( current_time( 'timestamp' ) );
		if ( $start_date && $end_date ) {
			if ( $from_time ) {
				$start_date = absint( strtotime( $from_time, $start_date ) );
			}
			if ( $to_time ) {
				$end_date = absint( strtotime( $to_time, $end_date ) );
			} else {
				$end_date = absint( strtotime( '+1 day', $end_date ) );
			}
			if ( $use_on == 'booking_date' ) {
				if ( $current_time >= $start_date && $current_time < $end_date ) {
					return true;
				}
			} else {
				if ( $current_time < $end_date ) {
					return true;
				}
			}
			return false;
		}
		return true;
	}

	public function get_order_from(){
		$id = $this->get_id();
		$value = OBP()->get_post_meta( $id, 'order_from' );
		return $value;
	}

	public function get_apply_to(){
		$id = $this->get_id();
		$value = OBP()->get_post_meta( $id, 'apply_to', 'all_services' );
		return $value;
	}

	public function get_apply_services(){
		$id = $this->get_id();
		$value = OBP()->get_post_meta( $id, 'apply_services', [] );
		return $value;
	}

	public function get_service_ids(){
		$service_ids 	= [];
		$apply_to 		= $this->get_apply_to();
		$vendor_id 		= $this->get_vendor_id();
		
		switch ( $apply_to ) {
			case 'all_services':
				$service_ids = OBP_Service::get_service_ids_by_vendor_id( $vendor_id );
				break;

			case 'custom_services':
			$service_ids = $this->get_apply_services();
				break;
			
			default:
				break;
		}

		return $service_ids;
	}

	public function get_use_on(){
		$id 	= $this->get_id();
		$value 	= OBP()->get_post_meta( $id, 'use_on', 'booking_date' );
		return $value;
	}

	public function get_start_date(){
		$id = $this->get_id();
		$value = OBP()->get_post_meta( $id, 'start_date' );
		return $value;
	}

	public function get_time_formated(){
		$start_date = $this->get_start_date();
		$end_date 	= $this->get_end_date();
		$from_time 	= $this->get_from_time();
		$to_time 	= $this->get_to_time();
		$date_format = OBP()->settings->general->get('date_format', 'Y-m-d');
		$time_format = OBP()->settings->general->get('time_format', 'H:i');
		$str = '';
		if ( $start_date && $end_date ) {
			if ( $from_time ) {
				$start_date = absint( strtotime( $from_time, $start_date ) );
			}
			if ( $to_time ) {
				$end_date = absint( strtotime( $to_time, $end_date ) );
			}
			$from 	= date_i18n( $date_format.' '.$time_format, $start_date );
			$to 	= date_i18n( $date_format.' '.$time_format, $end_date );
			if ( $start_date == $end_date ) {
				$str = $from;
			} else {
				// translators: 1: from time 2: to time.
				$str = sprintf( esc_html__( '%1$s to %2$s', 'ovabookpro' ), $from, $to );
			}
		}

		return $str;
	}

	public function get_amount_discount_str(){
		$discount_type = $this->get_discount_type();
		$coupon_amount = $this->get_coupon_amount();
		$str = '';
		if ( $discount_type == 'percent' ) {
			$str = $coupon_amount.'%';
		} else {
			$str = obp_get_price_html( $coupon_amount );
		}
		return $str;
	}

	public function get_end_date(){
		$id = $this->get_id();
		$value = OBP()->get_post_meta( $id, 'end_date' );
		return $value;
	}

	public function get_apply_for_str(){
		$apply_to = $this->get_apply_to();
		$apply_services = $this->get_apply_services();
		$str_arr = [];
		if ( $apply_to == 'all_services' ) {
			$str_arr[] = esc_html__( 'All Services', 'ovabookpro' );
		} else {
			if ( count( $apply_services ) > 0 ) {
				foreach ( $apply_services as $service_id ) {
					$service = obp_get_service( $service_id );
					$str_arr[] = $service->get_title();
				}
			}
		}
		return implode(", ", $str_arr);
	}

	public function get_only_use_for(){
		$use_on = $this->get_use_on();
		$str = '';
		if ( $use_on == 'booking_date' ) {
			$str = esc_html__( 'For the booking date', 'ovabookpro' );
		} else {
			$str = esc_html__( 'For the service usage date', 'ovabookpro' );
		}
		return $str;
	}

	public function get_from_time(){
		$id = $this->get_id();
		$value = OBP()->get_post_meta( $id, 'from_time' );
		return $value;
	}

	public function get_to_time(){
		$id = $this->get_id();
		$value = OBP()->get_post_meta( $id, 'to_time' );
		return $value;
	}

	public function get_vendor_id(){
		$id = $this->get_id();
		$value = OBP()->get_post_meta( $id, 'vendor_id' );
		return $value;
	}
}