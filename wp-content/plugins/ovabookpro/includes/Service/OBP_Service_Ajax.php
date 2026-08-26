<?php 
namespace BookPro\Service;

use BookPro\Traits\SingletonTrait;
use BookPro\OBP_Permission;
use BookPro\Package\OBP_Package;
use BookPro\Type\OBP_Type;
use BookPro\Role\OBP_Role;
use BookPro\Staff\OBP_Staff;
use BookPro\User\OBP_User;


defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Service_Ajax') ) {
	class OBP_Service_Ajax {
		
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
				'obp_save_edit_service',
				'obp_delete_service',
				'obp_filter_service',
				'obp_add_extra_option',
				'obp_add_package_option',
				'obp_reload_type_select_box',
				'obp_show_add_staff',
				'obp_save_staff_service',
				'obp_save_type_service',
			);

			foreach( $arr_ajax as $val ){
				add_action( 'wp_ajax_'.$val, array( $this, $val ) );
				add_action( 'wp_ajax_nopriv_'.$val, array( $this, $val ) );
			}
		}

		public function obp_save_type_service(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can( 'manage_type' ) ) {
				
				$vendor_id = OBP_User::get_vendor_id();
				$name = isset( $_POST['type_name'] ) ? sanitize_text_field( wp_unslash( $_POST['type_name'] ) ) : '';
				

				$post_arr = array(
					'post_title'   	=> $name,
					'post_status'  	=> 'publish',
					'post_type' 	=> 'obp_type',
					'post_author'  	=> get_current_user_id(),
					'meta_input' 	=> array(
						OBP_METABOX.'vendor_id' => $vendor_id
					),
				);

				$post_id = wp_insert_post( $post_arr, true );

				echo esc_html($post_id);
			}

			wp_die();
		}

		public function obp_save_staff_service(){
			$response = [
				'mess' 		=> '',
				'html' 		=> '',
			];

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('manage_staff') ) {

				$vendor_id 	 = OBP_User::get_vendor_id();

				$staff_ids = isset( $_POST['staff_ids'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['staff_ids'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$username    = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
				$avatar		 = isset( $_POST['avatar'] ) ? sanitize_text_field( wp_unslash( $_POST['avatar'] ) ) : '';
				$email       = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
				$first_name  = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
				$last_name   = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
				$nickname    = isset( $_POST['nickname'] ) ? sanitize_text_field( wp_unslash( $_POST['nickname'] ) ) : '';
				$position    = isset( $_POST['position'] ) ? sanitize_text_field( wp_unslash( $_POST['position'] ) ) : '';
				$role   	 = isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : '';
				$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';

				$password = isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '';

				$userdata = array(
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

				$user_id = wp_insert_user( $userdata );

				if ( ! is_wp_error( $user_id ) ) {

					update_user_meta( $user_id, OBP_METABOX.'avatar', $avatar );
					update_user_meta( $user_id, OBP_METABOX.'position', $position );
					update_user_meta( $user_id, OBP_METABOX.'role_id', $role );
					update_user_meta( $user_id, OBP_METABOX.'vendor_id', $vendor_id );
					
					array_push($staff_ids, $user_id);
					// add html
					$list_user = OBP_Staff::get_view_schedule_staff();

					ob_start();

					obp_get_template( 'manage-service/service-staff-list.php', array(
						'list_user' => $list_user,
						'staff_ids' => $staff_ids,
					) );

					$response['html'] = ob_get_clean();

				} else {
					$response['mess'] = esc_html__( 'Username or email address already exists.', 'ovabookpro' );
				}
			}

			echo json_encode( $response );

			wp_die();
		}

		public function obp_show_add_staff(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				$roles = OBP_Role::get_all();
		
				obp_get_template( 'manage-service/add-staff-form.php', array( 'roles' => $roles ) );
	
			}

			
			wp_die();
		}

		public function obp_reload_type_select_box(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				$type_id 	= isset( $_POST['type_id'] ) ? sanitize_text_field( wp_unslash( $_POST['type_id'] ) ) : '';
				$list_type 	= OBP_Type::get_type_ajax();

		
				obp_get_template( 'manage-service/type-select-box.php', array( 'type_id' => $type_id, 'list_type' => $list_type ) );
		
			}

	
			wp_die();
		}

		public function obp_add_package_option(){

		

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				ob_start();
				$package_id = '';
				obp_get_template( 'manage-service/service-package-item.php', array( 'package_id' => $package_id ) );
	
			}	

			wp_die();
		}

		public function obp_add_extra_option(){
	

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
	
				obp_get_template( 'manage-service/service-package-group.php' );
	
			}

	
			wp_die();
		}

		public function obp_filter_service(){
			$response = array(
				'service_html' 		=> '',
				'pagination_html' 	=> '',
			);

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('manage_service') ) {
				$services = OBP_Service::get_service_ajax();
				ob_start();
				if ( $services->have_posts() ) {
					while ( $services->have_posts() ) {
						$services->the_post();
						$id = get_the_ID();
						obp_get_template("manage-service/service-item.php", array( 'id' => $id ) );
					}
				} else {
					?>
					<td colspan="6">
						<?php esc_html_e( 'Services not found.', 'ovabookpro' ); ?>
					</td>
					<?php
				}
				wp_reset_postdata();
				$response['service_html'] = ob_get_clean();

				ob_start();
				obp_get_template("manage-service/service-pagination.php", array( 'services' => $services ) );
				$response['pagination_html'] = ob_get_clean();
			}

			wp_send_json( $response );
		}


		public function obp_save_edit_service() {

			$response = array(
				'url' 		=> '',
				'redirect' 	=> false,
			);

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('manage_service') ) {

				$vendor_id = obp_get_vendor_id();

				$post_id 		= isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
				$service_name 	= isset( $_POST['service_name'] ) ? sanitize_text_field( wp_unslash( $_POST['service_name'] ) ) : '';
				$description    = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
				$type   		= isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
				$price 			= isset( $_POST['price'] ) ? sanitize_text_field( wp_unslash( $_POST['price'] ) ) : '0';

				$sale_price 	= isset( $_POST['sale_price'] ) ? sanitize_text_field( wp_unslash( $_POST['sale_price'] ) ) : '';

				$sale_off_start_date 	= isset( $_POST['sale_off_start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['sale_off_start_date'] ) ) : '';
				$sale_off_end_date 		= isset( $_POST['sale_off_end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['sale_off_end_date'] ) ) : '';

				$hour 			= isset( $_POST['hour'] ) ? sanitize_text_field( wp_unslash( $_POST['hour'] ) ) : '';
				$minute 		= isset( $_POST['minute'] ) ? sanitize_text_field( wp_unslash( $_POST['minute'] ) ) : '';
				$price_type 	= isset( $_POST['price_type'] ) ? sanitize_text_field( wp_unslash( $_POST['price_type'] ) ) : '';
				$color 			= isset( $_POST['color'] ) ? sanitize_text_field( wp_unslash( $_POST['color'] ) ) : '';
				$sale_off_from 	= isset( $_POST['sale_off_from'] ) ? sanitize_text_field( wp_unslash( $_POST['sale_off_from'] ) ) : '';
				$sale_off_to 	= isset( $_POST['sale_off_to'] ) ? sanitize_text_field( wp_unslash( $_POST['sale_off_to'] ) ) : '';
				$staff_ids 		= isset( $_POST['staff_ids'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['staff_ids'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$use_on 		= isset( $_POST['use_on'] ) ? sanitize_text_field( wp_unslash( $_POST['use_on'] ) ) : 'booking_date';
				$packages 		= isset( $_POST['packages'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['packages'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$note_price 	= isset( $_POST['note_price'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note_price'] ) ) : '';
				$tax_class 		= isset( $_POST['tax_class'] ) ? sanitize_text_field( wp_unslash( $_POST['tax_class'] ) ) : '';

				$sale_off_start_date 	= $sale_off_start_date ? strtotime( $sale_off_start_date ) : '';
				$sale_off_end_date 		= $sale_off_end_date ? strtotime( $sale_off_end_date ) : '';
				$decimal_separator 		= OBP()->settings->general->get('decimal_separator','.');

				// Fortmat package
				$packages = array_map(function( $value ){

					if ( ! empty( $value['data'] ) ) {
						foreach ( $value['data'] as $key => $val ) {
							$hours 		= absint( $val['hours'] );
							$minutes 	= absint( $val['minutes'] );
							$seconds 	= ($hours*60*60) + ( $minutes*60 );
							$p_price 	= $val['price'];
							$value['data'][$key]['seconds'] = $seconds;

							$value['data'][$key]['price'] = obp_convert_price( $p_price );
						}
					}

					return $value;
				}, $packages );
				$new_package = [];
				$package_id_arr = [];

				// Format regular price
				if ( $price ) {
					$last_dot_position 	= strrpos($price, '.');

					if ( $last_dot_position !== false ) {
						$before_last_dot = substr($price, 0, $last_dot_position);
						$after_last_dot = substr($price, $last_dot_position + 1);

						$before_last_dot_cleaned = str_replace('.', '', $before_last_dot);
						$price = $before_last_dot_cleaned . '.' . $after_last_dot;
					}
				}

				// Format sale price
				if ( $sale_price ) {
					
					$sale_price 		= str_replace($decimal_separator, '.', $sale_price );
					$last_dot_position 	= strrpos($sale_price, '.');

					if ( $last_dot_position !== false ) {
						$before_last_dot = substr($sale_price, 0, $last_dot_position);
						$after_last_dot = substr($sale_price, $last_dot_position + 1);

						$before_last_dot_cleaned = str_replace('.', '', $before_last_dot);
						$sale_price = $before_last_dot_cleaned . '.' . $after_last_dot;
					}

					if ( (float)$sale_price > (float)$price ) {
						$sale_price = '';
					}
				}
			}

			$meta_input = array(
				OBP_METABOX.'price' 				=> $price,
				OBP_METABOX.'sale_price' 			=> $sale_price,
				OBP_METABOX.'sale_off_start_date' 	=> $sale_off_start_date,
				OBP_METABOX.'sale_off_end_date' 	=> $sale_off_end_date,
				OBP_METABOX.'hour' 					=> $hour,
				OBP_METABOX.'minute' 				=> $minute,
				OBP_METABOX.'price_type' 			=> $price_type,
				OBP_METABOX.'color' 				=> $color,
				OBP_METABOX.'sale_off_from' 		=> $sale_off_from,
				OBP_METABOX.'sale_off_to' 			=> $sale_off_to,
				OBP_METABOX.'staff_ids' 			=> $staff_ids,
				OBP_METABOX.'vendor_id' 			=> $vendor_id,
				OBP_METABOX.'type' 					=> $type,
				OBP_METABOX.'use_on' 				=> $use_on,
				OBP_METABOX.'note_price' 			=> $note_price,
				OBP_METABOX.'tax_class' 			=> $tax_class,
			);

			$data_post = array(
				'post_title' 	=> $service_name,
				'post_content' 	=> $description,
			);

			if ( $post_id ) {

				// Check multi language

				$check = wp_update_post(
					array(
						'ID' 			=> $post_id,
						'post_title' 	=> $service_name,
						'post_content' 	=> $description,
					)
				);

				foreach ($meta_input as $meta_key => $meta_value) {
					update_post_meta( $post_id, $meta_key, $meta_value );
				}

				do_action( 'obp_update_postmeta_service', $data_post, $meta_input, $post_id );

				// Save packages
				if ( ! empty( $packages ) ) {
					foreach ( $packages as $k => $package ) {
						$type 		= isset( $package['type'] ) ? $package['type'] : 'radio';
						$label 		= isset( $package['label'] ) ? $package['label'] : '';
						$data 		= isset( $package['data'] ) ? $package['data'] : [];
						$data_gr 	= array();
						$new_data 	= array();
						foreach ( $data as $val ) {
							$id 		= isset( $val['id'] ) ? $val['id'] : '';
							$name 		= isset( $val['name'] ) ? $val['name'] : '';
							$hours 		= isset( $val['hours'] ) ? $val['hours'] : 0;
							$minutes 	= isset( $val['minutes'] ) ? $val['minutes'] : 0;
							$seconds 	= isset( $val['seconds'] ) ? $val['seconds'] : 0;
							$price 		= isset( $val['price'] ) ? $val['price'] : 0;
							$post_name 	= $name ? sanitize_title( $name ) : '';


							$data_package = array(
								'post_title' 	=> $name,
							);

							// Meta Input
							$meta_package = array(
								OBP_METABOX.'hours' 		=> $hours,
								OBP_METABOX.'minutes' 		=> $minutes,
								OBP_METABOX.'seconds' 		=> $seconds,
								OBP_METABOX.'price' 		=> $price,
								OBP_METABOX.'service_id' 	=> $post_id,
								OBP_METABOX.'type' 			=> $type,
								OBP_METABOX.'label' 		=> $label,
							);

							if ( $id ) {
								$postarr['ID'] = $id;

								// Check multi language

								wp_update_post( array(
									'ID' 			=> $id,
									'post_title' 	=> $name,
									'post_name' 	=> $post_name,
								) );

								// Update post meta
								foreach ($meta_package as $meta_key => $meta_value) {
									update_post_meta( $id, $meta_key, $meta_value );
								}
					
								do_action( 'obp_update_postmeta_package', $data_package, $id , $meta_package );

								$new_data[] = $id;
					
							} else {
								$package_id = wp_insert_post( array(
									'post_title' 	=> $name,
									'post_name' 	=> $post_name
								), true );

								if ( ! is_wp_error( $package_id ) ) {

									// Update post meta
									foreach ($meta_package as $meta_key => $meta_value) {
										update_post_meta( $package_id, $meta_key, $meta_value );
									}
									$new_data[] = $package_id;
								}
							}
						}
						$data_gr['type'] = $type;
						$data_gr['label'] = $label;
						$data_gr['data'] = $new_data;
						$package_id_arr = array_merge( $package_id_arr, $new_data );
						$new_package[] = $data_gr;
					}
				}

			
				update_post_meta( $post_id, OBP_METABOX.'packages', $new_package );
				
				do_action( 'obp_after_update_postmeta_service', $post_id, $new_package );
	

				// Delete package not use
				$package_exclude = OBP_Package::get_package_exclude( $post_id, $package_id_arr );
				if ( ! empty( $package_exclude ) ) {
					foreach ( $package_exclude as $package_id ) {
						wp_delete_post( $package_id, true );
						do_action( 'obp_delete_package_exclude', $package_id );
					}
				}
				
				if ( ! is_wp_error( $check ) ) {
					OBP()->message->add( esc_html__( 'Updated successfully.', 'ovabookpro' ) );
				} else {
					OBP()->message->add( esc_html__( 'Updated failed.', 'ovabookpro' ), 'error' );
				}

			} else {

				$postarr = array(
					'post_author' 		=> get_current_user_id(),
					'post_type' 		=> 'obp_service',
					'post_status' 		=> 'publish',
					'post_title' 		=> $service_name,
					'post_content' 		=> $description,
				);

				$new_post = wp_insert_post( $postarr, true );

				foreach ($meta_input as $meta_key => $meta_value) {
					update_post_meta( $new_post, $meta_key, $meta_value );
				}

				do_action( 'obp_update_postmeta_service', $data_post, $meta_input, $new_post );

				// Save packages
				if ( ! empty( $packages ) ) {
					foreach ( $packages as $k => $package ) {
						$type = isset( $package['type'] ) ? $package['type'] : 'radio';
						$label = isset( $package['label'] ) ? $package['label'] : '';
						$data = isset( $package['data'] ) ? $package['data'] : [];
						$data_gr = array();
						$new_data = array();
						foreach ( $data as $val ) {
							$id 		= isset( $val['id'] ) ? $val['id'] : '';
							$name 		= isset( $val['name'] ) ? $val['name'] : '';
							$hours 		= isset( $val['hours'] ) ? $val['hours'] : 0;
							$minutes 	= isset( $val['minutes'] ) ? $val['minutes'] : 0;
							$seconds 	= isset( $val['seconds'] ) ? $val['seconds'] : 0;
							$price 		= isset( $val['price'] ) ? $val['price'] : 0;
							$post_name 	= $name ? sanitize_title( $name ) : '';

							$postarr = array(
								'post_title' 	=> $name,
								'post_name' 	=> $post_name,
								'post_type' 	=> 'obp_package',
								'post_status' 	=> 'publish',
							);

							// Meta Input
							$meta_package = array(
								OBP_METABOX.'hours' 		=> $hours,
								OBP_METABOX.'minutes' 		=> $minutes,
								OBP_METABOX.'seconds' 		=> $seconds,
								OBP_METABOX.'price' 		=> $price,
								OBP_METABOX.'service_id' 	=> $new_post,
								OBP_METABOX.'type' 			=> $type,
								OBP_METABOX.'label' 		=> $label,
							);

							$data_package = array(
								'post_title' => $name,
							);
							
							if ( $id ) {
								$postarr['ID'] = $id;
								$package_id = wp_update_post( $postarr, true );

								if ( ! is_wp_error( $package_id ) ) {
						
									// Update post meta
									foreach ($meta_package as $meta_key => $meta_value) {
										update_post_meta( $package_id, $meta_key, $meta_value );
									}
						
									do_action( 'obp_update_postmeta_package', $data_package, $package_id , $meta_package );
							
									$new_data[] = $package_id;
								}
							} else {
								$package_id = wp_insert_post( $postarr, true );

								if ( ! is_wp_error( $package_id ) ) {
								
									// Update post meta
									foreach ($meta_package as $meta_key => $meta_value) {
										update_post_meta( $package_id, $meta_key, $meta_value );
									}
						
									// For multi language
									do_action( 'obp_update_postmeta_package', $data_package, $package_id, $meta_package );
									
									$new_data[] = $package_id;
								}
							}
						}
						$data_gr['type'] = $type;
						$data_gr['data'] = $new_data;
						$data_gr['label'] = $label;
						$new_package[] = $data_gr;
						$package_id_arr = array_merge( $package_id_arr, $new_data );
					}
				}

				if ( ! is_wp_error( $new_post ) ) {

					// Update metadata
					foreach ( $meta_input as $key => $value ) {
						update_post_meta( $new_post, $key, $value );
					}

					update_post_meta( $new_post, OBP_METABOX.'packages', $new_package );
					
					do_action( 'obp_after_update_postmeta_service', $new_post, $new_package );

					// Delete package not use
					$package_exclude = OBP_Package::get_package_exclude( $new_post, $package_id_arr );

					if ( ! empty( $package_exclude ) ) {
						foreach ( $package_exclude as $package_id ) {
							wp_delete_post( $package_id, true );

							do_action( 'obp_delete_package_exclude', $package_id );
						}
					}

					OBP()->message->add( esc_html__( 'Updated successfully.', 'ovabookpro' ) );

					$member_acc_url 	= obp_member_account_url();
					$endpoint 			= OBP()->endpoint->get_endpoint('edit-service');
					$response['url'] 	= OBP()->endpoint->get_endpoint_url( $endpoint, $new_post, $member_acc_url );
					$response['redirect'] = true;

				} else {
					OBP()->message->add( esc_html__( 'Updated failed.', 'ovabookpro' ), 'error' );
				}

			}

			wp_send_json( $response );
			wp_die();
		}

		public function obp_delete_service() {

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('manage_service') ) {
				
				$service_id = isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : '';

				$check = wp_delete_post( $service_id, true );

				if ( $check ) {
					OBP()->message->add( esc_html__( 'Deleted successfully', 'ovabookpro' ) );
				} else {
					OBP()->message->add( esc_html__( 'Delete failed', 'ovabookpro' ), 'error' );
				}
			}

			echo '';
			wp_die();
		}

		
	}

}