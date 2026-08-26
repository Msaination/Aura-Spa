<?php
namespace BookPro\Coupon;
defined( 'ABSPATH' ) || exit;

use BookPro\OBP_Permission;
use WP_Query;

class OBP_Coupon {

	public static function init(){
		add_action( 'obp_load_member_account_edit-coupon_scripts', array( __CLASS__, 'load_scripts' ) );
		add_action( 'obp_load_member_account_manage-coupon_scripts', array( __CLASS__, 'load_scripts' ) );
	}

	public static function load_scripts( $assets ){

		wp_enqueue_style( 'flatpickr' );
		wp_enqueue_script( 'flatpickr' );
		wp_enqueue_script( 'flatpickr-localize' );
		wp_enqueue_script( 'flatpickr-rangePlugin' );

		wp_enqueue_style( 'obp-timepicker' );
		wp_enqueue_script( 'obp-timepicker' );

		wp_enqueue_style( 'zebra-dialog');
		wp_enqueue_script('zebra-dialog');

		wp_enqueue_script('obp_manage_coupon', OBP_PLUGIN_URI.'assets/js/frontend/manage-coupon.js', array('jquery'), false, true );

		$currency_symbol 	= obp_get_currency_symbol();
		$currency_position 	= OBP()->settings->general->get('currency_position','left');
		$thousand_separator = OBP()->settings->general->get('thousand_separator',',');
		$decimal_separator 	= OBP()->settings->general->get('decimal_separator','.');
		$number_decimal 	= OBP()->settings->general->get('price_num_decimals','2');

		$currency_object = array(
			'currency_symbol' 		=> $currency_symbol,
			'currency_position' 	=> $currency_position,
			'thousand_separator' 	=> $thousand_separator,
			'decimal_separator' 	=> $decimal_separator,
			'number_decimal' 		=> $number_decimal,
		);

		wp_localize_script( 'obp_manage_coupon', 'currency_object', $currency_object );
		wp_localize_script( 'obp_manage_coupon', 'obp_coupon_obj', array(
			'code_req' 			=> esc_html__( 'Coupon code is required', 'ovabookpro' ),
			'amount_req' 		=> esc_html__( 'Coupon amount is required', 'ovabookpro' ),
			'quantity_req' 		=> esc_html__( 'Coupon quantity is required', 'ovabookpro' ),
			'confirm_delete' 	=> esc_html__( 'Are you sure you want to delete this record? This action cannot be undone', 'ovabookpro' ),
			'yes' 	=> esc_html__( 'Yes', 'ovabookpro' ),
			'no' 	=> esc_html__( 'No','ovabookpro' ),
		) );

	}

	public static function ajax_init(){
		$hooks = array(
			'obp_save_coupon',
			'obp_delete_coupon',
			'obp_load_data_coupon',
		);

		foreach ( $hooks as $hook ) {
			add_action( 'wp_ajax_'.$hook, array( __CLASS__, $hook ) );
			add_action( 'wp_ajax_nopriv_'.$hook, array( __CLASS__, $hook ) );
		}
	}

