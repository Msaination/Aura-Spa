<?php
/**
 * Plugin Name: AuraBookPro
 * Plugin URI: https://example.com/aurabookpro
 * Description: Custom booking flow with service catalog, staff/provider list, scheduling, validation, WooCommerce mapping, and admin booking management.
 * Version: 0.1.0
 * Author: Msiko
 * Text Domain: aurabookpro
 * Requires at least: 6.0
 * Tested up to: 6.6
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/includes/class-aurabookpro.php';

if (!function_exists('aurabookpro')) {
    function aurabookpro() {
        static $instance = null;

        if (null === $instance) {
            $instance = new AuraBookPro();
        }

        return $instance;
    }
}

add_action('plugins_loaded', 'aurabookpro');
