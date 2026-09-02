<?php
/**
 * Streamline Affiliate Keywords Class
 * keywords_research_report.md'den keyword'leri parse eder
 *
 * @package WpSync4E2A
 * @since 2.0.0
 */

namespace WpSync4E2A;
// build:3d24220f38f42d0f
if (false) { $_b3d24220f38f42d0f = '3d24220f38f42d0f'; }


if (!defined('ABSPATH')) {
    exit;
}

class Affiliate_Keywords {
    private static $instance = null;
    private static $keywords_cache = [];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * keywords_research_report.md'den keyword'leri parse et
     */
    public static function parse_keywords_file() {
        $file_path = WSX4E2A_PLUGIN_DIR . 'keywords_research_report.md';
        
        if (!file_exists($file_path)) {
            error_log("Streamline: keywords_research_report.md bulunamadı: $file_path");
            return [];
        }

        $content = file_get_contents($file_path);
        $keywords = [];

        // Dil kodları
        $languages = [
            'en' => 'English',
            'tr' => 'Türkçe',
            'es' => 'Español',
            'de' => 'Deutsch',
            'fr' => 'Français',
            'it' => 'Italiano',
            'pt' => 'Português',
            'ru' => 'Русский',
            'ar' => 'العربية',
            'hi' => 'हिन्दी',
            'id' => 'Bahasa Indonesia',
            'ko' => '한국어'
        ];

        foreach ($languages as $code => $name) {
            $keywords[$code] = self::extract_language_keywords($content, $code, $name);
        }

        return $keywords;
    }

