<?php
/**
 * Admin First-Party Traffic & Events Analytics Dashboard
 * Displays channel attribution (SEO, Google Ads, Meta Ads, GEO/AI, Social, Referral, Direct),
 * interactive daily/hourly trend chart, top pages, homepage search insights, conversion breakdown, and CSV export.
 * Designed following UI/UX Pro Max Data-Dense Dashboard guidelines.
 */

require_once __DIR__ . '/auth.php';

// Channel display metadata (accessible contrast >= 4.5:1 + semantic dot/bar colors)
$channel_meta = [
    'seo_organic'    => ['label' => 'SEO Organic',     'bg' => '#ecfdf5', 'color' => '#047857', 'border' => '#a7f3d0', 'bar' => '#059669'],
    'google_ads'     => ['label' => 'Google Ads',      'bg' => '#eff6ff', 'color' => '#1d4ed8', 'border' => '#bfdbfe', 'bar' => '#2563eb'],
    'meta_ads'       => ['label' => 'Meta Ads',        'bg' => '#f5f3ff', 'color' => '#6d28d9', 'border' => '#ddd6fe', 'bar' => '#7c3aed'],
    'geo_ai'         => ['label' => 'GEO / AI Search', 'bg' => '#fff7ed', 'color' => '#c2410c', 'border' => '#fed7aa', 'bar' => '#ea580c'],
    'paid_ads'       => ['label' => 'Paid Ads Lain',   'bg' => '#fdf2f8', 'color' => '#be185d', 'border' => '#fbcfe8', 'bar' => '#db2777'],
    'social_organic' => ['label' => 'Social Organic',  'bg' => '#f0f9ff', 'color' => '#0369a1', 'border' => '#bae6fd', 'bar' => '#0284c7'],
    'referral'       => ['label' => 'Referral',        'bg' => '#fefce8', 'color' => '#a16207', 'border' => '#fde68a', 'bar' => '#ca8a04'],
    'direct'         => ['label' => 'Direct',          'bg' => '#f1f5f9', 'color' => '#334155', 'border' => '#cbd5e1', 'bar' => '#64748b'],
];

$event_labels = [
    'page_view'           => ['label' => 'Page View',      'bg' => '#f1f5f9', 'color' => '#334155', 'dot' => '#64748b'],
    'booking_cta_click'   => ['label' => 'Booking Online', 'bg' => '#fff7ed', 'color' => '#c2410c', 'dot' => '#ea580c'],
    'webapp_portal_click' => ['label' => 'Portal App',     'bg' => '#f0f9ff', 'color' => '#0369a1', 'dot' => '#0284c7'],
    'whatsapp_click'      => ['label' => 'WhatsApp Click', 'bg' => '#ecfdf5', 'color' => '#047857', 'dot' => '#059669'],
    'lead_form_submit'    => ['label' => 'Lead Form',      'bg' => '#eff6ff', 'color' => '#1d4ed8', 'dot' => '#2563eb'],
    'homepage_search'     => ['label' => 'Search Bar',     'bg' => '#f5f3ff', 'color' => '#6d28d9', 'dot' => '#7c3aed'],
];

// Filter parameters
$range = isset($_GET['range']) ? trim((string) $_GET['range']) : '30d';
$channel_filter = isset($_GET['channel']) ? trim((string) $_GET['channel']) : '';
$custom_start = isset($_GET['start']) ? trim((string) $_GET['start']) : '';
$custom_end   = isset($_GET['end']) ? trim((string) $_GET['end']) : '';

if ($channel_filter !== '' && !isset($channel_meta[$channel_filter])) {
    $channel_filter = '';
}

$where_clauses = [];
$params = [];
$period_label = '30 Hari Terakhir';

if ($range === 'custom' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $custom_start) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $custom_end)) {
    if (strtotime($custom_start) > strtotime($custom_end)) {
        [$custom_start, $custom_end] = [$custom_end, $custom_start];
    }
    $where_clauses[] = 'created_at >= ? AND created_at <= ?';
    $params[] = $custom_start . ' 00:00:00';
    $params[] = $custom_end . ' 23:59:59';
    $period_label = date('d M Y', strtotime($custom_start)) . ' – ' . date('d M Y', strtotime($custom_end));
} elseif ($range === 'today') {
    $where_clauses[] = 'created_at >= ?';
    $params[] = date('Y-m-d 00:00:00');
    $period_label = 'Hari Ini (' . date('d M Y') . ')';
} elseif ($range === '7d') {
    $where_clauses[] = 'created_at >= ?';
    $params[] = date('Y-m-d 00:00:00', strtotime('-6 days'));
    $period_label = '7 Hari Terakhir';
} elseif ($range === '90d') {
    $where_clauses[] = 'created_at >= ?';
    $params[] = date('Y-m-d 00:00:00', strtotime('-89 days'));
    $period_label = '90 Hari Terakhir';
} elseif ($range === 'all') {
    $period_label = 'Semua Waktu';
} else {
    $range = '30d';
    $where_clauses[] = 'created_at >= ?';
    $params[] = date('Y-m-d 00:00:00', strtotime('-29 days'));
    $period_label = '30 Hari Terakhir';
}

// Separate WHERE without channel filter so the channel comparison table always shows full breakdown
$where_date_sql = !empty($where_clauses) ? ('WHERE ' . implode(' AND ', $where_clauses)) : '';
$date_params = $params;

if ($channel_filter !== '') {
    $where_clauses[] = 'traffic_channel = ?';
    $params[] = $channel_filter;
}
$where_sql = !empty($where_clauses) ? ('WHERE ' . implode(' AND ', $where_clauses)) : '';

// Export CSV action
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    try {
        $rows = Database::fetchAll(
            "SELECT created_at, event_name, traffic_channel, traffic_source, traffic_medium, traffic_campaign, page_path, landing_page, service_name, branch_slug, wa_channel, event_detail, device_type
             FROM analytics_events {$where_sql}
             ORDER BY created_at DESC LIMIT 50000",
            $params
        );
        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=traffic_events_' . date('Ymd_His') . '.csv');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, ['Waktu', 'Event', 'Channel', 'Source', 'Medium', 'Campaign', 'Halaman', 'Landing Page', 'Layanan', 'Cabang', 'WA Channel', 'Detail', 'Device']);
        foreach ($rows as $r) {
            fputcsv($out, [
                admin_csv_safe($r['created_at']),
                admin_csv_safe($r['event_name']),
                admin_csv_safe($r['traffic_channel']),
                admin_csv_safe($r['traffic_source']),
                admin_csv_safe($r['traffic_medium']),
                admin_csv_safe($r['traffic_campaign']),
                admin_csv_safe($r['page_path']),
                admin_csv_safe($r['landing_page']),
                admin_csv_safe($r['service_name']),
                admin_csv_safe($r['branch_slug']),
                admin_csv_safe($r['wa_channel']),
                admin_csv_safe($r['event_detail']),
                admin_csv_safe($r['device_type']),
            ]);
        }
        fclose($out);
        exit;
    } catch (Exception $e) {
        $db_error = admin_friendly_error('Ekspor CSV gagal.', $e);
    }
}

$db_error = $db_error ?? '';
$kpi = [
    'page_views'      => 0,
    'unique_sessions' => 0,
    'booking_clicks'  => 0,
    'wa_sales'        => 0,
    'wa_cs'           => 0,
    'lead_submits'    => 0,
    'searches'        => 0,
];
$channel_rows = [];
$trend_series = [];
$trend_mode   = ($range === 'today') ? 'hourly' : 'daily';
$top_pages    = [];
$top_searches = [];
$top_sources  = [];
$top_services = [];
$device_rows  = [];
$recent_logs  = [];

