<?php

namespace BookPro\Payout;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists("OBP_Payout_Item") ) {
	

	class OBP_Payout_Item {

		protected $id = null;

		public function __construct( $id ){
			$this->id = $id;
		}

		public function get_id(){
			$id = $this->id;
			return $id;
		}

		public function get_user_id(){
			$id = $this->get_id();
			$user_id = get_post_meta( $id, OBP_METABOX.'user_id', true );
			return $user_id;
		}

		public function get_amount(){
			$id = $this->get_id();
			$amount = get_post_meta( $id, OBP_METABOX.'amount', true );
			return $amount;
		}

		public function get_withdraw_date_timestamp(){
			$id = $this->get_id();
			$withdraw_date_timestamp = get_post_meta( $id, OBP_METABOX.'withdraw_date', true );
			return $withdraw_date_timestamp;
		}

		public function get_withdraw_date(){
			$date_format = obp_get_date_format();
			$time_format = obp_get_time_format();
			$withdraw_date_timestamp 	= $this->get_withdraw_date_timestamp();
			$withdraw_date 				= date_i18n( $date_format.' '.$time_format, $withdraw_date_timestamp );

			return $withdraw_date;
		}

		public function get_payout_status(){
			$id = $this->get_id();
			$payout_status = get_post_meta( $id, OBP_METABOX.'payout_status', true );
			return $payout_status;
		}

		public function get_payout_status_translate(){
			$payout_status 		= $this->get_payout_status();
			$translate_status 	= array(
				'obp_pending' 		=> esc_html__( 'Pending', 'ovabookpro' ),
				'obp_completed' 	=> esc_html__( 'Completed', 'ovabookpro' ),
				'obp_cancelled' 	=> esc_html__( 'Cancelled', 'ovabookpro' ),
			);
			return $translate_status[$payout_status];
		}

		public function get_holding_amount(){
			$id = $this->get_id();
			$holding_amount = get_post_meta( $id, OBP_METABOX.'holding_amount', true );
			return $holding_amount;
		}

		public function get_payout_date_timestamp(){
			$id = $this->get_id();
			$payout_date = get_post_meta( $id, OBP_METABOX.'payout_date', true );
			return $payout_date;
		}

		public function get_payout_date(){
			$date_format 	= obp_get_date_format();
			$time_format 	= obp_get_time_format();
			$date_timestamp = $this->get_payout_date_timestamp();
			$payout_date 	= '';
			if ( $date_timestamp ) {
				$payout_date = date_i18n( $date_format.' '.$time_format, $date_timestamp );
			}
			return $payout_date;
		}

		public function get_payout_method_id(){
			$id = $this->get_id();
			$payout_method_id = get_post_meta( $id, OBP_METABOX.'payout_method_id', true );
			return $payout_method_id;
		}

		public function get_payout_method(){
			$payout_method_id = $this->get_payout_method_id();
			$payout_method = get_the_title( $payout_method_id );
			return $payout_method;
		}

		public function get_payout_info(){
			$id = $this->get_id();
			$payout_info = get_post_meta( $id, OBP_METABOX.'payout_info', true );
			return $payout_info;
		}
	}
}