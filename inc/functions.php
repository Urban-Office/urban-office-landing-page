<?php
/**
 * Utility functions for Security, Output Optimization, and File Caching
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

// Prevent direct access
if (basename($_SERVER['SCRIPT_FILENAME']) === 'functions.php') {
    header('HTTP/1.1 403 Forbidden');
    exit('Direct access forbidden.');
}

/**
 * XSS Sanitization: Sanitize inputs for secure output
 */
function sanitize(?string $data): string {
    if ($data === null) return '';
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Verifies if user is logged in as administrator
 */
function is_admin(): bool {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Enforces admin login
 */
function check_admin_auth(): void {
    if (!is_admin()) {
        header('Location: ' . BASE_URL . 'admin/index.php');
        exit;
    }
}

/**
 * CSRF protection form input field generator
 */
function csrf_field(): string {
    $token = sanitize($_SESSION['csrf_token'] ?? '');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * CSRF token verification
 */
function verify_csrf_token(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Simple HTML Minifier
 * Compresses HTML outputs to reduce bandwidth and load times
 */
function minify_html(string $html): string {
    $search = [
        '/\n+/',             // Replace multiple newlines with a single space
        '/[ \t]+/',          // Replace tabs and multiple spaces with a single space
        '/<!--[^]\[|><]*(?<![<>])-->/' // Strip HTML comments (leaving browser hacks if any)
    ];
    $replace = [
        ' ',
        ' ',
        ''
    ];
    return preg_replace($search, $replace, $html);
}

/**
 * Log activity to SQL audit database
 */
function log_activity(?int $user_id, string $action, string $details): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    try {
        Database::insert(
            "INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)",
            [$user_id, $action, $details, $ip]
        );
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

/**
 * Dynamic File Cache Helpers
 * Saves compiled HTML pages into /cache to bypass MySQL queries on subsequent requests
 */
function start_page_cache(string $page_key): void {
    if (DEV_MODE || is_admin() || $_SERVER['REQUEST_METHOD'] !== 'GET') {
        return; // Bypass cache in dev mode, for logged-in admins, or on POST requests
    }

    $cache_file = DIR_CACHE . md5($page_key) . '.html';

    // Verify cache file exists and has not expired
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < CACHE_EXPIRY) {
        readfile($cache_file);
        echo "\n<!-- Served from Local Cache (TTFB < 50ms) -->";
        exit;
    }

    // Start buffer capturing for page caching
    ob_start();
}

function end_page_cache(string $page_key): void {
    if (DEV_MODE || is_admin() || $_SERVER['REQUEST_METHOD'] !== 'GET') {
        return;
    }

    $html = ob_get_clean();
    $minified_html = minify_html($html);

    // Save cache file inside Cache directory
    if (!is_dir(DIR_CACHE)) {
        mkdir(DIR_CACHE, 0755, true);
    }

    $cache_file = DIR_CACHE . md5($page_key) . '.html';
    file_put_contents($cache_file, $minified_html);

    echo $minified_html;
    echo "\n<!-- Cached on " . date('Y-m-d H:i:s') . " -->";
}

/**
 * Clears all compiled cache files
 */
function clear_page_cache(): void {
    if (is_dir(DIR_CACHE)) {
        $files = glob(DIR_CACHE . '*.html');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

/**
 * Auto-Publish Scheduled Posts
 * Automatically changes post status from 'scheduled' to 'published'
 * when published_at datetime has arrived or passed.
 * 
 * Also handles legacy drafts: any post with status='draft' whose
 * published_at is in the past AND was created before today (catch-up
 * for posts made before the 'scheduled' feature existed).
 * 
 * Lightweight: two UPDATE queries, no row scanning.
 * Called on every page load (frontend + admin).
 */
function auto_publish_scheduled_posts(): void {
    try {
        // 1. Publish explicitly scheduled posts whose time has arrived
        Database::query(
            "UPDATE posts SET status = 'published', updated_at = NOW()
             WHERE status = 'scheduled'
               AND published_at IS NOT NULL
               AND published_at <= NOW()"
        );

        // 2. Catch-up: auto-publish legacy drafts whose published_at
        //    is in the past AND the post was created before today.
        //    This handles posts made before the 'scheduled' feature
        //    was added, where admin set a future date but status stayed 'draft'.
        Database::query(
            "UPDATE posts SET status = 'published', updated_at = NOW()
             WHERE status = 'draft'
               AND published_at IS NOT NULL
               AND published_at <= NOW()
               AND DATE(created_at) < CURDATE()"
        );
    } catch (Exception $e) {
        error_log("Auto-publish scheduled posts failed: " . $e->getMessage());
    }
}
