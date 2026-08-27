<?php
namespace BookPro\Role;

use BookPro\User\OBP_User;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Role') ) {
	

	class OBP_Role {

		public static function get_all(){

			$vendor_id = OBP_User::get_vendor_id();
			self::create_staff_role( $vendor_id );

			$args = array(
				'post_type' 		=> 'obp_role',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'order' 			=> 'ASC',
				'orderby' 			=> 'ID',
				'meta_query' 		=> array(
					array(
						'key' 	=> OBP_METABOX.'vendor_id',
						'value' => $vendor_id,
					),
				),
				'fields' => 'ids',
			);

			$role_ids = get_posts( $args );
			$data_all = array();

			if ( count( $role_ids ) > 0 ) {
				foreach ( $role_ids as $role_id ) {
					$vendor_id 	= get_post_meta( $role_id, OBP_METABOX.'vendor_id', true );
					$cap 		= get_post_meta( $role_id, OBP_METABOX.'capabilities', true );
					$data 		= array();
					$data['id'] 			= $role_id;
					$data['name'] 			= get_the_title( $role_id );
					$data['vendor_id'] 		= $vendor_id;
					$data['capabilities'] 	= $cap;
					$data_all[] = $data;
				}
			}

			return $data_all;
		}

		public static function create_staff_role( $vendor_id ){
			// check role exits
			$args = array(
				'post_type' 		=> 'obp_role',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'order' 			=> 'ASC',
				'orderby' 			=> 'ID',
				'meta_query' 		=> array(
					array(
						'key' 	=> OBP_METABOX.'vendor_id',
						'value' => $vendor_id,
					),
				),
				'fields' => 'ids',
			);

			$roles = get_posts( $args );

			if ( count( $roles ) < 1 ) {
				$cap = array(
					'staff_schedule',
				);

				$meta_input = array(
					OBP_METABOX.'vendor_id' 	=> $vendor_id,
					OBP_METABOX.'capabilities' 	=> $cap,
				);

				$post_arr = array(
					'post_status' 	=> 'publish',
					'post_type' 	=> 'obp_role',
					'post_title' 	=> esc_html__( 'Staff', 'ovabookpro' ),
					'meta_input' 	=> $meta_input,
				);
				wp_insert_post( $post_arr, true );
			}

		}

		public static function get_list_capabilities(){
			return array(
				'my_business' 		=> esc_html__( 'Manage Business Info', 'ovabookpro' ),
				'manage_staff' 		=> esc_html__( 'Manage Staff', 'ovabookpro' ),
				'manage_booking' 	=> esc_html__( 'Manage Booking', 'ovabookpro' ),
				'manage_service' 	=> esc_html__( 'Manage Service', 'ovabookpro' ),
				'manage_type' 		=> esc_html__( 'Manage Type', 'ovabookpro' ),
				'manage_schedules' 	=> esc_html__( 'Overall Schedule', 'ovabookpro' ),
				'manage_plan' 		=> esc_html__( 'Manage Plan', 'ovabookpro' ),
				'manage_coupon' 	=> esc_html__( 'Manage Coupon', 'ovabookpro' ),
				'manage_role' 		=> esc_html__( 'Manage Role', 'ovabookpro' ),
				'staff_schedule' 	=> esc_html__( 'Service Staff', 'ovabookpro' ),
			);
		}

		public static function obp_manage_role_listing_args( $args = array() ){
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

			$roles = self::get_all();

			$data_mess = array(
				'confirm_remove' => esc_html__( 'Do you really want to delete?', 'ovabookpro' ),
			);
			$data_button = array(
				'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
				'no' 	=> esc_html__( 'No', 'ovabookpro' ),
			);

			$args = array(
				'roles' 		=> $roles,
				'data_mess' 	=> $data_mess,
				'data_button' 	=> $data_button,
				'messages' 		=> $messages,
			);


			return apply_filters( 'obp_manage_role_listing_args', $args );
		}

		public static function obp_manage_role_add_new_args( $args = array() ){

			$errors = array(
				'empty_name' 	=> esc_html__( 'Name is required.', 'ovabookpro' ),
				'invalid_name' 	=> esc_html__( 'Name is invalid.', 'ovabookpro' ),
			);

			$capabilities = self::get_list_capabilities();

			$capabilities = obp_array_chunk_key($capabilities, 2 );

			$args = array(
				'errors' 		=> $errors,
				'capabilities' 	=> $capabilities,
			);


			return apply_filters( 'obp_manage_role_add_new_args', $args );
		}

		public static function obp_edit_role_args( $args = array() ){
			$post_id = isset( $args['post_id'] ) ? $args['post_id'] : '';

			$role_name 	= get_the_title( $post_id );
			$caps 		= get_post_meta( $post_id, OBP_METABOX.'capabilities', true );

			$capabilities = self::get_list_capabilities();
			$capabilities = obp_array_chunk_key($capabilities, 2 );

			$args = array_merge( $args, array(
				'post_id' 		=> $post_id,
				'role_name' 	=> $role_name,
				'caps' 			=> $caps,
				'capabilities' 	=> $capabilities,
			) );

			return apply_filters( 'obp_edit_role_args', $args );
		}
	}
}