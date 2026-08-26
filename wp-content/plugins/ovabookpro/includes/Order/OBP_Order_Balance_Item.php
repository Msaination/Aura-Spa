<?php

namespace BookPro\Order;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Order_Balance_Item") ) {
	

	class OBP_Order_Balance_Item {

		protected $item = null;

		protected $table = 'obp_order_balance';

		public function __construct( $item ){

			$this->item = $item;
		}

		public function get_item(){
			$item = $this->item;
			return $item;
		}

		public function get_id(){
			$item = $this->get_item();
			$id = $item['id'];
			return $id;
		}

		public function get_vendor_id(){
			$item = $this->get_item();
			$vendor_id = $item['vendor_id'];
			return $vendor_id;
		}

		public function get_order_id(){
			$item = $this->get_item();
			$order_id = $item['order_id'];
			return $order_id;
		}

		public function get_remaining_phased(){
			$item = $this->get_item();
			$remaining_phased = $item['remaining_phased'];
			return $remaining_phased;
		}

		public function get_start_date(){
			$item = $this->get_item();
			$start_date = $item['start_date'];
			return $start_date;
		}

		public function get_balance_status(){
			$item = $this->get_item();
			$status = $item['balance_status'];
			return $status;
		}

		public function get_vendor_total(){
			$item = $this->get_item();
			$vendor_total = $item['vendor_total'];
			return $vendor_total;
		}

		public function get_remaining_service(){
			$item = $this->get_item();
			$remaining_service = $item['remaining_service'];
			return $remaining_service;
		}

		public function get_table(){
			$table = $this->table;
			return $table;
		}

		public function set_remaining_phased( $amount ){
			global $wpdb;

			$id = $this->get_id();

			$table_name = $wpdb->prefix.$this->get_table();

			$data_column = array(
				'remaining_phased' => esc_sql( $amount ),
			);

			$wpdb->update( $table_name, $data_column, array( 'id', esc_sql( $id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		}

		public function set_remaining_service( $remaining_service ){
			global $wpdb;

			$id = $this->get_id();

			$table_name = $wpdb->prefix.$this->get_table();

			$data_column = array(
				'remaining_service' => esc_sql( $remaining_service ),
			);

			$wpdb->update( $table_name, $data_column, array( 'id', esc_sql( $id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		public function set_balance_status( $status = "obp_pending" ){
			global $wpdb;

			$id = $this->get_id();

			$table_name = $wpdb->prefix.$this->get_table();

			$data_column = array(
				'balance_status' => esc_sql( $status ),
			);

			$wpdb->update( $table_name, $data_column, array( 'id', esc_sql( $id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		}
	}
}