<?php 
namespace BookPro\Staff;

use BookPro\Traits\SingletonTrait;
use BookPro\StaffDayOff\OBP_Day_Off;
use BookPro\OBP_Calendar;
use BookPro\OBP_Permission;
use BookPro\User\OBP_User;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Staff_Ajax') ) {
	class OBP_Staff_Ajax {

		use SingletonTrait;
		
		/**
		 * @var bool
		 */
		protected static $_loaded = false;

		public function __construct(){

			if ( self::$_loaded ) {
				return;
			}
			
			if (!defined('DOING_AJAX') || !DOING_AJAX)
				return;

			$this->init();

			self::$_loaded = true;
		}

		public function init(){

			$arr_ajax =  array(
				'obp_save_edit_staff',
				'obp_add_off_time',
				'obp_add_day_off',
				'obp_save_day_off',
				'obp_delete_day_off',
				'obp_edit_day_off',
				'obp_delete_staff',
				'obp_staff_load_data',
			);

			foreach($arr_ajax as $val){
				add_action( 'wp_ajax_'.$val, array( $this, $val ) );
				add_action( 'wp_ajax_nopriv_'.$val, array( $this, $val ) );
			}
		}

		public function obp_delete_staff(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('manage_staff') ) {

				$staff_id = isset( $_POST['staff_id'] ) ? sanitize_text_field( wp_unslash( $_POST['staff_id'] ) ) : '';

				wp_delete_user( $staff_id );

				OBP()->message->add( esc_html__( 'Deleted successfully', 'ovabookpro' ) );
			}

			wp_die();
		}

		public function obp_delete_day_off(){
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('manage_staff') ) {

				$day_off_id = isset( $_POST['day_off_id'] ) ? sanitize_text_field( wp_unslash( $_POST['day_off_id'] ) ) : '';

				$check = OBP_Day_Off::delete_staff_day_off( $day_off_id );

				if ( $check ) {
					OBP()->message->add( esc_html__( 'Deleted successfully', 'ovabookpro' ) );
				} else {
					OBP()->message->add( esc_html__( 'Delete failed', 'ovabookpro' ) );
				}
			}

			wp_die();
		}

		public function obp_staff_load_data(){
			$response = array(
				'staff_html' 		=> '',
				'pagination_html' 	=> '',
			);
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('manage_staff') ) {
				
				$user_query = OBP_Staff::get_users_ajax();
				ob_start();

				if ( ! empty( $user_query->get_results() ) ) {
					$i = 0;
					foreach ( $user_query->get_results() as $user ) {
						obp_get_template( 'manage-staff/staff-item.php', array( 'user' => $user, 'key' => $i ) );
						$i++;
					}
				} else {
					?>
					<td colspan="4"><?php esc_html_e( 'No staff found.', 'ovabookpro' ); ?></td>
					<?php
				}
				$response['staff_html'] = ob_get_clean();

				ob_start();
				obp_get_template("manage-staff/staff-pagination.php", array( 'user_query' => $user_query ) );
				$response['pagination_html'] = ob_get_clean();
			}

			wp_send_json( $response );
			wp_die();
		}

		public function obp_save_day_off(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('manage_staff') ) {
				$vendor_id = OBP_User::get_vendor_id();

				$day_off_id = isset( $_POST['day_off_id'] ) ? sanitize_text_field( wp_unslash( $_POST['day_off_id'] ) ) : '';
				$staff_id 	= isset( $_POST['staff_id'] ) ? sanitize_text_field( wp_unslash( $_POST['staff_id'] ) ) : '';
				$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
				$end_date 	= isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
				$time 		= isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : 'full_time';
				$hour_off 	= isset( $_POST['hour_off'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['hour_off'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				$start_date = strtotime( $start_date );
				$end_date 	= strtotime( $end_date );

				// Convert times : seconds to H:i
				if ( ! empty( $hour_off ) ) {
					$hour_off = array_map(function( $value ){
						$value['start_hour'] 	= gmdate("H:i", $value['start_hour'] );
						$value['end_hour'] 		= gmdate("H:i", $value['end_hour'] );
						return $value;
					}, $hour_off);
				}

				$hour_off = maybe_serialize( $hour_off );

				$data = array(
					'vendor_id' 	=> $vendor_id,
					'staff_id' 		=> $staff_id,
					'start_date' 	=> $start_date,
					'end_date' 		=> $end_date,
					'time' 			=> $time,
					'hour_off' 		=> $hour_off,
				);


				if ( $day_off_id ) {
					$dayoff_id = OBP_Day_Off::update( $data, $day_off_id );
				} else {
					$dayoff_id = OBP_Day_Off::add( $data );
				}
				
				if ( $dayoff_id ) {
					OBP()->message->add( esc_html__( 'Updated successfully', 'ovabookpro' ) );
				} else {
					OBP()->message->add( esc_html__( 'Update failed', 'ovabookpro' ), 'error');
				}
			}

			wp_die();
		}

		public function obp_add_off_time(){
			
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('manage_staff') ) {
		
				obp_get_template("manage-staff/custom-time.php");
			
			}
	
			wp_die();
		}

		public function obp_save_edit_staff(){

			$response = array(
				'url' 		=> '',
				'redirect' 	=> false,
			);

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('manage_staff') ) {

				$vendor_id 	 = OBP_User::get_vendor_id();
				$user_id     = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
				$username    = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
				$avatar		 = isset( $_POST['avatar'] ) ? sanitize_text_field( wp_unslash( $_POST['avatar'] ) ) : '';
				$email       = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
				$first_name  = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
				$last_name   = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
				$nickname    = isset( $_POST['nickname'] ) ? sanitize_text_field( wp_unslash( $_POST['nickname'] ) ) : '';
				$position    = isset( $_POST['position'] ) ? sanitize_text_field( wp_unslash( $_POST['position'] ) ) : '';
				$role    	 = isset( $_POST['staff_role'] ) ? sanitize_text_field( wp_unslash( $_POST['staff_role'] ) ) : ( isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : '' );
				$description = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';

				$password    	= isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '';

				$userdata = array(
					'ID' 					=> $user_id,
					'user_pass' 			=> $password,
					'user_login' 			=> $username,
					'user_email' 			=> $email,
					'nickname' 				=> $nickname,
					'first_name' 			=> $first_name,
					'last_name' 			=> $last_name,
					'description' 			=> $description,
					'show_admin_bar_front' 	=> false,
					'role' 					=> 'staff',
				);
				
				if ( $user_id ) {
					$user_id = wp_update_user( $userdata );

					if ( ! is_wp_error( $user_id ) ) {

						update_user_meta( $user_id, OBP_METABOX.'avatar', $avatar );
						update_user_meta( $user_id, OBP_METABOX.'position', $position );
						update_user_meta( $user_id, OBP_METABOX.'role_id', $role );
						update_user_meta( $user_id, OBP_METABOX.'vendor_id', $vendor_id );

						OBP()->message->add( esc_html__( 'Updated successfully', 'ovabookpro' ) );

					} else {
						OBP()->message->add( esc_html__( 'Update failed', 'ovabookpro' ), 'error');
					}
				} else {
					$user_id = wp_insert_user( $userdata );
					if ( ! is_wp_error( $user_id ) ) {

						update_user_meta( $user_id, OBP_METABOX.'avatar', $avatar );
						update_user_meta( $user_id, OBP_METABOX.'position', $position );
						update_user_meta( $user_id, OBP_METABOX.'role_id', $role );
						update_user_meta( $user_id, OBP_METABOX.'vendor_id', $vendor_id );

						OBP()->message->add( esc_html__( 'Added successfully', 'ovabookpro' ) );
						$response['redirect'] = true;
						$endpoint = OBP()->endpoint->get_endpoint('edit-staff');
						$member_account_url = obp_member_account_url();
						$url = OBP()->endpoint->get_endpoint_url( $endpoint, $user_id, $member_account_url );
						$response['url'] = $url;
					} else {
						OBP()->message->add( esc_html__( 'Username or email address already exists.', 'ovabookpro' ), 'error');
					}
				}

			}
			wp_send_json( $response );
			wp_die();
		}

		public function obp_add_day_off() {
		
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('manage_staff') ) {
			
				obp_get_template("manage-staff/add-dayoff.php" );
		
			}
		
			wp_die();
		}

		public function obp_edit_day_off(){
		
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('manage_staff') ) {
				$day_off_id = isset( $_POST['day_off_id'] ) ? sanitize_text_field( wp_unslash( $_POST['day_off_id'] ) ) : '';
			
				obp_get_template("manage-staff/edit-dayoff.php", array( 'day_off_id' => $day_off_id ) );
	
			}
		
			wp_die();
		}

	}
	
}