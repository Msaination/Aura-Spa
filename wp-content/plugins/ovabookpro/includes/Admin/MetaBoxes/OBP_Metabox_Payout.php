<?php
namespace BookPro\Admin\MetaBoxes;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Metabox_Payout") ) {


	class OBP_Metabox_Payout {

		public static function output( $post ){
			OBP()->include('Admin/MetaBoxes/views/html-payout-info.php');
		}

		public static function save( $post_id ){

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			if ( get_post_type( $post_id ) == 'obp_payout' ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$new_status = isset( $_POST[OBP_METABOX.'payout_status'] ) ? sanitize_text_field( wp_unslash( $_POST[OBP_METABOX.'payout_status'] ) ) : 'obp_pending';

				$payout 	= obp_get_payout( $post_id );
				$old_status = $payout->get_payout_status();

				update_post_meta( $post_id, OBP_METABOX.'payout_status', $new_status );

				do_action( 'obp_payout_status_'.$old_status.'_to_'.$new_status, $post_id );

			}
		}
	}
}