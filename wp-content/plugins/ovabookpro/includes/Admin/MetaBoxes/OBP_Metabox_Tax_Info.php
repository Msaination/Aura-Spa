<?php
namespace BookPro\Admin\MetaBoxes;

defined( 'ABSPATH' ) || exit;


class OBP_Metabox_Tax_Info {

	public static function output( $post ){
		OBP()->include('Admin/MetaBoxes/views/html-tax-information.php');
	}

	public static function save( $post_id ){
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( get_post_type( $post_id ) == 'obp_tax' ) {
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			$country_code 	= isset( $_POST['country_code'] ) ? sanitize_text_field( wp_unslash( $_POST['country_code'] ) ) : '';
			$state_code 	= isset( $_POST['state_code'] ) ? sanitize_text_field( wp_unslash( $_POST['state_code'] ) ) : '';
			$postcode 		= isset( $_POST['postcode_zip'] ) ? sanitize_text_field( wp_unslash( $_POST['postcode_zip'] ) ) : '';
			$city 			= isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
			$rate 			= isset( $_POST['rate'] ) ? sanitize_text_field( wp_unslash( $_POST['rate'] ) ) : '';
			$priority 		= isset( $_POST['priority'] ) ? sanitize_text_field( wp_unslash( $_POST['priority'] ) ) : '1';
			// phpcs:enable WordPress.Security.NonceVerification.Missing
			$rate 			= obp_format_price( $rate );
			if ( is_numeric( $rate ) && $rate > 100 ) {
				$rate = '';
			}

			update_post_meta( $post_id, OBP_METABOX.'country_code', $country_code );

			if ( $country_code == '' ) {
				$state_code = '';
				$postcode = '';
				$city = '';
			}

			update_post_meta( $post_id, OBP_METABOX.'state_code', $state_code );
			update_post_meta( $post_id, OBP_METABOX.'city', $city );
			update_post_meta( $post_id, OBP_METABOX.'postcode', $postcode );
			update_post_meta( $post_id, OBP_METABOX.'rate', $rate );
			update_post_meta( $post_id, OBP_METABOX.'priority', $priority );
		}
	}
}