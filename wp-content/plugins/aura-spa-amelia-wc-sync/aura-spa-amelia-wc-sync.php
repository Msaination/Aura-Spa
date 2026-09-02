<?php
/**
 * Plugin Name: Aura Spa Amelia WooCommerce Sync
 * Description: Maps Amelia services and packages to WooCommerce products and syncs their prices to the treatment amount.
 * Version: 1.0.0
 * Author: Aura Spa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'aura_spa_amelia_wc_sync_bootstrap', 20 );
add_action( 'admin_init', 'aura_spa_amelia_wc_sync_manual_trigger' );

function aura_spa_amelia_wc_sync_bootstrap() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	aura_spa_amelia_wc_sync_all();
}

function aura_spa_amelia_wc_sync_manual_trigger() {
	if ( ! current_user_can( 'manage_woocommerce' ) || ! isset( $_GET['aura_sync_amelia_wc'] ) ) {
		return;
	}

	aura_spa_amelia_wc_sync_all();
	wp_safe_redirect( remove_query_arg( 'aura_sync_amelia_wc' ) );
	exit;
}

function aura_spa_amelia_wc_sync_all() {
	global $wpdb;

	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	$services = $wpdb->get_results(
		"SELECT id, name, price, settings FROM {$wpdb->prefix}amelia_services ORDER BY id",
		ARRAY_A
	);

	if ( is_array( $services ) ) {
		foreach ( $services as $service ) {
			aura_spa_amelia_wc_sync_service( $service );
		}
	}

	$packages = $wpdb->get_results(
		"SELECT id, name, price, settings FROM {$wpdb->prefix}amelia_packages ORDER BY id",
		ARRAY_A
	);

	if ( is_array( $packages ) ) {
		foreach ( $packages as $package ) {
			aura_spa_amelia_wc_sync_package( $package );
		}
	}
}

function aura_spa_amelia_wc_sync_service( $service ) {
	if ( empty( $service['id'] ) || empty( $service['name'] ) ) {
		return;
	}

	$service_id = (int) $service['id'];
	$name       = trim( (string) $service['name'] );
	$price      = (float) ( $service['price'] ?? 0 );
	$product_id = aura_spa_amelia_wc_find_or_create_product( $name, 'service', $service_id );

	if ( ! $product_id ) {
		return;
	}

	aura_spa_amelia_wc_apply_mapping( $product_id, $name, $price, 'service', $service_id );
	aura_spa_amelia_wc_update_service_settings( $service_id, $product_id );
}

function aura_spa_amelia_wc_sync_package( $package ) {
	if ( empty( $package['id'] ) || empty( $package['name'] ) ) {
		return;
	}

	$package_id = (int) $package['id'];
	$name       = trim( (string) $package['name'] );
	$price      = (float) ( $package['price'] ?? 0 );
	$product_id = aura_spa_amelia_wc_find_or_create_product( $name, 'package', $package_id );

	if ( ! $product_id ) {
		return;
	}

	aura_spa_amelia_wc_apply_mapping( $product_id, $name, $price, 'package', $package_id );
	aura_spa_amelia_wc_update_package_settings( $package_id, $product_id );
}

function aura_spa_amelia_wc_find_or_create_product( $name, $type, $source_id ) {
	$meta_key = 'service' === $type ? '_aura_amelia_service_id' : '_aura_amelia_package_id';

	$existing = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 1,
			'meta_key'       => $meta_key,
			'meta_value'     => (string) $source_id,
			'fields'         => 'ids',
		)
	);

	if ( ! empty( $existing ) ) {
		return (int) $existing[0];
	}

	$existing_title = get_page_by_title( $name, OBJECT, 'product' );
	if ( $existing_title ) {
		update_post_meta( $existing_title->ID, $meta_key, (string) $source_id );
		return (int) $existing_title->ID;
	}

	$product_id = wp_insert_post(
		array(
			'post_title'   => $name,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'product',
		)
	);

	if ( is_wp_error( $product_id ) || ! $product_id ) {
		return 0;
	}

	wp_set_object_terms( $product_id, 'simple', 'product_type', false );
	update_post_meta( $product_id, '_visibility', 'visible' );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_manage_stock', 'no' );
	update_post_meta( $product_id, '_sold_individually', 'yes' );
	update_post_meta( $product_id, '_tax_status', 'taxable' );
	update_post_meta( $product_id, $meta_key, (string) $source_id );

	return (int) $product_id;
}

function aura_spa_amelia_wc_apply_mapping( $product_id, $name, $price, $type, $source_id ) {
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return false;
	}

	$price = (float) $price;
	$regular_price = $price > 0 ? round( max( $price * 1.15, $price + 1 ), 2 ) : 0;
	$sale_price    = $price > 0 ? $price : 0;

	$product->set_name( $name );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_regular_price( (string) $regular_price );
	$product->set_sale_price( (string) $sale_price );
	$product->set_price( (string) $sale_price );
	$product->save();

	update_post_meta( $product_id, '_aura_amelia_mapped', '1' );
	update_post_meta( $product_id, '_aura_amelia_mapping_type', $type );
	update_post_meta( $product_id, '_aura_amelia_service_id', 'service' === $type ? (string) $source_id : '' );
	update_post_meta( $product_id, '_aura_amelia_package_id', 'package' === $type ? (string) $source_id : '' );
	update_post_meta( $product_id, '_aura_amelia_wc_checkout_product', '1' );

	return true;
}

function aura_spa_amelia_wc_decode_settings( $settings ) {
	if ( empty( $settings ) ) {
		return array();
	}

	$decoded = json_decode( $settings, true );

	return is_array( $decoded ) ? $decoded : array();
}

function aura_spa_amelia_wc_update_service_settings( $service_id, $product_id ) {
	global $wpdb;

	$service = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT settings FROM {$wpdb->prefix}amelia_services WHERE id = %d LIMIT 1",
			$service_id
		),
		ARRAY_A
	);

	if ( ! $service ) {
		return;
	}

	$settings = aura_spa_amelia_wc_decode_settings( $service['settings'] );
	$settings['payments'] = is_array( $settings['payments'] ?? null ) ? $settings['payments'] : array();
	$settings['payments']['wc'] = array_merge(
		array(
			'enabled'      => true,
			'page'         => 'cart',
			'onSiteIfFree' => false,
		),
		is_array( $settings['payments']['wc'] ?? null ) ? $settings['payments']['wc'] : array()
	);
	$settings['payments']['wc']['enabled'] = true;
	$settings['payments']['wc']['productId'] = (int) $product_id;
	$settings['payments']['wc']['page'] = 'cart';
	$settings['payments']['wc']['onSiteIfFree'] = false;

	$wpdb->update(
		$wpdb->prefix . 'amelia_services',
		array( 'settings' => wp_json_encode( $settings ) ),
		array( 'id' => (int) $service_id ),
		array( '%s' ),
		array( '%d' )
	);
}

function aura_spa_amelia_wc_update_package_settings( $package_id, $product_id ) {
	global $wpdb;

	$package = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT settings FROM {$wpdb->prefix}amelia_packages WHERE id = %d LIMIT 1",
			$package_id
		),
		ARRAY_A
	);

	if ( ! $package ) {
		return;
	}

	$settings = aura_spa_amelia_wc_decode_settings( $package['settings'] );
	$settings['payments'] = is_array( $settings['payments'] ?? null ) ? $settings['payments'] : array();
	$settings['payments']['wc'] = array_merge(
		array(
			'enabled'      => true,
			'page'         => 'cart',
			'onSiteIfFree' => false,
		),
		is_array( $settings['payments']['wc'] ?? null ) ? $settings['payments']['wc'] : array()
	);
	$settings['payments']['wc']['enabled'] = true;
	$settings['payments']['wc']['productId'] = (int) $product_id;
	$settings['payments']['wc']['page'] = 'cart';
	$settings['payments']['wc']['onSiteIfFree'] = false;

	$wpdb->update(
		$wpdb->prefix . 'amelia_packages',
		array( 'settings' => wp_json_encode( $settings ) ),
		array( 'id' => (int) $package_id ),
		array( '%s' ),
		array( '%d' )
	);
}
