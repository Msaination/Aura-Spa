<?php
namespace BookPro\Admin\MetaBoxes;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Metabox_Payout_Method") ) {
	
	class OBP_Metabox_Payout_Method {
		public static function output( $post ){
			OBP()->include('Admin/MetaBoxes/views/html-payout-method.php');
		}

		public static function save( $post_id ){

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			if ( get_post_type( $post_id ) == 'obp_payout_method' ) {
				// phpcs:disable WordPress.Security.NonceVerification.Missing
				$labels = isset( $_POST[OBP_METABOX.'label'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST[OBP_METABOX.'label'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$keys = isset( $_POST[OBP_METABOX.'key'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST[OBP_METABOX.'key'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$placeholders = isset( $_POST[OBP_METABOX.'placeholder'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST[OBP_METABOX.'placeholder'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$requireds = isset( $_POST[OBP_METABOX.'required'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST[OBP_METABOX.'required'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				// phpcs:enable WordPress.Security.NonceVerification.Missing
				if ( ! empty( $keys ) ) {
					$keys = array_map(function($value){
						$value = sanitize_key( $value );
						return $value;
					}, $keys);
				}

				update_post_meta( $post_id, OBP_METABOX.'label', $labels );
				update_post_meta( $post_id, OBP_METABOX.'key', $keys );
				update_post_meta( $post_id, OBP_METABOX.'placeholder', $placeholders );
				update_post_meta( $post_id, OBP_METABOX.'required', $requireds );

			}

		}
	}
}