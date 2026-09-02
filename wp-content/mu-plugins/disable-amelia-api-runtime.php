<?php
/**
 * Plugin Name: Restrict Amelia external API runtime
 * Description: Keep the local Amelia WordPress AJAX runtime enabled, while blocking only remote Amelia/Melograno network requests.
 */

$ameliaBlockedHosts = [
    'wpamelia.com',
    'middleware.wpamelia.com',
    'smsapi.wpamelia.com',
    'store.melograno.io',
    'bi.melograno.io',
];

add_filter('pre_http_request', static function ($preempt, $parsed_args, $url) use ($ameliaBlockedHosts) {
    $target = (string) $url;

    foreach ($ameliaBlockedHosts as $host) {
        if (stripos($target, $host) !== false) {
            return new WP_Error('amelia_api_disabled', 'Amelia external API calls are disabled in this environment.');
        }
    }

    return $preempt;
}, 10, 3);

add_filter('http_request_args', static function ($args, $url) use ($ameliaBlockedHosts) {
    $target = (string) $url;

    foreach ($ameliaBlockedHosts as $host) {
        if (stripos($target, $host) !== false) {
            return new WP_Error('amelia_api_disabled', 'Amelia external API calls are disabled in this environment.');
        }
    }

    return $args;
}, 10, 2);

add_action('init', static function () {
    if (!class_exists('AmeliaBooking\\Plugin')) {
        return;
    }

    add_action('wp_ajax_wpamelia_api', ['AmeliaBooking\\Plugin', 'wpAmeliaApiCall']);
    add_action('wp_ajax_nopriv_wpamelia_api', ['AmeliaBooking\\Plugin', 'wpAmeliaApiCall']);
}, 0);
