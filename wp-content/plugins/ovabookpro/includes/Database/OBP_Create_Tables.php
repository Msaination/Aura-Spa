<?php
namespace BookPro\Database;

use BookPro\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'OBP_Create_Tables', false ) ) {
	class OBP_Create_Tables {

		use SingletonTrait;

		public function __construct() {}

		public function get_all_table_names() {
			return apply_filters( 'obp_get_all_table_names' , array(
				$this->get_order_meta_table_name(),
				$this->get_order_meta_queue_table_name(),
				$this->get_order_holding_table_name(),
				$this->get_order_balance_table_name(),
				$this->get_payout_method_info_table_name(),
				$this->get_day_off_table_name()
			));
		}

		public function create_new_tables() {
			// SQL query to create the tables
			$sql = $this->get_sql_create_new_tables();

			// Include the upgrade file
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	    	dbDelta($sql);
		}

		public function drop_new_tables() {
			global $wpdb;

			$table_names = $this->get_all_table_names();

			// Drop each table
		    foreach ( $table_names as $table_name ) {
		        // Check if the table exists
		        if ( $wpdb->get_var( $wpdb->prepare("SHOW TABLES LIKE %s", $table_name ) ) == $table_name ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		            // Drop the table
		            $wpdb->query( "DROP TABLE $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		        }
		    }
		}

		public function check_tables_exists() {
			global $wpdb;

			$table_names = $this->get_all_table_names();

			// Drop each table
		    foreach ( $table_names as $table_name ) {
		        // Check if the table exists
		        if ( $wpdb->get_var( $wpdb->prepare("SHOW TABLES LIKE %s", $table_name ) ) != $table_name ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		            add_settings_error(
				        $table_name, // Unique ID for the error
				        $table_name,
				        // translators: %s: table name.
				        sprintf( __( 'Table %s does not exist.', 'ovabookpro' ), $table_name ),
				        'error'
				    );
				    
				    // Display the errors
				    settings_errors($table_name);
		        }
		    }
		}

		public function get_sql_create_new_tables(){
			global $wpdb;

			$collate = $wpdb->has_cap( 'collation' ) ? $wpdb->get_charset_collate() : '';

			// Get order table names
			$order_meta 		= $this->get_order_meta_table_name();
			$order_meta_queue 	= $this->get_order_meta_queue_table_name();
			$order_holding 		= $this->get_order_holding_table_name();
			$order_balance 		= $this->get_order_balance_table_name();
			$payout_method_info = $this->get_payout_method_info_table_name();
			$day_off 			= $this->get_day_off_table_name();
			$sessions 			= $this->get_sessions_table_name();
			$commission 		= $this->get_commission();
			
			$sql = "CREATE TABLE $order_meta (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				vendor_id BIGINT(20) UNSIGNED NULL,
		        order_id BIGINT(20) UNSIGNED NULL,
		        service_id BIGINT(20) UNSIGNED NULL,
		        staff_id BIGINT(20) UNSIGNED NULL,
		        customer_id BIGINT(20) UNSIGNED NULL,
		        start_date BIGINT(20) UNSIGNED NULL,
		        end_date BIGINT(20) UNSIGNED NULL,
		        duration INT(10) UNSIGNED NULL,
		        plan_id BIGINT(20) UNSIGNED NULL,
		        business_id BIGINT(20) UNSIGNED NULL,
		        package_ids LONGTEXT NULL,
		        taxes LONGTEXT NULL,
		        price DECIMAL(26,8) NULL,
		        PRIMARY KEY (id),
		        KEY vendor_id (vendor_id),
		        KEY order_id (order_id),
		        KEY service_id (service_id),
		        KEY staff_id (staff_id),
		        KEY customer_id (customer_id),
		        KEY plan_id (plan_id),
		        KEY business_id (business_id)
			) $collate;
			CREATE TABLE $order_meta_queue (
		    	id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		        vendor_id BIGINT(20) UNSIGNED NULL,
		        order_id BIGINT(20) UNSIGNED NULL,
		        service_id BIGINT(20) UNSIGNED NULL,
		        staff_id BIGINT(20) UNSIGNED NULL,
		        customer_id BIGINT(20) UNSIGNED NULL,
		        order_balance_id BIGINT(20) UNSIGNED NULL,
		        start_date BIGINT(20) UNSIGNED NULL,
		        end_date BIGINT(20) UNSIGNED NULL,
		        price DECIMAL(26,8) NULL,
		        PRIMARY KEY (id),
		        KEY vendor_id (vendor_id),
		        KEY order_id (order_id),
		        KEY service_id (service_id),
		        KEY staff_id (staff_id),
		        KEY customer_id (customer_id),
		        KEY order_balance_id (order_balance_id)
		    ) $collate;
		    CREATE TABLE $order_holding (
		        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		        vendor_id BIGINT(20) UNSIGNED NULL,
		        order_id BIGINT(20) UNSIGNED NULL,
		        service_id BIGINT(20) UNSIGNED NULL,
		        staff_id BIGINT(20) UNSIGNED NULL,
		        start_date BIGINT(20) UNSIGNED NULL,
		        end_date BIGINT(20) UNSIGNED NULL,
		        PRIMARY KEY (id),
		        KEY vendor_id (vendor_id),
		        KEY order_id (order_id),
		        KEY service_id (service_id),
		        KEY staff_id (staff_id)
		    ) $collate;
			CREATE TABLE $order_balance (
		    	id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		        vendor_id BIGINT(20) UNSIGNED NULL,
		        order_id BIGINT(20) UNSIGNED NULL,
		        vendor_total DECIMAL(26,8) NULL,
		        remaining_phased DECIMAL(26,8) NULL,
		        start_date BIGINT(20) UNSIGNED NULL,
		        remaining_service INT(10) UNSIGNED NULL,
		        balance_status VARCHAR(100) NULL,
		        PRIMARY KEY (id),
		        KEY vendor_id (vendor_id),
		        KEY order_id (order_id)
		    ) $collate;
		    CREATE TABLE $payout_method_info (
		    	id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		        user_id BIGINT(20) UNSIGNED NULL,
		        payout_method_id BIGINT(20) UNSIGNED NULL,
		        payout_info LONGTEXT NULL,
		        PRIMARY KEY (id),
		        KEY user_id (user_id),
		        KEY payout_method_id (payout_method_id)
		    ) $collate;
		    CREATE TABLE $day_off (
		    	id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		        vendor_id BIGINT(20) UNSIGNED NULL,
		        staff_id BIGINT(20) UNSIGNED NULL,
		        start_date BIGINT(20) UNSIGNED NULL,
		        end_date BIGINT(20) UNSIGNED NULL,
		        time VARCHAR(20) NULL,
		        hour_off VARCHAR(255) NULL,
		        PRIMARY KEY (id),
		        KEY vendor_id (vendor_id),
		        KEY staff_id (staff_id)
		    ) $collate;
		    CREATE TABLE $sessions (
				session_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				session_key CHAR(32) NOT NULL,
				session_value LONGTEXT NOT NULL,
				session_expiry BIGINT(20) UNSIGNED NOT NULL,
				PRIMARY KEY  (session_id),
				UNIQUE KEY session_key (session_key)
			) $collate;
		    CREATE TABLE $commission (
		    	id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		    	order_id BIGINT(20) UNSIGNED NULL,
		    	vendor_id BIGINT(20) UNSIGNED NULL,
		    	system_fee DECIMAL(26,8) DEFAULT 0 NULL,
		    	vendor_fee DECIMAL(26,8) DEFAULT 0 NULL,
		    	tax_amount DECIMAL(26,8) DEFAULT 0 NULL,
		    	profit DECIMAL(26,8) DEFAULT 0 NULL,
		    	commission DECIMAL(26,8) DEFAULT 0 NULL,
		    	total DECIMAL(26,8) DEFAULT 0 NULL,
		    	date_created BIGINT(20) UNSIGNED NULL,
		    	PRIMARY KEY  (id),
		    	UNIQUE KEY order_id (order_id),
		    	KEY vendor_id (vendor_id)
			) $collate;";

		    return apply_filters( 'obp_get_sql_create_new_tables', $sql );
		}

		public function get_sessions_table_name(){
			global $wpdb;
			return $wpdb->prefix. 'obp_sessions';
		}

		public function get_order_meta_table_name(){
			global $wpdb;
			return $wpdb->prefix . 'obp_order_meta';
		}

		public function get_order_meta_queue_table_name(){
			global $wpdb;
			return $wpdb->prefix . 'obp_order_meta_queue';
		}

		public function get_order_holding_table_name(){
			global $wpdb;
			return $wpdb->prefix . 'obp_order_holding';
		}

		public function get_order_balance_table_name(){
			global $wpdb;
			return $wpdb->prefix . 'obp_order_balance';
		}

		public function get_payout_method_info_table_name(){
			global $wpdb;
			return $wpdb->prefix . 'obp_payout_method_info';
		}

		public function get_day_off_table_name(){
			global $wpdb;
			return $wpdb->prefix . 'obp_day_off';
		}

		public function get_commission(){
			global $wpdb;
			return $wpdb->prefix.'obp_commission';
		}

	}
}