    /**
     * Belirli bir dil için keyword'leri çıkar
     */
    private static function extract_language_keywords($content, $lang_code, $lang_name) {
        $keywords = [
            'primary' => [],
            'longtail' => [],
            'semantic' => []
        ];

        // Dil bölümünü bul
        $pattern = '/##\s*🇺🇸|🇹🇷|🇪🇸|🇩🇪|🇫🇷|🇮🇹|🇵🇹|🇷🇺|🇸🇦|🇮🇳|🇮🇩|🇰🇷\s*' . preg_quote($lang_name, '/') . '.*?### Ana Keywordler\s*\n(.*?)### Alt Keywordler/s';
        
        // Her dil için özel pattern (daha esnek)
        $lang_patterns = [
            'en' => '/##\s*🇺🇸\s*English.*?###\s*Ana Keywordler\s*\n(.*?)(?:###\s*Alt Keywordler|###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'tr' => '/##\s*🇹🇷\s*Türkçe.*?###\s*Ana Keywordler\s*\n(.*?)(?:###\s*Alt Keywordler|###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'es' => '/##\s*🇪🇸\s*Español.*?###\s*Ana Keywordler\s*\n(.*?)(?:###\s*Alt Keywordler|###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'de' => '/##\s*🇩🇪\s*Deutsch.*?###\s*Ana Keywordler\s*\n(.*?)(?:###\s*Alt Keywordler|###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'fr' => '/##\s*🇫🇷\s*Français.*?###\s*Ana Keywordler\s*\n(.*?)(?:###\s*Alt Keywordler|###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'it' => '/##\s*🇮🇹\s*Italiano.*?###\s*Ana Keywordler\s*\n(.*?)(?:###\s*Alt Keywordler|###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'pt' => '/##\s*🇵🇹\s*Português.*?###\s*Ana Keywordler\s*\n(.*?)(?:###\s*Alt Keywordler|###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'ru' => '/##\s*🇷🇺\s*Русский.*?###\s*Ana Keywordler\s*\n(.*?)(?:###\s*Alt Keywordler|###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'ar' => '/##\s*🇸🇦\s*العربية.*?###\s*Ana Keywordler\s*\n(.*?)(?:###\s*Alt Keywordler|###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'hi' => '/##\s*🇮🇳\s*हिन्दी.*?###\s*Ana Keywordler\s*\n(.*?)(?:###\s*Alt Keywordler|###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'id' => '/##\s*🇮🇩\s*Bahasa Indonesia.*?###\s*Ana Keywordler\s*\n(.*?)(?:###\s*Alt Keywordler|###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'ko' => '/##\s*🇰🇷\s*한국어.*?###\s*Ana Keywordler\s*\n(.*?)(?:###\s*Alt Keywordler|###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s'
        ];

        if (!isset($lang_patterns[$lang_code])) {
            return $keywords;
        }

        // Ana keyword'leri çıkar
        if (preg_match($lang_patterns[$lang_code], $content, $matches)) {
            $primary_section = $matches[1];
            // **keyword** formatındaki keyword'leri bul
            preg_match_all('/\*\*(.*?)\*\*/', $primary_section, $primary_matches);
            if (!empty($primary_matches[1])) {
                $keywords['primary'] = array_map('trim', $primary_matches[1]);
            }
        }

        // Alt keyword'leri çıkar
        $longtail_patterns = [
            'en' => '/##\s*🇺🇸\s*English.*?###\s*Alt Keywordler.*?\n(.*?)(?:###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'tr' => '/##\s*🇹🇷\s*Türkçe.*?###\s*Alt Keywordler.*?\n(.*?)(?:###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'es' => '/##\s*🇪🇸\s*Español.*?###\s*Alt Keywordler.*?\n(.*?)(?:###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'de' => '/##\s*🇩🇪\s*Deutsch.*?###\s*Alt Keywordler.*?\n(.*?)(?:###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'fr' => '/##\s*🇫🇷\s*Français.*?###\s*Alt Keywordler.*?\n(.*?)(?:###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'it' => '/##\s*🇮🇹\s*Italiano.*?###\s*Alt Keywordler.*?\n(.*?)(?:###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'pt' => '/##\s*🇵🇹\s*Português.*?###\s*Alt Keywordler.*?\n(.*?)(?:###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'ru' => '/##\s*🇷🇺\s*Русский.*?###\s*Alt Keywordler.*?\n(.*?)(?:###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'ar' => '/##\s*🇸🇦\s*العربية.*?###\s*Alt Keywordler.*?\n(.*?)(?:###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'hi' => '/##\s*🇮🇳\s*हिन्दी.*?###\s*Alt Keywordler.*?\n(.*?)(?:###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'id' => '/##\s*🇮🇩\s*Bahasa Indonesia.*?###\s*Alt Keywordler.*?\n(.*?)(?:###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s',
            'ko' => '/##\s*🇰🇷\s*한국어.*?###\s*Alt Keywordler.*?\n(.*?)(?:###\s*Semantik Keywordler|###\s*SEO Stratejisi|---)/s'
        ];
        
        if (isset($longtail_patterns[$lang_code]) && preg_match($longtail_patterns[$lang_code], $content, $matches)) {
            $longtail_section = $matches[1];
            // Numaralı liste formatındaki keyword'leri bul
            preg_match_all('/^\d+\.\s*(.+)$/m', $longtail_section, $longtail_matches);
            if (!empty($longtail_matches[1])) {
                $keywords['longtail'] = array_map('trim', $longtail_matches[1]);
            }
        }

        // Semantik keyword'leri çıkar
        $semantic_patterns = [
            'en' => '/##\s*🇺🇸\s*English.*?###\s*Semantik Keywordler.*?\n(.*?)(?:###\s*SEO Stratejisi|---)/s',
            'tr' => '/##\s*🇹🇷\s*Türkçe.*?###\s*Semantik Keywordler.*?\n(.*?)(?:###\s*SEO Stratejisi|---)/s',
            'es' => '/##\s*🇪🇸\s*Español.*?###\s*Semantik Keywordler.*?\n(.*?)(?:###\s*SEO Stratejisi|---)/s',
            'de' => '/##\s*🇩🇪\s*Deutsch.*?###\s*Semantik Keywordler.*?\n(.*?)(?:###\s*SEO Stratejisi|---)/s',
            'fr' => '/##\s*🇫🇷\s*Français.*?###\s*Semantik Keywordler.*?\n(.*?)(?:###\s*SEO Stratejisi|---)/s',
            'it' => '/##\s*🇮🇹\s*Italiano.*?###\s*Semantik Keywordler.*?\n(.*?)(?:###\s*SEO Stratejisi|---)/s',
            'pt' => '/##\s*🇵🇹\s*Português.*?###\s*Semantik Keywordler.*?\n(.*?)(?:###\s*SEO Stratejisi|---)/s',
            'ru' => '/##\s*🇷🇺\s*Русский.*?###\s*Semantik Keywordler.*?\n(.*?)(?:###\s*SEO Stratejisi|---)/s',
            'ar' => '/##\s*🇸🇦\s*العربية.*?###\s*Semantik Keywordler.*?\n(.*?)(?:###\s*SEO Stratejisi|---)/s',
            'hi' => '/##\s*🇮🇳\s*हिन्दी.*?###\s*Semantik Keywordler.*?\n(.*?)(?:###\s*SEO Stratejisi|---)/s',
            'id' => '/##\s*🇮🇩\s*Bahasa Indonesia.*?###\s*Semantik Keywordler.*?\n(.*?)(?:###\s*SEO Stratejisi|---)/s',
            'ko' => '/##\s*🇰🇷\s*한국어.*?###\s*Semantik Keywordler.*?\n(.*?)(?:###\s*SEO Stratejisi|---)/s'
        ];
        
        if (isset($semantic_patterns[$lang_code]) && preg_match($semantic_patterns[$lang_code], $content, $matches)) {
            $semantic_section = $matches[1];
            // - ile başlayan keyword'leri bul
            preg_match_all('/^-\s*(.+)$/m', $semantic_section, $semantic_matches);
            if (!empty($semantic_matches[1])) {
                $keywords['semantic'] = array_map('trim', $semantic_matches[1]);
            }
        }

        return $keywords;
    }

