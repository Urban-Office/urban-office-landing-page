<?php
/**
 * Automation API — Moderate (Approve / Reject)
 * ------------------------------------------------------------------
 * POST /_api/moderate.php   (JSON body, header X-Automation-Token)
 *
 * Triggered by the Telegram approve/reject buttons via n8n.
 *
 * Request JSON:
 * {
 *   "post_id":    123,                    // required
 *   "action":     "approve" | "reject",  // required
 *   "publish_at": "2026-09-02 08:00:00"  // optional (approve only)
 * }
 *
 * APPROVE:
 *   - no publish_at, or publish_at in the past -> status='published', published_at=NOW()/that time
 *   - publish_at in the future                 -> status='scheduled' (site auto-publishes at that time)
 * REJECT:
 *   - status='rejected' (hidden from the public blog, row kept for audit — non-destructive)
 *
 * Cache is cleared on approve so the new article appears immediately.
 */

require_once __DIR__ . '/_bootstrap.php';

api_require_post();
api_require_token();

$in      = api_read_json();
$post_id = (int)($in['post_id'] ?? 0);
$action  = strtolower(trim((string)($in['action'] ?? '')));

if ($post_id <= 0) {
    api_fail(422, "Field 'post_id' wajib dan harus > 0.");
}
if (!in_array($action, ['approve', 'reject'], true)) {
    api_fail(422, "Field 'action' harus 'approve' atau 'reject'.");
}

$post = Database::fetch("SELECT id, slug, status, title FROM posts WHERE id = ?", [$post_id]);
if (!$post) {
    api_fail(404, "Post #{$post_id} tidak ditemukan.");
}

// ---------------------------------------------------------------- REJECT
if ($action === 'reject') {
    Database::query("UPDATE posts SET status = 'rejected' WHERE id = ?", [$post_id]);
    log_activity(null, 'automation_rejected', "Post #{$post_id} ({$post['title']})");
    clear_page_cache(); // in case it had previously been published
    api_ok(['post_id' => $post_id, 'status' => 'rejected']);
}

// ---------------------------------------------------------------- APPROVE
$publish_at = trim((string)($in['publish_at'] ?? ''));

// Idempotency: already live and no reschedule requested -> no-op success.
if ($post['status'] === 'published' && $publish_at === '') {
    api_ok([
        'post_id'    => $post_id,
        'status'     => 'published',
        'idempotent' => true,
        'public_url' => BASE_URL . 'blog/' . $post['slug'] . '/',
    ]);
}

if ($publish_at !== '') {
    $ts = strtotime($publish_at);
    if ($ts === false) {
        api_fail(422, "Format 'publish_at' tidak valid. Gunakan 'Y-m-d H:i:s'.");
    }
    $dt = date('Y-m-d H:i:s', $ts);
    if ($ts > time()) {
        // Future -> let the site's auto-publisher flip it to 'published' at that time.
        Database::query("UPDATE posts SET status = 'scheduled', published_at = ? WHERE id = ?", [$dt, $post_id]);
        $new_status = 'scheduled';
    } else {
        Database::query("UPDATE posts SET status = 'published', published_at = ? WHERE id = ?", [$dt, $post_id]);
        $new_status = 'published';
    }
} else {
    Database::query("UPDATE posts SET status = 'published', published_at = NOW() WHERE id = ?", [$post_id]);
    $new_status = 'published';
}

log_activity(null, 'automation_' . $new_status, "Post #{$post_id} ({$post['title']})");
clear_page_cache();

api_ok([
    'post_id'      => $post_id,
    'status'       => $new_status,
    'published_at' => ($publish_at !== '' ? date('Y-m-d H:i:s', strtotime($publish_at)) : date('Y-m-d H:i:s')),
    'public_url'   => BASE_URL . 'blog/' . $post['slug'] . '/',
]);
