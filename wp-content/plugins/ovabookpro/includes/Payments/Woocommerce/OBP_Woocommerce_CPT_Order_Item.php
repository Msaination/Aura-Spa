<?php
namespace BookPro\Payments\Woocommerce;

use WC_Order_Item_Product;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists("OBP_Woocommerce_CPT_Order_Item") ) {


	class OBP_Woocommerce_CPT_Order_Item extends WC_Order_Item_Product {

		public function set_product_id( $value ) {
			$this->set_prop( 'product_id', absint( $value ) );
		}
		
	}
}