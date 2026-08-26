<?php
namespace BookPro\Abstracts;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists("OBP_Payment_Gateway") ) {

	abstract class OBP_Payment_Gateway {

		public $enabled = 'yes';

		public $title;

		public $id;
		
	 	abstract public function get_checkout_url();

	 	public function is_available() {
	 		return $this->enabled == 'yes' ? true : false;
		}

		public function process_payment( $order_id ) {
			return array();
		}

		public function get_id(){
			return $this->id;
		}

		public function get_title(){
			return $this->title;
		}
	}
}