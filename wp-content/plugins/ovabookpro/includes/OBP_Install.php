<?php
namespace BookPro;

use WP_Roles;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Install') ) {

	class OBP_Install {

		public static function get_vendor_capabilities(){
			return array(
				'my_business',
				'my_wallet',
				'manage_booking',
				'manage_service',
				'edit_service',
				'edit_staff',
				'edit_coupon',
				'manage_type',
				'manage_plan',
				'manage_staff',
				'manage_coupon',
				'manage_schedules',
				'staff_schedule',
				'manage_role',
				'my_booking',
				'my_message',
				'my_wishlist',
				'my_profile',
			);
		}

		public static function get_staff_capabilities(){
			return array(
				'my_wallet',
				'staff_schedule',
				'my_booking',
				'my_message',
				'my_wishlist',
				'my_profile',
			);
		}

		public static function get_customer_capabilities(){
			return array(
				'my_wallet',
				'my_booking',
				'my_message',
				'my_wishlist',
				'my_profile',
			);
		}

		public static function get_backend_capabilities(){
			return array(
				'vendor_approve',
			);
		}

		public static function create_roles(){

			global $wp_roles;

			if ( ! class_exists( 'WP_Roles' ) ) {
				return;
			}

			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}

			/* translators: user role */
			_x( 'Staff', 'User role', 'ovabookpro' );

			add_role( 'staff', 'Staff' , array(
				'read' 			=> true,
				'upload_files' 	=> true,
			) );

			$frontend_capabilities 	= self::get_vendor_capabilities();
			$customer_capabilities 	= self::get_customer_capabilities();
			$backend_capabilities 	= self::get_backend_capabilities();
			$staff_capabilities 	= self::get_staff_capabilities();

			// Customer
			foreach ( $customer_capabilities as $cap ) {
				$wp_roles->add_cap( 'subscriber', $cap );
				$wp_roles->add_cap( 'customer', $cap );
				$wp_roles->add_cap( 'shop_manager', $cap );
			}

			foreach ( $frontend_capabilities as $cap ) {
				$wp_roles->add_cap( 'administrator', $cap );
			}

			foreach ($backend_capabilities as $cap) {
				$wp_roles->add_cap( 'administrator', $cap );
			}

			foreach ( $staff_capabilities as $cap ) {
				$wp_roles->add_cap( 'staff', $cap );
			}

			$capabilities = self::get_core_capabilities();

			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->add_cap( 'administrator', $cap );
				}
			}
			
		}

		public static function get_core_capabilities() {
			$capabilities = array();

			$capability_types = array( 'obp_business', 'obp_payout_method', 'obp_order', 'obp_type', 'obp_service', 'obp_payout', 'obp_tax' );

			foreach ( $capability_types as $capability_type ) {

				$capabilities[ $capability_type ] = array(
					// Post type.
					"edit_{$capability_type}",
					"read_{$capability_type}",
					"delete_{$capability_type}",
					"edit_{$capability_type}s",
					"edit_others_{$capability_type}s",
					"publish_{$capability_type}s",
					"read_private_{$capability_type}s",
					"delete_{$capability_type}s",
					"delete_private_{$capability_type}s",
					"delete_published_{$capability_type}s",
					"delete_others_{$capability_type}s",
					"edit_private_{$capability_type}s",
					"edit_published_{$capability_type}s",

					// Terms.
					"manage_{$capability_type}_terms",
					"edit_{$capability_type}_terms",
					"delete_{$capability_type}_terms",
					"assign_{$capability_type}_terms",
				);
			}

			return $capabilities;
		}

		public static function create_pages(){
			$pages = apply_filters( 'obp_create_pages', array(
				'member_account' => array(
					'name' 		=> _x( 'member-account', 'Page slug', 'ovabookpro' ),
					'title' 	=> _x( 'Member Account', 'Page title', 'ovabookpro' ),
					'content' 	=> '[obp_member_account]',
				),
				'login' => array(
					'name' 		=> _x( 'login', 'Page slug', 'ovabookpro' ),
					'title' 	=> _x( 'Login', 'Page title', 'ovabookpro' ),
					'content' 	=> '[obp_login]',
				),
				'register_user' => array(
					'name' 		=> _x( 'register-user', 'Page slug', 'ovabookpro' ),
					'title' 	=> _x( 'Register User', 'Page title', 'ovabookpro' ),
					'content' 	=> '[obp_register_user]',
				),
				'forgot_password' => array(
					'name' 		=> _x( 'forgot-password', 'Page slug', 'ovabookpro' ),
					'title' 	=> _x( 'Forgot Password', 'Page title', 'ovabookpro' ),
					'content' 	=> '[obp_forgot_password]',
				),
				'reset_password' => array(
					'name' 		=> _x( 'reset-password', 'Page slug', 'ovabookpro' ),
					'title' 	=> _x( 'Reset Password', 'Page title', 'ovabookpro' ),
					'content' 	=> '[obp_reset_password]',
				),
				'thank' => array(
					'name' 		=> _x( 'thank-you', 'Page slug', 'ovabookpro' ),
					'title' 	=> _x( 'Thank you', 'Page title', 'ovabookpro' ),
					'content' 	=> '[obp_booking_info]',
				),
			) );


			foreach ( $pages as $key => $page ) {
				obp_create_page(
					esc_sql( $page['name'] ),
					$key . '_page_id',
					$page['title'],
					$page['content'],
				);
			}

			// Restore the locale to the default locale.
			obp_restore_locale();
		}

		public static function install(){

			self::create_pages();

			// Force a flush of rewrite rules even if the corresponding hook isn't initialized yet.
			if ( ! has_action( 'obp_flush_rewrite_rules' ) ) {
				flush_rewrite_rules();
			}

			/**
			 * Flush the rewrite rules after install or update.
			 */
			do_action( 'obp_flush_rewrite_rules' );
		}
	}
	
}