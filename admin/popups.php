<?php
/**
 * Admin Pop-up Banners Management
 * Manage promotional pop-up modals, image upload, target pages, display frequency, and SEO deep-linking.
 */

require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/inc/locations_data.php';

$error = '';
$success = '';
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Helper: Slugify string
function make_slug(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'promo-' . time());
}

// ---------------------------------------------------------------------------
// POST Handler: Toggle Status
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $toggle_id = intval($_POST['toggle_id'] ?? 0);
        try {
            $cur = Database::fetch("SELECT id, title, status FROM popups WHERE id = ?", [$toggle_id]);
            if ($cur) {
                $new_status = $cur['status'] ? 0 : 1;
                Database::query("UPDATE popups SET status = ? WHERE id = ?", [$new_status, $toggle_id]);
                log_activity($_SESSION['admin_user_id'], 'popup_toggle', "Toggled status popup #$toggle_id ({$cur['title']}) to " . ($new_status ? 'Active' : 'Inactive'));
                clear_page_cache();
                header('Location: ' . BASE_URL . 'admin/popups.php?msg=toggled');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Gagal mengubah status: ' . $e->getMessage();
        }
    }
}

// ---------------------------------------------------------------------------
// POST Handler: Delete Popup
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_popup'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $delete_id = intval($_POST['delete_id'] ?? 0);
        try {
            $item = Database::fetch("SELECT id, title, image_path FROM popups WHERE id = ?", [$delete_id]);
            if ($item) {
                // Delete image file if inside assets/images/popups/
                if (!empty($item['image_path']) && strpos($item['image_path'], 'assets/images/popups/') === 0) {
                    $file_abs = DIR_ROOT . $item['image_path'];
                    if (is_file($file_abs)) {
                        @unlink($file_abs);
                    }
                }
                Database::query("DELETE FROM popups WHERE id = ?", [$delete_id]);
                log_activity($_SESSION['admin_user_id'], 'popup_delete', "Deleted popup #$delete_id ({$item['title']})");
                clear_page_cache();
                header('Location: ' . BASE_URL . 'admin/popups.php?msg=deleted');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Gagal menghapus pop-up: ' . $e->getMessage();
        }
    }
}

// ---------------------------------------------------------------------------
// POST Handler: Save (Create / Update) Popup
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_popup'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $title          = trim($_POST['title'] ?? '');
        $slug_input     = trim($_POST['slug'] ?? '');
        $alt_text       = trim($_POST['alt_text'] ?? '');
        $link_type      = trim($_POST['link_type'] ?? 'url');
        if (!in_array($link_type, ['url', 'whatsapp'], true)) {
            $link_type = 'url';
        }
        $target_url     = trim($_POST['target_url'] ?? '');
        $wa_number      = trim($_POST['wa_number'] ?? '085107620100');
        $wa_message     = trim($_POST['wa_message'] ?? '');

        if ($link_type === 'whatsapp') {
            $clean_phone = preg_replace('/[^0-9]/', '', $wa_number);
            if (strpos($clean_phone, '0') === 0) {
                $clean_phone = '62' . substr($clean_phone, 1);
            }
            if (empty($clean_phone)) {
                $clean_phone = '6285107620100';
            }
            $target_url = 'https://web.whatsapp.com/send?phone=' . $clean_phone . (!empty($wa_message) ? ('&text=' . rawurlencode($wa_message)) : '');
        } else {
            if (empty($target_url)) {
                $target_url = 'https://my.urbanoffice.co.id/beforelogin';
            }
        }

        $display_target = trim($_POST['display_target'] ?? 'all');
        $frequency      = trim($_POST['frequency'] ?? 'daily');
        $frequency_days = max(1, intval($_POST['frequency_days'] ?? 1));
        $delay_seconds  = max(2, intval($_POST['delay_seconds'] ?? 2));
        $status         = isset($_POST['status']) ? 1 : 0;
        $start_date     = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date       = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $existing_image = trim($_POST['existing_image'] ?? '');

        // Resolve slug
        $slug = $slug_input !== '' ? make_slug($slug_input) : make_slug($title);
        if ($alt_text === '') {
            $alt_text = $title;
        }

        // Selected specific pages
        $target_pages_json = null;
        if ($display_target === 'specific') {
            $sel_pages = isset($_POST['target_pages']) && is_array($_POST['target_pages']) ? $_POST['target_pages'] : [];
            $target_pages_json = json_encode(array_values(array_filter($sel_pages)));
        }

        if (empty($title)) {
            $error = 'Judul pop-up wajib diisi.';
        } else {
            // Check slug uniqueness
            $slug_check = Database::fetch("SELECT id FROM popups WHERE slug = ? AND id != ?", [$slug, $id]);
            if ($slug_check) {
                $slug = $slug . '-' . time();
            }

            // Image Upload Handling
            $image_path = $existing_image;
            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmp  = $_FILES['image_file']['tmp_name'];
                $fileName = $_FILES['image_file']['name'];
                $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowed  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                if (!in_array($fileExt, $allowed, true)) {
                    $error = 'Format file gambar tidak didukung. Harap unggah format JPG, PNG, WEBP, atau GIF.';
                } else {
                    $uploadDir = DIR_ROOT . 'assets/images/popups/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $newFileName = 'popup_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $fileExt;
                    $destPath    = $uploadDir . $newFileName;

                    if (move_uploaded_file($fileTmp, $destPath)) {
                        // Delete previous image if replaced
                        if (!empty($existing_image) && strpos($existing_image, 'assets/images/popups/') === 0) {
                            $old_abs = DIR_ROOT . $existing_image;
                            if (is_file($old_abs)) {
                                @unlink($old_abs);
                            }
                        }
                        $image_path = 'assets/images/popups/' . $newFileName;
                    } else {
                        $error = 'Gagal mengunggah file gambar ke server.';
                    }
                }
            } elseif (!empty($_POST['image_url_direct'])) {
                $image_path = trim($_POST['image_url_direct']);
            }

            if (empty($error)) {
                if (empty($image_path)) {
                    $error = 'Gambar pop-up wajib diunggah atau disertakan.';
                } else {
                    try {
                        if ($id > 0) {
                            // Update
                            Database::query(
                                "UPDATE popups SET 
                                    title = ?, slug = ?, image_path = ?, target_url = ?, link_type = ?, wa_number = ?, wa_message = ?, alt_text = ?,
                                    display_target = ?, target_pages = ?, frequency = ?, frequency_days = ?,
                                    delay_seconds = ?, start_date = ?, end_date = ?, status = ?
                                 WHERE id = ?",
                                [
                                    $title, $slug, $image_path, $target_url, $link_type, $wa_number, $wa_message, $alt_text,
                                    $display_target, $target_pages_json, $frequency, $frequency_days,
                                    $delay_seconds, $start_date, $end_date, $status, $id
                                ]
                            );
                            log_activity($_SESSION['admin_user_id'], 'popup_update', "Updated popup #$id ($title)");
                            clear_page_cache();
                            header('Location: ' . BASE_URL . 'admin/popups.php?msg=updated');
                            exit;
                        } else {
                            // Insert
                            Database::insert(
                                "INSERT INTO popups 
                                    (title, slug, image_path, target_url, link_type, wa_number, wa_message, alt_text, display_target, target_pages, frequency, frequency_days, delay_seconds, start_date, end_date, status)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                                [
                                    $title, $slug, $image_path, $target_url, $link_type, $wa_number, $wa_message, $alt_text,
                                    $display_target, $target_pages_json, $frequency, $frequency_days,
                                    $delay_seconds, $start_date, $end_date, $status
                                ]
                            );
                            log_activity($_SESSION['admin_user_id'], 'popup_create', "Created new popup ($title)");
                            clear_page_cache();
                            header('Location: ' . BASE_URL . 'admin/popups.php?msg=created');
                            exit;
                        }
                    } catch (Exception $e) {
                        $error = 'Gagal menyimpan data pop-up: ' . $e->getMessage();
                    }
                }
            }
        }
    }
}

