<?php
/**
 * Admin Security Audit logs
 */

require_once __DIR__ . '/auth.php';

// Pagination setup
$limit = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

try {
    $total_logs = Database::fetch("SELECT COUNT(*) as total FROM activity_logs")['total'];
    $total_pages = ceil($total_logs / $limit);
    
    $logs = Database::fetchAll(
        "SELECT l.*, u.username FROM activity_logs l 
         LEFT JOIN users u ON l.user_id = u.id 
         ORDER BY l.created_at DESC LIMIT ? OFFSET ?",
        [$limit, $offset]
    );
} catch (Exception $e) {
    error_log("Failed pulling logs: " . $e->getMessage());
    $logs = [];
    $total_pages = 1;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - Urban Office</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        
        <!-- Sidebar Navigation Menu -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Workspace Area -->
        <main class="admin-main">
            <div class="admin-top">
                <h1 class="admin-title">Log Aktivitas (Security Audit Trail)</h1>
            </div>

            <div class="admin-card">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>User</th>
                                <th>Aksi</th>
                                <th>Keterangan</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($logs)): foreach ($logs as $log): ?>
                                <tr>
                                    <td style="white-space: nowrap;"><?php echo date('d M Y H:i:s', strtotime($log['created_at'])); ?></td>
                                    <td>
                                        <strong><?php echo sanitize($log['username'] ?: 'Sistem/Guest'); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge <?php 
                                            if (strpos($log['action'], 'delete') !== false || strpos($log['action'], 'failed') !== false) echo 'badge-danger';
                                            elseif (strpos($log['action'], 'create') !== false) echo 'badge-success';
                                            elseif (strpos($log['action'], 'update') !== false) echo 'badge-info';
                                            else echo 'badge-warning';
                                        ?>">
                                            <?php echo sanitize($log['action']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo sanitize($log['details']); ?></td>
                                    <td><code><?php echo sanitize($log['ip_address']); ?></code></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #94a3b8;">Belum ada log tercatat.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div style="display: flex; gap: 6px; justify-content: center; margin-top: 30px;">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="btn-admin <?php echo $i === $page ? 'btn-admin-primary' : 'btn-admin-secondary'; ?> btn-admin-sm"><?php echo $i; ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</body>
</html>
