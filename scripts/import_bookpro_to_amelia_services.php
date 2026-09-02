<?php
/**
 * Production-safe Amelia import for the BookPro CSV seed file.
 *
 * Usage:
 *   php scripts/import_bookpro_to_amelia_services.php
 *   php scripts/import_bookpro_to_amelia_services.php --csv=/path/to/file.csv
 *   php scripts/import_bookpro_to_amelia_services.php --dry-run
 *   php scripts/import_bookpro_to_amelia_services.php --csv=./scripts/bookpro_service_seed.csv --dry-run
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

function aura_import_default_csv_path()
{
    return dirname(__DIR__) . '/scripts/bookpro_service_seed.csv';
}

function aura_import_parse_cli($argv = [])
{
    $options = [
        'csv' => aura_import_default_csv_path(),
        'dry_run' => false,
        'verbose' => false,
        'limit' => 0,
    ];

    foreach ($argv as $arg) {
        if (preg_match('/^--?csv=(.+)$/', $arg, $matches)) {
            $options['csv'] = trim($matches[1]);
            continue;
        }

        if ($arg === '--csv' || $arg === '-csv') {
            $next = next($argv);
            if ($next !== false) {
                $options['csv'] = trim((string) $next);
            }
            continue;
        }

        if ($arg === '--dry-run' || $arg === '-n') {
            $options['dry_run'] = true;
            continue;
        }

        if ($arg === '--verbose' || $arg === '-v') {
            $options['verbose'] = true;
            continue;
        }

        if (preg_match('/^--?limit=(\d+)$/', $arg, $matches)) {
            $options['limit'] = (int) $matches[1];
            continue;
        }

        if ($arg === '--limit' || $arg === '-l') {
            $next = next($argv);
            if ($next !== false) {
                $options['limit'] = (int) $next;
            }
        }
    }

    return $options;
}

function aura_import_log($message, $verbose = false)
{
    if ($verbose) {
        fwrite(STDOUT, $message . "\n");
    }
}

function aura_import_normalize_text($value)
{
    if ($value === null) {
        return '';
    }

    return trim((string) $value);
}

function aura_import_normalize_currency($value)
{
    $text = aura_import_normalize_text($value);
    if ($text === '') {
        return 0.0;
    }

    $text = str_replace([' ', 'R', 'r'], '', $text);
    $text = preg_replace('/[^0-9,\.\-]/', '', $text);
    if ($text === '') {
        return 0.0;
    }

    $text = str_replace(',', '', $text);

    return (float) $text;
}

function aura_import_parse_duration_minutes($value)
{
    $text = strtolower(aura_import_normalize_text($value));
    if ($text === '' || $text === 'add-on' || $text === 'addon' || $text === 'add on') {
        return 10;
    }

    $text = preg_replace('/\s+/', ' ', $text);

    if (preg_match('/^(\d+)\s*[–-]\s*(\d+)\s*(?:min|mins|minute|minutes|h|hr|hrs|hour|hours)?/i', $text, $m)) {
        $minutes = max((int) $m[1], (int) $m[2]);
        if ($minutes <= 0) {
            return 60;
        }
        return (int) ceil($minutes / 10) * 10;
    }

    if (preg_match('/^(\d+)\s*(?:h|hr|hrs|hour|hours)/i', $text, $m)) {
        $minutes = (int) $m[1] * 60;
        if ($minutes <= 0) {
            return 60;
        }
        return (int) ceil($minutes / 10) * 10;
    }

    if (preg_match('/^(\d+)\s*(?:min|mins|minute|minutes)/i', $text, $m)) {
        $minutes = (int) $m[1];
        if ($minutes <= 0) {
            return 60;
        }
        return (int) ceil($minutes / 10) * 10;
    }

    preg_match_all('/\d+/', $text, $matches);
    if (!empty($matches[0])) {
        $numbers = array_map('intval', $matches[0]);
        $minutes = max($numbers);
        if ($minutes <= 0) {
            return 60;
        }
        return (int) ceil($minutes / 10) * 10;
    }

    return 30;
}

function aura_import_set_default_appointment_status_pending()
{
    $settings = get_option('amelia_settings', []);
    if (is_string($settings)) {
        $decoded = json_decode($settings, true);
        $settings = is_array($decoded) ? $decoded : [];
    } elseif (!is_array($settings)) {
        $settings = [];
    }

    if (!isset($settings['general']) || !is_array($settings['general'])) {
        $settings['general'] = [];
    }

    $settings['general']['defaultAppointmentStatus'] = 'pending';
    $settings['general']['timeSlotLength'] = 600;

    $encoded = function_exists('wp_json_encode') ? wp_json_encode($settings) : json_encode($settings);
    update_option('amelia_settings', $encoded, true);
}

function aura_import_ensure_category($categoryName)
{
    global $wpdb;

    $name = aura_import_normalize_text($categoryName);
    if ($name === '') {
        $name = 'General';
    }

    $table = $wpdb->prefix . 'amelia_categories';
    $existing = $wpdb->get_var(
        $wpdb->prepare(
            'SELECT id FROM ' . $table . ' WHERE LOWER(name) = LOWER(%s) LIMIT 1',
            $name
        )
    );

    if ($existing) {
        return (int) $existing;
    }

    $position = (int) $wpdb->get_var('SELECT COALESCE(MAX(position), 0) + 1 FROM ' . $table);

    $wpdb->insert(
        $table,
        [
            'status' => 'visible',
            'name' => $name,
            'position' => $position,
            'color' => '#1788FB',
            'translations' => null,
            'pictureFullPath' => null,
            'pictureThumbPath' => null,
        ],
        ['%s', '%s', '%d', '%s', '%s', '%s', '%s']
    );

    return (int) $wpdb->insert_id;
}

function aura_import_get_employee_ids()
{
    global $wpdb;

    $table = $wpdb->prefix . 'amelia_users';
    $candidates = [
        ['Bon', 'Jovi'],
        ['Test', 'User'],
        ['Bob', 'Builder'],
    ];

    $ids = [];

    foreach ($candidates as $candidate) {
        $first = aura_import_normalize_text($candidate[0]);
        $last = aura_import_normalize_text($candidate[1]);

        if ($first === '') {
            continue;
        }

        $id = (int) $wpdb->get_var(
            $wpdb->prepare(
                'SELECT id FROM ' . $table . ' WHERE type = %s AND LOWER(firstName) = LOWER(%s) AND LOWER(lastName) = LOWER(%s) LIMIT 1',
                'provider',
                $first,
                $last
            )
        );

        if ($id > 0) {
            $ids[] = $id;
            continue;
        }

        $fallback = (int) $wpdb->get_var(
            $wpdb->prepare(
                'SELECT id FROM ' . $table . ' WHERE type = %s AND LOWER(firstName) = LOWER(%s) LIMIT 1',
                'provider',
                $first
            )
        );

        if ($fallback > 0) {
            $ids[] = $fallback;
        }
    }

    return array_values(array_unique($ids));
}

function aura_import_link_employees_to_service($serviceId)
{
    global $wpdb;

    $serviceId = (int) $serviceId;
    if ($serviceId <= 0) {
        return;
    }

    $table = $wpdb->prefix . 'amelia_providers_to_services';

    foreach (aura_import_get_employee_ids() as $employeeId) {
        $employeeId = (int) $employeeId;
        if ($employeeId <= 0) {
            continue;
        }

        $exists = (int) $wpdb->get_var(
            $wpdb->prepare(
                'SELECT id FROM ' . $table . ' WHERE userId = %d AND serviceId = %d LIMIT 1',
                $employeeId,
                $serviceId
            )
        );

        if ($exists > 0) {
            continue;
        }

        $wpdb->insert(
            $table,
            [
                'userId' => $employeeId,
                'serviceId' => $serviceId,
                'price' => 0,
                'minCapacity' => 1,
                'maxCapacity' => 1,
                'customPricing' => null,
            ],
            ['%d', '%d', '%f', '%d', '%d', '%s']
        );
    }
}

function aura_import_find_service_id($name, $categoryId = null)
{
    global $wpdb;

    $table = $wpdb->prefix . 'amelia_services';

    if ($categoryId) {
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                'SELECT id FROM ' . $table . ' WHERE LOWER(name) = LOWER(%s) AND categoryId = %d LIMIT 1',
                $name,
                (int) $categoryId
            )
        );
    }

    return (int) $wpdb->get_var(
        $wpdb->prepare(
            'SELECT id FROM ' . $table . ' WHERE LOWER(name) = LOWER(%s) LIMIT 1',
            $name
        )
    );
}

function aura_import_clean_row($row)
{
    $clean = [];
    foreach ($row as $key => $value) {
        $clean[(string) $key] = is_scalar($value) ? trim((string) $value) : (string) $value;
    }

    return $clean;
}

function aura_import_upsert_service($row, $dryRun = false)
{
    global $wpdb;

    $normalized = aura_import_clean_row($row);

    $type = strtolower(aura_import_normalize_text($normalized['type'] ?? 'service'));
    if ($type !== '' && $type !== 'service') {
        return ['created' => 0, 'updated' => 0, 'skipped' => 1, 'reason' => 'type_not_service'];
    }

    $name = aura_import_normalize_text($normalized['name'] ?? $normalized['amelia_service_name'] ?? $normalized['bookpro_service_name'] ?? '');
    if ($name === '') {
        return ['created' => 0, 'updated' => 0, 'skipped' => 1, 'reason' => 'empty_name'];
    }

    $categoryName = aura_import_normalize_text($normalized['catalog'] ?? $normalized['category'] ?? $normalized['amelia_category'] ?? $normalized['bookpro_category'] ?? '');
    if ($categoryName === '') {
        $categoryName = 'General';
    }

    $categoryId = aura_import_ensure_category($categoryName);
    $duration = aura_import_parse_duration_minutes($normalized['duration'] ?? $normalized['amelia_duration'] ?? 30);
    $price = aura_import_normalize_currency($normalized['price'] ?? $normalized['amelia_price'] ?? 0);
    $description = aura_import_normalize_text($normalized['note'] ?? $normalized['notes'] ?? '');

    $table = $wpdb->prefix . 'amelia_services';
    $serviceId = aura_import_find_service_id($name, $categoryId);

    $serviceData = [
        'name' => $name,
        'description' => $description,
        'color' => '#1788FB',
        'price' => $price,
        'status' => 'visible',
        'categoryId' => $categoryId,
        'minCapacity' => 1,
        'maxCapacity' => 1,
        'duration' => $duration,
        'timeBefore' => 0,
        'timeAfter' => 0,
        'bringingAnyone' => 1,
        'priority' => 'least_expensive',
        'pictureFullPath' => null,
        'pictureThumbPath' => null,
        'position' => (int) $wpdb->get_var('SELECT COALESCE(MAX(position), 0) + 1 FROM ' . $table),
        'show' => 1,
        'aggregatedPrice' => 1,
        'settings' => null,
        'recurringCycle' => 'disabled',
        'recurringSub' => 'future',
        'recurringPayment' => 0,
        'translations' => null,
        'depositPayment' => 'disabled',
        'depositPerPerson' => 1,
        'deposit' => 0,
        'fullPayment' => 0,
        'mandatoryExtra' => 0,
        'minSelectedExtras' => 0,
        'customPricing' => null,
        'maxExtraPeople' => null,
        'limitPerCustomer' => null,
    ];

    if ($dryRun) {
        return [
            'created' => $serviceId ? 0 : 1,
            'updated' => $serviceId ? 1 : 0,
            'skipped' => 0,
            'service_id' => $serviceId,
            'name' => $name,
            'category_id' => $categoryId,
        ];
    }

    if ($serviceId) {
        $wpdb->update($table, $serviceData, ['id' => $serviceId]);
        aura_import_link_employees_to_service($serviceId);
        return ['created' => 0, 'updated' => 1, 'skipped' => 0, 'service_id' => $serviceId, 'name' => $name];
    }

    $wpdb->insert($table, $serviceData);
    $newId = (int) $wpdb->insert_id;
    aura_import_link_employees_to_service($newId);

    return ['created' => 1, 'updated' => 0, 'skipped' => 0, 'service_id' => $newId, 'name' => $name];
}

function aura_import_process_csv($csvPath, $options = [])
{
    $csvPath = trim((string) $csvPath);
    if (!is_readable($csvPath)) {
        throw new RuntimeException('CSV file not readable: ' . $csvPath);
    }

    $handle = fopen($csvPath, 'r');
    if ($handle === false) {
        throw new RuntimeException('Unable to open CSV file: ' . $csvPath);
    }

    $header = fgetcsv($handle);
    if ($header === false || empty($header)) {
        fclose($handle);
        throw new RuntimeException('CSV file is empty or missing a header row.');
    }

    $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'processed' => 0, 'total' => 0];
    $limit = isset($options['limit']) ? (int) $options['limit'] : 0;
    $rowNumber = 0;

    while (($row = fgetcsv($handle)) !== false) {
        $rowNumber++;

        if ($limit > 0 && $stats['processed'] >= $limit) {
            break;
        }

        if (empty(array_filter($row, static fn ($value) => $value !== null && trim((string) $value) !== ''))) {
            continue;
        }

        $normalized = [];
        foreach ($header as $index => $columnName) {
            $normalized[strtolower(trim((string) $columnName))] = isset($row[$index]) ? $row[$index] : '';
        }

        $type = strtolower(aura_import_normalize_text($normalized['type'] ?? 'service'));
        if ($type !== '' && $type !== 'service') {
            $stats['skipped']++;
            continue;
        }

        $name = aura_import_normalize_text($normalized['name'] ?? $normalized['amelia_service_name'] ?? $normalized['bookpro_service_name'] ?? '');
        if ($name === '') {
            $stats['skipped']++;
            continue;
        }

        $stats['processed']++;
        $stats['total']++;

        $result = aura_import_upsert_service($normalized, !empty($options['dry_run']));

        if (!empty($result['created'])) {
            $stats['created'] += (int) $result['created'];
        }

        if (!empty($result['updated'])) {
            $stats['updated'] += (int) $result['updated'];
        }

        if (!empty($result['skipped'])) {
            $stats['skipped'] += (int) $result['skipped'];
        }

        if (!empty($options['verbose'])) {
            aura_import_log(sprintf('Processed %s (%s)', $name, $result['created'] ? 'created' : 'updated'), true);
        }
    }

    fclose($handle);

    return $stats;
}

$cliOptions = aura_import_parse_cli($argv ?? []);
$csvPath = $cliOptions['csv'];

if (!is_readable($csvPath)) {
    fwrite(STDERR, "CSV file not found or not readable: {$csvPath}\n");
    exit(1);
}

try {
    aura_import_set_default_appointment_status_pending();
    $stats = aura_import_process_csv($csvPath, $cliOptions);

    fwrite(STDOUT, "Import complete.\n");
    fwrite(STDOUT, "Source: {$csvPath}\n");
    fwrite(STDOUT, "Processed: {$stats['processed']}\n");
    fwrite(STDOUT, "Created: {$stats['created']}\n");
    fwrite(STDOUT, "Updated: {$stats['updated']}\n");
    fwrite(STDOUT, "Skipped: {$stats['skipped']}\n");
    fwrite(STDOUT, "Dry run: " . (!empty($cliOptions['dry_run']) ? 'yes' : 'no') . "\n");
} catch (Throwable $e) {
    fwrite(STDERR, "Import failed: " . $e->getMessage() . "\n");
    exit(1);
}
