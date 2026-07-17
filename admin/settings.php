<?php
/**
 * Admin Website Settings Manager
 */

require_once __DIR__ . '/auth.php';

$error = '';
$success = '';

// Handle Settings POST Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        unset($_POST['csrf_token']);
        unset($_POST['save_settings']);

        try {
            foreach ($_POST as $key => $val) {
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
            $error = 'Gagal menyimpan pengaturan: ' . $e->getMessage();
        }
    }
}

// Fetch all Settings from Database
$settings = [];
try {
    $settings_list = Database::fetchAll("SELECT * FROM settings");
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
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
                <div style="background-color: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 6px; margin-bottom: 24px; font-weight: 500;">
                    ✗ <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div style="background-color: #dcfce7; color: #15803d; padding: 12px; border-radius: 6px; margin-bottom: 24px; font-weight: 500;">
                    ✓ <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="admin-card">
                <form action="" method="POST" class="form-horizontal">
                    <?php echo csrf_field(); ?>

                    <div style="margin-bottom: 20px;">
                        <label class="form-label">Nama Website / Perusahaan</label>
                        <input type="text" name="site_title" class="form-control" value="<?php echo sanitize($site_title); ?>" style="background-color: white; border:1px solid #cbd5e1; width:100%;" required>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label class="form-label">Kontak Email Perusahaan</label>
                        <input type="email" name="contact_email" class="form-control" value="<?php echo sanitize($contact_email); ?>" style="background-color: white; border:1px solid #cbd5e1; width:100%;" required>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label class="form-label">WhatsApp Number (Gunakan kode negara tanpa tanda tambah, cth: 6285107620100)</label>
                        <input type="text" name="wa_number" class="form-control" value="<?php echo sanitize($wa_number); ?>" style="background-color: white; border:1px solid #cbd5e1; width:100%;" required>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label class="form-label">Default WhatsApp Pre-filled Message</label>
                        <textarea name="wa_text" class="form-control" style="background-color: white; border:1px solid #cbd5e1; width:100%; height:80px;" required><?php echo sanitize($wa_text); ?></textarea>
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
