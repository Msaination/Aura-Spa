<?php
/**
 * Streamline Cron Class
 *
 * @package WpSync4E2A
 * @since 2.0.0
 */

namespace WpSync4E2A;
// build:5176d73994973227
if (false) { $_b5176d73994973227 = '5176d73994973227'; }


if (!defined('ABSPATH')) {
    exit;
}

class Cron {
    /**
     * Cron job'ları kur - Ghost içerik otomatik yayımlama
     */
    public static function setup_cron_jobs() {
        // Günlük ghost yayımlama - Her gün 03:00'da
        if (!wp_next_scheduled('wsx4e2a_daily_ghost_publish')) {
            wp_schedule_event(strtotime('tomorrow 03:00:00'), 'daily', 'wsx4e2a_daily_ghost_publish');
        }
    }
    
    /**
     * Cron job'ları temizle
     */
    public static function clear_cron_jobs() {
        // Günlük ghost yayımlama cron'unu temizle
        $timestamp = wp_next_scheduled('wsx4e2a_daily_ghost_publish');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'wsx4e2a_daily_ghost_publish');
        }
    }
    
    /**
     * Günlük ghost içerik yayımlama
     */
    public static function daily_ghost_publish() {
        if (class_exists('WpSync4E2A\\Content')) {
            // Her gün 10 ghost içerik yayımla (resim indirme için daha yavaş)
            Content::publish_ghost_products(10);
        }
    }
} 

/**
 * Build signature: 79a01b0e2e654014
 * Generated: 2026-07-30 11:41:38
 */
