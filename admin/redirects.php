<?php
/**
 * Admin Redirect Map CRUD
 */

require_once __DIR__ . '/auth.php';

$error = '';
$success = '';

// Handle Redirect Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $old_url = '/' . ltrim(trim($_POST['old_url']), '/'); // Enforce leading slash
        $new_url = trim($_POST['new_url']);
        $type = intval($_POST['type']);

        if (empty($old_url) || empty($new_url)) {
            $error = 'Kedua kolom URL wajib diisi.';
        } else {
            try {
                // Check duplicate old URL
                $check = Database::fetch("SELECT id FROM redirects WHERE old_url = ?", [$old_url]);
                if ($check) {
                    $error = 'Pengalihan untuk URL asal tersebut sudah terdaftar.';
                } else {
                    Database::insert("INSERT INTO redirects (old_url, new_url, type) VALUES (?, ?, ?)", [$old_url, $new_url, $type]);
                    log_activity($_SESSION['admin_user_id'], 'create_redirect', "Created redirect: $old_url -> $new_url ($type)");
                    clear_page_cache();
                    $success = 'Pengalihan URL berhasil ditambahkan!';
                }
            } catch (Exception $e) {
                $error = 'Gagal menyimpan pengalihan: ' . $e->getMessage();
            }
        }
    }
}

// Handle Redirect Deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $red = Database::fetch("SELECT old_url FROM redirects WHERE id = ?", [$id]);
        if ($red) {
            Database::query("DELETE FROM redirects WHERE id = ?", [$id]);
            log_activity($_SESSION['admin_user_id'], 'delete_redirect', "Deleted redirect ID: $id (" . sanitize($red['old_url']) . ")");
            clear_page_cache();
            $success = 'Pengalihan URL berhasil dihapus!';
        }
    } catch (Exception $e) {
        $error = 'Gagal menghapus pengalihan: ' . $e->getMessage();
    }
}

// Fetch all Redirects
try {
    $redirects_list = Database::fetchAll("SELECT * FROM redirects ORDER BY old_url ASC");
} catch (Exception $e) {
    error_log("Failed fetching redirects: " . $e->getMessage());
    $redirects_list = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirect Map - Urban Office</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css?v=<?php echo filemtime(dirname(__DIR__) . '/assets/css/admin.css'); ?>">
</head>
<body class="admin-body">
    <div class="admin-layout">
        
        <!-- Sidebar Navigation Menu -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Workspace Area -->
        <main class="admin-main">
            <div class="admin-top">
                <h1 class="admin-title">Redirect Map (URL Preservation)</h1>
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

            <div class="admin-split-grid">
                
                <!-- Left Column: Add Redirect form -->
                <div class="admin-card">
                    <h3>Tambah Pengalihan</h3>
                    <form action="" method="POST" style="margin-top: 16px;">
                        <?php echo csrf_field(); ?>
                        
                        <div style="margin-bottom: 16px;">
                            <label class="form-label" for="red-old">URL Asal (Old Path) *</label>
                            <input type="text" id="red-old" name="old_url" class="form-control" placeholder="Contoh: /virtual-office/" required>
                            <p class="form-help">Gunakan absolute path diawali tanda garis miring (/).</p>
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label class="form-label" for="red-new">URL Tujuan (New Path/Link) *</label>
                            <input type="text" id="red-new" name="new_url" class="form-control" placeholder="Contoh: /virtual-office-surabaya/" required>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label class="form-label" for="red-type">Tipe Pengalihan</label>
                            <select id="red-type" name="type" class="form-control">
                                <option value="301">301 Permanent Redirect (Terbaik untuk SEO)</option>
                                <option value="302">302 Temporary Redirect</option>
                            </select>
                        </div>

                        <button type="submit" name="create" class="btn-admin btn-admin-primary" style="width: 100%; padding: 10px;">
                            Tambah Redirect
                        </button>
                    </form>
                </div>

                <!-- Right Column: Redirects List table -->
                <div class="admin-card">
                    <h3>Daftar Pengalihan</h3>
                    <div class="table-responsive" style="margin-top: 16px;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>URL Asal</th>
                                    <th>URL Tujuan</th>
                                    <th>Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($redirects_list)): foreach ($redirects_list as $red): ?>
                                    <tr>
                                        <td><span class="admin-code-pill"><?php echo sanitize($red['old_url']); ?></span></td>
                                        <td><span class="admin-code-pill"><?php echo sanitize($red['new_url']); ?></span></td>
                                        <td><span class="badge badge-info admin-num"><?php echo $red['type']; ?></span></td>
                                        <td>
                                            <a href="?delete=<?php echo $red['id']; ?>" class="btn-admin btn-admin-danger btn-admin-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus redirect map ini?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="4" class="admin-empty">Belum ada pengalihan terdaftar.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </main>
    </div>
</body>
</html>
