<?php
/**
 * Automation API — shared bootstrap.
 *
 * NOT a callable endpoint. Included by ingest.php / moderate.php.
 * Provides: JSON I/O helpers, token auth (settings.automation_api_token),
 * slugify + unique-slug, author resolution, term (category/tag) mapping,
 * and a hardened image downloader that writes into assets/images/blog/.
 *
 * All responses are JSON. PHP errors are never leaked into the body.
 */

// Not a callable endpoint — only meant to be require'd by the API scripts.
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === '_bootstrap.php') {
    header('HTTP/1.1 403 Forbidden');
    exit('Direct access forbidden.');
}

require_once __DIR__ . '/../inc/functions.php'; // pulls in config.php + database.php

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
ini_set('display_errors', '0'); // DEV_MODE turns this on in config.php; force off so JSON stays clean

// Berikan kelonggaran waktu & memori untuk download Fal.ai, komposit GD, dan upload CDN
@set_time_limit(180);
@ini_set('max_execution_time', '180');
@ini_set('memory_limit', '512M');

// ---------------------------------------------------------------------------
// Response helpers
// ---------------------------------------------------------------------------
function api_out(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
function api_ok(array $data = []): void {
    api_out(200, array_merge(['ok' => true], $data));
}
function api_fail(int $status, string $message, array $extra = []): void {
    api_out($status, array_merge(['ok' => false, 'error' => $message], $extra));
}

// Catch fatals so the caller always gets JSON, never a blank 500 / HTML.
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }
        error_log('Automation API fatal: ' . $e['message'] . ' @ ' . $e['file'] . ':' . $e['line']);
        echo json_encode(['ok' => false, 'error' => 'Kesalahan server internal.']);
    }
});

// ---------------------------------------------------------------------------
// Request helpers
// ---------------------------------------------------------------------------
function api_require_post(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        api_fail(405, 'Gunakan metode POST.');
    }
}

function api_require_get(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        api_fail(405, 'Gunakan metode GET.');
    }
}

function api_read_json(): array {
    $raw = file_get_contents('php://input');
    if ($raw === '' || $raw === false) {
        return [];
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        api_fail(400, 'Body request harus JSON valid.');
    }
    return $data;
}

/**
 * Verify the automation token. Accepts either header:
 *   X-Automation-Token: <token>
 *   Authorization: Bearer <token>
 * Compared in constant time against settings.automation_api_token.
 */
