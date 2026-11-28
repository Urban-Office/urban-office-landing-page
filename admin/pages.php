<?php
/**
 * Admin Landing Pages SEO Metadata, Next Audit Schedule & Before/After Performance Comparison
 * Supports:
 * - Next Audit countdown per page (configurable cycle: 14 / 30 / 60 / 90 days)
 * - Automatic Before vs After comparison from first-party `analytics_events` (apple-to-apple window)
 * - Automatic metadata revision history (Before vs After diff on Meta Title & Description)
 * - Manual Google Search Console (GSC) snapshot logging & comparison (Impressions, Clicks, CTR, Avg Position)
 */

require_once __DIR__ . '/auth.php';
ensure_page_seo_audit_schema();

$action = isset($_GET['action']) ? trim((string)$_GET['action']) : 'list';
if (!in_array($action, ['list', 'new', 'edit', 'audit'], true)) {
    $action = 'list';
}
$error = '';
$success = '';

$seo_channels = [
    ''               => 'Semua Channel',
    'seo_geo'        => 'SEO Organic + GEO/AI',
    'seo_organic'    => 'SEO Organic',
    'geo_ai'         => 'GEO / AI Search',
    'google_ads'     => 'Google Ads',
    'meta_ads'       => 'Meta Ads',
    'paid_ads'       => 'Paid Ads Lain',
    'social_organic' => 'Social Organic',
    'referral'       => 'Referral',
    'direct'         => 'Direct',
];

function seo_build_channel_clause(string $channel_filter, array &$params): string {
    if ($channel_filter === 'seo_geo') {
        return " AND traffic_channel IN ('seo_organic', 'geo_ai')";
    }
    if ($channel_filter !== '' && in_array($channel_filter, ['seo_organic', 'geo_ai', 'google_ads', 'meta_ads', 'paid_ads', 'social_organic', 'referral', 'direct'], true)) {
        $params[] = $channel_filter;
        return " AND traffic_channel = ?";
    }
    return '';
}

/**
 * Helper: Pull first-party analytics metrics for a specific page slug within [start_dt, end_dt].
 */
function seo_fetch_page_fp_metrics(string $slug, string $start_dt, string $end_dt, string $channel_filter = ''): array {
    $empty = [
        'total_views'      => 0,
        'organic_views'    => 0,
        'geo_views'        => 0,
        'google_ads_views' => 0,
        'meta_ads_views'   => 0,
        'paid_ads_views'   => 0,
        'social_views'     => 0,
        'referral_views'   => 0,
        'direct_views'     => 0,
        'sessions'         => 0,
        'conversions'      => 0,
    ];
    try {
        $paths = seo_page_path_variants($slug);
        $ph = implode(',', array_fill(0, count($paths), '?'));
        $params = array_merge($paths, [$start_dt, $end_dt]);
        $ch_sql = seo_build_channel_clause($channel_filter, $params);
        $row = Database::fetch(
            "SELECT
                SUM(event_name = 'page_view') AS total_views,
                SUM(event_name = 'page_view' AND traffic_channel = 'seo_organic') AS organic_views,
                SUM(event_name = 'page_view' AND traffic_channel = 'geo_ai') AS geo_views,
                SUM(event_name = 'page_view' AND traffic_channel = 'google_ads') AS google_ads_views,
                SUM(event_name = 'page_view' AND traffic_channel = 'meta_ads') AS meta_ads_views,
                SUM(event_name = 'page_view' AND traffic_channel = 'paid_ads') AS paid_ads_views,
                SUM(event_name = 'page_view' AND traffic_channel = 'social_organic') AS social_views,
                SUM(event_name = 'page_view' AND traffic_channel = 'referral') AS referral_views,
                SUM(event_name = 'page_view' AND traffic_channel = 'direct') AS direct_views,
                COUNT(DISTINCT session_hash) AS sessions,
                SUM(event_name IN ('booking_cta_click', 'lead_form_submit') OR (event_name = 'whatsapp_click' AND (wa_channel = 'sales' OR wa_channel IS NULL OR wa_channel = ''))) AS conversions
             FROM analytics_events
             WHERE page_path IN ({$ph}) AND created_at >= ? AND created_at <= ?{$ch_sql}",
            $params
        );
        if ($row) {
            foreach ($empty as $k => $_v) {
                $empty[$k] = (int)($row[$k] ?? 0);
            }
        }
    } catch (Exception $e) {
        error_log("seo_fetch_page_fp_metrics failed: " . $e->getMessage());
    }
    return $empty;
}

/**
 * Helper: Fetch continuous daily time-series from analytics_events for GA4-style charts.
 * Returns ['series' => [...], 'totals' => [...]] with zero-filled days so dual-line overlays align cleanly.
 */
function seo_fetch_daily_series(array $page_paths, string $start_date, string $end_date, string $channel_filter = ''): array {
    $totals = [
        'total_views'   => 0,
        'seo_geo_views' => 0,
        'organic_views' => 0,
        'geo_views'     => 0,
        'sessions'      => 0,
        'conversions'   => 0,
    ];
    $series = [];

    $start_ts = strtotime($start_date);
    $end_ts   = strtotime($end_date);
    if ($start_ts === false || $end_ts === false) {
        return ['series' => [], 'totals' => $totals];
    }
    if ($start_ts > $end_ts) {
        [$start_ts, $end_ts] = [$end_ts, $start_ts];
        $start_date = date('Y-m-d', $start_ts);
        $end_date   = date('Y-m-d', $end_ts);
    }

    $daily_map = [];
    try {
        $where = ['created_at >= ?', 'created_at <= ?'];
        $params = [$start_date . ' 00:00:00', $end_date . ' 23:59:59'];
        if (!empty($page_paths)) {
            $ph = implode(',', array_fill(0, count($page_paths), '?'));
            $where[] = "page_path IN ({$ph})";
            $params = array_merge($params, $page_paths);
        }
        $where_sql = implode(' AND ', $where) . seo_build_channel_clause($channel_filter, $params);

        $rows = Database::fetchAll(
            "SELECT
                DATE(created_at) AS day_date,
                SUM(event_name = 'page_view') AS total_views,
                SUM(event_name = 'page_view' AND traffic_channel = 'seo_organic') AS organic_views,
                SUM(event_name = 'page_view' AND traffic_channel = 'geo_ai') AS geo_views,
                COUNT(DISTINCT session_hash) AS sessions,
                SUM(event_name IN ('booking_cta_click', 'lead_form_submit') OR (event_name = 'whatsapp_click' AND (wa_channel = 'sales' OR wa_channel IS NULL OR wa_channel = ''))) AS conversions
             FROM analytics_events
             WHERE {$where_sql}
             GROUP BY DATE(created_at)
             ORDER BY day_date ASC",
            $params
        );
        foreach ($rows as $r) {
            $daily_map[$r['day_date']] = $r;
        }

        // Exact period unique sessions (distinct across the whole span, not sum of daily sessions)
        $tot_row = Database::fetch(
            "SELECT COUNT(DISTINCT session_hash) AS unique_sessions FROM analytics_events WHERE {$where_sql}",
            $params
        );
        if ($tot_row) {
            $totals['sessions'] = (int)($tot_row['unique_sessions'] ?? 0);
        }
    } catch (Exception $e) {
        error_log("seo_fetch_daily_series failed: " . $e->getMessage());
    }

    $span_days = (int)round(($end_ts - $start_ts) / 86400) + 1;
    if ($span_days > 180) {
        foreach ($daily_map as $day => $r) {
            $ts = strtotime($day);
            $org = (int)$r['organic_views'];
            $geo = (int)$r['geo_views'];
            $tv  = (int)$r['total_views'];
            $ses = (int)$r['sessions'];
            $cv  = (int)$r['conversions'];
            $series[] = [
                'date'          => $day,
                'label'         => date('d M', $ts),
                'full_label'    => date('D, d M Y', $ts),
                'total_views'   => $tv,
                'seo_geo_views' => $org + $geo,
                'organic_views' => $org,
                'geo_views'     => $geo,
                'sessions'      => $ses,
                'conversions'   => $cv,
            ];
            $totals['total_views']   += $tv;
            $totals['organic_views'] += $org;
            $totals['geo_views']     += $geo;
            $totals['seo_geo_views'] += ($org + $geo);
            $totals['conversions']   += $cv;
        }
    } else {
        for ($ts = $start_ts; $ts <= $end_ts; $ts += 86400) {
            $day = date('Y-m-d', $ts);
            $r = $daily_map[$day] ?? null;
            $org = $r ? (int)$r['organic_views'] : 0;
            $geo = $r ? (int)$r['geo_views'] : 0;
            $tv  = $r ? (int)$r['total_views'] : 0;
            $ses = $r ? (int)$r['sessions'] : 0;
            $cv  = $r ? (int)$r['conversions'] : 0;
            $series[] = [
                'date'          => $day,
                'label'         => date('d M', $ts),
                'full_label'    => date('D, d M Y', $ts),
                'total_views'   => $tv,
                'seo_geo_views' => $org + $geo,
                'organic_views' => $org,
                'geo_views'     => $geo,
                'sessions'      => $ses,
                'conversions'   => $cv,
            ];
            $totals['total_views']   += $tv;
            $totals['organic_views'] += $org;
            $totals['geo_views']     += $geo;
            $totals['seo_geo_views'] += ($org + $geo);
            $totals['conversions']   += $cv;
        }
    }

    return ['series' => $series, 'totals' => $totals];
}

/**
 * Helper: Parse optional numeric GSC input (returns null when blank).
 */
function seo_parse_opt_int($raw): ?int {
    $s = trim((string)($raw ?? ''));
    if ($s === '') return null;
    $clean = preg_replace('/[^0-9]/', '', $s);
    return $clean === '' ? null : (int)$clean;
}

function seo_parse_opt_float($raw): ?float {
    $s = str_replace(',', '.', trim((string)($raw ?? '')));
    if ($s === '' || !is_numeric($s)) return null;
    return round((float)$s, 2);
}

// ============================================================================
// POST HANDLER 1: Update SEO Metadata (auto-logs revision + optional GSC baseline)
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $id               = intval($_POST['id'] ?? 0);
        $meta_title       = trim((string)($_POST['meta_title'] ?? ''));
        $meta_description = trim((string)($_POST['meta_description'] ?? ''));
        $canonical_url    = trim((string)($_POST['canonical_url'] ?? ''));
        $og_title         = trim((string)($_POST['og_title'] ?? ''));
        $og_description   = trim((string)($_POST['og_description'] ?? ''));
        $og_image         = trim((string)($_POST['og_image'] ?? ''));
        $audit_interval   = intval($_POST['audit_interval_days'] ?? 30);
        if (!in_array($audit_interval, [14, 30, 60, 90], true)) {
            $audit_interval = 30;
        }
        $change_notes     = trim((string)($_POST['change_notes'] ?? ''));

        // Optional GSC baseline captured during meta update
        $gsc_period = intval($_POST['gsc_period_days'] ?? 28);
        if (!in_array($gsc_period, [14, 28, 30, 90], true)) {
            $gsc_period = 28;
        }
        $gsc_impr = seo_parse_opt_int($_POST['gsc_impressions'] ?? '');
        $gsc_clk  = seo_parse_opt_int($_POST['gsc_clicks'] ?? '');
        $gsc_ctr  = seo_parse_opt_float($_POST['gsc_ctr'] ?? '');
        $gsc_pos  = seo_parse_opt_float($_POST['gsc_position'] ?? '');
        if ($gsc_ctr === null && $gsc_impr !== null && $gsc_impr > 0 && $gsc_clk !== null) {
            $gsc_ctr = round(($gsc_clk / $gsc_impr) * 100, 2);
        }

        // Parse FAQ JSON array
        $faq_questions = isset($_POST['faq_q']) && is_array($_POST['faq_q']) ? $_POST['faq_q'] : [];
        $faq_answers   = isset($_POST['faq_a']) && is_array($_POST['faq_a']) ? $_POST['faq_a'] : [];
        $schema_faq_array = [];
        for ($i = 0; $i < count($faq_questions); $i++) {
            $q = trim((string)($faq_questions[$i] ?? ''));
            $a = trim((string)($faq_answers[$i] ?? ''));
            if ($q !== '' && $a !== '') {
                $schema_faq_array[] = ['question' => $q, 'answer' => $a];
            }
        }
        $schema_faq = !empty($schema_faq_array) ? json_encode($schema_faq_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;

        try {
            $old_page = Database::fetch("SELECT * FROM pages WHERE id = ?", [$id]);
            if (!$old_page) {
                $error = 'Halaman tidak ditemukan.';
            } else {
                Database::query(
                    "UPDATE pages
                     SET meta_title = ?, meta_description = ?, canonical_url = ?, og_title = ?, og_description = ?, og_image = ?, schema_faq = ?, audit_interval_days = ?
                     WHERE id = ?",
                    [$meta_title, $meta_description, $canonical_url, $og_title, $og_description, $og_image, $schema_faq, $audit_interval, $id]
                );

                // Record revision snapshot automatically if SEO fields changed or notes/GSC provided
                $meta_changed = ((string)($old_page['meta_title'] ?? '') !== $meta_title)
                    || ((string)($old_page['meta_description'] ?? '') !== $meta_description)
                    || ((string)($old_page['og_title'] ?? '') !== $og_title)
                    || ((string)($old_page['og_description'] ?? '') !== $og_description)
                    || ((string)($old_page['schema_faq'] ?? '') !== (string)($schema_faq ?? ''));
                $has_gsc = ($gsc_impr !== null || $gsc_clk !== null || $gsc_ctr !== null || $gsc_pos !== null);

                if ($meta_changed || $has_gsc || $change_notes !== '') {
                    $fp_start = date('Y-m-d 00:00:00', strtotime('-' . ($gsc_period - 1) . ' days'));
                    $fp_end   = date('Y-m-d 23:59:59');
                    $fp = seo_fetch_page_fp_metrics((string)$old_page['slug'], $fp_start, $fp_end);

                    Database::insert(
                        "INSERT INTO page_seo_audits
                            (page_id, event_type, user_id, prev_meta_title, prev_meta_description, new_meta_title, new_meta_description,
                             gsc_period_days, gsc_impressions, gsc_clicks, gsc_ctr, gsc_position,
                             fp_organic_views, fp_geo_views, fp_total_views, fp_conversions, notes)
                         VALUES (?, 'meta_update', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        [
                            $id,
                            (int)$_SESSION['admin_user_id'],
                            $old_page['meta_title'],
                            $old_page['meta_description'],
                            $meta_title,
                            $meta_description,
                            $gsc_period,
                            $gsc_impr,
                            $gsc_clk,
                            $gsc_ctr,
                            $gsc_pos,
                            $fp['organic_views'],
                            $fp['geo_views'],
                            $fp['total_views'],
                            $fp['conversions'],
                            $change_notes !== '' ? $change_notes : null,
                        ]
                    );
                }

                log_activity($_SESSION['admin_user_id'], 'update_page_seo', "Updated SEO tags for page: " . $old_page['title']);
                clear_page_cache();
                header('Location: ' . BASE_URL . 'admin/pages.php?action=audit&id=' . $id . '&success=1');
                exit;
            }
        } catch (Exception $e) {
            $error = admin_friendly_error('Gagal memperbarui SEO.', $e);
        }
    }
}

// ============================================================================
// POST HANDLER 2: Log Audit / GSC Snapshot (Option B)
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_audit'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $id = intval($_POST['page_id'] ?? 0);
        $gsc_period = intval($_POST['gsc_period_days'] ?? 28);
        if (!in_array($gsc_period, [14, 28, 30, 90], true)) {
            $gsc_period = 28;
        }
        $audit_interval = intval($_POST['audit_interval_days'] ?? 30);
        if (!in_array($audit_interval, [14, 30, 60, 90], true)) {
            $audit_interval = 30;
        }
        $gsc_impr = seo_parse_opt_int($_POST['gsc_impressions'] ?? '');
        $gsc_clk  = seo_parse_opt_int($_POST['gsc_clicks'] ?? '');
        $gsc_ctr  = seo_parse_opt_float($_POST['gsc_ctr'] ?? '');
        $gsc_pos  = seo_parse_opt_float($_POST['gsc_position'] ?? '');
        if ($gsc_ctr === null && $gsc_impr !== null && $gsc_impr > 0 && $gsc_clk !== null) {
            $gsc_ctr = round(($gsc_clk / $gsc_impr) * 100, 2);
        }
        $notes = trim((string)($_POST['notes'] ?? ''));

        try {
            $page = Database::fetch("SELECT * FROM pages WHERE id = ?", [$id]);
            if (!$page) {
                $error = 'Halaman tidak ditemukan.';
            } else {
                $fp_start = date('Y-m-d 00:00:00', strtotime('-' . ($gsc_period - 1) . ' days'));
                $fp_end   = date('Y-m-d 23:59:59');
                $fp = seo_fetch_page_fp_metrics((string)$page['slug'], $fp_start, $fp_end);

                Database::insert(
                    "INSERT INTO page_seo_audits
                        (page_id, event_type, user_id, new_meta_title, new_meta_description,
                         gsc_period_days, gsc_impressions, gsc_clicks, gsc_ctr, gsc_position,
                         fp_organic_views, fp_geo_views, fp_total_views, fp_conversions, notes)
                     VALUES (?, 'audit_check', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $id,
                        (int)$_SESSION['admin_user_id'],
                        $page['meta_title'],
                        $page['meta_description'],
                        $gsc_period,
                        $gsc_impr,
                        $gsc_clk,
                        $gsc_ctr,
                        $gsc_pos,
                        $fp['organic_views'],
                        $fp['geo_views'],
                        $fp['total_views'],
                        $fp['conversions'],
                        $notes !== '' ? $notes : null,
                    ]
                );

                // Reset Next Audit countdown using last_audited_at without disturbing updated_at
                Database::query(
                    "UPDATE pages SET last_audited_at = NOW(), audit_interval_days = ?, updated_at = updated_at WHERE id = ?",
                    [$audit_interval, $id]
                );

                log_activity($_SESSION['admin_user_id'], 'seo_audit_log', "Logged SEO/GSC audit snapshot for page: " . $page['title']);
                header('Location: ' . BASE_URL . 'admin/pages.php?action=audit&id=' . $id . '&success=2');
                exit;
            }
        } catch (Exception $e) {
            $error = admin_friendly_error('Gagal menyimpan snapshot audit.', $e);
        }
    }
}

