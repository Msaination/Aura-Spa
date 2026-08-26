<?php

namespace BookPro\Payout;

use BookPro\OBP_Mail;
use WP_Query;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists("OBP_Payout") ) {
	

	class OBP_Payout {

		public static function create( $amount, $user ){
			$user_id 			= $user->get_id();
			$user_fullname 		= $user->get_fullname();
			$current_timestamp 	= current_time( 'timestamp' );
			$payout_method_id 	= $user->get_payout_method_id();
			$payout_info_row 	= OBP_Payout_Method_Info::get_row( $payout_method_id, $user_id );
			$payout_info 		= unserialize( $payout_info_row['payout_info'] );

			$meta_input = array(
				OBP_METABOX.'user_id' 			=> $user_id,
				OBP_METABOX.'amount' 			=> $amount,
				OBP_METABOX.'holding_amount' 	=> $amount,
				OBP_METABOX.'withdraw_date' 	=> $current_timestamp,
				OBP_METABOX.'payout_status' 	=> "obp_pending",
				OBP_METABOX.'payout_date' 		=> "",
				OBP_METABOX.'payout_method_id' 	=> $payout_method_id,
				OBP_METABOX.'payout_info' 		=> $payout_info,
			);

			$postarr = array(
				'post_title' 	=> '',
				'post_status' 	=> 'publish',
				'post_type' 	=> 'obp_payout',
				'post_author' 	=> $user_id,
				'meta_input' 	=> $meta_input,
			);

			$payout_id = wp_insert_post( $postarr, true );

			if ( ! is_wp_error( $payout_id ) ) {
				wp_update_post( array(
					'ID' 			=> $payout_id,
					'post_title' 	=> '#'.$payout_id.' '.$user_fullname,
				) );

				// Update balance amount
				$balance_amount 	= $user->get_balance_amount();
				$remaining_amount 	= (float)$balance_amount - (float)$amount;
				$user->set_balance_amount( $remaining_amount );

				// Send mail request
				$send_mail_request = OBP()->settings->mail->get('withdraw_request_send_mail','yes');

				if ( $send_mail_request === 'yes' ) {
					OBP_Mail::obp_withdraw_request_mail( $payout_id );
				}
			}
		}

		public static function get_payouts_ajax(){
			$default_posts_per_page = apply_filters( 'obp_transaction_history_per_page' , 3 );

			$user_id 	= get_current_user_id();
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$page 		= isset( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : 1;

			$offset = ( absint( $page ) - 1 ) * absint( $default_posts_per_page );
			$args = array(
				'post_type' 		=> 'obp_payout',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> $default_posts_per_page,
				'order' 			=> 'DESC',
				'orderby' 			=> 'ID',
				'meta_key' 			=> OBP_METABOX.'user_id',
				'meta_value' 		=> $user_id,
			);

			if ( $offset > 0 ) {
				$args['offset'] = $offset;
			}

			$payouts = new WP_Query( $args );

			return $payouts;
		}

		public static function get_payout_ids_by_status( $status = "obp_pending" ){
			$args = array(
				'post_type' 		=> 'obp_payout',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'order' 			=> 'DESC',
				'orderby' 			=> 'ID',
				'meta_query' 		=> array(
					array(
						'key'   => OBP_METABOX.'payout_status',
						'value' => $status,
					),
				),
				'fields' => 'ids',
			);

			$payout_ids = get_posts( $args );
			return $payout_ids;
		}
	}
}