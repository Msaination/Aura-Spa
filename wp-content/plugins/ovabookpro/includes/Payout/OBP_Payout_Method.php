<?php

namespace BookPro\Payout;

use BookPro\Payout\OBP_Payout_Method_Item;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Payout_Method") ) {
	

	class OBP_Payout_Method {

		public static function get_all(){
			$args = array(
				'post_type' 		=> 'obp_payout_method',
				'posts_per_page' 	=> -1,
				'post_status' 		=> 'publish',
				'fields' 			=> 'ids',
				'order' 			=> 'ASC',
				'orderby' 			=> 'ID',
			);

			$payout_method_ids = get_posts( $args );

			$result = array();

			if ( count( $payout_method_ids ) > 0 ) {
				foreach ( $payout_method_ids as $id ) {
					$result[] = obp_get_payout_method( $id );
				}
			}

			return $result;
		}
	}
}