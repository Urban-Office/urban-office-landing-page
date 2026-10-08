<?php
/**
 * Admin Landing Pages SEO Metadata Editor
 */

require_once __DIR__ . '/auth.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = '';
$success = '';

// Process Update Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $id = intval($_POST['id']);
        $meta_title = trim($_POST['meta_title']);
        $meta_description = trim($_POST['meta_description']);
        $canonical_url = trim($_POST['canonical_url']);
        $og_title = trim($_POST['og_title']);
        $og_description = trim($_POST['og_description']);
        $og_image = trim($_POST['og_image']);
        
        // Parse and validate FAQ JSON array
        $faq_questions = isset($_POST['faq_q']) ? $_POST['faq_q'] : [];
        $faq_answers = isset($_POST['faq_a']) ? $_POST['faq_a'] : [];
        $schema_faq_array = [];
        
        for ($i = 0; $i < count($faq_questions); $i++) {
            $q = trim($faq_questions[$i]);
            $a = trim($faq_answers[$i]);
            if (!empty($q) && !empty($a)) {
                $schema_faq_array[] = [
                    'question' => $q,
                    'answer' => $a
                ];
            }
        }
        $schema_faq = !empty($schema_faq_array) ? json_encode($schema_faq_array) : null;

        try {
            // Update page details
            Database::query(
                "UPDATE pages SET meta_title = ?, meta_description = ?, canonical_url = ?, og_title = ?, og_description = ?, og_image = ?, schema_faq = ? 
                 WHERE id = ?",
                [$meta_title, $meta_description, $canonical_url, $og_title, $og_description, $og_image, $schema_faq, $id]
            );

            $page = Database::fetch("SELECT title FROM pages WHERE id = ?", [$id]);
            log_activity($_SESSION['admin_user_id'], 'update_page_seo', "Updated SEO tags for page: " . ($page ? $page['title'] : "ID $id"));
            clear_page_cache(); // Reset compiled cache
            header('Location: ' . BASE_URL . 'admin/pages.php?success=1');
            exit;
        } catch (Exception $e) {
            $error = 'Gagal memperbarui SEO: ' . $e->getMessage();
        }
    }
}

// Process Create Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $title = trim($_POST['title']);
        $slug = trim($_POST['slug']);
        $meta_title = trim($_POST['meta_title']);
        $meta_description = trim($_POST['meta_description']);
        $canonical_url = trim($_POST['canonical_url'] ?? '');

        // Normalise slug: strip surrounding slashes, lowercase, safe characters only
        $slug = strtolower(trim($slug, "/ \t\n\r\0\x0B"));
        $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug);
        $slug = trim(preg_replace('/-+/', '-', $slug), '-');

        if (empty($title) || empty($slug) || empty($meta_title) || empty($meta_description)) {
            $error = 'Nama halaman, slug, meta title, dan meta description wajib diisi.';
        } elseif (Database::fetch("SELECT id FROM pages WHERE slug = ?", [$slug])) {
            $error = 'Halaman dengan slug "' . sanitize($slug) . '" sudah terdaftar.';
        } elseif (!is_dir(DIR_ROOT . $slug)) {
            // A page row without a matching route would be published to sitemap.xml
            // and return 404 to crawlers, so refuse to create it.
            $error = 'Folder "' . sanitize($slug) . '" tidak ditemukan di server. Buat foldernya terlebih dahulu agar halaman tidak menjadi 404 di sitemap.';
        } else {
            try {
                Database::insert(
                    "INSERT INTO pages (title, slug, meta_title, meta_description, canonical_url) VALUES (?, ?, ?, ?, ?)",
                    [$title, $slug, $meta_title, $meta_description, ($canonical_url !== '' ? $canonical_url : null)]
                );
                log_activity($_SESSION['admin_user_id'], 'create_page_seo', "Created SEO landing page: " . sanitize($title) . " (/$slug/)");
                clear_page_cache();
                header('Location: ' . BASE_URL . 'admin/pages.php?created=1');
                exit;
            } catch (Exception $e) {
                $error = 'Gagal menambahkan halaman: ' . $e->getMessage();
            }
        }
    }
}

// Fetch Page details for editing
$page_data = null;
$faq_list = [];
if ($action === 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $page_data = Database::fetch("SELECT * FROM pages WHERE id = ?", [$id]);
    if ($page_data) {
        $faq_list = $page_data['schema_faq'] ? json_decode($page_data['schema_faq'], true) : [];
    } else {
        header('Location: ' . BASE_URL . 'admin/pages.php');
        exit;
    }
}

