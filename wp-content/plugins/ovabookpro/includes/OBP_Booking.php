<?php

namespace BookPro;

use BookPro\Traits\SingletonTrait;
use BookPro\Order\OBP_Order;
use BookPro\Order\OBP_Order_Holding;
use BookPro\Order\OBP_Order_Meta_Queue;
use BookPro\Plan\OBP_Plan;
use BookPro\Service\OBP_Service;
use BookPro\OBP_Calendar;
use BookPro\Payments\OBP_Payment_Method;
use BookPro\StaffDayOff\OBP_Day_Off;
use BookPro\Staff\OBP_Staff;
use BookPro\Coupon\OBP_Coupon;
use BookPro\Payments\Free\OBP_Free_Payment;
use BookPro\Order\OBP_Order_Meta;
use BookPro\OBP_Checkout;
use Exception;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists('OBP_Booking') ) {
	

	class OBP_Booking {

		use SingletonTrait;

		public function __construct(){
			$hooks = array(
				'obp_booking_next_calendar',
				'obp_booking_prev_calendar',
				'obp_booking_popup',
				'obp_booking_remove_item',
				'obp_booking_continue',
				'obp_booking_change_time',
				'obp_booking_change_calendar',
				'obp_booking_come_back',
				'obp_booking_sort_item',
				'obp_booking_change_staff',
				'obp_booking_update_staff',
				'obp_booking_another_service',
				'obp_booking_save_another_service',
				'obp_booking_countdown_timeout',
				'obp_booking_filter_service',
				'obp_booking_empty_cart',
				'obp_booking_apply_coupon',
				'obp_booking_add_package',
				'obp_checkout_form',
				'obp_checkout_submit',
				'obp_booking_remove_package',
			);

			foreach ( $hooks as $hook ) {
				add_action( 'wp_ajax_'.$hook, array( $this, $hook ) );
				add_action( 'wp_ajax_nopriv_'.$hook, array( $this, $hook ) );
			}
		}

		public function obp_booking_remove_package(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				$package_id = isset( $_POST['package_id'] ) ? sanitize_text_field( wp_unslash( $_POST['package_id'] ) ) : '';
				$service_id = isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : '';

				$package_duration = 0;
				$service = obp_get_service( $service_id );
				$service_duration = absint( $service->get_duration() );

				$cart_item = OBP()->cart->get_cart_item( $service_id );

				if ( $cart_item ) {
					$old_package_ids = $cart_item->get_package_ids();
					$new_package_ids = array_values( array_diff( $old_package_ids, [$package_id] ) );
					if ( ! empty( $new_package_ids ) ) {
						foreach ( $new_package_ids as $key => $value ) {
							$package = obp_get_package( $value );
							$package_duration += absint( $package->get_seconds() );
						}
					}
					// Update package ids
					OBP()->cart->update_packages( $service_id, $new_package_ids );

					$first_cart_item 	= OBP()->cart->get_first_item();

					$plan_id 			= $first_cart_item->get_plan_id();
					$staff_id 			= $first_cart_item->get_staff_id();
					$duration 			= $service_duration + $package_duration;
					$business_id 		= $first_cart_item->get_business_id();
					$vendor_id 			= $first_cart_item->get_vendor_id();

					$date_timestamp = strtotime( gmdate("Y-m-d", $first_cart_item->get_start_date() ) );
					$plan 			= obp_get_plan( $plan_id );
					$plan_time_type = $plan->get_time_type();
					$business 		= obp_get_business( $business_id );

					$weekday 		= gmdate("w",$date_timestamp);
					$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

					$weekday_work_hours = $business->get_business_hours();
					$business_hours 	= isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

					if ( $plan->has_special_service() == 'yes' &&
					in_array( $service_id , $plan->get_custom_service_ids() ) && ! empty( $plan->get_time_from_service( $service_id ) ) ) {
						$business_hours = $plan->get_time_from_service( $service_id );
					} else {
						if ( $plan_time_type !== 'full_time' ) {
							$business_hours = $plan->get_times();
						}
					}

					

					$time_slots_first_item = OBP()->cart->get_timeslots_first_item( $business_hours, $date_timestamp, $duration );

					if ( empty( $time_slots_first_item ) ) {

						OBP()->cart->update_packages( $service_id, $old_package_ids );

						obp_get_template("my-business/single-business/popup-form/staff-error.php");
					
						wp_die();
					}

					$from = $time_slots_first_item[0];
					$to = absint( $from ) + absint( $duration );

					$cart_content = OBP()->cart->content;

					// first item timeslot
					$time_slot_items = array(
						array(
							'start_date' 	=> $from,
							'end_date' 		=> $to,
						)
					);

					$data_update_cart = array(
						array(
							'start_date' 	=> $from,
							'end_date' 		=> $to,
							'staff_id' 		=> $staff_id,
							'plan_id' 		=> $plan_id,
						),
					);

					if ( count( $cart_content ) > 1 ) {
						foreach ( $cart_content as $cart_key => $cart_item ) {
							// ignore first item
							if ( $cart_key === 0 ) {
								continue;
							}

							$data = OBP()->cart->update_cart_items( $cart_item, $plan_id, $business_hours, $time_slot_items, $date_timestamp );

							if ( ! empty( $data ) ) {
								$data_update_cart[] = $data;
								$time_slot_items[] = array(
									'start_date' 	=> $data['start_date'],
									'end_date' 		=> $data['end_date'],
								);
							} else {

								OBP()->cart->update_packages( $service_id, $old_package_ids );

								obp_get_template("my-business/single-business/popup-form/time-error.php");
							
								wp_die();
							}
						}
					}

					// Update Cart
					
					if ( count( $data_update_cart ) > 0 ) {
						OBP()->cart->update_cart( $data_update_cart );
					}

					$first_cart_item 	= OBP()->cart->get_first_item();
					$time_slots 		= OBP()->cart->get_timeslots_first_item( $business_hours, $date_timestamp );

					// Sale Off
					$service_id 			= $first_cart_item->get_service_id();
					$service 				= obp_get_service( $service_id );
					$sale_off_start_date 	= $service->get_sale_off_start_date();
					$sale_off_end_date 		= $service->get_sale_off_end_date();
					$sale_off_from 			= $service->get_sale_off_from();
					$sale_off_to 			= $service->get_sale_off_to();
					$sale_off_start_time 	= 0;
					$sale_off_end_time 		= 0;
					$percent_sale_off 		= $service->get_percent_sale_off();

					if ( $sale_off_start_date && $sale_off_end_date ) {

						$sale_off_start_time 	= $sale_off_start_date;
						$sale_off_end_time 		= strtotime("+1 day", $sale_off_end_date );

						if ( $sale_off_from && $sale_off_to ) {
							$sale_off_start_time 	= strtotime( $sale_off_from, $sale_off_start_date );
							$sale_off_end_time 		= strtotime( $sale_off_to, $sale_off_end_date );
						}
					}

					$target_date = gmdate( "Y-m-d", $from );

					$args = array(
						'target_date' 			=> $target_date,
						'target_time' 			=> $from,
						'time_slots' 			=> $time_slots,
						'service_id' 			=> $first_cart_item->get_service_id(),
						'business_id' 			=> $first_cart_item->get_business_id(),
						'vendor_id' 			=> $first_cart_item->get_vendor_id(),
						'sale_off_start_time' 	=> $sale_off_start_time,
						'sale_off_end_time' 	=> $sale_off_end_time,
						'percent_sale_off' 		=> $percent_sale_off,
					);

					obp_get_template("my-business/single-business/popup-form/calendar-content.php", $args );

					wp_die();
				}
			}
			obp_get_template( "my-business/single-business/popup-form/booking-error.php" );
	
			wp_die();
		}

		public function obp_checkout_submit(){
			$response = array(
				'message' 			=> '',
				'status' 			=> 'error',
				'url' 				=> '',
				'callback' 			=> '',
				'data' 				=> array(),
			);

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				$order_id 		= isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
				$full_name 		= isset( $_POST['full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['full_name'] ) ) : '';
				$phone_number 	= isset( $_POST['phone_number'] ) ? sanitize_text_field( wp_unslash( $_POST['phone_number'] ) ) : '';
				$email 			= isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
				$customer_note 	= isset( $_POST['customer_note'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_note'] ) ) : '';
				$payment 		= isset( $_POST['payment'] ) ? sanitize_text_field( wp_unslash( $_POST['payment'] ) ) : '';

				$payment_gateways 	= OBP()->payment_gateways()->get_available_payment_gateways();

				$meta_input = array(
					OBP_METABOX.'customer_name' 	=> $full_name,
					OBP_METABOX.'customer_email' 	=> $email,
					OBP_METABOX.'customer_phone' 	=> $phone_number,
					OBP_METABOX.'customer_note' 	=> $customer_note,
				);

				$order = obp_get_order( $order_id );
				$order_total = $order->get_total();
				// Order Free
				if ( $order_total == 0 ) {
					$meta_input[OBP_METABOX.'order_status'] 	= 'obp_processing';
					$meta_input[OBP_METABOX.'payment_method'] 	= __( 'Free', 'ovabookpro' );
				}

				// Update Order Meta
				foreach ( $meta_input as $key => $value ) {
					update_post_meta( $order_id, $key, $value );
				}

				if ( isset( $payment_gateways[$payment] ) && $order_total > 0 ) {
					$response = array_merge( $response, $payment_gateways[$payment]->process_payment( $order_id ) );
				} else {
					OBP_Order::obp_order_status_completed_processing( $order_id );
					$response['status'] = 'success';
					$response['url'] 	= obp_get_thank_url_with_key( $order->get_key() );
				}
			}
			wp_send_json( $response );
			wp_die();
		}

		public function obp_checkout_form(){
		
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				$order_id 			= isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
				$order 				= obp_get_order( $order_id );
				$order_items 		= OBP_Order_Meta::get_order_items( $order_id );
				$payment_gateways 	= OBP()->payment_gateways()->get_available_payment_gateways();
				$args = array(
					'order' 			=> $order,
					'order_items' 		=> $order_items,
					'payment_gateways' 	=> $payment_gateways,
				);

			
				obp_get_template( 'my-business/single-business/popup-form/checkout-form.php', $args );
		
			}

		
			wp_die();
		}

		public function obp_booking_add_package(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				$service_id = isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : '';
				$package_ids = isset( $_POST['package_ids'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['package_ids'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$package_duration = 0;
				$service = obp_get_service( $service_id );
				$service_duration = absint( $service->get_duration() );
				
				if ( ! empty( $package_ids ) ) {
					foreach ( $package_ids as $key => $value ) {
						$package = obp_get_package( $value );
						$package_duration += absint( $package->get_seconds() );
					}
				}
				
				$cart_item 			= OBP()->cart->get_cart_item( $service_id );
				$old_package_ids 	= $cart_item->get_package_ids();
				// Add package ids
				OBP()->cart->update_packages( $service_id, $package_ids );

				$first_cart_item 	= OBP()->cart->get_first_item();

				$plan_id 			= $first_cart_item->get_plan_id();
				$staff_id 			= $first_cart_item->get_staff_id();
				$duration 			= $first_cart_item->get_duration();
				$business_id 		= $first_cart_item->get_business_id();
				$vendor_id 			= $first_cart_item->get_vendor_id();

				$date_timestamp = strtotime( gmdate("Y-m-d", $first_cart_item->get_start_date() ) );
				$plan 			= obp_get_plan( $plan_id );
				$plan_time_type = $plan->get_time_type();
				$business 		= obp_get_business( $business_id );

				$weekday 		= gmdate("w",$date_timestamp);
				$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

				$weekday_work_hours = $business->get_business_hours();
				$business_hours 	= isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

				if ( $plan->has_special_service() == 'yes' &&
				in_array( $service_id , $plan->get_custom_service_ids() ) && ! empty( $plan->get_time_from_service( $service_id ) ) ) {
					$business_hours = $plan->get_time_from_service( $service_id );
				} else {
					if ( $plan_time_type !== 'full_time' ) {
						$business_hours = $plan->get_times();
					}
				}

				$time_slots_first_item = OBP()->cart->get_timeslots_first_item( $business_hours, $date_timestamp, $duration );

				if ( empty( $time_slots_first_item ) ) {
					// Remove package ids
					OBP()->cart->update_packages( $service_id, $old_package_ids );
					obp_get_template("my-business/single-business/popup-form/staff-error.php");
			
					wp_die();
				}

				$from = $time_slots_first_item[0];
				$to = absint( $from ) + absint( $duration );

				$cart_content = OBP()->cart->content;

				// first item timeslot
				$time_slot_items = array(
					array(
						'start_date' 	=> $from,
						'end_date' 		=> $to,
					)
				);

				$data_update_cart = array(
					array(
						'start_date' 	=> $from,
						'end_date' 		=> $to,
						'staff_id' 		=> $staff_id,
						'plan_id' 		=> $plan_id,
					),
				);

				if ( count( $cart_content ) > 1 ) {
					foreach ( $cart_content as $cart_key => $cart_item ) {
						// ignore first item
						if ( $cart_key === 0 ) {
							continue;
						}

						$data = OBP()->cart->update_cart_items( $cart_item, $plan_id, $business_hours, $time_slot_items, $date_timestamp );

						if ( ! empty( $data ) ) {
							$data_update_cart[] = $data;
							$time_slot_items[] = array(
								'start_date' 	=> $data['start_date'],
								'end_date' 		=> $data['end_date'],
							);
						} else {
							// Remove package ids
							OBP()->cart->update_packages( $service_id, $old_package_ids );

							obp_get_template("my-business/single-business/popup-form/time-error.php");
						
							wp_die();
						}
					}
				}

				// Update Cart
				
				if ( count( $data_update_cart ) > 0 ) {
					OBP()->cart->update_cart( $data_update_cart );
				}

				$first_cart_item 	= OBP()->cart->get_first_item();
				$time_slots 		= OBP()->cart->get_timeslots_first_item( $business_hours, $date_timestamp );

				// Sale Off
				$service_id 			= $first_cart_item->get_service_id();
				$service 				= obp_get_service( $service_id );
				$sale_off_start_date 	= $service->get_sale_off_start_date();
				$sale_off_end_date 		= $service->get_sale_off_end_date();
				$sale_off_from 			= $service->get_sale_off_from();
				$sale_off_to 			= $service->get_sale_off_to();
				$sale_off_start_time 	= 0;
				$sale_off_end_time 		= 0;
				$percent_sale_off 		= $service->get_percent_sale_off();

				if ( $sale_off_start_date && $sale_off_end_date ) {

					$sale_off_start_time 	= $sale_off_start_date;
					$sale_off_end_time 		= strtotime("+1 day", $sale_off_end_date );

					if ( $sale_off_from && $sale_off_to ) {
						$sale_off_start_time 	= strtotime( $sale_off_from, $sale_off_start_date );
						$sale_off_end_time 		= strtotime( $sale_off_to, $sale_off_end_date );
					}
				}

				$target_date = gmdate( "Y-m-d", $from );

				$args = array(
					'target_date' 			=> $target_date,
					'target_time' 			=> $from,
					'time_slots' 			=> $time_slots,
					'service_id' 			=> $first_cart_item->get_service_id(),
					'business_id' 			=> $first_cart_item->get_business_id(),
					'vendor_id' 			=> $first_cart_item->get_vendor_id(),
					'sale_off_start_time' 	=> $sale_off_start_time,
					'sale_off_end_time' 	=> $sale_off_end_time,
					'percent_sale_off' 		=> $percent_sale_off,
				);

				obp_get_template("my-business/single-business/popup-form/calendar-content.php", $args );

			
				wp_die();
			}

			obp_get_template( "my-business/single-business/popup-form/booking-error.php" );
		
			wp_die();

		}

		public function obp_booking_empty_cart(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				obp_clear_session();
			}
			echo "";
			wp_die();
		}

		public function obp_booking_apply_coupon(){
		
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				
				$coupon_code 	= isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';
				$vendor_id 		= isset( $_POST['vendor_id'] ) ? sanitize_text_field( wp_unslash( $_POST['vendor_id'] ) ) : '';

				// clear coupon
				OBP()->cart->clear_coupon();
				$coupon_code = preg_replace( '/\s+/', '', $coupon_code );

				if ( $coupon_code ) {
					// set coupon
					OBP()->cart->session->set( 'coupon_code', $coupon_code );
					OBP()->cart->save_cart();
					// verify coupon
					$coupon_id = OBP_Coupon::verify_coupon_code( $coupon_code, $vendor_id );

					if ( $coupon_id ) {
						$coupon 		= obp_get_coupon( $coupon_id );
						$coupon_code 	= $coupon->get_coupon_code();
						$coupon_amount 	= $coupon->get_coupon_amount();
						$discount_type 	= $coupon->get_discount_type();
						$coupon_qty 	= $coupon->get_coupon_qty();
						$service_ids 	= $coupon->get_service_ids();
						$__start_date 	= absint( $coupon->get_start_date() );
						$__end_date 	= absint( $coupon->get_end_date() );
						$__from_time 	= $coupon->get_from_time();
						$__to_time 		= $coupon->get_to_time();
						$order_from 	= $coupon->get_order_from();
						$use_on 		= $coupon->get_use_on();
						$current_time 	= current_time( 'timestamp' );
						$first_cart_item = OBP()->cart->get_first_item();
						$coupons_used = OBP_Order::get_order_ids_by_coupon_id( $coupon_id, $vendor_id );

						// check date time
						if ( $__start_date && $__end_date ) {
							$_start 	= $__from_time ? strtotime( $__from_time, $__start_date ) : $__start_date;
							$_end 		= $__to_time ? strtotime( $__to_time, $__end_date ) : strtotime( "+1 day", $__end_date );
							
							$check = absint( $current_time ) >= absint( $_start ) && absint( $current_time ) < absint( $_end );

							if ( $use_on == 'scheduled_date' ) {
								$check = absint( $first_cart_item->get_start_date() ) >= absint( $_start ) && absint( $first_cart_item->get_start_date() ) < absint( $_end );
							}

							if ( $check ) {

								$remaining_qty 	= absint( $coupon_qty ) - count( $coupons_used );
								if ( $remaining_qty > 0 ) {
									$data = array(
										'coupon_id' 	=> $coupon_id,
										'coupon_code' 	=> $coupon_code,
										'coupon_amount' => $coupon_amount,
										'discount_type' => $discount_type,
										'service_ids' 	=> $service_ids,
										'order_from' 	=> $order_from,
										'use_on' 		=> $use_on,
										'start_at' 		=> $_start,
										'end_at' 		=> $_end,
									);
									OBP()->cart->session->set( 'coupon', $data );
									OBP()->cart->save_cart();

									$discount 	= OBP()->cart->get_discount();
									$has_coupon = OBP()->cart->session->get('has_coupon');
									$total 		= OBP()->cart->get_subtotal();

									if ( $has_coupon != 'yes' ) {

										$message = esc_html__( 'Coupon code is invalid', 'ovabookpro' );
										OBP()->cart->session->set( 'coupon_message', $message );
										OBP()->cart->save_cart();

										if ( $order_from && $order_from > $total ) {
											// translators: %s: amount.
											$message = sprintf( _x( 'Order value must be greater than %s', 'total' , 'ovabookpro' ), obp_get_price_html( $order_from ) );
											OBP()->cart->session->set( 'coupon_message', $message );
											OBP()->cart->save_cart();
										}
									}

								} else {
									$message = esc_html__( 'Coupon code not available', 'ovabookpro' );
									OBP()->cart->session->set( 'coupon_message', $message );
									OBP()->cart->save_cart();
								}

							} else {
								$message = esc_html__( 'Coupon code has expired', 'ovabookpro' );
								OBP()->cart->session->set( 'coupon_message', $message );
								OBP()->cart->save_cart();
							}
						} else {
							$remaining_qty = absint( $coupon_qty ) - count( $coupons_used );

							if ( $remaining_qty > 0 ) {

								$data = array(
									'coupon_id' 	=> $coupon_id,
									'coupon_code' 	=> $coupon_code,
									'coupon_amount' => $coupon_amount,
									'discount_type' => $discount_type,
									'service_ids' 	=> $service_ids,
									'order_from' 	=> $order_from,
									'use_on' 		=> $use_on,
								);

								OBP()->cart->session->set( 'coupon', $data );
								OBP()->cart->save_cart();

								$discount 	= OBP()->cart->get_discount();
								$has_coupon = OBP()->cart->session->get('has_coupon');
								$total 		= OBP()->cart->get_subtotal();

								if ( $has_coupon != 'yes' ) {

									$message = esc_html__( 'Coupon code is invalid', 'ovabookpro' );
									OBP()->cart->session->set( 'coupon_message', $message );
									OBP()->cart->save_cart();
									
									if ( $order_from && $order_from > $total ) {
										// translators: %s: amount.
										$message = sprintf( _x( 'Order value must be greater than %s', 'total' , 'ovabookpro' ), obp_get_price_html( $order_from ) );
										OBP()->cart->session->set( 'coupon_message', $message );
										OBP()->cart->save_cart();
									}

								}

								
							} else {
								$message = esc_html__( 'Coupon code not available', 'ovabookpro' );
								OBP()->cart->session->set( 'coupon_message', $message );
								OBP()->cart->save_cart();
							}
						}

					} else {
						$message = esc_html__( 'Coupon code is invalid', 'ovabookpro' );
						OBP()->cart->session->set( 'coupon_message', $message );
						OBP()->cart->save_cart();
					}
				} else {
					OBP()->cart->clear_coupon();
				}

				// Load first item
				$first_cart_item = OBP()->cart->get_first_item();

				if ( ! empty( $first_cart_item ) ) {
					$plan_id 			= $first_cart_item->get_plan_id();
					$plan 				= obp_get_plan( $plan_id );
					$business_id 		= $first_cart_item->get_business_id();
					$start_date 		= $first_cart_item->get_start_date();
					$business 			= obp_get_business( $business_id );
					$plan_time_type 	= $plan->get_time_type();
					$current_timestamp 	= absint( current_time( 'timestamp' ) );
					$target_date 		= gmdate("Y-m-d", $start_date );
					$date_timestamp 	= absint( strtotime( $target_date ) );

					$weekday 		= gmdate("w",$date_timestamp);
					$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

					$weekday_work_hours = $business->get_business_hours();
					$business_hours 	= isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

					// Custom Time
					if ( $plan->has_special_service() == 'yes' &&
					in_array( $service_id , $plan->get_custom_service_ids() ) && ! empty( $plan->get_time_from_service( $service_id ) ) ) {
						$business_hours = $plan->get_time_from_service( $service_id );
					} else {
						if ( $plan_time_type !== 'full_time' ) {
							$business_hours = $plan->get_times();
						}
					}

					if ( ! empty( $business_hours ) ) {

						$first_cart_item 		= OBP()->cart->get_first_item();
						$first_item_time_slots 	= OBP()->cart->get_timeslots_first_item( $business_hours, $date_timestamp );
						$service_id 			= $first_cart_item->get_service_id();
						// Sale Off
						$service 				= obp_get_service( $service_id );
						$sale_off_start_date 	= $service->get_sale_off_start_date();
						$sale_off_end_date 		= $service->get_sale_off_end_date();
						$sale_off_from 			= $service->get_sale_off_from();
						$sale_off_to 			= $service->get_sale_off_to();
						$sale_off_start_time 	= 0;
						$sale_off_end_time 		= 0;
						$percent_sale_off 		= $service->get_percent_sale_off();

						if ( $sale_off_start_date && $sale_off_end_date ) {

							$sale_off_start_time 	= $sale_off_start_date;
							$sale_off_end_time 		= strtotime("+1 day", $sale_off_end_date );

							if ( $sale_off_from && $sale_off_to ) {
								$sale_off_start_time 	= strtotime( $sale_off_from, $sale_off_start_date );
								$sale_off_end_time 		= strtotime( $sale_off_to, $sale_off_end_date );
							}
						}

						$args = array(
							'target_date' 			=> $target_date,
							'target_time' 			=> $start_date,
							'time_slots' 			=> $first_item_time_slots,
							'service_id' 			=> $first_cart_item->get_service_id(),
							'business_id' 			=> $first_cart_item->get_business_id(),
							'vendor_id' 			=> $first_cart_item->get_vendor_id(),
							'sale_off_start_time' 	=> $sale_off_start_time,
							'sale_off_end_time' 	=> $sale_off_end_time,
							'percent_sale_off' 		=> $percent_sale_off,
						);

						obp_get_template("my-business/single-business/popup-form/calendar-content.php", $args );

						
						wp_die();
					}
				}
			}

			obp_get_template( "my-business/single-business/popup-form/booking-error.php" );
			
			wp_die();
		}

		public function obp_booking_filter_service(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_booking_another_service' ) ) {
				
				$service_name 	= isset( $_POST['service_name'] ) ? sanitize_text_field( wp_unslash( $_POST['service_name'] ) ) : "";
				$vendor_id 		= isset( $_POST['vendor_id'] ) ? sanitize_text_field( wp_unslash( $_POST['vendor_id'] ) ) : "";

				$excludes = OBP()->cart->get_service_ids();
				$services = OBP_Service::get_category_service_groups( $vendor_id, $excludes, $service_name );

				$first_cart_item 	= OBP()->cart->get_first_item();
				$start_date 		= $first_cart_item->get_start_date();
				$date_timestamp 	= strtotime( gmdate("Y-m-d", $start_date ) );

				$args = array(
					'services' 			=> $services,
					'date_timestamp' 	=> $date_timestamp,
				);

				obp_get_template( "my-business/single-business/popup-form/service-items.php", $args );
			}

		
			wp_die();
		}

		public function obp_booking_countdown_timeout(){
		
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_booking_countdown' ) ) {
				
				$order_id = OBP()->session->get('order_id');

				if ( $order_id ) {
					OBP_Order_Holding::delete_order_holding( $order_id );
					OBP()->session->set('order_id', null);
					OBP()->session->save_data();
					do_action( 'obp_countdown_timeout', $order_id );
				}

			}
		
			wp_die();
		}

		public function obp_booking_sort_item(){
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_booking_nonce' ) ) {
				$old_index = isset( $_POST['old_index'] ) ? (int)sanitize_text_field( wp_unslash( $_POST['old_index'] ) ) : '';
				$new_index = isset( $_POST['new_index'] ) ? (int)sanitize_text_field( wp_unslash( $_POST['new_index'] ) ) : '';

				OBP()->cart->update_key( $old_index, $new_index );

				$first_cart_item 	= OBP()->cart->get_first_item();
				$service_id 		= $first_cart_item->get_service_id();
				$plan_id 			= $first_cart_item->get_plan_id();
				$staff_id 			= $first_cart_item->get_staff_id();
				$duration 			= $first_cart_item->get_duration();
				$business_id 		= $first_cart_item->get_business_id();
				$vendor_id 			= $first_cart_item->get_vendor_id();
				$start_date 		= $first_cart_item->get_start_date();

				$date_timestamp = strtotime( gmdate("Y-m-d", $start_date ) );
				$plan 			= obp_get_plan( $plan_id );
				$plan_time_type = $plan->get_time_type();
				$business 		= obp_get_business( $business_id );

				$weekday 		= gmdate("w",$date_timestamp);
				$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

				$weekday_work_hours = $business->get_business_hours();
				$business_hours 	= isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

				if ( $plan->has_special_service() == 'yes' &&
				in_array( $service_id , $plan->get_custom_service_ids() ) && ! empty( $plan->get_time_from_service( $service_id ) ) ) {
					$business_hours = $plan->get_time_from_service( $service_id );
				} else {
					if ( $plan_time_type !== 'full_time' ) {
						$business_hours = $plan->get_times();
					}
				}
				// get timeslots of first cart item
				$time_slots = OBP()->cart->get_timeslots_first_item( $business_hours, $date_timestamp );

				if ( count( $time_slots ) > 0 ) {
				
					$from = $time_slots[0];

					$to = absint( $from ) + absint( $duration );

					$cart_content = OBP()->cart->content;

					// first item timeslot
					$time_slot_items = array(
						array(
							'start_date' 	=> $from,
							'end_date' 		=> $to,
						)
					);

					$data_update_cart = array(
						array(
							'start_date' 	=> $from,
							'end_date' 		=> $to,
							'staff_id' 		=> $staff_id,
							'plan_id' 		=> $plan_id,
						),
					);

					if ( count( $cart_content ) > 1 ) {

						foreach ( $cart_content as $cart_key => $cart_item ) {
							// ignore first item
							if ( $cart_key === 0 ) {
								continue;
							}

							$data = OBP()->cart->update_cart_items( $cart_item, $plan_id, $business_hours, $time_slot_items, $date_timestamp );
							if ( ! empty( $data ) ) {
								$data_update_cart[] = $data;
								$time_slot_items[] = array(
									'start_date' 	=> $data['start_date'],
									'end_date' 		=> $data['end_date'],
								);
							} else {
								// reverse
								OBP()->cart->update_key( $old_index, $new_index );
								obp_get_template("my-business/single-business/popup-form/sort-order-item-error.php");
								
								wp_die();
							}
						}

					}

					if ( count( $data_update_cart ) > 0 ) {
						OBP()->cart->update_cart( $data_update_cart );
					}

				}
				// This section is reload only.
				$first_cart_item 	= OBP()->cart->get_first_item();
				$time_slots 		= OBP()->cart->get_timeslots_first_item( $business_hours, $date_timestamp );
				$from 				= $first_cart_item->get_start_date();
				// Sale Off
				$service 				= obp_get_service( $service_id );
				$sale_off_start_date 	= $service->get_sale_off_start_date();
				$sale_off_end_date 		= $service->get_sale_off_end_date();
				$sale_off_from 			= $service->get_sale_off_from();
				$sale_off_to 			= $service->get_sale_off_to();
				$sale_off_start_time 	= 0;
				$sale_off_end_time 		= 0;
				$percent_sale_off 		= $service->get_percent_sale_off();

				if ( $sale_off_start_date && $sale_off_end_date ) {

					$sale_off_start_time 	= $sale_off_start_date;
					$sale_off_end_time 		= strtotime("+1 day", $sale_off_end_date );

					if ( $sale_off_from && $sale_off_to ) {
						$sale_off_start_time 	= strtotime( $sale_off_from, $sale_off_start_date );
						$sale_off_end_time 		= strtotime( $sale_off_to, $sale_off_end_date );
					}
			
				}

				$target_date = gmdate( "Y-m-d", $from );

				$args = array(
					'target_date' 			=> $target_date,
					'target_time' 			=> $from,
					'time_slots' 			=> $time_slots,
					'service_id' 			=> $first_cart_item->get_service_id(),
					'business_id' 			=> $first_cart_item->get_business_id(),
					'vendor_id' 			=> $first_cart_item->get_vendor_id(),
					'sale_off_start_time' 	=> $sale_off_start_time,
					'sale_off_end_time' 	=> $sale_off_end_time,
					'percent_sale_off' 		=> $percent_sale_off,
				);

				obp_get_template("my-business/single-business/popup-form/calendar-content.php", $args );

			
				wp_die();
			}
		}

		public function obp_booking_save_another_service(){
		
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_booking_another_service' ) ) {
				$service_id 	= isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : '';
				$business_id 	= isset( $_POST['business_id'] ) ? sanitize_text_field( wp_unslash( $_POST['business_id'] ) ) : '';
				$vendor_id 		= isset( $_POST['vendor_id'] ) ? sanitize_text_field( wp_unslash( $_POST['vendor_id'] ) ) : '';

				$current_timestamp 	= absint( current_time('timestamp') );
				$first_cart_item 	= OBP()->cart->get_first_item();

				// Plans
				$service 	= obp_get_service( $service_id );
				$staff_ids 	= $service->get_staff_ids();
				$plan_ids 	= OBP_Plan::get_plan_ids_by_service_id( $vendor_id, $service_id );

				if ( empty( $plan_ids ) ) {
					obp_get_template("my-business/single-business/popup-form/plan-none.php");
			
					wp_die();
				}

				if ( empty( $staff_ids ) ) {
					obp_get_template("my-business/single-business/popup-form/staff-none.php");
				
					wp_die();
				}



				if ( ! empty( $first_cart_item ) ) {
					$plan_id 		= $first_cart_item->get_plan_id();
					$plan 			= obp_get_plan( $plan_id );
					$service_ids 	= $plan->get_service_ids();

					if ( in_array( $service_id , $service_ids ) ) {
						// Get last cart item
						$start_date_earliest 	= OBP()->cart->get_start_date_earliest();
						$end_date_lastest 		= OBP()->cart->get_end_date_lastest();

						$target_date 	= gmdate( "Y-m-d", $start_date_earliest );
						$date_timestamp = absint( strtotime( $target_date ) );

						// Duration
						$duration 	= $service->get_duration();
						$business 	= obp_get_business( $business_id );

						$time_slots 	= array();
						$weekday 		= gmdate("w",$start_date_earliest );
						$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

						$weekday_work_hours = $business->get_business_hours();
						$plan_time_type 	= $plan->get_time_type();
			
						$staff_id_exclude = array();

						$business_hours = isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

						// get business hours
						if ( $plan->has_special_service() == 'yes' &&
						in_array( $service_id , $plan->get_custom_service_ids() ) && ! empty( $plan->get_time_from_service( $service_id ) ) ) {
							$business_hours = $plan->get_time_from_service( $service_id );
						} else {
							if ( $plan_time_type !== 'full_time' ) {
								$business_hours = $plan->get_times();
							}
						}

						// find a staff id
						while ( count( $staff_ids ) - count( $staff_id_exclude ) > 0 ) {

							$staff_id = OBP_Staff::get_priority_staff_id( $service_id, $date_timestamp, $staff_id_exclude );

							// Get Staff Id
							if ( ! empty( $staff_id ) ) {
								// next time need
								$to = absint( $end_date_lastest ) + absint( $duration );

								$exclude_times = array();

								// get day off times of staff
								$day_off_row = OBP_Day_Off::get_row( $staff_id, $date_timestamp );

								if ( ! empty( $day_off_row ) ) {

									$day_off 		= obp_get_day_off( $day_off_row );
									$day_off_time 	= $day_off->get_time();

									if ( $day_off_time === 'custom_time' ) {
										$hour_off = $day_off->get_hour_off();

										// Convert to timestamp
										$timestamp_off = array_map( function( $value ) use( $date_timestamp ){
											$value['start_date'] = strtotime( $value['start_hour'], $date_timestamp );
											$value['end_date'] = strtotime( $value['end_hour'], $date_timestamp );

											unset( $value['start_hour'] );
											unset( $value['end_hour'] );
											
											return $value;
										}, $hour_off );

										$exclude_times = array_merge( $exclude_times, $timestamp_off );
									} else {
										$staff_id_exclude[] = $staff_id;
										continue;
									}
								}

								// add timeslot in cart to exclude times
								if ( ! empty( $start_date_earliest ) && ! empty( $end_date_lastest ) ) {
									$exclude_times[] = array(
										'start_date' 	=> $start_date_earliest,
										'end_date' 		=> $end_date_lastest,
									);
								}

								// Check Order Meta & Order Holding
								$order_meta_queue_timeslots = OBP_Order_Meta_Queue::get_timeslots_in_day( $staff_id , $date_timestamp );

								$order_holding_timeslots = OBP_Order_Holding::get_timeslots_in_day( $staff_id, $date_timestamp );

								// add to exclude times
								$exclude_times = array_merge( $exclude_times, $order_meta_queue_timeslots, $order_holding_timeslots );

								// Sort exclude times
								if ( ! empty( $exclude_times ) ) {
					                usort( $exclude_times, function($a, $b) {
					                    return absint( $a['start_date'] ) - absint( $b['start_date'] );
					                });
					            }

					            // Remake Business hours
								if ( ! empty( $exclude_times ) ) {
									foreach ( $exclude_times as $key => $value ) {
										$_ex_start_date = absint( $value['start_date'] );
										$_ex_end_date 	= absint( $value['end_date'] );
										$remaining_work_hours = array();

										foreach ( $business_hours as $times ) {
											$work_hours 	= array();
											$from_time 		= absint( strtotime( $times['start_hour'], $date_timestamp ) );
											$to_time 		= absint( strtotime( $times['end_hour'], $date_timestamp ) );

											if ( $_ex_start_date > $from_time && $_ex_end_date <= $to_time ) {
												$work_hours[] = array(
													'start_hour' 	=> gmdate( "H:i", $from_time ),
													'end_hour' 		=> gmdate( "H:i", $_ex_start_date ),
												);
											}

											if ( $_ex_end_date < $to_time && $_ex_end_date >= $from_time ) {
												$work_hours[] = array(
													'start_hour' 	=> gmdate( "H:i", $_ex_end_date ),
													'end_hour' 		=> gmdate( "H:i", $to_time ),
												);
											}

											// ignore
											if ( $from_time >= $_ex_start_date && $to_time <= $_ex_end_date ) {
												continue;
											}

											if ( empty( $work_hours ) ) {
												$work_hours[] = array(
													'start_hour' 	=> gmdate( "H:i", $from_time ),
													'end_hour' 		=> gmdate( "H:i", $to_time ),
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
										$_from_time = absint( strtotime( $from_time, $date_timestamp ) );

										$to_time 	= absint( strtotime( $to_time, $date_timestamp ) );

										// Get the time closest to the end time of the last service

										if ( $_from_time <= absint( $end_date_lastest ) && $to_time >= $to ) {

											$price = $service->get_price_specified_time( $end_date_lastest );

											$data = array(
												'vendor_id' 	=> $vendor_id,
												'service_id' 	=> $service_id,
												'staff_id' 		=> $staff_id,
												'start_date' 	=> $end_date_lastest,
												'end_date' 		=> $to,
												'price' 		=> $price,
												'duration' 		=> $duration,
												'plan_id' 		=> $plan_id,
												'business_id' 	=> $business_id,
											);
											
											OBP()->cart->add_item( $data );
											
											// Load first item
											$first_cart_item = OBP()->cart->get_first_item();

											if ( ! empty( $first_cart_item ) ) {
												$plan_id 		= $first_cart_item->get_plan_id();
												$plan 			= obp_get_plan( $plan_id );
												$business_id 	= $first_cart_item->get_business_id();
												$start_date 	= $first_cart_item->get_start_date();
												$_service_id 	= $first_cart_item->get_service_id();
												$business 		= obp_get_business( $business_id );
												$plan_time_type = $plan->get_time_type();

												$target_date 	= gmdate("Y-m-d", $start_date );
												$date_timestamp = strtotime( $target_date );

												$weekday 		= gmdate("w",$date_timestamp);
												$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

												$weekday_work_hours = $business->get_business_hours();
												$business_hours 	= isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

												// Custom Time
												if ( $plan->has_special_service() == 'yes' &&
												in_array( $_service_id , $plan->get_custom_service_ids() ) && ! empty( $plan->get_time_from_service( $_service_id ) ) ) {
													$business_hours = $plan->get_time_from_service( $_service_id );
												} else {
													if ( $plan_time_type !== 'full_time' ) {
														$business_hours = $plan->get_times();
													}
												}

												if ( ! empty( $business_hours ) ) {

													$first_cart_item = OBP()->cart->get_first_item();
													
													$first_item_time_slots = OBP()->cart->get_timeslots_first_item( $business_hours, $date_timestamp );

													// Sale Off
													$service = obp_get_service( $_service_id );
													$sale_off_start_date 	= $service->get_sale_off_start_date();
													$sale_off_end_date 		= $service->get_sale_off_end_date();
													$sale_off_from 			= $service->get_sale_off_from();
													$sale_off_to 			= $service->get_sale_off_to();
													$sale_off_start_time 	= 0;
													$sale_off_end_time 		= 0;
													$percent_sale_off 		= $service->get_percent_sale_off();

													if ( $sale_off_start_date && $sale_off_end_date ) {

														$sale_off_start_time 	= $sale_off_start_date;
														$sale_off_end_time 		= strtotime("+1 day", $sale_off_end_date );

														if ( $sale_off_from && $sale_off_to ) {
															$sale_off_start_time 	= strtotime( $sale_off_from, $sale_off_start_date );
															$sale_off_end_time 		= strtotime( $sale_off_to, $sale_off_end_date );
														}
														
													}

													$args = array(
														'target_date' 			=> $target_date,
														'target_time' 			=> $start_date,
														'time_slots' 			=> $first_item_time_slots,
														'service_id' 			=> $first_cart_item->get_service_id(),
														'business_id' 			=> $first_cart_item->get_business_id(),
														'vendor_id' 			=> $first_cart_item->get_vendor_id(),
														'sale_off_start_time' 	=> $sale_off_start_time,
														'sale_off_end_time' 	=> $sale_off_end_time,
														'percent_sale_off' 		=> $percent_sale_off,
													);

													obp_get_template("my-business/single-business/popup-form/calendar-content.php", $args );

											
													wp_die();
												}
											}
											break;
										
										}

										// Add timeslots
										while ( $timestamp < $to_time && ( absint( $timestamp ) + absint( $duration ) ) <= $to_time ) {
											$end_timestamp = absint( $timestamp ) + absint( $duration );

											if ( $timestamp > $current_timestamp ) {

												$check_order_meta_queue = OBP_Order_Meta_Queue::check_timeslots( $staff_id , $timestamp, $end_timestamp );

												$check_order_holding = OBP_Order_Holding::check_timeslots( $staff_id , $timestamp, $end_timestamp );

												$check_timeslots = empty( $check_order_meta_queue ) && empty( $check_order_holding );

												if ( $check_timeslots && $timestamp >= $end_date_lastest ) {

													$found_key = array_search( $staff_id, array_column($time_slots, 'staff_id') );
													// only added once
													if ( $found_key === false ) {
														$time_slots[] = array(
															'staff_id' 	=> $staff_id,
															'timestamp' => $timestamp,
														);
													}
												}
											}

											$timestamp = $end_timestamp;
										}
										
									}

								} else {
									$staff_id_exclude[] = $staff_id;
									continue;
								}

							}

							$staff_id_exclude[] = $staff_id;
						}

						// Get the nearest time
						if ( count( $time_slots ) > 0 ) {

							// sort timeslots
							usort( $time_slots, function($a, $b) {
						        return absint( $a['timestamp'] ) - absint( $b['timestamp'] );
						    });

						    $_from = $time_slots[0]['timestamp'];
						    $_to = absint($_from) + absint( $duration );
						    $_staff_id = $time_slots[0]['staff_id'];

						    $price = $service->get_price_specified_time( $_from );


						    $data = array(
								'vendor_id' 	=> $vendor_id,
								'service_id' 	=> $service_id,
								'staff_id' 		=> $_staff_id,
								'start_date' 	=> $_from,
								'end_date' 		=> $_to,
								'price' 		=> $price,
								'duration' 		=> $duration,
								'plan_id' 		=> $plan_id,
								'business_id' 	=> $business_id,
							);

							OBP()->cart->add_item( $data );

							// Load first item
							$first_cart_item = OBP()->cart->get_first_item();

							if ( ! empty( $first_cart_item ) ) {
								$plan_id 		= $first_cart_item->get_plan_id();
								$plan 			= obp_get_plan( $plan_id );
								$business_id 	= $first_cart_item->get_business_id();
								$start_date 	= $first_cart_item->get_start_date();
								$business 		= obp_get_business( $business_id );
								$plan_time_type = $plan->get_time_type();
								$_service_id 	= $first_cart_item->get_service_id();
								$target_date 	= gmdate("Y-m-d", $start_date );
								$date_timestamp = strtotime( $target_date );

								$weekday 		= gmdate("w",$date_timestamp);
								$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

								$weekday_work_hours = $business->get_business_hours();
								$business_hours 	= isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

								// Custom Time
								if ( $plan->has_special_service() == 'yes' &&
								in_array( $_service_id , $plan->get_custom_service_ids() ) && ! empty( $plan->get_time_from_service( $_service_id ) ) ) {
									$business_hours = $plan->get_time_from_service( $_service_id );
								} else {
									if ( $plan_time_type !== 'full_time' ) {
										$business_hours = $plan->get_times();
									}
								}


								if ( ! empty( $business_hours ) ) {

									$first_cart_item 	= OBP()->cart->get_first_item();

									$time_slots 		= OBP()->cart->get_timeslots_first_item( $business_hours, $date_timestamp );

									// Sale Off
									$service_id 			= $first_cart_item->get_service_id();
									$service 				= obp_get_service( $service_id );
									$sale_off_start_date 	= $service->get_sale_off_start_date();
									$sale_off_end_date 		= $service->get_sale_off_end_date();
									$sale_off_from 			= $service->get_sale_off_from();
									$sale_off_to 			= $service->get_sale_off_to();
									$sale_off_start_time 	= 0;
									$sale_off_end_time 		= 0;
									$percent_sale_off 		= $service->get_percent_sale_off();

									if ( $sale_off_start_date && $sale_off_end_date ) {

										$sale_off_start_time 	= $sale_off_start_date;
										$sale_off_end_time 		= strtotime("+1 day", $sale_off_end_date );

										if ( $sale_off_from && $sale_off_to ) {
											$sale_off_start_time 	= strtotime( $sale_off_from, $sale_off_start_date );
											$sale_off_end_time 		= strtotime( $sale_off_to, $sale_off_end_date );
										}
									
									}

									$args = array(
										'target_date' 			=> $target_date,
										'target_time' 			=> $start_date,
										'time_slots' 			=> $time_slots,
										'service_id' 			=> $first_cart_item->get_service_id(),
										'business_id' 			=> $first_cart_item->get_business_id(),
										'vendor_id' 			=> $first_cart_item->get_vendor_id(),
										'sale_off_start_time' 	=> $sale_off_start_time,
										'sale_off_end_time' 	=> $sale_off_end_time,
										'percent_sale_off' 		=> $percent_sale_off,
									);

									obp_get_template("my-business/single-business/popup-form/calendar-content.php", $args );

									wp_die();
								}
							}

						}

					}
				}



				obp_get_template("my-business/single-business/popup-form/calendar-none.php");
				wp_die();

			}

			obp_get_template("my-business/single-business/popup-form/content-none.php");
			wp_die();
		}

		public function obp_booking_another_service(){
		
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_booking_nonce' ) ) {
				$vendor_id 		= isset( $_POST['vendor_id'] ) ? sanitize_text_field( wp_unslash( $_POST['vendor_id'] ) ) : '';
				$business_id 	= isset( $_POST['business_id'] ) ? sanitize_text_field( wp_unslash( $_POST['business_id'] ) ) : '';

				$service_ids_cart 	= OBP()->cart->get_service_ids();
				$first_cart_item 	= OBP()->cart->get_first_item();
				$date_timestamp 	= strtotime( gmdate("Y-m-d", absint( $first_cart_item->get_start_date() ) ) );
				$services 			= OBP_Service::get_category_service_groups( $vendor_id, $service_ids_cart );

				$args = array(
					'services' 			=> $services,
					'vendor_id' 		=> $vendor_id,
					'business_id' 		=> $business_id,
					'date_timestamp' 	=> $date_timestamp,
				);

				obp_booking_popup_service( $args );

			}

			wp_die();
		}

		public function obp_booking_update_staff(){

			// Get timeslot valid for staff selected
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_booking_staff' ) ) {
				$staff_id 	= isset( $_POST['staff_id'] ) ? sanitize_text_field( wp_unslash( $_POST['staff_id'] ) ) : '';
				$service_id = isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : '';

				if ( $service_id ) {
					$cart_item 			= OBP()->cart->get_cart_item( $service_id );
					$start_date 		= $cart_item->get_start_date();
					$end_date 			= $cart_item->get_end_date();
					$plan_id 			= $cart_item->get_plan_id();
					$duration 			= $cart_item->get_duration();
					$business_id 		= $cart_item->get_business_id();
					$vendor_id 			= $cart_item->get_vendor_id();
					$old_staff_id 		= $cart_item->get_staff_id();

					$date_timestamp = strtotime( gmdate("Y-m-d", $start_date ) );
					$plan 			= obp_get_plan( $plan_id );
					$plan_time_type = $plan->get_time_type();
					$business 		= obp_get_business( $business_id );
					$current_timestamp = current_time('timestamp');

					$weekday 		= gmdate("w",$date_timestamp);
					$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

					$weekday_work_hours = $business->get_business_hours();
					$business_hours 	= isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

					if ( $plan->has_special_service() == 'yes' &&
					in_array( $service_id , $plan->get_custom_service_ids() ) && ! empty( $plan->get_time_from_service( $service_id ) ) ) {
						$business_hours = $plan->get_time_from_service( $service_id );
					} else {
						if ( $plan_time_type !== 'full_time' ) {
							$business_hours = $plan->get_times();
						}
					}

					// Check if the required time is valid or not
					$check_order_holding 	= OBP_Order_Holding::check_timeslots( $staff_id , $start_date, $end_date );
					$check_order_meta_queue = OBP_Order_Meta_Queue::check_timeslots( $staff_id , $start_date, $end_date );

					$check_timeslots = empty( $check_order_holding ) && empty( $check_order_meta_queue );
					
					if ( ! $check_timeslots ) {

						obp_get_template("my-business/single-business/popup-form/staff-error.php");
				
						wp_die();
					}

					$exclude_times = array();
					// Check Day Off Of Staff
					$day_off_row = OBP_Day_Off::get_row( $staff_id, $date_timestamp );

					if ( ! empty( $day_off_row ) ) {
						$day_off 		= obp_get_day_off( $day_off_row );
						$day_off_time 	= $day_off->get_time();

						if ( $day_off_time === 'custom_time' ) {
							$hour_off = $day_off->get_hour_off();
							// Convert to timestamp
							$timestamp_off = array_map(function( $value ) use( $date_timestamp ){
								$value['start_date'] = strtotime( $value['start_hour'], $date_timestamp );
								$value['end_date'] = strtotime( $value['end_hour'], $date_timestamp );
								unset( $value['start_hour'] );
								unset( $value['end_hour'] );
								return $value;
							}, $hour_off );

							$exclude_times = array_merge( $exclude_times, $timestamp_off );
						} else {
							obp_get_template("my-business/single-business/popup-form/staff-error.php");
					
							wp_die();
						}
					}

					// Check Order Meta & Order Holding
					$order_meta_queue_timeslots = OBP_Order_Meta_Queue::get_timeslots_in_day( $staff_id , $date_timestamp );
					$order_holding_timeslots = OBP_Order_Holding::get_timeslots_in_day( $staff_id, $date_timestamp );

					$exclude_times = array_merge( $exclude_times, $order_meta_queue_timeslots, $order_holding_timeslots );

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
								$work_hours 	= array();
								$from_time 		= absint( strtotime( $times['start_hour'], $date_timestamp ) );
								$to_time 		= absint( strtotime( $times['end_hour'], $date_timestamp ) );

								if ( $_ex_start_date > $from_time && $_ex_end_date <= $to_time ) {
									$work_hours[] = array(
										'start_hour' 	=> gmdate( "H:i", $from_time ),
										'end_hour' 		=> gmdate( "H:i", $_ex_start_date ),
									);
								}

								if ( $_ex_end_date < $to_time && $_ex_end_date >= $from_time ) {
									$work_hours[] = array(
										'start_hour' 	=> gmdate( "H:i", $_ex_end_date ),
										'end_hour' 		=> gmdate( "H:i", $to_time ),
									);
								}

								if ( $from_time >= $_ex_start_date && $to_time <= $_ex_end_date ) {
									continue;
								}

								if ( empty( $work_hours ) ) {
									$work_hours[] = array(
										'start_hour' 	=> gmdate( "H:i", $from_time ),
										'end_hour' 		=> gmdate( "H:i", $to_time ),
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

							$timestamp 	= strtotime( $from_time, $date_timestamp );
							$to_time 	= strtotime( $to_time, $date_timestamp );

							if ( $start_date >= $timestamp && $start_date < $to_time &&
							$end_date > $timestamp && $end_date <= $to_time ) {
								
								OBP()->cart->update_staff( $service_id, $staff_id );

								// Update cart
								$first_cart_item = OBP()->cart->get_first_item();

								if ( ! empty( $first_cart_item ) ) {

									$plan_id 		= $first_cart_item->get_plan_id();
									$plan 			= obp_get_plan( $plan_id );
									$business_id 	= $first_cart_item->get_business_id();
									$start_date 	= $first_cart_item->get_start_date();
									$business 		= obp_get_business( $business_id );
									$plan_time_type = $plan->get_time_type();
									$duration 		= $first_cart_item->get_duration();

									$target_date 	= gmdate("Y-m-d", $start_date );
									$date_timestamp = strtotime( $target_date );

									$weekday 		= gmdate("w",$date_timestamp);
									$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

									$weekday_work_hours = $business->get_business_hours();
									$business_hours 	= isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

									// Custom Time
									if ( $plan->has_special_service() == 'yes' &&
									in_array( $service_id , $plan->get_custom_service_ids() ) && ! empty( $plan->get_time_from_service( $service_id ) ) ) {
										$business_hours = $plan->get_time_from_service( $service_id );
									} else {
										if ( $plan_time_type !== 'full_time' ) {
											$business_hours = $plan->get_times();
										}
									}
									$time_slots = OBP()->cart->get_timeslots_first_item( $business_hours, $date_timestamp );

									// If change staff first item
									if ( $first_cart_item->get_service_id() == $service_id ) {
										
										if ( ! empty( $business_hours ) ) {
											$first_cart_item = OBP()->cart->get_first_item();
											$time_slots = OBP()->cart->get_timeslots_first_item( $business_hours, $date_timestamp );

											if ( ! empty( $time_slots ) ) {
												$from = $time_slots[0];
												$to = (int)$from + (int)$duration;

												$cart_content = OBP()->cart->content;

												// first item timeslot
												$time_slot_items = array(
													array(
														'start_date' 	=> $from,
														'end_date' 		=> $to,
													)
												);

												$data_update_cart = array(
													array(
														'start_date' 	=> $from,
														'end_date' 		=> $to,
														'staff_id' 		=> $staff_id,
														'plan_id' 		=> $plan_id,
													),
												);

												if ( count( $cart_content ) > 1 ) {
													foreach ( $cart_content as $cart_key => $cart_item ) {
														// ignore first item
														if ( $cart_key === 0 ) {
															continue;
														}

														$data = OBP()->cart->update_cart_items( $cart_item, $plan_id, $business_hours, $time_slot_items, $date_timestamp );
														if ( ! empty( $data ) ) {
															$data_update_cart[] = $data;
															$time_slot_items[] = array(
																'start_date' 	=> $data['start_date'],
																'end_date' 		=> $data['end_date'],
															);
														} else {
															OBP()->cart->update_staff( $service_id, $old_staff_id );
															obp_get_template("my-business/single-business/popup-form/staff-error.php");
													
															wp_die();
															break;
														}
													}
												}

												if ( count( $data_update_cart ) > 0 ) {
													OBP()->cart->update_cart( $data_update_cart );
												}

												$first_cart_item = OBP()->cart->get_first_item();

												// Sale Off
												$service_id 			= $first_cart_item->get_service_id();
												$service 				= obp_get_service( $service_id );
												$sale_off_start_date 	= $service->get_sale_off_start_date();
												$sale_off_end_date 		= $service->get_sale_off_end_date();
												$sale_off_from 			= $service->get_sale_off_from();
												$sale_off_to 			= $service->get_sale_off_to();
												$sale_off_start_time 	= 0;
												$sale_off_end_time 		= 0;
												$percent_sale_off 		= $service->get_percent_sale_off();

												if ( $sale_off_start_date && $sale_off_end_date ) {


													$sale_off_start_time 	= $sale_off_start_date;
													$sale_off_end_time 		= strtotime("+1 day", $sale_off_end_date );

													if ( $sale_off_from && $sale_off_to ) {
														$sale_off_start_time 	= strtotime( $sale_off_from, $sale_off_start_date );
														$sale_off_end_time 		= strtotime( $sale_off_to, $sale_off_end_date );
													}
											
												}

												$args = array(
													'target_date' 			=> $target_date,
													'target_time' 			=> $from,
													'time_slots' 			=> $time_slots,
													'service_id' 			=> $first_cart_item->get_service_id(),
													'business_id' 			=> $first_cart_item->get_business_id(),
													'vendor_id' 			=> $first_cart_item->get_vendor_id(),
													'sale_off_start_time' 	=> $sale_off_start_time,
													'sale_off_end_time' 	=> $sale_off_end_time,
													'percent_sale_off' 		=> $percent_sale_off,
												);

												obp_get_template("my-business/single-business/popup-form/calendar-content.php", $args );

												wp_die();
											}
										}

									}

									// Sale Off
									$service_id 			= $first_cart_item->get_service_id();
									$service 				= obp_get_service( $service_id );
									$sale_off_start_date 	= $service->get_sale_off_start_date();
									$sale_off_end_date 		= $service->get_sale_off_end_date();
									$sale_off_from 			= $service->get_sale_off_from();
									$sale_off_to 			= $service->get_sale_off_to();
									$sale_off_start_time 	= 0;
									$sale_off_end_time 		= 0;
									$percent_sale_off 		= $service->get_percent_sale_off();

									if ( $sale_off_start_date && $sale_off_end_date ) {

										$sale_off_start_time 	= $sale_off_start_date;
										$sale_off_end_time 		= strtotime("+1 day", $sale_off_end_date );

										if ( $sale_off_from && $sale_off_to ) {
											$sale_off_start_time 	= strtotime( $sale_off_from, $sale_off_start_date );
											$sale_off_end_time 		= strtotime( $sale_off_to, $sale_off_end_date );
										}
							
									}

									$args = array(
										'target_date' 			=> $target_date,
										'target_time' 			=> $first_cart_item->get_start_date(),
										'time_slots' 			=> $time_slots,
										'service_id' 			=> $first_cart_item->get_service_id(),
										'business_id' 			=> $first_cart_item->get_business_id(),
										'vendor_id' 			=> $first_cart_item->get_vendor_id(),
										'sale_off_start_time' 	=> $sale_off_start_time,
										'sale_off_end_time' 	=> $sale_off_end_time,
										'percent_sale_off' 		=> $percent_sale_off,
									);

									obp_get_template("my-business/single-business/popup-form/calendar-content.php", $args );
									wp_die();
								}
							}
						}
					}
					// 

					obp_get_template("my-business/single-business/popup-form/staff-error.php");
					wp_die();
				}
				
			}

			obp_get_template("my-business/single-business/popup-form/booking-error.php");
			wp_die();
		}

		public function obp_booking_change_staff(){
	
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_booking_nonce' ) ) {
				$service_id = isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : '';
				if ( $service_id ) {
					$args = array(
						'service_id' => $service_id,
					);
					obp_booking_popup_staff( $args );
				}
				
			}
	
			wp_die();
		}

		public function obp_booking_change_calendar(){
			$response = [];

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_booking_nonce' ) ) {

				$selected_date 	= isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
				$response['month_year'] = date_i18n( 'F Y', absint( strtotime( $selected_date ) ) );

				$date_timestamp 	= absint( strtotime( $selected_date ) );
				$first_cart_item 	= OBP()->cart->get_first_item();

				$service_id 	= $first_cart_item->get_service_id();
				$vendor_id 		= $first_cart_item->get_vendor_id();
				$business_id 	= $first_cart_item->get_business_id();
				$duration 		= absint( $first_cart_item->get_duration() );
				$start_date 	= $first_cart_item->get_start_date();
				$staff_id 		= $first_cart_item->get_staff_id();

				$service 		= obp_get_service( $service_id );
				$staff_ids 		= $service->get_staff_ids();

				$business 		= obp_get_business( $business_id );

				$old_date_timestamp = strtotime( gmdate("Y-m-d", absint( $start_date ) ) );
				$hour_timestamp 	= absint( $start_date ) - absint( $old_date_timestamp );
				$need_timestamp 	= $hour_timestamp + $date_timestamp;


				$plan_ids = OBP_Plan::get_plan_id_by_date( $vendor_id, $service_id, $date_timestamp );

				if ( count( $plan_ids ) < 1 ) {
					ob_start();
					obp_get_template("my-business/single-business/popup-form/calendar-none.php");
					$response['html'] = ob_get_clean();
					wp_die();
				}

				$current_timestamp = absint( current_time('timestamp') );

				$plan_id 		= $plan_ids[0];
				
				$plan 			= obp_get_plan( $plan_id );
				$plan_time_type = $plan->get_time_type();

				$weekday 		= gmdate("w",$date_timestamp);
				$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

				$weekday_work_hours = $business->get_business_hours();

				$business_hours 	= isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

				if ( $plan->has_special_service() == 'yes' &&
				in_array( $service_id , $plan->get_custom_service_ids() ) && ! empty( $plan->get_time_from_service( $service_id ) ) ) {
					$business_hours = $plan->get_time_from_service( $service_id );
				} else {
					if ( $plan_time_type !== 'full_time' ) {
						$business_hours = $plan->get_times();
					}
				}

				// Find Staff Id
				$exclude_staff_ids = array();

				while ( count( $exclude_staff_ids ) < count( $staff_ids ) ) {
					// find next staff id
					if ( count( $exclude_staff_ids ) > 0 ) {
						$staff_id = OBP_Staff::get_priority_staff_id( $service_id, $date_timestamp, $exclude_staff_ids );
					}

					$exclude_times = array();

					$day_off_row 		= OBP_Day_Off::get_row( $staff_id, $date_timestamp );
					$time_slots 		= array();

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
							// choose another staff id
							$exclude_staff_ids[] = $staff_id;
							continue;
						}
					}
					
					// Check Order Meta & Order Holding
					$order_meta_queue_timeslots = OBP_Order_Meta_Queue::get_timeslots_in_day( $staff_id , $date_timestamp );
					$order_holding_timeslots = OBP_Order_Holding::get_timeslots_in_day( $staff_id, $date_timestamp );

					$exclude_times = array_merge( $exclude_times, $order_meta_queue_timeslots, $order_holding_timeslots );

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

								// ignore
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
							
							while ( ( $timestamp + $duration ) <= $to_time ) {
								// Check time
								$__end_date = $timestamp + $duration;

								if ( $timestamp > $current_timestamp ) {
		
									$check_order_meta_queue = OBP_Order_Meta_Queue::check_timeslots( $staff_id , $timestamp, $__end_date );
									$check_order_holding = OBP_Order_Holding::check_timeslots( $staff_id , $timestamp, $__end_date );
									
									$check_timeslots = empty( $check_order_meta_queue ) && empty( $check_order_holding );

									if ( $check_timeslots == true ) {
										$time_slots[] = $timestamp;
									}
								}

								$timestamp = $__end_date;
							}
							
						}
					}


					if ( count( $time_slots ) > 0 ) {
						$from = $time_slots[0];
						
						if ( in_array( $need_timestamp, $time_slots ) ) {
							$from = $need_timestamp;
						}

						$to = absint( $from ) + absint( $duration );
						$target_time = $from;

						// Check time another items
						$cart_content = OBP()->cart->content;

						// first item timeslot
						$time_slot_items = array(
							array(
								'start_date' 	=> $from,
								'end_date' 		=> $to,
							)
						);

						$data_update_cart = array(
							array(
								'start_date' 	=> $from,
								'end_date' 		=> $to,
								'staff_id' 		=> $staff_id,
								'plan_id' 		=> $plan_id,
							),
						);

						if ( count( $cart_content ) > 1 ) {
							foreach ( $cart_content as $cart_key => $cart_item ) {
								// ignore first item
								if ( $cart_key === 0 ) {
									continue;
								}

								$data = OBP()->cart->update_cart_items( $cart_item, $plan_id, $business_hours, $time_slot_items, $date_timestamp );
								if ( ! empty( $data ) ) {
									$data_update_cart[] = $data;
									$time_slot_items[] = array(
										'start_date' 	=> $data['start_date'],
										'end_date' 		=> $data['end_date'],
									);
								} else {

									ob_start();
									obp_get_template("my-business/single-business/popup-form/calendar-none.php");
									$response['html'] = ob_get_clean();

									wp_send_json( $response );
								}
							}
						}


						if ( count( $data_update_cart ) > 0 ) {
							OBP()->cart->update_cart( $data_update_cart );
						}

						// Sale Off
						$service 				= obp_get_service( $service_id );
						$sale_off_start_date 	= $service->get_sale_off_start_date();
						$sale_off_end_date 		= $service->get_sale_off_end_date();
						$sale_off_from 			= $service->get_sale_off_from();
						$sale_off_to 			= $service->get_sale_off_to();
						$sale_off_start_time 	= 0;
						$sale_off_end_time 		= 0;
						$percent_sale_off 		= $service->get_percent_sale_off();

						if ( $sale_off_start_date && $sale_off_end_date ) {

							$sale_off_start_time 	= $sale_off_start_date;
							$sale_off_end_time 		= strtotime("+1 day", $sale_off_end_date );

							if ( $sale_off_from && $sale_off_to ) {
								$sale_off_start_time 	= strtotime( $sale_off_from, $sale_off_start_date );
								$sale_off_end_time 		= strtotime( $sale_off_to, $sale_off_end_date );
							}
						
						}

						$args = array(
							'target_date' 			=> $selected_date,
							'target_time' 			=> $target_time,
							'time_slots' 			=> $time_slots,
							'service_id' 			=> $service_id,
							'business_id' 			=> $business_id,
							'vendor_id' 			=> $vendor_id,
							'sale_off_start_time' 	=> $sale_off_start_time,
							'sale_off_end_time' 	=> $sale_off_end_time,
							'percent_sale_off' 		=> $percent_sale_off
						);
						ob_start();
						obp_get_template("my-business/single-business/popup-form/calendar-content.php", $args );
						$response['html'] = ob_get_clean();
						wp_send_json( $response );
						
					}

					$exclude_staff_ids[] = $staff_id;
				}
	

			}
			ob_start();
			obp_get_template( "my-business/single-business/popup-form/calendar-none.php" );
			$response['html'] = ob_get_clean();
			wp_send_json( $response );
			wp_die();
		}

		public function obp_booking_change_time(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_booking_nonce' ) ) {
				$new_time 			= isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '';
				$new_time 			= absint( $new_time );

				if ( ! empty( $new_time ) ) {
					$first_cart_item 	= OBP()->cart->get_first_item();
					$service_id 		= $first_cart_item->get_service_id();
					$plan_id 			= $first_cart_item->get_plan_id();
					$staff_id 			= $first_cart_item->get_staff_id();
					$duration 			= $first_cart_item->get_duration();
					$business_id 		= $first_cart_item->get_business_id();
					$vendor_id 			= $first_cart_item->get_vendor_id();

					$date_timestamp = strtotime( gmdate("Y-m-d", $new_time ) );
					$plan 			= obp_get_plan( $plan_id );
					$plan_time_type = $plan->get_time_type();
					$business 		= obp_get_business( $business_id );

					$weekday 		= gmdate("w",$date_timestamp);
					$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

					$weekday_work_hours = $business->get_business_hours();
					$business_hours 	= isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

					$from = $new_time;
					$to = absint( $from ) + absint( $duration );

					$cart_content = OBP()->cart->content;

					// first item timeslot
					$time_slot_items = array(
						array(
							'start_date' 	=> $from,
							'end_date' 		=> $to,
						)
					);

					$data_update_cart = array(
						array(
							'start_date' 	=> $from,
							'end_date' 		=> $to,
							'staff_id' 		=> $staff_id,
							'plan_id' 		=> $plan_id,
						),
					);

					if ( count( $cart_content ) > 1 ) {
						foreach ( $cart_content as $cart_key => $cart_item ) {
							// ignore first item
							if ( $cart_key === 0 ) {
								continue;
							}

							$data = OBP()->cart->update_cart_items( $cart_item, $plan_id, $business_hours, $time_slot_items, $date_timestamp );

							if ( ! empty( $data ) ) {
								$data_update_cart[] = $data;
								$time_slot_items[] = array(
									'start_date' 	=> $data['start_date'],
									'end_date' 		=> $data['end_date'],
								);
							} else {
								obp_get_template("my-business/single-business/popup-form/time-error.php");
								wp_die();
							}
						}
					}

					// Update Cart
					if ( count( $data_update_cart ) > 0 ) {
						OBP()->cart->update_cart( $data_update_cart );
					}
				}

				// Load first item
				$first_cart_item = OBP()->cart->get_first_item();

				if ( ! empty( $first_cart_item ) ) {
					$plan_id 		= $first_cart_item->get_plan_id();
					$plan 			= obp_get_plan( $plan_id );
					$business_id 	= $first_cart_item->get_business_id();
					$start_date 	= $first_cart_item->get_start_date();
					$business 		= obp_get_business( $business_id );
					$plan_time_type = $plan->get_time_type();

					$target_date 	= gmdate("Y-m-d", absint( $start_date ) );
					$date_timestamp = strtotime( $target_date );

					$weekday 		= gmdate("w",$date_timestamp);
					$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

					$weekday_work_hours = $business->get_business_hours();
					$business_hours 	= isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

					// Custom Time
					if ( $plan->has_special_service() == 'yes' &&
					in_array( $service_id , $plan->get_custom_service_ids() ) && ! empty( $plan->get_time_from_service( $service_id ) ) ) {
						$business_hours = $plan->get_time_from_service( $service_id );
					} else {
						if ( $plan_time_type !== 'full_time' ) {
							$business_hours = $plan->get_times();
						}
					}

					if ( ! empty( $business_hours ) ) {

						$first_cart_item 	= OBP()->cart->get_first_item();
						$time_slots 		= OBP()->cart->get_timeslots_first_item( $business_hours, $date_timestamp );

						// Sale Off
						$service_id 			= $first_cart_item->get_service_id();
						$service 				= obp_get_service( $service_id );
						$sale_off_start_date 	= $service->get_sale_off_start_date();
						$sale_off_end_date 		= $service->get_sale_off_end_date();
						$sale_off_from 			= $service->get_sale_off_from();
						$sale_off_to 			= $service->get_sale_off_to();
						$sale_off_start_time 	= 0;
						$sale_off_end_time 		= 0;
						$percent_sale_off 		= $service->get_percent_sale_off();

						if ( $sale_off_start_date && $sale_off_end_date ) {

							$sale_off_start_time 	= $sale_off_start_date;
							$sale_off_end_time 		= strtotime("+1 day", $sale_off_end_date );

							if ( $sale_off_from && $sale_off_to ) {
								$sale_off_start_time 	= strtotime( $sale_off_from, absint( $sale_off_start_date ) );
								$sale_off_end_time 		= strtotime( $sale_off_to, absint( $sale_off_end_date ) );
							}
		
						}

						$args = array(
							'target_date' 			=> $target_date,
							'target_time' 			=> $start_date,
							'time_slots' 			=> $time_slots,
							'service_id' 			=> $first_cart_item->get_service_id(),
							'business_id' 			=> $first_cart_item->get_business_id(),
							'vendor_id' 			=> $first_cart_item->get_vendor_id(),
							'sale_off_start_time' 	=> $sale_off_start_time,
							'sale_off_end_time' 	=> $sale_off_end_time,
							'percent_sale_off' 		=> $percent_sale_off,

						);

						obp_get_template("my-business/single-business/popup-form/calendar-content.php", $args );
						wp_die();
					}
				}
			}

			obp_get_template("my-business/single-business/popup-form/booking-error.php" );
			wp_die();
		}

		public function obp_booking_come_back(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
	
				$first_cart_item = OBP()->cart->get_first_item();

				if ( ! empty( $first_cart_item ) ) {
					$plan_id 		= $first_cart_item->get_plan_id();
					$plan 			= obp_get_plan( $plan_id );
					$business_id 	= $first_cart_item->get_business_id();
					$start_date 	= $first_cart_item->get_start_date();
					$business 		= obp_get_business( $business_id );
					$plan_time_type = $plan->get_time_type();
					$service_id 	= $first_cart_item->get_service_id();

					$target_date 	= gmdate("Y-m-d", absint( $start_date ) );
					$date_timestamp = strtotime( $target_date );

					$weekday 		= gmdate("w",$date_timestamp);
					$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

					$weekday_work_hours = $business->get_business_hours();
					$business_hours 	= isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

					// Custom Time
					if ( $plan->has_special_service() == 'yes' &&
					in_array( $service_id , $plan->get_custom_service_ids() ) && ! empty( $plan->get_time_from_service( $service_id ) ) ) {
						$business_hours = $plan->get_time_from_service( $service_id );
					} else {
						if ( $plan_time_type !== 'full_time' ) {
							$business_hours = $plan->get_times();
						}
					}

					if ( ! empty( $business_hours ) ) {
						$first_cart_item = OBP()->cart->get_first_item();
						$time_slots = OBP()->cart->get_timeslots_first_item( $business_hours, $date_timestamp );
						$service_id = $first_cart_item->get_service_id();
						// Sale Off
						$service 				= obp_get_service( $service_id );
						$sale_off_start_date 	= $service->get_sale_off_start_date();
						$sale_off_end_date 		= $service->get_sale_off_end_date();
						$sale_off_from 			= $service->get_sale_off_from();
						$sale_off_to 			= $service->get_sale_off_to();
						$sale_off_start_time 	= 0;
						$sale_off_end_time 		= 0;
						$percent_sale_off 		= $service->get_percent_sale_off();

						if ( $sale_off_start_date && $sale_off_end_date ) {

							$sale_off_start_time 	= $sale_off_start_date;
							$sale_off_end_time 		= strtotime("+1 day", $sale_off_end_date );

							if ( $sale_off_from && $sale_off_to ) {
								$sale_off_start_time 	= strtotime( $sale_off_from, $sale_off_start_date );
								$sale_off_end_time 		= strtotime( $sale_off_to, $sale_off_end_date );
							}
						}

						$args = array(
							'target_date' 			=> $target_date,
							'target_time' 			=> $start_date,
							'time_slots' 			=> $time_slots,
							'service_id' 			=> $first_cart_item->get_service_id(),
							'business_id' 			=> $first_cart_item->get_business_id(),
							'vendor_id' 			=> $first_cart_item->get_vendor_id(),
							'sale_off_start_time' 	=> $sale_off_start_time,
							'sale_off_end_time' 	=> $sale_off_end_time,
							'percent_sale_off' 		=> $percent_sale_off,
						);

						obp_get_template("my-business/single-business/popup-form/booking-content.php", $args );
						wp_die();
					}
				}
			}
			obp_get_template( "my-business/single-business/popup-form/booking-error.php" );
			wp_die();
		}

		public function obp_booking_remove_item(){
			
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_booking_nonce' ) ) {
				$service_id = isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : '';
				if ( $service_id ) {
					OBP()->cart->remove_item( $service_id );
				}
			}
			$current_timestamp 	= absint( current_time( 'timestamp' ) );
			$first_cart_item 	= OBP()->cart->get_first_item();

			if ( ! empty( $first_cart_item ) ) {
				$plan_id 		= $first_cart_item->get_plan_id();
				$plan 			= obp_get_plan( $plan_id );
				$business_id 	= $first_cart_item->get_business_id();
				$start_date 	= absint( $first_cart_item->get_start_date() );
				$end_date 		= absint( $first_cart_item->get_end_date() );
				$staff_id 		= $first_cart_item->get_staff_id();
				$business 		= obp_get_business( $business_id );
				$plan_time_type = $plan->get_time_type();
				$service_id 	= $first_cart_item->get_service_id();
				$duration 		= absint( $first_cart_item->get_duration() );
				$total_timestamp = OBP()->cart->get_total_timestamp();
				$time_slots 	= array();
				$target_date 	= gmdate("Y-m-d", $start_date );
				$date_timestamp = strtotime( $target_date );

				$weekday 		= gmdate("w",$date_timestamp);
				$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

				$weekday_work_hours = $business->get_business_hours();
				$business_hours 	= isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

				// Custom Time
				if ( $plan->has_special_service() == 'yes' &&
				in_array( $service_id , $plan->get_custom_service_ids() ) && ! empty( $plan->get_time_from_service( $service_id ) ) ) {
					$business_hours = $plan->get_time_from_service( $service_id );
				} else {
					if ( $plan_time_type !== 'full_time' ) {
						$business_hours = $plan->get_times();
					}
				}

				if ( ! empty( $business_hours ) ) {
					$time_slots = OBP()->cart->get_timeslots_first_item( $business_hours, $date_timestamp );
					
					if ( count( $time_slots ) > 0 ) {

						$from = absint( $time_slots[0] );

						$to = $from + $duration;

						// Check time another items
						$cart_content = OBP()->cart->content;

						// first item timeslot
						$time_slot_items = array(
							array(
								'start_date' 	=> $from,
								'end_date' 		=> $to,
							)
						);

						$data_update_cart = array(
							array(
								'start_date' 	=> $from,
								'end_date' 		=> $to,
								'staff_id' 		=> $staff_id,
								'plan_id' 		=> $plan_id,
							),
						);

						if ( count( $cart_content ) > 1 ) {
							foreach ( $cart_content as $cart_key => $cart_item ) {
								// ignore first item
								if ( $cart_key === 0 ) {
									continue;
								}

								$data = OBP()->cart->update_cart_items( $cart_item, $plan_id, $business_hours, $time_slot_items, $date_timestamp );
								if ( ! empty( $data ) ) {
									$data_update_cart[] = $data;
									$time_slot_items[] = array(
										'start_date' 	=> $data['start_date'],
										'end_date' 		=> $data['end_date'],
									);
								} else {

									OBP()->cart->remove_cart();
									obp_get_template("my-business/single-business/popup-form/booking-error.php");
									wp_die();
								}
							}
						}
						
						if ( count( $data_update_cart ) > 0 ) {
							OBP()->cart->update_cart( $data_update_cart );
						}
						// Load first item
						$first_cart_item = OBP()->cart->get_first_item();

						// Sale Off
						$service_id 			= $first_cart_item->get_service_id();
						$service 				= obp_get_service( $service_id );
						$sale_off_start_date 	= $service->get_sale_off_start_date();
						$sale_off_end_date 		= $service->get_sale_off_end_date();
						$sale_off_from 			= $service->get_sale_off_from();
						$sale_off_to 			= $service->get_sale_off_to();
						$sale_off_start_time 	= 0;
						$sale_off_end_time 		= 0;
						$percent_sale_off 		= $service->get_percent_sale_off();

						if ( $sale_off_start_date && $sale_off_end_date ) {

							$sale_off_start_time 	= $sale_off_start_date;
							$sale_off_end_time 		= strtotime("+1 day", $sale_off_end_date );

							if ( $sale_off_from && $sale_off_to ) {
								$sale_off_start_time 	= strtotime( $sale_off_from, $sale_off_start_date );
								$sale_off_end_time 		= strtotime( $sale_off_to, $sale_off_end_date );
							}
				
						}

						$args = array(
							'target_date' 			=> $target_date,
							'target_time' 			=> $from,
							'time_slots' 			=> $time_slots,
							'service_id' 			=> $first_cart_item->get_service_id(),
							'business_id' 			=> $first_cart_item->get_business_id(),
							'vendor_id' 			=> $first_cart_item->get_vendor_id(),
							'sale_off_start_time' 	=> $sale_off_start_time,
							'sale_off_end_time' 	=> $sale_off_end_time,
							'percent_sale_off' 		=> $percent_sale_off,
						);

						obp_get_template("my-business/single-business/popup-form/calendar-content.php", $args );
						wp_die();
					}
					
				}
			}
			
			wp_die();
		}

		public function obp_booking_continue(){
			$response = array(
				'message' 			=> '',
				'status' 			=> 'error',
				'url' 				=> '',
				'callback' 			=> '',
				'data' 				=> array(),
			);

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_booking_nonce' ) ) {

				$payment_gateways 			= OBP()->payment_gateways();
				$available_payment_gateways = $payment_gateways->get_available_payment_gateways();
				$count_payment_methods 		= count( $available_payment_gateways );

				if ( $count_payment_methods < 1 ) {
					$response['message'] = esc_html__( 'Payment gateway not found', 'ovabookpro' );
					wp_send_json( $response );
				}
				
				$check_cart = OBP()->cart->check_cart_valid();

				if ( ! $check_cart ) {
					OBP()->cart->remove_cart();

					$response['message'] = esc_html__( 'Invalid service please try again.', 'ovabookpro' );
					wp_send_json( $response );
				}

				try {

					$order_id 	= OBP_Order::create_order();

					$order = obp_get_order( $order_id );
					$order_total = $order->get_total();

					if ( $order_total == 0 ) {
						// Show checkout form
						$response = array_merge( $response, OBP_Checkout::processing( $order_id ) );
					} else {

						if ( $count_payment_methods < 2 && array_key_exists( 'woocommerce', $available_payment_gateways ) ) {
							$response = array_merge( $response, $available_payment_gateways['woocommerce']->process_payment( $order_id ) );
						} else {
							// Show checkout form
							$response = array_merge( $response, OBP_Checkout::processing( $order_id ) );
						}
					}

				} catch (Exception $e) {
					$response['message'] = $e->getMessage();
				}

				$response = apply_filters( 'obp_booking_continue_response', $response );
			}
			wp_send_json( $response );
			wp_die();
		}

		public function obp_booking_popup(){
		
			
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_booking_popup' ) ) {

				$service_id 		= isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : '';
				$business_id 		= isset( $_POST['business_id'] ) ? sanitize_text_field( wp_unslash( $_POST['business_id'] ) ) : '';
				$vendor_id 			= isset( $_POST['vendor_id'] ) ? sanitize_text_field( wp_unslash( $_POST['vendor_id'] ) ) : '';

				OBP()->cart->remove_cart();

				$this->add_to_cart( $service_id, $business_id, $vendor_id );
			}

			obp_get_template( "my-business/single-business/popup-form/content-none.php" );
		
			wp_die();
		}

		public function add_to_cart( $service_id, $business_id, $vendor_id ){
			$current_timestamp = absint( current_time('timestamp') );
			// Plans
			$service 	= obp_get_service( $service_id );
			$staff_ids 	= $service->get_staff_ids();
			$plan_ids 	= OBP_Plan::get_plan_ids_by_service_id( $vendor_id, $service_id );

			if ( empty( $plan_ids ) ) {
				obp_get_template("my-business/single-business/popup-form/plan-none.php");
			
				wp_die();
			}

			if ( empty( $staff_ids ) ) {
				obp_get_template("my-business/single-business/popup-form/staff-none.php");
			
				wp_die();
			}

			// Duration
			$duration 	= absint( $service->get_duration() );
			$business 	= obp_get_business( $business_id );
			$weekday_work_hours = $business->get_business_hours();
			$keys = array_keys($weekday_work_hours);
	
			foreach ( $plan_ids as $plan_id ) {
				$plan 		= obp_get_plan( $plan_id );
				$start_date = absint( $plan->get_start_date() );
				$end_date 	= absint( $plan->get_end_date() );
				
				while ( $start_date <= ( absint( strtotime("+1 day", $end_date ) ) - 1 ) ) {
					
					if ( strtotime("+1 day", $start_date) > $current_timestamp ) {

						$target_date 	= gmdate( "Y-m-d", $start_date );
						$date_timestamp = absint( strtotime( $target_date ) );

						$weekday 		= gmdate("w",$start_date);
						$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];


						$plan_time_type 	= $plan->get_time_type();
						
						$staff_id_exclude = array();

						while ( count( $staff_ids ) - count( $staff_id_exclude ) > 0 ) {
							// Get Staff ID
							$staff_id = OBP_Staff::get_priority_staff_id( $service_id, $date_timestamp, $staff_id_exclude );

							$business_hours = isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

							// Custom Time
							if ( $plan->has_special_service() == 'yes' &&
							in_array( $service_id , $plan->get_custom_service_ids() ) && ! empty( $plan->get_time_from_service( $service_id ) ) ) {
								$business_hours = $plan->get_time_from_service( $service_id );
							} else {
								if ( $plan_time_type !== 'full_time' ) {
									$business_hours = $plan->get_times();
								}
							}
							
							if ( ! empty( $staff_id ) ) {

								$exclude_times = array();

								// Check Day Off
								$day_off_row = OBP_Day_Off::get_row( $staff_id, $date_timestamp );
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
										$staff_id_exclude[] = $staff_id;
										continue;
									}
								}

								// Check Order Meta & Order Holding
								$order_meta_queue_timeslots = OBP_Order_Meta_Queue::get_timeslots_in_day( $staff_id , $date_timestamp );
								$order_holding_timeslots = OBP_Order_Holding::get_timeslots_in_day( $staff_id, $date_timestamp );

								$exclude_times = array_merge( $exclude_times, $order_meta_queue_timeslots, $order_holding_timeslots );

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

											// ignore
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

								$time_slots = array();

								if ( count( $business_hours ) > 0 && in_array($weekday_key, $keys) ) {
									foreach ( $business_hours as $times ) {
										$from_time 	= $times['start_hour'];
										$to_time 	= $times['end_hour'];

										$timestamp 	= absint( strtotime( $from_time, $date_timestamp ) );
										$to_time 	= absint( strtotime( $to_time, $date_timestamp ) );
										
										while ( ( $timestamp + $duration ) <= $to_time ) {
											$_end_date = $timestamp + $duration;
											// Check time
											if ( $timestamp > $current_timestamp ) {

												$check_order_meta_queue = OBP_Order_Meta_Queue::check_timeslots( $staff_id , $timestamp, $_end_date );
												$check_order_holding = OBP_Order_Holding::check_timeslots( $staff_id , $timestamp, $_end_date );
												
												$check_timeslots = empty( $check_order_meta_queue ) && empty( $check_order_holding );

												if ( $check_timeslots ) {
													$time_slots[] = $timestamp;
												}
											}

											$timestamp = $_end_date;
										}
									}

								}

								
								if ( count( $time_slots ) > 0 ) {

									$from 			= $time_slots[0];
									$to 			= absint( $from ) + absint( $duration );
									$target_date 	= gmdate("Y-m-d",$from);

									$target_time 	= $from;

									$price = $service->get_price_specified_time( $from );

									$data = array(
										'vendor_id' 	=> $vendor_id,
										'service_id' 	=> $service_id,
										'staff_id' 		=> $staff_id,
										'start_date' 	=> $from,
										'end_date' 		=> $to,
										'price' 		=> $price,
										'duration' 		=> $duration,
										'plan_id' 		=> $plan_id,
										'business_id' 	=> $business_id,
									);

									OBP()->cart->add_item( $data );
									// Sale Off
									$service = obp_get_service( $service_id );
									$sale_off_start_date 	= $service->get_sale_off_start_date();
									$sale_off_end_date 		= $service->get_sale_off_end_date();
									$sale_off_from 			= $service->get_sale_off_from();
									$sale_off_to 			= $service->get_sale_off_to();
									$sale_off_start_time 	= 0;
									$sale_off_end_time 		= 0;
									$percent_sale_off 		= $service->get_percent_sale_off();

									if ( $sale_off_start_date && $sale_off_end_date ) {

										$sale_off_start_time 	= $sale_off_start_date;
										$sale_off_end_time 		= strtotime("+1 day", $sale_off_end_date );

										if ( $sale_off_from && $sale_off_to ) {
											$sale_off_start_time 	= strtotime( $sale_off_from, $sale_off_start_date );
											$sale_off_end_time 		= strtotime( $sale_off_to, $sale_off_end_date );
										}
									}

									$args = array(
										'target_date' 			=> $target_date,
										'target_time' 			=> $target_time,
										'time_slots' 			=> $time_slots,
										'service_id' 			=> $service_id,
										'business_id' 			=> $business_id,
										'vendor_id' 			=> $vendor_id,
										'sale_off_start_time' 	=> $sale_off_start_time,
										'sale_off_end_time' 	=> $sale_off_end_time,
										'percent_sale_off' 		=> $percent_sale_off,
									);

									obp_get_template("my-business/single-business/popup-form.php", $args );

								
									wp_die();
									break;
								}

							} else {
								break;
							}

							$staff_id_exclude[] = $staff_id;
						}


					}

					$start_date = strtotime("+1 day", $start_date );
					
				}
				
			}
		}

		public function obp_booking_next_calendar(){
			global $wp_locale;

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				
				$end_date 		= isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
				$target_date 	= isset( $_POST['target_date'] ) ? sanitize_text_field( wp_unslash( $_POST['target_date'] ) ) : '';
				$business_id 	= isset( $_POST['business_id'] ) ? sanitize_text_field( wp_unslash( $_POST['business_id'] ) ) : '';

				if ( ! empty( $end_date ) ) {
					$business 		= obp_get_business( $business_id );
					$business_hours = $business->get_business_hours();
					$keys 			= array_keys( $business_hours );
					$weekday 		= obp_get_weekday();

					$timestamp = strtotime( $end_date );
					$year_month = gmdate("Y-m", $timestamp);
					list($year, $month) = explode( "-", $year_month );

					if ( $month == "12" ) {
						$year 	= (int)$year + 1;
						$month 	= "01";
					} else {
						$month = (int)$month + 1;
						$month = $month < 10 ? "0".$month : $month;
					}
					$days_in_month 	= gmdate( "t", strtotime( "$year-$month" ) );
					$days_range 	= range( 1, $days_in_month );
					$days_arr 		= array();

					foreach ( $days_range as $number ) {
						$day = $number < 10 ? "0".$number : $number;
						$days_arr[] = "$year-$month-$day";
					}
					$count_days = count( $days_arr );

					$key_last_day = $count_days - 1;
				
					if ( $count_days > 0 ) {
						?>
						<div class="owl-carousel owl-theme"
						data-month-year="<?php echo esc_attr( date_i18n( "F Y", strtotime( $days_arr[0] ) ) ); ?>"
						data-start-date="<?php echo esc_attr( $days_arr[0] ); ?>"
						data-end-date="<?php echo esc_attr( $days_arr[$key_last_day] ); ?>">
						<?php
						foreach ( $days_arr as $date ) {
							$timestamp 		= strtotime( $date );
							$weekday_number = gmdate( 'w', $timestamp );
							$weekday_key 	= isset( $weekday[$weekday_number] ) ? $weekday[$weekday_number] : '';
							$weekday_name 	= $wp_locale->get_weekday( $weekday_number );
							$weekday_abbrev = $wp_locale->get_weekday_abbrev( $weekday_name );
							$is_active 		= $target_date == $date ? 'is-active': '';
							if ( in_array( $weekday_key, $keys ) ) {
							?>
							<div class="item">
								<div class="date-card <?php echo esc_attr( $is_active ); ?>" data-date="<?php echo esc_attr( $date ); ?>">
									<div class="day"><?php echo esc_html( date_i18n( 'j', $timestamp ) ); ?></div>
									<div class="week_day"><?php echo esc_html( $weekday_abbrev ); ?></div>
								</div>
							</div>
							<?php
							}
						}
						?>
						</div>
						<?php
					}
				}

			}
			wp_die();
		}

		public function obp_booking_prev_calendar(){
			global $wp_locale;

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {

				$start_date 	= isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
				$target_date 	= isset( $_POST['target_date'] ) ? sanitize_text_field( wp_unslash( $_POST['target_date'] ) ) : '';
				$business_id 	= isset( $_POST['business_id'] ) ? sanitize_text_field( wp_unslash( $_POST['business_id'] ) ) : '';

				if ( ! empty( $start_date ) ) {
					$business 		= obp_get_business( $business_id );
					$business_hours = $business->get_business_hours();
					$keys 			= array_keys( $business_hours );
					$weekday 		= obp_get_weekday();

					$timestamp = strtotime( $start_date );
					$year_month = gmdate("Y-m", $timestamp);
					list($year, $month) = explode( "-", $year_month );

					if ( $month == "01" ) {
						$year 	= (int)$year - 1;
						$month 	= "12";
					} else {
						$month = (int)$month - 1;
						$month = $month < 10 ? "0".$month : $month;
					}
					$days_in_month 	= gmdate( "t", strtotime( "$year-$month" ) );
					$days_range 	= range( 1, (int)$days_in_month );
					$days_arr 		= array();

					$cur_timestamp = current_time('timestamp');
					$cur_date_timestamp = strtotime( gmdate("Y-m-d", $cur_timestamp ) );

					foreach ( $days_range as $number ) {
						$day = $number < 10 ? "0".$number : $number;
						$date = "$year-$month-$day";
						if ( absint( strtotime($date) ) < $cur_date_timestamp || absint( strtotime($target_date) ) - absint( strtotime($date) ) > 0 ) {
							continue;
						}

						$days_arr[] = $date;
					}
					$count_days = count( $days_arr );
					
					if ( $count_days > 0 ) {
						$data_prev = $days_arr[0] == $target_date ? 'false' : 'true';
						?>
						<div class="owl-carousel owl-theme"
						data-prev="<?php echo esc_attr( $data_prev ); ?>"
						data-month-year="<?php echo esc_attr( date_i18n( "F Y", strtotime( $days_arr[0] ) ) ); ?>"
						data-start-date="<?php echo esc_attr( $days_arr[0] ); ?>"
						data-end-date="<?php echo esc_attr( $days_arr[$count_days-1] ); ?>">
						<?php
						foreach ( $days_arr as $key => $date ) {
							$timestamp 		= strtotime( $date );
							$weekday_number = gmdate( 'w', $timestamp );
							$weekday_key 	= isset( $weekday[$weekday_number] ) ? $weekday[$weekday_number] : '';
							$weekday_name 	= $wp_locale->get_weekday( $weekday_number );
							$weekday_abbrev = $wp_locale->get_weekday_abbrev( $weekday_name );
							$is_active 		= $target_date == $date ? 'is-active': '';
							if ( in_array($weekday_key, $keys) ) {
							?>
							<div class="item">
								<div class="date-card <?php echo esc_attr( $is_active ); ?>" data-date="<?php echo esc_attr( $date ); ?>">
									<div class="day"><?php echo esc_html( date_i18n( 'j', $timestamp ) ); ?></div>
									<div class="week_day"><?php echo esc_html( $weekday_abbrev ); ?></div>
								</div>
							</div>
							<?php
							}
						}
						?>
						</div>
						<?php
					}
				}
			}
	
			wp_die();
		}


		public static function obp_booking_form_order_args( $args = array() ){
			$vendor_id = isset( $args['vendor_id'] ) ? $args['vendor_id'] : '';
			$service_ids_cart 	= OBP()->cart->get_service_ids();
			$service_ids 		= OBP_Service::get_service_ids_exclude_cart( $vendor_id ,$service_ids_cart );

			$args = array_merge( $args, array(
				'service_ids' => $service_ids,
			) );

			return apply_filters( 'obp_booking_form_order_args', $args );
		}

		public static function obp_booking_popup_staff_args( $args = array() ) {
			$service_id 		= isset( $args['service_id'] ) ? $args['service_id'] : '';
			$service 			= obp_get_service( $service_id );
			$staff_ids 			= $service->get_staff_ids();
			$cart_item 			= OBP()->cart->get_cart_item( $service_id );
			$current_staff_id 	= $cart_item->get_staff_id();


			$args = array_merge( $args, array(
				'staff_ids' 		=> $staff_ids,
				'current_staff_id' 	=> $current_staff_id,
			) );

			return apply_filters( 'obp_booking_popup_staff_args', $args );
		}

		public static function obp_booking_form_footer_args( $args = array() ){

			$subtotal 		= OBP()->cart->get_subtotal();
			$system_fee 	= OBP()->cart->get_system_fee();
			$tax_fee 		= OBP()->cart->get_tax_amount();
			$total 			= OBP()->cart->get_total();
			$total_time 	= OBP()->cart->get_total_time();
			$message 		= OBP()->cart->session->get('coupon_message');
			$coupon_code  	= OBP()->cart->session->get('coupon_code');
			$discount 		= OBP()->cart->get_discount();
			$show_tax 		= OBP()->settings->tax->get('show_tax', 'yes');

			$args = array_merge( $args, array(
				'system_fee' 	=> $system_fee,
				'tax_fee' 		=> $tax_fee,
				'show_tax' 		=> $show_tax,
				'total' 		=> $total,
				'total_time' 	=> $total_time,
				'coupon_code' 	=> $coupon_code,
				'discount' 		=> $discount,
				'message' 		=> $message,
				'subtotal' 		=> $subtotal,
			) );

			return apply_filters( 'obp_booking_form_footer_args', $args );
		}

		public static function obp_booking_form_calendar_args( $args = array() ) {
			$current_timestamp 	= current_time('timestamp');
			$current_date 		= gmdate("Y-m-d",$current_timestamp);

			$target_date = isset( $args['target_date'] ) ? $args['target_date'] : '';
			$target_time = isset( $args['target_time'] ) ? $args['target_time'] : '';

			list($year, $month,$day) = explode( "-", $target_date );

			$days_in_month 	= gmdate( "t", $target_time );
			$days_range 	= range( 1, $days_in_month );
			$days_arr 		= array();

			foreach ( $days_range as $key => $number ) {
				$d = $number < 10 ? "0".$number : $number;
				$date_ymd = "$year-$month-$d";
				if ( strtotime( $date_ymd ) >= strtotime( $current_date ) ) {
					$days_arr[] = $date_ymd;
				}
			}

			while ( ! in_array( $target_date, $days_arr ) ) {

				list($year, $month,$day) = explode( "-", $target_date );

				$days_in_month = gmdate("t", strtotime( $target_date ) );

				$days_range = range( 1, $days_in_month );

				$days_arr = array();

				foreach ( $days_range as $key => $number ) {
					$d = $number < 10 ? "0".$number : $number;
					$date_ymd = "$year-$month-$d";

					if ( strtotime( $date_ymd ) >= strtotime( $current_date ) ) {
						$days_arr[] = $date_ymd;
					}

				}
			}

			if ( count( $days_arr ) == 1 ) {
				$next_day = absint( strtotime( $days_arr[0] ) ) + 86400;
				$days_next_month = absint( gmdate("t", $next_day ) );
				for ($number=1; $number <= $days_next_month; $number++) { 
					$d = $number < 10 ? "0".$number : $number;
					$date_ymd = "$year-$month-$d";
					$days_arr[] = $date_ymd;
				}
			}

			$data_prev = $days_arr[0] == $current_date ? 'false' : 'true';

			$count_days_arr = count( $days_arr );
			$key_last_day 	= $count_days_arr - 1;
			$data_end_date 	= isset( $days_arr[$key_last_day] ) ? $days_arr[$key_last_day] : '';

			$args = array_merge( $args, array(
				'days_arr' 		=> $days_arr,
				'target_date' 	=> $target_date,
				'data_prev' 	=> $data_prev,
				'data_end_date' => $data_end_date,
			) );

			return apply_filters( 'obp_booking_form_calendar_args', $args );
		}

		public static function obp_booking_form_time_slider_args( $args = array() ) {
			$business_id = isset( $args['business_id'] ) ? $args['business_id'] : '';
			$target_date = isset( $args['target_date'] ) ? $args['target_date'] : '';
			// Time Format
			$time_format 	= OBP()->settings->general->get('time_format','H:i');
			$last_cart_item = OBP()->cart->get_last_item();
			$duration 		= $last_cart_item->get_duration();
			$work_hours 	= get_post_meta( $business_id, OBP_METABOX.'work_hours', true );
			$date_timestamp = strtotime( $target_date );

			$args = array_merge( $args, array(
				'work_hours' 		=> $work_hours,
				'date_timestamp' 	=> $date_timestamp,
				'time_format' 		=> $time_format,
			) );

			return apply_filters( 'obp_booking_form_time_slider_args', $args );
		}

		public static function obp_booking_form_order_item_args( $args = array() ) {
			$time_format 	= OBP()->settings->general->get('time_format','H:i');
			$cart_content 	= OBP()->cart->content;

			$args = array_merge( $args, array(
				'time_format' 	=> $time_format,
				'cart_content' 	=> $cart_content,
			) );

			return apply_filters( 'obp_booking_form_order_item_args', $args );
		}

	}

}