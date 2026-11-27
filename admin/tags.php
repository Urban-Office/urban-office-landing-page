<?php
/**
 * Admin Tags CRUD
 */

require_once __DIR__ . '/auth.php';

$error = '';
$success = '';

// Handle Tag Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $name = trim((string)($_POST['name'] ?? ''));
        $raw_slug = trim((string)($_POST['slug'] ?? ''));
        $slug = admin_slugify($raw_slug !== '' ? $raw_slug : $name);

        if ($name === '') {
            $error = 'Nama tag wajib diisi.';
        } elseif ($slug === '') {
            $error = 'Slug tag tidak valid.';
        } else {
            try {
                // Check duplicate slug or name
                $check = Database::fetch("SELECT id FROM tags WHERE slug = ? OR name = ?", [$slug, $name]);
                if ($check) {
                    $error = 'Tag dengan nama atau slug tersebut sudah ada.';
                } else {
                    Database::insert("INSERT INTO tags (name, slug) VALUES (?, ?)", [$name, $slug]);
                    log_activity($_SESSION['admin_user_id'], 'create_tag', "Created tag: #$name");
                    clear_page_cache();
                    $success = 'Tag berhasil ditambahkan!';
                }
            } catch (Exception $e) {
                $error = admin_friendly_error('Gagal menyimpan tag.', $e);
            }
        }
    }
}

// Handle Tag Deletion (CSRF-protected POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_tag'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $id = intval($_POST['delete_id'] ?? 0);
        try {
            $tag = Database::fetch("SELECT name FROM tags WHERE id = ?", [$id]);
            if ($tag) {
                Database::query("DELETE FROM tags WHERE id = ?", [$id]);
                log_activity($_SESSION['admin_user_id'], 'delete_tag', "Deleted tag ID: $id (#" . $tag['name'] . ")");
                clear_page_cache();
                $success = 'Tag berhasil dihapus!';
            }
        } catch (Exception $e) {
            $error = admin_friendly_error('Gagal menghapus tag.', $e);
        }
    }
}

// Fetch Tags list
try {
    $tags = Database::fetchAll("SELECT t.*, COUNT(pt.post_id) as post_count FROM tags t 
                                LEFT JOIN post_tags pt ON t.id = pt.tag_id 
                                GROUP BY t.id ORDER BY t.name ASC");
} catch (Exception $e) {
    error_log("Tags fetch failed: " . $e->getMessage());
    $tags = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tags - Urban Office</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css?v=<?php echo filemtime(dirname(__DIR__) . '/assets/css/admin.css'); ?>">
</head>
<body class="admin-body">
    <div class="admin-layout">
        
        <!-- Sidebar Navigation Menu -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Workspace Area -->
        <main class="admin-main">
            <div class="admin-top">
                <h1 class="admin-title">Kelola Tags</h1>
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
                
                <!-- Left Column: Add Tag form -->
                <div class="admin-card">
                    <h3>Tambah Tag</h3>
                    <form action="" method="POST" style="margin-top: 16px;">
                        <?php echo csrf_field(); ?>
                        
                        <div style="margin-bottom: 16px;">
                            <label class="form-label" for="tag-name">Nama Tag *</label>
                            <input type="text" id="tag-name" name="name" class="form-control" placeholder="Contoh: virtual-office" required>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label class="form-label" for="tag-slug">Slug (Opsional)</label>
                            <input type="text" id="tag-slug" name="slug" class="form-control" placeholder="virtual-office">
                        </div>

                        <button type="submit" name="create" class="btn-admin btn-admin-primary" style="width: 100%; padding: 10px;">
                            Tambah Tag
                        </button>
                    </form>
                </div>

                <!-- Right Column: Tags List table -->
                <div class="admin-card">
                    <h3>Daftar Tags</h3>
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
                                <?php if (!empty($tags)): foreach ($tags as $t): ?>
                                    <tr>
                                        <td><strong>#<?php echo sanitize($t['name']); ?></strong></td>
                                        <td><span class="admin-code-pill">/tag/<?php echo sanitize($t['slug']); ?>/</span></td>
                                        <td><span class="badge badge-info admin-num"><?php echo $t['post_count']; ?></span></td>
                                        <td>
                                            <form method="POST" action="<?php echo BASE_URL; ?>admin/tags.php" style="margin: 0; display: inline-flex;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tag ini? Seluruh relasi artikel akan terputus.')">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="delete_id" value="<?php echo (int)$t['id']; ?>">
                                                <button type="submit" name="delete_tag" value="1" class="btn-admin btn-admin-danger btn-admin-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="4" class="admin-empty">Belum ada tag.</td>
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
