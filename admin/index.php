<?php
/**
 * Admin Panel Login and Dashboard Overview
 */

require_once dirname(dirname(__FILE__)) . '/inc/config.php';
require_once dirname(dirname(__FILE__)) . '/inc/database.php';
require_once dirname(dirname(__FILE__)) . '/inc/functions.php';

$error = '';

// Handle Login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi token CSRF gagal.';
    } else {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        try {
            $user = Database::fetch("SELECT * FROM users WHERE username = ? AND status = 1", [$username]);
            if ($user && password_verify($password, $user['password'])) {
                // Set Session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_role'] = $user['role'];

                log_activity($user['id'], 'login', "User $username logged in successfully.");
                header('Location: ' . BASE_URL . 'admin/index.php');
                exit;
            } else {
                $error = 'Username atau Password salah.';
                log_activity(null, 'login_failed', "Failed login attempt for username: " . sanitize($username));
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Terjadi kesalahan sistem.';
        }
    }
}

// Action: Clear Cache
if (is_admin() && isset($_GET['action']) && $_GET['action'] === 'clear_cache') {
    clear_page_cache();
    log_activity($_SESSION['admin_user_id'], 'clear_cache', "Admin cleared website HTML page cache.");
    header('Location: ' . BASE_URL . 'admin/index.php?cached_cleared=1');
    exit;
}

// Render Dashboard if Admin is logged in
if (is_admin()):
    try {
        $count_posts = Database::fetch("SELECT COUNT(*) as total FROM posts")['total'];
        $count_leads = Database::fetch("SELECT COUNT(*) as total FROM leads")['total'];
        $count_cats = Database::fetch("SELECT COUNT(*) as total FROM categories")['total'];
        $count_tags = Database::fetch("SELECT COUNT(*) as total FROM tags")['total'];
        
        // Fetch 5 latest leads
        $recent_leads = Database::fetchAll("SELECT * FROM leads ORDER BY created_at DESC LIMIT 5");
    } catch (Exception $e) {
        error_log("Failed pulling dashboard metrics: " . $e->getMessage());
        $count_posts = $count_leads = $count_cats = $count_tags = 0;
        $recent_leads = [];
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Urban Office</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        
        <!-- Sidebar Navigation Menu -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Workspace Area -->
        <main class="admin-main">
            <div class="admin-top">
                <h1 class="admin-title">Dashboard Overview</h1>
                <div>
                    Selamat datang, <strong><?php echo sanitize($_SESSION['admin_username']); ?></strong>
                </div>
            </div>

            <?php if (isset($_GET['cached_cleared'])): ?>
                <div style="background-color: #dcfce7; color: #15803d; padding: 12px 20px; border-radius: 6px; margin-bottom: 24px; font-weight: 500;">
                    ✓ Cache halaman berhasil dibersihkan!
                </div>
            <?php endif; ?>

            <!-- Metrics Statistics Cards -->
            <div class="metric-grid">
                <div class="metric-card">
                    <div class="metric-label">Total Posts</div>
                    <div class="metric-val"><?php echo $count_posts; ?></div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Total Leads</div>
                    <div class="metric-val"><?php echo $count_leads; ?></div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Kategori</div>
                    <div class="metric-val"><?php echo $count_cats; ?></div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Tag Blog</div>
                    <div class="metric-val"><?php echo $count_tags; ?></div>
                </div>
            </div>

            <!-- Quick Action Options -->
            <div class="admin-card">
                <h3 style="margin-bottom: 16px;">Aksi Cepat</h3>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="<?php echo BASE_URL; ?>admin/posts.php?action=new" class="btn-admin btn-admin-primary">Tulis Artikel Baru</a>
                    <a href="<?php echo BASE_URL; ?>admin/leads.php" class="btn-admin btn-admin-secondary">Lihat Semua Leads</a>
                    <a href="<?php echo BASE_URL; ?>admin/index.php?action=clear_cache" class="btn-admin btn-admin-secondary" style="color:#ef4444; border-color:#fca5a5;">Clear HTML Cache</a>
                    <a href="<?php echo BASE_URL; ?>" target="_blank" class="btn-admin btn-admin-secondary">Lihat Website</a>
                </div>
            </div>

            <!-- Recent Submissions leads captured -->
            <div class="admin-card">
                <h3 style="margin-bottom: 16px;">Pendaftaran Leads Terbaru</h3>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama</th>
                                <th>WhatsApp / HP</th>
                                <th>Service</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_leads)): foreach ($recent_leads as $lead): ?>
                                <tr>
                                    <td><?php echo date('d M Y H:i', strtotime($lead['created_at'])); ?></td>
                                    <td><strong><?php echo sanitize($lead['name']); ?></strong></td>
                                    <td><?php echo sanitize($lead['phone']); ?></td>
                                    <td><span class="badge badge-info"><?php echo sanitize($lead['service']); ?></span></td>
                                    <td>
                                        <span class="badge <?php echo $lead['status'] === 'unread' ? 'badge-danger' : 'badge-success'; ?>">
                                            <?php echo sanitize($lead['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #94a3b8;">Belum ada leads masuk.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</body>
</html>

<?php else: ?>

<!-- Login Screen Panel -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Urban Office</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>
<body class="admin-body" style="background-color: #0f172a; display: flex; align-items: center; min-height: 100vh;">
    <div class="login-card">
        <h2 style="text-align: center; margin-bottom: 10px; font-family: 'Outfit', sans-serif;">URBAN OFFICE</h2>
        <p style="text-align: center; color:#64748b; margin-bottom: 30px;">Silakan login ke panel manajemen</p>
        
        <?php if (!empty($error)): ?>
            <div style="background-color: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; text-align: center; font-weight: 500;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <!-- CSRF Field -->
            <?php echo csrf_field(); ?>

            <div style="margin-bottom: 20px;">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="admin" required autofocus style="background-color: #f8fafc; border: 1.5px solid #cbd5e1; width:100%; padding:10px 14px; border-radius:6px;">
            </div>
            
            <div style="margin-bottom: 30px;">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required style="background-color: #f8fafc; border: 1.5px solid #cbd5e1; width:100%; padding:10px 14px; border-radius:6px;">
            </div>

            <button type="submit" name="login" class="btn-admin btn-admin-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem; border-radius:6px;">
                Masuk Dashboard
            </button>
        </form>
    </div>
</body>
</html>
<?php endif; ?>