function api_require_token(): void {
    $provided = $_SERVER['HTTP_X_AUTOMATION_TOKEN'] ?? '';
    if ($provided === '' && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
        if (preg_match('/Bearer\s+(.+)/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) {
            $provided = trim($m[1]);
        }
    }
    $row = Database::fetch("SELECT value FROM settings WHERE name = 'automation_api_token'");
    $stored = $row['value'] ?? '';
    if ($stored === '') {
        api_fail(500, 'Token automation belum diset di server. Jalankan: php _api/_setup_token.php');
    }
    if ($provided === '' || !hash_equals($stored, (string)$provided)) {
        api_fail(401, 'Token tidak valid.');
    }
}

// ---------------------------------------------------------------------------
// Domain helpers
// ---------------------------------------------------------------------------

/** Normalise a source_url for storage/dedup: trim + cap at column width (512). */
function api_norm_source(string $u): string {
    $u = trim($u);
    return $u === '' ? '' : mb_substr($u, 0, 512);
}

/** Slugify a title: lowercase, dash-separated, safe chars only. */
function api_slugify(string $text): string {
    $slug = strtolower(trim($text));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug !== '' ? $slug : 'artikel';
}

/** Ensure slug is unique in posts, appending -2, -3 ... (timestamp as last resort). */
function api_unique_slug(string $slug): string {
    $base = $slug;
    $i = 2;
    while (Database::fetch("SELECT id FROM posts WHERE slug = ?", [$slug])) {
        $slug = $base . '-' . $i;
        if (++$i > 50) { $slug = $base . '-' . time(); break; }
    }
    return $slug;
}

/**
 * Resolve the author_id for AI-generated posts. Order of preference:
 *   1. settings.automation_author_id (if it points to a real user)
 *   2. a user whose username hints it's the AI writer (ai/writer/bot)
 *   3. the lowest existing user id (FK requires a valid user)
 */
function api_resolve_author_id(): int {
    $row = Database::fetch("SELECT value FROM settings WHERE name = 'automation_author_id'");
    if ($row && ctype_digit((string)$row['value'])) {
        $u = Database::fetch("SELECT id FROM users WHERE id = ?", [(int)$row['value']]);
        if ($u) return (int)$u['id'];
    }
    
    // Check if user Tiara exists
    $tiara = Database::fetch("SELECT id FROM users WHERE LOWER(username) = 'tiara' LIMIT 1");
    if ($tiara) return (int)$tiara['id'];

    $u = Database::fetch(
        "SELECT id FROM users
         WHERE username LIKE '%writer%' OR username LIKE '%ai%' OR username LIKE '%bot%'
         ORDER BY id ASC LIMIT 1"
    );
    if ($u) return (int)$u['id'];
    $u = Database::fetch("SELECT id FROM users ORDER BY id ASC LIMIT 1");
    if (!$u) {
        api_fail(500, 'Tidak ada user di tabel users untuk dipakai sebagai author_id.');
    }
    return (int)$u['id'];
}

/**
 * Map an array of category/tag references to EXISTING row ids only.
 * Accepts numeric ids or name/slug strings. Never creates new terms
 * (out-of-scope per PRD). $table is a fixed literal ('categories'|'tags').
 */
function api_resolve_term_ids(array $items, string $table): array {
    if (!in_array($table, ['categories', 'tags'], true)) {
        return [];
    }
    $ids = [];
    foreach ($items as $it) {
        if (is_int($it) || (is_string($it) && ctype_digit($it))) {
            $row = Database::fetch("SELECT id FROM {$table} WHERE id = ?", [(int)$it]);
        } else {
            $needle = trim((string)$it);
            if ($needle === '') continue;
            $row = Database::fetch(
                "SELECT id FROM {$table} WHERE LOWER(name) = LOWER(?) OR LOWER(slug) = LOWER(?)",
                [$needle, $needle]
            );
        }
        if ($row) $ids[] = (int)$row['id'];
    }
    return array_values(array_unique($ids));
}

/**
 * Look for an existing post that would make a new one a duplicate.
 * Matches by source_url first (strong signal), then by normalised title.
 * Rejected posts are ignored so a rejected topic can be retried.
 * Returns the matching row (with a 'matched_by' key) or null.
 */
function api_find_duplicate(string $source_url = '', string $title = ''): ?array {
    $source_url = api_norm_source($source_url);
    if ($source_url !== '') {
        // Match ANY status (incl. 'rejected') so a source article is used at most
        // once — a rejected topic is NOT regenerated; the reject→regenerate loop
        // moves on to a different topic instead.
        $row = Database::fetch(
            "SELECT id, slug, status, title FROM posts
             WHERE source_url = ? LIMIT 1",
            [$source_url]
        );
        if ($row) { $row['matched_by'] = 'source_url'; return $row; }
    }
    $title = trim($title);
    if ($title !== '') {
        // Normalise to lowercase alphanumerics for a tolerant compare. Done in
        // PHP (not SQL) to stay portable — REGEXP_REPLACE needs MySQL 8+. The
        // posts table is small, so scanning titles here is cheap.
        $norm = preg_replace('/[^a-z0-9]+/', '', strtolower($title));
        if ($norm !== '') {
            $rows = Database::fetchAll(
                "SELECT id, slug, status, title FROM posts WHERE status <> 'rejected'"
            );
            foreach ($rows as $r) {
                if (preg_replace('/[^a-z0-9]+/', '', strtolower($r['title'])) === $norm) {
                    $r['matched_by'] = 'title';
                    return $r;
                }
            }
        }
    }
    return null;
}

/**
 * Download a remote image (e.g. from Fal.ai) into assets/images/blog/ and
 * return ['path' => relative-path|null, 'warning' => string|null].
 * Never throws / never aborts ingest — a failed image just yields a warning
 * so the draft can still be created without a featured image.
 * Hardened: http(s) only, blocks private/reserved IPs (SSRF), 10 MB cap,
 * verifies real image bytes before saving.
 */
function api_download_image(string $url): array {
    $url = trim($url);
    if ($url === '') return ['path' => null, 'warning' => null];

    $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
    if (!in_array($scheme, ['http', 'https'], true)) {
        return ['path' => null, 'warning' => 'image_url dilewati: skema harus http/https.'];
    }
    $host = parse_url($url, PHP_URL_HOST);
    if (!$host) {
        return ['path' => null, 'warning' => 'image_url dilewati: host tidak valid.'];
    }
    $ip = gethostbyname($host);
    if (filter_var($ip, FILTER_VALIDATE_IP)
        && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return ['path' => null, 'warning' => 'image_url dilewati: mengarah ke IP privat/terlarang.'];
    }

    $maxBytes = 10 * 1024 * 1024;
    $data = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $data = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($data === false) {
            return ['path' => null, 'warning' => 'Gagal mengunduh gambar: ' . $err];
        }
    } else {
        $ctx = stream_context_create(['http' => ['timeout' => 20], 'https' => ['timeout' => 20]]);
        $data = @file_get_contents($url, false, $ctx);
        if ($data === false) {
            return ['path' => null, 'warning' => 'Gagal mengunduh gambar.'];
        }
    }

    if (strlen($data) > $maxBytes) {
        return ['path' => null, 'warning' => 'Gambar terlalu besar (>10MB).'];
    }
    $info = @getimagesizefromstring($data);
    if ($info === false) {
        return ['path' => null, 'warning' => 'File yang diunduh bukan gambar yang valid.'];
    }
    $mimeMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = $info['mime'] ?? '';
    if (!isset($mimeMap[$mime])) {
        return ['path' => null, 'warning' => 'Format gambar tidak didukung: ' . $mime];
    }

    $dir = DIR_ROOT . 'assets/images/blog/';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        return ['path' => null, 'warning' => 'Folder assets/images/blog/ tidak bisa dibuat.'];
    }
    $name = md5(uniqid('', true) . $host) . '.' . $mimeMap[$mime];
    if (file_put_contents($dir . $name, $data) === false) {
        return ['path' => null, 'warning' => 'Gagal menyimpan gambar ke disk.'];
    }
    return ['path' => 'assets/images/blog/' . $name, 'warning' => null];
}
