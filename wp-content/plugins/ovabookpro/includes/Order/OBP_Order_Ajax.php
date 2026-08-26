<?php 
namespace BookPro\Order;

use BookPro\OBP_Mail;
use BookPro\OBP_Calendar;
use BookPro\Traits\SingletonTrait;
use BookPro\Cart\OBP_Cart_Order;
use BookPro\Order\OBP_Order_Meta;
use BookPro\StaffDayOff\OBP_Day_Off;
use BookPro\Order\OBP_Order_Meta_Queue;
use BookPro\Order\OBP_Order_Holding;
use BookPro\Plan\OBP_Plan;
use BookPro\Payout\OBP_Payout_Method_Info;
use BookPro\Payout\OBP_Payout_Method;
USE BookPro\Payout\OBP_Payout;
use BookPro\Staff\OBP_Staff;


defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Order_Ajax') ) {
	class OBP_Order_Ajax {
		
		use SingletonTrait;
		/**
		 * @var bool
		 */
		protected static $_loaded = false;

		public function __construct(){

			if ( self::$_loaded ) {
				return;
			}
			
			if (!defined('DOING_AJAX') || !DOING_AJAX)
				return;

			$this->init();

			self::$_loaded = true;
		}

		public function init(){

			$arr_ajax =  array(
				'obp_my_booking_load_data',
				'obp_my_booking_download',
				'obp_my_booking_cancel_order',
				'obp_order_change_schedule',
				'obp_order_change_next_calendar',
				'obp_order_change_prev_calendar',
				'obp_order_change_calendar',
				'obp_order_change_time',
				'obp_order_change_come_back',
				'obp_order_change_sort_item',
				'obp_order_change_update',
				'obp_order_change_staff',
				'obp_order_change_update_staff',
				'obp_order_change_empty_cart',
				'obp_manager_orders_load_data',
				'obp_manager_orders_export_popup',
				'obp_manager_orders_export_data',
				'obp_order_detail_popup',
			);

			foreach($arr_ajax as $val){
				add_action( 'wp_ajax_'.$val, array( $this, $val ) );
				add_action( 'wp_ajax_nopriv_'.$val, array( $this, $val ) );
			}
		}

		public function obp_order_detail_popup(){
		
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && current_user_can( 'my_booking' ) ) {
				$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
				$order = obp_get_order( $order_id );
		
				obp_get_template( 'manage-booking/order-detail-popup.php', array( 'order' => $order ) );
			}

	
			wp_die();
		}

		public function obp_manager_orders_export_data(){
			$response = [];
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				$orders = OBP_Order::get_orders_data_export();
				obp_download_send_headers("data_export_" . gmdate("Y-m-d") . ".csv");
				$response['data'] = obp_array2csv($orders);
				$response['file_name'] = "data_export_" . gmdate("Y-m-d") . ".csv";
			}
			wp_send_json( $response );
		}

		public function obp_manager_orders_export_popup(){
		
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				obp_get_template( 'manage-booking/export-popup.php' );
			}
	
			wp_die();
		}

		public function obp_my_booking_refund_submit(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {

				$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';

				// Update payout method info for current user
		
				$order 		= obp_get_order( $order_id );
				$user_id 	= $order->get_customer_id();
				$user 		= obp_get_user( $user_id );
				
				$amount = $order->get_total();
				$user->add_balance_amount( $amount );

				// Send mail cancel
				$send_mail_to_admin 	= OBP()->settings->mail->get('cancel_admin_send_mail', 'yes');
				$send_mail_to_customer 	= OBP()->settings->mail->get('cancel_customer_send_mail', 'yes');

				if ( $send_mail_to_admin == 'yes' ) {
					OBP_Mail::obp_cancel_order_admin_mail( $order );
				}

				if ( $send_mail_to_customer == 'yes' ) {
					OBP_Mail::obp_cancel_order_customer_mail( $order );
				}

				do_action( 'obp_my_booking_refund_submit', $order );

				// Delete order meta queue
				OBP_Order_Meta_Queue::delete_by_order_id( $order_id );
				// Delete order balance
				OBP_Order_Balance::delete_by_order_id( $order_id );
				$order->set_balance_status("obp_completed");
				$order->set_payout_status("obp_completed");
				$order->set_order_status("obp_cancelled");
			
			}
			wp_die();
		}

		public function obp_my_booking_change_payout_method(){
	
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				$payout_method_id = isset( $_POST['payout_method_id'] ) ? sanitize_text_field( wp_unslash( $_POST['payout_method_id'] ) ) : '';
				$args = array(
					'payout_method_id' => $payout_method_id,
				);
			
				obp_get_template("my-booking/payout-method-field.php", $args );
		
	
			}
	
			wp_die();
		}

		public function obp_my_booking_cancel_order(){
	
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';

				// Update payout method info for current user
		
				$order 		= obp_get_order( $order_id );
				$user_id 	= $order->get_customer_id();
				$user 		= obp_get_user( $user_id );
				
				$amount = $order->get_total();
				$user->add_balance_amount( $amount );

				// Send mail cancel
				$send_mail_to_admin 	= OBP()->settings->mail->get('cancel_admin_send_mail', 'yes');
				
				$send_mail_to_customer 	= OBP()->settings->mail->get('cancel_customer_send_mail', 'yes');

				if ( $send_mail_to_admin == 'yes' ) {
					OBP_Mail::obp_cancel_order_admin_mail( $order );
				}

				if ( $send_mail_to_customer == 'yes' ) {
					OBP_Mail::obp_cancel_order_customer_mail( $order );
				}

				do_action( 'obp_my_booking_cancel_order', $order );

				// Delete order meta queue
				OBP_Order_Meta_Queue::delete_by_order_id( $order_id );
				// Delete order balance
				OBP_Order_Balance::delete_by_order_id( $order_id );
				$order->set_balance_status("obp_completed");
				$order->set_payout_status("obp_completed");
				$order->set_order_status("obp_cancelled");
			}
		
			wp_die();
		}

		public function obp_my_booking_load_data(){
			$response = array(
				'table_html' 		=> '',
				'pagination_html' 	=> '',
			);

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {

				$orders = OBP_Order::instance()->get_orders_ajax();

				// Table Html
				ob_start();
				if ( $orders->have_posts() ): ?>
				<?php while ( $orders->have_posts() ) :
					$orders->the_post();
					obp_get_template( "my-booking/order-table-item.php" );
				endwhile;
				else:
					?>
					<tr>
						<td colspan="6"><?php esc_html_e( 'Bookings not found.', 'ovabookpro' ); ?></td>
					</tr>
					<?php
				endif;
				wp_reset_postdata();

				$response['table_html'] = ob_get_contents();
				ob_end_clean();

				// Pagination Html
				ob_start();
				obp_get_template( "my-booking/order-pagination.php", array( 'orders' => $orders ) );
				$response['pagination_html'] = ob_get_contents();
				ob_end_clean();

			}

			wp_send_json( $response );
		}

		public function obp_my_booking_download(){
			$response = array();
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				$order_id 	= isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
				$order_pdf 	= new OBP_Order_PDF();
				$file_path 	= $order_pdf->make_pdf_invoice( $order_id );
				$file_name 	= basename( $file_path );
				$upload_dir = wp_upload_dir();
				$file_url 	= trailingslashit( $upload_dir['baseurl'] ).'invoices/'.$file_name;

				$response['file_url'] 	= $file_url;
				$response['file_name'] 	= $file_name;
			}
			wp_send_json( $response );
		}

		public function obp_order_change_empty_cart(){
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_order_nonce' ) ) {
				$cart = new OBP_Cart_Order();
				$cart->remove_cart();
			}
			echo "";
			wp_die();
		}

		public function obp_order_change_update_staff(){
			$cart = new OBP_Cart_Order();
			
			// Get timeslot valid for staff selected
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_order_staff' ) ) {

				$staff_id 	= isset( $_POST['staff_id'] ) ? sanitize_text_field( wp_unslash( $_POST['staff_id'] ) ) : '';
				$service_id = isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : '';
				$order_id 	= isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
				
				if ( $service_id ) {
					$cart_item 			= $cart->get_cart_item( $service_id );
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

					if ( $plan_time_type !== 'full_time' ) {
						$business_hours = $plan->get_times();
					}

					// Get order timestamp
					$order_meta_items = array();
					$order_item_data = array();
					$order = obp_get_order( $order_id );
					$order_start_date = $order->get_service_start_date_earliest();
					$order_datetimestamp = strtotime( gmdate( "Y-m-d", $order_start_date ) );

					if ( $order_datetimestamp == $date_timestamp ) {
						$order_meta_items = OBP_Order_Meta::get_order_items( $order_id );
					}
					
					if ( count( $order_meta_items ) > 0 ) {
						foreach ( $order_meta_items as $item ) {

							$found_key = array_search( $item->staff_id, array_column($order_item_data, 'staff_id'));

							if ( $found_key === false ) {
								$order_item_data[] = array(
									'staff_id' 		=> $item->staff_id,
									'timestamp' 	=> array(
										array(
											'start_date' 	=> $item->start_date,
											'end_date' 		=> $item->end_date
										)
									),
								);
							} else {
								$order_item_data[$found_key]['timestamp'][] = array(
									'start_date' 	=> $item->start_date,
									'end_date' 		=> $item->end_date
								);
							}
							
						}
					}

					// Check if the required time is valid or not
					$check_order_holding 	= OBP_Order_Holding::check_timeslots( $staff_id , $start_date, $end_date );
					$check_order_meta_queue = OBP_Order_Meta_Queue::check_timeslots( $staff_id , $start_date, $end_date );

					$order_timestamp = array();
					if ( count( $order_item_data ) > 0 ) {
						$found_key = array_search($staff_id, array_column($order_item_data, 'staff_id'));
						if ( $found_key !== false ) {
							$order_timestamp = $order_item_data[$found_key]['timestamp'];

							foreach ( $order_timestamp as $timestamp ) {
								if ( ( $start_date >= $timestamp['start_date'] && $start_date < $timestamp['end_date'] ) || ( $end_date > $timestamp['start_date'] && $end_date <= $timestamp['end_date'] ) ) {
									$check_order_holding 	= array();
									$check_order_meta_queue = array();
									break;
								}
							}
						}
					}
					
					$check_timeslots = empty( $check_order_holding ) && empty( $check_order_meta_queue );
					
					if ( ! $check_timeslots ) {

						obp_get_template("my-booking/popup-form/staff-error.php");
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
								unset($value['start_hour']);
								unset($value['end_hour']);
								return $value;
							}, $hour_off );

							$exclude_times = array_merge( $exclude_times, $timestamp_off );
						} else {
							obp_get_template("my-booking/popup-form/staff-error.php");
					
							wp_die();
						}
					}

					// Check Order Meta & Order Holding
					$order_meta_queue_timeslots = OBP_Order_Meta_Queue::get_timeslots_in_day( $staff_id , $date_timestamp );
					$order_holding_timeslots = OBP_Order_Holding::get_timeslots_in_day( $staff_id, $date_timestamp );

					$exclude_times = array_merge( $exclude_times, $order_meta_queue_timeslots, $order_holding_timeslots );

					if ( count( $exclude_times ) > 0 && count( $order_timestamp ) > 0 ) {
						$exclude_times = array_udiff($exclude_times, $order_timestamp, function($a,$b){
							if ($a['start_date'] == $b['start_date'] && $a['end_date'] == $b['end_date']) {
						        return 0;
						    }
						    return ($a['start_date'] < $b['start_date']) ? -1 : 1;
						});

						usort($exclude_times, function( $a, $b ){
							return (int)$a['start_date'] - (int)$b['start_date'];
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
								
								$cart->update_staff( $service_id, $staff_id );

								// Update cart
								$first_cart_item = $cart->get_first_item();

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
									if ( $plan_time_type !== 'full_time' ) {
										$business_hours = $plan->get_times();
									}
									$time_slots = $cart->get_timeslots_first_item( $business_hours, $date_timestamp, $order_item_data );

									// If change staff first item
									if ( $first_cart_item->get_service_id() == $service_id ) {
										
										if ( ! empty( $business_hours ) ) {
											$first_cart_item = $cart->get_first_item();
											$time_slots = $cart->get_timeslots_first_item( $business_hours, $date_timestamp, $order_item_data );

											if ( ! empty( $time_slots ) ) {
												$from = $time_slots[0];
												$to = (int)$from + (int)$duration;

												$cart_content = $cart->content;

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

														$data = $cart->update_cart_items( $cart_item, $plan_id, $business_hours, $time_slot_items, $date_timestamp, $order_item_data );
														if ( ! empty( $data ) ) {
															$data_update_cart[] = $data;
															$time_slot_items[] = array(
																'start_date' 	=> $data['start_date'],
																'end_date' 		=> $data['end_date'],
															);
														} else {
															$cart->update_staff( $service_id, $old_staff_id );
															obp_get_template("my-booking/popup-form/staff-error.php");
												
															wp_die();
															break;
														}
													}
												}

												if ( count( $data_update_cart ) > 0 ) {
													$cart->update_cart( $data_update_cart );
												}

												$first_cart_item = $cart->get_first_item();

												$args = array(
													'target_date' 	=> $target_date,
													'target_time' 	=> $from,
													'time_slots' 	=> $time_slots,
													'service_id' 	=> $first_cart_item->get_service_id(),
													'business_id' 	=> $first_cart_item->get_business_id(),
													'vendor_id' 	=> $first_cart_item->get_vendor_id(),
													'order_id' 		=> $order_id,
												);

												obp_get_template("my-booking/popup-form/calendar-content.php", $args );
										
												wp_die();
											}
										}

									}


									if ( ! in_array( $first_cart_item->get_start_date(), $time_slots ) ) {
										$time_slots[] = $first_cart_item->get_start_date();
										usort( $time_slots , function($a,$b){
											return absint( $a ) - absint( $b );
										} );
									}

									$args = array(
										'target_date' 	=> $target_date,
										'target_time' 	=> $first_cart_item->get_start_date(),
										'time_slots' 	=> $time_slots,
										'service_id' 	=> $first_cart_item->get_service_id(),
										'business_id' 	=> $first_cart_item->get_business_id(),
										'vendor_id' 	=> $first_cart_item->get_vendor_id(),
										'order_id' 		=> $order_id,
									);

									obp_get_template("my-booking/popup-form/calendar-content.php", $args );
									wp_die();
								}
							}
						}
					}
					// 

					obp_get_template("my-booking/popup-form/staff-error.php");
					wp_die();
				}

			}
			obp_get_template("my-booking/popup-form/staff-none.php");
			wp_die();
		}

		public function obp_order_change_staff(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_order_nonce' ) ) {
				$service_id = isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : '';
				$order_id 	= isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';

				if ( $service_id ) {
					$args = array(
						'service_id' 	=> $service_id,
						'order_id' 		=> $order_id,
					);
					obp_change_order_popup_staff( $args );
				}
				
			}
			wp_die();
		}

		public function obp_order_change_update(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_order_nonce' ) ) {
				$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
				if ( $order_id ) {
					$order 					= obp_get_order( $order_id );
					$order_status 			= $order->get_order_status();
					$order_balance_status 	= $order->get_balance_status();
					$timestamp_check 		= $order->get_service_start_date_earliest();

					$current_time 			= current_time( 'timestamp' );
					$before_minutes 		= OBP()->settings->change_order->get('change_order_before_time',120);
					$before_minutes 		= absint( $before_minutes );
					$check_time 			= absint( $current_time ) + ( $before_minutes * 60 );
					$limited 				= OBP()->settings->change_order->get('change_order_limited', 1 );
					$allow_change_order 	= OBP()->settings->change_order->get('change_order_enable', 'yes' );
					$number_change_order 	= $order->get_number_change_order();
					$check_order_change 	= $order->get_allow_change();

					if ( in_array( $order_status, array( 'obp_processing', 'obp_completed' ) ) &&
						$order_balance_status === 'obp_pending' &&
						$check_time < $timestamp_check && $number_change_order < $limited &&
						$check_order_change === "yes" &&
						$allow_change_order === "yes" ) {
						$cart = new OBP_Cart_Order();
						$cart_content = $cart->content;
						$start_date_earliest = $cart->get_start_date_earliest();

						// Delete Order Meta
						OBP_Order_Meta::delete_by_order_id( $order_id );

						// Delete Order Holding
						OBP_Order_Holding::delete_order_holding( $order_id );

						// Delete Order Meta Queue
						OBP_Order_Meta_Queue::delete_by_order_id( $order_id );

						// Update Order Balance
						$order_balace_row = OBP_Order_Balance::get_row_by_order_id( $order_id );

						if ( ! empty( $order_balace_row ) ) {
							$order_balance_id = $order_balace_row['id'];
							OBP_Order_Balance::update_start_date( $order_balance_id, $start_date_earliest );

							if ( count( $cart_content ) > 0 ) {
								foreach ( $cart_content as $item ) {
									$data = array(
										'vendor_id' 		=> $item->get_vendor_id(),
										'order_id' 			=> $order_id,
										'service_id' 		=> $item->get_service_id(),
										'staff_id' 			=> $item->get_staff_id(),
										'customer_id' 		=> $order->get_customer_id(),
										'start_date' 		=> $item->get_start_date(),
										'end_date' 			=> $item->get_end_date(),
										'price' 			=> obp_calculate_inc_tax( $item->get_price(), $item->get_rates() ),
										'order_balance_id' 	=> $order_balance_id,
									);
									// Add Order Meta Queue
									OBP_Order_Meta_Queue::add( $data );
								}
							}
						} else {
							// Add Order Holding
							if ( count( $cart_content ) > 0 ) {
								foreach ( $cart_content as $item ) {
									$data = array(
										'vendor_id' 		=> $item->get_vendor_id(),
										'order_id' 			=> $order_id,
										'service_id' 		=> $item->get_service_id(),
										'staff_id' 			=> $item->get_staff_id(),
										'start_date' 		=> $item->get_start_date(),
										'end_date' 			=> $item->get_end_date(),
									);
									// Add Order Meta Queue
									OBP_Order_Holding::add( $data );
								}
							}
						}
						
						if ( count( $cart_content ) > 0 ) {
							foreach ( $cart_content as $item ) {
								$data = array(
									'vendor_id' 		=> $item->get_vendor_id(),
									'order_id' 			=> $order_id,
									'service_id' 		=> $item->get_service_id(),
									'staff_id' 			=> $item->get_staff_id(),
									'customer_id' 		=> $order->get_customer_id(),
									'start_date' 		=> $item->get_start_date(),
									'end_date' 			=> $item->get_end_date(),
									'price' 			=> $item->get_price(),
									'duration' 			=> $item->get_duration(),
									'plan_id' 			=> $item->get_plan_id(),
									'business_id' 		=> $item->get_business_id(),
									'package_ids' 		=> maybe_serialize( $item->get_package_ids() ),
									'taxes' 			=> maybe_serialize( $item->get_data_taxes() ),
								);
								// Add Order Meta
								OBP_Order_Meta::add( $data );
							}
						}

						// Update Order
						$order->set_start_date( $start_date_earliest );
						$number_change_order = absint( $number_change_order ) + 1;
						$order->set_number_change_order( $number_change_order );

						// Send mail Change Order
						$send_to_admin 		= OBP()->settings->mail->get('change_admin_send_mail','yes');
						$send_to_customer 	= OBP()->settings->mail->get('change_customer_send_mail','yes');

						if ( $send_to_admin === 'yes' ) {
							OBP_Mail::obp_change_order_admin_mail( $order );
						}
						
						if ( $send_to_customer === 'yes' ) {
							OBP_Mail::obp_change_order_customer_mail( $order );
						}

						do_action( 'obp_order_change_update', $order );
						
						OBP()->message->add( esc_html__( 'Update order successfully.', 'ovabookpro' ) );
					}
				}
			} else {
				OBP()->message->add( esc_html__( 'Order update failed, please try again later.', 'ovabookpro' ), 'error' );
			}

			echo '';
			wp_die();
		}

		public function obp_order_change_come_back(){
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_order_nonce' ) ) {
				$cart = new OBP_Cart_Order();
				$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
				$first_cart_item = $cart->get_first_item();

				if ( ! empty( $first_cart_item ) ) {
					$plan_id 		= $first_cart_item->get_plan_id();
					$plan 			= obp_get_plan( $plan_id );
					$business_id 	= $first_cart_item->get_business_id();
					$start_date 	= $first_cart_item->get_start_date();
					$business 		= obp_get_business( $business_id );
					$plan_time_type = $plan->get_time_type();

					$target_date 	= gmdate("Y-m-d", $start_date );
					$date_timestamp = strtotime( $target_date );

					$weekday 		= gmdate("w",$date_timestamp);
					$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

					$weekday_work_hours = $business->get_business_hours();
					$business_hours 	= isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

					// Custom Time
					if ( $plan_time_type !== 'full_time' ) {
						$business_hours = $plan->get_times();
					}

					if ( ! empty( $business_hours ) ) {

						$first_cart_item = $cart->get_first_item();

						// Get order timestamp
						$order_meta_items = OBP_Order_Meta::get_order_items( $order_id );
						$order_item_data = array();

						foreach ( $order_meta_items as $item ) {

							$found_key = array_search( $item->staff_id, array_column($order_item_data, 'staff_id'));

							if ( $found_key === false ) {
								$order_item_data[] = array(
									'staff_id' 		=> $item->staff_id,
									'timestamp' 	=> array(
										array(
											'start_date' 	=> $item->start_date,
											'end_date' 		=> $item->end_date
										)
									),
								);
							} else {
								$order_item_data[$found_key]['timestamp'][] = array(
									'start_date' 	=> $item->start_date,
									'end_date' 		=> $item->end_date
								);
							}
							
						}

						$time_slots = $cart->get_timeslots_first_item( $business_hours, $date_timestamp, $order_item_data );

						if ( ! in_array( $start_date, $time_slots ) ) {
							$time_slots[] = $start_date;
							usort( $time_slots , function($a,$b){
								return absint( $a ) - absint( $b );
							});
						}

						$args = array(
							'target_date' 	=> $target_date,
							'target_time' 	=> $start_date,
							'time_slots' 	=> $time_slots,
							'service_id' 	=> $first_cart_item->get_service_id(),
							'business_id' 	=> $first_cart_item->get_business_id(),
							'vendor_id' 	=> $first_cart_item->get_vendor_id(),
							'order_id' 		=> $order_id,
						);

						obp_get_template("my-booking/popup-form/change-order-content.php", $args );
						wp_die();
					}
				}
			}
			echo '';
			wp_die();
		}

		public function obp_order_change_sort_item(){
			$cart = new OBP_Cart_Order();
			$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_order_nonce' ) ) {
				$old_index = isset( $_POST['old_index'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['old_index'] ) ) ) : '';
				$new_index = isset( $_POST['new_index'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['new_index'] ) ) ) : '';
				$cart->update_key( $old_index, $new_index );

				$first_cart_item 	= $cart->get_first_item();
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

				if ( $plan_time_type !== 'full_time' ) {
					$business_hours = $plan->get_times();
				}

				// Get order timestamp
				$order_meta_items 	= OBP_Order_Meta::get_order_items( $order_id );
				$order_item_data 	= array();

				foreach ( $order_meta_items as $item ) {

					$found_key = array_search( $item->staff_id, array_column($order_item_data, 'staff_id'));

					if ( $found_key === false ) {
						$order_item_data[] = array(
							'staff_id' 		=> $item->staff_id,
							'timestamp' 	=> array(
								array(
									'start_date' 	=> $item->start_date,
									'end_date' 		=> $item->end_date
								)
							),
						);
					} else {
						$order_item_data[$found_key]['timestamp'][] = array(
							'start_date' 	=> $item->start_date,
							'end_date' 		=> $item->end_date
						);
					}
					
				}

				$time_slots = $cart->get_timeslots_first_item( $business_hours, $date_timestamp, $order_item_data );

				$from = $time_slots[0];

				$to = absint( $from ) + absint( $duration );

				$cart_content = $cart->content;

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

						$data = $cart->update_cart_items( $cart_item, $plan_id, $business_hours, $time_slot_items, $date_timestamp, $order_item_data );
						if ( ! empty( $data ) ) {
							$data_update_cart[] = $data;
							$time_slot_items[] = array(
								'start_date' 	=> $data['start_date'],
								'end_date' 		=> $data['end_date'],
							);
						} else {
							// reverse
							$cart->update_key( $old_index, $new_index );
							obp_get_template("my-booking/popup-form/sort-order-item-error.php");
					
							wp_die();
						}
					}

				}

				if ( count( $data_update_cart ) > 0 ) {
					$cart->update_cart( $data_update_cart );
				}
				
				$first_cart_item 	= $cart->get_first_item();
				$time_slots 		= $cart->get_timeslots_first_item( $business_hours, $date_timestamp, $order_item_data );

				$target_date = gmdate( "Y-m-d", $from );

				$args = array(
					'target_date' 	=> $target_date,
					'target_time' 	=> $from,
					'time_slots' 	=> $time_slots,
					'service_id' 	=> $first_cart_item->get_service_id(),
					'business_id' 	=> $first_cart_item->get_business_id(),
					'vendor_id' 	=> $first_cart_item->get_vendor_id(),
					'order_id' 		=> $order_id,
				);

				obp_get_template("my-booking/popup-form/change-order-content.php", $args );

			
				wp_die();
			}
			echo "";
			wp_die();
		}

		public function obp_order_change_next_calendar(){
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

					$timestamp 	= strtotime( $end_date );
					$year_month = gmdate("Y-m", $timestamp);
					list($year, $month) = explode( "-", $year_month );

					if ( $month == "12" ) {
						$year 	= absint( $year ) + 1;
						$month 	= "01";
					} else {
						$month = absint( $month ) + 1;
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

		public function obp_order_change_prev_calendar(){
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
						$year 	= absint( $year ) - 1;
						$month 	= "12";
					} else {
						$month = absint( $month ) - 1;
						$month = $month < 10 ? "0".$month : $month;
					}
					$days_in_month 	= gmdate( "t", strtotime( "$year-$month" ) );
					$days_range 	= range( 1, absint( $days_in_month ) );
					$days_arr 		= array();

					$cur_timestamp = current_time('timestamp');
					$cur_date_timestamp = strtotime( gmdate("Y-m-d", $cur_timestamp ) );

					foreach ( $days_range as $number ) {
						$day = $number < 10 ? "0".$number : $number;
						$date = "$year-$month-$day";
						if ( strtotime($date) < $cur_date_timestamp ) {
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

		public function obp_order_change_calendar(){
			$cart = new OBP_Cart_Order();
			$response = [];

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_order_nonce' ) ) {
				$order_id 		= isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
				$selected_date 	= isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
				$response['month_year'] = date_i18n( 'F Y' , absint( strtotime( $selected_date ) ) );
				$date_timestamp = strtotime( $selected_date );
				
				$first_cart_item = $cart->get_first_item();

				$service_id 	= $first_cart_item->get_service_id();
				$vendor_id 		= $first_cart_item->get_vendor_id();
				$business_id 	= $first_cart_item->get_business_id();
				$duration 		= $first_cart_item->get_duration();
				$start_date 	= $first_cart_item->get_start_date();
				$staff_id 		= $first_cart_item->get_staff_id();

				$service 		= obp_get_service( $service_id );
				$staff_ids 		= $service->get_staff_ids();

				$business 		= obp_get_business( $business_id );

				$old_date_timestamp = strtotime( gmdate("Y-m-d", $start_date ) );
				$hour_timestamp 	= absint( $start_date ) - absint( $old_date_timestamp );
				$need_timestamp 	= $hour_timestamp + absint( $date_timestamp );


				$plan_ids = OBP_Plan::get_plan_id_by_date( $vendor_id, $service_id, $date_timestamp );

				if ( count( $plan_ids ) < 1 ) {
					ob_start();
					obp_get_template("my-booking/popup-form/calendar-none.php");
					$response['html'] = ob_get_clean();
					wp_send_json( $response );
				}

				// Get order timestamp
				$order_meta_items = array();
				$order_item_data = array();
				
				$order = obp_get_order( $order_id );
				$order_start_date = $order->get_service_start_date_earliest();
				$order_datetimestamp = strtotime( gmdate("Y-m-d", $order_start_date ) );
				
				if ( $order_datetimestamp == $date_timestamp ) {
					$order_meta_items = OBP_Order_Meta::get_order_items( $order_id );
					foreach ( $order_meta_items as $item ) {
						$found_key = array_search( $item->staff_id, array_column($order_item_data, 'staff_id'));

						if ( $found_key === false ) {
							$order_item_data[] = array(
								'staff_id' 		=> $item->staff_id,
								'timestamp' 	=> array(
									array(
										'start_date' 	=> $item->start_date,
										'end_date' 		=> $item->end_date
									)
								),
							);
						} else {
							$order_item_data[$found_key]['timestamp'][] = array(
								'start_date' 	=> $item->start_date,
								'end_date' 		=> $item->end_date
							);
						}
					}
				}

				$current_timestamp = current_time('timestamp');

				$plan_id 		= $plan_ids[0];
				$time_slots 	= array();
				$plan 			= obp_get_plan( $plan_id );
				$plan_time_type = $plan->get_time_type();

				$weekday 		= gmdate("w",$date_timestamp);
				$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

				$weekday_work_hours = $business->get_business_hours();
				$business_hours 	= isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

				if ( $plan_time_type !== 'full_time' ) {
					$business_hours = $plan->get_times();
				}

				if ( ! empty( $business_hours ) ) {
					// Staff Id
					$exclude_staff_ids = array();

					while ( count( $exclude_staff_ids ) < count( $staff_ids ) ) {

						if ( count( $exclude_staff_ids ) > 0 ) {
							$staff_id = OBP_Staff::get_priority_staff_id( $service_id, $date_timestamp, $exclude_staff_ids );
						}

						$order_timestamp = array();

						if ( count( $order_item_data ) > 0 ) {
							$found_key = array_search($staff_id, array_column($order_item_data, 'staff_id'));
							if ( $found_key !== false ) {
								$order_timestamp = $order_item_data[$found_key]['timestamp'];
							}
						}

						$exclude_times = array();

						$day_off_row 		= OBP_Day_Off::get_row( $staff_id, $date_timestamp );
						$day_off_timeslots 	= array();

						if ( ! empty( $day_off_row ) ) {	

							$day_off 		= obp_get_day_off( $day_off_row );
							$day_off_time 	= $day_off->get_time();

							if ( $day_off_time === 'custom_time' ) {
								$hour_off = $day_off->get_hour_off();

								$timestamp_off = array_map(function( $value ) use( $date_timestamp ){
									$value['start_date'] = strtotime( $value['start_hour'], $date_timestamp );
									$value['end_date'] = strtotime( $value['end_hour'], $date_timestamp );
									unset($value['start_hour']);
									unset($value['end_hour']);
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


						if ( count( $exclude_times ) > 0 && count( $order_timestamp ) > 0 ) {
							$exclude_times = array_udiff($exclude_times, $order_timestamp, function($a,$b){
								if ($a['start_date'] == $b['start_date'] && $a['end_date'] == $b['end_date']) {
							        return 0;
							    }
							    return ($a['start_date'] < $b['start_date']) ? -1 : 1;
							});

							usort($exclude_times, function( $a, $b ){
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
								
								while ( $timestamp < $to_time && ( absint( $timestamp ) + absint( $duration ) ) <= $to_time ) {
									// Check time
									$end_date = absint( $timestamp ) + absint( $duration );
									if ( $timestamp > $current_timestamp ) {
			
										$check_order_meta_queue = OBP_Order_Meta_Queue::check_timeslots( $staff_id , $timestamp, $end_date );
										$check_order_holding = OBP_Order_Holding::check_timeslots( $staff_id , $timestamp, $end_date );

										if ( count( $order_timestamp ) > 0 ) {

											if ( count( $check_order_holding ) > 0 ) {

												$check_order_holding = array_udiff($check_order_holding, $order_timestamp, function($a,$b){
													if ($a['start_date'] == $b['start_date'] && $a['end_date'] == $b['end_date']) {
												        return 0;
												    }
												    return ($a['start_date'] < $b['start_date']) ? -1 : 1;
												});

												if ( count( $check_order_holding ) > 0 ) {
													usort( $check_order_holding , function( $a, $b ){
														return absint( $a['start_date'] ) - absint( $b['start_date'] );
													});
												}

											}

											if ( count( $check_order_meta_queue ) > 0 ) {

												$check_order_meta_queue = array_udiff($check_order_meta_queue, $order_timestamp, function($a,$b){
													if ($a['start_date'] == $b['start_date'] && $a['end_date'] == $b['end_date']) {
												        return 0;
												    }
												    return ($a['start_date'] < $b['start_date']) ? -1 : 1;
												});

												if ( count( $check_order_meta_queue ) > 0 ) {
													usort( $check_order_meta_queue , function( $a, $b ){
														return absint( $a['start_date'] ) - absint( $b['start_date'] );
													});
												}

											}
										}
										
										$check_timeslots = empty( $check_order_meta_queue ) && empty( $check_order_holding );

										if ( $check_timeslots ) {
											$time_slots[] = $timestamp;
										}
									}

									$timestamp = $end_date;
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
							$cart_content = $cart->content;

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

									$data = $cart->update_cart_items( $cart_item, $plan_id, $business_hours, $time_slot_items, $date_timestamp, $order_item_data );
									if ( ! empty( $data ) ) {
										$data_update_cart[] = $data;
										$time_slot_items[] = array(
											'start_date' 	=> $data['start_date'],
											'end_date' 		=> $data['end_date'],
										);
									} else {
										ob_start();
										obp_get_template("my-booking/popup-form/calendar-none.php");
										$response['html'] = ob_get_clean();
										wp_send_json( $response );
									}
								}
							}

							if ( count( $data_update_cart ) > 0 ) {
								$cart->update_cart( $data_update_cart );
							}

							$args = array(
								'target_date' 	=> $selected_date,
								'target_time' 	=> $target_time,
								'time_slots' 	=> $time_slots,
								'service_id' 	=> $service_id,
								'business_id' 	=> $business_id,
								'vendor_id' 	=> $vendor_id,
								'order_id' 		=> $order_id,
							);
							ob_start();
							obp_get_template("my-booking/popup-form/calendar-content.php", $args );
							$response['html'] = ob_get_clean();
							wp_send_json( $response );
						}

						$exclude_staff_ids[] = $staff_id;
					}
				}
	

			}
			ob_start();
			obp_get_template("my-booking/popup-form/calendar-none.php");
			$response['html'] = ob_get_clean();
			wp_send_json( $response );
		}

		public function obp_order_change_time(){
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_order_nonce' ) ) {
				$new_time = isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '';
				$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
				$new_time = absint( $new_time );
				$cart = new OBP_Cart_Order();
				if ( ! empty( $new_time ) ) {
					$first_cart_item 	= $cart->get_first_item();
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

					if ( $plan_time_type !== 'full_time' ) {
						$business_hours = $plan->get_times();
					}

					$from = $new_time;
					$to = absint( $from ) + absint( $duration );

					$cart_content = $cart->content;

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
					// Get order timestamp
					$order_meta_items = OBP_Order_Meta::get_order_items( $order_id );
					$order_item_data = array();

					foreach ( $order_meta_items as $item ) {

						$found_key = array_search( $item->staff_id, array_column($order_item_data, 'staff_id'));

						if ( $found_key === false ) {
							$order_item_data[] = array(
								'staff_id' 		=> $item->staff_id,
								'timestamp' 	=> array(
									array(
										'start_date' 	=> $item->start_date,
										'end_date' 		=> $item->end_date
									)
								),
							);
						} else {
							$order_item_data[$found_key]['timestamp'][] = array(
								'start_date' 	=> $item->start_date,
								'end_date' 		=> $item->end_date
							);
						}
						
					}

					if ( count( $cart_content ) > 1 ) {
						foreach ( $cart_content as $cart_key => $cart_item ) {
							// ignore first item
							if ( $cart_key === 0 ) {
								continue;
							}

							$data = $cart->update_cart_items( $cart_item, $plan_id, $business_hours, $time_slot_items, $date_timestamp, $order_item_data );
							if ( ! empty( $data ) ) {
								$data_update_cart[] = $data;
								$time_slot_items[] = array(
									'start_date' 	=> $data['start_date'],
									'end_date' 		=> $data['end_date'],
								);
							} else {
								obp_get_template("my-booking/popup-form/time-error.php");
						
								wp_die();
							}
						}
					}
					// Update Cart
					if ( count( $data_update_cart ) > 0 ) {
						$cart->update_cart( $data_update_cart );
					}
				}
				// Reload order item
				obp_change_order_form_order_item( array( 'order_id' => $order_id ) );
			}
	
			wp_die();
		}

		public function obp_order_change_schedule(){
	
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
				if ( $order_id ) {
					$order 				= obp_get_order( $order_id );
					$order_status 		= $order->get_order_status();
					$timestamp_check 	= $order->get_service_start_date_earliest();
					$current_time 		= current_time( 'timestamp' );
					$before_minutes 	= OBP()->settings->change_order->get('change_order_before_time',120);
					$before_minutes 	= absint( $before_minutes );
					$check_time 		= absint( $current_time ) + ( $before_minutes * 60 );
					$limited 				= absint( OBP()->settings->change_order->get('change_order_limited', 1 ) );
					$allow_change_order 	= OBP()->settings->change_order->get('change_order_enable', 'yes' );
					$number_change_order 	= $order->get_number_change_order();
					$check_order_change 	= $order->get_allow_change();

					if ( in_array( $order_status,  array( 'obp_pending', 'obp_processing' ) ) &&
						$check_time < $timestamp_check &&
						$allow_change_order === 'yes' &&
						$check_order_change === 'yes' &&
						$number_change_order < $limited ) {

						$cart = new OBP_Cart_Order();
						$cart->remove_cart();

						$order_meta_items = OBP_Order_Meta::get_order_items( $order_id );
						$order_item_data = array();

						if ( ! empty( $order_meta_items ) ) {
							foreach ( $order_meta_items as $item ) {
								$data = array(
									'vendor_id' 	=> $item->vendor_id,
									'service_id' 	=> $item->service_id,
									'staff_id' 		=> $item->staff_id,
									'start_date' 	=> $item->start_date,
									'end_date' 		=> $item->end_date,
									'duration' 		=> $item->duration,
									'price' 		=> $item->price,
									'plan_id' 		=> $item->plan_id,
									'business_id' 	=> $item->business_id,
									'package_ids' 	=> maybe_unserialize( $item->package_ids ),
								);
								$cart->add_item( $data );

								$found_key = array_search( $item->staff_id, array_column($order_item_data, 'staff_id'));

								if ( $found_key === false ) {
									$order_item_data[] = array(
										'staff_id' 		=> $item->staff_id,
										'timestamp' 	=> array(
											array(
												'start_date' 	=> $item->start_date,
												'end_date' 		=> $item->end_date
											)
										),
									);
								} else {
									$order_item_data[$found_key]['timestamp'][] = array(
										'start_date' 	=> $item->start_date,
										'end_date' 		=> $item->end_date
									);
								}
							}
						}

						$first_cart_item = $cart->get_first_item();
						
						if ( ! empty( $first_cart_item ) ) {
							$plan_id 		= $first_cart_item->get_plan_id();
							$plan 			= obp_get_plan( $plan_id );
							$business_id 	= $first_cart_item->get_business_id();
							$start_date 	= $first_cart_item->get_start_date();
							$business 		= obp_get_business( $business_id );
							$plan_time_type = $plan->get_time_type();

							$target_date 	= gmdate("Y-m-d", $start_date );
							$date_timestamp = strtotime( $target_date );

							$weekday 		= gmdate("w",$date_timestamp);
							$weekday_key 	= OBP_Calendar::get_weekday_keys()[$weekday];

							$weekday_work_hours = $business->get_business_hours();
							$business_hours 	= isset( $weekday_work_hours[$weekday_key] ) ? $weekday_work_hours[$weekday_key] : array();

							// Custom Time
							if ( $plan_time_type !== 'full_time' ) {
								$business_hours = $plan->get_times();
							}

							if ( ! empty( $business_hours ) ) {

								$time_slots = $cart->get_timeslots_first_item( $business_hours, $date_timestamp, $order_item_data );
								
								
								if ( ! in_array( $start_date, $time_slots ) ) {
									$time_slots[] = $start_date;
									usort($time_slots, function($a,$b){
										return absint( $a ) - absint( $b );
									});
								}

								$args = array(
									'target_date' 	=> $target_date,
									'target_time' 	=> $start_date,
									'time_slots' 	=> $time_slots,
									'service_id' 	=> $first_cart_item->get_service_id(),
									'business_id' 	=> $first_cart_item->get_business_id(),
									'vendor_id' 	=> $first_cart_item->get_vendor_id(),
									'order_id' 		=> $order_id,
								);
								
								obp_get_template("my-booking/popup-form/change-order-popup.php", $args );

						
								wp_die();
								
							}
						}
					}
				}
			}
			obp_get_template("my-booking/popup-form/change-order-error.php" );
	
			wp_die();
		}

		public function obp_manager_orders_load_data(){
			$response = array(
				'table_html' 		=> '',
				'pagination_html' 	=> '',
			);

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {

				$orders = OBP_Order::instance()->get_manager_orders_ajax();

				// Table Html
				ob_start();
				if ( $orders->have_posts() ): ?>
				<?php while ( $orders->have_posts() ) :
					$orders->the_post();
					obp_get_template( "manage-booking/order-table-item.php" );
				endwhile;
				else:
					?>
					<tr>
						<td colspan="5"><?php esc_html_e( 'Bookings not found.', 'ovabookpro' ); ?></td>
					</tr>
					<?php
				endif;
				wp_reset_postdata();

				$response['table_html'] = ob_get_contents();
				ob_end_clean();

				// Pagination Html
				ob_start();
				obp_get_template( "manage-booking/order-pagination.php", array( 'orders' => $orders ) );
				$response['pagination_html'] = ob_get_contents();
				ob_end_clean();

			}

			wp_send_json( $response );
		}
		
	}

}