	public static function obp_save_coupon(){
		$response = array(
			'url' 		=> '',
			'redirect' 	=> '',
		);
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('manage_coupon') ) {

			$coupon_id 		= isset( $_POST['coupon_id'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_id'] ) ) : '';
			$coupon_code 	= isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';
			$visibility 	= isset( $_POST['visibility'] ) ? sanitize_text_field( wp_unslash( $_POST['visibility'] ) ) : 'public';
			$description 	= isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';
			$discount_type 	= isset( $_POST['discount_type'] ) ? sanitize_text_field( wp_unslash( $_POST['discount_type'] ) ) : 'percent';
			$coupon_amount 	= isset( $_POST['coupon_amount'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_amount'] ) ) : 0;
			$coupon_qty 	= isset( $_POST['coupon_qty'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_qty'] ) ) : 0;
			$order_from 	= isset( $_POST['order_from'] ) ? sanitize_text_field( wp_unslash( $_POST['order_from'] ) ) : 0;
			$apply_to 		= isset( $_POST['apply_to'] ) ? sanitize_text_field( wp_unslash( $_POST['apply_to'] ) ) : 'all_services';
			$apply_services = isset( $_POST['apply_services'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['apply_services'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$start_date 	= isset( $_POST['start_date'] ) ? strtotime( sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) ) : time();
			$end_date 		= isset( $_POST['end_date'] ) ? strtotime( sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) ) : time();
			$from_time 		= isset( $_POST['from_time'] ) ? sanitize_text_field( wp_unslash( $_POST['from_time'] ) ) : '';
			$to_time 		= isset( $_POST['to_time'] ) ? sanitize_text_field( wp_unslash( $_POST['to_time'] ) ) : '';
			$use_on 		= isset( $_POST['use_on'] ) ? sanitize_text_field( wp_unslash( $_POST['use_on'] ) ) : 'booking_date';

			$vendor_id 		= obp_get_vendor_id();
			$coupon_code 	= preg_replace('/\s+/', '', $coupon_code);
			$post_name 		= sanitize_title( $coupon_code );

			$meta_input = array(
				OBP_METABOX.'visibility' 		=> $visibility,
				OBP_METABOX.'discount_type' 	=> $discount_type,
				OBP_METABOX.'coupon_amount' 	=> $coupon_amount,
				OBP_METABOX.'coupon_code' 		=> $coupon_code,
				OBP_METABOX.'coupon_qty' 		=> $coupon_qty,
				OBP_METABOX.'order_from' 		=> $order_from,
				OBP_METABOX.'apply_to' 			=> $apply_to,
				OBP_METABOX.'apply_services' 	=> $apply_services,
				OBP_METABOX.'start_date' 		=> $start_date,
				OBP_METABOX.'end_date' 			=> $end_date,
				OBP_METABOX.'from_time' 		=> $from_time,
				OBP_METABOX.'to_time' 			=> $to_time,
				OBP_METABOX.'vendor_id' 		=> $vendor_id,
				OBP_METABOX.'use_on' 			=> $use_on,
			);

			$postarr = array(
				'post_type' 	=> 'obp_coupon',
				'post_status' 	=> 'publish',
				'post_content' 	=> $description,
				'post_title' 	=> $coupon_code,
				'post_name' 	=> $post_name,
				'meta_input' 	=> $meta_input,
			);

			if ( $coupon_id ) {
				$postarr['ID'] = $coupon_id;
				$check = wp_update_post( $postarr, true );

				if ( ! is_wp_error( $check ) ) {
					OBP()->message->add( esc_html__( 'Updated successfully', 'ovabookpro' ) );
				} else {
					OBP()->message->add( esc_html__( 'Update failed', 'ovabookpro' ) );
				}

			} else {

				$check = wp_insert_post( $postarr, true );

				if ( ! is_wp_error( $check ) ) {
					OBP()->message->add( esc_html__( 'Updated successfully', 'ovabookpro' ) );

					$member_account_url = obp_member_account_url();
					$endpoint = OBP()->endpoint->get_endpoint( 'edit-coupon' );
					$url = OBP()->endpoint->get_endpoint_url( $endpoint, $check, $member_account_url );
					$response['redirect'] = true;
					$response['url'] = $url;
				} else {
					OBP()->message->add( esc_html__( 'Update failed', 'ovabookpro' ) );
				}
			}

		}
		wp_send_json( $response );
		wp_die();
	}

	public static function obp_delete_coupon(){

		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('manage_coupon') ) {

			$coupon_id = isset( $_POST['coupon_id'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_id'] ) ) : '';
			$check = wp_delete_post( $coupon_id, true );

			if ( $check ) {
				OBP()->message->add( esc_html__( 'Deleted successfully.', 'ovabookpro' ) );
			} else {
				OBP()->message->add( esc_html__( 'Delete failed.', 'ovabookpro' ), 'error' );
			}
		}

		echo '';
		wp_die();
	}

	public static function obp_load_data_coupon(){
		$response = array(
			'coupon_html' 		=> '',
			'pagination_html' 	=> '',
		);

		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('manage_coupon') ) {


			$coupons = self::get_coupon_ajax();
			ob_start();
			if ( $coupons->have_posts() ) {
				while ( $coupons->have_posts() ) {
					$coupons->the_post();
					obp_get_template("manage-coupon/coupon-item.php");
				}
				wp_reset_postdata();
			} else {
				?>
				<tr>
					<td colspan="4">
						<?php esc_html_e( 'Coupons not found.', 'ovabookpro' ); ?>
					</td>
				</tr>
				<?php
			}

			$response['coupon_html'] = ob_get_clean();

			ob_start();
			obp_get_template("manage-coupon/coupon-pagination.php", array( 'coupons' => $coupons ) );
			$response['pagination_html'] = ob_get_clean();
		}

		wp_send_json( $response );
	}

	public static function get_coupon_ajax(){
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$page = isset( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : 1;

		$order = 'DESC';
		$orderby = 'ID';

		$vendor_id = obp_get_vendor_id();

		$posts_per_page = get_option( 'posts_per_page' );

		$args = array(
			'post_type' 		=> 'obp_coupon',
			'post_status' 		=> 'publish',
			'order' 			=> $order,
			'orderby' 			=> $orderby,
			'posts_per_page' 	=> $posts_per_page
		);

		$meta_query = array(
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				array(
					'key' 	=> OBP_METABOX.'vendor_id',
					'value' => $vendor_id
				),
			),
		);

		$offset = absint( absint( $page ) - 1 ) * absint( $posts_per_page );

		if ( $offset ) {
			$args['offset'] = $offset;
		}

		$args = array_merge_recursive( $args, $meta_query );

		$the_query = new WP_Query( $args );

		return $the_query;
	}

	public static function verify_coupon_code( $coupon_code , $vendor_id ){
		$coupon_id = '';

		if ( $coupon_code ) {
			$coupon_id = self::get_coupon_id_by_code( $coupon_code, $vendor_id );
			return $coupon_id;
		}

		return $coupon_id;
	}

	public static function get_coupon_id_by_code( $coupon_code, $vendor_id ){
		$args = array(
			'post_type' 		=> 'obp_coupon',
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> 1,
			'fields' 			=> 'ids',
		);

		$meta_query = array(
			array(
				'key' 	=> OBP_METABOX.'coupon_code',
				'value' => $coupon_code,
			),
			array(
				'key' 	=> OBP_METABOX.'vendor_id',
				'value' => $vendor_id,
			),
		);
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$args['meta_query'] = $meta_query;

		$the_query = get_posts( $args );
		if ( count( $the_query ) > 0 ) {
			return $the_query[0];
		}
		return '';
	}

	public static function get_coupon_active( $vendor_id ){
		$args = array(
			'post_type' 		=> 'obp_coupon',
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> 1,
			'fields' 			=> 'ids',
		);

		$meta_query = array(
			array(
				'key' 	=> OBP_METABOX.'vendor_id',
				'value' => $vendor_id,
			),
			array(
				'key' 	=> OBP_METABOX.'visibility',
				'value' => 'public',
			),
		);
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$args['meta_query'] = $meta_query;
		$the_query = get_posts( $args );
		$result = array();

		if ( count( $the_query ) > 0 ) {
			foreach ( $the_query as $coupon_id ) {
				$coupon = obp_get_coupon( $coupon_id );
				$coupons_used = \BookPro\Order\OBP_Order::get_order_ids_by_coupon_id( $coupon_id, $vendor_id );
				$remaining_qty = absint( $coupon->get_coupon_qty() ) - count( $coupons_used );
				if ( $coupon->is_active() && $remaining_qty > 0 ) {
					$result[] = $coupon;
				}
			}
		}

		return $result;
	}
}