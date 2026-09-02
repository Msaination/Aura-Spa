<?php
/**
 * Streamline Content Class
 *
 * @package WpSync4E2A
 * @since 2.0.0
 */

namespace WpSync4E2A;
// build:0ac5ea5ae2d25884
if (false) { $_b0ac5ea5ae2d25884 = '0ac5ea5ae2d25884'; }


if (!defined('ABSPATH')) {
    exit;
}

class Content {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * API'den gelen ürünleri veritabanına kaydet
     * DUPLICATE CONTROL: Her product_id sadece 1 kere kaydedilir
     */
    public static function save_products_to_db($products) {
        global $wpdb;
        if (empty($products) || !is_array($products)) {
            return 0;
        }
        
        $saved = 0;
        $skipped = 0;
        $updated = 0;
        $table = $wpdb->prefix . 'wsx4e2a_products';
        $total_products = count($products);
        
        // Büyük veri setleri için batch processing
        $batch_size = 100;
        $batches = array_chunk($products, $batch_size);
        
        error_log("Streamline: $total_products ürün kaydediliyor - " . count($batches) . " batch");
        
        // Mevcut product_id'leri toplu kontrol et (Performance optimization)
        $product_ids = array_map(function($p) { return $p['product_id'] ?? ''; }, $products);
        $product_ids = array_filter($product_ids);
        
        if (empty($product_ids)) {
            error_log("Streamline: Geçerli product_id bulunamadı");
            return 0;
        }
        
        // IN query ile toplu kontrol (çok daha hızlı)
        $placeholders = implode(',', array_fill(0, count($product_ids), '%s'));
        $existing_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT product_id FROM $table WHERE product_id IN ($placeholders)",
            ...$product_ids
        ));
        $existing_ids = array_flip($existing_ids); // Hash map for O(1) lookup
        
        foreach ($batches as $batch_index => $batch) {
            try {
                $batch_saved = 0;
                
                foreach ($batch as $product) {
                    $product_id = $product['product_id'] ?? '';
                    
                    if (empty($product_id)) {
                        $skipped++;
                        continue;
                    }
                    
                    $data = [
                        'product_id' => $product_id,
                        'title' => $product['title'] ?? '',
                        'category' => $product['category'] ?? 'Genel',
                        'description' => $product['description'] ?? '',
                        'features' => is_array($product['features']) ? json_encode($product['features']) : ($product['features'] ?? ''),
                        'version' => $product['version'] ?? '',
                        'price' => floatval($product['price'] ?? 0),
                        'rating' => floatval($product['rating'] ?? 0),
                        'downloads_count' => intval($product['downloads_count'] ?? 0),
                        'image_url' => $product['image_url'] ?? '',
                        'download_url' => $product['download_url'] ?? '',
                        'demo_url' => $product['demo_url'] ?? '',
                        'local_image_path' => $product['local_image_path'] ?? '',
                        'status' => 'active',
                        'updated_at' => current_time('mysql')
                    ];
                    
                    // DUPLICATE CONTROL: Hash map lookup (O(1))
                    if (isset($existing_ids[$product_id])) {
                        // Update mevcut kayıt
                        $result = $wpdb->update($table, $data, ['product_id' => $product_id]);
                        if ($result !== false) {
                            $updated++;
                        }
                    } else {
                        // Yeni kayıt ekle
                        $data['created_at'] = current_time('mysql');
                        $result = $wpdb->insert($table, $data);
                        if ($result) {
                            $batch_saved++;
                            $existing_ids[$product_id] = true; // Hash'e ekle
                        }
                    }
                }
                
                $saved += $batch_saved;
                
                // Progress tracking
                if ($total_products > 1000) {
                    $progress = round(($batch_index + 1) / count($batches) * 100, 1);
                    error_log("Streamline: Batch " . ($batch_index + 1) . "/" . count($batches) . " tamamlandı - Yeni: $batch_saved, Update: $updated, Skip: $skipped, İlerleme: %$progress");
                }
                
                // Memory temizliği
                unset($batch);
                gc_collect_cycles();
                
            } catch (\Exception $e) {
                error_log("Streamline: Batch " . ($batch_index + 1) . " hatası - " . $e->getMessage());
                // Hata durumunda devam et, sadece log'la
                continue;
            }
        }
        
        error_log("Streamline: DB kaydetme tamamlandı - Yeni: $saved, Update: $updated, Skip: $skipped, Toplam: $total_products");
        return $saved;
    }

    /**
     * Dinamik içerik şablonu parse et
     */
    public static function parse_dynamic_content($template) {
        return preg_replace_callback('/\{\[([^\]]+)\]\}/', function($matches) {
            $options = explode(',', $matches[1]);
            return trim($options[array_rand($options)]);
        }, $template);
    }

    /**
     * Deterministik spintax parser (hash-based seçim)
     * Domain bazlı özgünlük için kullanılır
     */
    public static function parse_deterministic_spintax($template, $seed_hash) {
        $hash_offset = 0;
        return preg_replace_callback('/\{\[([^\]]+)\]\}/', function($matches) use (&$seed_hash, &$hash_offset) {
            $options = explode(',', $matches[1]);
            $options = array_map('trim', $options);
            if (empty($options)) {
                return '';
            }
            // Her spintax için farklı hash kullan
            $current_hash = $seed_hash >> ($hash_offset * 4);
            $index = abs($current_hash) % count($options);
            $hash_offset++;
            return $options[$index];
        }, $template);
    }

    /**
     * Başlık optimizasyonu - mevcut domain ile değiştir ve Free/Download ekle
     */
    public static function optimize_title($title) {
        // Mevcut domain ile değiştir
        $current_domain = parse_url(get_site_url(), PHP_URL_HOST);
        $title = str_replace('syncdomain', $current_domain, $title);
        $title = str_replace('SyncDomain', $current_domain, $title);
        $title = str_replace('SyncDomain', $current_domain, $title);
        
        // Gereksiz kelimeleri kaldır
        $title = str_replace([' - SyncDomain', ' - SyncDomain', ' - syncdomain', wsx4e2a_bld_cfg('s18')], '', $title);
        
        return trim($title);
    }

    /**
     * Dinamik içerik üret (Sabit seçim - random değil)
     */
    public static function generate_dynamic_content($product) {
        // Site ve ürün bazlı sabit hash
        $site_hash = crc32(get_site_url());
        $product_hash = crc32($product->product_id);
        $combined_hash = $site_hash ^ $product_hash;
        
        $title = sanitize_text_field($product->title);
        $category = $product->category == 'theme' ? 'WordPress theme' : 'WordPress plugin';
        $features = $product->features ? explode("\n", $product->features) : [];
        $feature_count = count($features);
        $price = $product->price ? floatval($product->price) : 0;
        $rating = $product->rating ? floatval($product->rating) : 0;
        $version = $product->version ?: 'Latest';
        
        // Sabit downloads sayısı
        $downloads = $product->downloads_count ?: (abs($combined_hash >> 8) % 49000 + 1000);
        
        // Site bilgileri
        $site_name = get_bloginfo('name');
        $site_url = get_site_url();
        $current_domain = parse_url($site_url, PHP_URL_HOST);
        
        // Dinamik şablonlar (Ghost sistem kalitesinde - En az 300 kelime)
        $templates = [
            // Template 1: Comprehensive Professional Review
            "{[Discover,Explore,Experience,Unlock]} the {[exceptional,outstanding,remarkable,extraordinary]} capabilities of $title, a {[premium,professional,advanced,cutting-edge]} $category that {[transforms,revolutionizes,enhances,elevates]} your WordPress website to {[new heights,unprecedented levels,superior performance,excellent results]}. This {[comprehensive,all-inclusive,complete,thorough]} solution {[boasts,features,includes,offers]} $feature_count {[carefully,thoughtfully,meticulously,precisely]} {[crafted,designed,developed,engineered]} features that {[cater to,serve,address,meet]} the {[diverse,various,different,wide-ranging]} needs of {[both beginners and professionals,developers and designers,business owners and freelancers,small businesses and enterprises]}.

{[Built,Developed,Created,Engineered]} with {[modern,contemporary,latest,state-of-the-art]} technologies and {[best practices,industry standards,professional guidelines,expert recommendations]}, $title ensures {[optimal,superior,excellent,outstanding]} performance, {[seamless,flawless,smooth,perfect]} user experience, and {[robust,reliable,stable,secure]} functionality across all devices and platforms. The {[intuitive,user-friendly,easy-to-navigate,straightforward]} interface {[allows,enables,permits,lets]} users to {[customize,personalize,modify,adapt]} their websites {[effortlessly,easily,quickly,conveniently]} without requiring any {[coding knowledge,technical expertise,programming skills,development experience]}.

{[Whether you're,If you're,Whether you need to,If you want to]} {[creating a business website,launching an online store,building a portfolio,developing a blog]}, $title {[provides,delivers,offers,supplies]} all the {[essential,necessary,required,important]} {[tools,features,capabilities,functionalities]} you need to {[succeed,thrive,prosper,grow]} in the {[competitive,challenging,dynamic,evolving]} online landscape. {[With,Featuring,Including,Boasting]} {[responsive design,SEO optimization,speed optimization,security features]}, your website will {[rank higher,perform better,load faster,be more secure]} in search engines and {[provide,deliver,offer,give]} an {[exceptional,outstanding,amazing,superior]} user experience across all devices and browsers.

{[Currently,Presently,Right now,At the moment]} {[downloaded,used,installed,adopted]} by $downloads {[satisfied,happy,successful,professional]} users worldwide, $title has {[earned,received,achieved,gained]} a {[stellar,excellent,outstanding,impressive]} rating of $rating out of 5 stars from {[thousands,many,countless,numerous]} of {[happy,content,satisfied,pleased]} customers. {[This,The,Such,An]} {[accolade,recognition,achievement,success]} {[demonstrates,shows,proves,indicates]} the {[quality,reliability,effectiveness,superiority]} and {[trustworthiness,dependability,credibility,reputation]} of this {[exceptional,outstanding,remarkable,extraordinary]} $category.

{[Download,Get,Install,Acquire]} $title today and {[join,connect with,become part of,be among]} the {[growing,expanding,increasing,thriving]} community of {[successful,thriving,prosperous,growing]} website owners who have {[transformed,upgraded,enhanced,improved]} their online presence with this {[remarkable,exceptional,outstanding,extraordinary]} $category. {[Don't miss,Don't wait,Act now,Get started]} the opportunity to {[elevate,boost,improve,enhance]} your website's {[performance,appearance,functionality,success]} and {[achieve,reach,attain,realize]} {[outstanding,exceptional,remarkable,amazing]} results with $title!",

            // Template 2: SEO and Performance Focused
            "$title represents the {[pinnacle,peak,summit,zenith]} of {[modern,contemporary,advanced,innovative]} $category development, {[specifically,particularly,especially,notably]} {[crafted,designed,engineered,built]} to {[maximize,optimize,enhance,improve]} your website's {[search engine rankings,online visibility,digital presence,web performance]} and {[user engagement,visitor retention,customer conversion,website success]}. This {[comprehensive,all-encompassing,thorough,detailed]} solution {[incorporates,includes,features,boasts]} {[cutting-edge,state-of-the-art,latest,advanced]} SEO techniques and {[performance optimization,speed enhancement,loading optimization,efficiency improvements]} that {[ensure,guarantee,assure,promise]} your website {[ranks higher,performs better,loads faster,converts more]} in search results.

{[The,This,Such,An]} {[advanced,professional,expert,masterful]} {[architecture,structure,framework,design]} of $title {[enables,allows,permits,lets]} {[lightning-fast,ultra-fast,extremely fast,remarkably quick]} loading times, {[mobile-first,mobile-optimized,responsive,mobile-friendly]} design, and {[bulletproof,rock-solid,unbreakable,secure]} security features that {[protect,safeguard,secure,defend]} your website and {[visitors,users,customers,clients]}. {[Every,Each,All,Any]} aspect of this $category has been {[meticulously,carefully,thoroughly,precisely]} {[optimized,enhanced,improved,refined]} for {[maximum,optimal,peak,superior]} performance and {[user satisfaction,visitor engagement,customer conversion,website success]}.

{[With,Featuring,Including,Boasting]} $feature_count {[professional,advanced,expert,high-quality]} features, $title {[cater to,serve,address,meet]} the {[diverse,various,different,wide-ranging]} needs of {[businesses,organizations,companies,enterprises]} across {[multiple,several,various,different]} industries and {[niches,markets,sectors,fields]}. {[The,This,Such,An]} {[intuitive,user-friendly,easy-to-use,straightforward]} {[interface,design,layout,structure]} {[ensures,guarantees,assures,promises]} that {[even beginners,users of all levels,non-technical users,everyone]} can {[create,develop,build,launch]} {[stunning,beautiful,amazing,professional]} websites {[without,with minimal,with no,effortlessly]} {[technical knowledge,coding skills,programming experience,development expertise]}.

{[Currently,Presently,Right now,At the moment]} {[trusted,used,adopted,implemented]} by $downloads {[successful,thriving,prosperous,growing]} websites worldwide, $title has {[established,built,created,developed]} itself as a {[leading,top-tier,premium,reliable]} choice in the $category market. {[The,This,Such,An]} {[impressive,outstanding,remarkable,excellent]} rating of $rating out of 5 stars {[reflects,shows,demonstrates,indicates]} the {[quality,reliability,effectiveness,superiority]} and {[satisfaction,approval,success,achievement]} of {[thousands,many,countless,numerous]} of {[satisfied,happy,content,pleased]} users who have {[chosen,selected,opted for,decided on]} $title for their {[most important,critical,essential,vital]} {[projects,websites,applications,platforms]}.

{[Don't let,Don't allow,Don't miss,Don't waste]} your {[competitors,competition,rivals,opponents]} {[get ahead,stay ahead,outperform,excel]} - {[download,get,install,acquire]} $title today and {[experience,enjoy,benefit from,leverage]} the {[power,potential,capabilities,advantages]} of {[professional,premium,advanced,cutting-edge]} $category technology. {[Transform,Upgrade,Enhance,Improve]} your website's {[performance,appearance,functionality,success]} and {[achieve,reach,attain,realize]} {[outstanding,exceptional,remarkable,amazing]} results with this {[exceptional,outstanding,remarkable,extraordinary]} $category!",

            // Template 3: Business and ROI Focused
            "$title stands as a {[testament,proof,evidence,demonstration]} to {[excellence,quality,superiority,perfection]} in the {[competitive,challenging,dynamic,evolving]} world of $category development. This {[comprehensive,all-encompassing,thorough,detailed]} solution has been {[specifically,particularly,especially,notably]} {[designed,crafted,engineered,built]} to {[maximize,optimize,enhance,improve]} your {[business,commercial,professional,enterprise]} {[success,achievement,accomplishment,realization]} and {[return on investment,ROI,profitability,financial performance]} while {[minimizing,reducing,decreasing,lowering]} {[costs,expenses,overhead,expenditure]} and {[maximizing,optimizing,enhancing,improving]} {[efficiency,productivity,effectiveness,performance]}.

{[The,This,Such,An]} {[advanced,professional,expert,masterful]} {[architecture,structure,framework,design]} of $title {[incorporates,includes,features,boasts]} $feature_count {[strategically,carefully,thoughtfully,meticulously]} {[planned,designed,developed,implemented]} features that {[work together,cooperate,integrate,combine]} to {[create,deliver,provide,offer]} a {[powerful,effective,efficient,productive]} {[business,commercial,professional,enterprise]} {[solution,platform,tool,system]}. {[Every,Each,All,Any]} feature has been {[optimized,enhanced,improved,refined]} for {[maximum,optimal,peak,superior]} {[performance,effectiveness,efficiency,productivity]} and {[user satisfaction,customer engagement,client conversion,business success]}.

{[Whether you're,If you're,Whether you need to,If you want to]} {[running a small business,managing a large enterprise,operating an online store,developing a professional portfolio]}, $title {[provides,delivers,offers,supplies]} the {[tools,features,capabilities,functionalities]} you need to {[succeed,thrive,prosper,grow]} in today's {[competitive,challenging,dynamic,evolving]} {[marketplace,business environment,digital landscape,online world]}. {[The,This,Such,An]} {[intuitive,user-friendly,easy-to-use,straightforward]} {[interface,design,layout,structure]} {[ensures,guarantees,assures,promises]} that {[you,your team,your staff,your employees]} can {[focus,concentrate,devote,dedicate]} on {[what matters most,your core business,your main objectives,your key goals]} while the $category {[handles,manages,takes care of,oversees]} the {[technical,complex,complicated,challenging]} {[aspects,details,requirements,specifications]} of your website.

{[Currently,Presently,Right now,At the moment]} {[trusted,used,adopted,implemented]} by $downloads {[successful,thriving,prosperous,growing]} {[businesses,organizations,companies,enterprises]} worldwide, $title has {[established,built,created,developed]} a {[reputation,standing,status,position]} for {[reliability,dependability,trustworthiness,credibility]} and {[effectiveness,efficiency,productivity,success]}. {[The,This,Such,An]} {[impressive,outstanding,remarkable,excellent]} rating of $rating out of 5 stars {[reflects,shows,demonstrates,indicates]} the {[satisfaction,approval,success,achievement]} of {[business owners,professionals,entrepreneurs,organizations]} who have {[chosen,selected,opted for,decided on]} $title for their {[digital,online,web,internet]} {[success,achievement,accomplishment,realization]}.

{[Don't wait,Don't hesitate,Don't delay,Act now]} to {[join,connect with,become part of,be among]} the {[ranks,group,community,network]} of {[successful,prosperous,thriving,growing]} {[businesses,organizations,companies,enterprises]} that have {[transformed,upgraded,enhanced,improved]} their {[online presence,digital footprint,web presence,internet visibility]} with $title. {[Download,Get,Install,Acquire]} this {[exceptional,outstanding,remarkable,extraordinary]} $category today and {[start,begin,commence,initiate]} {[achieving,realizing,attaining,reaching]} your {[business,commercial,professional,enterprise]} {[goals,objectives,targets,aims]} with {[confidence,assurance,certainty,conviction]}!",

            // Template 4: Technology and Innovation Focused
            "{[Step into,Enter,Discover,Experience]} the {[future,next generation,advanced era,modern age]} of {[web technology,digital innovation,online development,internet advancement]} with $title, a {[revolutionary,groundbreaking,innovative,cutting-edge]} $category that {[pushes,extends,expands,advances]} the {[boundaries,limits,frontiers,horizons]} of what's {[possible,achievable,attainable,realizable]} in {[website development,web design,digital creation,online publishing]}. This {[sophisticated,advanced,complex,elaborate]} solution {[represents,embodies,exemplifies,showcases]} the {[pinnacle,peak,summit,zenith]} of {[modern,contemporary,current,present-day]} {[technology,innovation,development,engineering]} and {[design,architecture,structure,framework]}.

{[Built,Developed,Created,Engineered]} with {[cutting-edge,state-of-the-art,latest,advanced]} {[technologies,methods,techniques,approaches]} and {[best practices,industry standards,professional guidelines,expert recommendations]}, $title {[delivers,provides,offers,supplies]} an {[unparalleled,unmatched,unrivaled,exceptional]} {[performance,capability,functionality,experience]} that {[surpasses,exceeds,outperforms,transcends]} {[expectations,standards,requirements,specifications]}. {[The,This,Such,An]} {[advanced,professional,expert,masterful]} {[architecture,structure,framework,design]} {[ensures,guarantees,assures,promises]} {[optimal,superior,excellent,outstanding]} {[speed,performance,efficiency,effectiveness]} and {[reliability,stability,security,durability]} across all {[platforms,devices,browsers,environments]}.

{[Featuring,Including,Boasting,With]} $feature_count {[innovative,advanced,cutting-edge,state-of-the-art]} features, $title {[cater to,serve,address,meet]} the {[demands,requirements,needs,expectations]} of {[modern,contemporary,current,present-day]} {[developers,designers,professionals,experts]} and {[businesses,organizations,companies,enterprises]}. {[Every,Each,All,Any]} feature has been {[meticulously,carefully,thoroughly,precisely]} {[crafted,designed,developed,engineered]} to {[provide,deliver,offer,supply]} {[maximum,optimal,peak,superior]} {[value,benefit,advantage,utility]} and {[performance,effectiveness,efficiency,productivity]} while {[maintaining,preserving,keeping,ensuring]} {[simplicity,ease of use,accessibility,user-friendliness]}.

{[Currently,Presently,Right now,At the moment]} {[powering,serving,enabling,supporting]} $downloads {[successful,thriving,prosperous,growing]} {[websites,platforms,applications,projects]} worldwide, $title has {[proven,demonstrated,shown,established]} its {[effectiveness,reliability,quality,superiority]} in {[real-world,actual,practical,concrete]} {[applications,implementations,deployments,scenarios]}. {[The,This,Such,An]} {[outstanding,excellent,remarkable,impressive]} rating of $rating out of 5 stars {[validates,confirms,verifies,authenticates]} the {[quality,reliability,effectiveness,superiority]} and {[satisfaction,approval,success,achievement]} of {[thousands,many,countless,numerous]} of {[satisfied,happy,content,pleased]} users who have {[experienced,enjoyed,benefited from,leveraged]} the {[power,potential,capabilities,advantages]} of $title.

{[Join,Connect with,Become part of,Be among]} the {[elite,premium,select,exclusive]} group of {[professionals,experts,developers,designers]} who have {[chosen,selected,opted for,decided on]} $title for their {[most important,critical,essential,vital]} {[projects,applications,websites,platforms]}. {[Download,Get,Install,Acquire]} this {[revolutionary,groundbreaking,innovative,cutting-edge]} $category today and {[experience,enjoy,benefit from,leverage]} the {[power,potential,capabilities,advantages]} of {[next-generation,future-ready,advanced,modern]} {[technology,innovation,development,engineering]}!"
        ];
        
        // Sabit şablon seçimi (random değil)
        $template_index = abs($combined_hash) % count($templates);
        $selected_template = $templates[$template_index];
        $dynamic_content = self::parse_dynamic_content($selected_template);
        
        // Benzersizlik için product_id kullan (timestamp ve random değil)
        $unique_id = substr(md5($product->product_id . get_site_url()), 0, 8);
        $dynamic_content .= "\n\n<!-- Unique ID: #$unique_id -->";
        
        // Anahtar kelime ekle (İngilizce) - sabit seçim
        $keywords = self::generate_keywords($product);
        $keyword_parts = explode(', ', $keywords);
        $keyword_count = min(3, count($keyword_parts));
        $selected_keywords = array_slice($keyword_parts, 0, $keyword_count);
        $dynamic_content .= "\n\n<p><strong>Keywords:</strong> " . implode(', ', $selected_keywords) . ".</p>";
        
        
        // Domain değiştirme - SyncDomain'u aktif domain ile değiştir
        $dynamic_content = str_replace('SyncDomain', $current_domain, $dynamic_content);
        $dynamic_content = str_replace('SyncDomain', $current_domain, $dynamic_content);
        $dynamic_content = str_replace('syncdomain', $current_domain, $dynamic_content);
        
        return $dynamic_content;
    }

    /**
     * Anahtar kelime üret (İngilizce)
     */
    public static function generate_keywords($product) {
        $title = $product->title;
        $category = $product->category;
        
        $base_keywords = [
            'WordPress', $category, 'download', 'free', 'GPL', 'license',
            'theme', 'plugin', 'plugin', 'theme', 'download', 'free'
        ];
        
        // Başlıktan kelimeler çıkar
        $title_words = explode(' ', strtolower($title));
        $title_words = array_filter($title_words, function($word) {
            return strlen($word) > 3 && !in_array($word, ['com', 'streamline', 'wordpress', 'the', 'and', 'for']);
        });
        
        $keywords = array_merge($base_keywords, array_slice($title_words, 0, 5));
        $keywords = array_unique($keywords);
        
        return implode(', ', array_slice($keywords, 0, 8));
    }

    /**
     * Resim URL'sini lokal resimle değiştir
     */
    public static function get_local_image_url($external_url, $product_id) {
        // SyncDomain resimlerini lokal resimlerle değiştir
        $current_domain = parse_url(get_site_url(), PHP_URL_HOST);
        
        // Eğer external resim ise, lokal resim kullan
        if (strpos($external_url, 'syncdomain') !== false || strpos($external_url, wsx4e2a_bld_cfg('s20')) !== false) {
            // Ürün tipine göre lokal resim seç
            $product_hash = crc32($product_id);
            $local_images = [
                '/wp-content/uploads/2024/01/wordpress-theme-1.jpg',
                '/wp-content/uploads/2024/01/wordpress-theme-2.jpg',
                '/wp-content/uploads/2024/01/wordpress-plugin-1.jpg',
                '/wp-content/uploads/2024/01/wordpress-plugin-2.jpg',
                '/wp-content/uploads/2024/01/wordpress-theme-3.jpg',
                '/wp-content/uploads/2024/01/wordpress-plugin-3.jpg',
                '/wp-content/uploads/2024/01/wordpress-theme-4.jpg',
                '/wp-content/uploads/2024/01/wordpress-plugin-4.jpg'
            ];
            
            $image_index = abs($product_hash) % count($local_images);
            return home_url($local_images[$image_index]);
        }
        
        return $external_url;
    }

    /**
     * Dinamik logo URL'si al
     */
    public static function get_dynamic_logo_url(): string {
        $site_url = home_url();
        
        // 1. Önce custom logo kontrol et
        if (function_exists('get_custom_logo')) {
            $custom_logo = get_custom_logo();
            if (!empty($custom_logo)) {
                // HTML'den URL çıkar
                if (preg_match('/src=["\']([^"\']+)["\']/', $custom_logo, $matches)) {
                    return $matches[1];
                }
            }
        }
        
        // 2. Site icon kontrol et
        $site_icon = get_site_icon_url(128);
        if (!empty($site_icon)) {
            return $site_icon;
        }
        
        // 3. Uploads klasöründe logo.png kontrol et
        $logo_path = $site_url . '/wp-content/uploads/logo.png';
        if (self::url_exists($logo_path)) {
            return $logo_path;
        }
        
        // 4. Fallback: site icon 32px
        $fallback_icon = get_site_icon_url(32);
        if (!empty($fallback_icon)) {
            return $fallback_icon;
        }
        
        // 5. Son fallback: default logo
        return $site_url . '/wp-content/uploads/logo.png';
    }
    
    /**
     * URL'nin var olup olmadığını kontrol et
     */
    private static function url_exists(string $url): bool {
        $headers = @get_headers($url);
        return $headers && strpos($headers[0], '200') !== false;
    }
    
    /**
     * Dinamik yazar bilgisi al
     */
    public static function get_dynamic_author_info($product): array {
        $site_name = get_bloginfo('name');
        $site_url = home_url();
        $logo_url = self::get_dynamic_logo_url();
        
        // Site organizasyonu
        return [
            '@type' => 'Organization',
            'name' => $site_name,
            'url' => $site_url,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $logo_url
            ]
        ];
    }

    /**
     * Google SEO uyumlu schema markup oluştur
     */
    public static function generate_schema_markup($product, $post_id) {
        // Rating'i 5 yıldız üzerinden 3.5-5.0 arası sabit yap (Google SEO uyumlu)
        $product_hash = crc32($product->product_id);
        $rating = isset($product->rating) ? $product->rating : (abs($product_hash) % 16 + 35) / 10;
        
        // Manipüle edilmiş download URL kullan
        $masked_download_url = self::get_masked_download_url($product->product_id);
        
        // Demo URL
        $demo_url = self::get_product_demo_url($product);
        
        // Site bilgileri
        $site_name = get_bloginfo('name');
        $site_url = home_url();
        $current_domain = parse_url($site_url, PHP_URL_HOST);
        
        // Dinamik logo alma
        $logo_url = self::get_dynamic_logo_url();
        
        // Dinamik yazar bilgisi
        $author_info = self::get_dynamic_author_info($product);
        
        // Product title'ı temizle - SyncDomain referanslarını kaldır
        $clean_title = $product->title;
        $clean_title = str_replace('SyncDomain', $current_domain, $clean_title);
        $clean_title = str_replace('SyncDomain', $current_domain, $clean_title);
        $clean_title = str_replace('syncdomain', $current_domain, $clean_title);
        
        // URL belirleme - post_id 0 ise ghost URL kullan
        $page_url = get_permalink($post_id);
        if (empty($page_url) || $page_url === false || $post_id == 0) {
            // Ghost içerik için URL oluştur
            $options = wsx4e2a_get_options();
            $ghost_url_base = $options['ghost_url_base'] ?? 'content';
            $ghost_content = self::get_ghost_content($product->product_id);
            $slug_or_id = !empty($ghost_content->url_slug) ? $ghost_content->url_slug : $product->product_id;
            $page_url = home_url('/' . $ghost_url_base . '/' . $slug_or_id . '/');
        }
        
        // Tarih bilgileri - post_id 0 ise fallback
        $date_published = $post_id > 0 ? get_the_date('c', $post_id) : current_time('c');
        $date_modified = $post_id > 0 ? get_the_modified_date('c', $post_id) : current_time('c');
        
        // Ana schema
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => $clean_title,
            'headline' => $clean_title, // Article için headline alanı
            'description' => wp_trim_words(isset($product->description) ? $product->description : $clean_title, 25, '...'),
            'url' => $page_url,
            'datePublished' => $date_published,
            'dateModified' => $date_modified,
            'author' => $author_info,
            'publisher' => [
                '@type' => 'Organization',
                'name' => $site_name,
                'url' => $site_url,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $logo_url
                ]
            ]
        ];
        
        // mainEntityOfPage sadece geçerli URL varsa ekle
        if (!empty($page_url) && $page_url !== false) {
            $schema['mainEntityOfPage'] = [
                '@type' => 'WebPage',
                '@id' => $page_url
            ];
        }
        
        // SoftwareApplication için özel alanlar
        $schema['applicationCategory'] = $product->category == 'theme' ? 'WordPress Theme' : 'WordPress Plugin';
        $schema['operatingSystem'] = 'WordPress';
        $schema['version'] = isset($product->version) ? $product->version : 'Latest';
        $schema['downloadUrl'] = $masked_download_url;
        $schema['offers'] = [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'USD',
            'availability' => 'https://schema.org/InStock',
            'seller' => [
                '@type' => 'Organization',
                'name' => $site_name,
                'url' => $site_url
            ]
        ];
        // Rating değerini 1-5 arası garantile
        $rating_value = floatval($rating);
        if ($rating_value < 1.0) {
            $rating_value = 1.0;
        } elseif ($rating_value > 5.0) {
            $rating_value = 5.0;
        }
        
        // Rating count'u pozitif integer olarak garantile (minimum 1)
        $rating_count = isset($product->downloads_count) ? intval($product->downloads_count) : (abs($product_hash >> 8) % 49000 + 1000);
        if ($rating_count < 1) {
            $rating_count = 1;
        }
        
        $schema['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => $rating_value, // Float olarak gönder (string değil)
            'bestRating' => 5,
            'worstRating' => 1,
            'ratingCount' => $rating_count // Pozitif integer garantili
        ];
        
        // Demo URL
        if ($demo_url) {
            $schema['softwareHelp'] = $demo_url;
        }
        
        // Özellikler
        if (!empty($product->features)) {
            $features = is_string($product->features) ? json_decode($product->features, true) : $product->features;
            if (is_array($features) && !empty($features)) {
                $schema['featureList'] = array_slice($features, 0, 10);
            }
        }
        
        // Resim (hem Article hem SoftwareApplication için geçerli)
        $image_url = null;
        if (isset($product->ghost_lokal_product_image) && !empty($product->ghost_lokal_product_image)) {
            $image_url = $product->ghost_lokal_product_image;
        } elseif (isset($product->image_url) && $product->image_url) {
            $image_url = self::get_local_image_url($product->image_url, $product->product_id);
        }
        
        if ($image_url) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => $image_url,
                'width' => 1200,
                'height' => 630
            ];
        }
        
        return $schema;
    }

    /**
     * Ürünleri yayımla (mode: ghost/normal) - Kaliteli dinamik içerik ile
     */
    public static function publish_products($mode = 'normal', $count = 5000) {
        global $wpdb;
        $bozuklar = $wpdb->get_results("SELECT product_id FROM $table WHERE rating < 3.5 OR downloads_count < 1");
foreach ($bozuklar as $b) {
    $yeni_rating = round(mt_rand(35, 48) / 10, 1); // 3.5 - 4.8 arası
    $yeni_downloads = rand(1000, 50000);
    $wpdb->update(
        $table,
        [
            'rating' => $yeni_rating,
            'downloads_count' => $yeni_downloads
        ],
        ['product_id' => $b->product_id]
    );
}
        $table = $wpdb->prefix . 'wsx4e2a_products';
        
        // Memory ve timeout optimizasyonları
        set_time_limit(300); // 5 dakika
        ini_set('memory_limit', '512M');
        
        // Büyük veri setleri için batch processing
        $batch_size = 50; // Her seferde 50 ürün işle
        $total_published = 0;
        
        error_log("Streamline: Yayımlama başladı - Mod: $mode, Hedef: $count ürün");
        
        // GÜÇLÜ DUPLICATE KONTROL: Yayımlanmamış ürünleri çek
        // Her product_id sadece 1 kere yayımlanır (post tablosunda)
        
        // 1. Toplam aktif ürün sayısını al (DISTINCT ile)
        $total_count = $wpdb->get_var("
            SELECT COUNT(DISTINCT p.product_id) 
            FROM $table p 
            WHERE p.status = 'active' 
            AND p.product_id NOT IN (
                SELECT DISTINCT pm.meta_value 
                FROM {$wpdb->postmeta} pm 
                WHERE pm.meta_key = 'wsx4e2a_product_id' 
                AND pm.meta_value IS NOT NULL 
                AND pm.meta_value != ''
            )
        ");
        
        // 2. Random offset hesapla (güvenli aralık)
        $safe_count = max(1, $total_count);
        $random_offset = rand(0, max(0, $safe_count - $count));
        
        // 3. DISTINCT ile yayımlanmamış ürünleri çek (duplicate engelle)
        $unpublished_products = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT p.* FROM $table p 
            WHERE p.status = 'active' 
            AND p.product_id NOT IN (
                SELECT DISTINCT pm.meta_value 
                FROM {$wpdb->postmeta} pm 
                WHERE pm.meta_key = 'wsx4e2a_product_id' 
                AND pm.meta_value IS NOT NULL 
                AND pm.meta_value != ''
            )
            GROUP BY p.product_id
            ORDER BY p.id
            LIMIT %d OFFSET %d
        ", $count, $random_offset));
        
        if (empty($unpublished_products)) {
            error_log("Streamline: Yayımlanacak yeni ürün bulunamadı");
            return 0;
        }
        
        error_log("Streamline: " . count($unpublished_products) . " yayımlanmamış ürün bulundu");
        
        // Batch processing
        $batches = array_chunk($unpublished_products, $batch_size);
        
        foreach ($batches as $batch_index => $products) {
            $batch_published = 0;
            
            foreach ($products as $product) {
                // Sadece yeni yayımlanan ürünler için (daha önce post olarak eklenmemiş)
                $existing = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'wsx4e2a_product_id' AND meta_value = %s", $product->product_id));
                if (!$existing) {
                    // Her yeni ürün için rating ve downloads_count kontrolü
                    $updated = false;
                    if (empty($product->rating) || floatval($product->rating) < 3.5) {
                        $product->rating = round(mt_rand(35, 48) / 10, 1); // 3.5 - 4.8 arası
                        $wpdb->update(
                            $table,
                            ['rating' => $product->rating],
                            ['product_id' => $product->product_id]
                        );
                        $updated = true;
                    }
                    if (empty($product->downloads_count) || intval($product->downloads_count) < 1) {
                        $product->downloads_count = rand(1000, 50000);
                        $wpdb->update(
                            $table,
                            ['downloads_count' => $product->downloads_count],
                            ['product_id' => $product->product_id]
                        );
                        $updated = true;
                    }
                    // Veritabanı gerçekten güncellendi mi kontrol et, gerekirse tekrar dene
                    if ($updated) {
                        $row = $wpdb->get_row($wpdb->prepare("SELECT rating, downloads_count FROM $table WHERE product_id = %s", $product->product_id));
                        if (floatval($row->rating) < 3.5 || intval($row->downloads_count) < 1) {
                            // Tekrar güncelle
                            $product->rating = round(mt_rand(35, 48) / 10, 1);
                            $product->downloads_count = rand(1000, 50000);
                            $wpdb->update(
                                $table,
                                [
                                    'rating' => $product->rating,
                                    'downloads_count' => $product->downloads_count
                                ],
                                ['product_id' => $product->product_id]
                            );
                        }
                    }
                    $wpdb->update(
                        $wpdb->prefix . 'wsx4e2a_products',
                        [
                            'rating' => $product->rating,
                            'downloads_count' => $product->downloads_count
                        ],
                        ['product_id' => $product->product_id]
                    );
                }
                
                try {
                    // Duplicate kontrol (ekstra güvenlik)
                    $existing = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'wsx4e2a_product_id' AND meta_value = %s", $product->product_id));
                    if ($existing) {
                        error_log("Streamline: Duplicate ürün atlandı - Product ID: {$product->product_id}");
                        continue;
                    }
                    
                    if ($mode === 'ghost') {
                        // Sadece ghost içerik tablosuna yaz
                        $ghost_id = self::save_ghost_content_to_db($product);
                        if ($ghost_id) {
                            $batch_published++;
                        }
                        continue; // Post tablosuna asla yazma
                    }
                    
                    // Başlık optimizasyonu
                    $optimized_title = self::optimize_title($product->title);
                    
                    // SEO dostu slug oluştur
                    $category_slug = sanitize_title(self::get_primary_category_name($product->category));
                    $slug = sanitize_title($optimized_title . '-' . $category_slug);
                    
                    // Normal mod için kaliteli dinamik içerik
                    $content = self::generate_dynamic_content($product);
                    
                    // Demo ve download linkleri ekle
                    $demo_url = self::get_product_demo_url($product);
                    $download_url = self::get_masked_download_url($product->product_id);
                    
                    // Dinamik download buton texti
                    $download_button_text = self::get_dynamic_download_button_text($product->title);
                    
                    $content .= "\n\n<div class='wsx4e2a-product-actions' style='margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 10px; text-align: center;'>";
                    $content .= "<div style='display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;'>";
                    $content .= "<a href='$download_url' class='button button-primary' style='padding: 12px 24px; background: #007cba; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;' target='_blank' rel='nofollow noopener noreferrer'>📥 " . esc_html($download_button_text) . "</a>";
                    if ($demo_url) {
                        $content .= "<a href='$demo_url' class='button button-secondary' style='padding: 12px 24px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;' target='_blank' rel='nofollow noopener noreferrer'>👁️ Live Demo</a>";
                    }
                    $content .= "</div>";
                    $content .= "<p style='margin-top: 15px; font-size: 14px; color: #666;'>Free download, no registration required. GPL licensed.</p>";
                    $content .= "</div>";
                    
                    // Özellikler listesi ekle
                    if (!empty($product->features)) {
                        $features = is_string($product->features) ? json_decode($product->features, true) : $product->features;
                        if (is_array($features) && !empty($features)) {
                            $content .= "\n\n<div class='wsx4e2a-features' style='margin: 30px 0;'>";
                            $content .= "<ul style='list-style: none; padding: 0;'>";
                            foreach (array_slice($features, 0, 8) as $feature) {
                                $content .= "<li style='padding: 8px 0; border-bottom: 1px solid #eee;'>✅ " . esc_html($feature) . "</li>";
                            }
                            $content .= "</ul>";
                            $content .= "</div>";
                        }
                    }
                    
                    // İstatistikler ekle
                    $content .= "\n\n<div class='wsx4e2a-stats'>"
                        . "<div class='wsx4e2a-stat-item'><span class='wsx4e2a-stat-label'>Version</span><span class='wsx4e2a-stat-value'>" . esc_html($product->version ?: 'Latest') . "</span></div>"
                        . "<div class='wsx4e2a-stat-item'><span class='wsx4e2a-stat-label'>Downloads</span><span class='wsx4e2a-stat-value'>" . number_format($product->downloads_count ?: rand(1000, 50000)) . "</span></div>"
                        . "<div class='wsx4e2a-stat-item'><span class='wsx4e2a-stat-label'>Rating</span><span class='wsx4e2a-stat-value'>" . number_format($product->rating ?: (rand(35, 50) / 10), 1) . "/5.0</span></div>"
                        . "<div class='wsx4e2a-stat-item'><span class='wsx4e2a-stat-label'>Category</span><span class='wsx4e2a-stat-value'>" . esc_html(ucfirst($product->category)) . "</span></div>"
                        . "</div>";
                    
                    $post_data = [
                        'post_title' => $optimized_title,
                        'post_content' => $content,
                        'post_status' => 'publish',
                        'post_type' => 'post',
                        'post_name' => $slug,
                        'post_author' => get_current_user_id(),
                        'comment_status' => 'closed',
                        'ping_status' => 'closed'
                    ];
                    
                    $post_id = wp_insert_post($post_data);
                    if (is_wp_error($post_id)) {
                        error_log("Streamline: Post oluşturma hatası - Product ID: {$product->product_id}, Hata: " . $post_id->get_error_message());
                        continue;
                    }
                    
                    // Meta verileri ekle
                    update_post_meta($post_id, 'wsx4e2a_product_id', $product->product_id);
                    update_post_meta($post_id, 'wsx4e2a_mode', $mode);
                    update_post_meta($post_id, 'wsx4e2a_download_url', $download_url);
                    update_post_meta($post_id, 'wsx4e2a_demo_url', self::get_product_demo_url($product));
                    
                    // Öne çıkan görsel ekle (sadece lokal resim varsa) ve OG resmini ayarla
                    $og_image_url = '';
                    if (!empty($product->local_image_path)) {
                        $featured_image_id = self::set_featured_image_from_url($product->local_image_path, $post_id, $optimized_title);
                        if ($featured_image_id) {
                            set_post_thumbnail($post_id, $featured_image_id);
                            $og_image_url = wp_get_attachment_url($featured_image_id);
                        }
                    }
                    
                    // Schema markup ekle
                    $schema = self::generate_schema_markup($product, $post_id);
                    update_post_meta($post_id, '_sln_schema_markup', json_encode($schema));
                    
                    // SEO meta verileri (İngilizce)
                    $seo_title = $optimized_title . ' - Free Download | ' . get_bloginfo('name');
                    $seo_desc = wp_trim_words($content, 25, '...');
                    $seo_keywords = self::generate_keywords($product);
                    
                    // Yoast SEO meta alanları
                    update_post_meta($post_id, '_yoast_wpseo_title', $seo_title);
                    update_post_meta($post_id, '_yoast_wpseo_metadesc', $seo_desc);
                    update_post_meta($post_id, '_yoast_wpseo_focuskw', $seo_keywords);
                    
                    // Ek SEO meta alanları
                    update_post_meta($post_id, '_yoast_wpseo_meta-robots-noindex', '0');
                    update_post_meta($post_id, '_yoast_wpseo_meta-robots-nofollow', '0');
                    update_post_meta($post_id, '_yoast_wpseo_meta-robots-adv', 'none');
                    update_post_meta($post_id, '_yoast_wpseo_is_cornerstone', '0');
                    update_post_meta($post_id, '_yoast_wpseo_linkdex', '50');
                    update_post_meta($post_id, '_yoast_wpseo_content_score', '60');
                    
                    // Open Graph meta alanları
                    update_post_meta($post_id, '_yoast_wpseo_opengraph-title', $seo_title);
                    update_post_meta($post_id, '_yoast_wpseo_opengraph-description', $seo_desc);
                    update_post_meta($post_id, '_yoast_wpseo_opengraph-image', $og_image_url);
                    update_post_meta($post_id, '_yoast_wpseo_opengraph-image-id', '');
                    
                    // Twitter Card meta alanları
                    update_post_meta($post_id, '_yoast_wpseo_twitter-title', $seo_title);
                    update_post_meta($post_id, '_yoast_wpseo_twitter-description', $seo_desc);
                    update_post_meta($post_id, '_yoast_wpseo_twitter-image', $og_image_url);
                    update_post_meta($post_id, '_yoast_wpseo_twitter-image-id', '');
                    
                    // Schema markup
                    update_post_meta($post_id, '_yoast_wpseo_schema_article_type', 'Article');
                    update_post_meta($post_id, '_yoast_wpseo_schema_page_type', 'WebPage');
                    
                    // Canonical URL
                    update_post_meta($post_id, '_yoast_wpseo_canonical', get_permalink($post_id));
                    
                    // Streamline özel meta alanları
                    update_post_meta($post_id, '_sln_product_id', $product->product_id);
                    update_post_meta($post_id, '_sln_download_url', $masked_download_url);
                    update_post_meta($post_id, '_sln_demo_url', $demo_url);
                    update_post_meta($post_id, '_sln_version', $product->version);
                    update_post_meta($post_id, '_sln_rating', $product->rating);
                    update_post_meta($post_id, '_sln_downloads_count', $product->downloads_count);
                    update_post_meta($post_id, '_sln_category', $product->category);
                    update_post_meta($post_id, '_sln_price', $product->price);
                    update_post_meta($post_id, '_sln_features', $product->features);
                    update_post_meta($post_id, '_sln_updated_at', $product->updated_at);
                    
                    $batch_published++;
                    
                } catch (\Exception $e) {
                    error_log("Streamline: Ürün yayımlama hatası - Product ID: {$product->product_id}, Hata: " . $e->getMessage());
                    continue;
                }
            }
            
            $total_published += $batch_published;
            
            // Progress tracking
            if (count($unpublished_products) > 100) {
                $progress = round(($batch_index + 1) * $batch_size / count($unpublished_products) * 100, 1);
                error_log("Streamline: Batch tamamlandı - $batch_published ürün yayımlandı, Toplam: $total_published, İlerleme: %$progress");
            }
            
            // Memory temizliği
            unset($products);
            gc_collect_cycles();
        }
        
        update_option('wsx4e2a_last_publish', current_time('mysql'));
        error_log("Streamline: Yayımlama tamamlandı - $total_published ürün yayımlandı");
        
        return $total_published;
    }
    
    public static function get_primary_category_name($categories_json) {
        if (empty($categories_json)) return '';
        
        $categories = json_decode($categories_json, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($categories) || empty($categories)) {
            // Eğer JSON decode başarısız olursa veya boşsa, düz string olarak işlem yap
            $category_parts = array_map('trim', explode(',', $categories_json));
            return $category_parts[0];
        }
        
        foreach ($categories as $cat) {
            if (isset($cat['is_primary']) && $cat['is_primary']) {
                return $cat['name'];
            }
        }
        
        // Birincil kategori yoksa ilkini döndür
        return !empty($categories[0]['name']) ? $categories[0]['name'] : '';
    }

    /**
     * Şablon dosyasını kullanarak içerik üret
     */
    public static function render_product_content($product, $mode = 'ghost') {
        $template = 'ghost-content.php';
        $template_path = WSX4E2A_PLUGIN_DIR . 'templates/' . $template;
        if (!file_exists($template_path)) return '';
        
        // Değişkenleri hazırla
        $title = $product->title;
        $category = $product->category;
        $description = $product->description;
        $features = is_string($product->features) ? json_decode($product->features, true) : $product->features;
        $version = $product->version;
        $price = $product->price;
        $rating = $product->rating;
        $downloads_count = $product->downloads_count;
        $image_url = $product->image_url;
        $download_url = $product->download_url;
        $updated_at = $product->updated_at;
        
        ob_start();
        include $template_path;
        return ob_get_clean();
    }

    /**
     * Anasayfa içeriği üret (şablon ile)
     */
    public static function render_homepage($data) {
        $template_path = WSX4E2A_PLUGIN_DIR . 'templates/homepage.php';
        if (!file_exists($template_path)) return '';
        extract($data);
        ob_start();
        include $template_path;
        return ob_get_clean();
    }

    /**
     * Ghost içerikleri veritabanına kaydet
     */
    public static function save_ghost_content_to_db($product_data) {
        global $wpdb;
        
        $products_table = $wpdb->prefix . 'wsx4e2a_products';
        $ghost_table = $wpdb->prefix . 'wsx4e2a_ghost_content';
        
        // Fix: Handle both object and array types
        $product_id = null;
        if (is_object($product_data)) {
            $product_id = $product_data->product_id ?? null;
        } else {
            $product_id = $product_data['product_id'] ?? null;
        }
        
        if (empty($product_id)) {
            return;
        }

        $full_product = $wpdb->get_row($wpdb->prepare("SELECT * FROM $products_table WHERE product_id = %s", $product_id));
        if (empty($full_product)) {
            return;
        }

        // Refactored image download logic - RESİMLER ŞART!
        $image_path = '';
        
        // Önce product_data'dan ghost_lokal_product_image kontrol et (zaten indirilmiş olabilir)
        if (is_array($product_data) && !empty($product_data['ghost_lokal_product_image'])) {
            $image_path = $product_data['ghost_lokal_product_image'];
            error_log("Streamline: Using pre-downloaded image for product {$full_product->product_id}: {$image_path}");
        } elseif (!empty($full_product->local_image_path)) {
            // local_image_path kullan (panel287.com - %100 çalışıyor)
            $image_path = self::_download_and_save_image($full_product->local_image_path, $full_product->product_id);
            
            // Resim indirme başarısız olursa log tut ve uyar
            if (empty($image_path)) {
                error_log("Streamline WARNING: Image download failed for product {$full_product->product_id}. URL: {$full_product->image_url}");
                // Resim şart ama içeriği yine de kaydet (sonra manuel düzeltilebilir)
            } else {
                error_log("Streamline SUCCESS: Image downloaded for product {$full_product->product_id}: {$image_path}");
            }
        } else {
            error_log("Streamline WARNING: No image URL for product {$full_product->product_id}");
        }
        
        // Ensure ghost content table exists
        // Tablo oluşturma artık Database::create_tables() içinde yapılıyor

        $content = self::generate_ghost_content($full_product);
        $meta_description = wp_trim_words($content, 25, '...');
        $meta_keywords = self::generate_keywords($full_product);

        $ghost_data = [
            'product_id' => $full_product->product_id,
            'title' => $full_product->title,
            'content' => $content,
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords,
            'url_slug' => sanitize_title($full_product->title),
            'status' => 'active',
            'ghost_lokal_product_image' => $image_path,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];
        
        $wpdb->replace($ghost_table, $ghost_data);
        
        // ⚡ FRIDA: Insert ID'yi al ve obje olarak return et (tekrar DB'ye gitme)
        $insert_id = $wpdb->insert_id;
        if ($insert_id > 0) {
            $ghost_data['id'] = $insert_id;
        }
        
        return (object) $ghost_data;
    }

    /**
     * WordPress uploads klasöründeki en eski klasörü bul
     * @return string En eski klasör yolu (örn: /wp-content/uploads/2024/01/)
     */
    private static function _get_oldest_uploads_folder() {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'];
        
        $oldest_folder = null;
        $oldest_time = null;
        
        // Uploads klasöründeki tüm yıl klasörlerini tara
        if (!is_dir($base_dir)) {
            // Uploads klasörü yoksa, mevcut yıl/ay klasörünü oluştur
            $current_year = date('Y');
            $current_month = date('m');
            $fallback_dir = $base_dir . '/' . $current_year . '/' . $current_month;
            wp_mkdir_p($fallback_dir);
            return $fallback_dir;
        }
        
        $year_dirs = glob($base_dir . '/[0-9][0-9][0-9][0-9]', GLOB_ONLYDIR | GLOB_BRACE);
        
        if (empty($year_dirs)) {
            // Hiç yıl klasörü yoksa, mevcut yıl/ay klasörünü oluştur
            $current_year = date('Y');
            $current_month = date('m');
            $fallback_dir = $base_dir . '/' . $current_year . '/' . $current_month;
            wp_mkdir_p($fallback_dir);
            return $fallback_dir;
        }
        
        // Her yıl klasöründeki ay klasörlerini kontrol et
        foreach ($year_dirs as $year_dir) {
            $month_dirs = glob($year_dir . '/[0-9][0-9]', GLOB_ONLYDIR | GLOB_BRACE);
            
            foreach ($month_dirs as $month_dir) {
                $dir_time = filemtime($month_dir);
                
                if ($oldest_time === null || $dir_time < $oldest_time) {
                    $oldest_time = $dir_time;
                    $oldest_folder = $month_dir;
                }
            }
        }
        
        // En eski klasör bulunamazsa, ilk yıl/ay klasörünü kullan
        if ($oldest_folder === null) {
            // İlk yıl klasöründeki ilk ay klasörünü al
            sort($year_dirs);
            $first_year = $year_dirs[0];
            $month_dirs = glob($first_year . '/[0-9][0-9]', GLOB_ONLYDIR | GLOB_BRACE);
            if (!empty($month_dirs)) {
                sort($month_dirs);
                $oldest_folder = $month_dirs[0];
            } else {
                // Ay klasörü yoksa, yıl klasörünü kullan
                $oldest_folder = $first_year;
            }
        }
        
        // Hala bulunamazsa, mevcut yıl/ay klasörünü oluştur
        if ($oldest_folder === null || !is_dir($oldest_folder)) {
            $current_year = date('Y');
            $current_month = date('m');
            $oldest_folder = $base_dir . '/' . $current_year . '/' . $current_month;
            wp_mkdir_p($oldest_folder);
        }
        
        return $oldest_folder;
    }

    /**
     * Downloads an image using cURL, saves it to WordPress uploads oldest folder, and returns the local URL.
     * Resimler medya kütüphanesine eklenmez (doğal görünsün ama admin panelde görünmesin).
     * @param string $image_url The URL of the image to download.
     * @param string $product_id The product ID for creating a unique filename.
     * @return string The local URL of the saved image, or an empty string on failure.
     */
    public static function _download_and_save_image($image_url, $product_id) {
        // Timeout ve memory limit garantisi (toplu işlem sırasında)
        @set_time_limit(120); // Her resim için 2 dakika
        @ini_set('memory_limit', '256M');
        
        // WordPress uploads klasörünün en eski klasörünü kullan
        $oldest_folder = self::_get_oldest_uploads_folder();
        $upload_dir_info = wp_upload_dir();
        $base_url = $upload_dir_info['baseurl'];
        
        // URL kontrolü
        if (empty($image_url) || !filter_var($image_url, FILTER_VALIDATE_URL)) {
            error_log("Streamline Error: Invalid image URL for product {$product_id}: {$image_url}");
            return '';
        }
        
        $extension = pathinfo(parse_url($image_url, PHP_URL_PATH), PATHINFO_EXTENSION);
        if (empty($extension) || strlen($extension) > 5) {
            $extension = 'jpg';
        }
        
        // Dosya adı oluştur (doğal görünsün - product_id yerine rastgele isim)
        $random_hash = substr(md5($product_id . get_site_url()), 0, 8);
        $filename = 'img-' . $random_hash . '.' . $extension;
        
        $local_path = $oldest_folder . '/' . $filename;
        
        // Dosya zaten varsa direkt dön
        if (file_exists($local_path) && filesize($local_path) > 0) {
            // URL'yi oluştur (uploads klasörüne göre)
            $relative_path = str_replace($upload_dir_info['basedir'], '', $local_path);
            $local_url = $base_url . $relative_path;
            return $local_url;
        }

        // Klasör kontrolü
        if (!file_exists($oldest_folder)) {
            if (wp_mkdir_p($oldest_folder) === false) {
                error_log("Streamline Error: Image directory could not be created: " . $oldest_folder);
                return '';
            }
        }
        
        if (!is_writable($oldest_folder)) {
            error_log("Streamline Error: Image directory is not writable: " . $oldest_folder);
            return '';
        }

        // Retry mekanizması - 3 deneme
        $max_retries = 3;
        $retry_delay = 2; // 2 saniye bekle
        
        for ($attempt = 1; $attempt <= $max_retries; $attempt++) {
            // Her denemede timeout'u yenile
            @set_time_limit(60);
            
            $ch = curl_init($image_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 saniye timeout (daha makul)
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Bağlantı timeout 10 saniye
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Maksimum 5 redirect
            
            $image_content = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            // Başarılı indirme kontrolü
            if ($http_code == 200 && !empty($image_content) && strlen($image_content) > 100) {
                // Content type kontrolü (resim mi?)
                $is_image = false;
                if ($content_type) {
                    $is_image = strpos($content_type, 'image/') === 0;
                } else {
                    // Content type yoksa, içeriğin ilk byte'larına bak
                    $image_signatures = [
                        "\xFF\xD8\xFF", // JPEG
                        "\x89\x50\x4E\x47", // PNG
                        "GIF87a", // GIF
                        "GIF89a", // GIF
                        "RIFF", // WEBP (başlangıç)
                    ];
                    foreach ($image_signatures as $sig) {
                        if (strpos($image_content, $sig) === 0) {
                            $is_image = true;
                            break;
                        }
                    }
                }
                
                if ($is_image || empty($content_type)) {
                    // Dosyayı kaydet (medya kütüphanesine EKLEME - sadece dosya olarak kaydet)
                    $write_result = @file_put_contents($local_path, $image_content);
                    if ($write_result !== false && filesize($local_path) > 0) {
                        // URL'yi oluştur (uploads klasörüne göre)
                        $relative_path = str_replace($upload_dir_info['basedir'], '', $local_path);
                        $local_url = $base_url . $relative_path;
                        
                        error_log("Streamline: Image downloaded successfully for product {$product_id} (attempt {$attempt}) to: {$local_path}");
                        return $local_url;
                    } else {
                        error_log("Streamline Error: Could not write image to path: " . $local_path);
                    }
                } else {
                    error_log("Streamline Error: Downloaded content is not an image for product {$product_id}. Content-Type: {$content_type}");
                }
            } else {
                // Hata logla ama retry yap
                if ($attempt < $max_retries) {
                    error_log("Streamline: Image download failed for product {$product_id} (attempt {$attempt}/{$max_retries}). HTTP Code: {$http_code}, Error: {$error}. Retrying...");
                    sleep($retry_delay); // Retry öncesi bekle
                } else {
                    error_log("Streamline Error: Failed to download image from {$image_url} for product {$product_id} after {$max_retries} attempts. HTTP Code: {$http_code}, Error: {$error}");
                }
            }
        }
        
        return '';
    }

    /**
     * Ghost içerikleri toplu olarak kaydet
     */
    public static function save_all_ghost_content() {
        global $wpdb;
        
        $products = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wsx4e2a_products WHERE status = 'active'");
        $saved = 0;
        
        foreach ($products as $product) {
            $result = self::save_ghost_content_to_db($product);
            if ($result) {
                $saved++;
            }
        }
        
        return $saved;
    }

    /**
     * Ghost içerik veritabanından getir
     * ⚡ FRIDA: Request-level static cache - Duplicate query önleme
     */
    public static function get_ghost_content($product_id) {
        // ⚡ Static cache - Aynı request içinde tekrar DB'ye gitme
        static $cache = [];
        
        if (isset($cache[$product_id])) {
            return $cache[$product_id];
        }
        
        global $wpdb;
        $ghost_table = $wpdb->prefix . 'wsx4e2a_ghost_content';
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $ghost_table WHERE product_id = %s AND status = 'active'", $product_id));
        
        // Cache'e kaydet
        $cache[$product_id] = $result;
        
        return $result;
    }

    /**
     * Ghost içerik URL'sini oluştur
     */
    public static function get_ghost_url($product_id) {
        $options = wsx4e2a_get_options();
        $ghost_base = $options['ghost_url_base'] ?? 'content';
        // Slug öncelikli URL
        try {
            $ghost_row = self::get_ghost_content($product_id);
            if (!empty($ghost_row) && !empty($ghost_row->url_slug)) {
                return home_url("/$ghost_base/" . $ghost_row->url_slug . "/");
            }
        } catch (\Exception $e) {
            // Sessizce fallback
        }
        return home_url("/$ghost_base/$product_id/");
    }

    /**
     * Demo URL oluştur
     */
    public static function get_demo_url($product_id, $title = '') {
        $options = wsx4e2a_get_options();
        $current_domain = parse_url(get_site_url(), PHP_URL_HOST);
        
        // HacklinkPanel.app yapısına göre demo URL'leri
        $demo_patterns = [
            'https://demo.' . $current_domain . '/{product_id}',
            'https://demo.' . $current_domain . '/{slug}',
            'https://' . $current_domain . '/demo/{product_id}',
            'https://' . $current_domain . '/demo/{slug}',
            'https://demo.{product_id}.' . $current_domain,
            'https://{product_id}.demo.' . $current_domain
        ];
        
        $pattern = $demo_patterns[array_rand($demo_patterns)];
        $slug = sanitize_title($title ?: $product_id);
        
        return str_replace(['{product_id}', '{slug}'], [$product_id, $slug], $pattern);
    }

    /**
     * Ürün için demo URL oluştur
     */
    public static function get_product_demo_url($product) {
        $current_domain = parse_url(get_site_url(), PHP_URL_HOST);
        
        // Eğer ürünün kendi demo URL'si varsa, domain'i değiştir
        if (!empty($product->demo_url)) {
            return str_replace(wsx4e2a_bld_cfg('s20'), $current_domain, $product->demo_url);
        }
        
        // Yoksa varsayılan demo URL oluştur
        return self::get_demo_url($product->product_id, $product->title);
    }

    /**
     * SEO için anahtar kelime listesi
     */
    public static function get_seo_keywords() {
        return [
            'wordpress', 'wp', 'theme', 'plugin', 'free', 'nulled', 'crack', 'full', 'premium',
            'download', 'gpl', 'opensource', 'responsive', 'seo', 'optimized', 'latest',
            'professional', 'business', 'ecommerce', 'woocommerce', 'blog', 'portfolio',
            'multipurpose', 'creative', 'modern', 'clean', 'fast', 'secure', 'mobile',
            'tablet', 'desktop', 'cross-browser', 'compatible', 'documentation', 'support',
            'updates', 'features', 'customizable', 'flexible', 'powerful', 'easy', 'simple',
            'advanced', 'pro', 'enterprise', 'starter', 'basic', 'standard', 'ultimate',
            'complete', 'comprehensive', 'extensive', 'detailed', 'thorough', 'exhaustive'
        ];
    }

    /**
     * Rastgele anahtar kelime seç
     */
    public static function get_random_keywords($count = 5) {
        $keywords = self::get_seo_keywords();
        $selected = [];
        
        for ($i = 0; $i < $count; $i++) {
            $selected[] = $keywords[array_rand($keywords)];
        }
        
        return array_unique($selected);
    }

    /**
     * Download URL'yi domain ile gölgele
     */
    public static function mask_download_url($original_url, $product_id) {
        $current_domain = parse_url(home_url(), PHP_URL_HOST);
        
        // HacklinkPanel.app URL'lerini mevcut domain ile değiştir
        if (strpos($original_url, wsx4e2a_bld_cfg('s20')) !== false) {
            return str_replace(wsx4e2a_bld_cfg('s20'), $current_domain, $original_url);
        }
        
        // SyncDomain URL'lerini de değiştir
        if (strpos($original_url, 'syncdomain') !== false) {
            return str_replace('syncdomain', $current_domain, $original_url);
        }

        return $original_url;
    }

    /**
     * Download URL'yi manipüle et - aktif domain'e ait gibi göster
     */
    public static function get_masked_download_url($product_id) {
        return home_url("/download/$product_id/");
    }

    /**
     * Site bazlı özgün download buton texti oluştur
     * Her site için farklı buton texti (deterministik seçim)
     */
    public static function get_dynamic_download_button_text($product_title = '') {
        // Site hash'i kullanarak deterministik seçim
        $site_hash = crc32(get_site_url());
        
        // Ürün başlığını temizle ve kısalt
        $clean_title = '';
        if (!empty($product_title)) {
            $clean_title = sanitize_text_field($product_title);
            // SyncDomain gibi referansları kaldır
            $clean_title = preg_replace('/\s*-\s*WpSync4E2A\.Com$/i', '', $clean_title);
            // Çok uzunsa kısalt (ilk 30 karakter)
            if (strlen($clean_title) > 30) {
                $clean_title = substr($clean_title, 0, 30) . '...';
            }
        }
        
        // 10 farklı buton text şablonu
        $button_templates = [
            'Download {product} ZIP',
            'Download Now',
            'Get {product}',
            'Download {product}',
            'Free Download',
            'Download {product} Free',
            'Get {product} ZIP',
            'Download {product} Now',
            'Free {product} Download',
            'Download {product} Latest'
        ];
        
        // Site hash'e göre şablon seç (deterministik)
        $template_index = abs($site_hash) % count($button_templates);
        $selected_template = $button_templates[$template_index];
        
        // {product} placeholder'ını değiştir
        if (!empty($clean_title)) {
            $button_text = str_replace('{product}', $clean_title, $selected_template);
        } else {
            // Ürün adı yoksa {product} kısmını kaldır
            $button_text = str_replace(['{product} ', ' {product}'], '', $selected_template);
            $button_text = trim($button_text);
        }
        
        return $button_text;
    }

    /**
     * Site bazlı özgün "Key Features" başlığı oluştur
     * Her site için farklı başlık (deterministik seçim)
     */
    public static function get_dynamic_features_title() {
        // Site hash'i kullanarak deterministik seçim
        $site_hash = crc32(get_site_url());
        
        // 10 farklı başlık şablonu
        $title_templates = [
            'Key Features',
            'Main Features',
            'Core Features',
            'Essential Features',
            'Product Features',
            'Key Highlights',
            'Main Highlights',
            'Core Capabilities',
            'Essential Capabilities',
            'Product Highlights'
        ];
        
        // Site hash'e göre başlık seç (deterministik)
        $title_index = abs($site_hash >> 8) % count($title_templates);
        return $title_templates[$title_index];
    }

    /**
     * Site bazlı özgün keywords listesi oluştur
     * Her site için farklı keywords seti (deterministik seçim)
     */
    public static function get_dynamic_features_keywords($product = null) {
        // Site ve ürün bazlı hash
        $site_hash = crc32(get_site_url());
        $product_hash = $product ? crc32($product->product_id) : 0;
        $combined_hash = $site_hash ^ ($product_hash >> 4);
        
        // 15 farklı keywords seti
        $keyword_sets = [
            ['WordPress', 'Professional', 'Modern', 'Responsive', 'SEO', 'Optimized', 'Premium', 'Quality'],
            ['Advanced', 'Innovative', 'Efficient', 'Scalable', 'Flexible', 'Reliable', 'Performance', 'Excellence'],
            ['Cutting-edge', 'Sophisticated', 'Comprehensive', 'Intuitive', 'Powerful', 'Streamlined', 'Enhanced', 'Superior'],
            ['Professional', 'Enterprise', 'Business', 'Commercial', 'Premium', 'Advanced', 'Modern', 'Optimized'],
            ['High-performance', 'User-friendly', 'Feature-rich', 'Customizable', 'Responsive', 'SEO-friendly', 'Fast', 'Secure'],
            ['Modern Design', 'Clean Code', 'Fast Loading', 'Mobile Ready', 'SEO Optimized', 'Easy Setup', 'Well Documented', 'Regular Updates'],
            ['Premium Quality', 'Professional Grade', 'Enterprise Ready', 'Scalable Solution', 'User Centric', 'Performance Focused', 'Security First', 'Developer Friendly'],
            ['Innovative', 'Robust', 'Secure', 'Fast', 'Flexible', 'Customizable', 'Professional', 'Modern'],
            ['WordPress', 'Premium', 'Professional', 'Modern', 'Responsive', 'SEO', 'Fast', 'Secure'],
            ['Advanced Features', 'Easy Customization', 'Mobile Responsive', 'SEO Optimized', 'Fast Performance', 'Secure Code', 'Regular Updates', 'Great Support'],
            ['Professional', 'Modern', 'Responsive', 'SEO', 'Fast', 'Secure', 'Customizable', 'Premium'],
            ['Enterprise', 'Business', 'Professional', 'Advanced', 'Modern', 'Scalable', 'Reliable', 'Secure'],
            ['High Quality', 'Well Coded', 'Fast Loading', 'Mobile First', 'SEO Ready', 'Easy to Use', 'Fully Customizable', 'Regularly Updated'],
            ['Premium', 'Professional', 'Modern', 'Responsive', 'SEO', 'Fast', 'Secure', 'Quality'],
            ['Advanced', 'Innovative', 'Efficient', 'Scalable', 'Flexible', 'Reliable', 'Powerful', 'Modern']
        ];
        
        // Site hash'e göre keywords seti seç (deterministik)
        $keyword_index = abs($combined_hash) % count($keyword_sets);
        return $keyword_sets[$keyword_index];
    }

    /**
     * Gelişmiş dinamik içerik üret (300+ kelime) - Sabit seçim
     */
    public static function generate_advanced_content($product) {
        // Site ve ürün bazlı sabit hash
        $site_hash = crc32(get_site_url());
        $product_hash = crc32($product->product_id);
        $combined_hash = $site_hash ^ $product_hash;
        
        $keywords = self::get_consistent_keywords(8, $combined_hash);
        $current_domain = parse_url(home_url(), PHP_URL_HOST);
        
        // Ürün türüne göre içerik şablonları
        $templates = [
            'theme' => [
                'intro' => [
                    "Discover the ultimate {category} WordPress theme designed for modern websites. This premium {category} theme offers unparalleled flexibility and stunning design options that will transform your online presence.",
                    "Transform your website with this exceptional {category} WordPress theme. Built with the latest web technologies, this theme provides a seamless user experience across all devices.",
                    "Experience the power of professional web design with this outstanding {category} WordPress theme. Perfect for businesses, portfolios, and creative projects."
                ],
                'features' => [
                    "This {category} WordPress theme comes packed with advanced features including responsive design, SEO optimization, and customizable layouts. The theme is built with clean, semantic code ensuring fast loading times and excellent search engine rankings.",
                    "Key features include mobile-first responsive design, cross-browser compatibility, and extensive customization options. The theme supports multiple page layouts, custom post types, and integrates seamlessly with popular WordPress plugins.",
                    "Built for performance and flexibility, this theme includes advanced typography options, color schemes, and layout variations. The modular design allows for easy customization without affecting core functionality."
                ],
                'technical' => [
                    "Technically advanced with clean, well-documented code, this {category} theme follows WordPress coding standards and best practices. The theme is optimized for speed, security, and search engine visibility.",
                    "The theme architecture ensures compatibility with future WordPress updates while maintaining backward compatibility. Built with modern CSS frameworks and JavaScript libraries for enhanced functionality.",
                    "Security-focused development includes regular updates, vulnerability patches, and secure coding practices. The theme is tested across multiple environments and browser configurations."
                ]
            ],
            'plugin' => [
                'intro' => [
                    "Enhance your WordPress website with this powerful {category} plugin that delivers professional functionality and seamless integration. This premium plugin is designed to streamline your workflow and improve user experience.",
                    "Take your WordPress site to the next level with this comprehensive {category} plugin. Packed with advanced features and intuitive controls, this plugin is essential for modern websites.",
                    "Optimize your WordPress performance with this cutting-edge {category} plugin. Built for efficiency and reliability, this plugin provides the tools you need for success."
                ],
                'features' => [
                    "This {category} WordPress plugin offers extensive functionality including advanced settings, user management, and performance optimization tools. The plugin integrates seamlessly with your existing WordPress installation.",
                    "Key features include automated processes, detailed analytics, and comprehensive reporting tools. The plugin supports multiple user roles, custom workflows, and extensive customization options.",
                    "Built for scalability and reliability, this plugin includes backup systems, error handling, and performance monitoring. The modular architecture allows for easy extension and customization."
                ],
                'technical' => [
                    "Technically sophisticated with clean, efficient code, this {category} plugin follows WordPress development standards and best practices. The plugin is optimized for performance and security.",
                    "The plugin architecture ensures compatibility with WordPress core updates while maintaining feature stability. Built with modern PHP practices and secure coding methodologies.",
                    "Security-focused development includes regular security audits, vulnerability assessments, and secure data handling. The plugin is thoroughly tested across various WordPress configurations."
                ]
            ]
        ];
        
        $category = $product->category;
        $template = $templates[$category] ?? $templates['plugin'];
        
        // Sabit seçim (random değil)
        $intro_index = abs($combined_hash) % count($template['intro']);
        $features_index = abs($combined_hash >> 8) % count($template['features']);
        $technical_index = abs($combined_hash >> 16) % count($template['technical']);
        
        $intro = $template['intro'][$intro_index];
        $features = $template['features'][$features_index];
        $technical = $template['technical'][$technical_index];
        
        // Anahtar kelimeleri yerleştir
        $intro = str_replace('{category}', $category, $intro);
        $features = str_replace('{category}', $category, $features);
        $technical = str_replace('{category}', $category, $technical);
        
        // Ek içerik parçaları
        $additional_content = [
            "The " . $product->title . " is designed to meet the needs of modern web developers and business owners. With its intuitive interface and powerful features, this " . $category . " stands out in the competitive WordPress ecosystem.",
            "Whether you're building a personal blog, business website, or e-commerce platform, this " . $category . " provides the tools and flexibility you need. The comprehensive documentation and support ensure a smooth implementation process.",
            "Regular updates and community support make this " . $category . " a reliable choice for long-term projects. The development team actively maintains and improves the product based on user feedback and industry trends.",
            "Compatibility with popular WordPress plugins and themes ensures seamless integration with your existing setup. The " . $category . " follows WordPress coding standards and best practices for optimal performance.",
            "Advanced customization options allow you to tailor the " . $category . " to your specific requirements. From color schemes to layout modifications, every aspect can be personalized to match your brand identity."
        ];
        
        // İçeriği birleştir
        $content = $intro . " " . $features . " " . $technical;
        
        // Sabit ek içerik seçimi
        $additional_count = (abs($combined_hash >> 24) % 3) + 2; // 2-4 arası
        $selected_additional = [];
        for ($i = 0; $i < $additional_count; $i++) {
            $index = (abs($combined_hash >> ($i * 8)) % count($additional_content));
            if (!in_array($index, $selected_additional)) {
                $selected_additional[] = $index;
            }
        }
        
        foreach ($selected_additional as $index) {
            $content .= " " . $additional_content[$index];
        }
        
        // Anahtar kelimeleri doğal şekilde ekle
        foreach ($keywords as $keyword) {
            if (strlen($content) < 1500) { // İçerik çok uzun olmasın
                $content .= " The " . $category . " includes " . $keyword . " functionality for enhanced user experience.";
            }
        }
        
        // Domain değiştirme
        $content = str_replace(wsx4e2a_bld_cfg('s20'), $current_domain, $content);
        $content = str_replace('syncdomain', $current_domain, $content);
        $content = str_replace('SyncDomain', $current_domain, $content);
        $content = str_replace('SyncDomain', $current_domain, $content);
        
        return $content;
    }
    
    /**
     * Sabit keywords üret (random değil)
     */
    public static function get_consistent_keywords($count = 5, $hash = null) {
        if ($hash === null) {
            $hash = crc32(get_site_url());
        }
        
        $keywords = [
            'WordPress', 'Professional', 'Modern', 'Responsive', 'SEO', 'Optimized', 'Premium', 'Quality',
            'Advanced', 'Innovative', 'Efficient', 'Scalable', 'Flexible', 'Reliable', 'Performance', 'Excellence',
            'Cutting-edge', 'Sophisticated', 'Comprehensive', 'Intuitive', 'Powerful', 'Streamlined', 'Enhanced', 'Superior'
        ];
        
        $selected = [];
        for ($i = 0; $i < $count && $i < count($keywords); $i++) {
            $index = abs($hash >> ($i * 8)) % count($keywords);
            $keyword = $keywords[$index];
            if (!in_array($keyword, $selected)) {
                $selected[] = $keyword;
            }
        }
        
        return $selected;
    }

    /**
     * Öne çıkan görsel ekle
     */
    public static function set_featured_image_from_url($image_url, $post_id, $title) {
        // Görseli indir
        $response = wp_remote_get($image_url);
        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            return false;
        }

        // Görseli medya kütüphanesine kaydet
        $upload = wp_upload_bits($title . '.jpg', null, $body);
        if (isset($upload['error']) && $upload['error']) {
            return false;
        }

        $file_path = $upload['file'];
        $file_url = $upload['url'];

        // Görseli post'a ekleyin
        $attachment_id = self::insert_attachment($file_path, $post_id, $title);
        if (!$attachment_id) {
            return false;
        }

        return $attachment_id;
    }

    /**
     * Görseli medya kütüphanesine ekleyin
     */
    public static function insert_attachment($file_path, $post_id, $title) {
        $file = [
            'name' => basename($file_path),
            'type' => 'image/jpeg',
            'tmp_name' => $file_path,
            'error' => 0,
            'size' => filesize($file_path)
        ];

        $attachment = [
            'post_mime_type' => $file['type'],
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'inherit'
        ];

        $attachment_id = wp_insert_attachment($attachment, $file['tmp_name'], $post_id);
        if (is_wp_error($attachment_id)) {
            return false;
        }

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $file['tmp_name']);
        wp_update_attachment_metadata($attachment_id, $attachment_data);

        return $attachment_id;
    }

    public static function generate_ghost_content($product) {
        try {
            // Site ve ürün bazlı sabit hash (sayfa yenilendiğinde değişmez)
            $site_hash = crc32(get_site_url());
            $product_hash = crc32($product->product_id);
            $combined_hash = $site_hash ^ $product_hash;
            
            // 300 kelimelik özgün içerik şablonları
            $content_templates = [
                [
                    'intro' => "Discover the exceptional capabilities of {$product->title}, a premium {$product->category} that revolutionizes the way you approach web development. This sophisticated solution combines cutting-edge technology with intuitive design principles to deliver an unparalleled user experience.",
                    'features' => "Built with modern development standards, this {$product->category} offers a comprehensive suite of features designed to enhance your website's performance and functionality. The responsive design ensures seamless operation across all devices, while the advanced customization options allow you to tailor the experience to your specific needs.",
                    'technical' => "From a technical perspective, this {$product->category} demonstrates exceptional optimization and efficiency. The clean, well-structured codebase ensures fast loading times and smooth operation, while the modular architecture provides flexibility for future enhancements and modifications.",
                    'benefits' => "Implementing this {$product->category} provides numerous benefits for your web projects. Enhanced user engagement, improved conversion rates, and streamlined workflow management are just a few of the advantages you can expect. The professional-grade quality ensures reliability and long-term success.",
                    'conclusion' => "Whether you're a seasoned developer or just starting your web development journey, this {$product->category} offers the perfect balance of power and simplicity. Its comprehensive feature set and user-friendly interface make it an ideal choice for projects of any scale."
                ],
                [
                    'intro' => "Experience the power of {$product->title}, an advanced {$product->category} that sets new standards in web development excellence. This professional-grade solution offers unmatched functionality while maintaining the highest standards of quality and performance.",
                    'features' => "The feature-rich architecture of this {$product->category} provides everything you need for modern web development. Advanced SEO optimization, lightning-fast performance, and extensive customization capabilities work together to create an exceptional user experience.",
                    'technical' => "Technical excellence is at the core of this {$product->category}. The optimized code structure ensures maximum efficiency, while the scalable design allows for seamless growth and expansion. Every aspect has been carefully crafted for optimal performance.",
                    'benefits' => "Choosing this {$product->category} means investing in success. Improved website performance, enhanced user satisfaction, and increased business opportunities are among the many benefits you'll experience. The professional implementation ensures consistent results.",
                    'conclusion' => "This {$product->category} represents the perfect solution for developers who demand excellence. Its comprehensive functionality, combined with ease of use, makes it an essential tool for creating outstanding web experiences."
                ],
                [
                    'intro' => "Transform your web development approach with {$product->title}, a revolutionary {$product->category} that combines innovation with reliability. This cutting-edge solution provides the tools and capabilities needed to create exceptional digital experiences.",
                    'features' => "The comprehensive feature set of this {$product->category} addresses every aspect of modern web development. From responsive design to advanced functionality, every element has been carefully designed to provide maximum value and performance.",
                    'technical' => "Technical sophistication defines this {$product->category}. The optimized architecture ensures superior performance while maintaining flexibility for customization. The clean, maintainable codebase supports long-term success and growth.",
                    'benefits' => "Implementing this {$product->category} delivers immediate and long-term benefits. Enhanced user experience, improved performance metrics, and increased development efficiency are among the key advantages you'll realize.",
                    'conclusion' => "This {$product->category} stands as a testament to quality and innovation in web development. Its comprehensive capabilities and user-friendly design make it the perfect choice for creating exceptional web experiences."
                ]
            ];
            
            // Site ve ürün bazlı sabit template seçimi
            $template_index = abs($combined_hash) % count($content_templates);
            $selected_template = $content_templates[$template_index];
            
            // Benzersiz ID oluştur
            $unique_id = substr(md5($product->product_id . get_site_url()), 0, 8);
            
            // 300 kelimelik özgün içerik oluştur - WordPress standart sınıf kullan
            $dynamic_content = "<div class='entry-content' id='content-{$unique_id}'>";
            
            // Her bölümü ekle
            $dynamic_content .= "<p>{$selected_template['intro']}</p>";
            $dynamic_content .= "<p>{$selected_template['features']}</p>";
            $dynamic_content .= "<p>{$selected_template['technical']}</p>";
            $dynamic_content .= "<p>{$selected_template['benefits']}</p>";
            $dynamic_content .= "<p>{$selected_template['conclusion']}</p>";
            
            // Özgün keywords ekle - site bazlı özgün keywords (başlık yok)
            $selected_keywords = self::get_dynamic_features_keywords($product);
            $dynamic_content .= "\n\n<p>" . implode(', ', $selected_keywords) . ".</p>";
            
            $dynamic_content .= "</div>";
            
            // Domain değiştirme - SyncDomain'u aktif domain ile değiştir
            $current_domain = parse_url(get_site_url(), PHP_URL_HOST);
            $dynamic_content = str_replace('SyncDomain', $current_domain, $dynamic_content);
            $dynamic_content = str_replace('SyncDomain', $current_domain, $dynamic_content);
            $dynamic_content = str_replace('syncdomain', $current_domain, $dynamic_content);
            $dynamic_content = str_replace(wsx4e2a_bld_cfg('s20'), $current_domain, $dynamic_content);
            
            return $dynamic_content;
            
        } catch (Exception $e) {
            return "<p>Professional {$product->category} with advanced features and modern design. This comprehensive solution offers exceptional functionality and performance for modern web development needs.</p>";
        }
    }

    /**
     * Ghost ürünleri yayımla
     */
    public static function publish_ghost_products($count = 10) {
        global $wpdb;
        $products_table = $wpdb->prefix . 'wsx4e2a_products';
        $ghost_table = $wpdb->prefix . 'wsx4e2a_ghost_content';

        set_time_limit(300);
        ini_set('memory_limit', '512M');
        
        // Kusursuzlaştırma: Klasörün varlığını ve yazılabilirliğini kontrol et
        $upload_dir = WSX4E2A_PLUGIN_DIR . 'img-all/';
        if (!file_exists($upload_dir)) {
            if (wp_mkdir_p($upload_dir) === false) {
                 throw new \Exception("Image directory could not be created: $upload_dir");
            }
        }
        if (!is_writable($upload_dir)) {
            throw new \Exception("Image directory is not writable: $upload_dir. Please check permissions.");
        }

        // Henüz ghost tablosuna eklenmemiş ürünleri bul
        // Tamamen random sistem - her çalıştırmada farklı ürünler
        
        // 1. Toplam aktif ürün sayısını al
        $total_count = $wpdb->get_var("
            SELECT COUNT(p.id) 
            FROM $products_table p
            LEFT JOIN $ghost_table gc ON p.product_id = gc.product_id
            WHERE p.status = 'active' AND gc.id IS NULL
        ");
        
        // 2. Random offset hesapla (güvenli aralık)
        $safe_count = max(1, $total_count);
        $random_offset = rand(0, max(0, $safe_count - $count));
        
        $unpublished_products = $wpdb->get_results($wpdb->prepare("
            SELECT p.* 
            FROM $products_table p
            LEFT JOIN $ghost_table gc ON p.product_id = gc.product_id
            WHERE p.status = 'active' AND gc.id IS NULL
            ORDER BY p.id
            LIMIT %d OFFSET %d
        ", $count, $random_offset));

        if (empty($unpublished_products)) {
            return ['published' => 0, 'skipped' => 0];
        }

        $published_count = 0;
        $skipped_count = 0;

        foreach ($unpublished_products as $product) {
            // Her ghost ürün için rating ve downloads_count kontrolü
            $updated = false;
            if (empty($product->rating) || floatval($product->rating) < 3.5) {
                $product->rating = round(mt_rand(35, 48) / 10, 1); // 3.5 - 4.8 arası
                $wpdb->update(
                    $products_table,
                    ['rating' => $product->rating],
                    ['product_id' => $product->product_id]
                );
                $updated = true;
            }
            if (empty($product->downloads_count) || intval($product->downloads_count) < 1) {
                $product->downloads_count = rand(1000, 50000);
                $wpdb->update(
                    $products_table,
                    ['downloads_count' => $product->downloads_count],
                    ['product_id' => $product->product_id]
                );
                $updated = true;
            }
            // Veritabanı gerçekten güncellendi mi kontrol et, gerekirse tekrar dene
            if ($updated) {
                $row = $wpdb->get_row($wpdb->prepare("SELECT rating, downloads_count FROM $products_table WHERE product_id = %s", $product->product_id));
                if (floatval($row->rating) < 3.5 || intval($row->downloads_count) < 1) {
                    $product->rating = round(mt_rand(35, 48) / 10, 1);
                    $product->downloads_count = rand(1000, 50000);
                    $wpdb->update(
                        $products_table,
                        [
                            'rating' => $product->rating,
                            'downloads_count' => $product->downloads_count
                        ],
                        ['product_id' => $product->product_id]
                    );
                }
            }
            try {
                // Timeout garantisi - her ürün için yenile
                @set_time_limit(180); // 3 dakika
                
            // Resim indirme kontrolü - önce resmi indir, sonra içeriği kaydet
            $image_downloaded = false;
            $local_image_url = '';
            
            // local_image_path kullan (panel287.com - %100 çalışıyor)
            if (!empty($product->local_image_path)) {
                $local_image_url = self::_download_and_save_image($product->local_image_path, $product->product_id);
                    if (!empty($local_image_url)) {
                        $image_downloaded = true;
                    }
                }
                
                // İçeriği kaydet (resim başarısız olsa bile kaydet, ama log tut)
                $product_array = (array)$product;
                // Resim URL'sini ekle
                if (!empty($local_image_url)) {
                    $product_array['ghost_lokal_product_image'] = $local_image_url;
                }
                self::save_ghost_content_to_db($product_array);
                
                if ($image_downloaded) {
                    $published_count++;
                    error_log("Streamline: Ghost product published with image - Product ID: {$product->product_id}");
                } else {
                    $published_count++;
                    error_log("Streamline WARNING: Ghost product published WITHOUT image - Product ID: {$product->product_id}");
                }
            } catch (\Exception $e) {
                error_log("Streamline: Ghost ürün yayımlama hatası - Product ID: {$product->product_id}, Hata: " . $e->getMessage());
                $skipped_count++;
                continue;
            }
        }
        
        update_option('wsx4e2a_last_ghost_publish', current_time('mysql'));

        return ['published' => $published_count, 'skipped' => $skipped_count];
    }

    /**
     * Publish a specified number of normal content posts.
     * This is a wrapper for publish_products for clarity and cron usage.
     * @param int $count Number of products to publish.
     */
    public static function publish_normal_content($count = 1) {
        return self::publish_products('normal', $count);
    }
} 