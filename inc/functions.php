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
 * Public visibility flag for branch/location data.
 * Missing flags remain public for backwards compatibility.
 */
function is_public_location(array $location): bool {
    return !array_key_exists('is_public', $location) || $location['is_public'] !== false;
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
        if (!is_public_location($branch)) {
            continue;
        }
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
        if (!is_public_location($branch)) {
            continue;
        }
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
 * Checks whether the logged-in admin has the specified role.
 */
function is_admin_role(string $role = 'administrator'): bool {
    return is_admin() && (($_SESSION['admin_role'] ?? '') === $role);
}

/**
 * Sends defensive HTTP security & no-cache headers for admin panel responses.
 */
function send_admin_security_headers(): void {
    if (php_sapi_name() === 'cli' || headers_sent()) {
        return;
    }
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}

/**
 * Enforces admin login and verifies the user account is still active in the database.
 */
function check_admin_auth(): void {
    $is_json = defined('ADMIN_JSON_ENDPOINT') && ADMIN_JSON_ENDPOINT;
    if (!is_admin() || empty($_SESSION['admin_user_id'])) {
        if ($is_json) {
            header('Content-Type: application/json; charset=UTF-8');
            http_response_code(401);
            echo json_encode(['uploaded' => false, 'error' => ['message' => 'Sesi login telah berakhir. Silakan login kembali.']]);
            exit;
        }
        header('Location: ' . BASE_URL . 'admin/index.php');
        exit;
    }

    // Re-validate user status & role so deactivated or demoted accounts lose access immediately
    try {
        $u = Database::fetch("SELECT username, role, status FROM users WHERE id = ?", [(int)$_SESSION['admin_user_id']]);
        if (!$u || (int)$u['status'] !== 1) {
            $_SESSION = [];
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            if ($is_json) {
                header('Content-Type: application/json; charset=UTF-8');
                http_response_code(401);
                echo json_encode(['uploaded' => false, 'error' => ['message' => 'Akun tidak aktif. Silakan login kembali.']]);
                exit;
            }
            header('Location: ' . BASE_URL . 'admin/index.php');
            exit;
        }
        $_SESSION['admin_username'] = $u['username'];
        $_SESSION['admin_role'] = $u['role'];
    } catch (Exception $e) {
        error_log("Admin session re-check failed: " . $e->getMessage());
    }
}

/**
 * Enforces a specific admin role (default: administrator).
 */
function require_admin_role(string $role = 'administrator'): void {
    if (!is_admin_role($role)) {
        if (defined('ADMIN_JSON_ENDPOINT') && ADMIN_JSON_ENDPOINT) {
            header('Content-Type: application/json; charset=UTF-8');
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Akses ditolak. Hanya Administrator yang diizinkan.']);
            exit;
        }
        header('Location: ' . BASE_URL . 'admin/index.php?error=forbidden');
        exit;
    }
}

/**
 * Generates a clean, URL-safe lowercase slug (alphanumeric + single hyphens).
 */
function admin_slugify(string $text, string $fallback = ''): string {
    $text = trim($text);
    if ($text !== '' && function_exists('iconv')) {
        $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($trans !== false) {
            $text = $trans;
        }
    }
    $slug = strtolower($text);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim((string)$slug, '-');
    return $slug !== '' ? $slug : $fallback;
}

/**
 * Neutralizes CSV Formula Injection (Excel/Sheets) for user-supplied fields.
 */
function admin_csv_safe($value): string {
    $str = (string)($value ?? '');
    if ($str !== '' && preg_match('/^[\t\r\n ]*[=+\-@]/', $str)) {
        return "'" . $str;
    }
    return $str;
}

/**
 * Safely deletes a file ONLY if its resolved realpath lies strictly inside one of the allowed directories.
 */
function admin_safe_unlink(string $rel_path, array $allowed_dirs): bool {
    $rel_path = trim($rel_path);
    if ($rel_path === '' || strpos($rel_path, "\0") !== false) {
        return false;
    }
    if (preg_match('#^https?://#i', $rel_path)) {
        return false;
    }
    $full = DIR_ROOT . ltrim(str_replace('\\', '/', $rel_path), '/');
    $real_file = realpath($full);
    if ($real_file === false || !is_file($real_file)) {
        return false;
    }
    $norm_file = str_replace('\\', '/', $real_file);
    foreach ($allowed_dirs as $dir) {
        $real_dir = realpath($dir);
        if ($real_dir === false) {
            continue;
        }
        $norm_dir = rtrim(str_replace('\\', '/', $real_dir), '/') . '/';
        if (stripos($norm_file, $norm_dir) === 0) {
            return @unlink($real_file);
        }
    }
    return false;
}

/**
 * Validates an uploaded image file via upload error code, size cap, and finfo MIME inspection.
 * Returns ['ok' => true, 'ext' => string, 'mime' => string] or ['ok' => false, 'error' => string].
 */
function admin_validate_image_upload(array $file, int $max_bytes = 5242880): array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Gagal mengunggah file (Kode error: ' . intval($file['error'] ?? -1) . ').'];
    }
    if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > $max_bytes) {
        $mb = round($max_bytes / (1024 * 1024), 1);
        return ['ok' => false, 'error' => "Ukuran file maksimal {$mb}MB."];
    }
    $tmp = $file['tmp_name'] ?? '';
    if ($tmp === '' || !is_file($tmp)) {
        return ['ok' => false, 'error' => 'File sementara tidak valid.'];
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $tmp) : '';
    if ($finfo) {
        finfo_close($finfo);
    }
    $allowed_mimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    if (!isset($allowed_mimes[$mime])) {
        return ['ok' => false, 'error' => 'Format file tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF yang valid.'];
    }
    return ['ok' => true, 'ext' => $allowed_mimes[$mime], 'mime' => $mime];
}

/**
 * Normalizes Indonesian phone numbers (08... -> 628...) for WhatsApp links.
 */
