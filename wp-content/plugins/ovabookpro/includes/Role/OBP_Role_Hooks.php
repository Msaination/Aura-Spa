<?php
namespace BookPro\Role;
use BookPro\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

use BookPro\User\OBP_User;
use BookPro\OBP_Permission;

if ( ! class_exists('OBP_Role_Hooks') ) {
	
	class OBP_Role_Hooks {

		use SingletonTrait;


		public function __construct(){

			$hooks = array(
				'obp_save_role',
				'obp_edit_role',
				'obp_remove_role',
			);

			foreach ( $hooks as $hook ) {
				add_action( 'wp_ajax_'.$hook, array( $this, $hook ) );
				add_action( 'wp_ajax_nopriv_'.$hook, array( $this, $hook ) );
			}

			// Scripts
			add_action( 'obp_load_member_account_manage-role_scripts', array( $this, 'load_scripts' ) );
		}

		public function load_scripts(){

			wp_enqueue_style( 'zebra-dialog');
			wp_enqueue_script('zebra-dialog');
			
			wp_enqueue_script('obp-role', OBP_PLUGIN_URI.'assets/js/frontend/role.js' , array('jquery'), false, true );
		}

		public function obp_remove_role(){

			$args = array();

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_remove_role' ) && OBP_Permission::user_can('manage_role') ) {

				$post_id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';

				if ( wp_delete_post( $post_id, true ) ) {
					$args['success'] = 'remove';
				} else {
					$args['error'] = 'remove';
				}
			}

			obp_get_template('manage-role/manage-role.php', $args );

			wp_die();
		}

		public function obp_save_role(){

			$args = array();

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_save_role' ) && OBP_Permission::user_can('manage_role') ) {

				$vendor_id = OBP_User::get_vendor_id();
				$role_id 	= isset( $_POST['role_id'] ) ? sanitize_text_field( wp_unslash( $_POST['role_id'] ) ) : '';
				$role_name 	= isset( $_POST['role_name'] ) ? sanitize_text_field( wp_unslash( $_POST['role_name'] ) ) : '';
				$cap 		= isset( $_POST['cap'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['cap'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				$meta_input = array(
					OBP_METABOX.'vendor_id' 	=> $vendor_id,
					OBP_METABOX.'capabilities' 	=> $cap,
				);

				$post_arr = array(
					'post_status' 	=> 'publish',
					'post_type' 	=> 'obp_role',
					'post_title' 	=> $role_name,
					'meta_input' 	=> $meta_input,
				);

				if ( $role_id ) {

					$post_arr['ID'] = $role_id;
					$role_id = wp_update_post( $post_arr, true );

					if ( is_wp_error( $role_id ) ) {
						$args['error'] = 'update';
					} else {
						$args['success'] = 'update';
					}

				} else {
					$role_id = wp_insert_post( $post_arr, true );

					if ( is_wp_error( $role_id ) ) {
						$args['error'] = 'add_new';
					} else {
						$args['success'] = 'add_new';
					}
				}

				
			}

			obp_get_template('manage-role/manage-role.php', $args );
			wp_die();
		}

		public function obp_edit_role(){

			$role_id = isset( $_POST['role_id'] ) ? sanitize_text_field( wp_unslash( $_POST['role_id'] ) ) :  '';

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_edit_role' ) && ! empty( $role_id ) && OBP_Permission::user_can('manage_role') ) {

				$args = array(
					'post_id' => $role_id,
				);

				obp_manage_role_edit( $args );
			}
	
			wp_die();

		}
	}

}