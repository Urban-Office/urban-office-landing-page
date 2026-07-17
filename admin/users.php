<?php
/**
 * Admin User Management CRUD
 */

require_once __DIR__ . '/auth.php';

// Enforce that only logged in administrators can access user management
if ($_SESSION['admin_role'] !== 'administrator') {
    header('Location: ' . BASE_URL . 'admin/index.php');
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = '';
$success = '';

// Handle Create User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);
        $status = intval($_POST['status']);

        if (empty($username) || empty($email) || empty($password)) {
            $error = 'Semua kolom bertanda * wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } else {
            try {
                // Check duplicate username and email
                $check_username = Database::fetch("SELECT id FROM users WHERE username = ?", [$username]);
                $check_email = Database::fetch("SELECT id FROM users WHERE email = ?", [$email]);

                if ($check_username) {
                    $error = 'Username sudah digunakan oleh user lain.';
                } elseif ($check_email) {
                    $error = 'Email sudah digunakan oleh user lain.';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    Database::insert(
                        "INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, ?, ?)",
                        [$username, $hashed_password, $email, $role, $status]
                    );
                    log_activity($_SESSION['admin_user_id'], 'create_user', "Created user account: " . sanitize($username) . " ($role)");
                    $success = 'User baru berhasil ditambahkan!';
                }
            } catch (Exception $e) {
                $error = 'Gagal menyimpan user: ' . $e->getMessage();
            }
        }
    }
}

// Handle Update User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $id = intval($_POST['id']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);
        $status = intval($_POST['status']);

        // Prevent admin from deactivating or demoting themselves
        if ($id === $_SESSION['admin_user_id']) {
            $role = 'administrator';
            $status = 1;
        }

        if (empty($username) || empty($email)) {
            $error = 'Kolom Username dan Email wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } else {
            try {
                // Check duplicates excluding current user
                $check_username = Database::fetch("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $id]);
                $check_email = Database::fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $id]);

                if ($check_username) {
                    $error = 'Username sudah digunakan oleh user lain.';
                } elseif ($check_email) {
                    $error = 'Email sudah digunakan oleh user lain.';
                } else {
                    if (!empty($password)) {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        Database::query(
                            "UPDATE users SET username = ?, email = ?, password = ?, role = ?, status = ? WHERE id = ?",
                            [$username, $email, $hashed_password, $role, $status, $id]
                        );
                    } else {
                        Database::query(
                            "UPDATE users SET username = ?, email = ?, role = ?, status = ? WHERE id = ?",
                            [$username, $email, $role, $status, $id]
                        );
                    }
                    
                    // If current logged-in user updated their own username, update session
                    if ($id === $_SESSION['admin_user_id']) {
                        $_SESSION['admin_username'] = $username;
                    }

                    log_activity($_SESSION['admin_user_id'], 'update_user', "Updated user account: " . sanitize($username));
                    header('Location: ' . BASE_URL . 'admin/users.php?success=updated');
                    exit;
                }
            } catch (Exception $e) {
                $error = 'Gagal memperbarui user: ' . $e->getMessage();
            }
        }
    }
}

// Handle User Deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id === $_SESSION['admin_user_id']) {
        $error = 'Anda tidak dapat menghapus akun Anda sendiri!';
    } else {
        try {
            $user = Database::fetch("SELECT username FROM users WHERE id = ?", [$id]);
            if ($user) {
                Database::query("DELETE FROM users WHERE id = ?", [$id]);
                log_activity($_SESSION['admin_user_id'], 'delete_user', "Deleted user account ID: $id (" . sanitize($user['username']) . ")");
                $success = 'User berhasil dihapus!';
            }
        } catch (Exception $e) {
            $error = 'Gagal menghapus user: ' . $e->getMessage();
        }
    }
}

// Fetch user for editing
$user_data = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $user_data = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
    if (!$user_data) {
        header('Location: ' . BASE_URL . 'admin/users.php');
        exit;
    }
}

// Fetch all users list
try {
    $users = Database::fetchAll("SELECT * FROM users ORDER BY username ASC");
} catch (Exception $e) {
    error_log("Users fetch failed: " . $e->getMessage());
    $users = [];
}

