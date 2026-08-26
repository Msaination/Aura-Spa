<?php
namespace BookPro;

use BookPro\Traits\SingletonTrait;
use BookPro\Order\OBP_Order;
use BookPro\Order\OBP_Order_Holding;
use BookPro\Order\OBP_Order_Balance;
use BookPro\Order\OBP_Order_Meta_Queue;
use BookPro\Commission\OBP_Commission;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Cron_Jobs") ) {
	

	class OBP_Cron_Jobs {

		use SingletonTrait;

		public function __construct(){

			add_filter( 'cron_schedules', array( __CLASS__, 'obp_cron_schedules' ) );
			add_action( 'obp_order_holding', array( __CLASS__, 'obp_order_holding_exec' ) );
			add_action( 'obp_update_order_queue', array( __CLASS__, 'obp_update_order_queue_exec' ) );
			add_action( 'obp_cleanup_sessions', 'obp_cleanup_session_data' );
			add_action( 'obp_clear_files', array( __CLASS__, 'obp_clear_files_exec' ) );
		}

		public static function activation(){
			// Create cron jobs (clear them first).
			
			wp_clear_scheduled_hook( 'obp_order_holding' );
			wp_clear_scheduled_hook( 'obp_update_order_queue' );
			wp_clear_scheduled_hook( 'obp_cleanup_sessions' );


			if ( ! wp_next_scheduled('obp_order_holding') ) {
				wp_schedule_event( time(), 'obp_order_holding_time', 'obp_order_holding' );
			}
			if ( ! wp_next_scheduled('obp_update_order_queue') ) {
				wp_schedule_event( time(), 'obp_update_order_queue_time', 'obp_update_order_queue' );
			}

			if ( ! wp_next_scheduled('obp_cleanup_sessions') ) {
				wp_schedule_event( time() + ( 6 * HOUR_IN_SECONDS ), 'twicedaily', 'obp_cleanup_sessions' );
			}

			if ( ! wp_next_scheduled('obp_clear_files') ) {
				wp_schedule_event( time(), 'daily', 'obp_clear_files' );
			}
		}

		public static function obp_cron_schedules( $schedules ){

			// Order Holding
			$minutes 	= absint( OBP()->settings->general->get('order_holding',60) );
			$interval 	= $minutes*60;
			$key 		= 'obp_order_holding_time';

			if( !isset( $schedules[$key] ) ){
		        $schedules[$key] = array(
		            'interval' 	=> $interval,
		            // translators: %d: number of minutes.
		            'display' 	=> sprintf( __( 'Once every %d minutes', 'ovabookpro' ), $minutes ),
		        );
		    }

		    // Update Order Queue
		    // Update Order Balance
		    unset( $interval );
		    unset( $minutes );
		    unset( $key );
		    
			$minutes 	= absint( OBP()->settings->general->get('update_order_queue',60 ) );
			$interval 	= $minutes*60;
			$key 		= 'obp_update_order_queue_time';

			if( !isset( $schedules[$key] ) ){
		        $schedules[$key] = array(
		            'interval' 	=> $interval,
		            // translators: %d: number of minutes.
		            'display' 	=> sprintf( __( 'Once every %d minutes', 'ovabookpro' ), $minutes ),
		        );
		    }

			return apply_filters( 'obp_cron_schedules', $schedules );
		}

		public static function deactivate(){
    		wp_clear_scheduled_hook( 'obp_order_holding' );
    		wp_clear_scheduled_hook( 'obp_update_order_queue' );
    		wp_clear_scheduled_hook( 'obp_cleanup_sessions' );
    		wp_clear_scheduled_hook( 'obp_clear_files' );
		}

		public static function obp_clear_files_exec(){
			$upload_dir = wp_upload_dir();
			$dir = trailingslashit( $upload_dir['basedir'] . '/invoices' );
			$files = glob($dir."*.{jpg,png,gif,pdf}", GLOB_BRACE);

			if ( ! empty( $files ) ) {
				foreach ($files as $file) {
					wp_delete_file( $file );
				}
			}
		}

		public static function obp_order_holding_exec(){
			// clear holding order
			$order_ids 			= OBP_Order::get_order_ids_by_status("obp_pending");
			$current_timestamp 	= current_time('timestamp');
			$max_time_checkout 	= absint( OBP()->settings->payment->get( 'max_time_complete_checkout', '10' ) );
			if ( count( $order_ids ) > 0 ) {
				foreach ( $order_ids as $key => $order_id ) {
					$order 			= obp_get_order( $order_id );
					$order->set_order_status("obp_expired");
					$created_time 	= $order->get_date_created_timestamp();
					$check_time 	= absint( $current_timestamp ) - ( $max_time_checkout*60 );
					if ( $check_time - absint( $created_time ) >= 0 ) {
						OBP_Order_Holding::delete_order_holding( $order_id );
					}
				}
			}
		}
		
		// Transfer profit from Pending to Balance
		public static function obp_update_order_queue_exec(){
			$transfer_profit_to_balance = OBP()->settings->earning->get('transfer_profit_to_balance','one_service');

			// Update Order Queue
			$order_queue_passed = OBP_Order_Meta_Queue::get_all_passed();

			if ( ! empty( $order_queue_passed ) ) {
				foreach ( $order_queue_passed as $order_queue ) {
					$end_date 		= $order_queue->end_date;
					$start_date 	= $order_queue->start_date;
					$order_queue_id = $order_queue->id;
					$price 			= $order_queue->price;

					$order_balance_id 			= $order_queue->order_balance_id;
					$order_balance_row 			= OBP_Order_Balance::get_row( $order_balance_id );
					
					if ( ! empty( $order_balance_row ) ) {

						$order_balance 				= obp_get_order_balance( $order_balance_row );
						$order_balance_start_date 	= $order_balance->get_start_date();
						$remaining_phased 			= $order_balance->get_remaining_phased();
						$remaining_service 			= $order_balance->get_remaining_service();
						$vendor_total 				= $order_balance->get_vendor_total();
						$order_id 					= $order_balance->get_order_id();
						$vendor_id 					= $order_balance->get_vendor_id();

						$order = obp_get_order( $order_id );

						$vendor = obp_get_user( $vendor_id );

						switch ( $transfer_profit_to_balance ) {
							case 'one_service':
								
								if ( $order_balance_start_date == $start_date ) {
									$vendor->add_balance_amount( $vendor_total );
									
									$order->set_balance_status("obp_completed");
									$order->set_order_status("obp_completed");

									// Remove Order Balance
									OBP_Order_Balance::delete( $order_balance_id );

									// Add Commission
									OBP_Commission::add(
										array(
											'vendor_id' 	=> $order->get_vendor_id(),
											'order_id' 		=> $order_id,
											'system_fee' 	=> $order->get_system_fee(),
											'tax_amount' 	=> $order->get_tax_amount(),
											'vendor_fee' 	=> $order->get_vendor_fee(),
											'date_created' 	=> $order->get_date_created_timestamp(),
											'profit' 		=> $order->get_vendor_total(),
											'total' 		=> $order->get_total(),
											'commission' 	=> $order->get_commission(),
										)
									);
								}

								break;

							case 'all_services':
								
								$remaining_items = absint( $remaining_service ) - 1;

								if ( $remaining_items > 0 ) {
									$order_balance->set_remaining_service( $remaining_items );

								} else {
									$order->set_balance_status("obp_completed");
									$order->set_order_status("obp_completed");
									$vendor->add_balance_amount( $vendor_total );
									// Remove Order Balance
									OBP_Order_Balance::delete( $order_balance_id );

									// Add Commission
									OBP_Commission::add(
										array(
											'vendor_id' 	=> $order->get_vendor_id(),
											'order_id' 		=> $order_id,
											'system_fee' 	=> $order->get_system_fee(),
											'tax_amount' 	=> $order->get_tax_amount(),
											'vendor_fee' 	=> $order->get_vendor_fee(),
											'date_created' 	=> $order->get_date_created_timestamp(),
											'profit' 		=> $order->get_vendor_total(),
											'total' 		=> $order->get_total(),
											'commission' 	=> $order->get_commission(),
										)
									);
								}

								break;

							case 'each_service':
								$remaining_items = absint( $remaining_service ) - 1;

								if ( $remaining_items > 0 ) {
									$order_balance->set_remaining_service( $remaining_items );
									$order_balance->set_balance_status("obp_phased");
									$amount 		= (float)$remaining_phased - (float)$price;
									$balance_amount = $price;

									if ( $amount < 0 ) {
										$amount = 0;
										$balance_amount = 0;
									}

									$order_balance->set_remaining_phased( $amount );
									$vendor->add_balance_amount( $balance_amount );
								} else {
									$order->set_balance_status("obp_completed");
									$order->set_order_status("obp_completed");
									$vendor->add_balance_amount( $remaining_phased );
									// Remove Order Balance
									OBP_Order_Balance::delete( $order_balance_id );

									// Add Commission
									OBP_Commission::add(
										array(
											'vendor_id' 	=> $order->get_vendor_id(),
											'order_id' 		=> $order_id,
											'system_fee' 	=> $order->get_system_fee(),
											'tax_amount' 	=> $order->get_tax_amount(),
											'vendor_fee' 	=> $order->get_vendor_fee(),
											'date_created' 	=> $order->get_date_created_timestamp(),
											'profit' 		=> $order->get_vendor_total(),
											'total' 		=> $order->get_total(),
											'commission' 	=> $order->get_commission(),
										)
									);
								}

								break;
							
							default:
								break;
						}

					}
					// Remove Order Meta Queue
					OBP_Order_Meta_Queue::delete( $order_queue_id );
		
				}
			}
		}

	}

}