<?php
/**
 * First-Party Analytics & Event Collector
 * Lightweight, non-blocking endpoint (called via navigator.sendBeacon / fetch keepalive)
 * that records page views and conversion events with channel attribution into MySQL.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Ignore logged-in admin browsing in production so internal previews don't skew metrics.
// In local DEV_MODE, allow recording so developers/admins can verify tracking locally.
if ((!defined('DEV_MODE') || !DEV_MODE) && is_admin()) {
    echo json_encode(['success' => true, 'skipped' => 'admin']);
    exit;
}

// Filter out known crawlers, bots, and headless scrapers
$ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
if ($ua === '' || preg_match('/bot|crawl|spider|slurp|mediapartners|headless|lighthouse|pagespeed|gtmetrix|pingdom|uptimerobot|curl|wget|python-requests|scrapy|ahrefs|semrush|mj12bot|dotbot|bingpreview|facebookexternalhit|whatsapp|telegrambot|discordbot|twitterbot|linkedinbot|yandex|baiduspider|bytespider|gptbot|oai-searchbot|claudebot|perplexitybot|petalbot|applebot|amazonbot/i', $ua)) {
    echo json_encode(['success' => true, 'skipped' => 'bot']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data) || empty($data['event_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid payload']);
    exit;
}

$allowed_events = [
    'page_view',
    'booking_cta_click',
    'webapp_portal_click',
    'whatsapp_click',
    'lead_form_submit',
    'homepage_search',
];

$event_name = strtolower(trim((string) ($data['event_name'] ?? '')));
if (!in_array($event_name, $allowed_events, true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Unsupported event']);
    exit;
}

$allowed_channels = [
    'google_ads',
    'meta_ads',
    'paid_ads',
    'geo_ai',
    'seo_organic',
    'social_organic',
    'referral',
    'direct',
];

$channel = strtolower(trim((string) ($data['traffic_channel'] ?? 'direct')));
if (!in_array($channel, $allowed_channels, true)) {
    $channel = 'direct';
}

$clip = static function ($val, int $max): ?string {
    $s = trim((string) ($val ?? ''));
    if ($s === '') return null;
    if (function_exists('mb_substr')) {
        return mb_substr($s, 0, $max, 'UTF-8');
    }
    return substr($s, 0, $max);
};

$page_path = $clip($data['page_path'] ?? '/', 255) ?: '/';
// Never record admin panel paths
if (strpos($page_path, '/admin') === 0 || strpos($page_path, '/admin/') !== false) {
    echo json_encode(['success' => true, 'skipped' => 'admin_path']);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
}
$ip = $clip($ip, 45);

$raw_sid = trim((string) ($data['session_id'] ?? ''));
$session_hash = ($raw_sid !== '')
    ? hash('sha256', $raw_sid)
    : hash('sha256', ($ip ?? '') . '|' . $ua . '|' . date('Y-m-d'));

$device_type = 'desktop';
if (preg_match('/ipad|tablet|playbook|silk/i', $ua)) {
    $device_type = 'tablet';
} elseif (preg_match('/mobile|android|iphone|ipod|blackberry|opera mini|iemobile/i', $ua)) {
    $device_type = 'mobile';
}

$traffic_source   = $clip($data['traffic_source'] ?? 'direct', 100) ?: 'direct';
$traffic_medium   = $clip($data['traffic_medium'] ?? 'none', 64) ?: 'none';
$traffic_campaign = $clip($data['traffic_campaign'] ?? null, 150);
if ($traffic_campaign === '(none)') {
    $traffic_campaign = null;
}
$landing_page = $clip($data['landing_page'] ?? $page_path, 255);
$service_name = $clip($data['service_name'] ?? null, 100);
$branch_slug  = $clip($data['branch_slug'] ?? null, 80);
$wa_channel   = $clip($data['wa_channel'] ?? null, 20);
$event_detail = $clip($data['event_detail'] ?? null, 255);

try {
    Database::insert(
        "INSERT INTO analytics_events
            (session_hash, event_name, traffic_channel, traffic_source, traffic_medium, traffic_campaign, page_path, landing_page, service_name, branch_slug, wa_channel, event_detail, device_type, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $session_hash,
            $event_name,
            $channel,
            $traffic_source,
            $traffic_medium,
            $traffic_campaign,
            $page_path,
            $landing_page,
            $service_name,
            $branch_slug,
            $wa_channel,
            $event_detail,
            $device_type,
            $ip,
        ]
    );
    echo json_encode(['success' => true]);
    exit;
} catch (Exception $e) {
    error_log('Analytics track error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false]);
    exit;
}
