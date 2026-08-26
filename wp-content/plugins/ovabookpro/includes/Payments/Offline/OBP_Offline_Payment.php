<?php
namespace BookPro\Payments\Offline;

use BookPro\Order\OBP_Order;
use BookPro\Traits\SingletonTrait;
use BookPro\Abstracts\OBP_Payment_Gateway;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Offline_Payment') ) {
	

	class OBP_Offline_Payment extends OBP_Payment_Gateway {

		public function __construct(){
			$this->id 		= 'offline';
			$this->title 	= esc_html__( 'Offline', 'ovabookpro' );
			$enabled 		= OBP()->settings->payment->get('offline_enable', '');

			if ( ! $enabled ) {
				$this->enabled = 'no';
			}
		}

		public function process_payment( $order_id ){
			$response = [];
			$order = obp_get_order( $order_id );
			
			update_post_meta( $order_id, OBP_METABOX.'order_status', 'obp_completed' );
			update_post_meta( $order_id, OBP_METABOX.'payment_method', __( 'Offline', 'ovabookpro' ) );

			OBP_Order::obp_order_status_completed_processing( $order_id );
			$response['status'] = 'success';
			$response['url'] 	= obp_get_thank_url_with_key( $order->get_key() );
			return $response;
		}

		public function get_checkout_url(){
			return '';
		}
	}
}
