<?php
/**
 * Plugin Name: Aura Spa Integration
 * Description: WordPress plugin for Aura Spa booking flow, BookPro sync, and GraphQL integration.
 * Version: 0.1.0
 * Author: Aura Spa
 * Text Domain: auraspa-integration
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('AURASPA_INTEGRATION_PLUGIN_FILE')) {
    define('AURASPA_INTEGRATION_PLUGIN_FILE', __FILE__);
}

if (!defined('AURASPA_INTEGRATION_PLUGIN_DIR')) {
    define('AURASPA_INTEGRATION_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

add_action('plugins_loaded', 'auraspa_integration_bootstrap', 20);

function auraspa_integration_bootstrap() {
    if (!function_exists('WC') || !class_exists('WC_Order')) {
        return;
    }

    require_once AURASPA_INTEGRATION_PLUGIN_DIR . 'includes/bootstrap.php';

    AuraSpa_Integration_Bootstrap::init();
}

register_activation_hook(__FILE__, 'auraspa_integration_activate');
function auraspa_integration_activate() {
    if (!function_exists('WC') || !class_exists('WC_Order')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(esc_html__('Aura Spa Integration requires WooCommerce to be active.', 'auraspa-integration'));
    }

    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'auraspa_integration_deactivate');
function auraspa_integration_deactivate() {
    flush_rewrite_rules();
}
