<?php
/**
 * Activate Amelia WooCommerce checkout integration for this WordPress site.
 *
 * Usage:
 *   php scripts/activate_amelia_wc.php
 *   wp eval-file scripts/activate_amelia_wc.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

function aura_get_first_published_wc_product_id()
{
    $products = get_posts([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 10,
        'orderby'        => 'ID',
        'order'          => 'ASC',
        'fields'         => 'ids',
    ]);

    if (empty($products)) {
        throw new RuntimeException('No published WooCommerce products were found. Create a product in WooCommerce first.');
    }

    return (int) $products[0];
}

function aura_get_valid_wc_product_id($candidateId)
{
    $candidateId = (int) $candidateId;

    if ($candidateId > 0) {
        $post = get_post($candidateId);
        if ($post && get_post_type($candidateId) === 'product' && get_post_status($candidateId) === 'publish') {
            return $candidateId;
        }
    }

    return aura_get_first_published_wc_product_id();
}

try {
    $settingsJson = get_option('amelia_settings', '');
    $settings = $settingsJson ? json_decode($settingsJson, true) : [];

    if (!is_array($settings)) {
        $settings = [];
    }

    if (!isset($settings['payments']) || !is_array($settings['payments'])) {
        $settings['payments'] = [];
    }

    if (!isset($settings['payments']['wc']) || !is_array($settings['payments']['wc'])) {
        $settings['payments']['wc'] = [];
    }

    $productId = aura_get_valid_wc_product_id($settings['payments']['wc']['productId'] ?? 0);

    $settings['payments']['wc'] = array_merge([
        'enabled'      => false,
        'productId'    => '',
        'onSiteIfFree' => false,
        'page'         => 'cart',
        'dashboard'    => true,
        'checkoutData' => [
            'appointment' => '',
            'package'     => '',
            'event'       => '',
            'cart'        => '',
            'translations' => [
                'appointment' => null,
                'event'       => null,
                'package'     => null,
                'cart'        => '',
            ],
        ],
        'skipCheckoutGetValueProcessing' => true,
        'skipGetItemDataProcessing'      => true,
        'redirectPage'                   => 1,
        'bookMultiple'                   => false,
    ], $settings['payments']['wc']);

    $settings['payments']['wc']['enabled'] = true;
    $settings['payments']['wc']['productId'] = (string) $productId;
    $settings['payments']['wc']['onSiteIfFree'] = false;
    $settings['payments']['wc']['page'] = 'cart';
    $settings['payments']['wc']['dashboard'] = true;

    $encoded = wp_json_encode($settings);
    $updated = update_option('amelia_settings', $encoded);

    if ($updated === false) {
        fwrite(STDERR, "Failed to save Amelia settings.\n");
        exit(1);
    }

    echo "Amelia WooCommerce integration activated successfully.\n";
    echo "Enabled: true\n";
    echo "Product ID: {$productId}\n";
    echo "WooCommerce URL: " . admin_url('edit.php?post_type=product') . "\n";
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    exit(1);
}
