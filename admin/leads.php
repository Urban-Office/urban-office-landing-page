<?php
/**
 * Admin Lead Management and Export
 * Includes search, status filtering, normalized WhatsApp follow-up, CSRF-protected actions, and CSV export.
 */

require_once __DIR__ . '/auth.php';

$error = '';
$success = '';

/**
 * Normalize Indonesian phone numbers (08... -> 628...) for WhatsApp links.
 */
function leads_format_wa_phone(string $phone): string {
    $digits = preg_replace('/[^0-9]/', '', $phone);
    if ($digits === '') {
        return '';
    }
    if (strpos($digits, '0') === 0) {
        return '62' . substr($digits, 1);
    }
    if (strpos($digits, '8') === 0) {
        return '62' . $digits;
    }
    return $digits;
}

// Handle CSRF-protected POST status changes & deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lead_action'], $_POST['lead_id'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi token CSRF gagal. Silakan muat ulang halaman.';
    } else {
        $id = intval($_POST['lead_id']);
        $lead_action = trim((string) $_POST['lead_action']);
        $redirect_q = [];
        if (!empty($_POST['return_status'])) $redirect_q['status'] = $_POST['return_status'];
        if (!empty($_POST['return_q']))      $redirect_q['q']      = $_POST['return_q'];
        if (!empty($_POST['return_page']) && intval($_POST['return_page']) > 1) {
            $redirect_q['page'] = intval($_POST['return_page']);
        }

        try {
            if ($lead_action === 'mark_read') {
                Database::query("UPDATE leads SET status = 'read' WHERE id = ?", [$id]);
                log_activity($_SESSION['admin_user_id'], 'lead_status', "Marked lead ID $id as read.");
                $redirect_q['success'] = 1;
            } elseif ($lead_action === 'mark_processed') {
                Database::query("UPDATE leads SET status = 'processed' WHERE id = ?", [$id]);
                log_activity($_SESSION['admin_user_id'], 'lead_status', "Marked lead ID $id as processed.");
                $redirect_q['success'] = 2;
            } elseif ($lead_action === 'delete') {
                Database::query("DELETE FROM leads WHERE id = ?", [$id]);
                log_activity($_SESSION['admin_user_id'], 'lead_delete', "Deleted lead entry ID $id.");
                $redirect_q['success'] = 3;
            }
            header('Location: ' . BASE_URL . 'admin/leads.php?' . http_build_query($redirect_q));
            exit;
        } catch (Exception $e) {
            $error = 'Gagal memproses aksi lead: ' . $e->getMessage();
        }
    }
}

// Filter & search parameters
$filter = isset($_GET['status']) ? trim((string) $_GET['status']) : '';
if (!in_array($filter, ['', 'unread', 'read', 'processed'], true)) {
    $filter = '';
}
$search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

$where_parts = [];
$where_params = [];
if ($filter !== '') {
    $where_parts[] = 'status = ?';
    $where_params[] = $filter;
}
if ($search !== '') {
    $where_parts[] = '(name LIKE ? OR phone LIKE ? OR email LIKE ? OR service LIKE ? OR message LIKE ?)';
    $like = '%' . $search . '%';
    array_push($where_params, $like, $like, $like, $like, $like);
}
$where_sql = !empty($where_parts) ? ('WHERE ' . implode(' AND ', $where_parts)) : '';

// Action: EXPORT TO CSV (respects active status/search filter)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    try {
        $leads_export = Database::fetchAll(
            "SELECT created_at, name, email, phone, service, message, ip_address, status
             FROM leads {$where_sql}
             ORDER BY created_at DESC",
            $where_params
        );

        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=leads_urban_office_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['Tanggal Terdaftar', 'Nama Lengkap', 'Alamat Email', 'Nomor HP/WhatsApp', 'Layanan Diminati', 'Pesan', 'IP Address', 'Status']);

        foreach ($leads_export as $l) {
            fputcsv($output, [
                date('Y-m-d H:i:s', strtotime($l['created_at'])),
                $l['name'],
                $l['email'],
                $l['phone'],
                $l['service'],
                $l['message'],
                $l['ip_address'],
                $l['status']
            ]);
        }
        fclose($output);
        exit;
    } catch (Exception $e) {
        $error = 'Ekspor CSV gagal: ' . $e->getMessage();
    }
}

// Fetch leads with pagination
$limit = 15;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

try {
    $total_rows = (int) (Database::fetch("SELECT COUNT(*) AS total FROM leads {$where_sql}", $where_params)['total'] ?? 0);
    $leads = Database::fetchAll(
        "SELECT * FROM leads {$where_sql} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}",
        $where_params
    );
    $total_pages = max(1, (int) ceil($total_rows / $limit));
} catch (Exception $e) {
    error_log("Failed pulling leads: " . $e->getMessage());
    $leads = [];
    $total_rows = 0;
    $total_pages = 1;
}