// ============================================================================
// POST HANDLER 3: Delete Audit Snapshot Entry
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_audit'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $audit_id = intval($_POST['audit_id'] ?? 0);
        $page_id  = intval($_POST['page_id'] ?? 0);
        try {
            Database::query("DELETE FROM page_seo_audits WHERE id = ? AND page_id = ?", [$audit_id, $page_id]);
            log_activity($_SESSION['admin_user_id'], 'seo_audit_delete', "Deleted SEO audit snapshot #{$audit_id} on page #{$page_id}");
            header('Location: ' . BASE_URL . 'admin/pages.php?action=audit&id=' . $page_id . '&success=3');
            exit;
        } catch (Exception $e) {
            $error = admin_friendly_error('Gagal menghapus catatan audit.', $e);
        }
    }
}

// ============================================================================
// POST HANDLER 4: Create New Landing Page
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $title            = trim((string)($_POST['title'] ?? ''));
        $slug             = admin_slugify(trim((string)($_POST['slug'] ?? ''), "/ \t\n\r\0\x0B"));
        $meta_title       = trim((string)($_POST['meta_title'] ?? ''));
        $meta_description = trim((string)($_POST['meta_description'] ?? ''));
        $canonical_url    = trim((string)($_POST['canonical_url'] ?? ''));
        $audit_interval   = intval($_POST['audit_interval_days'] ?? 30);
        if (!in_array($audit_interval, [14, 30, 60, 90], true)) {
            $audit_interval = 30;
        }

        $is_virtual_route = (bool)preg_match('#^(virtual-office|sewa-kantor|meeting-room|coworking-space|event-space)-[a-z0-9\-]+$#', $slug);

        if ($title === '' || $slug === '' || $meta_title === '' || $meta_description === '') {
            $error = 'Nama halaman, slug, meta title, dan meta description wajib diisi.';
        } elseif (Database::fetch("SELECT id FROM pages WHERE slug = ?", [$slug])) {
            $error = 'Halaman dengan slug "' . $slug . '" sudah terdaftar.';
        } elseif (!is_dir(DIR_ROOT . $slug) && !$is_virtual_route) {
            $error = 'Folder atau rute dinamis "' . $slug . '" tidak ditemukan di server. Pastikan folder atau aturan rewrite sudah tersedia.';
        } else {
            try {
                Database::insert(
                    "INSERT INTO pages (title, slug, meta_title, meta_description, canonical_url, audit_interval_days) VALUES (?, ?, ?, ?, ?, ?)",
                    [$title, $slug, $meta_title, $meta_description, ($canonical_url !== '' ? $canonical_url : null), $audit_interval]
                );
                log_activity($_SESSION['admin_user_id'], 'create_page_seo', "Created SEO landing page: $title (/$slug/)");
                clear_page_cache();
                header('Location: ' . BASE_URL . 'admin/pages.php?created=1');
                exit;
            } catch (Exception $e) {
                $error = admin_friendly_error('Gagal menambahkan halaman.', $e);
            }
        }
    }
}

// Flash feedback
if (isset($_GET['success'])) {
    if ($_GET['success'] == 1) $success = 'Metadata SEO berhasil diperbarui & histori perubahan otomatis dicatat!';
    if ($_GET['success'] == 2) $success = 'Snapshot Audit & GSC berhasil disimpan! Jadwal Next Audit diperbarui.';
    if ($_GET['success'] == 3) $success = 'Catatan riwayat audit berhasil dihapus.';
}
if (isset($_GET['created']) && $_GET['created'] == 1) {
    $success = 'Halaman baru berhasil ditambahkan! Silakan lengkapi Open Graph & FAQ Schema-nya.';
}

// ============================================================================
// LOAD VIEW DATA
// ============================================================================
$page_data = null;
$faq_list = [];
$audit_history = [];
$gsc_before = null;
$gsc_after = null;
$gsc_chart_series = [];
$fp_compare = null;
$audit_chart_payload = null;
$page_schedule = null;

$has_explicit_win = isset($_GET['win']) && trim((string)$_GET['win']) !== '';
$compare_win_raw = $has_explicit_win ? trim((string)$_GET['win']) : '28';
$audit_channel   = isset($_GET['channel']) ? trim((string)$_GET['channel']) : '';
if (!isset($seo_channels[$audit_channel])) {
    $audit_channel = '';
}
$audit_custom_start = isset($_GET['a_start']) ? trim((string)$_GET['a_start']) : '';
$audit_custom_end   = isset($_GET['a_end']) ? trim((string)$_GET['a_end']) : '';
$is_audit_custom = ($compare_win_raw === 'custom' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $audit_custom_start) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $audit_custom_end));
if ($is_audit_custom && strtotime($audit_custom_start) > strtotime($audit_custom_end)) {
    [$audit_custom_start, $audit_custom_end] = [$audit_custom_end, $audit_custom_start];
}
$is_audit_all = ($compare_win_raw === 'all');
$compare_window = in_array($compare_win_raw, ['14', '28', '30'], true) ? (int)$compare_win_raw : 28;

if (($action === 'edit' || $action === 'audit') && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $page_data = Database::fetch("SELECT * FROM pages WHERE id = ?", [$id]);
    if (!$page_data) {
        header('Location: ' . BASE_URL . 'admin/pages.php');
        exit;
    }
    $decoded_faq = !empty($page_data['schema_faq']) ? json_decode($page_data['schema_faq'], true) : [];
    $faq_list = is_array($decoded_faq) ? $decoded_faq : [];
    $page_schedule = seo_compute_audit_schedule(
        $page_data['updated_at'] ?? null,
        $page_data['last_audited_at'] ?? null,
        (int)($page_data['audit_interval_days'] ?? 30)
    );

    if ($action === 'audit') {
        try {
            $audit_history = Database::fetchAll(
                "SELECT a.*, u.username
                 FROM page_seo_audits a
                 LEFT JOIN users u ON a.user_id = u.id
                 WHERE a.page_id = ?
                 ORDER BY a.created_at DESC, a.id DESC",
                [$id]
            );
        } catch (Exception $e) {
            error_log("Failed loading audit history: " . $e->getMessage());
            $audit_history = [];
        }

        // Extract GSC snapshots (newest first for delta cards, chronological for trend chart)
        $gsc_snaps = array_values(array_filter($audit_history, static function ($r) {
            return $r['gsc_impressions'] !== null || $r['gsc_clicks'] !== null || $r['gsc_ctr'] !== null || $r['gsc_position'] !== null;
        }));
        if (count($gsc_snaps) >= 2) {
            $gsc_after  = $gsc_snaps[0];
            $gsc_before = $gsc_snaps[1];
        } elseif (count($gsc_snaps) === 1) {
            $gsc_before = $gsc_snaps[0];
        }
        foreach (array_reverse($gsc_snaps) as $gs) {
            $gsc_chart_series[] = [
                'label'       => date('d M y', strtotime($gs['created_at'])),
                'full_label'  => date('d M Y H:i', strtotime($gs['created_at'])),
                'impressions' => $gs['gsc_impressions'] !== null ? (int)$gs['gsc_impressions'] : null,
                'clicks'      => $gs['gsc_clicks'] !== null ? (int)$gs['gsc_clicks'] : null,
                'ctr'         => $gs['gsc_ctr'] !== null ? (float)$gs['gsc_ctr'] : null,
                'position'    => $gs['gsc_position'] !== null ? (float)$gs['gsc_position'] : null,
            ];
        }

        // Determine pivot date (latest meta_update event, or pages.updated_at)
        $pivot_dt = $page_data['updated_at'] ?? date('Y-m-d H:i:s');
        foreach ($audit_history as $ah) {
            if ($ah['event_type'] === 'meta_update') {
                $pivot_dt = $ah['created_at'];
                break;
            }
        }
        $pivot_ts = strtotime($pivot_dt) ?: time();
        $pivot_day_str = date('Y-m-d', $pivot_ts);
        $today_day_str = date('Y-m-d');
        $page_paths = seo_page_path_variants((string)$page_data['slug']);
        $auto_all_fallback = false;

        if ($is_audit_custom) {
            $span = max(1, (int)round((strtotime($audit_custom_end) - strtotime($audit_custom_start)) / 86400) + 1);
            $after_start_day  = $audit_custom_start;
            $after_end_day    = $audit_custom_end;
            $before_end_day   = date('Y-m-d', strtotime($audit_custom_start) - 86400);
            $before_start_day = date('Y-m-d', strtotime($audit_custom_start) - ($span * 86400));
            $effective_days   = $span;
            $is_partial       = false;
        } elseif ($is_audit_all) {
            $earliest_day = null;
            try {
                $ph_min = implode(',', array_fill(0, count($page_paths), '?'));
                $min_row = Database::fetch("SELECT MIN(DATE(created_at)) AS first_day FROM analytics_events WHERE page_path IN ({$ph_min})", $page_paths);
                if (!empty($min_row['first_day'])) {
                    $earliest_day = $min_row['first_day'];
                } else {
                    $glob_min = Database::fetch("SELECT MIN(DATE(created_at)) AS first_day FROM analytics_events");
                    if (!empty($glob_min['first_day'])) {
                        $earliest_day = $glob_min['first_day'];
                    }
                }
            } catch (Exception $e) {
                $earliest_day = null;
            }
            $after_start_day  = $pivot_day_str;
            $after_end_day    = $today_day_str;
            $before_end_day   = date('Y-m-d', strtotime($pivot_day_str . ' 00:00:00') - 86400);
            $fallback_start   = date('Y-m-d', strtotime($before_end_day) - (27 * 86400));
            $before_start_day = ($earliest_day && strtotime($earliest_day) <= strtotime($before_end_day)) ? $earliest_day : $fallback_start;
            $effective_days   = max(1, (int)round((strtotime($after_end_day) - strtotime($before_start_day)) / 86400) + 1);
            $is_partial       = false;
        } else {
            // Standard GA4/GSC rolling period comparison: last N days vs previous N days (apple-to-apple)
            $after_end_day    = $today_day_str;
            $after_start_day  = date('Y-m-d', strtotime($today_day_str) - (($compare_window - 1) * 86400));
            $before_end_day   = date('Y-m-d', strtotime($after_start_day) - 86400);
            $before_start_day = date('Y-m-d', strtotime($after_start_day) - ($compare_window * 86400));
            $effective_days   = $compare_window;
            $is_partial       = false;
        }

        $after_start  = $after_start_day . ' 00:00:00';
        $after_end    = $after_end_day . ' 23:59:59';
        $before_start = $before_start_day . ' 00:00:00';
        $before_end   = $before_end_day . ' 23:59:59';

        $fp_before = seo_fetch_page_fp_metrics((string)$page_data['slug'], $before_start, $before_end, $audit_channel);
        $fp_after  = seo_fetch_page_fp_metrics((string)$page_data['slug'], $after_start, $after_end, $audit_channel);

        $series_before = seo_fetch_daily_series($page_paths, $before_start_day, $before_end_day, $audit_channel);
        $series_after  = seo_fetch_daily_series($page_paths, $after_start_day, $after_end_day, $audit_channel);

        $fp_compare = [
            'pivot_dt'          => $pivot_dt,
            'pivot_label'       => date('d M', $pivot_ts),
            'pivot_date'        => $pivot_day_str,
            'is_custom'         => $is_audit_custom,
            'is_all'            => $is_audit_all,
            'auto_all_fallback' => $auto_all_fallback,
            'requested_days'    => $compare_window,
            'effective_days'    => $effective_days,
            'is_partial'        => $is_partial,
            'mature_date'       => date('d M Y', strtotime($pivot_day_str) + ($compare_window * 86400)),
            'before_range'      => date('d M Y', strtotime($before_start_day)) . ' – ' . date('d M Y', strtotime($before_end_day)),
            'after_range'       => date('d M Y', strtotime($after_start_day)) . ' – ' . date('d M Y', strtotime($after_end_day)),
            'before'            => $fp_before,
            'after'             => $fp_after,
        ];

        $audit_chart_payload = [
            'before'      => $series_before['series'],
            'after'       => $series_after['series'],
            'pivotDate'   => $pivot_day_str,
            'pivotLabel'  => 'Update SEO (' . date('d M', $pivot_ts) . ')',
            'beforeRange' => $fp_compare['before_range'],
            'afterRange'  => $fp_compare['after_range'],
            'defaultMode' => $is_audit_all ? 'timeline' : 'overlay',
            'gsc'         => $gsc_chart_series,
        ];
    }
}

$build_audit_url = static function (array $overrides = []) use ($page_data, $compare_win_raw, $audit_channel, $audit_custom_start, $audit_custom_end): string {
    $q = array_merge([
        'action'  => 'audit',
        'id'      => $page_data ? (int)$page_data['id'] : 0,
        'win'     => $compare_win_raw,
        'channel' => $audit_channel,
        'a_start' => $audit_custom_start,
        'a_end'   => $audit_custom_end,
    ], $overrides);
    $q = array_filter($q, static fn($v) => $v !== '' && $v !== null);
    return BASE_URL . 'admin/pages.php?' . http_build_query($q);
};

// List View Data + Global GA4-Style Chart + Batch Analytics + Latest Audit Summary
$filter_status = isset($_GET['filter']) ? trim((string)$_GET['filter']) : '';
if (!in_array($filter_status, ['', 'due', 'scheduled', 'no_gsc'], true)) {
    $filter_status = '';
}
$search_q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$sort_by  = isset($_GET['sort']) ? trim((string)$_GET['sort']) : 'next_audit';
if (!in_array($sort_by, ['next_audit', 'updated_desc', 'views_desc', 'title_asc'], true)) {
    $sort_by = 'next_audit';
}
$per_page_raw = isset($_GET['per_page']) ? trim((string)$_GET['per_page']) : '10';
$per_page = in_array($per_page_raw, ['10', '20', '50', 'all'], true) ? ($per_page_raw === 'all' ? 'all' : (int)$per_page_raw) : 10;
$curr_page = max(1, intval($_GET['page'] ?? 1));

// Global GA4 Chart filter parameters
$g_range   = isset($_GET['g_range']) ? trim((string)$_GET['g_range']) : '28d';
if (!in_array($g_range, ['7d', '14d', '28d', '90d', 'all', 'custom'], true)) {
    $g_range = '28d';
}
$g_channel = isset($_GET['g_channel']) ? trim((string)$_GET['g_channel']) : '';
if (!isset($seo_channels[$g_channel])) {
    $g_channel = '';
}
$g_page_id = isset($_GET['g_page']) ? max(0, intval($_GET['g_page'])) : 0;
$g_start   = isset($_GET['g_start']) ? trim((string)$_GET['g_start']) : '';
$g_end     = isset($_GET['g_end']) ? trim((string)$_GET['g_end']) : '';
if ($g_range === 'custom' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $g_start) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $g_end)) {
    if (strtotime($g_start) > strtotime($g_end)) {
        [$g_start, $g_end] = [$g_end, $g_start];
    }
    $g_curr_start = $g_start;
    $g_curr_end   = $g_end;
    $g_span_days  = max(1, (int)round((strtotime($g_curr_end) - strtotime($g_curr_start)) / 86400) + 1);
    $g_period_lbl = date('d M Y', strtotime($g_curr_start)) . ' – ' . date('d M Y', strtotime($g_curr_end));
} elseif ($g_range === 'all') {
    $g_curr_end = date('Y-m-d');
    $g_min_day  = null;
    try {
        $g_min_row = Database::fetch("SELECT MIN(DATE(created_at)) AS first_day FROM analytics_events");
        if (!empty($g_min_row['first_day'])) {
            $g_min_day = $g_min_row['first_day'];
        }
    } catch (Exception $e) {
        $g_min_day = null;
    }
    $g_curr_start = ($g_min_day && strtotime($g_min_day) <= strtotime($g_curr_end)) ? $g_min_day : date('Y-m-d', strtotime('-27 days'));
    $g_span_days  = max(1, (int)round((strtotime($g_curr_end) - strtotime($g_curr_start)) / 86400) + 1);
    $g_period_lbl = 'Semua Data';
} else {
    if ($g_range === 'custom') $g_range = '28d';
    $g_span_days  = ($g_range === '7d') ? 7 : (($g_range === '14d') ? 14 : (($g_range === '90d') ? 90 : 28));
    $g_curr_end   = date('Y-m-d');
    $g_curr_start = date('Y-m-d', strtotime('-' . ($g_span_days - 1) . ' days'));
    $g_period_lbl = $g_span_days . ' Hari Terakhir';
}
$g_prev_end   = date('Y-m-d', strtotime($g_curr_start) - 86400);
$g_prev_start = date('Y-m-d', strtotime($g_curr_start) - ($g_span_days * 86400));

$pages_rows = [];
$raw_pages_dropdown = [];
$global_chart_payload = null;
$list_kpi = [
    'total'     => 0,
    'due'       => 0,
    'scheduled' => 0,
    'has_gsc'   => 0,
];

