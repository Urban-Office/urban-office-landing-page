<?php
/**
 * Pure-PHP Google Search Console (GSC) API Client
 * Zero Composer dependencies — uses native PHP 8.3 cURL & OpenSSL.
 * Supports:
 *   1. OAuth 2.0 Refresh Token (GSC_OAUTH_CLIENT_ID / SECRET / REFRESH_TOKEN)
 *   2. Service Account JSON (GSC_SERVICE_ACCOUNT_JSON — RS256 JWT)
 */

if (PHP_SAPI !== 'cli' && basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'gsc_client.php') {
    header('HTTP/1.1 403 Forbidden');
    exit('Direct access forbidden.');
}

/**
 * Returns active GSC authentication mode: 'oauth', 'service_account', or 'none'.
 */
function gsc_auth_mode(): string {
    $cid = defined('GSC_OAUTH_CLIENT_ID') ? trim((string)GSC_OAUTH_CLIENT_ID) : '';
    $sec = defined('GSC_OAUTH_CLIENT_SECRET') ? trim((string)GSC_OAUTH_CLIENT_SECRET) : '';
    $ref = defined('GSC_OAUTH_REFRESH_TOKEN') ? trim((string)GSC_OAUTH_REFRESH_TOKEN) : '';
    if ($cid !== '' && $sec !== '' && $ref !== '') {
        return 'oauth';
    }
    $sa_path = defined('GSC_SERVICE_ACCOUNT_JSON') ? trim((string)GSC_SERVICE_ACCOUNT_JSON) : '';
    if ($sa_path !== '' && is_file($sa_path) && is_readable($sa_path)) {
        return 'service_account';
    }
    return 'none';
}

function gsc_is_configured(): bool {
    return gsc_auth_mode() !== 'none';
}

function gsc_site_url(): string {
    $site = defined('GSC_SITE_URL') ? trim((string)GSC_SITE_URL) : '';
    return $site !== '' ? $site : 'sc-domain:urbanoffice.co.id';
}

function gsc_base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Low-level HTTPS POST helper via native cURL.
 * Returns decoded JSON array or throws RuntimeException on error.
 */
function gsc_http_post(string $url, $body, array $headers = [], int $timeout = 25): array {
    if (!function_exists('curl_init')) {
        throw new RuntimeException('Ekstensi PHP cURL tidak aktif di server.');
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $raw = curl_exec($ch);
    $errno = curl_errno($ch);
    $err   = curl_error($ch);
    $code  = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno !== 0) {
        throw new RuntimeException("Koneksi GSC API gagal (cURL #{$errno}): {$err}");
    }

    $decoded = json_decode((string)$raw, true);
    if ($code < 200 || $code >= 300) {
        $api_msg = '';
        if (is_array($decoded)) {
            $api_msg = $decoded['error_description']
                ?? ($decoded['error']['message'] ?? (is_string($decoded['error'] ?? null) ? $decoded['error'] : ''));
        }
        $detail = $api_msg !== '' ? $api_msg : substr(trim((string)$raw), 0, 200);
        throw new RuntimeException("GSC API HTTP {$code}: {$detail}");
    }

    if (!is_array($decoded)) {
        throw new RuntimeException('Respons GSC API bukan JSON valid.');
    }

    return $decoded;
}

/**
 * Exchanges configured credentials for a short-lived Google OAuth2 access token.
 * Cached in memory for the duration of the PHP request/CLI run.
 */
