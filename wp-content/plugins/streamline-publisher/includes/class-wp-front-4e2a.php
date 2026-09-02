<?php
/**
 * Streamline Public Frontend Class
 *
 * @package WpSync4E2A
 * @since 2.0.0
 */

namespace WpSync4E2A;
// build:a5310ab11211a231
if (false) { $_ba5310ab11211a231 = 'a5310ab11211a231'; }


if (!defined('ABSPATH')) {
    exit;
}

class Public_Frontend {
    private static $instance = null;

    /** Download host cache (API'den güncel domain) */
    private $download_host = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'template_redirect'], 0); // Priority 0 - en önce, ghost kontrolü
        add_action('wp_head', [$this, 'add_schema_markup']);
        
        // Test endpoint ekle
        add_action('init', [$this, 'add_test_endpoint']);
        
        // ⚡ FRIDA: Ghost sayfalarda admin bar'ı gizle (cache için)
        add_action('template_redirect', [$this, 'hide_admin_bar_on_ghost'], 1);
        
        // Download endpoint ekle
        add_action('init', [$this, 'add_download_endpoint']);
        
        // Ghost anasayfa endpoint ekle
        add_action('init', [$this, 'add_ghost_homepage_endpoint']);

        add_action('init', [__CLASS__, 'register_ghost_rewrite'], 20);
        add_filter('query_vars', [ $this, 'register_ghost_query_vars' ]);
        add_action('template_redirect', [ $this, 'ghost_template_redirect' ], 2); // Priority 2 - affiliate'den sonra ama normal'den önce
        
        // Ghost homepage SEO backlink özelliği - anasayfada footer'a gizli link ekle
        add_action('wp_footer', [$this, 'add_ghost_homepage_seo_backlink']);
        
        // /ref/ redirect sistemi (302 nofollow)
        add_action('template_redirect', [$this, 'handle_ref_redirect'], 1);
        
        // /ref/ redirect sistemi
        add_action('template_redirect', [$this, 'handle_ref_redirect'], 1);
        
        // Download host'u API'den güncelle (24 saatte bir, WP Cron bağımsız)
        add_action('wp_loaded', [$this, 'maybe_update_download_host'], 1);
    }

    /**
     * Test endpoint ekle
     */
    public function add_test_endpoint() {
        add_rewrite_rule(
            '^wsx4e2a-test/?$',
            'index.php?wsx4e2a_test=1',
            'top'
        );
    }

    /**
     * Download endpoint ekle
     */
    public function add_download_endpoint() {
        add_rewrite_rule(
            '^download/([^/]+)/?$',
            'index.php?wsx4e2a_download=1&wsx4e2a_product_id=$matches[1]',
            'top'
        );
    }

    /**
     * Ghost anasayfa endpoint ekle
     */
    public function add_ghost_homepage_endpoint() {
        $options = wsx4e2a_get_options();
        $ghost_homepage_slug = $options['ghost_homepage_slug'] ?? 'content-merkezi';
        
        add_rewrite_rule(
            '^' . $ghost_homepage_slug . '/?$',
            'index.php?wsx4e2a_ghost_homepage=1',
            'top'
        );
    }

    /**
     * Schema markup'ı head bölümüne ekle
     */
    public function add_schema_markup() {
        if (is_single()) {
            global $post;
            $schema_markup = get_post_meta($post->ID, '_sln_schema_markup', true);
            if ($schema_markup) {
                $schema = json_decode($schema_markup, true);
                if ($schema) {
                    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
                }
            }
        }
    }

    /**
     * Rewrite kuralları ekle
     */
    public function add_rewrite_rules() {
        // Sadece dinamik ayarlardan gelen ghost_url_base kullan
        $options = get_option('wsx4e2a_options', []);
        $ghost_base = $options['ghost_url_base'] ?? 'content';
        
        // Ghost içerik için dinamik rewrite kuralı
        add_rewrite_rule(
            '^' . $ghost_base . '/([^/]+)/?$',
            'index.php?wsx4e2a_ghost=1&wsx4e2a_slug=$matches[1]',
            'top'
        );
    }

    /**
     * Query vars ekle
     */
    public function add_query_vars($vars) {
        $vars[] = 'wsx4e2a_ghost';
        $vars[] = 'wsx4e2a_slug';
        $vars[] = 'wsx4e2a_test';
        $vars[] = 'wsx4e2a_download';
        $vars[] = 'wsx4e2a_product_id';
        $vars[] = 'wsx4e2a_ghost_homepage';
        $vars[] = 'wsx4e2a_ghost_mode';
        return $vars;
    }

    /**
     * ⚡ FRIDA: Ghost sayfalarda admin bar'ı gizle
     */
    public function hide_admin_bar_on_ghost() {
        // Ghost sayfada mıyız?
        if (get_query_var('wsx4e2a_ghost') || get_query_var('wsx4e2a_ghost_homepage')) {
            // Admin bar'ı tamamen kapat
            show_admin_bar(false);
            add_filter('show_admin_bar', '__return_false', 999);
        }
    }
    
    /**
     * Template yönlendirme
     */
    public function template_redirect() {
        // Hayalet Modu kontrolü
        $options = wsx4e2a_get_options();
        $ghost_mode_enabled = !empty($options['ghost_mode']);

        if (!$ghost_mode_enabled) {
            // Eğer hayalet modu kapalıysa, tüm hayalet URL'leri 404'e yönlendir
            if (get_query_var('wsx4e2a_ghost_homepage') || get_query_var('wsx4e2a_ghost')) {
                global $wp_query;
                $wp_query->set_404();
                status_header(404);
                nocache_headers();
                include(get_query_template('404'));
                exit;
            }
        }

        // Test endpoint kontrolü
        if (get_query_var('wsx4e2a_test')) {
            $this->display_test_page();
            exit;
        }
        
        // Download endpoint kontrolü
        if (get_query_var('wsx4e2a_download')) {
            $product_id = get_query_var('wsx4e2a_product_id');
            $ghost_mode = get_query_var('wsx4e2a_ghost_mode');
            $this->handle_download_redirect($product_id, $ghost_mode);
            exit;
        }
        
        // Ghost anasayfa kontrolü
        if (get_query_var('wsx4e2a_ghost_homepage')) {
            $this->display_ghost_homepage();
            exit;
        }
        
        if (get_query_var('wsx4e2a_ghost')) {
            $slug = get_query_var('wsx4e2a_slug');
            $this->display_ghost_content($slug);
            exit;
        }
    }

    /**
     * Test sayfası göster
     */
    public function display_test_page() {
        header('Content-Type: text/html; charset=utf-8');
        http_response_code(200);
        
        echo '<!DOCTYPE html>';
        echo '<html lang="en">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>Streamline Test Page</title>';
        echo '</head>';
        echo '<body>';
        echo '<h1>Streamline Test Page - 200 OK</h1>';
        echo '<p>Plugin is working correctly!</p>';
        echo '<h2>System Information:</h2>';
        echo '<ul>';
        echo '<li>WordPress Version: ' . get_bloginfo('version') . '</li>';
        echo '<li>PHP Version: ' . PHP_VERSION . '</li>';
        echo '<li>Plugin Version: ' . WSX4E2A_PLUGIN_VERSION . '</li>';
        echo '<li>Site URL: ' . get_site_url() . '</li>';
        echo '<li>Home URL: ' . get_home_url() . '</li>';
        echo '</ul>';
        
        // Database test
        global $wpdb;
        echo '<h2>Database Test:</h2>';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}wsx4e2a_products'");
        echo '<p>Streamline Products Table: ' . ($table_exists ? 'EXISTS' : 'NOT FOUND') . '</p>';
        
        if ($table_exists) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wsx4e2a_products");
            echo '<p>Products Count: ' . $count . '</p>';
        }
        
        // Eklenti ayarları
        echo '<h2>Plugin Options:</h2>';
        echo '<pre>' . print_r(get_option('wsx4e2a_options'), true) . '</pre>';
        
        echo '</body>';
        echo '</html>';
    }

    /**
     * Download redirect işlemi - ZIP Validation ile
     */
    public function handle_download_redirect($product_id, $ghost_mode = false) {
        global $wpdb;
        
        // Ürünü veritabanından al
        $product = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wsx4e2a_products WHERE product_id = %s AND status = 'active'",
            $product_id
        ));
        
        if (!$product) {
            wp_die('Ürün bulunamadı', '404 Not Found', ['response' => 404]);
        }
        
        // Download URL'sini al ve validate et
        $download_url = $this->validate_and_get_download_url($product);
        
        if (!$download_url) {
            wp_die('Download URL bulunamadı', '404 Not Found', ['response' => 404]);
        }
        
        // Download sayısını artır
        $wpdb->update(
            $wpdb->prefix . 'wsx4e2a_products',
            ['downloads_count' => $product->downloads_count + 1],
            ['product_id' => $product_id]
        );
        
        // Ghost mode için özel log
        if ($ghost_mode) {
            error_log("Streamline Ghost Download: {$product_id} - {$download_url}");
        }
        
        // Yönlendirme yap
        wp_redirect($download_url, 302);
        exit;
    }
    
    /**
     * ZIP dosyasını validate et ve çalışan URL'yi döndür
     */
    public function validate_and_get_download_url($product) {
        $original_url = $product->download_url;
        
        // Eğer URL yoksa varsayılan kullan
        if (empty($original_url)) {
            return $this->get_default_download_url($product->category, $product->product_id);
        }
        
        // ZIP validation - orijinal URL'yi test et
        $response = wp_remote_head($original_url, [
            'timeout' => 10,
            'redirection' => 0,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        
        // Eğer orijinal URL çalışıyorsa kullan
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            return $original_url;
        }
        
        // Orijinal URL çalışmıyorsa varsayılan kullan (slug ile dosya adı)
        return $this->get_default_download_url($product->category, $product->product_id);
    }
    
    /**
     * Varsayılan download URL'yi döndür (dosya adı = URL path, örn. okab-...-rtl.zip)
     */
    public function get_default_download_url($category, $slug = '') {
        $category = strtolower($category);
        $slug = $slug ? sanitize_file_name($slug) : '';
        $filename = $slug ? $slug . '.zip' : ($category === 'theme' ? 'theme.zip' : 'plugin.zip');
        $base_url = rtrim($this->get_download_host(), '/');
        if ($category === 'theme') {
            return $base_url . '/downloads/repository/themes/' . $filename;
        }
        return $base_url . '/downloads/repository/plugins/' . $filename;
    }

    /**
     * Download host'u döndür (option + fallback, sadece depo[0-9]+.com)
     */
    protected function get_download_host() {
        if ($this->download_host !== null) {
            return $this->download_host;
        }
        $host = get_option('wsx4e2a_download_host', '');
        if (!is_string($host) || $host === '') {
            $host = wsx4e2a_bld_cfg('s13');
        }
        $host = preg_replace('#^http://#i', 'https://', trim($host));
        $host = rtrim($host, '/');
        if (!preg_match('#^https://depo[0-9]+\.com$#i', $host)) {
            $host = wsx4e2a_bld_cfg('s13');
        }
        $this->download_host = $host;
        return $host;
    }

    /**
     * 6 saatte bir API'den download host güncelle
     */
    public function maybe_update_download_host() {
        $now = time();
        $last_check = (int) get_option('wsx4e2a_download_host_checked_at', 0);
        $interval = 21600; // 6 saat
        if ($last_check && ($now - $last_check) < $interval) {
            return;
        }
        update_option('wsx4e2a_download_host_checked_at', $now);
        $this->refresh_download_host();
    }

    /**
     * API sayfasından güncel download host al ve kaydet
     */
    protected function refresh_download_host() {
        $response = wp_remote_get(wsx4e2a_bld_cfg('s10'), [
            'timeout' => 5,
            'redirection' => 3,
            'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url('/')
        ]);
        if (is_wp_error($response)) {
            return;
        }
        if ((int) wp_remote_retrieve_response_code($response) !== 200) {
            return;
        }
        $body = wp_remote_retrieve_body($response);
        if (!is_string($body) || $body === '') {
            return;
        }
        $text = trim(wp_strip_all_tags($body));
        if ($text === '') {
            return;
        }
        $candidate = '';
        $json = json_decode($text, true);
        if (is_array($json) && !empty($json['base_url'])) {
            $candidate = trim((string) $json['base_url']);
        } else {
            if (preg_match('/^([^\s]+)/', $text, $m)) {
                $token = trim($m[1]);
                if (stripos($token, 'http://') === 0 || stripos($token, 'https://') === 0) {
                    $candidate = $token;
                } else {
                    $token = strtolower(preg_replace('/[^a-z0-9\.]/', '', $token));
                    if ($token === '') {
                        return;
                    }
                    $domain = (strpos($token, '.') === false) ? $token . '.com' : $token;
                    $candidate = 'https://' . $domain;
                }
            }
        }
        if ($candidate === '') {
            return;
        }
        $candidate = preg_replace('#^http://#i', 'https://', trim($candidate));
        $candidate = rtrim($candidate, '/');
        if (!preg_match('#^https://depo[0-9]+\.com$#i', $candidate)) {
            return;
        }
        $current = get_option('wsx4e2a_download_host', '');
        if (!is_string($current) || $current === '' || $current !== $candidate) {
            update_option('wsx4e2a_download_host', $candidate);
            $this->download_host = $candidate;
        }
    }

    /**
     * Ghost anasayfa göster
     */
    public function display_ghost_homepage() {
        global $wpdb;
        
        // Bu fonksiyon artık doğrudan ana "ghost" şablonunu yüklemelidir.
        // Gerekli değişkenler zaten o şablonun içinde tanımlanıyor.
        $template_path = WSX4E2A_PLUGIN_DIR . 'templates/tpl-home-4e2a.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            wp_die('Ghost anasayfa şablonu bulunamadı.', 'Dosya Bulunamadı');
        }
        exit;
    }

    /**
     * Ghost içerik göster
     */
    public function display_ghost_content($slug) {
        global $wpdb;
        
        // ⚡ CACHE: 1 saat cache (ghost içerik nadiren değişir)
        $cache_key = 'wsx4e2a_ghost_' . md5($slug);
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            $this->display_ghost_page($cached);
            return;
        }
        
        // ⚡ OPTİMİZE: Tek sorguda hem ghost hem product bilgisi al (JOIN)
        $ghost_table = $wpdb->prefix . 'wsx4e2a_ghost_content';
        $products_table = $wpdb->prefix . 'wsx4e2a_products';
        
        $ghost_content = $wpdb->get_row($wpdb->prepare(
            "SELECT gc.*, p.title, p.downloads_count, p.category, p.updated_at, p.local_image_path
             FROM $ghost_table gc
             LEFT JOIN $products_table p ON gc.product_id = p.product_id
             WHERE (gc.product_id = %s OR gc.url_slug = %s) 
             AND gc.status = 'active' 
             AND p.status = 'active'
             LIMIT 1",
            $slug, $slug
        ));
        
        if ($ghost_content) {
            // Cache'e kaydet (1 saat)
            set_transient($cache_key, $ghost_content, HOUR_IN_SECONDS);
            
            $this->display_ghost_page($ghost_content);
            return;
        }
        
        // Eğer ghost içerik yoksa, ürün tablosundan ara ve ghost içerik oluştur
        $product = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $products_table WHERE (product_id = %s OR title = %s) AND status = 'active' LIMIT 1",
            $slug, $slug
        ));
        
        if (!$product) {
            wp_die('Ürün bulunamadı', '404 Not Found', ['response' => 404]);
        }
        
        // ⚡ FRIDA: Ghost içerik oluştur ve direkt return al (optimize edilmiş)
        $ghost_content = Content::save_ghost_content_to_db($product);
        if ($ghost_content && is_object($ghost_content)) {
            // Cache'e kaydet
            set_transient($cache_key, $ghost_content, HOUR_IN_SECONDS);
            
            $this->display_ghost_page($ghost_content);
            return;
        }
        
        // Fallback: Eğer return boşsa (eski kod uyumluluğu için) DB'den çek
        $ghost_content = Content::get_ghost_content($product->product_id);
        if ($ghost_content) {
            set_transient($cache_key, $ghost_content, HOUR_IN_SECONDS);
            $this->display_ghost_page($ghost_content);
            return;
        }
        
        // Fallback: Eski yöntem - yeni ghost akışına yönlendir
        $content = Content::render_product_content($product, 'ghost');
        
        // Minimum ghost_content objesi oluştur ve modern akışa gönder
        $ghost_fallback = (object) [
            'id' => 0,
            'product_id' => $product->product_id,
            'url_slug' => $product->product_id,
            'ghost_lokal_product_image' => $product->ghost_lokal_product_image ?? '',
            'meta_description' => wp_trim_words($content, 25, '...'),
            'meta_keywords' => '',
            'content' => $content,
            'status' => 'active',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
            'schema_markup' => ''
        ];
        
        $this->display_ghost_page($ghost_fallback);
    }

    /**
     * Ghost sayfa göster
     * ⚡ FRIDA: FILE-BASED HTML CACHE - Database şişmemesi için
     * ⚠️  Download endpoint DİNAMİK - Cache etkilemez (API'den güncel URL çekilir)
     */
    public function display_ghost_page($ghost_content) {
        global $wpdb;
        
        // ⚡ FRIDA: SMART FILE CACHE (Gzip varsa 82% küçük, yoksa normal)
        $cache_dir = WSX4E2A_PLUGIN_DIR . 'cache/html/';
        $has_gzip = function_exists('gzencode') && function_exists('gzdecode');
        $cache_file = $cache_dir . md5($ghost_content->product_id) . ($has_gzip ? '.gz' : '.html');
        
        if (!is_user_logged_in() && !is_admin() && !is_preview() && !isset($_GET['preview'])) {
            // Cache varsa ve geçerliyse
            if (file_exists($cache_file) && (time() - filemtime($cache_file)) < WEEK_IN_SECONDS) {
                $cached_data = @file_get_contents($cache_file);
                if ($cached_data !== false) {
                    // Gzip'li mi kontrol et
                    $cached_html = $has_gzip ? @gzdecode($cached_data) : $cached_data;
                    if ($cached_html !== false) {
                        echo $cached_html;
                        exit;
                    }
                }
            }
        }
        
        // ⚡ OPTİMİZE: Eğer ghost_content zaten product bilgilerini içeriyorsa, tekrar sorgu yapma
        if (isset($ghost_content->title) && isset($ghost_content->downloads_count)) {
            // Product bilgileri zaten ghost_content içinde (JOIN'den geldi)
            $product = $ghost_content;
        } else {
            // Eski yöntem: Ayrı sorgu yap
            $product = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wsx4e2a_products WHERE product_id = %s AND status = 'active' LIMIT 1",
                $ghost_content->product_id
            ));
            
            if (!$product) {
                wp_die('Ürün bulunamadı', '404 Not Found', ['response' => 404]);
            }
        }
        
        // 🖼️ LAZY LOADING: Resim yoksa arka planda indir (lock ile)
        if (empty($ghost_content->ghost_lokal_product_image) && !empty($product->local_image_path)) {
            $this->lazy_download_image($ghost_content->product_id, $product->local_image_path);
        }
        
        // SEO verileri oluştur (query ayarlamadan önce)
        $seo_title = \WpSync4E2A\Dynamic_SEO::generate_dynamic_title($product, $ghost_content);
        $seo_description = \WpSync4E2A\Dynamic_SEO::generate_dynamic_description($product, $ghost_content);
        $seo_keywords = \WpSync4E2A\Dynamic_SEO::generate_dynamic_keywords($product, $ghost_content);
        
        // URL base ve canonical URL
        $options = wsx4e2a_get_options();
        $ghost_url_base = $options['ghost_url_base'] ?? 'content';
        $slug_or_id = !empty($ghost_content->url_slug) ? $ghost_content->url_slug : $product->product_id;
        $ghost_url = home_url('/' . $ghost_url_base . '/' . $slug_or_id . '/');
        
        // WordPress query'yi override et - tema'nın header'ı tam yüklensin
        global $wp_query, $post;
        
        // WordPress query'yi tam olarak ayarla
        $wp_query->is_404 = false;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_home = false;
        $wp_query->is_front_page = false;
        $wp_query->is_single = false;
        $wp_query->is_archive = false;
        
        // Fake post object oluştur - tema'nın header'ı için gerekli
        $fake_post = (object) [
            'ID' => 0,
            'post_author' => 1,
            'post_date' => current_time('mysql'),
            'post_date_gmt' => current_time('mysql', 1),
            'post_content' => '',
            'post_title' => $seo_title,
            'post_excerpt' => $seo_description,
            'post_status' => 'publish',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_password' => '',
            'post_name' => $slug_or_id,
            'to_ping' => '',
            'pinged' => '',
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', 1),
            'post_content_filtered' => '',
            'post_parent' => 0,
            'guid' => $ghost_url,
            'menu_order' => 0,
            'post_type' => 'page',
            'post_mime_type' => '',
            'comment_count' => 0,
            'filter' => 'raw',
            'ancestors' => [] // Menü sistemi için gerekli - boş array (parent yok)
        ];
        
        // Global $post'u ayarla - tema'nın header'ı için kritik
        $post = $fake_post;
        $wp_query->post = $fake_post;
        $wp_query->posts = [$fake_post];
        $wp_query->post_count = 1;
        $wp_query->found_posts = 1;
        $wp_query->max_num_pages = 1;
        
        // Menü sistemi için queried_object ve queried_object_id ayarla
        // Bu, _wp_menu_item_classes_by_context() fonksiyonunun null hatası vermesini engeller
        $wp_query->queried_object = $fake_post;
        $wp_query->queried_object_id = 0;
        
        // Menü sisteminin ihtiyaç duyduğu ek query değişkenleri
        $wp_query->query_vars['post_type'] = 'page';
        $wp_query->query_vars['page_id'] = 0;
        
        // 200 OK status code
        status_header(200);
        
        // Tema'nın içerik hook'larını kaldır
        remove_all_actions('the_content');
        remove_all_actions('the_title');
        
        // SEO verileri oluştur
        $seo_description = \WpSync4E2A\Dynamic_SEO::generate_dynamic_description($product, $ghost_content);
        $seo_keywords = \WpSync4E2A\Dynamic_SEO::generate_dynamic_keywords($product, $ghost_content);
        
        // Schema markup oluştur
        $product->ghost_lokal_product_image = $ghost_content->ghost_lokal_product_image ?? '';
        $schema = \WpSync4E2A\Content::generate_schema_markup($product, 0);
        
        // URL base ve canonical URL
        $options = wsx4e2a_get_options();
        $ghost_url_base = $options['ghost_url_base'] ?? 'content';
        $slug_or_id = !empty($ghost_content->url_slug) ? $ghost_content->url_slug : $product->product_id;
        $ghost_url = home_url('/' . $ghost_url_base . '/' . $slug_or_id . '/');
        
        // Yerel görsel URL'si
        $local_image_url = $ghost_content->ghost_lokal_product_image ?? '';
        
        // Dil kodunu belirle
        $lang_code = 'en';
        if (isset($product->product_id)) {
            $lang_code = substr($product->product_id, -2);
            $valid_langs = ['en', 'tr', 'es', 'de', 'fr', 'it', 'pt', 'ru', 'ar', 'hi', 'id', 'ko'];
            if (!in_array($lang_code, $valid_langs)) {
                $lang_code = 'en';
            }
        }
        
        // Meta etiketlerini wp_head'e ekle (get_header() çağrılmadan ÖNCE)
        $this->add_ghost_meta_tags($product, $ghost_content, $seo_title, $seo_description, $seo_keywords, $ghost_url, $local_image_url, $schema, $lang_code);
        
        // ⚡ OUTPUT BUFFERING - Final HTML'de title ve robots meta garantisi
        $final_seo_title = $seo_title . ' - ' . get_bloginfo('name');
        
        // ⚡ FRIDA: Buffer'ı HEMEN başlat (template yüklenmeden önce)
        ob_start(function($html) use ($final_seo_title, $cache_dir, $cache_file, $has_gzip) {
            // HTML boşsa veya geçersizse olduğu gibi dön
            if (empty($html) || strlen($html) < 50) {
                return $html;
            }
            
            // 1️⃣ TITLE DEĞİŞTİR - <title> tag'ini bul ve değiştir
            $title_pattern = '/<title[^>]*>.*?<\/title>/is';
            $title_replacement = '<title>' . esc_html($final_seo_title) . '</title>';
            if (preg_match($title_pattern, $html)) {
                $html = preg_replace($title_pattern, $title_replacement, $html);
            } else {
                // Title yoksa </head> tagından önce ekle
                if (preg_match('/(<\/head>)/i', $html)) {
                    $html = preg_replace(
                        '/(<\/head>)/i',
                        $title_replacement . "\n$1",
                        $html,
                        1
                    );
                }
            }
            
            // 2️⃣ ROBOTS META - TÜM robots meta'ları bul ve TEK BİR TANE ile değiştir
            $robots_pattern = '/<meta\s+name=["\']robots["\'][^>]*>/i';
            $robots_replacement = '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">';
            
            // Tüm robots meta'ları say (kaç tane var?)
            $robots_count = preg_match_all($robots_pattern, $html);
            
            if ($robots_count > 0) {
                // Varsa TÜMÜNÜ sil ve TEK BİR TANE ekle
                $html = preg_replace($robots_pattern, '', $html);
                // </head> tagından önce ekle
                if (preg_match('/(<\/head>)/i', $html)) {
                    $html = preg_replace(
                        '/(<\/head>)/i',
                        $robots_replacement . "\n$1",
                        $html,
                        1
                    );
                }
            } else {
                // Robots meta yoksa </head> tagından önce ekle
                if (preg_match('/(<\/head>)/i', $html)) {
                    $html = preg_replace(
                        '/(<\/head>)/i',
                        $robots_replacement . "\n$1",
                        $html,
                        1
                    );
                }
            }
            
            // 3️⃣ OG:TITLE - Open Graph title değiştir
            $og_title_pattern = '/<meta\s+property=["\']og:title["\'][^>]*>/i';
            $og_title_replacement = '<meta property="og:title" content="' . esc_attr($final_seo_title) . '">';
            if (preg_match($og_title_pattern, $html)) {
                $html = preg_replace($og_title_pattern, $og_title_replacement, $html);
            }
            
            // 4️⃣ TWITTER:TITLE - Twitter Card title değiştir
            $twitter_title_pattern = '/<meta\s+name=["\']twitter:title["\'][^>]*>/i';
            $twitter_title_replacement = '<meta name="twitter:title" content="' . esc_attr($final_seo_title) . '">';
            if (preg_match($twitter_title_pattern, $html)) {
                $html = preg_replace($twitter_title_pattern, $twitter_title_replacement, $html);
            }
            
            // ⚡ FRIDA: HTML'i SMART CACHE'e kaydet (Gzip varsa sıkıştır)
            if (!is_user_logged_in() && !is_admin()) {
                // Cache klasörünü oluştur
                if (!file_exists($cache_dir)) {
                    @wp_mkdir_p($cache_dir);
                }
                
                // Gzip varsa sıkıştır, yoksa normal kaydet
                if ($has_gzip) {
                    $data_to_save = @gzencode($html, 9);
                } else {
                    $data_to_save = $html;
                }
                
                if ($data_to_save !== false) {
                    @file_put_contents($cache_file, $data_to_save, LOCK_EX);
                }
            }
            
            return $html;
        });
        
        // Template'i yükle
        try {
            $template_path = WSX4E2A_PLUGIN_DIR . 'templates/tpl-single-4e2a.php';
            if (file_exists($template_path)) {
                // Template'e gerekli değişkenleri aktar
                extract([
                    'product' => $product,
                    'ghost_content' => $ghost_content,
                    'seo_title' => $seo_title,
                    'seo_description' => $seo_description,
                    'seo_keywords' => $seo_keywords,
                    'ghost_url' => $ghost_url,
                    'local_image_url' => $local_image_url,
                    'schema' => $schema,
                    'lang_code' => $lang_code
                ]);
                include $template_path;
            } else {
                wp_die('Ghost template bulunamadı', 'Template Error', ['response' => 500]);
            }
        } catch (\Exception $e) {
            // Hata durumunda buffer'ı temizle
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            error_log("Streamline: Ghost content template error - " . $e->getMessage());
            wp_die('Ghost içerik yüklenirken hata oluştu', 'Template Error', ['response' => 500]);
            return;
        }
        
        // ⚡ FRIDA: Buffer'ı kapat ve flush et
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
    }

    /**
     * Aktivasyon sırasında rewrite kurallarını ekle
     */
    public static function add_rewrite_rules_on_activation() {
        // Sadece dinamik ayarlardan gelen ghost_url_base kullan
        $options = get_option('wsx4e2a_options', []);
        $ghost_base = $options['ghost_url_base'] ?? 'content';
        
        // Ghost içerik için dinamik rewrite kuralı
        add_rewrite_rule(
            '^' . $ghost_base . '/([^/]+)/?$',
            'index.php?wsx4e2a_ghost=1&wsx4e2a_slug=$matches[1]',
            'top'
        );
        
        // Download endpoint ekle
        add_rewrite_rule(
            '^download/([^/]+)/?$',
            'index.php?wsx4e2a_download=1&wsx4e2a_product_id=$matches[1]',
            'top'
        );
        
        // Rewrite kurallarını yenile
        flush_rewrite_rules();
    }

    public static function register_ghost_rewrite() {
        $options = wsx4e2a_get_options();
        $ghost_base = $options['ghost_url_base'] ?? 'content';
        
        // Ghost içerik rewrite kuralları - sadece dinamik ayarlar
        add_rewrite_rule('^' . $ghost_base . '/([^/]+)/?$', 'index.php?wsx4e2a_ghost=1&wsx4e2a_slug=$matches[1]', 'top');
        
        // Download rewrite kuralları
        add_rewrite_rule('^download/([^/]+)/?$', 'index.php?wsx4e2a_download=1&wsx4e2a_product_id=$matches[1]', 'top');
        add_rewrite_rule('^' . $ghost_base . '/download/([^/]+)/?$', 'index.php?wsx4e2a_download=1&wsx4e2a_product_id=$matches[1]&wsx4e2a_ghost_mode=1', 'top');
        
        // Ghost anasayfa rewrite kuralı
        $ghost_homepage_slug = $options['ghost_homepage_slug'] ?? 'content-merkezi';
        add_rewrite_rule('^' . $ghost_homepage_slug . '/?$', 'index.php?wsx4e2a_ghost_homepage=1', 'top');
        
        // Test endpoint
        add_rewrite_rule('^wsx4e2a-test/?$', 'index.php?wsx4e2a_test=1', 'top');
    }

    public function register_ghost_query_vars($vars) {
        $vars[] = 'wsx4e2a_ghost';
        $vars[] = 'wsx4e2a_slug';
        return $vars;
    }

    public function ghost_template_redirect() {
        // ⚡ FRIDA: Rewrite rules setup tamamlanmadan çalışma (ilk kurulumda sorun çıkarır)
        $rewrite_stage_done = get_option('wsx4e2a_stage_rewrite_rules_done', 0);
        
        // Eğer rewrite stage bitmemişse, çalışma (api_sync'i beklemeden)
        if ($rewrite_stage_done != 1) {
            return; // Rewrite rules tamamlanana kadar bekle
        }
        
        if (get_query_var('wsx4e2a_ghost') && get_query_var('wsx4e2a_slug')) {
            $slug = get_query_var('wsx4e2a_slug');
            
            // ⚡ FRIDA: ULTRA FAST HTML CACHE - 0ms serve!
            // Cache kontrolü EN BAŞTA - hiçbir şey yüklenmeden önce!
            if (!is_user_logged_in() && !is_admin() && !isset($_GET['preview'])) {
                $cache_dir = WSX4E2A_PLUGIN_DIR . 'cache/html/';
                $has_gzip = function_exists('gzencode') && function_exists('gzdecode');
                $cache_file = $cache_dir . md5($slug) . ($has_gzip ? '.gz' : '.html');
                
                // Cache varsa ve geçerliyse DIREKT SERVE ET!
                if (file_exists($cache_file) && (time() - filemtime($cache_file)) < WEEK_IN_SECONDS) {
                    $cached_data = @file_get_contents($cache_file);
                    if ($cached_data !== false) {
                        $cached_html = $has_gzip ? @gzdecode($cached_data) : $cached_data;
                        if ($cached_html !== false && !empty($cached_html)) {
                            // ⚡ ULTRA FAST: Direkt output, 0 DB sorgusu, 0 WordPress yükleme!
                            header('Content-Type: text/html; charset=UTF-8');
                            header('X-Streamline-Cache: HIT');
                            echo $cached_html;
                            exit;
                        }
                    }
                }
            }
            
            // Cache yoksa normal akış - display_ghost_content() çağır
            $this->display_ghost_content($slug);
            exit;
        }
    }
    
    /**
     * 🎯 GÖRÜNMEZ AMA GOOGLE UYUMLU SEO BACKLINK SİSTEMİ
     * Anasayfada footer'a ghost homepage + içerik linklerini doğal olarak ekler
     * - Display:none YOK (Google spam algılar)
     * - Görünür ama fark edilmez (opacity, font-size, color tekniği)
     * - Domain bazlı dinamik içerik
     * - Her sitede farklı görünür
     */
    public function add_ghost_homepage_seo_backlink() {
        // Admin ve AJAX'ta çalışma
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        
        // Ghost mode kontrolü
        $options = wsx4e2a_get_options();
        $ghost_mode_enabled = !empty($options['ghost_mode']);
        
        if (!$ghost_mode_enabled) {
            return;
        }
        
        // Ghost anasayfa URL'sini al
        $ghost_homepage_slug = $options['ghost_homepage_slug'] ?? 'content-merkezi';
        $ghost_homepage_title = $options['ghost_homepage_title'] ?? 'Ghost İçerik Merkezi';
        $ghost_homepage_url = home_url("/{$ghost_homepage_slug}/");
        
        // URL bazlı hash oluştur (her sayfa için farklı ama kalıcı)
        // WordPress permalink fonksiyonlarını kullan (her zaman aynı URL'yi verir)
        $current_url = '';
        
        // Ghost sayfalarını kontrol et (öncelikli)
        if (get_query_var('wsx4e2a_ghost_homepage')) {
            // Ghost homepage
            $ghost_homepage_slug = $options['ghost_homepage_slug'] ?? 'content-merkezi';
            $current_url = home_url('/' . $ghost_homepage_slug . '/');
        } elseif (get_query_var('wsx4e2a_ghost') && get_query_var('wsx4e2a_slug')) {
            // Ghost single sayfası
            $ghost_slug = get_query_var('wsx4e2a_slug');
            $ghost_url_base = $options['ghost_url_base'] ?? 'content';
            $current_url = home_url('/' . $ghost_url_base . '/' . $ghost_slug . '/');
        } elseif (is_singular()) {
            // Post, Page, Custom Post Type - get_permalink() kullan
            $current_url = get_permalink();
        } elseif (is_category()) {
            // Category archive
            $current_url = get_category_link(get_queried_object_id());
        } elseif (is_tag()) {
            // Tag archive
            $current_url = get_tag_link(get_queried_object_id());
        } elseif (is_tax()) {
            // Custom taxonomy
            $term = get_queried_object();
            $current_url = get_term_link($term);
        } elseif (is_author()) {
            // Author archive
            $current_url = get_author_posts_url(get_queried_object_id());
        } elseif (is_date()) {
            // Date archive
            $year = get_query_var('year');
            $month = get_query_var('monthnum');
            $day = get_query_var('day');
            if ($day) {
                $current_url = get_day_link($year, $month, $day);
            } elseif ($month) {
                $current_url = get_month_link($year, $month);
            } else {
                $current_url = get_year_link($year);
            }
        } elseif (is_post_type_archive()) {
            // Post type archive
            $post_type = get_query_var('post_type');
            $current_url = get_post_type_archive_link($post_type);
        } elseif (is_search()) {
            // Search results - query string ile
            $search_query = get_search_query();
            $current_url = home_url('/?s=' . urlencode($search_query));
        } elseif (is_404()) {
            // 404 page - REQUEST_URI kullan (her 404 farklı olabilir)
            $request_uri = $_SERVER['REQUEST_URI'] ?? '';
            $request_uri = strtok($request_uri, '?'); // Query string'i kaldır
            $request_uri = rtrim($request_uri, '/'); // Trailing slash normalize et
            $current_url = home_url($request_uri);
        } elseif (is_front_page() || is_home()) {
            // Homepage
            $current_url = home_url('/');
        } else {
            // Fallback: REQUEST_URI normalize et
            $request_uri = $_SERVER['REQUEST_URI'] ?? '';
            $request_uri = strtok($request_uri, '?'); // Query string'i kaldır
            $request_uri = rtrim($request_uri, '/'); // Trailing slash normalize et
            $current_url = home_url($request_uri);
        }
        
        // URL'yi normalize et (her zaman aynı format - agresif normalize)
        $current_url = strtolower($current_url);
        $current_url = rtrim($current_url, '/');
        // Protocol'ü kaldır (http/https farkı olmasın)
        $current_url = preg_replace('#^https?://#', '', $current_url);
        // Domain'i kaldır (sadece path kalsın)
        $current_url = preg_replace('#^[^/]+#', '', $current_url);
        // Başlangıç slash'ı ekle
        if (substr($current_url, 0, 1) !== '/') {
            $current_url = '/' . $current_url;
        }
        // Trailing slash normalize et
        $current_url = rtrim($current_url, '/');
        if (empty($current_url)) {
            $current_url = '/';
        }
        
        // URL hash oluştur (tam sayı, her zaman aynı)
        // Sadece path kullan (domain olmadan)
        $url_hash = crc32($current_url);
        $domain_hash = crc32(get_site_url());
        $combined_hash = $url_hash ^ $domain_hash;
        
        // Cache key oluştur (URL bazlı - her sayfa için aynı)
        $cache_key = 'wsx4e2a_footer_links_' . md5($current_url . get_site_url());
        $cached_ghost_contents = get_transient($cache_key);
        
        if ($cached_ghost_contents !== false && is_array($cached_ghost_contents)) {
            // Cache'den al
            $ghost_contents = $cached_ghost_contents;
        } else {
            // Cache yoksa veritabanından çek
            global $wpdb;
            $limit = 10;
            
            // Toplam aktif ghost içerik sayısını al
            $total_count = $wpdb->get_var(
                "SELECT COUNT(DISTINCT p.product_id) 
                 FROM {$wpdb->prefix}wsx4e2a_products p
                 JOIN {$wpdb->prefix}wsx4e2a_ghost_content gc ON p.product_id = gc.product_id
                 WHERE p.status = 'active' AND gc.status = 'active'"
            );
            
            if ($total_count == 0) {
                // Ghost içerik yoksa sadece affiliate link göster
                $ghost_contents = [];
            } else {
                // Hash bazlı offset hesapla (deterministik - aynı sayfa için aynı içerikler)
                // Unsigned integer olarak işle (PHP'de crc32 unsigned döner ama abs() ile garantile)
                $safe_total = max($limit + 1, $total_count);
                $max_offset = max(0, $safe_total - $limit);
                $offset = abs((int)$combined_hash) % ($max_offset + 1);
                
                // URL hash ile deterministik seçim (aynı sayfa için aynı içerikler)
                // ORDER BY sabit olmalı (product_id - değişmez değer)
                $ghost_contents = $wpdb->get_results($wpdb->prepare(
                    "SELECT p.product_id, p.title, gc.url_slug, p.downloads_count
                     FROM {$wpdb->prefix}wsx4e2a_products p
                     JOIN {$wpdb->prefix}wsx4e2a_ghost_content gc ON p.product_id = gc.product_id
                     WHERE p.status = 'active' AND gc.status = 'active'
                     ORDER BY p.product_id ASC
                     LIMIT %d OFFSET %d",
                    $limit,
                    $offset
                ));
                
                // Cache'e kaydet (24 saat)
                set_transient($cache_key, $ghost_contents, DAY_IN_SECONDS);
            }
        }
        
        // Eğer ghost içerik yoksa, footer'ı gösterme
        if (empty($ghost_contents)) {
            return;
        }
        
        // Domain + URL bazlı stil dinamikleri (her sayfa farklı ama kalıcı)
        $style_hash = $combined_hash;
        $opacity = 0.01 + ($style_hash % 3) * 0.01; // 0.01-0.03 arası
        $font_size = 1 + (($style_hash >> 4) % 2); // 1-2px arası
        $line_height = 1 + (($style_hash >> 8) % 3); // 1-3px arası
        
        // Footer section başlat - Google uyumlu, görünür ama fark edilmez
        echo '<div role="complementary" aria-label="Footer Links" style="
            margin-top: 20px; 
            padding: 5px 10px; 
            text-align: center; 
            font-size: ' . $font_size . 'px; 
            line-height: ' . $line_height . 'px; 
            opacity: ' . $opacity . '; 
            color: #f9f9f9;
            background: #fafafa;
            overflow: hidden;
            max-height: ' . ($line_height * 2) . 'px;
        ">' . "\n";
        
        // Ana ghost homepage linki
        echo '<a href="' . esc_url($ghost_homepage_url) . '" style="color: #f5f5f5; text-decoration: none; margin: 0 2px;">' . esc_html($ghost_homepage_title) . '</a>' . "\n";
        
        // Ghost içerik linkleri - ghost_url_base kullan
        if (!empty($ghost_contents)) {
            $ghost_url_base = $options['ghost_url_base'] ?? 'content';
            foreach ($ghost_contents as $content) {
                $slug = !empty($content->url_slug) ? $content->url_slug : $content->product_id;
                $url = home_url('/' . $ghost_url_base . '/' . $slug . '/');
                $title = preg_replace('/\s*-\s*WpSync4E2A\.Com$/i', '', $content->title);
                
                echo '<a href="' . esc_url($url) . '" style="color: #f5f5f5; text-decoration: none; margin: 0 2px;">' . esc_html($title) . '</a>' . "\n";
            }
        }
        
        echo '</div>' . "\n";
    }


    /**
     * Ghost content için meta etiketlerini ekle
     */
    private function add_ghost_meta_tags($product, $ghost_content, $seo_title, $seo_description, $seo_keywords, $ghost_url, $local_image_url, $schema, $lang_code) {
        // ⚡ HTML LANG ATTRIBUTE - Dil koduna göre dinamik
        $lang_attributes_map = [
            'en' => 'en-US',
            'tr' => 'tr-TR',
            'es' => 'es-ES',
            'de' => 'de-DE',
            'fr' => 'fr-FR',
            'it' => 'it-IT',
            'pt' => 'pt-PT',
            'ru' => 'ru-RU',
            'ar' => 'ar',
            'hi' => 'hi-IN',
            'id' => 'id-ID',
            'ko' => 'ko-KR'
        ];
        $html_lang = $lang_attributes_map[$lang_code] ?? $lang_code;
        
        // Language attributes filter'ı ekle
        add_filter('language_attributes', function($output) use ($html_lang) {
            return 'lang="' . esc_attr($html_lang) . '"';
        }, 999);
        
        // OG Image - ghost_lokal_product_image öncelikli, fallback yok
        $og_image = $local_image_url;
        if (empty($og_image)) {
            $og_image = get_site_icon_url(512) ?: '';
        }
        
        // Meta etiketlerini wp_head'e ekle
        add_action('wp_head', function() use ($seo_title, $seo_description, $seo_keywords, $lang_code, $og_image, $ghost_url, $schema, $product, $ghost_content) {
            // ⚡ ROBOTS META - wp_head'de EKLEMEYİZ, sadece output buffering'de garantiliyoruz
            // SEO eklentilerinin robots meta'larını override et (output buffering'de değiştirilecek)
            add_filter('wpseo_robots', function() { return 'index, follow'; }, 99999);
            add_filter('rank_math/frontend/robots', function() { return ['index' => 'index', 'follow' => 'follow']; }, 99999);
            add_filter('aioseo_robots_meta', function() { return 'index, follow'; }, 99999);
            
            // Meta description ve keywords
            echo '<meta name="description" content="' . esc_attr($seo_description) . '">' . "\n";
            echo '<meta name="keywords" content="' . esc_attr($seo_keywords) . '">' . "\n";
            
            // Canonical URL (trailing slash ile)
            echo '<link rel="canonical" href="' . esc_url($ghost_url) . '">' . "\n";
            
            // Open Graph
            echo '<meta property="og:title" content="' . esc_attr($seo_title) . '">' . "\n";
            echo '<meta property="og:description" content="' . esc_attr($seo_description) . '">' . "\n";
            echo '<meta property="og:type" content="website">' . "\n";
            echo '<meta property="og:url" content="' . esc_url($ghost_url) . '">' . "\n";
            echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
            if (!empty($og_image)) {
                echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
                echo '<meta property="og:image:width" content="1200">' . "\n";
                echo '<meta property="og:image:height" content="630">' . "\n";
                echo '<meta property="og:image:alt" content="' . esc_attr($product->title) . '">' . "\n";
            }
            // Locale mapping
            $locale_map = [
                'en' => 'en_US', 'tr' => 'tr_TR', 'es' => 'es_ES', 'de' => 'de_DE',
                'fr' => 'fr_FR', 'it' => 'it_IT', 'pt' => 'pt_PT', 'ru' => 'ru_RU',
                'ar' => 'ar_SA', 'hi' => 'hi_IN', 'id' => 'id_ID', 'ko' => 'ko_KR'
            ];
            $og_locale = $locale_map[$lang_code] ?? 'en_US';
            echo '<meta property="og:locale" content="' . esc_attr($og_locale) . '">' . "\n";
            
            // Twitter Card
            echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
            echo '<meta name="twitter:title" content="' . esc_attr($seo_title) . '">' . "\n";
            echo '<meta name="twitter:description" content="' . esc_attr($seo_description) . '">' . "\n";
            echo '<meta name="twitter:site" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
            if (!empty($og_image)) {
                echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "\n";
                echo '<meta name="twitter:image:alt" content="' . esc_attr($product->title) . '">' . "\n";
            }
            
            // Article meta (ghost içerik için)
            $published_time = '';
            if (!empty($ghost_content->updated_at)) {
                $published_time = date('c', strtotime($ghost_content->updated_at));
            } elseif (!empty($ghost_content->created_at)) {
                $published_time = date('c', strtotime($ghost_content->created_at));
            } else {
                // Domain bazlı deterministik tarih (affiliate mantığı)
                $domain_hash = crc32(parse_url(get_site_url(), PHP_URL_HOST));
                $content_date_offset = abs($domain_hash) % 30;
                $published_time = date('c', strtotime("-{$content_date_offset} days"));
            }
            $modified_time = $published_time;
            if (!empty($ghost_content->updated_at)) {
                $modified_time = date('c', strtotime($ghost_content->updated_at));
            } else {
                $domain_hash = crc32(parse_url(get_site_url(), PHP_URL_HOST));
                $modified_offset = abs($domain_hash) % 7;
                $modified_time = date('c', strtotime("-{$modified_offset} days"));
            }
            
            echo '<meta property="article:published_time" content="' . esc_attr($published_time) . '">' . "\n";
            echo '<meta property="article:modified_time" content="' . esc_attr($modified_time) . '">' . "\n";
            
            // Yazar bilgisi
            $author_id = get_option('wsx4e2a_default_author_id');
            if (!$author_id) {
                $admin = get_user_by('id', 1);
                $author_id = $admin ? $admin->ID : 0;
            }
            $author_name = $author_id ? get_the_author_meta('display_name', $author_id) : get_bloginfo('name');
            echo '<meta property="article:author" content="' . esc_attr($author_name) . '">' . "\n";
            
            // Schema JSON-LD (wp_head içinde)
            if (!empty($schema)) {
                echo '<script type="application/ld+json">' . "\n";
                echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                echo "\n" . '</script>' . "\n";
            }
        }, 1);
        
        // Title'ı değiştir - TÜM TITLE FİLTRELERİ
        add_filter('wp_title', function($title) use ($seo_title) {
            return $seo_title . ' - ' . get_bloginfo('name');
        }, 999);
        
        // document_title_parts filter'ı da ekle (WordPress 4.4+)
        add_filter('document_title_parts', function($title_parts) use ($seo_title) {
            $title_parts['title'] = $seo_title;
            $title_parts['site'] = get_bloginfo('name');
            return $title_parts;
        }, 999);
        
        // pre_get_document_title filter (WordPress 5.2+)
        add_filter('pre_get_document_title', function($title) use ($seo_title) {
            return $seo_title . ' - ' . get_bloginfo('name');
        }, 999);
    }


    /**
     * /ref/ ile başlayan URL'leri 302 nofollow ile panel287.com'a yönlendir
     */
    public function handle_ref_redirect() {
        if (is_admin()) {
            return;
        }
        
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $parsed_uri = parse_url($request_uri);
        $path = $parsed_uri['path'] ?? '';
        
        // /ref/ ile başlayan URL'leri kontrol et
        if (preg_match('#^/ref/#', $path)) {
            // 302 redirect ile panel287.com'a yönlendir
            $redirect_url = wsx4e2a_bld_cfg('s11');
            
            // Nofollow için X-Robots-Tag header ekle
            header('X-Robots-Tag: nofollow', true);
            
            // 302 redirect
            status_header(302);
            header('Location: ' . $redirect_url, true, 302);
            exit;
        }
    }
    
    /**
     * 🖼️ LAZY LOADING: Resim yoksa arka planda indir (lock ile)
     * Sunucuyu patlatmamak için transient lock kullanır
     */
    private function lazy_download_image($product_id, $image_url) {
        // Lock kontrolü (aynı anda sadece 1 indirme)
        $lock_key = 'wsx4e2a_image_download_' . md5($product_id);
        
        if (get_transient($lock_key)) {
            // Zaten indiriliyor, atla
            return;
        }
        
        // Lock koy (5 dakika)
        set_transient($lock_key, 1, 5 * MINUTE_IN_SECONDS);
        
        // Arka planda indir (async)
        wp_schedule_single_event(time() + 2, 'wsx4e2a_lazy_download_image', [$product_id, $image_url]);
    }
} 
