<?php

namespace BookPro\AllSchedule;

use BookPro\Order\OBP_Order;
use BookPro\Traits\SingletonTrait;
use BookPro\OBP_Permission;

if ( ! class_exists("OBP_All_Schedule") ) {
	

	class OBP_All_Schedule {

		use SingletonTrait;

		public function __construct(){

			$hooks = array(
				'obp_filter_all_schedule',
				'obp_filter_staff_schedule',
				'obp_info_schedule',
			);

			foreach ( $hooks as $hook ) {
				add_action( 'wp_ajax_'.$hook, array( $this, $hook ) );
				add_action( 'wp_ajax_nopriv_'.$hook, array( $this, $hook ) );
			}

			add_action( 'obp_load_member_account_overall-schedule_scripts', array( $this, 'overall_schedule_scripts' ) );
			add_action( 'obp_load_member_account_staff-schedule_scripts', array( $this, 'staff_schedule_scripts' ) );
		}

		public function overall_schedule_scripts(){
			wp_enqueue_script( 'fullcalendar' );
			wp_enqueue_script( 'fullcalendar-daygrid' );
			wp_enqueue_script( 'fullcalendar-timegrid' );
			wp_enqueue_script( 'fullcalendar-list' );
			wp_enqueue_script('fullcalendar-locales' );

			wp_enqueue_style('zebra-dialog');
			wp_enqueue_script('zebra-dialog');

			wp_enqueue_script('obp-all-schedules', OBP_PLUGIN_URI.'assets/js/frontend/all-schedules.js' , array('jquery'), false, true );
		}

		public function staff_schedule_scripts(){
			wp_enqueue_script( 'fullcalendar' );
			wp_enqueue_script( 'fullcalendar-daygrid' );
			wp_enqueue_script( 'fullcalendar-timegrid' );
			wp_enqueue_script( 'fullcalendar-list' );
			wp_enqueue_script('fullcalendar-locales' );
			
			wp_enqueue_script('obp-staff-schedule', OBP_PLUGIN_URI.'assets/js/frontend/staff-schedule.js' , array('jquery'), false, true );
		}

		public function obp_info_schedule(){
		
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('manage_schedules') ) {
				$order_id 	= isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
				$staff_id 	= isset( $_POST['staff_id'] ) ? sanitize_text_field( wp_unslash( $_POST['staff_id'] ) ) : '';
				$service_id = isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : '';
				$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
				$end_date 	= isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
				$order 		= obp_get_order( $order_id );
				$staff 		= obp_get_user( $staff_id );
				$service 	= obp_get_service( $service_id );

				$date_format 	= obp_get_date_format();
				$time_format 	= obp_get_time_format();
				$format 		= $date_format.' '.$time_format;
				$start 			= date_i18n( $format, absint( $start_date ) );
				$end 			= date_i18n( $time_format, absint( $end_date ) );
				$from_to 		= $start.' - '.$end;

				$args = array(
					'order' 	=> $order,
					'staff' 	=> $staff,
					'service' 	=> $service,
					'from_to' 	=> $from_to
				);
				obp_get_template( 'overall-schedule/info-schedule.php', $args );
			}
	
			wp_die();
		}

		public function obp_filter_all_schedule(){

			
			$args = array();

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_all_schedule_nonce' ) &&
			OBP_Permission::user_can('manage_schedules') ) {
				$customer_name = isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '';
				$staff_id = isset( $_POST['staff_id'] ) ? sanitize_text_field( wp_unslash( $_POST['staff_id'] ) ) : '';

				if ( ! empty( $customer_name ) ) {
					$order_ids = OBP_Order::get_order_ids_by_customer_name( $customer_name );
					$args['order_ids'] = $order_ids;
				}

				if ( ! empty( $staff_id ) ) {
					$args['staff_id'] = $staff_id;
				}
			
				obp_overall_schedule_calendar_content( $args );
		
			}

	
			wp_die();
		}

		public function obp_filter_staff_schedule(){
		
			$args = array();

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_staff_schedule_nonce' ) &&
			OBP_Permission::user_can('staff_schedule') ) {
				$customer_name = isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '';

				if ( ! empty( $customer_name ) ) {
					$order_ids = OBP_Order::get_order_ids_by_customer_name( $customer_name );
					$args['order_ids'] = $order_ids;
				}

				obp_staff_schedule_calendar_content( $args );
			}

			
			wp_die();
		}
	}
}