try {
    $kpi_row = Database::fetch(
        "SELECT
            SUM(event_name = 'page_view') AS page_views,
            COUNT(DISTINCT session_hash) AS unique_sessions,
            SUM(event_name = 'booking_cta_click') AS booking_clicks,
            SUM(event_name = 'whatsapp_click' AND (wa_channel = 'sales' OR wa_channel IS NULL OR wa_channel = '')) AS wa_sales,
            SUM(event_name = 'whatsapp_click' AND wa_channel = 'cs') AS wa_cs,
            SUM(event_name = 'lead_form_submit') AS lead_submits,
            SUM(event_name = 'homepage_search') AS searches
         FROM analytics_events {$where_sql}",
        $params
    );
    if ($kpi_row) {
        foreach ($kpi as $k => $_v) {
            $kpi[$k] = (int) ($kpi_row[$k] ?? 0);
        }
    }

    // Channel attribution comparison (uses date filter only so all channels remain comparable)
    $channel_rows = Database::fetchAll(
        "SELECT
            traffic_channel,
            SUM(event_name = 'page_view') AS page_views,
            COUNT(DISTINCT session_hash) AS unique_sessions,
            SUM(event_name = 'booking_cta_click') AS booking_clicks,
            SUM(event_name = 'whatsapp_click' AND (wa_channel = 'sales' OR wa_channel IS NULL OR wa_channel = '')) AS wa_sales,
            SUM(event_name = 'lead_form_submit') AS lead_submits,
            SUM(event_name = 'homepage_search') AS searches
         FROM analytics_events {$where_date_sql}
         GROUP BY traffic_channel
         ORDER BY page_views DESC, unique_sessions DESC",
        $date_params
    );

    // Trend series: Hourly for "today", continuous Daily timeline for multi-day ranges
    if ($trend_mode === 'hourly') {
        $hourly_raw = Database::fetchAll(
            "SELECT
                HOUR(created_at) AS hr,
                SUM(event_name = 'page_view') AS page_views,
                COUNT(DISTINCT session_hash) AS unique_sessions,
                SUM(event_name IN ('booking_cta_click', 'lead_form_submit') OR (event_name = 'whatsapp_click' AND (wa_channel = 'sales' OR wa_channel IS NULL OR wa_channel = ''))) AS conversions
             FROM analytics_events {$where_sql}
             GROUP BY HOUR(created_at)
             ORDER BY hr ASC",
            $params
        );
        $hour_map = [];
        foreach ($hourly_raw as $hr) {
            $hour_map[(int) $hr['hr']] = $hr;
        }
        $current_hour = (int) date('G');
        $end_hour = max(12, $current_hour);
        for ($h = 0; $h <= $end_hour; $h++) {
            $row = $hour_map[$h] ?? null;
            $lbl = str_pad((string) $h, 2, '0', STR_PAD_LEFT) . ':00';
            $trend_series[] = [
                'label'       => $lbl,
                'full_label'  => date('d M Y') . ' · ' . $lbl,
                'page_views'  => $row ? (int) $row['page_views'] : 0,
                'sessions'    => $row ? (int) $row['unique_sessions'] : 0,
                'conversions' => $row ? (int) $row['conversions'] : 0,
            ];
        }
    } else {
        $daily_raw = Database::fetchAll(
            "SELECT
                DATE(created_at) AS day_date,
                SUM(event_name = 'page_view') AS page_views,
                COUNT(DISTINCT session_hash) AS unique_sessions,
                SUM(event_name IN ('booking_cta_click', 'lead_form_submit') OR (event_name = 'whatsapp_click' AND (wa_channel = 'sales' OR wa_channel IS NULL OR wa_channel = ''))) AS conversions
             FROM analytics_events {$where_sql}
             GROUP BY DATE(created_at)
             ORDER BY day_date ASC",
            $params
        );
        $daily_map = [];
        foreach ($daily_raw as $dr) {
            $daily_map[$dr['day_date']] = $dr;
        }

        // Build continuous date timeline so bars/area lines never float awkwardly in whitespace
        $today_ts = strtotime(date('Y-m-d'));
        if ($range === '7d') {
            $start_ts = strtotime('-6 days', $today_ts);
            $end_ts   = $today_ts;
        } elseif ($range === '30d') {
            $start_ts = strtotime('-29 days', $today_ts);
            $end_ts   = $today_ts;
        } elseif ($range === '90d') {
            $start_ts = strtotime('-89 days', $today_ts);
            $end_ts   = $today_ts;
        } elseif ($range === 'custom' && $custom_start && $custom_end) {
            $start_ts = strtotime($custom_start);
            $end_ts   = strtotime($custom_end);
            if ($start_ts > $end_ts) {
                [$start_ts, $end_ts] = [$end_ts, $start_ts];
            }
        } else {
            // 'all' -> at least last 14 days, up to 60 days ending today
            $first_active_ts = !empty($daily_raw) ? strtotime($daily_raw[0]['day_date']) : strtotime('-13 days', $today_ts);
            $min_window_ts   = strtotime('-13 days', $today_ts);
            $cap_window_ts   = strtotime('-59 days', $today_ts);
            $start_ts = max($cap_window_ts, min($first_active_ts, $min_window_ts));
            $end_ts   = $today_ts;
        }

        $span_days = (int) round(($end_ts - $start_ts) / 86400) + 1;
        if ($span_days > 120) {
            // Fallback to active days only for huge custom spans
            foreach ($daily_raw as $dr) {
                $ts = strtotime($dr['day_date']);
                $trend_series[] = [
                    'label'       => date('d M', $ts),
                    'full_label'  => date('d M Y', $ts),
                    'page_views'  => (int) $dr['page_views'],
                    'sessions'    => (int) $dr['unique_sessions'],
                    'conversions' => (int) $dr['conversions'],
                ];
            }
        } else {
            for ($ts = $start_ts; $ts <= $end_ts; $ts += 86400) {
                $key = date('Y-m-d', $ts);
                $row = $daily_map[$key] ?? null;
                $trend_series[] = [
                    'label'       => date('d M', $ts),
                    'full_label'  => date('D, d M Y', $ts),
                    'page_views'  => $row ? (int) $row['page_views'] : 0,
                    'sessions'    => $row ? (int) $row['unique_sessions'] : 0,
                    'conversions' => $row ? (int) $row['conversions'] : 0,
                ];
            }
        }
    }

    // Top 15 pages by views + conversions
    $top_pages = Database::fetchAll(
        "SELECT
            page_path,
            SUM(event_name = 'page_view') AS page_views,
            COUNT(DISTINCT session_hash) AS unique_sessions,
            SUM(event_name = 'booking_cta_click') AS booking_clicks,
            SUM(event_name = 'whatsapp_click' AND (wa_channel = 'sales' OR wa_channel IS NULL OR wa_channel = '')) AS wa_sales,
            SUM(event_name = 'lead_form_submit') AS lead_submits
         FROM analytics_events {$where_sql}
         GROUP BY page_path
         ORDER BY page_views DESC, booking_clicks DESC
         LIMIT 15",
        $params
    );

    // Top 15 homepage searches
    $search_where = $where_sql ? ($where_sql . " AND event_name = 'homepage_search'") : "WHERE event_name = 'homepage_search'";
    $top_searches = Database::fetchAll(
        "SELECT
            COALESCE(service_name, '-') AS service_name,
            COALESCE(event_detail, '-') AS event_detail,
            COUNT(*) AS total
         FROM analytics_events {$search_where}
         GROUP BY service_name, event_detail
         ORDER BY total DESC
         LIMIT 15",
        $params
    );

    // Top specific sources (e.g. chatgpt.com, google / cpc, ig / paid_social)
    $top_sources = Database::fetchAll(
        "SELECT
            traffic_channel,
            traffic_source,
            traffic_medium,
            COUNT(DISTINCT session_hash) AS unique_sessions,
            SUM(event_name = 'page_view') AS page_views,
            SUM(event_name IN ('booking_cta_click', 'lead_form_submit') OR (event_name = 'whatsapp_click' AND (wa_channel = 'sales' OR wa_channel IS NULL OR wa_channel = ''))) AS conversions
         FROM analytics_events {$where_sql}
         GROUP BY traffic_channel, traffic_source, traffic_medium
         ORDER BY unique_sessions DESC, page_views DESC
         LIMIT 12",
        $params
    );

    // Top clicked services (Booking / WA / Form)
    $svc_where = $where_sql
        ? ($where_sql . " AND event_name IN ('booking_cta_click', 'whatsapp_click', 'lead_form_submit') AND service_name IS NOT NULL AND service_name != ''")
        : "WHERE event_name IN ('booking_cta_click', 'whatsapp_click', 'lead_form_submit') AND service_name IS NOT NULL AND service_name != ''";
    $top_services = Database::fetchAll(
        "SELECT
            service_name,
            SUM(event_name = 'booking_cta_click') AS booking_clicks,
            SUM(event_name = 'whatsapp_click') AS wa_clicks,
            SUM(event_name = 'lead_form_submit') AS lead_submits,
            COUNT(*) AS total
         FROM analytics_events {$svc_where}
         GROUP BY service_name
         ORDER BY total DESC
         LIMIT 10",
        $params
    );

    // Device breakdown
    $device_rows = Database::fetchAll(
        "SELECT device_type, COUNT(DISTINCT session_hash) AS unique_sessions, SUM(event_name = 'page_view') AS page_views
         FROM analytics_events {$where_sql}
         GROUP BY device_type
         ORDER BY unique_sessions DESC",
        $params
    );

    // Recent 25 events (live activity feed)
    $recent_logs = Database::fetchAll(
        "SELECT created_at, event_name, traffic_channel, traffic_source, page_path, service_name, wa_channel, event_detail, device_type
         FROM analytics_events {$where_sql}
         ORDER BY id DESC
         LIMIT 25",
        $params
    );
} catch (Exception $e) {
    $db_error = admin_friendly_error('Gagal memuat data analytics.', $e);
}

