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
 * Resolve a stored media path to a usable URL. Absolute URLs (e.g. Cloudinary
 * https://res.cloudinary.com/...) are returned as-is; relative paths get
 * BASE_URL prepended. Returns '' for empty input.
 */
function media_url(?string $path): string {
    $path = trim((string)$path);
    if ($path === '') return '';
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) return $path;
    return BASE_URL . ltrim($path, '/');
}

/**
 * For service landing pages that share one URL across cities (filtered via ?lokasi=),
 * return the proper-cased City label from the ?lokasi query param — but ONLY when a branch
 * in that city actually offers the given service category (per locations_data.php). Returns
 * '' when there is no valid location, so callers can leave the default hero untouched.
 */
function service_city_from_query(string $category): string {
    $lokasi = isset($_GET['lokasi']) ? strtolower(trim($_GET['lokasi'])) : '';
    if ($lokasi === '') {
        return '';
    }
    global $locations_db;
    if (!isset($locations_db) || !is_array($locations_db)) {
        $path = __DIR__ . '/locations_data.php';
        if (file_exists($path)) {
            require $path;
        }
    }
    if (!isset($locations_db) || !is_array($locations_db)) {
        return ucwords(str_replace('-', ' ', $lokasi));
    }
    foreach ($locations_db as $key => $branch) {
        $matches_loc = (isset($branch['city']) && strtolower($branch['city']) === $lokasi)
                    || $key === $lokasi
                    || (isset($branch['slug']) && $branch['slug'] === $lokasi);
        if (!$matches_loc || empty($branch['pricing'])) {
            continue;
        }
        foreach ($branch['pricing'] as $pkg) {
            if (isset($pkg['category']) && $pkg['category'] === $category) {
                return !empty($branch['location']) ? $branch['location'] : $branch['city'];
            }
        }
    }
    return ucwords(str_replace('-', ' ', $lokasi));
}

/**
 * Return all branches (locations_data rows) in a given city slug that offer a given service
 * category. Powers the per-city service landing pages (e.g. /sewa-kantor-jakarta/) with real,
 * locally-unique content (address, map, advantages). Empty array if none.
 */
function service_city_branches(string $category, string $city_slug): array {
    global $locations_db;
    if (!isset($locations_db) || !is_array($locations_db)) {
        $path = __DIR__ . '/locations_data.php';
        if (file_exists($path)) {
            require $path;
        }
    }
    $out = [];
    if (!isset($locations_db) || !is_array($locations_db)) {
        return $out;
    }
    foreach ($locations_db as $key => $branch) {
        $matches_loc = (isset($branch['city']) && strtolower($branch['city']) === $city_slug)
                    || $key === $city_slug
                    || (isset($branch['slug']) && $branch['slug'] === $city_slug);
        if (!$matches_loc || empty($branch['pricing'])) {
            continue;
        }
        foreach ($branch['pricing'] as $pkg) {
            if (isset($pkg['category']) && $pkg['category'] === $category) {
                $out[] = $branch;
                break;
            }
        }
    }
    return $out;
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
 * Safe HTML Minifier
 * Strips HTML comments and collapses redundant spaces while preserving newlines
 * so inline JavaScript comments (//) and formatting are never broken.
 */
function minify_html(string $html): string {
    $search = [
        '/<!--[^]\[|><]*(?<![<>])-->/', // Strip HTML comments (leaving browser hacks)
        '/[ \t]+/',                     // Collapse multiple horizontal spaces/tabs into a single space
        '/(\r?\n)+/'                   // Collapse multiple empty newlines into a single newline
    ];
    $replace = [
        '',
        ' ',
        "\n"
    ];
    return trim(preg_replace($search, $replace, $html));
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
    if (DEV_MODE || is_admin() || $_SERVER['REQUEST_METHOD'] !== 'GET' || isset($_GET['lokasi']) || isset($_GET['type'])) {
        return; // Bypass cache in dev mode, for admins, on POST, for ?lokasi city variants, or ?type detail
        // pages. ?type is critical: detail.php reuses page_key 'sewa-kantor-surabaya' for every room, so
        // without this every detail?type=N (and the index page) would collide on one cache entry.
    }

    // Incorporate full request URI (query string like ?page=2, ?q=...) into cache key to avoid cache collision
    $uri_suffix = $_SERVER['REQUEST_URI'] ?? '';
    $cache_key = md5($page_key . '_' . $uri_suffix);
    $cache_file = DIR_CACHE . $cache_key . '.html';

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
    if (DEV_MODE || is_admin() || $_SERVER['REQUEST_METHOD'] !== 'GET' || isset($_GET['lokasi']) || isset($_GET['type'])) {
        return;
    }

    $html = ob_get_clean();
    $minified_html = minify_html($html);

    // Save cache file inside Cache directory
    if (!is_dir(DIR_CACHE)) {
        mkdir(DIR_CACHE, 0755, true);
    }

    $uri_suffix = $_SERVER['REQUEST_URI'] ?? '';
    $cache_key = md5($page_key . '_' . $uri_suffix);
    $cache_file = DIR_CACHE . $cache_key . '.html';
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
