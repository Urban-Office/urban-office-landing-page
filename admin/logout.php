<?php
/**
 * Admin Logout script
 * CSRF-protected POST logout that destroys session cleanly and redirects to login.
 */

require_once dirname(dirname(__FILE__)) . '/inc/config.php';
require_once dirname(dirname(__FILE__)) . '/inc/functions.php';

send_admin_security_headers();

// Require CSRF-protected POST when logged in to prevent cross-site forced logout
if (is_admin()) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . 'admin/index.php');
        exit;
    }

    $user_id = $_SESSION['admin_user_id'] ?? null;
    $username = $_SESSION['admin_username'] ?? 'Unknown';
    log_activity($user_id, 'logout', "User $username logged out successfully.");
}

// Unset all session variables
$_SESSION = [];

// Destroy session cookie with matching SameSite/Secure flags
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $params['path'],
        'domain'   => $params['domain'],
        'secure'   => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?: 'Strict',
    ]);
}

// Destroy session
session_destroy();

header('Location: ' . BASE_URL . 'admin/index.php');
exit;
?>