if ($action === 'list') {
    try {
        $raw_pages = Database::fetchAll(
            "SELECT p.id, p.title, p.slug, p.meta_title, p.meta_description, p.audit_interval_days, p.last_audited_at, p.updated_at,
                    (SELECT COUNT(*) FROM page_seo_audits a WHERE a.page_id = p.id) AS audit_count,
                    (SELECT a.gsc_impressions FROM page_seo_audits a WHERE a.page_id = p.id AND (a.gsc_impressions IS NOT NULL OR a.gsc_clicks IS NOT NULL) ORDER BY a.created_at DESC, a.id DESC LIMIT 1) AS latest_impr,
                    (SELECT a.gsc_clicks FROM page_seo_audits a WHERE a.page_id = p.id AND (a.gsc_impressions IS NOT NULL OR a.gsc_clicks IS NOT NULL) ORDER BY a.created_at DESC, a.id DESC LIMIT 1) AS latest_clicks,
                    (SELECT a.gsc_ctr FROM page_seo_audits a WHERE a.page_id = p.id AND (a.gsc_impressions IS NOT NULL OR a.gsc_clicks IS NOT NULL) ORDER BY a.created_at DESC, a.id DESC LIMIT 1) AS latest_ctr,
                    (SELECT a.gsc_position FROM page_seo_audits a WHERE a.page_id = p.id AND (a.gsc_impressions IS NOT NULL OR a.gsc_clicks IS NOT NULL) ORDER BY a.created_at DESC, a.id DESC LIMIT 1) AS latest_pos
             FROM pages p
             ORDER BY p.title ASC"
        );
        $raw_pages_dropdown = $raw_pages;

        // Resolve target paths for Global GA4 Chart (either selected page or all registered SEO landing pages)
        $chart_target_paths = [];
        $g_page_label = 'Semua SEO Landing Pages';
        foreach ($raw_pages as $rp) {
            if ($g_page_id > 0) {
                if ((int)$rp['id'] === $g_page_id) {
                    $chart_target_paths = seo_page_path_variants((string)$rp['slug']);
                    $g_page_label = $rp['title'];
                    break;
                }
            } else {
                foreach (seo_page_path_variants((string)$rp['slug']) as $pv) {
                    $chart_target_paths[$pv] = true;
                }
            }
        }
        if ($g_page_id === 0) {
            $chart_target_paths = array_keys($chart_target_paths);
        } elseif (empty($chart_target_paths)) {
            $g_page_id = 0;
        }

        $g_curr_res = seo_fetch_daily_series($chart_target_paths, $g_curr_start, $g_curr_end, $g_channel);
        $g_prev_res = seo_fetch_daily_series($chart_target_paths, $g_prev_start, $g_prev_end, $g_channel);

        $global_chart_payload = [
            'current'      => $g_curr_res['series'],
            'previous'     => $g_prev_res['series'],
            'currTotals'   => $g_curr_res['totals'],
            'prevTotals'   => $g_prev_res['totals'],
            'currRange'    => date('d M Y', strtotime($g_curr_start)) . ' – ' . date('d M Y', strtotime($g_curr_end)),
            'prevRange'    => date('d M Y', strtotime($g_prev_start)) . ' – ' . date('d M Y', strtotime($g_prev_end)),
            'periodLabel'  => $g_period_lbl,
            'pageLabel'    => $g_page_label,
            'channelLabel' => $seo_channels[$g_channel] ?? 'Semua Channel',
        ];

        // Single batch query for 28-day first-party performance across all pages
        $fp_map = [];
        try {
            $since_28d = date('Y-m-d 00:00:00', strtotime('-27 days'));
            $ae_rows = Database::fetchAll(
                "SELECT page_path,
                        SUM(event_name = 'page_view') AS total_views,
                        SUM(event_name = 'page_view' AND traffic_channel IN ('seo_organic', 'geo_ai')) AS seo_geo_views,
                        SUM(event_name IN ('booking_cta_click', 'lead_form_submit') OR (event_name = 'whatsapp_click' AND (wa_channel = 'sales' OR wa_channel IS NULL OR wa_channel = ''))) AS conversions
                 FROM analytics_events
                 WHERE created_at >= ?
                 GROUP BY page_path",
                [$since_28d]
            );
            foreach ($ae_rows as $ar) {
                $norm = '/' . trim((string)$ar['page_path'], '/');
                if ($norm === '/index.php') $norm = '/';
                if (!isset($fp_map[$norm])) {
                    $fp_map[$norm] = ['total_views' => 0, 'seo_geo_views' => 0, 'conversions' => 0];
                }
                $fp_map[$norm]['total_views']   += (int)$ar['total_views'];
                $fp_map[$norm]['seo_geo_views'] += (int)$ar['seo_geo_views'];
                $fp_map[$norm]['conversions']   += (int)$ar['conversions'];
            }
        } catch (Exception $e) {
            error_log("Batch analytics for pages list skipped: " . $e->getMessage());
        }

        foreach ($raw_pages as $rp) {
            $sched = seo_compute_audit_schedule(
                $rp['updated_at'] ?? null,
                $rp['last_audited_at'] ?? null,
                (int)($rp['audit_interval_days'] ?? 30)
            );
            $norm_slug = $rp['slug'] === '' ? '/' : ('/' . trim((string)$rp['slug'], '/'));
            $fp = $fp_map[$norm_slug] ?? ['total_views' => 0, 'seo_geo_views' => 0, 'conversions' => 0];
            $has_gsc = ($rp['latest_impr'] !== null || $rp['latest_clicks'] !== null);

            $list_kpi['total']++;
            if ($sched['status'] === 'overdue' || $sched['status'] === 'due') {
                $list_kpi['due']++;
            } else {
                $list_kpi['scheduled']++;
            }
            if ($has_gsc) {
                $list_kpi['has_gsc']++;
            }

            // Apply filter & search
            if ($filter_status === 'due' && !in_array($sched['status'], ['overdue', 'due'], true)) {
                continue;
            }
            if ($filter_status === 'scheduled' && $sched['status'] !== 'scheduled') {
                continue;
            }
            if ($filter_status === 'no_gsc' && $has_gsc) {
                continue;
            }
            if ($search_q !== '') {
                $hay = strtolower($rp['title'] . ' ' . $rp['slug'] . ' ' . ($rp['meta_title'] ?? ''));
                if (strpos($hay, strtolower($search_q)) === false) {
                    continue;
                }
            }

            $rp['_sched']   = $sched;
            $rp['_fp']      = $fp;
            $rp['_has_gsc'] = $has_gsc;
            $pages_rows[]   = $rp;
        }

        // Sort rows
        usort($pages_rows, static function ($a, $b) use ($sort_by) {
            if ($sort_by === 'next_audit') {
                $cmp = $a['_sched']['days_left'] <=> $b['_sched']['days_left'];
                return $cmp !== 0 ? $cmp : strcmp($a['title'], $b['title']);
            }
            if ($sort_by === 'updated_desc') {
                return strtotime((string)$b['updated_at']) <=> strtotime((string)$a['updated_at']);
            }
            if ($sort_by === 'views_desc') {
                $cmp = $b['_fp']['seo_geo_views'] <=> $a['_fp']['seo_geo_views'];
                return $cmp !== 0 ? $cmp : ($b['_fp']['total_views'] <=> $a['_fp']['total_views']);
            }
            return strcasecmp($a['title'], $b['title']);
        });
    } catch (Exception $e) {
        error_log("Failed retrieving pages: " . $e->getMessage());
        $pages_rows = [];
    }
}

$total_filtered = count($pages_rows);
$total_pages    = ($per_page === 'all' || $total_filtered === 0) ? 1 : (int)ceil($total_filtered / $per_page);
if ($curr_page > $total_pages) {
    $curr_page = $total_pages;
}
$offset        = ($per_page === 'all') ? 0 : (($curr_page - 1) * $per_page);
$visible_pages = ($per_page === 'all') ? $pages_rows : array_slice($pages_rows, $offset, $per_page);
$range_from    = $total_filtered > 0 ? ($offset + 1) : 0;
$range_to      = $total_filtered > 0 ? min($total_filtered, $offset + count($visible_pages)) : 0;

$build_pages_url = static function (array $overrides = []) use ($filter_status, $search_q, $sort_by, $per_page, $curr_page, $g_range, $g_channel, $g_page_id, $g_start, $g_end): string {
    $reset_page = !array_key_exists('page', $overrides)
        && (array_key_exists('filter', $overrides) || array_key_exists('q', $overrides) || array_key_exists('sort', $overrides) || array_key_exists('per_page', $overrides));
    $q = array_merge([
        'filter'    => $filter_status,
        'q'         => $search_q,
        'sort'      => $sort_by === 'next_audit' ? '' : $sort_by,
        'per_page'  => $per_page === 10 ? '' : (string)$per_page,
        'page'      => ($reset_page || $curr_page <= 1) ? '' : $curr_page,
        'g_range'   => $g_range === '28d' ? '' : $g_range,
        'g_channel' => $g_channel,
        'g_page'    => $g_page_id > 0 ? $g_page_id : '',
        'g_start'   => $g_range === 'custom' ? $g_start : '',
        'g_end'     => $g_range === 'custom' ? $g_end : '',
    ], $overrides);
    if (isset($q['page']) && (int)$q['page'] <= 1) {
        $q['page'] = '';
    }
    if (isset($q['per_page']) && (string)$q['per_page'] === '10') {
        $q['per_page'] = '';
    }
    $q = array_filter($q, static fn($v) => $v !== '' && $v !== null);
    return BASE_URL . 'admin/pages.php' . (!empty($q) ? ('?' . http_build_query($q)) : '');
};

/**
 * Helper to render percentage / point delta badge cleanly.
 */
