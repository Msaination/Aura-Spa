<?php

namespace BookPro\Order;

use BookPro\Traits\SingletonTrait;
use BookPro\Order\OBP_Order_Meta_Queue;
use BookPro\Order\OBP_Order_Holding;
use BookPro\Order\OBP_Order_Balance;
use BookPro\OBP_Mail;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Order_Hooks") ) {

	class OBP_Order_Hooks {

		use SingletonTrait;

		public function __construct(){
			if ( is_admin() ) {
				add_action( 'before_delete_post', array( $this, 'obp_delete_order' ), 10, 1 );
			}
			add_action( 'obp_after_checkout_content', array( $this, 'obp_order_countdown' ) );
		}

		public static function obp_order_countdown(){

			if ( OBP()->session->get('order_id') ) {
				$remaining_time = absint( OBP()->session->get('order_countdown') ) - time();
				?>
				<div id="obp_order_countdown" data-nonce="<?php echo esc_attr( wp_create_nonce( 'obp_booking_countdown' ) ); ?>" data-time="<?php echo esc_attr( absint( $remaining_time ) ); ?>"></div>
				<?php
			}
		}

		public static function obp_order_countdown_block( $block_content, $block ){
			if ( $block['blockName'] == 'woocommerce/checkout' && OBP()->session->get('order_id') ) {
			
				$remaining_time = absint( OBP()->session->get('order_countdown') ) - time();
				ob_start();
				?>
				<div id="obp_order_countdown" data-nonce="<?php echo esc_attr( wp_create_nonce( 'obp_booking_countdown' ) ); ?>" data-time="<?php echo esc_attr( absint( $remaining_time ) ); ?>"></div>
				<?php
				$block_content .= ob_get_clean();
			}

			return $block_content;
		}

		public function obp_delete_order( $post_id ){

			if ( get_post_type( $post_id ) == 'obp_order' ) {
				// Delete Order Meta
				OBP_Order_Meta::delete_by_order_id( $post_id );
				// Delete Order Holding
				OBP_Order_Holding::delete_order_holding( $post_id );
				// Delete Order Meta Queue
				OBP_Order_Meta_Queue::delete_by_order_id( $post_id );
				// Delete Order Balance
				OBP_Order_Balance::delete_by_order_id( $post_id );
			}
		}
	}

}