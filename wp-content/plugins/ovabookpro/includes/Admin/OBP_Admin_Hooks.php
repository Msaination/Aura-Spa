<?php
namespace BookPro\Admin;

use BookPro\Traits\SingletonTrait;
use BookPro\OBP_Mail;

use BookPro\Payout\OBP_Payout_Method_Info;
use BookPro\User\OBP_User;
use BookPro\Order\OBP_Order;
use BookPro\Order\OBP_Order_Balance;
use BookPro\Order\OBP_Order_Holding;
use BookPro\Order\OBP_Order_Meta;
use BookPro\Order\OBP_Order_Meta_Queue;
use Bookpro\Commission\OBP_Commission;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists('OBP_Admin_Hooks') ) {
	

	class OBP_Admin_Hooks {

		use SingletonTrait;

		public function __construct(){

			$hooks = array(
				'obp_add_field_payout_method',
				'obp_show_payout_info',
				'obp_load_state_code',
				'obp_export_csv_commission',
			);

			foreach ( $hooks as $hook ) {
				add_action( 'wp_ajax_'.$hook, array( $this, $hook ) );
			}

			// Save Settings
			add_filter( 'pre_update_option_obp_settings', array( $this, 'before_save_options' ), 10, 2 );

			// Withdraw Status Change
			add_action( 'obp_payout_status_obp_pending_to_obp_completed', array( $this, 'obp_payout_status_completed_processing' ) );
			add_action( 'obp_payout_status_obp_pending_to_obp_cancelled', array( $this, 'obp_payout_status_cancelled_processing' ) );

			// Order Status Change
			add_action( 'obp_order_status_obp_pending_to_obp_processing', array( $this, 'obp_order_status_completed_processing' )  );
			add_action( 'obp_order_status_obp_processing_to_obp_cancelled', array( $this, 'obp_order_status_cancelled' ) );
			add_action( 'obp_order_status_obp_processing_to_obp_completed', array( $this, 'obp_order_status_completed' ) );

			// Custom Post Type Table Column Customize

			// for payouts column
			add_filter( 'manage_obp_payout_posts_columns', array( $this, 'obp_manage_obp_payout_columns' ) );
			add_action( 'manage_obp_payout_posts_custom_column', array( $this, 'obp_obp_payout_custom_column' ), 10, 2 );

			// for orders column
			add_filter( 'manage_obp_order_posts_columns', array( $this, 'obp_manage_obp_order_columns' ) );
			add_action( 'manage_obp_order_posts_custom_column', array( $this, 'obp_obp_order_custom_column' ), 10, 2 );

			// for payout methods column
			add_filter( 'manage_obp_payout_method_posts_columns', array( $this, 'obp_manage_obp_payout_method_columns' ) );

			add_filter( 'manage_obp_tax_posts_columns', array( $this, 'obp_manage_obp_tax_columns' ) );
			add_action( 'manage_obp_tax_posts_custom_column', array( $this, 'obp_obp_tax_custom_column' ), 10, 2 );

			// for business column
			add_filter( 'manage_obp_business_posts_columns', array( $this, 'obp_manage_obp_business_columns' ) );
			add_action( 'manage_obp_business_posts_custom_column', array( $this, 'obp_obp_business_custom_column' ), 10, 2 );

			// For withdraw table
			add_action( 'restrict_manage_posts', array( $this, 'obp_withdraw_status_filter_box' ), 10, 1 );
			add_action( 'pre_get_posts',  array( $this, 'obp_withdraw_status_filter_query' ) );
			// for booking table
			add_action( 'restrict_manage_posts', array( $this, 'obp_booking_status_filter_box' ), 10, 1 );
			add_action( 'pre_get_posts', array( $this, 'obp_booking_status_filter_query' ) );

			// Delete Payout Method

			add_action( 'before_delete_post', array( $this, 'obp_before_delete_payout_method' ) );

			// Delete Booking
			add_action( 'before_delete_post', array( $this, 'obp_before_delete_order' ) );

			// User Profile
			add_action( 'show_user_profile', array( $this, 'obp_extra_user_profile_fields' ) );
			add_action( 'edit_user_profile', array( $this, 'obp_extra_user_profile_fields' ) );

			add_action( 'personal_options_update', array( $this, 'obp_save_extra_user_profile_fields' ) );
			add_action( 'edit_user_profile_update', array( $this, 'obp_save_extra_user_profile_fields' ) );

			// Row actions
			add_filter( 'get_edit_post_link', array( $this, 'obp_get_edit_post_link' ), 10, 2 );

			// Fix upload file woocommerce
			add_filter( 'woocommerce_prevent_admin_access', array( $this, 'obp_woocommerce_prevent_admin_access' ) );

			add_filter( 'obp_statistic_total_system_fee', array( $this, 'obp_statistic_total_system_fee_show' ), 10, 2 );

			add_filter( 'obp_statistic_total_vendor_fee', array( $this, 'obp_statistic_total_vendor_fee_show' ), 10 , 2 );

			add_filter( 'obp_statistic_total_tax', array( $this, 'obp_statistic_total_tax_show' ), 10, 2 );

			add_filter( 'obp_statistic_total_profit', array( $this, 'obp_statistic_total_profit_show' ), 10, 2 );

			add_filter( 'obp_statistic_total_booking', array( $this, 'obp_statistic_total_booking_show' ), 10, 2 );

			add_filter( 'obp_statistic_total_commission', array( $this, 'obp_statistic_total_commission_show' ), 10, 2 );

			add_filter( 'obp_date_created_row', array( $this, 'obp_date_created_row_show' ), 10, 2 );

			/*
			format price export commission
			*/
			add_filter( 'obp_system_fee_row', 'obp_round_number'  );
			add_filter( 'obp_vendor_fee_row', 'obp_round_number'  );
			add_filter( 'obp_tax_amount_row', 'obp_round_number'  );
			add_filter( 'obp_commission_row', 'obp_round_number'  );
			add_filter( 'obp_profit_row', 'obp_round_number'  );
			add_filter( 'obp_total_row', 'obp_round_number'  );
		}

		
		public function obp_booking_status_filter_query( $query ){
			if ( is_admin() && $query->is_main_query() && $query->get('post_type') == 'obp_order' ) {
				$status = sanitize_text_field( wp_unslash( $_GET['obp_status'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( in_array( $status, ['obp_pending', 'obp_completed', 'obp_cancelled', 'obp_processing', 'obp_expired', 'obp_refunded'] ) ) {
					$query->set( 'meta_key', 'obp_mb_order_status' );
    				$query->set( 'meta_value', $status );
				}
			}
		}

		public function obp_booking_status_filter_box( $post_type ){
			if( 'obp_order' !== $post_type ){
				return;
			}
			$status = sanitize_text_field( wp_unslash( $_GET['obp_status'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<select name="obp_status">
				<option value=""><?php esc_html_e( 'All Status', 'ovabookpro' ); ?></option>
				<option value="obp_pending" <?php selected( $status, 'obp_pending' ); ?> >
					<?php esc_html_e( 'Pending', 'ovabookpro' ); ?>
				</option>
				<option value="obp_processing" <?php selected( $status, 'obp_processing' ); ?>>
					<?php esc_html_e( 'Processing', 'ovabookpro' ); ?>
				</option>
				<option value="obp_completed" <?php selected( $status, 'obp_completed' ); ?>>
					<?php esc_html_e( 'Completed', 'ovabookpro' ); ?>
				</option>
				<option value="obp_cancelled" <?php selected( $status, 'obp_cancelled' ); ?>>
					<?php esc_html_e( 'Cancelled', 'ovabookpro' ); ?>
				</option>
				<option value="obp_refunded" <?php selected( $status, 'obp_refunded' ); ?>>
					<?php esc_html_e( 'Refunded', 'ovabookpro' ); ?>
				</option>
				<option value="obp_expired" <?php selected( $status, 'obp_expired' ); ?>>
					<?php esc_html_e( 'Expired', 'ovabookpro' ); ?>
				</option>
			</select>
			<?php
		}

		public function obp_withdraw_status_filter_query( $query ){
			if ( is_admin() && $query->is_main_query() && $query->get('post_type') == 'obp_payout' ) {
				$status = sanitize_text_field( wp_unslash( $_GET['obp_status'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( in_array( $status, ['obp_pending', 'obp_completed', 'obp_cancelled'] ) ) {
					$query->set( 'meta_key', 'obp_mb_payout_status' );
    				$query->set( 'meta_value', $status );
				}
			}
		}

		public function obp_withdraw_status_filter_box( $post_type ){
			if( 'obp_payout' !== $post_type ){
				return;
			}
			$status = sanitize_text_field( wp_unslash( $_GET['obp_status'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<select name="obp_status">
				<option value=""><?php esc_html_e( 'All Status', 'ovabookpro' ); ?></option>
				<option value="obp_pending" <?php selected( $status, 'obp_pending' ); ?> >
					<?php esc_html_e( 'Pending', 'ovabookpro' ); ?>
				</option>
				<option value="obp_completed" <?php selected( $status, 'obp_completed' ); ?>>
					<?php esc_html_e( 'Completed', 'ovabookpro' ); ?>
				</option>
				<option value="obp_cancelled" <?php selected( $status, 'obp_cancelled' ); ?>>
					<?php esc_html_e( 'Cancelled', 'ovabookpro' ); ?>
				</option>
			</select>
			<?php
		}

		public function obp_date_created_row_show( $output, $arr ){

			$output = obp_get_date_html( $output );

			return apply_filters( 'obp_date_created_row_show', "$output", $arr );
		}

		public function obp_export_csv_commission(){
			$response = [];
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				// handle export
				$commission_export = OBP_Commission::get_commission_data_export();
				// handle format
				obp_download_send_headers("data_export_" . gmdate("Y-m-d") . ".csv");
				$response['data'] = obp_array2csv($commission_export);
				$response['file_name'] = "data_export_" . gmdate("Y-m-d") . ".csv";
			}
			wp_send_json( $response );
		}

		public function obp_statistic_total_booking_show( $output, $object ){

			$output = $object->get_total_booking();

			return apply_filters( 'obp_statistic_total_booking_show', $output, $object );
		}

		public function obp_statistic_total_profit_show( $output, $object ){

			$output = $object->get_total_profit();

			return apply_filters( 'obp_statistic_total_profit_show', obp_get_price_html( $output ), $object );
		}

		public function obp_statistic_total_commission_show( $output, $object ){

			$output = $object->get_total_commission();

			return apply_filters( 'obp_statistic_total_commission_show', obp_get_price_html( $output ), $object );
		}	

		public function obp_statistic_total_tax_show( $output, $object ){
			$output = $object->get_total_tax();

			return apply_filters( 'obp_statistic_total_tax_show', obp_get_price_html( $output ), $object );
		}

		public function obp_statistic_total_vendor_fee_show( $output, $object ){
			$output = $object->get_total_vendor_fee();

			return apply_filters( 'obp_statistic_total_vendor_fee_show', obp_get_price_html( $output ), $object );
		}

		public function obp_statistic_total_system_fee_show( $output, $object ){

			$output = $object->get_total_system_fee();

			return apply_filters( 'obp_statistic_total_system_fee_show', obp_get_price_html( $output ), $object );
		}

		public function obp_manage_obp_business_columns( $columns ){
			$new_columns = array();

			foreach ( $columns as $key => $column ) {
				$new_columns[$key] = $column;
				if ( $key === 'author' ) {
					$new_columns['vendor_id'] = esc_html__( 'Vendor ID', 'ovabookpro' );
				}
			}

			return apply_filters( 'obp_manage_obp_business_columns', $new_columns );
		}

		public function obp_obp_business_custom_column( $column, $post_id ){
			$business = obp_get_business( $post_id );

			switch ( $column ) {
				case 'vendor_id':
					echo esc_html( $business->get_vendor_id() );
					break;
				default:
					break;
			}

		}

		public function obp_before_delete_order( $post_id ){
			if ( get_post_type( $post_id ) == 'obp_order' ) {
				OBP_Order_Balance::delete_by_order_id( $post_id );
				OBP_Order_Holding::delete_order_holding( $post_id );
				OBP_Order_Meta::delete_by_order_id( $post_id );
				OBP_Order_Meta_Queue::delete_by_order_id( $post_id );
				OBP_Commission::delele_by_order_id( $post_id );
			}
		}

		public function obp_woocommerce_prevent_admin_access( $prevent_access ){
			if ( obp_can_upload_files() ) {
				return false;
			}
			return $prevent_access;
		}

		public function obp_save_extra_user_profile_fields( $user_id ){
			if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-user_' . $user_id ) ) {
		        return;
		    }
		    
		    if ( !current_user_can( 'edit_user', $user_id ) ) { 
		        return false; 
		    }

		    $balance_amount = isset( $_POST['balance_amount'] ) ? sanitize_text_field( wp_unslash( $_POST['balance_amount'] ) ) : '';
		    $user_status 	= isset( $_POST['user_status'] ) ? sanitize_text_field( wp_unslash( $_POST['user_status'] ) ) : '';

		    update_user_meta( $user_id, 'obp_mb_balance_amount', $balance_amount );
		    update_user_meta( $user_id, 'obp_mb_user_status', $user_status );
		}

		public function obp_extra_user_profile_fields( $user ){
			$user_status = get_user_meta( $user->ID, 'obp_mb_user_status', true );
			?>
			<table class="form-table">
			    <tr>
			        <th><label for="balance_amount"><?php esc_attr_e( 'Balance Amount', 'ovabookpro' ); ?></label></th>
			        <td>
			            <input type="text" name="balance_amount" id="balance_amount" value="<?php echo esc_attr( get_user_meta( $user->ID, 'obp_mb_balance_amount', true ) ); ?>" class="regular-number" /><br />
			            <span class="description"><?php esc_html_e( 'Amount of money in wallet', 'ovabookpro' ); ?></span>
			        </td>
			    </tr>

			    <tr>
			    	<th>
			    		<label for="user_status"><?php esc_attr_e( 'User status', 'ovabookpro' ); ?></label>
			    	</th>
			    	<td>
			    		<select name="user_status" id="user_status">
							<option <?php selected( $user_status, '' ); ?> value=""><?php esc_html_e( 'Activated', 'ovabookpro' ); ?></option>
							<option <?php selected( $user_status, 'locked' ); ?> value="locked"><?php esc_html_e( 'Locked', 'ovabookpro' ); ?></option>
						</select>
			    	</td>
			    </tr>
			</table>
			<?php
		}

		public function obp_get_edit_post_link( $link, $post_id ){

			
			if ( get_post_type( $post_id ) == 'obp_business' ) {
				$my_business = OBP()->settings->endpoint->get( 'my_business', 'my-business' );
				$link = OBP()->endpoint->get_endpoint_url( $my_business, '', obp_member_account_url() );
			}

			return apply_filters( 'obp_get_edit_post_link', $link, $post_id );
		}

		public function obp_before_delete_payout_method( $post_id ){

			if ( get_post_type( $post_id ) == 'obp_payout_method' ) {
				// Delete Payout Method Info
				OBP_Payout_Method_Info::delete_by_payout_method_id( $post_id );

				// Clear User Payout Method ID
				$user_ids = OBP_User::get_user_ids_by_payout_method_id( $post_id );
				if ( count( $user_ids ) > 0 ) {
					foreach ( $user_ids as $user_id ) {
						$user = obp_get_user( $user_id );
						$user->set_payout_method_id("");
					}
				}
			}
		}

		public function obp_order_status_completed_processing( $order_id ){
			OBP_Order::obp_order_status_completed_processing( $order_id );
		}

		public function obp_order_status_cancelled( $order_id ){
			// Update payout method info for current user
		
			$order 		= obp_get_order( $order_id );
			$user_id 	= $order->get_customer_id();
			$user 		= obp_get_user( $user_id );
			
			$amount = $order->get_total();
			$user->add_balance_amount( $amount );

			// Send mail cancel
			$send_mail_to_admin 	= OBP()->settings->mail->get('cancel_admin_send_mail', 'yes');
			
			$send_mail_to_customer 	= OBP()->settings->mail->get('cancel_customer_send_mail', 'yes');

			if ( $send_mail_to_admin == 'yes' ){
				OBP_Mail::obp_cancel_order_admin_mail( $order );
			}
			
			if ( $send_mail_to_customer == 'yes' ){
				OBP_Mail::obp_cancel_order_customer_mail( $order );
			}

			do_action( 'obp_admin_order_status_cancelled', $order );

			// Delete order meta queue
			OBP_Order_Meta_Queue::delete_by_order_id( $order_id );
			// Delete order balance
			OBP_Order_Balance::delete_by_order_id( $order_id );
			$order->set_balance_status("obp_completed");
			$order->set_payout_status("obp_completed");
			$order->set_order_status("obp_cancelled");
		}

		public function obp_order_status_completed( $order_id ){

			$order_balance_row 		= OBP_Order_Balance::get_row_by_order_id( $order_id );
			$order_meta_queue_rows 	= OBP_Order_Meta_Queue::get_order_meta_by_order_id( $order_id );
			$current_timestamp 		= current_time( 'timestamp' );

			// Delete order meta queue passed
			if ( ! empty( $order_meta_queue_rows ) ) {
				foreach ( $order_meta_queue_rows as $row ) {
					$end_date = absint( $row->end_date );
					if ( $current_timestamp - $end_date > 0 ) {
						OBP_Order_Meta_Queue::delete( $row->id );
					}
				}
			}

			// Update amount for vendor & Update status order & delete order balance
			if ( ! empty( $order_balance_row ) ) {

				$order_balance 				= obp_get_order_balance( $order_balance_row );
				$order_balance_id 			= $order_balance->get_id();
				$order_balance_start_date 	= $order_balance->get_start_date();
				$remaining_phased 			= $order_balance->get_remaining_phased();
				$remaining_service 			= $order_balance->get_remaining_service();
				$vendor_total 				= $order_balance->get_vendor_total();

				$vendor_id 					= $order_balance->get_vendor_id();

				$order = obp_get_order( $order_id );
				$vendor = obp_get_user( $vendor_id );


				$vendor->add_balance_amount( $vendor_total );
							
				$order->set_balance_status("obp_completed");
				$order->set_order_status("obp_completed");

				// Add commission
				OBP_Commission::add( array(
					'order_id' 		=> $order_id,
					'vendor_id' 	=> $order->get_vendor_id(),
					'system_fee' 	=> $order->get_system_fee(),
					'tax_amount' 	=> $order->get_tax_amount(),
					'vendor_fee' 	=> $order->get_vendor_fee(),
					'date_created' 	=> $order->get_date_created_timestamp(),
					'profit' 		=> $order->get_vendor_total(),
					'total' 		=> $order->get_total(),
					'commission' 	=> $order->get_commission(),
				) );
				
				// Remove Order Balance
				OBP_Order_Balance::delete( $order_balance_id );
			}
		}

		public function obp_manage_obp_payout_method_columns( $columns ){
			$columns['title'] = esc_html__( 'Payout Method', 'ovabookpro' );

			return apply_filters( 'obp_manage_obp_payout_method_columns', $columns );
		}

		public function obp_manage_obp_tax_columns( $columns ){
			$new_columns = array();

			foreach ( $columns as $key => $column ) {
				$new_columns[$key] = $column;
				if ( $key === 'title' ) {
					$new_columns['country'] = esc_html__( 'Country', 'ovabookpro' );
					$new_columns['rate'] = esc_html__( 'Rate', 'ovabookpro' );
					$new_columns['priority'] = esc_html__( 'Priority', 'ovabookpro' );
				}
			}

			$new_columns['title'] = esc_html__( 'Tax', 'ovabookpro' );

			return apply_filters( 'obp_manage_obp_order_columns', $new_columns );
		}

		public function obp_obp_tax_custom_column( $column, $post_id ){
			$tax = obp_get_tax( $post_id );

			switch ( $column ) {
				case 'country':
					echo esc_html( $tax->get_country() );
					break;
				case 'rate':
					echo esc_html( $tax->get_rate().'%' );
				break;
				case 'priority':
					echo esc_html( $tax->get_priority() );
				break;
				default:
					break;
			}
		}

		public function obp_load_state_code(){
		
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				$country_code = isset( $_POST['country_code'] ) ? sanitize_text_field( wp_unslash( $_POST['country_code'] ) ) : '';
			
				if ( isset( obp_get_states()[$country_code] ) ): ?>
					<?php foreach (obp_get_states()[$country_code] as $key => $value ): ?>
						<option value="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $value ); ?>
						</option>
					<?php endforeach; ?>
				<?php endif;
			
			}
	
			wp_die();
		}

		public function obp_manage_obp_order_columns( $columns ){
			$new_columns = array();

			foreach ( $columns as $key => $column ) {
				$new_columns[$key] = $column;
				if ( $key === 'title' ) {
					$new_columns['status'] = esc_html__( 'Status', 'ovabookpro' );
					$new_columns['total'] = esc_html__( 'Total', 'ovabookpro' );
					$new_columns['method'] = esc_html__( 'Payment Method', 'ovabookpro' );
				}
			}

			$new_columns['title'] = esc_html__( 'Booking', 'ovabookpro' );

			return apply_filters( 'obp_manage_obp_order_columns', $new_columns );
		}

		public function obp_manage_obp_payout_columns( $columns ){
			$new_columns = array();

			foreach ( $columns as $key => $column ) {
				$new_columns[$key] = $column;
				if ( $key === 'title' ) {
					$new_columns['status'] = esc_html__( 'Status', 'ovabookpro' );
					$new_columns['amount'] = esc_html__( 'Amount', 'ovabookpro' );
					$new_columns['method'] = esc_html__( 'Payout Method', 'ovabookpro' );
				}
			}

			$new_columns['title'] = esc_html__( 'Withdrawal', 'ovabookpro' );

			return apply_filters( 'obp_manage_obp_payout_columns', $new_columns );
		}

		public function obp_obp_order_custom_column( $column, $post_id ){
			$order = obp_get_order( $post_id );
			switch ( $column ) {
				case 'status':
					?>
					<span class="order_status_<?php echo esc_attr( strtolower( $order->get_order_status() ) ); ?>">
						<?php echo esc_html( $order->get_order_status_translate() ); ?>
					</span>
					<?php
					break;

				case 'total':
				echo wp_kses_post( obp_show_booking_total( $order->get_total(), $order->has_varies() ) );
				break;

				case 'method':
				echo esc_html( $order->get_payment_method() );
				break;

				default:
					break;
			}
		}

		public function obp_obp_payout_custom_column( $column, $post_id ){
			$payout = obp_get_payout( $post_id );
			switch ( $column ) {
				case 'status':
					?>
					<span class="payout_status_<?php echo esc_attr( strtolower( $payout->get_payout_status() ) ) ?>">
						<?php echo esc_html( $payout->get_payout_status_translate() ); ?>
					</span>
					<?php
					break;

				case 'amount':
				echo wp_kses_post( obp_get_price_html( $payout->get_amount() ) );
				break;

				case 'method':
				echo esc_html( $payout->get_payout_method() );
				break;

				default:
					break;
			}
		}

		public function before_save_options( $new_value, $old_value ){
			$endpoint_settings = isset( $new_value['endpoint'] ) ? $new_value['endpoint'] : array();
			$new_order_email_style = isset( $new_value['mail']['new_order_email_style'] ) ? $new_value['mail']['new_order_email_style'] : '';

			if ( ! empty( $endpoint_settings ) ) {
				$endpoint_settings = array_map( 'sanitize_title', $endpoint_settings );
				$new_value['endpoint'] = $endpoint_settings;
			}

			if ( ! empty( $new_order_email_style ) ) {
				$new_order_email_style = wp_strip_all_tags( $new_order_email_style );
				$new_value['mail']['new_order_email_style'] = $new_order_email_style;
			}
			
			return $new_value;
		}

		public function obp_add_field_payout_method(){

	

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_add_field_payout_method' ) &&
				current_user_can('edit_posts')
			) {
			
				include_once OBP_PLUGIN_INC."Admin/MetaBoxes/views/html-payout-method-field.php";
		
			}

	
			wp_die();
		}

		public function obp_show_payout_info(){

		

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_show_payout_info' ) &&
				current_user_can('edit_posts')
			) {
				$user_id = isset( $_POST['user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : '';

				$args = array(
					'user_id' => $user_id,
				);

				OBP()->include( "Admin/Dialog/html-payout-info.php", $args );
			}

		
			wp_die();
		}


		public function obp_payout_status_completed_processing( $post_id ){
			$send_mail_check = OBP()->settings->mail->get('withdraw_success_send_mail','yes');
			if ( $send_mail_check === 'yes' ) {
				OBP_Mail::obp_withdraw_completed_mail( $post_id );
			}

			update_post_meta( $post_id, OBP_METABOX.'payout_date', current_time( 'timestamp' ) );
			update_post_meta( $post_id, OBP_METABOX.'holding_amount', 0 );
		}

		public function obp_payout_status_cancelled_processing( $post_id ){

			$payout 	= obp_get_payout( $post_id );
			$user_id 	= $payout->get_user_id();
			$user 		= obp_get_user( $user_id );

			// Refund
			$holding_amount = $payout->get_holding_amount();
			$user->add_balance_amount( $holding_amount );
			update_post_meta( $post_id, OBP_METABOX.'holding_amount', 0 );

			$send_mail_check = OBP()->settings->mail->get('withdraw_cancelled_send_mail','yes');
			if ( $send_mail_check === 'yes' ) {
				OBP_Mail::obp_withdraw_cancelled_mail( $post_id );
			}

		}

	}
}