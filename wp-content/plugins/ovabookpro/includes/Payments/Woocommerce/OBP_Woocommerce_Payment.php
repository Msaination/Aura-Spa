<?php
namespace BookPro\Payments\Woocommerce;

use BookPro\Abstracts\OBP_Payment_Gateway;


defined( 'ABSPATH' ) || exit;


if ( ! class_exists("OBP_Woocommerce_Payment") ) {
	
	class OBP_Woocommerce_Payment extends OBP_Payment_Gateway {

		public function __construct(){

			$this->id 		= 'woocommerce';
			$this->title 	= esc_html__( 'Woocommerce', 'ovabookpro' );
			$enabled 		= OBP()->settings->payment->get('woo_enable', '');

			if ( ! $enabled ) {
				$this->enabled = 'no';
			}
		}

		public function process_payment( $order_id ){
			$response = [];
			if ( class_exists('Woocommerce') ) {
				WC()->cart->empty_cart();
				$cart_content = OBP()->cart->content;

				if ( count( $cart_content ) > 0 ) {
					foreach ( $cart_content as $key => $item ) {
						WC()->cart->add_to_cart( $item->get_service_id() );
					}
				}

				WC()->cart->set_subtotal( OBP()->cart->get_subtotal() );
				$response['status'] = 'success';
				$response['url'] 	= $this->get_checkout_url();
			} else {
				$response['message'] = esc_html__( 'Payment gateway not found', 'ovabookpro' );
			}
			
			return $response;
		}

		public function get_checkout_url(){
			return wc_get_checkout_url();
		}
	}
}