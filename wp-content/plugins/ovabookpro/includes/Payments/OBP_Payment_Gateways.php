<?php
namespace BookPro\Payments;

use BookPro\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists("OBP_Payment_Gateways") ) {
	

	class OBP_Payment_Gateways {

		use SingletonTrait;

		public $payment_gateways = array();

		public function __construct(){
			$this->init();
		}

		public function init(){
			$load_gateways = array(
				'BookPro\Payments\Offline\OBP_Offline_Payment',
				'BookPro\Payments\Woocommerce\OBP_Woocommerce_Payment',
			);

			$load_gateways = apply_filters( 'obp_payment_gateways' , $load_gateways );

			foreach ( $load_gateways as $gateway_id => $gateway ) {
				if ( is_string( $gateway ) && class_exists( $gateway ) ) {
					$this->payment_gateways[$gateway_id] = new $gateway();
				}
			}
		}

		public function get_available_payment_gateways(){
			$_available = array();

			foreach ( $this->payment_gateways as $gateway ) {
				if ( $gateway->is_available() ) {
					$_available[$gateway->id] = $gateway;
				}
			}

			return $_available;
		}

	}
}