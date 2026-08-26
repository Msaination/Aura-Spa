<?php
namespace BookPro\Cart;

use BookPro\Cart\OBP_Cart_Item;
use BookPro\Order\OBP_Order_Holding;
use BookPro\Order\OBP_Order_Meta_Queue;
use BookPro\StaffDayOff\OBP_Day_Off;
use BookPro\OBP_Calendar;
use BookPro\Staff\OBP_Staff;
use BookPro\OBP_Session_Handler;
use BookPro\Tax\OBP_Tax;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Cart') ) {
	
	class OBP_Cart {

		public $session = null;

		protected $data = array();

		public $content = array();

		public function __construct(){
			$this->session = new OBP_Session_Handler();
			$this->session->init();
			$this->data = $this->session->get('cart', [] );
			$this->cart_content_init();
		}

		public function cart_content_init(){
			$cart_data = $this->data;
			$cart_content = array();

			if ( count( $cart_data ) > 0 ) {
				foreach ( $cart_data as $key => $item ) {
					$cart_content[] = new OBP_Cart_Item( $item );
				}
			}

			$this->content = $cart_content;
		}

		public function get_discount(){
			$total 			= $this->get_subtotal();
			$coupon 		= $this->session->get( 'coupon', [] );
			$use_on 		= isset( $coupon['use_on'] ) ? $coupon['use_on'] : 'booking_date';
			$_start_at 		= isset( $coupon['start_at'] ) ? $coupon['start_at'] : '';
			$_end_at 		= isset( $coupon['end_at'] ) ? $coupon['end_at'] : '';
			$cart_content 	= $this->content;
			$subtotal 		= 0;
			$_subtotal 		= 0;
			$coupon_services = isset( $coupon['service_ids'] ) ? $coupon['service_ids'] : [];

			if ( count( $cart_content ) > 0 ) {

				foreach ( $cart_content as $key => $item ) {

					if ( $use_on == 'booking_date' ) {
						if ( in_array( $item->get_service_id(), $coupon_services ) ) {
							$_subtotal += (float)obp_calculate_inc_tax( $item->get_price(), $item->get_rates() );
						} else {
							$subtotal += (float)obp_calculate_inc_tax( $item->get_price(), $item->get_rates() );
						}
					} else { /* scheduled date */
						$check = true;
						
						if ( $_start_at && $_end_at ) {
							if ( absint( $item->get_start_date() ) >= absint( $_start_at ) && absint( $item->get_end_date() ) <= absint( $_end_at ) ) {
								$check = true;
							} else {
								$check = false;
							}
						}

						if ( in_array( $item->get_service_id(), $coupon_services ) && $check == true ) {
							$_subtotal += (float)obp_calculate_inc_tax( $item->get_price(), $item->get_rates() );
						} else {
							$subtotal += (float)obp_calculate_inc_tax( $item->get_price(), $item->get_rates() );
						}
					}
				}

				$order_from 	= isset( $coupon['order_from'] ) ? $coupon['order_from'] : 0;
				$discount_type 	= isset( $coupon['discount_type'] ) ? $coupon['discount_type'] : 'percent';
				$coupon_amount 	= isset( $coupon['coupon_amount'] ) ? $coupon['coupon_amount'] : 0;

				if ( $order_from ) {
					
					if ( $order_from <= $total ) {

						switch ( $discount_type ) {
							case 'fixed':
								$_subtotal = $_subtotal - (float)$coupon_amount;
								break;

							case 'percent':
								$_subtotal = $_subtotal - ( (float)$coupon_amount/100 * $_subtotal );
								break;
							
							default:
								break;
						}

					}

				} else {
					switch ( $discount_type ) {
						case 'fixed':
							$_subtotal = $_subtotal - (float)$coupon_amount;
							break;

						case 'percent':
							$_subtotal = $_subtotal - ( (float)$coupon_amount/100 * $_subtotal );
							break;
						default:
							break;
					}
				}
			}

			$discount = $total -( (float)$subtotal + (float)$_subtotal );

			if ( $discount <= 0 ) {
				$discount = 0;
			} else {
				$this->session->set( 'has_coupon', 'yes' );
			}
			
			return $discount;
		}

		public function has_varies(){
			$cart_content = $this->content;
			$result = false;
			if ( $cart_content ) {
				foreach ( $cart_content as $key => $item ) {
					$service = obp_get_service( $item->get_service_id() );
					$price_type = $service->get_price_type();
					if ( $price_type == 'varies' ) {
						$result = true;
						break;
					}
				}
			}
			return $result;
		}

		public function get_vendor_id(){
			$last_item = $this->get_last_item();
			$vendor_id = '';
			if ( $last_item ) {
				$vendor_id = $last_item->get_vendor_id();
			}

			return $vendor_id;
		}

		public function get_business_id(){
			$last_item = $this->get_last_item();
			$business_id = '';
			if ( $last_item ) {
				$business_id = $last_item->get_business_id();
			}
			return $business_id;
		}

		public function count_item(){
			$cart_data = $this->data;
			return count( $cart_data );
		}

		public function add_item( $args ){

			$data = $this->data;
			$new_data = array(
				'vendor_id' 	=> $args['vendor_id'],
				'service_id' 	=> $args['service_id'],
				'staff_id' 		=> $args['staff_id'],
				'start_date' 	=> $args['start_date'],
				'end_date' 		=> $args['end_date'],
				'duration' 		=> $args['duration'],
				'price' 		=> $args['price'],
				'plan_id' 		=> $args['plan_id'],
				'business_id' 	=> $args['business_id'],
				'package_ids' 	=> isset( $args['package_ids'] ) ? $args['package_ids'] : [],
			);

			$check_item = $this->get_cart_item( $args['service_id'] );

			if ( $check_item == false ) {
				array_push( $data, $new_data );
				$this->data = $data;
				$this->save_cart();
			}
		}

		public function update_staff( $service_id, $staff_id ){

			$cart_data = $this->data;

			if ( count( $cart_data ) > 0 ) {
				foreach ( $cart_data as $key => $item ) {
					if ( $item['service_id'] == $service_id ) {
						$cart_data[$key]['staff_id'] = $staff_id;
						break;
					}
				}
			}

			$this->data = $cart_data;
			$this->save_cart();
		}

		public function update_key( $old_key, $new_key ){

			$data = $this->data;

			$from_item = isset( $data[$old_key] ) ? $data[$old_key] : array();
			$to_item = isset( $data[$new_key] ) ? $data[$new_key] : array();

			if ( ! empty( $from_item ) && ! empty( $to_item ) ) {
				$data[$old_key] = $to_item;
				$data[$new_key] = $from_item;
			}
			
			$this->data = $data;
			$this->save_cart();
		}

		public function get_cart_item( $service_id ){
			$cart_data = $this->data;

			if ( count( $cart_data ) > 0 ) {
				foreach ( $cart_data as $key => $item ) {
					if ( $item['service_id'] == $service_id ) {
						return new OBP_Cart_Item( $item );
					}
				}
			}

			return false;
		}

		public function get_subtotal(){
			$cart_content = $this->content;
			$subtotal = 0;
			$price_includes_tax = OBP()->settings->tax->get( 'prices_include_tax', 'no' );
			if ( count( $cart_content ) > 0 ) {
				foreach ( $cart_content as $key => $item ) {
					if ( $price_includes_tax == 'yes' ) {
						$subtotal += $item->get_price() - $item->get_total_tax();
					} else {
						$subtotal += $item->get_price();
					}
				}
			}

			return apply_filters( 'obp_cart_subtotal', $subtotal );

		}

		public function get_service_ids(){
			$cart_data = $this->data;
			$service_ids = array();
			if ( count( $cart_data ) ) {
				foreach ( $cart_data as $key => $item ) {
					$service_ids[] = $item['service_id'];
				}
			}

			return $service_ids;
		}

		public function get_total(){
			$subtotal 	= $this->get_subtotal();
			$discount 	= $this->get_discount();
			$system_fee = $this->get_system_fee();
			$tax_fee 	= $this->get_tax_amount();
			$total 		= $subtotal - $discount + $system_fee;
			$total += $tax_fee;

			return apply_filters( 'obp_cart_total', $total, $this );
		}

		public function get_commission(){
   
            $tax_fee = $this->get_tax_amount();
            $system_fee = $this->get_system_fee();
            $commission = 0;
            $commission += $system_fee;
            $commission += $tax_fee;
            return apply_filters( 'obp_cart_commission', $commission, $this );
        }

		public function check_cart_valid(){
			$current_timestamp = current_time( 'timestamp' );
			$cart_content = $this->content;
			$flag =  true;
			
			$date_timestamp_arr = array();

			if ( count( $cart_content ) > 0 ) {

				foreach ( $cart_content as $key => $item ) {
					$start_date = $item->get_start_date();
					$end_date 	= $item->get_end_date();
					$service_id = $item->get_service_id();
					$staff_id 	= $item->get_staff_id();
					$date_timestamp = strtotime( gmdate("Y-m-d", $start_date) );

					$date_timestamp_arr[] = $date_timestamp;

					$order_holding_timeslots 	= OBP_Order_Holding::check_timeslots( $staff_id , $start_date, $end_date );
					$order_meta_queue_timeslots = OBP_Order_Meta_Queue::check_timeslots( $staff_id , $start_date, $end_date );
					$check_timeslots = empty( $order_holding_timeslots ) && empty( $order_meta_queue_timeslots );

					// Check Day Off
					$day_off_row 	= OBP_Day_Off::get_row( $staff_id, $date_timestamp );

					if ( ! empty( $day_off_row ) ) {

						$day_off 		= obp_get_day_off( $day_off_row );
						$day_off_time 	= $day_off->get_time();

						if ( $day_off_time == 'custom_time' ) {
							$flag = $day_off->check_timeslots( $start_date, $end_date );
						} else {
							$flag = false;
						}
					}

					if ( $start_date <= $current_timestamp || ! $check_timeslots ) {
						$flag = false;
					}
				}

			}

			if ( count( $date_timestamp_arr ) > 0 ) {
				$date_timestamp_unique = array_unique( $date_timestamp_arr );
				if ( count( $date_timestamp_unique ) > 1 ) {
					$flag = false;
				}
			}
			
			return $flag;
		}

		public function get_system_fee(){
			$subtotal = $this->get_subtotal();
			$system_fixed_fee 	= OBP()->settings->earning->get('system_fixed_fee', 0);
			$system_percent_fee = OBP()->settings->earning->get('system_percent_fee',0);
			$system_fixed_fee 	= is_numeric( (float)$system_fixed_fee ) ? (float)$system_fixed_fee : 0;
			$system_percent_fee = is_numeric( (float)$system_percent_fee ) ? (float)$system_percent_fee : 0;
			$system_fee = 0;
			$system_fee += $system_fixed_fee;

			$system_fee += $system_percent_fee / 100 * $subtotal;

			return apply_filters( 'obp_cart_system_fee', $system_fee );
		}

		public function get_tax_fee(){
			$subtotal 	= $this->get_subtotal();
			$tax_rate 	= OBP()->settings->tax->get('rate',0);
			$tax_rate 	= is_numeric( (float)$tax_rate ) ? (float)$tax_rate : 0;
			$tax_fee 	= 0;

			$tax_fee += $subtotal * $tax_rate / 100;
			return apply_filters( 'obp_cart_tax_fee', $tax_fee );
		}

		public function get_tax_amount(){

			$cart_content = $this->content;
			$total_tax = 0;
			if ( ! empty( $cart_content ) ) {
				foreach ( $cart_content as $key => $cart_item ) {
					$total_tax += $cart_item->get_total_tax();
				}
			}
			return apply_filters( 'obp_cart_tax_amount', $total_tax );
		}

		public function get_total_time(){
			$cart_content = $this->content;
			$time = 0;
			if ( count( $cart_content ) > 0 ) {
				foreach ( $cart_content as $key => $item ) {
					$time += $item->get_duration();
				}
			}

			$hours = floor($time / (60*60) );
			$minutes = floor( ($time % (60*60)) / 60 );

			$total_time = array();
			if ( $hours ) {
				$total_time[] = number_format_i18n( $hours )._x( 'h', 'hours', 'ovabookpro' );
			}
			if ( $minutes ) {
				$total_time[] = number_format_i18n( $minutes )._x( 'min', 'minutes' ,'ovabookpro' );
			}
			$total_time_str = '';
			if ( ! empty( $total_time ) ) {
				$total_time_str = implode(" ", $total_time);
			}

			return apply_filters( 'obp_cart_total_time', $total_time_str );
		}

		public function get_vendor_total(){
			$subtotal 	= $this->get_subtotal();
			$discount 	= $this->get_discount();
			$tax_amount = $this->get_tax_amount();
			$system_fee = $this->get_system_fee();

			$vendor_total = (float)$subtotal - (float)$discount;

			$vendor_total = $vendor_total + (float)$tax_amount + (float)$system_fee;

			return apply_filters( 'obp_cart_vendor_total', $vendor_total );
		}

		public function get_start_date_earliest(){
			$cart_content 	= $this->content;
			$start_date 	= 0;
			$start_date_arr = array();
			if ( count( $cart_content ) > 0 ) {
				foreach ( $cart_content as $cart_item ) {
					$start_date_arr[] = (int)$cart_item->get_start_date();
				}
			}

			if ( count( $start_date_arr ) > 0 ) {
				$start_date = min( $start_date_arr );
			}

			return $start_date;
		}

		public function get_end_date_lastest(){
			$cart_content 	= $this->content;
			$end_date 		= 0;
			$end_date_arr = array();
			if ( count( $cart_content ) > 0 ) {
				foreach ( $cart_content as $cart_item ) {
					$end_date_arr[] = (int)$cart_item->get_end_date();
				}
			}

			if ( count( $end_date_arr ) > 0 ) {
				$end_date = max( $end_date_arr );
			}

			return $end_date;
		}

		public function get_timeslots_first_item( $business_hours, $date_timestamp, $duration = 0 ){
			
			$current_timestamp 	= absint( current_time('timestamp') );
			$first_cart_item 	= $this->get_first_item();
			$staff_id 			= $first_cart_item->get_staff_id();
			$service_id 		= $first_cart_item->get_service_id();
			$exclude_times 		= array();
			$day_off_row 		= OBP_Day_Off::get_row( $staff_id, $date_timestamp );
			$time_slots 		= array();

			if ( empty( $duration ) ) {
				$duration = absint( $first_cart_item->get_duration() );
			}
		
			if ( ! empty( $day_off_row ) ) {

				$day_off 		= obp_get_day_off( $day_off_row );
				$day_off_time 	= $day_off->get_time();

				if ( $day_off_time === 'custom_time' ) {
					$hour_off = $day_off->get_hour_off();

					$timestamp_off = array_map( function( $value ) use( $date_timestamp ){
						$value['start_date'] = strtotime( $value['start_hour'], $date_timestamp );
						$value['end_date'] = strtotime( $value['end_hour'], $date_timestamp );

						unset( $value['start_hour'] );
						unset( $value['end_hour'] );

						return $value;
					}, $hour_off );

					$exclude_times = array_merge( $exclude_times, $timestamp_off );
				}
			}

			$order_meta_queue_timeslots = OBP_Order_Meta_Queue::get_timeslots_in_day( $staff_id , $date_timestamp );

			$order_holding_timeslots = OBP_Order_Holding::get_timeslots_in_day( $staff_id, $date_timestamp );

			$exclude_times = array_merge( $exclude_times, $order_meta_queue_timeslots, $order_holding_timeslots );

			// Sort exclude times
			if ( ! empty( $exclude_times ) ) {
                usort( $exclude_times, function($a, $b) {
                    return absint( $a['start_date'] ) - absint( $b['start_date'] );
                });
            }

			if ( ! empty( $exclude_times ) ) {
				foreach ( $exclude_times as $key => $value ) {

					$_ex_start_date = absint( $value['start_date'] );
					$_ex_end_date 	= absint( $value['end_date'] );

					$remaining_work_hours = array();

					foreach ( $business_hours as $times ) {

						$work_hours = array();
						$_from_time = absint( strtotime( $times['start_hour'], $date_timestamp ) );
						$_to_time 	= absint( strtotime( $times['end_hour'], $date_timestamp ) );

						if ( $_ex_start_date > $_from_time && $_ex_end_date <= $_to_time ) {
							$work_hours[] = array(
								'start_hour' 	=> gmdate( "H:i", $_from_time ),
								'end_hour' 		=> gmdate( "H:i", $_ex_start_date ),
							);
						}

						if ( $_ex_end_date < $_to_time && $_ex_end_date >= $_from_time ) {
							$work_hours[] = array(
								'start_hour' 	=> gmdate( "H:i", $_ex_end_date ),
								'end_hour' 		=> gmdate( "H:i", $_to_time ),
							);
						}

						if ( $_from_time >= $_ex_start_date && $_to_time <= $_ex_end_date ) {
							continue;
						}

						if ( empty( $work_hours ) ) {
							$work_hours[] = array(
								'start_hour' 	=> gmdate( "H:i", $_from_time ),
								'end_hour' 		=> gmdate( "H:i", $_to_time ),
							);
						}

						$remaining_work_hours = array_merge( $remaining_work_hours, $work_hours );
					}
					$business_hours = $remaining_work_hours;
				}
			}

			if ( count( $business_hours ) > 0 ) {

				foreach ( $business_hours as $times ) {
					$from_time 	= $times['start_hour'];
					$to_time 	= $times['end_hour'];

					$timestamp 	= absint( strtotime( $from_time, $date_timestamp ) );
					$to_time 	= absint( strtotime( $to_time, $date_timestamp ) );

					while ( $timestamp < $to_time && ( $timestamp + $duration ) <= $to_time ) {
						// Check time
						$_end_date = $timestamp + $duration;

						if ( $timestamp > $current_timestamp ) {
							
							$check_order_holding = OBP_Order_Holding::check_timeslots( $staff_id , $timestamp, $_end_date );
							$check_order_meta_queue = OBP_Order_Meta_Queue::check_timeslots( $staff_id , $timestamp, $_end_date );

							$check_timeslots = empty( $check_order_holding ) && empty( $check_order_meta_queue );

							if ( $check_timeslots === true ) {
								$time_slots[] = $timestamp;
							}
						}

						$timestamp = $_end_date;
					}
				}
			}

			return $time_slots;
		}

		public function get_total_timestamp(){
			$start_date_earliest 	= absint( $this->get_start_date_earliest() );
			$end_date_lastest 		= absint( $this->get_end_date_lastest() );

			$total_timestamp = $end_date_lastest - $start_date_earliest;
			return $total_timestamp;
		}

		public function check_timeslots( $start_date, $end_date, $staff_id ){
			$cart_content = $this->content;
			$flag = true;

			if ( count( $cart_content ) > 0 ) {
				foreach ( $cart_content as $item ) {
					$_start_date 	= $item->get_start_date();
					$_end_date 		= $item->get_end_date();
					if ( $staff_id == $item->get_staff_id() ) {
						if ( ( $start_date >= $_start_date && $start_date <= $_end_date ) || ( $end_date >= $_start_date && $end_date <= $_end_date ) ) {
							$flag = false;
						}
					}
				}
			}

			return $flag;
		}

		public function get_last_item(){
			$cart_data 	= $this->data;
			$count 		= count( $cart_data );
			$key 		= $count - 1;
			if ( $count > 0 ) {
				return new OBP_Cart_Item( $cart_data[$key] );
			}

			return array();
		}

		public function get_first_item(){
			$cart_data 	= $this->data;
			$count 		= count( $cart_data );
			if ( $count > 0 ) {
				return new OBP_Cart_Item( $cart_data[0] );
			}

			return new OBP_Cart_Item();
		}

		public function update_cart( $data ){
			$cart_data = $this->data;

			if ( count( $cart_data ) > 0 ) {
				foreach ( $cart_data as $key => $item ) {

					$service_id 	= $item['service_id'];
					$cart_item 		= $this->get_cart_item( $service_id  );
					$service 		= obp_get_service( $service_id );
					$price 			= $service->get_price_specified_time( $data[$key]['start_date'] );
					$package_ids 	= $cart_item->get_package_ids();
					$package_price = 0;
					foreach ( $package_ids as $package_id ) {
						$package = obp_get_package( $package_id );
						$package_price += (float)$package->get_price();
					}
					$price += $package_price;

					$cart_data[$key]['plan_id'] 	= $data[$key]['plan_id'];
					$cart_data[$key]['start_date'] 	= $data[$key]['start_date'];
					$cart_data[$key]['end_date'] 	= $data[$key]['end_date'];
					$cart_data[$key]['staff_id'] 	= $data[$key]['staff_id'];
					$cart_data[$key]['price'] 		= $price;
				}
			}

			$this->data = $cart_data;
			$this->save_cart();
		}

		public function update_packages( $service_id, $package_ids = array() ){
			$cart_data = $this->data;
			$service = obp_get_service( $service_id );
			$service_duration = absint( $service->get_duration() );

			$package_duration = 0;
			$package_price = 0;
			foreach ( $package_ids as $key => $package_id ) {
				$package = obp_get_package( $package_id );
				$package_price += (float)$package->get_price();
				$package_duration += absint( $package->get_seconds() );
			}
			$duration = $service_duration + $package_duration;

			$cart_data = array_map(function( $item ) use ( $service_id, $package_ids, $duration, $service, $package_price ){
				if ( $item['service_id'] == $service_id ) {
					$start_date = $item['start_date'];
					$service_price = $service->get_price_specified_time( $start_date );
					$item['package_ids'] = $package_ids;
					$item['duration'] = $duration;
					$item['price'] = (float)$service_price + $package_price;
				}
				return $item;
			}, $cart_data );

			$this->data = $cart_data;
			$this->save_cart();
		}

		public function update_cart_items( $cart_item, $plan_id, $business_hours, $time_slot_items, $date_timestamp ){
			$result = array();

			$service_id 	= $cart_item->get_service_id();
			$vendor_id 		= $cart_item->get_vendor_id();
			$business_id 	= $cart_item->get_business_id();
			$duration 		= absint( $cart_item->get_duration() );

			$current_timestamp = absint( current_time( 'timestamp' ) );
			$staff_id 		= $cart_item->get_staff_id();
			$service 		= obp_get_service( $service_id );
			$staff_ids 		= $service->get_staff_ids();

			$time_slots = array();
			$last_key 	= count( $time_slot_items ) - 1;
			$from 		= absint( $time_slot_items[$last_key]['end_date'] );
			$to 		= $from + $duration;

			$plan 			= obp_get_plan( $plan_id );
			$plan_time_type = $plan->get_time_type();
			$service_ids 	= $plan->get_service_ids();

			// Get business hours
			if ( $plan->has_special_service() == 'yes' &&
				in_array( $service_id , $plan->get_custom_service_ids() ) && ! empty( $plan->get_time_from_service( $service_id ) ) ) {
				$business_hours = $plan->get_time_from_service( $service_id );
			} else {
				if ( $plan_time_type !== 'full_time' ) {
					$business_hours = $plan->get_times();
				}
			}

			// check service in plan
			if ( in_array( $service_id, $service_ids ) ) {
				$exclude_staff_ids 	= array();

				// find a staff id 
				while ( count( $exclude_staff_ids ) - count( $staff_ids ) < 0 ) {

					if ( count( $exclude_staff_ids ) > 0 ) {
						$staff_id = OBP_Staff::get_priority_staff_id( $service_id, $date_timestamp, $exclude_staff_ids );
					}

					if ( ! empty( $staff_id ) ) {

						$exclude_times 		= array();
						$day_off_row 		= OBP_Day_Off::get_row( $staff_id, $date_timestamp );
						$day_off_timeslots 	= array();

						// Staff day off
						if ( ! empty( $day_off_row ) ) {	
							$day_off 		= obp_get_day_off( $day_off_row );
							$day_off_time 	= $day_off->get_time();

							if ( $day_off_time === 'custom_time' ) {
								$hour_off = $day_off->get_hour_off();

								$timestamp_off = array_map( function( $value ) use( $date_timestamp ){
									$value['start_date'] = strtotime( $value['start_hour'], $date_timestamp );
									$value['end_date'] = strtotime( $value['end_hour'], $date_timestamp );

									unset( $value['start_hour'] );
									unset( $value['end_hour'] );

									return $value;
								}, $hour_off );

								$exclude_times = array_merge( $exclude_times, $timestamp_off );
							} else {
								$exclude_staff_ids[] = $staff_id;
								continue;
							}
						}

						// Check Order Meta & Order Holding
						$order_meta_queue_timeslots = OBP_Order_Meta_Queue::get_timeslots_in_day( $staff_id , $date_timestamp );
						$order_holding_timeslots = OBP_Order_Holding::get_timeslots_in_day( $staff_id , $date_timestamp );

						$exclude_times = array_merge( $exclude_times, $order_meta_queue_timeslots, $order_holding_timeslots, $time_slot_items );

						// Sort exclude time
						if ( ! empty( $exclude_times ) ) {
			                usort( $exclude_times, function($a, $b) {
			                    return absint( $a['start_date'] ) - absint( $b['start_date'] );
			                });
			            }

						if ( ! empty( $exclude_times ) ) {
							foreach ( $exclude_times as $key => $value ) {
								$_ex_start_date = absint( $value['start_date'] );
								$_ex_end_date 	= absint( $value['end_date'] );
								$remaining_work_hours = array();

								foreach ( $business_hours as $times ) {
									$work_hours = array();
									$_from_time = absint( strtotime( $times['start_hour'], $date_timestamp ) );
									$_to_time = absint( strtotime( $times['end_hour'], $date_timestamp ) );

									if ( $_ex_start_date > $_from_time && $_ex_end_date <= $_to_time ) {
										$work_hours[] = array(
											'start_hour' 	=> gmdate( "H:i", $_from_time ),
											'end_hour' 		=> gmdate( "H:i", $_ex_start_date ),
										);
									}

									if ( $_ex_end_date < $_to_time && $_ex_end_date >= $_from_time ) {
										$work_hours[] = array(
											'start_hour' 	=> gmdate( "H:i", $_ex_end_date ),
											'end_hour' 		=> gmdate( "H:i", $_to_time ),
										);
									}

									if ( $_from_time >= $_ex_start_date && $_to_time <= $_ex_end_date ) {
										continue;
									}

									if ( empty( $work_hours ) ) {
										$work_hours[] = array(
											'start_hour' 	=> gmdate( "H:i", $_from_time ),
											'end_hour' 		=> gmdate( "H:i", $_to_time ),
										);
									}

									$remaining_work_hours = array_merge( $remaining_work_hours, $work_hours );
								}
								$business_hours = $remaining_work_hours;
							}
						}


						if ( count( $business_hours ) > 0 ) {
							foreach ( $business_hours as $times ) {
								$from_time 	= $times['start_hour'];
								$to_time 	= $times['end_hour'];

								$timestamp 	= absint( strtotime( $from_time, $date_timestamp ) );
								$to_time 	= absint( strtotime( $to_time, $date_timestamp ) );
								// If the next time is available


								// get timeslots
								while ( $timestamp < $to_time && ( $timestamp + $duration ) <= $to_time ) {
									$end_timestamp = $timestamp + $duration;

									$check_order_holding 	= OBP_Order_Holding::check_timeslots( $staff_id , $timestamp, $end_timestamp );

									$check_order_meta_queue = OBP_Order_Meta_Queue::check_timeslots( $staff_id , $timestamp, $end_timestamp );

									$check_timeslots = empty( $check_order_holding ) && empty( $check_order_meta_queue );

									if ( $timestamp > $current_timestamp && $check_timeslots && $timestamp >= $from ) {
										$found_key = array_search( $staff_id, array_column( $time_slots, 'staff_id') );
										// only added once
										if ( $found_key === false ) {
											$time_slots[] = array(
												'staff_id' 	=> $staff_id,
												'timestamp' => $timestamp,
											);
										}
										
									}
									$timestamp = $end_timestamp;
								}

							}
						} else {
							$exclude_staff_ids[] = $staff_id;
							continue;
						}

						if ( count( $time_slots ) > 0 ) {

							// sort timeslots
							usort( $time_slots, function($a, $b) {
						        return absint( $a['timestamp'] ) - absint( $b['timestamp'] );
						    });

						    $_from = $time_slots[0]['timestamp'];
						    $_to = $_from + $duration;
						    $_staff_id = $time_slots[0]['staff_id'];

						    $result = array(
								'staff_id' 		=> $_staff_id,
								'plan_id' 		=> $plan_id,
								'start_date' 	=> $_from,
								'end_date' 		=> $_to,
							);
							return $result;

						}

					} else {
						break;
					}

					$exclude_staff_ids[] = $staff_id;

				}
			}

			return $result;
		}

		public function remove_item( $service_id ){
			$cart_data = $this->data;

			if ( count( $cart_data ) > 0 ) {
				foreach ( $cart_data as $key => $item ) {
					if ( $item['service_id'] == $service_id ) {
						array_splice( $cart_data, $key,1 );
						break;
					}
				}
			}

			$this->data = $cart_data;
			$this->save_cart();

		}

		public function remove_cart(){
			$this->data = [];
			$this->session->set('cart', [] );
			$this->clear_coupon();
		}

		public function save_cart(){
			$this->session->set('cart', $this->data );
			$this->session->save_data();
			$this->cart_content_init();
			do_action( 'obp_after_save_cart' );
		}

		public function clear_coupon(){
			$this->session->set('coupon_message', null);
			$this->session->set('coupon_code', null );
			$this->session->set('has_coupon', null );
			$this->session->set('coupon', [] );
			$this->session->set('coupon_message', null);
			$this->save_cart();
		}
	}
}