<?php 
namespace BookPro\Staff;
defined( 'ABSPATH' ) || exit;

use BookPro\OBP_Calendar;
use BookPro\OBP_Permission;
use BookPro\Role\OBP_Role;
use BookPro\Order\OBP_Order_Meta;
use BookPro\StaffDayOff\OBP_Day_Off;
use BookPro\Order\OBP_Order_Meta_Queue;
use BookPro\User\OBP_User;
use WP_User_Query;

/**
 * OBP_Staff class.
 */
class OBP_Staff {

	protected static $_instance = null;


	public static function init(){

		add_action( 'obp_load_member_account_manage-staff_scripts', array( __CLASS__, 'load_scripts' ) );
		add_action( 'obp_load_member_account_edit-staff_scripts', array( __CLASS__, 'load_scripts' ) );
	}

	public static function load_scripts(){

		// time settings
		$time_format = OBP()->settings->general->get('time_format');
		$date_format = OBP()->settings->general->get('date_format');
		$time_step   = apply_filters('obp_business_hour_time_step', 15);
		$min_time    = apply_filters('obp_business_hour_min_time', '06:00am');

		wp_enqueue_script( 'fullcalendar' );
		wp_enqueue_script( 'fullcalendar-daygrid' );
		wp_enqueue_script( 'fullcalendar-timegrid' );
		wp_enqueue_script( 'fullcalendar-list' );
		wp_enqueue_script('fullcalendar-locales' );

		wp_enqueue_style( 'zebra-dialog');
		wp_enqueue_script('zebra-dialog');

		wp_enqueue_style( 'obp-timepicker' );
		wp_enqueue_script( 'obp-timepicker' );

		wp_enqueue_style( 'flatpickr' );
		wp_enqueue_script( 'flatpickr' );
		wp_enqueue_script( 'flatpickr-localize' );
		wp_enqueue_script( 'flatpickr-rangePlugin' );

		wp_enqueue_script( 'obp-frontend-staff', OBP_PLUGIN_URI.'assets/js/frontend/staff.js', array('jquery'), false, true );
		wp_localize_script( 'obp-frontend-staff', 'obp_staff_obj', array(
			'date_format' 		=> $date_format,
			'time_format' 		=> $time_format,
			'time_step' 		=> $time_step,
			'min_time' 			=> $min_time,
			'confirm_dayoff' 	=> esc_html__( 'Are you sure you want to delete permanently this day off?', 'ovabookpro' ),
			'date_req' 			=> esc_html__( 'The start and end date must be filled out', 'ovabookpro' ),
			'date_min' 			=> esc_html__( 'You have to choose at least one time period', 'ovabookpro' ),
			'date_invalid' 		=> esc_html__( 'The next hour must be greater than the previous hour', 'ovabookpro' ),
			'role_req' 			=> esc_html__( 'Role is required', 'ovabookpro' ),
			'username_req' 		=> esc_html__( 'Username is required', 'ovabookpro' ),
			'email_req' 		=> esc_html__( 'Email is required', 'ovabookpro' ),
			'email_invalid' 	=> esc_html__( 'Email is invalid', 'ovabookpro' ),
			'password_req' 		=> esc_html__( 'Password is required', 'ovabookpro' ),
			'nickname_req' 		=> esc_html__( 'Nickname is required', 'ovabookpro' ),
			'empty_date' 		=> esc_html__( 'Dates cannot be empty.', 'ovabookpro' ),
			'empty_time' 		=> esc_html__( 'Off hours cannot be empty.', 'ovabookpro' ),
			'invalid_time' 		=> esc_html__( 'Off hours is invalid.', 'ovabookpro' ),
			'invalid_time_day' 	=> esc_html__( 'Off hours must increase gradually.', 'ovabookpro' ),
			'confirm_delete' 	=> esc_html__( 'Are you sure you want to delete this record? This action cannot be undone', 'ovabookpro' ),
			'yes' => esc_html__( 'Yes', 'ovabookpro' ),
			'no' => esc_html__( 'No','ovabookpro' ),
		) );
	}

