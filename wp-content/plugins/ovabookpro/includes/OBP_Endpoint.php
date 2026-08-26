<?php
namespace BookPro;

use BookPro\Traits\SingletonTrait;
use BookPro\OBP_Permission;
use BookPro\OBP_Data_Setting;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Endpoint') ) {
	
	class OBP_Endpoint {

		use SingletonTrait;

		public $query_vars = array();

		public $settings;

		public function __construct(){

			$this->settings = new OBP_Data_Setting();

			add_action( 'init', array( $this, 'add_endpoints' ) );
			if ( ! is_admin() ) {
				add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
				add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
			}
			$this->init_query_vars();
		}

		public function add_endpoints(){

			$mask = $this->get_endpoints_mask();

			foreach ( $this->get_query_vars() as $key => $var ) {
				if ( ! empty( $var ) ) {
					add_rewrite_endpoint( $var, $mask );
				}
			}

			// For single business page
			$our_work = obp_get_our_work_endpoint();
			$post_type_slug = _x('business','Business Slug', 'ovabookpro');
			$regex = apply_filters( 'obp_regex_our_work', '^'.$post_type_slug.'/([^/]+)/'.$our_work.'/?$' );
		    add_rewrite_rule(
		        $regex, // Custom URL structure
		        'index.php?post_type=obp_business&name=$matches[1]&our_work=1',
		        'top'
		    );

		    do_action( 'obp_add_rewrite_rule', $this, $post_type_slug );

		}

		public function init_query_vars() {


			$my_business 		= obp_get_my_business_endpoint();

			$manage_booking 	= obp_get_manage_booking_endpoint();
			$manage_type 		= obp_get_manage_type_endpoint();
			$manage_plan 		= obp_get_manage_plan_endpoint();
			$manage_staff 		= obp_get_manage_staff_endpoint();
			$edit_staff 		= obp_get_edit_staff_endpoint();
			$manage_coupon 		= obp_get_manage_coupon_endpoint();
			$edit_coupon 		= obp_get_edit_coupon_endpoint();
			$manage_service 	= obp_get_manage_service_endpoint();
			$edit_service 		= obp_get_edit_service_endpoint();
			$overall_schedule 	= obp_get_overall_schedule_endpoint();
			$staff_schedule 	= obp_get_staff_schedule_endpoint();
			$manage_role 		= obp_get_manage_role_endpoint();
			$my_wallet 			= obp_get_my_wallet_endpoint();
			$my_booking 		= obp_get_my_booking_endpoint();
			$my_wishlist 		= obp_get_my_wishlist_endpoint();
			$my_profile 		= obp_get_my_profile_endpoint();
			$logout 			= obp_get_logout_endpoint();
			$our_work 			= obp_get_our_work_endpoint();


			// Query vars to add to WP.
			$this->query_vars = array(
				'my-business' 		=> $my_business,
				'manage-booking' 	=> $manage_booking,
				'manage-service' 	=> $manage_service,
				'edit-service' 		=> $edit_service,
				'manage-type' 		=> $manage_type,
				'manage-plan' 		=> $manage_plan,
				'manage-staff' 		=> $manage_staff,
				'edit-staff' 		=> $edit_staff,
				'manage-coupon' 	=> $manage_coupon,
				'edit-coupon' 		=> $edit_coupon,
				'overall-schedule' 	=> $overall_schedule,
				'staff-schedule' 	=> $staff_schedule,
				'manage-role' 		=> $manage_role,
				'my-wallet' 		=> $my_wallet,
				'my-booking' 		=> $my_booking,
				'our-work' 			=> $our_work,
				'my-wishlist' 		=> $my_wishlist,
				'my-profile' 		=> $my_profile,
				'logout' 			=> $logout,
			);
		}

		public function get_endpoint( $key = 'my-profile' ){
			if ( isset( $this->get_query_vars()[$key] ) ) {
				return $this->get_query_vars()[$key];
			}
			return $key;
		}

		public function get_endpoints_mask() {
			if ( 'page' === get_option( 'show_on_front' ) ) {
				$page_on_front     	= get_option( 'page_on_front' );
				$myaccount_page_id = $this->settings->general->get('member_account_page_id');
				if ( in_array( $page_on_front, array( $myaccount_page_id ), true ) ) {
					return EP_ROOT | EP_PAGES;
				}
			}

			return EP_PAGES;
		}

		public function get_query_vars() {
			return apply_filters( 'obp_get_query_vars', $this->query_vars );
		}

		public function parse_request() {
			global $wp;

			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			// Map query vars to their keys, or get them if endpoints are not supported.
			foreach ( $this->get_query_vars() as $key => $var ) {
				if ( isset( $_GET[ $var ] ) ) {
					$wp->query_vars[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $var ] ) );
				} elseif ( isset( $wp->query_vars[ $var ] ) ) {
					$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
				}
			}


			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

			/**
		 * Hook into pre_get_posts to do the main product query.
		 *
		 * @param WP_Query $q Query instance.
		 */
		public function pre_get_posts( $q ) {
			// We only want to affect the main query.
			if ( ! $q->is_main_query() ) {
				return;
			}

			// Fixes for queries on static homepages.
			if ( $this->is_showing_page_on_front( $q ) ) {

				// Fix for endpoints on the homepage.
				if ( ! $this->page_on_front_is( $q->get( 'page_id' ) ) ) {
					$_query = wp_parse_args( $q->query );
					if ( ! empty( $_query ) && array_intersect( array_keys( $_query ), array_keys( $this->get_query_vars() ) ) ) {
						$q->is_page     = true;
						$q->is_home     = false;
						$q->is_singular = true;
						$q->set( 'page_id', (int) get_option( 'page_on_front' ) );
						add_filter( 'redirect_canonical', '__return_false' );
					}
				}
			}
		}

		private function is_showing_page_on_front( $q ) {
			return ( $q->is_home() && ! $q->is_posts_page ) && 'page' === get_option( 'show_on_front' );
		}

		private function page_on_front_is( $page_id ) {
			return absint( get_option( 'page_on_front' ) ) === absint( $page_id );
		}

		public function is_endpoint_url( $endpoint = false ){
			global $wp;

			$obp_endpoints = $this->get_query_vars();

			if ( false !== $endpoint ) {
				if ( ! isset( $obp_endpoints[ $endpoint ] ) ) {
					return false;
				} else {
					$endpoint_var = $obp_endpoints[ $endpoint ];
				}

				return isset( $wp->query_vars[ $endpoint_var ] );
			} else {
				foreach ( $obp_endpoints as $key => $value ) {
					if ( isset( $wp->query_vars[ $key ] ) ) {
						return true;
					}
				}

				return false;
			}
		}

		public function get_endpoint_url( $endpoint, $value = '', $permalink = '' ){
			if ( ! $permalink ) {
				$permalink = get_permalink();
			}

			// Map endpoint to options.
			$query_vars = $this->get_query_vars();
			$endpoint   = ! empty( $query_vars[ $endpoint ] ) ? $query_vars[ $endpoint ] : $endpoint;

			if ( get_option( 'permalink_structure' ) ) {
				if ( strstr( $permalink, '?' ) ) {
					$query_string = '?' . wp_parse_url( $permalink, PHP_URL_QUERY );
					$permalink    = current( explode( '?', $permalink ) );
				} else {
					$query_string = '';
				}
				$url = trailingslashit( $permalink );

				if ( $value ) {
					$url .= trailingslashit( $endpoint ) . user_trailingslashit( $value );
				} else {
					$url .= user_trailingslashit( $endpoint );
				}

				$url .= $query_string;
			} else {
				$url = add_query_arg( $endpoint, $value, $permalink );
			}

			return apply_filters( 'get_endpoint_url', $url, $endpoint, $value, $permalink );
		}

		public function get_endpoint_key( $endpoint ){
			$query_vars = $this->get_query_vars();
			$key = array_search($endpoint, $query_vars);
			return $key;
		}

		public function get_endpoint_title( $endpoint ){
			global $wp;
			$endpoint_key = $this->get_endpoint_key( $endpoint );
			$endpoint_title = '';
			switch ( $endpoint_key ) {
				case 'my-business':
					$endpoint_title = esc_html__( 'My Business', 'ovabookpro' );
					break;
				case 'my-wallet':
					$endpoint_title = esc_html__( 'My Wallet', 'ovabookpro' );
					break;
				case 'manage-booking':
					$endpoint_title = esc_html__( 'Manage Booking', 'ovabookpro' );
					break;
				case 'manage-service':
					$endpoint_title = esc_html__( 'Manage Service', 'ovabookpro' );
					break;
				case 'manage-type':
					$endpoint_title = esc_html__( 'Manage Type', 'ovabookpro' );
					break;
				case 'manage-plan':
					$endpoint_title = esc_html__( 'Manage Plan', 'ovabookpro' );
					break;
				case 'manage-staff':
					$endpoint_title = esc_html__( 'Manage Staff', 'ovabookpro' );
					break;
				case 'overall-schedule':
					$endpoint_title = esc_html__( 'Overall Schedule', 'ovabookpro' );
					break;
				case 'staff-schedule':
					$endpoint_title = esc_html__( 'My Schedule', 'ovabookpro' );
					break;
				case 'manage-coupon':
					$endpoint_title = esc_html__( 'Manage Coupon', 'ovabookpro' );
					break;
				case 'manage-role':
					$endpoint_title = esc_html__( 'Manage Role', 'ovabookpro' );
					break;
				case 'my-booking':
					$endpoint_title = esc_html__( 'My Booking', 'ovabookpro' );
					break;
				case 'my-wishlist':
					$endpoint_title = esc_html__( 'My Wishlist', 'ovabookpro' );
					break;
				case 'my-profile':
					$endpoint_title = esc_html__( 'My Profile', 'ovabookpro' );
					break;
				case 'edit-service':
					$post_id = isset( $wp->query_vars['edit-service'] ) ? absint( wp_unslash(  $wp->query_vars['edit-service'] ) ) : '';
					$endpoint_title = $post_id ? esc_html__( 'Edit Service', 'ovabookpro' ) : esc_html__( 'Add Service', 'ovabookpro' );
					break;
				case 'edit-staff':
					$staff_id = isset( $wp->query_vars['edit-staff'] ) ? absint( wp_unslash(  $wp->query_vars['edit-staff'] ) ) : '';
					$endpoint_title = $staff_id ? esc_html__( 'Edit Staff', 'ovabookpro' ) : esc_html__( 'Add Staff', 'ovabookpro' );
					break;
				case 'edit-coupon':
					$coupon_id = isset( $wp->query_vars['edit-coupon'] ) ? absint( wp_unslash(  $wp->query_vars['edit-coupon'] ) ) : '';
					$endpoint_title = $coupon_id ? esc_html__( 'Edit Coupon', 'ovabookpro' ) : esc_html__( 'Add Coupon', 'ovabookpro' );
					break;
				case 'logout':
					$endpoint_title = esc_html__( 'Logout', 'ovabookpro' );
					break;
				default:
					break;
			}	

			return apply_filters('obp_get_endpoint_title', $endpoint_title );
		}

		public function get_navigation_items(){
			$navigation_items = array();
			$query_vars = $this->get_query_vars();

			foreach ( $query_vars as $key => $value ) {
				$data_item = array();
				switch ( $key ) {
					case 'my-business':
						$data_item = array(
							'endpoint' 		=> $value,
							'icon'			=> 'bookproicon-home',
							'class' 		=> '',
							'url' 			=> $this->get_endpoint_url( $value ),
							'title' 		=> esc_html__( 'My Business', 'ovabookpro' ),
						);
						break;
					case 'my-wallet':
						$data_item = array(
							'endpoint' 		=> $value,
							'icon'			=> 'bookproicon-wallet',
							'class' 		=> '',
							'url' 			=> $this->get_endpoint_url( $value ),
							'title' 		=> esc_html__( 'My Wallet', 'ovabookpro' ),
						);
						break;
					case 'manage-booking':
						$data_item = array(
							'endpoint' 		=> $value,
							'icon' 			=> 'bookproicon-iteration',
							'class' 		=> '',
							'url' 			=> $this->get_endpoint_url( $value ),
							'title' 		=> esc_html__( 'Manage Booking', 'ovabookpro' ),
						);
						break;
					case 'manage-service':
						$data_item = array(
							'endpoint' 		=> $value,
							'icon'			=> 'bookproicon-category',
							'class' 		=> '',
							'url' 			=> $this->get_endpoint_url( $value ),
							'title' 		=> esc_html__( 'Manage Service', 'ovabookpro' ),
							'child_endpoint' => array(
								$query_vars['edit-service']
							),
						);
						break;
					case 'manage-type':
						$data_item = array(
							'endpoint' 		=> $value,
							'icon' 			=> 'bookproicon-folder',
							'class' 		=> 'item-child',
							'url' 			=> $this->get_endpoint_url( $value ),
							'title' 		=> esc_html__( 'Manage Type', 'ovabookpro' ),
						);
						break;
					case 'manage-plan':
						$data_item = array(
							'endpoint' 		=> $value,
							'icon' 			=> 'bookproicon-calendar-1',
							'class' 		=> 'item-child',
							'url' 			=> $this->get_endpoint_url( $value ),
							'title' 		=> esc_html__( 'Manage Plan', 'ovabookpro' ),
						);
						break;
					case 'manage-staff':
						$data_item = array(
							'endpoint' 		=> $value,
							'icon' 			=> 'bookproicon-employees',
							'class' 		=> 'item-child',
							'url' 			=> $this->get_endpoint_url( $value ),
							'title' 		=> esc_html__( 'Manage Staff', 'ovabookpro' ),
							'child_endpoint' => array(
								$query_vars['edit-staff']
							),
						);
						break;
					case 'overall-schedule':
						$data_item = array(
							'endpoint' 		=> $value,
							'icon'			=> 'bookproicon-task',
							'class' 		=> '',
							'url' 			=> $this->get_endpoint_url( $value ),
							'title' 		=> esc_html__( 'Overall Schedule', 'ovabookpro' ),
						);
						break;
					case 'staff-schedule':
						$data_item = array(
							'endpoint' 		=> $value,
							'icon'			=> 'bookproicon-user-1',
							'class' 		=> '',
							'url' 			=> $this->get_endpoint_url( $value ),
							'title' 		=> esc_html__( 'My Schedule', 'ovabookpro' ),
						);
						break;
					case 'manage-coupon':
						$data_item = array(
							'endpoint' 		=> $value,
							'icon'			=> 'bookproicon-user-1',
							'class' 		=> '',
							'url' 			=> $this->get_endpoint_url( $value ),
							'title' 		=> esc_html__( 'Manage Coupon', 'ovabookpro' ),
							'child_endpoint' => array(
								$query_vars['edit-coupon']
							),
						);
						break;
					case 'manage-role':
						$data_item = array(
							'endpoint' 		=> $value,
							'icon' 			=> 'bookproicon-change',
							'class'			=> '',
							'url' 			=> $this->get_endpoint_url( $value ),
							'title' 		=> esc_html__( 'Manage Role', 'ovabookpro' ),
						);
						break;
					case 'my-booking':
						$data_item = array(
							'endpoint' 		=> $value,
							'icon' 			=> 'bookproicon-lists',
							'class' 		=> '',
							'url' 			=> $this->get_endpoint_url( $value ),
							'title' 		=> esc_html__( 'My Booking', 'ovabookpro' ),
						);
						break;

					case 'my-wishlist':
						if ( apply_filters( 'obp_vendor_plugin_activated', false ) == true ) {
							$data_item = array(
								'endpoint' 			=> $value,
								'icon' 				=> 'bookproicon-heart',
								'class' 			=> '',
								'url' 				=> $this->get_endpoint_url( $value ),
								'title' 			=> esc_html__( 'My Wishlist', 'ovabookpro' ),
							);
						}
						break;
					case 'my-profile':
						$data_item = array(
							'endpoint' 			=> $value,
							'icon' 				=> 'bookproicon-user',
							'class' 			=> '',
							'url' 				=> $this->get_endpoint_url( $value ),
							'title' 			=> esc_html__( 'My Profile', 'ovabookpro' ),
						);
						break;

					case 'edit-service':
					case 'edit-staff':
					case 'edit-coupon':
					case 'our-work':
					break;
					case 'logout':
						$data_item = array(
							'endpoint' 			=> $value,
							'icon' 				=> 'bookproicon-exit',
							'class' 			=> '',
							'url' 				=> $this->get_endpoint_url( $value ),
							'title' 			=> esc_html__( 'Logout', 'ovabookpro' ),
						);
					break;
					
					default:
						$data_item = array(
							'endpoint' 			=> $value,
							'icon' 				=> 'bookproicon-category',
							'class' 			=> '',
							'url' 				=> $this->get_endpoint_url( $value ),
							'title' 			=> esc_html__( 'Custom Endpoint', 'ovabookpro' ),
						);
						break;
				}
				if ( ! empty( $data_item ) ) {
					$data_item = apply_filters( 'obp_navigation_'.$key.'_item', $data_item );
					$navigation_items[$key] = $data_item;
				}
			}

			return apply_filters( 'get_navigation_items', $navigation_items );
		}

		public function get_capabilities(){
			$result = [];
			$query_vars = $this->get_query_vars();
			$capabilities = array(
				'my-business' 		=> 'my_business',
				'my-wallet' 		=> 'my_wallet',
				'manage-booking' 	=> 'manage_booking',
				'manage-service' 	=> 'manage_service',
				'edit-service' 		=> 'manage_service',
				'edit-staff' 		=> 'manage_staff',
				'manage-type' 		=> 'manage_type',
				'manage-plan' 		=> 'manage_plan',
				'manage-staff' 		=> 'manage_staff',
				'manage-coupon' 	=> 'manage_coupon',
				'overall-schedule' 	=> 'manage_schedules',
				'staff-schedule' 	=> 'staff_schedule',
				'manage-role' 		=> 'manage_role',
				'my-booking' 		=> 'my_booking',
				'my-wishlist' 		=> 'my_wishlist',
				'my-profile' 		=> 'my_profile',
			);
			foreach ( $query_vars as $key => $value ) {
				$result[$value] = isset( $capabilities[$key] ) ? $capabilities[$key] : '';
			}

			return $result;
		}

		public function get_current_endpoint(){
			global $wp_query;
			
			// Default endpoint
			$default_endpoint = 'my-business';

			if ( OBP_Permission::is_staff() ) {
				$default_endpoint = $this->settings->general->get( 'default_endpoint_staff', 'my-profile' );
			} elseif ( OBP_Permission::is_customer() ) {
				$default_endpoint = $this->settings->general->get( 'default_endpoint_customer', 'my-profile' );
			}
			
			$query_vars = $this->get_query_vars();

			foreach ( $query_vars as $key => $value ) {
				if ( isset( $wp_query->query_vars[$key] ) ) {

					return $value;
				}
			}

			$default_endpoint = apply_filters( 'obp_default_endpoint', $default_endpoint, $this );

			return $query_vars[$default_endpoint];
		}

		public static function install(){
			flush_rewrite_rules();
		}

	}
	
}