function admin_format_wa_phone(string $phone): string {
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

/**
 * Logs technical exception details internally and returns a safe user-facing message.
 */
function admin_friendly_error(string $user_msg, Throwable $e): string {
    error_log($user_msg . ' | ' . $e->getMessage());
    return $user_msg . ' Silakan coba lagi atau periksa log server.';
}

/**
 * Resolves the client IP safely, honoring Cloudflare's CF-Connecting-IP when valid,
 * with fallback to REMOTE_ADDR.
 */
function admin_client_ip(): string {
    $cf = trim((string)($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
    if ($cf !== '' && filter_var($cf, FILTER_VALIDATE_IP)) {
        return $cf;
    }
    $remote = trim((string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'));
    return $remote !== '' ? substr($remote, 0, 45) : '0.0.0.0';
}

/**
 * Ensures the SEO audit columns on `pages` and `page_seo_audits` table exist.
 * Cached in session so DDL/information_schema checks run at most once per login session.
 */
function ensure_page_seo_audit_schema(): void {
    static $checked = false;
    if ($checked || !empty($_SESSION['seo_audit_schema_ok'])) {
        return;
    }
    $checked = true;

    try {
        $db = Database::getConnection();
        foreach ([
            'audit_interval_days' => 'INT NOT NULL DEFAULT 30 AFTER schema_faq',
            'last_audited_at'     => 'DATETIME NULL AFTER audit_interval_days',
        ] as $col => $def) {
            $row = Database::fetch(
                "SELECT COUNT(*) AS c FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'pages' AND COLUMN_NAME = ?",
                [DB_NAME, $col]
            );
            if (!$row || (int)$row['c'] === 0) {
                $db->exec("ALTER TABLE pages ADD COLUMN {$col} {$def}");
            }
        }
        $db->exec("CREATE TABLE IF NOT EXISTS page_seo_audits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_id INT NOT NULL,
            event_type VARCHAR(20) NOT NULL DEFAULT 'audit_check',
            user_id INT NULL,
            prev_meta_title VARCHAR(255) NULL,
            prev_meta_description VARCHAR(255) NULL,
            new_meta_title VARCHAR(255) NULL,
            new_meta_description VARCHAR(255) NULL,
            gsc_period_days INT NOT NULL DEFAULT 28,
            gsc_impressions INT NULL,
            gsc_clicks INT NULL,
            gsc_ctr DECIMAL(5,2) NULL,
            gsc_position DECIMAL(6,2) NULL,
            fp_organic_views INT NOT NULL DEFAULT 0,
            fp_geo_views INT NOT NULL DEFAULT 0,
            fp_total_views INT NOT NULL DEFAULT 0,
            fp_conversions INT NOT NULL DEFAULT 0,
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_psa_page_created (page_id, created_at DESC),
            FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['seo_audit_schema_ok'] = true;
        }
    } catch (Exception $e) {
        error_log("ensure_page_seo_audit_schema failed: " . $e->getMessage());
    }
}

/**
 * Returns possible analytics_events.page_path variants for a landing page slug.
 */
function seo_page_path_variants(string $slug): array {
    $slug = trim($slug, '/');
    if ($slug === '') {
        return ['/', '/index.php'];
    }
    return ['/' . $slug . '/', '/' . $slug];
}

/**
 * Computes Next Audit schedule & status for an SEO landing page.
 * Reference date = max(updated_at, last_audited_at).
 */
function seo_compute_audit_schedule(?string $updated_at, ?string $last_audited_at, int $interval_days = 30, ?int $now_ts = null): array {
    $interval_days = max(7, min(365, $interval_days > 0 ? $interval_days : 30));
    $now_ts = $now_ts ?? time();
    $today_start = strtotime(date('Y-m-d 00:00:00', $now_ts));

    $upd_ts = $updated_at ? strtotime($updated_at) : false;
    $aud_ts = $last_audited_at ? strtotime($last_audited_at) : false;

    $ref_ts = $upd_ts ?: $today_start;
    if ($aud_ts && $aud_ts > $ref_ts) {
        $ref_ts = $aud_ts;
    }

    $ref_day_ts = strtotime(date('Y-m-d 00:00:00', $ref_ts));
    $next_ts = $ref_day_ts + ($interval_days * 86400);
    $days_left = (int) floor(($next_ts - $today_start) / 86400);
    $days_since_update = $upd_ts ? max(0, (int) floor(($today_start - strtotime(date('Y-m-d 00:00:00', $upd_ts))) / 86400)) : 0;

    if ($days_left < 0) {
        $status = 'overdue';
        $label = 'Terlewat ' . abs($days_left) . ' hari';
        $badge = 'badge-danger';
    } elseif ($days_left === 0) {
        $status = 'due';
        $label = 'Jadwal Hari Ini';
        $badge = 'badge-warning';
    } elseif ($days_left <= 3) {
        $status = 'due';
        $label = 'Siap Audit (H-' . $days_left . ')';
        $badge = 'badge-warning';
    } else {
        $status = 'scheduled';
        $label = $days_left . ' hari lagi';
        $badge = 'badge-info';
    }

    return [
        'interval_days'     => $interval_days,
        'ref_ts'            => $ref_ts,
        'next_ts'           => $next_ts,
        'next_date'         => date('d M Y', $next_ts),
        'days_left'         => $days_left,
        'days_since_update' => $days_since_update,
        'status'            => $status,
        'label'             => $label,
        'badge'             => $badge,
    ];
}

/**
 * Calculates percentage change between baseline (before) and new (after) values.
 * Returns null if before is 0 and after is 0; returns 100.0 when growing from 0 to >0.
 */
function seo_delta_pct(float $before, float $after): ?float {
    if ($before == 0.0) {
        if ($after == 0.0) {
            return null;
        }
        return $after > 0 ? 100.0 : -100.0;
    }
    return round((($after - $before) / abs($before)) * 100, 1);
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
    $ip = admin_client_ip();
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
    if (DEV_MODE || is_admin() || $_SERVER['REQUEST_METHOD'] !== 'GET' || isset($_GET['lokasi']) || isset($_GET['type']) || isset($_GET['cabang'])) {
        return; // Bypass cache in dev mode, for admins, on POST, for ?lokasi city variants, or ?type detail
        // pages. ?type is critical: detail.php reuses page_key 'sewa-kantor-surabaya' for every room, so
        // without this every detail?type=N (and the index page) would collide on one cache entry.
    }

    if (php_sapi_name() !== 'cli' && !headers_sent() && session_status() !== PHP_SESSION_ACTIVE) {
        header('Cache-Control: public, max-age=300, stale-while-revalidate=60');
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
    if (DEV_MODE || is_admin() || $_SERVER['REQUEST_METHOD'] !== 'GET' || isset($_GET['lokasi']) || isset($_GET['type']) || isset($_GET['cabang'])) {
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

/**
 * Derive 3 concise scannable highlight bullets for a Virtual Office package card
 * directly from its structured tier metadata/features so every branch stays consistent.
 */
function get_vo_package_highlights(array $tier): array {
    if (!empty($tier['highlights']) && is_array($tier['highlights'])) {
        return array_slice($tier['highlights'], 0, 3);
    }

    $name     = strtolower($tier['name'] ?? '');
    $features = $tier['features'] ?? [];
    $joined   = strtolower(implode(' | ', $features));

    if (strpos($name, 'business') !== false) {
        return [
            'Alamat Bisnis Resmi & Domisili',
            'Penanganan Surat & Paket Masuk',
            'Notifikasi Real-Time WhatsApp',
        ];
    }

    if (strpos($name, 'starter') !== false) {
        if (strpos($joined, 'website') !== false) {
            return [
                'Alamat Bisnis & Penanganan Surat',
                'Gratis Meeting Room 4 Jam / Bulan',
                'Gratis Pembuatan Website Bisnis',
            ];
        }
        if (strpos($joined, 'meeting room') !== false && (strpos($joined, '2x 2 jam') !== false || strpos($joined, '4 jam') !== false)) {
            return [
                'Alamat Bisnis & Penanganan Surat',
                'Akses Meeting Room 4 Jam / Bulan',
                'Domisili Usaha & Resepsionis',
            ];
        }
        return array_slice($features, 0, 3);
    }

    if (strpos($name, 'luxury') !== false) {
        return [
            'Semua Fasilitas Paket Starter',
            'Akses Meeting Room 6 Jam / Bulan',
            'Nomor Telepon Kantor Bersama',
        ];
    }

    if (strpos($name, 'priority') !== false) {
        return [
            'Gratis Meeting Room 8 Jam / Bulan',
            'Akses Coworking Space 1 Orang',
            'Gratis Pembuatan Website Bisnis',
        ];
    }

    return array_slice($features, 0, 3);
}

/**
 * URL-encode path segments safely (spaces, &, etc.) while keeping slashes and scheme intact.
 */
function encode_asset_url(string $url): string {
    if ($url === '') {
        return '';
    }
    $prefix = '';
    $rel = $url;
    if (defined('BASE_URL') && strpos($url, BASE_URL) === 0) {
        $prefix = BASE_URL;
        $rel = substr($url, strlen(BASE_URL));
    } elseif (preg_match('#^https?://#i', $url)) {
        return $url;
    }
    $parts = explode('/', ltrim($rel, '/'));
    $encoded = array_map(static function (string $seg): string {
        return rawurlencode(rawurldecode($seg));
    }, $parts);
    return $prefix . implode('/', $encoded);
}

/**
 * Public-facing branch label per city/slug (keeps partner brand names out of SEO/service UI).
 */
function get_private_office_branch_label(string $city_slug, array $branch): string {
    if ($city_slug === 'jakarta' || $city_slug === 'jakarta-timur' || ($branch['slug'] ?? '') === 'jakarta-timur') {
        return !empty($branch['location']) ? $branch['location'] : 'Jakarta Timur';
    }
    $city_label = $branch['city'] ?? ucwords(str_replace('-', ' ', $city_slug));
    return !empty($branch['short_title']) ? $branch['short_title'] : $city_label;
}

/**
 * Return public cities offering a given service category, as [citySlug => cityLabel].
 */
function get_service_cities(string $category): array {
    global $locations_db;
    if (!isset($locations_db) || !is_array($locations_db)) {
        $path = __DIR__ . '/locations_data.php';
        if (file_exists($path)) {
            require $path;
        }
    }
    $cities = [];
    if (!isset($locations_db) || !is_array($locations_db)) {
        return $cities;
    }
    foreach ($locations_db as $branch) {
        if (!is_public_location($branch) || empty($branch['pricing'])) {
            continue;
        }
        foreach ($branch['pricing'] as $pkg) {
            if (($pkg['category'] ?? '') === $category) {
                $c_slug = strtolower(trim((string)($branch['city'] ?? '')));
                if ($c_slug !== '') {
                    $cities[$c_slug] = $branch['city'];
                }
                break;
            }
        }
    }
    return $cities;
}

/**
 * Return public cities offering Private Office, as [citySlug => cityLabel].
 */
function get_private_office_cities(): array {
    return get_service_cities('private-office');
}

/**
 * Return public cities offering Meeting Room, as [citySlug => cityLabel].
 */
function get_meeting_room_cities(): array {
    return get_service_cities('meeting-room');
}

/**
 * Per-city Private Office gallery items using verified local workspace assets
 * and SEO-descriptive alt attributes tailored to the active city/branch.
 */
function get_private_office_city_gallery(string $city_slug, array $branch): array {
    $city_label  = $branch['city'] ?? ucwords(str_replace('-', ' ', $city_slug));
    $short_title = get_private_office_branch_label($city_slug, $branch);
    $loc_suffix  = ($city_slug === 'jakarta') ? 'Jakarta Timur' : ($short_title . ' ' . $city_label);

    $catalog = [
        'surabaya' => [
            ['img' => 'assets/images/privateoffice/Foto ruangan 2 pax.webp', 'title' => 'Private Office Small', 'desc' => 'Kapasitas 2 Orang · 6m²', 'pax' => '2 Pax', 'type_id' => 1, 'alt' => 'Ruang Private Office Small 2 Pax siap pakai di Urban Office MERR Surabaya'],
            ['img' => 'assets/images/privateoffice/offie 301 people.webp', 'title' => 'Private Office Corporate', 'desc' => 'Kapasitas 5 Orang · 14m²', 'pax' => '5 Pax', 'type_id' => 2, 'alt' => 'Interior Private Office Corporate 5 Pax di MERR Surabaya Timur'],
            ['img' => 'assets/images/privateoffice/305.webp', 'title' => 'Private Office Corporate', 'desc' => 'Kapasitas 5 Orang · 14m²', 'pax' => '5 Pax', 'type_id' => 3, 'alt' => 'Ruang kantor privat eksklusif 5 orang di Urban Office Surabaya'],
            ['img' => 'assets/images/privateoffice/Foto ruangan 4 pax.webp', 'title' => 'Private Office Medium', 'desc' => 'Kapasitas 4 Orang · 12m²', 'pax' => '4 Pax', 'type_id' => 4, 'alt' => 'Sewa Private Office Medium 4 Pax fully furnished di Surabaya'],
            ['img' => 'assets/images/privateoffice/Foto ruangan 6 pax.webp', 'title' => 'Private Office Corporate', 'desc' => 'Kapasitas 6 Orang · 16m²', 'pax' => '6 Pax', 'type_id' => 5, 'alt' => 'Kantor privat tim 6 orang luas 16m2 di Urban Office MERR Surabaya'],
            ['img' => 'assets/images/privateoffice/Meeting 2 Pax.webp', 'title' => 'Private Office Small', 'desc' => 'Kapasitas 2 Orang · 6m²', 'pax' => '2 Pax', 'type_id' => 6, 'alt' => 'Private Office Small kapasitas 2 orang dengan meja kursi ergonomis di Surabaya'],
            ['img' => 'assets/images/privateoffice/304.webp', 'title' => 'Private Office Small', 'desc' => 'Kapasitas 2 Orang · 6m²', 'pax' => '2 Pax', 'type_id' => 7, 'alt' => 'Ruang kerja privat 2 orang hemat di Urban Office MERR Surabaya'],
            ['img' => 'assets/images/privateoffice/Meeting 6 pax.webp', 'title' => 'Private Office Medium', 'desc' => 'Kapasitas 5 Orang · 10m²', 'pax' => '5 Pax', 'type_id' => 8, 'alt' => 'Ruang kerja tim 5 orang di Serviced Office MERR Surabaya'],
        ],
        'jakarta' => [
            ['img' => 'assets/images/privateoffice/office 5 orang jaktim.webp', 'title' => 'Private Office Corporate', 'desc' => 'Kapasitas 5 Orang · Jakarta Timur', 'pax' => '5 Pax', 'type_id' => 2, 'alt' => 'Ruang Private Office 5 Orang di Urban Office Cakung Jakarta Timur'],
            ['img' => 'assets/images/privateoffice/office 5 orang jaktim 2.webp', 'title' => 'Private Office Medium', 'desc' => 'Kapasitas 4–5 Orang · Siap Pakai', 'pax' => '4-5 Pax', 'type_id' => 4, 'alt' => 'Interior Serviced Office 4-5 Orang di Cakung Jakarta Timur'],
            ['img' => 'assets/images/privateoffice/office fatmawati.webp', 'title' => 'Private Office Small', 'desc' => 'Kapasitas 2 Orang · Fully Furnished', 'pax' => '2 Pax', 'type_id' => 1, 'alt' => 'Ruang Private Office 2 Orang siap kerja di Urban Office Jakarta Timur'],
            ['img' => 'assets/images/privateoffice/private-office-5.webp', 'title' => 'Private Office Medium', 'desc' => 'Kapasitas 4 Orang · Fully Furnished', 'pax' => '4 Pax', 'type_id' => 4, 'alt' => 'Ruang Private Office Medium 4 Orang modern di Jakarta Timur'],
            ['img' => 'assets/images/privateoffice/private-office-6.webp', 'title' => 'Private Office Corporate', 'desc' => 'Kapasitas 6 Orang · Siap Pakai', 'pax' => '6 Pax', 'type_id' => 5, 'alt' => 'Ruang Private Office Corporate 6 Orang di Urban Office Jakarta Timur'],
        ],
        'gresik' => [
            ['img' => 'assets/images/privateoffice/private-office-small.webp', 'title' => 'Private Office Small', 'desc' => 'Kapasitas 2 Orang · PTGM Tower', 'pax' => '2 Pax', 'type_id' => 1, 'alt' => 'Sewa Private Office Small 2 Orang di PTGM Tower Kebomas Gresik'],
            ['img' => 'assets/images/privateoffice/private-office-medium.webp', 'title' => 'Private Office Medium', 'desc' => 'Kapasitas 4–5 Orang · PTGM Tower', 'pax' => '4-5 Pax', 'type_id' => 4, 'alt' => 'Ruang Private Office Medium 4-5 Orang siap pakai di Gresik'],
            ['img' => 'assets/images/privateoffice/private-office-corporate.webp', 'title' => 'Private Office Corporate', 'desc' => 'Kapasitas 5–6 Orang · PTGM Tower', 'pax' => '6 Pax', 'type_id' => 5, 'alt' => 'Ruang kantor korporat kapasitas 6 orang di Urban Office Gresik'],
            ['img' => 'assets/images/privateoffice/private-office-4.webp', 'title' => 'Private Office Small', 'desc' => 'Kapasitas 2 Orang · Siap Pakai', 'pax' => '2 Pax', 'type_id' => 7, 'alt' => 'Ruang kerja privat 2 orang hemat di PTGM Tower Gresik'],
            ['img' => 'assets/images/privateoffice/private-office-7.webp', 'title' => 'Private Office Executive', 'desc' => 'Kapasitas 5 Orang · PTGM Tower', 'pax' => '5 Pax', 'type_id' => 3, 'alt' => 'Ruang kantor eksekutif 5 orang di Urban Office PTGM Tower Gresik'],
        ],
        'malang' => [
            ['img' => 'assets/images/privateoffice/office malang.webp', 'title' => 'Private Office Medium', 'desc' => 'Kapasitas 4–5 Orang · Cabang Malang', 'pax' => '4-5 Pax', 'type_id' => 4, 'alt' => 'Ruang Private Office modern di Urban Office Blimbing Malang'],
            ['img' => 'assets/images/privateoffice/office meeting 8 - 12 orang malang.webp', 'title' => 'Private Office Corporate', 'desc' => 'Kapasitas 5–6 Orang · Blimbing Malang', 'pax' => '5-6 Pax', 'type_id' => 5, 'alt' => 'Ruang Private Office tim 5-6 orang di Urban Office Blimbing Malang'],
            ['img' => 'assets/images/privateoffice/private-office-small.webp', 'title' => 'Private Office Small', 'desc' => 'Kapasitas 2 Orang · Fully Furnished', 'pax' => '2 Pax', 'type_id' => 1, 'alt' => 'Sewa Private Office Small 2 Orang di Kota Malang'],
            ['img' => 'assets/images/privateoffice/private-office-8.webp', 'title' => 'Private Office Small', 'desc' => 'Kapasitas 2 Orang · Cabang Malang', 'pax' => '2 Pax', 'type_id' => 7, 'alt' => 'Ruang kerja privat 2 orang nyaman di Urban Office Malang'],
            ['img' => 'assets/images/privateoffice/office meeting 4 orang malang.webp', 'title' => 'Private Office Medium', 'desc' => 'Kapasitas 5 Orang · Siap Pakai', 'pax' => '5 Pax', 'type_id' => 8, 'alt' => 'Private Office tim 5 orang fully furnished di Malang'],
        ],
    ];

    if (isset($catalog[$city_slug])) {
        return $catalog[$city_slug];
    }

    return [
        ['img' => 'assets/images/privateoffice/private-office-small.webp', 'title' => 'Private Office Small', 'desc' => 'Kapasitas 2 Orang · Siap Pakai', 'pax' => '2 Pax', 'type_id' => 1, 'alt' => 'Private Office Small 2 Pax di Urban Office ' . $loc_suffix],
        ['img' => 'assets/images/privateoffice/private-office-medium.webp', 'title' => 'Private Office Medium', 'desc' => 'Kapasitas 4–5 Orang · Siap Pakai', 'pax' => '4-5 Pax', 'type_id' => 4, 'alt' => 'Private Office Medium di Urban Office ' . $loc_suffix],
        ['img' => 'assets/images/privateoffice/private-office-corporate.webp', 'title' => 'Private Office Corporate', 'desc' => 'Kapasitas 5–6 Orang · Siap Pakai', 'pax' => '6 Pax', 'type_id' => 5, 'alt' => 'Private Office Corporate di Urban Office ' . $loc_suffix],
    ];
}

/**
 * Per-city Serviced Office shared-facilities gallery (Meeting Room, Desk, Pantry, Resepsionis, Musholla)
 * rendered inside the "Galeri Serviced Office" section on detail.php.
 */
function get_serviced_office_gallery(string $city_slug): array {
    $catalog = [
        'surabaya' => [
            ['img' => 'assets/images/meetingroom/Meeting 18 pax.webp', 'title' => 'Meeting Room', 'alt' => 'Meeting Room Surabaya'],
            ['img' => 'assets/images/privateoffice/serviced-office-desk.webp', 'title' => 'Serviced Office', 'alt' => 'Serviced Office Surabaya'],
            ['img' => 'assets/images/privateoffice/pantry lantai 3.webp', 'title' => 'Pantry', 'alt' => 'Pantry Surabaya'],
            ['img' => 'assets/images/privateoffice/resepsionis.webp', 'title' => 'Resepsionis', 'alt' => 'Resepsionis Surabaya'],
            ['img' => 'assets/images/privateoffice/musholla.webp', 'title' => 'Tempat Ibadah', 'alt' => 'Tempat Ibadah Surabaya'],
        ],
        'jakarta' => [
            ['img' => 'assets/images/meetingroom/Meeting 8 orang jaktim.webp', 'title' => 'Meeting Room', 'alt' => 'Meeting Room Jakarta'],
            ['img' => 'assets/images/privateoffice/serviced-office-desk.webp', 'title' => 'Serviced Office', 'alt' => 'Serviced Office Jakarta'],
            ['img' => 'assets/images/privateoffice/serviced-office-pantry.webp', 'title' => 'Pantry', 'alt' => 'Pantry Jakarta'],
            ['img' => 'assets/images/privateoffice/ruang tamu & resepsionis.webp', 'title' => 'Resepsionis', 'alt' => 'Resepsionis & Ruang Tamu Jakarta'],
        ],
        'gresik' => [
            ['img' => 'assets/images/privateoffice/serviced-office-meeting.webp', 'title' => 'Meeting Room', 'alt' => 'Meeting Room Gresik'],
            ['img' => 'assets/images/privateoffice/serviced-office-desk.webp', 'title' => 'Serviced Office', 'alt' => 'Serviced Office Gresik'],
            ['img' => 'assets/images/privateoffice/serviced-office-pantry.webp', 'title' => 'Pantry', 'alt' => 'Pantry Gresik'],
            ['img' => 'assets/images/privateoffice/serviced-office-receptionist.webp', 'title' => 'Resepsionis', 'alt' => 'Resepsionis PTGM Tower Gresik'],
            ['img' => 'assets/images/privateoffice/serviced-office-outdoor.webp', 'title' => 'Lounge & Area Bersama', 'alt' => 'Area Bersama Gresik'],
        ],
        'malang' => [
            ['img' => 'assets/images/privateoffice/office meeting 8 - 12 orang malang.webp', 'title' => 'Meeting Room', 'alt' => 'Meeting Room Malang'],
            ['img' => 'assets/images/privateoffice/serviced-office-desk.webp', 'title' => 'Serviced Office', 'alt' => 'Serviced Office Malang'],
            ['img' => 'assets/images/privateoffice/resepsion malang.webp', 'title' => 'Resepsionis', 'alt' => 'Resepsionis Urban Office Malang'],
            ['img' => 'assets/images/privateoffice/open space malang.jpeg', 'title' => 'Open Space Area', 'alt' => 'Area Bersama Malang'],
        ],
    ];

    return $catalog[$city_slug] ?? $catalog['surabaya'];
}

/**
 * Per-city Private Office FAQs (shared by FAQ UI section & JSON-LD FAQPage schema).
 */
function get_private_office_city_faqs(string $city_slug, array $branch): array {
    $city_label  = $branch['city'] ?? ucwords(str_replace('-', ' ', $city_slug));
    $short_title = get_private_office_branch_label($city_slug, $branch);
    $address     = $branch['address'] ?? '';

    $local_q = [
        'surabaya' => [
            'question' => 'Di mana lokasi Private Office Urban Office Surabaya dan apakah tersedia parkir?',
            'answer'   => 'Urban Office Surabaya berlokasi di jalur utama MERR (' . $address . '). Tersedia area parkir luas gratis untuk mobil maupun motor serta pengawasan keamanan 24 jam dan CCTV.'
        ],
        'jakarta' => [
            'question' => 'Di mana lokasi Sewa Kantor Urban Office Jakarta Timur dan apakah dekat area industri?',
            'answer'   => 'Cabang Private Office Jakarta berlokasi di Urban Office Jakarta Timur (' . $address . '), berdekatan dengan kawasan industri Pulogadung, akses tol JORR Cakung, serta tersedia area parkir bagi penyewa.'
        ],
        'gresik' => [
            'question' => 'Apakah Private Office Urban Office Gresik cocok untuk perusahaan di kawasan industri?',
            'answer'   => 'Sangat cocok. Urban Office Gresik berlokasi di PTGM Tower Kebomas (' . $address . ') yang dekat kawasan industri utama Gresik serta akses Gerbang Tol Kebomas, lengkap dengan alamat domisili bisnis resmi.'
        ],
        'malang' => [
            'question' => 'Apakah tersedia akses parkir dan fasilitas pendukung di Private Office Malang?',
            'answer'   => 'Ya, Urban Office Malang di kawasan Blimbing (' . $address . ') menyediakan area parkir gratis yang memadai, resepsionis profesional, pantry, serta akses internet fiber optic.'
        ],
    ];

    $faqs = [];
    if (isset($local_q[$city_slug])) {
        $faqs[] = $local_q[$city_slug];
    }

    $faqs[] = [
        'question' => 'Apa saja yang sudah termasuk dalam harga sewa Private Office di ' . $city_label . '?',
        'answer'   => 'Harga sewa sudah mencakup ruangan fully furnished (meja, kursi ergonomis, AC), bebas biaya listrik & air, internet fiber optic hingga 100 Mbps, resepsionis, cleaning service harian, free flow kopi/teh, serta alamat bisnis resmi di Cabang ' . $short_title . '.'
    ];
    $faqs[] = [
        'question' => 'Apakah ada koneksi WiFi dan berapa kecepatannya?',
        'answer'   => 'Ya, seluruh ruang Private Office di ' . $city_label . ' dilengkapi koneksi internet WiFi dan LAN berkecepatan tinggi hingga 100 Mbps.'
    ];
    $faqs[] = [
        'question' => 'Apakah bisa survei lokasi atau uji coba harian sebelum sewa tahunan?',
        'answer'   => 'Ya, Anda dapat menjadwalkan kunjungan survei ke Cabang ' . $short_title . ' (' . $city_label . ') maupun mengambil opsi trial harian/mingguan melalui tim admin kami sebelum memutuskan kontrak sewa.'
    ];
    $faqs[] = [
        'question' => 'Bagaimana jika ingin melakukan perpanjangan sewa Private Office?',
        'answer'   => 'Perpanjangan sewa dapat dilakukan dengan mudah melalui tim admin kami dengan memberikan konfirmasi "Perpanjangan Sewa" sebelum masa kontrak berakhir.'
    ];

    return $faqs;
}

/**
 * Per-city Meeting Room gallery items using verified local workspace assets
 * and SEO-descriptive alt attributes tailored to the active city/branch.
 */
function get_meeting_room_city_gallery(string $city_slug, array $branch): array {
    $city_label  = ($city_slug === 'surabaya-barat') ? 'Surabaya Barat' : ($branch['city'] ?? ucwords(str_replace('-', ' ', $city_slug)));
    $short_title = get_private_office_branch_label($city_slug, $branch);
    $loc_suffix  = ($city_slug === 'jakarta') ? 'Jakarta Timur' : ($short_title . ' ' . $city_label);

    $catalog = [
        'surabaya' => [
            ['img' => 'assets/images/meetingroom/Small Meeting.webp', 'title' => 'Small Meeting', 'desc' => 'Mulai Rp 125.000 / jam', 'pax' => '6 Pax', 'type_id' => 1, 'alt' => 'Ruang Small Meeting kapasitas 6 orang di Urban Office MERR Surabaya'],
            ['img' => 'assets/images/meetingroom/Big Meeting.webp', 'title' => 'Big Meeting', 'desc' => 'Mulai Rp 45.000 / pax', 'pax' => '15 Pax', 'type_id' => 2, 'alt' => 'Ruang Big Meeting kapasitas 15 orang lengkap proyektor di Surabaya'],
            ['img' => 'assets/images/meetingroom/Small Meeting (2).webp', 'title' => 'Small Meeting', 'desc' => 'Mulai Rp 125.000 / jam', 'pax' => '6 Pax', 'type_id' => 3, 'alt' => 'Sewa ruang meeting per jam untuk 6 orang di Urban Office Surabaya'],
            ['img' => 'assets/images/meetingroom/meeting klampis.webp', 'title' => 'Small Meeting Klampis', 'desc' => 'Mulai Rp 125.000 / jam', 'pax' => '6 Pax', 'type_id' => 1, 'alt' => 'Ruang meeting privat cabang Klampis Surabaya Timur'],
            ['img' => 'assets/images/meetingroom/Meeting 18 pax.webp', 'title' => 'Big Meeting', 'desc' => 'Mulai Rp 45.000 / pax', 'pax' => '18 Pax', 'type_id' => 5, 'alt' => 'Conference meeting room 18 orang di Urban Office MERR Surabaya'],
        ],
        'jakarta' => [
            ['img' => 'assets/images/meetingroom/Meeting 8 orang jaktim.webp', 'title' => 'Small Meeting', 'desc' => 'Mulai Rp 125.000 / jam', 'pax' => '8 Pax', 'type_id' => 1, 'alt' => 'Ruang meeting kapasitas 8 orang di Urban Office Cakung Jakarta Timur'],
            ['img' => 'assets/images/privateoffice/serviced-office-meeting.webp', 'title' => 'Executive Meeting', 'desc' => 'Mulai Rp 125.000 / jam', 'pax' => '6-8 Pax', 'type_id' => 1, 'alt' => 'Ruang rapat representatif untuk presentasi klien di Jakarta Timur'],
        ],
        'gresik' => [
            ['img' => 'assets/images/meetingroom/Big Meeting (2).webp', 'title' => 'Big Meeting', 'desc' => 'Mulai Rp 45.000 / pax', 'pax' => '15 Pax', 'type_id' => 4, 'alt' => 'Big Meeting Room untuk training dan rapat tim di Gresik'],
        ],
        'malang' => [
            ['img' => 'assets/images/privateoffice/office meeting 4 orang malang.webp', 'title' => 'Small Meeting', 'desc' => 'Mulai Rp 125.000 / jam', 'pax' => '4-6 Pax', 'type_id' => 1, 'alt' => 'Small Meeting Room 4-6 orang di Urban Office Blimbing Malang'],
            ['img' => 'assets/images/privateoffice/office meeting 8 - 12 orang malang.webp', 'title' => 'Big Meeting', 'desc' => 'Mulai Rp 45.000 / pax', 'pax' => '8-12 Pax', 'type_id' => 2, 'alt' => 'Big Meeting Room 8-12 orang dengan layar TV LED di Urban Office Malang'],
            ['img' => 'assets/images/meetingroom/Small Meeting.webp', 'title' => 'Small Meeting', 'desc' => 'Mulai Rp 125.000 / jam', 'pax' => '6 Pax', 'type_id' => 3, 'alt' => 'Ruang rapat per jam siap pakai di Kota Malang'],
            ['img' => 'assets/images/meetingroom/Big Meeting (3).webp', 'title' => 'Big Meeting', 'desc' => 'Mulai Rp 45.000 / pax', 'pax' => '15 Pax', 'type_id' => 5, 'alt' => 'Ruang meeting luas untuk seminar dan diskusi tim di Malang'],
            ['img' => 'assets/images/privateoffice/serviced-office-meeting.webp', 'title' => 'Executive Meeting', 'desc' => 'Mulai Rp 125.000 / jam', 'pax' => '6 Pax', 'type_id' => 1, 'alt' => 'Ruang pertemuan bisnis nyaman di Urban Office Malang'],
        ],
    ];

    $gallery_key = ($city_slug === 'surabaya-barat') ? 'surabaya' : $city_slug;
    if (isset($catalog[$gallery_key])) {
        return $catalog[$gallery_key];
    }

    return [
        ['img' => 'assets/images/meetingroom/Small Meeting.webp', 'title' => 'Small Meeting', 'desc' => 'Mulai Rp 125.000 / jam', 'pax' => '6 Pax', 'type_id' => 1, 'alt' => 'Small Meeting Room di Urban Office ' . $loc_suffix],
        ['img' => 'assets/images/meetingroom/Big Meeting.webp', 'title' => 'Big Meeting', 'desc' => 'Mulai Rp 45.000 / pax', 'pax' => '15 Pax', 'type_id' => 2, 'alt' => 'Big Meeting Room di Urban Office ' . $loc_suffix],
    ];
}

/**
 * Per-city Meeting Room FAQs (shared by FAQ UI section & JSON-LD FAQPage schema).
 */
function get_meeting_room_city_faqs(string $city_slug, array $branch): array {
    $city_label = ($city_slug === 'surabaya-barat')
        ? 'Surabaya Barat'
        : (($city_slug === 'jakarta') ? 'Jakarta Timur' : ($branch['city'] ?? ucwords(str_replace('-', ' ', $city_slug))));
    $short_title = get_private_office_branch_label($city_slug, $branch);
    $address     = $branch['address'] ?? '';

    $local_q = [
        'surabaya' => [
            'question' => 'Di mana lokasi Meeting Room Urban Office di Surabaya?',
            'answer'   => 'Ruang meeting kami tersedia di MERR Surabaya Timur (' . $address . '), Klampis, dan Grand Sungkono Lagoon Surabaya Barat. Ketiganya mudah diakses dan dilengkapi area parkir serta resepsionis penyambut tamu.'
        ],
        'surabaya-barat' => [
            'question' => 'Di mana lokasi Meeting Room Urban Office di Surabaya Barat?',
            'answer'   => 'Meeting Room Surabaya Barat berlokasi di Grand Sungkono Lagoon (' . $address . ') yang dekat akses Tol Satelit dan pusat bisnis Surabaya Barat, lengkap dengan fasilitas TV LED, proyektor, dan pantry.'
        ],
        'jakarta' => [
            'question' => 'Di mana lokasi Sewa Meeting Room Urban Office di Jakarta Timur?',
            'answer'   => 'Meeting Room Urban Office Jakarta berlokasi di Jakarta Timur (' . $address . '), strategis di koridor Jl. Raya Bekasi KM 17 dekat kawasan industri Pulogadung dan akses tol JORR Cakung.'
        ],
        'gresik' => [
            'question' => 'Apakah tersedia Meeting Room di PTGM Tower Gresik untuk rapat klien?',
            'answer'   => 'Ya, Urban Office Gresik di PTGM Tower Kebomas (' . $address . ') menyediakan Small Meeting hingga Big Meeting Room lengkap dengan proyektor, Smart TV, whiteboard, WiFi cepat, dan layanan resepsionis.'
        ],
        'malang' => [
            'question' => 'Apakah Meeting Room Urban Office Malang bisa disewa per jam atau harian?',
            'answer'   => 'Ya, ruang meeting kami di kawasan Blimbing, Malang (' . $address . ') dapat disewa fleksibel per jam, paket 4 jam, maupun 8 jam (full-day), dengan pilihan paket termasuk coffee break.'
        ],
    ];

    $faqs = [];
    if (isset($local_q[$city_slug])) {
        $faqs[] = $local_q[$city_slug];
    }

    $faqs[] = [
        'question' => 'Berapa harga sewa meeting room di Urban Office ' . $city_label . '?',
        'answer'   => 'Small Meeting di ' . $city_label . ' mulai Rp 125.000 per jam (Rp 300.000 untuk 4 jam, Rp 550.000 untuk 8 jam). Big Meeting dihitung per orang, mulai Rp 45.000/pax untuk 4 jam dan Rp 90.000/pax untuk 8 jam. Tersedia juga paket dengan coffee break.'
    ];
    $faqs[] = [
        'question' => 'Apa perbedaan paket dengan dan tanpa coffee break?',
        'answer'   => 'Paket dengan coffee break sudah termasuk kopi, teh, snack, dan air mineral untuk peserta. Contoh: Small Meeting 4 jam Rp 350.000 (dengan coffee break) vs Rp 300.000 (tanpa). Big Meeting 4 jam Rp 55.000/pax (dengan) vs Rp 45.000/pax (tanpa).'
    ];
    $faqs[] = [
        'question' => 'Apakah sewa Meeting Room di ' . $short_title . ' sudah termasuk proyektor dan whiteboard?',
        'answer'   => 'Ya, seluruh tipe Meeting Room kami sudah dilengkapi proyektor atau layar TV LED modern, kabel HDMI, serta papan tulis (whiteboard) dan spidol rapat.'
    ];
    $faqs[] = [
        'question' => 'Apakah tersedia akses WiFi dan berapa kecepatannya?',
        'answer'   => 'Ya, seluruh peserta rapat mendapatkan akses WiFi cepat hingga 100 Mbps secara gratis agar meeting hybrid atau presentasi online berjalan lancar.'
    ];

    return $faqs;
}

/**
 * Per-branch Virtual Office FAQs (shared by FAQ UI section & JSON-LD FAQPage schema).
 */
function get_virtual_office_branch_faqs(string $branch_slug, array $branch): array {
    $area_label  = ($branch_slug === 'jakarta-timur') ? 'Jakarta Timur (Cakung)' : ($branch['location'] ?? ($branch['city'] ?? ''));
    $short_title = get_private_office_branch_label($branch_slug, $branch);
    $address     = $branch['address'] ?? '';

    return [
        [
            'question' => 'Di mana alamat cabang Virtual Office Urban Office di ' . $area_label . '?',
            'answer'   => 'Alamat bisnis resmi cabang ' . $short_title . ' berada di ' . $address . '. Lokasi strategis ini dapat digunakan untuk domisili perusahaan, penerimaan surat/paket, dan pertemuan dengan klien.'
        ],
        [
            'question' => 'Apa perbedaan paket Virtual Office Business, Starter, Luxury, dan Priority?',
            'answer'   => 'Paket Business (di cabang Surabaya Timur Klampis & Business Merr di MERR Surabaya) berfokus pada alamat bisnis resmi serta penanganan surat dan paket masuk. Paket Starter (Populer) menambah akses gratis Meeting Room 4 jam/bulan dan gratis website (khusus Surabaya & Surabaya Timur). Paket Luxury menawarkan akses Meeting Room 6 jam/bulan dan nomor telepon bersama, sementara Priority (seluruh cabang kecuali Klampis) menyediakan Meeting Room 8 jam/bulan, coworking space 1 orang, dan gratis pembuatan website bisnis.'
        ],
        [
            'question' => 'Apa saja yang termasuk dalam layanan Virtual Office ' . $area_label . '?',
            'answer'   => 'Layanan mencakup penggunaan alamat kantor bergengsi, penanganan surat dan paket masuk dengan notifikasi WhatsApp real-time, resepsionis profesional untuk penyambutan tamu, serta kuota penggunaan meeting room sesuai paket pilihan.'
        ],
        [
            'question' => 'Berapa lama proses aktivasi Virtual Office setelah pembayaran?',
            'answer'   => 'Layanan Virtual Office Anda dapat langsung aktif setelah pembayaran terverifikasi (settlement) melalui portal resmi my.urbanoffice.id dan masuk ke rekening resmi PT URBAN KREASI BERSAMA.'
        ],
        [
            'question' => 'Bagaimana jika ingin melakukan perpanjangan sewa Virtual Office?',
            'answer'   => 'Perpanjangan sewa dapat dilakukan dengan mudah melalui tim admin kami sebelum masa kontrak berakhir cukup dengan konfirmasi "Perpanjangan Sewa".'
        ]
    ];
}

/**
 * Return public cities offering Coworking Space, as [citySlug => cityLabel].
 */
function get_coworking_cities(): array {
    return get_service_cities('coworking');
}

/**
 * Per-city Coworking Space gallery items using verified local workspace assets
 * and SEO-descriptive alt attributes tailored to Surabaya and Malang.
 */
function get_coworking_city_gallery(string $city_slug, array $branch): array {
    $city_label  = ($city_slug === 'surabaya-timur') ? 'Surabaya Timur' : ($branch['city'] ?? ucwords(str_replace('-', ' ', $city_slug)));
    $short_title = get_private_office_branch_label($city_slug, $branch);
    $loc_suffix  = $short_title . ' ' . $city_label;

    $catalog = [
        'surabaya' => [
            ['img' => 'assets/images/coworkingspace/Foto Coworking Space.webp', 'title' => 'Coworking Shared Area', 'desc' => 'Mulai Rp 10.000 / jam', 'badge' => 'Hot Desk', 'type_id' => 1, 'alt' => 'Area kerja bersama Coworking Space Hot Desk di Urban Office MERR Surabaya'],
            ['img' => 'assets/images/coworkingspace/Foto Coworking Space Klampis(2).webp', 'title' => 'Coworking Space Klampis', 'desc' => 'Mulai Rp 45.000 / hari', 'badge' => 'Klampis Area', 'type_id' => 2, 'alt' => 'Ruang Coworking Space harian tenang di Urban Office Klampis Surabaya Timur'],
            ['img' => 'assets/images/sharingroom/sharing-room-desk.webp', 'title' => 'Monthly Dedicated Desk', 'desc' => 'Mulai Rp 750.000 / bulan', 'badge' => 'Monthly Pass', 'type_id' => 3, 'alt' => 'Meja kerja bulanan Coworking Monthly di Urban Office Surabaya'],
            ['img' => 'assets/images/sharingroom/sharing-room-lounge.webp', 'title' => 'Lounge & Collab Area', 'desc' => 'Mulai Rp 45.000 / hari', 'badge' => 'Daily Pass', 'type_id' => 2, 'alt' => 'Area lounge kolaboratif Coworking Space di Urban Office Surabaya'],
        ],
        'malang' => [
            ['img' => 'assets/images/privateoffice/open space malang.jpeg', 'title' => 'Open Space Hot Desk', 'desc' => 'Mulai Rp 10.000 / jam', 'badge' => 'Hot Desk', 'type_id' => 1, 'alt' => 'Area Open Space Coworking per jam di Urban Office Blimbing Malang'],
            ['img' => 'assets/images/privateoffice/office meeting 4 orang malang.webp', 'title' => 'Daily Working Desk', 'desc' => 'Mulai Rp 45.000 / hari', 'badge' => 'Daily Pass', 'type_id' => 2, 'alt' => 'Meja kerja harian Coworking Space dengan WiFi cepat di Kota Malang'],
            ['img' => 'assets/images/privateoffice/office malang.webp', 'title' => 'Monthly Workspace & Lounge', 'desc' => 'Mulai Rp 750.000 / bulan', 'badge' => 'Monthly Pass', 'type_id' => 3, 'alt' => 'Akses Coworking bulanan dan lounge kolaborasi di Urban Office Malang'],
            ['img' => 'assets/images/privateoffice/Malang.webp', 'title' => 'Workspace Area Blimbing', 'desc' => 'Mulai Rp 45.000 / hari', 'badge' => 'Malang Area', 'type_id' => 2, 'alt' => 'Ruang kerja bersama nyaman di cabang Urban Office Blimbing Malang'],
        ],
    ];

    $gallery_key = ($city_slug === 'surabaya-timur') ? 'surabaya' : $city_slug;
    if (isset($catalog[$gallery_key])) {
        return $catalog[$gallery_key];
    }

    return [
        ['img' => 'assets/images/coworkingspace/Foto Coworking Space.webp', 'title' => 'Coworking Shared Area', 'desc' => 'Mulai Rp 10.000 / jam', 'badge' => 'Hot Desk', 'type_id' => 1, 'alt' => 'Coworking Space di Urban Office ' . $loc_suffix],
        ['img' => 'assets/images/sharingroom/sharing-room-desk.webp', 'title' => 'Coworking Daily Pass', 'desc' => 'Mulai Rp 45.000 / hari', 'badge' => 'Daily Pass', 'type_id' => 2, 'alt' => 'Daily Coworking Desk di Urban Office ' . $loc_suffix],
    ];
}

/**
 * Per-city Coworking Space FAQs (shared by FAQ UI section & JSON-LD FAQPage schema).
 */
function get_coworking_city_faqs(string $city_slug, array $branch): array {
    $city_label  = ($city_slug === 'surabaya-timur') ? 'Surabaya Timur' : ($branch['city'] ?? ucwords(str_replace('-', ' ', $city_slug)));
    $short_title = get_private_office_branch_label($city_slug, $branch);
    $address     = $branch['address'] ?? '';

    $local_q = [
        'surabaya' => [
            'question' => 'Di mana lokasi Coworking Space Urban Office di Surabaya?',
            'answer'   => 'Coworking Space kami di Surabaya tersedia di cabang MERR Surabaya Timur (' . $address . ') yang dilengkapi area café di Lantai 1, serta cabang Klampis di Ruko Klampis Megah dekat kampus ITS dan UNAIR.'
        ],
        'surabaya-timur' => [
            'question' => 'Di mana lokasi Coworking Space Klampis Surabaya Timur?',
            'answer'   => 'Coworking Space Klampis berlokasi di ' . $address . ', strategis dekat kawasan kuliner Klampis Jaya serta kampus ITS dan UNAIR dengan suasana kerja tenang dan kondusif.'
        ],
        'malang' => [
            'question' => 'Di mana lokasi Coworking Space Urban Office di Kota Malang?',
            'answer'   => 'Coworking Space Malang berlokasi di kawasan bisnis Blimbing (' . $address . '), mudah diakses untuk WFA, mahasiswa, freelancer, maupun tim startup di Kota Malang.'
        ],
    ];

    $faqs = [];
    if (isset($local_q[$city_slug])) {
        $faqs[] = $local_q[$city_slug];
    }

    $faqs[] = [
        'question' => 'Berapa tarif sewa Coworking Space di Urban Office ' . $city_label . '?',
        'answer'   => 'Tarif Coworking Space di ' . $city_label . ' mulai Rp 10.000/jam (Coworking Hours), Rp 45.000/hari (Daily Pass), dan Rp 750.000/bulan (Monthly Pass) tanpa biaya tersembunyi.'
    ];
    $faqs[] = [
        'question' => 'Apa saja fasilitas yang tersedia di Coworking Space ' . $short_title . '?',
        'answer'   => 'Seluruh area dilengkapi meja dan kursi ergonomis, koneksi WiFi berkecepatan tinggi hingga 100 Mbps, colokan listrik (power outlet) di setiap meja, serta free flow kopi, teh, dan air mineral.'
    ];
    $faqs[] = [
        'question' => 'Apakah bisa coba harian dulu sebelum berlangganan Coworking bulanan?',
        'answer'   => 'Tentu bisa. Anda dapat memesan Coworking Hours (per jam) atau Daily Pass harian terlebih dahulu untuk mencoba kenyamanan ruang kerja kami sebelum mendaftar paket bulanan.'
    ];

    return $faqs;
}

/**
 * Return public cities offering Event Space, as [citySlug => cityLabel].
 */
function get_event_space_cities(): array {
    return get_service_cities('event-space');
}

/**
 * Per-city Event Space gallery items using verified local event space assets
 * and SEO-descriptive alt attributes tailored to Surabaya and Malang.
 */
function get_event_space_city_gallery(string $city_slug, array $branch): array {
    $city_label  = $branch['city'] ?? ucwords(str_replace('-', ' ', $city_slug));
    $short_title = get_private_office_branch_label($city_slug, $branch);
    $loc_suffix  = $short_title . ' ' . $city_label;

    $catalog = [
        'surabaya' => [
            ['img' => 'assets/images/eventspace/Foto Event Space (2).webp', 'title' => 'Event Space Classroom', 'desc' => 'Tata letak meja-kursi untuk kelas, training & workshop.', 'pax' => '30-80 Pax', 'alt' => 'Event Space tata letak Classroom untuk workshop dan training di Urban Office MERR Surabaya'],
            ['img' => 'assets/images/eventspace/Foto Event Space (4).webp', 'title' => 'Grand Event Space', 'desc' => 'Area luas lengkap dengan proyektor HD & sistem audio visual.', 'pax' => '30-80 Pax', 'alt' => 'Grand Event Space berkapasitas besar untuk seminar bisnis di Surabaya'],
            ['img' => 'assets/images/eventspace/WhatsApp Image 2026-06-08 at 11.44.08.webp', 'title' => 'Event Space Boardroom', 'desc' => 'Layout U-Shape atau boardroom untuk rapat eksekutif besar.', 'pax' => '30-80 Pax', 'alt' => 'Ruang acara tata letak Boardroom dan U-Shape di Urban Office Surabaya'],
            ['img' => 'assets/images/eventspace/WhatsApp Image 2026-06-08 at 11.44.58.webp', 'title' => 'Theater Event Layout', 'desc' => 'Tata ruang Theater untuk konferensi, talkshow & press release.', 'pax' => '30-80 Pax', 'alt' => 'Event Space konsep Theater untuk konferensi dan talkshow di Surabaya'],
        ],
        'malang' => [
            ['img' => 'assets/images/eventspace/event space outdor malang.jpeg', 'title' => 'Ruang Seminar Malang', 'desc' => 'Area representatif untuk seminar, workshop & gathering di Blimbing.', 'pax' => '30-80 Pax', 'alt' => 'Sewa Event Space ruang seminar lengkap proyektor di Blimbing Malang'],
            ['img' => 'assets/images/privateoffice/office meeting 8 - 12 orang malang.webp', 'title' => 'Classroom & Workshop Setup', 'desc' => 'Penataan meja-kursi fleksibel untuk pelatihan tim & komunitas.', 'pax' => '30-80 Pax', 'alt' => 'Event Space setting Classroom untuk workshop bisnis di Kota Malang'],
            ['img' => 'assets/images/eventspace/event space indoor malang.webp', 'title' => 'U-Shape & Forum Setup', 'desc' => 'Format diskusi interaktif lengkap dengan whiteboard & flipchart.', 'pax' => '30-80 Pax', 'alt' => 'Ruang Event Space setting U-Shape untuk forum diskusi di Urban Office Malang'],
            ['img' => 'assets/images/eventspace/WhatsApp Image 2026-06-08 at 11.44.58.webp', 'title' => 'Theater Conference Setup', 'desc' => 'Cocok untuk peluncuran produk, talkshow & presentasi publik.', 'pax' => '30-80 Pax', 'alt' => 'Sewa aula Theater Event Space strategis di Kota Malang'],
        ],
    ];

    if (isset($catalog[$city_slug])) {
        return $catalog[$city_slug];
    }

    return [
        ['img' => 'assets/images/eventspace/Foto Event Space (2).webp', 'title' => 'Event Space Classroom', 'desc' => 'Tata letak meja-kursi untuk kelas & workshop.', 'pax' => '30-80 Pax', 'alt' => 'Event Space Classroom di Urban Office ' . $loc_suffix],
        ['img' => 'assets/images/eventspace/Foto Event Space (4).webp', 'title' => 'Grand Event Space', 'desc' => 'Area luas lengkap dengan audio visual.', 'pax' => '30-80 Pax', 'alt' => 'Grand Event Space di Urban Office ' . $loc_suffix],
    ];
}

/**
 * Per-city Event Space FAQs (shared by FAQ UI section & JSON-LD FAQPage schema).
 */
function get_event_space_city_faqs(string $city_slug, array $branch): array {
    $city_label  = $branch['city'] ?? ucwords(str_replace('-', ' ', $city_slug));
    $address     = $branch['address'] ?? '';

    $local_q = [
        'surabaya' => [
            'question' => 'Di mana lokasi Event Space Urban Office di Surabaya?',
            'answer'   => 'Event Space kami berlokasi di gedung Urban Office MERR Surabaya Timur (' . $address . '), dilengkapi area registrasi tamu, café di Lantai 1, dan area parkir gratis untuk peserta acara.'
        ],
        'malang' => [
            'question' => 'Di mana lokasi sewa Event Space Urban Office di Malang?',
            'answer'   => 'Event Space Malang berada di cabang Urban Office Blimbing (' . $address . '), mudah dijangkau dari pusat Kota Malang untuk seminar, pelatihan tim, maupun pertemuan komunitas.'
        ],
    ];

    $faqs = [];
    if (isset($local_q[$city_slug])) {
        $faqs[] = $local_q[$city_slug];
    }

    $faqs[] = [
        'question' => 'Berapa harga sewa Event Space di ' . $city_label . ' dan kapasitas maksimalnya?',
        'answer'   => 'Harga sewa Event Space mulai Rp 45.000/pax untuk Half-Day (4 jam) dan Rp 90.000/pax untuk Full-Day (8 jam), dengan kapasitas fleksibel untuk 30 hingga 80 peserta.'
    ];
    $faqs[] = [
        'question' => 'Apakah tata letak (layout) kursi dan peralatan AV sudah disediakan?',
        'answer'   => 'Ya, layout dapat diatur bebas (Theater, Classroom, U-Shape, maupun Boardroom) dan sudah termasuk proyektor HD, layar lebar, sound system, wireless mic, whiteboard, serta WiFi berkecepatan tinggi hingga 100 Mbps.'
    ];
    $faqs[] = [
        'question' => 'Apakah tersedia layanan konsumsi / coffee break untuk peserta event?',
        'answer'   => 'Tersedia. Anda dapat memesan paket air mineral, snack box, kopi/teh, hingga layanan catering makan siang/malam sesuai kebutuhan acara.'
    ];

    return $faqs;
}

/**
 * Shared FAQ sets for Homepage, Sharing Room, Pajak & Akunting, and Business Legal pages
 * so UI FAQ accordions and FAQPage JSON-LD stay 100% synchronized.
 */
function get_homepage_faqs(): array {
    return [
        [
            'question' => 'Di mana saja lokasi Urban Office?',
            'answer'   => 'Urban Office hadir di Surabaya (MERR Surabaya Timur, Klampis Jaya, dan Grand Sungkono Lagoon Surabaya Barat), Jakarta Timur (Cakung), Gresik (PTGM Tower Kebomas), Medan, serta Malang (Blimbing Indah Megah).'
        ],
        [
            'question' => 'Bagaimana jam operasional Urban Office?',
            'answer'   => 'Jam operasional seluruh cabang adalah Senin - Sabtu pukul 08:00 - 15:00 WIB, khusus untuk cabang Urban Office - MERR dan Urban Office - Klampis beroperasi pukul 08:00 - 17:00 WIB.'
        ],
        [
            'question' => 'Apakah tersedia parkir?',
            'answer'   => 'Ya, tempat parkir tersedia dan gratis (free).'
        ],
        [
            'question' => 'Apakah lokasi strategis / mudah dijangkau?',
            'answer'   => 'Ya, seluruh lokasi Urban Office berada di titik strategis kawasan bisnis kota dan sangat mudah dijangkau transportasi.'
        ],
        [
            'question' => 'Metode pembayaran apa saja yang diterima?',
            'answer'   => 'Pembayaran bisa langsung melalui portal my.urbanoffice.id yang mendukung Virtual Account berbagai bank (Mandiri, BNI, BRI, Permata Bank, CIMB Niaga, BSI), QRIS, serta Gopay.'
        ],
        [
            'question' => 'Bagaimana cara mendapatkan invoice / kwitansi?',
            'answer'   => 'Anda dapat langsung mengakses dan mengunduh invoice serta kwitansi pembayaran di portal pelanggan my.urbanoffice.id.'
        ],
        [
            'question' => 'Apakah ada promo atau diskon yang sedang berjalan?',
            'answer'   => 'Setiap informasi promo atau diskon terbaru akan kami sampaikan secara berkala, atau Anda bisa langsung memantaunya melalui portal resmi my.urbanoffice.id.'
        ],
        [
            'question' => 'Bagaimana cara komplain jika ada masalah dengan layanan?',
            'answer'   => 'Anda bisa langsung datang ke cabang Urban Office tempat Anda melakukan transaksi atau menghubungi kami melalui nomor WhatsApp resmi 0851-0762-0100.'
        ],
        [
            'question' => 'Apakah ada customer service yang bisa dihubungi di luar jam kerja?',
            'answer'   => 'Ya, Anda dapat menghubungi tim customer service kami di luar jam kerja melalui WhatsApp di nomor 0851-0762-0100 atau telepon +62-31-87855578.'
        ]
    ];
}

function get_sharing_room_faqs(): array {
    return [
        [
            'question' => 'Apakah Sharing Room berbeda dengan Coworking Space?',
            'answer'   => 'Ya, sangat berbeda. Sharing Room adalah meja kerja terdedikasi (Dedicated Desk Rp 1.000.000/bulan atau Rp 12.000.000/tahun) di dalam ruang kantor privat yang tenang bersama penyewa tetap lainnya. Sedangkan Coworking Space berkonsep area kerja terbuka (open space) harian/per jam.'
        ],
        [
            'question' => 'Bagaimana jika ingin melakukan perpanjangan sewa?',
            'answer'   => 'Perpanjangan sewa Sharing Room dapat dikonfirmasikan langsung melalui tim admin kami sebelum masa kontrak berakhir dengan menyebutkan "Perpanjangan Sewa".'
        ],
        [
            'question' => 'Apakah ada koneksi WiFi? Kecepatannya berapa?',
            'answer'   => 'Tentu saja. Seluruh penyewa Sharing Room mendapatkan akses WiFi stabil berkecepatan tinggi up to 100 Mbps, listrik, AC, layanan resepsionis, serta free flow kopi dan teh.'
        ]
    ];
}

function get_pajak_akunting_faqs(): array {
    return [
        [
            'question' => 'Dokumen apa saja yang diperlukan untuk pengurusan pelaporan pajak bulanan?',
            'answer'   => 'Untuk pelaporan rutin bulanan, Anda cukup menyiapkan mutasi rekening bank (rekening koran), rekap penjualan & pembelian (invoice/faktur), serta bukti pemotongan pajak dari pihak ketiga (bila ada).'
        ],
        [
            'question' => 'Kapan batas waktu pelaporan dan pembayaran pajak badan usaha?',
            'answer'   => 'Pembayaran PPh bulanan paling lambat tanggal 10 atau 15 bulan berikutnya, sedangkan pelaporannya paling lambat tanggal 20. Untuk SPT Tahunan PPh Badan, batas waktu pelaporan dan pembayarannya adalah 30 April tahun berikutnya.'
        ],
        [
            'question' => 'Apakah perusahaan Non-PKP tetap wajib melaporkan pajak?',
            'answer'   => 'Ya, perusahaan Non-PKP tetap berkewajiban melakukan pelaporan SPT Tahunan PPh Badan dan membayar pajak atas penghasilan bruto perusahaan sesuai ketentuan perpajakan (seperti PPh Final 0.5%). Mereka hanya tidak memungut/melaporkan PPN.'
        ],
        [
            'question' => 'Apa itu SP2DK dan bagaimana tim Urban Office dapat membantu?',
            'answer'   => 'SP2DK adalah Surat Permintaan Penjelasan atas Data dan/atau Keterangan yang diterbitkan oleh KPP apabila ditemukan ketidaksesuaian laporan. Tim ahli perpajakan kami (berlisensi BKP) akan menganalisis data keuangan Anda dan menyusun surat tanggapan formal serta mendampingi pertemuan dengan pihak pajak.'
        ],
        [
            'question' => 'Apakah layanan ini bisa sekaligus membantu pembukuan perusahaan?',
            'answer'   => 'Bisa. Tim kami dapat membantu merapikan pembukuan, merekap transaksi, menyusun laporan keuangan dasar, dan menyiapkan data yang dibutuhkan untuk pelaporan pajak bulanan maupun tahunan.'
        ],
        [
            'question' => 'Apakah data keuangan perusahaan saya dijamin kerahasiaannya?',
            'answer'   => 'Tentu saja. Kami menjamin 100% kerahasiaan seluruh dokumen, data transaksi, dan laporan perpajakan Anda. Kerahasiaan ini dituangkan secara resmi dalam Non-Disclosure Agreement (NDA) yang ditandatangani bersama di awal kerjasama.'
        ]
    ];
}

function get_pendirian_faqs(string $slug): array {
    $doc_q = [
        'pendirian-perorangan-plus-virtual-office' => [
            'question' => 'Dokumen apa saja yang dibutuhkan untuk mendirikan PT Perorangan?',
            'answer'   => 'Dokumen dan data yang diperlukan untuk pendirian PT Perorangan meliputi: KTP pemilik PT, NPWP pemilik PT, modal dasar, bidang usaha (KBLI), nomor telepon, alamat email + password, Kartu Keluarga (KK), serta alamat lengkap PT.'
        ],
        'pendirian-pt-include-virtual-office' => [
            'question' => 'Dokumen apa saja yang dibutuhkan untuk mendirikan PT Badan?',
            'answer'   => 'Dokumen dan data yang diperlukan untuk pendirian PT Badan meliputi: nama PT badan, modal dasar, bidang usaha (KBLI), KTP & NPWP pengurus PT, KTP & NPWP pemegang saham, komposisi pemilik saham, alamat lengkap RT/RW, nomor telepon PT, serta alamat email dan password.'
        ],
        'pendirian-cv-virtual-office' => [
            'question' => 'Dokumen apa saja yang dibutuhkan untuk mendirikan CV?',
            'answer'   => 'Dokumen dan data yang diperlukan untuk pendirian CV meliputi: nama CV, alamat domisili CV, NPWP direktur, bidang usaha (KBLI), nomor telepon CV, serta alamat email dan password.'
        ],
        'pendirian-pma-plus-virtual-office' => [
            'question' => 'Dokumen apa saja yang dibutuhkan untuk mendirikan PT PMA?',
            'answer'   => 'Dokumen dan data yang diperlukan untuk pendirian PT PMA meliputi: nama PT, modal dasar minimal 10 Miliar, modal disetor minimal 10 Miliar, bidang usaha (KBLI), KTP & NPWP pengurus PT, KTP/Paspor & NPWP pemegang saham (minimal 1 orang Warga Negara Asing), komposisi pemegang saham, alamat lengkap PT, nomor telepon PT, serta alamat email dan password.'
        ],
    ];

    $faqs = [
        [
            'question' => 'Berapa lama proses pendirian PT / CV selesai?',
            'answer'   => 'Proses pendirian PT / CV kurang lebih membutuhkan waktu 1 minggu setelah semua persyaratan dokumen dinyatakan lengkap.'
        ]
    ];
    if (isset($doc_q[$slug])) {
        $faqs[] = $doc_q[$slug];
    }
    $faqs[] = [
        'question' => 'Apakah bisa konsultasi terlebih dahulu sebelum memutuskan jenis badan usaha?',
        'answer'   => 'Tentu saja bisa. Anda dapat melakukan konsultasi gratis terlebih dahulu dengan tim legal kami untuk menentukan jenis badan usaha yang paling tepat untuk model bisnis Anda.'
    ];
    return $faqs;
}

/**
 * Web App Portal URLs & Booking Auto-Fill Integration Helpers
 * Safe fallbacks ensure production works even before inc/config.php is updated.
 */
function get_webapp_base_url(): string {
    return defined('APP_BASE_URL') ? rtrim(APP_BASE_URL, '/') : 'https://my.urbanoffice.co.id';
}

function get_webapp_home_url(): string {
    return defined('APP_HOME_URL') ? APP_HOME_URL : (get_webapp_base_url() . '/dashboard/home');
}

function get_webapp_booking_base_url(): string {
    return defined('APP_BOOKING_URL') ? APP_BOOKING_URL : (get_webapp_base_url() . '/dashboard/bookingform');
}

/**
 * Maps Landing Page service category slugs to exact Web App room_types.name values.
 */
function get_webapp_room_type(string $category): string {
    $map = [
        'virtual-office'      => 'Virtual Office',
        'private-office'      => 'Private Office',
        'meeting-room'        => 'Meeting Room',
        'coworking'           => 'Coworking Space',
        'event-space'         => 'Event Space',
        'sharing-room-office' => 'Sharing Room',
    ];
    return $map[strtolower(trim($category))] ?? '';
}

/**
 * Safely returns $locations_db from global scope or loads inc/locations_data.php.
 */
function get_locations_db_safe(): array {
    global $locations_db;
    if (!isset($locations_db) || !is_array($locations_db)) {
        $path = __DIR__ . '/locations_data.php';
        if (file_exists($path)) {
            require $path;
        }
    }
    return (isset($locations_db) && is_array($locations_db)) ? $locations_db : [];
}

/**
 * Returns true if the branch is registered & active for direct Web App booking prefill.
 */
function has_webapp_booking(string $branch_slug): bool {
    $db = get_locations_db_safe();
    if (!isset($db[$branch_slug]) || !is_public_location($db[$branch_slug])) {
        return false;
    }
    $b = $db[$branch_slug];
    return !empty($b['app_city_id']) && !empty($b['app_location_id']);
}

/**
 * Builds the Web App booking URL with auto-fill query params (?city_id=&location_id=&room_type=).
 * Falls back cleanly to $fallback_url or a context-aware WhatsApp link when a branch
 * (e.g. Malang) is not yet registered in the Web App database.
 */
function build_webapp_booking_url(string $branch_slug, string $category, ?string $fallback_url = null): string {
    $db = get_locations_db_safe();
    $room_type = get_webapp_room_type($category);

    if (isset($db[$branch_slug]) && is_public_location($db[$branch_slug])) {
        $branch = $db[$branch_slug];
        if (!empty($branch['app_city_id']) && !empty($branch['app_location_id']) && $room_type !== '') {
            return get_webapp_booking_base_url() . '?' . http_build_query([
                'city_id'     => (int) $branch['app_city_id'],
                'location_id' => (int) $branch['app_location_id'],
                'room_type'   => $room_type,
            ]);
        }
    }

    if ($fallback_url !== null && $fallback_url !== '') {
        return $fallback_url;
    }

    $branch_label = isset($db[$branch_slug]['title']) ? $db[$branch_slug]['title'] : ucwords(str_replace('-', ' ', $branch_slug));
    $service_label = $room_type !== '' ? $room_type : ucwords(str_replace('-', ' ', $category));
    $wa_num = defined('WHATSAPP_NUMBER_SALES') ? WHATSAPP_NUMBER_SALES : (defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '6285107620100');
    $msg = "Halo Urban Office, saya ingin memesan layanan {$service_label} di {$branch_label}. Mohon info ketersediaan dan langkah booking-nya.";
    return 'https://api.whatsapp.com/send?phone=' . $wa_num . '&text=' . rawurlencode($msg);
}

/**
 * Resolves the active branch on a city service landing page, honoring ?cabang={slug}.
 */
function resolve_active_booking_branch(array $svc_branches): ?array {
    if (empty($svc_branches)) {
        return null;
    }
    $requested = isset($_GET['cabang']) ? strtolower(trim((string) $_GET['cabang'])) : '';
    if ($requested !== '') {
        foreach ($svc_branches as $b) {
            if (strtolower((string) ($b['slug'] ?? '')) === $requested) {
                return $b;
            }
        }
    }
    return $svc_branches[0];
}

/**
 * Computes the clean Landing Page URL for a given (service category, branch) pair.
 * Used by the Homepage Search Bar so every search lands on the exact branch/city page.
 */
function get_service_landing_url(string $category, string $branch_slug, array $branch): string {
    $city_slug = strtolower(trim((string) ($branch['city'] ?? 'surabaya')));
    $same_city_branches = service_city_branches($category, $city_slug);
    $is_multi_branch_city = count($same_city_branches) > 1;
    $branch_query = $is_multi_branch_city ? ('?cabang=' . urlencode($branch_slug) . '#pricing') : '#pricing';

    switch ($category) {
        case 'virtual-office':
            return BASE_URL . 'virtual-office-' . $branch_slug . '/';
        case 'private-office':
            return BASE_URL . 'sewa-kantor-' . $city_slug . '/' . $branch_query;
        case 'meeting-room':
            return BASE_URL . 'meeting-room-' . $city_slug . '/' . $branch_query;
        case 'coworking':
            $base = ($city_slug === 'surabaya')
                ? (BASE_URL . 'coworking-space-urban-office/')
                : (BASE_URL . 'coworking-space-' . $city_slug . '/');
            return $base . $branch_query;
        case 'event-space':
            $base = ($city_slug === 'surabaya')
                ? (BASE_URL . 'event-space-55k-perjam-urbanoffice/')
                : (BASE_URL . 'event-space-' . $city_slug . '/');
            return $base . $branch_query;
        case 'sharing-room-office':
            if ($city_slug === 'surabaya') {
                return BASE_URL . 'sharing-room-office/#pricing';
            }
            return BASE_URL . 'sharing-room-office/?lokasi=' . urlencode($city_slug) . '#pricing';
        default:
            return BASE_URL . 'lokasi-urban-office/' . $branch_slug . '/';
    }
}

/**
 * Builds the single-source-of-truth catalog for the Homepage Search Bar (Option A).
 * Only includes public branches and the services each branch actually offers.
 */
function get_homepage_search_catalog(): array {
    $db = get_locations_db_safe();
    $catalog = [];
    $tracked_categories = [
        'virtual-office',
        'private-office',
        'meeting-room',
        'coworking',
        'event-space',
        'sharing-room-office',
    ];

    foreach ($db as $slug => $b) {
        if (!is_public_location($b) || empty($b['pricing'])) {
            continue;
        }
        $present = [];
        foreach ($b['pricing'] as $pkg) {
            $cat = $pkg['category'] ?? '';
            if (in_array($cat, $tracked_categories, true)) {
                $present[$cat] = true;
            }
        }
        if (empty($present)) {
            continue;
        }

        $services = [];
        foreach (array_keys($present) as $cat) {
            $services[$cat] = [
                'landingUrl' => get_service_landing_url($cat, $slug, $b),
                'bookingUrl' => build_webapp_booking_url($slug, $cat),
            ];
        }

        $city = (string) ($b['city'] ?? '');
        $location = (string) ($b['location'] ?? $city);
        $short_title = (string) ($b['short_title'] ?? ucwords(str_replace('-', ' ', $slug)));
        $display_label = (strtolower($short_title) === strtolower($city))
            ? $city
            : ($city . ' — ' . $short_title);

        $keywords = strtolower(implode(' ', array_filter([
            $slug,
            str_replace('-', ' ', $slug),
            $city,
            $location,
            $short_title,
            $b['title'] ?? '',
            $b['address'] ?? '',
        ])));

        $catalog[] = [
            'slug'          => $slug,
            'city'          => $city,
            'location'      => $location,
            'shortTitle'    => $short_title,
            'label'         => $display_label,
            'sublabel'      => $location . ' • ' . ($b['title'] ?? $short_title),
            'lat'           => isset($b['lat']) ? (float) $b['lat'] : null,
            'lng'           => isset($b['lng']) ? (float) $b['lng'] : null,
            'hasWebapp'     => has_webapp_booking($slug),
            'keywords'      => $keywords,
            'services'      => $services,
        ];
    }

    return $catalog;
}
