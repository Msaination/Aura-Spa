<?php


namespace BookPro\Order;
use BookPro\User\OBP_User;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists("OBP_Order_Meta_Queue") ) {
	

	class OBP_Order_Meta_Queue {

		protected static $table = 'obp_order_meta_queue';

		public function __construct(){}


		public static function add( $data ){
			global $wpdb;

			$data_column = array(
				'vendor_id' 		=> esc_sql( $data['vendor_id'] ),
				'order_id' 			=> esc_sql( $data['order_id'] ),
				'service_id' 		=> esc_sql( $data['service_id'] ),
				'staff_id' 			=> esc_sql( $data['staff_id'] ),
				'customer_id' 		=> esc_sql( $data['customer_id'] ),
				'start_date' 		=> esc_sql( $data['start_date'] ),
				'end_date' 			=> esc_sql( $data['end_date'] ),
				'price' 			=> esc_sql( $data['price'] ),
				'order_balance_id' 	=> esc_sql( $data['order_balance_id'] ),
			);

			$table_name = $wpdb->prefix.self::$table; 
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$table_name,
				$data_column,
			);

			return $wpdb->insert_id;
		}

		public static function delete( $id ){
			global $wpdb;

			$table_name = $wpdb->prefix.self::$table; 

			$wpdb->delete( $table_name, array( 'id' => esc_sql( $id ) ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		public static function delete_all(){
			global $wpdb;

			$delete = $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}obp_order_meta_queue");
			$wpdb->flush();
		}

		public static function delete_by_order_id( $order_id ){
			global $wpdb;

			$table_name = $wpdb->prefix.self::$table; 

			$wpdb->delete( $table_name, array( 'order_id' => esc_sql( $order_id ) ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->flush();
		}

		public static function get_all_passed(){
			global $wpdb;

			$current_time = current_time( 'timestamp' );

			$rows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}obp_order_meta_queue WHERE end_date < %d", esc_sql( $current_time ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$wpdb->flush();

			return $rows;
		}

		public static function get_data_calendar_all_schedule( $args = array() ){
			global $wpdb;

			$vendor_id = OBP_User::get_vendor_id();

			$current_time = current_time( 'timestamp' );
			$current_date = strtotime( gmdate("Y-m-d", $current_time ) );

			$query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}obp_order_meta_queue WHERE start_date >= %d AND vendor_id = %d", esc_sql( $current_date ), esc_sql( $vendor_id ) );

			if ( isset( $args['order_ids'] ) && isset( $args['staff_id'] ) ) {
				$order_ids 	= isset( $args['order_ids'] ) ? $args['order_ids'] : array();
				$staff_id 	= isset( $args['staff_id'] ) ? $args['staff_id'] : '';
				if ( empty( $order_ids ) ) {
					return $order_ids;
				}
				$order_id_placeholders = implode( ', ', array_fill( 0, count( $order_ids ), '%d' ) );

				$query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}obp_order_meta_queue WHERE start_date >= $current_date AND vendor_id = $vendor_id AND staff_id = $staff_id AND order_id IN ( $order_id_placeholders )", $order_ids ); // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			} else {

				if ( isset( $args['order_ids'] ) ) {
					$order_ids 	= isset( $args['order_ids'] ) ? $args['order_ids'] : array();
					if ( empty( $order_ids ) ) {
						return $order_ids;
					}
					$order_id_placeholders = implode( ', ', array_fill( 0, count( $order_ids ), '%d' ) );
					$query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}obp_order_meta_queue WHERE start_date >= $current_date AND vendor_id = $vendor_id AND order_id IN ($order_id_placeholders)", $order_ids ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				}

				if ( isset( $args['staff_id'] ) ) {
					$staff_id 	= isset( $args['staff_id'] ) ? $args['staff_id'] : '';
					$query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}obp_order_meta_queue WHERE start_date >= %d AND vendor_id = %d AND staff_id = %d", esc_sql( $current_date ), esc_sql( $vendor_id ), esc_sql( $staff_id ) );
				}

			}
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
			$overall_schedule = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->flush();

			$results = array();

			if ( count( $overall_schedule ) > 0 ) {
				foreach ( $overall_schedule as $key => $item ) {
					$service = obp_get_service( $item->service_id );
					$title = $service->get_title();
					$start = gmdate("Y-m-d H:i:s", $item->start_date );
					$start = str_replace(" ", "T", $start);

					$end = gmdate("Y-m-d H:i:s", $item->end_date );
					$end = str_replace(" ", "T", $end);

					$color = get_post_meta( $item->service_id, OBP_METABOX.'color', true );

					$results[$item->service_id]['color'] = $color;
					$results[$item->service_id]['events'][] = array(
						'title' 		=> $title,
						'start' 		=> $start,
						'end' 			=> $end,
						'order_id' 		=> $item->order_id,
						'staff_id' 		=> $item->staff_id,
						'service_id' 	=> $item->service_id,
						'start_date' 	=> $item->start_date,
						'end_date' 		=> $item->end_date,
					);
				}
			}

			return $results;

		}

		public static function get_data_calendar_staff_schedule( $args = array() ){
			global $wpdb;

			$user_id = get_current_user_id();

			$current_time = current_time( 'timestamp' );

			$query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}obp_order_meta_queue WHERE start_date >= %d AND staff_id = %d", esc_sql( $current_time ), esc_sql( $user_id ) );

			if ( isset( $args['order_ids'] ) ) {
				$order_ids 	= isset( $args['order_ids'] ) ? $args['order_ids'] : array();
				if ( empty( $order_ids ) ) {
					return $order_ids;
				}
				$order_id_placeholders = implode( ', ', array_fill( 0, count( $order_ids ), '%d' ) );

				$query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}obp_order_meta_queue WHERE start_date >= $current_time AND staff_id = $user_id AND order_id IN ( $order_id_placeholders )", $order_ids ); // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
			$staff_schedule = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->flush();

			$results = array();

			if ( count( $staff_schedule ) > 0 ) {
				foreach ( $staff_schedule as $key => $item ) {
					$service = obp_get_service( $item->service_id );
					$title = $service->get_title();
					$start = gmdate("Y-m-d H:i:s", $item->start_date );
					$start = str_replace(" ", "T", $start);

					$end = gmdate("Y-m-d H:i:s", $item->end_date );
					$end = str_replace(" ", "T", $end);

					$color = $service->get_color();

					$results[$item->service_id]['color'] = $color;
					$results[$item->service_id]['events'][] = array(
						'title' => $title,
						'start' => $start,
						'end' 	=> $end,
					);
				}
			}

			return $results;
		}

		public static function get_data_calendar_staff_schedule_by_user_id( $user_id ){
			global $wpdb;

			$current_time = current_time( 'timestamp' );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$staff_schedule = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}obp_order_meta_queue WHERE start_date >= %d AND staff_id = %d",
					esc_sql( $current_time ),
					esc_sql( $user_id )
				)
			);

			$wpdb->flush();

			$results = array();

			if ( count( $staff_schedule ) > 0 ) {
				foreach ( $staff_schedule as $key => $item ) {
					$service = obp_get_service( $item->service_id );
					$title = $service->get_title();
					$start = gmdate("Y-m-d H:i:s", $item->start_date );
					$start = str_replace(" ", "T", $start);

					$end = gmdate("Y-m-d H:i:s", $item->end_date );
					$end = str_replace(" ", "T", $end);

					$color = get_post_meta( $item->service_id, OBP_METABOX.'color', true );

					$results[$item->service_id]['color'] = $color;
					$results[$item->service_id]['events'][] = array(
						'title' => $title,
						'start' => $start,
						'end' 	=> $end,
					);
				}
			}


			return $results;
		}

		public static function get_order_meta_by_order_id( $order_id ){
			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}obp_order_meta_queue WHERE order_id = %d",
					esc_sql( $order_id )
				)
			);

			$wpdb->flush();

			return $results;
		}

		public static function get_order_meta_by_service_id( $service_id ){
			global $wpdb;

			$current_time = current_time( 'timestamp' );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}obp_order_meta_queue WHERE start_date >= %d AND service_id = %d",
					esc_sql( $current_time ),
					esc_sql( $service_id )
				)
			);

			$wpdb->flush();

			return $results;
		}

		public static function get_timeslots_in_day( $staff_id , $start_date ){
			global $wpdb;

			$end_date = strtotime( "+1 day", $start_date );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT start_date, end_date FROM {$wpdb->prefix}obp_order_meta_queue WHERE start_date BETWEEN %d AND %d AND staff_id = %d",
					esc_sql( $start_date ),
					esc_sql( $end_date ),
					esc_sql( $staff_id )
				)
			);

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

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT start_date, end_date FROM {$wpdb->prefix}obp_order_meta_queue WHERE ( (start_date <= %d AND end_date > %d) OR (start_date < %d AND end_date >= %d) ) AND staff_id = %d",
					esc_sql( $start_date ),
					esc_sql( $start_date ),
					esc_sql( $end_date ),
					esc_sql( $end_date ),
					esc_sql( $staff_id )
				),
				ARRAY_A
			);

			return $results;
		}
	}
}