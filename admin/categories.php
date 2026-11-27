<?php
/**
 * Admin Categories CRUD
 */

require_once __DIR__ . '/auth.php';

$error = '';
$success = '';

// Handle Category Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $name = trim((string)($_POST['name'] ?? ''));
        $raw_slug = trim((string)($_POST['slug'] ?? ''));
        $slug = admin_slugify($raw_slug !== '' ? $raw_slug : $name);

        if ($name === '') {
            $error = 'Nama kategori wajib diisi.';
        } elseif ($slug === '') {
            $error = 'Slug kategori tidak valid.';
        } else {
            try {
                // Check duplicate slug or name
                $check = Database::fetch("SELECT id FROM categories WHERE slug = ? OR name = ?", [$slug, $name]);
                if ($check) {
                    $error = 'Kategori dengan nama atau slug tersebut sudah ada.';
                } else {
                    Database::insert("INSERT INTO categories (name, slug) VALUES (?, ?)", [$name, $slug]);
                    log_activity($_SESSION['admin_user_id'], 'create_category', "Created category: $name");
                    clear_page_cache();
                    $success = 'Kategori berhasil ditambahkan!';
                }
            } catch (Exception $e) {
                $error = admin_friendly_error('Gagal menyimpan kategori.', $e);
            }
        }
    }
}

// Handle Category Deletion (CSRF-protected POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $id = intval($_POST['delete_id'] ?? 0);
        try {
            $cat = Database::fetch("SELECT name FROM categories WHERE id = ?", [$id]);
            if ($cat) {
                Database::query("DELETE FROM categories WHERE id = ?", [$id]);
                log_activity($_SESSION['admin_user_id'], 'delete_category', "Deleted category ID: $id (" . $cat['name'] . ")");
                clear_page_cache();
                $success = 'Kategori berhasil dihapus!';
            }
        } catch (Exception $e) {
            $error = admin_friendly_error('Gagal menghapus kategori.', $e);
        }
    }
}

// Fetch Categories list
try {
    $categories = Database::fetchAll("SELECT c.*, COUNT(pc.post_id) as post_count FROM categories c 
                                      LEFT JOIN post_categories pc ON c.id = pc.category_id 
                                      GROUP BY c.id ORDER BY c.name ASC");
} catch (Exception $e) {
    error_log("Categories fetch failed: " . $e->getMessage());
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Urban Office</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css?v=<?php echo filemtime(dirname(__DIR__) . '/assets/css/admin.css'); ?>">
</head>
<body class="admin-body">
    <div class="admin-layout">
        
        <!-- Sidebar Navigation Menu -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Workspace Area -->
        <main class="admin-main">
            <div class="admin-top">
                <h1 class="admin-title">Kelola Kategori</h1>
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
                
                <!-- Left Column: Add Category form -->
                <div class="admin-card">
                    <h3>Tambah Kategori</h3>
                    <form action="" method="POST" style="margin-top: 16px;">
                        <?php echo csrf_field(); ?>
                        
                        <div style="margin-bottom: 16px;">
                            <label class="form-label" for="cat-name">Nama Kategori *</label>
                            <input type="text" id="cat-name" name="name" class="form-control" placeholder="Contoh: Bisnis WFA" required>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label class="form-label" for="cat-slug">Slug (Opsional)</label>
                            <input type="text" id="cat-slug" name="slug" class="form-control" placeholder="contoh-bisnis-wfa">
                        </div>

                        <button type="submit" name="create" class="btn-admin btn-admin-primary" style="width: 100%; padding: 10px;">
                            Tambah Kategori
                        </button>
                    </form>
                </div>

                <!-- Right Column: Categories List table -->
                <div class="admin-card">
                    <h3>Daftar Kategori</h3>
                    <div class="table-responsive" style="margin-top: 16px;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Slug</th>
                                    <th>Artikel Terkait</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><strong><?php echo sanitize($cat['name']); ?></strong></td>
                                        <td><span class="admin-code-pill">/category/<?php echo sanitize($cat['slug']); ?>/</span></td>
                                        <td><span class="badge badge-info admin-num"><?php echo $cat['post_count']; ?></span></td>
                                        <td>
                                            <form method="POST" action="<?php echo BASE_URL; ?>admin/categories.php" style="margin: 0; display: inline-flex;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Seluruh relasi artikel akan terputus.')">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="delete_id" value="<?php echo (int)$cat['id']; ?>">
                                                <button type="submit" name="delete_category" value="1" class="btn-admin btn-admin-danger btn-admin-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="4" class="admin-empty">Belum ada kategori.</td>
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
