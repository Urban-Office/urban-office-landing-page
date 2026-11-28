<?php
/**
 * Google Search Console (GSC) Data Syncer
 * Can be called from:
 *   1. Admin panel POST handler (`require_once` + `seo_sync_gsc_data()`)
 *   2. Daily CLI cron job: `php /www/wwwroot/urbanoffice.co.id/inc/sync_gsc.php [--days=90]`
 */

if (PHP_SAPI !== 'cli' && basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'sync_gsc.php') {
    header('HTTP/1.1 403 Forbidden');
    exit('Direct access forbidden.');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/gsc_client.php';

/**
 * Pulls daily page metrics & top queries from GSC API and upserts into MySQL.
 * Automatically chunks date ranges into 30-day windows to avoid the 25k row limit.
 */
function seo_sync_gsc_data(int $days = 90, bool $sync_queries = true): array {
    if (!gsc_is_configured()) {
        throw new RuntimeException('Credential GSC API belum dikonfigurasi di inc/config.php.');
    }

    ensure_page_seo_audit_schema();
    $days = max(7, min(365, $days));

    // Build normalized slug -> page_id map
    $pages = Database::fetchAll("SELECT id, slug FROM pages");
    $slug_map = [];
    foreach ($pages as $p) {
        $norm = strtolower(trim((string)$p['slug'], '/'));
        $slug_map[$norm] = (int)$p['id'];
    }
    if (empty($slug_map)) {
        return ['ok' => true, 'pages_matched' => 0, 'daily_rows' => 0, 'query_rows' => 0, 'start_date' => '', 'end_date' => ''];
    }

    $end_ts   = strtotime(date('Y-m-d'));
    $start_ts = $end_ts - (($days - 1) * 86400);
    $start_date = date('Y-m-d', $start_ts);
    $end_date   = date('Y-m-d', $end_ts);

    // Aggregate multiple URL variants (http/https, trailing slash, UTM query) per (page_id, stat_date)
    $daily_agg = []; // ["{$page_id}|{$date}"] => ['page_id', 'date', 'impr', 'clicks', 'pos_w']
    $matched_page_ids = [];

    $chunk_start = $start_ts;
    while ($chunk_start <= $end_ts) {
        $chunk_end = min($end_ts, $chunk_start + (29 * 86400));
        $rows = gsc_search_analytics(
            date('Y-m-d', $chunk_start),
            date('Y-m-d', $chunk_end),
            ['page', 'date'],
            25000
        );

        foreach ($rows as $r) {
            $keys = $r['keys'] ?? [];
            if (count($keys) < 2) continue;
            $slug = gsc_normalize_url_to_slug((string)$keys[0]);
            if (!isset($slug_map[$slug])) continue;

            $pid  = $slug_map[$slug];
            $date = (string)$keys[1];
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) continue;

            $impr = (int)round((float)($r['impressions'] ?? 0));
            $clk  = (int)round((float)($r['clicks'] ?? 0));
            $pos  = (float)($r['position'] ?? 0);

            $k = $pid . '|' . $date;
            if (!isset($daily_agg[$k])) {
                $daily_agg[$k] = [
                    'page_id' => $pid,
                    'date'    => $date,
                    'impr'    => 0,
                    'clicks'  => 0,
                    'pos_w'   => 0.0,
                    'pos_sum' => 0.0,
                    'cnt'     => 0,
                ];
            }
            $daily_agg[$k]['impr']    += $impr;
            $daily_agg[$k]['clicks']  += $clk;
            $daily_agg[$k]['pos_w']   += ($pos * $impr);
            $daily_agg[$k]['pos_sum'] += $pos;
            $daily_agg[$k]['cnt']     += 1;
            $matched_page_ids[$pid]    = true;
        }

        $chunk_start = $chunk_end + 86400;
    }

    // Upsert daily rows in a single transaction
    $db = Database::getConnection();
    $daily_upserted = 0;
    if (!empty($daily_agg)) {
        $db->beginTransaction();
        try {
            $stmt = $db->prepare(
                "INSERT INTO page_gsc_daily (page_id, stat_date, impressions, clicks, ctr, position)
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    impressions = VALUES(impressions),
                    clicks      = VALUES(clicks),
                    ctr         = VALUES(ctr),
                    position    = VALUES(position)"
            );
            foreach ($daily_agg as $d) {
                $impr = $d['impr'];
                $clk  = $d['clicks'];
                $ctr  = $impr > 0 ? round(($clk / $impr) * 100, 2) : 0.00;
                $pos  = $impr > 0
                    ? round($d['pos_w'] / $impr, 2)
                    : ($d['cnt'] > 0 ? round($d['pos_sum'] / $d['cnt'], 2) : 0.00);
                $stmt->execute([$d['page_id'], $d['date'], $impr, $clk, $ctr, $pos]);
                $daily_upserted++;
            }
            $db->commit();
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    // Sync Top 15 Queries per page (last 28 days)
    $query_upserted = 0;
    if ($sync_queries) {
        $q_days  = 28;
        $q_start = date('Y-m-d', $end_ts - (($q_days - 1) * 86400));
        $q_rows  = gsc_search_analytics($q_start, $end_date, ['page', 'query'], 25000);

        $per_page_q = []; // [page_id][query_lc] => [...]
        foreach ($q_rows as $r) {
            $keys = $r['keys'] ?? [];
            if (count($keys) < 2) continue;
            $slug = gsc_normalize_url_to_slug((string)$keys[0]);
            if (!isset($slug_map[$slug])) continue;

            $pid   = $slug_map[$slug];
            $qtxt  = trim((string)$keys[1]);
            if ($qtxt === '') continue;
            if (mb_strlen($qtxt, 'UTF-8') > 250) {
                $qtxt = mb_substr($qtxt, 0, 250, 'UTF-8');
            }
            $qkey  = mb_strtolower($qtxt, 'UTF-8');
            $impr  = (int)round((float)($r['impressions'] ?? 0));
            $clk   = (int)round((float)($r['clicks'] ?? 0));
            $pos   = (float)($r['position'] ?? 0);

            if (!isset($per_page_q[$pid][$qkey])) {
                $per_page_q[$pid][$qkey] = [
                    'query'   => $qtxt,
                    'impr'    => 0,
                    'clicks'  => 0,
                    'pos_w'   => 0.0,
                    'pos_sum' => 0.0,
                    'cnt'     => 0,
                ];
            }
            $per_page_q[$pid][$qkey]['impr']    += $impr;
            $per_page_q[$pid][$qkey]['clicks']  += $clk;
            $per_page_q[$pid][$qkey]['pos_w']   += ($pos * $impr);
            $per_page_q[$pid][$qkey]['pos_sum'] += $pos;
            $per_page_q[$pid][$qkey]['cnt']     += 1;
            $matched_page_ids[$pid] = true;
        }

        if (!empty($per_page_q)) {
            $db->beginTransaction();
            try {
                $delStmt = $db->prepare("DELETE FROM page_gsc_queries WHERE page_id = ?");
                $insStmt = $db->prepare(
                    "INSERT INTO page_gsc_queries (page_id, query_text, impressions, clicks, ctr, position, period_days)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                foreach ($per_page_q as $pid => $qmap) {
                    $qlist = array_values($qmap);
                    usort($qlist, static function ($a, $b) {
                        $c = $b['clicks'] <=> $a['clicks'];
                        return $c !== 0 ? $c : ($b['impr'] <=> $a['impr']);
                    });
                    $top = array_slice($qlist, 0, 15);
                    $delStmt->execute([$pid]);
                    foreach ($top as $q) {
                        $impr = $q['impr'];
                        $clk  = $q['clicks'];
                        $ctr  = $impr > 0 ? round(($clk / $impr) * 100, 2) : 0.00;
                        $pos  = $impr > 0
                            ? round($q['pos_w'] / $impr, 2)
                            : ($q['cnt'] > 0 ? round($q['pos_sum'] / $q['cnt'], 2) : 0.00);
                        $insStmt->execute([$pid, $q['query'], $impr, $clk, $ctr, $pos, $q_days]);
                        $query_upserted++;
                    }
                }
                $db->commit();
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                throw $e;
            }
        }
    }

    // Record last sync metadata in settings
    $now_str = date('Y-m-d H:i:s');
    $summary = sprintf(
        '%d halaman, %d baris harian (%s s/d %s), %d keyword',
        count($matched_page_ids),
        $daily_upserted,
        $start_date,
        $end_date,
        $query_upserted
    );
    Database::query(
        "INSERT INTO settings (name, value) VALUES ('last_gsc_sync_at', ?) ON DUPLICATE KEY UPDATE value = VALUES(value)",
        [$now_str]
    );
    Database::query(
        "INSERT INTO settings (name, value) VALUES ('last_gsc_sync_summary', ?) ON DUPLICATE KEY UPDATE value = VALUES(value)",
        [$summary]
    );

    return [
        'ok'            => true,
        'pages_matched' => count($matched_page_ids),
        'daily_rows'    => $daily_upserted,
        'query_rows'    => $query_upserted,
        'start_date'    => $start_date,
        'end_date'      => $end_date,
        'synced_at'     => $now_str,
        'summary'       => $summary,
    ];
}

// CLI execution entrypoint (for aaPanel daily cron)
if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)) {
    $days = 30;
    // Check if table is empty -> auto backfill 90 days on first run
    try {
        ensure_page_seo_audit_schema();
        $cnt = Database::fetch("SELECT COUNT(*) AS c FROM page_gsc_daily");
        if (!$cnt || (int)$cnt['c'] === 0) {
            $days = 90;
        }
    } catch (Exception $e) {
        $days = 90;
    }

    foreach ($argv ?? [] as $arg) {
        if (preg_match('/^--days=(\d+)$/', (string)$arg, $m)) {
            $days = max(7, min(365, (int)$m[1]));
        }
    }

    try {
        echo "[" . date('Y-m-d H:i:s') . "] Starting GSC sync ({$days} days) for " . gsc_site_url() . "...\n";
        $res = seo_sync_gsc_data($days, true);
        echo "[" . date('Y-m-d H:i:s') . "] GSC sync OK: {$res['summary']}\n";
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] GSC sync FAILED: " . $e->getMessage() . "\n");
        exit(1);
    }
}
