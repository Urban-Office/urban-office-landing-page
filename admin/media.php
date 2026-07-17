<?php
/**
 * Admin Media Library Manager
 */

require_once __DIR__ . '/auth.php';

$error = '';
$success = '';

// Handle Image Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $file = $_FILES['media_file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Gagal mengupload file (Error Code: ' . $file['error'] . ')';
        } else {
            // Verify Mime Type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowed_mimes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif'
            ];

            if (!array_key_exists($mime, $allowed_mimes)) {
                $error = 'Format file tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF.';
            } else {
                // Limit size (5MB)
                $max_size = 5 * 1024 * 1024;
                if ($file['size'] > $max_size) {
                    $error = 'Ukuran file maksimal 5MB.';
                } else {
                    $ext = $allowed_mimes[$mime];
                    $crypt_name = bin2hex(random_bytes(16)) . '.' . $ext;
                    
                    if (!is_dir(DIR_UPLOADS)) {
                        mkdir(DIR_UPLOADS, 0755, true);
                    }

                    $dest_path = DIR_UPLOADS . $crypt_name;
                    $rel_path = 'uploads/' . $crypt_name;

                    if (move_uploaded_file($file['tmp_name'], $dest_path)) {
                        try {
                            Database::insert(
                                "INSERT INTO media (file_name, file_path, file_type, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?)",
                                [$file['name'], $rel_path, $mime, $file['size'], $_SESSION['admin_user_id']]
                            );
                            log_activity($_SESSION['admin_user_id'], 'upload_media', "Uploaded file: " . sanitize($file['name']) . " -> $rel_path");
                            $success = 'Media berhasil diupload!';
                        } catch (Exception $e) {
                            // Clean up file if DB insert failed
                            if (file_exists($dest_path)) unlink($dest_path);
                            $error = 'Gagal mendaftarkan file di database: ' . $e->getMessage();
                        }
                    } else {
                        $error = 'Gagal memindahkan file ke direktori tujuan.';
                    }
                }
            }
        }
    }
}

// Handle Media Deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $item = Database::fetch("SELECT file_path, file_name FROM media WHERE id = ?", [$id]);
        if ($item) {
            $full_path = DIR_ROOT . $item['file_path'];
            if (file_exists($full_path) && is_file($full_path)) {
                unlink($full_path);
            }
            Database::query("DELETE FROM media WHERE id = ?", [$id]);
            log_activity($_SESSION['admin_user_id'], 'delete_media', "Deleted file: " . sanitize($item['file_name']));
            $success = 'Media berhasil dihapus!';
        }
    } catch (Exception $e) {
        $error = 'Gagal menghapus file: ' . $e->getMessage();
    }
}

// Fetch Media Items list with pagination
$limit = 12;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

try {
    $total_items = Database::fetch("SELECT COUNT(*) as total FROM media")['total'];
    $total_pages = ceil($total_items / $limit);
    
    $media_list = Database::fetchAll(
        "SELECT m.*, u.username FROM media m 
         JOIN users u ON m.uploaded_by = u.id 
         ORDER BY m.created_at DESC LIMIT ? OFFSET ?",
        [$limit, $offset]
    );
} catch (Exception $e) {
    error_log("Failed pulling media library: " . $e->getMessage());
    $media_list = [];
    $total_pages = 1;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Library - Urban Office</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
    <script>
        // Copy path link helper
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Path/Link berhasil disalin ke papan klip: ' + text);
            }).catch(err => {
                console.error('Failed to copy text: ', err);
            });
        }
    </script>
</head>
<body class="admin-body">
    <div class="admin-layout">
        
        <!-- Sidebar Navigation Menu -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Workspace Area -->
        <main class="admin-main">
            <div class="admin-top">
                <h1 class="admin-title">Media Library</h1>
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

            <!-- Upload Box Card -->
            <div class="admin-card">
                <h3>Upload File Baru</h3>
                <form action="" method="POST" enctype="multipart/form-data" style="margin-top: 16px; display: flex; gap: 16px; align-items: center;">
                    <?php echo csrf_field(); ?>
                    <input type="file" name="media_file" class="form-control" style="background-color: white; border:1px solid #cbd5e1; max-width: 400px; padding: 8px 12px;" required>
                    <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 20px;">Upload Image</button>
                </form>
            </div>

            <!-- Media Grid Display -->
            <div class="admin-card">
                <h3>Dokumen Media</h3>
                
                <?php if (!empty($media_list)): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 24px; margin-top: 24px;">
                        <?php foreach ($media_list as $media): 
                            $rel_url = $media['file_path'];
                            $full_url = BASE_URL . $rel_url;
                        ?>
                            <div style="border: 1px solid var(--admin-border); border-radius: 8px; overflow: hidden; background-color: #f8fafc; display: flex; flex-direction: column; justify-content: space-between;">
                                <div style="height: 140px; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                    <img src="<?php echo $full_url; ?>" alt="<?php echo sanitize($media['file_name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div style="padding: 12px; font-size: 0.8rem;">
                                    <div style="font-weight: 600; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; margin-bottom: 4px;" title="<?php echo sanitize($media['file_name']); ?>">
                                        <?php echo sanitize($media['file_name']); ?>
                                    </div>
                                    <div style="color: #64748b; margin-bottom: 8px;">
                                        Size: <?php echo round($media['file_size'] / 1024, 1); ?> KB
                                    </div>
                                    <div style="display: flex; gap: 6px;">
                                        <button type="button" class="btn-admin btn-admin-secondary btn-admin-sm" style="flex-grow:1; justify-content:center; padding: 4px;" onclick="copyToClipboard('<?php echo sanitize($rel_url); ?>')">Copy Path</button>
                                        <a href="?delete=<?php echo $media['id']; ?>" class="btn-admin btn-admin-danger btn-admin-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus file ini dari server?')" style="padding: 4px 8px;">Del</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div style="display: flex; gap: 6px; justify-content: center; margin-top: 30px;">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>" class="btn-admin <?php echo $i === $page ? 'btn-admin-primary' : 'btn-admin-secondary'; ?> btn-admin-sm"><?php echo $i; ?></a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <p style="text-align: center; color: #94a3b8; margin-top: 30px;">Belum ada media diupload.</p>
                <?php endif; ?>
            </div>

        </main>
    </div>
</body>
</html>
