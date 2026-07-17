<?php
/**
 * Admin Logout script
 * Destroys session and redirects to login
 */

require_once dirname(dirname(__FILE__)) . '/inc/config.php';
require_once dirname(dirname(__FILE__)) . '/inc/functions.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $user_id = $_SESSION['admin_user_id'] ?? null;
    $username = $_SESSION['admin_username'] ?? 'Unknown';
    log_activity($user_id, 'logout', "User $username logged out successfully.");
}

// Unset all session variables
$_SESSION = [];

// Destroy session cookies
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

header('Location: ' . BASE_URL . 'admin/index.php');
exit;
?>
