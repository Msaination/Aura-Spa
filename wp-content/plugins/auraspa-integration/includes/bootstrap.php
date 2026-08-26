<?php

if (!defined('ABSPATH')) {
    exit;
}

class AuraSpa_Integration_Bootstrap {
    public static function init() {
        self::load_dependencies();
        self::register_hooks();
    }

    private static function load_dependencies() {
        require_once AURASPA_INTEGRATION_PLUGIN_DIR . 'includes/bookpro/class-bookpro-sync.php';
        require_once AURASPA_INTEGRATION_PLUGIN_DIR . 'includes/graphql/class-graphql-schema.php';
        require_once AURASPA_INTEGRATION_PLUGIN_DIR . 'includes/graphql/class-booking-mutation.php';
    }

    private static function register_hooks() {
        add_action('graphql_register_types', [AuraSpa_Integration_GraphQL_Schema::class, 'register'], 10);
        add_action('graphql_register_types', [AuraSpa_Integration_Booking_Mutation::class, 'register'], 11);

        add_action('woocommerce_order_status_changed', [AuraSpa_Integration_BookPro_Sync::class, 'sync_order_status_to_bookpro'], 10, 4);
    }
}
