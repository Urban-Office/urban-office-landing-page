<?php
/**
 * Configuration Settings for UrbanOffice Platform
 * Enforces security and global definitions
 */

// Prevent direct access to configuration files
if (basename($_SERVER['SCRIPT_FILENAME']) === 'config.php') {
    header('HTTP/1.1 403 Forbidden');
    exit('Direct access forbidden.');
}

// Development Mode / Error Reporting
define('DEV_MODE', true); // Toggle to false in production

if (DEV_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// System Timezone — set to Asia/Jakarta for consistent scheduling
date_default_timezone_set('Asia/Jakarta');

// System Constants
if (php_sapi_name() === 'cli') {
    define('BASE_URL', 'http://localhost/');
    define('DIR_SUBFOLDER', '');
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443 ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
    $proj_root = str_replace('\\', '/', dirname(__DIR__));
    $subfolder = '';
    if (!empty($doc_root) && strpos($proj_root, $doc_root) === 0) {
        $subfolder = substr($proj_root, strlen($doc_root));
    }
    $subfolder = rtrim(str_replace('\\', '/', $subfolder), '/');
    define('BASE_URL', $protocol . '://' . $host . $subfolder . '/');
    define('DIR_SUBFOLDER', $subfolder);
}
define('SITE_NAME', 'Urban Office');
define('WHATSAPP_NUMBER', '6285107620100'); // Standardized international format (legacy fallback)
define('WHATSAPP_NUMBER_SALES', '628212338100'); // Nomor Sales & Konsultasi Produk
define('WHATSAPP_NUMBER_CS', '6285107620100'); // Nomor Customer Service & Komplain
define('WHATSAPP_MESSAGE', 'Halo, saya ingin menanyakan perihal layanan Urban Office.');

// Web App Portal URLs (My Urban Office)
define('APP_BASE_URL', 'https://my.urbanoffice.co.id');
define('APP_HOME_URL', APP_BASE_URL . '/dashboard/home');
define('APP_BOOKING_URL', APP_BASE_URL . '/dashboard/bookingform');

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'urban_office');
define('DB_USER', 'root');
define('DB_PASS', '');

// File Paths
define('DIR_ROOT', dirname(__DIR__) . '/');
define('DIR_UPLOADS', DIR_ROOT . 'uploads/');
define('DIR_CACHE', DIR_ROOT . 'cache/');
define('DIR_SESSIONS', DIR_ROOT . 'cache/sessions/');

// Cache Expiry (in seconds) - 1 Hour
define('CACHE_EXPIRY', 3600);

// Session Hardening Rules
// Only start a PHP session when needed (admin, API, POST, CSRF/lead endpoints, or an existing
// session cookie). Starting sessions on every anonymous GET forces `Cache-Control: no-store`
// and `Set-Cookie` headers that block Cloudflare / browser edge caching on public landing pages.
$req_uri_for_sess = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$sess_path_norm   = strtolower($req_uri_for_sess);
$needs_session = (php_sapi_name() === 'cli')
    || (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST')
    || isset($_COOKIE['URBAN_SESSID'])
    || strpos($sess_path_norm, '/admin') !== false
    || strpos($sess_path_norm, '/_api') !== false
    || strpos($sess_path_norm, '/inc/csrf_token.php') !== false
    || strpos($sess_path_norm, '/inc/lead_handler.php') !== false;

if ($needs_session && session_status() === PHP_SESSION_NONE) {
    // Keep sessions alive for 4 hours of inactivity — long enough to draft/review
    // an article in the admin panel without being logged out mid-edit. Using our
    // own save path (instead of the host's shared session dir) also keeps these
    // sessions out of reach of OS-level session-cleanup cron jobs that some
    // production hosts run against the default system session path, which ignore
    // this app's gc_maxlifetime setting.
    $session_lifetime = 14400;

    if (!is_dir(DIR_SESSIONS)) {
        mkdir(DIR_SESSIONS, 0755, true);
    }
    if (!file_exists(DIR_SESSIONS . '.htaccess')) {
        file_put_contents(DIR_SESSIONS . '.htaccess', "Require all denied\n");
    }
    session_save_path(DIR_SESSIONS);

    ini_set('session.gc_maxlifetime', $session_lifetime);
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    ini_set('session.cookie_lifetime', $session_lifetime);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');

    // Check if SSL is enabled
    $is_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
    if ($is_secure) {
        ini_set('session.cookie_secure', 1);
    }

    session_name('URBAN_SESSID');
    session_start();
}

// Generate CSRF Token if session is active and token is not set
if (session_status() === PHP_SESSION_ACTIVE && empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect Map Interceptor (URL Preservation)
// Never intercept /admin or /_api paths so a misconfigured redirect cannot lock out admins or break APIs.
if (php_sapi_name() !== 'cli') {
    $req_uri = $_SERVER['REQUEST_URI'] ?? '';
    // Strip query parameters
    $req_path = urldecode(parse_url($req_uri, PHP_URL_PATH) ?? '');
    
    // Normalize relative path if in subfolder (e.g. /Clone Website urban hanya php/virtual-office/)
    $subfolder = DIR_SUBFOLDER; 
    if (!empty($subfolder) && strpos($req_path, $subfolder) === 0) {
        $req_path = substr($req_path, strlen($subfolder));
    }
    
    $clean_path = '/' . ltrim($req_path, '/');

    if (strpos($clean_path, '/admin') !== 0 && strpos($clean_path, '/_api') !== 0) {
        try {
            require_once __DIR__ . '/database.php';
            $redirect = Database::fetch("SELECT new_url, type FROM redirects WHERE old_url = ?", [$clean_path]);
            if ($redirect) {
                $status_code = in_array((int)$redirect['type'], [301, 302], true) ? (int)$redirect['type'] : 301;
                $raw_new = str_replace(["\r", "\n"], '', trim((string)$redirect['new_url']));
                if ($raw_new !== '') {
                    $dest = (preg_match('#^https?://#i', $raw_new)) ? $raw_new : BASE_URL . ltrim($raw_new, '/');
                    header("Location: " . $dest, true, $status_code);
                    exit;
                }
            }
        } catch (Exception $e) {
            // Silent fallback in case DB table is not set up yet
        }
    }
}
