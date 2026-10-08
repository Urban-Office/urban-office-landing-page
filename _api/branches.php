<?php
/**
 * Automation API — Branch Facts (for GEO / E-E-A-T)
 * ------------------------------------------------------------------
 * GET /_api/branches.php   (header X-Automation-Token)
 *
 * Exposes a SAFE, trimmed subset of inc/locations_data.php so n8n can feed
 * the LLM real branch facts (address, area, real pricing, advantages, FAQ)
 * when generating location-specific articles — instead of hallucinating them.
 *
 * Excluded on purpose: google_reviews / testimonials (third-party text),
 * gallery, and map_embed iframes (not needed for article copy).
 *
 * Read-only.
 */

require_once __DIR__ . '/_bootstrap.php';

api_require_get();
api_require_token();

// locations_data.php uses BASE_URL and self-blocks only on direct hit; when
// require'd here SCRIPT_FILENAME is branches.php, so the guard passes.
require_once __DIR__ . '/../inc/locations_data.php';

$out = [];
if (isset($locations_db) && is_array($locations_db)) {
    foreach ($locations_db as $key => $b) {
        // Compact pricing: category / name / price / period only.
        $pricing = [];
        foreach (($b['pricing'] ?? []) as $p) {
            $pricing[] = [
                'category' => $p['category'] ?? '',
                'name'     => $p['name'] ?? '',
                'price'    => $p['price'] ?? '',
                'period'   => $p['period'] ?? '',
            ];
        }
        $faq = [];
        foreach (($b['faq'] ?? []) as $f) {
            if (!empty($f['question'])) {
                $faq[] = ['question' => $f['question'], 'answer' => $f['answer'] ?? ''];
            }
        }
        $slug = $b['slug'] ?? (string)$key;
        $out[] = [
            'key'         => (string)$key,
            'slug'        => $slug,
            'name'        => $b['short_title'] ?? ($b['title'] ?? ''),
            'full_title'  => $b['title'] ?? '',
            'city'        => $b['city'] ?? '',
            'location'    => $b['location'] ?? '',
            'nearby'      => $b['nearby'] ?? '',
            'address'     => $b['address'] ?? '',
            'services'    => $b['services'] ?? '',
            'advantages'  => array_values($b['advantages'] ?? []),
            'pricing'     => $pricing,
            'faq'         => $faq,
            // Internal-link target for this branch's detail page.
            'detail_url'  => BASE_URL . 'lokasi-urban-office/' . $slug . '/',
        ];
    }
}

api_ok([
    'count'    => count($out),
    'branches' => $out,
]);
