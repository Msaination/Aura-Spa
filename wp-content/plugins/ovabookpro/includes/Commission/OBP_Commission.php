<?php

namespace Bookpro\Commission;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Commission") ) {

	class OBP_Commission {

		protected static $table = 'obp_commission';

		public static function add( $data = array() ){
			global $wpdb;

			// Default value
			$data_default = array(
				'id' 			=> null,
				'order_id' 		=> null,
				'vendor_id' 	=> null,
				'system_fee' 	=> 0,
				'vendor_fee' 	=> 0,
				'tax_amount' 	=> 0,
				'profit' 		=> 0,
				'total' 		=> 0,
				'commission' 	=> 0,
				'date_created' 	=> null
			);

			$data = array_merge($data_default, $data);


			$data_column = array(
				'order_id' 		=> esc_sql( $data['order_id'] ),
				'vendor_id' 	=> esc_sql( $data['vendor_id'] ),
				'system_fee' 	=> esc_sql( $data['system_fee'] ),
				'tax_amount' 	=> esc_sql( $data['tax_amount'] ),
				'vendor_fee' 	=> esc_sql( $data['vendor_fee'] ),
				'date_created' 	=> esc_sql( $data['date_created'] ),
				'profit' 		=> esc_sql( $data['profit'] ),
				'total' 		=> esc_sql( $data['total'] ),
				'commission' 	=> esc_sql( $data['commission'] ),
			);

			$table_name = $wpdb->prefix.self::$table; 
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$table_name,
				$data_column,
			);

			return $wpdb->insert_id;
		}

		public static function get_commission_data_export(){
			// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
			$data_column = isset( $_POST['data_column'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['data_column'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$start_date 	= isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
			$end_date 		= isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';

			// get commission
			$commission = self::get_all( $start_date, $end_date );

			usort($commission, function($a,$b){
				// If no sort, default to title.
				$orderby = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'id'; // WPCS: Input var ok.

				// If no order, default to asc.
				$order = ! empty( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'desc'; // WPCS: Input var ok.

				// Determine sort order.
				$result = strcmp( $a[ $orderby ], $b[ $orderby ] );

				return ( 'asc' === $order ) ? $result : - $result;
			});

			$column_name 	= array_values( $data_column );
			$column_key 	= array_keys( $data_column );

			$data_export 	= [];

			$data_export[] = $column_name;

			foreach ( $commission as $key => $arr ) {
				$row = [];
				
				foreach ( $column_key as $_key => $__column_name ) {
					$val = isset( $arr[$__column_name] ) ? $arr[$__column_name] : '';
					$row[] = apply_filters( 'obp_'.$__column_name.'_row', $val, $arr );
				}

				$data_export[] = $row;
			}

			return $data_export; // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
		}

		public static function get_all( $start_date = '', $end_date = '' ){
			global $wpdb;

			$query = "SELECT * FROM {$wpdb->prefix}obp_commission";
			$start_date = absint( strtotime( $start_date ) );
			$end_date 	= absint( strtotime( $end_date . ' +1 day' ) );

			if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
				$query = "SELECT * FROM {$wpdb->prefix}obp_commission WHERE date_created BETWEEN $start_date AND $end_date";
			}
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
			$results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
			return $results;
		}

		public static function delele_by_order_id( $order_id ){

			global $wpdb;

			$table_name = $wpdb->prefix.self::$table; 

			$wpdb->delete( $table_name, array( 'order_id' => esc_sql( $order_id ) ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->flush();
		}
	}

}