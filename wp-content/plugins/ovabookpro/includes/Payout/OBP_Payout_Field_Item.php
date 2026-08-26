<?php

namespace BookPro\Payout;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Payout_Field_Item") ) {
	

	class OBP_Payout_Field_Item {

		protected $item = array();

		public function __construct( $item ){
			$this->item = $item;
		}

		public function get_item(){
			$item = $this->item;
			return $item;
		}

		public function get_label(){
			$item = $this->get_item();
			$label = isset( $item['label'] ) ? $item['label'] : '';
			return $label;
		}

		public function get_key(){
			$item = $this->get_item();
			$key = isset( $item['key'] ) ? $item['key'] : '';
			return $key;
		}

		public function get_placeholder(){
			$item = $this->get_item();
			$placeholder = isset( $item['placeholder'] ) ? $item['placeholder'] : '';
			return $placeholder;
		}

		public function get_required(){
			$item = $this->get_item();
			$required = isset( $item['required'] ) ? $item['required'] : '';
			return $required;
		}
	}
}