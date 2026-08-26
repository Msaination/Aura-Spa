<?php

namespace BookPro\Order;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Order_Holding") ) {
	
	class OBP_Order_Holding {

		protected static $table = 'obp_order_holding';

		public function __construct(){}


		public static function add( $data ){

			global $wpdb;

			$data_column = array(
				'vendor_id' 	=> $data['vendor_id'],
				'order_id' 		=> $data['order_id'],
				'service_id' 	=> $data['service_id'],
				'staff_id' 		=> $data['staff_id'],
				'start_date' 	=> $data['start_date'],
				'end_date' 		=> $data['end_date'],
			);

			$table_name = $wpdb->prefix.self::$table; 
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$table_name,
				$data_column,
			);

			return $wpdb->insert_id;
		}

		public static function add_order_from_cart(){
			$cart_content = OBP()->cart->content;

			if ( count( $cart_content ) > 0 ) {
				foreach ( $cart_content as $item ) {
					self::add( $item );
				}
			}
			
		}

		public static function get_order_holding( $order_id ){
			global $wpdb;

			$table_name = $wpdb->prefix.self::$table;

			$results = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}obp_order_holding WHERE order_id = %d", $order_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			return $results;
		}

		public static function delete_order_holding( $order_id ){

			global $wpdb;

			$table_name = $wpdb->prefix.self::$table;

			$where = array( 'order_id' => $order_id );

			$results = $wpdb->delete( $table_name, $where ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$wpdb->flush();

			return $results;
		}

		public static function delete_all(){
			global $wpdb;
			$delete = $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}obp_order_holding");
		}

		public static function get_timeslots_in_day( $staff_id , $start_date ){
			global $wpdb;

			$end_date = strtotime( "+1 day", $start_date );

			$results = $wpdb->get_results( $wpdb->prepare("SELECT start_date, end_date FROM {$wpdb->prefix}obp_order_holding WHERE start_date BETWEEN %d AND %d AND staff_id = %d", $start_date, $end_date, $staff_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$wpdb->flush();

			$timeslots = array();

			if ( count( $results ) > 0 ) {
				foreach ( $results as $key => $item ) {
					$data = array();
					$data['start_date'] = $item->start_date;
					$data['end_date'] = $item->end_date;
					$timeslots[] = $data;
				}
			}

			return $timeslots;

		}

		public static function check_timeslots( $staff_id , $start_date, $end_date ){
			global $wpdb;
			// get records between start date and end date.

			$results = $wpdb->get_results( $wpdb->prepare("SELECT start_date, end_date FROM {$wpdb->prefix}obp_order_holding WHERE ( (start_date <= %d AND end_date > %d) OR (start_date < %d AND end_date >= %d) ) AND staff_id = %d", $start_date, $start_date, $end_date, $end_date, $staff_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			return $results;
		}
	}
}