// Success message feedback
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $success = 'Pop-up banner baru berhasil disimpan!';
    if ($_GET['msg'] === 'updated') $success = 'Pop-up banner berhasil diperbarui!';
    if ($_GET['msg'] === 'deleted') $success = 'Pop-up banner berhasil dihapus!';
    if ($_GET['msg'] === 'toggled') $success = 'Status tayang pop-up berhasil diubah!';
}

// ---------------------------------------------------------------------------
// Prepare Form Data (if Add / Edit)
// ---------------------------------------------------------------------------
$edit_data = null;
if ($action === 'edit' && $id > 0) {
    $edit_data = Database::fetch("SELECT * FROM popups WHERE id = ?", [$id]);
    if (!$edit_data) {
        $error = 'Data pop-up tidak ditemukan.';
        $action = 'list';
    }
}

// Available pages categorized by workspace services and sections
$available_groups = [
    '🏢 Virtual Office (Per Cabang)' => [
        'virtual-office-surabaya'       => 'Virtual Office Surabaya (MERR)',
        'virtual-office-surabaya-timur' => 'Virtual Office Surabaya Timur (Klampis)',
        'virtual-office-surabaya-barat' => 'Virtual Office Surabaya Barat (Grand Sungkono Lagoon)',
        'virtual-office-jakarta'        => 'Virtual Office Jakarta Selatan (Fatmawati)',
        'virtual-office-jakarta-timur'  => 'Virtual Office Jakarta Timur (Cakung)',
        'virtual-office-gresik'         => 'Virtual Office Gresik',
        'virtual-office-medan'          => 'Virtual Office Medan',
        'virtual-office-malang'         => 'Virtual Office Malang',
    ],
    '🏢 Private Office / Sewa Kantor (Per Kota)' => [
        'sewa-kantor-surabaya' => 'Private Office Surabaya',
        'sewa-kantor-jakarta'  => 'Private Office Jakarta',
        'sewa-kantor-gresik'   => 'Private Office Gresik',
        'sewa-kantor-malang'   => 'Private Office Malang',
    ],
    '🤝 Ruang Meeting (Per Kota)' => [
        'meeting-room-surabaya' => 'Ruang Meeting Surabaya',
        'meeting-room-jakarta'  => 'Ruang Meeting Jakarta',
        'meeting-room-gresik'   => 'Ruang Meeting Gresik',
        'meeting-room-malang'   => 'Ruang Meeting Malang',
    ],
    '💻 Coworking Space (Per Kota)' => [
        'coworking-space-urban-office' => 'Coworking Space Surabaya (Utama)',
        'coworking-space-surabaya'     => 'Coworking Space Surabaya',
        'coworking-space-jakarta'      => 'Coworking Space Jakarta',
        'coworking-space-malang'       => 'Coworking Space Malang',
    ],
    '🎉 Event Space (Per Kota)' => [
        'event-space-55k-perjam-urbanoffice' => 'Event Space Surabaya (Utama)',
        'event-space-surabaya'               => 'Event Space Surabaya',
        'event-space-jakarta'                => 'Event Space Jakarta',
        'event-space-malang'                 => 'Event Space Malang',
    ],
    '👥 Sharing Room Office' => [
        'sharing-room-office' => 'Sharing Room Office (Surabaya, Jakarta, Malang)',
    ],
    '⚖️ Jasa Pendirian Usaha & Legalitas' => [
        'pendirian-pt-include-virtual-office'      => 'Pendirian PT + Virtual Office',
        'pendirian-cv-virtual-office'              => 'Pendirian CV + Virtual Office',
        'pendirian-perorangan-plus-virtual-office' => 'PT Perorangan + Virtual Office',
        'pendirian-pma-plus-virtual-office'        => 'Pendirian PMA + Virtual Office',
        'perizinan-dan-perubahan-perusahaan'       => 'Perizinan & Perubahan Perusahaan',
        'pajak-dan-akunting'                       => 'Jasa Pajak & Akunting',
    ],
    '🌐 Halaman Utama, Profil & Blog' => [
        ''                       => 'Beranda / Homepage Utama',
        'about'                  => 'Tentang Kami',
        'gallery'                => 'Galeri Foto Fasilitas',
        'lokasi-urban-office'    => 'Direktori Jaringan Lokasi',
        'kemitraan-urban-office' => 'Kemitraan Properti',
        'urban-office-karir'     => 'Karir & Lowongan Kerja',
        'blog'                   => 'Blog & Artikel Bisnis',
        'newsletter'             => 'Berlangganan Newsletter',
    ],
];

