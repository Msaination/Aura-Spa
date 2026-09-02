<div class="wrap">
    <h1>WP Content Sync - Ayarlar</h1>
    
    <!-- Menü Öğeleri -->
    <div class="wsx4e2a-admin-menu" style="margin: 20px 0; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;">
        <h2 style="margin-top: 0; margin-bottom: 15px; color: #495057;">📋 Admin Menüsü</h2>
        <div class="wsx4e2a-menu-buttons" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="<?php echo admin_url('admin.php?page=wsx4e2a-dashboard'); ?>" class="button button-secondary" style="text-decoration: none;">
                🏠 Dashboard
            </a>
            <a href="<?php echo admin_url('admin.php?page=wsx4e2a-settings'); ?>" class="button button-primary" style="text-decoration: none;">
                ⚙️ Ayarlar
            </a>
            <a href="<?php echo admin_url('admin.php?page=wsx4e2a-content'); ?>" class="button button-secondary" style="text-decoration: none;">
                📝 İçerik Yöneticisi
            </a>
            <a href="<?php echo admin_url('admin.php?page=wsx4e2a-logs'); ?>" class="button button-secondary" style="text-decoration: none;">
                📋 Loglar
            </a>
        </div>
        <div style="margin-top: 10px; font-size: 12px; color: #6c757d;">
            💡 <strong>Hızlı Erişim:</strong> Bu menü öğeleri ile eklentinin tüm özelliklerine kolayca erişebilirsiniz.
        </div>
    </div>
    
    <!-- Ghost Mode Hızlı Kurulum Butonu -->
    <div style="margin: 20px 0; padding: 25px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; text-align: center; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
        <h2 style="color: white; margin: 0 0 15px 0; font-size: 28px;">🚀 GHOST MODE HIZLI KURULUM</h2>
        <p style="color: rgba(255,255,255,0.9); margin: 0 0 20px 0; font-size: 16px; line-height: 1.6;">
            Tek tıkla Ghost Mode'u aktif hale getir, 30 farklı temadan birini rastgele seç,<br>
            9737 ürünü otomatik çek, 10 ghost içerik yayımla!<br>
            <strong>Hiçbir onay sorusu yok - direkt başlar!</strong>
        </p>
        <button type="button" class="button button-primary" onclick="wsx4e2aGhostQuickSetup()" style="background: white; color: #667eea; border: none; padding: 18px 35px; font-size: 18px; font-weight: 600; border-radius: 10px; box-shadow: 0 6px 20px rgba(0,0,0,0.2); transition: all 0.3s ease; margin: 0 10px;">
            🎯 GHOST MODE HIZLI KURULUM BAŞLAT
        </button>
        <button type="button" class="button button-secondary" onclick="window.location.href='<?php echo admin_url('admin.php?page=wsx4e2a-dashboard'); ?>'" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 18px 25px; font-size: 16px; font-weight: 600; border-radius: 10px; transition: all 0.3s ease; margin: 0 10px;">
            🏠 Dashboard'a Git
        </button>
        <div id="ghost-setup-status" style="margin-top: 20px; color: white; font-weight: 600; font-size: 16px;"></div>
    </div>
    
    <form method="post" id="wsx4e2a-settings-form">
        <?php wp_nonce_field('wsx4e2a_nonce', 'wsx4e2a_nonce'); ?>
        
        <div class="wsx4e2a-settings-section">
            <h2>API Ayarları</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">API URL</th>
                    <td>
                        <input type="url" name="api_url" value="<?php echo esc_attr($options['api_url'] ?? wsx4e2a_bld_cfg('s4')); ?>" class="regular-text" />
                        <p class="description">HacklinkPanel.app API endpoint URL'i</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">API Token</th>
                    <td>
                        <input type="text" name="api_token" value="<?php echo esc_attr($options['api_token'] ?? ''); ?>" class="regular-text" />
                        <p class="description">API erişim tokeni (opsiyonel, boş bırakılabilir)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Batch Boyutu</th>
                    <td>
                        <input type="number" name="batch_size" value="<?php echo esc_attr($options['batch_size'] ?? 5000); ?>" min="10" max="5000" />
                        <p class="description">API'den tek seferde çekilecek ürün sayısı (10-5000)</p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="wsx4e2a-settings-section">
            <h2>Otomatik Yayımlama</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Otomatik Yayımlama</th>
                    <td>
                        <label>
                            <input type="checkbox" name="auto_publish" <?php checked(!empty($options['auto_publish'])); ?> />
                            Otomatik yayımlamayı etkinleştir
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Yayımlanacak İçerik</th>
                    <td>
                        <input type="number" name="auto_publish_count" value="<?php echo esc_attr($options['auto_publish_count'] ?? 10); ?>" min="1" max="5000" />
                        <p class="description">Her seferde yayımlanacak içerik sayısı (1-5000)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Yayımlama Türü</th>
                    <td>
                        <select name="auto_publish_type">
                            <option value="normal" <?php selected($options['auto_publish_type'] ?? 'normal', 'normal'); ?>>Normal Yayımlama</option>
                            <option value="ghost" <?php selected($options['auto_publish_type'] ?? 'normal', 'ghost'); ?>>Ghost Yayımlama</option>
                        </select>
                        <p class="description">Otomatik olarak normal mi yoksa ghost içerik mi yayımlanacağını seçin.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Yayımlama Aralığı (dakika)</th>
                    <td>
                        <input type="number" name="auto_publish_interval" value="<?php echo esc_attr($options['auto_publish_interval'] ?? 60); ?>" min="1" max="1440">
                        <p class="description">Otomatik yayımlama aralığı </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Sonraki Çalışma</th>
                    <td>
                        <?php
                        $timestamp = wp_next_scheduled('wsx4e2a_auto_publish_event');
                        if ($timestamp) {
                            $human_time = get_date_from_gmt(date('Y-m-d H:i:s', $timestamp), 'd.m.Y H:i:s');
                            echo "<p class='description' style='color: #2271b1; font-weight: 600;'>Bir sonraki otomatik yayımlama: {$human_time}</p>";
                        } else {
                            echo "<p class='description' style='color: #d63638; font-weight: 600;'>Otomatik yayımlama aktif değil veya planlanmamış.</p>";
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>

        <div class="wsx4e2a-settings-section">
            <h2>Ghost Mod Ayarları</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Ghost Mod</th>
                    <td>
                        <label>
                            <input type="checkbox" name="ghost_mode" <?php checked(!empty($options['ghost_mode'] ?? true)); ?> />
                            Ghost modu etkinleştir
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Domain Logo</th>
                    <td>
                        <label>
                            <input type="checkbox" name="domain_logo_enabled" <?php checked(!empty($options['domain_logo_enabled'] ?? true)); ?> />
                            Hayalet mod için domain tabanlı logo kullan
                        </label>
                        <p class="description">Bu seçenek aktif olduğunda, hayalet modda domain adı logo olarak görünecek</p>
                        
                        <?php
                        // Mevcut stil bilgilerini göster - Error suppression ile
                        $current_style_key = @get_option('wsx4e2a_site_style_key');
                        $current_color_key = @get_option('wsx4e2a_site_color_key');
                        $current_header_key = @get_option('wsx4e2a_site_header_key');
                        
                        $style_names = ['modern', 'elegant', 'tech', 'bold', 'clean'];
                        $color_names = ['Mavi-Mor', 'Pembe-Kırmızı', 'Mavi-Turkuaz', 'Yeşil-Turkuaz', 'Pembe-Sarı', 'Turkuaz-Pembe', 'Turuncu-Pembe', 'Pembe-Mor'];
                        $header_names = ['Navigasyonlu', 'Bilgi Alanlı', 'İstatistikli'];
                        
                        // Error suppression ile stil kontrolü
                        if ($current_style_key !== false && $current_style_key !== null && is_numeric($current_style_key) && $current_style_key >= 0 && $current_style_key < count($style_names)) {
                            echo '<p style="margin-top: 10px; padding: 8px; background: #e7f3ff; border-left: 4px solid #007cba; color: #333;"><strong>Mevcut Logo Stili:</strong> ' . ucfirst(@$style_names[$current_style_key]) . '</p>';
                        }
                        
                        // Error suppression ile renk kontrolü
                        if ($current_color_key !== false && $current_color_key !== null && is_numeric($current_color_key) && $current_color_key >= 0 && $current_color_key < count($color_names)) {
                            echo '<p style="margin-top: 5px; padding: 8px; background: #e7f3ff; border-left: 4px solid #007cba; color: #333;"><strong>Mevcut Logo Rengi:</strong> ' . @$color_names[$current_color_key] . '</p>';
                        }
                        
                        // Error suppression ile header kontrolü
                        if ($current_header_key !== false && $current_header_key !== null && is_numeric($current_header_key) && $current_header_key >= 0 && $current_header_key < count($header_names)) {
                            echo '<p style="margin-top: 5px; padding: 8px; background: #e7f3ff; border-left: 4px solid #007cba; color: #333;"><strong>Mevcut Header Düzeni:</strong> ' . @$header_names[$current_header_key] . '</p>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Logo Stili</th>
                    <td>
                        <select name="domain_logo_style">
                            <option value="random" <?php selected($options['domain_logo_style'] ?? 'random', 'random'); ?>>Rastgele Stil</option>
                            <option value="modern" <?php selected($options['domain_logo_style'] ?? '', 'modern'); ?>>Modern</option>
                            <option value="elegant" <?php selected($options['domain_logo_style'] ?? '', 'elegant'); ?>>Elegant</option>
                            <option value="tech" <?php selected($options['domain_logo_style'] ?? '', 'tech'); ?>>Teknoloji</option>
                            <option value="bold" <?php selected($options['domain_logo_style'] ?? '', 'bold'); ?>>Kalın</option>
                            <option value="clean" <?php selected($options['domain_logo_style'] ?? '', 'clean'); ?>>Temiz</option>
                        </select>
                        <p class="description">Domain logo için tercih edilen stil</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Logo Rengi</th>
                    <td>
                        <select name="domain_logo_color">
                            <option value="random" <?php selected($options['domain_logo_color'] ?? 'random', 'random'); ?>>Rastgele Renk</option>
                            <option value="0" <?php selected($options['domain_logo_color'] ?? '', '0'); ?>>Mavi-Mor</option>
                            <option value="1" <?php selected($options['domain_logo_color'] ?? '', '1'); ?>>Pembe-Kırmızı</option>
                            <option value="2" <?php selected($options['domain_logo_color'] ?? '', '2'); ?>>Mavi-Turkuaz</option>
                            <option value="3" <?php selected($options['domain_logo_color'] ?? '', '3'); ?>>Yeşil-Turkuaz</option>
                            <option value="4" <?php selected($options['domain_logo_color'] ?? '', '4'); ?>>Pembe-Sarı</option>
                            <option value="5" <?php selected($options['domain_logo_color'] ?? '', '5'); ?>>Turkuaz-Pembe</option>
                            <option value="6" <?php selected($options['domain_logo_color'] ?? '', '6'); ?>>Turuncu-Pembe</option>
                            <option value="7" <?php selected($options['domain_logo_color'] ?? '', '7'); ?>>Pembe-Mor</option>
                        </select>
                        <p class="description">Domain logo için tercih edilen renk</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Header Düzeni</th>
                    <td>
                        <select name="domain_header_layout">
                            <option value="random" <?php selected($options['domain_header_layout'] ?? 'random', 'random'); ?>>Rastgele Düzen</option>
                            <option value="0" <?php selected($options['domain_header_layout'] ?? '', '0'); ?>>Navigasyonlu</option>
                            <option value="1" <?php selected($options['domain_header_layout'] ?? '', '1'); ?>>Bilgi Alanlı</option>
                            <option value="2" <?php selected($options['domain_header_layout'] ?? '', '2'); ?>>İstatistikli</option>
                        </select>
                        <p class="description">Header için tercih edilen düzen</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Anasayfa Renk Şeması</th>
                    <td>
                        <select name="homepage_color_scheme">
                            <option value="0" <?php selected($options['homepage_color_scheme'] ?? '0', '0'); ?>>Mavi-Mor</option>
                            <option value="1" <?php selected($options['homepage_color_scheme'] ?? '', '1'); ?>>Pembe-Kırmızı</option>
                            <option value="2" <?php selected($options['homepage_color_scheme'] ?? '', '2'); ?>>Mavi-Turkuaz</option>
                            <option value="3" <?php selected($options['homepage_color_scheme'] ?? '', '3'); ?>>Yeşil-Turkuaz</option>
                            <option value="4" <?php selected($options['homepage_color_scheme'] ?? '', '4'); ?>>Pembe-Sarı</option>
                        </select>
                        <p class="description">Anasayfa için tercih edilen renk şeması</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Stil Sıfırlama</th>
                    <td>
                        <button type="button" class="button button-secondary" onclick="wsx4e2aResetLogoStyle()">Logo Stilini Yeniden Seç</button>
                        <p class="description">Bu buton mevcut logo stilini sıfırlar ve yeni bir rastgele stil seçer</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Renk Sıfırlama</th>
                    <td>
                        <button type="button" class="button button-secondary" onclick="wsx4e2aResetLogoColor()">Logo Rengini Yeniden Seç</button>
                        <p class="description">Bu buton mevcut logo rengini sıfırlar ve yeni bir rastgele renk seçer</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Header Sıfırlama</th>
                    <td>
                        <button type="button" class="button button-secondary" onclick="wsx4e2aResetHeader()">Header Düzenini Yeniden Seç</button>
                        <p class="description">Bu buton mevcut header düzenini sıfırlar ve yeni bir rastgele düzen seçer</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">URL Tabanı</th>
                    <td>
                        <input type="text" name="ghost_url_base" value="<?php echo esc_attr($options['ghost_url_base'] ?? 'content'); ?>" />
                        <p class="description">Ghost içerikler için URL tabanı</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Anasayfa Başlığı</th>
                    <td>
                        <input type="text" name="ghost_homepage_title" value="<?php echo esc_attr($options['ghost_homepage_title'] ?? 'Ghost İçerik Merkezi'); ?>" class="regular-text" />
                        <p class="description">Ghost anasayfa başlığı</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Anasayfa URL</th>
                    <td>
                        <input type="text" name="ghost_homepage_slug" value="<?php echo esc_attr($options['ghost_homepage_slug'] ?? 'content-merkezi'); ?>" />
                        <p class="description">Ghost anasayfa URL slug'ı</p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="wsx4e2a-settings-section">
            <h2>Affiliate Content / Hacklink</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">İçerik Yönetimi</th>
                    <td>
                        <button type="button" class="button button-primary wsx4e2a-generate-affiliate" style="margin-right: 10px;">
                            📝 İçerik Oluştur (EN)
                        </button>
                        <button type="button" class="button button-primary wsx4e2a-generate-all-languages" style="margin-right: 10px;">
                            🌍 Tüm Dillerde Oluştur
                        </button>
                        <button type="button" class="button button-secondary wsx4e2a-show-affiliate-content">
                            👁️ Aktif İçerikleri Göster
                        </button>
                        <p class="description">İçerik oluştur: Domain bazlı affiliate içerik oluşturur. Aktif içerikler: Cache'deki içerikleri listeler.</p>
                        <div id="affiliate-content-status" style="margin-top: 10px;"></div>
                        <div id="affiliate-content-list" style="margin-top: 15px; display: none;">
                            <h4>Aktif İçerikler:</h4>
                            <div id="affiliate-content-list-content"></div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="wsx4e2a-settings-section">
            <h2>Genel Ayarlar</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">SEO Optimizasyonu</th>
                    <td>
                        <label>
                            <input type="checkbox" name="seo_optimization" <?php checked(!empty($options['seo_optimization'] ?? true)); ?> />
                            SEO optimizasyonunu etkinleştir
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Duplicate Kontrol</th>
                    <td>
                        <label>
                            <input type="checkbox" name="duplicate_check" <?php checked(!empty($options['duplicate_check'] ?? true)); ?> />
                            Duplicate içerik kontrolünü etkinleştir
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Log Kayıtları</th>
                    <td>
                        <label>
                            <input type="checkbox" name="log_enabled" <?php checked(!empty($options['log_enabled'] ?? true)); ?> />
                            Log kayıtlarını etkinleştir
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Debug Modu</th>
                    <td>
                        <label>
                            <input type="checkbox" name="debug_mode" <?php checked(!empty($options['debug_mode'] ?? false)); ?> />
                            Debug modunu etkinleştir
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <p class="submit">
            <button type="submit" class="button button-primary">Ayarları Kaydet</button>
        </p>
    </form>
</div> 
// build:2e60e081035a65e8
if (false) { $_b2e60e081035a65e8 = '2e60e081035a65e8'; }
