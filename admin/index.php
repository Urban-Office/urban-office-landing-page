<?php
/**
 * Admin Panel Login and Executive Dashboard Overview
 * Unified with UI/UX Pro Max Data-Dense Dashboard design system.
 */

require_once dirname(dirname(__FILE__)) . '/inc/config.php';
require_once dirname(dirname(__FILE__)) . '/inc/database.php';
require_once dirname(dirname(__FILE__)) . '/inc/functions.php';

$error = '';

// Handle Login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi token CSRF gagal. Silakan muat ulang halaman.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = trim((string) ($_POST['password'] ?? ''));

        try {
            $user = Database::fetch("SELECT * FROM users WHERE username = ? AND status = 1", [$username]);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_role'] = $user['role'];

                log_activity($user['id'], 'login', "User $username logged in successfully.");
                header('Location: ' . BASE_URL . 'admin/index.php');
                exit;
            } else {
                $error = 'Username atau password salah.';
                log_activity(null, 'login_failed', "Failed login attempt for username: " . sanitize($username));
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}

// Action: Clear Cache (CSRF-protected POST)
if (is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_cache'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (verify_csrf_token($token)) {
        clear_page_cache();
        log_activity($_SESSION['admin_user_id'], 'clear_cache', "Admin cleared website HTML page cache.");
        header('Location: ' . BASE_URL . 'admin/index.php?cached_cleared=1');
        exit;
    } else {
        $error = 'Validasi CSRF gagal saat membersihkan cache.';
    }
}

