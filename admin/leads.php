<?php
/**
 * Admin Lead Management and Export
 */

require_once __DIR__ . '/auth.php';

$error = '';
$success = '';

// Handle AJAX or POST status changes
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    try {
        if ($action === 'mark_read') {
            Database::query("UPDATE leads SET status = 'read' WHERE id = ?", [$id]);
            log_activity($_SESSION['admin_user_id'], 'lead_status', "Marked lead ID $id as read.");
            header('Location: ' . BASE_URL . 'admin/leads.php?success=1');
            exit;
        } elseif ($action === 'mark_processed') {
            Database::query("UPDATE leads SET status = 'processed' WHERE id = ?", [$id]);
            log_activity($_SESSION['admin_user_id'], 'lead_status', "Marked lead ID $id as processed.");
            header('Location: ' . BASE_URL . 'admin/leads.php?success=2');
            exit;
        } elseif ($action === 'delete') {
            Database::query("DELETE FROM leads WHERE id = ?", [$id]);
            log_activity($_SESSION['admin_user_id'], 'lead_delete', "Deleted lead entry ID $id.");
            header('Location: ' . BASE_URL . 'admin/leads.php?success=3');
            exit;
        }
    } catch (Exception $e) {
        $error = 'Gagal memproses aksi lead: ' . $e->getMessage();
    }
}

// Action: EXPORT TO CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    try {
        $leads = Database::fetchAll("SELECT created_at, name, email, phone, service, message, ip_address, status FROM leads ORDER BY created_at DESC");
        
        // Clear buffer and write file headers
        ob_end_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=leads_urban_office_' . date('Ymd_His') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Add CSV Headers (BOM for Excel compatibility)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['Tanggal Terdaftar', 'Nama Lengkap', 'Alamat Email', 'Nomor HP/WhatsApp', 'Layanan Diminati', 'Pesan', 'IP Address', 'Status']);
        
        foreach ($leads as $l) {
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

// Filter status
$filter = isset($_GET['status']) ? trim($_GET['status']) : '';

try {
    if (!empty($filter)) {
        $total_rows = Database::fetch("SELECT COUNT(*) as total FROM leads WHERE status = ?", [$filter])['total'];
        $leads = Database::fetchAll("SELECT * FROM leads WHERE status = ? ORDER BY created_at DESC LIMIT ? OFFSET ?", [$filter, $limit, $offset]);
    } else {
        $total_rows = Database::fetch("SELECT COUNT(*) as total FROM leads")['total'];
        $leads = Database::fetchAll("SELECT * FROM leads ORDER BY created_at DESC LIMIT ? OFFSET ?", [$limit, $offset]);
    }
    $total_pages = ceil($total_rows / $limit);
} catch (Exception $e) {
    error_log("Failed pulling leads: " . $e->getMessage());
    $leads = [];
    $total_pages = 1;
}

if (isset($_GET['success'])) {
    if ($_GET['success'] == 1) $success = 'Lead ditandai sebagai dibaca.';
    if ($_GET['success'] == 2) $success = 'Lead ditandai sebagai diproses.';
    if ($_GET['success'] == 3) $success = 'Lead berhasil dihapus.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Leads - Urban Office</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        
        <!-- Sidebar Navigation Menu -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Workspace Area -->
        <main class="admin-main">
            <div class="admin-top">
                <h1 class="admin-title">Pendaftaran Leads Capture</h1>
                <div>
                    <a href="?export=csv" class="btn-admin btn-admin-primary">Ekspor ke Excel/CSV</a>
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

            <!-- Filter Options bar -->
            <div class="admin-card" style="padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; gap: 8px;">
                    <a href="leads.php" class="btn-admin <?php echo empty($filter) ? 'btn-admin-primary' : 'btn-admin-secondary'; ?> btn-admin-sm">Semua</a>
                    <a href="?status=unread" class="btn-admin <?php echo $filter === 'unread' ? 'btn-admin-primary' : 'btn-admin-secondary'; ?> btn-admin-sm">Belum Dibaca</a>
                    <a href="?status=read" class="btn-admin <?php echo $filter === 'read' ? 'btn-admin-primary' : 'btn-admin-secondary'; ?> btn-admin-sm">Dibaca</a>
                    <a href="?status=processed" class="btn-admin <?php echo $filter === 'processed' ? 'btn-admin-primary' : 'btn-admin-secondary'; ?> btn-admin-sm">Diproses</a>
                </div>
                <div style="font-size: 0.9rem; color:#64748b;">
                    Total Data: <strong><?php echo $total_rows; ?></strong> leads.
                </div>
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
                            <?php if (!empty($leads)): foreach ($leads as $l): ?>
                                <tr style="background-color: <?php echo $l['status'] === 'unread' ? '#fffbeb' : 'inherit'; ?>;">
                                    <td style="white-space: nowrap;">
                                        <?php echo date('d M Y H:i', strtotime($l['created_at'])); ?><br>
                                        <span style="font-size:0.75rem; color:#94a3b8;">IP: <?php echo sanitize($l['ip_address']); ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo sanitize($l['name']); ?></strong><br>
                                        <span style="font-size:0.85rem; color:#475569;">WA/Telp: <strong><?php echo sanitize($l['phone']); ?></strong></span><br>
                                        <span style="font-size:0.85rem; color:#64748b;">Email: <?php echo sanitize($l['email']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?php echo sanitize($l['service']); ?></span>
                                    </td>
                                    <td>
                                        <div style="max-width: 300px; font-size:0.85rem; line-height: 1.4; color: #475569;">
                                            <?php echo nl2br(sanitize($l['message'])); ?>
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
                                    <td style="white-space: nowrap;">
                                        <?php if ($l['status'] === 'unread'): ?>
                                            <a href="?action=mark_read&id=<?php echo $l['id']; ?>" class="btn-admin btn-admin-secondary btn-admin-sm" style="color: #d97706; border-color: #fcd34d;">Mark Read</a>
                                        <?php endif; ?>
                                        <?php if ($l['status'] !== 'processed'): ?>
                                            <a href="?action=mark_processed&id=<?php echo $l['id']; ?>" class="btn-admin btn-admin-secondary btn-admin-sm" style="color: #16a34a; border-color: #86efac;">Mark Done</a>
                                        <?php endif; ?>
                                        
                                        <!-- WhatsApp Follow Up button directly from admin panel -->
                                        <?php
                                        $waText = "Halo " . $l['name'] . ", terima kasih telah menghubungi Urban Office perihal " . $l['service'] . ".";
                                        $waUrl = "https://api.whatsapp.com/send/?phone=" . preg_replace('/[^0-9]/', '', $l['phone']) . "&text=" . urlencode($waText);
                                        ?>
                                        <a href="<?php echo $waUrl; ?>" target="_blank" class="btn-admin btn-admin-primary btn-admin-sm" style="background-color: #25D366; color:white;">WA Follow-up</a>
                                        
                                        <a href="?action=delete&id=<?php echo $l['id']; ?>" class="btn-admin btn-admin-danger btn-admin-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus leads ini?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #94a3b8;">Belum ada leads yang terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div style="display: flex; gap: 6px; justify-content: center; margin-top: 30px;">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($filter) ? '&status='.urlencode($filter) : ''; ?>" class="btn-admin <?php echo $i === $page ? 'btn-admin-primary' : 'btn-admin-secondary'; ?> btn-admin-sm"><?php echo $i; ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</body>
</html>
