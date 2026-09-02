<?php
/**
 * Sync Amelia service rows to exact WooCommerce product titles without enabling the
 * global Amelia WooCommerce checkout flow.
 *
 * Usage:
 *   php scripts/sync_amelia_service_wc_product_mapping.php
 *   php scripts/sync_amelia_service_wc_product_mapping.php --dry-run
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

function aura_wc_title_sync_parse_cli($argv = [])
{
    $options = [
        'dry_run' => false,
        'verbose' => false,
    ];

    foreach ($argv as $arg) {
        if ($arg === '--dry-run' || $arg === '-n') {
            $options['dry_run'] = true;
            continue;
        }

        if ($arg === '--verbose' || $arg === '-v') {
            $options['verbose'] = true;
        }
    }

    return $options;
}

function aura_wc_title_sync_lookup_product_id_by_title($serviceName)
{
    global $wpdb;

    $title = trim((string) $serviceName);
    if ($title === '') {
        return null;
    }

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN ('publish', 'draft', 'pending') AND LOWER(TRIM(post_title)) = LOWER(TRIM(%s)) LIMIT 1",
            'product',
            $title
        ),
        ARRAY_A
    );

    return $row && isset($row['ID']) ? (int) $row['ID'] : null;
}

function aura_wc_title_sync_normalize_settings($settings)
{
    if (!is_array($settings)) {
        return [];
    }

    return $settings;
}

$cliOptions = aura_wc_title_sync_parse_cli($argv ?? []);

global $wpdb;

$serviceTable = $wpdb->prefix . 'amelia_services';
$services = $wpdb->get_results(
    "SELECT id, name, settings FROM {$serviceTable} ORDER BY id",
    ARRAY_A
);

$updated = 0;
$matched = 0;
$missing = 0;

foreach ($services as $service) {
    $serviceId = (int) ($service['id'] ?? 0);
    $serviceName = trim((string) ($service['name'] ?? ''));

    if ($serviceId <= 0 || $serviceName === '') {
        continue;
    }

    $productId = aura_wc_title_sync_lookup_product_id_by_title($serviceName);
    if ($productId === null) {
        $missing++;
        if (!empty($cliOptions['verbose'])) {
            echo "NO_MATCH | service_id={$serviceId} | name={$serviceName}\n";
        }
        continue;
    }

    $matched++;

    $settings = aura_wc_title_sync_normalize_settings(json_decode((string) ($service['settings'] ?: '{}'), true));
    if (!isset($settings['payments']) || !is_array($settings['payments'])) {
        $settings['payments'] = [];
    }
    if (!isset($settings['payments']['wc']) || !is_array($settings['payments']['wc'])) {
        $settings['payments']['wc'] = [];
    }

    $settings['payments']['wc']['productId'] = $productId;
    $settings['payments']['wc']['enabled'] = false;
    $settings['payments']['wc']['page'] = $settings['payments']['wc']['page'] ?? 'cart';
    $settings['payments']['wc']['onSiteIfFree'] = $settings['payments']['wc']['onSiteIfFree'] ?? false;

    $json = function_exists('wp_json_encode') ? wp_json_encode($settings) : json_encode($settings);

    if ($cliOptions['dry_run']) {
        echo "DRY_RUN | service_id={$serviceId} | product_id={$productId} | name={$serviceName}\n";
        continue;
    }

    $wpdb->update(
        $serviceTable,
        ['settings' => $json],
        ['id' => $serviceId],
        ['%s'],
        ['%d']
    );

    $updated++;

    if (!empty($cliOptions['verbose'])) {
        echo "UPDATED | service_id={$serviceId} | product_id={$productId} | name={$serviceName}\n";
    }
}

$settingsOption = get_option('amelia_settings', '');
$settings = is_string($settingsOption) ? json_decode($settingsOption, true) : $settingsOption;
if (!is_array($settings)) {
    $settings = [];
}
if (!isset($settings['payments']) || !is_array($settings['payments'])) {
    $settings['payments'] = [];
}
if (!isset($settings['payments']['wc']) || !is_array($settings['payments']['wc'])) {
    $settings['payments']['wc'] = [];
}

$settings['payments']['wc']['productId'] = null;
$settings['payments']['wc']['enabled'] = false;
$settings['payments']['wc']['page'] = $settings['payments']['wc']['page'] ?? 'cart';
$settings['payments']['wc']['onSiteIfFree'] = $settings['payments']['wc']['onSiteIfFree'] ?? false;

if (!$cliOptions['dry_run']) {
    update_option('amelia_settings', wp_json_encode($settings));
}

fwrite(STDOUT, "SERVICE_COUNT=" . count($services) . "\n");
fwrite(STDOUT, "MATCHED=" . $matched . "\n");
fwrite(STDOUT, "UPDATED=" . $updated . "\n");
fwrite(STDOUT, "MISSING=" . $missing . "\n");
fwrite(STDOUT, "GLOBAL_WC_PRODUCT_ID_RESET_TO_NULL=yes\n");
fwrite(STDOUT, "GLOBAL_WC_ENABLED=false\n");
