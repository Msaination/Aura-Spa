<?php
namespace BookPro\Payments\Woocommerce;

use BookPro\Traits\SingletonTrait;
use BookPro\Payments\Woocommerce\OBP_Woocommerce_CPT;
use BookPro\Payments\Woocommerce\OBP_Woocommerce_CPT_Order_Item;
use BookPro\Order\OBP_Order_Hooks;
use BookPro\Order\OBP_Order;
use BookPro\Payments\Woocommerce\OBP_Woocommerce_Payment;
use BookPro\Business\OBP_Business;
use BookPro\Order\OBP_Order_Meta;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Woocommerce_Hooks") ) {
	

	class OBP_Woocommerce_Hooks {

		use SingletonTrait;

		public function __construct(){

			// CPT Product
			add_filter( 'woocommerce_data_stores', array( $this, 'obp_woocommerce_data_stores' ), 99 );
			add_filter( 'woocommerce_product_get_price', array( $this, 'obp_woocommerce_product_get_price' ), 10, 2 );
			add_filter( 'woocommerce_get_order_item_classname', array( $this, 'obp_woocommerce_order_item_classname' ), 12, 3 );
			add_filter('woocommerce_checkout_create_order_line_item_object', array( $this, 'obp_checkout_create_order_line_item_object' ) , 12 );

			// Total & Subtotal
			add_filter( 'woocommerce_cart_total', array( $this, 'obp_woocommerce_cart_total' ), 10 , 1 );
			add_filter( 'woocommerce_cart_subtotal', array( $this, 'obp_woocommerce_cart_subtotal' ), 10 , 1 );

			// Cart Item Data
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'obp_woocommerce_add_cart_item_data' ), 10, 2 );
			add_filter( 'woocommerce_get_item_data', array( $this, 'obp_woocommerce_get_item_data' ), 10, 2 );

			add_filter( 'woocommerce_cart_item_permalink', array( $this, 'obp_woocommerce_cart_item_permalink' ), 10, 3 );
			add_filter( 'woocommerce_cart_item_thumbnail', array( $this, 'obp_woocommerce_cart_item_thumbnail' ), 10, 3 );

			add_action( 'woocommerce_cart_totals_before_order_total', array( $this, 'obp_woocommerce_system_fee_and_tax' ) );
			add_action( 'woocommerce_review_order_before_order_total', array( $this, 'obp_woocommerce_system_fee_and_tax' ) );

			// Order Status Change

			if ( ! is_admin() ) {
				add_action( 'woocommerce_order_status_pending_to_processing', array( $this, 'obp_update_order_status' ), 10, 1 );
				add_action( 'woocommerce_order_status_pending_to_completed', array( $this, 'obp_update_order_status' ), 10, 1 );
				add_action( 'woocommerce_order_status_pending_to_on-hold', array( $this, 'obp_update_order_status' ), 10, 1 );
				// Mail Attachments
				add_filter( 'woocommerce_email_attachments', array( $this, 'obp_woocommerce_email_attachments' ), 15, 3 );

				add_action('woocommerce_email_sent', array( $this, 'obp_woocommerce_email_sent' ), 15, 3 );

			}

			// Thank you page redirect
			add_filter( 'woocommerce_get_checkout_order_received_url', array( $this, 'obp_woocommerce_get_checkout_order_received_url' ), 10, 2 );

			// Cart Calculate totals
			add_filter('woocommerce_calculated_total', array( $this, 'obp_woocommerce_calculated_total' ), 10, 2);

			add_action( 'obp_after_save_cart', array( $this, 'obp_woocommerce_empty_cart' ) );

			add_action( 'obp_countdown_timeout', array( $this, 'obp_woocommerce_empty_cart' ) );

			add_action( 'woocommerce_before_calculate_totals', array( $this, 'obp_woocommerce_before_calculate_totals' ), 1000, 1);

			// Hide Remove Link
			add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'obp_woocommerce_cart_item_remove_link' ), 10, 2 );

			// Litmit Qty
			add_filter( 'woocommerce_after_cart_item_quantity_update', array( $this, 'obp_woocommerce_after_cart_item_quantity_update' ) ,20, 4 );

			// Order Item Data
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'obp_add_cart_item_data' ), 10, 2 );
			add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'obp_checkout_create_order_line_item' ), 10, 4 );

			// Order Processed
			add_action( 'woocommerce_checkout_order_created', array( $this, 'obp_checkout_order_processed' ), 10 , 1 );
			add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'obp_api_checkout_order_processed' ), 10, 1 );

			add_action( 'woocommerce_review_order_after_payment', array( 'BookPro\\Order\\OBP_Order_Hooks', 'obp_order_countdown' ) );

			add_filter( 'render_block', array( 'BookPro\\Order\\OBP_Order_Hooks', 'obp_order_countdown_block' ), 10, 2 );
			
			// Order Item Permalink
			add_filter( 'woocommerce_order_item_permalink', array( $this, 'obp_woocommerce_order_item_permalink' ), 10, 3  );

			// Admin Order
			add_action( 'woocommerce_admin_order_totals_after_tax', array( $this, 'obp_woocommerce_admin_order_totals_after_tax' ), 10, 1 );

			add_filter( 'woocommerce_get_order_item_totals', array( $this, 'obp_woocommerce_get_order_item_totals' ), 10, 2 );

			// Set Total
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'obp_woocommerce_checkout_order_processed'), 99 ,1 );

			add_action( 'woocommerce_store_api_checkout_order_processed',array( $this, 'obp_woocommerce_store_api_checkout_order_processed' ), 99, 1 );

			// Set subtotal service
			add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'obp_woocommerce_order_formatted_line_subtotal' ), 10 , 3 );

			add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'obp_woocommerce_cart_item_subtotal' ), 10, 3 );

			if ( ! is_admin() ) {
				// Blocks
				add_action(
				    'woocommerce_blocks_mini-cart_block_registration',
				    function( $integration_registry ) {
				        $integration_registry->register( new OBP_Woocommerce_Block() );
				    }
				);
				add_action(
				    'woocommerce_blocks_cart_block_registration',
				    function( $integration_registry ) {
				        $integration_registry->register( new OBP_Woocommerce_Block() );
				    }
				);
				add_action(
				    'woocommerce_blocks_checkout_block_registration',
				    function( $integration_registry ) {
				        $integration_registry->register( new OBP_Woocommerce_Block() );
				    }
				);
			}
			
			// Mini cart item
			add_filter( 'woocommerce_widget_cart_item_quantity', array( $this, 'obp_woocommerce_widget_cart_item_quantity' ), 10, 3 );
		}

		public static function obp_woocommerce_get_checkout_order_received_url( $order_received_url, $woo_order ){
			$order_id = $woo_order->get_meta('obp_mb_order_id');
			if ( $order_id ) {
				$order = obp_get_order( $order_id );
				$order_received_url = obp_get_thank_url_with_key( $order->get_key() );
			}
			return apply_filters( 'obp_woocommerce_get_checkout_order_received_url', $order_received_url, $woo_order );
		}

		public function obp_woocommerce_widget_cart_item_quantity( $html, $cart_item, $cart_item_key ){
			$product 	= $cart_item['data'];
   			$product_id = $cart_item['product_id'];
   			if ( get_post_type( $product_id ) == 'obp_service' ) {
   				$service 	= obp_get_service( $product_id );
   				$html 		= obp_get_price_html( obp_show_price_cart( $product->get_price(), $service->get_rates() ), $service->get_price_type() );
   			}

			return apply_filters( 'obp_woocommerce_widget_cart_item_quantity', $html, $cart_item, $cart_item_key );
		}

		public function obp_woocommerce_cart_item_subtotal( $subtotal, $cart_item, $cart_item_key ){
			$product_id = $cart_item['product_id'];
			$product 	= $cart_item['data'];
			if ( get_post_type( $product_id ) == 'obp_service' ) {
				$service 	= obp_get_service( $product_id );
				$subtotal 	= obp_get_price_html( obp_show_price_cart( $product->get_price(), $service->get_rates() ), $service->get_price_type() );
			}

			return apply_filters( 'obp_woocommerce_cart_item_subtotal', $subtotal, $cart_item, $cart_item_key );
		}

		public function obp_woocommerce_order_formatted_line_subtotal( $subtotal, $item, $woo_order ){

			$order_id = $woo_order->get_meta('obp_mb_order_id');
			if ( $order_id ) {
				$order 				= obp_get_order( $order_id );
				$product_id 		= $item->get_product_id();
				$service 			= obp_get_service( $product_id );
				$item_order_meta 	= OBP_Order_Meta::get_order_item_by_order_id_service_id( $order_id, $product_id );
				if ( $item_order_meta ) {
					$subtotal = obp_get_price_html( $item_order_meta->price, $service->get_price_type() );
				}
			}

			return apply_filters( 'obp_woocommerce_order_formatted_line_subtotal', $subtotal, $item, $woo_order );
		}

		public function obp_woocommerce_store_api_checkout_order_processed( $woo_order ){
			$order_id = $woo_order->get_meta('obp_mb_order_id');
			if ( $order_id ) {
				$order = obp_get_order( $order_id );
				$woo_order->set_total( $order->get_total() );
			}
			$woo_order->save();
		}

		public function obp_woocommerce_checkout_order_processed( $woo_order_id ){
			$woo_order = wc_get_order( $woo_order_id );
			$order_id = $woo_order->get_meta('obp_mb_order_id');
			if ( $order_id ) {
				$order = obp_get_order( $order_id );
				$woo_order->set_total( $order->get_total() );
			}
			$woo_order->save();
		}

		public function obp_woocommerce_email_attachments( $attachments, $email_id, $woo_order ) {
			// Check if the email is related to an order
    		if ( $woo_order instanceof WC_Order ) {
				$woo_order_id 			= $woo_order->get_id();
				$check_send_mail 		= OBP()->settings->mail->get('new_order_send_mail', 'yes');
				$order_ids 				= OBP_Order::get_order_ids_by_woo_order_id( $woo_order_id );
				$woo_order 				= wc_get_order( $woo_order_id );
				$order_status 			= 'wc-'.$woo_order->get_status();
				$woo_order_status 		= obp_get_woocommerce_order_status();
				$additional_attach 		= OBP()->settings->mail->get('new_order_additional_attach','');

				if ( $check_send_mail === "yes" && count( $order_ids ) > 0 &&
					in_array( $order_status, $woo_order_status ) ) {
					// Get PDF path
					$order_pdf = new \BookPro\Order\OBP_Order_PDF();
					foreach ( $order_ids as $order_id ) {
						$pdf_path = $order_pdf->make_pdf_invoice( $order_id );
						if ( file_exists( $pdf_path ) ) {
							$attachments[] = $pdf_path;
						}
					}

					if ( ! empty( $additional_attach ) ) {
						$fullsize_path = get_attached_file( $additional_attach );
						if ( file_exists( $fullsize_path ) ) {
							$attachments[] = $fullsize_path;
						}
					}
				}
			}

			return apply_filters( 'obp_woocommerce_email_attachments', $attachments, $email_id, $woo_order );
		}

		public function obp_woocommerce_email_sent( $return, $email_id, $woo_email ){

			$attachments 		= $woo_email->get_attachments();
			$additional_attach 	= OBP()->settings->mail->get('new_order_additional_attach','');

			if ( ! empty( $additional_attach ) ) {
				$fullsize_path = get_attached_file( $additional_attach );

				if ( ($key = array_search($fullsize_path, $attachments)) !== false ) {
				    unset($attachments[$key]);
				}
			}

			// Delete Files
			if ( count( $attachments ) > 0 ) {
				foreach ( $attachments as $file ) {
					wp_delete_file( $file );
				}
			}

		}

		public function obp_woocommerce_before_calculate_totals( $wc_cart ){
			// This is necessary for WC 3.0+
		    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
		        return;

		    // Loop through cart items
		    foreach ( $wc_cart->get_cart() as $cart_item ) {
		        $product_id = $cart_item['product_id'];
		        if ( get_post_type( $product_id ) == 'obp_service' ) {
		        	$_cart_item = OBP()->cart->get_cart_item( $product_id );
		        	$price = $_cart_item->get_price();
		        	$cart_item['data']->set_price( obp_calculate_inc_tax( $price, $_cart_item->get_rates() ) );
		        }
		    }
		}

		public function obp_woocommerce_data_stores( $stores ){
			$stores['product'] = OBP_Woocommerce_CPT::class;
			return $stores;
		}

		public function obp_woocommerce_empty_cart(){
			WC()->cart->empty_cart();
		}

		public function obp_woocommerce_get_order_item_totals( $total_rows, $woo_order ){
			$order_id = $woo_order->get_meta(OBP_METABOX.'order_id');
			if ( $order_id ) {
				$order 		= obp_get_order( $order_id );
				$system_fee = $order->get_system_fee();
				$tax_amount = $order->get_tax_amount();
				$discount 	= $order->get_discount();
				$subtotal 	= $order->get_subtotal();
				$new_total_rows = array();

				foreach ( $total_rows as $key => $row ) {
					$new_total_rows[$key] = $row;
					if ( $key === 'cart_subtotal' ) {
						$row['value'] = wc_price( $subtotal );

						if ( $discount ) {
							$new_total_rows['obp_discount'] = array(
								'label' => __( 'Discount:', 'ovabookpro' ),
								'value' => '-'.wc_price( $discount ),
							);
						}

						if ( $system_fee ) {
							$new_total_rows['obp_system_fee'] = array(
								'label' => __( 'System Fee:', 'ovabookpro' ),
								'value' => wc_price( $system_fee ),
							);
						}

						if ( $tax_amount ) {
							$new_total_rows['obp_tax'] = array(
								'label' => __( 'Tax:', 'ovabookpro' ),
								'value' => wc_price( $tax_amount ),
							);
						}
						
					}
				}

				$total_rows = $new_total_rows;

			}

			return apply_filters( 'obp_woocommerce_get_order_item_totals', $total_rows, $woo_order  );
		}

		public function obp_woocommerce_admin_order_totals_after_tax( $woo_order_id ){
			$woo_order 	= wc_get_order( $woo_order_id );
			$order_id 	= $woo_order->get_meta(OBP_METABOX.'order_id');

			if ( $order_id ) {
				$order 		= obp_get_order( $order_id );
				$system_fee = $order->get_system_fee();
				$tax_amount = $order->get_tax_amount();
				$discount 	= $order->get_discount();

				if ( $discount ) {
					?>
					<tr>
						<td class="label"><?php esc_html_e( 'Discount', 'ovabookpro' ); ?>:</td>
						<td width="1%"></td>
						<td class="total">
							<?php echo '-'.wp_kses_post( wc_price( $discount ) ); ?>
						</td>
					</tr>
					<?php
				}

				if ( $system_fee ) {
					?>
					<tr>
						<td class="label"><?php esc_html_e( 'System Fee', 'ovabookpro' ); ?>:</td>
						<td width="1%"></td>
						<td class="total">
							<?php echo wp_kses_post( wc_price( $system_fee ) ); ?>
						</td>
					</tr>
					<?php
				}
				if ( $tax_amount ) {
					?>
					<tr>
						<td class="label"><?php esc_html_e( 'Tax', 'ovabookpro' ); ?>:</td>
						<td width="1%"></td>
						<td class="total">
							<?php echo wp_kses_post( wc_price( $tax_amount ) ); ?>
						</td>
					</tr>
					<?php
				}
			}
		}

		public function obp_woocommerce_order_item_permalink( $permalink, $item, $order ){

			$product_id = $item->get_product_id();

			if ( get_post_type( $product_id ) === 'obp_service' ) {
				$service 		= obp_get_service( $product_id );
				$vendor_id 		= $service->get_vendor_id();
				$business_id 	= OBP_Business::get_id( $vendor_id );
				$permalink      = get_permalink( $business_id );
			}

			return apply_filters( 'obp_woocommerce_order_item_permalink', $permalink, $item, $order );
		}

		public function obp_woocommerce_cart_item_remove_link( $remove_link, $cart_item_key ){
			$cart_item = WC()->cart->get_cart()[$cart_item_key];
			$product_id = $cart_item['product_id'];
			if ( get_post_type( $product_id ) === 'obp_service' ) {
				$remove_link = '';
			}

			return $remove_link;
		}

		public function obp_update_order_status( $woo_order_id ){
			$order_ids 			= OBP_Order::get_order_ids_by_woo_order_id( $woo_order_id );
			$woo_order 			= wc_get_order( $woo_order_id );
			$order_status 		= 'wc-'.$woo_order->get_status();
			$woo_order_status 	= obp_get_woocommerce_order_status();
			$customer_id = $woo_order->get_customer_id();
			if ( count( $order_ids ) > 0 && in_array( $order_status, $woo_order_status ) ) {

				foreach ( $order_ids as $order_id ) {
					// Update Order Status

					update_post_meta( $order_id, OBP_METABOX.'customer_id', $customer_id );
					update_post_meta( $order_id, OBP_METABOX.'order_status', "obp_processing" );
					OBP_Order::obp_order_status_completed_processing( $order_id );
				}
			}
		}

		public function obp_woocommerce_cart_item_thumbnail( $product_image, $cart_item, $cart_item_key ){
			$product_id = $cart_item['product_id'];

			if ( get_post_type( $product_id ) === 'obp_service' ) {
				$cart_item = OBP()->cart->get_cart_item( $product_id );
				if ( $cart_item ) {
					$business_id = $cart_item->get_business_id();
					$product_image = get_the_post_thumbnail( $business_id, 'woocommerce_thumbnail' );
				}
			}

			return $product_image;
		}

		public function obp_woocommerce_calculated_total( $total , $wc_cart ){
			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
            
			$order_id = OBP()->session->get('order_id');

            if ( $order_id ) {

            	$order = obp_get_order( $order_id );
            	$total = $order->get_total();
            }

            return apply_filters( 'obp_woocommerce_calculated_total', $total , $wc_cart );
		}

		public function obp_woocommerce_cart_item_permalink( $product_permalink, $cart_item, $cart_item_key ){

			$product_id = $cart_item['product_id'];

			if ( get_post_type( $product_id ) === 'obp_service' ) {
				$cart_item = OBP()->cart->get_cart_item( $product_id );
				if ( $cart_item ) {
					$business_id = $cart_item->get_business_id();
					$product_permalink = get_permalink( $business_id );
				}
			}

			return apply_filters( 'obp_woocommerce_cart_item_permalink', $product_permalink, $cart_item, $cart_item_key );
		}

		public function obp_woocommerce_add_cart_item_data( $cart_item_data, $product_id ){
			$cart_item = OBP()->cart->get_cart_item( $product_id );
			if( $cart_item  ) {
				$user = obp_get_user( $cart_item->get_staff_id() );
				$cart_item_data['obp_time'] 	= $cart_item->get_time();
				$cart_item_data['obp_date'] 	= $cart_item->get_date();
				$cart_item_data['obp_staff'] 	= $user->get_nickname();
				$cart_item_data['obp_packages'] = $cart_item->get_package_names();
			}
			return apply_filters( 'obp_woocommerce_add_cart_item_data', $cart_item_data, $product_id );
		}

		public function obp_woocommerce_get_item_data( $item_data, $cart_item_data ){

			if( isset( $cart_item_data['obp_time'] ) ) {
				$item_data[] = array(
					'key' 	=> __( 'Time', 'ovabookpro' ),
					'value' => wc_clean( $cart_item_data['obp_time'] )
				);
			}

			if( isset( $cart_item_data['obp_date'] ) ) {
				$item_data[] = array(
					'key' 	=> __( 'Date', 'ovabookpro' ),
					'value' => wc_clean( $cart_item_data['obp_date'] )
				);
			}

			if( isset( $cart_item_data['obp_staff'] ) ) {
				$item_data[] = array(
					'key' 	=> __( 'Staff', 'ovabookpro' ),
					'value' => wc_clean( $cart_item_data['obp_staff'] )
				);
			}

			if ( isset( $cart_item_data['obp_packages'] ) && $cart_item_data['obp_packages'] ) {
				$item_data[] = array(
					'key' 	=> __( 'Packages', 'ovabookpro' ),
					'value' => wc_clean( $cart_item_data['obp_packages'] )
				);
			}

			return $item_data;
		}

		public function obp_woocommerce_system_fee_and_tax(){

			foreach ( WC()->cart->get_cart() as $cart_item ) {
				$product_id = $cart_item['data']->get_id();
				if ( get_post_type( $product_id ) === 'obp_service' ) {
					$system_fee = OBP()->cart->get_system_fee();
					$tax_fee 	= OBP()->cart->get_tax_amount();
					$discount 	= OBP()->cart->get_discount();

					if ( $discount ) {
						?>
						<tr class="obp-discount">
							<th>
								<?php esc_html_e( 'Discount', 'ovabookpro' ); ?>
							</th>
							<td>
								<?php echo '-'.wp_kses_post( wc_price( $discount ) ); ?>
							</td>
						</tr>
						<?php
					}
					
					if ( $system_fee ): ?>
						
						<tr class="obp-system-fee">
							<th>
								<?php esc_html_e( 'System Fee', 'ovabookpro' ); ?>
							</th>
							<td>
								<?php echo wp_kses_post( wc_price( $system_fee ) ); ?>
							</td>
						</tr>

					<?php endif; ?>

					<?php if ( $tax_fee ): ?>
						<tr class="obp-tax-fee">
							<th>
								<?php esc_html_e( 'Tax', 'ovabookpro' ); ?>
							</th>
							<td>
								<?php echo wp_kses_post( wc_price( $tax_fee ) ); ?>
							</td>
						</tr>
					<?php endif; ?>

					<?php
					break;
				}
			}

		}

		public function obp_woocommerce_after_cart_item_quantity_update( $cart_item_key, $quantity, $old_quantity, $cart ){
			// if( ! is_cart() ) return; // Only on cart page

			$product_id = $cart->cart_contents[ $cart_item_key ]['product_id'];
			$limit = null;
			if ( get_post_type( $product_id ) === 'obp_service' ) {
				$limit = 1;
			}
			// check limit item
			if ( $limit ) {
				if( $quantity > $limit ){
			        // Change the quantity to the limit allowed
			    	$cart->cart_contents[ $cart_item_key ]['quantity'] = $limit;
			        // Add a custom notice
			    	wc_add_notice( __('Quantity limit reached for this item', 'ovabookpro'), 'notice' );
			    }
			}
		}

		public function obp_woocommerce_cart_total( $total ){
			foreach ( WC()->cart->get_cart() as $cart_item ) {
				$product_id = $cart_item['data']->get_id();
				if ( get_post_type( $product_id ) === 'obp_service' ) {
					$total = OBP()->cart->get_total();
					$total = wc_price( $total );
					break;
				}
			}
			return apply_filters( 'obp_woocommerce_cart_total', $total );
		}

		public function obp_woocommerce_product_get_price( $price, $product ){
			$product_id = $product->get_id();

			if ( get_post_type( $product_id ) === 'obp_service' ) {
				$cart_item = OBP()->cart->get_cart_item( $product_id );
				if ( ! empty( $cart_item ) ) {
					$price = obp_calculate_inc_tax( $cart_item->get_price(), $cart_item->get_rates() );
				}
				
			}

			return apply_filters( 'obp_woocommerce_product_get_price', $price, $product );
		}

		public function obp_woocommerce_cart_subtotal( $cart_subtotal ){
			foreach ( WC()->cart->get_cart() as $cart_item ) {
				$product_id = $cart_item['data']->get_id();
				if ( get_post_type( $product_id ) === 'obp_service' ) {
					$cart_subtotal = OBP()->cart->get_subtotal();
					$cart_subtotal = wc_price( $cart_subtotal );
					break;
				}
			}
			return apply_filters( 'obp_woocommerce_cart_subtotal', $cart_subtotal );
		}

		public function obp_checkout_create_order_line_item_object( $obj_WC_Order_Item_Product ){
			$obj_WC_Order_Item_Product = new OBP_Woocommerce_CPT_Order_Item();
			return $obj_WC_Order_Item_Product;
		}

		public function obp_woocommerce_order_item_classname( $classname, $item_type, $id ){
			if ( 'WC_Order_Item_Product' === $classname ) {
				$classname = '\BookPro\Payments\Woocommerce\OBP_Woocommerce_CPT_Order_Item';
			}
			return $classname;
		}


		public function obp_add_cart_item_data( $cart_item_data, $product_id ){

			if ( get_post_type( $product_id ) === 'obp_service' ) {
				$cart_item 						= OBP()->cart->get_cart_item( $product_id );
				$user = obp_get_user( $cart_item->get_staff_id() );
				$cart_item_data['obp_time'] 	= $cart_item->get_time();
				$cart_item_data['obp_date'] 	= $cart_item->get_date();
				$cart_item_data['obp_staff'] 	= $user->get_nickname();
				$cart_item_data['obp_packages'] = $cart_item->get_package_names();
			}

			return $cart_item_data;

		}

		public function obp_checkout_create_order_line_item( $item, $cart_item_key, $values, $order ){
			if( isset( $values['obp_time'] ) ) {
				$item->add_meta_data(
					__( 'Time', 'ovabookpro' ),
					$values['obp_time'],
					true
				);
			}
			if( isset( $values['obp_date'] ) ) {
				$item->add_meta_data(
					__( 'Date', 'ovabookpro' ),
					$values['obp_date'],
					true
				);
			}
			if( isset( $values['obp_staff'] ) ) {
				$item->add_meta_data(
					__( 'Staff', 'ovabookpro' ),
					$values['obp_staff'],
					true
				);
			}
			if ( isset( $values['obp_packages'] ) && $values['obp_packages'] ) {
				$item->add_meta_data(
					__( 'Packages', 'ovabookpro' ),
					$values['obp_packages'],
					true
				);
			}
		}

		public function obp_checkout_order_processed( $order_id ){
			$order_woo = wc_get_order( $order_id );
			OBP_Order::update_order_woocommerce( $order_woo );
		}

		public function obp_api_checkout_order_processed( $order ){
			OBP_Order::update_order_woocommerce( $order );
		}

	}
}
