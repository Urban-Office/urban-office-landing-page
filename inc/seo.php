<?php
/**
 * SEO & Schema Markup Helper
 * Generates SEO meta tags and JSON-LD Structured Data
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

// Prevent direct access
if (basename($_SERVER['SCRIPT_FILENAME']) === 'seo.php') {
    header('HTTP/1.1 403 Forbidden');
    exit('Direct access forbidden.');
}

/**
 * Retrieve SEO settings for the current page from database
 */
function get_page_seo(string $slug): array {
    // Intercept location detail pages
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    if ($request_uri !== '' && strpos($request_uri, '/lokasi-urban-office/') !== false && $slug !== 'lokasi-urban-office' && $slug !== '') {
        // header.php already require_once's locations_data.php at global scope, so a
        // require_once here would be a no-op that never populates a local $locations_db,
        // silently dropping this page back to the generic default. Pull the global.
        global $locations_db;
        if (!isset($locations_db) || !is_array($locations_db)) {
            $locations_data_path = dirname(__FILE__) . '/locations_data.php';
            if (file_exists($locations_data_path)) {
                require $locations_data_path;
            }
        }
        if (isset($locations_db[$slug])) {
            $branch = $locations_db[$slug];
            return [
                'title' => $branch['seo']['title'],
                'meta_description' => $branch['seo']['description'],
                'canonical_url' => BASE_URL . 'lokasi-urban-office/' . $slug . '/',
                'og_title' => $branch['seo']['title'],
                'og_description' => $branch['seo']['description'],
                'og_image' => $branch['image'],
                'schema_faq' => null,
                'is_location_detail' => true,
                'branch_data' => $branch
            ];
        }
    }

    // Virtual Office branch landing pages (/virtual-office-{branch}/):
    // Curated `pages` row wins for title/meta when present; always enrich og_image,
    // FAQPage schema, and LocalBusiness schema from locations_db + VO FAQ helper.
    if (preg_match('/^virtual-office-([a-z0-9-]+)$/', $slug, $vo_match)) {
        $vo_branch_key = $vo_match[1];
        global $locations_db;
        if (!isset($locations_db) || !is_array($locations_db)) {
            $locations_data_path = dirname(__FILE__) . '/locations_data.php';
            if (file_exists($locations_data_path)) {
                require $locations_data_path;
            }
        }
        $vo_b = $locations_db[$vo_branch_key] ?? null;
        $vo_faqs = ($vo_b && function_exists('get_virtual_office_branch_faqs'))
            ? get_virtual_office_branch_faqs($vo_branch_key, $vo_b)
            : null;
        $vo_row = null;
        try {
            $vo_row = Database::fetch("SELECT * FROM pages WHERE slug = ?", [$slug]);
        } catch (Exception $e) {
            // DB not ready — fall through to locations_db below
        }

        if ($vo_row) {
            $vo_row_faqs = !empty($vo_row['schema_faq']) ? json_decode($vo_row['schema_faq'], true) : null;
            $vo_og_img = !empty($vo_row['og_image']) ? (BASE_URL . $vo_row['og_image']) : (!empty($vo_b['image']) ? $vo_b['image'] : $default_seo['og_image']);
            return [
                'title' => $vo_row['meta_title'] ?: $vo_row['title'] . ' - Urban Office',
                'meta_description' => $vo_row['meta_description'] ?: $default_seo['meta_description'],
                'canonical_url' => $vo_row['canonical_url'] ?: BASE_URL . $slug . '/',
                'og_title' => $vo_row['og_title'] ?: $vo_row['meta_title'] ?: $vo_row['title'],
                'og_description' => $vo_row['og_description'] ?: $vo_row['meta_description'],
                'og_image' => function_exists('encode_asset_url') ? encode_asset_url($vo_og_img) : $vo_og_img,
                'schema_faq' => !empty($vo_row_faqs) ? $vo_row_faqs : $vo_faqs,
                'branch_data' => $vo_b,
                'is_service_city' => (bool)$vo_b
            ];
        }
        if ($vo_b && isset($vo_b['seo'])) {
            $vo_og_img = !empty($vo_b['image']) ? $vo_b['image'] : $default_seo['og_image'];
            return [
                'title' => $vo_b['seo']['title'],
                'meta_description' => $vo_b['seo']['description'],
                'canonical_url' => BASE_URL . $slug . '/',
                'og_title' => $vo_b['seo']['title'],
                'og_description' => $vo_b['seo']['description'],
                'og_image' => function_exists('encode_asset_url') ? encode_asset_url($vo_og_img) : $vo_og_img,
                'schema_faq' => $vo_faqs,
                'branch_data' => $vo_b,
                'is_service_city' => true
            ];
        }
    }

    // Per-city Private Office landing pages (/sewa-kantor-{city}/).
    // Curated `pages` row wins when present; we always attach per-city LocalBusiness
    // & FAQPage schema from the branch + get_private_office_city_faqs().
    if (preg_match('/^sewa-kantor-([a-z0-9-]+)$/', $slug, $sk_match)) {
        $sk_city_slug = $sk_match[1];
        $sk_branches = function_exists('service_city_branches') ? service_city_branches('private-office', $sk_city_slug) : [];
        $sk_branch = !empty($sk_branches) ? $sk_branches[0] : null;
        $sk_faqs = ($sk_branch && function_exists('get_private_office_city_faqs'))
            ? get_private_office_city_faqs($sk_city_slug, $sk_branch)
            : null;
        $sk_row = null;
        try {
            $sk_row = Database::fetch("SELECT * FROM pages WHERE slug = ?", [$slug]);
        } catch (Exception $e) {
            // DB not ready — fall through to generated SEO below
        }
        $sk_og_img = '';
        if ($sk_row) {
            $sk_row_faqs = !empty($sk_row['schema_faq']) ? json_decode($sk_row['schema_faq'], true) : null;
            $sk_og_img = $sk_row['og_image'] ? (BASE_URL . $sk_row['og_image']) : (!empty($sk_branch['image']) ? $sk_branch['image'] : $default_seo['og_image']);
            return [
                'title' => $sk_row['meta_title'] ?: $sk_row['title'] . ' - Urban Office',
                'meta_description' => $sk_row['meta_description'] ?: $default_seo['meta_description'],
                'canonical_url' => $sk_row['canonical_url'] ?: BASE_URL . $slug . '/',
                'og_title' => $sk_row['og_title'] ?: $sk_row['meta_title'] ?: $sk_row['title'],
                'og_description' => $sk_row['og_description'] ?: $sk_row['meta_description'],
                'og_image' => function_exists('encode_asset_url') ? encode_asset_url($sk_og_img) : $sk_og_img,
                'schema_faq' => !empty($sk_row_faqs) ? $sk_row_faqs : $sk_faqs,
                'branch_data' => $sk_branch,
                'is_service_city' => true
            ];
        }
        $sk_city = ($sk_city_slug === 'jakarta') ? 'Jakarta Timur' : ($sk_branch ? $sk_branch['city'] : ucwords(str_replace('-', ' ', $sk_city_slug)));
        $sk_branch_label = ($sk_branch && function_exists('get_private_office_branch_label')) ? get_private_office_branch_label($sk_city_slug, $sk_branch) : ($sk_branch['short_title'] ?? $sk_city);
        $sk_area = ($sk_city_slug === 'jakarta')
            ? 'Cakung, Pulogadung, Jakarta Timur'
            : ($sk_branch ? ($sk_branch_label . ' (' . $sk_branch['location'] . ')') : $sk_city);
        $sk_short = ($sk_city_slug !== 'jakarta' && $sk_branch_label !== '') ? ' ' . $sk_branch_label : '';
        $sk_title = 'Sewa Kantor ' . $sk_city . $sk_short . ' | Urban Office';
        $sk_desc = 'Sewa kantor privat siap pakai di ' . $sk_area . '. Fully furnished, WiFi fiber 100 Mbps, resepsionis, bebas listrik & IPL.';
        $sk_og_img = !empty($sk_branch['image']) ? $sk_branch['image'] : (BASE_URL . 'assets/images/og-default.png');
        return [
            'title' => $sk_title,
            'meta_description' => $sk_desc,
            'canonical_url' => BASE_URL . $slug . '/',
            'og_title' => $sk_title,
            'og_description' => $sk_desc,
            'og_image' => function_exists('encode_asset_url') ? encode_asset_url($sk_og_img) : $sk_og_img,
            'schema_faq' => $sk_faqs,
            'branch_data' => $sk_branch,
            'is_service_city' => true
        ];
    }

    // Per-city Meeting Room landing pages (/meeting-room-{city}/):
    // Curated `pages` row wins for title/meta when present; always attach per-city
    // LocalBusiness, FAQPage schema, and branch og_image fallback.
    if (preg_match('/^meeting-room-([a-z0-9-]+)$/', $slug, $mr_match)) {
        $mr_city_slug = $mr_match[1];
        $mr_branches = function_exists('service_city_branches') ? service_city_branches('meeting-room', $mr_city_slug) : [];
        $mr_branch = !empty($mr_branches) ? $mr_branches[0] : null;
        $mr_faqs = ($mr_branch && function_exists('get_meeting_room_city_faqs'))
            ? get_meeting_room_city_faqs($mr_city_slug, $mr_branch)
            : null;
        $mr_row = null;
        try {
            $mr_row = Database::fetch("SELECT * FROM pages WHERE slug = ?", [$slug]);
        } catch (Exception $e) {
            // DB not ready — fall through to generated SEO below
        }
        if ($mr_row) {
            $mr_row_faqs = !empty($mr_row['schema_faq']) ? json_decode($mr_row['schema_faq'], true) : null;
            $mr_og_img = !empty($mr_row['og_image']) ? (BASE_URL . $mr_row['og_image']) : (!empty($mr_branch['image']) ? $mr_branch['image'] : $default_seo['og_image']);
            return [
                'title' => $mr_row['meta_title'] ?: $mr_row['title'] . ' - Urban Office',
                'meta_description' => $mr_row['meta_description'] ?: $default_seo['meta_description'],
                'canonical_url' => $mr_row['canonical_url'] ?: BASE_URL . $slug . '/',
                'og_title' => $mr_row['og_title'] ?: $mr_row['meta_title'] ?: $mr_row['title'],
                'og_description' => $mr_row['og_description'] ?: $mr_row['meta_description'],
                'og_image' => function_exists('encode_asset_url') ? encode_asset_url($mr_og_img) : $mr_og_img,
                'schema_faq' => !empty($mr_row_faqs) ? $mr_row_faqs : $mr_faqs,
                'branch_data' => $mr_branch,
                'is_service_city' => (bool)$mr_branch
            ];
        }
        $mr_city = ($mr_city_slug === 'jakarta')
            ? 'Jakarta Timur'
            : ($mr_city_slug === 'surabaya-barat' ? 'Surabaya Barat' : ($mr_branch ? $mr_branch['city'] : ucwords(str_replace('-', ' ', $mr_city_slug))));
        $mr_title = 'Sewa Meeting Room ' . $mr_city . ' Mulai 125Rb | Urban Office';
        $mr_desc = 'Sewa ruang meeting di ' . $mr_city . ' per jam atau harian. Layar LED/proyektor, WiFi cepat, whiteboard & opsi coffee break.';
        $mr_og_img = !empty($mr_branch['image']) ? $mr_branch['image'] : (BASE_URL . 'assets/images/og-default.png');
        return [
            'title' => $mr_title,
            'meta_description' => $mr_desc,
            'canonical_url' => BASE_URL . $slug . '/',
            'og_title' => $mr_title,
            'og_description' => $mr_desc,
            'og_image' => function_exists('encode_asset_url') ? encode_asset_url($mr_og_img) : $mr_og_img,
            'schema_faq' => $mr_faqs,
            'branch_data' => $mr_branch,
            'is_service_city' => (bool)$mr_branch
        ];
    }

    // Coworking Space landing pages (/coworking-space-urban-office/ & /coworking-space-{city}/):
    // Curated `pages` row wins for title/meta when present; always attach per-city
    // LocalBusiness, FAQPage schema, and branch og_image fallback.
    if (preg_match('/^coworking-space-([a-z0-9-]+)$/', $slug, $cs_match)) {
        $cs_city_slug = ($slug === 'coworking-space-urban-office') ? 'surabaya' : $cs_match[1];
        $cs_branches = function_exists('service_city_branches') ? service_city_branches('coworking', $cs_city_slug) : [];
        $cs_branch = !empty($cs_branches) ? $cs_branches[0] : null;
        $cs_faqs = ($cs_branch && function_exists('get_coworking_city_faqs'))
            ? get_coworking_city_faqs($cs_city_slug, $cs_branch)
            : null;
        $cs_row = null;
        try {
            $cs_row = Database::fetch("SELECT * FROM pages WHERE slug = ?", [$slug]);
        } catch (Exception $e) {
            // Fall through
        }
        if ($cs_row) {
            $cs_row_faqs = !empty($cs_row['schema_faq']) ? json_decode($cs_row['schema_faq'], true) : null;
            $cs_og_img = !empty($cs_row['og_image']) ? (BASE_URL . $cs_row['og_image']) : (!empty($cs_branch['image']) ? $cs_branch['image'] : (BASE_URL . 'assets/images/og-default.png'));
            return [
                'title' => $cs_row['meta_title'] ?: $cs_row['title'] . ' - Urban Office',
                'meta_description' => $cs_row['meta_description'] ?: 'Temukan ruang kerja bersama di ' . ucwords(str_replace('-', ' ', $cs_city_slug)),
                'canonical_url' => $cs_row['canonical_url'] ?: BASE_URL . $slug . '/',
                'og_title' => $cs_row['og_title'] ?: $cs_row['meta_title'] ?: $cs_row['title'],
                'og_description' => $cs_row['og_description'] ?: $cs_row['meta_description'],
                'og_image' => function_exists('encode_asset_url') ? encode_asset_url($cs_og_img) : $cs_og_img,
                'schema_faq' => !empty($cs_row_faqs) ? $cs_row_faqs : $cs_faqs,
                'branch_data' => $cs_branch,
                'is_service_city' => (bool)$cs_branch
            ];
        }
        $cs_city = ($cs_city_slug === 'surabaya-timur') ? 'Surabaya Timur' : ($cs_branch ? $cs_branch['city'] : ucwords(str_replace('-', ' ', $cs_city_slug)));
        $cs_title = 'Coworking Space ' . $cs_city . ' 10Rb/Jam | Urban Office';
        $cs_desc = 'Ruang kerja bersama (coworking space) di ' . $cs_city . ' per jam, harian & bulanan. Fasilitas lengkap, kopi/teh & WiFi cepat.';
        $cs_og_img = !empty($cs_branch['image']) ? $cs_branch['image'] : (BASE_URL . 'assets/images/og-default.png');
        return [
            'title' => $cs_title,
            'meta_description' => $cs_desc,
            'canonical_url' => BASE_URL . $slug . '/',
            'og_title' => $cs_title,
            'og_description' => $cs_desc,
            'og_image' => function_exists('encode_asset_url') ? encode_asset_url($cs_og_img) : $cs_og_img,
            'schema_faq' => $cs_faqs,
            'branch_data' => $cs_branch,
            'is_service_city' => (bool)$cs_branch
        ];
    }

    // Event Space landing pages (/event-space-55k-perjam-urbanoffice/ & /event-space-{city}/):
    // Curated `pages` row wins for title/meta when present; always attach per-city
    // LocalBusiness, FAQPage schema, and branch og_image fallback.
    if (preg_match('/^event-space-([a-z0-9-]+)$/', $slug, $es_match)) {
        $es_city_slug = ($slug === 'event-space-55k-perjam-urbanoffice') ? 'surabaya' : $es_match[1];
        $es_branches = function_exists('service_city_branches') ? service_city_branches('event-space', $es_city_slug) : [];
        $es_branch = !empty($es_branches) ? $es_branches[0] : null;
        $es_faqs = ($es_branch && function_exists('get_event_space_city_faqs'))
            ? get_event_space_city_faqs($es_city_slug, $es_branch)
            : null;
        $es_row = null;
        try {
            $es_row = Database::fetch("SELECT * FROM pages WHERE slug = ?", [$slug]);
        } catch (Exception $e) {
            // Fall through
        }
        if ($es_row) {
            $es_row_faqs = !empty($es_row['schema_faq']) ? json_decode($es_row['schema_faq'], true) : null;
            $es_og_img = !empty($es_row['og_image']) ? (BASE_URL . $es_row['og_image']) : (!empty($es_branch['image']) ? $es_branch['image'] : (BASE_URL . 'assets/images/og-default.png'));
            return [
                'title' => $es_row['meta_title'] ?: $es_row['title'] . ' - Urban Office',
                'meta_description' => $es_row['meta_description'] ?: 'Sewa event space murah di ' . ucwords(str_replace('-', ' ', $es_city_slug)),
                'canonical_url' => $es_row['canonical_url'] ?: BASE_URL . $slug . '/',
                'og_title' => $es_row['og_title'] ?: $es_row['meta_title'] ?: $es_row['title'],
                'og_description' => $es_row['og_description'] ?: $es_row['meta_description'],
                'og_image' => function_exists('encode_asset_url') ? encode_asset_url($es_og_img) : $es_og_img,
                'schema_faq' => !empty($es_row_faqs) ? $es_row_faqs : $es_faqs,
                'branch_data' => $es_branch,
                'is_service_city' => (bool)$es_branch
            ];
        }
        $es_city = $es_branch ? $es_branch['city'] : ucwords(str_replace('-', ' ', $es_city_slug));
        $es_title = 'Sewa Event Space ' . $es_city . ' 45Rb/Pax | Urban Office';
        $es_desc = 'Sewa event space & aula di ' . $es_city . ' untuk seminar dan workshop (30–80 pax). Kapasitas fleksibel & perlengkapan audio visual.';
        $es_og_img = !empty($es_branch['image']) ? $es_branch['image'] : (BASE_URL . 'assets/images/og-default.png');
        return [
            'title' => $es_title,
            'meta_description' => $es_desc,
            'canonical_url' => BASE_URL . $slug . '/',
            'og_title' => $es_title,
            'og_description' => $es_desc,
            'og_image' => function_exists('encode_asset_url') ? encode_asset_url($es_og_img) : $es_og_img,
            'schema_faq' => $es_faqs,
            'branch_data' => $es_branch,
            'is_service_city' => (bool)$es_branch
        ];
    }

    $default_seo = [
        'title' => 'Virtual Office & Sewa Kantor 6Rb/Hr | Urban Office',
        'meta_description' => 'Sewa Virtual Office mulai 6Rb/hr, Private Office 4 Jt/bln, Meeting Room & Coworking di Surabaya, Jakarta Timur, Malang, Gresik & Medan.',
        'canonical_url' => BASE_URL . ($slug ? $slug . '/' : ''),
        'og_title' => 'Virtual Office & Sewa Kantor 6Rb/Hr | Urban Office',
        'og_description' => 'Sewa Virtual Office mulai 6Rb/hr, Private Office 4 Jt/bln, Meeting Room & Coworking di Surabaya, Jakarta Timur, Malang, Gresik & Medan.',
        'og_image' => BASE_URL . 'assets/images/branch/merr%20new.webp',
        'schema_faq' => ($slug === '' || $slug === 'index') && function_exists('get_homepage_faqs') ? get_homepage_faqs() : null
    ];

    if ($slug === '' || $slug === 'index') {
        try {
            $home_page = Database::fetch("SELECT * FROM pages WHERE slug = ''");
            if ($home_page) {
                $home_faqs = !empty($home_page['schema_faq']) ? json_decode($home_page['schema_faq'], true) : $default_seo['schema_faq'];
                return [
                    'title' => $home_page['meta_title'] ?: $default_seo['title'],
                    'meta_description' => $home_page['meta_description'] ?: $default_seo['meta_description'],
                    'canonical_url' => $home_page['canonical_url'] ?: BASE_URL,
                    'og_title' => $home_page['og_title'] ?: $home_page['meta_title'] ?: $default_seo['title'],
                    'og_description' => $home_page['og_description'] ?: $home_page['meta_description'] ?: $default_seo['meta_description'],
                    'og_image' => !empty($home_page['og_image']) ? (BASE_URL . $home_page['og_image']) : $default_seo['og_image'],
                    'schema_faq' => $home_faqs
                ];
            }
        } catch (Exception $e) {
            // Fall through to $default_seo
        }
        return $default_seo;
    }

    $fallback_og_map = [
        'sharing-room-office' => BASE_URL . 'assets/images/sharingroom/Sharing%20Room%20Office.webp',
        'pajak-dan-akunting'  => BASE_URL . 'assets/images/branch/urban%20merr%20new.webp',
    ];
    $resolve_fallback_faq = static function (string $s): ?array {
        if ($s === 'sharing-room-office' && function_exists('get_sharing_room_faqs')) {
            return get_sharing_room_faqs();
        }
        if ($s === 'pajak-dan-akunting' && function_exists('get_pajak_akunting_faqs')) {
            return get_pajak_akunting_faqs();
        }
        if (strpos($s, 'pendirian-') === 0 && function_exists('get_pendirian_faqs')) {
            return get_pendirian_faqs($s);
        }
        return null;
    };

    try {
        // First check if it's a blog post slug (if the page is loading a blog article)
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        if ($request_uri !== '' && strpos($request_uri, '/blog/') !== false && $slug !== 'blog' && $slug !== '') {
            $post = Database::fetch("SELECT title, meta_title, meta_description, featured_image, excerpt, content, published_at, created_at, updated_at FROM posts WHERE slug = ? AND status = 'published'", [$slug]);
            if ($post) {
                // Fetch categories and tags for article OG tags and schema
                $article_tags = Database::fetchAll(
                    "SELECT t.name FROM tags t JOIN post_tags pt ON t.id = pt.tag_id WHERE pt.post_id = (SELECT id FROM posts WHERE slug = ? AND status = 'published')",
                    [$slug]
                );
                $article_cats = Database::fetchAll(
                    "SELECT c.name FROM categories c JOIN post_categories pc ON c.id = pc.category_id WHERE pc.post_id = (SELECT id FROM posts WHERE slug = ? AND status = 'published')",
                    [$slug]
                );
                return [
                    'title' => $post['meta_title'] ?: $post['title'] . ' - Urban Office Blog',
                    'meta_description' => $post['meta_description'] ?: ($post['excerpt'] ?: substr(strip_tags($post['content']), 0, 155)),
                    'canonical_url' => BASE_URL . 'blog/' . $slug . '/',
                    'og_title' => $post['meta_title'] ?: $post['title'],
                    'og_description' => $post['meta_description'] ?: ($post['excerpt'] ?: substr(strip_tags($post['content']), 0, 155)),
                    'og_image' => $post['featured_image'] ? (BASE_URL . $post['featured_image']) : $default_seo['og_image'],
                    'schema_faq' => null,
                    'is_article' => true,
                    'post_data' => $post,
                    'article_tags' => array_column($article_tags, 'name'),
                    'article_categories' => array_column($article_cats, 'name')
                ];
            }
        }

        // Fetch from pages table for static landing page metadata
        $page = Database::fetch("SELECT * FROM pages WHERE slug = ?", [$slug]);
        if ($page) {
            $page_faqs = !empty($page['schema_faq']) ? json_decode($page['schema_faq'], true) : null;
            if (empty($page_faqs)) {
                $page_faqs = $resolve_fallback_faq($slug);
            }
            $page_og = $page['og_image'] ? (BASE_URL . $page['og_image']) : ($fallback_og_map[$slug] ?? $default_seo['og_image']);
            return [
                'title' => $page['meta_title'] ?: $page['title'] . ' - Urban Office',
                'meta_description' => $page['meta_description'] ?: $default_seo['meta_description'],
                'canonical_url' => $page['canonical_url'] ?: BASE_URL . ($slug ? $slug . '/' : ''),
                'og_title' => $page['og_title'] ?: $page['meta_title'] ?: $page['title'],
                'og_description' => $page['og_description'] ?: $page['meta_description'],
                'og_image' => $page_og,
                'schema_faq' => $page_faqs
            ];
        }
    } catch (Exception $e) {
        error_log("SEO query failed: " . $e->getMessage());
    }

    // Hardcoded fallback checks (if DB not populated yet or query fails)
    if ($slug === 'pendirian-perorangan-plus-virtual-office') {
        return [
            'title' => 'Pendirian PT Perorangan + VO Resmi | Urban Office',
            'meta_description' => 'Paket bundling pendirian PT Perorangan cepat (±1 minggu) plus Virtual Office alamat resmi. Termasuk SK Kemenkumham, NIB OSS & NPWP.',
            'canonical_url' => BASE_URL . 'pendirian-perorangan-plus-virtual-office/',
            'og_title' => 'Pendirian PT Perorangan + VO Resmi | Urban Office',
            'og_description' => 'Paket bundling pendirian PT Perorangan cepat (±1 minggu) plus Virtual Office alamat resmi. Termasuk SK Kemenkumham, NIB OSS & NPWP.',
            'og_image' => $default_seo['og_image'],
            'schema_faq' => $resolve_fallback_faq($slug)
        ];
    }

    if ($slug === 'pendirian-pt-include-virtual-office') {
        return [
            'title' => 'Jasa Pendirian PT + Virtual Office | Urban Office',
            'meta_description' => 'Paket pendirian PT Badan Usaha + Virtual Office resmi ±1 minggu selesai. Akta notaris, SK Kemenkumham, NIB OSS, NPWP & meeting room.',
            'canonical_url' => BASE_URL . 'pendirian-pt-include-virtual-office/',
            'og_title' => 'Jasa Pendirian PT + Virtual Office | Urban Office',
            'og_description' => 'Paket pendirian PT Badan Usaha + Virtual Office resmi ±1 minggu selesai. Akta notaris, SK Kemenkumham, NIB OSS, NPWP & meeting room.',
            'og_image' => $default_seo['og_image'],
            'schema_faq' => $resolve_fallback_faq($slug)
        ];
    }

    if ($slug === 'pendirian-cv-virtual-office') {
        return [
            'title' => 'Jasa Pendirian CV + Virtual Office | Urban Office',
            'meta_description' => 'Paket bundling pendirian CV + Virtual Office resmi untuk domisili usaha & NIB. Proses cepat ±1 minggu, lengkap akta notaris & SK.',
            'canonical_url' => BASE_URL . 'pendirian-cv-virtual-office/',
            'og_title' => 'Jasa Pendirian CV + Virtual Office | Urban Office',
            'og_description' => 'Paket bundling pendirian CV + Virtual Office resmi untuk domisili usaha & NIB. Proses cepat ±1 minggu, lengkap akta notaris & SK.',
            'og_image' => $default_seo['og_image'],
            'schema_faq' => $resolve_fallback_faq($slug)
        ];
    }

    if ($slug === 'pendirian-pma-plus-virtual-office') {
        return [
            'title' => 'Jasa Pendirian PT PMA + Virtual Office|Urban Office',
            'meta_description' => 'Solusi pendirian PT PMA (investor asing) plus alamat Virtual Office komersial di Indonesia. Termasuk akta, SK Kemenkumham, NIB & NPWP.',
            'canonical_url' => BASE_URL . 'pendirian-pma-plus-virtual-office/',
            'og_title' => 'Jasa Pendirian PT PMA + Virtual Office|Urban Office',
            'og_description' => 'Solusi pendirian PT PMA (investor asing) plus alamat Virtual Office komersial di Indonesia. Termasuk akta, SK Kemenkumham, NIB & NPWP.',
            'og_image' => $default_seo['og_image'],
            'schema_faq' => $resolve_fallback_faq($slug)
        ];
    }

    return $default_seo;
}

