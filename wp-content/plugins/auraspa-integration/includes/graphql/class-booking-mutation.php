<?php

if (!defined('ABSPATH')) {
    exit;
}

class AuraSpa_Integration_Booking_Mutation {
    private static function create_bookpro_order_for_woocommerce_order($woo_order, $service_id, $amount, $customer_name, $customer_email, $appointment_date, $appointment_time, $phone, $notes) {
        if (!$woo_order || !method_exists($woo_order, 'get_id') || !function_exists('obp_get_order')) {
            return 0;
        }

        $meta_prefix = defined('OBP_METABOX') ? OBP_METABOX : 'obp_mb_';
        $vendor_id = 0;

        if (function_exists('obp_get_service')) {
            $service = obp_get_service((int) $service_id);
            if ($service && method_exists($service, 'get_vendor_id')) {
                $vendor_id = (int) $service->get_vendor_id();
            }
        }

        $order_id = wp_insert_post([
            'post_type' => 'obp_order',
            'post_status' => 'publish',
            'post_title' => '#' . $woo_order->get_id() . ' ' . trim((string) $customer_name),
            'meta_input' => [
                $meta_prefix . 'vendor_id' => $vendor_id,
                $meta_prefix . 'business_id' => 0,
                $meta_prefix . 'order_status' => 'obp_pending',
                $meta_prefix . 'customer_id' => get_current_user_id() ?: 0,
                $meta_prefix . 'customer_name' => trim((string) $customer_name),
                $meta_prefix . 'customer_email' => trim((string) $customer_email),
                $meta_prefix . 'customer_phone' => trim((string) $phone),
                $meta_prefix . 'customer_note' => trim((string) $notes),
                $meta_prefix . 'woo_order_id' => (int) $woo_order->get_id(),
                $meta_prefix . 'payment_method' => $woo_order->get_payment_method_title() ?: 'GraphQL',
                $meta_prefix . 'payment_gateway' => 'Woocommerce',
                $meta_prefix . 'total' => (float) $amount,
                $meta_prefix . 'has_varies' => 'no',
                $meta_prefix . 'subtotal' => (float) $amount,
                $meta_prefix . 'system_fee' => 0,
                $meta_prefix . 'tax_amount' => 0,
                $meta_prefix . 'discount' => 0,
                $meta_prefix . 'date_created' => current_time('timestamp'),
                $meta_prefix . 'commission' => 0,
                $meta_prefix . 'vendor_total' => (float) $amount,
                $meta_prefix . 'start_date' => strtotime($appointment_date . ' ' . $appointment_time),
                $meta_prefix . 'balance_status' => 'obp_pending',
                $meta_prefix . 'allow_change' => 'yes',
            ],
        ], true);

        if (is_wp_error($order_id)) {
            return 0;
        }

        $bookpro_order = obp_get_order($order_id);
        if ($bookpro_order && method_exists($bookpro_order, 'set_key')) {
            $bookpro_order->set_key();
        }

        do_action('auraspa_bookpro_order_created', $order_id, $woo_order->get_id(), $service_id, $amount, $appointment_date, $appointment_time, $customer_name, $customer_email);

        return (int) $order_id;
    }

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
            'outputFields' => [
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
            'mutateAndGetPayload' => static function ($input, $context, $info) {
                $customer_name = trim((string) ($input['customerName'] ?? ''));
                $customer_email = trim((string) ($input['customerEmail'] ?? ''));
                $service_id = trim((string) ($input['serviceId'] ?? ''));
                $appointment_date = trim((string) ($input['appointmentDate'] ?? ''));
                $appointment_time = trim((string) ($input['appointmentTime'] ?? ''));
                $amount = floatval($input['amount'] ?? 0);

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

                $order->set_customer_note((string) ($input['notes'] ?? ''));
                $order->set_billing_first_name($first_name);
                $order->set_billing_last_name($last_name);
                $order->set_billing_email($customer_email);
                $order->set_billing_phone((string) ($input['phone'] ?? ''));

                $product = wc_get_product($service_id);
                if ($product) {
                    $order->add_product($product, 1, [
                        'subtotal' => $amount,
                        'total' => $amount,
                    ]);
                }

                $order->calculate_totals();
                $order->update_meta_data('_aura_service_id', $service_id);
                $order->update_meta_data('_aura_booking_date', $appointment_date);
                $order->update_meta_data('_aura_booking_time', $appointment_time);
                $order->update_meta_data('_aura_booking_customer_name', $customer_name);
                $order->update_meta_data('_aura_booking_customer_email', $customer_email);
                $order->update_meta_data('_aura_booking_source', 'graphql');
                $order->save();

                self::create_bookpro_order_for_woocommerce_order(
                    $order,
                    $service_id,
                    $amount,
                    $customer_name,
                    $customer_email,
                    $appointment_date,
                    $appointment_time,
                    (string) ($input['phone'] ?? ''),
                    (string) ($input['notes'] ?? '')
                );

                do_action('auraspa_booking_order_created', $order->get_id(), $input);

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
