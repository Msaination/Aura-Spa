<?php

if (!defined('ABSPATH')) {
    exit;
}

class AuraSpa_Integration_Booking_Mutation {
    public static function register() {
        register_graphql_mutation('createBooking', [
            'description' => __('Create a WooCommerce booking order and redirect the customer to PayFast.', 'auraspa-integration'),
            'inputFields' => [
                'customerName' => ['type' => ['non_null' => 'String']],
                'customerEmail' => ['type' => ['non_null' => 'String']],
                'phone' => ['type' => 'String'],
                'serviceId' => ['type' => ['non_null' => 'String']],
                'appointmentDate' => ['type' => ['non_null' => 'String']],
                'appointmentTime' => ['type' => ['non_null' => 'String']],
                'notes' => ['type' => 'String'],
                'amount' => ['type' => ['non_null' => 'Float']],
            ],
            'outputType' => 'AuraSpaBooking',
            'resolve' => static function ($root, $args) {
                $customer_name = trim((string) ($args['customerName'] ?? ''));
                $customer_email = trim((string) ($args['customerEmail'] ?? ''));
                $service_id = trim((string) ($args['serviceId'] ?? ''));
                $appointment_date = trim((string) ($args['appointmentDate'] ?? ''));
                $appointment_time = trim((string) ($args['appointmentTime'] ?? ''));
                $amount = floatval($args['amount'] ?? 0);

                if (empty($customer_name) || empty($customer_email) || empty($service_id) || empty($appointment_date) || empty($appointment_time)) {
                    throw new \GraphQL\Error\Error(__('Missing required booking input.', 'auraspa-integration'));
                }

                $order = wc_create_order();

                if (!$order) {
                    throw new \GraphQL\Error\Error(__('Unable to create the booking order.', 'auraspa-integration'));
                }

                $customer_name_parts = preg_split('/\\s+/', $customer_name, 2);
                $first_name = $customer_name_parts[0] ?? '';
                $last_name = $customer_name_parts[1] ?? '';

                $order->set_customer_note($args['notes'] ?? '');
                $order->set_billing_first_name($first_name);
                $order->set_billing_last_name($last_name);
                $order->set_billing_email($customer_email);
                $order->set_billing_phone((string) ($args['phone'] ?? ''));
                $order->add_product(wc_get_product($service_id), 1, [
                    'subtotal' => $amount,
                    'total' => $amount,
                ]);
                $order->calculate_totals();

                $order->update_meta_data('_aura_service_id', $service_id);
                $order->update_meta_data('_aura_booking_date', $appointment_date);
                $order->update_meta_data('_aura_booking_time', $appointment_time);
                $order->update_meta_data('_aura_booking_customer_name', $customer_name);
                $order->update_meta_data('_aura_booking_customer_email', $customer_email);
                $order->update_meta_data('_aura_booking_source', 'graphql');
                $order->save();

                do_action('auraspa_booking_order_created', $order->get_id(), $args);

                return [
                    'id' => (string) $order->get_id(),
                    'orderId' => $order->get_id(),
                    'status' => $order->get_status(),
                    'serviceId' => (string) $service_id,
                    'amount' => floatval($amount),
                    'customerName' => $customer_name,
                    'customerEmail' => $customer_email,
                    'appointmentDate' => $appointment_date,
                    'appointmentTime' => $appointment_time,
                    'checkoutUrl' => $order->get_checkout_payment_url(),
                ];
            },
        ]);
    }
}
