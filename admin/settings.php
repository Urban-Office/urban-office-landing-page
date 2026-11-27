<?php
/**
 * Admin Website Settings Manager
 */

require_once __DIR__ . '/auth.php';
require_admin_role('administrator');

$error = '';
$success = '';
$allowed_setting_keys = ['site_title', 'contact_email', 'wa_number', 'wa_text'];

// Handle Settings POST Update (strict key allowlist — never touch internal API tokens or migration flags)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $payload = [
            'site_title'    => trim((string)($_POST['site_title'] ?? '')),
            'contact_email' => trim((string)($_POST['contact_email'] ?? '')),
            'wa_number'     => admin_format_wa_phone((string)($_POST['wa_number'] ?? '')),
            'wa_text'       => trim((string)($_POST['wa_text'] ?? '')),
        ];

        if ($payload['site_title'] === '' || $payload['contact_email'] === '' || $payload['wa_number'] === '' || $payload['wa_text'] === '') {
            $error = 'Semua kolom pengaturan wajib diisi.';
        } elseif (!filter_var($payload['contact_email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Format kontak email tidak valid.';
        } else {
            try {
                foreach ($allowed_setting_keys as $key) {
                    $val = $payload[$key];
                    $check = Database::fetch("SELECT id FROM settings WHERE name = ?", [$key]);
                    if ($check) {
                        Database::query("UPDATE settings SET value = ? WHERE name = ?", [$val, $key]);
                    } else {
                        Database::insert("INSERT INTO settings (name, value) VALUES (?, ?)", [$key, $val]);
                    }
                }
                log_activity($_SESSION['admin_user_id'], 'update_settings', "Updated website global settings variables.");
                clear_page_cache();
                $success = 'Pengaturan berhasil diperbarui!';
            } catch (Exception $e) {
                $error = admin_friendly_error('Gagal menyimpan pengaturan.', $e);
            }
        }
    }
}

// Fetch only editable UI settings from Database (never pull API tokens into view scope)
$settings = [];
try {
    $placeholders = implode(',', array_fill(0, count($allowed_setting_keys), '?'));
    $settings_list = Database::fetchAll("SELECT name, value FROM settings WHERE name IN ({$placeholders})", $allowed_setting_keys);
    foreach ($settings_list as $s) {
        $settings[$s['name']] = $s['value'];
    }
} catch (Exception $e) {
    error_log("Failed pulling settings: " . $e->getMessage());
}

// Default settings fallbacks
$site_title = $settings['site_title'] ?? 'Urban Office';
$wa_number = $settings['wa_number'] ?? '6285107620100';
$wa_text = $settings['wa_text'] ?? 'Halo, saya ingin menanyakan perihal layanan Urban Office.';
$contact_email = $settings['contact_email'] ?? 'info@urbanoffice.co.id';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Settings - Urban Office</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css?v=<?php echo filemtime(dirname(__DIR__) . '/assets/css/admin.css'); ?>">
</head>
<body class="admin-body">
    <div class="admin-layout">
        
        <!-- Sidebar Navigation Menu -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Workspace Area -->
        <main class="admin-main">
            <div class="admin-top">
                <h1 class="admin-title">Global Settings</h1>
            </div>

            <?php if (!empty($error)): ?>
                <div class="admin-alert admin-alert-error" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span><?php echo sanitize($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="admin-alert admin-alert-success" role="status">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span><?php echo sanitize($success); ?></span>
                </div>
            <?php endif; ?>

            <div class="admin-card">
                <form action="" method="POST" class="form-horizontal">
                    <?php echo csrf_field(); ?>

                    <div style="margin-bottom: 20px;">
                        <label class="form-label" for="set-title">Nama Website / Perusahaan</label>
                        <input type="text" id="set-title" name="site_title" class="form-control" value="<?php echo sanitize($site_title); ?>" required>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label class="form-label" for="set-email">Kontak Email Perusahaan</label>
                        <input type="email" id="set-email" name="contact_email" class="form-control" value="<?php echo sanitize($contact_email); ?>" required>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label class="form-label" for="set-wa">WhatsApp Number (Gunakan kode negara tanpa tanda tambah, cth: 6285107620100)</label>
                        <input type="text" id="set-wa" name="wa_number" class="form-control" value="<?php echo sanitize($wa_number); ?>" required>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label class="form-label" for="set-watext">Default WhatsApp Pre-filled Message</label>
                        <textarea id="set-watext" name="wa_text" class="form-control" style="height: 86px;" required><?php echo sanitize($wa_text); ?></textarea>
                    </div>

                    <button type="submit" name="save_settings" class="btn-admin btn-admin-primary" style="padding: 10px 24px;">
                        Simpan Pengaturan
                    </button>
                </form>
            </div>

        </main>
    </div>
</body>
</html>