if (isset($_GET['success']) && $_GET['success'] === 'updated') {
    $success = 'User berhasil diperbarui!';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Urban Office</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        
        <!-- Sidebar Navigation Menu -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Workspace Area -->
        <main class="admin-main">
            <div class="admin-top">
                <h1 class="admin-title">Kelola User Admin / Editor</h1>
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

            <?php if ($action === 'edit' && $user_data): ?>
                <!-- Edit User Mode -->
                <div class="admin-card" style="max-width: 600px; margin: 0 auto;">
                    <h3>Edit User: <?php echo sanitize($user_data['username']); ?></h3>
                    <form action="" method="POST" style="margin-top: 20px;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo $user_data['id']; ?>">
                        
                        <div style="margin-bottom: 16px;">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control" value="<?php echo sanitize($user_data['username']); ?>" required style="background-color: white; border:1px solid #cbd5e1; width:100%;">
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" value="<?php echo sanitize($user_data['email']); ?>" required style="background-color: white; border:1px solid #cbd5e1; width:100%;">
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label class="form-label">Password Baru (Biarkan kosong jika tidak ingin mengganti)</label>
                            <div style="position: relative; display: flex; align-items: center;">
                                <input type="password" id="edit-password" name="password" class="form-control" placeholder="Isi hanya jika ingin mengganti password" style="background-color: white; border:1px solid #cbd5e1; width:100%; padding-right: 40px;">
                                <button type="button" onclick="togglePassword('edit-password', this)" style="position: absolute; right: 10px; background: none; border: none; cursor: pointer; color: #64748b; display: flex; align-items: center; justify-content: center; height: 100%; padding: 0 5px; z-index: 10;">
                                    <i class="bi bi-eye-slash" style="font-size: 1.15rem; margin: 0;"></i>
                                </button>
                            </div>
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-control" style="background-color: white; border:1px solid #cbd5e1; width:100%;" <?php echo $user_data['id'] === $_SESSION['admin_user_id'] ? 'disabled' : ''; ?>>
                                <option value="administrator" <?php echo $user_data['role'] === 'administrator' ? 'selected' : ''; ?>>Administrator</option>
                                <option value="editor" <?php echo $user_data['role'] === 'editor' ? 'selected' : ''; ?>>Editor</option>
                            </select>
                            <?php if ($user_data['id'] === $_SESSION['admin_user_id']): ?>
                                <input type="hidden" name="role" value="administrator">
                                <small style="color: #64748b; display: block; margin-top: 4px;">Anda tidak bisa mengubah role akun Anda sendiri.</small>
                            <?php endif; ?>
                        </div>

                        <div style="margin-bottom: 24px;">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" style="background-color: white; border:1px solid #cbd5e1; width:100%;" <?php echo $user_data['id'] === $_SESSION['admin_user_id'] ? 'disabled' : ''; ?>>
                                <option value="1" <?php echo $user_data['status'] == 1 ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo $user_data['status'] == 0 ? 'selected' : ''; ?>>Nonaktif</option>
                            </select>
                            <?php if ($user_data['id'] === $_SESSION['admin_user_id']): ?>
                                <input type="hidden" name="status" value="1">
                                <small style="color: #64748b; display: block; margin-top: 4px;">Anda tidak bisa menonaktifkan akun Anda sendiri.</small>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <button type="submit" name="update" class="btn-admin btn-admin-primary" style="flex: 1; justify-content: center; padding: 10px;">
                                Simpan Perubahan
                            </button>
                            <a href="users.php" class="btn-admin btn-admin-danger" style="flex: 1; justify-content: center; padding: 10px; text-decoration: none; text-align: center;">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- List & Add User Mode -->
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                    
                    <!-- Left Column: Add User form -->
                    <div class="admin-card">
                        <h3>Tambah User</h3>
                        <form action="" method="POST" style="margin-top: 20px;">
                            <?php echo csrf_field(); ?>
                            
                            <div style="margin-bottom: 16px;">
                                <label class="form-label">Username *</label>
                                <input type="text" name="username" class="form-control" placeholder="Contoh: admin_dua" required style="background-color: white; border:1px solid #cbd5e1; width:100%;">
                            </div>

                            <div style="margin-bottom: 16px;">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" placeholder="Contoh: admin2@urbanoffice.co.id" required style="background-color: white; border:1px solid #cbd5e1; width:100%;">
                            </div>

                            <div style="margin-bottom: 16px;">
                                <label class="form-label">Password *</label>
                                <div style="position: relative; display: flex; align-items: center;">
                                    <input type="password" id="create-password" name="password" class="form-control" placeholder="Masukkan password kuat" required style="background-color: white; border:1px solid #cbd5e1; width:100%; padding-right: 40px;">
                                    <button type="button" onclick="togglePassword('create-password', this)" style="position: absolute; right: 10px; background: none; border: none; cursor: pointer; color: #64748b; display: flex; align-items: center; justify-content: center; height: 100%; padding: 0 5px; z-index: 10;">
                                        <i class="bi bi-eye-slash" style="font-size: 1.15rem; margin: 0;"></i>
                                    </button>
                                </div>
                            </div>

                            <div style="margin-bottom: 16px;">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-control" style="background-color: white; border:1px solid #cbd5e1; width:100%;">
                                    <option value="administrator">Administrator</option>
                                    <option value="editor" selected>Editor</option>
                                </select>
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control" style="background-color: white; border:1px solid #cbd5e1; width:100%;">
                                    <option value="1" selected>Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </select>
                            </div>

                            <button type="submit" name="create" class="btn-admin btn-admin-primary" style="width: 100%; justify-content: center; padding: 10px;">
                                Tambah User
                            </button>
                        </form>
                    </div>

                    <!-- Right Column: Users List table -->
                    <div class="admin-card">
                        <h3>Daftar User</h3>
                        <div class="table-responsive" style="margin-top: 20px;">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Dibuat Pada</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($users)): foreach ($users as $u): ?>
                                        <tr>
                                            <td><strong><?php echo sanitize($u['username']); ?></strong></td>
                                            <td><?php echo sanitize($u['email']); ?></td>
                                            <td>
                                                <span class="badge badge-info"><?php echo sanitize(ucfirst($u['role'])); ?></span>
                                            </td>
                                            <td>
                                                <?php if ($u['status'] == 1): ?>
                                                    <span class="badge badge-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><small><?php echo date('d M Y H:i', strtotime($u['created_at'])); ?></small></td>
                                            <td>
                                                <div style="display: flex; gap: 5px;">
                                                    <a href="?action=edit&id=<?php echo $u['id']; ?>" class="btn-admin btn-admin-primary btn-admin-sm" style="text-decoration: none;">Edit</a>
                                                    <?php if ($u['id'] !== $_SESSION['admin_user_id']): ?>
                                                        <a href="?delete=<?php echo $u['id']; ?>" class="btn-admin btn-admin-danger btn-admin-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">Delete</a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; else: ?>
                                        <tr>
                                            <td colspan="6" style="text-align: center; color: #94a3b8;">Belum ada user.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            <?php endif; ?>

        </main>
    </div>
    <script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
    }
    </script>
</body>
</html>