	public static function get_users_ajax(){

		$vendor_id = OBP_User::get_vendor_id();
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$page 		= isset( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : 1;
		$name 		= isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$sortby 	= isset( $_POST['sortby'] ) ? sanitize_text_field( wp_unslash( $_POST['sortby'] ) ) : 'user_nicename';
		$orderby 	= 'user_nicename';
		$order 		= 'ASC';
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		$posts_per_page = get_option( 'posts_per_page' );

		$offset = absint( absint( $page ) - 1 ) * $posts_per_page;

		switch ( $sortby ) {
			case 'user_nicename_desc':
			$order = 'DESC';
				break;
			case 'ID':
			$orderby 	= 'ID';
				break;

			case 'ID_desc':
			$orderby 	= 'ID';
			$order 		= 'DESC';
				break;
			
			default:
				break;
		}
		

		$args = array(
			'role' 			=> 'Staff',
			'meta_key' 		=> OBP_METABOX.'vendor_id',
			'meta_value' 	=> $vendor_id,
			'orderby' 		=> $orderby,
			'order' 		=> $order,
			'number' 		=> $posts_per_page,
		);

		if ( $name ) {
			$args['search'] = '*'.$name.'*';
			$args['search_columns'] = array( 'user_login', 'user_email', 'user_nicename' );
		}

		if ( $offset ) {
			$args['offset'] = $offset;
		}

		$the_query = new WP_User_Query( $args );

		return $the_query;
	}


	public static function get_data_calendar_by_user_id( $user_id ){
		$order_meta_queue_calendar 	= OBP_Order_Meta_Queue::get_data_calendar_staff_schedule_by_user_id( $user_id );
		$staff_day_off_calendar 	= OBP_Day_Off::get_data_calendar_dayoff_by_user_id( $user_id );

		$result = array_merge_recursive( $order_meta_queue_calendar );
		if ( ! empty( $staff_day_off_calendar ) ) {
			$result[] = $staff_day_off_calendar;
		}

		return $result;
	}


	public static function edit_staff_args(){
		global $wp;
		$user_id 	= isset( $wp->query_vars['edit-staff'] ) ? absint( wp_unslash(  $wp->query_vars['edit-staff'] ) ) : '';
		$email   = $username = $first_name = $last_name = $nickname = $position = $role = $description = $id_avatar = $avatar_url = '';

		if( $user_id ) {
			$user  			= obp_get_user( $user_id );
			$email 			= $user->get_user_email();
			$username		= $user->get_user_login();
			$first_name 	= $user->get_first_name();
			$last_name 		= $user->get_last_name();
			$nickname 		= $user->get_nickname();
			$description 	= $user->get_description();
			$position 		= $user->get_position();
			$role 		    = $user->get_role();
			$id_avatar 		= $user->get_avatar_id();
			$avatar_url 	= wp_get_attachment_url( $id_avatar );

			// edit: change text
			$text_button = esc_html__('Update Staff','ovabookpro');
			$obp_title   = esc_html__('Edit Staff','ovabookpro');
		} else {
			// add: change text
			$text_button = esc_html__('Add Staff','ovabookpro');
			$obp_title   = esc_html__('Add Staff','ovabookpro');
		}

		$roles = OBP_Role::get_all();

		$args = array(
			'user_id' 		=> $user_id,
			'obp_title' 	=> $obp_title,
			'text_button' 	=> $text_button,
			'id_avatar' 	=> $id_avatar,
			'avatar_url' 	=> $avatar_url,
			'username' 		=> $username,
			'email' 		=> $email,
			'first_name' 	=> $first_name,
			'last_name' 	=> $last_name,
			'nickname' 		=> $nickname,
			'description' 	=> $description,
			'position' 		=> $position,
			'role' 			=> $role,
			'roles' 		=> $roles
		);

		return apply_filters( 'obp_staff_edit_staff_args', $args );

	}

	public static function get_list_staff(){
		$list_staff = array();
		$user_id 	= get_current_user_id();
		$staff_ids 	= array();

		if ( OBP_Permission::is_vendor() ) {
			$args = array(
				'orderby' 			=> 'meta_value',
				'meta_value_num' 	=> 'nickname',
				'meta_key' 			=> OBP_METABOX.'vendor_id',
				'meta_value' 		=> $user_id,
				'fields' 			=> 'ID',
			);

			$user = obp_get_user( $user_id );
			$list_staff[$user_id] = $user->get_nickname();
			$staff_ids = get_users( $args );

		} elseif ( OBP_Permission::is_staff() ) {
			$vendor_id = get_user_meta( $user_id, OBP_METABOX.'vendor_id', true );
			$args = array(
				'orderby' 			=> 'meta_value',
				'meta_value_num' 	=> 'nickname',
				'meta_key' 			=> OBP_METABOX.'vendor_id',
				'meta_value' 		=> $vendor_id,
				'fields' 			=> 'ID',
			);

			$staff_ids = get_users( $args );
		}

		if ( count( $staff_ids ) > 0 ) {
			foreach ( $staff_ids as $staff_id ) {
				$staff = obp_get_user( $staff_id );
				$list_staff[$staff_id] = $staff->get_nickname();
			}
		}

		return $list_staff;
	}

	public static function get_view_schedule_staff(){
		$vendor_id = obp_get_vendor_id();
		$args = array(
			'orderby' 			=> 'meta_value',
			'meta_value_num' 	=> 'nickname',
			'meta_key' 			=> OBP_METABOX.'vendor_id',
			'meta_value' 		=> $vendor_id,
			'fields' 			=> 'ID',
		);

		$staff_ids = get_users( $args );
		$result = array();

		$current_user_id 	= get_current_user_id();
		$current_user 		= obp_get_user( $current_user_id );
		$result[][$current_user_id] = $current_user->get_nickname();

		if ( count( $staff_ids ) > 0 ) {
			foreach ( $staff_ids as $staff_id ) {
				$staff = obp_get_user( $staff_id );
				$role_id = $staff->get_role();
				$caps = OBP()->get_post_meta( $role_id, 'capabilities', [] );
				if ( in_array( 'staff_schedule', $caps ) ) {
					$result[$role_id][$staff_id] = $staff->get_nickname();
				}
			}
		}

		return $result;
	}

	public static function get_priority_staff_id( $service_id, $date_timestamp, $staff_exclude = array() ){

		global $wpdb;

		// the time
		$from 	= $date_timestamp;
		$to 	= strtotime("+1 day", $from);

		$service = obp_get_service( $service_id );

		$staff_id = 0;

		$service_staff_ids = $service->get_staff_ids();

		$staff_count_arr = array();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$order_meta_queue = $wpdb->get_results(
			$wpdb->prepare("SELECT *
				FROM {$wpdb->prefix}obp_order_meta_queue
				WHERE start_date BETWEEN %d AND %d AND staff_id IN (%s)",
				esc_sql( $from ),
				esc_sql( $to ),
				implode(', ', $service_staff_ids )
			)
		); 

		$wpdb->flush();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$order_holding = $wpdb->get_results(
			$wpdb->prepare("
				SELECT *
				FROM {$wpdb->prefix}obp_order_holding
				WHERE start_date BETWEEN %d AND %d AND staff_id IN (%s)",
				esc_sql( $from ),
				esc_sql( $to ),
				implode( ', ', $service_staff_ids )
			)
		); 

		$wpdb->flush();

		// Order Meta Queue
		if ( ! empty( $order_meta_queue ) ) {

			foreach ( $order_meta_queue as $item ) {
				$duration = absint( $item->end_date ) - absint( $item->start_date );
				$staff_count_arr[] = array(
					'duration' 	=> $duration,
					'staff_id' 	=> $item->staff_id,
				);
			}
		}

		// Order Holding
		if ( ! empty( $order_holding ) ) {
			foreach ( $order_holding as $item ) {
				$duration = absint( $item->end_date ) - absint( $item->start_date );
				$staff_count_arr[] = array(
					'duration' 	=> $duration,
					'staff_id' 	=> $item->staff_id,
				);
			}
		}
		
		// Sort staff ids
		$new_staff_id = array();
		if ( count( $staff_count_arr ) > 0 ) {
			// sort
			usort($staff_count_arr, function($a, $b) {
		        return absint( $a['duration'] ) - absint( $b['duration'] );
		    });

			while ( count( $new_staff_id ) - count( $staff_count_arr ) <= 0 ) {
				$id = $staff_count_arr[0]['staff_id'];
				$new_staff_id[] = $id;
				array_splice( $staff_count_arr , 0, 1);
			}

			// Remove Staff ID not belong service
			foreach ( $new_staff_id as $key => $value ) {
				if ( ! in_array( $value, $service_staff_ids ) ) {
					array_splice($new_staff_id, $key, 1);
				}
			}
		}

		if ( count( $service_staff_ids ) > count( $new_staff_id ) ) {
			$staff_id_diff 		= array_diff( $service_staff_ids, $new_staff_id );
			$staff_id_diff 		= array_values( $staff_id_diff );
			$service_staff_ids 	= array_merge( $staff_id_diff, $new_staff_id );

			// Exclude staff ids
			if ( count( $staff_exclude ) > 0 ) {
				$service_staff_ids = array_diff( $service_staff_ids, $staff_exclude );
				$service_staff_ids = array_values( $service_staff_ids );
			}

			if ( count( $service_staff_ids ) > 0 ) {
				$staff_id = $service_staff_ids[0];
			}

		} else {

			// Exclude staff ids
			if ( count( $staff_exclude ) > 0 ) {
				$new_staff_id = array_diff( $new_staff_id, $staff_exclude );
				$new_staff_id = array_values( $new_staff_id );
			}

			if ( count( $new_staff_id ) > 0 ) {
				$staff_id = $new_staff_id[0];
			}

		}

		return $staff_id;

	}

	/**
	 * Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}