// Flat array for quick label resolution
$available_pages = [];
foreach ($available_groups as $group_items) {
    foreach ($group_items as $s_k => $s_lbl) {
        $available_pages[$s_k] = $s_lbl;
    }
}

// ---------------------------------------------------------------------------
// Prepare List Data
// ---------------------------------------------------------------------------
$popups_list = [];
if ($action === 'list') {
    try {
        $popups_list = Database::fetchAll("SELECT * FROM popups ORDER BY id DESC");
    } catch (Exception $e) {
        error_log("Failed pulling popups: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($action === 'add' ? 'Tambah Pop-up' : ($action === 'edit' ? 'Edit Pop-up' : 'Kelola Pop-up Banner')); ?> - Urban Office</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .badge-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-active { background-color: #dcfce7; color: #15803d; }
        .badge-inactive { background-color: #f1f5f9; color: #64748b; }
        .badge-info { background-color: #e0f2fe; color: #0369a1; }
        .badge-warning { background-color: #fef3c7; color: #b45309; }
        .checklist-box {
            max-height: 320px;
            overflow-y: auto;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 14px;
            background: #fff;
        }
        .checklist-group-title {
            background-color: #f1f5f9;
            border-left: 3px solid var(--admin-primary);
            padding: 6px 10px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #1e293b;
            border-radius: 3px;
            margin: 12px 0 6px 0;
            letter-spacing: 0.02em;
        }
        .checklist-group-title:first-child {
            margin-top: 0;
        }
        .checklist-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.88rem;
            cursor: pointer;
        }
        .checklist-item:last-child { border-bottom: none; }
        .checklist-item input { cursor: pointer; }
        .popup-thumb {
            width: 70px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .popup-thumb:hover { transform: scale(1.08); }
        .radio-card-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }
        .radio-card {
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px;
            cursor: pointer;
            background: #fff;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .radio-card:hover { border-color: var(--admin-primary); }
        .radio-card input[type="radio"] { margin-right: 6px; }
        .radio-card.selected {
            border-color: var(--admin-primary);
            background-color: #f0f9ff;
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-layout">
        
        <!-- Sidebar Navigation Menu -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Workspace Area -->
        <main class="admin-main">
            <div class="admin-top">
                <div>
                    <h1 class="admin-title">Pop-up Banner Promosi</h1>
                    <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">
                        Kelola banner pop-up modal, penargetan halaman, frekuensi tampil, dan deep-link URL indexing.
                    </p>
                </div>
                <div>
                    <?php if ($action !== 'list'): ?>
                        <a href="<?php echo BASE_URL; ?>admin/popups.php" class="btn-admin btn-admin-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>admin/popups.php?action=add" class="btn-admin btn-admin-primary">
                            <i class="bi bi-plus-lg"></i> Tambah Pop-up Baru
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Alerts -->
            <?php if (!empty($error)): ?>
                <div style="background-color: #fee2e2; color: #b91c1c; padding: 12px 18px; border-radius: 6px; margin-bottom: 24px; font-weight: 500;">
                    ✗ <?php echo sanitize($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div style="background-color: #dcfce7; color: #15803d; padding: 12px 18px; border-radius: 6px; margin-bottom: 24px; font-weight: 500;">
                    ✓ <?php echo sanitize($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <!-- ========================================================= -->
                <!-- LIST VIEW                                                 -->
                <!-- ========================================================= -->
                <div class="admin-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="margin: 0;">Daftar Pop-up Banner (<?php echo count($popups_list); ?>)</h3>
                    </div>

                    <?php if (empty($popups_list)): ?>
                        <div style="text-align: center; padding: 60px 20px; color: #64748b;">
                            <i class="bi bi-images" style="font-size: 3rem; color: #cbd5e1; display: block; margin-bottom: 12px;"></i>
                            <h4 style="margin: 0 0 8px 0; color: #334155;">Belum ada Pop-up Banner</h4>
                            <p style="margin: 0 0 20px 0;">Buat banner promosi pertama Anda untuk meningkatkan konversi dan engagement pengunjung.</p>
                            <a href="<?php echo BASE_URL; ?>admin/popups.php?action=add" class="btn-admin btn-admin-primary">
                                <i class="bi bi-plus-lg"></i> Buat Pop-up Sekarang
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Banner</th>
                                        <th>Judul & Identifier (Slug)</th>
                                        <th>Target Halaman</th>
                                        <th>Frekuensi Tampil</th>
                                        <th>Status Tayang</th>
                                        <th style="text-align: center;">Views / Clicks</th>
                                        <th style="width: 140px; text-align: right;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($popups_list as $p): 
                                        $p_id = (int)$p['id'];
                                        $views = (int)$p['views_count'];
                                        $clicks = (int)$p['clicks_count'];
                                        $ctr = $views > 0 ? round(($clicks / $views) * 100, 1) : 0;
                                        $img_src = media_url($p['image_path']);
                                        $deep_link = BASE_URL . '?promo=' . urlencode($p['slug']);
                                        
                                        // Target Label
                                        $target_label = 'Semua Halaman';
                                        if ($p['display_target'] === 'home_only') {
                                            $target_label = 'Beranda Saja';
                                        } elseif ($p['display_target'] === 'specific') {
                                            $decoded_pages = json_decode($p['target_pages'] ?? '[]', true);
                                            $count_p = is_array($decoded_pages) ? count($decoded_pages) : 0;
                                            $target_label = $count_p . ' Halaman Terpilih';
                                        }

                                        // Frequency Label
                                        $freq_label = 'Tiap Refresh';
                                        if ($p['frequency'] === 'session') $freq_label = '1x per Sesi';
                                        if ($p['frequency'] === 'daily') $freq_label = 'Gap 24 Jam (1 Hari)';
                                        if ($p['frequency'] === 'custom') $freq_label = 'Gap ' . $p['frequency_days'] . ' Hari';
                                    ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo $img_src; ?>" target="_blank" title="Klik untuk lihat ukuran penuh">
                                                    <img src="<?php echo $img_src; ?>" alt="<?php echo sanitize($p['title']); ?>" class="popup-thumb" onerror="this.src='https://placehold.co/120x80?text=No+Image';">
                                                </a>
                                            </td>
                                            <td>
                                                <strong><?php echo sanitize($p['title']); ?></strong>
                                                <div style="margin-top: 4px; font-size: 0.82rem; color: #64748b;">
                                                    Slug: <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #0284c7;"><?php echo sanitize($p['slug']); ?></code>
                                                </div>
                                                <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                                                    <?php if (($p['link_type'] ?? 'url') === 'whatsapp'): ?>
                                                        <span class="badge-pill" style="background: #dcfce7; color: #166534; font-size: 0.72rem; font-weight: 600;">
                                                            <i class="bi bi-whatsapp"></i> WA (<?php echo sanitize($p['wa_number'] ?: '085107620100'); ?>)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge-pill" style="background: #e0f2fe; color: #0369a1; font-size: 0.72rem; font-weight: 600;">
                                                            <i class="bi bi-link-45deg"></i> Web URL
                                                        </span>
                                                    <?php endif; ?>
                                                    <a href="<?php echo $deep_link; ?>" target="_blank" class="btn-admin-sm btn-admin-secondary" style="display: inline-flex; align-items: center; gap: 4px; text-decoration: none; font-size: 0.75rem;">
                                                        <i class="bi bi-box-arrow-up-right"></i> Test Deep-Link
                                                    </a>
                                                    <button type="button" class="btn-admin-sm btn-admin-secondary" style="font-size: 0.75rem; cursor: pointer;" onclick="navigator.clipboard.writeText('<?php echo $deep_link; ?>'); alert('Link disalin: <?php echo $deep_link; ?>');">
                                                        <i class="bi bi-clipboard"></i> Salin URL
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge-pill badge-info">
                                                    <i class="bi bi-geo-alt"></i> <?php echo $target_label; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="font-size: 0.88rem; font-weight: 500;">
                                                    <i class="bi bi-clock-history"></i> <?php echo $freq_label; ?>
                                                </div>
                                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 3px;">
                                                    Delay: <?php echo (int)$p['delay_seconds']; ?> detik
                                                </div>
                                            </td>
                                            <td>
                                                <form action="" method="POST" style="display: inline;">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="toggle_id" value="<?php echo $p_id; ?>">
                                                    <input type="hidden" name="toggle_status" value="1">
                                                    <?php if ($p['status'] == 1): ?>
                                                        <button type="submit" class="badge-pill badge-active" style="border: none; cursor: pointer;" title="Klik untuk nonaktifkan">
                                                            ● Aktif
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="submit" class="badge-pill badge-inactive" style="border: none; cursor: pointer;" title="Klik untuk aktifkan">
                                                            ○ Nonaktif
                                                        </button>
                                                    <?php endif; ?>
                                                </form>
                                                <?php if (!empty($p['start_date']) || !empty($p['end_date'])): ?>
                                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">
                                                        Jadwal: <?php echo !empty($p['start_date']) ? date('d/m/y', strtotime($p['start_date'])) : '∞'; ?> - <?php echo !empty($p['end_date']) ? date('d/m/y', strtotime($p['end_date'])) : '∞'; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <div style="font-weight: 600; font-size: 0.95rem;">
                                                    <?php echo number_format($views); ?> <span style="font-size: 0.75rem; color: #64748b; font-weight: normal;">views</span>
                                                </div>
                                                <div style="font-size: 0.8rem; color: #0284c7;">
                                                    <?php echo number_format($clicks); ?> clicks (CTR <?php echo $ctr; ?>%)
                                                </div>
                                            </td>
                                            <td style="text-align: right;">
                                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                                    <a href="<?php echo BASE_URL; ?>admin/popups.php?action=edit&id=<?php echo $p_id; ?>" class="btn-admin-sm btn-admin-secondary" title="Edit Pop-up">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                    <form action="" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pop-up ini?');" style="display: inline;">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="delete_id" value="<?php echo $p_id; ?>">
                                                        <button type="submit" name="delete_popup" class="btn-admin-sm btn-admin-danger" title="Hapus Pop-up" style="cursor: pointer;">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else: 
                // =========================================================
                // ADD / EDIT FORM VIEW                                     
                // =========================================================
                $form_title          = $edit_data['title'] ?? '';
                $form_slug           = $edit_data['slug'] ?? '';
                $form_alt            = $edit_data['alt_text'] ?? '';
                $form_image          = $edit_data['image_path'] ?? '';
                $form_link_type      = $edit_data['link_type'] ?? 'url';
                $form_target_url     = $edit_data['target_url'] ?? 'https://my.urbanoffice.co.id/beforelogin';
                $form_wa_number      = !empty($edit_data['wa_number']) ? $edit_data['wa_number'] : '085107620100';
                $form_wa_message     = $edit_data['wa_message'] ?? (!empty($edit_data['title']) ? "Halo Admin Urban Office, saya tertarik dengan promo \"" . $edit_data['title'] . "\" yang ada di website. Boleh minta info detail dan ketentuannya?" : "Halo Admin Urban Office, saya tertarik dengan promo yang ada di website. Boleh minta info detail dan ketentuannya?");
                $form_display        = $edit_data['display_target'] ?? 'all';
                $form_pages          = json_decode($edit_data['target_pages'] ?? '[]', true) ?: [];
                $form_freq           = $edit_data['frequency'] ?? 'daily';
                $form_days           = $edit_data['frequency_days'] ?? 1;
                $form_delay          = max(2, (int)($edit_data['delay_seconds'] ?? 2));
                $form_status         = isset($edit_data) ? ($edit_data['status'] == 1) : true;
                $form_start          = !empty($edit_data['start_date']) ? date('Y-m-d\TH:i', strtotime($edit_data['start_date'])) : '';
                $form_end            = !empty($edit_data['end_date']) ? date('Y-m-d\TH:i', strtotime($edit_data['end_date'])) : '';
            ?>
                <form action="" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="save_popup" value="1">
                    <input type="hidden" name="existing_image" value="<?php echo sanitize($form_image); ?>">

                    <div style="display: grid; grid-template-columns: 1.3fr 1fr; gap: 30px;">
                        
                        <!-- LEFT COLUMN: Content & Image -->
                        <div style="display: flex; flex-direction: column; gap: 24px;">
                            
                            <!-- Card: Info Dasar -->
                            <div class="admin-card">
                                <h3>1. Informasi & Konten Banner</h3>
                                
                                <div style="margin-top: 18px;">
                                    <label class="form-label">Judul Pop-up Promosi *</label>
                                    <input type="text" name="title" id="popup_title" class="form-control" value="<?php echo sanitize($form_title); ?>" placeholder="Contoh: Promo Spesial Virtual Office Jakarta Diskon 20%" required>
                                    <div class="form-help">Nama internal untuk mengenali kampanye pop-up ini.</div>
                                </div>

                                <div style="margin-top: 18px;">
                                    <label class="form-label">Identifier URL / Slug (Untuk Indexing & Deep-link) *</label>
                                    <input type="text" name="slug" id="popup_slug" class="form-control" value="<?php echo sanitize($form_slug); ?>" placeholder="promo-vo-jakarta">
                                    <div class="slug-preview-container">
                                        <span class="slug-preview-label">Deep-link URL:</span>
                                        <span class="slug-preview-val"><?php echo BASE_URL; ?>?promo=<span id="slug_preview_text"><?php echo sanitize($form_slug ?: 'promo-vo-jakarta'); ?></span></span>
                                    </div>
                                    <div class="form-help">Saat pop-up terbuka, URL di browser akan berganti secara dinamis ke parameter ini tanpa reload. Pengunjung yang membuka link ini juga langsung melihat pop-up.</div>
                                </div>

                                <!-- Aksi Klik Banner Pop-up -->
                                <div style="margin-top: 22px; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                    <label class="form-label" style="font-weight: 600; font-size: 0.95rem; margin-bottom: 12px; display: block;">
                                        <i class="bi bi-cursor-fill" style="color: #0284c7;"></i> Aksi Ketika Banner Diklik Pengunjung *
                                    </label>
                                    
                                    <div style="display: flex; gap: 24px; margin-bottom: 16px;">
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500;">
                                            <input type="radio" name="link_type" value="url" <?php echo $form_link_type === 'url' ? 'checked' : ''; ?> onchange="toggleLinkType('url')" style="width: 17px; height: 17px; cursor: pointer;">
                                            <span><i class="bi bi-link-45deg"></i> Buka URL / Web (Redirect)</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500;">
                                            <input type="radio" name="link_type" value="whatsapp" <?php echo $form_link_type === 'whatsapp' ? 'checked' : ''; ?> onchange="toggleLinkType('whatsapp')" style="width: 17px; height: 17px; cursor: pointer;">
                                            <span style="color: #16a34a;"><i class="bi bi-whatsapp"></i> Kirim Pesan WhatsApp</span>
                                        </label>
                                    </div>

                                    <!-- Section 1: URL Input -->
                                    <div id="section_link_url" style="<?php echo $form_link_type === 'whatsapp' ? 'display: none;' : ''; ?>">
                                        <label class="form-label" style="font-size: 0.88rem;">Target URL Tujuan Klik</label>
                                        <input type="text" name="target_url" id="target_url_input" class="form-control" value="<?php echo sanitize($form_link_type === 'url' ? $form_target_url : 'https://my.urbanoffice.co.id/beforelogin'); ?>" placeholder="https://my.urbanoffice.co.id/beforelogin">
                                        <div class="form-help">Pengunjung akan dialihkan ke halaman web ini saat mengklik banner (default: portal member).</div>
                                    </div>

                                    <!-- Section 2: WhatsApp Input -->
                                    <div id="section_link_whatsapp" style="<?php echo $form_link_type === 'whatsapp' ? '' : 'display: none;'; ?>">
                                        <div style="display: grid; grid-template-columns: 1fr; gap: 14px;">
                                            <div>
                                                <label class="form-label" style="font-size: 0.88rem;">Nomor WhatsApp Tujuan *</label>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <div style="background: #e2e8f0; padding: 9px 14px; border-radius: 6px; font-weight: 600; color: #475569; font-size: 0.9rem;">
                                                        <i class="bi bi-whatsapp" style="color: #16a34a;"></i> WA
                                                    </div>
                                                    <input type="text" name="wa_number" id="wa_number_input" class="form-control" value="<?php echo sanitize($form_wa_number); ?>" placeholder="085107620100">
                                                </div>
                                                <div class="form-help">Nomor CS tujuan (default: <code>085107620100</code>). Otomatis disesuaikan ke standar WhatsApp (+62).</div>
                                            </div>

                                            <div>
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                                    <label class="form-label" style="font-size: 0.88rem; margin-bottom: 0;">Template Pesan WhatsApp (Dapat Dikustomisasi) *</label>
                                                    <button type="button" class="btn-admin-sm btn-admin-secondary" style="font-size: 0.72rem; padding: 3px 8px; cursor: pointer;" onclick="insertTitleToWa()">
                                                        <i class="bi bi-magic"></i> Sisipkan Judul Promo
                                                    </button>
                                                </div>
                                                <textarea name="wa_message" id="wa_message_input" class="form-control" rows="3" placeholder="Halo Admin Urban Office, saya tertarik dengan promo..."><?php echo sanitize($form_wa_message); ?></textarea>
                                                <div class="form-help">Pesan template ini akan otomatis terisi saat pengunjung membuka WhatsApp.</div>
                                            </div>

                                            <!-- Live Preview Link WhatsApp -->
                                            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 14px; font-size: 0.85rem; color: #065f46;">
                                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                                    <span style="font-weight: 700; color: #047857;">
                                                        <i class="bi bi-lightning-charge-fill" style="color: #10b981;"></i> Smart Direct Bypass Aktif
                                                    </span>
                                                    <span class="badge-pill" style="background: #dcfce7; color: #166534; font-size: 0.72rem; font-weight: 600;">Tanpa Halaman Perantara</span>
                                                </div>
                                                <div style="margin: 0 0 10px 0; font-size: 0.8rem; color: #065f46; line-height: 1.45;">
                                                    Pengunjung tidak akan ditahan di halaman perantara <code>api.whatsapp.com</code> lagi:<br>
                                                    • <strong>Laptop/PC:</strong> Langsung diarahkan ke <strong>WhatsApp Web</strong> (chat langsung terbuka).<br>
                                                    • <strong>HP/Smartphone:</strong> Langsung meluncurkan <strong>aplikasi WhatsApp</strong>.
                                                </div>
                                                <div style="font-weight: 600; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                                                    <span style="font-size: 0.8rem;"><i class="bi bi-chat-dots-fill"></i> Tautan WhatsApp Web (Bypass):</span>
                                                    <div style="display: flex; gap: 6px;">
                                                        <a href="#" id="wa_test_web_btn" target="_blank" class="btn-admin-sm btn-admin-secondary" style="background: #fff; border-color: #a7f3d0; color: #065f46; text-decoration: none; padding: 3px 8px; font-size: 0.75rem; font-weight: 600;">
                                                            <i class="bi bi-box-arrow-up-right"></i> Tes WA Web (Bypass)
                                                        </a>
                                                        <a href="#" id="wa_test_app_btn" target="_blank" class="btn-admin-sm btn-admin-secondary" style="background: #f0fdf4; border-color: #cbd5e1; color: #475569; text-decoration: none; padding: 3px 8px; font-size: 0.75rem;">
                                                            Tes wa.me
                                                        </a>
                                                    </div>
                                                </div>
                                                <div id="wa_preview_link" style="word-break: break-all; font-family: monospace; color: #047857; font-size: 0.78rem; background: #fff; padding: 8px 10px; border-radius: 4px; border: 1px solid #d1fae5;">
                                                    https://web.whatsapp.com/send?phone=6285107620100
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card: Upload Banner Image -->
                            <div class="admin-card">
                                <h3>2. Gambar Banner Pop-up</h3>
                                
                                <div style="margin-top: 18px;">
                                    <label class="form-label">Upload File Gambar Banner *</label>
                                    <input type="file" name="image_file" id="image_file_input" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif" <?php echo empty($form_image) ? 'required' : ''; ?>>
                                    <div class="form-help">Format didukung: WEBP, JPG, PNG, GIF (Maks. 5MB). Rekomendasi rasio: 4:5 atau 1:1 (vertikal/kotak) agar optimal di layar desktop & HP.</div>
                                </div>

                                <div style="margin-top: 14px;">
                                    <label class="form-label">Atau Masukkan Path / URL Gambar Langsung</label>
                                    <input type="text" name="image_url_direct" class="form-control" value="<?php echo sanitize($form_image); ?>" placeholder="assets/images/popups/banner.webp">
                                </div>

                                <div style="margin-top: 18px;">
                                    <label class="form-label">Alt Text Gambar (SEO Image Optimization)</label>
                                    <input type="text" name="alt_text" class="form-control" value="<?php echo sanitize($form_alt); ?>" placeholder="Promo Sewa Kantor Urban Office">
                                    <div class="form-help">Teks deskripsi gambar yang dibaca oleh bot Google Image untuk kebutuhan indexing.</div>
                                </div>

                                <!-- Live Image Preview -->
                                <div style="margin-top: 18px;">
                                    <label class="form-label">Preview Gambar Banner:</label>
                                    <div style="border: 2px dashed #cbd5e1; border-radius: 8px; padding: 12px; text-align: center; background: #f8fafc; min-height: 140px; display: flex; align-items: center; justify-content: center;">
                                        <img id="image_preview" src="<?php echo !empty($form_image) ? media_url($form_image) : ''; ?>" alt="Preview" style="max-height: 240px; max-width: 100%; border-radius: 6px; <?php echo empty($form_image) ? 'display: none;' : ''; ?>">
                                        <span id="preview_placeholder" style="<?php echo !empty($form_image) ? 'display: none;' : ''; ?> color: #94a3b8; font-size: 0.9rem;">
                                            <i class="bi bi-cloud-arrow-up" style="font-size: 2rem; display: block; margin-bottom: 4px;"></i>
                                            Pilih gambar di atas untuk melihat preview
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- RIGHT COLUMN: Targeting, Frequency & Timing -->
                        <div style="display: flex; flex-direction: column; gap: 24px;">

                            <!-- Card: Penargetan Halaman -->
                            <div class="admin-card">
                                <h3>3. Target Halaman Muncul</h3>
                                
                                <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 10px;">
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.92rem;">
                                        <input type="radio" name="display_target" value="all" <?php echo $form_display === 'all' ? 'checked' : ''; ?> onchange="toggleSpecificPages(false)">
                                        <span><strong>Semua Halaman Website</strong> (Global)</span>
                                    </label>

                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.92rem;">
                                        <input type="radio" name="display_target" value="home_only" <?php echo $form_display === 'home_only' ? 'checked' : ''; ?> onchange="toggleSpecificPages(false)">
                                        <span><strong>Hanya di Beranda / Homepage</strong></span>
                                    </label>

                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.92rem;">
                                        <input type="radio" name="display_target" value="specific" <?php echo $form_display === 'specific' ? 'checked' : ''; ?> onchange="toggleSpecificPages(true)">
                                        <span><strong>Pilih Halaman Tertentu</strong></span>
                                    </label>
                                </div>

                                <div id="specific_pages_box" style="margin-top: 14px; <?php echo $form_display === 'specific' ? '' : 'display: none;'; ?>">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                        <span style="font-size: 0.8rem; font-weight: 600; color: #475569;">CENTANG HALAMAN TARGET:</span>
                                        <div style="font-size: 0.75rem;">
                                            <span style="color: #0284c7; cursor: pointer; font-weight: 600;" onclick="checkAllPages(true)">Pilih Semua</span>
                                            <span style="color: #94a3b8; margin: 0 4px;">|</span>
                                            <span style="color: #ef4444; cursor: pointer; font-weight: 600;" onclick="checkAllPages(false)">Batal Semua</span>
                                        </div>
                                    </div>
                                    <div class="checklist-box">
                                        <?php foreach ($available_groups as $group_title => $group_pages): ?>
                                            <div class="checklist-group-title">
                                                <?php echo $group_title; ?>
                                            </div>
                                            <?php foreach ($group_pages as $p_slug => $p_name): 
                                                $is_checked = in_array($p_slug, $form_pages, true);
                                            ?>
                                                <label class="checklist-item">
                                                    <input type="checkbox" name="target_pages[]" value="<?php echo sanitize($p_slug); ?>" <?php echo $is_checked ? 'checked' : ''; ?>>
                                                    <span><?php echo sanitize($p_name); ?> <small style="color: #94a3b8;">(<?php echo $p_slug ? '/' . $p_slug . '/' : '/'; ?>)</small></span>
                                                </label>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Card: Frekuensi & Gap Durasi -->
                            <div class="admin-card">
                                <h3>4. Frekuensi Tampil & Gap Durasi</h3>
                                
                                <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 12px;">
                                    <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; font-size: 0.92rem;">
                                        <input type="radio" name="frequency" value="always" <?php echo $form_freq === 'always' ? 'checked' : ''; ?> onchange="toggleCustomDays(false)">
                                        <div>
                                            <strong>Tampilkan Terus-Menerus</strong>
                                            <div style="font-size: 0.8rem; color: #64748b;">Selalu muncul setiap kali halaman dibuka atau di-refresh.</div>
                                        </div>
                                    </label>

                                    <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; font-size: 0.92rem;">
                                        <input type="radio" name="frequency" value="session" <?php echo $form_freq === 'session' ? 'checked' : ''; ?> onchange="toggleCustomDays(false)">
                                        <div>
                                            <strong>1 Kali per Sesi Browser</strong>
                                            <div style="font-size: 0.8rem; color: #64748b;">Hanya muncul sekali. Tidak muncul lagi sampai user menutup dan membuka ulang tab browser.</div>
                                        </div>
                                    </label>

                                    <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; font-size: 0.92rem;">
                                        <input type="radio" name="frequency" value="daily" <?php echo $form_freq === 'daily' ? 'checked' : ''; ?> onchange="toggleCustomDays(false)">
                                        <div>
                                            <strong>Gap 1 Hari (24 Jam)</strong>
                                            <div style="font-size: 0.8rem; color: #64748b;">Setelah ditutup, banner tidak akan muncul lagi selama 24 jam untuk user tersebut.</div>
                                        </div>
                                    </label>

                                    <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; font-size: 0.92rem;">
                                        <input type="radio" name="frequency" value="custom" <?php echo $form_freq === 'custom' ? 'checked' : ''; ?> onchange="toggleCustomDays(true)">
                                        <div>
                                            <strong>Gap Durasi Kustom</strong>
                                            <div style="font-size: 0.8rem; color: #64748b;">Atur jumlah hari jeda sebelum banner ditampilkan kembali.</div>
                                        </div>
                                    </label>

                                    <div id="custom_days_input" style="margin-left: 26px; <?php echo $form_freq === 'custom' ? '' : 'display: none;'; ?>">
                                        <label class="form-label">Jumlah Hari Gap Durasi:</label>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <input type="number" name="frequency_days" class="form-control" value="<?php echo (int)$form_days; ?>" min="1" max="90" style="width: 100px;">
                                            <span>Hari</span>
                                        </div>
                                    </div>
                                </div>

                                <div style="margin-top: 20px; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                                    <label class="form-label">Delay Waktu Tampil (Detik) *</label>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <input type="number" name="delay_seconds" id="delay_seconds_input" class="form-control" value="<?php echo max(2, (int)$form_delay); ?>" min="2" max="30" style="width: 100px;" required onblur="if(this.value < 2) this.value = 2;">
                                        <span>Detik setelah halaman selesai dimuat</span>
                                    </div>
                                    <div class="form-help">Minimal 2 detik (tidak bisa diisi di bawah 2 detik) agar tidak mengganggu kecepatan muat dan mematuhi kebijakan Google Interstitial Ads.</div>
                                </div>
                            </div>

                            <!-- Card: Jadwal & Status -->
                            <div class="admin-card">
                                <h3>5. Pengaturan Tayang</h3>

                                <div style="margin-top: 16px;">
                                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 1rem; font-weight: 600;">
                                        <input type="checkbox" name="status" value="1" <?php echo $form_status ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer;">
                                        <span>Aktifkan Pop-up Banner Ini</span>
                                    </label>
                                    <div class="form-help" style="margin-left: 28px;">Jika tidak dicentang, banner disimpan sebagai draft dan tidak akan tampil ke pengunjung.</div>
                                </div>

                                <div style="margin-top: 20px; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                                    <label class="form-label">Jadwal Mulai Tayang (Opsional)</label>
                                    <input type="datetime-local" name="start_date" class="form-control" value="<?php echo sanitize($form_start); ?>">
                                </div>

                                <div style="margin-top: 14px;">
                                    <label class="form-label">Jadwal Selesai Tayang (Opsional)</label>
                                    <input type="datetime-local" name="end_date" class="form-control" value="<?php echo sanitize($form_end); ?>">
                                    <div class="form-help">Kosongkan jadwal jika ingin banner tayang terus menerus tanpa batas tanggal.</div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div style="display: flex; gap: 12px;">
                                <button type="submit" class="btn-admin btn-admin-primary" style="flex: 1; padding: 14px; font-size: 1rem; justify-content: center;">
                                    <i class="bi bi-check-circle"></i> <?php echo $id > 0 ? 'Simpan Perubahan' : 'Terbitkan Pop-up'; ?>
                                </button>
                                <a href="<?php echo BASE_URL; ?>admin/popups.php" class="btn-admin btn-admin-secondary" style="padding: 14px;">
                                    Batal
                                </a>
                            </div>

                        </div>

                    </div>
                </form>

                <script>
                    // Auto-slug generator from Title
                    const titleInput = document.getElementById('popup_title');
                    const slugInput = document.getElementById('popup_slug');
                    const slugPreviewText = document.getElementById('slug_preview_text');

                    if (titleInput && slugInput) {
                        titleInput.addEventListener('input', function() {
                            <?php if (empty($id)): ?>
                            const slugVal = this.value.toLowerCase()
                                .replace(/[^a-z0-9]+/g, '-')
                                .replace(/^-+|-+$/g, '');
                            slugInput.value = slugVal;
                            if (slugPreviewText) slugPreviewText.textContent = slugVal || 'promo-vo-jakarta';
                            <?php endif; ?>
                        });

                        slugInput.addEventListener('input', function() {
                            if (slugPreviewText) slugPreviewText.textContent = this.value || 'promo-vo-jakarta';
                        });
                    }

                    // Live Image Preview
                    const fileInput = document.getElementById('image_file_input');
                    const imgPreview = document.getElementById('image_preview');
                    const placeholder = document.getElementById('preview_placeholder');

                    if (fileInput && imgPreview) {
                        fileInput.addEventListener('change', function() {
                            const file = this.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    imgPreview.src = e.target.result;
                                    imgPreview.style.display = 'block';
                                    if (placeholder) placeholder.style.display = 'none';
                                };
                                reader.readAsDataURL(file);
                            }
                        });
                    }

                    // Toggle Specific Pages checklist
                    function toggleSpecificPages(show) {
                        const box = document.getElementById('specific_pages_box');
                        if (box) box.style.display = show ? 'block' : 'none';
                    }

                    // Toggle Custom Days input
                    function toggleCustomDays(show) {
                        const box = document.getElementById('custom_days_input');
                        if (box) box.style.display = show ? 'block' : 'none';
                    }

                    // Check all pages
                    function checkAllPages(check) {
                        document.querySelectorAll('#specific_pages_box input[type="checkbox"]').forEach(cb => cb.checked = check);
                    }

                    // Toggle Link Type (URL vs WhatsApp)
                    function toggleLinkType(type) {
                        const urlSec = document.getElementById('section_link_url');
                        const waSec = document.getElementById('section_link_whatsapp');
                        if (urlSec && waSec) {
                            if (type === 'whatsapp') {
                                urlSec.style.display = 'none';
                                waSec.style.display = 'block';
                                updateWaPreview();
                            } else {
                                urlSec.style.display = 'block';
                                waSec.style.display = 'none';
                            }
                        }
                    }

                    // Live update WhatsApp link preview and test button
                    function updateWaPreview() {
                        const numInput = document.getElementById('wa_number_input');
                        const msgInput = document.getElementById('wa_message_input');
                        const previewEl = document.getElementById('wa_preview_link');
                        const testWebBtn = document.getElementById('wa_test_web_btn');
                        const testAppBtn = document.getElementById('wa_test_app_btn');
                        if (!numInput || !previewEl) return;

                        let phone = numInput.value.replace(/[^0-9]/g, '');
                        if (phone.startsWith('0')) {
                            phone = '62' + phone.substring(1);
                        }
                        if (!phone) phone = '6285107620100';

                        let msg = msgInput ? msgInput.value.trim() : '';
                        let encMsg = msg ? encodeURIComponent(msg) : '';

                        let waWebUrl = 'https://web.whatsapp.com/send?phone=' + phone + (encMsg ? '&text=' + encMsg : '');
                        let waAppUrl = 'https://wa.me/' + phone + (encMsg ? '?text=' + encMsg : '');

                        previewEl.textContent = waWebUrl;
                        if (testWebBtn) testWebBtn.href = waWebUrl;
                        if (testAppBtn) testAppBtn.href = waAppUrl;
                    }

                    function insertTitleToWa() {
                        const titleVal = (document.getElementById('popup_title')?.value || '').trim();
                        const msgInput = document.getElementById('wa_message_input');
                        if (msgInput) {
                            if (titleVal) {
                                msgInput.value = `Halo Tim Urban Office, saya tertarik dengan promo "${titleVal}" yang ada di website. Boleh minta info detail dan ketentuannya?`;
                            } else {
                                msgInput.value = `Halo Tim Urban Office, saya tertarik dengan promo yang ada di website. Boleh minta info detail dan ketentuannya?`;
                            }
                            updateWaPreview();
                        }
                    }

                    const waNumEl = document.getElementById('wa_number_input');
                    const waMsgEl = document.getElementById('wa_message_input');
                    if (waNumEl) waNumEl.addEventListener('input', updateWaPreview);
                    if (waMsgEl) waMsgEl.addEventListener('input', updateWaPreview);
                    updateWaPreview();
                </script>
            <?php endif; ?>

        </main>
    </div>
</body>
</html>
