<?php

namespace BookPro\Payout;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists("OBP_Payout_Method_Info") ) {
	


	class OBP_Payout_Method_Info {

		protected static $table = 'obp_payout_method_info';

		public static function add( $payout_method_id , $payout_info ){
			global $wpdb;

			$user_id = get_current_user_id();

			$data_column = array(
				'user_id' 			=> esc_sql( $user_id ),
				'payout_method_id' 	=> esc_sql( $payout_method_id ),
				'payout_info' 		=> maybe_serialize( $payout_info ),
			);

			$table_name = $wpdb->prefix.self::$table; 
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$table_name,
				$data_column,
			);

			return $wpdb->insert_id;
		}

		public static function get_row( $payout_method_id, $user_id = null ){
			global $wpdb;

			if ( is_null( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$results = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}obp_payout_method_info WHERE user_id = %d AND payout_method_id = %d",
					esc_sql( $user_id ),
					esc_sql( $payout_method_id )
			), ARRAY_A );

			$wpdb->flush();

			return $results;
		}

		public static function delete_by_payout_method_id( $payout_method_id ){
			global $wpdb;

			$table_name = $wpdb->prefix.self::$table;

			$where = array( 'payout_method_id' => esc_sql( $payout_method_id ) );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$results = $wpdb->delete( $table_name, $where );

			$wpdb->flush();

			return $results;
		}

		/* Get list payout method by user id */
		public static function get_row_by_user_id($user_id) {
			global $wpdb;

			$table_name = $wpdb->prefix.self::$table;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}obp_payout_method_info WHERE user_id = %d",
					esc_sql( $user_id )
				)
			);

			return $results;
		}

		public static function update( $id, $payout_info ){
			global $wpdb;

			$table_name = $wpdb->prefix.self::$table;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return $wpdb->update(
			    $table_name,
			    array(
			        'payout_info' => maybe_serialize( $payout_info ),
			    ),
			    array(
			        'id' => esc_sql( $id ),
			    )
			);
		}
	}
}