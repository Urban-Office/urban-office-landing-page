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

    // Intercept Virtual Office branch landing pages (/virtual-office-{branch}/)
    // These branch URLs are virtual routes served by one shared template. Only
    // 'virtual-office-surabaya' has a curated row in the `pages` table; every other
    // branch would otherwise fall through to the generic Surabaya default below,
    // sending a Jakarta visitor a <title> that says Surabaya. Source their SEO from
    // locations_db per branch instead. An admin-curated `pages` row still wins if one
    // exists, so this only fills the gap for branches that lack one.
    if (preg_match('/^virtual-office-([a-z0-9-]+)$/', $slug, $vo_match)) {
        $has_page_row = false;
        try {
            $has_page_row = (bool) Database::fetch("SELECT id FROM pages WHERE slug = ?", [$slug]);
        } catch (Exception $e) {
            // DB not ready — fall through to locations_db below
        }

        if (!$has_page_row) {
            $vo_branch_key = $vo_match[1]; // e.g. 'jakarta', 'jakarta-timur'
            // header.php already require_once's locations_data.php at global scope, which
            // makes a require_once here a no-op that never populates a local $locations_db.
            // Pull the global instead, loading it ourselves only if no one has yet.
            global $locations_db;
            if (!isset($locations_db) || !is_array($locations_db)) {
                $locations_data_path = dirname(__FILE__) . '/locations_data.php';
                if (file_exists($locations_data_path)) {
                    require $locations_data_path;
                }
            }
            if (isset($locations_db[$vo_branch_key]['seo'])) {
                $vo_b = $locations_db[$vo_branch_key];
                return [
                    'title' => $vo_b['seo']['title'],
                    'meta_description' => $vo_b['seo']['description'],
                    'canonical_url' => BASE_URL . $slug . '/',
                    'og_title' => $vo_b['seo']['title'],
                    'og_description' => $vo_b['seo']['description'],
                    'og_image' => $vo_b['image'],
                    'schema_faq' => null
                ];
            }
        }
    }

    // Per-city Private Office landing pages (/sewa-kantor-{city}/), pilot for service SEM.
    // The default 'sewa-kantor-surabaya' keeps its curated `pages` row; other cities get
    // generated, city-specific SEO so each is a distinct, relevant landing page.
    if (preg_match('/^sewa-kantor-([a-z0-9-]+)$/', $slug, $sk_match)) {
        $sk_has_row = false;
        try {
            $sk_has_row = (bool) Database::fetch("SELECT id FROM pages WHERE slug = ?", [$slug]);
        } catch (Exception $e) {
            // DB not ready — fall through to generated SEO below
        }
        if (!$sk_has_row) {
            $sk_city = ucwords(str_replace('-', ' ', $sk_match[1]));
            $sk_title = 'Sewa Kantor ' . $sk_city . ' - Private Office Fully Furnished | Urban Office';
            $sk_desc = 'Sewa kantor privat (serviced office) siap pakai di ' . $sk_city . '. Fully furnished, internet fiber, resepsionis, bebas biaya utilitas, alamat bisnis prestisius.';
            return [
                'title' => $sk_title,
                'meta_description' => $sk_desc,
                'canonical_url' => BASE_URL . $slug . '/',
                'og_title' => $sk_title,
                'og_description' => $sk_desc,
                'og_image' => BASE_URL . 'assets/images/og-default.png',
                'schema_faq' => null
            ];
        }
    }

    // Per-city Meeting Room landing pages (/meeting-room-{city}/), pilot pattern for service SEM.
    if (preg_match('/^meeting-room-([a-z0-9-]+)$/', $slug, $mr_match)) {
        $mr_has_row = false;
        try {
            $mr_has_row = (bool) Database::fetch("SELECT id FROM pages WHERE slug = ?", [$slug]);
        } catch (Exception $e) {
            // DB not ready — fall through to generated SEO below
        }
        if (!$mr_has_row) {
            $mr_city = ucwords(str_replace('-', ' ', $mr_match[1]));
            $mr_title = 'Sewa Ruang Meeting ' . $mr_city . ' - Meeting Room Mulai 125Rb/Jam | Urban Office';
            $mr_desc = 'Sewa ruang meeting (meeting room) di ' . $mr_city . ' per jam atau harian. Layar LED/proyektor, WiFi cepat, whiteboard, free flow air mineral, lokasi strategis.';
            return [
                'title' => $mr_title,
                'meta_description' => $mr_desc,
                'canonical_url' => BASE_URL . $slug . '/',
                'og_title' => $mr_title,
                'og_description' => $mr_desc,
                'og_image' => BASE_URL . 'assets/images/og-default.png',
                'schema_faq' => null
            ];
        }
    }

    // Per-city Coworking Space landing pages (/coworking-space-{city}/)
    if (preg_match('/^coworking-space-([a-z0-9-]+)$/', $slug, $cs_match) && $cs_match[1] !== 'urban-office') {
        try {
            $cs_row = Database::fetch("SELECT * FROM pages WHERE slug = ?", [$slug]);
            if ($cs_row) {
                return [
                    'title' => $cs_row['meta_title'] ?: $cs_row['title'] . ' - Urban Office',
                    'meta_description' => $cs_row['meta_description'] ?: 'Temukan ruang kerja bersama di ' . ucwords(str_replace('-', ' ', $cs_match[1])),
                    'canonical_url' => $cs_row['canonical_url'] ?: BASE_URL . $slug . '/',
                    'og_title' => $cs_row['og_title'] ?: $cs_row['meta_title'] ?: $cs_row['title'],
                    'og_description' => $cs_row['og_description'] ?: $cs_row['meta_description'],
                    'og_image' => $cs_row['og_image'] ? (BASE_URL . $cs_row['og_image']) : BASE_URL . 'assets/images/og-default.png',
                    'schema_faq' => $cs_row['schema_faq'] ? json_decode($cs_row['schema_faq'], true) : null
                ];
            }
        } catch (Exception $e) {
            // Fall through
        }
        $cs_city = ucwords(str_replace('-', ' ', $cs_match[1]));
        $cs_title = 'Coworking Space ' . $cs_city . ' | Harian & Bulanan - Urban Office';
        $cs_desc = 'Temukan ruang kerja bersama (coworking space) di ' . $cs_city . '. Meja kerja fleksibel per jam, harian, atau bulanan dengan fasilitas lengkap & internet cepat.';
        return [
            'title' => $cs_title,
            'meta_description' => $cs_desc,
            'canonical_url' => BASE_URL . $slug . '/',
            'og_title' => $cs_title,
            'og_description' => $cs_desc,
            'og_image' => BASE_URL . 'assets/images/og-default.png',
            'schema_faq' => null
        ];
    }

    // Per-city Event Space landing pages (/event-space-{city}/)
    if (preg_match('/^event-space-([a-z0-9-]+)$/', $slug, $es_match) && $es_match[1] !== '55k-perjam-urbanoffice') {
        try {
            $es_row = Database::fetch("SELECT * FROM pages WHERE slug = ?", [$slug]);
            if ($es_row) {
                return [
                    'title' => $es_row['meta_title'] ?: $es_row['title'] . ' - Urban Office',
                    'meta_description' => $es_row['meta_description'] ?: 'Sewa event space murah di ' . ucwords(str_replace('-', ' ', $es_match[1])),
                    'canonical_url' => $es_row['canonical_url'] ?: BASE_URL . $slug . '/',
                    'og_title' => $es_row['og_title'] ?: $es_row['meta_title'] ?: $es_row['title'],
                    'og_description' => $es_row['og_description'] ?: $es_row['meta_description'],
                    'og_image' => $es_row['og_image'] ? (BASE_URL . $es_row['og_image']) : BASE_URL . 'assets/images/og-default.png',
                    'schema_faq' => $es_row['schema_faq'] ? json_decode($es_row['schema_faq'], true) : null
                ];
            }
        } catch (Exception $e) {
            // Fall through
        }
        $es_city = ucwords(str_replace('-', ' ', $es_match[1]));
        $es_title = 'Sewa Event Space ' . $es_city . ' | Ruang Seminar & Workshop - Urban Office';
        $es_desc = 'Sewa event space murah di ' . $es_city . ' untuk seminar, workshop, dan pelatihan bisnis. Kapasitas fleksibel dengan perlengkapan lengkap.';
        return [
            'title' => $es_title,
            'meta_description' => $es_desc,
            'canonical_url' => BASE_URL . $slug . '/',
            'og_title' => $es_title,
            'og_description' => $es_desc,
            'og_image' => BASE_URL . 'assets/images/og-default.png',
            'schema_faq' => null
        ];
    }

    $default_seo = [
        'title' => 'Sewa Virtual Office & Ruang Kantor Surabaya - Urban Office',
        'meta_description' => 'Sewa Virtual Office Surabaya Murah. Dapatkan alamat bisnis prestisius, gratis pembuatan PT/CV, meeting room, & coworking space di Urban Office.',
        'canonical_url' => BASE_URL . ($slug ? $slug . '/' : ''),
        'og_title' => 'Sewa Virtual Office & Ruang Kantor Surabaya - Urban Office',
        'og_description' => 'Sewa Virtual Office Surabaya Murah. Dapatkan alamat bisnis prestisius, gratis pembuatan PT/CV, meeting room, & coworking space di Urban Office.',
        'og_image' => BASE_URL . 'assets/images/og-default.png',
        'schema_faq' => null
    ];

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
            return [
                'title' => $page['meta_title'] ?: $page['title'] . ' - Urban Office',
                'meta_description' => $page['meta_description'] ?: $default_seo['meta_description'],
                'canonical_url' => $page['canonical_url'] ?: BASE_URL . ($slug ? $slug . '/' : ''),
                'og_title' => $page['og_title'] ?: $page['meta_title'] ?: $page['title'],
                'og_description' => $page['og_description'] ?: $page['meta_description'],
                'og_image' => $page['og_image'] ? (BASE_URL . $page['og_image']) : $default_seo['og_image'],
                'schema_faq' => $page['schema_faq'] ? json_decode($page['schema_faq'], true) : null
            ];
        }
    } catch (Exception $e) {
        error_log("SEO query failed: " . $e->getMessage());
    }

    // Hardcoded fallback checks (if DB not populated yet or query fails)
    if ($slug === 'pendirian-perorangan-plus-virtual-office') {
        return [
            'title' => 'PT Perorangan + Virtual Office Bundling Murah - Urban Office',
            'meta_description' => 'Paket bundling pendirian PT Perorangan terlengkap + sewa Virtual Office dengan alamat bisnis prestisius. Dapatkan SK Kemenkumham, NIB, NPWP, & surat domisili resmi.',
            'canonical_url' => BASE_URL . 'pendirian-perorangan-plus-virtual-office/',
            'og_title' => 'PT Perorangan + Virtual Office Bundling Murah - Urban Office',
            'og_description' => 'Paket bundling pendirian PT Perorangan terlengkap + sewa Virtual Office dengan alamat bisnis prestisius. Dapatkan SK Kemenkumham, NIB, NPWP, & surat domisili resmi.',
            'og_image' => BASE_URL . 'assets/images/og-default.png',
            'schema_faq' => null
        ];
    }

    if ($slug === 'pendirian-pt-include-virtual-office') {
        return [
            'title' => 'Pendirian PT Badan Usaha + Virtual Office Murah - Urban Office',
            'meta_description' => 'Paket bundling pendirian PT Badan Usaha (Persekutuan Modal) terlengkap + sewa Virtual Office dengan alamat bisnis prestisius. Legalitas akta notaris, SK Kemenkumham, NIB, & NPWP resmi.',
            'canonical_url' => BASE_URL . 'pendirian-pt-include-virtual-office/',
            'og_title' => 'Pendirian PT Badan Usaha + Virtual Office Murah - Urban Office',
            'og_description' => 'Paket bundling pendirian PT Badan Usaha (Persekutuan Modal) terlengkap + sewa Virtual Office dengan alamat bisnis prestisius. Legalitas akta notaris, SK Kemenkumham, NIB, & NPWP resmi.',
            'og_image' => BASE_URL . 'assets/images/og-default.png',
            'schema_faq' => null
        ];
    }

    if ($slug === 'pendirian-cv-virtual-office') {
        return [
            'title' => 'Pendirian CV + Virtual Office Bundling Murah - Urban Office',
            'meta_description' => 'Paket bundling pendirian CV (Persekutuan Komanditer) terlengkap + sewa Virtual Office dengan alamat bisnis prestisius. Proses cepat dengan akta notaris, SK Kemenkumham, & NIB.',
            'canonical_url' => BASE_URL . 'pendirian-cv-virtual-office/',
            'og_title' => 'Pendirian CV + Virtual Office Bundling Murah - Urban Office',
            'og_description' => 'Paket bundling pendirian CV (Persekutuan Komanditer) terlengkap + sewa Virtual Office dengan alamat bisnis prestisius. Proses cepat dengan akta notaris, SK Kemenkumham, & NIB.',
            'og_image' => BASE_URL . 'assets/images/og-default.png',
            'schema_faq' => null
        ];
    }

    if ($slug === 'pendirian-pma-plus-virtual-office') {
        return [
            'title' => 'Pendirian PT PMA (Foreign Investment) + Virtual Office - Urban Office',
            'meta_description' => 'Seamless PT PMA (Foreign Owned Enterprise) incorporation in Indonesia with premium Virtual Office address. Complete setup, notarial deed, Kemenkumham approval, and NIB.',
            'canonical_url' => BASE_URL . 'pendirian-pma-plus-virtual-office/',
            'og_title' => 'Pendirian PT PMA (Foreign Investment) + Virtual Office - Urban Office',
            'og_description' => 'Seamless PT PMA (Foreign Owned Enterprise) incorporation in Indonesia with premium Virtual Office address. Complete setup, notarial deed, Kemenkumham approval, and NIB.',
            'og_image' => BASE_URL . 'assets/images/og-default.png',
            'schema_faq' => null
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

        // Local Business (Physical Office Headquarter)
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => 'Gedung Urban Office Surabaya',
            'image' => BASE_URL . 'assets/images/buildings/head-office.webp',
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

    // 2.5 LocalBusiness Schema for Location Details
    if (isset($seo['is_location_detail']) && !empty($seo['branch_data'])) {
        $branch = $seo['branch_data'];
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $branch['title'],
            'image' => $branch['image'],
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
    if (in_array($slug, $speakable_pages) || preg_match('/^virtual-office-[a-z0-9-]+$/', $slug)) {
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
