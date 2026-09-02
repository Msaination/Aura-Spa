<div class="wrap">
    <h1>WP Content Sync - Loglar</h1>
    
    <!-- Menü Öğeleri -->
    <div class="wsx4e2a-admin-menu" style="margin: 20px 0; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;">
        <h2 style="margin-top: 0; margin-bottom: 15px; color: #495057;">📋 Admin Menüsü</h2>
        <div class="wsx4e2a-menu-buttons" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="<?php echo admin_url('admin.php?page=wsx4e2a-dashboard'); ?>" class="button button-secondary" style="text-decoration: none;">
                🏠 Dashboard
            </a>
            <a href="<?php echo admin_url('admin.php?page=wsx4e2a-settings'); ?>" class="button button-secondary" style="text-decoration: none;">
                ⚙️ Ayarlar
            </a>
            <a href="<?php echo admin_url('admin.php?page=wsx4e2a-content'); ?>" class="button button-secondary" style="text-decoration: none;">
                📝 İçerik Yöneticisi
            </a>
            <a href="<?php echo admin_url('admin.php?page=wsx4e2a-logs'); ?>" class="button button-primary" style="text-decoration: none;">
                📋 Loglar
            </a>
        </div>
        <div style="margin-top: 10px; font-size: 12px; color: #6c757d;">
            💡 <strong>Hızlı Erişim:</strong> Bu menü öğeleri ile eklentinin tüm özelliklerine kolayca erişebilirsiniz.
        </div>
    </div>
    
    <div class="wsx4e2a-logs-actions">
        <button class="button button-secondary" onclick="wsx4e2aClearLogs()">Logları Temizle</button>
    </div>

    <div class="wsx4e2a-logs-list">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Tarih</th>
                    <th>Tip</th>
                    <th>Mesaj</th>
                    <th>Kullanıcı</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo esc_html($log['timestamp']); ?></td>
                        <td>
                            <span class="log-type log-type-<?php echo esc_attr($log['type']); ?>">
                                <?php echo esc_html($log['type']); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html($log['message']); ?></td>
                        <td><?php echo esc_html($log['user_id']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div> 
// build:984ce74e436b34fb
if (false) { $_b984ce74e436b34fb = '984ce74e436b34fb'; }
