<?php 
namespace BookPro\Type;

use BookPro\User\OBP_User;
use WP_Query;
defined( 'ABSPATH' ) || exit;

/**
 * OBP_Type class.
 */
class OBP_Type {

	protected static $_instance = null;


	public static function init(){

		add_action( 'obp_load_member_account_manage-type_scripts', array( __CLASS__, 'load_scripts' ) );
	}

	public static function load_scripts(){

		wp_enqueue_style( 'zebra-dialog');
		wp_enqueue_script('zebra-dialog');

		wp_enqueue_script( 'obp-frontend-type', OBP_PLUGIN_URI.'assets/js/frontend/type.js', array('jquery'), false, true );
		
		wp_localize_script( 'obp-frontend-type', 'obp_type_obj', array(
			'confirm_delete' => esc_html__( 'Are you sure you want to delete this record? This action cannot be undone', 'ovabookpro' ),
			'yes' 		=> esc_html__( 'Yes', 'ovabookpro' ),
			'no' 		=> esc_html__( 'No','ovabookpro' ),
			'name_req' 	=> esc_html__( 'Name is required', 'ovabookpro' ),
		) );
	}


	public static function get_type_ajax(){
				
		$vendor_id = OBP_User::get_vendor_id();

		$args = array(
			'post_type'   		=> 'obp_type',
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> -1,
			'meta_key' 			=> OBP_METABOX.'vendor_id',
			'meta_value' 		=> $vendor_id,
		);
		
		$query = new WP_Query( $args );

		return $query;
	}

	public static function get_types( $vendor_id = '' ){

		$args = array(
			'post_type'   		=> 'obp_type',
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> -1,
			'meta_key' 			=> OBP_METABOX.'vendor_id',
			'meta_value' 		=> $vendor_id,
			'fields' 			=> 'ids',
		);
		
		$query = get_posts( $args );

		return $query;
	}

	public static function get_all_types(){
		$args = array(
			'post_type'   		=> 'obp_type',
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> -1,
			'fields' 			=> 'ids',
		);
		
		$query = get_posts( $args );

		return $query;
	}

	public static function get_types_by_id( $ids = array() ){
		$args = array(
			'post_type'   		=> 'obp_type',
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> -1,
			'post__in' 			=> $ids,
			'fields' 			=> 'ids',
		);

		$query = get_posts( $args );

		return $query;
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}