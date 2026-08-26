<?php

namespace BookPro\Payout;

use BookPro\Traits\SingletonTrait;
use BookPro\OBP_Permission;
use BookPro\Payout\OBP_Payout_Method_Info;
use BookPro\Payout\OBP_Payout_Method;
use BookPro\User\OBP_User;
use BookPro\Payout\OBP_Payout;

defined( 'ABSPATH' ) || exit;



if ( ! class_exists("OBP_Payout_Hooks") ) {
	

	class OBP_Payout_Hooks {

		use SingletonTrait;

		public function __construct(){


			$hooks = array(
				'obp_set_payout_method',
				'obp_update_payout_method',
				'obp_payout_method_change',
				'obp_reload_payout_method',
				'obp_withdraw_popup',
				'obp_withdraw_request',
				'obp_reload_manage_wallet',
				'obp_payout_load_data',
				'obp_show_payout_popup',
			);

			foreach ( $hooks as $hook ) {
				add_action( 'wp_ajax_'.$hook, array( $this, $hook ) );
				add_action( 'wp_ajax_nopriv_'.$hook, array( $this, $hook ) );
			}

		}

		public function obp_show_payout_popup(){
		
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				$id 				= isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
				$payout 			= obp_get_payout( $id );
				$payout_info 		= $payout->get_payout_info();
				$payout_method_id 	= $payout->get_payout_method_id();
				$payout_method 		= obp_get_payout_method( $payout_method_id );
				$field_settings 	= $payout_method->get_setting_fields();
				$args = array(
					'payout' 			=> $payout,
					'payout_info' 		=> $payout_info,
					'field_settings' 	=> $field_settings,
				);
	
				obp_get_template( 'manage-wallet/payout-info-popup.php', $args );
		
			}
	
			wp_die();
		}

		public function obp_payout_load_data(){
			$response = [];

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {

				$payouts = OBP_Payout::get_payouts_ajax();

				// Table Html
				ob_start();
				if ( $payouts->have_posts() ): ?>
				<?php while ( $payouts->have_posts() ) :
					$payouts->the_post();
					obp_get_template( "manage-wallet/transaction-item.php" );
				endwhile;
				else:
					?>
					<tr>
						<td colspan="4"><?php esc_html_e( 'Payouts not found.', 'ovabookpro' ); ?></td>
					</tr>
					<?php
				endif;
				wp_reset_postdata();

				$response['table_html'] = ob_get_contents();
				ob_end_clean();

				// Pagination Html
				ob_start();
				obp_get_template( "manage-wallet/transaction-history-pagination.php", array( 'payouts' => $payouts ) );
				$response['pagination_html'] = ob_get_contents();
				ob_end_clean();
			}

			wp_send_json( $response );
		}

		public function obp_reload_manage_wallet(){
	

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_manage_wallet' ) ) {

				obp_get_template("manage-wallet/content.php" );
			}


			wp_die();
		}

		public function obp_withdraw_popup(){
		

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_withdraw_popup' ) ) {

				obp_get_template("manage-wallet/withdraw-popup.php" );
			}

		
			wp_die();
		}

		public function obp_withdraw_request(){
			$args = array(
				'status' 	=> 'error',
				'mess' 		=> '',
			);
	

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_withdraw_request' ) ) {

				$amount 		= isset( $_POST['amount'] ) ? (float)sanitize_text_field( wp_unslash( $_POST['amount'] ) ) : 0;
				$user_id 		= get_current_user_id();
				$user 			= obp_get_user( $user_id );
				$balance_amount = (float)$user->get_balance_amount();

				if ( ! is_numeric( $amount ) || $amount === 0 ) {
					$args['mess'] = esc_html__( 'Invalid amount, amount must be greater than zero', 'ovabookpro' );
					obp_withdraw_popup_content( $args );
		
					wp_die();
				}

				if ( $amount > $balance_amount ) {
					// translators: %s: balance amount.
					$args['mess'] = sprintf( __( 'The maximum amount that can be withdrawn is %s', 'ovabookpro' ), obp_get_price_html( $balance_amount ) );
					obp_withdraw_popup_content( $args );
					wp_die();
				}

				// Make Request
				OBP_Payout::create( $amount, $user );

				$args['status'] = 'success';
				$args['mess'] 	= esc_html__( 'You have submitted a withdrawal request successfully.', 'ovabookpro' );

				obp_withdraw_popup_content( $args );
			}


			wp_die();
		}

		public function obp_set_payout_method(){
		

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_set_payout_method' ) ) {

				obp_payout_method_popup();
			}
	
			wp_die();
		}

		public function obp_reload_payout_method(){

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_set_payout_method' ) ) {

				obp_manage_wallet_payout_method();
			}

			wp_die();
		}

		public function obp_update_payout_method(){

			$response = array(
				'mess' 		=> esc_html__( 'Update failed.', 'ovabookpro' ),
				'status' 	=> 'error',
			);

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_update_payout_method' ) ) {

				$payout_method_id 	= isset( $_POST['payout_method_id'] ) ? sanitize_text_field( wp_unslash( $_POST['payout_method_id'] ) ) : '';
				$payout_info 		= isset( $_POST['payout_info'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['payout_info'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized


				if ( $payout_method_id ) {
					$check_row = OBP_Payout_Method_Info::get_row( $payout_method_id );

					if ( empty( $check_row ) ) {
						OBP_Payout_Method_Info::add( $payout_method_id, $payout_info );
					} else {
						$id = $check_row['id'];
						OBP_Payout_Method_Info::update( $id, $payout_info );
					}
					$user_id = get_current_user_id();
					$user = obp_get_user( $user_id );
					$user->set_payout_method_id( $payout_method_id );

					$response['status'] = 'success';
					$response['mess'] = esc_html__( 'Update successful.', 'ovabookpro' );
					
				}
				
			}

			echo json_encode( $response );
			wp_die();
		}

		public function obp_payout_method_change(){

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_update_payout_method' ) ) {

				$payout_method_id = isset( $_POST['payout_method_id'] ) ? sanitize_text_field( wp_unslash( $_POST['payout_method_id'] ) ) : '';
				$args = array(
					'payout_method_id' => $payout_method_id,
				);
				obp_payout_method_field( $args );
			}

	
			wp_die();
		}


	}
}