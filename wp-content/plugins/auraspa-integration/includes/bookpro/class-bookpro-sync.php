<?php

if (!defined('ABSPATH')) {
    exit;
}

class AuraSpa_Integration_BookPro_Sync {
    public static function get_services($limit = 20) {
        if (!function_exists('obp_get_service')) {
            return [];
        }

        $args = [
            'post_type' => 'obp_service',
            'post_status' => 'publish',
            'posts_per_page' => absint($limit),
            'orderby' => 'title',
            'order' => 'ASC',
            'fields' => 'ids',
        ];

        $service_ids = get_posts($args);

        if (empty($service_ids)) {
            return [];
        }

        $items = [];

        foreach ($service_ids as $service_id) {
            $service = obp_get_service($service_id);

            if (!$service || !method_exists($service, 'get_title')) {
                continue;
            }

            $items[] = [
                'id' => (string) $service->get_id(),
                'name' => $service->get_title(),
                'description' => method_exists($service, 'get_description') ? $service->get_description() : '',
                'price' => method_exists($service, 'get_price') ? (float) $service->get_price() : 0,
                'duration' => method_exists($service, 'get_duration_text') ? $service->get_duration_text() : '',
                'slug' => sanitize_title($service->get_title()),
            ];
        }

        return $items;
    }

    public static function find_bookpro_order_by_woo_order_id($woo_order_id) {
        if (!class_exists('BookPro\\Order\\OBP_Order')) {
            return 0;
        }

        $order_ids = \BookPro\Order\OBP_Order::get_order_ids_by_woo_order_id($woo_order_id);

        if (empty($order_ids)) {
            return 0;
        }

        return (int) $order_ids[0];
    }

    public static function sync_order_status_to_bookpro($order_id, $old_status, $new_status, $order) {
        if (!$order instanceof WC_Order) {
            return;
        }

        $service_id = $order->get_meta('_aura_service_id', true);

        if (empty($service_id)) {
            return;
        }

        $bookpro_order_id = self::find_bookpro_order_by_woo_order_id($order->get_id());
        $mapped_status = self::map_wc_status_to_bookpro_status($new_status);

        if (!$mapped_status) {
            return;
        }

        if (function_exists('obp_get_order') && $bookpro_order_id) {
            $bookpro_order = obp_get_order($bookpro_order_id);

            if ($bookpro_order && method_exists($bookpro_order, 'set_order_status')) {
                $bookpro_order->set_order_status($mapped_status);
            }
        }

        do_action('auraspa_bookpro_booking_synced', [
            'woo_order_id' => $order->get_id(),
            'bookpro_order_id' => $bookpro_order_id,
            'service_id' => $service_id,
            'wc_status' => $new_status,
            'bookpro_status' => $mapped_status,
            'customer_email' => $order->get_billing_email(),
            'customer_name' => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
            'date' => $order->get_meta('_aura_booking_date', true),
            'time' => $order->get_meta('_aura_booking_time', true),
            'total' => $order->get_total(),
        ]);
    }

    public static function map_wc_status_to_bookpro_status($wc_status) {
        $map = [
            'processing' => 'obp_processing',
            'completed' => 'obp_completed',
            'cancelled' => 'obp_cancelled',
            'refunded' => 'obp_refunded',
            'failed' => 'obp_cancelled',
        ];

        return $map[$wc_status] ?? null;
    }
}
