<?php

if (!defined('ABSPATH')) {
    exit;
}

class AuraSpa_Integration_GraphQL_Schema {
    public static function register() {
        register_graphql_object_type('AuraSpaService', [
            'description' => __('A spa treatment service from BookPro.', 'auraspa-integration'),
            'fields' => [
                'id' => ['type' => 'ID'],
                'name' => ['type' => 'String'],
                'description' => ['type' => 'String'],
                'price' => ['type' => 'Float'],
                'duration' => ['type' => 'String'],
                'slug' => ['type' => 'String'],
            ],
        ]);

        register_graphql_object_type('AuraSpaBooking', [
            'description' => __('Booking response object for the Aura Spa frontend.', 'auraspa-integration'),
            'fields' => [
                'id' => ['type' => 'ID'],
                'orderId' => ['type' => 'Int'],
                'status' => ['type' => 'String'],
                'serviceId' => ['type' => 'String'],
                'amount' => ['type' => 'Float'],
                'customerName' => ['type' => 'String'],
                'customerEmail' => ['type' => 'String'],
                'appointmentDate' => ['type' => 'String'],
                'appointmentTime' => ['type' => 'String'],
                'checkoutUrl' => ['type' => 'String'],
            ],
        ]);

        register_graphql_field('RootQuery', 'auraSpaServices', [
            'type' => ['list_of' => 'AuraSpaService'],
            'description' => __('List available spa services from BookPro.', 'auraspa-integration'),
            'args' => [
                'limit' => ['type' => 'Int'],
            ],
            'resolve' => static function ($root, $args) {
                $limit = !empty($args['limit']) ? absint($args['limit']) : 20;

                if (!class_exists('AuraSpa_Integration_BookPro_Sync')) {
                    return [];
                }

                $services = AuraSpa_Integration_BookPro_Sync::get_services($limit);

                return array_map(static function ($service) {
                    return [
                        'id' => (string) $service['id'],
                        'name' => $service['name'],
                        'description' => $service['description'],
                        'price' => floatval($service['price']),
                        'duration' => $service['duration'],
                        'slug' => $service['slug'],
                    ];
                }, $services);
            },
        ]);

        register_graphql_field('RootQuery', 'auraSpaBooking', [
            'type' => 'AuraSpaBooking',
            'description' => __('Fetch a booking by WooCommerce order ID.', 'auraspa-integration'),
            'args' => [
                'orderId' => ['type' => 'Int'],
            ],
            'resolve' => static function ($root, $args) {
                $order_id = !empty($args['orderId']) ? absint($args['orderId']) : 0;

                if (!$order_id) {
                    return null;
                }

                $order = wc_get_order($order_id);

                if (!$order) {
                    return null;
                }

                return [
                    'id' => (string) $order->get_id(),
                    'orderId' => $order->get_id(),
                    'status' => $order->get_status(),
                    'serviceId' => (string) $order->get_meta('_aura_service_id', true),
                    'amount' => floatval($order->get_total()),
                    'customerName' => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
                    'customerEmail' => $order->get_billing_email(),
                    'appointmentDate' => $order->get_meta('_aura_booking_date', true),
                    'appointmentTime' => $order->get_meta('_aura_booking_time', true),
                    'checkoutUrl' => '',
                ];
            },
        ]);
    }
}
