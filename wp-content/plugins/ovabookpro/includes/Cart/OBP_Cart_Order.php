<?php
namespace BookPro\Cart;

use BookPro\Cart\OBP_Cart;
use BookPro\Cart\OBP_Cart_Item;
use BookPro\Order\OBP_Order_Holding;
use BookPro\Order\OBP_Order_Meta_Queue;
use BookPro\StaffDayOff\OBP_Day_Off;
use BookPro\OBP_Calendar;
use BookPro\Staff\OBP_Staff;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Cart_Order") ) {

	class OBP_Cart_Order extends OBP_Cart {

		public function __construct(){
			$this->data = OBP()->session->get('cart_change', []);
			$this->cart_content_init();
		}

		public function remove_cart(){
			OBP()->session->set('cart_change', []);
			OBP()->session->save_data();
			$this->data = [];
			$this->cart_content_init();
		}

		public function save_cart(){
			OBP()->session->set('cart_change', $this->data );
			OBP()->session->save_data();
			$this->cart_content_init();
		}

		public function get_timeslots_first_item( $business_hours, $date_timestamp, $order_item_data = array() ){
			$current_timestamp 	= current_time('timestamp');
			$first_cart_item 	= $this->get_first_item();
			$staff_id 			= $first_cart_item->get_staff_id();
			$service_id 		= $first_cart_item->get_service_id();
			$exclude_times 		= array();
			$day_off_row 		= OBP_Day_Off::get_row( $staff_id, $date_timestamp );
			$time_slots 		= array();
			$duration 			= $first_cart_item->get_duration();

			$order_timestamp = array();

			if ( count( $order_item_data ) > 0 ) {
				$found_key = array_search($staff_id, array_column($order_item_data, 'staff_id'));
				if ( $found_key !== false ) {
					$order_timestamp = $order_item_data[$found_key]['timestamp'];
				}
			}

			if ( ! empty( $day_off_row ) ) {

				$day_off 		= obp_get_day_off( $day_off_row );
				$day_off_time 	= $day_off->get_time();

				if ( $day_off_time === 'custom_time' ) {
					$hour_off = $day_off->get_hour_off();

					$timestamp_off = array_map(function( $value ) use( $date_timestamp ){
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

					$timestamp 	= strtotime( $from_time, $date_timestamp );
					$to_time 	= strtotime( $to_time, $date_timestamp );

					while ( $timestamp < $to_time && $to_time >= ( (int)$timestamp + (int)$duration ) ) {
						// Check time
						$_end_date = (int)$timestamp + (int)$duration;
						if ( $timestamp > $current_timestamp ) {

							$check_order_holding = OBP_Order_Holding::check_timeslots( $staff_id , $timestamp, $_end_date );
							$check_order_meta_queue = OBP_Order_Meta_Queue::check_timeslots( $staff_id , $timestamp, $_end_date );


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
											return (int)$a['start_date'] - (int)$b['start_date'];
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
											return (int)$a['start_date'] - (int)$b['start_date'];
										});
									}

								}
							}


							$check_timeslots = empty( $check_order_holding ) && empty( $check_order_meta_queue );

							if ( $check_timeslots ) {
								$time_slots[] = $timestamp;
							}

						}

						$timestamp = $_end_date;
					}
					
				}
			}

			return $time_slots;
		}

		public function update_cart_items( $cart_item, $plan_id, $business_hours, $time_slot_items, $date_timestamp, $order_item_data = array() ){
			$result = array();

			$service_id 	= $cart_item->get_service_id();
			$vendor_id 		= $cart_item->get_vendor_id();
			$business_id 	= $cart_item->get_business_id();
			$duration 		= $cart_item->get_duration();

			$current_timestamp = current_time( 'timestamp' );
			$staff_id 		= $cart_item->get_staff_id();
			$service 		= obp_get_service( $service_id );
			$staff_ids 		= $service->get_staff_ids();

			$time_slots = array();
			$last_key 	= count( $time_slot_items ) - 1;
			$from 		= $time_slot_items[$last_key]['end_date'];
			$to 		= (int)$from + (int)$duration;

			$plan 			= obp_get_plan( $plan_id );
			$service_ids 	= $plan->get_service_ids();

			if ( in_array( $service_id, $service_ids ) ) {
				$exclude_staff_ids 	= array();

				while ( count( $exclude_staff_ids ) - count( $staff_ids ) < 0 ) {
					if ( count( $exclude_staff_ids ) > 0 ) {
						$staff_id = OBP_Staff::get_priority_staff_id( $service_id, $date_timestamp, $exclude_staff_ids );
					}

					if ( ! empty( $staff_id ) ) {
						// Check if the required time is valid or not

						$check_order_holding 	= OBP_Order_Holding::check_timeslots( $staff_id , $from, $to );
						$check_order_meta_queue = OBP_Order_Meta_Queue::check_timeslots( $staff_id , $from, $to );

						$order_timestamp = array();

						if ( count( $order_item_data ) > 0 ) {
							$found_key = array_search($staff_id, array_column($order_item_data, 'staff_id'));
							if ( $found_key !== false ) {
								$order_timestamp = $order_item_data[$found_key]['timestamp'];
								foreach ( $order_timestamp as $timestamp ) {
									if ( ( $from >= $timestamp['start_date'] && $from < $timestamp['end_date'] ) || ( $to > $timestamp['start_date'] && $to <= $timestamp['end_date'] ) ) {
										$check_order_holding 	= array();
										$check_order_meta_queue = array();
										break;
									}
								}
							}
						}


						$check_next_timeslots = empty( $check_order_holding ) && empty( $check_order_meta_queue );

						$exclude_times 		= array();
						$day_off_row 		= OBP_Day_Off::get_row( $staff_id, $date_timestamp );
						$day_off_timeslots 	= array();

						// Staff day off
						if ( ! empty( $day_off_row ) ) {	

							$day_off 		= obp_get_day_off( $day_off_row );
							$day_off_time 	= $day_off->get_time();

							if ( $day_off_time === 'custom_time' ) {
								$hour_off = $day_off->get_hour_off();

								$timestamp_off = array_map(function( $value ) use( $date_timestamp ){
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

								$timestamp 	= strtotime( $from_time, $date_timestamp );
								$to_time 	= strtotime( $to_time, $date_timestamp );
								// If the next time is available
								if ( $check_next_timeslots && $from >= $timestamp && $from < $to_time && $to > $timestamp && $to <= $to_time ) {
									// Add data
									$result = array(
										'staff_id' 		=> $staff_id,
										'plan_id' 		=> $plan_id,
										'start_date' 	=> $from,
										'end_date' 		=> $to,
									);
									return $result;
									break;
								}
								// get timeslots
								while ( $timestamp < $to_time && ( (int)$timestamp + (int)$duration ) <= $to_time ) {

									$end_timestamp = (int)$timestamp + (int)$duration;

									$check_order_holding 	= OBP_Order_Holding::check_timeslots( $staff_id , $timestamp, $end_timestamp );
									$check_order_meta_queue = OBP_Order_Meta_Queue::check_timeslots( $staff_id , $timestamp, $end_timestamp );

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
													return (int)$a['start_date'] - (int)$b['start_date'];
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
													return (int)$a['start_date'] - (int)$b['start_date'];
												});
											}

										}
									}
									
									$check_timeslots = empty( $check_order_holding ) && empty( $check_order_meta_queue );

									if ( $timestamp > $current_timestamp && $check_timeslots && $timestamp >= $from ) {
										$found_key = array_search($staff_id, array_column($time_slots, 'staff_id'));
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
							usort($time_slots, function($a, $b) {
						        return (int)$a['timestamp'] - (int)$b['timestamp'];
						    });

						    $_from = $time_slots[0]['timestamp'];
						    $_to = (int)$_from + (int)$duration;
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

	}
}