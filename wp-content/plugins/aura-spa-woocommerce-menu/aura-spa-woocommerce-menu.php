<?php
/**
 * Plugin Name: Aura Spa WooCommerce Menu
 * Description: Adds WooCommerce shop, cart, checkout, and clear-cart links to the active navigation and cart page.
 * Version: 1.0.0
 * Author: Aura Spa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'aura_spa_wc_enqueue_assets' );
add_action( 'init', 'aura_spa_wc_handle_clear_cart_request' );
add_filter( 'wp_nav_menu_objects', 'aura_spa_wc_add_shop_cart_links', 10, 2 );
add_action( 'woocommerce_before_cart', 'aura_spa_wc_render_cart_actions', 20 );

function aura_spa_wc_enqueue_assets() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	wp_enqueue_style(
		'aura-spa-wc-menu',
		plugin_dir_url( __FILE__ ) . 'style.css',
		array(),
		'1.0.0'
	);
}

function aura_spa_wc_handle_clear_cart_request() {
	if ( ! class_exists( 'WooCommerce' ) || ! isset( $_GET['aura_clear_cart'] ) || empty( $_GET['aura_clear_cart'] ) ) {
		return;
	}

	if ( ! is_cart() || ! isset( WC()->cart ) ) {
		return;
	}

	WC()->cart->empty_cart();
	wp_safe_redirect( add_query_arg( 'cleared', '1', wc_get_cart_url() ) );
	exit;
}

function aura_spa_wc_prepare_navigation_link( $title, $url, $classes = array() ) {
	$item = new stdClass();
	$item->ID = 0;
	$item->db_id = 0;
	$item->menu_item_parent = 0;
	$item->object_id = 0;
	$item->object = 'custom';
	$item->type = 'custom';
	$item->type_label = 'Custom Link';
	$item->title = $title;
	$item->url = $url;
	$item->target = '';
	$item->attr_title = '';
	$item->description = '';
	$item->classes = array_filter( (array) $classes );
	$item->xfn = '';
	$item->status = 'publish';

	return $item;
}

function aura_spa_wc_should_inject_nav_links( $args ) {
	if ( ! isset( $args->theme_location ) ) {
		return false;
	}

	return in_array( $args->theme_location, array( 'menu_main', 'menu_mobile' ), true );
}

function aura_spa_wc_add_shop_cart_links( $items, $args ) {
	if ( ! class_exists( 'WooCommerce' ) || ! aura_spa_wc_should_inject_nav_links( $args ) ) {
		return $items;
	}

	$existing_urls = array_map(
		static function ( $url ) {
			return untrailingslashit( esc_url_raw( $url ) );
		},
		(array) wp_list_pluck( $items, 'url' )
	);

	$links = array();

	if ( function_exists( 'wc_get_page_id' ) ) {
		$shop_id = wc_get_page_id( 'shop' );
		if ( $shop_id ) {
			$links[] = array(
				'title' => __( 'Shop', 'auraspa' ),
				'url'   => get_permalink( $shop_id ) ?: home_url( '/shop/' ),
			);
		}
	}

	if ( function_exists( 'wc_get_cart_url' ) ) {
		$links[] = array(
			'title' => __( 'Cart', 'auraspa' ),
			'url'   => wc_get_cart_url(),
		);
	}

	if ( function_exists( 'wc_get_checkout_url' ) ) {
		$links[] = array(
			'title' => __( 'Checkout', 'auraspa' ),
			'url'   => wc_get_checkout_url(),
		);
	}

	if ( function_exists( 'get_permalink' ) && function_exists( 'wc_get_page_id' ) ) {
		$account_id = wc_get_page_id( 'myaccount' );
		if ( $account_id ) {
			$links[] = array(
				'title' => __( 'My Account', 'auraspa' ),
				'url'   => get_permalink( $account_id ) ?: wp_login_url(),
			);
		}
	}

	foreach ( $links as $link ) {
		$target_url = untrailingslashit( esc_url_raw( $link['url'] ) );
		if ( in_array( $target_url, $existing_urls, true ) ) {
			continue;
		}

		$items[] = aura_spa_wc_prepare_navigation_link( $link['title'], $link['url'], array( 'menu-item', 'menu-item-type-custom' ) );
		$existing_urls[] = $target_url;
	}

	return $items;
}

function aura_spa_wc_render_cart_actions() {
	if ( ! class_exists( 'WooCommerce' ) || ! isset( WC()->cart ) ) {
		return;
	}

	$cart_url = wc_get_cart_url();
	$checkout_url = wc_get_checkout_url();
	$clear_cart_url = add_query_arg( 'aura_clear_cart', '1', $cart_url );

	if ( WC()->cart->is_empty() ) {
		return;
	}

	echo '<div class="aura-spa-cart-actions">';
	echo '<a class="button wc-forward" href="' . esc_url( $cart_url ) . '">' . esc_html__( 'View cart', 'auraspa' ) . '</a>';
	echo '<a class="button alt wc-forward" href="' . esc_url( $checkout_url ) . '">' . esc_html__( 'Checkout', 'auraspa' ) . '</a>';
	echo '<a class="button" href="' . esc_url( $clear_cart_url ) . '">' . esc_html__( 'Clear cart', 'auraspa' ) . '</a>';
	echo '</div>';
}
