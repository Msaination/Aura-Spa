<?php

namespace BookPro\Wallet;

use BookPro\Traits\SingletonTrait;
use BookPro\Order\OBP_Order_Balance;
use BookPro\Payout\OBP_Payout;
use BookPro\Payout\OBP_Payout_Method_Info;
use BookPro\Payout\OBP_Payout_Method;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Wallet") ) {


	class OBP_Wallet {

		use SingletonTrait;

		public static function init(){
			add_action( 'obp_load_member_account_my-wallet_scripts', array( __CLASS__, 'load_scripts' ) );
		}

		public static function load_scripts(){

			wp_enqueue_style( 'zebra-dialog');
			wp_enqueue_script('zebra-dialog');
			
			wp_enqueue_script('manage-wallet', OBP_PLUGIN_URI."assets/js/frontend/manage-wallet.js", array('jquery'), false, true );
		}


		public static function obp_manage_wallet_cards_args( $args = array() ){
			$user_id 		= get_current_user_id();
			$user 			= obp_get_user( $user_id );
			$total_pending 	= OBP_Order_Balance::get_total_order_queue( $user_id );

			$args = array_merge( $args, array(
				'user' 			=> $user,
				'total_pending' => $total_pending,
			) );

			return apply_filters( 'obp_manage_wallet_cards_args' , $args );
		}

		public static function obp_transaction_history_args( $args = array() ){
			$user_id 			= get_current_user_id();
			$payouts 			= OBP_Payout::get_payouts_ajax();

			$args = array_merge( $args, array(
				'payouts' => $payouts,
			) );

			return apply_filters( 'obp_transaction_history_content_args', $args );
		}

		public static function obp_manage_wallet_payout_method_args( $args = array() ){
			$user_id 			= get_current_user_id();
			$user 				= obp_get_user( $user_id );
			$payout_method_id 	= $user->get_payout_method_id();

			$payout_method_name 	= '';
			$payout_method_fields 	= array();
			$payout_info 			= array();

			if ( $payout_method_id ) {
				$payout_method 			= obp_get_payout_method( $payout_method_id );
				$payout_method_name 	= $payout_method->get_name();
				$payout_method_fields 	= $payout_method->get_setting_fields();
				$payout_info_row 		= OBP_Payout_Method_Info::get_row( $payout_method_id );
				$payout_info 			= isset( $payout_info_row['payout_info'] ) ? maybe_unserialize( ( $payout_info_row['payout_info'] ) ) : array();
			}

			$args = array_merge( $args, array(
				'payout_method_name' 	=> $payout_method_name,
				'payout_method_fields' 	=> $payout_method_fields,
				'payout_info' 			=> $payout_info,
			) );

			return apply_filters( 'obp_manage_wallet_payout_method_args', $args );
		}

		public static function obp_payout_method_field_args( $args = array() ){
			$payout_method_id = isset( $args['payout_method_id'] ) ? $args['payout_method_id'] : '';

			$payout_method_id_selected = $payout_method_id;
			if ( ! $payout_method_id_selected ) {
				$payout_method_id_selected = isset( $payout_methods ) && count( $payout_methods ) > 0 ? $payout_methods[0]->get_id() : '';
			}

			$payout_method_info = OBP_Payout_Method_Info::get_row( $payout_method_id_selected );
			$payout_info 		= isset( $payout_method_info['payout_info'] ) ? unserialize( $payout_method_info['payout_info'] ) : array();

			$args = array_merge( $args, array(
				'payout_method_id_selected' => $payout_method_id_selected,
				'payout_info' 				=> $payout_info,
			) );

			return apply_filters( 'obp_payout_method_field_args', $args );
		}

		public static function obp_payout_method_popup_args( $args = array() ){
			$user_id 			= get_current_user_id();
			$payout_methods 	= OBP_Payout_Method::get_all();
			$user 				= obp_get_user( $user_id );
			$payout_method_id 	= $user->get_payout_method_id();
			// if empty get first payout method
			if ( empty( $payout_method_id ) ) {
				if ( isset( $payout_methods[0] ) ) {
					$payout_method_id = $payout_methods[0]->get_id();
				}
			}

			$args = array_merge( $args, array(
				'payout_method_id' 	=> $payout_method_id,
				'payout_methods' 	=> $payout_methods,
			) );

			return apply_filters( 'obp_payout_method_popup_args', $args );
		}

		public static function obp_withdraw_popup_content_args( $args = array() ){
			$user_id 		= get_current_user_id();
			$user 			= obp_get_user( $user_id );
			$payout_method 	= $user->get_payout_method();
			$balance_amount = $user->get_balance_amount();

			$status = isset( $args['status'] ) ? $args['status'] : '';
			$mess 	= isset( $args['mess'] ) ? $args['mess'] : '';

			$errors = array(
				'invalid' 	=> esc_html__( 'Invalid amount, amount must be greater than zero', 'ovabookpro' ),
				'maximum' 	=> esc_html__( 'The maximum amount that can be withdrawn is [amount]', 'ovabookpro' ),
			);

			$args = array_merge( $args, array(
				'errors' 			=> $errors,
				'payout_method' 	=> $payout_method,
				'balance_amount' 	=> $balance_amount,
				'status' 			=> $status,
				'mess' 				=> $mess,
			) );

			return apply_filters( 'obp_withdraw_popup_content_args', $args );
		}
	}
}