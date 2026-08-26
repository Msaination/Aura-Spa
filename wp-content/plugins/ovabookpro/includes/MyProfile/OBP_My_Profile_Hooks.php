<?php
namespace BookPro\MyProfile;

use BookPro\Traits\SingletonTrait;
use BookPro\OBP_Mail;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_My_Profile_Hooks') ) {

	class OBP_My_Profile_Hooks {

		use SingletonTrait;

		public function __construct(){

			$hooks = array(
				'obp_update_my_profile',
				'obp_update_password_profile',
				'obp_delete_account_profile',
			);

			foreach ( $hooks as $hook ) {
				add_action( 'wp_ajax_'.$hook, array( $this, $hook ) );
				add_action( 'wp_ajax_nopriv_'.$hook, array( $this, $hook ) );
			}

			// Scripts
			add_action( 'obp_load_member_account_my-profile_scripts', array( $this, 'load_scripts' ) );
			
		}

		public function load_scripts(){
			wp_enqueue_script('obp-my-profile', OBP_PLUGIN_URI.'assets/js/frontend/my-profile.js' , array('jquery'), false, true );
		}

		public function obp_update_my_profile(){

			$args = array();

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'update_my_profile' ) ) {
				
				$user_id 		= get_current_user_id();
				$avatar_id 		= isset( $_POST['avatar_id'] ) ? sanitize_text_field( wp_unslash( $_POST['avatar_id'] ) ) : '';
				$phone_number 	= isset( $_POST['phone_number'] ) ? sanitize_text_field( wp_unslash( $_POST['phone_number'] ) ) : '';
				$first_name 	= isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
				$last_name 		= isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
				$nickname 		= isset( $_POST['nickname'] ) ? sanitize_text_field( wp_unslash( $_POST['nickname'] ) ) : '';
				$description 	= isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';

				update_user_meta( $user_id, OBP_METABOX.'avatar', $avatar_id );
				update_user_meta( $user_id, OBP_METABOX.'phone_number', $phone_number );
				update_user_meta( $user_id, 'first_name', $first_name );
				update_user_meta( $user_id, 'last_name', $last_name );
				update_user_meta( $user_id, 'description', $description );
				update_user_meta( $user_id, 'nickname', $nickname );

				$args['update_status'] = 'success';

			} else {
				$args['update_status'] = 'error';
			}

			obp_update_profile_content( $args );
			wp_die();
		}

		public function obp_update_password_profile(){

			$args = array();

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'update_password' ) ) {
				$user_id 	= get_current_user_id();
				$user 		= obp_get_user( $user_id );
				$old_password 		= isset( $_POST['old_password'] ) ? sanitize_text_field( wp_unslash( $_POST['old_password'] ) ) : '';
				$new_password 		= isset( $_POST['new_password'] ) ? sanitize_text_field( wp_unslash( $_POST['new_password'] ) ) : '';
				$confirm_password 	= isset( $_POST['confirm_password'] ) ? sanitize_text_field( wp_unslash( $_POST['confirm_password'] ) ) : '';

				if ( ! wp_check_password( $old_password, $user->get_password(), $user_id ) ) {
					$args['password_status'] = 'not_match';
					obp_change_password_content( $args );
					wp_die();
				}

				if ( ! empty( $new_password ) ) {
					reset_password( $user->get_user(), $new_password );
					$args['password_status'] = 'success';
				} else {
					$args['password_status'] = 'error';
				}

			}

			obp_change_password_content( $args );
			wp_die();
		}

		public function obp_delete_account_profile(){
			
			$args = array();

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'delete_account' ) ) {

				$user_id 		= get_current_user_id();
				$user 			= obp_get_user( $user_id );
				$user_email 	= $user->get_user_email();
				
				$user_status 	= $user->get_status();
				
				if ( ! in_array( 'administrator', $user->get_user()->roles ) ) {
					if ( $user_status !== 'locked' ) {
						// Send mail
						OBP_Mail::obp_delete_account_mail();
					}
					update_user_meta( $user_id, OBP_METABOX.'user_status', 'locked' );
				}

				$args['delete_status'] = 'success';
				
				wp_logout();
				
			}

			obp_delete_account_content( $args );
			wp_die();
		}

	}
}