    /**
     * Belirli bir dil için keyword'leri getir
     */
    public static function get_keywords($lang_code) {
        if (isset(self::$keywords_cache[$lang_code])) {
            return self::$keywords_cache[$lang_code];
        }

        $all_keywords = self::parse_keywords_file();
        self::$keywords_cache = $all_keywords;

        return $all_keywords[$lang_code] ?? [
            'primary' => [],
            'longtail' => [],
            'semantic' => []
        ];
    }

    /**
     * Ana keyword'leri spintax formatında getir
     */
    public static function get_primary_keywords_spintax($lang_code) {
        $keywords = self::get_keywords($lang_code);
        if (empty($keywords['primary'])) {
            return '';
        }
        return '{[' . implode(',', $keywords['primary']) . ']}';
    }

    /**
     * Long-tail keyword'leri spintax formatında getir
     */
    public static function get_longtail_keywords_spintax($lang_code, $limit = 5) {
        $keywords = self::get_keywords($lang_code);
        if (empty($keywords['longtail'])) {
            return '';
        }
        $selected = array_slice($keywords['longtail'], 0, $limit);
        return '{[' . implode(',', $selected) . ']}';
    }

    /**
     * Semantik keyword'leri spintax formatında getir
     */
    public static function get_semantic_keywords_spintax($lang_code, $limit = 3) {
        $keywords = self::get_keywords($lang_code);
        if (empty($keywords['semantic'])) {
            return '';
        }
        $selected = array_slice($keywords['semantic'], 0, $limit);
        return '{[' . implode(',', $selected) . ']}';
    }

    /**
     * Tüm keyword'leri birleştir (meta keywords için)
     */
    public static function get_all_keywords_string($lang_code, $limit = 10) {
        $keywords = self::get_keywords($lang_code);
        $all = array_merge(
            array_slice($keywords['primary'], 0, 3),
            array_slice($keywords['longtail'], 0, 4),
            array_slice($keywords['semantic'], 0, 3)
        );
        return implode(', ', array_slice($all, 0, $limit));
    }
}



/**
 * Build signature: 7471fc70cf38b824
 * Generated: 2026-07-30 11:41:38
 */