$total_channel_views = 0;
foreach ($channel_rows as $cr) {
    $total_channel_views += (int) $cr['page_views'];
}

$total_device_sessions = 0;
foreach ($device_rows as $dv) {
    $total_device_sessions += (int) $dv['unique_sessions'];
}

// Trend summary statistics
$trend_total_views = 0;
$trend_total_conv  = 0;
$trend_peak_views  = 0;
$trend_peak_label  = '-';
$trend_active_slots = 0;
foreach ($trend_series as $pt) {
    $trend_total_views += $pt['page_views'];
    $trend_total_conv  += $pt['conversions'];
    if ($pt['page_views'] > 0) {
        $trend_active_slots++;
    }
    if ($pt['page_views'] > $trend_peak_views) {
        $trend_peak_views = $pt['page_views'];
        $trend_peak_label = $pt['label'];
    }
}
$trend_avg_views = $trend_active_slots > 0 ? round($trend_total_views / $trend_active_slots, 1) : 0;

$total_conversions = $kpi['booking_clicks'] + $kpi['wa_sales'] + $kpi['lead_submits'];
$overall_cvr = $kpi['unique_sessions'] > 0 ? round(($total_conversions / $kpi['unique_sessions']) * 100, 1) : 0;

$build_filter_url = static function (array $overrides = []) use ($range, $channel_filter, $custom_start, $custom_end): string {
    $q = array_merge([
        'range'   => $range,
        'channel' => $channel_filter,
        'start'   => $custom_start,
        'end'     => $custom_end,
    ], $overrides);
    $q = array_filter($q, static fn($v) => $v !== '' && $v !== null);
    return BASE_URL . 'admin/analytics.php?' . http_build_query($q);
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traffic &amp; Events Analytics - Urban Office CMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css?v=<?php echo filemtime(dirname(__DIR__) . '/assets/css/admin.css'); ?>">
    <style>
        /* UI/UX Pro Max — Data-Dense Dashboard Semantic Tokens */
        :root {
            --an-primary: #1e40af;
            --an-primary-hover: #1d4ed8;
            --an-secondary: #2563eb;
            --an-accent: #d97706;
            --an-emerald: #059669;
            --an-violet: #7c3aed;
            --an-bg: #f8fafc;
            --an-surface: #ffffff;
            --an-surface-muted: #f1f5f9;
            --an-ink: #0f172a;
            --an-ink-2: #334155;
            --an-muted: #475569;
            --an-subtle: #64748b;
            --an-border: #e2e8f0;
            --an-border-strong: #cbd5e1;
            --an-ring: rgba(30, 64, 175, 0.25);
            --an-radius: 12px;
            --an-radius-sm: 8px;
            --an-shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.03);
            --an-shadow-hover: 0 6px 18px -4px rgba(15, 23, 42, 0.08), 0 2px 6px -1px rgba(15, 23, 42, 0.04);
            --an-font-sans: 'Fira Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            --an-font-mono: 'Fira Code', ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        body.admin-body {
            background-color: var(--an-bg);
            font-family: var(--an-font-sans);
            color: var(--an-ink-2);
            -webkit-font-smoothing: antialiased;
        }

        .admin-main {
            padding: 28px 32px 48px;
            max-width: 1520px;
        }

        /* Focus accessibility */
        .an-page a:focus-visible,
        .an-page button:focus-visible,
        .an-page select:focus-visible,
        .an-page input:focus-visible {
            outline: 2px solid var(--an-primary);
            outline-offset: 2px;
        }

        .an-num {
            font-family: var(--an-font-mono);
            font-variant-numeric: tabular-nums;
        }

        /* Header */
        .an-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .an-header-title-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .an-header h1 {
            margin: 0;
            font-size: 1.55rem;
            font-weight: 700;
            color: var(--an-ink);
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        .an-live-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }
        .an-live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.22);
        }
        .an-header-sub {
            margin: 6px 0 0;
            font-size: 0.85rem;
            color: var(--an-muted);
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .an-period-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 2px 9px;
            border-radius: 6px;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            color: var(--an-primary);
            font-weight: 600;
            font-size: 0.78rem;
        }
        .an-header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .an-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 8px 14px;
            border-radius: var(--an-radius-sm);
            font-family: var(--an-font-sans);
            font-size: 0.84rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease, box-shadow 160ms ease;
            line-height: 1.25;
            white-space: nowrap;
        }
        .an-btn svg {
            width: 15px;
            height: 15px;
            flex-shrink: 0;
        }
        .an-btn-primary {
            background: var(--an-primary);
            color: #ffffff;
            box-shadow: 0 1px 2px rgba(30, 64, 175, 0.2);
        }
        .an-btn-primary:hover {
            background: var(--an-primary-hover);
            color: #ffffff;
        }
        .an-btn-secondary {
            background: var(--an-surface);
            color: var(--an-ink-2);
            border-color: var(--an-border-strong);
        }
        .an-btn-secondary:hover {
            background: var(--an-surface-muted);
            color: var(--an-ink);
            border-color: #94a3b8;
        }

        /* Filter Toolbar */
        .an-filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: var(--an-surface);
            border: 1px solid var(--an-border);
            border-radius: var(--an-radius);
            padding: 12px 16px;
            margin-bottom: 18px;
            box-shadow: var(--an-shadow-sm);
        }
        .an-pills {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 4px;
            background: var(--an-surface-muted);
            padding: 4px;
            border-radius: 9px;
            border: 1px solid var(--an-border);
        }
        .an-pill {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.81rem;
            font-weight: 600;
            text-decoration: none;
            color: var(--an-muted);
            background: transparent;
            cursor: pointer;
            transition: background-color 150ms ease, color 150ms ease, box-shadow 150ms ease;
            white-space: nowrap;
        }
        .an-pill:hover {
            color: var(--an-ink);
            background: rgba(255, 255, 255, 0.7);
        }
        .an-pill.active {
            background: var(--an-surface);
            color: var(--an-primary);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1);
        }
        .an-filter-form {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }
        .an-input {
            height: 35px;
            padding: 0 10px;
            border: 1px solid var(--an-border-strong);
            border-radius: var(--an-radius-sm);
            font-family: var(--an-font-sans);
            font-size: 0.82rem;
            background: var(--an-surface);
            color: var(--an-ink);
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }
        .an-input:focus {
            border-color: var(--an-primary);
            box-shadow: 0 0 0 3px var(--an-ring);
            outline: none;
        }
        .an-date-sep {
            font-size: 0.78rem;
            color: var(--an-subtle);
            font-weight: 500;
        }

        /* Active Filter Banner */
        .an-active-filter {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e3a8a;
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 18px;
            font-size: 0.84rem;
        }
        .an-active-filter a {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #1e40af;
            font-weight: 600;
            text-decoration: none;
            padding: 4px 10px;
            border-radius: 6px;
            background: #ffffff;
            border: 1px solid #bfdbfe;
            font-size: 0.78rem;
            transition: background-color 150ms ease;
        }
        .an-active-filter a:hover {
            background: #dbeafe;
        }

        /* KPI Row — 6 Cards on 1 Row on Desktop */
        .an-kpi-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        .an-kpi-card {
            position: relative;
            background: var(--an-surface);
            border: 1px solid var(--an-border);
            border-radius: var(--an-radius);
            padding: 14px 14px 13px;
            box-shadow: var(--an-shadow-sm);
            min-width: 0;
            overflow: hidden;
            transition: border-color 180ms ease, box-shadow 180ms ease, transform 180ms ease;
        }
        .an-kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--kpi-accent, #cbd5e1);
        }
        .an-kpi-card:hover {
            border-color: var(--an-border-strong);
            box-shadow: var(--an-shadow-hover);
            transform: translateY(-1px);
        }
        .an-kpi-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 6px;
        }
        .an-kpi-label {
            font-size: 0.69rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--an-muted);
            letter-spacing: 0.03em;
            line-height: 1.3;
        }
        .an-kpi-icon {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: var(--kpi-icon-bg, #f1f5f9);
            color: var(--kpi-accent, #475569);
        }
        .an-kpi-icon svg {
            width: 15px;
            height: 15px;
        }
        .an-kpi-val {
            font-family: var(--an-font-mono);
            font-variant-numeric: tabular-nums;
            font-size: 1.55rem;
            font-weight: 700;
            color: var(--an-ink);
            margin-top: 8px;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }
        .an-kpi-sub {
            font-size: 0.72rem;
            color: var(--an-subtle);
            margin-top: 6px;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .an-kpi-sub strong {
            color: var(--an-ink);
            font-family: var(--an-font-mono);
        }

        /* Cards & Sections */
        .an-card {
            background: var(--an-surface);
            border: 1px solid var(--an-border);
            border-radius: var(--an-radius);
            box-shadow: var(--an-shadow-sm);
            padding: 20px 22px;
            margin-bottom: 20px;
        }
        .an-card-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .an-card-title-wrap {
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .an-card-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #eff6ff;
            color: var(--an-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .an-card-icon svg {
            width: 16px;
            height: 16px;
        }
        .an-card-head h2,
        .an-card-head h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--an-ink);
            letter-spacing: -0.01em;
        }
        .an-card-subtitle {
            margin: 2px 0 0;
            font-size: 0.78rem;
            color: var(--an-subtle);
        }
        .an-muted {
            color: var(--an-subtle);
            font-size: 0.8rem;
        }

        /* Chart Section */
        .an-chart-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .an-chart-stats {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .an-stat-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 7px;
            background: var(--an-bg);
            border: 1px solid var(--an-border);
            font-size: 0.75rem;
            color: var(--an-muted);
        }
        .an-stat-chip strong {
            color: var(--an-ink);
            font-family: var(--an-font-mono);
            font-weight: 600;
        }
        .an-seg-toggle {
            display: inline-flex;
            background: var(--an-surface-muted);
            border: 1px solid var(--an-border);
            border-radius: 8px;
            padding: 3px;
            gap: 2px;
        }
        .an-seg-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: var(--an-muted);
            font-family: var(--an-font-sans);
            font-size: 0.76rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 150ms ease, color 150ms ease, box-shadow 150ms ease;
        }
        .an-seg-btn svg {
            width: 13px;
            height: 13px;
        }
        .an-seg-btn:hover {
            color: var(--an-ink);
        }
        .an-seg-btn.active {
            background: var(--an-surface);
            color: var(--an-primary);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }
        .an-chart-legend {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 10px;
            font-size: 0.78rem;
            color: var(--an-muted);
            flex-wrap: wrap;
        }
        .an-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }
        .an-legend-swatch {
            width: 10px;
            height: 10px;
            border-radius: 3px;
            display: inline-block;
        }
        .an-chart-canvas-wrap {
            position: relative;
            height: 270px;
            width: 100%;
        }

        /* 2-column grid */
        .an-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .an-grid-2 .an-card {
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
        }

        /* Tables */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .an-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            text-align: left;
            font-size: 0.84rem;
        }
        .an-table thead th {
            background: var(--an-bg);
            color: var(--an-muted);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 10px 12px;
            border-top: 1px solid var(--an-border);
            border-bottom: 1px solid var(--an-border);
            white-space: nowrap;
        }
        .an-table thead th:first-child {
            border-top-left-radius: 8px;
            border-left: 1px solid var(--an-border);
        }
        .an-table thead th:last-child {
            border-top-right-radius: 8px;
            border-right: 1px solid var(--an-border);
        }
        .an-table tbody td {
            padding: 11px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: var(--an-ink-2);
            vertical-align: middle;
        }
        .an-table tbody tr {
            transition: background-color 140ms ease;
        }
        .an-table tbody tr:hover {
            background: #f8fafc;
        }
        .an-table tbody tr.an-row-active {
            background: #eff6ff;
        }
        .an-table tbody tr:last-child td {
            border-bottom: 0;
        }
        .an-th-num,
        .an-td-num {
            text-align: right;
            font-family: var(--an-font-mono);
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        /* Badges & Progress */
        .an-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 600;
            white-space: nowrap;
            border: 1px solid transparent;
        }
        .an-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .an-channel-link {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            transition: transform 140ms ease, box-shadow 140ms ease;
        }
        .an-channel-link:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
        }
        .an-share-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 110px;
        }
        .an-share-pct {
            font-family: var(--an-font-mono);
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--an-ink-2);
            min-width: 40px;
        }
        .an-bar-track {
            flex: 1;
            height: 7px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }
        .an-bar-fill {
            height: 100%;
            border-radius: 999px;
            transition: width 350ms ease;
        }
        .an-cvr-pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 6px;
            font-family: var(--an-font-mono);
            font-size: 0.76rem;
            font-weight: 600;
            background: #f1f5f9;
            color: var(--an-ink);
        }
        .an-cvr-pill.high {
            background: #ecfdf5;
            color: #047857;
        }

        .an-path-code {
            display: inline-block;
            font-family: var(--an-font-mono);
            font-size: 0.77rem;
            color: var(--an-primary);
            background: #eff6ff;
            border: 1px solid #dbeafe;
            padding: 2px 7px;
            border-radius: 5px;
            word-break: break-all;
            max-width: 100%;
        }

        /* Device Cards */
        .an-device-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-top: auto;
            padding-top: 14px;
            border-top: 1px solid var(--an-border);
        }
        .an-device-card {
            background: var(--an-bg);
            border: 1px solid var(--an-border);
            border-radius: 9px;
            padding: 10px 12px;
        }
        .an-device-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            color: var(--an-muted);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .an-device-head svg {
            width: 14px;
            height: 14px;
            color: var(--an-primary);
        }
        .an-device-val {
            font-family: var(--an-font-mono);
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--an-ink);
            margin-top: 5px;
            display: flex;
            align-items: baseline;
            gap: 5px;
        }
        .an-device-val span {
            font-family: var(--an-font-sans);
            font-size: 0.73rem;
            font-weight: 500;
            color: var(--an-subtle);
        }

        /* Empty State */
        .an-empty {
            text-align: center;
            padding: 28px 16px;
            color: var(--an-subtle);
        }
        .an-empty svg {
            width: 28px;
            height: 28px;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        .an-empty p {
            margin: 0;
            font-size: 0.83rem;
        }

        /* Error Banner */
        .an-alert-error {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fef2f2;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 18px;
            border: 1px solid #fecaca;
            font-size: 0.86rem;
        }

        /* Screen-reader only */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        /* Responsive Breakpoints */
        @media (max-width: 1200px) {
            .an-kpi-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (max-width: 1080px) {
            .an-grid-2 {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 640px) {
            .an-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .an-chart-canvas-wrap {
                height: 220px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .an-kpi-card,
            .an-btn,
            .an-pill,
            .an-bar-fill,
            .an-channel-link,
            .an-table tbody tr {
                transition: none !important;
                transform: none !important;
            }
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="admin-main an-page">
            <!-- Header -->
            <header class="an-header">
                <div>
                    <div class="an-header-title-row">
                        <h1>Traffic &amp; Events Analytics</h1>
                        <span class="an-live-pill">
                            <span class="an-live-dot" aria-hidden="true"></span>
                            First-Party Real-Time
                        </span>
                    </div>
                    <p class="an-header-sub">
                        <span>Tracking langsung bebas AdBlocker &bull; Penyimpanan permanen</span>
                        <span class="an-period-chip">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            <?php echo sanitize($period_label); ?>
                        </span>
                    </p>
                </div>

                <div class="an-header-actions">
                    <a href="<?php echo sanitize($build_filter_url(['export' => 'csv'])); ?>" class="an-btn an-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Export CSV
                    </a>
                    <a href="<?php echo sanitize($build_filter_url()); ?>" class="an-btn an-btn-secondary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                        Refresh
                    </a>
                </div>
            </header>

            <?php if ($db_error): ?>
                <div class="an-alert-error" role="alert">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span><?php echo sanitize($db_error); ?></span>
                </div>
            <?php endif; ?>

            <!-- Filter Bar -->
            <section class="an-filter-bar" aria-label="Filter Periode dan Channel">
                <nav class="an-pills" aria-label="Pilih rentang waktu">
                    <a href="<?php echo sanitize($build_filter_url(['range' => 'today', 'start' => '', 'end' => ''])); ?>" class="an-pill<?php echo $range === 'today' ? ' active' : ''; ?>">Hari Ini</a>
                    <a href="<?php echo sanitize($build_filter_url(['range' => '7d', 'start' => '', 'end' => ''])); ?>" class="an-pill<?php echo $range === '7d' ? ' active' : ''; ?>">7 Hari</a>
                    <a href="<?php echo sanitize($build_filter_url(['range' => '30d', 'start' => '', 'end' => ''])); ?>" class="an-pill<?php echo $range === '30d' ? ' active' : ''; ?>">30 Hari</a>
                    <a href="<?php echo sanitize($build_filter_url(['range' => '90d', 'start' => '', 'end' => ''])); ?>" class="an-pill<?php echo $range === '90d' ? ' active' : ''; ?>">90 Hari</a>
                    <a href="<?php echo sanitize($build_filter_url(['range' => 'all', 'start' => '', 'end' => ''])); ?>" class="an-pill<?php echo $range === 'all' ? ' active' : ''; ?>">Semua Waktu</a>
                </nav>

                <form method="GET" action="<?php echo BASE_URL; ?>admin/analytics.php" class="an-filter-form">
                    <select name="channel" class="an-input" aria-label="Filter Sumber Traffic">
                        <option value="">Semua Channel</option>
                        <?php foreach ($channel_meta as $ck => $cm): ?>
                            <option value="<?php echo sanitize($ck); ?>" <?php echo $channel_filter === $ck ? 'selected' : ''; ?>>
                                <?php echo sanitize($cm['label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="range" value="<?php echo ($custom_start && $custom_end) ? 'custom' : sanitize($range); ?>" id="an-range-input">
                    <input type="date" name="start" class="an-input" value="<?php echo sanitize($custom_start); ?>" aria-label="Tanggal Mulai" title="Tanggal Mulai" onchange="document.getElementById('an-range-input').value='custom'">
                    <span class="an-date-sep" aria-hidden="true">–</span>
                    <input type="date" name="end" class="an-input" value="<?php echo sanitize($custom_end); ?>" aria-label="Tanggal Akhir" title="Tanggal Akhir" onchange="document.getElementById('an-range-input').value='custom'">
                    <button type="submit" class="an-btn an-btn-primary" style="height: 35px; padding: 0 14px;">Terapkan</button>
                </form>
            </section>

            <?php if ($channel_filter !== '' && isset($channel_meta[$channel_filter])): ?>
                <div class="an-active-filter">
                    <div>
                        Menampilkan metrik khusus channel:
                        <span class="an-badge" style="margin-left: 6px; background: <?php echo $channel_meta[$channel_filter]['bg']; ?>; color: <?php echo $channel_meta[$channel_filter]['color']; ?>; border-color: <?php echo $channel_meta[$channel_filter]['border']; ?>;">
                            <span class="an-badge-dot" style="background: <?php echo $channel_meta[$channel_filter]['bar']; ?>;" aria-hidden="true"></span>
                            <?php echo sanitize($channel_meta[$channel_filter]['label']); ?>
                        </span>
                    </div>
                    <a href="<?php echo sanitize($build_filter_url(['channel' => ''])); ?>">
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        Reset Channel
                    </a>
                </div>
            <?php endif; ?>

            <!-- KPI Metric Cards (6 Cards Sejajar 1 Baris di Desktop) -->
            <section class="an-kpi-grid" aria-label="Ringkasan KPI Utama">
                <article class="an-kpi-card" style="--kpi-accent: #1e40af; --kpi-icon-bg: #eff6ff;">
                    <div class="an-kpi-top">
                        <div class="an-kpi-label">Total Kunjungan</div>
                        <span class="an-kpi-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </span>
                    </div>
                    <div class="an-kpi-val"><?php echo number_format($kpi['page_views']); ?></div>
                    <div class="an-kpi-sub">Page views non-bot</div>
                </article>

                <article class="an-kpi-card" style="--kpi-accent: #0284c7; --kpi-icon-bg: #f0f9ff;">
                    <div class="an-kpi-top">
                        <div class="an-kpi-label">Sesi Unik</div>
                        <span class="an-kpi-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </span>
                    </div>
                    <div class="an-kpi-val"><?php echo number_format($kpi['unique_sessions']); ?></div>
                    <div class="an-kpi-sub">Conv. rate: <strong><?php echo $overall_cvr; ?>%</strong></div>
                </article>

                <article class="an-kpi-card" style="--kpi-accent: #ea580c; --kpi-icon-bg: #fff7ed;">
                    <div class="an-kpi-top">
                        <div class="an-kpi-label">Klik Booking Online</div>
                        <span class="an-kpi-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><path d="M9 16l2 2 4-4"></path></svg>
                        </span>
                    </div>
                    <div class="an-kpi-val"><?php echo number_format($kpi['booking_clicks']); ?></div>
                    <div class="an-kpi-sub">Redirect ke Web App</div>
                </article>

                <article class="an-kpi-card" style="--kpi-accent: #059669; --kpi-icon-bg: #ecfdf5;">
                    <div class="an-kpi-top">
                        <div class="an-kpi-label">Klik WA Sales</div>
                        <span class="an-kpi-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        </span>
                    </div>
                    <div class="an-kpi-val"><?php echo number_format($kpi['wa_sales']); ?></div>
                    <div class="an-kpi-sub">WA CS: <strong><?php echo number_format($kpi['wa_cs']); ?></strong></div>
                </article>

                <article class="an-kpi-card" style="--kpi-accent: #2563eb; --kpi-icon-bg: #eff6ff;">
                    <div class="an-kpi-top">
                        <div class="an-kpi-label">Lead Form Submit</div>
                        <span class="an-kpi-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        </span>
                    </div>
                    <div class="an-kpi-val"><?php echo number_format($kpi['lead_submits']); ?></div>
                    <div class="an-kpi-sub">Sinkron Lead Capture</div>
                </article>

                <article class="an-kpi-card" style="--kpi-accent: #7c3aed; --kpi-icon-bg: #f5f3ff;">
                    <div class="an-kpi-top">
                        <div class="an-kpi-label">Pencarian Homepage</div>
                        <span class="an-kpi-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                    </div>
                    <div class="an-kpi-val"><?php echo number_format($kpi['searches']); ?></div>
                    <div class="an-kpi-sub">Search Bar layanan/kota</div>
                </article>
            </section>

            <!-- Section 1: Modern Interactive Trend Chart -->
            <section class="an-card" aria-label="Grafik Tren Kunjungan dan Konversi">
                <div class="an-card-head">
                    <div class="an-card-title-wrap">
                        <span class="an-card-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                        </span>
                        <div>
                            <h2><?php echo $trend_mode === 'hourly' ? 'Tren Kunjungan Per Jam (Hari Ini)' : 'Tren Kunjungan &amp; Konversi Harian'; ?></h2>
                            <p class="an-card-subtitle">
                                <?php echo $trend_mode === 'hourly'
                                    ? 'Distribusi kunjungan dan aksi konversi per jam hari ini'
                                    : 'Timeline kontinu dalam periode filter — arahkan kursor pada grafik untuk detail'; ?>
                            </p>
                        </div>
                    </div>

                    <div class="an-chart-controls">
                        <div class="an-chart-stats">
                            <span class="an-stat-chip">
                                Puncak: <strong><?php echo number_format($trend_peak_views); ?></strong> (<?php echo sanitize($trend_peak_label); ?>)
                            </span>
                            <span class="an-stat-chip">
                                Rata-rata aktif: <strong><?php echo $trend_avg_views; ?></strong>
                            </span>
                            <span class="an-stat-chip">
                                Konversi: <strong><?php echo number_format($trend_total_conv); ?></strong>
                            </span>
                        </div>

                        <div class="an-seg-toggle" role="group" aria-label="Pilih tipe grafik">
                            <button type="button" class="an-seg-btn active" data-chart-type="bar" aria-pressed="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                                Batang
                            </button>
                            <button type="button" class="an-seg-btn" data-chart-type="line" aria-pressed="false">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                                Area
                            </button>
                        </div>
                    </div>
                </div>

                <div class="an-chart-legend" aria-hidden="true">
                    <span class="an-legend-item">
                        <span class="an-legend-swatch" style="background: #2563eb;"></span>
                        Kunjungan (Page Views)
                    </span>
                    <span class="an-legend-item">
                        <span class="an-legend-swatch" style="background: #d97706;"></span>
                        Aksi Konversi (Booking · WA Sales · Lead)
                    </span>
                </div>

                <?php if (empty($trend_series)): ?>
                    <div class="an-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                        <p>Belum ada data grafik untuk periode yang dipilih.</p>
                    </div>
                <?php else: ?>
                    <div class="an-chart-canvas-wrap">
                        <canvas id="anTrendChart" role="img" aria-label="Grafik tren kunjungan dan konversi untuk <?php echo sanitize($period_label); ?>. Total <?php echo number_format($trend_total_views); ?> kunjungan dan <?php echo number_format($trend_total_conv); ?> konversi."></canvas>
                    </div>
                    <!-- Screen-reader accessible data fallback -->
                    <table class="sr-only">
                        <caption>Data Tren Kunjungan (<?php echo sanitize($period_label); ?>)</caption>
                        <thead><tr><th>Waktu</th><th>Kunjungan</th><th>Sesi</th><th>Konversi</th></tr></thead>
                        <tbody>
                            <?php foreach ($trend_series as $pt): ?>
                                <tr>
                                    <td><?php echo sanitize($pt['full_label']); ?></td>
                                    <td><?php echo $pt['page_views']; ?></td>
                                    <td><?php echo $pt['sessions']; ?></td>
                                    <td><?php echo $pt['conversions']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

            <!-- Section 2: Channel Attribution Comparison -->
            <section class="an-card" aria-label="Perbandingan Sumber Traffic">
                <div class="an-card-head">
                    <div class="an-card-title-wrap">
                        <span class="an-card-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                        </span>
                        <div>
                            <h2>Perbandingan Sumber Traffic</h2>
                            <p class="an-card-subtitle">SEO Organic · Google Ads · Meta Ads · GEO/AI Search · Direct — klik channel untuk memfilter seluruh dashboard</p>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="an-table">
                        <thead>
                            <tr>
                                <th>Channel</th>
                                <th style="width: 22%;">Porsi Kunjungan</th>
                                <th class="an-th-num">Kunjungan</th>
                                <th class="an-th-num">Sesi Unik</th>
                                <th class="an-th-num">Booking</th>
                                <th class="an-th-num">WA Sales</th>
                                <th class="an-th-num">Lead Form</th>
                                <th class="an-th-num">Search</th>
                                <th class="an-th-num">Conv. Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($channel_rows)): ?>
                                <tr>
                                    <td colspan="9">
                                        <div class="an-empty">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                            <p>Belum ada data event pada periode ini. Buka halaman website untuk mulai merekam kunjungan.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($channel_rows as $cr):
                                    $ck = $cr['traffic_channel'];
                                    $cm = $channel_meta[$ck] ?? ['label' => ucfirst($ck), 'bg' => '#f1f5f9', 'color' => '#334155', 'border' => '#cbd5e1', 'bar' => '#64748b'];
                                    $pv = (int) $cr['page_views'];
                                    $us = (int) $cr['unique_sessions'];
                                    $bk = (int) $cr['booking_clicks'];
                                    $wa = (int) $cr['wa_sales'];
                                    $ld = (int) $cr['lead_submits'];
                                    $sr = (int) $cr['searches'];
                                    $share = $total_channel_views > 0 ? round(($pv / $total_channel_views) * 100, 1) : 0;
                                    $cvr = $us > 0 ? round((($bk + $wa + $ld) / $us) * 100, 1) : 0;
                                    $is_selected = ($channel_filter === $ck);
                                ?>
                                    <tr class="<?php echo $is_selected ? 'an-row-active' : ''; ?>">
                                        <td>
                                            <a href="<?php echo sanitize($build_filter_url(['channel' => ($is_selected ? '' : $ck)])); ?>" class="an-channel-link" title="<?php echo $is_selected ? 'Klik untuk hapus filter' : 'Klik untuk filter dashboard berdasarkan channel ini'; ?>">
                                                <span class="an-badge" style="background: <?php echo $cm['bg']; ?>; color: <?php echo $cm['color']; ?>; border-color: <?php echo $cm['border']; ?>;">
                                                    <span class="an-badge-dot" style="background: <?php echo $cm['bar']; ?>;" aria-hidden="true"></span>
                                                    <?php echo sanitize($cm['label']); ?>
                                                </span>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="an-share-wrap">
                                                <span class="an-share-pct"><?php echo $share; ?>%</span>
                                                <div class="an-bar-track" aria-hidden="true">
                                                    <div class="an-bar-fill" style="width: <?php echo min(100, $share); ?>%; background: <?php echo $cm['bar']; ?>;"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="an-td-num"><strong><?php echo number_format($pv); ?></strong></td>
                                        <td class="an-td-num"><?php echo number_format($us); ?></td>
                                        <td class="an-td-num" style="color: #c2410c; font-weight: 600;"><?php echo number_format($bk); ?></td>
                                        <td class="an-td-num" style="color: #047857; font-weight: 600;"><?php echo number_format($wa); ?></td>
                                        <td class="an-td-num" style="color: #1d4ed8; font-weight: 600;"><?php echo number_format($ld); ?></td>
                                        <td class="an-td-num"><?php echo number_format($sr); ?></td>
                                        <td class="an-td-num">
                                            <span class="an-cvr-pill<?php echo $cvr >= 5 ? ' high' : ''; ?>"><?php echo $cvr; ?>%</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Section 3: Top Pages & Top Homepage Searches -->
            <div class="an-grid-2">
                <section class="an-card" aria-label="Top 15 Halaman Paling Sering Diakses">
                    <div class="an-card-head">
                        <div class="an-card-title-wrap">
                            <span class="an-card-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            </span>
                            <div>
                                <h3>Top 15 Halaman Paling Sering Diakses</h3>
                                <p class="an-card-subtitle">Kunjungan dan performa konversi per URL</p>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="an-table">
                            <thead>
                                <tr>
                                    <th>URL Halaman</th>
                                    <th class="an-th-num">Views</th>
                                    <th class="an-th-num">Booking</th>
                                    <th class="an-th-num">WA Sales</th>
                                    <th class="an-th-num">Lead</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($top_pages)): ?>
                                    <tr>
                                        <td colspan="5">
                                            <div class="an-empty">
                                                <p>Belum ada data halaman pada periode ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($top_pages as $tp): ?>
                                        <tr>
                                            <td><span class="an-path-code"><?php echo sanitize($tp['page_path']); ?></span></td>
                                            <td class="an-td-num"><strong><?php echo number_format((int) $tp['page_views']); ?></strong></td>
                                            <td class="an-td-num" style="color: #c2410c; font-weight: 600;"><?php echo number_format((int) $tp['booking_clicks']); ?></td>
                                            <td class="an-td-num" style="color: #047857; font-weight: 600;"><?php echo number_format((int) $tp['wa_sales']); ?></td>
                                            <td class="an-td-num" style="color: #1d4ed8; font-weight: 600;"><?php echo number_format((int) $tp['lead_submits']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="an-card" aria-label="Top Pencarian Search Bar Homepage">
                    <div class="an-card-head">
                        <div class="an-card-title-wrap">
                            <span class="an-card-icon" style="background: #f5f3ff; color: #7c3aed;" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </span>
                            <div>
                                <h3>Top Pencarian Search Bar Homepage</h3>
                                <p class="an-card-subtitle">Riset demand layanan &amp; kota baru dari pengunjung</p>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="an-table">
                            <thead>
                                <tr>
                                    <th>Layanan</th>
                                    <th>Lokasi / Kata Dicari</th>
                                    <th>Status</th>
                                    <th class="an-th-num">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($top_searches)): ?>
                                    <tr>
                                        <td colspan="4">
                                            <div class="an-empty">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                                <p>Belum ada data pencarian Search Bar pada periode ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($top_searches as $ts):
                                        $raw_detail = (string) $ts['event_detail'];
                                        $is_unmatched = strpos($raw_detail, 'unmatched:') === 0;
                                        $clean_query = preg_replace('/^(matched|unmatched):/', '', $raw_detail);
                                    ?>
                                        <tr>
                                            <td><strong><?php echo sanitize($ts['service_name']); ?></strong></td>
                                            <td><?php echo sanitize($clean_query); ?></td>
                                            <td>
                                                <?php if ($is_unmatched): ?>
                                                    <span class="an-badge" style="background: #fef2f2; color: #b91c1c; border-color: #fecaca;">
                                                        <span class="an-badge-dot" style="background: #dc2626;" aria-hidden="true"></span>
                                                        Belum Ada
                                                    </span>
                                                <?php else: ?>
                                                    <span class="an-badge" style="background: #ecfdf5; color: #047857; border-color: #a7f3d0;">
                                                        <span class="an-badge-dot" style="background: #059669;" aria-hidden="true"></span>
                                                        Tersedia
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="an-td-num"><strong><?php echo number_format((int) $ts['total']); ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- Section 4: Specific Sources & Services / Devices -->
            <div class="an-grid-2">
                <section class="an-card" aria-label="Sumber Spesifik">
                    <div class="an-card-head">
                        <div class="an-card-title-wrap">
                            <span class="an-card-icon" style="background: #ecfdf5; color: #047857;" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                            </span>
                            <div>
                                <h3>Sumber Spesifik (Source / Medium)</h3>
                                <p class="an-card-subtitle">Detail domain rujukan seperti google/cpc, chatgpt.com, ig</p>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="an-table">
                            <thead>
                                <tr>
                                    <th>Source / Medium</th>
                                    <th>Channel</th>
                                    <th class="an-th-num">Sesi</th>
                                    <th class="an-th-num">Konversi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($top_sources)): ?>
                                    <tr>
                                        <td colspan="4">
                                            <div class="an-empty">
                                                <p>Belum ada data sumber spesifik.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($top_sources as $src):
                                        $cm = $channel_meta[$src['traffic_channel']] ?? ['label' => $src['traffic_channel'], 'bg' => '#f1f5f9', 'color' => '#334155', 'border' => '#cbd5e1', 'bar' => '#64748b'];
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo sanitize($src['traffic_source']); ?></strong>
                                                <span class="an-muted">/ <?php echo sanitize($src['traffic_medium']); ?></span>
                                            </td>
                                            <td>
                                                <span class="an-badge" style="background: <?php echo $cm['bg']; ?>; color: <?php echo $cm['color']; ?>; border-color: <?php echo $cm['border']; ?>;">
                                                    <span class="an-badge-dot" style="background: <?php echo $cm['bar']; ?>;" aria-hidden="true"></span>
                                                    <?php echo sanitize($cm['label']); ?>
                                                </span>
                                            </td>
                                            <td class="an-td-num"><?php echo number_format((int) $src['unique_sessions']); ?></td>
                                            <td class="an-td-num"><strong><?php echo number_format((int) $src['conversions']); ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="an-card" aria-label="Layanan Paling Diminati dan Perangkat">
                    <div class="an-card-head">
                        <div class="an-card-title-wrap">
                            <span class="an-card-icon" style="background: #fff7ed; color: #c2410c;" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            </span>
                            <div>
                                <h3>Layanan Paling Diminati &amp; Perangkat</h3>
                                <p class="an-card-subtitle">Agregasi klik Booking, WA Sales &amp; Lead Form per produk</p>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" style="margin-bottom: 16px;">
                        <table class="an-table">
                            <thead>
                                <tr>
                                    <th>Layanan / Konteks</th>
                                    <th class="an-th-num">Booking</th>
                                    <th class="an-th-num">WA</th>
                                    <th class="an-th-num">Lead</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($top_services)): ?>
                                    <tr>
                                        <td colspan="4">
                                            <div class="an-empty">
                                                <p>Belum ada data klik layanan pada periode ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($top_services as $sv): ?>
                                        <tr>
                                            <td><strong><?php echo sanitize($sv['service_name']); ?></strong></td>
                                            <td class="an-td-num" style="color: #c2410c; font-weight: 600;"><?php echo number_format((int) $sv['booking_clicks']); ?></td>
                                            <td class="an-td-num" style="color: #047857; font-weight: 600;"><?php echo number_format((int) $sv['wa_clicks']); ?></td>
                                            <td class="an-td-num" style="color: #1d4ed8; font-weight: 600;"><?php echo number_format((int) $sv['lead_submits']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!empty($device_rows)): ?>
                        <div class="an-device-grid" aria-label="Distribusi Perangkat Pengunjung">
                            <?php foreach ($device_rows as $dv):
                                $dt = strtolower((string) $dv['device_type']);
                                $dv_sessions = (int) $dv['unique_sessions'];
                                $dv_pct = $total_device_sessions > 0 ? round(($dv_sessions / $total_device_sessions) * 100) : 0;
                            ?>
                                <div class="an-device-card">
                                    <div class="an-device-head">
                                        <span><?php echo sanitize($dv['device_type']); ?></span>
                                        <?php if ($dt === 'mobile'): ?>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                                        <?php elseif ($dt === 'tablet'): ?>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                                        <?php else: ?>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="an-device-val">
                                        <?php echo number_format($dv_sessions); ?>
                                        <span>sesi (<?php echo $dv_pct; ?>%)</span>
                                    </div>
                                    <div class="an-bar-track" style="margin-top: 7px;" aria-hidden="true">
                                        <div class="an-bar-fill" style="width: <?php echo min(100, $dv_pct); ?>%; background: #2563eb;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            <!-- Section 5: Recent Live Activity Feed -->
            <section class="an-card" aria-label="Aktivitas Event Terbaru">
                <div class="an-card-head">
                    <div class="an-card-title-wrap">
                        <span class="an-card-icon" style="background: #ecfdf5; color: #059669;" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                        </span>
                        <div>
                            <h2>25 Aktivitas Event Terbaru (Live Feed)</h2>
                            <p class="an-card-subtitle">Terekam otomatis setiap kali pengunjung mengakses halaman atau mengklik CTA di website</p>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="an-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Event</th>
                                <th>Channel</th>
                                <th>Halaman</th>
                                <th>Layanan / Detail</th>
                                <th>Device</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_logs)): ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="an-empty">
                                            <p>Belum ada aktivitas terekam pada periode ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_logs as $log):
                                    $em = $event_labels[$log['event_name']] ?? ['label' => $log['event_name'], 'bg' => '#f1f5f9', 'color' => '#334155', 'dot' => '#64748b'];
                                    $cm = $channel_meta[$log['traffic_channel']] ?? ['label' => $log['traffic_channel'], 'bg' => '#f1f5f9', 'color' => '#334155', 'border' => '#cbd5e1', 'bar' => '#64748b'];
                                    $detail_parts = array_filter([
                                        $log['service_name'] ?? '',
                                        !empty($log['wa_channel']) ? ('WA:' . strtoupper($log['wa_channel'])) : '',
                                        $log['event_detail'] ?? '',
                                    ]);
                                ?>
                                    <tr>
                                        <td class="an-num" style="white-space: nowrap; color: var(--an-subtle); font-size: 0.78rem;"><?php echo date('d M H:i:s', strtotime($log['created_at'])); ?></td>
                                        <td>
                                            <span class="an-badge" style="background: <?php echo $em['bg']; ?>; color: <?php echo $em['color']; ?>;">
                                                <span class="an-badge-dot" style="background: <?php echo $em['dot']; ?>;" aria-hidden="true"></span>
                                                <?php echo sanitize($em['label']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="an-badge" style="background: <?php echo $cm['bg']; ?>; color: <?php echo $cm['color']; ?>; border-color: <?php echo $cm['border']; ?>;">
                                                <span class="an-badge-dot" style="background: <?php echo $cm['bar']; ?>;" aria-hidden="true"></span>
                                                <?php echo sanitize($cm['label']); ?>
                                            </span>
                                            <div class="an-muted" style="font-size: 0.72rem; margin-top: 3px;"><?php echo sanitize($log['traffic_source']); ?></div>
                                        </td>
                                        <td><span class="an-path-code"><?php echo sanitize($log['page_path']); ?></span></td>
                                        <td style="max-width: 280px; word-break: break-word;"><?php echo sanitize(implode(' · ', $detail_parts) ?: '—'); ?></td>
                                        <td style="text-transform: capitalize; color: var(--an-subtle); font-weight: 500;"><?php echo sanitize($log['device_type']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <?php if (!empty($trend_series)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            var canvas = document.getElementById('anTrendChart');
            if (!canvas || typeof Chart === 'undefined') return;

            var series = <?php echo json_encode($trend_series, JSON_UNESCAPED_UNICODE); ?>;
            var labels = series.map(function (d) { return d.label; });
            var views  = series.map(function (d) { return d.page_views; });
            var convs  = series.map(function (d) { return d.conversions; });

            var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var savedType = 'bar';
            try {
                var stored = localStorage.getItem('uo_an_chart_type');
                if (stored === 'bar' || stored === 'line') savedType = stored;
            } catch (e) {}

            var chartInstance = null;

            function createBlueGradient(ctx, chartArea, isLine) {
                var grad = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                if (isLine) {
                    grad.addColorStop(0, 'rgba(37, 99, 235, 0.28)');
                    grad.addColorStop(1, 'rgba(37, 99, 235, 0.01)');
                } else {
                    grad.addColorStop(0, '#1e40af');
                    grad.addColorStop(1, '#3b82f6');
                }
                return grad;
            }

            function renderChart(type) {
                if (chartInstance) {
                    chartInstance.destroy();
                }

                var isLine = type === 'line';
                var datasets = isLine
                    ? [
                        {
                            label: 'Kunjungan',
                            data: views,
                            borderColor: '#2563eb',
                            backgroundColor: function (context) {
                                var chart = context.chart;
                                if (!chart.chartArea) return 'rgba(37, 99, 235, 0.16)';
                                return createBlueGradient(chart.ctx, chart.chartArea, true);
                            },
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35,
                            pointRadius: series.length > 31 ? 0 : 3.5,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#2563eb',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Konversi',
                            data: convs,
                            borderColor: '#d97706',
                            backgroundColor: 'rgba(217, 119, 6, 0.08)',
                            borderWidth: 2,
                            borderDash: [5, 4],
                            fill: false,
                            tension: 0.35,
                            pointRadius: series.length > 31 ? 0 : 3,
                            pointHoverRadius: 5.5,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#d97706',
                            pointBorderWidth: 2
                        }
                    ]
                    : [
                        {
                            label: 'Kunjungan',
                            data: views,
                            backgroundColor: function (context) {
                                var chart = context.chart;
                                if (!chart.chartArea) return '#2563eb';
                                return createBlueGradient(chart.ctx, chart.chartArea, false);
                            },
                            hoverBackgroundColor: '#1d4ed8',
                            borderRadius: { topLeft: 6, topRight: 6 },
                            borderSkipped: 'bottom',
                            maxBarThickness: 28,
                            barPercentage: 0.72,
                            categoryPercentage: 0.76
                        },
                        {
                            label: 'Konversi',
                            data: convs,
                            backgroundColor: '#f59e0b',
                            hoverBackgroundColor: '#d97706',
                            borderRadius: { topLeft: 6, topRight: 6 },
                            borderSkipped: 'bottom',
                            maxBarThickness: 28,
                            barPercentage: 0.72,
                            categoryPercentage: 0.76
                        }
                    ];

                chartInstance = new Chart(canvas.getContext('2d'), {
                    type: isLine ? 'line' : 'bar',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: prefersReducedMotion ? false : { duration: 420, easing: 'easeOutQuart' },
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleColor: '#f8fafc',
                                bodyColor: '#e2e8f0',
                                borderColor: 'rgba(148, 163, 184, 0.25)',
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: true,
                                boxPadding: 5,
                                titleFont: { family: "'Fira Sans', sans-serif", size: 12, weight: '600' },
                                bodyFont: { family: "'Fira Code', monospace", size: 12 },
                                callbacks: {
                                    title: function (items) {
                                        var idx = items[0] ? items[0].dataIndex : 0;
                                        return series[idx] ? series[idx].full_label : '';
                                    },
                                    afterBody: function (items) {
                                        var idx = items[0] ? items[0].dataIndex : 0;
                                        if (!series[idx]) return '';
                                        return 'Sesi Unik: ' + series[idx].sessions.toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { color: '#e2e8f0' },
                                ticks: {
                                    color: '#64748b',
                                    font: { family: "'Fira Sans', sans-serif", size: 11 },
                                    maxRotation: 0,
                                    autoSkip: true,
                                    maxTicksLimit: 12
                                }
                            },
                            y: {
                                beginAtZero: true,
                                suggestedMax: 5,
                                border: { display: false },
                                grid: {
                                    color: '#f1f5f9',
                                    drawTicks: false
                                },
                                ticks: {
                                    color: '#64748b',
                                    precision: 0,
                                    padding: 8,
                                    font: { family: "'Fira Code', monospace", size: 11 }
                                }
                            }
                        }
                    }
                });
            }

            var toggleBtns = document.querySelectorAll('.an-seg-btn[data-chart-type]');
            function setActiveButton(type) {
                toggleBtns.forEach(function (btn) {
                    var active = btn.getAttribute('data-chart-type') === type;
                    btn.classList.toggle('active', active);
                    btn.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
            }

            toggleBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var nextType = btn.getAttribute('data-chart-type');
                    if (nextType !== 'bar' && nextType !== 'line') return;
                    setActiveButton(nextType);
                    renderChart(nextType);
                    try { localStorage.setItem('uo_an_chart_type', nextType); } catch (e) {}
                });
            });

            setActiveButton(savedType);
            renderChart(savedType);
        })();
    </script>
    <?php endif; ?>
</body>
</html>
