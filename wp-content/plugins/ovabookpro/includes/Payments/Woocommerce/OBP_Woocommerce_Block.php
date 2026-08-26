<?php

namespace BookPro\Payments\Woocommerce;
use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

defined( 'ABSPATH' ) || exit;

class OBP_Woocommerce_Block implements IntegrationInterface {
	public function get_name() {
		return 'obp_woocommerce_block';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {
		wp_register_script(
			'obp-blocks-integration',
			OBP_PLUGIN_URI.'assets/js/frontend/obp-woocommerce-block.js',
			array('jquery'), false, true
		);
		wp_set_script_translations(
			'obp-blocks-integration',
			'obp_woocommerce_block',
			plugin_basename( dirname( OBP_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array( 'obp-blocks-integration' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array( 'obp-blocks-integration' );
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		$cart 		= [];
		$tax 		= OBP()->cart->get_tax_amount();
		$system_fee = OBP()->cart->get_system_fee();
		$discount 	= OBP()->cart->get_discount();
		$result 	= [];

		// Loop over $cart items
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];
			$product_id = $cart_item['product_id'];
			if ( get_post_type( $product_id ) == 'obp_service' ) {
				$service = obp_get_service( $product_id );
				$cart[$cart_item_key] = obp_get_price_html( obp_show_price_cart( $product->get_price(), $service->get_rates() ), $service->get_price_type() );
			}
		}
		if ( $tax ) {
			$result['tax'] = [ 'label' => esc_html__( 'Tax', 'ovabookpro' ), 'value' => wc_price( $tax ) ];
		}
		if ( $system_fee ) {
			$result['system_fee'] = [ 'label' => esc_html__( 'System Fee', 'ovabookpro' ), 'value' => wc_price( $system_fee ) ];
		}
		if ( $discount ) {
			$result['discount'] = [ 'label' => esc_html__( 'Discount', 'ovabookpro' ), 'value' => '-'.wc_price( $discount ) ];
		}

	    return $result;
	}
}