<?php

namespace BookPro\Plan;

use BookPro\Traits\SingletonTrait;
use BookPro\User\OBP_User;

use BookPro\OBP_Permission;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Plan_Hooks') ) {

	class OBP_Plan_Hooks {

		use SingletonTrait;

		public function __construct(){
			
			$hooks = array(
				'obp_add_plan',
				'obp_save_plan',
				'obp_edit_plan',
				'obp_remove_plan',
				'obp_add_business_time',
				'obp_show_plan_info',
				'obp_special_service_time',
				'obp_add_service_time',
			);

			foreach ( $hooks as $hook ) {
				add_action( 'wp_ajax_'.$hook, array( $this, $hook ) );
				add_action( 'wp_ajax_nopriv_'.$hook, array( $this, $hook ) );
			}

			// Scripts
			add_action( 'obp_load_member_account_manage-plan_scripts', array( $this, 'load_scripts' ) );
		}

		public function load_scripts(){
			wp_enqueue_script( 'fullcalendar' );
			wp_enqueue_script( 'fullcalendar-daygrid' );
			wp_enqueue_script( 'fullcalendar-timegrid' );
			wp_enqueue_script( 'fullcalendar-list' );
			wp_enqueue_script('fullcalendar-locales' );

			wp_enqueue_style( 'obp-timepicker' );
			wp_enqueue_script( 'obp-timepicker' );

			wp_enqueue_style( 'zebra-dialog');
			wp_enqueue_script('zebra-dialog');

			wp_enqueue_style( 'flatpickr' );
			wp_enqueue_script( 'flatpickr' );
			wp_enqueue_script( 'flatpickr-localize' );
			wp_enqueue_script( 'flatpickr-rangePlugin' );

			wp_enqueue_script('obp-plan', OBP_PLUGIN_URI.'assets/js/frontend/plan.js' , array('jquery'), false, true );
		}

		public function obp_add_service_time(){
		

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
	
				obp_get_template('manage-plan/service-time.php');
	
			}

		
			wp_die();
		}

		public function obp_special_service_time(){
	

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {

				$service_ids = isset( $_POST['service_ids'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['service_ids'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				$cur_service_ids = isset( $_POST['cur_service_ids'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['cur_service_ids'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				
				$remaining_sv_ids = array_diff( $service_ids, $cur_service_ids );

		
				foreach ( $remaining_sv_ids as $service_id ) {
					$service = obp_get_service( $service_id );
					obp_get_template( 'manage-plan/special-service-item.php', array( 'service' => $service ) );
				}
		
			}

	
			wp_die();
		}

		public function obp_add_plan(){
		
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_add_plan' ) && OBP_Permission::user_can('manage_plan') ) {
				obp_manage_plan_add_new();
			}
	
			wp_die();
		}

		public function obp_edit_plan(){
	
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_edit_plan' ) && OBP_Permission::user_can('manage_plan') ) {
				$post_id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
				$args = array(
					'post_id' => $post_id
				);
				obp_manage_plan_edit( $args );
			}

			wp_die();
		}

		public function obp_add_business_time(){
	
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_add_business_time' ) && OBP_Permission::user_can('manage_plan') ) {
				
				obp_get_template("manage-plan/custom-time.php");

			}
	
			wp_die();
		}

		public function obp_show_plan_info(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				$plan_id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
				$plan = obp_get_plan( $plan_id );
		
				obp_get_template( 'manage-plan/plan-info.php', array( 'plan' => $plan ) );
		
			}
	
			wp_die();
		}

		public function obp_save_plan(){

			$args = array();

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_save_plan' ) && OBP_Permission::user_can('manage_plan') ) {

				$vendor_id = OBP_User::get_vendor_id();

				$start_date 	= isset( $_POST['start_date'] ) ? strtotime( sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) ) : current_time( 'timestamp' );
				$end_date 		= isset( $_POST['end_date'] ) ? strtotime( sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) ) : current_time( 'timestamp' );
				$status 		= isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'open';
				$service_ids 	= isset( $_POST['service_ids'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['service_ids'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$time_type 		= isset( $_POST['business_type'] ) ? sanitize_text_field( wp_unslash( $_POST['business_type'] ) ) : 'full_time';
				$service_type 	= isset( $_POST['service_type'] ) ? sanitize_text_field( wp_unslash( $_POST['service_type'] ) ) : '';
				$times 			= isset( $_POST['business_hours'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['business_hours'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$plan_id = isset( $_POST['plan_id'] ) ? sanitize_text_field( wp_unslash( $_POST['plan_id'] ) ) : '';

				$special_service = isset( $_POST['special_service'] ) ? sanitize_text_field( wp_unslash( $_POST['special_service'] ) ) : '';

				$data_special_services = isset( $_POST['data_special_services'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['data_special_services'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				if ( ! empty( $service_ids ) ) {
					$service_ids = implode("|", $service_ids );
				}

				$data_special_services = array_map(function($value){
					if ( isset( $value['time'] ) && is_array( $value['time'] ) ) {
						foreach ( $value['time'] as $key => $val ) {
							$value['time'][$key]['start_hour'] 	= gmdate("H:i", $val['start_hour'] );
							$value['time'][$key]['end_hour'] 	= gmdate("H:i", $val['end_hour'] );
						}
					}
					return $value;
				}, $data_special_services );

				// Convert times : seconds to H:i
				if ( ! empty( $times ) ) {
					$times = array_map(function( $value ){
						$value['start_hour'] 	= gmdate("H:i", $value['start_hour'] );
						$value['end_hour'] 		= gmdate("H:i", $value['end_hour'] );
						return $value;
					}, $times);
				}

				$meta_input = array(
					OBP_METABOX.'vendor_id' 			=> $vendor_id,
					OBP_METABOX.'start_date' 			=> $start_date,
					OBP_METABOX.'end_date' 				=> $end_date,
					OBP_METABOX.'status' 				=> $status,
					OBP_METABOX.'service_ids' 			=> $service_ids,
					OBP_METABOX.'service_type' 			=> $service_type,
					OBP_METABOX.'time_type' 			=> $time_type,
					OBP_METABOX.'times' 				=> $times,
					OBP_METABOX.'special_service' 		=> $special_service,
					OBP_METABOX.'data_special_services' => $data_special_services,
				);

				$post_arr = array(
					'post_status' 	=> 'publish',
					'post_type' 	=> 'obp_plan',
					'meta_input' 	=> $meta_input,
				);

				if ( $plan_id ) {

					foreach ( $meta_input as $key => $value ) {
						update_post_meta( $plan_id, $key, $value );
					}

					if ( is_wp_error( $plan_id ) ) {
						OBP()->message->add( esc_html__( 'Update failed', 'ovabookpro' ), 'error' );
					} else {
						OBP()->message->add( esc_html__( 'Updated successfully', 'ovabookpro' ) );
					}

				} else {
					$plan_id = wp_insert_post( $post_arr, true );

					if ( is_wp_error( $plan_id ) ) {
						OBP()->message->add( esc_html__( 'Update failed', 'ovabookpro' ), 'error' );
					} else {
						// update post title
						wp_update_post( array( 'ID' => $plan_id, 'post_title' => '#'.$plan_id ) );
						OBP()->message->add( esc_html__( 'Updated successfully', 'ovabookpro' ) );
					}
				}
				
			}
			wp_die();

		}

		public function obp_remove_plan(){

			$args = array();

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_remove_plan' ) && OBP_Permission::user_can('manage_plan') ) {

				$post_id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';

				if ( wp_delete_post( $post_id, true ) ) {

					OBP()->message->add( esc_html__( 'Deleted successfully', 'ovabookpro' ) );
				} else {
					OBP()->message->add( esc_html__( 'Delete failed', 'ovabookpro' ), 'error' );
				}
			}
			wp_die();
		}
	}

}