/**
 * Renders the full SEO tag structure in the document header
 */
function render_seo_tags(string $slug): void {
    $seo = get_page_seo($slug);

    // Per-request robots override (e.g. thin tag archives set noindex,follow
    // before including the header). Keeps such pages out of the index while
    // still letting Google follow their internal links.
    if (!empty($GLOBALS['seo_robots_override'])) {
        $seo['meta_robots'] = $GLOBALS['seo_robots_override'];
    }
    if (!empty($GLOBALS['seo_title_override'])) {
        $seo['title'] = $GLOBALS['seo_title_override'];
        $seo['og_title'] = $GLOBALS['seo_title_override'];
    }
    if (!empty($GLOBALS['seo_desc_override'])) {
        $seo['meta_description'] = $GLOBALS['seo_desc_override'];
        $seo['og_description'] = $GLOBALS['seo_desc_override'];
    }
    if (!empty($seo['og_image']) && function_exists('encode_asset_url')) {
        $seo['og_image'] = encode_asset_url($seo['og_image']);
    }

    echo '<!-- SEO & Open Graph Tags -->' . "\n";
    echo '<title>' . sanitize($seo['title']) . '</title>' . "\n";
    echo '<meta name="description" content="' . sanitize($seo['meta_description']) . '">' . "\n";
    echo '<link rel="canonical" href="' . sanitize($seo['canonical_url']) . '">' . "\n";
    
    // Open Graph (Facebook / LinkedIn)
    echo '<meta property="og:locale" content="id_ID">' . "\n";
    echo '<meta property="og:type" content="' . (isset($seo['is_article']) ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:title" content="' . sanitize($seo['og_title']) . '">' . "\n";
    echo '<meta property="og:description" content="' . sanitize($seo['og_description']) . '">' . "\n";
    echo '<meta property="og:url" content="' . sanitize($seo['canonical_url']) . '">' . "\n";
    echo '<meta property="og:site_name" content="Urban Office">' . "\n";
    echo '<meta property="og:image" content="' . sanitize($seo['og_image']) . '">' . "\n";
    echo '<meta property="og:image:secure_url" content="' . sanitize($seo['og_image']) . '">' . "\n";
    echo '<meta property="og:image:width" content="1200">' . "\n";
    echo '<meta property="og:image:height" content="630">' . "\n";
    
    // Twitter Cards
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . sanitize($seo['og_title']) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . sanitize($seo['og_description']) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . sanitize($seo['og_image']) . '">' . "\n";

    // Article-specific OG tags (for Google/Bing rich results & social sharing)
    if (isset($seo['is_article']) && !empty($seo['post_data'])) {
        $post = $seo['post_data'];
        $pub_time = $post['published_at'] ?: $post['created_at'];
        $mod_time = $post['updated_at'] ?: $pub_time;
        echo '<meta property="article:published_time" content="' . date('c', strtotime($pub_time)) . '">' . "\n";
        echo '<meta property="article:modified_time" content="' . date('c', strtotime($mod_time)) . '">' . "\n";
        echo '<meta property="article:author" content="' . BASE_URL . '">' . "\n";
        echo '<meta property="article:publisher" content="https://www.facebook.com/urbanoffice.co.id/">' . "\n";
        // Article sections (categories)
        if (!empty($seo['article_categories'])) {
            foreach ($seo['article_categories'] as $cat_name) {
                echo '<meta property="article:section" content="' . sanitize($cat_name) . '">' . "\n";
            }
        }
        // Article tags
        if (!empty($seo['article_tags'])) {
            foreach ($seo['article_tags'] as $tag_name) {
                echo '<meta property="article:tag" content="' . sanitize($tag_name) . '">' . "\n";
            }
        }
    }

    // Meta Robots directive (per-page control)
    if (isset($seo['meta_robots']) && !empty($seo['meta_robots'])) {
        echo '<meta name="robots" content="' . sanitize($seo['meta_robots']) . '">' . "\n";
    } else {
        // Default: allow indexing with large image previews for Google Discover
        echo '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">' . "\n";
    }
}

/**
 * Renders JSON-LD Schema Markups (Organization, LocalBusiness, Breadcrumbs, FAQ, and Article)
 */
function render_schema_markup(string $slug): void {
    $seo = get_page_seo($slug);
    $schemas = [];

    // 1. Organization Schema (Rendered on Homepage)
    if ($slug === '' || $slug === 'index' || $slug === '/') {
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Urban Office',
            'legalName' => 'PT. Urban Kreasi Bersama',
            'url' => BASE_URL,
            'logo' => BASE_URL . 'assets/images/logo.png',
            'description' => 'Urban Office adalah penyedia layanan ruang kerja profesional di Indonesia. Menyediakan Virtual Office, Private Office, Meeting Room, Coworking Space, Event Space, serta layanan pendirian badan usaha dan perpajakan.',
            'foundingDate' => '2019',
            'slogan' => 'All-In-One Business Solution',
            'areaServed' => [
                '@type' => 'Country',
                'name' => 'Indonesia'
            ],
            'numberOfEmployees' => [
                '@type' => 'QuantitativeValue',
                'minValue' => 50,
                'maxValue' => 200
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Jl. Dr. Ir. H. Soekarno No.470, Kedung Baruk',
                'addressLocality' => 'Surabaya',
                'addressRegion' => 'Jawa Timur',
                'postalCode' => '60298',
                'addressCountry' => 'ID'
            ],
            'sameAs' => [
                'https://www.facebook.com/urbanoffice.co.id/',
                'https://www.instagram.com/urbanoffice.co.id/',
                'https://x.com/urbanofficecoid',
                'https://id.linkedin.com/company/urbanofficeid',
                'https://www.youtube.com/channel/UCxIPwkXzEu-0mmBlIZL2yYw'
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+62-31-87855578',
                'contactType' => 'customer service',
                'areaServed' => 'ID',
                'availableLanguage' => ['Indonesian', 'English']
            ]
        ];

        // WebSite Schema with SearchAction
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Urban Office',
            'url' => BASE_URL,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => BASE_URL . 'blog/?q={search_term_string}',
                'query-input' => 'required name=search_term_string'
            ]
        ];

        // Local Business (Physical Office Headquarter)
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => 'Gedung Urban Office Surabaya',
            'image' => BASE_URL . 'assets/images/branch/urban%20merr%20new.webp',
            '@id' => BASE_URL . '#localbusiness',
            'url' => BASE_URL,
            'telephone' => '+623187855578',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Jl. Dr. Ir. H. Soekarno No.470, Kedung Baruk',
                'addressLocality' => 'Surabaya',
                'addressRegion' => 'Jawa Timur',
                'postalCode' => '60298',
                'addressCountry' => 'ID'
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => -7.318898,
                'longitude' => 112.782806
            ],
            'openingHoursSpecification' => [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'opens' => '08:00',
                'closes' => '17:00'
            ]
        ];
    }

    // 2. Breadcrumbs Schema
    if ($slug !== '' && $slug !== 'index' && $slug !== '/') {
        $breadcrumbs = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => BASE_URL
                ]
            ]
        ];

        if (isset($seo['is_article'])) {
            $breadcrumbs['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Blog',
                'item' => BASE_URL . 'blog/'
            ];
            $breadcrumbs['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $seo['title'],
                'item' => $seo['canonical_url']
            ];
        } elseif (isset($seo['is_location_detail']) && !empty($seo['branch_data'])) {
            $breadcrumbs['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Lokasi',
                'item' => BASE_URL . 'lokasi-urban-office/'
            ];
            $breadcrumbs['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $seo['branch_data']['short_title'],
                'item' => $seo['canonical_url']
            ];
        } else {
            $breadcrumbs['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => str_replace('-', ' ', ucfirst($slug)),
                'item' => $seo['canonical_url']
            ];
        }
        $schemas[] = $breadcrumbs;
    }

    // 2.5 LocalBusiness Schema for Location Details & Per-City Service Landing Pages
    if ((isset($seo['is_location_detail']) || !empty($seo['is_service_city'])) && !empty($seo['branch_data'])) {
        $branch = $seo['branch_data'];
        $lb_name = (!empty($seo['is_service_city']) && in_array($slug, ['sewa-kantor-jakarta', 'meeting-room-jakarta', 'virtual-office-jakarta-timur'], true))
            ? 'Urban Office Jakarta Timur'
            : $branch['title'];
        $lb_img = function_exists('encode_asset_url') ? encode_asset_url($branch['image']) : $branch['image'];
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $lb_name,
            'image' => $lb_img,
            '@id' => $seo['canonical_url'] . '#localbusiness',
            'url' => $seo['canonical_url'],
            'telephone' => '+623187855578',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $branch['address'],
                'addressLocality' => $branch['city'],
                'addressCountry' => 'ID'
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $branch['rating'],
                'reviewCount' => $branch['reviews_count']
            ]
        ];
    }

    // 3. FAQ Schema
    if (!empty($seo['schema_faq'])) {
        $faq_schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => []
        ];
        foreach ($seo['schema_faq'] as $faq) {
            $faq_schema['mainEntity'][] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer']
                ]
            ];
        }
        $schemas[] = $faq_schema;
    }

    // 4. Article Schema (Blog details) with enhanced properties for GEO
    if (isset($seo['is_article']) && !empty($seo['post_data'])) {
        $post = $seo['post_data'];
        
        // Build 'about' entities from categories
        $about_entities = [];
        if (!empty($seo['article_categories'])) {
            foreach ($seo['article_categories'] as $cat_name) {
                $about_entities[] = [
                    '@type' => 'Thing',
                    'name' => $cat_name
                ];
            }
        }

        // Build 'mentions' from tags
        $mention_entities = [];
        if (!empty($seo['article_tags'])) {
            foreach ($seo['article_tags'] as $tag_name) {
                $mention_entities[] = [
                    '@type' => 'Thing',
                    'name' => $tag_name
                ];
            }
        }

        $article_schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $seo['canonical_url']
            ],
            'headline' => $post['title'],
            'image' => $seo['og_image'],
            'datePublished' => $post['published_at'] ?: $post['created_at'],
            'dateModified' => $post['updated_at'] ?: $post['created_at'],
            'author' => [
                '@type' => 'Organization',
                'name' => 'Urban Office Editor',
                'url' => BASE_URL
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Urban Office',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => BASE_URL . 'assets/images/logo.png'
                ]
            ],
            'description' => $seo['meta_description'],
            'inLanguage' => 'id',
            'isPartOf' => [
                '@type' => 'Blog',
                'name' => 'Urban Office Blog',
                'url' => BASE_URL . 'blog/'
            ]
        ];

        // Add 'about' entities if categories exist
        if (!empty($about_entities)) {
            $article_schema['about'] = count($about_entities) === 1 ? $about_entities[0] : $about_entities;
        }

        // Add 'mentions' entities if tags exist
        if (!empty($mention_entities)) {
            $article_schema['mentions'] = count($mention_entities) === 1 ? $mention_entities[0] : $mention_entities;
        }

        // Add keywords from tags
        if (!empty($seo['article_tags'])) {
            $article_schema['keywords'] = implode(', ', $seo['article_tags']);
        }

        $schemas[] = $article_schema;
    }

    // 2.6 Service + Offer Schema (transparent IDR pricing for Search & AI engines)
    $svc_offer_catalog = [
        'virtual-office' => ['name' => 'Virtual Office Urban Office', 'low' => '180000', 'high' => '900000', 'desc' => 'Sewa alamat bisnis prestisius legalitas PT/CV, NIB & NPWP plus akses meeting room bulanan.'],
        'sewa-kantor'    => ['name' => 'Private Office & Serviced Office', 'low' => '4000000', 'high' => '7500000', 'desc' => 'Ruang kantor privat fully furnished siap pakai termasuk WiFi fiber 100 Mbps, resepsionis, AC, listrik & IPL.'],
        'coworking'      => ['name' => 'Coworking Space Urban Office', 'low' => '10000', 'high' => '750000', 'desc' => 'Sewa meja kerja fleksibel per jam, harian, dan bulanan dengan WiFi cepat serta free flow kopi/teh.'],
        'event-space'    => ['name' => 'Sewa Event Space & Aula Seminar', 'low' => '45000', 'high' => '90000', 'desc' => 'Ruang seminar, workshop & gathering kapasitas 30-80 orang dengan proyektor HD dan sound system.'],
        'sharing-room'   => ['name' => 'Sharing Room Office Dedicated Desk', 'low' => '1000000', 'high' => '12000000', 'desc' => 'Meja kerja khusus terdedikasi dalam ruang kantor bersama ber-AC, WiFi cepat & bebas IPL.'],
    ];
    foreach ($svc_offer_catalog as $svc_prefix => $svc_spec) {
        if (strpos($slug, $svc_prefix) === 0) {
            $area_name = !empty($seo['branch_data']['city']) ? $seo['branch_data']['city'] : 'Indonesia';
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'Service',
                'name' => $svc_spec['name'] . ($area_name !== 'Indonesia' ? ' ' . $area_name : ''),
                'description' => $svc_spec['desc'],
                'provider' => [
                    '@type' => 'Organization',
                    'name' => 'Urban Office',
                    'url' => BASE_URL
                ],
                'areaServed' => [
                    '@type' => ($area_name === 'Indonesia') ? 'Country' : 'City',
                    'name' => $area_name
                ],
                'offers' => [
                    '@type' => 'AggregateOffer',
                    'priceCurrency' => 'IDR',
                    'lowPrice' => $svc_spec['low'],
                    'highPrice' => $svc_spec['high'],
                    'availability' => 'https://schema.org/InStock',
                    'url' => $seo['canonical_url']
                ]
            ];
            break;
        }
    }

    // 5. Speakable Schema (for voice search / AI assistant readiness on service pages)
    $speakable_pages = [
        'virtual-office-surabaya',
        'sewa-kantor-surabaya',
        'meeting-room-surabaya',
        'coworking-space-urban-office',
        'event-space-55k-perjam-urbanoffice',
        'sharing-room-office',
        'pajak-dan-akunting'
    ];
    if (in_array($slug, $speakable_pages) || preg_match('/^(virtual-office|sewa-kantor|meeting-room|coworking-space|event-space)-[a-z0-9-]+$/', $slug)) {
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $seo['title'],
            'url' => $seo['canonical_url'],
            'speakable' => [
                '@type' => 'SpeakableSpecification',
                'cssSelector' => ['.hero-sec h1', '.section-title', '.faq-question']
            ]
        ];
    }

    // Output all schemas inside JSON script blocks
    foreach ($schemas as $schema) {
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
    }
}
