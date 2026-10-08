<?php
/**
 * Automation API — Duplicate Check
 * ------------------------------------------------------------------
 * GET /_api/check.php?source_url=...&title=...   (header X-Automation-Token)
 *
 * Lets n8n check BEFORE spending LLM/image credits whether a topic has
 * already been turned into a post. Matches by source_url first, then by
 * normalised title. Rejected posts are ignored (a rejected topic may be
 * retried). At least one of source_url / title is required.
 *
 * Response: { ok, duplicate: bool, matched_by: 'source_url'|'title'|null,
 *             post: {id, slug, status, title} | null }
 */

require_once __DIR__ . '/_bootstrap.php';

api_require_get();
api_require_token();

$source_url = trim((string)($_GET['source_url'] ?? ''));
$title      = trim((string)($_GET['title'] ?? ''));

if ($source_url === '' && $title === '') {
    api_fail(422, "Sertakan minimal satu parameter: 'source_url' atau 'title'.");
}

$dupe = api_find_duplicate($source_url, $title);

api_ok([
    'duplicate'  => (bool)$dupe,
    'matched_by' => $dupe['matched_by'] ?? null,
    'post'       => $dupe ? [
        'id'     => (int)$dupe['id'],
        'slug'   => $dupe['slug'],
        'status' => $dupe['status'],
        'title'  => $dupe['title'],
    ] : null,
]);