if (isset($_GET['success'])) {
    if ($_GET['success'] == 1) $success = 'Lead ditandai sebagai dibaca.';
    if ($_GET['success'] == 2) $success = 'Lead ditandai sebagai diproses.';
    if ($_GET['success'] == 3) $success = 'Lead berhasil dihapus.';
}

$build_leads_url = static function (array $overrides = []) use ($filter, $search): string {
    $q = array_merge(['status' => $filter, 'q' => $search], $overrides);
    $q = array_filter($q, static fn($v) => $v !== '' && $v !== null);
    return BASE_URL . 'admin/leads.php' . (!empty($q) ? ('?' . http_build_query($q)) : '');
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Leads - Urban Office CMS</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
    <style>
        .leads-search-form {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .leads-search-input {
            height: 35px;
            width: 240px;
            max-width: 100%;
            padding: 0 11px;
            border: 1px solid var(--admin-border-strong);
            border-radius: var(--admin-radius-sm);
            font-family: inherit;
            font-size: 0.83rem;
            color: var(--admin-ink);
            background: var(--admin-surface);
        }
        .leads-search-input:focus {
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 3px var(--admin-ring);
            outline: none;
        }
        .leads-actions-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .leads-actions-wrap form {
            display: inline-flex;
            margin: 0;
        }
        .btn-wa-follow {
            background: #059669;
            color: #ffffff;
            border-color: #059669;
        }
        .btn-wa-follow:hover {
            background: #047857;
            border-color: #047857;
            color: #ffffff;
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="admin-main">
            <div class="admin-top">
                <div>
                    <h1 class="admin-title">Pendaftaran Leads Capture</h1>
                    <p class="admin-subtitle">Kelola prospek formulir website, filter status, dan follow-up cepat via WhatsApp.</p>
                </div>
                <div>
                    <a href="<?php echo sanitize($build_leads_url(['export' => 'csv'])); ?>" class="btn-admin btn-admin-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Ekspor CSV
                    </a>
                </div>
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

            <!-- Filter & Search Toolbar -->
            <div class="admin-toolbar">
                <div class="admin-pill-group" role="navigation" aria-label="Filter status leads">
                    <a href="<?php echo sanitize($build_leads_url(['status' => '', 'page' => ''])); ?>" class="admin-pill<?php echo $filter === '' ? ' active' : ''; ?>">Semua</a>
                    <a href="<?php echo sanitize($build_leads_url(['status' => 'unread', 'page' => ''])); ?>" class="admin-pill<?php echo $filter === 'unread' ? ' active' : ''; ?>">Belum Dibaca</a>
                    <a href="<?php echo sanitize($build_leads_url(['status' => 'read', 'page' => ''])); ?>" class="admin-pill<?php echo $filter === 'read' ? ' active' : ''; ?>">Dibaca</a>
                    <a href="<?php echo sanitize($build_leads_url(['status' => 'processed', 'page' => ''])); ?>" class="admin-pill<?php echo $filter === 'processed' ? ' active' : ''; ?>">Diproses</a>
                </div>

                <form method="GET" action="<?php echo BASE_URL; ?>admin/leads.php" class="leads-search-form">
                    <?php if ($filter !== ''): ?>
                        <input type="hidden" name="status" value="<?php echo sanitize($filter); ?>">
                    <?php endif; ?>
                    <input type="search" name="q" value="<?php echo sanitize($search); ?>" class="leads-search-input" placeholder="Cari nama, WA, email, layanan..." aria-label="Cari leads">
                    <button type="submit" class="btn-admin btn-admin-primary btn-admin-sm" style="height: 35px; padding: 0 12px;">Cari</button>
                    <?php if ($search !== ''): ?>
                        <a href="<?php echo sanitize($build_leads_url(['q' => '', 'page' => ''])); ?>" class="btn-admin btn-admin-secondary btn-admin-sm" style="height: 35px; padding: 0 10px;">Reset</a>
                    <?php endif; ?>
                    <span class="admin-num" style="font-size: 0.8rem; color: var(--admin-muted); margin-left: 4px;">
                        Total: <strong><?php echo number_format($total_rows); ?></strong>
                    </span>
                </form>
            </div>

            <!-- Leads Entries Table -->
            <div class="admin-card">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Tanggal Masuk</th>
                                <th>Detail Pelanggan</th>
                                <th>Produk / Service</th>
                                <th>Pesan Klien</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($leads)): foreach ($leads as $l):
                                $wa_phone = leads_format_wa_phone((string) $l['phone']);
                                $wa_text = "Halo " . $l['name'] . ", terima kasih telah menghubungi Urban Office perihal " . $l['service'] . ".";
                                $wa_url = $wa_phone !== '' ? ("https://api.whatsapp.com/send/?phone=" . $wa_phone . "&text=" . rawurlencode($wa_text)) : '';
                            ?>
                                <tr style="background-color: <?php echo $l['status'] === 'unread' ? '#fffbeb' : 'inherit'; ?>;">
                                    <td style="white-space: nowrap;">
                                        <span class="admin-num" style="font-size: 0.8rem; color: var(--admin-ink);"><?php echo date('d M Y H:i', strtotime($l['created_at'])); ?></span><br>
                                        <span class="admin-num" style="font-size: 0.72rem; color: var(--admin-subtle);">IP: <?php echo sanitize($l['ip_address']); ?></span>
                                    </td>
                                    <td>
                                        <strong style="color: var(--admin-ink);"><?php echo sanitize($l['name']); ?></strong><br>
                                        <span style="font-size: 0.82rem; color: var(--admin-ink-2);">WA/Telp: <strong class="admin-num"><?php echo sanitize($l['phone']); ?></strong></span><br>
                                        <?php if (!empty($l['email'])): ?>
                                            <span style="font-size: 0.8rem; color: var(--admin-subtle);"><?php echo sanitize($l['email']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?php echo sanitize($l['service']); ?></span>
                                    </td>
                                    <td>
                                        <div style="max-width: 280px; font-size: 0.83rem; line-height: 1.45; color: var(--admin-ink-2);">
                                            <?php echo $l['message'] !== '' ? nl2br(sanitize($l['message'])) : '<span style="color: var(--admin-subtle);">—</span>'; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?php
                                            if ($l['status'] === 'unread') echo 'badge-danger';
                                            elseif ($l['status'] === 'read') echo 'badge-warning';
                                            else echo 'badge-success';
                                        ?>">
                                            <?php echo sanitize($l['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="leads-actions-wrap">
                                            <?php if ($l['status'] === 'unread'): ?>
                                                <form method="POST" action="<?php echo BASE_URL; ?>admin/leads.php">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="lead_id" value="<?php echo (int) $l['id']; ?>">
                                                    <input type="hidden" name="lead_action" value="mark_read">
                                                    <input type="hidden" name="return_status" value="<?php echo sanitize($filter); ?>">
                                                    <input type="hidden" name="return_q" value="<?php echo sanitize($search); ?>">
                                                    <input type="hidden" name="return_page" value="<?php echo $page; ?>">
                                                    <button type="submit" class="btn-admin btn-admin-secondary btn-admin-sm" style="color: #a16207; border-color: #fde68a; background: #fefce8;">Mark Read</button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($l['status'] !== 'processed'): ?>
                                                <form method="POST" action="<?php echo BASE_URL; ?>admin/leads.php">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="lead_id" value="<?php echo (int) $l['id']; ?>">
                                                    <input type="hidden" name="lead_action" value="mark_processed">
                                                    <input type="hidden" name="return_status" value="<?php echo sanitize($filter); ?>">
                                                    <input type="hidden" name="return_q" value="<?php echo sanitize($search); ?>">
                                                    <input type="hidden" name="return_page" value="<?php echo $page; ?>">
                                                    <button type="submit" class="btn-admin btn-admin-secondary btn-admin-sm" style="color: #047857; border-color: #a7f3d0; background: #ecfdf5;">Mark Done</button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($wa_url !== ''): ?>
                                                <a href="<?php echo sanitize($wa_url); ?>" target="_blank" rel="noopener" class="btn-admin btn-wa-follow btn-admin-sm">WA Follow-up</a>
                                            <?php endif; ?>

                                            <form method="POST" action="<?php echo BASE_URL; ?>admin/leads.php" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lead ini?')">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="lead_id" value="<?php echo (int) $l['id']; ?>">
                                                <input type="hidden" name="lead_action" value="delete">
                                                <input type="hidden" name="return_status" value="<?php echo sanitize($filter); ?>">
                                                <input type="hidden" name="return_q" value="<?php echo sanitize($search); ?>">
                                                <input type="hidden" name="return_page" value="<?php echo $page; ?>">
                                                <button type="submit" class="btn-admin btn-admin-danger btn-admin-sm">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" class="admin-empty">
                                        <?php echo $search !== '' ? 'Tidak ada leads yang cocok dengan pencarian Anda.' : 'Belum ada leads yang terdaftar.'; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="admin-pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="<?php echo sanitize($build_leads_url(['page' => $i])); ?>" class="btn-admin <?php echo $i === $page ? 'btn-admin-primary' : 'btn-admin-secondary'; ?> btn-admin-sm"><?php echo $i; ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
