<?php

namespace BookPro\Order;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Order_Balance") ) {
	

	class OBP_Order_Balance {

		protected static $table = 'obp_order_balance';


		public static function add( $data = array() ){
			global $wpdb;

			$data_column = array(
				'vendor_id' 			=> esc_sql( $data['vendor_id'] ),
				'order_id' 				=> esc_sql( $data['order_id'] ),
				'vendor_total' 			=> esc_sql( $data['vendor_total'] ),
				'remaining_phased' 		=> esc_sql( $data['remaining_phased'] ),
				'start_date' 			=> esc_sql( $data['start_date'] ),
				'remaining_service' 	=> esc_sql( $data['remaining_service'] ),
				'balance_status' 		=> esc_sql( $data['balance_status'] ),
			);

			$table_name = $wpdb->prefix.self::$table; 
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$table_name,
				$data_column,
			);

			return $wpdb->insert_id;
		}

		public static function get_row( $order_balance_id ){
			global $wpdb;

			$row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}obp_order_balance WHERE id = %d" , esc_sql( $order_balance_id ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$wpdb->flush();

			return $row;
		}

		public static function get_row_by_order_id( $order_id ){
			global $wpdb;

			$row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}obp_order_balance WHERE order_id = %d" , esc_sql( $order_id ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$wpdb->flush();

			return $row;
		}

		public static function update_start_date( $order_balance_id, $start_date ){
			global $wpdb;

			$table_name = $wpdb->prefix.self::$table;

			$wpdb->update( $table_name, array( 'start_date' => esc_sql( $start_date ) ), array( 'id' => esc_sql( $order_balance_id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		public static function delete( $id ){
			global $wpdb;

			$table_name = $wpdb->prefix.self::$table; 

			$wpdb->delete( $table_name, array( 'id' => esc_sql( $id ) ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->flush();
		}

		public static function delete_all(){
			global $wpdb;

			$delete = $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}obp_order_balance");
			$wpdb->flush();
		}

		public static function delete_by_order_id( $order_id ){
			global $wpdb;

			$table_name = $wpdb->prefix.self::$table; 

			$wpdb->delete( $table_name, array( 'order_id' => esc_sql( $order_id ) ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->flush();
		}

		public static function get_total_order_queue( $vendor_id ){
			global $wpdb;

			$results = $wpdb->get_col( $wpdb->prepare("SELECT remaining_phased FROM {$wpdb->prefix}obp_order_balance WHERE balance_status IN ('obp_phased','obp_pending') AND vendor_id = %d", esc_sql( $vendor_id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$wpdb->flush();

			$total = 0;

			if ( ! empty( $results ) ) {
				$results = array_map (function( $value ){
					return (float)$value;
				}, $results );
				$total = array_sum( $results );
			}

			return $total;
		}


	}
}