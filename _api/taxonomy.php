<?php
/**
 * Automation API — Taxonomy & Link Targets
 * ------------------------------------------------------------------
 * GET /_api/taxonomy.php   (header X-Automation-Token)
 *
 * Feeds n8n everything it needs to (a) map an article to EXISTING
 * categories/tags and (b) insert relevant internal links:
 *   - categories:    [{id, name, slug}]
 *   - tags:          [{id, name, slug}]
 *   - link_targets:  [{title, slug, url}]  (from the `pages` table — the
 *                     8-branch/product landing pages to link into articles)
 *   - recent_posts:  [{title, slug}]       (last 30, for dedup awareness)
 *
 * Read-only. Safe to call as often as needed.
 */

require_once __DIR__ . '/_bootstrap.php';

api_require_get();
api_require_token();

$categories = Database::fetchAll("SELECT id, name, slug FROM categories ORDER BY name ASC");
$tags       = Database::fetchAll("SELECT id, name, slug FROM tags ORDER BY name ASC");

$pages = Database::fetchAll("SELECT slug, title FROM pages ORDER BY title ASC");
$link_targets = [];
foreach ($pages as $p) {
    $slug = (string)$p['slug'];
    // Skip the blog index itself as an internal-link target for articles.
    if ($slug === 'blog') continue;
    $link_targets[] = [
        'title' => $p['title'],
        'slug'  => $slug,
        'url'   => $slug === '' ? BASE_URL : BASE_URL . $slug . '/',
    ];
}

$recent = Database::fetchAll(
    "SELECT title, slug FROM posts WHERE status <> 'rejected' ORDER BY id DESC LIMIT 30"
);

api_ok([
    'categories'    => array_map(fn($c) => ['id' => (int)$c['id'], 'name' => $c['name'], 'slug' => $c['slug']], $categories),
    'tags'          => array_map(fn($t) => ['id' => (int)$t['id'], 'name' => $t['name'], 'slug' => $t['slug']], $tags),
    'link_targets'  => $link_targets,
    'recent_posts'  => $recent,
    'article_base'  => BASE_URL . 'blog/',
]);