function seo_render_delta_badge(?float $pct, bool $lower_is_better = false, string $suffix = '%'): string {
    if ($pct === null) {
        return '<span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;">—</span>';
    }
    if (abs($pct) < 0.05) {
        return '<span class="badge" style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;">0' . $suffix . '</span>';
    }
    $is_good = $lower_is_better ? ($pct < 0) : ($pct > 0);
    $cls = $is_good ? 'badge-success' : 'badge-danger';
    $sign = $pct > 0 ? '+' : '';
    return '<span class="badge ' . $cls . ' admin-num">' . $sign . $pct . $suffix . '</span>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO Landing Pages &amp; Audit - Urban Office CMS</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css?v=<?php echo filemtime(dirname(__DIR__) . '/assets/css/admin.css'); ?>">
    <style>
        .seo-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }
        .seo-kpi-card {
            position: relative;
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius);
            padding: 15px 16px;
            box-shadow: var(--admin-shadow-sm);
            overflow: hidden;
            text-decoration: none;
            display: block;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }
        .seo-kpi-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--sk-accent, #1e40af);
        }
        .seo-kpi-card:hover {
            border-color: var(--admin-border-strong);
            box-shadow: var(--admin-shadow-hover);
        }
        .seo-kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .seo-kpi-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--admin-muted);
        }
        .seo-kpi-icon {
            width: 28px; height: 28px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--sk-bg, #eff6ff);
            color: var(--sk-accent, #1e40af);
            flex-shrink: 0;
        }
        .seo-kpi-icon svg { width: 15px; height: 15px; }
        .seo-kpi-val {
            font-family: var(--admin-font-mono);
            font-variant-numeric: tabular-nums;
            font-size: 1.55rem;
            font-weight: 700;
            color: var(--admin-ink);
            margin-top: 8px;
            line-height: 1.1;
        }
        .seo-kpi-sub {
            font-size: 0.74rem;
            color: var(--admin-subtle);
            margin-top: 5px;
        }
        .seo-filter-input {
            height: 35px;
            padding: 0 10px;
            border: 1px solid var(--admin-border-strong);
            border-radius: var(--admin-radius-sm);
            font-family: inherit;
            font-size: 0.82rem;
            color: var(--admin-ink);
            background: var(--admin-surface);
        }
        .seo-filter-input:focus {
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 3px var(--admin-ring);
            outline: none;
        }
        .seo-health-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 4px;
        }
        .seo-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-family: var(--admin-font-mono);
            font-size: 0.7rem;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 5px;
            border: 1px solid transparent;
        }
        .seo-chip-ok   { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
        .seo-chip-warn { background: #fefce8; color: #a16207; border-color: #fde68a; }
        .seo-chip-bad  { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }

        /* Audit Workspace */
        .seo-audit-banner {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius);
            padding: 16px 18px;
            margin-bottom: 20px;
            box-shadow: var(--admin-shadow-sm);
        }
        .seo-banner-item-lbl {
            font-size: 0.69rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--admin-muted);
            margin-bottom: 4px;
        }
        .seo-banner-item-val {
            font-size: 0.94rem;
            font-weight: 700;
            color: var(--admin-ink);
        }
        .seo-audit-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr);
            gap: 20px;
            align-items: start;
            margin-bottom: 20px;
        }
        .seo-card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .seo-card-head h2, .seo-card-head h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--admin-ink);
        }
        .seo-card-sub {
            margin: 3px 0 0;
            font-size: 0.78rem;
            color: var(--admin-subtle);
        }
        .seo-gsc-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }
        .seo-gsc-box {
            background: var(--admin-light);
            border: 1px solid var(--admin-border);
            border-radius: 10px;
            padding: 12px 14px;
        }
        .seo-gsc-box-lbl {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--admin-muted);
            letter-spacing: 0.03em;
        }
        .seo-gsc-box-vals {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 8px;
            margin-top: 6px;
        }
        .seo-gsc-main {
            font-family: var(--admin-font-mono);
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--admin-ink);
        }
        .seo-gsc-prev {
            font-size: 0.74rem;
            color: var(--admin-subtle);
            margin-top: 4px;
            font-family: var(--admin-font-mono);
        }
        .seo-diff-box {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 8px;
        }
        .seo-diff-col {
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 0.81rem;
            line-height: 1.45;
            border: 1px solid var(--admin-border);
        }
        .seo-diff-old {
            background: #fff7ed;
            border-color: #fed7aa;
        }
        .seo-diff-new {
            background: #ecfdf5;
            border-color: #a7f3d0;
        }
        .seo-diff-tag {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 4px;
        }
        .seo-timeline {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .seo-timeline-item {
            border: 1px solid var(--admin-border);
            border-radius: 10px;
            padding: 14px 16px;
            background: var(--admin-surface);
        }
        .seo-timeline-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }
        .seo-serp-box {
            background: #ffffff;
            border: 1px solid var(--admin-border-strong);
            border-radius: 10px;
            padding: 14px 18px;
            font-family: Arial, sans-serif;
        }
        .seo-form-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .faq-row {
            display: grid;
            grid-template-columns: 1fr 1fr 40px;
            gap: 10px;
            margin-bottom: 10px;
        }
        /* GA4-Style Interactive Chart & Metric Scorecards */
        .ga-filter-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            padding-bottom: 14px;
            margin-bottom: 14px;
            border-bottom: 1px solid var(--admin-border);
        }
        .ga-filter-form {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .ga-scorecards {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }
        .ga-scorecard {
            text-align: left;
            background: var(--admin-light);
            border: 1.5px solid var(--admin-border);
            border-radius: 10px;
            padding: 11px 13px;
            cursor: pointer;
            font-family: inherit;
            transition: border-color 140ms ease, background-color 140ms ease, box-shadow 140ms ease;
        }
        .ga-scorecard:hover {
            border-color: #93c5fd;
            background: #f8fbff;
        }
        .ga-scorecard.active {
            background: #ffffff;
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 3px var(--admin-ring);
        }
        .ga-sc-lbl {
            font-size: 0.69rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--admin-muted);
        }
        .ga-scorecard.active .ga-sc-lbl {
            color: var(--admin-primary);
        }
        .ga-sc-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 6px;
            margin-top: 5px;
        }
        .ga-sc-val {
            font-family: var(--admin-font-mono);
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--admin-ink);
        }
        .ga-sc-prev {
            font-family: var(--admin-font-mono);
            font-size: 0.72rem;
            color: var(--admin-subtle);
            margin-top: 3px;
        }
        .ga-toolbar-sub {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        .ga-legend {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            font-size: 0.78rem;
            color: var(--admin-muted);
        }
        .ga-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }
        .ga-line-solid {
            width: 16px;
            height: 3px;
            border-radius: 2px;
            background: #1e40af;
            display: inline-block;
        }
        .ga-line-dashed {
            width: 16px;
            height: 0;
            border-top: 2px dashed #64748b;
            display: inline-block;
        }
        .ga-controls-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .ga-compare-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--admin-ink-2);
            cursor: pointer;
            user-select: none;
        }
        .ga-seg-group {
            display: inline-flex;
            background: var(--admin-surface-muted);
            border: 1px solid var(--admin-border);
            border-radius: 8px;
            padding: 3px;
            gap: 2px;
        }
        .ga-seg-btn {
            border: 0;
            background: transparent;
            color: var(--admin-muted);
            font-family: inherit;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 140ms ease, color 140ms ease;
        }
        .ga-seg-btn:hover { color: var(--admin-ink); }
        .ga-seg-btn.active {
            background: var(--admin-surface);
            color: var(--admin-primary);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }
        .ga-canvas-wrap {
            position: relative;
            height: 270px;
            width: 100%;
        }
        .seo-slug-pill {
            max-width: 240px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: middle;
        }
        .seo-table-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid var(--admin-border);
        }
        .seo-page-info {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            font-size: 0.8rem;
            color: var(--admin-muted);
        }
        .seo-perpage-group {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: var(--admin-surface-muted);
            border: 1px solid var(--admin-border);
            border-radius: 7px;
            padding: 2px;
        }
        .seo-perpage-btn {
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 0.74rem;
            font-weight: 600;
            text-decoration: none;
            color: var(--admin-muted);
            transition: background-color 140ms ease, color 140ms ease;
        }
        .seo-perpage-btn:hover { color: var(--admin-ink); }
        .seo-perpage-btn.active {
            background: var(--admin-surface);
            color: var(--admin-primary);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }
        .sr-only {
            position: absolute;
            width: 1px; height: 1px;
            padding: 0; margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        @media (max-width: 1120px) {
            .seo-kpi-grid, .ga-scorecards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .seo-audit-banner { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .seo-audit-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 700px) {
            .seo-diff-box, .seo-form-grid-2, .seo-gsc-grid { grid-template-columns: 1fr; }
            .faq-row { grid-template-columns: 1fr; }
            .ga-canvas-wrap { height: 225px; }
        }
    </style>
    <script>
        function addFaqField() {
            const container = document.getElementById('faq-container');
            if (!container) return;
            const row = document.createElement('div');
            row.className = 'faq-row';
            row.innerHTML = `
                <input type="text" name="faq_q[]" class="form-control" placeholder="Pertanyaan...">
                <input type="text" name="faq_a[]" class="form-control" placeholder="Jawaban...">
                <button type="button" class="btn-admin btn-admin-danger" onclick="this.parentElement.remove()" style="padding:8px; width:100%; justify-content:center;" aria-label="Hapus FAQ">X</button>
            `;
            container.appendChild(row);
        }
    </script>
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="admin-main">
            <div class="admin-top">
                <div>
                    <h1 class="admin-title">
                        <?php
                        if ($action === 'audit' && $page_data) {
                            echo 'Audit &amp; Perbandingan SEO';
                        } elseif ($action === 'edit' && $page_data) {
                            echo 'Edit Metadata SEO';
                        } elseif ($action === 'new') {
                            echo 'Tambah SEO Landing Page';
                        } else {
                            echo 'SEO Landing Pages';
                        }
                        ?>
                    </h1>
                    <p class="admin-subtitle">
                        <?php if ($action === 'audit' && $page_data): ?>
                            Pantau jadwal <strong>Next Audit</strong> dan bandingkan performa sebelum vs sesudah update SEO untuk <strong><?php echo sanitize($page_data['title']); ?></strong>.
                        <?php elseif ($action === 'list'): ?>
                            Kelola metadata SEO, jadwal Next Audit otomatis, histori revisi, dan perbandingan performa Before vs After (Website + GSC).
                        <?php else: ?>
                            Atur Meta Title, Meta Description, Open Graph, FAQ Schema, serta siklus evaluasi SEO per halaman.
                        <?php endif; ?>
                    </p>
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <?php if ($action === 'audit' && $page_data): ?>
                        <a href="?action=edit&id=<?php echo (int)$page_data['id']; ?>" class="btn-admin btn-admin-primary">Edit SEO Meta</a>
                        <a href="<?php echo sanitize(BASE_URL . ($page_data['slug'] !== '' ? ($page_data['slug'] . '/') : '')); ?>" target="_blank" rel="noopener" class="btn-admin btn-admin-secondary">Lihat Halaman</a>
                        <a href="pages.php" class="btn-admin btn-admin-secondary">Kembali ke Daftar</a>
                    <?php elseif ($action === 'edit' && $page_data): ?>
                        <a href="?action=audit&id=<?php echo (int)$page_data['id']; ?>" class="btn-admin btn-admin-secondary">Audit &amp; Bandingkan</a>
                        <a href="pages.php" class="btn-admin btn-admin-secondary">Kembali ke Daftar</a>
                    <?php elseif ($action !== 'list'): ?>
                        <a href="pages.php" class="btn-admin btn-admin-secondary">Kembali ke Daftar</a>
                    <?php else: ?>
                        <a href="?action=new" class="btn-admin btn-admin-primary">+ Tambah Halaman</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="admin-alert admin-alert-error" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span><?php echo sanitize($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="admin-alert admin-alert-success" role="status">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span><?php echo sanitize($success); ?></span>
                </div>
            <?php endif; ?>

            <!-- ===================================================================== -->
            <!-- VIEW 1: AUDIT & BEFORE/AFTER COMPARISON (?action=audit&id=X)          -->
            <!-- ===================================================================== -->
            <?php if ($action === 'audit' && $page_data && $page_schedule && $fp_compare):
                $slug_display = $page_data['slug'] !== '' ? ('/' . $page_data['slug'] . '/') : '/';
                $b = $fp_compare['before'];
                $a = $fp_compare['after'];
                $b_seogeo = $b['organic_views'] + $b['geo_views'];
                $a_seogeo = $a['organic_views'] + $a['geo_views'];
                $b_cvr = $b['sessions'] > 0 ? round(($b['conversions'] / $b['sessions']) * 100, 2) : 0.0;
                $a_cvr = $a['sessions'] > 0 ? round(($a['conversions'] / $a['sessions']) * 100, 2) : 0.0;
                $cvr_diff = round($a_cvr - $b_cvr, 2);
            ?>
                <!-- Schedule Status Strip -->
                <section class="seo-audit-banner" aria-label="Jadwal Audit SEO Halaman">
                    <div>
                        <div class="seo-banner-item-lbl">URL Halaman</div>
                        <div class="seo-banner-item-val"><span class="admin-code-pill"><?php echo sanitize($slug_display); ?></span></div>
                    </div>
                    <div>
                        <div class="seo-banner-item-lbl">Last SEO Update</div>
                        <div class="seo-banner-item-val admin-num"><?php echo date('d M Y H:i', strtotime($fp_compare['pivot_dt'])); ?></div>
                        <div class="seo-card-sub"><?php echo (int)$page_schedule['days_since_update']; ?> hari yang lalu</div>
                    </div>
                    <div>
                        <div class="seo-banner-item-lbl">Last Audit / Snapshot</div>
                        <div class="seo-banner-item-val admin-num"><?php echo !empty($page_data['last_audited_at']) ? date('d M Y H:i', strtotime($page_data['last_audited_at'])) : 'Belum pernah'; ?></div>
                        <div class="seo-card-sub">Siklus: <?php echo (int)$page_schedule['interval_days']; ?> hari</div>
                    </div>
                    <div>
                        <div class="seo-banner-item-lbl">Jadwal Next Audit</div>
                        <div class="seo-banner-item-val" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <span class="admin-num"><?php echo sanitize($page_schedule['next_date']); ?></span>
                            <span class="badge <?php echo $page_schedule['badge']; ?>"><?php echo sanitize($page_schedule['label']); ?></span>
                        </div>
                    </div>
                </section>

                <!-- GA4-Style Interactive Before vs After & GSC Trend Chart -->
                <section class="admin-card" aria-label="Grafik GA4 Before vs After Halaman">
                    <div class="ga-filter-row">
                        <div class="admin-pill-group" role="navigation" aria-label="Rentang grafik audit">
                            <?php foreach (['14' => '14 Hari', '28' => '28 Hari', '30' => '30 Hari', 'all' => 'Semua Data'] as $wk => $wlbl):
                                $wk_str = (string)$wk;
                                $is_pill_active = (!$fp_compare['is_custom'] && (($wk_str === 'all' && !empty($fp_compare['is_all'])) || ($wk_str !== 'all' && empty($fp_compare['is_all']) && (string)$compare_window === $wk_str)));
                            ?>
                                <a href="<?php echo sanitize($build_audit_url(['win' => $wk_str, 'a_start' => '', 'a_end' => ''])); ?>" class="admin-pill<?php echo $is_pill_active ? ' active' : ''; ?>"><?php echo $wlbl; ?></a>
                            <?php endforeach; ?>
                        </div>

                        <form method="GET" action="<?php echo BASE_URL; ?>admin/pages.php" class="ga-filter-form">
                            <input type="hidden" name="action" value="audit">
                            <input type="hidden" name="id" value="<?php echo (int)$page_data['id']; ?>">
                            <input type="hidden" name="win" id="audit-win-input" value="<?php echo $fp_compare['is_custom'] ? 'custom' : (!empty($fp_compare['is_all']) ? 'all' : (int)$compare_window); ?>">

                            <select name="channel" class="seo-filter-input" aria-label="Filter Channel Traffic">
                                <?php foreach ($seo_channels as $ck => $clbl): ?>
                                    <option value="<?php echo sanitize($ck); ?>" <?php echo $audit_channel === $ck ? 'selected' : ''; ?>><?php echo sanitize($clbl); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="date" name="a_start" value="<?php echo sanitize($audit_custom_start); ?>" class="seo-filter-input" aria-label="Tanggal Mulai Custom" title="Tanggal Mulai Custom" onchange="document.getElementById('audit-win-input').value='custom'">
                            <span style="color:var(--admin-subtle); font-size:0.8rem;">–</span>
                            <input type="date" name="a_end" value="<?php echo sanitize($audit_custom_end); ?>" class="seo-filter-input" aria-label="Tanggal Akhir Custom" title="Tanggal Akhir Custom" onchange="document.getElementById('audit-win-input').value='custom'">
                            <button type="submit" class="btn-admin btn-admin-primary btn-admin-sm" style="height:35px; padding:0 12px;">Terapkan</button>
                            <?php if ($audit_channel !== '' || $fp_compare['is_custom']): ?>
                                <a href="?action=audit&amp;id=<?php echo (int)$page_data['id']; ?>" class="btn-admin btn-admin-secondary btn-admin-sm" style="height:35px; padding:0 10px;">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- 4 Clickable GA4 Metric Scorecards -->
                    <div class="ga-scorecards" id="auditScorecards" role="tablist" aria-label="Pilih Metrik Grafik">
                        <button type="button" class="ga-scorecard active" data-audit-metric="seo_geo_views" role="tab" aria-selected="true">
                            <div class="ga-sc-lbl">SEO + GEO Views</div>
                            <div class="ga-sc-row">
                                <span class="ga-sc-val"><?php echo number_format($a_seogeo); ?></span>
                                <?php echo seo_render_delta_badge(seo_delta_pct($b_seogeo, $a_seogeo)); ?>
                            </div>
                            <div class="ga-sc-prev">Sblm: <?php echo number_format($b_seogeo); ?></div>
                        </button>
                        <button type="button" class="ga-scorecard" data-audit-metric="total_views" role="tab" aria-selected="false">
                            <div class="ga-sc-lbl">Total Views</div>
                            <div class="ga-sc-row">
                                <span class="ga-sc-val"><?php echo number_format($a['total_views']); ?></span>
                                <?php echo seo_render_delta_badge(seo_delta_pct($b['total_views'], $a['total_views'])); ?>
                            </div>
                            <div class="ga-sc-prev">Sblm: <?php echo number_format($b['total_views']); ?></div>
                        </button>
                        <button type="button" class="ga-scorecard" data-audit-metric="sessions" role="tab" aria-selected="false">
                            <div class="ga-sc-lbl">Sesi Unik</div>
                            <div class="ga-sc-row">
                                <span class="ga-sc-val"><?php echo number_format($a['sessions']); ?></span>
                                <?php echo seo_render_delta_badge(seo_delta_pct($b['sessions'], $a['sessions'])); ?>
                            </div>
                            <div class="ga-sc-prev">Sblm: <?php echo number_format($b['sessions']); ?></div>
                        </button>
                        <button type="button" class="ga-scorecard" data-audit-metric="conversions" role="tab" aria-selected="false">
                            <div class="ga-sc-lbl">Konversi</div>
                            <div class="ga-sc-row">
                                <span class="ga-sc-val"><?php echo number_format($a['conversions']); ?></span>
                                <?php echo seo_render_delta_badge(seo_delta_pct($b['conversions'], $a['conversions'])); ?>
                            </div>
                            <div class="ga-sc-prev">Sblm: <?php echo number_format($b['conversions']); ?></div>
                        </button>
                    </div>

                    <?php
                    $lbl_curr_period = !empty($fp_compare['is_all']) ? 'Sesudah Update' : 'Periode Ini';
                    $lbl_prev_period = !empty($fp_compare['is_all']) ? 'Sebelum Update' : 'Periode Sebelumnya';
                    ?>
                    <div class="ga-toolbar-sub">
                        <div class="ga-legend" id="auditChartLegend">
                            <span class="ga-legend-item">
                                <span class="ga-line-solid"></span>
                                <span><?php echo $lbl_curr_period; ?> (<span class="admin-num"><?php echo sanitize($fp_compare['after_range']); ?></span>)</span>
                            </span>
                            <span class="ga-legend-item" id="auditPrevLegend">
                                <span class="ga-line-dashed"></span>
                                <span><?php echo $lbl_prev_period; ?> (<span class="admin-num"><?php echo sanitize($fp_compare['before_range']); ?></span>)</span>
                            </span>
                        </div>
                        <div class="ga-controls-right">
                            <label class="ga-compare-toggle" id="auditCompareWrap">
                                <input type="checkbox" id="auditCompareChk" checked>
                                <span>Bandingkan Sebelum</span>
                            </label>
                            <?php $def_chart_mode = !empty($fp_compare['is_all']) ? 'timeline' : 'overlay'; ?>
                            <div class="ga-seg-group" role="group" aria-label="Mode Grafik Audit">
                                <button type="button" class="ga-seg-btn<?php echo $def_chart_mode === 'overlay' ? ' active' : ''; ?>" data-audit-mode="overlay" aria-pressed="<?php echo $def_chart_mode === 'overlay' ? 'true' : 'false'; ?>">Compare Overlay</button>
                                <button type="button" class="ga-seg-btn<?php echo $def_chart_mode === 'timeline' ? ' active' : ''; ?>" data-audit-mode="timeline" aria-pressed="<?php echo $def_chart_mode === 'timeline' ? 'true' : 'false'; ?>">Timeline Kontinu</button>
                                <button type="button" class="ga-seg-btn" data-audit-mode="gsc_impr" aria-pressed="false">GSC Impr/Klik</button>
                                <button type="button" class="ga-seg-btn" data-audit-mode="gsc_rank" aria-pressed="false">GSC CTR/Posisi</button>
                            </div>
                        </div>
                    </div>

                    <div class="ga-canvas-wrap" id="auditChartWrap">
                        <canvas id="seoAuditChart" role="img" aria-label="Grafik perbandingan performa SEO sebelum dan sesudah update"></canvas>
                    </div>
                    <div id="auditGscEmpty" class="admin-empty" style="display:none; padding:32px 16px;">
                        Catat minimal <strong>2 snapshot GSC</strong> pada form di bawah untuk melihat grafik tren Impressions, Clicks, CTR, dan Posisi Google.
                    </div>
                </section>

                <!-- Main 2-Column Audit Workspace -->
                <div class="seo-audit-grid">
                    <!-- LEFT: Automatic First-Party Before vs After -->
                    <section class="admin-card" style="margin-bottom: 0;">
                        <div class="seo-card-head">
                            <div>
                                <h2>Ringkasan Performa Website (Before vs After)</h2>
                                <p class="seo-card-sub">
                                    Filter aktif: <strong><?php echo sanitize($seo_channels[$audit_channel] ?? 'Semua Channel'); ?></strong> ·
                                    <?php echo $fp_compare['is_custom'] ? 'Rentang Custom' : (!empty($fp_compare['is_all']) ? 'Semua Data (All Time)' : ($fp_compare['effective_days'] . ' Hari Terakhir vs ' . $fp_compare['effective_days'] . ' Hari Sebelumnya')); ?>
                                </p>
                            </div>
                        </div>

                        <?php if (!empty($fp_compare['is_all'])): ?>
                            <div class="admin-alert admin-alert-info" style="margin-bottom: 14px; font-size: 0.82rem;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                <span>
                                    <?php if (!empty($fp_compare['auto_all_fallback'])): ?>
                                        Update SEO baru dilakukan hari ini, sehingga sistem otomatis menampilkan mode <strong>Semua Data (All Time)</strong> agar seluruh histori sebelum update tetap terlihat.
                                    <?php else: ?>
                                        Mode <strong>Semua Data (All Time)</strong> menampilkan seluruh histori tercatat sebelum update SEO dibandingkan dengan data sejak update terakhir.
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php elseif ($fp_compare['is_partial']): ?>
                            <div class="admin-alert admin-alert-info" style="margin-bottom: 14px; font-size: 0.82rem;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                <span>
                                    Baru berjalan <strong><?php echo (int)$fp_compare['effective_days']; ?> dari <?php echo (int)$fp_compare['requested_days']; ?> hari</strong> sejak update SEO terakhir.
                                    Agar <em>apple-to-apple</em>, perbandingan saat ini menggunakan rentang setara <strong><?php echo (int)$fp_compare['effective_days']; ?> hari sebelum vs <?php echo (int)$fp_compare['effective_days']; ?> hari sesudah</strong>.
                                    Evaluasi penuh <?php echo (int)$fp_compare['requested_days']; ?> hari matang pada <strong><?php echo sanitize($fp_compare['mature_date']); ?></strong>.
                                </span>
                            </div>
                        <?php endif; ?>

                        <div style="display:flex; gap:14px; flex-wrap:wrap; margin-bottom:14px; font-size:0.78rem; color:var(--admin-muted);">
                            <span><strong><?php echo $lbl_prev_period; ?>:</strong> <span class="admin-num"><?php echo sanitize($fp_compare['before_range']); ?></span></span>
                            <span><strong><?php echo $lbl_curr_period; ?>:</strong> <span class="admin-num"><?php echo sanitize($fp_compare['after_range']); ?></span></span>
                        </div>

                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Metrik Halaman</th>
                                        <th style="text-align:right;"><?php echo !empty($fp_compare['is_all']) ? 'Sebelum' : 'Periode Sblm'; ?></th>
                                        <th style="text-align:right;"><?php echo !empty($fp_compare['is_all']) ? 'Sesudah' : 'Periode Ini'; ?></th>
                                        <th style="text-align:right;">Perubahan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>SEO Organic + GEO/AI Views</strong></td>
                                        <td class="admin-num" style="text-align:right;"><?php echo number_format($b_seogeo); ?></td>
                                        <td class="admin-num" style="text-align:right;"><strong><?php echo number_format($a_seogeo); ?></strong></td>
                                        <td style="text-align:right;"><?php echo seo_render_delta_badge(seo_delta_pct($b_seogeo, $a_seogeo)); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left:24px; color:var(--admin-muted);">↳ SEO Organic</td>
                                        <td class="admin-num" style="text-align:right; color:var(--admin-muted);"><?php echo number_format($b['organic_views']); ?></td>
                                        <td class="admin-num" style="text-align:right; color:var(--admin-muted);"><?php echo number_format($a['organic_views']); ?></td>
                                        <td style="text-align:right;"><?php echo seo_render_delta_badge(seo_delta_pct($b['organic_views'], $a['organic_views'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left:24px; color:var(--admin-muted);">↳ GEO / AI Search</td>
                                        <td class="admin-num" style="text-align:right; color:var(--admin-muted);"><?php echo number_format($b['geo_views']); ?></td>
                                        <td class="admin-num" style="text-align:right; color:var(--admin-muted);"><?php echo number_format($a['geo_views']); ?></td>
                                        <td style="text-align:right;"><?php echo seo_render_delta_badge(seo_delta_pct($b['geo_views'], $a['geo_views'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Page Views (Semua Channel)</strong></td>
                                        <td class="admin-num" style="text-align:right;"><?php echo number_format($b['total_views']); ?></td>
                                        <td class="admin-num" style="text-align:right;"><strong><?php echo number_format($a['total_views']); ?></strong></td>
                                        <td style="text-align:right;"><?php echo seo_render_delta_badge(seo_delta_pct($b['total_views'], $a['total_views'])); ?></td>
                                    </tr>
                                    <?php
                                    $extra_channel_rows = [
                                        'google_ads'     => ['key' => 'google_ads_views', 'label' => 'Google Ads'],
                                        'meta_ads'       => ['key' => 'meta_ads_views',   'label' => 'Meta Ads'],
                                        'paid_ads'       => ['key' => 'paid_ads_views',   'label' => 'Paid Ads Lain'],
                                        'social_organic' => ['key' => 'social_views',     'label' => 'Social Organic'],
                                        'referral'       => ['key' => 'referral_views',   'label' => 'Referral'],
                                        'direct'         => ['key' => 'direct_views',     'label' => 'Direct / Langsung'],
                                    ];
                                    foreach ($extra_channel_rows as $ch_slug => $ch_meta):
                                        if ($audit_channel !== '' && $audit_channel !== $ch_slug) {
                                            continue;
                                        }
                                        $bv = (int)($b[$ch_meta['key']] ?? 0);
                                        $av = (int)($a[$ch_meta['key']] ?? 0);
                                        $row_color = ($bv > 0 || $av > 0) ? 'var(--admin-ink-2)' : 'var(--admin-subtle)';
                                    ?>
                                    <tr>
                                        <td style="padding-left:24px; color:<?php echo $row_color; ?>;">↳ <?php echo sanitize($ch_meta['label']); ?></td>
                                        <td class="admin-num" style="text-align:right; color:<?php echo $row_color; ?>;"><?php echo number_format($bv); ?></td>
                                        <td class="admin-num" style="text-align:right; color:<?php echo $row_color; ?>;"><?php echo number_format($av); ?></td>
                                        <td style="text-align:right;"><?php echo seo_render_delta_badge(seo_delta_pct($bv, $av)); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td><strong>Sesi Unik</strong></td>
                                        <td class="admin-num" style="text-align:right;"><?php echo number_format($b['sessions']); ?></td>
                                        <td class="admin-num" style="text-align:right;"><strong><?php echo number_format($a['sessions']); ?></strong></td>
                                        <td style="text-align:right;"><?php echo seo_render_delta_badge(seo_delta_pct($b['sessions'], $a['sessions'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Konversi (Booking + WA Sales + Lead)</strong></td>
                                        <td class="admin-num" style="text-align:right;"><?php echo number_format($b['conversions']); ?></td>
                                        <td class="admin-num" style="text-align:right;"><strong><?php echo number_format($a['conversions']); ?></strong></td>
                                        <td style="text-align:right;"><?php echo seo_render_delta_badge(seo_delta_pct($b['conversions'], $a['conversions'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Conversion Rate (CVR)</strong></td>
                                        <td class="admin-num" style="text-align:right;"><?php echo number_format($b_cvr, 2); ?>%</td>
                                        <td class="admin-num" style="text-align:right;"><strong><?php echo number_format($a_cvr, 2); ?>%</strong></td>
                                        <td style="text-align:right;"><?php echo seo_render_delta_badge($cvr_diff, false, ' pp'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- RIGHT: GSC Before vs After + Log Snapshot Form -->
                    <div style="display:flex; flex-direction:column; gap:20px;">
                        <!-- GSC Comparison Card -->
                        <section class="admin-card" style="margin-bottom: 0;">
                            <div class="seo-card-head">
                                <div>
                                    <h2>Perbandingan Google Search Console (GSC)</h2>
                                    <p class="seo-card-sub">
                                        <?php if ($gsc_before && $gsc_after): ?>
                                            Snapshot <span class="admin-num"><?php echo date('d M Y', strtotime($gsc_before['created_at'])); ?></span> vs <span class="admin-num"><?php echo date('d M Y', strtotime($gsc_after['created_at'])); ?></span>
                                        <?php elseif ($gsc_before): ?>
                                            Baseline tercatat pada <span class="admin-num"><?php echo date('d M Y', strtotime($gsc_before['created_at'])); ?></span> — catat snapshot berikutnya pada Next Audit untuk melihat delta
                                        <?php else: ?>
                                            Belum ada data GSC dicatat — isi form di bawah untuk menyimpan baseline pertama
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <?php if ($gsc_before):
                                $curr = $gsc_after ?: $gsc_before;
                                $prev = $gsc_after ? $gsc_before : null;
                                $impr_delta = ($prev && $prev['gsc_impressions'] !== null && $curr['gsc_impressions'] !== null) ? seo_delta_pct((float)$prev['gsc_impressions'], (float)$curr['gsc_impressions']) : null;
                                $clk_delta  = ($prev && $prev['gsc_clicks'] !== null && $curr['gsc_clicks'] !== null) ? seo_delta_pct((float)$prev['gsc_clicks'], (float)$curr['gsc_clicks']) : null;
                                $ctr_delta  = ($prev && $prev['gsc_ctr'] !== null && $curr['gsc_ctr'] !== null) ? round((float)$curr['gsc_ctr'] - (float)$prev['gsc_ctr'], 2) : null;
                                $pos_delta  = ($prev && $prev['gsc_position'] !== null && $curr['gsc_position'] !== null) ? round((float)$curr['gsc_position'] - (float)$prev['gsc_position'], 2) : null;
                            ?>
                                <div class="seo-gsc-grid">
                                    <div class="seo-gsc-box">
                                        <div class="seo-gsc-box-lbl">Impressions</div>
                                        <div class="seo-gsc-box-vals">
                                            <span class="seo-gsc-main"><?php echo $curr['gsc_impressions'] !== null ? number_format((int)$curr['gsc_impressions']) : '—'; ?></span>
                                            <?php echo $prev ? seo_render_delta_badge($impr_delta) : '<span class="badge badge-info">Baseline</span>'; ?>
                                        </div>
                                        <?php if ($prev && $prev['gsc_impressions'] !== null): ?>
                                            <div class="seo-gsc-prev">Sblm: <?php echo number_format((int)$prev['gsc_impressions']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="seo-gsc-box">
                                        <div class="seo-gsc-box-lbl">Clicks</div>
                                        <div class="seo-gsc-box-vals">
                                            <span class="seo-gsc-main"><?php echo $curr['gsc_clicks'] !== null ? number_format((int)$curr['gsc_clicks']) : '—'; ?></span>
                                            <?php echo $prev ? seo_render_delta_badge($clk_delta) : '<span class="badge badge-info">Baseline</span>'; ?>
                                        </div>
                                        <?php if ($prev && $prev['gsc_clicks'] !== null): ?>
                                            <div class="seo-gsc-prev">Sblm: <?php echo number_format((int)$prev['gsc_clicks']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="seo-gsc-box">
                                        <div class="seo-gsc-box-lbl">CTR (%)</div>
                                        <div class="seo-gsc-box-vals">
                                            <span class="seo-gsc-main"><?php echo $curr['gsc_ctr'] !== null ? number_format((float)$curr['gsc_ctr'], 2) . '%' : '—'; ?></span>
                                            <?php echo $prev ? seo_render_delta_badge($ctr_delta, false, ' pp') : '<span class="badge badge-info">Baseline</span>'; ?>
                                        </div>
                                        <?php if ($prev && $prev['gsc_ctr'] !== null): ?>
                                            <div class="seo-gsc-prev">Sblm: <?php echo number_format((float)$prev['gsc_ctr'], 2); ?>%</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="seo-gsc-box">
                                        <div class="seo-gsc-box-lbl">Avg Position (lebih kecil = lebih baik)</div>
                                        <div class="seo-gsc-box-vals">
                                            <span class="seo-gsc-main"><?php echo $curr['gsc_position'] !== null ? number_format((float)$curr['gsc_position'], 1) : '—'; ?></span>
                                            <?php echo $prev ? seo_render_delta_badge($pos_delta, true, ' pos') : '<span class="badge badge-info">Baseline</span>'; ?>
                                        </div>
                                        <?php if ($prev && $prev['gsc_position'] !== null): ?>
                                            <div class="seo-gsc-prev">Sblm: <?php echo number_format((float)$prev['gsc_position'], 1); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="admin-empty" style="padding: 16px;">Belum ada snapshot GSC tersimpan untuk halaman ini.</div>
                            <?php endif; ?>
                        </section>

                        <!-- Form: Log New Audit & GSC Snapshot -->
                        <section class="admin-card" style="margin-bottom: 0;">
                            <div class="seo-card-head">
                                <div>
                                    <h3>Catat Snapshot Audit &amp; GSC Baru</h3>
                                    <p class="seo-card-sub">Menyimpan snapshot ini otomatis memperbarui jadwal <strong>Next Audit</strong>.</p>
                                </div>
                            </div>
                            <form method="POST" action="<?php echo BASE_URL; ?>admin/pages.php?action=audit&amp;id=<?php echo (int)$page_data['id']; ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="page_id" value="<?php echo (int)$page_data['id']; ?>">

                                <div class="seo-form-grid-2" style="margin-bottom: 12px;">
                                    <div>
                                        <label class="form-label" for="aud-period">Rentang Data GSC</label>
                                        <select id="aud-period" name="gsc_period_days" class="form-control">
                                            <option value="28" selected>28 Hari Terakhir (Standar GSC)</option>
                                            <option value="14">14 Hari Terakhir</option>
                                            <option value="30">30 Hari Terakhir</option>
                                            <option value="90">90 Hari Terakhir</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="aud-interval">Siklus Next Audit</label>
                                        <select id="aud-interval" name="audit_interval_days" class="form-control">
                                            <?php $cur_int = (int)($page_data['audit_interval_days'] ?? 30); ?>
                                            <option value="14" <?php echo $cur_int === 14 ? 'selected' : ''; ?>>14 Hari (Cepat / Eksperimen)</option>
                                            <option value="30" <?php echo $cur_int === 30 ? 'selected' : ''; ?>>30 Hari (Standar SEO)</option>
                                            <option value="60" <?php echo $cur_int === 60 ? 'selected' : ''; ?>>60 Hari</option>
                                            <option value="90" <?php echo $cur_int === 90 ? 'selected' : ''; ?>>90 Hari (Halaman Stabil)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="seo-form-grid-2" style="margin-bottom: 12px;">
                                    <div>
                                        <label class="form-label" for="aud-impr">GSC Impressions</label>
                                        <input type="number" id="aud-impr" name="gsc_impressions" min="0" class="form-control" placeholder="cth: 1250">
                                    </div>
                                    <div>
                                        <label class="form-label" for="aud-clk">GSC Clicks</label>
                                        <input type="number" id="aud-clk" name="gsc_clicks" min="0" class="form-control" placeholder="cth: 84">
                                    </div>
                                </div>

                                <div class="seo-form-grid-2" style="margin-bottom: 12px;">
                                    <div>
                                        <label class="form-label" for="aud-ctr">GSC CTR (%) — auto-hitung</label>
                                        <input type="number" id="aud-ctr" name="gsc_ctr" step="0.01" min="0" max="100" class="form-control" placeholder="Otomatis dari Clicks/Impr">
                                    </div>
                                    <div>
                                        <label class="form-label" for="aud-pos">GSC Avg Position</label>
                                        <input type="number" id="aud-pos" name="gsc_position" step="0.1" min="1" class="form-control" placeholder="cth: 6.4">
                                    </div>
                                </div>

                                <div style="margin-bottom: 14px;">
                                    <label class="form-label" for="aud-notes">Catatan Evaluasi / Action Item</label>
                                    <textarea id="aud-notes" name="notes" class="form-control" rows="2" placeholder="cth: CTR naik setelah tambah harga di Meta Title, posisi stabil di Top 5."></textarea>
                                </div>

                                <button type="submit" name="log_audit" value="1" class="btn-admin btn-admin-primary" style="width: 100%; padding: 10px;">
                                    Simpan Snapshot Audit &amp; Reset Jadwal
                                </button>
                            </form>
                        </section>
                    </div>
                </div>

                <!-- Timeline: Meta Revision Diffs + Audit Snapshots -->
                <section class="admin-card">
                    <div class="seo-card-head">
                        <div>
                            <h2>Riwayat Revisi Meta &amp; Log Audit (<?php echo count($audit_history); ?>)</h2>
                            <p class="seo-card-sub">Perbandingan teks Meta Title/Description sebelum vs sesudah beserta snapshot performa saat itu</p>
                        </div>
                    </div>

                    <?php if (empty($audit_history)): ?>
                        <div class="admin-empty">
                            Belum ada riwayat revisi atau audit tercatat. Riwayat akan otomatis terekam saat Anda mengedit SEO halaman ini atau mencatat snapshot GSC di atas.
                        </div>
                    <?php else: ?>
                        <div class="seo-timeline">
                            <?php foreach ($audit_history as $ah):
                                $is_meta = ($ah['event_type'] === 'meta_update');
                                $has_gsc_row = ($ah['gsc_impressions'] !== null || $ah['gsc_clicks'] !== null || $ah['gsc_ctr'] !== null || $ah['gsc_position'] !== null);
                            ?>
                                <article class="seo-timeline-item">
                                    <div class="seo-timeline-head">
                                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                            <span class="badge <?php echo $is_meta ? 'badge-info' : 'badge-success'; ?>">
                                                <?php echo $is_meta ? 'Update Meta SEO' : 'Snapshot Audit / GSC'; ?>
                                            </span>
                                            <span class="admin-num" style="font-size:0.78rem; color:var(--admin-subtle);">
                                                <?php echo date('d M Y H:i', strtotime($ah['created_at'])); ?>
                                            </span>
                                            <span style="font-size:0.78rem; color:var(--admin-muted);">
                                                oleh <strong><?php echo sanitize($ah['username'] ?: 'Sistem'); ?></strong>
                                            </span>
                                        </div>
                                        <form method="POST" action="<?php echo BASE_URL; ?>admin/pages.php?action=audit&amp;id=<?php echo (int)$page_data['id']; ?>" style="margin:0;" onsubmit="return confirm('Hapus catatan riwayat audit ini?')">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="page_id" value="<?php echo (int)$page_data['id']; ?>">
                                            <input type="hidden" name="audit_id" value="<?php echo (int)$ah['id']; ?>">
                                            <button type="submit" name="delete_audit" value="1" class="btn-admin btn-admin-secondary btn-admin-sm" style="color:#b91c1c;">Hapus</button>
                                        </form>
                                    </div>

                                    <?php if (!empty($ah['notes'])): ?>
                                        <div style="font-size:0.84rem; color:var(--admin-ink); margin-bottom:8px;">
                                            <?php echo nl2br(sanitize($ah['notes'])); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div style="display:flex; flex-wrap:wrap; gap:8px; font-size:0.76rem;">
                                        <span class="seo-chip seo-chip-ok">
                                            Web (<?php echo (int)$ah['gsc_period_days']; ?>h): <?php echo number_format((int)$ah['fp_total_views']); ?> views · <?php echo number_format((int)$ah['fp_organic_views'] + (int)$ah['fp_geo_views']); ?> SEO/GEO · <?php echo number_format((int)$ah['fp_conversions']); ?> conv
                                        </span>
                                        <?php if ($has_gsc_row): ?>
                                            <span class="seo-chip" style="background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe;">
                                                GSC: Impr <?php echo $ah['gsc_impressions'] !== null ? number_format((int)$ah['gsc_impressions']) : '—'; ?> ·
                                                Clicks <?php echo $ah['gsc_clicks'] !== null ? number_format((int)$ah['gsc_clicks']) : '—'; ?> ·
                                                CTR <?php echo $ah['gsc_ctr'] !== null ? number_format((float)$ah['gsc_ctr'], 2) . '%' : '—'; ?> ·
                                                Pos <?php echo $ah['gsc_position'] !== null ? number_format((float)$ah['gsc_position'], 1) : '—'; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($is_meta && ($ah['prev_meta_title'] !== $ah['new_meta_title'] || $ah['prev_meta_description'] !== $ah['new_meta_description'])): ?>
                                        <div class="seo-diff-box">
                                            <div class="seo-diff-col seo-diff-old">
                                                <div class="seo-diff-tag" style="color:#c2410c;">Sebelum Update</div>
                                                <div><strong>Title:</strong> <?php echo sanitize($ah['prev_meta_title'] ?: '—'); ?></div>
                                                <div style="margin-top:4px;"><strong>Desc:</strong> <?php echo sanitize($ah['prev_meta_description'] ?: '—'); ?></div>
                                            </div>
                                            <div class="seo-diff-col seo-diff-new">
                                                <div class="seo-diff-tag" style="color:#047857;">Sesudah Update</div>
                                                <div><strong>Title:</strong> <?php echo sanitize($ah['new_meta_title'] ?: '—'); ?></div>
                                                <div style="margin-top:4px;"><strong>Desc:</strong> <?php echo sanitize($ah['new_meta_description'] ?: '—'); ?></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <script>
                (function () {
                    var impr = document.getElementById('aud-impr');
                    var clk  = document.getElementById('aud-clk');
                    var ctr  = document.getElementById('aud-ctr');
                    if (!impr || !clk || !ctr) return;
                    function autoCtr() {
                        var i = parseFloat(impr.value);
                        var c = parseFloat(clk.value);
                        if (i > 0 && !isNaN(c) && c >= 0) {
                            ctr.placeholder = ((c / i) * 100).toFixed(2) + '% (otomatis)';
                        }
                    }
                    impr.addEventListener('input', autoCtr);
                    clk.addEventListener('input', autoCtr);
                })();
                </script>

            <!-- ===================================================================== -->
            <!-- VIEW 2: NEW LANDING PAGE FORM (?action=new)                           -->
            <!-- ===================================================================== -->
            <?php elseif ($action === 'new'): ?>
                <div class="admin-card">
                    <h3>Daftarkan Halaman Landing Page Baru</h3>
                    <p class="form-help" style="margin-top: 4px; margin-bottom: 20px;">
                        Daftarkan halaman fisik atau rute dinamis (mis. <code>virtual-office-malang</code>, <code>sewa-kantor-jakarta</code>) agar punya meta SEO &amp; jadwal audit sendiri.
                    </p>
                    <form action="" method="POST" class="form-horizontal">
                        <?php echo csrf_field(); ?>

                        <div class="seo-form-grid-2" style="margin-bottom: 18px;">
                            <div>
                                <label class="form-label" for="new-title">Nama Halaman (Tampilan Admin) *</label>
                                <input type="text" id="new-title" name="title" class="form-control" placeholder="Perizinan &amp; Perubahan Perusahaan" required>
                            </div>
                            <div>
                                <label class="form-label" for="new-slug">Slug URL (Tanpa Garis Miring) *</label>
                                <input type="text" id="new-slug" name="slug" class="form-control" placeholder="perizinan-dan-perubahan-perusahaan" required>
                            </div>
                        </div>

                        <div style="margin-bottom: 18px;">
                            <label class="form-label" for="seo-meta-title">SEO Meta Title *</label>
                            <input type="text" id="seo-meta-title" name="meta_title" class="form-control" required>
                            <div class="char-counter">
                                <span class="char-counter-text">Rekomendasi: 50 – 60 karakter</span>
                                <span class="char-counter-text"><span id="seo-mt-count" class="char-counter-num safe">0</span>/60</span>
                            </div>
                        </div>

                        <div style="margin-bottom: 18px;">
                            <label class="form-label" for="seo-meta-desc">SEO Meta Description *</label>
                            <textarea id="seo-meta-desc" name="meta_description" class="form-control" style="height: 90px;" required></textarea>
                            <div class="char-counter">
                                <span class="char-counter-text">Rekomendasi: 120 – 160 karakter</span>
                                <span class="char-counter-text"><span id="seo-md-count" class="char-counter-num safe">0</span>/160</span>
                            </div>
                        </div>

                        <div class="seo-form-grid-2" style="margin-bottom: 20px;">
                            <div>
                                <label class="form-label" for="new-canon">Canonical URL (Opsional)</label>
                                <input type="text" id="new-canon" name="canonical_url" class="form-control">
                            </div>
                            <div>
                                <label class="form-label" for="new-interval">Siklus Next Audit</label>
                                <select id="new-interval" name="audit_interval_days" class="form-control">
                                    <option value="14">14 Hari (Cepat)</option>
                                    <option value="30" selected>30 Hari (Standar SEO)</option>
                                    <option value="60">60 Hari</option>
                                    <option value="90">90 Hari (Stabil)</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" name="create" value="1" class="btn-admin btn-admin-primary" style="padding: 11px 24px;">
                            Simpan Halaman Baru
                        </button>
                    </form>
                </div>

            <!-- ===================================================================== -->
            <!-- VIEW 3: EDIT SEO METADATA FORM (?action=edit&id=X)                    -->
            <!-- ===================================================================== -->
            <?php elseif ($action === 'edit' && $page_data):
                $cur_int = (int)($page_data['audit_interval_days'] ?? 30);
                $edit_url_preview = BASE_URL . ($page_data['slug'] !== '' ? ($page_data['slug'] . '/') : '');
            ?>
                <div class="admin-card">
                    <div class="seo-card-head">
                        <div>
                            <h2>Edit SEO Meta: <?php echo sanitize($page_data['title']); ?></h2>
                            <p class="seo-card-sub">
                                Perubahan Meta Title / Description otomatis disimpan ke riwayat revisi untuk perbandingan Before vs After.
                            </p>
                        </div>
                        <?php if ($page_schedule): ?>
                            <span class="badge <?php echo $page_schedule['badge']; ?>">Next Audit: <?php echo sanitize($page_schedule['next_date']); ?> (<?php echo sanitize($page_schedule['label']); ?>)</span>
                        <?php endif; ?>
                    </div>

                    <form action="" method="POST" style="margin-top: 18px;" class="form-horizontal">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo (int)$page_data['id']; ?>">

                        <div style="margin-bottom: 18px;">
                            <label class="form-label" for="seo-meta-title">SEO Meta Title *</label>
                            <input type="text" id="seo-meta-title" name="meta_title" class="form-control" value="<?php echo sanitize($page_data['meta_title'] ?? ''); ?>" required>
                            <div class="char-counter">
                                <span class="char-counter-text">Rekomendasi: 50 – 60 karakter</span>
                                <span class="char-counter-text"><span id="seo-mt-count" class="char-counter-num safe">0</span>/60</span>
                            </div>
                        </div>

                        <div style="margin-bottom: 18px;">
                            <label class="form-label" for="seo-meta-desc">SEO Meta Description *</label>
                            <textarea id="seo-meta-desc" name="meta_description" class="form-control" style="height: 90px;" required><?php echo sanitize($page_data['meta_description'] ?? ''); ?></textarea>
                            <div class="char-counter">
                                <span class="char-counter-text">Rekomendasi: 120 – 160 karakter</span>
                                <span class="char-counter-text"><span id="seo-md-count" class="char-counter-num safe">0</span>/160</span>
                            </div>
                        </div>

                        <!-- Live SERP Preview -->
                        <div style="margin-bottom: 20px;">
                            <label class="form-label">Preview Hasil Google (SERP)</label>
                            <div class="seo-serp-box">
                                <div style="font-size:12px; color:#202124; margin-bottom:2px;"><?php echo sanitize($edit_url_preview); ?></div>
                                <div id="seo-serp-title" style="font-size:18px; color:#1a0dab; line-height:1.3; margin-bottom:4px;"><?php echo sanitize($page_data['meta_title'] ?? $page_data['title']); ?></div>
                                <div id="seo-serp-desc" style="font-size:13px; color:#4d5156; line-height:1.5;"><?php echo sanitize($page_data['meta_description'] ?? ''); ?></div>
                            </div>
                        </div>

                        <div class="seo-form-grid-2" style="margin-bottom: 18px;">
                            <div>
                                <label class="form-label" for="edit-canon">Canonical URL (Kosong = Otomatis)</label>
                                <input type="text" id="edit-canon" name="canonical_url" class="form-control" value="<?php echo sanitize($page_data['canonical_url'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="form-label" for="edit-interval">Siklus Next Audit SEO</label>
                                <select id="edit-interval" name="audit_interval_days" class="form-control">
                                    <option value="14" <?php echo $cur_int === 14 ? 'selected' : ''; ?>>14 Hari (Quick Check)</option>
                                    <option value="30" <?php echo $cur_int === 30 ? 'selected' : ''; ?>>30 Hari (Standar Evaluasi SEO)</option>
                                    <option value="60" <?php echo $cur_int === 60 ? 'selected' : ''; ?>>60 Hari</option>
                                    <option value="90" <?php echo $cur_int === 90 ? 'selected' : ''; ?>>90 Hari (Halaman Stabil)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Audit Note & Optional GSC Baseline on Edit -->
                        <div style="margin-bottom: 20px; padding: 14px 16px; background: var(--admin-light); border: 1px solid var(--admin-border); border-radius: 10px;">
                            <label class="form-label" for="edit-notes">Catatan Perubahan SEO (Opsional — Masuk ke Riwayat Audit)</label>
                            <input type="text" id="edit-notes" name="change_notes" class="form-control" placeholder="cth: Tambah keyword harga &amp; CTA untuk naikkan CTR Google">

                            <details style="margin-top: 12px;">
                                <summary style="cursor: pointer; font-size: 0.82rem; font-weight: 600; color: var(--admin-primary);">
                                    + Catat angka Baseline GSC sebelum update ini (Opsional)
                                </summary>
                                <div class="seo-form-grid-2" style="margin-top: 12px;">
                                    <div>
                                        <label class="form-label">GSC Impressions (28h)</label>
                                        <input type="number" name="gsc_impressions" min="0" class="form-control" placeholder="cth: 980">
                                    </div>
                                    <div>
                                        <label class="form-label">GSC Clicks (28h)</label>
                                        <input type="number" name="gsc_clicks" min="0" class="form-control" placeholder="cth: 45">
                                    </div>
                                    <div>
                                        <label class="form-label">GSC CTR (%)</label>
                                        <input type="number" name="gsc_ctr" step="0.01" min="0" max="100" class="form-control" placeholder="Otomatis jika dikosongkan">
                                    </div>
                                    <div>
                                        <label class="form-label">GSC Avg Position</label>
                                        <input type="number" name="gsc_position" step="0.1" min="1" class="form-control" placeholder="cth: 9.2">
                                    </div>
                                </div>
                            </details>
                        </div>

                        <!-- Open Graph Settings -->
                        <h3 style="margin: 26px 0 14px 0; border-top: 1px solid var(--admin-border); padding-top: 18px;">Open Graph (Social Sharing)</h3>

                        <div style="margin-bottom: 16px;">
                            <label class="form-label" for="edit-og-title">OG Title</label>
                            <input type="text" id="edit-og-title" name="og_title" class="form-control" value="<?php echo sanitize($page_data['og_title'] ?? ''); ?>">
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label class="form-label" for="edit-og-desc">OG Description</label>
                            <textarea id="edit-og-desc" name="og_description" class="form-control" style="height: 72px;"><?php echo sanitize($page_data['og_description'] ?? ''); ?></textarea>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label class="form-label" for="edit-og-img">OG Image (Path/URL)</label>
                            <input type="text" id="edit-og-img" name="og_image" class="form-control" value="<?php echo sanitize($page_data['og_image'] ?? ''); ?>">
                        </div>

                        <!-- FAQ JSON Schema Markup -->
                        <h3 style="margin: 26px 0 8px 0; border-top: 1px solid var(--admin-border); padding-top: 18px; display: flex; justify-content: space-between; align-items: center;">
                            FAQ Schema Markup
                            <button type="button" class="btn-admin btn-admin-secondary btn-admin-sm" onclick="addFaqField()">+ Tambah FAQ</button>
                        </h3>
                        <p class="form-help" style="margin-bottom: 14px;">Output JSON-LD structured data untuk hasil pencarian kaya Google &amp; AI Search.</p>

                        <div id="faq-container">
                            <?php if (!empty($faq_list)): foreach ($faq_list as $faq): ?>
                                <div class="faq-row">
                                    <input type="text" name="faq_q[]" class="form-control" value="<?php echo sanitize($faq['question'] ?? ''); ?>" placeholder="Pertanyaan...">
                                    <input type="text" name="faq_a[]" class="form-control" value="<?php echo sanitize($faq['answer'] ?? ''); ?>" placeholder="Jawaban...">
                                    <button type="button" class="btn-admin btn-admin-danger" onclick="this.parentElement.remove()" style="padding:8px; width:100%; justify-content:center;" aria-label="Hapus FAQ">X</button>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>

                        <button type="submit" name="update" value="1" class="btn-admin btn-admin-primary" style="margin-top: 24px; padding: 11px 24px;">
                            Simpan Perubahan SEO
                        </button>
                    </form>
                </div>

            <!-- ===================================================================== -->
            <!-- VIEW 4: LIST VIEW WITH NEXT AUDIT COUNTDOWN & 28D PERF                -->
            <!-- ===================================================================== -->
            <?php else: ?>
                <!-- 4 Executive KPI Cards -->
                <section class="seo-kpi-grid" aria-label="Ringkasan Status Audit SEO">
                    <a href="<?php echo sanitize($build_pages_url(['filter' => ''])); ?>" class="seo-kpi-card" style="--sk-accent: #1e40af; --sk-bg: #eff6ff;">
                        <div class="seo-kpi-top">
                            <span class="seo-kpi-label">Total SEO Pages</span>
                            <span class="seo-kpi-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                            </span>
                        </div>
                        <div class="seo-kpi-val"><?php echo number_format($list_kpi['total']); ?></div>
                        <div class="seo-kpi-sub">Halaman terindeks di database</div>
                    </a>

                    <a href="<?php echo sanitize($build_pages_url(['filter' => 'due'])); ?>" class="seo-kpi-card" style="--sk-accent: <?php echo $list_kpi['due'] > 0 ? '#dc2626' : '#059669'; ?>; --sk-bg: <?php echo $list_kpi['due'] > 0 ? '#fef2f2' : '#ecfdf5'; ?>;">
                        <div class="seo-kpi-top">
                            <span class="seo-kpi-label">Siap / Terlewat Audit</span>
                            <span class="seo-kpi-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            </span>
                        </div>
                        <div class="seo-kpi-val" style="<?php echo $list_kpi['due'] > 0 ? 'color:#b91c1c;' : ''; ?>"><?php echo number_format($list_kpi['due']); ?></div>
                        <div class="seo-kpi-sub">Jatuh tempo &le; 3 hari / terlewat</div>
                    </a>

                    <a href="<?php echo sanitize($build_pages_url(['filter' => 'scheduled'])); ?>" class="seo-kpi-card" style="--sk-accent: #0284c7; --sk-bg: #f0f9ff;">
                        <div class="seo-kpi-top">
                            <span class="seo-kpi-label">Dalam Observasi</span>
                            <span class="seo-kpi-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            </span>
                        </div>
                        <div class="seo-kpi-val"><?php echo number_format($list_kpi['scheduled']); ?></div>
                        <div class="seo-kpi-sub">Menunggu siklus evaluasi selesai</div>
                    </a>

                    <a href="<?php echo sanitize($build_pages_url(['filter' => 'no_gsc'])); ?>" class="seo-kpi-card" style="--sk-accent: #059669; --sk-bg: #ecfdf5;">
                        <div class="seo-kpi-top">
                            <span class="seo-kpi-label">Punya Snapshot GSC</span>
                            <span class="seo-kpi-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                            </span>
                        </div>
                        <div class="seo-kpi-val"><?php echo number_format($list_kpi['has_gsc']); ?> <span style="font-size:0.9rem;color:var(--admin-subtle);font-weight:500;">/ <?php echo number_format($list_kpi['total']); ?></span></div>
                        <div class="seo-kpi-sub">Klik untuk filter yang belum ada GSC</div>
                    </a>
                </section>

                <!-- Global GA4-Style Interactive SEO Landing Pages Overview Chart -->
                <?php if ($global_chart_payload):
                    $gc = $global_chart_payload['currTotals'];
                    $gp = $global_chart_payload['prevTotals'];
                ?>
                <section class="admin-card" aria-label="Grafik GA4 Performa SEO Landing Pages">
                    <div class="seo-card-head" style="margin-bottom: 12px;">
                        <div>
                            <h2>Tren Performa SEO Landing Pages (Konsep GA4)</h2>
                            <p class="seo-card-sub">
                                <strong><?php echo sanitize($global_chart_payload['pageLabel']); ?></strong> ·
                                Channel: <strong><?php echo sanitize($global_chart_payload['channelLabel']); ?></strong> ·
                                Periode: <span class="admin-num"><?php echo sanitize($global_chart_payload['currRange']); ?></span>
                            </p>
                        </div>
                    </div>

                    <div class="ga-filter-row">
                        <div class="admin-pill-group" role="navigation" aria-label="Rentang waktu grafik global">
                            <?php foreach (['7d' => '7 Hari', '14d' => '14 Hari', '28d' => '28 Hari', '90d' => '90 Hari', 'all' => 'Semua Data'] as $rk => $rlbl): ?>
                                <a href="<?php echo sanitize($build_pages_url(['g_range' => $rk, 'g_start' => '', 'g_end' => ''])); ?>" class="admin-pill<?php echo $g_range === $rk ? ' active' : ''; ?>"><?php echo $rlbl; ?></a>
                            <?php endforeach; ?>
                        </div>

                        <form method="GET" action="<?php echo BASE_URL; ?>admin/pages.php" class="ga-filter-form">
                            <?php if ($filter_status !== ''): ?><input type="hidden" name="filter" value="<?php echo sanitize($filter_status); ?>"><?php endif; ?>
                            <?php if ($search_q !== ''): ?><input type="hidden" name="q" value="<?php echo sanitize($search_q); ?>"><?php endif; ?>
                            <?php if ($sort_by !== 'next_audit'): ?><input type="hidden" name="sort" value="<?php echo sanitize($sort_by); ?>"><?php endif; ?>
                            <?php if ($per_page !== 10): ?><input type="hidden" name="per_page" value="<?php echo sanitize((string)$per_page); ?>"><?php endif; ?>
                            <input type="hidden" name="g_range" id="global-range-input" value="<?php echo sanitize($g_range); ?>">

                            <select name="g_page" class="seo-filter-input" style="max-width: 210px;" aria-label="Filter Halaman Landing Page">
                                <option value="0">Semua SEO Landing Pages</option>
                                <?php foreach ($raw_pages_dropdown as $dp): ?>
                                    <option value="<?php echo (int)$dp['id']; ?>" <?php echo $g_page_id === (int)$dp['id'] ? 'selected' : ''; ?>>
                                        <?php echo sanitize($dp['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <select name="g_channel" class="seo-filter-input" aria-label="Filter Channel Traffic">
                                <?php foreach ($seo_channels as $ck => $clbl): ?>
                                    <option value="<?php echo sanitize($ck); ?>" <?php echo $g_channel === $ck ? 'selected' : ''; ?>><?php echo sanitize($clbl); ?></option>
                                <?php endforeach; ?>
                            </select>

                            <input type="date" name="g_start" value="<?php echo sanitize($g_range === 'custom' ? $g_start : ''); ?>" class="seo-filter-input" aria-label="Tanggal Mulai" title="Tanggal Mulai" onchange="document.getElementById('global-range-input').value='custom'">
                            <span style="color:var(--admin-subtle); font-size:0.8rem;">–</span>
                            <input type="date" name="g_end" value="<?php echo sanitize($g_range === 'custom' ? $g_end : ''); ?>" class="seo-filter-input" aria-label="Tanggal Akhir" title="Tanggal Akhir" onchange="document.getElementById('global-range-input').value='custom'">
                            <button type="submit" class="btn-admin btn-admin-primary btn-admin-sm" style="height:35px; padding:0 12px;">Terapkan</button>
                            <?php if ($g_range !== '28d' || $g_channel !== '' || $g_page_id > 0): ?>
                                <a href="<?php echo sanitize($build_pages_url(['g_range' => '', 'g_channel' => '', 'g_page' => '', 'g_start' => '', 'g_end' => ''])); ?>" class="btn-admin btn-admin-secondary btn-admin-sm" style="height:35px; padding:0 10px;">Reset Grafik</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- 4 Clickable GA4 Scorecards -->
                    <div class="ga-scorecards" id="globalScorecards" role="tablist" aria-label="Pilih Metrik Grafik Global">
                        <button type="button" class="ga-scorecard active" data-global-metric="seo_geo_views" role="tab" aria-selected="true">
                            <div class="ga-sc-lbl">SEO + GEO Views</div>
                            <div class="ga-sc-row">
                                <span class="ga-sc-val"><?php echo number_format($gc['seo_geo_views']); ?></span>
                                <?php echo seo_render_delta_badge(seo_delta_pct($gp['seo_geo_views'], $gc['seo_geo_views'])); ?>
                            </div>
                            <div class="ga-sc-prev">Periode sblm: <?php echo number_format($gp['seo_geo_views']); ?></div>
                        </button>
                        <button type="button" class="ga-scorecard" data-global-metric="total_views" role="tab" aria-selected="false">
                            <div class="ga-sc-lbl">Total Views</div>
                            <div class="ga-sc-row">
                                <span class="ga-sc-val"><?php echo number_format($gc['total_views']); ?></span>
                                <?php echo seo_render_delta_badge(seo_delta_pct($gp['total_views'], $gc['total_views'])); ?>
                            </div>
                            <div class="ga-sc-prev">Periode sblm: <?php echo number_format($gp['total_views']); ?></div>
                        </button>
                        <button type="button" class="ga-scorecard" data-global-metric="sessions" role="tab" aria-selected="false">
                            <div class="ga-sc-lbl">Sesi Unik</div>
                            <div class="ga-sc-row">
                                <span class="ga-sc-val"><?php echo number_format($gc['sessions']); ?></span>
                                <?php echo seo_render_delta_badge(seo_delta_pct($gp['sessions'], $gc['sessions'])); ?>
                            </div>
                            <div class="ga-sc-prev">Periode sblm: <?php echo number_format($gp['sessions']); ?></div>
                        </button>
                        <button type="button" class="ga-scorecard" data-global-metric="conversions" role="tab" aria-selected="false">
                            <div class="ga-sc-lbl">Konversi</div>
                            <div class="ga-sc-row">
                                <span class="ga-sc-val"><?php echo number_format($gc['conversions']); ?></span>
                                <?php echo seo_render_delta_badge(seo_delta_pct($gp['conversions'], $gc['conversions'])); ?>
                            </div>
                            <div class="ga-sc-prev">Periode sblm: <?php echo number_format($gp['conversions']); ?></div>
                        </button>
                    </div>

                    <div class="ga-toolbar-sub">
                        <div class="ga-legend">
                            <span class="ga-legend-item">
                                <span class="ga-line-solid"></span>
                                <span>Periode Ini (<span class="admin-num"><?php echo sanitize($global_chart_payload['currRange']); ?></span>)</span>
                            </span>
                            <span class="ga-legend-item" id="globalPrevLegend">
                                <span class="ga-line-dashed"></span>
                                <span>Periode Sebelumnya (<span class="admin-num"><?php echo sanitize($global_chart_payload['prevRange']); ?></span>)</span>
                            </span>
                        </div>
                        <div class="ga-controls-right">
                            <label class="ga-compare-toggle">
                                <input type="checkbox" id="globalCompareChk" checked>
                                <span>Bandingkan Periode Sebelumnya</span>
                            </label>
                            <div class="ga-seg-group" role="group" aria-label="Tipe Grafik Global">
                                <button type="button" class="ga-seg-btn active" data-global-type="line" aria-pressed="true">Garis</button>
                                <button type="button" class="ga-seg-btn" data-global-type="bar" aria-pressed="false">Batang</button>
                            </div>
                        </div>
                    </div>

                    <div class="ga-canvas-wrap">
                        <canvas id="seoGlobalChart" role="img" aria-label="Grafik tren performa SEO Landing Pages dibanding periode sebelumnya"></canvas>
                    </div>
                    <table class="sr-only">
                        <caption>Data Harian SEO Landing Pages</caption>
                        <thead><tr><th>Tanggal</th><th>SEO+GEO Views</th><th>Total Views</th><th>Sesi</th><th>Konversi</th></tr></thead>
                        <tbody>
                            <?php foreach ($global_chart_payload['current'] as $pt): ?>
                                <tr>
                                    <td><?php echo sanitize($pt['full_label']); ?></td>
                                    <td><?php echo $pt['seo_geo_views']; ?></td>
                                    <td><?php echo $pt['total_views']; ?></td>
                                    <td><?php echo $pt['sessions']; ?></td>
                                    <td><?php echo $pt['conversions']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
                <?php endif; ?>

                <!-- Filter & Search Toolbar -->
                <div class="admin-toolbar">
                    <div class="admin-pill-group" role="navigation" aria-label="Filter status audit">
                        <a href="<?php echo sanitize($build_pages_url(['filter' => ''])); ?>" class="admin-pill<?php echo $filter_status === '' ? ' active' : ''; ?>">Semua</a>
                        <a href="<?php echo sanitize($build_pages_url(['filter' => 'due'])); ?>" class="admin-pill<?php echo $filter_status === 'due' ? ' active' : ''; ?>">Perlu Audit</a>
                        <a href="<?php echo sanitize($build_pages_url(['filter' => 'scheduled'])); ?>" class="admin-pill<?php echo $filter_status === 'scheduled' ? ' active' : ''; ?>">Observasi</a>
                        <a href="<?php echo sanitize($build_pages_url(['filter' => 'no_gsc'])); ?>" class="admin-pill<?php echo $filter_status === 'no_gsc' ? ' active' : ''; ?>">Belum Ada GSC</a>
                    </div>

                    <form method="GET" action="<?php echo BASE_URL; ?>admin/pages.php" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <?php if ($filter_status !== ''): ?><input type="hidden" name="filter" value="<?php echo sanitize($filter_status); ?>"><?php endif; ?>
                        <?php if ($per_page !== 10): ?><input type="hidden" name="per_page" value="<?php echo sanitize((string)$per_page); ?>"><?php endif; ?>
                        <?php if ($g_range !== '28d'): ?><input type="hidden" name="g_range" value="<?php echo sanitize($g_range); ?>"><?php endif; ?>
                        <?php if ($g_channel !== ''): ?><input type="hidden" name="g_channel" value="<?php echo sanitize($g_channel); ?>"><?php endif; ?>
                        <?php if ($g_page_id > 0): ?><input type="hidden" name="g_page" value="<?php echo (int)$g_page_id; ?>"><?php endif; ?>
                        <?php if ($g_range === 'custom' && $g_start !== ''): ?><input type="hidden" name="g_start" value="<?php echo sanitize($g_start); ?>"><?php endif; ?>
                        <?php if ($g_range === 'custom' && $g_end !== ''): ?><input type="hidden" name="g_end" value="<?php echo sanitize($g_end); ?>"><?php endif; ?>
                        <input type="search" name="q" value="<?php echo sanitize($search_q); ?>" class="seo-filter-input" style="width: 210px;" placeholder="Cari halaman atau slug..." aria-label="Cari halaman SEO">
                        <select name="sort" class="seo-filter-input" aria-label="Urutkan halaman" onchange="this.form.submit()">
                            <option value="next_audit" <?php echo $sort_by === 'next_audit' ? 'selected' : ''; ?>>Next Audit Terdekat</option>
                            <option value="updated_desc" <?php echo $sort_by === 'updated_desc' ? 'selected' : ''; ?>>Update Terbaru</option>
                            <option value="views_desc" <?php echo $sort_by === 'views_desc' ? 'selected' : ''; ?>>SEO/GEO Views Tertinggi</option>
                            <option value="title_asc" <?php echo $sort_by === 'title_asc' ? 'selected' : ''; ?>>Nama A–Z</option>
                        </select>
                        <button type="submit" class="btn-admin btn-admin-primary btn-admin-sm" style="height:35px; padding:0 12px;">Cari</button>
                        <?php if ($search_q !== '' || $filter_status !== '' || $sort_by !== 'next_audit'): ?>
                            <a href="<?php echo sanitize($build_pages_url(['filter' => '', 'q' => '', 'sort' => ''])); ?>" class="btn-admin btn-admin-secondary btn-admin-sm" style="height:35px; padding:0 10px;">Reset</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Pages Table -->
                <div class="admin-card" id="seo-pages-table">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Halaman &amp; Kesehatan Meta</th>
                                    <th>Last Updated</th>
                                    <th>Next Audit</th>
                                    <th>Performa 28h &amp; GSC</th>
                                    <th style="text-align:right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($visible_pages)): foreach ($visible_pages as $page):
                                    $mt_len = mb_strlen((string)($page['meta_title'] ?? ''), 'UTF-8');
                                    $md_len = mb_strlen((string)($page['meta_description'] ?? ''), 'UTF-8');
                                    $mt_cls = ($mt_len >= 40 && $mt_len <= 65) ? 'seo-chip-ok' : (($mt_len === 0 || $mt_len > 70) ? 'seo-chip-bad' : 'seo-chip-warn');
                                    $md_cls = ($md_len >= 110 && $md_len <= 165) ? 'seo-chip-ok' : (($md_len === 0 || $md_len > 175) ? 'seo-chip-bad' : 'seo-chip-warn');
                                    $sc = $page['_sched'];
                                    $fp = $page['_fp'];
                                    $slug_lbl = $page['slug'] !== '' ? ('/' . $page['slug'] . '/') : '/';
                                    $view_url = BASE_URL . ($page['slug'] !== '' ? ($page['slug'] . '/') : '');
                                ?>
                                    <tr>
                                        <td>
                                            <strong style="color:var(--admin-ink);"><?php echo sanitize($page['title']); ?></strong>
                                            <div style="margin-top:3px;">
                                                <span class="admin-code-pill seo-slug-pill" title="<?php echo sanitize($slug_lbl); ?>"><?php echo sanitize($slug_lbl); ?></span>
                                            </div>
                                            <div class="seo-health-chips">
                                                <span class="seo-chip <?php echo $mt_cls; ?>" title="Panjang Meta Title (ideal 50–60)">Title: <?php echo $mt_len; ?>c</span>
                                                <span class="seo-chip <?php echo $md_cls; ?>" title="Panjang Meta Description (ideal 120–160)">Desc: <?php echo $md_len; ?>c</span>
                                                <?php if ((int)$page['audit_count'] > 0): ?>
                                                    <span class="seo-chip" style="background:#f1f5f9;color:#475569;border-color:#cbd5e1;"><?php echo (int)$page['audit_count']; ?> log</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <div class="admin-num" style="font-size: 0.8rem; color: var(--admin-ink);">
                                                <?php echo date('d M Y', strtotime($page['updated_at'])); ?>
                                            </div>
                                            <div style="font-size: 0.72rem; color: var(--admin-subtle); margin-top: 2px;">
                                                <?php echo (int)$sc['days_since_update']; ?> hari lalu · siklus <?php echo (int)$sc['interval_days']; ?>h
                                            </div>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <span class="badge <?php echo $sc['badge']; ?>"><?php echo sanitize($sc['label']); ?></span>
                                            <div class="admin-num" style="font-size: 0.73rem; color: var(--admin-subtle); margin-top: 4px;">
                                                Target: <?php echo sanitize($sc['next_date']); ?>
                                            </div>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <div class="admin-num" style="font-size: 0.8rem; color: var(--admin-ink);">
                                                <strong><?php echo number_format($fp['seo_geo_views']); ?></strong> SEO/GEO · <strong><?php echo number_format($fp['conversions']); ?></strong> conv
                                            </div>
                                            <?php if ($page['_has_gsc']): ?>
                                                <div class="admin-num" style="font-size: 0.72rem; color: var(--admin-primary); margin-top: 3px;">
                                                    GSC: <?php echo $page['latest_impr'] !== null ? number_format((int)$page['latest_impr']) : '—'; ?> impr ·
                                                    <?php echo $page['latest_ctr'] !== null ? number_format((float)$page['latest_ctr'], 1) . '%' : '—'; ?> ·
                                                    #<?php echo $page['latest_pos'] !== null ? number_format((float)$page['latest_pos'], 1) : '—'; ?>
                                                </div>
                                            <?php else: ?>
                                                <div style="font-size: 0.72rem; color: var(--admin-subtle); margin-top: 3px;">GSC: belum dicatat</div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="white-space: nowrap; text-align: right;">
                                            <div style="display:inline-flex; gap:6px; align-items:center;">
                                                <a href="?action=audit&amp;id=<?php echo (int)$page['id']; ?>" class="btn-admin btn-admin-primary btn-admin-sm" title="Audit &amp; Bandingkan Performa SEO">Audit</a>
                                                <a href="?action=edit&amp;id=<?php echo (int)$page['id']; ?>" class="btn-admin btn-admin-secondary btn-admin-sm">Edit</a>
                                                <a href="<?php echo sanitize($view_url); ?>" target="_blank" rel="noopener" class="btn-admin btn-admin-secondary btn-admin-sm" title="Buka halaman di tab baru">View</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="5" class="admin-empty">
                                            <?php echo ($search_q !== '' || $filter_status !== '') ? 'Tidak ada halaman yang cocok dengan filter tersebut.' : 'Belum ada halaman terdaftar di database.'; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_filtered > 0): ?>
                        <div class="seo-table-footer">
                            <div class="seo-page-info">
                                <span>
                                    Menampilkan <strong class="admin-num"><?php echo $range_from; ?>–<?php echo $range_to; ?></strong>
                                    dari <strong class="admin-num"><?php echo $total_filtered; ?></strong> halaman
                                </span>
                                <div class="seo-perpage-group" role="group" aria-label="Jumlah baris per halaman">
                                    <?php foreach ([10 => '10', 20 => '20', 50 => '50', 'all' => 'Semua'] as $pp_val => $pp_lbl): ?>
                                        <a href="<?php echo sanitize($build_pages_url(['per_page' => (string)$pp_val])); ?>#seo-pages-table"
                                           class="seo-perpage-btn<?php echo $per_page === $pp_val ? ' active' : ''; ?>"><?php echo $pp_lbl; ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <?php if ($total_pages > 1): ?>
                                <nav class="admin-pagination" style="margin-top:0;" aria-label="Navigasi halaman SEO">
                                    <?php if ($curr_page > 1): ?>
                                        <a href="<?php echo sanitize($build_pages_url(['page' => $curr_page - 1])); ?>#seo-pages-table" class="btn-admin btn-admin-secondary btn-admin-sm">&larr; Prev</a>
                                    <?php endif; ?>
                                    <?php
                                    $win_start = max(1, $curr_page - 2);
                                    $win_end   = min($total_pages, $curr_page + 2);
                                    if ($win_start > 1): ?>
                                        <a href="<?php echo sanitize($build_pages_url(['page' => 1])); ?>#seo-pages-table" class="btn-admin btn-admin-secondary btn-admin-sm">1</a>
                                        <?php if ($win_start > 2): ?><span style="padding:4px 4px; color:var(--admin-subtle); font-size:0.8rem;">&hellip;</span><?php endif; ?>
                                    <?php endif;
                                    for ($p = $win_start; $p <= $win_end; $p++): ?>
                                        <a href="<?php echo sanitize($build_pages_url(['page' => $p])); ?>#seo-pages-table"
                                           class="btn-admin <?php echo $p === $curr_page ? 'btn-admin-primary' : 'btn-admin-secondary'; ?> btn-admin-sm"
                                           <?php echo $p === $curr_page ? 'aria-current="page"' : ''; ?>><?php echo $p; ?></a>
                                    <?php endfor;
                                    if ($win_end < $total_pages): ?>
                                        <?php if ($win_end < $total_pages - 1): ?><span style="padding:4px 4px; color:var(--admin-subtle); font-size:0.8rem;">&hellip;</span><?php endif; ?>
                                        <a href="<?php echo sanitize($build_pages_url(['page' => $total_pages])); ?>#seo-pages-table" class="btn-admin btn-admin-secondary btn-admin-sm"><?php echo $total_pages; ?></a>
                                    <?php endif; ?>
                                    <?php if ($curr_page < $total_pages): ?>
                                        <a href="<?php echo sanitize($build_pages_url(['page' => $curr_page + 1])); ?>#seo-pages-table" class="btn-admin btn-admin-secondary btn-admin-sm">Next &rarr;</a>
                                    <?php endif; ?>
                                </nav>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <?php if ($action === 'new' || $action === 'edit'): ?>
    <script>
    (function () {
        var mtInput = document.getElementById('seo-meta-title');
        var mdInput = document.getElementById('seo-meta-desc');
        var mtCount = document.getElementById('seo-mt-count');
        var mdCount = document.getElementById('seo-md-count');
        var serpTitle = document.getElementById('seo-serp-title');
        var serpDesc  = document.getElementById('seo-serp-desc');

        function updateCounter(input, el, minSafe, maxSafe) {
            if (!input || !el) return;
            var len = input.value.length;
            el.textContent = len;
            el.className = 'char-counter-num';
            if (len === 0) el.classList.add('safe');
            else if (len < minSafe) el.classList.add('warn');
            else if (len <= maxSafe) el.classList.add('safe');
            else el.classList.add('danger');
        }

        function syncSerp() {
            if (mtInput && serpTitle) {
                var t = mtInput.value.trim();
                serpTitle.textContent = t.length > 65 ? (t.substring(0, 62) + '...') : (t || 'Judul Halaman - Urban Office');
            }
            if (mdInput && serpDesc) {
                var d = mdInput.value.trim();
                serpDesc.textContent = d.length > 165 ? (d.substring(0, 162) + '...') : (d || 'Meta description halaman akan tampil di sini...');
            }
        }

        if (mtInput) {
            mtInput.addEventListener('input', function () {
                updateCounter(mtInput, mtCount, 45, 60);
                syncSerp();
            });
            updateCounter(mtInput, mtCount, 45, 60);
        }
        if (mdInput) {
            mdInput.addEventListener('input', function () {
                updateCounter(mdInput, mdCount, 120, 160);
                syncSerp();
            });
            updateCounter(mdInput, mdCount, 120, 160);
        }
        syncSerp();
    })();
    </script>
    <?php endif; ?>
    <?php if (($action === 'list' && $global_chart_payload) || ($action === 'audit' && $audit_chart_payload)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        if (typeof Chart === 'undefined') return;
        var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var metricNames = {
            seo_geo_views: 'SEO + GEO Views',
            total_views: 'Total Views',
            sessions: 'Sesi Unik',
            conversions: 'Konversi'
        };

        function makeAreaGrad(ctx, area, colorRgb) {
            if (!area) return 'rgba(' + colorRgb + ', 0.14)';
            var g = ctx.createLinearGradient(0, area.top, 0, area.bottom);
            g.addColorStop(0, 'rgba(' + colorRgb + ', 0.24)');
            g.addColorStop(1, 'rgba(' + colorRgb + ', 0.01)');
            return g;
        }

        // Custom lightweight Chart.js plugin: draws vertical SEO Update pivot line in Timeline mode
        var seoPivotPlugin = {
            id: 'seoPivotLine',
            afterDatasetsDraw: function (chart) {
                var opts = chart.options.plugins && chart.options.plugins.seoPivotLine;
                if (!opts || opts.index === undefined || opts.index < 0) return;
                var meta = chart.getDatasetMeta(0);
                if (!meta || !meta.data || !meta.data[opts.index]) return;
                var x = meta.data[opts.index].x;
                var area = chart.chartArea;
                var ctx = chart.ctx;
                ctx.save();
                ctx.beginPath();
                ctx.setLineDash([5, 4]);
                ctx.lineWidth = 1.8;
                ctx.strokeStyle = '#ea580c';
                ctx.moveTo(x, area.top);
                ctx.lineTo(x, area.bottom);
                ctx.stroke();
                ctx.setLineDash([]);
                if (opts.label) {
                    ctx.font = "600 11px 'Fira Sans', sans-serif";
                    var tw = ctx.measureText(opts.label).width;
                    var bx = Math.min(Math.max(x - tw / 2 - 6, area.left + 4), area.right - tw - 16);
                    var by = area.top + 6;
                    ctx.fillStyle = '#fff7ed';
                    ctx.strokeStyle = '#fdba74';
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.roundRect ? ctx.roundRect(bx, by, tw + 12, 20, 5) : ctx.rect(bx, by, tw + 12, 20);
                    ctx.fill();
                    ctx.stroke();
                    ctx.fillStyle = '#c2410c';
                    ctx.fillText(opts.label, bx + 6, by + 14);
                }
                ctx.restore();
            }
        };

        <?php if ($action === 'list' && $global_chart_payload): ?>
        (function initGlobalChart() {
            var canvas = document.getElementById('seoGlobalChart');
            if (!canvas) return;
            var payload = <?php echo json_encode($global_chart_payload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            var metric = 'seo_geo_views';
            var chartType = 'line';
            var showCompare = true;
            var inst = null;

            function render() {
                if (inst) inst.destroy();
                var curr = payload.current || [];
                var prev = payload.previous || [];
                var labels = curr.map(function (d) { return d.label; });
                var currVals = curr.map(function (d) { return d[metric] || 0; });
                var prevVals = curr.map(function (_d, i) { return prev[i] ? (prev[i][metric] || 0) : 0; });
                var isLine = chartType === 'line';
                var mLabel = metricNames[metric] || metric;

                var datasets = [{
                    label: mLabel + ' (Periode Ini)',
                    data: currVals,
                    borderColor: '#1e40af',
                    backgroundColor: isLine ? function (c) { return makeAreaGrad(c.chart.ctx, c.chart.chartArea, '37, 99, 235'); } : '#2563eb',
                    borderWidth: isLine ? 2.5 : 0,
                    borderRadius: isLine ? 0 : { topLeft: 5, topRight: 5 },
                    maxBarThickness: 26,
                    fill: isLine,
                    tension: 0.32,
                    pointRadius: curr.length > 35 ? 0 : 3,
                    pointHoverRadius: 5.5,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#1e40af',
                    pointBorderWidth: 2
                }];

                if (showCompare && prev.length > 0) {
                    datasets.push({
                        label: mLabel + ' (Periode Sblm)',
                        data: prevVals,
                        borderColor: '#64748b',
                        backgroundColor: isLine ? 'transparent' : 'rgba(148, 163, 184, 0.45)',
                        borderWidth: isLine ? 2 : 0,
                        borderDash: isLine ? [5, 4] : [],
                        borderRadius: isLine ? 0 : { topLeft: 5, topRight: 5 },
                        maxBarThickness: 26,
                        fill: false,
                        tension: 0.32,
                        pointRadius: curr.length > 35 ? 0 : 2.5,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#64748b',
                        pointBorderWidth: 1.5
                    });
                }

                inst = new Chart(canvas.getContext('2d'), {
                    type: chartType,
                    data: { labels: labels, datasets: datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: prefersReduced ? false : { duration: 360, easing: 'easeOutQuart' },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleColor: '#f8fafc',
                                bodyColor: '#e2e8f0',
                                padding: 11,
                                cornerRadius: 8,
                                titleFont: { family: "'Fira Sans', sans-serif", size: 12, weight: '600' },
                                bodyFont: { family: "'Fira Code', monospace", size: 11.5 },
                                callbacks: {
                                    title: function (items) {
                                        var idx = items[0] ? items[0].dataIndex : 0;
                                        return curr[idx] ? curr[idx].full_label : '';
                                    },
                                    label: function (ctx) {
                                        var idx = ctx.dataIndex;
                                        if (ctx.datasetIndex === 0) {
                                            return ' Periode Ini: ' + ctx.parsed.y.toLocaleString('id-ID');
                                        }
                                        var pDate = prev[idx] ? prev[idx].label : '-';
                                        return ' Sblm (' + pDate + '): ' + ctx.parsed.y.toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { color: '#e2e8f0' },
                                ticks: { color: '#64748b', maxRotation: 0, autoSkip: true, maxTicksLimit: 12, font: { family: "'Fira Sans', sans-serif", size: 11 } }
                            },
                            y: {
                                beginAtZero: true,
                                suggestedMax: 5,
                                border: { display: false },
                                grid: { color: '#f1f5f9', drawTicks: false },
                                ticks: { color: '#64748b', precision: 0, padding: 8, font: { family: "'Fira Code', monospace", size: 11 } }
                            }
                        }
                    }
                });
            }

            document.querySelectorAll('[data-global-metric]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    metric = btn.getAttribute('data-global-metric');
                    document.querySelectorAll('[data-global-metric]').forEach(function (b) {
                        var act = b === btn;
                        b.classList.toggle('active', act);
                        b.setAttribute('aria-selected', act ? 'true' : 'false');
                    });
                    render();
                });
            });

            document.querySelectorAll('[data-global-type]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    chartType = btn.getAttribute('data-global-type');
                    document.querySelectorAll('[data-global-type]').forEach(function (b) {
                        var act = b === btn;
                        b.classList.toggle('active', act);
                        b.setAttribute('aria-pressed', act ? 'true' : 'false');
                    });
                    render();
                });
            });

            var cmpChk = document.getElementById('globalCompareChk');
            var prevLeg = document.getElementById('globalPrevLegend');
            if (cmpChk) {
                cmpChk.addEventListener('change', function () {
                    showCompare = cmpChk.checked;
                    if (prevLeg) prevLeg.style.display = showCompare ? '' : 'none';
                    render();
                });
            }

            render();
        })();
        <?php endif; ?>

        <?php if ($action === 'audit' && $audit_chart_payload): ?>
        (function initAuditChart() {
            var canvas = document.getElementById('seoAuditChart');
            if (!canvas) return;
            var payload = <?php echo json_encode($audit_chart_payload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            var metric = 'seo_geo_views';
            var mode = payload.defaultMode || 'overlay'; // overlay | timeline | gsc_impr | gsc_rank
            var showCompare = true;
            var inst = null;

            var chartWrap = document.getElementById('auditChartWrap');
            var gscEmpty  = document.getElementById('auditGscEmpty');
            var cmpWrap   = document.getElementById('auditCompareWrap');
            var scoreWrap = document.getElementById('auditScorecards');
            var legendEl  = document.getElementById('auditChartLegend');

            function render() {
                if (inst) { inst.destroy(); inst = null; }
                var isGsc = (mode === 'gsc_impr' || mode === 'gsc_rank');
                if (cmpWrap) cmpWrap.style.display = (mode === 'overlay') ? '' : 'none';
                if (scoreWrap) scoreWrap.style.opacity = isGsc ? '0.55' : '1';

                if (isGsc) {
                    var gsc = payload.gsc || [];
                    if (gsc.length < 2) {
                        if (chartWrap) chartWrap.style.display = 'none';
                        if (gscEmpty) gscEmpty.style.display = 'block';
                        if (legendEl) legendEl.innerHTML = '';
                        return;
                    }
                    if (chartWrap) chartWrap.style.display = 'block';
                    if (gscEmpty) gscEmpty.style.display = 'none';

                    var gLabels = gsc.map(function (d) { return d.label; });
                    var datasets, scales;
                    if (mode === 'gsc_impr') {
                        if (legendEl) legendEl.innerHTML = '<span class="ga-legend-item"><span class="ga-line-solid" style="background:#2563eb;height:10px;width:10px;"></span>Impressions</span><span class="ga-legend-item"><span class="ga-line-solid" style="background:#059669;"></span>Clicks</span>';
                        datasets = [
                            { type: 'bar', label: 'Impressions', data: gsc.map(function (d) { return d.impressions; }), yAxisID: 'yLeft', backgroundColor: 'rgba(37, 99, 235, 0.25)', borderColor: '#2563eb', borderWidth: 1.5, borderRadius: 5, maxBarThickness: 38 },
                            { type: 'line', label: 'Clicks', data: gsc.map(function (d) { return d.clicks; }), yAxisID: 'yRight', borderColor: '#059669', backgroundColor: '#059669', borderWidth: 2.5, tension: 0.3, pointRadius: 4.5, pointBackgroundColor: '#fff', pointBorderWidth: 2 }
                        ];
                        scales = {
                            x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 11 } } },
                            yLeft: { type: 'linear', position: 'left', beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#1d4ed8', precision: 0, font: { family: "'Fira Code', monospace", size: 11 } } },
                            yRight: { type: 'linear', position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, ticks: { color: '#047857', precision: 0, font: { family: "'Fira Code', monospace", size: 11 } } }
                        };
                    } else {
                        if (legendEl) legendEl.innerHTML = '<span class="ga-legend-item"><span class="ga-line-solid" style="background:#7c3aed;"></span>CTR (%)</span><span class="ga-legend-item"><span class="ga-line-solid" style="background:#ea580c;"></span>Avg Position (atas = lebih baik)</span>';
                        datasets = [
                            { type: 'line', label: 'CTR (%)', data: gsc.map(function (d) { return d.ctr; }), yAxisID: 'yLeft', borderColor: '#7c3aed', backgroundColor: 'rgba(124, 58, 237, 0.12)', fill: true, borderWidth: 2.5, tension: 0.3, pointRadius: 4.5, pointBackgroundColor: '#fff', pointBorderWidth: 2 },
                            { type: 'line', label: 'Avg Position', data: gsc.map(function (d) { return d.position; }), yAxisID: 'yRight', borderColor: '#ea580c', borderDash: [5, 3], borderWidth: 2.5, tension: 0.3, pointRadius: 4.5, pointBackgroundColor: '#fff', pointBorderWidth: 2 }
                        ];
                        scales = {
                            x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 11 } } },
                            yLeft: { type: 'linear', position: 'left', beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#6d28d9', font: { family: "'Fira Code', monospace", size: 11 } } },
                            yRight: { type: 'linear', position: 'right', reverse: true, grid: { drawOnChartArea: false }, ticks: { color: '#c2410c', font: { family: "'Fira Code', monospace", size: 11 } } }
                        };
                    }

                    inst = new Chart(canvas.getContext('2d'), {
                        data: { labels: gLabels, datasets: datasets },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: prefersReduced ? false : { duration: 360 },
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { display: false },
                                tooltip: { backgroundColor: '#0f172a', padding: 11, cornerRadius: 8, bodyFont: { family: "'Fira Code', monospace", size: 11.5 } }
                            },
                            scales: scales
                        }
                    });
                    return;
                }

                if (chartWrap) chartWrap.style.display = 'block';
                if (gscEmpty) gscEmpty.style.display = 'none';

                var beforeArr = payload.before || [];
                var afterArr  = payload.after || [];
                var mLabel = metricNames[metric] || metric;

                if (mode === 'overlay') {
                    if (legendEl) {
                        legendEl.innerHTML =
                            '<span class="ga-legend-item"><span class="ga-line-solid"></span><span>Sesudah (' + payload.afterRange + ')</span></span>' +
                            (showCompare ? '<span class="ga-legend-item"><span class="ga-line-dashed"></span><span>Sebelum (' + payload.beforeRange + ')</span></span>' : '');
                    }
                    var len = Math.max(afterArr.length, beforeArr.length);
                    var oLabels = [];
                    var aVals = [];
                    var bVals = [];
                    for (var i = 0; i < len; i++) {
                        oLabels.push('H' + (i + 1) + (afterArr[i] ? ' (' + afterArr[i].label + ')' : ''));
                        aVals.push(afterArr[i] ? (afterArr[i][metric] || 0) : null);
                        bVals.push(beforeArr[i] ? (beforeArr[i][metric] || 0) : null);
                    }
                    var oDatasets = [{
                        label: mLabel + ' (Sesudah)',
                        data: aVals,
                        borderColor: '#1e40af',
                        backgroundColor: function (c) { return makeAreaGrad(c.chart.ctx, c.chart.chartArea, '37, 99, 235'); },
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.32,
                        pointRadius: len > 31 ? 0 : 3.5,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#1e40af',
                        pointBorderWidth: 2
                    }];
                    if (showCompare) {
                        oDatasets.push({
                            label: mLabel + ' (Sebelum)',
                            data: bVals,
                            borderColor: '#64748b',
                            borderDash: [5, 4],
                            borderWidth: 2,
                            fill: false,
                            tension: 0.32,
                            pointRadius: len > 31 ? 0 : 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#64748b',
                            pointBorderWidth: 1.5
                        });
                    }
                    inst = new Chart(canvas.getContext('2d'), {
                        type: 'line',
                        data: { labels: oLabels, datasets: oDatasets },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: prefersReduced ? false : { duration: 360 },
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    padding: 11,
                                    cornerRadius: 8,
                                    titleFont: { family: "'Fira Sans', sans-serif", size: 12, weight: '600' },
                                    bodyFont: { family: "'Fira Code', monospace", size: 11.5 },
                                    callbacks: {
                                        title: function (items) {
                                            var idx = items[0] ? items[0].dataIndex : 0;
                                            return 'Hari ke-' + (idx + 1) + ' perbandingan';
                                        },
                                        label: function (ctx) {
                                            var idx = ctx.dataIndex;
                                            if (ctx.datasetIndex === 0) {
                                                var dA = afterArr[idx] ? afterArr[idx].label : '-';
                                                return ' Sesudah (' + dA + '): ' + (ctx.parsed.y ?? 0).toLocaleString('id-ID');
                                            }
                                            var dB = beforeArr[idx] ? beforeArr[idx].label : '-';
                                            return ' Sebelum (' + dB + '): ' + (ctx.parsed.y ?? 0).toLocaleString('id-ID');
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: { grid: { display: false }, border: { color: '#e2e8f0' }, ticks: { color: '#64748b', maxRotation: 0, autoSkip: true, maxTicksLimit: 10, font: { size: 11 } } },
                                y: { beginAtZero: true, suggestedMax: 5, border: { display: false }, grid: { color: '#f1f5f9', drawTicks: false }, ticks: { color: '#64748b', precision: 0, padding: 8, font: { family: "'Fira Code', monospace", size: 11 } } }
                            }
                        }
                    });
                } else {
                    // Continuous Timeline with vertical SEO Update marker (only when pivotDate lies inside visible range)
                    var combined = beforeArr.concat(afterArr);
                    var pivotIdx = -1;
                    for (var j = 0; j < combined.length; j++) {
                        if (combined[j].date === payload.pivotDate) {
                            pivotIdx = j;
                            break;
                        }
                    }
                    if (legendEl) {
                        legendEl.innerHTML =
                            '<span class="ga-legend-item"><span class="ga-line-solid"></span><span>' + mLabel + ' Harian</span></span>' +
                            (pivotIdx >= 0 ? '<span class="ga-legend-item"><span class="ga-line-dashed" style="border-color:#ea580c;"></span><span>Garis Penanda ' + payload.pivotLabel + '</span></span>' : '');
                    }
                    inst = new Chart(canvas.getContext('2d'), {
                        type: 'line',
                        plugins: [seoPivotPlugin],
                        data: {
                            labels: combined.map(function (d) { return d.label; }),
                            datasets: [{
                                label: mLabel,
                                data: combined.map(function (d) { return d[metric] || 0; }),
                                borderColor: '#1e40af',
                                backgroundColor: function (c) { return makeAreaGrad(c.chart.ctx, c.chart.chartArea, '37, 99, 235'); },
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.3,
                                pointRadius: combined.length > 40 ? 0 : 3,
                                pointHoverRadius: 5.5,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#1e40af',
                                pointBorderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: prefersReduced ? false : { duration: 360 },
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { display: false },
                                seoPivotLine: { index: pivotIdx, label: payload.pivotLabel },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    padding: 11,
                                    cornerRadius: 8,
                                    titleFont: { family: "'Fira Sans', sans-serif", size: 12, weight: '600' },
                                    bodyFont: { family: "'Fira Code', monospace", size: 11.5 },
                                    callbacks: {
                                        title: function (items) {
                                            var idx = items[0] ? items[0].dataIndex : 0;
                                            return combined[idx] ? combined[idx].full_label : '';
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: { grid: { display: false }, border: { color: '#e2e8f0' }, ticks: { color: '#64748b', maxRotation: 0, autoSkip: true, maxTicksLimit: 12, font: { size: 11 } } },
                                y: { beginAtZero: true, suggestedMax: 5, border: { display: false }, grid: { color: '#f1f5f9', drawTicks: false }, ticks: { color: '#64748b', precision: 0, padding: 8, font: { family: "'Fira Code', monospace", size: 11 } } }
                            }
                        }
                    });
                }
            }

            document.querySelectorAll('[data-audit-metric]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    metric = btn.getAttribute('data-audit-metric');
                    document.querySelectorAll('[data-audit-metric]').forEach(function (b) {
                        var act = b === btn;
                        b.classList.toggle('active', act);
                        b.setAttribute('aria-selected', act ? 'true' : 'false');
                    });
                    if (mode === 'gsc_impr' || mode === 'gsc_rank') {
                        mode = 'overlay';
                        document.querySelectorAll('[data-audit-mode]').forEach(function (b) {
                            var act = b.getAttribute('data-audit-mode') === 'overlay';
                            b.classList.toggle('active', act);
                            b.setAttribute('aria-pressed', act ? 'true' : 'false');
                        });
                    }
                    render();
                });
            });

            document.querySelectorAll('[data-audit-mode]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    mode = btn.getAttribute('data-audit-mode');
                    document.querySelectorAll('[data-audit-mode]').forEach(function (b) {
                        var act = b === btn;
                        b.classList.toggle('active', act);
                        b.setAttribute('aria-pressed', act ? 'true' : 'false');
                    });
                    render();
                });
            });

            var cmpChk = document.getElementById('auditCompareChk');
            if (cmpChk) {
                cmpChk.addEventListener('change', function () {
                    showCompare = cmpChk.checked;
                    render();
                });
            }

            render();
        })();
        <?php endif; ?>
    })();
    </script>
    <?php endif; ?>
</body>
</html>
