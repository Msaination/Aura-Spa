<?php
namespace BookPro\Admin\MetaBoxes;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Metabox_Order_Info") ) {
	

	class OBP_Metabox_Order_Info {

		public static function output( $post ){
			OBP()->include('Admin/MetaBoxes/views/html-order-information.php');
		}

		public static function save( $post_id ){
			
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			if ( get_post_type( $post_id ) == 'obp_order' ) {
				
				$new_status = isset( $_POST[OBP_METABOX.'order_status'] ) ? sanitize_text_field( wp_unslash( $_POST[OBP_METABOX.'order_status'] ) ) : 'obp_pending'; // phpcs:ignore WordPress.Security.NonceVerification.Missing

				$order 		= obp_get_order( $post_id );
				$old_status = $order->get_order_status();

				update_post_meta( $post_id, OBP_METABOX.'order_status', $new_status );
				
				do_action( 'obp_order_status_'.$old_status.'_to_'.$new_status, $post_id );

			}
		}
	}
}