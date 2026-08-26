<?php 
namespace BookPro\Type;

use BookPro\Traits\SingletonTrait;
use BookPro\OBP_Permission;
use BookPro\User\OBP_User;


defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Type_Ajax') ) {
	class OBP_Type_Ajax {

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
				'obp_add_type',
				'obp_save_new_type',
				'obp_show_edit_type',
				'obp_save_edit_type',
				'obp_delete_type',
			);

			foreach($arr_ajax as $val){
				add_action( 'wp_ajax_'.$val, array( $this, $val ) );
				add_action( 'wp_ajax_nopriv_'.$val, array( $this, $val ) );
			}
		}


		public function obp_add_type(){

		
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can( 'manage_type' ) ) {
	
				obp_get_template("manage-type/add-new.php");
	
			}

			wp_die();
		}
		

		public function obp_save_new_type(){

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

				if ( ! is_wp_error( $post_id ) ) {
					OBP()->message->add( esc_html__( 'Added successfully', 'ovabookpro' ) );
				} else {
					OBP()->message->add( esc_html__( 'Add failed', 'ovabookpro' ), 'error');
				}

				echo esc_html( $post_id );
			}

			wp_die();
		}

		public function obp_show_edit_type(){
		
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can( 'manage_type' ) ) {

				$type_id 	= isset( $_POST['type_id'] ) ? sanitize_text_field( wp_unslash( $_POST['type_id'] ) ) : '';
				$type 		= obp_get_type( $type_id );
			
				obp_get_template("manage-type/edit-type.php", array( 'type' => $type ) );
		
			}

			wp_die();
		}

		public function obp_save_edit_type(){
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can( 'manage_type' ) ) {

				$type_id 			= isset( $_POST['type_id'] ) ? sanitize_text_field( wp_unslash( $_POST['type_id'] ) ) : '';
				$type_name 			= isset( $_POST['type_name'] ) ? sanitize_text_field( wp_unslash( $_POST['type_name'] ) ) : '';
				$current_language 	= obp_get_current_language();

				$postarr = array(
					'ID' 			=> $type_id,
					'post_title' 	=> $type_name,
					'post_name' 	=> sanitize_title( $type_name ),
				);

				if ( obp_get_default_language() == $current_language ) {
					$check = wp_update_post( $postarr, true );
				} else {
					// For multi language
					$check = true;
					do_action( 'obp_update_postmeta_type', $postarr, $type_id, $current_language );
				}

				if ( ! is_wp_error( $check ) ) {
					OBP()->message->add( esc_html__( 'Updated successfully', 'ovabookpro' ) );
				} else {
					OBP()->message->add( esc_html__( 'Update failed', 'ovabookpro' ), 'error');
				}

			}
			echo '';
			wp_die();
		}

		public function obp_delete_type(){
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can( 'manage_type' ) ) {

				$type_id = isset( $_POST['type_id'] ) ? sanitize_text_field( wp_unslash( $_POST['type_id'] ) ) : '';

				$check = wp_delete_post( $type_id, true );

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