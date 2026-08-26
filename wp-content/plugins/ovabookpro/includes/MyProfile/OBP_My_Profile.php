<?php
namespace BookPro\MyProfile;

use BookPro\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_My_Profile") ) {
	

	class OBP_My_Profile {

		use SingletonTrait;

		public static function obp_update_profile_args( $args = array() ){
			$user_id 	= get_current_user_id();
			$user 		= obp_get_user( $user_id );

			$errors = array(
				'empty_phone_numner' 	=> esc_html__( 'Phone number cannot be empty.', 'ovabookpro' ),
				'invalid_phone_number' 	=> esc_html__( 'Invalid phone number.', 'ovabookpro' ),
				'invalid_first_name' 	=> esc_html__( 'First name is not valid.', 'ovabookpro' ),
				'empty_first_name' 		=> esc_html__( 'First name cannot be empty.', 'ovabookpro' ),
				'invalid_last_name' 	=> esc_html__( 'Last name is not valid.', 'ovabookpro' ),
				'empty_last_name' 		=> esc_html__( 'Last name cannot be empty.', 'ovabookpro' ),
				'empty_nickname' 		=> esc_html__( 'Nickname cannot be empty.', 'ovabookpro' ),
				'invalid_nickname' 		=> esc_html__( 'Nickname is not valid.', 'ovabookpro' ),
			);

			$messages = array();

			if ( isset( $args['update_status'] ) ) {
				switch ( $args['update_status'] ) {
					case 'success':
						$messages['success'] = esc_html__( 'Update successful.', 'ovabookpro' );
						break;
					case 'error':
						$messages['danger'] = esc_html__( 'Update failed.', 'ovabookpro' );
						break;
					default:
						break;
				}
			}

			$args =  array(
				'errors' 	=> $errors,
				'messages' 	=> $messages,
				'user' 		=> $user,
			);

			return apply_filters( 'obp_update_profile_args', $args );
		}


		public static function obp_change_password_args( $args = array() ){
			$errors = array(
				'empty_old_pass' 		=> esc_html__( 'Old password cannot be empty.', 'ovabookpro' ),
				'empty_new_pass' 		=> esc_html__( 'New password cannot be empty.', 'ovabookpro' ),
				'empty_confirm_pass' 	=> esc_html__( 'Confirm password cannot be empty.', 'ovabookpro' ),
				'not_match' 			=> esc_html__( 'New password and confirm password must match.', 'ovabookpro' ),
			);

			$messages = array();

			if ( isset( $args['password_status'] ) ) {
				switch ( $args['password_status'] ) {
					case 'success':
						$messages['success'] = esc_html__( 'Update new password successful.', 'ovabookpro' );
						break;
					case 'error':
						$messages['danger'] = esc_html__( 'Update new password failed.', 'ovabookpro' );
						break;
					case 'not_match':
						$messages['danger'] = esc_html__( 'The old password does not match.', 'ovabookpro' );
					break;
					default:
						break;
				}
			}

			$status = isset( $args['password_status'] ) ? $args['password_status'] : '';

			$args = array(
				'errors' 	=> $errors,
				'messages' 	=> $messages,
				'status' 	=> $status,
			);

			return apply_filters( 'obp_change_password_args', $args );
		}

		public static function obp_delete_account_args( $args = array() ){
			$messages = array();

			if ( isset( $args['delete_status'] ) ) {
				switch ( $args['delete_status'] ) {
					case 'success':
						$messages['success'] = esc_html__( 'The account has been deleted successfully.', 'ovabookpro' );
						break;
					default:
						break;
				}
			}

			$status = isset( $args['delete_status'] ) ? $args['delete_status'] : '';


			$args = array(
				'messages' 	=> $messages,
				'status' 	=> $status,
			);

			return apply_filters( 'obp_delete_account_args', $args );
		}
	}
}