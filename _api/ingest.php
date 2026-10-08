<?php
/**
 * Automation API — Ingest Draft
 * ------------------------------------------------------------------
 * POST /_api/ingest.php   (JSON body, header X-Automation-Token)
 *
 * Creates an AI-generated article as a DRAFT. Critically, published_at is
 * forced to NULL so the site's auto_publish_scheduled_posts() catch-up rule
 * can never publish it without going through /_api/moderate.php first.
 *
 * Request JSON:
 * {
 *   "title":            "string (required)",
 *   "content":          "HTML string (required)",
 *   "excerpt":          "string (optional)",
 *   "meta_title":       "string (optional)",
 *   "meta_description": "string (optional)",
 *   "slug":             "string (optional; derived from title if omitted)",
 *   "source_url":       "https://... (optional; RSS source, used for dedup)",
 *   "image_url":        "https://... (optional; server downloads it)",
 *   "categories":       ["Virtual Office", 3, ...] (optional; existing only),
 *   "tags":             ["surabaya", ...]          (optional; existing only)
 * }
 *
 * Duplicate guard: if source_url (or the exact title) already belongs to a
 * non-rejected post, returns HTTP 409 without creating anything.
 *
 * Response: { ok, post_id, slug, status:'draft', featured_image,
 *             public_url_after_publish, admin_edit_url,
 *             categories_matched[], tags_matched[], image_warning }
 */

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/lib_content.php';

api_require_post();
api_require_token();

$in = api_read_json();

$title   = trim((string)($in['title'] ?? ''));
$content = (string)($in['content'] ?? '');
if ($title === '' || trim(strip_tags($content)) === '') {
    api_fail(422, "Field 'title' dan 'content' wajib diisi.");
}

$excerpt          = trim((string)($in['excerpt'] ?? ''));
$meta_title       = trim((string)($in['meta_title'] ?? ''));
$meta_description = trim((string)($in['meta_description'] ?? ''));
$source_url       = api_norm_source((string)($in['source_url'] ?? ''));

// Duplicate guard — refuse before spending any work (image download etc.).
$dupe = api_find_duplicate($source_url, $title);
if ($dupe) {
    api_fail(409, 'Duplikat: topik/sumber ini sudah ada sebagai post.', [
        'duplicate'  => true,
        'matched_by' => $dupe['matched_by'],
        'post'       => [
            'id'     => (int)$dupe['id'],
            'slug'   => $dupe['slug'],
            'status' => $dupe['status'],
            'title'  => $dupe['title'],
        ],
    ]);
}

// Slug: from provided value or title, then normalised + made unique.
$slug = trim((string)($in['slug'] ?? ''));
$slug = api_slugify($slug !== '' ? $slug : $title);
$slug = api_unique_slug($slug);

// Featured image (optional). A failure never blocks the draft.
$featured_image = null;
$image_warning  = null;
if (!empty($in['image_url'])) {
    $res            = api_download_image((string)$in['image_url']);
    $featured_image = $res['path'];
    $image_warning  = $res['warning'];
}

// Compose a branded hero (orange card + title text + the photo). Built even
// when no photo downloaded (gradient fallback), so every article gets a
// consistent branded featured image. Non-fatal: keeps the raw photo on failure.
$rawRel  = $featured_image; // downloaded Fal photo (relative) — reused as mid-card bg
$rawAbs  = $rawRel ? DIR_ROOT . $rawRel : null;
$heroAbs = DIR_ROOT . 'assets/images/blog/' . md5(uniqid('hero', true)) . '-hero.jpg';
$heroRel = uo_build_hero_image($title, $rawAbs, $heroAbs);

// Push images to Cloudinary (CDN) when configured; otherwise keep them local.
// Returns null when not configured / on failure, so everything degrades safely.
$cloudHero = $heroRel ? uo_upload_to_cloudinary(DIR_ROOT . $heroRel) : null;
$cloudRaw  = ($rawAbs && is_file($rawAbs)) ? uo_upload_to_cloudinary($rawAbs) : null;

// Featured image: Cloudinary hero → local hero → Cloudinary raw → local raw.
if ($cloudHero)   { $featured_image = $cloudHero; @unlink(DIR_ROOT . $heroRel); }
elseif ($heroRel) { $featured_image = $heroRel; }
elseif ($cloudRaw){ $featured_image = $cloudRaw; }
else              { $featured_image = $rawRel; }

// Mid-card background: Cloudinary raw → local raw URL → none.
if ($cloudRaw)    { $cardBg = $cloudRaw; if ($rawAbs && is_file($rawAbs)) @unlink($rawAbs); }
elseif ($rawRel)  { $cardBg = BASE_URL . $rawRel; }
else              { $cardBg = ''; }

$cta = trim((string)($in['cta'] ?? '')); // optional AI-written soft-sell line

// Inject the relevant-page card mid-article, and append the closing CTA block.
$content = uo_insert_midcontent_card($content, $title, $cardBg, $cta);
$content .= uo_build_end_cta();

$author_id = api_resolve_author_id();

try {
    $post_id = (int)Database::insert(
        "INSERT INTO posts
            (author_id, title, slug, excerpt, content, featured_image, meta_title, meta_description, status, views, source_url, published_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft', 0, ?, NULL)",
        [$author_id, $title, $slug, $excerpt, $content, $featured_image, $meta_title, $meta_description, ($source_url !== '' ? $source_url : null)]
    );
} catch (Exception $e) {
    error_log('Automation ingest insert failed: ' . $e->getMessage());
    api_fail(500, 'Gagal menyimpan draft ke database.');
}

// Map categories / tags to EXISTING ids only (no auto-create).
$cat_ids = api_resolve_term_ids(is_array($in['categories'] ?? null) ? $in['categories'] : [], 'categories');
foreach ($cat_ids as $cid) {
    Database::query("INSERT IGNORE INTO post_categories (post_id, category_id) VALUES (?, ?)", [$post_id, $cid]);
}
$tag_ids = api_resolve_term_ids(is_array($in['tags'] ?? null) ? $in['tags'] : [], 'tags');
foreach ($tag_ids as $tid) {
    Database::query("INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)", [$post_id, $tid]);
}

log_activity(null, 'automation_ingest', "AI draft #{$post_id}: " . $title);
// Note: no cache clear here — a draft is not publicly visible, so there is
// nothing stale to bust. Cache is cleared on approve/publish instead.

api_ok([
    'post_id'                  => $post_id,
    'slug'                     => $slug,
    'status'                   => 'draft',
    'featured_image'           => $featured_image,
    'public_url_after_publish' => BASE_URL . 'blog/' . $slug . '/',
    'admin_edit_url'           => BASE_URL . 'admin/posts.php?action=edit&id=' . $post_id,
    'categories_matched'       => $cat_ids,
    'tags_matched'             => $tag_ids,
    'image_warning'            => $image_warning,
]);
