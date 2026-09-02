<?php
/**
 * Import treatment menu CSV into WooCommerce products and BookPro services.
 *
 * Usage:
 *   studio wp eval-file scripts/import_treatment_menu.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

$source = __DIR__ . '/../frontend/src/data/treatment-menu-import.csv';

if (!file_exists($source)) {
    fwrite(STDERR, "CSV file not found: {$source}\n");
    exit(1);
}

if (!class_exists('WC_Product')) {
    fwrite(STDERR, "WooCommerce is not available.\n");
    exit(1);
}

$meta_prefix = defined('OBP_METABOX') ? OBP_METABOX : 'obp_mb_';

function aura_ensure_bookpro_vendor_data($service_ids = []) {
    $vendor_id = 1;
    $service_ids = array_values(array_unique(array_filter(array_map('intval', (array) $service_ids))));

    $business_ids = get_posts([
        'post_type' => 'obp_business',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_query' => [[
            'key' => OBP_METABOX . 'vendor_id',
            'value' => $vendor_id,
        ]],
    ]);

    if (empty($business_ids)) {
        $business_id = wp_insert_post([
            'post_type' => 'obp_business',
            'post_status' => 'publish',
            'post_title' => 'Aura Spa',
            'post_name' => 'aura-spa',
            'post_content' => 'Default BookPro business for Aura Spa services.',
        ], true);

        if (!is_wp_error($business_id)) {
            update_post_meta($business_id, OBP_METABOX . 'vendor_id', $vendor_id);
        }
    }

    $plan_ids = get_posts([
        'post_type' => 'obp_plan',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_query' => [[
            'key' => OBP_METABOX . 'vendor_id',
            'value' => $vendor_id,
        ]],
    ]);

    if (empty($plan_ids)) {
        $plan_id = wp_insert_post([
            'post_type' => 'obp_plan',
            'post_status' => 'publish',
            'post_title' => '#1',
            'meta_input' => [
                OBP_METABOX . 'vendor_id' => $vendor_id,
                OBP_METABOX . 'status' => 'open',
                OBP_METABOX . 'service_type' => 'all_services',
                OBP_METABOX . 'time_type' => 'full_time',
                OBP_METABOX . 'service_ids' => implode('|', $service_ids),
                OBP_METABOX . 'start_date' => strtotime('-1 day'),
                OBP_METABOX . 'end_date' => strtotime('+1 year'),
                OBP_METABOX . 'times' => [],
            ],
        ], true);

        if (!is_wp_error($plan_id)) {
            wp_update_post(['ID' => $plan_id, 'post_title' => '#' . $plan_id]);
        }
    }
}

function aura_normalize_price($value) {
    if ($value === null || $value === '') {
        return 0;
    }

    $text = trim((string) $value);
    if ($text === '') {
        return 0;
    }

    $lower = strtolower($text);
    if (strpos($lower, 'on request') !== false || strpos($lower, 'request') !== false) {
        return 0;
    }

    $clean = preg_replace('/\s+/', '', $text);
    $clean = preg_replace('/^From/i', '', $clean);
    $clean = preg_replace('/^R/i', '', $clean);
    $clean = preg_replace('/[^0-9.,]/', '', $clean);
    $clean = str_replace(',', '.', $clean);

    if ($clean === '' || !is_numeric($clean)) {
        return 0;
    }

    return (float) $clean;
}

function aura_normalize_type($value, $category = '', $name = '') {
    $type = strtolower(trim((string) $value));
    if ($type === 'service' || $type === 'product') {
        return $type;
    }

    $category_norm = strtolower(trim((string) $category));
    if (in_array($category_norm, ['extras', 'spa facility'], true)) {
        return 'product';
    }

    $name_norm = strtolower(trim((string) $name));
    if (str_contains($name_norm, 'garden') || str_contains($name_norm, 'flowers') || str_contains($name_norm, 'chocolates') || str_contains($name_norm, 'balloons') || str_contains($name_norm, 'cake') || str_contains($name_norm, 'gift bag') || str_contains($name_norm, 'celebration setup')) {
        return 'product';
    }

    return 'service';
}

function aura_default_minimum_price($type, $category, $name) {
    $name_norm = strtolower(trim((string) $name));
    $category_norm = strtolower(trim((string) $category));

    $product_prices = [
        'fresh flowers' => 150,
        'chocolates' => 150,
        'balloons' => 120,
        'cupcakes or celebration cake' => 180,
        'gift bag' => 120,
        'personalized celebration setup' => 250,
        'garden, pool, restaurant, secure parking' => 0,
    ];

    $service_package_prices = [
        'all treatments 30minutes' => 180,
        'teen glow collection' => 250,
        'all treatments 45min' => 220,
        'body treatment' => 280,
        'body waxes' => 220,
        'face waxes' => 180,
        'intimate waxes' => 220,
        'brow tint' => 180,
        'hydration facials' => 290,
        'hydration facial' => 290,
        'dermaplaning' => 500,
        'paint toe' => 130,
        'paint toes' => 130,
        'brodie wrap' => 320,
        'brody wrap' => 320,
        'face wax' => 180,
        'body wax' => 220,
    ];

    if ($type === 'product') {
        if (isset($product_prices[$name_norm])) {
            return (float) $product_prices[$name_norm];
        }
        if ($category_norm === 'extras' || $name_norm === 'extras') {
            return 150.0;
        }
    }

    if ($type === 'service' && isset($service_package_prices[$name_norm])) {
        return (float) $service_package_prices[$name_norm];
    }

    return 0.0;
}

function aura_parse_duration($value) {
    if (empty($value)) {
        return ['hours' => 0, 'minutes' => 0];
    }

    $normalized = trim((string) $value);
    $normalized = str_replace('–', '-', $normalized);
    $normalized = preg_replace('/\s+/', ' ', $normalized);

    if (preg_match('/(\d+)\s*[-–]\s*(\d+)\s*(?:hrs?|hr|hours?|min|mins|minutes?)/i', $normalized, $m)) {
        $start = (int) $m[1];
        $end = (int) $m[2];
        $hours = intdiv($start, 60);
        $minutes = $start % 60;
        if ($start < 60 && $end >= 60) {
            $hours = 1;
            $minutes = $start;
        }
        return ['hours' => $hours, 'minutes' => $minutes];
    }

    if (preg_match('/(\d+)\s*(?:hr|hrs|hour|hours)/i', $normalized, $m)) {
        return ['hours' => (int) $m[1], 'minutes' => 0];
    }

    if (preg_match('/(\d+)\s*(?:min|mins|minute|minutes)/i', $normalized, $m)) {
        $minutes = (int) $m[1];
        $hours = intdiv($minutes, 60);
        $minutes = $minutes % 60;
        return ['hours' => $hours, 'minutes' => $minutes];
    }

    if (preg_match('/(\d+)/', $normalized, $m)) {
        $value_num = (int) $m[1];
        return ['hours' => intdiv($value_num, 60), 'minutes' => $value_num % 60];
    }

    return ['hours' => 0, 'minutes' => 0];
}

function aura_title_aliases($title) {
    $base = trim((string) $title);
    $aliases = [
        'hydration facials' => ['Hydration Facial', 'Hydration facials'],
        'hydration facial' => ['Hydration Facial', 'Hydration facials'],
        'paint toes' => ['Paint Toes', 'Paint Toe'],
        'paint toe' => ['Paint Toe', 'Paint Toes'],
        'dermaplaning' => ['Dermaplaning', 'Dermaplanning'],
        'dermaplanning' => ['Dermaplaning', 'Dermaplanning'],
        'brody wrap' => ['Brody Wrap', 'Brodie Wrap'],
        'brodie wrap' => ['Brody Wrap', 'Brodie Wrap'],
        'manicures women' => ['Manicures Women', 'MANICURES WOMEN'],
        'pedicures' => ['Pedicures', 'PEDICURES'],
        'eyebrow lemination' => ['Eyebrow Lemination', 'Eyebrow lemination', 'eyebrow lemination'],
        'all treatments 30minutes' => ['All Treatments 30 Minutes', 'All Treatments 30Minutes'],
        'all treatments 45min' => ['All Treatments 45 Minutes', 'All Treatments 45min'],
        'brightening facial' => ['Brightening Facial', 'Brightening facial'],
        'brightening facials' => ['Brightening Facial', 'Brightening facials'],
        'clearing facials' => ['Clearing Facials', 'Clearing Facial'],
        'body treatment' => ['Body Treatment'],
        'body waxes' => ['Body Waxes'],
        'face waxes' => ['Face Waxes'],
        'intimate waxes' => ['Intimate Waxes'],
        'all treatments 30 minutes' => ['All Treatments 30 Minutes', 'All Treatments 30Minutes'],
        'all treatments 45 minutes' => ['All Treatments 45 Minutes', 'All Treatments 45min'],
    ];

    $normalized = strtolower($base);
    if (isset($aliases[$normalized])) {
        return $aliases[$normalized];
    }

    return [$base];
}

function aura_find_post_by_title($post_type, $title) {
    $candidates = array_values(array_unique(array_filter(aura_title_aliases($title), function ($value) {
        return trim((string) $value) !== '';
    })));

    foreach ($candidates as $candidate) {
        $query = new WP_Query([
            'post_type'      => $post_type,
            'post_status'    => ['publish', 'draft', 'private'],
            'title'          => $candidate,
            'posts_per_page' => 1,
            'post__not_in'   => [],
            'fields'         => 'ids',
        ]);

        if (!empty($query->posts)) {
            return (int) $query->posts[0];
        }
    }

    return 0;
}

$created = 0;
$updated = 0;
$skipped = 0;
$all_service_ids = [];

if (($handle = fopen($source, 'r')) !== false) {
    $header = fgetcsv($handle);
    $header = array_map('trim', $header);
    $index = array_flip($header);

    while (($row = fgetcsv($handle)) !== false) {
        if (!$row) {
            continue;
        }

        $record = [];
        foreach ($header as $field) {
            $record[$field] = isset($row[$index[$field]]) ? $row[$index[$field]] : '';
        }

        $category = trim((string) ($record['category'] ?? ''));
        $name = trim((string) ($record['name'] ?? ''));
        $type = aura_normalize_type($record['type'] ?? '', $category, $name);

        $raw_price = $record['price'] ?? '';
        $price = aura_normalize_price($raw_price);
        if ($price <= 0) {
            $price = aura_default_minimum_price($type, $category, $name);
        }

        $duration = trim((string) ($record['duration'] ?? ''));
        $note = trim((string) ($record['note'] ?? ''));

        if ($name === '') {
            $skipped++;
            continue;
        }

        $product_id = aura_find_post_by_title('product', $name);
        $product_data = [
            'post_type'    => 'product',
            'post_status'  => 'publish',
            'post_title'   => $name,
            'post_content' => $note !== '' ? $note : ($category !== '' ? $category : 'Treatment service'),
        ];

        if ($product_id) {
            $product_data['ID'] = $product_id;
            wp_update_post($product_data);
            $updated++;
        } else {
            $product_id = wp_insert_post($product_data, true);
            if (is_wp_error($product_id)) {
                fwrite(STDERR, "Failed to create product '{$name}': {$product_id->get_error_message()}\n");
                continue;
            }
            $created++;
        }

        $product = wc_get_product($product_id);
        if ($product) {
            $product->set_name($name);
            $product->set_status('publish');
            $product->set_catalog_visibility('visible');
            $product->set_description($note !== '' ? $note : ($category !== '' ? $category : 'Treatment service'));
            $product->set_short_description($category !== '' ? $category : 'Treatment service');
            $product->set_regular_price((string) $price);
            $product->set_price((string) $price);
            $product->set_manage_stock(false);
            $product->set_stock_status('instock');
            $product->save();
        }

        if ($category !== '') {
            wp_set_object_terms($product_id, $category, 'product_cat', true);
        }

        update_post_meta($product_id, '_aura_treatment_category', $category);
        update_post_meta($product_id, '_aura_treatment_type', $type);
        update_post_meta($product_id, '_aura_import_source', 'treatment_menu_csv');
        update_post_meta($product_id, '_aura_treatment_duration', $duration);

        if ($type === 'product') {
            continue;
        }

        $service_id = aura_find_post_by_title('obp_service', $name);
        $duration_bits = aura_parse_duration($duration);
        $service_data = [
            'post_type'    => 'obp_service',
            'post_status'  => 'publish',
            'post_title'   => $name,
            'post_content' => $note !== '' ? $note : ($category !== '' ? $category : 'Treatment service'),
        ];

        if ($service_id) {
            $service_data['ID'] = $service_id;
            wp_update_post($service_data);
            $updated++;
        } else {
            $service_id = wp_insert_post($service_data, true);
            if (is_wp_error($service_id)) {
                fwrite(STDERR, "Failed to create BookPro service '{$name}': {$service_id->get_error_message()}\n");
                continue;
            }
            $created++;
        }

        update_post_meta($service_id, $meta_prefix . 'price', (float) $price);
        update_post_meta($service_id, $meta_prefix . 'hour', (int) $duration_bits['hours']);
        update_post_meta($service_id, $meta_prefix . 'minute', (int) $duration_bits['minutes']);
        update_post_meta($service_id, $meta_prefix . 'vendor_id', 1);
        update_post_meta($service_id, $meta_prefix . 'type', 0);
        update_post_meta($service_id, $meta_prefix . 'use_on', 'booking_date');
        update_post_meta($service_id, $meta_prefix . 'note_price', $note);
        update_post_meta($service_id, $meta_prefix . 'tax_class', 'standard');
        update_post_meta($service_id, $meta_prefix . 'staff_ids', []);
        update_post_meta($service_id, '_aura_woo_product_id', $product_id);
        update_post_meta($service_id, '_aura_treatment_category', $category);
        if ($category !== '') {
            wp_set_object_terms($service_id, $category, 'obp_service_category', true);
        }
        update_post_meta($service_id, '_aura_import_source', 'treatment_menu_csv');

        $all_service_ids[] = (int) $service_id;
    }

    fclose($handle);
    aura_ensure_bookpro_vendor_data($all_service_ids);
}

fwrite(STDOUT, "Imported treatment menu: created={$created}, updated={$updated}, skipped={$skipped}\n");
