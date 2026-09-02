<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('AuraBookPro')) {
class AuraBookPro {
    public function __construct() {
        if (!empty($GLOBALS['aurabookpro_initialized'])) {
            return;
        }

        $GLOBALS['aurabookpro_initialized'] = true;

        add_action('init', [$this, 'register_post_types']);
        add_action('init', [$this, 'register_taxonomies']);
        add_action('admin_menu', [$this, 'register_admin_menu'], 999);
        add_action('admin_menu', [$this, 'remove_duplicate_cpt_menus'], 999);
        add_action('admin_head', [$this, 'admin_menu_styles']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_init', [$this, 'handle_booking_status_update']);
        add_action('add_meta_boxes', [$this, 'register_admin_meta_boxes']);
        add_action('save_post_aurabookpro_service', [$this, 'save_service_meta']);
        add_action('save_post_aurabookpro_staff', [$this, 'save_staff_meta']);
        add_action('save_post_aurabookpro_booking', [$this, 'save_booking_meta']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('wp_ajax_aurabookpro_submit_booking', [$this, 'handle_booking_submission']);
        add_action('wp_ajax_nopriv_aurabookpro_submit_booking', [$this, 'handle_booking_submission']);
        add_action('woocommerce_checkout_order_processed', [$this, 'sync_booking_from_order'], 10, 1);
        add_action('woocommerce_order_status_changed', [$this, 'sync_booking_status_from_order'], 10, 4);
        add_action('woocommerce_before_checkout_form', [$this, 'render_checkout_booking_summary'], 20);
        add_action('woocommerce_review_order_before_submit', [$this, 'render_coupon_review_status'], 20);
        add_action('woocommerce_checkout_create_order', [$this, 'sync_booking_details_to_checkout_order'], 20, 2);
        add_filter('woocommerce_checkout_get_value', [$this, 'prefill_checkout_from_booking_data'], 10, 2);
        add_action('woocommerce_thankyou', [$this, 'handle_thankyou_confirmation'], 10, 1);
        add_shortcode('aurabookpro_booking', [$this, 'render_booking_shortcode']);
        add_action('init', [$this, 'register_booking_status']);

        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }

    public function activate() {
        $this->register_post_types();
        $this->register_taxonomies();
        $this->create_phase1_tables();
        flush_rewrite_rules();
    }

    public function deactivate() {
        flush_rewrite_rules();
    }

    public function create_phase1_tables() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $tables = [
            'aurabookpro_bookings' => "
                CREATE TABLE {$wpdb->prefix}aurabookpro_bookings (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    booking_key varchar(64) NOT NULL,
                    customer_id bigint(20) unsigned NULL,
                    service_id bigint(20) unsigned NOT NULL,
                    staff_id bigint(20) unsigned NULL,
                    location_id bigint(20) unsigned NULL,
                    resource_id bigint(20) unsigned NULL,
                    start_at datetime NOT NULL,
                    end_at datetime NOT NULL,
                    status varchar(32) NOT NULL DEFAULT 'pending',
                    quantity int(11) NOT NULL DEFAULT 1,
                    total_amount decimal(10,2) NOT NULL DEFAULT 0.00,
                    deposit_amount decimal(10,2) NOT NULL DEFAULT 0.00,
                    currency varchar(10) NOT NULL DEFAULT 'USD',
                    wc_order_id bigint(20) unsigned NULL,
                    created_at datetime NOT NULL,
                    updated_at datetime NOT NULL,
                    PRIMARY KEY  (id),
                    KEY booking_key (booking_key),
                    KEY service_id (service_id),
                    KEY staff_id (staff_id),
                    KEY status (status),
                    KEY start_at (start_at)
                ) $charset_collate;",
            'aurabookpro_slots' => "
                CREATE TABLE {$wpdb->prefix}aurabookpro_slots (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    staff_id bigint(20) unsigned NOT NULL,
                    service_id bigint(20) unsigned NOT NULL,
                    location_id bigint(20) unsigned NULL,
                    slot_date date NOT NULL,
                    start_time time NOT NULL,
                    end_time time NOT NULL,
                    status varchar(32) NOT NULL DEFAULT 'available',
                    capacity int(11) NOT NULL DEFAULT 1,
                    booked_count int(11) NOT NULL DEFAULT 0,
                    created_at datetime NOT NULL,
                    updated_at datetime NOT NULL,
                    PRIMARY KEY  (id),
                    KEY staff_service_date (staff_id, service_id, slot_date),
                    KEY status (status)
                ) $charset_collate;",
            'aurabookpro_availability' => "
                CREATE TABLE {$wpdb->prefix}aurabookpro_availability (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    staff_id bigint(20) unsigned NULL,
                    location_id bigint(20) unsigned NULL,
                    service_id bigint(20) unsigned NULL,
                    availability_date date NOT NULL,
                    start_time time NOT NULL,
                    end_time time NOT NULL,
                    is_blocked tinyint(1) NOT NULL DEFAULT 0,
                    reason varchar(255) NULL,
                    created_at datetime NOT NULL,
                    updated_at datetime NOT NULL,
                    PRIMARY KEY  (id),
                    KEY availability_date (availability_date),
                    KEY staff_id (staff_id),
                    KEY service_id (service_id)
                ) $charset_collate;",
            'aurabookpro_waitlist' => "
                CREATE TABLE {$wpdb->prefix}aurabookpro_waitlist (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    event_id bigint(20) unsigned NULL,
                    customer_id bigint(20) unsigned NOT NULL,
                    status varchar(32) NOT NULL DEFAULT 'waiting',
                    position int(11) NOT NULL DEFAULT 0,
                    created_at datetime NOT NULL,
                    PRIMARY KEY  (id),
                    KEY event_id (event_id),
                    KEY customer_id (customer_id),
                    KEY status (status)
                ) $charset_collate;",
            'aurabookpro_refunds' => "
                CREATE TABLE {$wpdb->prefix}aurabookpro_refunds (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    booking_id bigint(20) unsigned NOT NULL,
                    wc_order_id bigint(20) unsigned NULL,
                    refund_amount decimal(10,2) NOT NULL DEFAULT 0.00,
                    refund_reason varchar(255) NULL,
                    status varchar(32) NOT NULL DEFAULT 'pending',
                    created_at datetime NOT NULL,
                    PRIMARY KEY  (id),
                    KEY booking_id (booking_id),
                    KEY status (status)
                ) $charset_collate;",
        ];

        foreach ($tables as $sql) {
            dbDelta($sql);
        }
    }

    public function register_post_types() {
        if (!empty($GLOBALS['aurabookpro_post_types_registered'])) {
            return;
        }

        $GLOBALS['aurabookpro_post_types_registered'] = true;

        register_post_type(
            'aurabookpro_service',
            [
                'labels' => [
                    'name' => __('Services', 'aurabookpro'),
                    'singular_name' => __('Service', 'aurabookpro'),
                    'add_new' => __('Add Service', 'aurabookpro'),
                    'add_new_item' => __('Add Service', 'aurabookpro'),
                    'menu_name' => __('Services', 'aurabookpro'),
                ],
                'public' => true,
                'show_in_menu' => false,
                'supports' => ['title', 'editor', 'thumbnail'],
                'has_archive' => true,
                'rewrite' => ['slug' => 'aurabookpro-services'],
                'show_in_rest' => true,
            ]
        );

        register_post_type(
            'aurabookpro_staff',
            [
                'labels' => [
                    'name' => __('Staff', 'aurabookpro'),
                    'singular_name' => __('Staff', 'aurabookpro'),
                    'add_new' => __('Add Staff', 'aurabookpro'),
                    'add_new_item' => __('Add Staff', 'aurabookpro'),
                    'menu_name' => __('Staff', 'aurabookpro'),
                ],
                'public' => true,
                'show_in_menu' => false,
                'supports' => ['title', 'editor', 'thumbnail'],
                'has_archive' => true,
                'rewrite' => ['slug' => 'aurabookpro-staff'],
                'show_in_rest' => true,
            ]
        );

        register_post_type(
            'aurabookpro_location',
            [
                'labels' => [
                    'name' => __('Locations', 'aurabookpro'),
                    'singular_name' => __('Location', 'aurabookpro'),
                    'add_new' => __('Add Location', 'aurabookpro'),
                    'add_new_item' => __('Add Location', 'aurabookpro'),
                    'menu_name' => __('Locations', 'aurabookpro'),
                ],
                'public' => true,
                'show_in_menu' => false,
                'supports' => ['title', 'editor'],
                'has_archive' => true,
                'rewrite' => ['slug' => 'aurabookpro-locations'],
                'show_in_rest' => true,
            ]
        );

        register_post_type(
            'aurabookpro_resource',
            [
                'labels' => [
                    'name' => __('Resources', 'aurabookpro'),
                    'singular_name' => __('Resource', 'aurabookpro'),
                    'add_new' => __('Add Resource', 'aurabookpro'),
                    'add_new_item' => __('Add Resource', 'aurabookpro'),
                    'menu_name' => __('Resources', 'aurabookpro'),
                ],
                'public' => true,
                'show_in_menu' => false,
                'supports' => ['title', 'editor'],
                'has_archive' => true,
                'rewrite' => ['slug' => 'aurabookpro-resources'],
                'show_in_rest' => true,
            ]
        );

        register_post_type(
            'aurabookpro_booking',
            [
                'labels' => [
                    'name' => __('Bookings', 'aurabookpro'),
                    'singular_name' => __('Booking', 'aurabookpro'),
                    'add_new' => __('Add Booking', 'aurabookpro'),
                    'add_new_item' => __('Add Booking', 'aurabookpro'),
                    'menu_name' => __('Bookings', 'aurabookpro'),
                ],
                'public' => false,
                'show_ui' => true,
                'show_in_menu' => false,
                'supports' => ['title'],
                'capability_type' => 'post',
                'show_in_rest' => true,
            ]
        );
    }

    public function register_taxonomies() {
        register_taxonomy('aurabookpro_category', ['aurabookpro_service'], [
            'labels' => [
                'name' => __('Categories', 'aurabookpro'),
                'singular_name' => __('Category', 'aurabookpro'),
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_menu' => 'aurabookpro',
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'aurabookpro-category'],
        ]);

        register_taxonomy('aurabookpro_tag', ['aurabookpro_service'], [
            'labels' => [
                'name' => __('Tags', 'aurabookpro'),
                'singular_name' => __('Tag', 'aurabookpro'),
            ],
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'aurabookpro-tag'],
        ]);
    }

    public function register_booking_status() {
        $statuses = [
            'pending' => __('Pending', 'aurabookpro'),
            'confirmed' => __('Confirmed', 'aurabookpro'),
            'completed' => __('Completed', 'aurabookpro'),
            'cancelled' => __('Cancelled', 'aurabookpro'),
            'refunded' => __('Refunded', 'aurabookpro'),
        ];

        foreach ($statuses as $status => $label) {
            register_post_status($status, [
                'label' => $label,
                'public' => true,
                'exclude_from_search' => false,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'label_count' => _n_noop($label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>', 'aurabookpro'),
            ]);
        }
    }

    public function booking_status_options() {
        return [
            'pending' => __('Pending', 'aurabookpro'),
            'confirmed' => __('Confirmed', 'aurabookpro'),
            'completed' => __('Completed', 'aurabookpro'),
            'cancelled' => __('Cancelled', 'aurabookpro'),
            'refunded' => __('Refunded', 'aurabookpro'),
        ];
    }

    public function register_admin_menu() {
        if (!empty($GLOBALS['aurabookpro_menu_registered'])) {
            return;
        }

        $GLOBALS['aurabookpro_menu_registered'] = true;

        $icon_url = plugins_url('assets/icon.svg', dirname(__FILE__));

        add_menu_page(
            __('AuraBookPro', 'aurabookpro'),
            __('AuraBookPro', 'aurabookpro'),
            'manage_options',
            'aurabookpro',
            [$this, 'render_admin_dashboard'],
            $icon_url,
            25
        );

        add_submenu_page(
            'aurabookpro',
            __('Dashboard', 'aurabookpro'),
            __('Dashboard', 'aurabookpro'),
            'manage_options',
            'aurabookpro-dashboard',
            [$this, 'render_admin_dashboard']
        );

        add_submenu_page(
            'aurabookpro',
            __('Services', 'aurabookpro'),
            __('Services', 'aurabookpro'),
            'manage_options',
            'aurabookpro-services',
            [$this, 'render_services_page']
        );

        add_submenu_page(
            'aurabookpro',
            __('Staff', 'aurabookpro'),
            __('Staff', 'aurabookpro'),
            'manage_options',
            'aurabookpro-staff',
            [$this, 'render_staff_page']
        );

        add_submenu_page(
            'aurabookpro',
            __('Bookings', 'aurabookpro'),
            __('Bookings', 'aurabookpro'),
            'manage_options',
            'aurabookpro-bookings',
            [$this, 'render_bookings_page']
        );

        add_submenu_page(
            'aurabookpro',
            __('Availability', 'aurabookpro'),
            __('Availability', 'aurabookpro'),
            'manage_options',
            'aurabookpro-availability',
            [$this, 'render_availability_page']
        );
    }

    public function remove_duplicate_cpt_menus() {
        $cpts = ['aurabookpro_service', 'aurabookpro_staff', 'aurabookpro_booking'];

        foreach ($cpts as $cpt) {
            remove_menu_page('edit.php?post_type=' . $cpt);
            remove_submenu_page('aurabookpro', 'edit.php?post_type=' . $cpt);
            remove_submenu_page('aurabookpro', 'post-new.php?post_type=' . $cpt);
            remove_submenu_page('edit.php?post_type=' . $cpt, 'post-new.php?post_type=' . $cpt);
        }
    }

    public function register_admin_meta_boxes() {
        add_meta_box(
            'aurabookpro_service_details',
            __('Service Details', 'aurabookpro'),
            [$this, 'render_service_meta_box'],
            'aurabookpro_service',
            'normal',
            'default'
        );

        add_meta_box(
            'aurabookpro_staff_details',
            __('Staff Details', 'aurabookpro'),
            [$this, 'render_staff_meta_box'],
            'aurabookpro_staff',
            'normal',
            'default'
        );

        add_meta_box(
            'aurabookpro_booking_details',
            __('Booking Details', 'aurabookpro'),
            [$this, 'render_booking_meta_box'],
            'aurabookpro_booking',
            'normal',
            'default'
        );
    }

    public function render_service_meta_box($post) {
        wp_nonce_field('aurabookpro_service_meta', 'aurabookpro_service_meta_nonce');

        $duration = (int) get_post_meta($post->ID, '_aurabookpro_duration', true);
        $price = get_post_meta($post->ID, '_aurabookpro_price', true);
        $capacity = (int) get_post_meta($post->ID, '_aurabookpro_capacity', true);
        $location = get_post_meta($post->ID, '_aurabookpro_default_location', true);
        ?>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="aurabookpro_duration"><?php esc_html_e('Duration (minutes)', 'aurabookpro'); ?></label></th>
                <td><input type="number" min="15" step="15" id="aurabookpro_duration" name="aurabookpro_duration" value="<?php echo esc_attr($duration ?: 60); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="aurabookpro_price"><?php esc_html_e('Price', 'aurabookpro'); ?></label></th>
                <td><input type="number" min="0" step="0.01" id="aurabookpro_price" name="aurabookpro_price" value="<?php echo esc_attr($price ?: '0.00'); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="aurabookpro_capacity"><?php esc_html_e('Capacity', 'aurabookpro'); ?></label></th>
                <td><input type="number" min="1" step="1" id="aurabookpro_capacity" name="aurabookpro_capacity" value="<?php echo esc_attr($capacity ?: 1); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="aurabookpro_default_location"><?php esc_html_e('Default location', 'aurabookpro'); ?></label></th>
                <td><input type="text" id="aurabookpro_default_location" name="aurabookpro_default_location" value="<?php echo esc_attr($location); ?>" class="regular-text" /></td>
            </tr>
        </table>
        <?php
    }

    public function render_staff_meta_box($post) {
        wp_nonce_field('aurabookpro_staff_meta', 'aurabookpro_staff_meta_nonce');

        $services = get_posts([
            'post_type' => 'aurabookpro_service',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $selected_services = get_post_meta($post->ID, '_aurabookpro_services', true);
        if (!is_array($selected_services)) {
            $selected_services = [];
        }

        $working_hours = get_post_meta($post->ID, '_aurabookpro_working_hours', true);
        if (!is_array($working_hours)) {
            $working_hours = [];
        }

        $slot_interval = (int) get_post_meta($post->ID, '_aurabookpro_slot_interval', true);
        $slot_interval = $slot_interval > 0 ? min($slot_interval, 180) : 30;

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        ?>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="aurabookpro_staff_email"><?php esc_html_e('Email', 'aurabookpro'); ?></label></th>
                <td><input type="email" id="aurabookpro_staff_email" name="aurabookpro_staff_email" value="<?php echo esc_attr(get_post_meta($post->ID, '_aurabookpro_email', true)); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="aurabookpro_slot_interval"><?php esc_html_e('Slot interval (minutes)', 'aurabookpro'); ?></label></th>
                <td><input type="number" min="15" max="180" step="15" id="aurabookpro_slot_interval" name="aurabookpro_slot_interval" value="<?php echo esc_attr($slot_interval); ?>" class="small-text" /></td>
            </tr>
            <tr>
                <th><label><?php esc_html_e('Assigned services', 'aurabookpro'); ?></label></th>
                <td>
                    <?php foreach ($services as $service) : ?>
                        <label style="display:block;margin-bottom:6px;">
                            <input type="checkbox" name="aurabookpro_staff_services[]" value="<?php echo esc_attr($service->ID); ?>" <?php checked(in_array((string) $service->ID, array_map('strval', $selected_services), true)); ?> />
                            <?php echo esc_html(get_the_title($service)); ?>
                        </label>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e('Working hours', 'aurabookpro'); ?></label></th>
                <td>
                    <?php foreach ($days as $day) : ?>
                        <?php $day_hours = isset($working_hours[$day]) ? $working_hours[$day] : []; ?>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                            <label style="min-width:90px;text-transform:capitalize;">
                                <?php echo esc_html(str_replace('_', ' ', $day)); ?>
                            </label>
                            <input type="time" name="aurabookpro_working_hours[<?php echo esc_attr($day); ?>][start]" value="<?php echo esc_attr($day_hours['start'] ?? ''); ?>" />
                            <span>to</span>
                            <input type="time" name="aurabookpro_working_hours[<?php echo esc_attr($day); ?>][end]" value="<?php echo esc_attr($day_hours['end'] ?? ''); ?>" />
                        </div>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>
        <?php
    }

    public function render_booking_meta_box($post) {
        wp_nonce_field('aurabookpro_booking_meta', 'aurabookpro_booking_meta_nonce');

        $service_id = (int) get_post_meta($post->ID, '_aurabookpro_service_id', true);
        $staff_id = (int) get_post_meta($post->ID, '_aurabookpro_staff_id', true);
        $location_id = (int) get_post_meta($post->ID, '_aurabookpro_location_id', true);
        $booking_date = get_post_meta($post->ID, '_aurabookpro_booking_date', true);
        $booking_time = get_post_meta($post->ID, '_aurabookpro_booking_time', true);
        $customer_name = get_post_meta($post->ID, '_aurabookpro_customer_name', true);
        $customer_first_name = get_post_meta($post->ID, '_aurabookpro_customer_first_name', true);
        $customer_last_name = get_post_meta($post->ID, '_aurabookpro_customer_last_name', true);

        if (empty($customer_first_name) && empty($customer_last_name) && !empty($customer_name)) {
            $name_parts = preg_split('/\s+/', trim((string) $customer_name), 2);
            $customer_first_name = $name_parts[0] ?? '';
            $customer_last_name = $name_parts[1] ?? '';
        }

        $customer_email = get_post_meta($post->ID, '_aurabookpro_customer_email', true);
        $status = $this->normalize_booking_status(get_post_meta($post->ID, '_aurabookpro_status', true));
        $wc_order_id = (int) get_post_meta($post->ID, '_aurabookpro_wc_order_id', true);
        $product_id = (int) get_post_meta($post->ID, '_aurabookpro_wc_product_id', true);
        $coupon_code = get_post_meta($post->ID, '_aurabookpro_coupon_code', true);
        $refund_amount = get_post_meta($post->ID, '_aurabookpro_refund_amount', true);
        $refund_reason = get_post_meta($post->ID, '_aurabookpro_refund_reason', true);

        $order = $wc_order_id > 0 && class_exists('WC_Order') ? wc_get_order($wc_order_id) : false;
        $order_status = $order ? $order->get_status() : 'not-created';
        $order_status_label = $order ? wc_get_order_status_name($order_status) : __('No WooCommerce order yet', 'aurabookpro');

        $services = get_posts([
            'post_type' => 'aurabookpro_service',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $staff = get_posts([
            'post_type' => 'aurabookpro_staff',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $locations = get_posts([
            'post_type' => 'aurabookpro_location',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $products = get_posts([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);
        ?>
        <?php if ($order) : ?>
            <div style="margin:0 0 16px;padding:12px 14px;border:1px solid #dcdcde;background:#f6f7f7;border-radius:6px;">
                <strong><?php esc_html_e('WooCommerce confirmation', 'aurabookpro'); ?>:</strong>
                <?php echo esc_html($order_status_label); ?>
                <?php if ($order->get_id()) : ?>
                    <span style="margin-left:8px;">|</span>
                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $order->get_id() . '&action=edit')); ?>" target="_blank" rel="noopener noreferrer">
                        <?php esc_html_e('View order', 'aurabookpro'); ?> #<?php echo esc_html($order->get_order_number()); ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <div style="margin:0 0 16px;padding:12px 14px;border:1px dashed #dcdcde;background:#fafafa;border-radius:6px;">
                <?php esc_html_e('No WooCommerce order has been created for this booking yet.', 'aurabookpro'); ?>
            </div>
        <?php endif; ?>

        <table class="form-table" role="presentation">
            <tr>
                <th><label for="aurabookpro_booking_service"><?php esc_html_e('Service', 'aurabookpro'); ?></label></th>
                <td>
                    <select id="aurabookpro_booking_service" name="aurabookpro_booking_service">
                        <option value="0"><?php esc_html_e('Select service', 'aurabookpro'); ?></option>
                        <?php foreach ($services as $service) : ?>
                            <option value="<?php echo esc_attr($service->ID); ?>" <?php selected($service_id, $service->ID); ?>><?php echo esc_html(get_the_title($service)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="aurabookpro_booking_staff"><?php esc_html_e('Staff', 'aurabookpro'); ?></label></th>
                <td>
                    <select id="aurabookpro_booking_staff" name="aurabookpro_booking_staff">
                        <option value="0"><?php esc_html_e('Select staff', 'aurabookpro'); ?></option>
                        <?php foreach ($staff as $member) : ?>
                            <option value="<?php echo esc_attr($member->ID); ?>" <?php selected($staff_id, $member->ID); ?>><?php echo esc_html(get_the_title($member)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="aurabookpro_booking_location"><?php esc_html_e('Location', 'aurabookpro'); ?></label></th>
                <td>
                    <select id="aurabookpro_booking_location" name="aurabookpro_booking_location">
                        <option value="0"><?php esc_html_e('Select location', 'aurabookpro'); ?></option>
                        <?php foreach ($locations as $location) : ?>
                            <option value="<?php echo esc_attr($location->ID); ?>" <?php selected($location_id, $location->ID); ?>><?php echo esc_html(get_the_title($location)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="aurabookpro_booking_date"><?php esc_html_e('Date', 'aurabookpro'); ?></label></th>
                <td><input type="date" id="aurabookpro_booking_date" name="aurabookpro_booking_date" value="<?php echo esc_attr($booking_date ?: date('Y-m-d')); ?>" /></td>
            </tr>
            <tr>
                <th><label for="aurabookpro_booking_time"><?php esc_html_e('Time', 'aurabookpro'); ?></label></th>
                <td><input type="time" id="aurabookpro_booking_time" name="aurabookpro_booking_time" value="<?php echo esc_attr($booking_time ?: '09:00'); ?>" /></td>
            </tr>
            <tr>
                <th><label for="aurabookpro_booking_customer_first_name"><?php esc_html_e('Customer first name', 'aurabookpro'); ?></label></th>
                <td><input type="text" id="aurabookpro_booking_customer_first_name" name="aurabookpro_booking_customer_first_name" value="<?php echo esc_attr($customer_first_name); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="aurabookpro_booking_customer_last_name"><?php esc_html_e('Customer surname', 'aurabookpro'); ?></label></th>
                <td><input type="text" id="aurabookpro_booking_customer_last_name" name="aurabookpro_booking_customer_last_name" value="<?php echo esc_attr($customer_last_name); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="aurabookpro_booking_customer_email"><?php esc_html_e('Customer email', 'aurabookpro'); ?></label></th>
                <td><input type="email" id="aurabookpro_booking_customer_email" name="aurabookpro_booking_customer_email" value="<?php echo esc_attr($customer_email); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="aurabookpro_booking_status"><?php esc_html_e('Status', 'aurabookpro'); ?></label></th>
                <td>
                    <select id="aurabookpro_booking_status" name="aurabookpro_booking_status">
                        <?php foreach (array_keys($this->booking_status_options()) as $option) : ?>
                            <option value="<?php echo esc_attr($option); ?>" <?php selected($status, $option); ?>><?php echo esc_html(ucfirst($option)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Booking workflow', 'aurabookpro'); ?></th>
                <td>
                    <?php $transition_states = $this->get_booking_status_transitions($status); ?>
                    <?php if (!empty($transition_states)) : ?>
                        <div style="display:flex;flex-wrap:wrap;gap:8px;">
                            <?php foreach ($transition_states as $transition_status) : ?>
                                <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=aurabookpro-bookings')); ?>" style="display:inline-block; margin:0;">
                                    <?php wp_nonce_field('abp_update_booking_status', 'abp_booking_status_nonce'); ?>
                                    <input type="hidden" name="abp_update_booking_status" value="1" />
                                    <input type="hidden" name="abp_booking_id" value="<?php echo esc_attr((string) $post->ID); ?>" />
                                    <input type="hidden" name="abp_booking_status" value="<?php echo esc_attr($transition_status); ?>" />
                                    <input type="hidden" name="page" value="aurabookpro-bookings" />
                                    <button type="submit" class="button button-secondary" title="<?php echo esc_attr(sprintf(__('Set booking to %s', 'aurabookpro'), $this->get_booking_status_label($transition_status))); ?>">
                                        <?php echo esc_html(sprintf(__('Mark %s', 'aurabookpro'), $this->get_booking_status_label($transition_status))); ?>
                                    </button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <span style="color:#5f6368;"><?php esc_html_e('No further workflow actions available.', 'aurabookpro'); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><label for="aurabookpro_coupon_code"><?php esc_html_e('Coupon code', 'aurabookpro'); ?></label></th>
                <td><input type="text" id="aurabookpro_coupon_code" name="aurabookpro_coupon_code" value="<?php echo esc_attr($coupon_code); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="aurabookpro_refund_amount"><?php esc_html_e('Refund amount', 'aurabookpro'); ?></label></th>
                <td><input type="number" min="0" step="0.01" id="aurabookpro_refund_amount" name="aurabookpro_refund_amount" value="<?php echo esc_attr($refund_amount ?: '0.00'); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="aurabookpro_refund_reason"><?php esc_html_e('Refund reason', 'aurabookpro'); ?></label></th>
                <td><input type="text" id="aurabookpro_refund_reason" name="aurabookpro_refund_reason" value="<?php echo esc_attr($refund_reason); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="aurabookpro_wc_product_id"><?php esc_html_e('WooCommerce product map', 'aurabookpro'); ?></label></th>
                <td>
                    <select id="aurabookpro_wc_product_id" name="aurabookpro_wc_product_id">
                        <option value="0"><?php esc_html_e('Select product', 'aurabookpro'); ?></option>
                        <?php foreach ($products as $product) : ?>
                            <option value="<?php echo esc_attr($product->ID); ?>" <?php selected($product_id, $product->ID); ?>><?php echo esc_html(get_the_title($product)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_service_meta($post_id) {
        if (!isset($_POST['aurabookpro_service_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aurabookpro_service_meta_nonce'])), 'aurabookpro_service_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $duration = isset($_POST['aurabookpro_duration']) ? absint($_POST['aurabookpro_duration']) : 60;
        $price = isset($_POST['aurabookpro_price']) ? floatval($_POST['aurabookpro_price']) : 0;
        $capacity = isset($_POST['aurabookpro_capacity']) ? absint($_POST['aurabookpro_capacity']) : 1;
        $location = isset($_POST['aurabookpro_default_location']) ? sanitize_text_field(wp_unslash($_POST['aurabookpro_default_location'])) : '';

        update_post_meta($post_id, '_aurabookpro_duration', $duration);
        update_post_meta($post_id, '_aurabookpro_price', $price);
        update_post_meta($post_id, '_aurabookpro_capacity', $capacity);
        update_post_meta($post_id, '_aurabookpro_default_location', $location);
    }

    public function save_staff_meta($post_id) {
        if (!isset($_POST['aurabookpro_staff_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aurabookpro_staff_meta_nonce'])), 'aurabookpro_staff_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $email = isset($_POST['aurabookpro_staff_email']) ? sanitize_email(wp_unslash($_POST['aurabookpro_staff_email'])) : '';
        $selected_services = isset($_POST['aurabookpro_staff_services']) ? array_map('absint', $_POST['aurabookpro_staff_services']) : [];
        $slot_interval = isset($_POST['aurabookpro_slot_interval']) ? absint($_POST['aurabookpro_slot_interval']) : 30;
        $slot_interval = $slot_interval > 0 ? min($slot_interval, 180) : 30;

        $working_hours = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($days as $day) {
            $day_hours = isset($_POST['aurabookpro_working_hours'][$day]) ? $_POST['aurabookpro_working_hours'][$day] : [];
            $start = isset($day_hours['start']) ? sanitize_text_field(wp_unslash($day_hours['start'])) : '';
            $end = isset($day_hours['end']) ? sanitize_text_field(wp_unslash($day_hours['end'])) : '';

            if (!empty($start) && !empty($end)) {
                $working_hours[$day] = [
                    'start' => $start,
                    'end' => $end,
                ];
            }
        }

        update_post_meta($post_id, '_aurabookpro_email', $email);
        update_post_meta($post_id, '_aurabookpro_services', $selected_services);
        update_post_meta($post_id, '_aurabookpro_slot_interval', $slot_interval);
        update_post_meta($post_id, '_aurabookpro_working_hours', $working_hours);
    }

    public function get_staff_working_hours($staff_id) {
        $hours = get_post_meta($staff_id, '_aurabookpro_working_hours', true);
        if (!is_array($hours)) {
            return [];
        }

        $normalized = [];
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            if (!empty($hours[$day]['start']) && !empty($hours[$day]['end'])) {
                $normalized[$day] = [
                    'start' => $hours[$day]['start'],
                    'end' => $hours[$day]['end'],
                ];
            }
        }

        return $normalized;
    }

    public function get_staff_slot_interval($staff_id) {
        $interval = (int) get_post_meta($staff_id, '_aurabookpro_slot_interval', true);
        return $interval > 0 ? min($interval, 180) : 30;
    }

    public function get_default_service_for_staff($staff_id) {
        $service_ids = get_post_meta($staff_id, '_aurabookpro_services', true);
        if (!is_array($service_ids) || empty($service_ids)) {
            $services = get_posts([
                'post_type' => 'aurabookpro_service',
                'post_status' => 'publish',
                'posts_per_page' => 1,
                'orderby' => 'title',
                'order' => 'ASC',
            ]);

            return $services ? (int) $services[0]->ID : 0;
        }

        foreach ($service_ids as $service_id) {
            $service = get_post((int) $service_id);
            if ($service && 'aurabookpro_service' === $service->post_type) {
                return (int) $service->ID;
            }
        }

        return 0;
    }

    public function generate_time_slots($start_time, $end_time, $interval_minutes = 30) {
        $interval_seconds = max(15, absint($interval_minutes)) * 60;
        $slots = [];
        $cursor = strtotime($start_time);
        $end = strtotime($end_time);

        while ($cursor + $interval_seconds <= $end) {
            $slots[] = date('H:i', $cursor);
            $cursor += $interval_seconds;
        }

        return $slots;
    }

    public function get_staff_daily_slots($staff_id, $service_id, $date) {
        $staff_id = absint($staff_id);
        $service_id = absint($service_id);
        $date = sanitize_text_field($date);

        if (!$staff_id || !$service_id || empty($date)) {
            return [];
        }

        $service_duration = (int) get_post_meta($service_id, '_aurabookpro_duration', true);
        $service_duration = $service_duration > 0 ? $service_duration : 60;
        $interval = $this->get_staff_slot_interval($staff_id);
        $working_hours = $this->get_staff_working_hours($staff_id);
        $day_name = strtolower((new DateTime($date))->format('l'));

        if (empty($working_hours[$day_name])) {
            return [];
        }

        $day_schedule = $working_hours[$day_name];
        $start_dt = new DateTime($date . ' ' . $day_schedule['start']);
        $end_dt = new DateTime($date . ' ' . $day_schedule['end']);
        $available = [];
        $slot_cursor = clone $start_dt;

        while (true) {
            $slot_end = clone $slot_cursor;
            $slot_end->modify('+' . $service_duration . ' minutes');

            if ($slot_end > $end_dt) {
                break;
            }

            $slot_start = $slot_cursor->format('Y-m-d H:i:s');
            $slot_end_value = $slot_end->format('Y-m-d H:i:s');

            if ($this->is_slot_available($service_id, $staff_id, $slot_start, $slot_end_value)) {
                $available[] = [
                    'start' => $slot_start,
                    'end' => $slot_end_value,
                    'label' => date_i18n(get_option('time_format'), strtotime($slot_start)),
                ];
            }

            $slot_cursor->modify('+' . $interval . ' minutes');
        }

        return $available;
    }

    public function get_staff_day_summary($staff_id, $date) {
        $staff_id = absint($staff_id);
        $date = sanitize_text_field($date);

        if (!$staff_id || empty($date)) {
            return [
                'total' => 0,
                'available' => 0,
                'booked' => 0,
            ];
        }

        $service_id = $this->get_default_service_for_staff($staff_id);
        $slots = $service_id ? $this->get_staff_daily_slots($staff_id, $service_id, $date) : [];

        return [
            'total' => count($slots),
            'available' => count($slots),
            'booked' => max(0, 8 - count($slots)),
        ];
    }

    public function is_slot_available($service_id, $staff_id, $start_at, $end_at, $ignore_booking_id = 0) {
        $service_id = absint($service_id);
        $staff_id = absint($staff_id);

        if (!$service_id || !$staff_id) {
            return false;
        }

        $start = gmdate('Y-m-d H:i:s', strtotime($start_at));
        $end = gmdate('Y-m-d H:i:s', strtotime($end_at));

        if (empty($start) || empty($end) || strtotime($start) >= strtotime($end)) {
            return false;
        }

        $working_hours = $this->get_staff_working_hours($staff_id);
        $day_name = strtolower((new DateTime($start))->format('l'));
        if (!empty($working_hours[$day_name])) {
            $schedule = $working_hours[$day_name];
            $day_date = gmdate('Y-m-d', strtotime($start));
            $window_start = strtotime($day_date . ' ' . $schedule['start']);
            $window_end = strtotime($day_date . ' ' . $schedule['end']);
            $requested_start = strtotime($start);
            $requested_end = strtotime($end);

            if ($requested_start < $window_start || $requested_end > $window_end) {
                return false;
            }
        }

        global $wpdb;
        $table = $wpdb->prefix . 'aurabookpro_bookings';

        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE staff_id = %d AND status != 'cancelled' AND ((start_at < %s AND end_at > %s) OR (start_at < %s AND end_at > %s) OR (start_at >= %s AND start_at < %s))",
            $staff_id,
            $end,
            $start,
            $start,
            $end,
            $start,
            $end
        );

        if ($ignore_booking_id) {
            $sql .= $wpdb->prepare(' AND id != %d', $ignore_booking_id);
        }

        $count = (int) $wpdb->get_var($sql);
        if ($count > 0) {
            return false;
        }

        $capacity = (int) get_post_meta($service_id, '_aurabookpro_capacity', true);
        $capacity = $capacity > 0 ? $capacity : 1;

        $simultaneous = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE staff_id = %d AND status != 'cancelled' AND start_at < %s AND end_at > %s",
            $staff_id,
            $end,
            $start
        ));

        if ($simultaneous >= $capacity) {
            return false;
        }

        $blocked = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}aurabookpro_availability WHERE staff_id = %d AND is_blocked = 1 AND availability_date = %s AND start_time < %s AND end_time > %s",
            $staff_id,
            gmdate('Y-m-d', strtotime($start)),
            gmdate('H:i:s', strtotime($end)),
            gmdate('H:i:s', strtotime($start))
        ));

        return (int) $blocked === 0;
    }

    public function save_booking_meta($post_id) {
        if (!isset($_POST['aurabookpro_booking_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aurabookpro_booking_meta_nonce'])), 'aurabookpro_booking_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $service_id = isset($_POST['aurabookpro_booking_service']) ? absint($_POST['aurabookpro_booking_service']) : 0;
        $staff_id = isset($_POST['aurabookpro_booking_staff']) ? absint($_POST['aurabookpro_booking_staff']) : 0;
        $location_id = isset($_POST['aurabookpro_booking_location']) ? absint($_POST['aurabookpro_booking_location']) : 0;
        $booking_date = isset($_POST['aurabookpro_booking_date']) ? sanitize_text_field(wp_unslash($_POST['aurabookpro_booking_date'])) : date('Y-m-d');
        $booking_time = isset($_POST['aurabookpro_booking_time']) ? sanitize_text_field(wp_unslash($_POST['aurabookpro_booking_time'])) : '09:00';
        $customer_first_name = isset($_POST['aurabookpro_booking_customer_first_name']) ? sanitize_text_field(wp_unslash($_POST['aurabookpro_booking_customer_first_name'])) : '';
        $customer_last_name = isset($_POST['aurabookpro_booking_customer_last_name']) ? sanitize_text_field(wp_unslash($_POST['aurabookpro_booking_customer_last_name'])) : '';
        $customer_name = trim($customer_first_name . ' ' . $customer_last_name);
        $customer_email = isset($_POST['aurabookpro_booking_customer_email']) ? sanitize_email(wp_unslash($_POST['aurabookpro_booking_customer_email'])) : '';
        $status = $this->normalize_booking_status(isset($_POST['aurabookpro_booking_status']) ? wp_unslash($_POST['aurabookpro_booking_status']) : 'pending');
        $wc_product_id = isset($_POST['aurabookpro_wc_product_id']) ? absint($_POST['aurabookpro_wc_product_id']) : 0;
        $coupon_code = isset($_POST['aurabookpro_coupon_code']) ? sanitize_text_field(wp_unslash($_POST['aurabookpro_coupon_code'])) : '';
        $refund_amount = isset($_POST['aurabookpro_refund_amount']) ? floatval($_POST['aurabookpro_refund_amount']) : 0;
        $refund_reason = isset($_POST['aurabookpro_refund_reason']) ? sanitize_text_field(wp_unslash($_POST['aurabookpro_refund_reason'])) : '';
        $wc_order_id = (int) get_post_meta($post_id, '_aurabookpro_wc_order_id', true);

        if (!$service_id || !$staff_id || !$booking_date || !$booking_time) {
            return;
        }

        $start_at = $booking_date . ' ' . $booking_time;
        $duration = (int) get_post_meta($service_id, '_aurabookpro_duration', true);
        $duration = $duration > 0 ? $duration : 60;
        $end_at = date('Y-m-d H:i:s', strtotime($start_at . ' + ' . $duration . ' minutes'));

        if (!$this->is_slot_available($service_id, $staff_id, $start_at, $end_at, $post_id)) {
            wp_die(__('This time slot is not available for the selected staff member.', 'aurabookpro'));
        }

        update_post_meta($post_id, '_aurabookpro_service_id', $service_id);
        update_post_meta($post_id, '_aurabookpro_staff_id', $staff_id);
        update_post_meta($post_id, '_aurabookpro_location_id', $location_id);
        update_post_meta($post_id, '_aurabookpro_booking_date', $booking_date);
        update_post_meta($post_id, '_aurabookpro_booking_time', $booking_time);
        update_post_meta($post_id, '_aurabookpro_customer_first_name', $customer_first_name);
        update_post_meta($post_id, '_aurabookpro_customer_last_name', $customer_last_name);
        update_post_meta($post_id, '_aurabookpro_customer_name', $customer_name);
        update_post_meta($post_id, '_aurabookpro_customer_email', $customer_email);
        update_post_meta($post_id, '_aurabookpro_status', $status);
        update_post_meta($post_id, '_aurabookpro_wc_product_id', $wc_product_id);
        update_post_meta($post_id, '_aurabookpro_coupon_code', $coupon_code);
        update_post_meta($post_id, '_aurabookpro_refund_amount', $refund_amount);
        update_post_meta($post_id, '_aurabookpro_refund_reason', $refund_reason);

        if ($wc_order_id > 0 && class_exists('WC_Order')) {
            $order = wc_get_order($wc_order_id);
            if ($order) {
                $order_status_map = [
                    'pending' => 'pending',
                    'confirmed' => 'processing',
                    'completed' => 'completed',
                    'cancelled' => 'cancelled',
                    'refunded' => 'refunded',
                ];

                $mapped_order_status = $order_status_map[$status] ?? 'pending';
                if (method_exists($order, 'update_status')) {
                    $order->update_status($mapped_order_status, __('Booking status synced from AuraBookPro admin.', 'aurabookpro'));
                }
            }
        }

        if ($refund_amount > 0) {
            $this->create_refund($post_id, $refund_amount, $refund_reason);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'aurabookpro_bookings';

        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE id = %d", $post_id));
        $booking_key = 'ABP-' . get_post($post_id)->ID;

        if ($exists) {
            $wpdb->update(
                $table,
                [
                    'booking_key' => $booking_key,
                    'service_id' => $service_id,
                    'staff_id' => $staff_id,
                    'location_id' => $location_id,
                    'start_at' => $start_at,
                    'end_at' => $end_at,
                    'status' => $status,
                    'total_amount' => floatval(get_post_meta($service_id, '_aurabookpro_price', true) ?: 0),
                    'currency' => 'USD',
                    'wc_order_id' => 0,
                    'updated_at' => current_time('mysql'),
                ],
                ['id' => $post_id]
            );
        } else {
            $wpdb->insert(
                $table,
                [
                    'id' => $post_id,
                    'booking_key' => $booking_key,
                    'customer_id' => get_current_user_id() ?: 0,
                    'service_id' => $service_id,
                    'staff_id' => $staff_id,
                    'location_id' => $location_id,
                    'resource_id' => 0,
                    'start_at' => $start_at,
                    'end_at' => $end_at,
                    'status' => $status,
                    'quantity' => 1,
                    'total_amount' => floatval(get_post_meta($service_id, '_aurabookpro_price', true) ?: 0),
                    'deposit_amount' => 0,
                    'currency' => 'USD',
                    'wc_order_id' => 0,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ]
            );
        }

        if ($wc_product_id > 0) {
            update_post_meta($post_id, '_aurabookpro_wc_product_id', $wc_product_id);
        }
    }

    public function render_services_page() {
        $services = get_posts([
            'post_type' => 'aurabookpro_service',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        ?>
        <div class="wrap">
            <h1>
                <?php echo esc_html__('Services', 'aurabookpro'); ?>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=aurabookpro_service')); ?>" class="page-title-action"><?php esc_html_e('Add New', 'aurabookpro'); ?></a>
            </h1>

            <?php if ($services) : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Service', 'aurabookpro'); ?></th>
                            <th><?php esc_html_e('Price', 'aurabookpro'); ?></th>
                            <th><?php esc_html_e('Duration', 'aurabookpro'); ?></th>
                            <th><?php esc_html_e('Capacity', 'aurabookpro'); ?></th>
                            <th><?php esc_html_e('Actions', 'aurabookpro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service) : ?>
                            <?php $price = get_post_meta($service->ID, '_aurabookpro_price', true); ?>
                            <?php $duration = (int) get_post_meta($service->ID, '_aurabookpro_duration', true); ?>
                            <?php $capacity = (int) get_post_meta($service->ID, '_aurabookpro_capacity', true); ?>
                            <tr>
                                <td><?php echo esc_html(get_the_title($service)); ?></td>
                                <td><?php echo esc_html(wc_price((float) $price)); ?></td>
                                <td><?php echo esc_html($duration > 0 ? sprintf('%d min', $duration) : __('N/A', 'aurabookpro')); ?></td>
                                <td><?php echo esc_html($capacity > 0 ? (string) $capacity : '1'); ?></td>
                                <td><a href="<?php echo esc_url(get_edit_post_link($service->ID)); ?>"><?php esc_html_e('Edit', 'aurabookpro'); ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php esc_html_e('No services created yet.', 'aurabookpro'); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    public function render_staff_page() {
        $staff = get_posts([
            'post_type' => 'aurabookpro_staff',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        ?>
        <div class="wrap">
            <h1>
                <?php echo esc_html__('Staff', 'aurabookpro'); ?>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=aurabookpro_staff')); ?>" class="page-title-action"><?php esc_html_e('Add New', 'aurabookpro'); ?></a>
            </h1>

            <?php if ($staff) : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Staff', 'aurabookpro'); ?></th>
                            <th><?php esc_html_e('Email', 'aurabookpro'); ?></th>
                            <th><?php esc_html_e('Assigned Services', 'aurabookpro'); ?></th>
                            <th><?php esc_html_e('Actions', 'aurabookpro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff as $person) : ?>
                            <?php $assigned_services = get_post_meta($person->ID, '_aurabookpro_services', true); ?>
                            <?php $service_names = []; ?>
                            <?php if (is_array($assigned_services) && !empty($assigned_services)) : ?>
                                <?php foreach ($assigned_services as $service_id) : ?>
                                    <?php $service_names[] = get_the_title((int) $service_id); ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr>
                                <td><?php echo esc_html(get_the_title($person)); ?></td>
                                <td><?php echo esc_html(get_post_meta($person->ID, '_aurabookpro_email', true)); ?></td>
                                <td><?php echo esc_html(implode(', ', array_filter($service_names))); ?></td>
                                <td><a href="<?php echo esc_url(get_edit_post_link($person->ID)); ?>"><?php esc_html_e('Edit', 'aurabookpro'); ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php esc_html_e('No staff created yet.', 'aurabookpro'); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    public function render_bookings_page() {
        $selected_status = isset($_GET['abp_status']) ? sanitize_key(wp_unslash($_GET['abp_status'])) : '';
        $selected_staff = isset($_GET['abp_staff']) ? absint($_GET['abp_staff']) : 0;
        $selected_day = isset($_GET['abp_day']) ? sanitize_text_field(wp_unslash($_GET['abp_day'])) : '';
        $date_from = isset($_GET['abp_date_from']) ? sanitize_text_field(wp_unslash($_GET['abp_date_from'])) : '';
        $date_to = isset($_GET['abp_date_to']) ? sanitize_text_field(wp_unslash($_GET['abp_date_to'])) : '';
        $search_term = isset($_GET['abp_search']) ? sanitize_text_field(wp_unslash($_GET['abp_search'])) : '';
        $current_view = isset($_GET['abp_view']) ? sanitize_key(wp_unslash($_GET['abp_view'])) : 'list';

        if ($selected_day !== '') {
            $date_from = $selected_day;
            $date_to = $selected_day;
        }

        if (isset($_GET['abp_export']) && 'csv' === $_GET['abp_export']) {
            $this->export_bookings_csv([
                'status' => $selected_status,
                'staff' => $selected_staff,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'search' => $search_term,
            ]);
            exit;
        }

        $meta_query = [];

        if ($selected_status !== '') {
            $meta_query[] = [
                'key' => '_aurabookpro_status',
                'value' => $selected_status,
                'compare' => '=',
            ];
        }

        if ($selected_staff > 0) {
            $meta_query[] = [
                'key' => '_aurabookpro_staff_id',
                'value' => (string) $selected_staff,
                'compare' => '=',
            ];
        }

        if ($date_from !== '') {
            $meta_query[] = [
                'key' => '_aurabookpro_booking_date',
                'value' => $date_from,
                'compare' => '>=',
                'type' => 'DATE',
            ];
        }

        if ($date_to !== '') {
            $meta_query[] = [
                'key' => '_aurabookpro_booking_date',
                'value' => $date_to,
                'compare' => '<=',
                'type' => 'DATE',
            ];
        }

        $query_args = [
            'post_type' => 'aurabookpro_booking',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        if (!empty($meta_query)) {
            $query_args['meta_query'] = $meta_query;
        }

        if ($search_term !== '') {
            $query_args['s'] = $search_term;
        }

        $bookings = get_posts($query_args);

        $stats = [
            'total' => count($bookings),
            'pending' => 0,
            'confirmed' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'refunded' => 0,
            'paid' => 0,
        ];

        foreach ($bookings as $booking) {
            $status = get_post_meta($booking->ID, '_aurabookpro_status', true) ?: 'pending';
            if (isset($stats[$status])) {
                $stats[$status]++;
            }

            $wc_order_id = (int) get_post_meta($booking->ID, '_aurabookpro_wc_order_id', true);
            if ($wc_order_id > 0) {
                $stats['paid']++;
            }
        }

        $staff_members = get_posts([
            'post_type' => 'aurabookpro_staff',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $calendar_dates = [];
        $calendar_start = new DateTime($date_from !== '' ? $date_from : 'today');
        $calendar_end = new DateTime($date_to !== '' ? $date_to : 'today');
        if ($date_from === '' && $date_to === '') {
            $calendar_end->modify('+6 days');
        }
        if ($date_to !== '' && $date_from === '') {
            $calendar_start = new DateTime('today');
        }
        if ($date_from !== '' && $date_to === '') {
            $calendar_end = clone $calendar_start;
            $calendar_end->modify('+6 days');
        }

        $interval = new DateInterval('P1D');
        $period = new DatePeriod($calendar_start, $interval, $calendar_end);
        foreach ($period as $date) {
            $calendar_dates[] = $date->format('Y-m-d');
        }
        if (empty($calendar_dates)) {
            $calendar_dates[] = $calendar_start->format('Y-m-d');
        }

        if ($date_from !== '' && $date_to !== '') {
            $period_end = new DateTime($date_to);
            $period_end->modify('+1 day');
            $period = new DatePeriod($calendar_start, $interval, $period_end);
            $calendar_dates = [];
            foreach ($period as $date) {
                $calendar_dates[] = $date->format('Y-m-d');
            }
        }

        $calendar_bookings = [];
        foreach ($bookings as $booking) {
            $booking_date = get_post_meta($booking->ID, '_aurabookpro_booking_date', true);
            if ($booking_date !== '') {
                $calendar_bookings[$booking_date][] = $booking;
            }
        }

        ?>
        <div class="wrap abp-bookings-wrap">
            <h1>
                <?php echo esc_html__('Bookings', 'aurabookpro'); ?>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=aurabookpro_booking')); ?>" class="page-title-action"><?php esc_html_e('Add New', 'aurabookpro'); ?></a>
            </h1>

            <div class="abp-bookings-summary">
                <div class="abp-summary-tile"><span><?php esc_html_e('Total', 'aurabookpro'); ?></span><strong><?php echo esc_html((string) $stats['total']); ?></strong></div>
                <div class="abp-summary-tile pending"><span><?php esc_html_e('Pending', 'aurabookpro'); ?></span><strong><?php echo esc_html((string) $stats['pending']); ?></strong></div>
                <div class="abp-summary-tile confirmed"><span><?php esc_html_e('Confirmed', 'aurabookpro'); ?></span><strong><?php echo esc_html((string) $stats['confirmed']); ?></strong></div>
                <div class="abp-summary-tile completed"><span><?php esc_html_e('Completed', 'aurabookpro'); ?></span><strong><?php echo esc_html((string) $stats['completed']); ?></strong></div>
                <div class="abp-summary-tile cancelled"><span><?php esc_html_e('Cancelled', 'aurabookpro'); ?></span><strong><?php echo esc_html((string) $stats['cancelled']); ?></strong></div>
                <div class="abp-summary-tile refunded"><span><?php esc_html_e('Refunded', 'aurabookpro'); ?></span><strong><?php echo esc_html((string) $stats['refunded']); ?></strong></div>
                <div class="abp-summary-tile paid"><span><?php esc_html_e('Paid Orders', 'aurabookpro'); ?></span><strong><?php echo esc_html((string) $stats['paid']); ?></strong></div>
            </div>

            <div class="abp-bookings-filters" style="margin:18px 0;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    <input type="hidden" name="page" value="aurabookpro-bookings" />
                    <input type="search" name="abp_search" value="<?php echo esc_attr($search_term); ?>" placeholder="<?php esc_attr_e('Search customer, service, staff', 'aurabookpro'); ?>" style="min-width:260px;" />
                    <select id="abp_staff_filter" name="abp_staff">
                        <option value="0"><?php esc_html_e('All staff', 'aurabookpro'); ?></option>
                        <?php foreach ($staff_members as $person) : ?>
                            <option value="<?php echo esc_attr($person->ID); ?>" <?php selected($selected_staff, $person->ID); ?>><?php echo esc_html(get_the_title($person)); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" id="abp_date_from" name="abp_date_from" value="<?php echo esc_attr($date_from); ?>" />
                    <input type="date" id="abp_date_to" name="abp_date_to" value="<?php echo esc_attr($date_to); ?>" />
                    <select id="abp_status_filter" name="abp_status">
                        <option value=""><?php esc_html_e('All statuses', 'aurabookpro'); ?></option>
                        <?php foreach ($this->booking_status_options() as $status_key => $status_label) : ?>
                            <option value="<?php echo esc_attr($status_key); ?>" <?php selected($selected_status, $status_key); ?>><?php echo esc_html($status_label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Filter', 'aurabookpro'); ?></button>
                    <?php if ($search_term !== '' || $selected_status !== '' || $selected_staff > 0 || $date_from !== '' || $date_to !== '') : ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=aurabookpro-bookings')); ?>" class="button"><?php esc_html_e('Clear', 'aurabookpro'); ?></a>
                    <?php endif; ?>
                </form>
                <div class="abp-view-toggle" style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="<?php echo esc_url(add_query_arg(['page' => 'aurabookpro-bookings', 'abp_view' => 'list', 'abp_status' => $selected_status, 'abp_staff' => $selected_staff, 'abp_date_from' => $date_from, 'abp_date_to' => $date_to, 'abp_search' => $search_term], admin_url('admin.php'))); ?>" class="button <?php echo 'list' === $current_view ? 'button-primary' : ''; ?>"><?php esc_html_e('List', 'aurabookpro'); ?></a>
                    <a href="<?php echo esc_url(add_query_arg(['page' => 'aurabookpro-bookings', 'abp_view' => 'calendar', 'abp_status' => $selected_status, 'abp_staff' => $selected_staff, 'abp_date_from' => $date_from, 'abp_date_to' => $date_to, 'abp_search' => $search_term], admin_url('admin.php'))); ?>" class="button <?php echo 'calendar' === $current_view ? 'button-primary' : ''; ?>"><?php esc_html_e('Calendar', 'aurabookpro'); ?></a>
                    <a href="<?php echo esc_url(add_query_arg(['page' => 'aurabookpro-bookings', 'abp_view' => 'calendar', 'abp_status' => $selected_status, 'abp_staff' => $selected_staff, 'abp_date_from' => current_time('Y-m-d'), 'abp_date_to' => date('Y-m-d', strtotime('+6 days', current_time('timestamp'))), 'abp_search' => $search_term], admin_url('admin.php'))); ?>" class="button"><?php esc_html_e('Today + 7 days', 'aurabookpro'); ?></a>
                    <a href="<?php echo esc_url(add_query_arg(['page' => 'aurabookpro-bookings', 'abp_export' => 'csv', 'abp_status' => $selected_status, 'abp_staff' => $selected_staff, 'abp_date_from' => $date_from, 'abp_date_to' => $date_to, 'abp_search' => $search_term], admin_url('admin.php'))); ?>" class="button"><?php esc_html_e('Export CSV', 'aurabookpro'); ?></a>
                </div>
            </div>

            <?php if ('calendar' === $current_view) : ?>
                <?php
                $staff_day_date = $date_from !== '' ? $date_from : current_time('Y-m-d');
                ?>

                <div class="abp-staff-day-panel <?php echo ($selected_staff > 0 || $selected_status !== '' || $search_term !== '') ? 'abp-staff-day-panel--filtered' : ''; ?>" style="margin-top:18px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
                        <h2 style="margin:0;font-size:18px;"><?php esc_html_e('Staff day', 'aurabookpro'); ?></h2>
                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                            <span class="abp-selected-day-badge" style="display:inline-flex;align-items:center;gap:6px;padding:4px 8px;border-radius:999px;background:#eef6ff;border:1px solid #bed8ff;color:#214a7d;font-size:11px;font-weight:700;letter-spacing:0.03em;text-transform:uppercase;">
                                <span><?php esc_html_e('Selected day', 'aurabookpro'); ?></span>
                                <strong style="font-size:12px;color:#123a66;">
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($staff_day_date))); ?>
                                </strong>
                            </span>
                            <label style="display:flex;align-items:center;gap:8px;font-weight:600;">
                                <span><?php esc_html_e('Date', 'aurabookpro'); ?></span>
                                <select id="abp-staff-day-select" style="min-width:170px;">
                                    <?php foreach ($calendar_dates as $day) : ?>
                                        <option value="<?php echo esc_attr($day); ?>" <?php selected($day, $staff_day_date); ?>><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($day))); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                    </div>

                    <?php foreach ($calendar_dates as $day) : ?>
                        <?php
                        $staff_day_slots = [];
                        $staff_day_lookup = [];

                        foreach ($bookings as $booking) {
                            $booking_date = get_post_meta($booking->ID, '_aurabookpro_booking_date', true);
                            if ($booking_date !== $day) {
                                continue;
                            }

                            $staff_id = (int) get_post_meta($booking->ID, '_aurabookpro_staff_id', true);
                            $time = get_post_meta($booking->ID, '_aurabookpro_booking_time', true);
                            if ($time !== '') {
                                $staff_day_slots[$time] = $time;
                            }

                            if ($staff_id > 0) {
                                $staff_day_lookup[$staff_id][$time][] = $booking;
                            }
                        }

                        ksort($staff_day_slots);
                        ?>

                        <div class="abp-staff-day-grid" data-date="<?php echo esc_attr($day); ?>" <?php echo $day === $staff_day_date ? '' : 'hidden'; ?> style="overflow-x:auto;">
                            <table class="widefat striped" style="min-width:700px;">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Staff', 'aurabookpro'); ?></th>
                                        <?php foreach ($staff_day_slots as $slot) : ?>
                                            <th style="min-width:110px;text-align:center;"><?php echo esc_html($slot); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($staff_members as $person) : ?>
                                        <tr>
                                            <td><strong><?php echo esc_html(get_the_title($person)); ?></strong></td>
                                            <?php foreach ($staff_day_slots as $slot) : ?>
                                                <?php $slot_bookings = $staff_day_lookup[$person->ID][$slot] ?? []; ?>
                                                <td class="abp-staff-slot-cell" style="vertical-align:top;padding:8px;">
                                                    <?php if (!empty($slot_bookings)) : ?>
                                                        <?php foreach ($slot_bookings as $booking) : ?>
                                                            <?php
                                                            $status = get_post_meta($booking->ID, '_aurabookpro_status', true) ?: 'pending';
                                                            $status_label = $this->get_booking_status_label($status);
                                                            $status_class = $this->get_booking_status_class($status);
                                                            $customer_name = get_post_meta($booking->ID, '_aurabookpro_customer_name', true);
                                                            $service_name = get_the_title((int) get_post_meta($booking->ID, '_aurabookpro_service_id', true));
                                                            ?>
                                                            <a class="abp-staff-slot-booking-link" href="<?php echo esc_url(get_edit_post_link($booking->ID)); ?>" style="display:block; text-decoration:none; color:inherit;">
                                                                <div class="abp-staff-slot-booking" style="margin-bottom:6px;padding:6px 7px;border:1px solid #dcdfe4;border-radius:6px;background:#f7f8fa;line-height:1.4;transition:all 0.15s ease;cursor:pointer;box-shadow:0 1px 0 rgba(0,0,0,0.02);">
                                                                    <div><strong><?php echo esc_html($customer_name ?: __('Guest', 'aurabookpro')); ?></strong></div>
                                                                    <div><?php echo esc_html($service_name); ?></div>
                                                                    <div><span class="aurabookpro-status-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span></div>
                                                                </div>
                                                            </a>
                                                        <?php endforeach; ?>
                                                    <?php else : ?>
                                                        <span style="color:#8c8f94;">—</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>

                <script>
                (function() {
                    const daySelect = document.getElementById('abp-staff-day-select');
                    const staffFilter = document.getElementById('abp_staff_filter');
                    const statusFilter = document.getElementById('abp_status_filter');
                    const dateFromInput = document.getElementById('abp_date_from');
                    const dateToInput = document.getElementById('abp_date_to');
                    const grids = Array.from(document.querySelectorAll('.abp-staff-day-grid'));
                    const dayCards = Array.from(document.querySelectorAll('.abp-calendar-day'));

                    const updateDateSelection = (selectedDate) => {
                        if (dateFromInput && dateToInput) {
                            dateFromInput.value = selectedDate;
                            dateToInput.value = selectedDate;
                        }

                        const scheduleIsFiltered = (staffFilter && staffFilter.value && staffFilter.value !== '0') || (statusFilter && statusFilter.value && statusFilter.value !== '');
                        const panel = document.querySelector('.abp-staff-day-panel');
                        if (panel) {
                            panel.classList.toggle('abp-staff-day-panel--filtered', scheduleIsFiltered);
                        }

                        grids.forEach((grid) => {
                            const matches = grid.getAttribute('data-date') === selectedDate;
                            grid.hidden = !matches;
                        });

                        dayCards.forEach((card) => {
                            const matches = card.getAttribute('data-date') === selectedDate;
                            card.hidden = !matches;
                            card.classList.toggle('abp-calendar-day--active', matches);
                            card.style.boxShadow = matches ? 'inset 0 0 0 2px #2271b1, 0 0 0 1px rgba(34, 113, 177, 0.15)' : 'none';
                        });
                    };

                    const syncAllControls = () => {
                        if (daySelect && dateFromInput && dateToInput && dateFromInput.value) {
                            daySelect.value = dateFromInput.value;
                            updateDateSelection(dateFromInput.value);
                        }

                        if (staffFilter && document.querySelector('select[name="abp_staff"]') && staffFilter.value !== document.querySelector('select[name="abp_staff"]').value) {
                            staffFilter.value = document.querySelector('select[name="abp_staff"]').value;
                        }

                        if (statusFilter && document.querySelector('select[name="abp_status"]') && statusFilter.value !== document.querySelector('select[name="abp_status"]').value) {
                            statusFilter.value = document.querySelector('select[name="abp_status"]').value;
                        }
                    };

                    if (daySelect) {
                        daySelect.addEventListener('change', function() {
                            updateDateSelection(this.value);
                            if (dateFromInput && dateToInput) {
                                dateFromInput.value = this.value;
                                dateToInput.value = this.value;
                            }
                        });
                    }

                    if (dateFromInput) {
                        dateFromInput.addEventListener('change', function() {
                            if (daySelect && this.value) {
                                daySelect.value = this.value;
                            }
                            updateDateSelection(this.value || (daySelect ? daySelect.value : ''));
                        });
                    }

                    if (dateToInput) {
                        dateToInput.addEventListener('change', function() {
                            if (daySelect && this.value) {
                                daySelect.value = this.value;
                            }
                            updateDateSelection(this.value || (daySelect ? daySelect.value : ''));
                        });
                    }

                    if (staffFilter) {
                        staffFilter.addEventListener('change', function() {
                            const mirrored = document.querySelector('select[name="abp_staff"]');
                            if (mirrored && mirrored !== this) {
                                mirrored.value = this.value;
                            }
                        });
                    }

                    if (statusFilter) {
                        statusFilter.addEventListener('change', function() {
                            const mirrored = document.querySelector('select[name="abp_status"]');
                            if (mirrored && mirrored !== this) {
                                mirrored.value = this.value;
                            }
                        });
                    }

                    syncAllControls();
                    if (daySelect) {
                        updateDateSelection(daySelect.value);
                    }
                })();
                </script>

                <style>
                    .abp-calendar-booking-link,
                    .abp-staff-slot-booking-link {
                        text-decoration: none;
                        color: inherit;
                    }
                    .abp-calendar-booking-link:hover .abp-calendar-booking,
                    .abp-staff-slot-booking-link:hover .abp-staff-slot-booking {
                        background: #f1f5f9;
                        border-color: #c6d0d8;
                        transform: translateY(-1px);
                        box-shadow: 0 10px 18px rgba(50, 74, 96, 0.08);
                    }
                    .abp-calendar-booking-link:focus-visible .abp-calendar-booking,
                    .abp-staff-slot-booking-link:focus-visible .abp-staff-slot-booking {
                        outline: 2px solid #2271b1;
                        outline-offset: 2px;
                    }
                </style>
                <div class="abp-calendar-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-top:18px;">
                    <?php foreach ($calendar_dates as $day) : ?>
                        <?php $day_date = new DateTime($day); ?>
                        <div class="abp-calendar-day" data-date="<?php echo esc_attr($day); ?>" <?php echo $day === $staff_day_date ? '' : 'hidden'; ?> style="border:1px solid #dcdcde;background:#fff;border-radius:8px;padding:10px;min-height:150px;<?php echo $day === $staff_day_date ? 'box-shadow: inset 0 0 0 1px #2271b1;' : ''; ?>">
                            <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#50575e;margin-bottom:8px;"><?php echo esc_html($day_date->format('D')); ?> <span style="font-weight:600;"><?php echo esc_html($day_date->format('M j')); ?></span></div>
                            <?php $day_bookings = $calendar_bookings[$day] ?? []; ?>
                            <?php if (!empty($day_bookings)) : ?>
                                <?php foreach ($day_bookings as $booking) : ?>
                                    <?php
                                    $status = get_post_meta($booking->ID, '_aurabookpro_status', true) ?: 'pending';
                                    $status_label = $this->get_booking_status_label($status);
                                    $status_class = $this->get_booking_status_class($status);
                                    $customer_name = get_post_meta($booking->ID, '_aurabookpro_customer_name', true);
                                    $service_name = get_the_title((int) get_post_meta($booking->ID, '_aurabookpro_service_id', true));
                                    $staff_name = get_the_title((int) get_post_meta($booking->ID, '_aurabookpro_staff_id', true));
                                    ?>
                                    <a class="abp-calendar-booking-link" href="<?php echo esc_url(get_edit_post_link($booking->ID)); ?>" style="display:block;text-decoration:none;color:inherit;">
                                        <div class="abp-calendar-booking" style="padding:7px 8px;border-radius:6px;background:#f6f7f7;border:1px solid #e0e0e0;margin-bottom:6px;line-height:1.35;cursor:pointer;transition:all 0.15s ease;box-shadow:0 1px 0 rgba(0,0,0,0.02);">
                                            <div><strong><?php echo esc_html($customer_name ?: __('Guest', 'aurabookpro')); ?></strong></div>
                                            <div><?php echo esc_html($service_name); ?></div>
                                            <div><?php echo esc_html($staff_name); ?></div>
                                            <div><?php echo esc_html(get_post_meta($booking->ID, '_aurabookpro_booking_time', true)); ?></div>
                                            <div style="margin-top:4px;"><span class="aurabookpro-status-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span></div>
                                            <div style="margin-top:4px;color:#2271b1;font-weight:600;"><?php esc_html_e('Edit', 'aurabookpro'); ?></div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <div style="color:#7a7a7a; font-size:11px;"><?php esc_html_e('No bookings', 'aurabookpro'); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <?php if ($bookings) : ?>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Customer', 'aurabookpro'); ?></th>
                                <th><?php esc_html_e('Service', 'aurabookpro'); ?></th>
                                <th><?php esc_html_e('Staff', 'aurabookpro'); ?></th>
                                <th><?php esc_html_e('Date', 'aurabookpro'); ?></th>
                                <th><?php esc_html_e('Status', 'aurabookpro'); ?></th>
                                <th><?php esc_html_e('Order', 'aurabookpro'); ?></th>
                                <th><?php esc_html_e('Actions', 'aurabookpro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking) : ?>
                                <?php
                                $status = get_post_meta($booking->ID, '_aurabookpro_status', true) ?: 'pending';
                                $wc_order_id = (int) get_post_meta($booking->ID, '_aurabookpro_wc_order_id', true);
                                $status_label = $this->get_booking_status_label($status);
                                $status_class = $this->get_booking_status_class($status);
                                $transition_states = $this->get_booking_status_transitions($status);
                                $order_label = __('No order', 'aurabookpro');
                                $order_link = '';
                                if ($wc_order_id > 0 && class_exists('WC_Order')) {
                                    $order = wc_get_order($wc_order_id);
                                    if ($order) {
                                        $order_label = sprintf(__('Order #%s', 'aurabookpro'), $order->get_order_number());
                                        $order_link = admin_url('post.php?post=' . $order->get_id() . '&action=edit');
                                    }
                                }
                                ?>
                                <tr>
                                    <td><?php echo esc_html(get_post_meta($booking->ID, '_aurabookpro_customer_name', true)); ?></td>
                                    <td><?php echo esc_html(get_the_title((int) get_post_meta($booking->ID, '_aurabookpro_service_id', true))); ?></td>
                                    <td><?php echo esc_html(get_the_title((int) get_post_meta($booking->ID, '_aurabookpro_staff_id', true))); ?></td>
                                    <td><?php echo esc_html(get_post_meta($booking->ID, '_aurabookpro_booking_date', true) . ' ' . get_post_meta($booking->ID, '_aurabookpro_booking_time', true)); ?></td>
                                    <td>
                                        <div class="abp-status-stack">
                                            <span class="aurabookpro-status-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
                                            <?php if (!empty($transition_states)) : ?>
                                                <div class="abp-status-actions">
                                                    <?php foreach ($transition_states as $transition_status) : ?>
                                                        <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=aurabookpro-bookings')); ?>" class="abp-status-form">
                                                            <?php wp_nonce_field('abp_update_booking_status', 'abp_booking_status_nonce'); ?>
                                                            <input type="hidden" name="abp_update_booking_status" value="1" />
                                                            <input type="hidden" name="abp_booking_id" value="<?php echo esc_attr((string) $booking->ID); ?>" />
                                                            <input type="hidden" name="abp_booking_status" value="<?php echo esc_attr($transition_status); ?>" />
                                                            <input type="hidden" name="page" value="aurabookpro-bookings" />
                                                            <button type="submit" class="button button-small abp-status-step-button" title="<?php echo esc_attr(sprintf(__('Set booking to %s', 'aurabookpro'), $this->get_booking_status_label($transition_status))); ?>">
                                                                <?php echo esc_html($this->get_booking_status_label($transition_status)); ?>
                                                            </button>
                                                        </form>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($order_link) : ?>
                                            <a href="<?php echo esc_url($order_link); ?>"><?php echo esc_html($order_label); ?></a>
                                        <?php else : ?>
                                            <?php echo esc_html($order_label); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="abp-row-actions">
                                            <a href="<?php echo esc_url(get_edit_post_link($booking->ID)); ?>"><?php esc_html_e('Edit', 'aurabookpro'); ?></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p><?php esc_html_e('No bookings match the current filters.', 'aurabookpro'); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }

    public function export_bookings_csv($filters = []) {
        $status = isset($filters['status']) ? sanitize_key(wp_unslash($filters['status'])) : '';
        $staff = isset($filters['staff']) ? absint($filters['staff']) : 0;
        $date_from = isset($filters['date_from']) ? sanitize_text_field(wp_unslash($filters['date_from'])) : '';
        $date_to = isset($filters['date_to']) ? sanitize_text_field(wp_unslash($filters['date_to'])) : '';
        $search = isset($filters['search']) ? sanitize_text_field(wp_unslash($filters['search'])) : '';

        $meta_query = [];

        if ($status !== '') {
            $meta_query[] = ['key' => '_aurabookpro_status', 'value' => $status, 'compare' => '='];
        }
        if ($staff > 0) {
            $meta_query[] = ['key' => '_aurabookpro_staff_id', 'value' => (string) $staff, 'compare' => '='];
        }
        if ($date_from !== '') {
            $meta_query[] = ['key' => '_aurabookpro_booking_date', 'value' => $date_from, 'compare' => '>=', 'type' => 'DATE'];
        }
        if ($date_to !== '') {
            $meta_query[] = ['key' => '_aurabookpro_booking_date', 'value' => $date_to, 'compare' => '<=', 'type' => 'DATE'];
        }

        $args = [
            'post_type' => 'aurabookpro_booking',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }

        if ($search !== '') {
            $args['s'] = $search;
        }

        $rows = get_posts($args);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="aurabookpro-bookings.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Booking ID', 'Customer', 'Email', 'Service', 'Staff', 'Date', 'Time', 'Status', 'WC Order ID']);

        foreach ($rows as $row) {
            $status_value = get_post_meta($row->ID, '_aurabookpro_status', true) ?: 'pending';
            $service = get_the_title((int) get_post_meta($row->ID, '_aurabookpro_service_id', true));
            $staff_name = get_the_title((int) get_post_meta($row->ID, '_aurabookpro_staff_id', true));
            $wc_order_id = get_post_meta($row->ID, '_aurabookpro_wc_order_id', true);

            fputcsv($output, [
                $row->ID,
                get_post_meta($row->ID, '_aurabookpro_customer_name', true),
                get_post_meta($row->ID, '_aurabookpro_customer_email', true),
                $service,
                $staff_name,
                get_post_meta($row->ID, '_aurabookpro_booking_date', true),
                get_post_meta($row->ID, '_aurabookpro_booking_time', true),
                $status_value,
                $wc_order_id,
            ]);
        }

        fclose($output);
    }

    public function handle_booking_status_update() {
        if (!isset($_POST['abp_update_booking_status']) || empty($_POST['abp_booking_id']) || !isset($_POST['abp_booking_status'])) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        if (!isset($_POST['abp_booking_status_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['abp_booking_status_nonce'])), 'abp_update_booking_status')) {
            return;
        }

        $booking_id = absint($_POST['abp_booking_id']);
        $status = sanitize_key(wp_unslash($_POST['abp_booking_status']));
        $allowed_statuses = array_keys($this->booking_status_options());

        if (!$booking_id || !in_array($status, $allowed_statuses, true)) {
            return;
        }

        $this->update_booking_status($booking_id, $status);

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => 'aurabookpro-bookings',
                    'abp_status' => isset($_POST['abp_status']) ? sanitize_key(wp_unslash($_POST['abp_status'])) : '',
                    'abp_staff' => isset($_POST['abp_staff']) ? absint($_POST['abp_staff']) : 0,
                    'abp_date_from' => isset($_POST['abp_date_from']) ? sanitize_text_field(wp_unslash($_POST['abp_date_from'])) : '',
                    'abp_date_to' => isset($_POST['abp_date_to']) ? sanitize_text_field(wp_unslash($_POST['abp_date_to'])) : '',
                    'abp_search' => isset($_POST['abp_search']) ? sanitize_text_field(wp_unslash($_POST['abp_search'])) : '',
                    'abp_view' => isset($_POST['abp_view']) ? sanitize_key(wp_unslash($_POST['abp_view'])) : 'list',
                ],
                admin_url('admin.php')
            )
        );
        exit;
    }

    public function update_booking_status($booking_id, $status) {
        $booking_id = absint($booking_id);
        $status = sanitize_key($status);
        $allowed_statuses = array_keys($this->booking_status_options());

        if (!$booking_id || !in_array($status, $allowed_statuses, true)) {
            return false;
        }

        update_post_meta($booking_id, '_aurabookpro_status', $status);

        if ('refunded' === $status) {
            $existing_refund = (float) get_post_meta($booking_id, '_aurabookpro_refund_amount', true);
            if ($existing_refund <= 0) {
                $wc_order_id = (int) get_post_meta($booking_id, '_aurabookpro_wc_order_id', true);
                $order_total = 0;
                if ($wc_order_id > 0 && class_exists('WC_Order')) {
                    $order = wc_get_order($wc_order_id);
                    if ($order) {
                        $order_total = floatval($order->get_total());
                    }
                }
                if ($order_total > 0) {
                    update_post_meta($booking_id, '_aurabookpro_refund_amount', $order_total);
                }
            }
        }

        $post = get_post($booking_id);
        if ($post && 'aurabookpro_booking' === $post->post_type) {
            wp_update_post([
                'ID' => $booking_id,
                'post_status' => $status,
            ]);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'aurabookpro_bookings';
        $wpdb->update(
            $table,
            [
                'status' => $status,
                'updated_at' => current_time('mysql'),
            ],
            ['id' => $booking_id]
        );

        $wc_order_id = (int) get_post_meta($booking_id, '_aurabookpro_wc_order_id', true);
        if ($wc_order_id > 0 && class_exists('WC_Order')) {
            $order = wc_get_order($wc_order_id);
            if ($order) {
                $order_status_map = [
                    'pending' => 'pending',
                    'confirmed' => 'processing',
                    'completed' => 'completed',
                    'cancelled' => 'cancelled',
                    'refunded' => 'refunded',
                ];

                $mapped_status = $order_status_map[$status] ?? 'pending';
                if (method_exists($order, 'update_status')) {
                    $order->update_status($mapped_status, __('Booking status synced from AuraBookPro.', 'aurabookpro'));
                }
            }
        }

        if ('refunded' === $status) {
            $refund_amount = (float) get_post_meta($booking_id, '_aurabookpro_refund_amount', true);
            if ($refund_amount > 0) {
                $this->create_refund($booking_id, $refund_amount, get_post_meta($booking_id, '_aurabookpro_refund_reason', true));
            }
        }

        return true;
    }

    public function get_booking_status_label($status) {
        $labels = [
            'pending' => __('Pending', 'aurabookpro'),
            'confirmed' => __('Confirmed', 'aurabookpro'),
            'completed' => __('Completed', 'aurabookpro'),
            'cancelled' => __('Cancelled', 'aurabookpro'),
            'refunded' => __('Refunded', 'aurabookpro'),
        ];

        return $labels[$status] ?? __('Pending', 'aurabookpro');
    }

    public function get_booking_status_class($status) {
        $classes = [
            'pending' => 'aurabookpro-status-pending',
            'confirmed' => 'aurabookpro-status-confirmed',
            'completed' => 'aurabookpro-status-completed',
            'cancelled' => 'aurabookpro-status-cancelled',
            'refunded' => 'aurabookpro-status-refunded',
        ];

        return $classes[$status] ?? 'aurabookpro-status-pending';
    }

    public function get_booking_status_transitions($status) {
        $status = sanitize_key($status ?: 'pending');

        $transitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'cancelled'],
            'completed' => ['refunded'],
            'cancelled' => ['pending'],
            'refunded' => [],
        ];

        return $transitions[$status] ?? [];
    }

    public function render_availability_page() {
        $staff_members = get_posts([
            'post_type' => 'aurabookpro_staff',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $selected_staff = isset($_GET['staff_id']) ? absint($_GET['staff_id']) : 0;
        $selected_date = isset($_GET['selected_date']) ? sanitize_text_field(wp_unslash($_GET['selected_date'])) : date('Y-m-d');

        if (!$selected_staff && !empty($staff_members)) {
            $selected_staff = (int) $staff_members[0]->ID;
        }

        $selected_service = $selected_staff ? $this->get_default_service_for_staff($selected_staff) : 0;
        $selected_slots = $selected_staff && $selected_service ? $this->get_staff_daily_slots($selected_staff, $selected_service, $selected_date) : [];

        $week_days = [];
        $week_start = new DateTime($selected_date);
        $week_start->modify('monday this week');
        for ($i = 0; $i < 7; $i++) {
            $day = clone $week_start;
            $day->modify('+' . $i . ' day');
            $week_days[] = $day->format('Y-m-d');
        }

        $staff_day_summary = [];
        foreach ($staff_members as $member) {
            $summary = [];
            foreach ($week_days as $day) {
                $summary[$day] = $this->get_staff_day_summary($member->ID, $day);
            }
            $staff_day_summary[$member->ID] = $summary;
        }

        $selected_week_total = 0;
        $selected_week_available = 0;
        if ($selected_staff) {
            foreach ($week_days as $day) {
                $day_summary = $this->get_staff_day_summary($selected_staff, $day);
                $selected_week_total += (int) $day_summary['total'];
                $selected_week_available += (int) $day_summary['available'];
            }
        }
        ?>
        <style>
            .abp-availability-shell {
                max-width: 1200px;
            }
            .abp-availability-topbar {
                display: flex;
                flex-wrap: wrap;
                align-items: end;
                gap: 14px;
                margin: 0 0 18px;
            }
            .abp-availability-summary {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 12px;
                margin: 0 0 18px;
            }
            .abp-availability-tile {
                padding: 14px 16px;
                border: 1px solid #dcdcde;
                border-radius: 10px;
                background: #fff;
                box-shadow: 0 1px 1px rgba(0,0,0,0.02);
            }
            .abp-availability-tile span {
                display: block;
                color: #5f6368;
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                margin-bottom: 6px;
            }
            .abp-availability-tile strong {
                font-size: 28px;
                line-height: 1;
                color: #1d2327;
            }
            .abp-week-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 12px;
                margin: 0 0 18px;
            }
            .abp-week-card {
                display: block;
                padding: 12px;
                border: 1px solid #dcdcde;
                border-radius: 10px;
                background: #fff;
                color: #1d2327;
                text-decoration: none;
                transition: all 0.15s ease;
            }
            .abp-week-card:hover {
                border-color: #a7c1d9;
                box-shadow: 0 6px 18px rgba(34, 113, 177, 0.08);
            }
            .abp-week-card.is-active {
                border-color: #2271b1;
                box-shadow: inset 0 0 0 1px #2271b1;
                background: #f8fbff;
            }
            .abp-week-card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }
            .abp-week-card-header strong {
                font-size: 13px;
                color: #1d2327;
            }
            .abp-week-card-meta {
                display: grid;
                gap: 6px;
                color: #5f6368;
                font-size: 12px;
            }
            .abp-day-link {
                color: inherit;
                text-decoration: none;
            }
            .abp-slot-board {
                padding: 18px;
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 12px;
                margin-top: 18px;
            }
            .abp-slot-list {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 10px;
            }
            .abp-slot-pill {
                display: inline-flex;
                align-items: center;
                padding: 8px 10px;
                border-radius: 999px;
                border: 1px solid #dcdcde;
                background: #f6f7f7;
                color: #1d2327;
                font-size: 12px;
                font-weight: 600;
            }
            .abp-slot-pill.is-booked {
                background: #f2f2f2;
                border-color: #d0d0d0;
                color: #6c6f73;
            }
            .abp-slot-pill.is-available {
                background: #edf8f0;
                border-color: #b9d7c6;
                color: #1f5a3c;
            }
            .abp-slot-pill.is-past {
                background: #f9f9f9;
                border-color: #e5e5e5;
                color: #8c8f94;
            }
        </style>
        <div class="wrap abp-availability-shell">
            <h1><?php esc_html_e('Staff Availability', 'aurabookpro'); ?></h1>

            <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="abp-availability-topbar" id="abp-availability-form">
                <input type="hidden" name="page" value="aurabookpro-availability" />
                <div>
                    <label for="staff_id" style="display:block;margin-bottom:6px;font-weight:600;"><?php esc_html_e('Staff member', 'aurabookpro'); ?></label>
                    <select name="staff_id" id="staff_id">
                        <option value="0"><?php esc_html_e('Select staff', 'aurabookpro'); ?></option>
                        <?php foreach ($staff_members as $member) : ?>
                            <option value="<?php echo esc_attr($member->ID); ?>" <?php selected($selected_staff, $member->ID); ?>>
                                <?php echo esc_html(get_the_title($member)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="selected_date" style="display:block;margin-bottom:6px;font-weight:600;"><?php esc_html_e('Date', 'aurabookpro'); ?></label>
                    <input type="date" name="selected_date" id="selected_date" value="<?php echo esc_attr($selected_date); ?>" />
                </div>
                <div>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Load', 'aurabookpro'); ?></button>
                </div>
            </form>

            <?php if ($selected_staff) : ?>
                <div class="abp-availability-summary">
                    <div class="abp-availability-tile">
                        <span><?php esc_html_e('This week', 'aurabookpro'); ?></span>
                        <strong><?php echo esc_html((string) $selected_week_total); ?></strong>
                    </div>
                    <div class="abp-availability-tile">
                        <span><?php esc_html_e('Available', 'aurabookpro'); ?></span>
                        <strong><?php echo esc_html((string) $selected_week_available); ?></strong>
                    </div>
                    <div class="abp-availability-tile">
                        <span><?php esc_html_e('Selected day', 'aurabookpro'); ?></span>
                        <strong><?php echo esc_html((string) count($selected_slots)); ?></strong>
                    </div>
                </div>
            <?php endif; ?>

            <div class="abp-week-grid">
                <?php foreach ($week_days as $day) : ?>
                    <?php
                    $this_date = new DateTime($day);
                    $day_summary = $selected_staff ? $this->get_staff_day_summary($selected_staff, $day) : ['available' => 0, 'booked' => 0, 'total' => 0];
                    $day_url = admin_url('admin.php?page=aurabookpro-availability&staff_id=' . rawurlencode((string) $selected_staff) . '&selected_date=' . rawurlencode($day));
                    ?>
                    <a class="abp-week-card <?php echo $day === $selected_date ? 'is-active' : ''; ?> abp-day-link" href="<?php echo esc_url($day_url); ?>">
                        <div class="abp-week-card-header">
                            <strong><?php echo esc_html($this_date->format('D')); ?></strong>
                            <span><?php echo esc_html($this_date->format('M j')); ?></span>
                        </div>
                        <div class="abp-week-card-meta">
                            <span><?php echo esc_html(sprintf(__('Available: %d', 'aurabookpro'), $day_summary['available'])); ?></span>
                            <span><?php echo esc_html(sprintf(__('Booked: %d', 'aurabookpro'), $day_summary['booked'])); ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($selected_staff && $selected_service) : ?>
                <div class="abp-slot-board">
                    <h2 style="margin:0 0 10px;"><?php echo esc_html(get_the_title($selected_staff)); ?> — <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($selected_date))); ?></h2>
                    <?php if (!empty($selected_slots)) : ?>
                        <div class="abp-slot-list">
                            <?php foreach ($selected_slots as $slot) : ?>
                                <?php
                                $slot_class = 'is-available';
                                $slot_time = strtotime($slot['start']);
                                if ($slot_time < current_time('timestamp')) {
                                    $slot_class = 'is-past';
                                }
                                ?>
                                <span class="abp-slot-pill <?php echo esc_attr($slot_class); ?>"><?php echo esc_html($slot['label']); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p><?php esc_html_e('No available slots found for this staff member on the selected date.', 'aurabookpro'); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('abp-availability-form');
            if (!form) {
                return;
            }

            const staffField = document.getElementById('staff_id');
            const dateField = document.getElementById('selected_date');
            if (!staffField || !dateField) {
                return;
            }

            const updateAvailabilityView = function () {
                const params = new URLSearchParams();
                params.set('page', 'aurabookpro-availability');

                if (staffField.value && staffField.value !== '0') {
                    params.set('staff_id', staffField.value);
                }

                if (dateField.value) {
                    params.set('selected_date', dateField.value);
                }

                const baseUrl = '<?php echo esc_url(admin_url('admin.php')); ?>';
                window.location.href = baseUrl + '?' + params.toString();
            };

            staffField.addEventListener('change', updateAvailabilityView);
            dateField.addEventListener('change', updateAvailabilityView);
        });
        </script>
        <?php
    }

    public function get_day_bookings_for_staff($staff_id, $date) {
        global $wpdb;
        $table = $wpdb->prefix . 'aurabookpro_bookings';

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE staff_id = %d AND status != 'cancelled' AND DATE(start_at) = %s",
            absint($staff_id),
            $date
        ));
    }

    public function apply_coupon_to_cart($coupon_code) {
        if (empty($coupon_code) || !class_exists('WooCommerce') || !isset(WC()->cart)) {
            return true;
        }

        $coupon_code = trim(strtoupper($coupon_code));
        if ('' === $coupon_code) {
            return true;
        }

        if (WC()->cart->has_discount($coupon_code)) {
            return true;
        }

        $coupon = new WC_Coupon($coupon_code);
        if (!$coupon->get_id()) {
            return false;
        }

        return WC()->cart->apply_coupon($coupon_code);
    }

    public function create_refund($booking_id, $amount, $reason = '') {
        if ($booking_id <= 0 || !is_numeric($amount) || floatval($amount) <= 0) {
            return false;
        }

        $amount = floatval($amount);
        $reason = sanitize_text_field($reason);

        global $wpdb;
        $table = $wpdb->prefix . 'aurabookpro_refunds';

        $order_id = (int) get_post_meta($booking_id, '_aurabookpro_wc_order_id', true);

        $wpdb->insert(
            $table,
            [
                'booking_id' => $booking_id,
                'wc_order_id' => $order_id,
                'refund_amount' => $amount,
                'refund_reason' => $reason,
                'status' => 'pending',
                'created_at' => current_time('mysql'),
            ]
        );

        update_post_meta($booking_id, '_aurabookpro_refund_amount', $amount);
        update_post_meta($booking_id, '_aurabookpro_refund_reason', $reason);
        update_post_meta($booking_id, '_aurabookpro_status', 'refunded');

        if ($order_id > 0 && class_exists('WC_Order')) {
            $order = wc_get_order($order_id);
            if ($order && method_exists($order, 'update_status')) {
                $order->update_status('refunded', __('Booking refunded from AuraBookPro admin.', 'aurabookpro'));
            }
        }

        return true;
    }

    public function sync_booking_from_order($order_id) {
        if (empty($order_id) || !class_exists('WC_Order')) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $booking_id = 0;
        foreach ($order->get_items() as $item) {
            $meta_booking_id = $item->get_meta('aurabookpro_booking_id');
            if (!empty($meta_booking_id)) {
                $booking_id = absint($meta_booking_id);
                break;
            }
        }

        if (!$booking_id) {
            return;
        }

        $status = $order->get_status();
        $booking_status = 'pending';
        if (in_array($status, ['processing', 'completed'], true)) {
            $booking_status = 'confirmed';
        } elseif ('cancelled' === $status) {
            $booking_status = 'cancelled';
        } elseif ('refunded' === $status) {
            $booking_status = 'refunded';
        }

        $booking_status = $this->normalize_booking_status($booking_status);

        update_post_meta($booking_id, '_aurabookpro_wc_order_id', $order_id);
        update_post_meta($booking_id, '_aurabookpro_status', $booking_status);

        global $wpdb;
        $table = $wpdb->prefix . 'aurabookpro_bookings';
        $wpdb->update(
            $table,
            [
                'wc_order_id' => $order_id,
                'status' => $booking_status,
                'updated_at' => current_time('mysql'),
            ],
            ['id' => $booking_id]
        );
    }

    public function sync_booking_status_from_order($order_id, $old_status, $new_status, $order) {
        if (!$order || !is_object($order)) {
            return;
        }

        foreach ($order->get_items() as $item) {
            $booking_id = absint($item->get_meta('aurabookpro_booking_id'));
            if (!$booking_id) {
                continue;
            }

            $status_map = [
                'pending' => 'pending',
                'processing' => 'confirmed',
                'completed' => 'confirmed',
                'cancelled' => 'cancelled',
                'refunded' => 'refunded',
            ];

            $booking_status = $this->normalize_booking_status($status_map[$new_status] ?? 'pending');
            update_post_meta($booking_id, '_aurabookpro_status', $booking_status);
            update_post_meta($booking_id, '_aurabookpro_wc_order_id', $order_id);

            global $wpdb;
            $table = $wpdb->prefix . 'aurabookpro_bookings';
            $wpdb->update(
                $table,
                [
                    'wc_order_id' => $order_id,
                    'status' => $booking_status,
                    'updated_at' => current_time('mysql'),
                ],
                ['id' => $booking_id]
            );
        }
    }

    public function build_booking_summary_from_order($order_id) {
        if (empty($order_id) || !class_exists('WC_Order')) {
            return [];
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return [];
        }

        $booking_id = 0;
        foreach ($order->get_items() as $item) {
            $meta_booking_id = $item->get_meta('aurabookpro_booking_id');
            if (!empty($meta_booking_id)) {
                $booking_id = absint($meta_booking_id);
                break;
            }
        }

        if (!$booking_id) {
            return [];
        }

        $service_id = (int) get_post_meta($booking_id, '_aurabookpro_service_id', true);
        $staff_id = (int) get_post_meta($booking_id, '_aurabookpro_staff_id', true);

        return [
            'booking_id' => $booking_id,
            'service_name' => $service_id ? get_the_title($service_id) : '',
            'staff_name' => $staff_id ? get_the_title($staff_id) : '',
            'date' => get_post_meta($booking_id, '_aurabookpro_booking_date', true),
            'time' => get_post_meta($booking_id, '_aurabookpro_booking_time', true),
            'customer_name' => get_post_meta($booking_id, '_aurabookpro_customer_name', true),
            'status' => get_post_meta($booking_id, '_aurabookpro_status', true) ?: 'pending',
        ];
    }

    public function sync_booking_details_to_checkout_order($order, $data) {
        if (!class_exists('WooCommerce') || !isset(WC()->cart)) {
            return;
        }

        foreach (WC()->cart->get_cart() as $cart_item) {
            $booking_id = !empty($cart_item['aurabookpro_booking_id']) ? absint($cart_item['aurabookpro_booking_id']) : 0;
            if (!$booking_id) {
                continue;
            }

            $service_id = !empty($cart_item['aurabookpro_service_id']) ? absint($cart_item['aurabookpro_service_id']) : 0;
            $staff_id = !empty($cart_item['aurabookpro_staff_id']) ? absint($cart_item['aurabookpro_staff_id']) : 0;
            $customer_first_name = !empty($cart_item['aurabookpro_customer_first_name']) ? sanitize_text_field($cart_item['aurabookpro_customer_first_name']) : get_post_meta($booking_id, '_aurabookpro_customer_first_name', true);
            $customer_last_name = !empty($cart_item['aurabookpro_customer_last_name']) ? sanitize_text_field($cart_item['aurabookpro_customer_last_name']) : get_post_meta($booking_id, '_aurabookpro_customer_last_name', true);
            $customer_name = !empty($cart_item['aurabookpro_customer_name']) ? sanitize_text_field($cart_item['aurabookpro_customer_name']) : trim($customer_first_name . ' ' . $customer_last_name);
            $customer_email = !empty($cart_item['aurabookpro_customer_email']) ? sanitize_email($cart_item['aurabookpro_customer_email']) : '';
            $booking_date = !empty($cart_item['aurabookpro_booking_date']) ? sanitize_text_field($cart_item['aurabookpro_booking_date']) : get_post_meta($booking_id, '_aurabookpro_booking_date', true);
            $booking_time = !empty($cart_item['aurabookpro_booking_time']) ? sanitize_text_field($cart_item['aurabookpro_booking_time']) : get_post_meta($booking_id, '_aurabookpro_booking_time', true);
            $coupon_code = !empty($cart_item['aurabookpro_coupon_code']) ? sanitize_text_field($cart_item['aurabookpro_coupon_code']) : '';

            if ($customer_first_name || $customer_last_name || $customer_name) {
                $order->set_billing_first_name($customer_first_name ?: preg_split('/\s+/', trim((string) $customer_name), 2)[0] ?? $customer_name);
                $order->set_billing_last_name($customer_last_name ?: preg_split('/\s+/', trim((string) $customer_name), 2)[1] ?? '');
            }

            if ($customer_email) {
                $order->set_billing_email($customer_email);
            }

            $order->update_meta_data('_aurabookpro_booking_id', $booking_id);
            $order->update_meta_data('_aurabookpro_service_id', $service_id);
            $order->update_meta_data('_aurabookpro_staff_id', $staff_id);
            $order->update_meta_data('_aurabookpro_customer_first_name', $customer_first_name);
            $order->update_meta_data('_aurabookpro_customer_last_name', $customer_last_name);
            $order->update_meta_data('_aurabookpro_customer_name', $customer_name);
            $order->update_meta_data('_aurabookpro_customer_email', $customer_email);
            $order->update_meta_data('_aurabookpro_booking_date', $booking_date);
            $order->update_meta_data('_aurabookpro_booking_time', $booking_time);
            $order->update_meta_data('_aurabookpro_coupon_code', $coupon_code);

            if ($service_id) {
                $order->update_meta_data('_aurabookpro_service_name', get_the_title($service_id));
            }

            if ($staff_id) {
                $order->update_meta_data('_aurabookpro_staff_name', get_the_title($staff_id));
            }

            break;
        }
    }

    public function normalize_booking_status($status) {
        $normalized = sanitize_key($status ?: 'pending');
        $allowed = array_keys($this->booking_status_options());

        return in_array($normalized, $allowed, true) ? $normalized : 'pending';
    }

    public function get_first_booking_cart_data() {
        if (!class_exists('WooCommerce') || !isset(WC()->cart)) {
            return [];
        }

        $items = WC()->cart->get_cart();
        foreach ($items as $cart_item) {
            $booking_id = !empty($cart_item['aurabookpro_booking_id']) ? absint($cart_item['aurabookpro_booking_id']) : 0;
            if (!$booking_id) {
                continue;
            }

            $full_name = !empty($cart_item['aurabookpro_customer_name']) ? sanitize_text_field($cart_item['aurabookpro_customer_name']) : get_post_meta($booking_id, '_aurabookpro_customer_name', true);
            $customer_first_name = !empty($cart_item['aurabookpro_customer_first_name']) ? sanitize_text_field($cart_item['aurabookpro_customer_first_name']) : get_post_meta($booking_id, '_aurabookpro_customer_first_name', true);
            $customer_last_name = !empty($cart_item['aurabookpro_customer_last_name']) ? sanitize_text_field($cart_item['aurabookpro_customer_last_name']) : get_post_meta($booking_id, '_aurabookpro_customer_last_name', true);
            $name_parts = preg_split('/\s+/', trim((string) ($customer_first_name || $customer_last_name ? trim($customer_first_name . ' ' . $customer_last_name) : $full_name)), 2);

            return [
                'customer_name' => $full_name,
                'customer_first_name' => $customer_first_name ?: ($name_parts[0] ?? ''),
                'customer_last_name' => $customer_last_name ?: ($name_parts[1] ?? ''),
                'customer_email' => !empty($cart_item['aurabookpro_customer_email']) ? sanitize_email($cart_item['aurabookpro_customer_email']) : get_post_meta($booking_id, '_aurabookpro_customer_email', true),
                'booking_date' => !empty($cart_item['aurabookpro_booking_date']) ? sanitize_text_field($cart_item['aurabookpro_booking_date']) : get_post_meta($booking_id, '_aurabookpro_booking_date', true),
                'booking_time' => !empty($cart_item['aurabookpro_booking_time']) ? sanitize_text_field($cart_item['aurabookpro_booking_time']) : get_post_meta($booking_id, '_aurabookpro_booking_time', true),
                'service_name' => !empty($cart_item['aurabookpro_service_id']) ? get_the_title(absint($cart_item['aurabookpro_service_id'])) : get_post_meta($booking_id, '_aurabookpro_service_id', true),
                'staff_name' => !empty($cart_item['aurabookpro_staff_id']) ? get_the_title(absint($cart_item['aurabookpro_staff_id'])) : get_post_meta($booking_id, '_aurabookpro_staff_id', true),
            ];
        }

        return [];
    }

    public function prefill_checkout_from_booking_data($value, $key) {
        if ('' !== $value && null !== $value && !empty($value)) {
            return $value;
        }

        $booking_data = $this->get_first_booking_cart_data();
        if (empty($booking_data)) {
            return $value;
        }

        switch ($key) {
            case 'billing_first_name':
                return $booking_data['customer_first_name'] ?? $value;
            case 'billing_last_name':
                return $booking_data['customer_last_name'] ?? $value;
            case 'billing_email':
                return $booking_data['customer_email'] ?? $value;
            default:
                return $value;
        }
    }

    public function render_checkout_booking_summary() {
        if (is_admin() || !class_exists('WooCommerce') || !isset(WC()->cart)) {
            return;
        }

        $items = WC()->cart->get_cart();
        $booking_details = [];

        foreach ($items as $cart_item) {
            $booking_id = !empty($cart_item['aurabookpro_booking_id']) ? absint($cart_item['aurabookpro_booking_id']) : 0;
            if (!$booking_id) {
                continue;
            }

            $service_id = !empty($cart_item['aurabookpro_service_id']) ? absint($cart_item['aurabookpro_service_id']) : 0;
            $staff_id = !empty($cart_item['aurabookpro_staff_id']) ? absint($cart_item['aurabookpro_staff_id']) : 0;
            $customer_name = !empty($cart_item['aurabookpro_customer_name']) ? sanitize_text_field($cart_item['aurabookpro_customer_name']) : get_post_meta($booking_id, '_aurabookpro_customer_name', true);
            $customer_email = !empty($cart_item['aurabookpro_customer_email']) ? sanitize_email($cart_item['aurabookpro_customer_email']) : get_post_meta($booking_id, '_aurabookpro_customer_email', true);
            $date = !empty($cart_item['aurabookpro_booking_date']) ? sanitize_text_field($cart_item['aurabookpro_booking_date']) : get_post_meta($booking_id, '_aurabookpro_booking_date', true);
            $time = !empty($cart_item['aurabookpro_booking_time']) ? sanitize_text_field($cart_item['aurabookpro_booking_time']) : get_post_meta($booking_id, '_aurabookpro_booking_time', true);

            $booking_details[] = [
                'booking_id' => $booking_id,
                'service_name' => $service_id ? get_the_title($service_id) : __('Appointment', 'aurabookpro'),
                'staff_name' => $staff_id ? get_the_title($staff_id) : __('Provider', 'aurabookpro'),
                'date' => $date,
                'time' => $time,
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
            ];
        }

        if (empty($booking_details)) {
            return;
        }

        $subtotal = floatval(WC()->cart->get_subtotal());
        $discount_total = floatval(WC()->cart->get_discount_total());
        $order_total = floatval(WC()->cart->get_total());
        $applied_coupons = WC()->cart->get_applied_coupons();

        ?>
        <div class="aurabookpro-checkout-summary-wrap">
            <div class="aurabookpro-checkout-summary">
                <div class="aurabookpro-summary-header">
                    <div class="aurabookpro-summary-meta">
                        <h3><?php esc_html_e('Your booking summary', 'aurabookpro'); ?></h3>
                        <span class="aurabookpro-summary-count"><?php echo esc_html((string) count($booking_details)); ?></span>
                    </div>
                    <span class="aurabookpro-summary-state"><?php esc_html_e('Ready to pay', 'aurabookpro'); ?></span>
                </div>

                <?php foreach ($booking_details as $detail) : ?>
                    <div class="aurabookpro-checkout-summary-item">
                        <div class="aurabookpro-checkout-roundel"><?php echo esc_html(strtoupper(substr((string) ($detail['service_name'] ?: 'A'), 0, 1))); ?></div>
                            <strong><?php echo esc_html($detail['service_name']); ?></strong>
                            <div><?php echo esc_html($detail['staff_name']); ?></div>
                            <div><?php echo esc_html(($detail['date'] ?: __('Selected date', 'aurabookpro')) . ' ' . ($detail['time'] ?: __('selected time', 'aurabookpro'))); ?></div>
                            <div><?php echo esc_html($detail['customer_name'] ?: __('Guest booking', 'aurabookpro')); ?></div>
                            <?php if (!empty($detail['customer_email'])) : ?>
                                <div><?php echo esc_html($detail['customer_email']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (!empty($applied_coupons)) : ?>
                    <div class="aurabookpro-coupon-pill">
                        <span><?php esc_html_e('Coupon', 'aurabookpro'); ?></span>
                        <strong><?php echo esc_html(implode(', ', array_map('strtoupper', $applied_coupons))); ?></strong>
                    </div>
                <?php endif; ?>

                <div class="aurabookpro-finance-group" aria-label="booking totals">
                    <div class="aurabookpro-finance-row">
                        <span><?php esc_html_e('Subtotal', 'aurabookpro'); ?></span>
                        <strong><?php echo esc_html(wc_price($subtotal)); ?></strong>
                    </div>
                    <?php if ($discount_total > 0) : ?>
                        <div class="aurabookpro-finance-row discounted">
                            <span><?php esc_html_e('Discount', 'aurabookpro'); ?></span>
                            <strong>-<?php echo esc_html(wc_price($discount_total)); ?></strong>
                        </div>
                    <?php endif; ?>
                    <div class="aurabookpro-finance-row total">
                        <span><?php esc_html_e('Total', 'aurabookpro'); ?></span>
                        <strong><?php echo esc_html(wc_price($order_total)); ?></strong>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function render_coupon_review_status() {
        if (is_admin() || !class_exists('WooCommerce') || !isset(WC()->cart)) {
            return;
        }

        $applied_coupons = WC()->cart->get_applied_coupons();
        $discount_total = floatval(WC()->cart->get_discount_total());
        $order_total = floatval(WC()->cart->get_total());

        if (empty($applied_coupons) && $discount_total <= 0) {
            return;
        }

        ?>
        <div class="aurabookpro-coupon-review">
            <div class="aurabookpro-coupon-head">
                <strong><?php esc_html_e('Coupon applied', 'aurabookpro'); ?>:</strong>
                <span><?php echo esc_html(!empty($applied_coupons) ? implode(', ', array_map('strtoupper', $applied_coupons)) : __('No active coupon', 'aurabookpro')); ?></span>
            </div>
            <?php if ($discount_total > 0) : ?>
                <div class="aurabookpro-finance-row discounted compact">
                    <span><?php esc_html_e('Discount value', 'aurabookpro'); ?></span>
                    <strong>-<?php echo esc_html(wc_price($discount_total)); ?></strong>
                </div>
            <?php endif; ?>
            <div class="aurabookpro-finance-row total compact">
                <span><?php esc_html_e('Order total', 'aurabookpro'); ?></span>
                <strong><?php echo esc_html(wc_price($order_total)); ?></strong>
            </div>
        </div>
        <?php
    }

    public function handle_thankyou_confirmation($order_id) {
        if (empty($order_id)) {
            return;
        }

        $this->sync_booking_from_order($order_id);

        $summary = $this->build_booking_summary_from_order($order_id);
        if (empty($summary)) {
            return;
        }

        $order = wc_get_order($order_id);
        $refund_amount = (float) get_post_meta($summary['booking_id'], '_aurabookpro_refund_amount', true);
        $booking_status = get_post_meta($summary['booking_id'], '_aurabookpro_status', true) ?: 'pending';
        $status_label = $this->get_booking_status_label($booking_status);
        $discount_total = $order ? floatval($order->get_discount_total()) : 0;
        $order_total = $order ? floatval($order->get_total()) : 0;
        $status_url = $order ? $order->get_view_order_url() : '';
        $booking_url = get_edit_post_link($summary['booking_id']);

        $status_chip_map = [
            'pending' => __('Pending', 'aurabookpro'),
            'confirmed' => __('Confirmed', 'aurabookpro'),
            'completed' => __('Completed', 'aurabookpro'),
            'cancelled' => __('Cancelled', 'aurabookpro'),
            'refunded' => __('Refunded', 'aurabookpro'),
        ];
        $status_chip = $status_chip_map[$booking_status] ?? $status_label;

        echo '<div class="aurabookpro-order-confirmation">';
        echo '<div class="aurabookpro-order-head">';
        echo '<h3>' . esc_html__('Your Aura appointment is reserved', 'aurabookpro') . '</h3>';
        echo '<span class="aurabookpro-status-badge ' . esc_attr($this->get_booking_status_class($booking_status)) . '">' . esc_html($status_label) . '</span>';
        echo '</div>';
        echo '<p>' . sprintf(esc_html__('Hi %s, your appointment is locked in and your order can be tracked below.', 'aurabookpro'), esc_html($summary['customer_name'] ?: 'there')) . '</p>';

        echo '<div class="aurabookpro-booking-card">';
        echo '<div class="aurabookpro-booking-card__top">';
        echo '<div class="aurabookpro-booking-card__label">' . esc_html__('Booking details', 'aurabookpro') . '</div>';
        echo '<span class="aurabookpro-booking-card__chip">' . esc_html($status_chip) . '</span>';
        echo '</div>';
        echo '<div class="aurabookpro-booking-card__body">';
        echo '<div class="aurabookpro-booking-card__service">' . esc_html($summary['service_name']) . '</div>';
        echo '<div class="aurabookpro-booking-card__meta"><span>' . esc_html__('Provider', 'aurabookpro') . '</span><strong>' . esc_html($summary['staff_name']) . '</strong></div>';
        echo '<div class="aurabookpro-booking-card__meta"><span>' . esc_html__('Date & time', 'aurabookpro') . '</span><strong>' . esc_html(($summary['date'] ?: __('Selected date', 'aurabookpro')) . ' ' . ($summary['time'] ?: __('selected time', 'aurabookpro'))) . '</strong></div>';
        echo '<div class="aurabookpro-booking-card__meta"><span>' . esc_html__('Order', 'aurabookpro') . '</span><strong>#' . esc_html($order_id) . '</strong></div>';
        echo '</div>';
        echo '</div>';

        echo '<div class="aurabookpro-order-grid">';
        echo '<div class="aurabookpro-order-detail"><span class="aurabookpro-order-label">' . esc_html__('Service', 'aurabookpro') . '</span><strong class="aurabookpro-order-value">' . esc_html($summary['service_name']) . '</strong></div>';
        echo '<div class="aurabookpro-order-detail"><span class="aurabookpro-order-label">' . esc_html__('Provider', 'aurabookpro') . '</span><strong class="aurabookpro-order-value">' . esc_html($summary['staff_name']) . '</strong></div>';
        echo '<div class="aurabookpro-order-detail"><span class="aurabookpro-order-label">' . esc_html__('Date & time', 'aurabookpro') . '</span><strong class="aurabookpro-order-value">' . esc_html(($summary['date'] ?: __('Selected date', 'aurabookpro')) . ' ' . ($summary['time'] ?: __('selected time', 'aurabookpro'))) . '</strong></div>';
        echo '<div class="aurabookpro-order-detail"><span class="aurabookpro-order-label">' . esc_html__('Order total', 'aurabookpro') . '</span><strong class="aurabookpro-order-value">' . esc_html(wc_price($order_total)) . '</strong></div>';
        echo '</div>';

        echo '<div class="aurabookpro-order-totals">';
        echo '<div class="aurabookpro-finance-row"><span>' . esc_html__('Subtotal', 'aurabookpro') . '</span><strong>' . esc_html(wc_price(floatval($order ? $order->get_subtotal() : 0))) . '</strong></div>';
        if ($discount_total > 0) {
            echo '<div class="aurabookpro-finance-row discounted"><span>' . esc_html__('Discount', 'aurabookpro') . '</span><strong>- ' . esc_html(wc_price($discount_total)) . '</strong></div>';
        }
        echo '<div class="aurabookpro-finance-row total"><span>' . esc_html__('Total', 'aurabookpro') . '</span><strong>' . esc_html(wc_price($order_total)) . '</strong></div>';
        echo '</div>';

        if ($booking_status === 'refunded' || $refund_amount > 0) {
            $refund_label = wc_price($refund_amount);
            echo '<div class="aurabookpro-refund-banner">';
            echo esc_html__('Refund status:', 'aurabookpro') . ' ' . esc_html__('A refund of', 'aurabookpro') . ' ' . esc_html($refund_label) . ' ' . esc_html__('has been recorded for this booking.', 'aurabookpro');
            echo '</div>';
        }

        echo '<div class="aurabookpro-order-actions">';
        if ($status_url) {
            echo '<a class="aurabookpro-order-link" href="' . esc_url($status_url) . '">' . esc_html__('View order status', 'aurabookpro') . '</a>';
        }
        if ($booking_url) {
            echo '<a class="aurabookpro-order-link secondary" href="' . esc_url($booking_url) . '">' . esc_html__('View booking details', 'aurabookpro') . '</a>';
        }
        echo '</div>';
        echo '</div>';
    }

    public function admin_menu_styles() {
        ?>
        <style>
            #adminmenu .toplevel_page_aurabookpro > a,
            #adminmenu .toplevel_page_aurabookpro > a .wp-menu-name,
            #adminmenu .toplevel_page_aurabookpro > a .wp-menu-image,
            #adminmenu .wp-submenu a[href*="page=aurabookpro"],
            #adminmenu .wp-submenu a[href*="page=aurabookpro-dashboard"],
            #adminmenu .wp-submenu a[href*="page=aurabookpro-services"],
            #adminmenu .wp-submenu a[href*="page=aurabookpro-staff"],
            #adminmenu .wp-submenu a[href*="page=aurabookpro-bookings"],
            #adminmenu .wp-submenu a[href*="taxonomy=aurabookpro_category"] {
                color: #A09086 !important;
            }

            #adminmenu .toplevel_page_aurabookpro > a:hover,
            #adminmenu .toplevel_page_aurabookpro > a:hover .wp-menu-name,
            #adminmenu .toplevel_page_aurabookpro > a:hover .wp-menu-image,
            #adminmenu .wp-submenu a[href*="page=aurabookpro"]:hover,
            #adminmenu .wp-submenu a[href*="page=aurabookpro-dashboard"]:hover,
            #adminmenu .wp-submenu a[href*="page=aurabookpro-services"]:hover,
            #adminmenu .wp-submenu a[href*="page=aurabookpro-staff"]:hover,
            #adminmenu .wp-submenu a[href*="page=aurabookpro-bookings"]:hover,
            #adminmenu .wp-submenu a[href*="taxonomy=aurabookpro_category"]:hover {
                color: #7d6962 !important;
            }

            #adminmenu .toplevel_page_aurabookpro.current > a,
            #adminmenu .toplevel_page_aurabookpro.current > a .wp-menu-name,
            #adminmenu .toplevel_page_aurabookpro.current > a .wp-menu-image,
            #adminmenu .wp-submenu a[href*="page=aurabookpro"].current,
            #adminmenu .wp-submenu a[href*="page=aurabookpro-dashboard"].current,
            #adminmenu .wp-submenu a[href*="page=aurabookpro-services"].current,
            #adminmenu .wp-submenu a[href*="page=aurabookpro-staff"].current,
            #adminmenu .wp-submenu a[href*="page=aurabookpro-bookings"].current,
            #adminmenu .wp-submenu a[href*="taxonomy=aurabookpro_category"].current {
                color: #5d4d47 !important;
            }

            #adminmenu .toplevel_page_aurabookpro > a,
            #adminmenu .wp-submenu a[href*="aurabookpro"] {
                border-radius: 10px;
                padding-top: 6px;
                padding-bottom: 6px;
                transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
            }

            #adminmenu .toplevel_page_aurabookpro > a:hover,
            #adminmenu .wp-submenu a[href*="aurabookpro"]:hover {
                background: rgba(206, 191, 185, 0.12);
                box-shadow: inset 0 0 0 1px rgba(160, 144, 134, 0.08);
            }

            #adminmenu .toplevel_page_aurabookpro.current > a,
            #adminmenu .wp-submenu a[href*="aurabookpro"].current {
                background: rgba(206, 191, 185, 0.14);
                box-shadow: inset 0 0 0 1px rgba(160, 144, 134, 0.12);
            }

            #adminmenu .toplevel_page_aurabookpro > a .wp-menu-image img,
            #adminmenu .toplevel_page_aurabookpro > a .wp-menu-image svg,
            #adminmenu .toplevel_page_aurabookpro > a .wp-menu-image:before,
            #adminmenu .wp-submenu a[href*="aurabookpro"] .wp-menu-image img,
            #adminmenu .wp-submenu a[href*="aurabookpro"] .wp-menu-image svg,
            #adminmenu .wp-submenu a[href*="aurabookpro"] .wp-menu-image:before {
                filter: saturate(1.1) brightness(0.95);
            }

            #adminmenu .toplevel_page_aurabookpro > a:hover .wp-menu-image img,
            #adminmenu .toplevel_page_aurabookpro > a:hover .wp-menu-image svg,
            #adminmenu .toplevel_page_aurabookpro > a:hover .wp-menu-image:before,
            #adminmenu .wp-submenu a[href*="aurabookpro"]:hover .wp-menu-image img,
            #adminmenu .wp-submenu a[href*="aurabookpro"]:hover .wp-menu-image svg,
            #adminmenu .wp-submenu a[href*="aurabookpro"]:hover .wp-menu-image:before {
                filter: brightness(1.12) saturate(1.2);
            }

            #adminmenu .toplevel_page_aurabookpro.current > a .wp-menu-image img,
            #adminmenu .toplevel_page_aurabookpro.current > a .wp-menu-image svg,
            #adminmenu .toplevel_page_aurabookpro.current > a .wp-menu-image:before,
            #adminmenu .wp-submenu a[href*="aurabookpro"].current .wp-menu-image img,
            #adminmenu .wp-submenu a[href*="aurabookpro"].current .wp-menu-image svg,
            #adminmenu .wp-submenu a[href*="aurabookpro"].current .wp-menu-image:before {
                filter: brightness(1.2) saturate(1.25);
            }

            .abp-bookings-filters input,
            .abp-bookings-filters select,
            .abp-bookings-filters .button,
            .wrap input[type="date"],
            .wrap input[type="search"],
            .wrap select,
            .wrap .button,
            .wrap .button-primary {
                border-radius: 10px !important;
                border: 1px solid #D9CFC6 !important;
                min-height: 38px;
                box-shadow: inset 0 0 0 1px rgba(160, 144, 134, 0.04);
            }

            .abp-bookings-filters input:focus,
            .abp-bookings-filters select:focus,
            .wrap input[type="date"]:focus,
            .wrap input[type="search"]:focus,
            .wrap select:focus {
                border-color: #A09086 !important;
                box-shadow: 0 0 0 1px rgba(160, 144, 134, 0.2) !important;
            }

            .postbox {
                border: 1px solid #E8DED7 !important;
                border-radius: 14px !important;
                box-shadow: 0 12px 24px rgba(160, 144, 134, 0.04) !important;
                overflow: hidden;
            }

            .postbox-header {
                background: linear-gradient(180deg, #F9F4F1 0%, #F5F0ED 100%);
                border-bottom: 1px solid #E8DED7;
                padding: 12px 14px;
            }

            .postbox-header h2,
            .postbox-header h3 {
                font-size: 0.97rem !important;
                font-weight: 700 !important;
                color: #352f2d !important;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .postbox .inside {
                padding: 18px 18px 14px !important;
                background: #fff;
            }

            .postbox .inside .form-table th,
            .postbox .inside .form-table td {
                padding-top: 12px;
                padding-bottom: 12px;
                vertical-align: top;
            }

            .postbox .inside .form-table th {
                width: 220px;
                padding-right: 18px;
                color: #403b39;
                font-weight: 700;
                font-size: 13px;
            }

            .postbox .inside .form-table td {
                color: #2d2928;
            }

            .postbox .inside input[type="text"],
            .postbox .inside input[type="email"],
            .postbox .inside input[type="number"],
            .postbox .inside input[type="date"],
            .postbox .inside input[type="time"],
            .postbox .inside select,
            .postbox .inside textarea,
            .postbox .inside .regular-text {
                width: min(100%, 420px);
                min-height: 38px;
                border-radius: 10px !important;
                border: 1px solid #D9CFC6 !important;
                background: linear-gradient(180deg, #ffffff 0%, #fcfaf8 100%);
                color: #2f2a29;
                padding: 8px 10px;
                box-shadow: inset 0 0 0 1px rgba(160, 144, 134, 0.04);
                font-size: 14px;
                line-height: 1.4;
            }

            .postbox .inside input[type="checkbox"],
            .postbox .inside input[type="radio"] {
                width: 16px;
                height: 16px;
                accent-color: #A09086;
                margin-right: 8px;
            }

            .postbox .inside input:focus,
            .postbox .inside select:focus,
            .postbox .inside textarea:focus {
                border-color: #A09086 !important;
                box-shadow: 0 0 0 1px rgba(160, 144, 134, 0.18) !important;
                outline: none;
                background: #fff;
            }

            .postbox .inside label {
                color: #403c3b;
                font-weight: 500;
                line-height: 1.5;
            }

            .postbox .inside .form-table label {
                display: inline-flex;
                align-items: center;
                min-height: 24px;
            }

            .postbox .inside .description,
            .postbox .inside small {
                color: #665f5c;
            }

            .postbox .inside select {
                background-image: linear-gradient(45deg, transparent 50%, #7d6962 50%), linear-gradient(135deg, #7d6962 50%, transparent 50%);
                background-position: calc(100% - 16px) calc(50% - 2px), calc(100% - 11px) calc(50% - 2px);
                background-size: 5px 5px, 5px 5px;
                background-repeat: no-repeat;
                appearance: none;
                padding-right: 34px;
            }

            .postbox .inside a {
                color: #5d4d47;
            }

            .wrap,
            .wrap * {
                font-family: "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, "Helvetica Neue", sans-serif;
            }

            .wrap h1,
            .wrap h2,
            .wrap h3,
            .wrap h4,
            .wrap h5,
            .wrap h6 {
                color: #2a2423;
                letter-spacing: -0.03em;
                line-height: 1.2;
                font-weight: 700;
                margin-top: 0;
            }

            .wrap h1 {
                font-size: 2.05rem;
                font-weight: 700;
                margin-bottom: 18px;
                color: #221f1d;
            }

            .wrap h2 {
                font-size: 1.35rem;
                font-weight: 700;
                margin-bottom: 12px;
                color: #352e2c;
            }

            .wrap h3 {
                font-size: 1.1rem;
                font-weight: 600;
                margin-bottom: 10px;
                color: #3f3835;
            }

            .wrap {
                max-width: 1400px;
            }

            .wrap .page-title-action {
                background: linear-gradient(180deg, #CDC6C3 0%, #A09086 100%);
                border: 1px solid #A09086;
                color: #fff !important;
                border-radius: 10px;
                padding: 7px 14px;
                font-weight: 700;
                box-shadow: 0 10px 18px rgba(160, 144, 134, 0.16);
            }

            .wrap .page-title-action:hover,
            .wrap .page-title-action:focus {
                background: linear-gradient(180deg, #CFB3A9 0%, #A09086 100%);
                color: #fff !important;
                box-shadow: 0 12px 20px rgba(160, 144, 134, 0.2);
            }

            .wrap .widefat {
                border: 1px solid #E7DDD7 !important;
                border-radius: 14px !important;
                overflow: hidden;
                box-shadow: 0 12px 24px rgba(160, 144, 134, 0.04);
                background: #fff;
            }

            .wrap .widefat thead th {
                background: linear-gradient(180deg, #F9F5F2 0%, #F3EEE9 100%);
                border-bottom: 1px solid #E7DDD7;
                padding: 12px 14px;
                color: #3d3634;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .wrap .widefat tbody td {
                padding: 12px 14px;
                border-top: 1px solid #F0E8E3;
                color: #2f2a29;
                vertical-align: middle;
            }

            .wrap .widefat tbody tr:hover {
                background: #FBF8F6;
            }

            .wrap .widefat a,
            .wrap a {
                color: #5d4d47;
            }

            .wrap p {
                color: #544d4b;
                line-height: 1.6;
            }

            .aurabookpro-dashboard-shell {
                position: relative;
                margin-top: 8px;
            }

            .abp-dashboard-hero {
                display: grid;
                grid-template-columns: minmax(0, 1.7fr) minmax(220px, 0.9fr);
                gap: 18px;
                align-items: stretch;
                margin: 8px 0 22px;
                padding: 22px 24px;
                border: 1px solid #E7DDD7;
                border-radius: 18px;
                background: linear-gradient(135deg, #F7F2EE 0%, #F2E9E4 48%, #EEE4DF 100%);
                box-shadow: 0 18px 32px rgba(160, 144, 134, 0.08);
            }

            .abp-dashboard-hero-copy {
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .abp-dashboard-kicker {
                display: inline-flex;
                align-items: center;
                width: fit-content;
                padding: 6px 10px;
                border-radius: 999px;
                border: 1px solid rgba(160, 144, 134, 0.3);
                background: rgba(255,255,255,0.42);
                color: #5d4d47;
                text-transform: uppercase;
                letter-spacing: 0.1em;
                font-size: 10px;
                font-weight: 700;
            }

            .abp-dashboard-hero h1 {
                margin: 12px 0 8px !important;
                font-size: clamp(2rem, 2.4vw, 2.8rem) !important;
                letter-spacing: -0.05em !important;
            }

            .abp-dashboard-hero p {
                margin: 0;
                max-width: 720px;
                color: #564f4d;
                font-size: 15px;
                line-height: 1.65;
            }

            .abp-dashboard-actions {
                display: flex;
                flex-direction: column;
                justify-content: center;
                gap: 10px;
            }

            .abp-dashboard-actions a {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 42px;
                padding: 10px 14px;
                border-radius: 12px;
                border: 1px solid #D9CFC6;
                background: #fff;
                color: #302b2a;
                font-weight: 700;
                text-decoration: none;
                box-shadow: 0 8px 18px rgba(160, 144, 134, 0.08);
            }

            .abp-dashboard-actions a.primary {
                background: linear-gradient(180deg, #CDC6C3 0%, #A09086 100%);
                border-color: #A09086;
                color: #fff;
                box-shadow: 0 12px 22px rgba(160, 144, 134, 0.2);
            }

            .abp-dashboard-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
                gap: 14px;
                margin: 0 0 22px;
            }

            .abp-dashboard-stat {
                background: linear-gradient(180deg, #ffffff 0%, #F8F3F0 100%);
                border: 1px solid #E8DED7;
                border-radius: 14px;
                padding: 14px 16px;
                box-shadow: 0 12px 22px rgba(160, 144, 134, 0.04);
            }

            .abp-dashboard-stat span {
                display: block;
                margin-bottom: 8px;
                color: #6b615d;
                font-size: 10px;
                letter-spacing: 0.09em;
                text-transform: uppercase;
                font-weight: 700;
            }

            .abp-dashboard-stat strong {
                display: block;
                color: #221f1d;
                font-size: clamp(1.7rem, 2vw, 2.3rem);
                letter-spacing: -0.05em;
                line-height: 1.1;
            }

            .abp-dashboard-panels {
                display: grid;
                grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.8fr);
                gap: 18px;
                align-items: start;
            }

            .abp-dashboard-panel {
                background: linear-gradient(180deg, #ffffff 0%, #F9F5F2 100%);
                border: 1px solid #E7DDD7;
                border-radius: 16px;
                box-shadow: 0 14px 26px rgba(160, 144, 134, 0.04);
                overflow: hidden;
            }

            .abp-dashboard-panel-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 16px 18px;
                border-bottom: 1px solid #E7DDD7;
                background: linear-gradient(180deg, #F9F4F1 0%, #F5F1EE 100%);
            }

            .abp-dashboard-panel-header h2,
            .abp-dashboard-panel-header h3 {
                margin: 0 !important;
                font-size: 1rem !important;
                color: #352f2d !important;
                letter-spacing: 0.04em !important;
                text-transform: uppercase !important;
            }

            .abp-dashboard-panel-body {
                padding: 18px;
            }

            .abp-dashboard-panel-body .form-table {
                margin-top: 0;
            }

            .abp-dashboard-panel-body .form-table th {
                width: 180px;
                padding-right: 18px;
            }

            .abp-bookings-wrap {
                padding-top: 6px;
            }

            .abp-bookings-wrap .widefat {
                border-radius: 12px;
                overflow: hidden;
                border: 1px solid #E7DDD7;
                box-shadow: 0 10px 22px rgba(160, 144, 134, 0.04);
            }

            .abp-bookings-wrap .widefat thead th {
                background: #F7F2EE;
                padding: 12px 14px;
                color: #433d3a;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .abp-bookings-wrap .widefat tbody td {
                padding: 12px 14px;
                vertical-align: middle;
            }

            .page-title-action,
            .wrap .button,
            .button-primary,
            .button-secondary {
                font-weight: 600;
                letter-spacing: 0.01em;
            }

            .page-title-action {
                border-radius: 10px !important;
                padding: 6px 12px !important;
                line-height: 1.5;
            }

            .abp-bookings-filters {
                margin: 18px 0 16px;
                padding: 14px 16px;
                border-radius: 14px;
                border: 1px solid #E7DDD7;
                background: rgba(255, 255, 255, 0.72);
                box-shadow: 0 10px 22px rgba(160, 144, 134, 0.03);
            }

            .abp-bookings-filters form {
                gap: 12px;
            }

            .abp-bookings-filters label,
            .abp-bookings-filters .button,
            .abp-bookings-filters select,
            .abp-bookings-filters input,
            .abp-view-toggle .button,
            .abp-summary-tile span,
            .abp-summary-tile strong,
            .aurabookpro-status-badge {
                font-family: "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, "Helvetica Neue", sans-serif;
            }

            .abp-summary-tile strong {
                font-weight: 700;
                letter-spacing: -0.04em;
            }

            .abp-summary-tile span {
                font-weight: 700;
                letter-spacing: 0.09em;
            }

            .aurabookpro-status-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 100px;
                min-height: 28px;
                padding: 5px 12px;
                border-radius: 999px;
                font-size: 10px;
                font-weight: 700;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                line-height: 1.2;
                border: 1px solid transparent;
                box-shadow: inset 0 0 0 1px rgba(50, 38, 35, 0.04);
                white-space: nowrap;
            }

            .aurabookpro-status-pending {
                background: #EFE4DC;
                color: #4C433F;
                border-color: #D2B9AE;
            }

            .aurabookpro-status-confirmed {
                background: #E0DDD9;
                color: #2E2B29;
                border-color: #A89C96;
            }

            .aurabookpro-status-completed {
                background: #D9E8DD;
                color: #214C3F;
                border-color: #6E9A7F;
            }

            .aurabookpro-status-cancelled {
                background: #F4EFEA;
                color: #4a413f;
                border-color: #CDC6C3;
            }

            .aurabookpro-status-refunded {
                background: #F3E3D8;
                color: #7C4F3C;
                border-color: #C58A6B;
            }

            .abp-status-row-actions {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
            }

            .abp-status-stack {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .abp-status-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
            }

            .abp-status-form {
                display: inline-block;
                margin: 0;
            }

            .abp-status-step-button {
                margin: 0;
                min-height: 26px;
                height: auto;
                padding: 4px 8px;
                line-height: 1.3;
                border-radius: 999px;
                border: 1px solid #D9CFC6;
                background: #F9F5F2;
                color: #413b38;
            }

            .abp-row-actions {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .abp-bookings-summary {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
                gap: 12px;
                margin: 18px 0 12px;
            }

            .abp-summary-tile {
                background: linear-gradient(180deg, #ffffff 0%, #F5F1EE 100%);
                border: 1px solid #E4D8CB;
                border-left: 4px solid #A09086;
                border-radius: 14px;
                padding: 12px 14px;
                box-shadow: 0 8px 18px rgba(160, 144, 134, 0.08);
            }

            .abp-summary-tile span {
                display: block;
                font-size: 10px;
                letter-spacing: 0.09em;
                text-transform: uppercase;
                color: #6a5f5a;
                margin-bottom: 6px;
            }

            .abp-summary-tile strong {
                display: block;
                font-size: 24px;
                line-height: 1.1;
                color: #221f1d;
            }

            .abp-summary-tile.pending { border-left-color: #CFB3A9; }
            .abp-summary-tile.confirmed { border-left-color: #A09086; }
            .abp-summary-tile.completed { border-left-color: #CDC6C3; }
            .abp-summary-tile.cancelled { border-left-color: #7d6962; }
            .abp-summary-tile.refunded { border-left-color: #A09086; }
            .abp-summary-tile.paid { border-left-color: #CFB3A9; }

            .abp-view-toggle {
                display: flex;
                gap: 8px;
                align-items: center;
            }

            .abp-view-toggle .button {
                border-radius: 10px;
                border: 1px solid #D9CFC6;
                background: #fff;
                color: #413b38;
                box-shadow: 0 2px 6px rgba(94, 79, 73, 0.06);
                font-weight: 600;
            }

            .abp-view-toggle .button.button-primary {
                background: linear-gradient(180deg, #CDC6C3 0%, #A09086 100%);
                border-color: #A09086;
                color: #fff;
                box-shadow: 0 10px 18px rgba(160, 144, 134, 0.22);
            }

            .abp-calendar-grid {
                margin-top: 12px;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 10px;
            }

            .abp-calendar-day {
                background: linear-gradient(180deg, #ffffff 0%, #F7F2EE 100%);
                border: 1px solid #E6DCD4;
                border-radius: 14px;
                box-shadow: 0 8px 18px rgba(152, 135, 127, 0.06);
                padding: 10px;
                min-height: 150px;
                transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            }

            .abp-calendar-booking {
                font-size: 11px;
                line-height: 1.35;
                background: #F8F4F1;
                border: 1px solid #E9D9CF;
                box-shadow: inset 0 0 0 1px rgba(160, 144, 134, 0.03);
            }

            .abp-staff-day-panel {
                background: linear-gradient(180deg, #ffffff 0%, #F9F3F0 100%);
                border: 1px solid #E4D8CB;
                border-radius: 14px;
                padding: 12px;
                box-shadow: 0 10px 20px rgba(160, 144, 134, 0.06);
                transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            }

            .abp-staff-day-panel--filtered {
                background: linear-gradient(180deg, #F9F4F1 0%, #F4EFEA 100%);
                border-color: #CFB3A9;
                box-shadow: 0 0 0 1px rgba(160, 144, 134, 0.18), 0 10px 20px rgba(160, 144, 134, 0.08);
            }

            .abp-selected-day-badge {
                background: linear-gradient(180deg, #F7F2EE 0%, #F1E8E2 100%);
                border: 1px solid #DCC6BA;
                box-shadow: inset 0 0 0 1px rgba(102, 85, 78, 0.08);
            }

            .abp-staff-slot-booking {
                font-size: 11px;
                background: linear-gradient(180deg, #ffffff 0%, #F8F4F1 100%);
                border: 1px solid #E8D9CF;
                border-radius: 8px;
            }

            .abp-staff-slot-cell {
                min-width: 110px;
                background: #FAF7F4;
            }

            .abp-calendar-day--active {
                background: linear-gradient(180deg, #F5F1EE 0%, #F9F4F1 100%);
                border-color: #A09086;
                box-shadow: 0 0 0 1px rgba(160, 144, 134, 0.15), 0 8px 18px rgba(160, 144, 134, 0.08);
            }
        </style>
        <?php
    }

    public function register_settings() {
        register_setting('aurabookpro_settings', 'aurabookpro_wc_product_id');
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style('aurabookpro-frontend', plugins_url('assets/frontend.css', __DIR__), [], '0.1.0');
        wp_register_script('aurabookpro-frontend', plugins_url('assets/frontend.js', __DIR__), [], '0.1.0', true);
        wp_localize_script('aurabookpro-frontend', 'aurabookproFrontend', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
        wp_enqueue_script('aurabookpro-frontend');
    }

    public function resolve_wc_product_id($service_id = 0, $fallback_product_id = 0) {
        $product_id = absint($fallback_product_id > 0 ? $fallback_product_id : get_option('aurabookpro_wc_product_id', 0));

        if ($product_id > 0) {
            return $product_id;
        }

        if ($service_id > 0) {
            $service_product_id = absint(get_post_meta($service_id, '_aurabookpro_wc_product_id', true));
            if ($service_product_id > 0) {
                return $service_product_id;
            }
        }

        return 0;
    }

    public function render_booking_shortcode() {
        $services = get_posts([
            'post_type' => 'aurabookpro_service',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $staff = get_posts([
            'post_type' => 'aurabookpro_staff',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $locations = get_posts([
            'post_type' => 'aurabookpro_location',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        ob_start();
        ?>
        <div class="aurabookpro-booking" data-plugin="aurabookpro">
            <form id="aurabookpro-booking-form" class="aurabookpro-form">
                <?php wp_nonce_field('aurabookpro_booking', 'aurabookpro_nonce'); ?>

                <div class="aurabookpro-field">
                    <label for="aurabookpro-service"><?php esc_html_e('Service', 'aurabookpro'); ?></label>
                    <select id="aurabookpro-service" name="service_id" required>
                        <option value=""><?php esc_html_e('Select a service', 'aurabookpro'); ?></option>
                        <?php foreach ($services as $service) : ?>
                            <option value="<?php echo esc_attr($service->ID); ?>"><?php echo esc_html(get_the_title($service)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="aurabookpro-field">
                    <label for="aurabookpro-staff"><?php esc_html_e('Staff / Provider', 'aurabookpro'); ?></label>
                    <select id="aurabookpro-staff" name="staff_id" required>
                        <option value=""><?php esc_html_e('Select a provider', 'aurabookpro'); ?></option>
                        <?php foreach ($staff as $person) : ?>
                            <option value="<?php echo esc_attr($person->ID); ?>"><?php echo esc_html(get_the_title($person)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="aurabookpro-field">
                    <label for="aurabookpro-location"><?php esc_html_e('Location', 'aurabookpro'); ?></label>
                    <select id="aurabookpro-location" name="location_id">
                        <option value=""><?php esc_html_e('Select a location', 'aurabookpro'); ?></option>
                        <?php foreach ($locations as $location) : ?>
                            <option value="<?php echo esc_attr($location->ID); ?>"><?php echo esc_html(get_the_title($location)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="aurabookpro-name-row">
                    <div class="aurabookpro-field">
                        <label for="aurabookpro-first-name"><?php esc_html_e('Name', 'aurabookpro'); ?></label>
                        <input type="text" id="aurabookpro-first-name" name="customer_first_name" required>
                    </div>

                    <div class="aurabookpro-field">
                        <label for="aurabookpro-last-name"><?php esc_html_e('Surname', 'aurabookpro'); ?></label>
                        <input type="text" id="aurabookpro-last-name" name="customer_last_name" required>
                    </div>
                </div>

                <div class="aurabookpro-field">
                    <label for="aurabookpro-email"><?php esc_html_e('Email', 'aurabookpro'); ?></label>
                    <input type="email" id="aurabookpro-email" name="customer_email" required>
                </div>

                <div class="aurabookpro-field">
                    <label for="aurabookpro-date"><?php esc_html_e('Date', 'aurabookpro'); ?></label>
                    <input type="date" id="aurabookpro-date" name="booking_date" required>
                </div>

                <div class="aurabookpro-field">
                    <label for="aurabookpro-time"><?php esc_html_e('Time', 'aurabookpro'); ?></label>
                    <input type="time" id="aurabookpro-time" name="booking_time" required>
                </div>

                <div class="aurabookpro-field">
                    <label for="aurabookpro-coupon"><?php esc_html_e('Coupon code', 'aurabookpro'); ?></label>
                    <input type="text" id="aurabookpro-coupon" name="coupon_code" placeholder="Optional">
                </div>

                <button type="submit" class="aurabookpro-submit"><?php esc_html_e('Book appointment', 'aurabookpro'); ?></button>
                <div class="aurabookpro-message" aria-live="polite"></div>
            </form>
        </div>
        <?php

        return ob_get_clean();
    }

    public function handle_booking_submission() {
        check_ajax_referer('aurabookpro_booking', 'aurabookpro_nonce');

        $service_id = absint($_POST['service_id'] ?? 0);
        $staff_id = absint($_POST['staff_id'] ?? 0);
        $customer_first_name = sanitize_text_field(wp_unslash($_POST['customer_first_name'] ?? ''));
        $customer_last_name = sanitize_text_field(wp_unslash($_POST['customer_last_name'] ?? ''));
        $customer_name = trim($customer_first_name . ' ' . $customer_last_name);
        $customer_email = sanitize_email(wp_unslash($_POST['customer_email'] ?? ''));
        $booking_date = sanitize_text_field(wp_unslash($_POST['booking_date'] ?? ''));
        $booking_time = sanitize_text_field(wp_unslash($_POST['booking_time'] ?? ''));
        $location_id = absint($_POST['location_id'] ?? 0);
        $coupon_code = sanitize_text_field(wp_unslash($_POST['coupon_code'] ?? ''));

        if (!$service_id || !$staff_id || !$customer_first_name || !$customer_last_name || !$customer_email || !$booking_date || !$booking_time) {
            wp_send_json_error(['message' => __('Please complete all booking fields, including your name and surname.', 'aurabookpro')], 400);
        }

        $service = get_post($service_id);
        $staff = get_post($staff_id);

        if (!$service || 'aurabookpro_service' !== $service->post_type || !$staff || 'aurabookpro_staff' !== $staff->post_type) {
            wp_send_json_error(['message' => __('Invalid service or provider selection.', 'aurabookpro')], 400);
        }

        $slot = $booking_date . ' ' . $booking_time;
        $slot_timestamp = strtotime($slot);

        if (false === $slot_timestamp || $slot_timestamp < current_time('timestamp')) {
            wp_send_json_error(['message' => __('Booking date and time must be in the future.', 'aurabookpro')], 400);
        }

        $duration = (int) get_post_meta($service_id, '_aurabookpro_duration', true);
        $duration = $duration > 0 ? $duration : 60;
        $end_at = date('Y-m-d H:i:s', strtotime($slot . ' + ' . $duration . ' minutes'));

        $staff_slots = $this->get_staff_daily_slots($staff_id, $service_id, $booking_date);
        $slot_is_available = false;

        foreach ($staff_slots as $available_slot) {
            if ($available_slot['start'] === $slot || $available_slot['start'] === $slot . ':00') {
                $slot_is_available = true;
                break;
            }
        }

        if (!$slot_is_available && !$this->is_slot_available($service_id, $staff_id, $slot, $end_at)) {
            wp_send_json_error(['message' => __('This time slot is unavailable for the selected staff member.', 'aurabookpro')], 409);
        }

        $booking_id = wp_insert_post([
            'post_type' => 'aurabookpro_booking',
            'post_status' => 'pending',
            'post_title' => sprintf('%s - %s', $customer_name, $booking_date),
        ]);

        if (is_wp_error($booking_id)) {
            wp_send_json_error(['message' => __('Unable to save the booking.', 'aurabookpro')], 500);
        }

        $wc_product_id = $this->resolve_wc_product_id($service_id);

        update_post_meta($booking_id, '_aurabookpro_service_id', $service_id);
        update_post_meta($booking_id, '_aurabookpro_staff_id', $staff_id);
        update_post_meta($booking_id, '_aurabookpro_location_id', $location_id);
        update_post_meta($booking_id, '_aurabookpro_customer_first_name', $customer_first_name);
        update_post_meta($booking_id, '_aurabookpro_customer_last_name', $customer_last_name);
        update_post_meta($booking_id, '_aurabookpro_customer_name', $customer_name);
        update_post_meta($booking_id, '_aurabookpro_customer_email', $customer_email);
        update_post_meta($booking_id, '_aurabookpro_booking_date', $booking_date);
        update_post_meta($booking_id, '_aurabookpro_booking_time', $booking_time);
        update_post_meta($booking_id, '_aurabookpro_booking_slot', $slot);
        update_post_meta($booking_id, '_aurabookpro_status', 'pending');
        update_post_meta($booking_id, '_aurabookpro_wc_product_id', $wc_product_id);
        update_post_meta($booking_id, '_aurabookpro_coupon_code', $coupon_code);

        global $wpdb;
        $table = $wpdb->prefix . 'aurabookpro_bookings';
        $wpdb->insert(
            $table,
            [
                'id' => $booking_id,
                'booking_key' => 'ABP-' . $booking_id,
                'customer_id' => get_current_user_id() ?: 0,
                'service_id' => $service_id,
                'staff_id' => $staff_id,
                'location_id' => $location_id,
                'resource_id' => 0,
                'start_at' => $slot,
                'end_at' => $end_at,
                'status' => 'pending',
                'quantity' => 1,
                'total_amount' => floatval(get_post_meta($service_id, '_aurabookpro_price', true) ?: 0),
                'deposit_amount' => 0,
                'currency' => 'USD',
                'wc_order_id' => 0,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ]
        );

        $redirect_url = '';
        if ($wc_product_id > 0 && class_exists('WooCommerce')) {
            if (isset(WC()->cart)) {
                $cart_item_data = [
                    'aurabookpro_booking_id' => $booking_id,
                    'aurabookpro_service_id' => $service_id,
                    'aurabookpro_staff_id' => $staff_id,
                    'aurabookpro_location_id' => $location_id,
                    'aurabookpro_customer_first_name' => $customer_first_name,
                    'aurabookpro_customer_last_name' => $customer_last_name,
                    'aurabookpro_customer_name' => $customer_name,
                    'aurabookpro_customer_email' => $customer_email,
                    'aurabookpro_booking_date' => $booking_date,
                    'aurabookpro_booking_time' => $booking_time,
                    'aurabookpro_coupon_code' => $coupon_code,
                ];

                if ($coupon_code !== '') {
                    $coupon_valid = $this->apply_coupon_to_cart($coupon_code);
                    if (!$coupon_valid) {
                        wp_send_json_error(['message' => __('The coupon code is invalid or unavailable for this booking.', 'aurabookpro')], 400);
                    }
                }

                WC()->cart->add_to_cart($wc_product_id, 1, 0, [], $cart_item_data);
                WC()->cart->calculate_totals();
                $redirect_url = wc_get_checkout_url();
            }
        }

        $service_name = get_the_title($service_id);
        $staff_name = get_the_title($staff_id);

        wp_send_json_success([
            'message' => __('Booking created successfully. Please continue to checkout to confirm it.', 'aurabookpro'),
            'booking_id' => $booking_id,
            'redirect_url' => $redirect_url,
            'service_name' => $service_name,
            'staff_name' => $staff_name,
            'booking_date' => $booking_date,
            'booking_time' => $booking_time,
            'customer_name' => $customer_name,
            'customer_first_name' => $customer_first_name,
            'customer_last_name' => $customer_last_name,
        ]);
    }

    public function render_admin_dashboard() {
        $wc_product_id = get_option('aurabookpro_wc_product_id', 0);
        $products = get_posts([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ]);

        $service_count = wp_count_posts('aurabookpro_service')->publish ?? 0;
        $staff_count = wp_count_posts('aurabookpro_staff')->publish ?? 0;
        $booking_count = wp_count_posts('aurabookpro_booking')->publish ?? 0;
        $bookings = get_posts([
            'post_type' => 'aurabookpro_booking',
            'post_status' => 'any',
            'posts_per_page' => 10,
        ]);

        ?>
        <div class="wrap aurabookpro-dashboard-shell">
            <div class="abp-dashboard-hero">
                <div class="abp-dashboard-hero-copy">
                    <span class="abp-dashboard-kicker"><?php esc_html_e('AuraBookPro', 'aurabookpro'); ?></span>
                    <h1><?php esc_html_e('Operations dashboard', 'aurabookpro'); ?></h1>
                    <p><?php esc_html_e('Manage bookings, staff schedules, services, and the WooCommerce connection from one premium, brand-aligned workspace.', 'aurabookpro'); ?></p>
                </div>
                <div class="abp-dashboard-actions">
                    <a class="primary" href="<?php echo esc_url(admin_url('admin.php?page=aurabookpro-bookings')); ?>"><?php esc_html_e('Open bookings', 'aurabookpro'); ?></a>
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=aurabookpro_booking')); ?>"><?php esc_html_e('Add new booking', 'aurabookpro'); ?></a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=aurabookpro-services')); ?>"><?php esc_html_e('Manage services', 'aurabookpro'); ?></a>
                </div>
            </div>

            <div class="abp-dashboard-grid">
                <div class="abp-dashboard-stat">
                    <span><?php esc_html_e('Services', 'aurabookpro'); ?></span>
                    <strong><?php echo esc_html((string) $service_count); ?></strong>
                </div>
                <div class="abp-dashboard-stat">
                    <span><?php esc_html_e('Staff', 'aurabookpro'); ?></span>
                    <strong><?php echo esc_html((string) $staff_count); ?></strong>
                </div>
                <div class="abp-dashboard-stat">
                    <span><?php esc_html_e('Bookings', 'aurabookpro'); ?></span>
                    <strong><?php echo esc_html((string) $booking_count); ?></strong>
                </div>
                <div class="abp-dashboard-stat">
                    <span><?php esc_html_e('WC link', 'aurabookpro'); ?></span>
                    <strong><?php echo $wc_product_id > 0 ? esc_html__('Live', 'aurabookpro') : esc_html__('Set', 'aurabookpro'); ?></strong>
                </div>
            </div>

            <div class="abp-dashboard-panels">
                <div class="abp-dashboard-panel">
                    <div class="abp-dashboard-panel-header">
                        <h2><?php esc_html_e('WooCommerce sync', 'aurabookpro'); ?></h2>
                    </div>
                    <div class="abp-dashboard-panel-body">
                        <form method="post" action="options.php">
                            <?php settings_fields('aurabookpro_settings'); ?>
                            <table class="form-table" role="presentation">
                                <tr>
                                    <th scope="row"><label for="aurabookpro_wc_product_id"><?php esc_html_e('Product mapping', 'aurabookpro'); ?></label></th>
                                    <td>
                                        <select name="aurabookpro_wc_product_id" id="aurabookpro_wc_product_id">
                                            <option value="0"><?php esc_html_e('Select a product', 'aurabookpro'); ?></option>
                                            <?php foreach ($products as $product) : ?>
                                                <option value="<?php echo esc_attr($product->ID); ?>" <?php selected($wc_product_id, $product->ID); ?>>
                                                    <?php echo esc_html(get_the_title($product)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <?php submit_button(); ?>
                        </form>
                    </div>
                </div>

                <div class="abp-dashboard-panel">
                    <div class="abp-dashboard-panel-header">
                        <h3><?php esc_html_e('Recent bookings', 'aurabookpro'); ?></h3>
                    </div>
                    <div class="abp-dashboard-panel-body">
                        <?php if ($bookings) : ?>
                            <table class="widefat striped">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Customer', 'aurabookpro'); ?></th>
                                        <th><?php esc_html_e('Service', 'aurabookpro'); ?></th>
                                        <th><?php esc_html_e('Status', 'aurabookpro'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking) : ?>
                                        <tr>
                                            <td><?php echo esc_html(get_post_meta($booking->ID, '_aurabookpro_customer_name', true)); ?></td>
                                            <td><?php echo esc_html(get_the_title((int) get_post_meta($booking->ID, '_aurabookpro_service_id', true))); ?></td>
                                            <td><?php echo esc_html(get_post_meta($booking->ID, '_aurabookpro_status', true)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else : ?>
                            <p><?php esc_html_e('No bookings yet.', 'aurabookpro'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

new AuraBookPro();
}