/**
 * Normalize Indonesian phone numbers (e.g. 0812... -> 62812...) for WhatsApp deep links.
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

// Render Dashboard if Admin is logged in
if (is_admin()):
    $count_posts_pub   = 0;
    $count_posts_draft = 0;
    $count_leads       = 0;
    $count_leads_unread = 0;
    $count_popups_active = 0;
    $count_popups_total  = 0;
    $count_cats        = 0;
    $count_tags        = 0;
    $recent_leads      = [];
    $recent_activities = [];

    // 7-day traffic & conversion metrics from analytics_events
    $traffic_7d = [
        'page_views'      => 0,
        'unique_sessions' => 0,
        'booking_clicks'  => 0,
        'wa_sales'        => 0,
        'lead_submits'    => 0,
    ];
    $top_channels_7d = [];
    $total_channel_views_7d = 0;

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

    try {
        $post_stats = Database::fetch(
            "SELECT
                SUM(status = 'published') AS published,
                SUM(status != 'published') AS draft
             FROM posts"
        );
        if ($post_stats) {
            $count_posts_pub   = (int) ($post_stats['published'] ?? 0);
            $count_posts_draft = (int) ($post_stats['draft'] ?? 0);
        }

        $lead_stats = Database::fetch(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'unread') AS unread
             FROM leads"
        );
        if ($lead_stats) {
            $count_leads        = (int) ($lead_stats['total'] ?? 0);
            $count_leads_unread = (int) ($lead_stats['unread'] ?? 0);
        }

        $count_cats = (int) (Database::fetch("SELECT COUNT(*) AS total FROM categories")['total'] ?? 0);
        $count_tags = (int) (Database::fetch("SELECT COUNT(*) AS total FROM tags")['total'] ?? 0);

        $recent_leads = Database::fetchAll("SELECT * FROM leads ORDER BY created_at DESC LIMIT 7");

        $recent_activities = Database::fetchAll(
            "SELECT l.action, l.details, l.created_at, u.username
             FROM activity_logs l
             LEFT JOIN users u ON l.user_id = u.id
             ORDER BY l.created_at DESC
             LIMIT 5"
        );
    } catch (Exception $e) {
        error_log("Failed pulling core dashboard metrics: " . $e->getMessage());
    }

    try {
        $popup_stats = Database::fetch(
            "SELECT COUNT(*) AS total, SUM(status = 1) AS active FROM popups"
        );
        if ($popup_stats) {
            $count_popups_total  = (int) ($popup_stats['total'] ?? 0);
            $count_popups_active = (int) ($popup_stats['active'] ?? 0);
        }
    } catch (Exception $e) {
        // Popups table optional on legacy DB
    }

    try {
        $since_7d = date('Y-m-d 00:00:00', strtotime('-6 days'));
        $t_row = Database::fetch(
            "SELECT
                SUM(event_name = 'page_view') AS page_views,
                COUNT(DISTINCT session_hash) AS unique_sessions,
                SUM(event_name = 'booking_cta_click') AS booking_clicks,
                SUM(event_name = 'whatsapp_click' AND (wa_channel = 'sales' OR wa_channel IS NULL OR wa_channel = '')) AS wa_sales,
                SUM(event_name = 'lead_form_submit') AS lead_submits
             FROM analytics_events
             WHERE created_at >= ?",
            [$since_7d]
        );
        if ($t_row) {
            foreach ($traffic_7d as $k => $_v) {
                $traffic_7d[$k] = (int) ($t_row[$k] ?? 0);
            }
        }

        $top_channels_7d = Database::fetchAll(
            "SELECT
                traffic_channel,
                SUM(event_name = 'page_view') AS page_views,
                COUNT(DISTINCT session_hash) AS unique_sessions,
                SUM(event_name IN ('booking_cta_click', 'lead_form_submit') OR (event_name = 'whatsapp_click' AND (wa_channel = 'sales' OR wa_channel IS NULL OR wa_channel = ''))) AS conversions
             FROM analytics_events
             WHERE created_at >= ?
             GROUP BY traffic_channel
             ORDER BY page_views DESC, unique_sessions DESC
             LIMIT 5",
            [$since_7d]
        );
        foreach ($top_channels_7d as $tc) {
            $total_channel_views_7d += (int) $tc['page_views'];
        }
    } catch (Exception $e) {
        // analytics_events not migrated yet — keep zero defaults gracefully
    }

    $conv_7d = $traffic_7d['booking_clicks'] + $traffic_7d['wa_sales'] + $traffic_7d['lead_submits'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Overview - Urban Office CMS</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
    <style>
        /* Overview Executive Grid & Widget Styles */
        .ov-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .ov-greeting-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .ov-date-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            color: var(--admin-primary);
            font-size: 0.75rem;
            font-weight: 600;
        }
        .ov-actions-inline {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* 6-Card Single-Row KPI Strip */
        .ov-kpi-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        .ov-kpi-card {
            position: relative;
            display: block;
            text-decoration: none;
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius);
            padding: 14px 14px 13px;
            box-shadow: var(--admin-shadow-sm);
            min-width: 0;
            overflow: hidden;
            transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease;
        }
        .ov-kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--kpi-accent, #cbd5e1);
        }
        .ov-kpi-card:hover {
            border-color: var(--admin-border-strong);
            box-shadow: var(--admin-shadow-hover);
            transform: translateY(-1px);
        }
        .ov-kpi-card.ov-kpi-alert {
            border-color: #fecaca;
            background: linear-gradient(180deg, #fffbfb 0%, #ffffff 100%);
        }
        .ov-kpi-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 6px;
        }
        .ov-kpi-label {
            font-size: 0.69rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--admin-muted);
            letter-spacing: 0.03em;
            line-height: 1.3;
        }
        .ov-kpi-icon {
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
        .ov-kpi-icon svg {
            width: 15px;
            height: 15px;
        }
        .ov-kpi-val {
            font-family: var(--admin-font-mono);
            font-variant-numeric: tabular-nums;
            font-size: 1.55rem;
            font-weight: 700;
            color: var(--admin-ink);
            margin-top: 8px;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }
        .ov-kpi-sub {
            font-size: 0.72rem;
            color: var(--admin-subtle);
            margin-top: 6px;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ov-kpi-sub strong {
            color: var(--admin-ink);
            font-family: var(--admin-font-mono);
        }

        /* Quick Actions Toolbar */
        .ov-quick-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius);
            padding: 13px 18px;
            margin-bottom: 20px;
            box-shadow: var(--admin-shadow-sm);
        }
        .ov-quick-title {
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--admin-muted);
        }
        .ov-quick-btns {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .ov-btn-cache {
            color: #b91c1c;
            border-color: #fecaca;
            background: #fef2f2;
        }
        .ov-btn-cache:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #991b1b;
        }

        /* Executive 2-Column Workspace */
        .ov-workspace {
            display: grid;
            grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr);
            gap: 20px;
            align-items: start;
        }
        .ov-side-stack {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .ov-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }
        .ov-card-title-wrap {
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .ov-card-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: #eff6ff;
            color: var(--admin-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .ov-card-icon svg {
            width: 15px;
            height: 15px;
        }
        .ov-card-head h2 {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 700;
            color: var(--admin-ink);
        }
        .ov-card-sub {
            margin: 2px 0 0;
            font-size: 0.77rem;
            color: var(--admin-subtle);
        }
        .ov-link-more {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--admin-primary);
            text-decoration: none;
            padding: 4px 8px;
            border-radius: 6px;
            transition: background-color 140ms ease;
        }
        .ov-link-more:hover {
            background: #eff6ff;
        }

        /* Leads Table Enhancements */
        .ov-lead-unread {
            background: #fffbeb;
        }
        .ov-lead-unread:hover {
            background: #fef3c7 !important;
        }
        .ov-wa-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 9px;
            border-radius: 6px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857;
            font-size: 0.76rem;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            transition: background-color 140ms ease, color 140ms ease;
        }
        .ov-wa-btn:hover {
            background: #059669;
            border-color: #059669;
            color: #ffffff;
        }
        .ov-wa-btn svg {
            width: 13px;
            height: 13px;
        }
        .ov-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        /* Channel Mini List */
        .ov-channel-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .ov-channel-row {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .ov-channel-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            font-size: 0.82rem;
        }
        .ov-channel-name {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-weight: 600;
            color: var(--admin-ink);
        }
        .ov-channel-meta {
            font-family: var(--admin-font-mono);
            font-size: 0.76rem;
            color: var(--admin-muted);
        }
        .ov-channel-meta strong {
            color: var(--admin-ink);
        }
        .ov-bar-track {
            height: 7px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }
        .ov-bar-fill {
            height: 100%;
            border-radius: 999px;
        }

        /* Activity Feed List */
        .ov-activity-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .ov-activity-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.81rem;
        }
        .ov-activity-item:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }
        .ov-activity-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #2563eb;
            margin-top: 6px;
            flex-shrink: 0;
        }
        .ov-activity-body {
            min-width: 0;
            flex: 1;
        }
        .ov-activity-text {
            color: var(--admin-ink-2);
            line-height: 1.4;
            word-break: break-word;
        }
        .ov-activity-meta {
            font-size: 0.72rem;
            color: var(--admin-subtle);
            margin-top: 2px;
            font-family: var(--admin-font-mono);
        }

        .ov-empty {
            text-align: center;
            padding: 24px 12px;
            color: var(--admin-subtle);
            font-size: 0.83rem;
        }

        @media (max-width: 1200px) {
            .ov-kpi-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (max-width: 1080px) {
            .ov-workspace {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 640px) {
            .ov-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="admin-main">
            <!-- Header -->
            <header class="ov-header">
                <div>
                    <div class="ov-greeting-row">
                        <h1 class="admin-title">Dashboard Overview</h1>
                        <span class="ov-date-chip">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            <?php echo date('d M Y'); ?>
                        </span>
                    </div>
                    <p class="admin-subtitle">
                        Selamat datang kembali, <strong><?php echo sanitize($_SESSION['admin_username']); ?></strong> — ringkasan performa traffic, konversi, dan konten website Urban Office.
                    </p>
                </div>

                <div class="ov-actions-inline">
                    <a href="<?php echo BASE_URL; ?>admin/posts.php?action=new" class="btn-admin btn-admin-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Tulis Artikel
                    </a>
                    <a href="<?php echo BASE_URL; ?>" target="_blank" rel="noopener" class="btn-admin btn-admin-secondary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                        Lihat Website
                    </a>
                </div>
            </header>

            <?php if (isset($_GET['cached_cleared'])): ?>
                <div class="admin-alert admin-alert-success" role="status">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span>Cache HTML halaman website berhasil dibersihkan. Perubahan terbaru kini langsung tampil ke pengunjung.</span>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="admin-alert admin-alert-error" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span><?php echo sanitize($error); ?></span>
                </div>
            <?php endif; ?>

            <!-- KPI Metric Strip (6 Cards Sejajar 1 Baris di Desktop) -->
            <section class="ov-kpi-grid" aria-label="Ringkasan Metrik Utama">
                <a href="<?php echo BASE_URL; ?>admin/analytics.php?range=7d" class="ov-kpi-card" style="--kpi-accent: #1e40af; --kpi-icon-bg: #eff6ff;">
                    <div class="ov-kpi-top">
                        <div class="ov-kpi-label">Kunjungan (7 Hari)</div>
                        <span class="ov-kpi-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </span>
                    </div>
                    <div class="ov-kpi-val"><?php echo number_format($traffic_7d['page_views']); ?></div>
                    <div class="ov-kpi-sub">Sesi unik: <strong><?php echo number_format($traffic_7d['unique_sessions']); ?></strong></div>
                </a>

                <a href="<?php echo BASE_URL; ?>admin/analytics.php?range=7d" class="ov-kpi-card" style="--kpi-accent: #ea580c; --kpi-icon-bg: #fff7ed;">
                    <div class="ov-kpi-top">
                        <div class="ov-kpi-label">Konversi (7 Hari)</div>
                        <span class="ov-kpi-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                        </span>
                    </div>
                    <div class="ov-kpi-val"><?php echo number_format($conv_7d); ?></div>
                    <div class="ov-kpi-sub">Book <strong><?php echo $traffic_7d['booking_clicks']; ?></strong> · WA <strong><?php echo $traffic_7d['wa_sales']; ?></strong></div>
                </a>

                <a href="<?php echo BASE_URL; ?>admin/leads.php?status=unread" class="ov-kpi-card<?php echo $count_leads_unread > 0 ? ' ov-kpi-alert' : ''; ?>" style="--kpi-accent: <?php echo $count_leads_unread > 0 ? '#dc2626' : '#059669'; ?>; --kpi-icon-bg: <?php echo $count_leads_unread > 0 ? '#fef2f2' : '#ecfdf5'; ?>;">
                    <div class="ov-kpi-top">
                        <div class="ov-kpi-label">Leads Unread</div>
                        <span class="ov-kpi-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        </span>
                    </div>
                    <div class="ov-kpi-val" style="<?php echo $count_leads_unread > 0 ? 'color: #b91c1c;' : ''; ?>"><?php echo number_format($count_leads_unread); ?></div>
                    <div class="ov-kpi-sub">Total semua: <strong><?php echo number_format($count_leads); ?></strong></div>
                </a>

                <a href="<?php echo BASE_URL; ?>admin/posts.php" class="ov-kpi-card" style="--kpi-accent: #2563eb; --kpi-icon-bg: #eff6ff;">
                    <div class="ov-kpi-top">
                        <div class="ov-kpi-label">Artikel Published</div>
                        <span class="ov-kpi-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        </span>
                    </div>
                    <div class="ov-kpi-val"><?php echo number_format($count_posts_pub); ?></div>
                    <div class="ov-kpi-sub">Draft tersimpan: <strong><?php echo number_format($count_posts_draft); ?></strong></div>
                </a>

                <a href="<?php echo BASE_URL; ?>admin/popups.php" class="ov-kpi-card" style="--kpi-accent: #7c3aed; --kpi-icon-bg: #f5f3ff;">
                    <div class="ov-kpi-top">
                        <div class="ov-kpi-label">Pop-up Aktif</div>
                        <span class="ov-kpi-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line></svg>
                        </span>
                    </div>
                    <div class="ov-kpi-val"><?php echo number_format($count_popups_active); ?></div>
                    <div class="ov-kpi-sub">Total banner: <strong><?php echo number_format($count_popups_total); ?></strong></div>
                </a>

                <a href="<?php echo BASE_URL; ?>admin/categories.php" class="ov-kpi-card" style="--kpi-accent: #0284c7; --kpi-icon-bg: #f0f9ff;">
                    <div class="ov-kpi-top">
                        <div class="ov-kpi-label">Kategori &amp; Tag</div>
                        <span class="ov-kpi-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                        </span>
                    </div>
                    <div class="ov-kpi-val"><?php echo number_format($count_cats); ?></div>
                    <div class="ov-kpi-sub">Tag blog: <strong><?php echo number_format($count_tags); ?></strong></div>
                </a>
            </section>

            <!-- Quick Actions Bar -->
            <section class="ov-quick-bar" aria-label="Aksi Cepat">
                <span class="ov-quick-title">Aksi Cepat</span>
                <div class="ov-quick-btns">
                    <a href="<?php echo BASE_URL; ?>admin/analytics.php" class="btn-admin btn-admin-secondary btn-admin-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                        Traffic &amp; Events
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin/leads.php" class="btn-admin btn-admin-secondary btn-admin-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        Kelola Leads
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin/popups.php" class="btn-admin btn-admin-secondary btn-admin-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line></svg>
                        Pop-up Promo
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin/pages.php" class="btn-admin btn-admin-secondary btn-admin-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line></svg>
                        SEO Pages
                    </a>
                    <form method="POST" action="<?php echo BASE_URL; ?>admin/index.php" style="display: inline;" onsubmit="return confirm('Bersihkan seluruh cache HTML halaman website sekarang?');">
                        <?php echo csrf_field(); ?>
                        <button type="submit" name="clear_cache" value="1" class="btn-admin btn-admin-secondary btn-admin-sm ov-btn-cache">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path></svg>
                            Clear HTML Cache
                        </button>
                    </form>
                </div>
            </section>

            <!-- 2-Column Executive Content Workspace -->
            <div class="ov-workspace">
                <!-- Left: Recent Leads with Direct WA Follow-up -->
                <section class="admin-card" style="margin-bottom: 0;" aria-label="Pendaftaran Leads Terbaru">
                    <div class="ov-card-head">
                        <div class="ov-card-title-wrap">
                            <span class="ov-card-icon" style="background: #ecfdf5; color: #047857;" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                            </span>
                            <div>
                                <h2>Pendaftaran Leads Terbaru</h2>
                                <p class="ov-card-sub">Follow-up cepat langsung ke WhatsApp prospek</p>
                            </div>
                        </div>
                        <a href="<?php echo BASE_URL; ?>admin/leads.php" class="ov-link-more">
                            Lihat Semua
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Nama &amp; Kontak</th>
                                    <th>Layanan</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_leads)): foreach ($recent_leads as $lead):
                                    $wa_num = admin_format_wa_phone((string) $lead['phone']);
                                    $wa_text = "Halo " . $lead['name'] . ", terima kasih telah menghubungi Urban Office terkait layanan " . $lead['service'] . ". Ada yang bisa kami bantu?";
                                    $wa_url = $wa_num !== '' ? ("https://api.whatsapp.com/send/?phone=" . $wa_num . "&text=" . rawurlencode($wa_text)) : '';
                                    $is_unread = ($lead['status'] === 'unread');
                                    $badge_cls = $is_unread ? 'badge-danger' : ($lead['status'] === 'read' ? 'badge-warning' : 'badge-success');
                                    $dot_color = $is_unread ? '#dc2626' : ($lead['status'] === 'read' ? '#ca8a04' : '#059669');
                                ?>
                                    <tr class="<?php echo $is_unread ? 'ov-lead-unread' : ''; ?>">
                                        <td class="admin-num" style="white-space: nowrap; font-size: 0.78rem; color: var(--admin-subtle);">
                                            <?php echo date('d M H:i', strtotime($lead['created_at'])); ?>
                                        </td>
                                        <td>
                                            <strong style="color: var(--admin-ink);"><?php echo sanitize($lead['name']); ?></strong>
                                            <div class="admin-num" style="font-size: 0.76rem; color: var(--admin-muted); margin-top: 2px;">
                                                <?php echo sanitize($lead['phone']); ?>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-info"><?php echo sanitize($lead['service']); ?></span></td>
                                        <td>
                                            <span class="badge <?php echo $badge_cls; ?>">
                                                <span class="ov-dot" style="background: <?php echo $dot_color; ?>;" aria-hidden="true"></span>
                                                <?php echo sanitize($lead['status']); ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right; white-space: nowrap;">
                                            <?php if ($wa_url !== ''): ?>
                                                <a href="<?php echo sanitize($wa_url); ?>" target="_blank" rel="noopener" class="ov-wa-btn" title="Chat WhatsApp prospek">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                                    WA
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="5">
                                            <div class="ov-empty">Belum ada pendaftaran leads yang masuk.</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Right Column: Top Traffic Channels + Recent Admin Activity -->
                <div class="ov-side-stack">
                    <!-- Top Channels (7 Days) -->
                    <section class="admin-card" style="margin-bottom: 0;" aria-label="Sumber Traffic 7 Hari Terakhir">
                        <div class="ov-card-head">
                            <div class="ov-card-title-wrap">
                                <span class="ov-card-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                                </span>
                                <div>
                                    <h2>Top Sumber Traffic (7 Hari)</h2>
                                    <p class="ov-card-sub">Perbandingan channel kunjungan terbaru</p>
                                </div>
                            </div>
                            <a href="<?php echo BASE_URL; ?>admin/analytics.php?range=7d" class="ov-link-more">
                                Detail
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </a>
                        </div>

                        <?php if (!empty($top_channels_7d)): ?>
                            <div class="ov-channel-list">
                                <?php foreach ($top_channels_7d as $ch):
                                    $ck = $ch['traffic_channel'];
                                    $cm = $channel_meta[$ck] ?? ['label' => ucfirst($ck), 'bar' => '#64748b'];
                                    $pv = (int) $ch['page_views'];
                                    $cv = (int) $ch['conversions'];
                                    $pct = $total_channel_views_7d > 0 ? round(($pv / $total_channel_views_7d) * 100, 1) : 0;
                                ?>
                                    <div class="ov-channel-row">
                                        <div class="ov-channel-top">
                                            <span class="ov-channel-name">
                                                <span class="ov-dot" style="background: <?php echo $cm['bar']; ?>;" aria-hidden="true"></span>
                                                <?php echo sanitize($cm['label']); ?>
                                            </span>
                                            <span class="ov-channel-meta">
                                                <strong><?php echo number_format($pv); ?></strong> views (<?php echo $pct; ?>%)<?php if ($cv > 0): ?> · <strong style="color:#047857;"><?php echo $cv; ?></strong> conv<?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="ov-bar-track" aria-hidden="true">
                                            <div class="ov-bar-fill" style="width: <?php echo min(100, $pct); ?>%; background: <?php echo $cm['bar']; ?>;"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="ov-empty">Belum ada data kunjungan dalam 7 hari terakhir.</div>
                        <?php endif; ?>
                    </section>

                    <!-- Recent Admin Activity -->
                    <section class="admin-card" style="margin-bottom: 0;" aria-label="Aktivitas Admin Terbaru">
                        <div class="ov-card-head">
                            <div class="ov-card-title-wrap">
                                <span class="ov-card-icon" style="background: #f5f3ff; color: #7c3aed;" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                </span>
                                <div>
                                    <h2>Aktivitas Admin Terbaru</h2>
                                    <p class="ov-card-sub">Audit log 5 aksi terakhir di CMS</p>
                                </div>
                            </div>
                            <a href="<?php echo BASE_URL; ?>admin/logs.php" class="ov-link-more">
                                Semua Log
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </a>
                        </div>

                        <?php if (!empty($recent_activities)): ?>
                            <ul class="ov-activity-list">
                                <?php foreach ($recent_activities as $act):
                                    $act_color = '#2563eb';
                                    if (strpos($act['action'], 'delete') !== false || strpos($act['action'], 'failed') !== false) {
                                        $act_color = '#dc2626';
                                    } elseif (strpos($act['action'], 'create') !== false || strpos($act['action'], 'login') !== false) {
                                        $act_color = '#059669';
                                    }
                                ?>
                                    <li class="ov-activity-item">
                                        <span class="ov-activity-dot" style="background: <?php echo $act_color; ?>;" aria-hidden="true"></span>
                                        <div class="ov-activity-body">
                                            <div class="ov-activity-text">
                                                <strong><?php echo sanitize($act['username'] ?: 'Sistem'); ?></strong> — <?php echo sanitize($act['details'] ?: $act['action']); ?>
                                            </div>
                                            <div class="ov-activity-meta">
                                                <?php echo date('d M H:i', strtotime($act['created_at'])); ?> · <?php echo sanitize($act['action']); ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="ov-empty">Belum ada log aktivitas tercatat.</div>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

<?php else: ?>

<!-- Modern Enterprise Login Screen -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Urban Office CMS</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
    <style>
        .login-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 26px;
        }
        .login-brand-mark {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            box-shadow: 0 8px 20px rgba(30, 64, 175, 0.28);
        }
        .login-brand h1 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--admin-ink);
            letter-spacing: -0.02em;
        }
        .login-brand p {
            margin: 5px 0 0;
            font-size: 0.86rem;
            color: var(--admin-muted);
        }
        .login-field {
            margin-bottom: 18px;
        }
        .login-pass-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .login-pass-wrap .form-control {
            padding-right: 44px;
        }
        .login-pass-toggle {
            position: absolute;
            right: 6px;
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 7px;
            background: transparent;
            color: var(--admin-subtle);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 140ms ease, color 140ms ease;
        }
        .login-pass-toggle:hover {
            background: var(--admin-surface-muted);
            color: var(--admin-ink);
        }
        .login-submit {
            width: 100%;
            padding: 11px 16px;
            font-size: 0.94rem;
            margin-top: 6px;
        }
        .login-back {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 0.82rem;
            color: var(--admin-muted);
            text-decoration: none;
        }
        .login-back:hover {
            color: var(--admin-primary);
            text-decoration: underline;
        }
    </style>
