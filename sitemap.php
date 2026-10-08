<?php
/**
 * Dynamic XML Sitemap Generator
 * Queries all pages, posts, categories, and tags to output W3C-compliant sitemaps
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/functions.php';

header('Content-Type: text/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

try {
    $emitted_urls = [];

    // Helper to output url node if not already emitted
    $emit_url = function($loc, $lastmod = null, $changefreq = 'weekly', $priority = '0.8', $extra = '') use (&$emitted_urls) {
        $loc_norm = rtrim($loc, '/') . '/';
        if (isset($emitted_urls[$loc_norm])) {
            return;
        }
        $emitted_urls[$loc_norm] = true;

        echo "\t<url>\n";
        echo "\t\t<loc>" . sanitize($loc) . "</loc>\n";
        if ($lastmod) {
            echo "\t\t<lastmod>" . $lastmod . "</lastmod>\n";
        }
        echo "\t\t<changefreq>" . $changefreq . "</changefreq>\n";
        echo "\t\t<priority>" . $priority . "</priority>\n";
        if ($extra) {
            echo $extra;
        }
        echo "\t</url>\n";
    };

    // 1. Add static landing pages from DB
    $pages = Database::fetchAll("SELECT slug, updated_at FROM pages");
    foreach ($pages as $page) {
        $slug = $page['slug'];
        $url = BASE_URL . ($slug ? $slug . '/' : '');
        $date = date('c', strtotime($page['updated_at']));
        $priority = ($slug === '') ? '1.0' : '0.8';
        $emit_url($url, $date, 'weekly', $priority);
    }

    // 2. Add published blog articles with image sitemap support
    $posts = Database::fetchAll("SELECT slug, title, featured_image, excerpt, published_at, updated_at FROM posts WHERE status = 'published' ORDER BY published_at DESC");
    foreach ($posts as $post) {
        $url = BASE_URL . 'blog/' . $post['slug'] . '/';
        $date = date('c', strtotime($post['updated_at'] ?: $post['published_at']));
        
        echo "\t<url>\n";
        echo "\t\t<loc>" . sanitize($url) . "</loc>\n";
        echo "\t\t<lastmod>" . $date . "</lastmod>\n";
        echo "\t\t<changefreq>monthly</changefreq>\n";
        echo "\t\t<priority>0.6</priority>\n";

        // Image sitemap tag for featured image (Google Image SEO)
        if (!empty($post['featured_image'])) {
            $img_url = $post['featured_image'];
            // Convert relative path to absolute URL
            if (strpos($img_url, 'http') !== 0) {
                $img_url = BASE_URL . ltrim($img_url, '/');
            }
            echo "\t\t<image:image>\n";
            echo "\t\t\t<image:loc>" . sanitize($img_url) . "</image:loc>\n";
            echo "\t\t\t<image:title>" . sanitize($post['title']) . "</image:title>\n";
            if (!empty($post['excerpt'])) {
                echo "\t\t\t<image:caption>" . sanitize(substr($post['excerpt'], 0, 150)) . "</image:caption>\n";
            }
            echo "\t\t</image:image>\n";
        }

        echo "\t</url>\n";
    }

    // 3. Add Categories
    $categories = Database::fetchAll("SELECT slug FROM categories");
    foreach ($categories as $cat) {
        $url = BASE_URL . 'category/' . $cat['slug'] . '/';
        $emit_url($url, null, 'weekly', '0.4');
    }

    // 4. Tags are intentionally EXCLUDED from the sitemap.
    //    Tag archive pages are thin and set noindex,follow (see blog/tag.php),
    //    so submitting them would only create crawl-budget waste / index bloat.
    //    Categories above remain indexable as the primary taxonomy.

    // 5. Per-city service landing pages (Private Office, Meeting Room, Coworking, Event Space).
    //    $emit_url automatically deduplicates against any rows already present in the `pages` table.
    require_once __DIR__ . '/inc/locations_data.php';
    if (isset($locations_db) && is_array($locations_db)) {
        $sitemap_service_bases = [
            'private-office' => 'sewa-kantor',
            'meeting-room'   => 'meeting-room',
            'coworking'      => 'coworking-space',
            'event-space'    => 'event-space',
        ];
        foreach ($sitemap_service_bases as $sm_cat => $sm_base) {
            $sm_seen = [];
            foreach ($locations_db as $sm_branch) {
                if (empty($sm_branch['pricing']) || empty($sm_branch['city'])) {
                    continue;
                }
                $sm_has = false;
                foreach ($sm_branch['pricing'] as $sm_pkg) {
                    if (isset($sm_pkg['category']) && $sm_pkg['category'] === $sm_cat) {
                        $sm_has = true;
                        break;
                    }
                }
                $sm_city = strtolower($sm_branch['city']);
                if (!$sm_has || $sm_city === 'surabaya' || isset($sm_seen[$sm_city])) {
                    continue;
                }
                $sm_seen[$sm_city] = true;
                $emit_url(BASE_URL . $sm_base . '-' . $sm_city . '/', null, 'weekly', '0.8');
            }
        }
    }

} catch (Exception $e) {
    error_log("Sitemap generation error: " . $e->getMessage());
}

echo '</urlset>';
