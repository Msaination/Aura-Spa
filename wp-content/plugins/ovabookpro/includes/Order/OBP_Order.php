<?php

namespace BookPro\Order;

use BookPro\Traits\SingletonTrait;
use BookPro\Business\OBP_Business;
use BookPro\User\OBP_User;
use BookPro\Order\OBP_Order_Meta;
use BookPro\OBP_Mail;
use BookPro\Cart\OBP_Cart_Order;
use BookPro\OBP_Permission;
use WP_Query;


defined( 'ABSPATH' ) || exit;


if ( ! class_exists("OBP_Order") ) {
	

	class OBP_Order {

		use SingletonTrait;

		public static function init(){

			add_action( 'obp_load_member_account_my-booking_scripts', array( __CLASS__, 'load_my_booking_scripts' ) );
			add_action( 'obp_load_member_account_manage-booking_scripts', array( __CLASS__, 'load_manager_orders_scripts' ) );
		}

		public static function load_my_booking_scripts( $assets ){

			wp_enqueue_style( 'flatpickr' );
			wp_enqueue_script( 'flatpickr' );
			wp_enqueue_script( 'flatpickr-localize' );
			wp_enqueue_script( 'flatpickr-rangePlugin' );

			wp_enqueue_style('zebra-dialog');
			wp_enqueue_script('zebra-dialog');

			wp_enqueue_script('obp-order', OBP_PLUGIN_URI.'assets/js/frontend/order.js' , array('jquery'), false, true );

			wp_localize_script( 'obp-order', 'obp_order_obj', array(
				'confirm_cancel' 	=> esc_html__( 'Are you sure you want to cancel your order?', 'ovabookpro' ),
				'yes' 				=> esc_html__( 'Yes', 'ovabookpro' ),
				'no' 				=> esc_html__( 'No', 'ovabookpro' ),
				'media_title' 		=> esc_html__( 'Add media', 'ovabookpro' ),
				'media_button' 		=> esc_html__( 'Select', 'ovabookpro' ),
				'rate_invalid' 		=> esc_html__( 'You have not rated any stars.', 'ovabookpro' ),
			) );

			wp_enqueue_script('obp-change-order', OBP_PLUGIN_URI.'assets/js/frontend/change-order.js' , array('jquery'), false, true );
		}

		public static function load_manager_orders_scripts(){
			wp_enqueue_style( 'flatpickr' );
			wp_enqueue_script( 'flatpickr' );
			wp_enqueue_script( 'flatpickr-localize' );
			wp_enqueue_script( 'flatpickr-rangePlugin' );

			wp_enqueue_style('zebra-dialog');
			wp_enqueue_script('zebra-dialog');


			wp_enqueue_script('obp-manager-order', OBP_PLUGIN_URI.'assets/js/frontend/manage-order.js' , array('jquery'), false, true );

			wp_localize_script( 'obp-manager-order', 'obp_order_obj', array(
				'confirm_cancel' 	=> esc_html__( 'Are you sure you want to cancel your order?', 'ovabookpro' ),
				'yes' 				=> esc_html__( 'Yes', 'ovabookpro' ),
				'no' 				=> esc_html__( 'No', 'ovabookpro' ),
				'from_date_req' 	=> esc_html__( 'From date is required', 'ovabookpro' ),
				'end_date_req' 		=> esc_html__( 'End date is required', 'ovabookpro' ),
			) );

			wp_enqueue_script('obp-change-order', OBP_PLUGIN_URI.'assets/js/frontend/change-order.js' , array('jquery'), false, true );
		}

		public static function get_order_ids_by_status( $status = "obp_completed" ){

			$args = array(
				'post_type' 		=> 'obp_order',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'meta_key' 			=> OBP_METABOX.'order_status',
				'meta_value' 		=> $status,
				'fields' 			=> 'ids',
			);

			$order_ids = get_posts( $args );
			return $order_ids;
		}

		public static function get_orders_data_export(){
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			$fields 		= isset( $_POST['fields'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['fields'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$from_date 		= isset( $_POST['from_date'] ) ? sanitize_text_field( wp_unslash( $_POST['from_date'] ) ) : '';
			$to_date 		= isset( $_POST['to_date'] ) ? sanitize_text_field( wp_unslash( $_POST['to_date'] ) ) : '';
			$order_status 	= isset( $_POST['order_status'] ) ? sanitize_text_field( wp_unslash( $_POST['order_status'] ) ) : 'All';
			$date_filter = isset( $_POST['date_filter'] ) ? sanitize_text_field( wp_unslash( $_POST['date_filter'] ) ) : 'service_date';
			$date_key = $date_filter == 'service_date' ? OBP_METABOX.'start_date' : OBP_METABOX.'date_created';
			// phpcs:enable WordPress.Security.NonceVerification.Missing
			$args = array(
				'post_type' 		=> 'obp_order',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'order' 			=> 'DESC',
				'orderby' 			=> 'ID',
				'fields' 			=> 'ids',
			);

			$meta_query = array();

			if ( ! OBP_Permission::is_administrator() ) {
				$meta_query[] = array(
					'key' 			=> OBP_METABOX.'vendor_id',
					'value' 		=> obp_get_vendor_id(),
				);
			}

			if ( ! empty( $from_date ) ) {
				$meta_query[] = array(
					'key' 		=> $date_key,
					'value' 	=> strtotime( $from_date ),
					'compare' 	=> '>=',
					'type' 		=> 'NUMERIC',
				);
			}

			if ( ! empty( $to_date ) ) {
				$to_date = absint( strtotime( $to_date ) ) + (3600*24) - 1;

				$meta_query[] = array(
					'key' 		=> $date_key,
					'value' 	=> $to_date,
					'compare' 	=> '<=',
					'type' 		=> 'NUMERIC',
				);
			}

			if ( $order_status != 'All' ) {
				$meta_query[] = array(
					'key' 		=> OBP_METABOX.'order_status',
					'value' 	=> $order_status,
				);
			}

			if ( ! empty( $meta_query ) ) {
				$args['meta_query'] = $meta_query;
			}

			$orders = get_posts( $args );

			$data_export 	= [];
			$first_row 		= [];
			// First row
			if ( in_array( 'id', $fields ) ) {
				$first_row[] = esc_html__( 'ID', 'ovabookpro' );
			}

			if ( in_array( 'name', $fields ) ) {
				$first_row[] = esc_html__( 'Name', 'ovabookpro' );
			}

			if ( in_array( 'phone', $fields ) ) {
				$first_row[] = esc_html__( 'Phone', 'ovabookpro' );
			}

			if ( in_array( 'email', $fields ) ) {
				$first_row[] = esc_html__( 'Email', 'ovabookpro' );
			}

			if ( in_array( 'note' , $fields ) ) {
				$first_row[] = esc_html__( 'Note', 'ovabookpro' );
			}

			if ( in_array( 'service', $fields ) ) {
				$first_row[] = esc_html__( 'Services', 'ovabookpro' );
			}

			if ( in_array( 'payment_gateway', $fields ) ) {
				$first_row[] = esc_html__( 'Payment Gateway', 'ovabookpro' );
			}

			if ( in_array( 'payment_method', $fields ) ) {
				$first_row[] = esc_html__( 'Payment Method', 'ovabookpro' );
			}

			if ( in_array( 'date_created', $fields ) ) {
				$first_row[] = esc_html__( 'Date Created', 'ovabookpro' );
			}

			if ( in_array( 'status', $fields ) ) {
				$first_row[] = esc_html__( 'Status', 'ovabookpro' );
			}

			if ( in_array( 'tax', $fields ) ) {
				$first_row[] = esc_html__( 'Tax', 'ovabookpro' );
			}

			if ( in_array( 'system_fee', $fields ) ) {
				$first_row[] = esc_html__( 'System Fee', 'ovabookpro' );
			}

			$first_row = apply_filters( 'obp_order_export_first_row', $first_row, $fields );

			// Total
			if ( in_array( 'total', $fields ) ) {
				$first_row[] = esc_html__( 'Total', 'ovabookpro' );
			}

			if ( ! empty( $first_row ) ) {
				$data_export[] = $first_row;
			}

			if ( count( $orders ) > 0 ) {
				foreach ( $orders as $order_id ) {
					$row = [];
					$order = obp_get_order( $order_id );

					if ( in_array( 'id', $fields ) ) {
						$row[] = $order_id;
					}

					if ( in_array( 'name', $fields ) ) {
						$row[] = $order->get_customer_name();
					}

					if ( in_array( 'phone', $fields ) ) {
						$row[] = $order->get_customer_phone();
					}

					if ( in_array( 'email', $fields ) ) {
						$row[] = $order->get_customer_email();
					}

					if ( in_array( 'note', $fields ) ) {
						$row[] = $order->get_customer_note();
					}

					if ( in_array( 'service', $fields ) ) {
						$order_meta_items = OBP_Order_Meta::get_order_items( $order_id );
						$service_name = [];
						foreach ( $order_meta_items as $key => $value ) {
							$item = obp_get_order_meta( $value );
							$service_name[] = $item->get_service_name();
						}
						$row[] = implode( ' | ', $service_name );
					}

					if ( in_array( 'payment_gateway', $fields ) ) {
						$row[] = $order->get_payment_gateway();
					}

					if ( in_array( 'payment_method', $fields ) ) {
						$row[] = $order->get_payment_method();
					}

					if ( in_array( 'date_created', $fields ) ) {
						$row[] = $order->get_date_created();
					}

					if ( in_array( 'status', $fields ) ) {
						$row[] = $order->get_order_status_translate();
					}

					if ( in_array( 'tax', $fields ) ) {
						$row[] = $order->get_tax_amount();
					}

					if ( in_array( 'system_fee', $fields ) ) {
						$row[] = $order->get_system_fee();
					}

					$row = apply_filters( 'obp_manager_order_export_row', $row, $fields, $order );

					// Total
					if ( in_array( 'total', $fields ) ) {
						$row[] = $order->get_total();
					}

					$data_export[] = $row;
				}
			}

			return apply_filters( 'obp_manager_order_data_export', $data_export, $fields );
		}

		public function get_order_ids_by_vendor( $vendor_id ){

			$args = array(
				'post_type' 		=> 'obp_order',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'meta_key' 			=> OBP_METABOX.'vendor_id',
				'meta_value' 		=> $vendor_id,
				'fields' 			=> 'ids',
			);

			$order_ids = get_posts( $args );
			
			return $order_ids;
		}

		public static function update_order_woocommerce( $order_woo ){

			$order_id = OBP()->session->get('order_id');

			$customer_name 	= $order_woo->get_billing_first_name().' '.$order_woo->get_billing_last_name();
			$customer_email = $order_woo->get_billing_email();
			$customer_phone = $order_woo->get_billing_phone();
			$customer_note 	= $order_woo->get_customer_note();
			$woo_order_id 	= $order_woo->get_id();
			$payment_method = $order_woo->get_payment_method_title();
			$payment_gateway = esc_html__( 'Woocommerce', 'ovabookpro' );

			$meta_input = array(
				OBP_METABOX.'vendor_id' 		=> OBP()->cart->get_vendor_id(),
				OBP_METABOX.'order_status' 		=> 'obp_pending',
				OBP_METABOX.'customer_name' 	=> $customer_name,
				OBP_METABOX.'customer_email' 	=> $customer_email,
				OBP_METABOX.'customer_phone' 	=> $customer_phone,
				OBP_METABOX.'customer_note' 	=> $customer_note,
				OBP_METABOX.'woo_order_id' 		=> $woo_order_id,
				OBP_METABOX.'payment_method' 	=> $payment_method,
				OBP_METABOX.'payment_gateway' 	=> $payment_gateway,
			);

			if ( $order_id ) {
				// Update order id
				$order_woo->update_meta_data( OBP_METABOX.'order_id', $order_id );
				// Set order total
				$order_woo->set_total( OBP()->cart->get_total() );
				$order_woo->save();
				
				// Update Order
				wp_update_post( array(
					'ID' 			=> $order_id,
					'post_title' 	=> '#'.$order_id.' '.$customer_name,
				) );

				// Update Meta Value
				foreach ( $meta_input as $key => $value ) {
					update_post_meta( $order_id, $key, $value );
				}

				// Remove order id

				OBP()->cart->session->set('order_id', null);
				OBP()->cart->session->save_data();
			}
			
		}

		public static function create_order(){
			$coupon 		= OBP()->cart->session->get('coupon', array() );
			$coupon_id 		= isset( $coupon['coupon_id'] ) ? $coupon['coupon_id'] : '';
			$coupon_code 	= isset( $coupon['coupon_code'] ) ? $coupon['coupon_code'] : '';
			$coupon_amount 	= isset( $coupon['coupon_amount'] ) ? $coupon['coupon_amount'] : '';
			$discount 		= OBP()->cart->get_discount();
			$has_coupon 	= OBP()->cart->session->get('has_coupon');
			$system_fee 	= OBP()->cart->get_system_fee();
			$tax_fee 		= OBP()->cart->get_tax_amount();
			$subtotal 		= OBP()->cart->get_subtotal();
			$total 			= apply_filters( 'obp_create_order_total' , OBP()->cart->get_total() );
			$date_created 	= current_time( 'timestamp' );
			$commission 	= OBP()->cart->get_commission();
			$vendor_total 	= OBP()->cart->get_vendor_total();
			$has_varies 	= OBP()->cart->has_varies() ? 'yes' : 'no';
			$change_order 	= OBP()->settings->change_order->get('change_order_enable', 'yes');

			$meta_input = array(
				OBP_METABOX.'vendor_id' 			=> OBP()->cart->get_vendor_id(),
				OBP_METABOX.'business_id' 			=> OBP()->cart->get_business_id(),
				OBP_METABOX.'order_status' 			=> 'obp_pending',
				OBP_METABOX.'customer_id' 			=> get_current_user_id(),
				OBP_METABOX.'customer_name' 		=> '',
				OBP_METABOX.'customer_email' 		=> '',
				OBP_METABOX.'customer_phone' 		=> '',
				OBP_METABOX.'customer_note' 		=> '',
				OBP_METABOX.'woo_order_id' 			=> '',
				OBP_METABOX.'payment_method' 		=> '',
				OBP_METABOX.'total' 				=> $total,
				OBP_METABOX.'has_varies' 			=> $has_varies,
				OBP_METABOX.'subtotal' 				=> $subtotal,
				OBP_METABOX.'system_fee' 			=> $system_fee,
				OBP_METABOX.'tax_amount' 			=> $tax_fee,
				OBP_METABOX.'discount' 				=> $discount,
				OBP_METABOX.'date_created' 			=> $date_created,
				OBP_METABOX.'commission' 			=> $commission,
				OBP_METABOX.'vendor_total' 			=> $vendor_total,
				OBP_METABOX.'start_date' 			=> OBP()->cart->get_start_date_earliest(),
				OBP_METABOX.'balance_status' 		=> 'obp_pending',
				OBP_METABOX.'balance_status_date' 	=> '',
				OBP_METABOX.'allow_change' 			=> $change_order,
			);

			// Coupon Meta Data
			if ( $has_coupon == 'yes' ){
				$meta_input[OBP_METABOX.'coupon_id'] 		= $coupon_id;
				$meta_input[OBP_METABOX.'coupon_code'] 		= $coupon_code;
				$meta_input[OBP_METABOX.'coupon_amount'] 	= $coupon_amount;
			}

			$postarr = array(
				'post_status' 	=> 'publish',
				'post_type' 	=> 'obp_order',
				'meta_input' 	=> $meta_input,
			);
			
			// Insert Order
			$order_id = wp_insert_post( $postarr, true );

			if ( ! is_wp_error( $order_id ) ) {

				$order = obp_get_order( $order_id );
				// Set key
				$order->set_key();

				$cart_content = OBP()->cart->content;
				$flag_allow_change = $order->get_coupon_id() ? false : true;

				foreach ( $cart_content as $item ) {
					$data = array(
						'vendor_id' 	=> $item->get_vendor_id(),
						'order_id' 		=> $order_id,
						'service_id' 	=> $item->get_service_id(),
						'staff_id' 		=> $item->get_staff_id(),
						'start_date' 	=> $item->get_start_date(),
						'end_date' 		=> $item->get_end_date(),
					);
					// Add order holding
					OBP_Order_Holding::add( $data );

					// Add Order Meta
					$data['price'] 			= obp_calculate_inc_tax( $item->get_price(), $item->get_rates() );
					$data['customer_id'] 	= $order->get_customer_id();
					$data['duration'] 		= $item->get_duration();
					$data['plan_id'] 		= $item->get_plan_id();
					$data['business_id'] 	= $item->get_business_id();
					$data['package_ids'] 	= maybe_serialize( $item->get_package_ids() );
					$data['taxes'] 			= maybe_serialize( $item->get_data_taxes() );
					
					// Add order meta
					OBP_Order_Meta::add( $data );

					// Check allow change order
					$service = obp_get_service( $item->get_service_id() );
					$regular_price = $service->get_price();

					if ( (float)$regular_price - (float)$item->get_price() > 0 ) {
						$flag_allow_change = false;
					}
				}
				// Allow change order
				if ( $flag_allow_change === false ) {
					update_post_meta( $order_id, OBP_METABOX.'allow_change', 'no' );
				}

				// Update Order
				wp_update_post( array(
					'ID' 			=> $order_id,
					'post_title' 	=> '#'.$order_id,
				) );

				$checkout_time = absint( OBP()->settings->payment->get( 'max_time_complete_checkout', '10' ) );

				OBP()->cart->session->set('order_id', $order_id );
				OBP()->cart->session->set('order_countdown', time() + ( $checkout_time*60 ) );
				OBP()->cart->session->save_data();
				
				do_action( 'obp_order_created', $order_id );

			}

			return $order_id;
		}

		public static function obp_order_status_completed_processing( $order_id ){

			$order_items 		= OBP_Order_Meta::get_order_items( $order_id );
			$order 				= obp_get_order( $order_id );
			$order_total 		= $order->get_total();
			$customer_id 		= $order->get_customer_id();
			$check_item_exits 	= OBP_Order_Meta_Queue::get_order_meta_by_order_id( $order_id );
			$check_send_mail 	= OBP()->settings->mail->get('new_order_send_mail','yes');

			if ( count( $check_item_exits ) > 0 ) {
				return;
			}

			// Send mail
			if ( $check_send_mail === "yes" ){
				OBP_Mail::obp_new_order_mail( $order );
			}

			$count_items = count( $order_items );

			if ( $count_items > 0 ) {
	
				$data_balance = apply_filters( 'obp_data_balance', array(
					'vendor_id' 			=> $order->get_vendor_id(),
					'order_id' 				=> $order->get_id(),
					'vendor_total' 			=> $order->get_vendor_total(),
					'remaining_phased' 		=> $order->get_vendor_total(),
					'start_date' 			=> $order->get_service_start_date_earliest(),
					'remaining_service' 	=> $count_items,
					'balance_status' 		=> 'obp_pending',
				), $order );
				// Add Order Balance
				$order_balance_id = OBP_Order_Balance::add( $data_balance );
				
				foreach ( $order_items as $_order ) {
					$data = array(
						'vendor_id' 		=> $_order->vendor_id,
						'order_id' 			=> $_order->order_id,
						'service_id' 		=> $_order->service_id,
						'staff_id' 			=> $_order->staff_id,
						'customer_id' 		=> $customer_id,
						'start_date' 		=> $_order->start_date,
						'end_date' 			=> $_order->end_date,
						'price'				=> $_order->price,
						'order_balance_id' 	=> $order_balance_id,
					);
					// Add Order Meta Queue
					OBP_Order_Meta_Queue::add( $data );
				}
				
			}
			// Delete Order Holding
			OBP_Order_Holding::delete_order_holding( $order_id );

			obp_clear_session();
		}

		public static function get_order_ids_by_woo_order_id( $woo_order_id ){
			$args = array(
				'post_type' 		=> 'obp_order',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'meta_key' 			=> OBP_METABOX.'woo_order_id',
				'meta_value' 		=> $woo_order_id,
				'fields' 			=> 'ids',
			);

			$order_ids = get_posts( $args );

			return $order_ids;
		}

		public function get_manager_orders_ajax(){

			$default_posts_per_page = apply_filters( 'obp_manager_order_per_page', get_option( 'posts_per_page' ) );
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			$order 			= isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : 'DESC';
			$orderby 		= isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : 'ID';
			$order_status 	= isset( $_POST['order_status'] ) ? sanitize_text_field( wp_unslash( $_POST['order_status'] ) ) : 'All';
			$page 			= isset( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : 1;

			$offset = ( absint( $page ) - 1 ) * absint( $default_posts_per_page );

			$date_filter = isset( $_POST['date_filter'] ) ? sanitize_text_field( wp_unslash( $_POST['date_filter'] ) ) : 'service_date';

			$from_date 		= isset( $_POST['from_date'] ) ? sanitize_text_field( wp_unslash( $_POST['from_date'] ) ) : '';
			$to_date 		= isset( $_POST['to_date'] ) ? sanitize_text_field( wp_unslash( $_POST['to_date'] ) ) : '';
			$customer_name 	= isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '';
			$user_id 		= obp_get_vendor_id();
			$date_key = $date_filter == 'service_date' ? OBP_METABOX.'start_date' : OBP_METABOX.'date_created';
			// phpcs:enable WordPress.Security.NonceVerification.Missing
			$args = array(
				'post_type' 		=> 'obp_order',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> $default_posts_per_page,
				'order' 			=> $order,
				'orderby' 			=> $orderby,
			);

			switch ( $order ) {
				case 'name':
					$args['orderby'] = 'meta_value';
					$args['meta_key'] = OBP_METABOX.'customer_name';
					break;
				
				default:
					break;
			}

			$meta_query = array();

			if ( ! OBP_Permission::is_administrator() ) {
				$meta_query[] = array(
					'key' 		=> OBP_METABOX.'vendor_id',
					'value' 	=> array( $user_id ),
					'compare' 	=> 'IN'
				);
			}

			if ( ! empty( $customer_name ) ) {
				$meta_query[] = array(
					'key' 		=> OBP_METABOX.'customer_name',
					'value' 	=> $customer_name,
					'compare' 	=> 'LIKE',
				);
			}

			if ( ! empty( $from_date ) ) {
				$meta_query[] = array(
					'key' 		=> $date_key,
					'value' 	=> strtotime( $from_date ),
					'compare' 	=> '>=',
					'type' 		=> 'NUMERIC',
				);
			}

			if ( ! empty( $to_date ) ) {
				$to_date = absint( strtotime( $to_date ) ) + (3600*24) - 1;

				$meta_query[] = array(
					'key' 		=> $date_key,
					'value' 	=> $to_date,
					'compare' 	=> '<=',
					'type' 		=> 'NUMERIC',
				);
			}

			if ( $order_status != 'All' ) {
				$meta_query[] = array(
					'key' 	=> OBP_METABOX.'order_status',
					'value' => $order_status,
				);
			}

			if ( $offset > 0 ) {
				$args['offset'] = absint( $offset );
			}

			if ( ! empty( $meta_query ) ) {
				$args['meta_query'] = $meta_query;
			}

			$orders = new WP_Query( $args );

			return $orders;
		}

		public function get_orders_ajax(){
			$default_posts_per_page = get_option( 'posts_per_page' );
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			$order 			= isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : 'DESC';
			$orderby 		= isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : 'ID';
			$order_status 	= isset( $_POST['order_status'] ) ? sanitize_text_field( wp_unslash( $_POST['order_status'] ) ) : 'All';
			$page 			= isset( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : 1;

			$offset = ( absint( $page ) - 1 ) * absint( $default_posts_per_page );

			$from_date 	= isset( $_POST['from_date'] ) ? sanitize_text_field( wp_unslash( $_POST['from_date'] ) ) : '';
			$to_date 	= isset( $_POST['to_date'] ) ? sanitize_text_field( wp_unslash( $_POST['to_date'] ) ) : '';
			// phpcs:enable WordPress.Security.NonceVerification.Missing
			$user_id 		= get_current_user_id();
			$customer_id 	= array( $user_id );

			if ( OBP_Permission::is_administrator() ) {
				$customer_id[] = 0;
			}

			$args = array(
				'post_type' 		=> 'obp_order',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> $default_posts_per_page,
				'order' 			=> $order,
				'orderby' 			=> $orderby,
			);

			switch ( $order ) {
				case 'name':
					$args['orderby'] = 'meta_value';
					$args['meta_key'] = OBP_METABOX.'customer_name';
					break;
				
				default:
					break;
			}

			$meta_query = array(
				array(
					'key' 		=> OBP_METABOX.'customer_id',
					'value' 	=> $customer_id,
					'compare' 	=> 'IN'
				),
			);


			if ( ! empty( $from_date ) ) {
				$meta_query[] = array(
					'key' 		=> OBP_METABOX.'start_date',
					'value' 	=> strtotime( $from_date ),
					'compare' 	=> '>=',
					'type' 		=> 'NUMERIC',
				);
			}

			if ( ! empty( $to_date ) ) {
				$meta_query[] = array(
					'key' 		=> OBP_METABOX.'start_date',
					'value' 	=> strtotime( $to_date ),
					'compare' 	=> '<=',
					'type' 		=> 'NUMERIC',
				);
			}

			if ( $order_status != 'All' ) {
				$meta_query[] = array(
					'key' 	=> OBP_METABOX.'order_status',
					'value' => $order_status,
				);
			}

			if ( $offset > 0 ) {
				$args['offset'] = absint( $offset );
			}

			if ( ! empty( $meta_query ) ) {
				$args['meta_query'] = $meta_query;
			}

			$orders = new WP_Query( $args );

			return $orders;
		}


		public static function get_order_ids_by_customer_name( $customer_name ){
			$args = array(
				'post_type' 		=> 'obp_order',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'meta_query' 		=> array(
					array(
						'key' => OBP_METABOX.'order_status',
						'value' => 'obp_processing',
					),
					array(
						'key' => OBP_METABOX.'customer_name',
						'value' => $customer_name,
					),
				),
				'fields' => 'ids',
			);

			$order_ids = get_posts( $args );
			return $order_ids;

		}

		public static function obp_change_order_form_calendar_args( $args = array() ){
			
			$current_timestamp 	= current_time('timestamp');
			$current_date 		= gmdate("Y-m-d",$current_timestamp);

			$target_date = isset( $args['target_date'] ) ? $args['target_date'] : '';
			$target_time = isset( $args['target_time'] ) ? $args['target_time'] : '';

			list($year, $month,$day) = explode( "-", $target_date );

			$days_in_month 	= gmdate( "t", $target_time );
			$days_range 	= range( 1, $days_in_month );
			$days_arr 		= array();

			foreach ( $days_range as $key => $number ) {
				$d = $number < 10 ? "0".$number : $number;
				$date_ymd = "$year-$month-$d";
				if ( strtotime( $date_ymd ) >= strtotime( $current_date ) ) {
					$days_arr[] = $date_ymd;
				}
			}

			while ( ! in_array( $target_date, $days_arr ) ) {

				list($year, $month,$day) = explode( "-", $target_date );

				$days_in_month = gmdate("t", strtotime( $target_date ) );

				$days_range = range( 1, $days_in_month );

				$days_arr = array();

				foreach ( $days_range as $key => $number ) {
					$d = $number < 10 ? "0".$number : $number;
					$date_ymd = "$year-$month-$d";

					if ( strtotime( $date_ymd ) >= strtotime( $current_date ) ) {
						$days_arr[] = $date_ymd;
					}

				}
			}

			if ( count( $days_arr ) == 1 ) {
				$next_day = absint( strtotime( $days_arr[0] ) ) + 86400;
				$days_next_month = absint( gmdate("t", $next_day ) );
				for ($number=1; $number <= $days_next_month; $number++) { 
					$d = $number < 10 ? "0".$number : $number;
					$date_ymd = "$year-$month-$d";
					$days_arr[] = $date_ymd;
				}
			}

			$data_prev = $days_arr[0] == $current_date ? 'false' : 'true';

			$count_days_arr = count( $days_arr );
			$key_last_day 	= $count_days_arr - 1;
			$data_end_date 	= isset( $days_arr[$key_last_day] ) ? $days_arr[$key_last_day] : '';

			$args = array_merge( $args, array(
				'days_arr' 		=> $days_arr,
				'target_date' 	=> $target_date,
				'data_prev' 	=> $data_prev,
				'data_end_date' => $data_end_date,
			) );

			return apply_filters( 'obp_change_order_form_calendar_args', $args );
		}

		public static function obp_change_order_time_slider_args( $args = array() ){
			$business_id = isset( $args['business_id'] ) ? $args['business_id'] : '';
			$target_date = isset( $args['target_date'] ) ? $args['target_date'] : '';

			// Time Format
			$cart 			= new OBP_Cart_Order();
			$time_format 	= OBP()->settings->general->get('time_format','H:i');
			$last_cart_item = $cart->get_last_item();
			$duration 		= $last_cart_item->get_duration();
			$business 		= obp_get_business( $business_id );
			$work_hours 	= $business->get_work_hours();
			$date_timestamp = strtotime( $target_date );

			$args = array_merge( $args, array(
				'work_hours' 		=> $work_hours,
				'date_timestamp' 	=> $date_timestamp,
				'time_format' 		=> $time_format,
			) );

			return apply_filters( 'obp_change_order_time_slider_args', $args );
		}

		public static function obp_change_order_popup_staff_args( $args = array() ){
			$service_id 		= isset( $args['service_id'] ) ? $args['service_id'] : '';
			$cart 				= new OBP_Cart_Order();
			$service 			= obp_get_service( $service_id );
			$staff_ids 			= $service->get_staff_ids();
			$cart_item 			= $cart->get_cart_item( $service_id );
			$current_staff_id 	= $cart_item->get_staff_id();

			$args = array_merge( $args, array(
				'current_staff_id' 	=> $current_staff_id,
				'staff_ids' 		=> $staff_ids,
			) );

			return apply_filters( 'obp_change_order_popup_staff_args', $args );
		}

		public static function obp_change_order_form_order_item_args( $args = array() ){
			$cart 			= new OBP_Cart_Order();
			$time_format 	= OBP()->settings->general->get('time_format','H:i');
			$cart_content 	= $cart->content;

			$args = array_merge( $args, array(
				'cart_content' 	=> $cart_content,
				'time_format' 	=> $time_format,
			) );

			return apply_filters( 'obp_change_order_form_order_item_args', $args );
		}

		public static function obp_change_order_form_footer_args( $args = array() ){
			$order_id 		= isset( $args['order_id'] ) ? $args['order_id'] : '';
			$cart 			= new OBP_Cart_Order();
			$order 			= obp_get_order( $order_id );
			$total 			= $order->get_total();
			$discount 		= $order->get_discount();
			$coupon_code 	= $order->get_coupon_code();
			$subtotal  		= $order->get_subtotal();
			$total_time 	= $cart->get_total_time();
			$tax_fee 		= $order->get_tax_amount();
			$system_fee 	= $order->get_system_fee();
			$has_varies 	= $order->has_varies();

			$args = array_merge( $args, array(
				'subtotal' 		=> $subtotal,
				'total' 		=> $total,
				'discount' 		=> $discount,
				'coupon_code' 	=> $coupon_code,
				'total_time' 	=> $total_time,
				'tax_fee' 		=> $tax_fee,
				'system_fee'  	=> $system_fee,
				'has_varies' 	=> $has_varies,
			) );

			return apply_filters( 'obp_change_order_form_footer_args', $args );
		}

		public static function get_order_ids_by_coupon_id( $coupon_id, $vendor_id ){
			$args = array(
				'post_type' 		=> 'obp_order',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'fields' 			=> 'ids',
			);

			$meta_query = array(
				array(
					'key' 		=> OBP_METABOX.'order_status',
					'value' 	=> array( 'obp_completed', 'obp_processing' ),
					'compare' 	=> 'IN',
				),
				array(
					'key' 	=> OBP_METABOX.'vendor_id',
					'value' => $vendor_id
				),
				array(
					'key' 	=> OBP_METABOX.'coupon_id',
					'value' => $coupon_id
				),
			);

			$args['meta_query'] = $meta_query;

			$the_query = get_posts( $args );
			return $the_query;
		}

		public static function get_order_by_id( $order_id ){
			$args = array(
				'post_type' 		=> 'obp_order',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> 1,
				'post__in' 			=> array( $order_id ),
				'fields' 			=> 'ids',
			);

			$orders = get_posts( $args );

			if ( count( $orders ) > 0 ) {
			 	return obp_get_order( $orders[0] );
			} else {
				return false;
			}
		}

		public static function get_ids_order_processing_beetween( $start_date, $end_date ){
			$args = array(
				'post_type' 		=> 'obp_order',
				'post_status' 		=> array( 'publish' ),
				'posts_per_page' 	=> -1,
				'fields' 			=> 'ids',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => OBP_METABOX.'start_date',
						'value' => array(
							$start_date,
							$end_date
						),
						'compare' => 'BETWEEN',
						'type' => 'NUMERIC'
					),
					array(
						'key' 	=> OBP_METABOX.'order_status',
						'value' => 'obp_processing',
					)
				),
			);

			$order_ids = get_posts( $args );
			return $order_ids;
		}
	}
}