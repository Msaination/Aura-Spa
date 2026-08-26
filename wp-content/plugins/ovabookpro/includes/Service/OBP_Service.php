<?php 
namespace BookPro\Service;
defined( 'ABSPATH' ) || exit;

use BookPro\Staff\OBP_Staff;
use BookPro\User\OBP_User;
use BookPro\OBP_Permission;
use WP_Query;
use BookPro\Type\OBP_Type;
use BookPro\Tax\OBP_Tax_Class;


class OBP_Service {

	protected static $_instance = null;


	public static function init(){

		add_action( 'obp_load_member_account_manage-service_scripts', array( __CLASS__, 'load_scripts_member_account' ) );
		add_action( 'obp_load_member_account_edit-service_scripts', array( __CLASS__, 'load_scripts_member_account' ) );
	}

	public static function load_scripts_member_account( $assets ){
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), array( 'iris' ), false, true);
		wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array(  'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch'  ), false, true );

		wp_enqueue_style( 'obp-timepicker' );
		wp_enqueue_script( 'obp-timepicker' );

		wp_enqueue_style( 'zebra-dialog');
		wp_enqueue_script('zebra-dialog');

		wp_enqueue_style( 'flatpickr' );
		wp_enqueue_script( 'flatpickr' );
		wp_enqueue_script( 'flatpickr-localize' );
		wp_enqueue_script( 'flatpickr-rangePlugin' );


		$currency_symbol 	= obp_get_currency_symbol();
		$currency_position 	= obp_get_currency_position();
		$thousand_separator = obp_get_thousand_separator();
		$decimal_separator 	= obp_get_decimal_separator();
		$number_decimal 	= obp_get_price_num_decimals();

		$currency_object = array(
			'currency_symbol' 		=> $currency_symbol,
			'currency_position' 	=> $currency_position,
			'thousand_separator' 	=> $thousand_separator,
			'decimal_separator' 	=> $decimal_separator,
			'number_decimal' 		=> $number_decimal,
		);

		wp_enqueue_script( 'obp-frontend-service', OBP_PLUGIN_URI.'assets/js/frontend/service.js', array('jquery'), false, true );
		wp_localize_script( 'obp-frontend-service', 'currency_object', $currency_object );
		wp_localize_script( 'obp-frontend-service', 'obp_service_obj', array(
			'confirm_delete' 	=> esc_html__( 'Are you sure you want to delete this record? This action cannot be undone', 'ovabookpro' ),
			'yes' 				=> esc_html__( 'Yes', 'ovabookpro' ),
			'no' 				=> esc_html__( 'No','ovabookpro' ),
			'name_req' 			=> esc_html__( 'Name is required', 'ovabookpro' ),
			'package_name_req' 	=> esc_html__( 'Package Name is required', 'ovabookpro' ),
			'package_label_req' => esc_html__( 'Package Label is required', 'ovabookpro' ),
			'package_time_req' 	=> esc_html__( 'Package Time is required', 'ovabookpro' ),
			'service_time_req' 	=> esc_html__( 'Service Duration is required', 'ovabookpro' ),
			'price_invalid' 	=> esc_html__( 'Price is invalid.', 'ovabookpro' ),
		) );

		wp_localize_script( 'obp-frontend-service', 'obp_staff_obj', array(
			'role_req' 			=> esc_html__( 'Role is required', 'ovabookpro' ),
			'username_req' 		=> esc_html__( 'Username is required', 'ovabookpro' ),
			'email_req' 		=> esc_html__( 'Email is required', 'ovabookpro' ),
			'email_invalid' 	=> esc_html__( 'Email is invalid', 'ovabookpro' ),
			'password_req' 		=> esc_html__( 'Password is required', 'ovabookpro' ),
			'nickname_req' 		=> esc_html__( 'Nickname is required', 'ovabookpro' ),
			'yes' => esc_html__( 'Yes', 'ovabookpro' ),
			'no' => esc_html__( 'No','ovabookpro' ),
		) );
	}


	/* Get hours */
	public static function get_hours() {
		$h_suffix = esc_html__('h','ovabookpro');

		$hours = array_unique(
			apply_filters (
				'obp_service_hours',
				array(
					0  => '0'.$h_suffix,
					1  => '1'.$h_suffix,
					2  => '2'.$h_suffix,
					3  => '3'.$h_suffix,
					4  => '4'.$h_suffix,
					5  => '5'.$h_suffix,
					6  => '6'.$h_suffix,
					7  => '7'.$h_suffix,
					8  => '8'.$h_suffix,
					9  => '9'.$h_suffix,
					10 => '10'.$h_suffix,
					11 => '11'.$h_suffix,
					12 => '12'.$h_suffix,
					13 => '13'.$h_suffix,
					14 => '14'.$h_suffix,
					15 => '15'.$h_suffix,
					16 => '16'.$h_suffix,
					17 => '17'.$h_suffix,
					18 => '18'.$h_suffix,
					19 => '19'.$h_suffix,
					20 => '20'.$h_suffix,
					21 => '21'.$h_suffix,
					22 => '22'.$h_suffix,
					23 => '23'.$h_suffix,
				)
			)
		);

		return $hours;
	}

	/* Get minutes */
	public static function get_minutes() {
		$m_suffix = esc_html__('min','ovabookpro');

		$minutes = array_unique(
			apply_filters (
				'obp_service_minutes',
				array(
					0  => '0'.$m_suffix,
					5  => '5'.$m_suffix,
					10 => '10'.$m_suffix,
					15 => '15'.$m_suffix,
					20 => '20'.$m_suffix,
					25 => '25'.$m_suffix,
					30 => '30'.$m_suffix,
					35 => '35'.$m_suffix,
					40 => '40'.$m_suffix,
					45 => '45'.$m_suffix,
					50 => '50'.$m_suffix,
					55 => '55'.$m_suffix,
				)
			)
		);

		return $minutes;
	}

	/* Get price types */
	public static function get_price_types() {
		
		$hours = array_unique(
			apply_filters (
				'obp_service_price_types',
				array(
					'fixed' 	=> esc_html__( 'Fixed', 'ovabookpro' ),
					'free' 		=> esc_html__( 'Free ', 'ovabookpro' ),
					'start_at' 	=> esc_html__( 'Start at', 'ovabookpro' ),
					'varies' 	=> esc_html__( 'Varies', 'ovabookpro' ),
					'not_show' 	=> esc_html__( "Don't show", 'ovabookpro' ),
				)
			)
		);

		return $hours;
	}

	/* Get category service group by Type <used by Manage Plan> */
	public static function get_category_service_groups( $vendor_id = null, $excludes = array(), $service_name = null, ){
			
		if ( is_null( $vendor_id ) ) {
			$vendor_id = obp_get_vendor_id();
		}
		
		$data_group = array();

		$all_types = OBP_Type::get_types( $vendor_id );

		// base query
		$args = array(
			'post_type' 		=> 'obp_service',
			'post_status' 		=> 'publish',
			'order' 			=> 'ASC',
			'orderby' 			=> 'title',
			'posts_per_page' 	=> -1,
			'post__not_in' 		=> $excludes,
			'meta_query' => array(
				array(
					'key' 	=> OBP_METABOX.'vendor_id',
					'value' => $vendor_id
				),
			),
			'fields' => 'ids',
		);

		if( $service_name ) {
			$args['s'] = $service_name;
			$args['search_columns'] = 'post_title';
		}

		if ( count( $all_types ) > 0 ) {

			foreach ( $all_types as $type_id ) {

				$type = obp_get_type( $type_id );
				if ( $type->get_id() ) {
					$data_arr = array();
					$args_2['meta_query'] = array(
						array(
							'key' 	=> OBP_METABOX.'type',
							'value' => $type->get_id()
						),
					);
					
					$sv_args = array_merge_recursive( $args, $args_2 );

					$service_ids = get_posts( $sv_args );
					if ( $service_ids ) {
						$data_arr['category'] = $type->get_name();
						$data_arr['services'] = array();

						foreach( $service_ids as $service_id ) {
							$service = obp_get_service( $service_id );
							if ( get_post_type( $service_id ) == 'obp_service' ) {
								$id    = $service->get_id();
								$data_arr['services'][] = $id;
								$excludes[] = $id;
							}
						}
			
					}

					if ( ! empty( $data_arr ) ) {
						$data_group[] = $data_arr;
					}
					
				}
				
			}

		}

		// Other Services
		$args['post__not_in'] = $excludes;
		$service_ids = get_posts( $args );

		if( ! empty( $service_ids ) ) {
			$data_arr = array();
			foreach( $service_ids as $service_id ) {
				$service = obp_get_service( $service_id );
				if ( get_post_type( $service_id ) == 'obp_service' ) {
					$id    = $service->get_id();
					$data_arr['services'][] = $id;
				}
			}

			if ( ! empty( $data_arr ) ) {
				$data_arr['category'] = esc_html__( 'Other', 'ovabookpro' );
				$data_group[] = $data_arr;
			}
		}

		return apply_filters( 'obp_get_category_service_groups', $data_group, $vendor_id, $all_types, $service_name );

	}

	/* Use in Booking Popup */
	public static function get_service_ids_exclude_cart( $vendor_id ,$service_ids_cart = array() ){
		$_prefix = OBP_METABOX;

		$args = array(
			'post_type' 		=> 'obp_service',
			'post_status' 		=> 'publish',
			'order' 			=> 'ASC',
			'orderby' 			=> 'title',
			'posts_per_page' 	=> -1,
			'meta_key' 			=> $_prefix.'vendor_id',
			'meta_value' 		=> $vendor_id,
			'post__not_in' 		=> $service_ids_cart,
			'fields' 			=> 'ids',
		);

		$service_ids = get_posts( $args );

		return $service_ids;
	}

	public static function get_list_service_ajax( $vendor_id = '' ){
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
		
		if ( empty( $vendor_id ) ) {
			$vendor_id 	= isset( $_POST['vendor_id'] ) ? sanitize_text_field( wp_unslash( $_POST['vendor_id'] ) ) : '';
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		$data_group = array();
		$sv_exclude = array();

		$all_types = OBP_Type::get_types( $vendor_id );

		// base query
		$args = array(
			'post_type' 		=> 'obp_service',
			'post_status' 		=> 'publish',
			'order' 			=> 'ASC',
			'orderby' 			=> 'name',
			'posts_per_page' 	=> -1,
			'fields' 			=> 'ids',
			'meta_query' => array(
				array(
					'key' 	=> OBP_METABOX.'vendor_id',
					'value' => $vendor_id
				),
			),
		);

		if( $keyword ) {
			$args['s'] = $keyword;
			$args['search_columns'] = 'post_title';
		}

		if ( count( $all_types ) > 0 ) {

			foreach ( $all_types as $type_id ) {

				$type = obp_get_type( $type_id );
				if ( $type ) {
					$data_arr = array();
					$args_2['meta_query'] = array(
						array(
							'key' 	=> OBP_METABOX.'type',
							'value' => $type->get_id()
						),
					);
					
					$sv_args = array_merge_recursive( $args, $args_2 );

					$service_ids = get_posts( $sv_args );
					if ( ! empty( $service_ids ) ) {
						$data_arr['category'] = $type->get_name();
						$data_arr['services'] = array();

						foreach( $service_ids as $service_id ) {
					
							$_service = obp_get_service( $service_id );
							if ( get_post_type( $service_id ) == 'obp_service' ) {
								$id    = $_service->get_id();
								$data_arr['services'][] = $id;
								$sv_exclude[] = $id;
							}
							
						}
			
					}

					if ( ! empty( $data_arr ) ) {
						$data_group[] = $data_arr;
					}
					
				}
				
			}
	
		}

		// Other Services
		$args['post__not_in'] = $sv_exclude;
		$service_ids = get_posts( $args );

		if( ! empty( $service_ids ) ) {
			$data_arr = array();
			foreach( $service_ids as $service_id ) {
				$service = obp_get_service( $service_id );
				if ( get_post_type( $service_id ) == 'obp_service' ) {
					$id    = $service->get_id();
					$data_arr['services'][] = $id;
				}
			}

			if ( ! empty( $data_arr ) ) {
				$data_arr['category'] = esc_html__( 'Other', 'ovabookpro' );
				$data_group[] = $data_arr;
			}
		}

		return apply_filters( 'obp_get_list_service_ajax', $data_group, $vendor_id, $keyword, $all_types );
	}

	public static function get_service_by_type( $type_ids = array() ){
		$data_group = array();
		$sv_exclude = array();

		if ( ! empty( $type_ids ) ) {
			$all_types = OBP_Type::get_types_by_id( $type_ids );
		} else {
			$all_types = OBP_Type::get_all_types();
		}

		// base query
		$args = array(
			'post_type' 		=> 'obp_service',
			'post_status' 		=> 'publish',
			'order' 			=> 'ASC',
			'orderby' 			=> 'name',
			'posts_per_page' 	=> -1,
			'fields' 			=> 'ids',
		);

		if ( count( $all_types ) > 0 ) {

			foreach ( $all_types as $type_id ) {
				$type = obp_get_type( $type_id );
				if ( $type->get_id() ) {
					$data_arr = array();
					$args_2['meta_query'] = array(
						array(
							'key' 	=> OBP_METABOX.'type',
							'value' => $type->get_id()
						),
					);
					
					$sv_args = array_merge_recursive( $args, $args_2 );

					$service_ids = get_posts( $sv_args );
					if ( $service_ids ) {
						$data_arr['category'] = $type->get_name();
						$data_arr['services'] = array();

						foreach( $service_ids as $service_id ) {
							$service = obp_get_service( $service_id );
							if ( $service->get_id() ) {
								$id    = $service->get_id();
								$data_arr['services'][] = $id;
								$sv_exclude[] = $id;
							}
							
						}
		
					}

					if ( ! empty( $data_arr ) ) {
						$data_group[] = $data_arr;
					}
					
				}
				
			}
		}

		if ( empty( $type_ids ) ) {
			// Other Services
			$args['post__not_in'] = $sv_exclude;
			$service_ids = get_posts( $args );

			if( ! empty( $service_ids ) ) {
				$data_arr = array();
				foreach( $service_ids as $service_id ) {
					$service = obp_get_service( $service_id );
					if ( $service->get_id() ) {
						$id    = $service->get_id();
						$data_arr['services'][] = $id;
					}
				}

				if ( ! empty( $data_arr ) ) {
					$data_arr['category'] = esc_html__( 'Other', 'ovabookpro' );
					$data_group[] = $data_arr;
				}
			}
		}

		return apply_filters( 'obp_get_service_by_type', $data_group, $all_types, $type_ids );
	}

	public static function get_service_ajax(){
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$name 	= isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$sortby = isset( $_POST['sortby'] ) ? sanitize_text_field( wp_unslash( $_POST['sortby'] ) ) : '';
		$page 	= isset( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : 1;
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		$vendor_id 	= OBP_User::get_vendor_id();
		$order 		= 'ASC';
		$orderby 	= 'title';

		switch ( $sortby ) {
			case 'title_desc':
				$order = 'DESC';
				break;

			case 'ID':
				$orderby = 'ID';
				break;

			case 'ID_desc':
				$orderby 	= 'ID';
				$order 		= 'DESC';
				break;
			default:
				break;
		}

		$posts_per_page = get_option( 'posts_per_page' );

		$meta_query = array(
			array(
				'key' 	=> OBP_METABOX.'vendor_id',
				'value' => $vendor_id,
			),
		);

		$args = array(
			'post_type' 		=> 'obp_service',
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> $posts_per_page,
			'order' 			=> $order,
			'orderby' 			=> $orderby,
			'meta_query' 		=> $meta_query,
		);

		if ( $name ) {
			$args['s'] = $name;
		}

		$offset = absint( absint( $page ) - 1 ) * absint( $posts_per_page );

		if ( $offset > 0 ) {
			$args['offset'] = $offset;
		}

		$args = apply_filters( 'obp_get_service_ajax_args', $args, $name );

		$the_query = new WP_Query( $args );

		return apply_filters( 'obp_get_service_ajax', $the_query );
	}

	/* Get service ids */
	public static function get_service_ids(){
		$_prefix   = OBP_METABOX;
		$vendor_id = obp_get_vendor_id();

		$args = array(
			'post_type' 		=> 'obp_service',
			'post_status' 		=> 'publish',
			'order' 			=> 'ASC',
			'orderby' 			=> 'title',
			'posts_per_page' 	=> -1,
			'meta_key' 			=> $_prefix.'vendor_id',
			'meta_value' 		=> $vendor_id,
			'fields' 			=> 'ids',
		);

		$service_ids = get_posts( $args );

		return $service_ids;
	}

	public static function get_service_ids_by_vendor_id( $vendor_id ){
		$_prefix = OBP_METABOX;

		$args = array(
			'post_type' 		=> 'obp_service',
			'post_status' 		=> 'publish',
			'order' 			=> 'ASC',
			'orderby' 			=> 'title',
			'posts_per_page' 	=> -1,
			'meta_key' 			=> $_prefix.'vendor_id',
			'meta_value' 		=> $vendor_id,
			'fields' 			=> 'ids',
		);

		$service_ids = get_posts( $args );

		return $service_ids;
	}

	/* Used in Search ajax: search results - list service items below business name and address */
	public static function get_popular_service_ids_by_vendor_id( $vendor_id ){
		$_prefix = OBP_METABOX;

		$args = array(
			'post_type' 		=> 'obp_service',
			'post_status' 		=> 'publish',
			'order' 			=> 'ASC',
			'orderby' 			=> 'title',
			'posts_per_page' 	=> 3,
			'meta_key' 			=> $_prefix.'vendor_id',
			'meta_value' 		=> $vendor_id,
			'fields' 			=> 'ids',
		);

		$service_ids = get_posts( $args );

		return $service_ids;
	}

	/* Used in Search ajax: suggestions for popular service tags */
	public static function get_popular_service_ids(){
		$_prefix = OBP_METABOX;

		$args = array(
			'post_type' 		=> 'obp_service',
			'post_status' 		=> 'publish',
			'orderby' 			=> 'rand',
			'posts_per_page' 	=> 7,
			'fields' 			=> 'ids',
		);

		$service_ids = get_posts( $args );

		return $service_ids;
	}

	public static function get_services_object( $service_ids = array() ){
		$services_object = array();
		if ( count( $service_ids ) > 0 ) {
			foreach ( $service_ids as $key => $service_id ) {
				$services_object[] = new OBP_Service_Item( $service_id );
			}
		}

		return $services_object;
	}

	public static function edit_service_args(){
		global $wp;

		$vendor_id = OBP_User::get_vendor_id();
		$post_id = isset( $wp->query_vars['edit-service'] ) ? absint( wp_unslash(  $wp->query_vars['edit-service'] ) ) : '';

		$service = obp_get_service( $post_id );

		$all_types = OBP_Type::get_types( $vendor_id );

		$settings_editor = array(
			'textarea_name' => 'obp_service_description',
			'media_buttons' => apply_filters( 'obp_add_media_service_description', true ),
			'textarea_rows' => 5,
			'wpautop' 		=> false,
		);

		/* get service data */
		$service_name = $service->get_title();
		$hour  		  = $service->get_hours();
		$minute  	  = $service->get_minutes();
		$price_type   = $service->get_price_type();
		$price        = $service->get_price();
		$color        = $service->get_color();
	    $staff_ids    = $service->get_staff_ids();
	    $sale_price   = $service->get_sale_price();
	    $use_on 	  = $service->get_use_on();
	    $packages 	  = $service->get_packages();
	    $note_price   = $service->get_note_price();
	    $tax_class 	  = $service->get_tax_class();

	    $sale_off_start_date 	= ! empty( $service->get_sale_off_start_date() ) ? gmdate("Y-m-d", $service->get_sale_off_start_date() ) : "";
	    $sale_off_end_date 		= ! empty( $service->get_sale_off_end_date() ) ? gmdate("Y-m-d", $service->get_sale_off_end_date() ) : "";

	    $sale_off_from 	= ! empty( $service->get_sale_off_from() ) ? $service->get_sale_off_from() : "";
	    $sale_off_to 	= ! empty( $service->get_sale_off_to() ) ? $service->get_sale_off_to() : "";

		if( $post_id == '') {
			$price = '';
		}

		$list_user = OBP_Staff::get_view_schedule_staff();

	    $type = $service->get_type();


		/* add new Type url */
		$manage_type_endpoint   = OBP()->endpoint->get_query_vars()['manage-type'];
		$add_type_url     		= OBP()->endpoint->get_endpoint_url($manage_type_endpoint);

		/* add new Staff url */
		$manage_staff_endpoint  = OBP()->endpoint->get_query_vars()['manage-staff'];
		$add_staff_url          = OBP()->endpoint->get_endpoint_url($manage_staff_endpoint);

		$tax_classes 		= OBP_Tax_Class::get_tax_classes();
		$vendor_choose_tax 	= OBP()->settings->tax->get('vendor_choose_tax', 'no');
		$price_inc_tax 		= OBP()->settings->tax->get('prices_include_tax', 'no');
		$price_help_content = $price_inc_tax == 'yes' ? esc_attr__( 'Price included tax', 'ovabookpro' ) : esc_attr__( 'Price exclude tax', 'ovabookpro' );

		$args = array(
			'post_id' 		  		=> $post_id,
			'all_types' 		  	=> $all_types,
			'service_name'    		=> $service_name,
			'settings_editor' 		=> $settings_editor,
			'hour' 			  		=> $hour,
			'minute' 		  		=> $minute,
			'price_type' 	  		=> $price_type,
			'price' 		  		=> $price,
			'sale_price' 	  		=> $sale_price,
			'sale_off_start_date' 	=> $sale_off_start_date,
			'sale_off_end_date'   	=> $sale_off_end_date,
			'sale_off_from' 		=> $sale_off_from,
			'sale_off_to' 			=> $sale_off_to,
			'color' 		  		=> $color,
			'staff_ids' 	  		=> $staff_ids,
			'type' 			  		=> $type,
			'add_type_url'    		=> $add_type_url,
			'add_staff_url'   		=> $add_staff_url,
			'service'   			=> $service,
			'list_user' 	  		=> $list_user,
			'use_on' 	  			=> $use_on,
			'packages' 	  			=> $packages,
			'note_price' 	  		=> $note_price,
			'tax_class' 	  		=> $tax_class,
			'tax_classes' 	  		=> $tax_classes,
			'vendor_choose_tax' 	=> $vendor_choose_tax,
			'price_help_content' 	=> $price_help_content,
		);

		return apply_filters( 'obp_service_edit_args', $args );
	}

	public static function get_service_by_keyword(){
		$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$args = array(
			'post_type' 		=> 'obp_service',
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> 5,
			'order' 			=> 'ASC',
			'orderby' 			=> 'ID',
			'fields' 			=> 'ids',
		);

		if ( $keyword ) {
			$args['s'] = $keyword;
		}

		$services = get_posts( $args );
		return $services;
	}

	/**
	 * Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}