<?php
/**
 * Plugin Name: Aura Spa Sale Shop Grid
 * Description: Shows only sale products in a styled WooCommerce shop grid.
 * Version: 1.0.0
 * Author: Aura Spa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'aura_spa_sale_shop_grid_enqueue_styles' );
add_action( 'pre_get_posts', 'aura_spa_sale_shop_grid_pre_get_posts', 30 );
add_action( 'woocommerce_product_query', 'aura_spa_sale_shop_grid_woocommerce_product_query', 20 );
add_filter( 'woocommerce_page_title', 'aura_spa_sale_shop_grid_page_title', 20 );

function aura_spa_sale_shop_grid_sale_ids() {
	if ( ! function_exists( 'wc_get_product_ids_on_sale' ) ) {
		return array( 0 );
	}

	$ids = wc_get_product_ids_on_sale();

	return ! empty( $ids ) ? $ids : array( 0 );
}

function aura_spa_sale_shop_grid_enqueue_styles() {
	if ( ! class_exists( 'WooCommerce' ) || ( ! is_shop() && ! is_product_taxonomy() ) ) {
		return;
	}

	wp_enqueue_style(
		'aura-spa-sale-shop-grid',
		plugin_dir_url( __FILE__ ) . 'style.css',
		array(),
		'1.0.0'
	);
}

function aura_spa_sale_shop_grid_pre_get_posts( $query ) {
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
	$query->set( 'post__in', aura_spa_sale_shop_grid_sale_ids() );
	$query->set( 'posts_per_page', 12 );
}

function aura_spa_sale_shop_grid_woocommerce_product_query( $query ) {
	if ( ! class_exists( 'WooCommerce' ) || ! is_shop() ) {
		return;
	}

	if ( isset( $_GET['aura_show_all_shop'] ) ) {
		return;
	}

	$query->set( 'post__in', aura_spa_sale_shop_grid_sale_ids() );
	$query->set( 'posts_per_page', 12 );
}

function aura_spa_sale_shop_grid_page_title( $title ) {
	if ( is_shop() ) {
		return __( 'Sale Treatments', 'auraspa' );
	}

	return $title;
}
