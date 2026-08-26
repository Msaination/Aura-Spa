<?php

namespace BookPro\Plan;

use BookPro\Service\OBP_Service;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Plan') ) {

	class OBP_Plan {

		public static function get_custom_times(){
			return array(
				'morning' 	=> esc_html__( 'Morning', 'ovabookpro' ),
				'afternoon' => esc_html__( 'Afternoon', 'ovabookpro' ),
				'evening' 	=> esc_html__( 'Evening', 'ovabookpro' ),
			);
		}

		public static function get_all(){

			$vendor_id = obp_get_vendor_id();

			$args = array(
				'post_type' 		=> 'obp_plan',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'order' 			=> 'ASC',
				'orderby' 			=> 'meta_value_num',
				'meta_key' 			=> OBP_METABOX.'start_date',
				'meta_query' 		=> array(
					array(
						'key' 	=> OBP_METABOX.'vendor_id',
						'value' => $vendor_id,
					),
				),
				'fields' => 'ids',
			);

			$plan_ids = get_posts( $args );
			$data_all = array();

			if ( $plan_ids  ) {
				foreach ( $plan_ids as $plan_id ) {
					$plan = obp_get_plan( $plan_id );
					$data_all[] = $plan;
				}
			}

			return $data_all;
		}

		public static function get_data_calendar(){
			$plans = self::get_all();
			$all_service_ids 	= OBP_Service::get_service_ids();
			$data_calendar 		= array();


			if ( ! empty( $plans ) ) {
				foreach ( $plans as $plan ) {
					$data_plan = array();
					
					if ( $plan->get_status() == 'closed' ) {
						$key = 'closed';
						if ( count( $plan->get_service_ids() ) != count( $all_service_ids ) ) {
							$key = 'some_closed';
						}
					} else {
						$key = 'all';
						if ( $plan->get_service_type() != 'all_services' ) {
							$key = 'some';
						}
					}
					$data_plan['id'] = $plan->get_id();
					$data_plan['start_date'] 	= gmdate("Y-m-d", $plan->get_start_date() );
					$data_plan['end_date'] 		= gmdate("Y-m-d", strtotime( "+1 day" ,$plan->get_end_date() ) );

					$data_calendar[$key][] = $data_plan;
				}
			}

			return $data_calendar;
		}

		public static function get_all_date_plans(){
			$vendor_id = obp_get_vendor_id();

			$args = array(
				'post_type' 		=> 'obp_plan',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'order' 			=> 'ASC',
				'orderby' 			=> 'meta_value_num',
				'meta_key' 			=> OBP_METABOX.'start_date',
				'meta_query' 		=> array(
					array(
						'key' => OBP_METABOX.'vendor_id',
						'value' => $vendor_id,
					),
				),
				'fields' => 'ids'
			);

			$plan_ids = get_posts( $args );

			$data_all = array();

			if ( $plan_ids ) {
				foreach ( $plan_ids as $plan_id ) {
					$plan = obp_get_plan( $plan_id );

					$start_date = $plan->get_start_date();
					$end_date 	= $plan->get_end_date();

					$data = array();
					$data['id'] = $plan_id;
					$data['start_date'] = gmdate("Y-m-d", $start_date );
					$data['end_date'] 	= gmdate("Y-m-d", $end_date );

					$data_all[] = $data;
				}
			}

			return $data_all;

		}

		public static function get_plan_ids_by_service_id( $vendor_id, $service_id ){

			$current_timestamp = current_time( 'timestamp' );
			$current_timestamp = strtotime("-1 day", $current_timestamp );
			$args = array(
				'post_type' 		=> 'obp_plan',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'order' 			=> 'ASC',
				'orderby' 			=> 'meta_value_num',
				'meta_key' 			=> OBP_METABOX.'start_date',
				'meta_query' 		=> array(
					'relation' => 'AND',
					array(
						'key' 	=> OBP_METABOX.'vendor_id',
						'value' => $vendor_id,
					),
					array(
						'key' 	=> OBP_METABOX.'status',
						'value' => 'open',
					),
					array(
						'key' 		=> OBP_METABOX.'end_date',
						'value' 	=> $current_timestamp,
						'compare' 	=> '>',
						'type' 		=> 'NUMERIC',
					),
					array(
						'relation' => 'OR',
						array(
							'key' 		=> OBP_METABOX.'service_ids',
							'value' 	=> $service_id,
							'compare' 	=> 'REGEXP',
						),
						array(
							'key' 		=> OBP_METABOX.'service_type',
							'value' 	=> 'all_services',
						),
					),
					
				),
				'fields' => 'ids',
			);

			$plan_ids = get_posts( $args );

			return $plan_ids;
		}

		public static function get_plan_id_by_date( $vendor_id, $service_id , $date_timestamp ){
			$args = array(
				'post_type' 		=> 'obp_plan',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'order' 			=> 'ASC',
				'orderby' 			=> 'meta_value_num',
				'meta_key' 			=> OBP_METABOX.'start_date',
				'meta_query' 		=> array(
					'relation' => 'AND',
					array(
						'key' 	=> OBP_METABOX.'vendor_id',
						'value' => $vendor_id,
					),
					array(
						'key' 	=> OBP_METABOX.'status',
						'value' => 'open',
					),
					array(
						'key' 		=> OBP_METABOX.'end_date',
						'value' 	=> $date_timestamp,
						'compare' 	=> '>=',
						'type' 		=> 'NUMERIC',
					),
					array(
						'key' 		=> OBP_METABOX.'start_date',
						'value' 	=> $date_timestamp,
						'compare' 	=> '<=',
						'type' 		=> 'NUMERIC',
					),
					array(
						'relation' => 'OR',
						array(
							'key' 		=> OBP_METABOX.'service_ids',
							'value' 	=> $service_id,
							'compare' 	=> 'REGEXP',
						),
						array(
							'key' 		=> OBP_METABOX.'service_type',
							'value' 	=> 'all_services',
						),
					),
				),
				'fields' => 'ids',
			);

			$plan_ids = get_posts( $args );

			return $plan_ids;

		}


		public static function obp_manage_plan_calendar_args(){
			$data_calendar = self::get_data_calendar();

			$args =  array(
				'data_calendar' => $data_calendar,
			);

			return apply_filters( 'obp_manage_plan_calendar_args', $args );
		}

		public static function obp_manage_plan_list_table_args( $args = array() ){
			$messages = array();

			if ( isset( $args['error'] ) ) {
				switch ( $args['error'] ) {
					case 'add_new':
						$messages['danger'] = esc_html__( 'Create new failed.', 'ovabookpro' );
						break;
					case 'update':
						$messages['danger'] = esc_html__( 'Update failed.', 'ovabookpro' );
						break;
					case 'remove':
						$messages['danger'] = esc_html__( 'Delete failed.', 'ovabookpro' );
						break;
					default:
						break;
				}
			}

			if ( isset( $args['success'] ) ) {
				switch ( $args['success'] ) {
					case 'add_new':
						$messages['success'] = esc_html__( 'Create new successfully.', 'ovabookpro' );
						break;
					case 'update':
						$messages['success'] = esc_html__( 'Update successful.', 'ovabookpro' );
						break;
					case 'remove':
						$messages['success'] = esc_html__( 'Delete successfully.', 'ovabookpro' );
						break;
					default:
						break;
				}
			}

			$list_plans 	= self::get_all();
			$settings 		= OBP()->settings->general;
			$date_format 	= $settings->get( 'date_format', 'Y-m-d' );

			$status_arr = array(
				'open' 		=> esc_html__( 'Open', 'ovabookpro' ),
				'closed' 	=> esc_html__( 'Closed', 'ovabookpro' ),
			);

			$data_mess = array(
				'confirm_remove' => esc_html__( 'Do you really want to delete?', 'ovabookpro' ),
			);
			$data_button = array(
				'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
				'no' 	=> esc_html__( 'No', 'ovabookpro' ),
			);

			$time_slots = self::get_all_date_plans();

			$args = array(
				'time_slots' 	=> $time_slots,
				'data_button' 	=> $data_button,
				'data_mess' 	=> $data_mess,
				'list_plans' 	=> $list_plans,
				'date_format' 	=> $date_format,
				'status_arr'	=> $status_arr,
				'messages'		=> $messages,
			);

			return apply_filters( 'obp_manage_plan_list_table_args', $args );
		}


		public static function obp_manage_plan_add_new_args( $args = array() ){
			$category_service_groups = OBP_Service::get_category_service_groups();
			$errors = array(
				'empty_date' 		=> esc_html__( 'Dates cannot be empty.', 'ovabookpro' ),
				'empty_time' 		=> esc_html__( 'Business hours cannot be empty.', 'ovabookpro' ),
				'invalid_time' 		=> esc_html__( 'Business hours is invalid.', 'ovabookpro' ),
				'invalid_time_day' 	=> esc_html__( 'Business hours must increase gradually.', 'ovabookpro' ),
			);

			$custom_times = self::get_custom_times();

			$args = array_merge( $args, array(
				'category_service_groups' 	=> $category_service_groups,
				'custom_times' 				=> $custom_times,
				'errors' 					=> $errors,
			) );

			return apply_filters( 'obp_manage_plan_add_new_args', $args );
		}

		public static function obp_manage_plan_edit_args( $args = array() ){
			$category_service_groups = OBP_Service::get_category_service_groups();

			$errors = array(
				'empty_date' 		=> esc_html__( 'Dates cannot be empty.', 'ovabookpro' ),
				'empty_time' 		=> esc_html__( 'Business hours cannot be empty.', 'ovabookpro' ),
				'invalid_time' 		=> esc_html__( 'Business hours is invalid.', 'ovabookpro' ),
				'invalid_time_day' 	=> esc_html__( 'Business hours must increase gradually.', 'ovabookpro' ),
			);

			$time_format = OBP()->settings->general->get('time_format','H:i');

			$custom_times = self::get_custom_times();

			$post_id 	= isset( $args['post_id'] ) ? $args['post_id'] : '';

			$plan = obp_get_plan( $post_id );

			$times = $plan->get_times();

			$service_label 	= $plan->get_service_type() == 'all_services' ? esc_attr__( 'All Services', 'ovabookpro' ) : esc_attr__( 'Some Service', 'ovabookpro' );
			$business_label = $plan->get_time_type() == 'full_time' ? esc_attr__( 'Full Time', 'ovabookpro' ) : esc_attr__( 'Custom Time', 'ovabookpro' );

			$args = array_merge( $args, array(
				'plan' 						=> $plan,
				'service_label' 			=> $service_label,
				'category_service_groups' 	=> $category_service_groups,
				'business_label' 			=> $business_label,
				'times' 					=> $times,
				'time_format' 				=> $time_format,
				'errors' 					=> $errors,
			) );

			return apply_filters( 'obp_manage_plan_edit_args', $args );
		}

	}

}



