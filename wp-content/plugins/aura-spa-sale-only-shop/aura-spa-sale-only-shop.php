<?php
/**
 * Plugin Name: Aura Spa Sale-Only Shop
 * Description: Restricts the WooCommerce shop to sale products only.
 * Version: 1.0.0
 * Author: Aura Spa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'pre_get_posts', 'aura_spa_sale_only_shop_pre_get_posts', 20 );
add_action( 'woocommerce_product_query', 'aura_spa_sale_only_shop_product_query', 20 );

/**
 * Return sale product ids or a safe empty result.
 *
 * @return array
 */
function aura_spa_sale_only_shop_get_sale_product_ids() {
	if ( ! function_exists( 'wc_get_product_ids_on_sale' ) ) {
		return array( 0 );
	}

	$ids = wc_get_product_ids_on_sale();

	return ! empty( $ids ) ? $ids : array( 0 );
}

/**
 * Restrict the main WooCommerce shop query to sale products.
 *
 * @param WP_Query $query
 */
function aura_spa_sale_only_shop_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! class_exists( 'WooCommerce' ) || ! is_shop() ) {
		return;
	}

	if ( isset( $_GET['aura_show_all_shop'] ) ) {
		return;
	}

	$query->set( 'post_type', 'product' );
	$query->set( 'post__in', aura_spa_sale_only_shop_get_sale_product_ids() );
}

/**
 * Restrict product archive query in WooCommerce as well.
 *
 * @param WC_Product_Query $query
 */
function aura_spa_sale_only_shop_product_query( $query ) {
	if ( is_admin() || ! is_shop() ) {
		return;
	}

	if ( isset( $_GET['aura_show_all_shop'] ) ) {
		return;
	}

	$query->set( 'post__in', aura_spa_sale_only_shop_get_sale_product_ids() );
}
