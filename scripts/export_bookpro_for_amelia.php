<?php
/**
 * Export BookPro services/plans into Amelia-ready CSV files.
 *
 * Usage:
 *   php scripts/export_bookpro_for_amelia.php
 *
 * Output folder:
 *   wp-content/uploads/bookpro-export/
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

$meta_prefix = defined('OBP_METABOX') ? OBP_METABOX : 'obp_mb_';
$export_dir = dirname(__DIR__) . '/wp-content/uploads/bookpro-export';
if (!is_dir($export_dir)) {
    if (!mkdir($export_dir, 0775, true) && !is_dir($export_dir)) {
        fwrite(STDERR, "Unable to create export directory: {$export_dir}\n");
        exit(1);
    }
}

function bp_export_normalize_string_array($value) {
    if (is_array($value)) {
        $items = $value;
    } elseif (is_string($value)) {
        $items = preg_split('/[|,]/', $value);
    } else {
        $items = [];
    }

    $clean = [];
    foreach ($items as $item) {
        $trimmed = trim((string) $item);
        if ($trimmed !== '') {
            $clean[] = $trimmed;
        }
    }

    return $clean;
}

function bp_export_parse_duration_minutes($service_id) {
    $meta_prefix = defined('OBP_METABOX') ? OBP_METABOX : 'obp_mb_';
    $hour = (int) get_post_meta($service_id, $meta_prefix . 'hour', true);
    $minute = (int) get_post_meta($service_id, $meta_prefix . 'minute', true);
    $duration = (int) get_post_meta($service_id, $meta_prefix . 'duration', true);

    if ($duration > 0) {
        return (int) $duration;
    }

    $total = ($hour * 60) + $minute;
    return $total > 0 ? $total : 0;
}

function bp_export_service_plan_ids($service_id) {
    $meta_prefix = defined('OBP_METABOX') ? OBP_METABOX : 'obp_mb_';
    $vendor_id = (int) get_post_meta($service_id, $meta_prefix . 'vendor_id', true);

    if (class_exists('OBP_Plan') && method_exists('OBP_Plan', 'get_plan_ids_by_service_id')) {
        $ids = OBP_Plan::get_plan_ids_by_service_id($vendor_id, $service_id);
        if (is_array($ids)) {
            return array_map('intval', $ids);
        }
    }

    $plans = get_posts([
        'post_type' => 'obp_plan',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => [
            [
                'key' => $meta_prefix . 'service_ids',
                'value' => (string) $service_id,
                'compare' => 'LIKE',
            ],
        ],
    ]);

    return array_map('intval', $plans);
}

function bp_export_plan_service_ids($plan_id) {
    $meta_prefix = defined('OBP_METABOX') ? OBP_METABOX : 'obp_mb_';
    $raw = get_post_meta($plan_id, $meta_prefix . 'service_ids', true);
    $ids = bp_export_normalize_string_array($raw);
    $parsed = [];
    foreach ($ids as $id) {
        $num = intval($id);
        if ($num > 0) {
            $parsed[] = $num;
        }
    }
    return $parsed;
}

function bp_export_write_csv($path, $headers, $rows) {
    $fh = fopen($path, 'w');
    if ($fh === false) {
        throw new RuntimeException("Unable to open file for writing: {$path}");
    }

    fputcsv($fh, $headers);
    foreach ($rows as $row) {
        $normalized = [];
        foreach ($headers as $header) {
            $normalized[] = isset($row[$header]) ? $row[$header] : '';
        }
        fputcsv($fh, $normalized);
    }

    fclose($fh);
}

$service_posts = get_posts([
    'post_type' => 'obp_service',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
    'fields' => 'ids',
]);

$service_rows = [];
$mapped_rows = [];
$plan_rows = [];

foreach ($service_posts as $service_id) {
    $service_id = (int) $service_id;
    $title = get_the_title($service_id);
    $status = get_post_status($service_id);
    $vendor_id = (int) get_post_meta($service_id, $meta_prefix . 'vendor_id', true);
    $category_terms = wp_get_post_terms($service_id, 'obp_service_category', ['fields' => 'names']);
    $category_names = is_array($category_terms) ? implode('|', $category_terms) : '';
    $staff_ids = bp_export_normalize_string_array(get_post_meta($service_id, $meta_prefix . 'staff_ids', true));
    $plan_ids = bp_export_service_plan_ids($service_id);
    $duration_minutes = bp_export_parse_duration_minutes($service_id);
    $price = get_post_meta($service_id, $meta_prefix . 'price', true);
    $price = $price === '' ? 0 : (float) $price;

    $service_rows[] = [
        'service_id' => $service_id,
        'service_title' => $title,
        'status' => $status,
        'vendor_id' => $vendor_id,
        'category_names' => $category_names,
        'duration_minutes' => $duration_minutes,
        'price' => $price,
        'staff_ids' => implode('|', $staff_ids),
        'plan_ids' => implode('|', $plan_ids),
        'plan_count' => count($plan_ids),
        'source_link' => get_permalink($service_id),
    ];

    $mapped_rows[] = [
        'bookpro_service_id' => $service_id,
        'bookpro_service_name' => $title,
        'bookpro_category' => $category_names,
        'vendor_id' => $vendor_id,
        'amelia_category' => '',
        'amelia_provider_name' => '',
        'amelia_service_name' => $title,
        'amelia_duration_minutes' => $duration_minutes,
        'amelia_price' => $price,
        'notes' => 'Map this BookPro service to the correct Amelia category/provider.'
    ];
}

$plan_posts = get_posts([
    'post_type' => 'obp_plan',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
    'fields' => 'ids',
]);

foreach ($plan_posts as $plan_id) {
    $plan_id = (int) $plan_id;
    $vendor_id = (int) get_post_meta($plan_id, $meta_prefix . 'vendor_id', true);
    $service_ids = bp_export_plan_service_ids($plan_id);
    $start_date = get_post_meta($plan_id, $meta_prefix . 'start_date', true);
    $end_date = get_post_meta($plan_id, $meta_prefix . 'end_date', true);
    $time_type = get_post_meta($plan_id, $meta_prefix . 'time_type', true);
    $status = get_post_meta($plan_id, $meta_prefix . 'status', true);
    $title = get_the_title($plan_id);

    $plan_rows[] = [
        'plan_id' => $plan_id,
        'plan_title' => $title,
        'vendor_id' => $vendor_id,
        'service_ids' => implode('|', $service_ids),
        'service_count' => count($service_ids),
        'start_date' => $start_date !== '' ? date('Y-m-d', (int) $start_date) : '',
        'end_date' => $end_date !== '' ? date('Y-m-d', (int) $end_date) : '',
        'time_type' => $time_type,
        'status' => $status,
    ];
}

$service_headers = [
    'service_id',
    'service_title',
    'status',
    'vendor_id',
    'category_names',
    'duration_minutes',
    'price',
    'staff_ids',
    'plan_ids',
    'plan_count',
    'source_link',
];

$plan_headers = [
    'plan_id',
    'plan_title',
    'vendor_id',
    'service_ids',
    'service_count',
    'start_date',
    'end_date',
    'time_type',
    'status',
];

$mapping_headers = [
    'bookpro_service_id',
    'bookpro_service_name',
    'bookpro_category',
    'vendor_id',
    'amelia_category',
    'amelia_provider_name',
    'amelia_service_name',
    'amelia_duration_minutes',
    'amelia_price',
    'notes',
];

$service_csv = $export_dir . '/bookpro_services_export.csv';
$plan_csv = $export_dir . '/bookpro_plans_export.csv';
$mapping_csv = $export_dir . '/bookpro_amelia_mapping.csv';

bp_export_write_csv($service_csv, $service_headers, $service_rows);
bp_export_write_csv($plan_csv, $plan_headers, $plan_rows);
bp_export_write_csv($mapping_csv, $mapping_headers, $mapped_rows);

fwrite(STDOUT, "BookPro export complete.\n");
fwrite(STDOUT, "Services: " . count($service_rows) . "\n");
fwrite(STDOUT, "Plans: " . count($plan_rows) . "\n");
fwrite(STDOUT, "Export directory: {$export_dir}\n");
fwrite(STDOUT, "Files:\n");
fwrite(STDOUT, " - {$service_csv}\n");
fwrite(STDOUT, " - {$plan_csv}\n");
fwrite(STDOUT, " - {$mapping_csv}\n");
