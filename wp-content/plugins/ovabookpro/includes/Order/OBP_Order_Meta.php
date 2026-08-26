<?php
namespace BookPro\Order;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists("OBP_Order_Meta") ) {
	class OBP_Order_Meta {

		protected static $table = 'obp_order_meta';

		public static function add( $data ){
			global $wpdb;

			$data_column = array(
				'vendor_id' 	=> esc_sql( $data['vendor_id'] ),
				'order_id' 		=> esc_sql( $data['order_id'] ),
				'service_id' 	=> esc_sql( $data['service_id'] ),
				'staff_id' 		=> esc_sql( $data['staff_id'] ),
				'customer_id' 	=> esc_sql( $data['customer_id'] ),
				'start_date' 	=> esc_sql( $data['start_date'] ),
				'end_date' 		=> esc_sql( $data['end_date'] ),
				'price' 		=> esc_sql( $data['price'] ),
				'duration' 		=> esc_sql( $data['duration'] ),
				'plan_id' 		=> esc_sql( $data['plan_id'] ),
				'business_id' 	=> esc_sql( $data['business_id'] ),
				'package_ids' 	=> $data['package_ids'],
				'taxes' 		=> $data['taxes'],
			);

			$table_name = $wpdb->prefix.self::$table; 
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$table_name,
				$data_column,
			);

			return $wpdb->insert_id;
		}

		public static function get_order_items( $order_id ){
			global $wpdb;

			$results = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}obp_order_meta WHERE order_id = %d", esc_sql( $order_id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$wpdb->flush();

			return $results;
		}

		public static function get_staff_ids_by_order_id( $order_id ){
			global $wpdb;

			$results = $wpdb->get_col( $wpdb->prepare("SELECT staff_id FROM {$wpdb->prefix}obp_order_meta WHERE order_id = %d", esc_sql( $order_id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$wpdb->flush();

			return $results;
		}

		public static function get_order_item_by_order_id_service_id( $order_id, $service_id ){
			global $wpdb;

			$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}obp_order_meta WHERE order_id = %d AND service_id = %d", esc_sql( $order_id ), esc_sql( $service_id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return $result;
		}

		public static function get_order_item_ids( $order_id ){
			global $wpdb;

			$results = $wpdb->get_col( $wpdb->prepare("SELECT id FROM {$wpdb->prefix}obp_order_meta WHERE order_id = %d", esc_sql( $order_id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$wpdb->flush();

			return $results;
		}

		public static function get_order_items_by_staff_id( $staff_id ){
			global $wpdb;

			$table_name = $wpdb->prefix.self::$table;

			$results = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}obp_order_meta WHERE staff_id = %d", esc_sql( $staff_id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$wpdb->flush();

			return $results;
		}

		public static function delete_by_order_id( $order_id ){

			global $wpdb;

			$table_name = $wpdb->prefix.self::$table; 

			$wpdb->delete( $table_name, array( 'order_id' => esc_sql( $order_id ) ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			
			$wpdb->flush();
		}

		public static function delete( $id ){
			global $wpdb;

			$table_name = $wpdb->prefix.self::$table; 

			$wpdb->delete( $table_name, array( 'id' => esc_sql( $id ) ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$wpdb->flush();
		}

		public static function delete_all(){
			global $wpdb;

			$delete = $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}obp_order_meta");
		}

	}
}