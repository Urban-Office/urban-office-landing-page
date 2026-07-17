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
        $name = trim($_POST['name']);
        $slug = trim($_POST['slug']);

        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        }

        if (empty($name)) {
            $error = 'Nama kategori wajib diisi.';
        } else {
            try {
                // Check duplicate
                $check = Database::fetch("SELECT id FROM categories WHERE slug = ?", [$slug]);
                if ($check) {
                    $error = 'Kategori dengan slug tersebut sudah ada.';
                } else {
                    Database::insert("INSERT INTO categories (name, slug) VALUES (?, ?)", [$name, $slug]);
                    log_activity($_SESSION['admin_user_id'], 'create_category', "Created category: " . sanitize($name));
                    clear_page_cache();
                    $success = 'Kategori berhasil ditambahkan!';
                }
            } catch (Exception $e) {
                $error = 'Gagal menyimpan kategori: ' . $e->getMessage();
            }
        }
    }
}

// Handle Category Deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $cat = Database::fetch("SELECT name FROM categories WHERE id = ?", [$id]);
        if ($cat) {
            Database::query("DELETE FROM categories WHERE id = ?", [$id]);
            log_activity($_SESSION['admin_user_id'], 'delete_category', "Deleted category ID: $id (" . sanitize($cat['name']) . ")");
            clear_page_cache();
            $success = 'Kategori berhasil dihapus!';
        }
    } catch (Exception $e) {
        $error = 'Gagal menghapus kategori: ' . $e->getMessage();
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
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
                <div style="background-color: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 6px; margin-bottom: 24px; font-weight: 500;">
                    ✗ <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div style="background-color: #dcfce7; color: #15803d; padding: 12px; border-radius: 6px; margin-bottom: 24px; font-weight: 500;">
                    ✓ <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                
                <!-- Left Column: Add Category form -->
                <div class="admin-card">
                    <h3>Tambah Kategori</h3>
                    <form action="" method="POST" style="margin-top: 20px;">
                        <?php echo csrf_field(); ?>
                        
                        <div style="margin-bottom: 16px;">
                            <label class="form-label">Nama Kategori *</label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Bisnis WFA" required style="background-color: white; border:1px solid #cbd5e1; width:100%;">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label class="form-label">Slug (Optional)</label>
                            <input type="text" name="slug" class="form-control" placeholder="contoh-bisnis-wfa" style="background-color: white; border:1px solid #cbd5e1; width:100%;">
                        </div>

                        <button type="submit" name="create" class="btn-admin btn-admin-primary" style="width: 100%; justify-content: center; padding: 10px;">
                            Tambah Kategori
                        </button>
                    </form>
                </div>

                <!-- Right Column: Categories List table -->
                <div class="admin-card">
                    <h3>Daftar Kategori</h3>
                    <div class="table-responsive" style="margin-top: 20px;">
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
                                        <td><code>/category/<?php echo sanitize($cat['slug']); ?>/</code></td>
                                        <td><span class="badge badge-info"><?php echo $cat['post_count']; ?></span></td>
                                        <td>
                                            <a href="?delete=<?php echo $cat['id']; ?>" class="btn-admin btn-admin-danger btn-admin-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Seluruh relasi artikel akan terputus.')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: #94a3b8;">Belum ada kategori.</td>
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