</head>
<body class="admin-body login-shell">
    <div class="login-card">
        <div class="login-brand">
            <span class="login-brand-mark" aria-hidden="true">UO</span>
            <h1>Urban Office CMS</h1>
            <p>Masuk untuk mengelola konten, leads &amp; analytics</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="admin-alert admin-alert-error" role="alert" style="margin-bottom: 20px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span><?php echo sanitize($error); ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <?php echo csrf_field(); ?>

            <div class="login-field">
                <label class="form-label" for="login-username">Username</label>
                <input type="text" id="login-username" name="username" class="form-control" placeholder="Masukkan username admin" autocomplete="username" required autofocus>
            </div>

            <div class="login-field">
                <label class="form-label" for="login-password">Password</label>
                <div class="login-pass-wrap">
                    <input type="password" id="login-password" name="password" class="form-control" placeholder="••••••••" autocomplete="current-password" required>
                    <button type="button" class="login-pass-toggle" id="toggleLoginPassword" aria-label="Tampilkan password" aria-pressed="false">
                        <svg id="eyeShowIcon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <svg id="eyeHideIcon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </button>
                </div>
            </div>

            <button type="submit" name="login" value="1" class="btn-admin btn-admin-primary login-submit">
                Masuk Dashboard
            </button>
        </form>

        <a href="<?php echo BASE_URL; ?>" class="login-back">&larr; Kembali ke website Urban Office</a>
    </div>

    <script>
    (function () {
        var btn = document.getElementById('toggleLoginPassword');
        var input = document.getElementById('login-password');
        var showIcon = document.getElementById('eyeShowIcon');
        var hideIcon = document.getElementById('eyeHideIcon');
        if (!btn || !input) return;

        btn.addEventListener('click', function () {
            var reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            btn.setAttribute('aria-pressed', reveal ? 'true' : 'false');
            btn.setAttribute('aria-label', reveal ? 'Sembunyikan password' : 'Tampilkan password');
            showIcon.style.display = reveal ? 'none' : '';
            hideIcon.style.display = reveal ? '' : 'none';
        });
    })();
    </script>
</body>
</html>
<?php endif; ?>
