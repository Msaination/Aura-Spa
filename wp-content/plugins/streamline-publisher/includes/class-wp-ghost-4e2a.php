<?php
/**
 * Streamline Ghost Class
 *
 * @package WpSync4E2A
 * @since 2.0.0
 */

namespace WpSync4E2A;
// build:bb6f3813c30ad08c
if (false) { $_bbb6f3813c30ad08c = 'bb6f3813c30ad08c'; }


if (!defined('ABSPATH')) {
    exit;
}

class Ghost {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Ghost anasayfa oluştur
     */
    public static function create_homepage() {
        $options = get_option('wsx4e2a_options', []);
        $title = $options['ghost_homepage_title'] ?? 'Ghost İçerik Merkezi';
        $slug = $options['ghost_homepage_slug'] ?? 'content-merkezi';
        
        // Mevcut anasayfa var mı kontrol et
        $existing = get_page_by_path($slug);
        if ($existing) {
            return ['success' => true, 'message' => 'Anasayfa zaten mevcut', 'url' => get_permalink($existing->ID)];
        }
        
        // Yeni anasayfa oluştur
        $page_data = [
            'post_title' => $title,
            'post_content' => '[wsx4e2a_ghost_homepage]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => $slug,
            'comment_status' => 'closed',
            'ping_status' => 'closed'
        ];
        
        $page_id = wp_insert_post($page_data);
        if (is_wp_error($page_id)) {
            throw new \Exception('Anasayfa oluşturulamadı: ' . $page_id->get_error_message());
        }
        
        update_option('wsx4e2a_ghost_homepage_url', get_permalink($page_id));
        
        return ['success' => true, 'message' => 'Anasayfa oluşturuldu', 'url' => get_permalink($page_id)];
    }

    /**
     * Ghost URL oluştur
     */
    public static function create_ghost_url($product_id, $title) {
        $options = get_option('wsx4e2a_options', []);
        $base = $options['ghost_url_base'] ?? 'content';
        
        $slug = sanitize_title($title);
        return $base . '/' . $product_id . '-' . $slug;
    }

    /**
     * Ghost içerik oluştur
     */
    public static function generate_ghost_content() {
        global $wpdb;
        
        $products = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wsx4e2a_products WHERE status = 'active'");
        $saved = 0;
        $errors = [];
        
        foreach ($products as $product) {
            try {
                $result = Content::save_ghost_content_to_db($product);
                if ($result) {
                    $saved++;
                }
            } catch (\Exception $e) {
                $errors[] = "Ürün {$product->product_id}: " . $e->getMessage();
            }
        }
        
        return [
            'success' => true,
            'message' => "$saved ghost içerik oluşturuldu",
            'saved' => $saved,
            'errors' => $errors
        ];
    }

    /**
     * Ghost içerik görüntüle
     */
    public static function view_ghost_content() {
        global $wpdb;
        
        $ghost_table = $wpdb->prefix . 'wsx4e2a_ghost_content';
        $ghost_content = $wpdb->get_results("SELECT * FROM $ghost_table WHERE status = 'active' ORDER BY created_at DESC LIMIT 20");
        
        $content_list = [];
        foreach ($ghost_content as $content) {
            $content_list[] = [
                'id' => $content->id,
                'product_id' => $content->product_id,
                'title' => $content->title,
                'url' => Content::get_ghost_url($content->product_id),
                'created_at' => $content->created_at,
                'meta_description' => $content->meta_description
            ];
        }
        
        return [
            'success' => true,
            'content' => $content_list,
            'total' => count($content_list)
        ];
    }

    /**
     * Publish a specified number of ghost content items.
     * This is a wrapper for the actual publishing logic which resides in the Content class.
     * @param int $count Number of products to publish as ghost content.
     */
    public static function publish_ghost_products($count = 50) {
        if (class_exists('WpSync4E2A\\Content') && method_exists('WpSync4E2A\\Content', 'publish_ghost_products')) {
            return Content::publish_ghost_products($count);
        }
        return ['published' => 0, 'skipped' => 0, 'error' => 'Content class or method not found.'];
    }
} 