function gsc_get_access_token(): string {
    static $cached_token = null;
    static $cached_exp   = 0;

    if ($cached_token !== null && time() < ($cached_exp - 60)) {
        return $cached_token;
    }

    $mode = gsc_auth_mode();
    if ($mode === 'none') {
        throw new RuntimeException('Credential GSC API belum dikonfigurasi di inc/config.php.');
    }

    if ($mode === 'oauth') {
        $payload = http_build_query([
            'client_id'     => trim((string)GSC_OAUTH_CLIENT_ID),
            'client_secret' => trim((string)GSC_OAUTH_CLIENT_SECRET),
            'refresh_token' => trim((string)GSC_OAUTH_REFRESH_TOKEN),
            'grant_type'    => 'refresh_token',
        ]);
        $res = gsc_http_post(
            'https://oauth2.googleapis.com/token',
            $payload,
            ['Content-Type: application/x-www-form-urlencoded']
        );
    } else {
        $json_raw = @file_get_contents(trim((string)GSC_SERVICE_ACCOUNT_JSON));
        $sa = json_decode((string)$json_raw, true);
        if (!is_array($sa) || empty($sa['client_email']) || empty($sa['private_key'])) {
            throw new RuntimeException('File Service Account JSON GSC tidak valid atau tidak terbaca.');
        }

        $now = time();
        $header = gsc_base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claims = gsc_base64url_encode(json_encode([
            'iss'   => $sa['client_email'],
            'scope' => 'https://www.googleapis.com/auth/webmasters.readonly',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));
        $signing_input = $header . '.' . $claims;
        $signature = '';
        if (!openssl_sign($signing_input, $signature, $sa['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Gagal menandatangani JWT Service Account (OpenSSL).');
        }
        $jwt = $signing_input . '.' . gsc_base64url_encode($signature);

        $payload = http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);
        $res = gsc_http_post(
            'https://oauth2.googleapis.com/token',
            $payload,
            ['Content-Type: application/x-www-form-urlencoded']
        );
    }

    if (empty($res['access_token'])) {
        throw new RuntimeException('Google OAuth tidak mengembalikan access_token.');
    }

    $cached_token = (string)$res['access_token'];
    $cached_exp   = time() + (int)($res['expires_in'] ?? 3500);
    return $cached_token;
}

/**
 * Executes a Google Search Console Search Analytics query.
 * Returns array of rows (each with keys, clicks, impressions, ctr, position).
 */
function gsc_search_analytics(
    string $start_date,
    string $end_date,
    array $dimensions = ['page', 'date'],
    int $row_limit = 25000,
    array $dim_filter_groups = [],
    string $data_state = 'all'
): array {
    $token = gsc_get_access_token();
    $site  = rawurlencode(gsc_site_url());
    $url   = "https://www.googleapis.com/webmasters/v3/sites/{$site}/searchAnalytics/query";

    $body = [
        'startDate'  => $start_date,
        'endDate'    => $end_date,
        'dimensions' => $dimensions,
        'type'       => 'web',
        'rowLimit'   => max(1, min(25000, $row_limit)),
        'dataState'  => $data_state,
    ];
    if (!empty($dim_filter_groups)) {
        $body['dimensionFilterGroups'] = $dim_filter_groups;
    }

    $res = gsc_http_post(
        $url,
        json_encode($body),
        [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ],
        30
    );

    return isset($res['rows']) && is_array($res['rows']) ? $res['rows'] : [];
}

/**
 * Normalizes a full GSC URL (e.g. https://urbanoffice.co.id/virtual-office-surabaya/?utm=1)
 * into a lowercase page slug matching pages.slug ('' for homepage).
 */
function gsc_normalize_url_to_slug(string $url): string {
    $path = parse_url(trim($url), PHP_URL_PATH);
    if (!is_string($path) || $path === '' || $path === '/') {
        return '';
    }
    $slug = strtolower(trim(urldecode($path), '/'));
    if ($slug === '' || $slug === 'index.php' || $slug === 'index.html') {
        return '';
    }
    return $slug;
}

/**
 * Reads aggregated GSC metrics for a single page across [start_date, end_date] from page_gsc_daily.
 * Returns null if no rows exist in that period.
 */
function gsc_get_page_period_metrics(int $page_id, string $start_date, string $end_date): ?array {
    try {
        $row = Database::fetch(
            "SELECT
                COUNT(*) AS days_count,
                COALESCE(SUM(impressions), 0) AS impressions,
                COALESCE(SUM(clicks), 0) AS clicks,
                CASE
                    WHEN SUM(impressions) > 0 THEN ROUND(SUM(position * impressions) / SUM(impressions), 2)
                    WHEN COUNT(*) > 0 THEN ROUND(AVG(position), 2)
                    ELSE NULL
                END AS avg_position
             FROM page_gsc_daily
             WHERE page_id = ? AND stat_date >= ? AND stat_date <= ?",
            [$page_id, $start_date, $end_date]
        );
        if (!$row || (int)$row['days_count'] === 0) {
            return null;
        }
        $impr = (int)$row['impressions'];
        $clk  = (int)$row['clicks'];
        $ctr  = $impr > 0 ? round(($clk / $impr) * 100, 2) : 0.0;
        return [
            'days_count'      => (int)$row['days_count'],
            'gsc_impressions' => $impr,
            'gsc_clicks'      => $clk,
            'gsc_ctr'         => $ctr,
            'gsc_position'    => $row['avg_position'] !== null ? (float)$row['avg_position'] : null,
        ];
    } catch (Exception $e) {
        error_log('gsc_get_page_period_metrics failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Returns daily GSC series for Chart.js on Audit View.
 */
function gsc_get_page_daily_series(int $page_id, string $start_date, string $end_date): array {
    $series = [];
    try {
        $rows = Database::fetchAll(
            "SELECT stat_date, impressions, clicks, ctr, position
             FROM page_gsc_daily
             WHERE page_id = ? AND stat_date >= ? AND stat_date <= ?
             ORDER BY stat_date ASC",
            [$page_id, $start_date, $end_date]
        );
        foreach ($rows as $r) {
            $ts = strtotime($r['stat_date']);
            $series[] = [
                'date'        => $r['stat_date'],
                'label'       => date('d M', $ts),
                'full_label'  => date('D, d M Y', $ts),
                'impressions' => (int)$r['impressions'],
                'clicks'      => (int)$r['clicks'],
                'ctr'         => (float)$r['ctr'],
                'position'    => (float)$r['position'],
            ];
        }
    } catch (Exception $e) {
        error_log('gsc_get_page_daily_series failed: ' . $e->getMessage());
    }
    return $series;
}

/**
 * Returns cached Top Keywords (queries) for a page from page_gsc_queries.
 */
function gsc_get_page_top_queries(int $page_id, int $limit = 10): array {
    try {
        $limit = max(1, min(50, $limit));
        return Database::fetchAll(
            "SELECT query_text, impressions, clicks, ctr, position, period_days, synced_at
             FROM page_gsc_queries
             WHERE page_id = ?
             ORDER BY clicks DESC, impressions DESC
             LIMIT {$limit}",
            [$page_id]
        );
    } catch (Exception $e) {
        error_log('gsc_get_page_top_queries failed: ' . $e->getMessage());
        return [];
    }
}

/**
 * Returns last GSC sync metadata from settings table.
 */
function gsc_get_last_sync_info(): array {
    $info = ['last_sync_at' => null, 'summary' => null];
    try {
        $rows = Database::fetchAll(
            "SELECT name, value FROM settings WHERE name IN ('last_gsc_sync_at', 'last_gsc_sync_summary')"
        );
        foreach ($rows as $r) {
            if ($r['name'] === 'last_gsc_sync_at') $info['last_sync_at'] = $r['value'];
            if ($r['name'] === 'last_gsc_sync_summary') $info['summary'] = $r['value'];
        }
    } catch (Exception $e) {
        // Ignore if settings table unavailable
    }
    return $info;
}
