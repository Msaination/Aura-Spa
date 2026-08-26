<?php

namespace BookPro\User;

use BookPro\Business\OBP_Business;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_User_Item") ) {
	
	class OBP_User_Item {

		protected $id = null;

		protected $user = null;

		public function __construct( $id ){
			$this->id = $id;
			$this->user = get_user_by( 'id', $id );
		}

		public function get_id(){
			$id = $this->id;
			return $id;
		}

		public function get_payout_method_id(){
			$id = $this->get_id();
			$payout_method_id = get_user_meta( $id, OBP_METABOX.'payout_method_id', true );
			return $payout_method_id;
		}

		public function get_payout_method(){
			$payout_method_id = $this->get_payout_method_id();
			$payout_method = get_the_title( $payout_method_id );
			return $payout_method;
		}

		public function get_balance_amount(){
			$id = $this->get_id();
			$balance_amount = get_user_meta( $id, OBP_METABOX.'balance_amount', true );
			if ( empty( $balance_amount ) ) {
				$balance_amount = 0;
			}
			return $balance_amount;
		}

		public function get_status(){
			$user_id 		= $this->get_id();
			$user_status 	= get_user_meta( $user_id, OBP_METABOX.'user_status', true );
			return $user_status;
		}

		public function get_user(){
			$user = $this->user;
			return $user;
		}

		public function get_user_login(){
			$user = $this->get_user();
			$username = isset( $user->user_login ) ? $user->user_login : '';
			return $username;
		}

		public function get_user_email(){
			$user = $this->get_user();
			$user_email = isset( $user->user_email ) ? $user->user_email : '';
			return $user_email;
		}

		public function get_custom_field(){
			$user_id = $this->get_id();
			$custom_field = get_user_meta( $user_id, OBP_METABOX.'custom_field', true );
			return $custom_field;
		}

		public function get_phone_number(){
			$user_id = $this->get_id();
			$phone_number = get_user_meta( $user_id, OBP_METABOX.'phone_number', true );
			return $phone_number;
		}

		public function get_avatar(){
			$avatar_id   = $this->get_avatar_id();
			$avatar_html = wp_get_attachment_image( $avatar_id );
			return $avatar_html;
		}

		public function get_password(){
			$user = $this->get_user();
			$password = $user->user_pass;
			return $password;
		}

		public function get_avatar_id(){
			$user_id = $this->get_id();
			$avatar_id = get_user_meta( $user_id, OBP_METABOX.'avatar', true );
			return $avatar_id;
		}

		public function get_position(){
			$user_id = $this->get_id();
			$position = get_user_meta( $user_id, OBP_METABOX.'position', true ) ? get_user_meta( $user_id, OBP_METABOX.'position', true)  : '';
			return $position;
		}

		public function get_role(){
			$user_id = $this->get_id();
			$role = get_user_meta( $user_id, OBP_METABOX.'role_id', true ) ? get_user_meta( $user_id, OBP_METABOX.'role_id', true)  : '';
			return $role;
		}

		public function get_role_name(){
			$role_id = $this->get_role();
			$role = get_post( $role_id );
			$role_name = ! empty( $role->post_title ) ? $role->post_title : '';
			return $role_name;
		}

		public function get_nickname(){
			$user_id = $this->get_id();
			$nickname = get_user_meta( $user_id, 'nickname', true );
			return $nickname;
		}

		public function get_first_name(){
			$user_id = $this->get_id();
			$first_name = get_user_meta( $user_id, 'first_name', true );
			return $first_name;
		}

		public function get_last_name(){
			$user_id = $this->get_id();
			$last_name = get_user_meta( $user_id, 'last_name', true );
			return $last_name;
		}

		public function get_fullname(){
			$user_id 	= $this->get_id();
			$nickname   = $this->get_nickname();
			$first_name = $this->get_first_name();
			$last_name  = $this->get_last_name();

			$fullname   = $first_name . ' ' . $last_name;
			
			if( $fullname == ' ' ) {
				$fullname = $nickname;
			}
			
			return $fullname;
		}

		public function get_description(){
			$user_id = $this->get_id();
			$user_meta = get_user_meta( $user_id );
			$description = implode(' ', $user_meta['description'] );
			return $description;
		}

		public function get_vendor_status(){
			$user_id = $this->get_id();
			$vendor_status = get_user_meta( $user_id, OBP_METABOX.'vendor_status', true );
			return $vendor_status;
		}

		public function get_avatar_url(){
			$user_id = $this->get_id();
			$avatar_url = get_avatar_url( $user_id );
			return $avatar_url;
		}

		public function get_obp_avatar_url(){
			$avatar_id = $this->get_avatar_id();
			$avatar_url = wp_get_attachment_url( $avatar_id );
			return $avatar_url;
		}

		public function get_business_id(){
			$business_id = OBP_Business::get_id( obp_get_vendor_id() );
			return $business_id;
		}

		public function get_wishlist(){
			$user_id = $this->get_id();
			$wishlist = get_user_meta( $user_id, OBP_METABOX.'wishlist', true ) ? get_user_meta( $user_id, OBP_METABOX.'wishlist', true ) : [];
			return $wishlist;
		}

		public function set_payout_method_id( $payout_method_id ){
			$id = $this->get_id();
			update_user_meta( $id, OBP_METABOX.'payout_method_id', $payout_method_id );
		}

		public function add_balance_amount( $amount ){
			
			$balance_amount = $this->get_balance_amount();
			$balance_amount = (float)$balance_amount + (float)$amount;
			$this->set_balance_amount( $balance_amount );
		}

		public function set_balance_amount( $balance_amount ){
			$id = $this->get_id();
			update_user_meta( $id, OBP_METABOX.'balance_amount', $balance_amount );
		}
	}
}