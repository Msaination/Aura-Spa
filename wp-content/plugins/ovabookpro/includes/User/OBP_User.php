<?php
namespace BookPro\User;

use WP_User_Query;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_User') ) {
	
	class OBP_User {

		public static function get_all_vendor(){
			$wp_user_query = new WP_User_Query( array( 'role' => 'vendor' ) );
			$users = $wp_user_query->get_results();
			return $users;
		}

		public static function get_user_by_status( $status = '', $keywords = '' ){

			$args = array(
				'order' 		=> 'DESC',
				'fields' 		=> array( 'ID', 'user_login', 'user_email', 'user_registered' ),
			);

			if ( ! empty( $status ) ) {
				$args['meta_key'] 	= OBP_METABOX.'vendor_status';
				$args['meta_value'] = $status;
			}

			if ( $keywords ) {
				$args['search'] = '*'.$keywords.'*';
			}

			$wp_user_query = new WP_User_Query( $args );
			$users = $wp_user_query->get_results();
			return $users;
		}

		public static function get_vendor_id(){
			$user 	 = wp_get_current_user();
			$user_id = $user->ID;

			$vendor_id = get_user_meta( $user_id, OBP_METABOX.'vendor_id', true );
			if ( $vendor_id ) {
				return $vendor_id;
			}
			return $user_id;
		}

		public static function convert_staff_ids_to_staff_emails( $user_ids = array() ){
			$staff_emails = [];
			foreach ( $user_ids as $user_id ) {
				$user = get_user_by('id', $user_id);
				if ( in_array( 'staff', (array) $user->roles ) ) {
				    $staff_emails[] = $user->user_email;
				}
			}
			return $staff_emails;
		}

		public static function get_user_ids_by_payout_method_id( $payout_method_id ){
			$args = array(
				'meta_key' 		=> OBP_METABOX.'payout_method_id',
				'meta_value' 	=> $payout_method_id,
				'fields' 		=> 'ID',
			);

			$user_ids = get_users( $args );

			return $user_ids;
		}

		public static function get_all_user( $keywords = null  ){
			$args = array(
				'order' 		=> 'DESC',
				'orderby'		=> 'ID',
				'fields' 		=> array( 'ID', 'user_login', 'user_email', 'user_registered' ),
			);

			if ( $keywords ) {
				$args['search'] = '*'.$keywords.'*';
			}

			$wp_user_query = new WP_User_Query( $args );
			$users = $wp_user_query->get_results();
			return $users;
		}

	}
}