<?php
namespace BookPro;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Permission') ) {
	

	class OBP_Permission {

		public static function is_vendor(){
			$user = wp_get_current_user();
			return in_array( 'vendor', $user->roles ) || in_array( 'administrator', $user->roles );
		}

		public static function is_administrator(){
			$user = wp_get_current_user();
			return in_array( 'administrator', $user->roles );
		}

		public static function is_staff(){
			$user = wp_get_current_user();
			$vendor = self::is_vendor();
			return ! $vendor && in_array( 'staff', $user->roles );
		}

		public static function is_customer(){
			$vendor = self::is_vendor();
			$staff = self::is_staff();
			return (! $vendor && ! $staff);
		}

		public static function user_can( $cap = null ){


			if ( self::is_administrator() ) {
				return true;
			}

			if ( empty( $cap ) ) {
				return false;
			}

			$user    = wp_get_current_user();
			$user_id = $user->ID;

			if ( self::is_vendor() || self::is_customer() ) {
				return array_key_exists( $cap, $user->allcaps );
			} else {
				$role_id 		= OBP()->get_user_meta($user_id, 'role_id');
				$capabilities 	= OBP()->get_post_meta( $role_id, 'capabilities', [] );

				return in_array( $cap, $capabilities ) || array_key_exists( $cap, $user->allcaps );
			}
		}
	}
}