// Fetch all landing pages records
try {
    $pages_list = Database::fetchAll("SELECT id, title, slug, updated_at FROM pages ORDER BY title ASC");
} catch (Exception $e) {
    error_log("Failed retrieving pages: " . $e->getMessage());
    $pages_list = [];
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = 'Metadata SEO berhasil diperbarui!';
}

if (isset($_GET['created']) && $_GET['created'] == 1) {
    $success = 'Halaman baru berhasil ditambahkan! Silakan lengkapi Open Graph & FAQ Schema-nya.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO Landing Pages - Urban Office</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
    <script>
        // Helper to add FAQ fields dynamically
        function addFaqField() {
            const container = document.getElementById('faq-container');
            const row = document.createElement('div');
            row.className = 'faq-row';
            row.style.display = 'grid';
            row.style.gridTemplateColumns = '1fr 1fr 40px';
            row.style.gap = '10px';
            row.style.marginBottom = '10px';
            row.innerHTML = `
                <input type="text" name="faq_q[]" class="form-control" placeholder="Pertanyaan..." style="background-color:white; border:1px solid #cbd5e1; padding:8px;">
                <input type="text" name="faq_a[]" class="form-control" placeholder="Jawaban..." style="background-color:white; border:1px solid #cbd5e1; padding:8px;">
                <button type="button" class="btn-admin btn-admin-danger" onclick="this.parentElement.remove()" style="padding:8px; width:100%; justify-content:center;">X</button>
            `;
            container.appendChild(row);
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
                <h1 class="admin-title">SEO Landing Pages</h1>
                <div>
                    <?php if ($action !== 'list'): ?>
                        <a href="pages.php" class="btn-admin btn-admin-secondary">Kembali ke Daftar</a>
                    <?php else: ?>
                        <a href="?action=new" class="btn-admin btn-admin-primary">+ Tambah Halaman</a>
                    <?php endif; ?>
                </div>
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

            <!-- Action: NEW Landing Page Form -->
            <?php if ($action === 'new'): ?>
                <div class="admin-card">
                    <h2>Tambah SEO Landing Page</h2>
                    <p style="font-size:0.85rem; color:#64748b; margin-top:8px;">
                        Daftarkan halaman yang sudah ada di server agar punya meta title/description sendiri dan masuk ke sitemap.xml.
                        Folder halaman harus sudah dibuat lebih dulu.
                    </p>
                    <form action="" method="POST" style="margin-top: 24px;" class="form-horizontal">
                        <?php echo csrf_field(); ?>

                        <div style="margin-bottom: 20px;">
                            <label class="form-label">Nama Halaman (untuk tampilan admin)</label>
                            <input type="text" name="title" class="form-control" placeholder="Perizinan &amp; Perubahan Perusahaan" style="background-color: white; border:1px solid #cbd5e1; width:100%;" required>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label class="form-label">Slug URL (tanpa garis miring)</label>
                            <input type="text" name="slug" class="form-control" placeholder="perizinan-dan-perubahan-perusahaan" style="background-color: white; border:1px solid #cbd5e1; width:100%;" required>
                            <p style="font-size:0.78rem; color:#64748b; margin-top:6px;">Harus sama persis dengan nama folder di server.</p>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label class="form-label">SEO Meta Title (Maks 60 Karakter)</label>
                            <input type="text" name="meta_title" class="form-control" style="background-color: white; border:1px solid #cbd5e1; width:100%;" required>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label class="form-label">SEO Meta Description (Maks 160 Karakter)</label>
                            <textarea name="meta_description" class="form-control" style="background-color: white; border:1px solid #cbd5e1; width:100%; height:90px;" required></textarea>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label class="form-label">Canonical URL (opsional)</label>
                            <input type="text" name="canonical_url" class="form-control" style="background-color: white; border:1px solid #cbd5e1; width:100%;">
                        </div>

                        <button type="submit" name="create" class="btn-admin btn-admin-primary" style="margin-top:10px; padding:12px 24px;">
                            Simpan Halaman Baru
                        </button>
                    </form>
                </div>

            <!-- Action: EDIT Metadata Page Form -->
            <?php elseif ($action === 'edit' && $page_data): ?>
                <div class="admin-card">
                    <h2>Edit SEO Meta: <?php echo sanitize($page_data['title']); ?></h2>
                    <form action="" method="POST" style="margin-top: 24px;" class="form-horizontal">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo $page_data['id']; ?>">

                        <!-- Base HTML Metadata -->
                        <div style="margin-bottom: 20px;">
                            <label class="form-label">SEO Meta Title (Maks 60 Karakter)</label>
                            <input type="text" name="meta_title" class="form-control" value="<?php echo sanitize($page_data['meta_title'] ?? ''); ?>" style="background-color: white; border:1px solid #cbd5e1; width:100%;" required>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label class="form-label">SEO Meta Description (Maks 160 Karakter)</label>
                            <textarea name="meta_description" class="form-control" style="background-color: white; border:1px solid #cbd5e1; width:100%; height:90px;" required><?php echo sanitize($page_data['meta_description'] ?? ''); ?></textarea>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label class="form-label">Canonical URL (Meninggalkan kosong W3C memproses link otomatis)</label>
                            <input type="text" name="canonical_url" class="form-control" value="<?php echo sanitize($page_data['canonical_url'] ?? ''); ?>" style="background-color: white; border:1px solid #cbd5e1; width:100%;">
                        </div>

                        <!-- Open Graph Settings -->
                        <h3 style="margin: 30px 0 16px 0; border-top:1px solid #e2e8f0; padding-top:20px;">Open Graph (Social Sharing)</h3>

                        <div style="margin-bottom: 20px;">
                            <label class="form-label">OG Title</label>
                            <input type="text" name="og_title" class="form-control" value="<?php echo sanitize($page_data['og_title'] ?? ''); ?>" style="background-color: white; border:1px solid #cbd5e1; width:100%;">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label class="form-label">OG Description</label>
                            <textarea name="og_description" class="form-control" style="background-color: white; border:1px solid #cbd5e1; width:100%; height:70px;"><?php echo sanitize($page_data['og_description'] ?? ''); ?></textarea>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label class="form-label">OG Image (Path/URL)</label>
                            <input type="text" name="og_image" class="form-control" value="<?php echo sanitize($page_data['og_image'] ?? ''); ?>" style="background-color: white; border:1px solid #cbd5e1; width:100%;">
                        </div>

                        <!-- FAQ JSON Schema Markup -->
                        <h3 style="margin: 30px 0 10px 0; border-top:1px solid #e2e8f0; padding-top:20px; display:flex; justify-content:space-between; align-items:center;">
                            FAQ Schema Markup
                            <button type="button" class="btn-admin btn-admin-secondary btn-admin-sm" onclick="addFaqField()">+ Tambah FAQ</button>
                        </h3>
                        <p style="font-size:0.8rem; color:#64748b; margin-bottom:16px;">Output JSON structured data yang akan dibaca oleh robot penelusur Google.</p>

                        <div id="faq-container">
                            <?php if (!empty($faq_list)): foreach ($faq_list as $faq): ?>
                                <div class="faq-row" style="display: grid; grid-template-columns: 1fr 1fr 40px; gap: 10px; margin-bottom: 10px;">
                                    <input type="text" name="faq_q[]" class="form-control" value="<?php echo sanitize($faq['question']); ?>" placeholder="Pertanyaan..." style="background-color:white; border:1px solid #cbd5e1; padding:8px;">
                                    <input type="text" name="faq_a[]" class="form-control" value="<?php echo sanitize($faq['answer']); ?>" placeholder="Jawaban..." style="background-color:white; border:1px solid #cbd5e1; padding:8px;">
                                    <button type="button" class="btn-admin btn-admin-danger" onclick="this.parentElement.remove()" style="padding:8px; width:100%; justify-content:center;">X</button>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>

                        <button type="submit" name="update" class="btn-admin btn-admin-primary" style="margin-top:30px; padding:12px 24px;">
                            Simpan Perubahan SEO
                        </button>
                    </form>
                </div>

            <!-- Action: LIST Pages View -->
            <?php else: ?>
                <div class="admin-card">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Nama Halaman</th>
                                    <th>Slug URL</th>
                                    <th>Last Updated</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($pages_list)): foreach ($pages_list as $page): ?>
                                    <tr>
                                        <td><strong><?php echo sanitize($page['title']); ?></strong></td>
                                        <td><code>/<?php echo sanitize($page['slug']); ?>/</code></td>
                                        <td><?php echo date('d M Y H:i', strtotime($page['updated_at'])); ?></td>
                                        <td>
                                            <a href="?action=edit&id=<?php echo $page['id']; ?>" class="btn-admin btn-admin-secondary btn-admin-sm">Edit SEO Meta</a>
                                            <a href="<?php echo BASE_URL . $page['slug']; ?>" target="_blank" class="btn-admin btn-admin-secondary btn-admin-sm" style="color:var(--admin-primary);">View Page</a>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: #94a3b8;">Belum ada halaman disematkan di database.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>
</body>
</html>
