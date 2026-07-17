<?php
/**
 * WordPress Importer Migrator Script
 * Migrates posts, categories, tags, images, and Yoast SEO meta tags from a WordPress database.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

// CLI or Local Admin only validation
if (php_sapi_name() !== 'cli' && (!empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access restricted to localhost or command line.');
}

// WordPress Database Credentials configuration
define('WP_DB_HOST', 'localhost');
define('WP_DB_PORT', '3306');
define('WP_DB_NAME', 'wordpress_db'); // Replace with your WordPress db name
define('WP_DB_USER', 'root');
define('WP_DB_PASS', '');
define('WP_TABLE_PREFIX', 'wp_'); // WordPress table prefix

try {
    // 1. Connect to WordPress Database
    $wp_dsn = "mysql:host=" . WP_DB_HOST . ";port=" . WP_DB_PORT . ";dbname=" . WP_DB_NAME . ";charset=utf8mb4";
    $wp_options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $wp_db = new PDO($wp_dsn, WP_DB_USER, WP_DB_PASS, $wp_options);
    $dest_db = Database::getConnection();
    
    echo "Successfully connected to both Source WordPress and Destination databases.\n";
    echo "Starting migration process...\n\n";

    // Disable Foreign Key checks during migration
    $dest_db->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 2. Fetch and Migrate Categories & Tags
    // In WordPress, both categories and tags reside in wp_terms, distinguished by wp_term_taxonomy.taxonomy
    $taxonomies = ['category' => 'categories', 'post_tag' => 'tags'];
    
    foreach ($taxonomies as $wp_tax => $dest_table) {
        echo "Migrating $dest_table...\n";
        
        $sql = "SELECT t.term_id, t.name, t.slug FROM " . WP_TABLE_PREFIX . "terms t 
                JOIN " . WP_TABLE_PREFIX . "term_taxonomy tt ON t.term_id = tt.term_id 
                WHERE tt.taxonomy = ?";
        
        $stmt = $wp_db->prepare($sql);
        $stmt->execute([$wp_tax]);
        $terms = $stmt->fetchAll();

        $count = 0;
        foreach ($terms as $term) {
            // Check if exists in destination
            $check = Database::fetch("SELECT id FROM $dest_table WHERE id = ? OR slug = ?", [$term['term_id'], $term['slug']]);
            if (!$check) {
                Database::insert("INSERT INTO $dest_table (id, name, slug) VALUES (?, ?, ?)", [
                    $term['term_id'],
                    $term['name'],
                    $term['slug']
                ]);
                $count++;
            }
        }
        echo "Successfully imported $count new records into $dest_table.\n\n";
    }

    // 3. Fetch and Migrate Posts
    echo "Migrating blog articles (wp_posts)...\n";
    
    // Author fallback: map to the seeded admin user (ID: 1)
    $author_id = 1;
    
    $posts_sql = "SELECT id, post_author, post_title, post_name, post_excerpt, post_content, post_date, post_modified 
                  FROM " . WP_TABLE_PREFIX . "posts 
                  WHERE post_type = 'post' AND post_status = 'publish'";
    
    $posts_stmt = $wp_db->query($posts_sql);
    $wp_posts = $posts_stmt->fetchAll();

    $post_count = 0;
    foreach ($wp_posts as $wp_post) {
        // Check if exists in destination
        $check = Database::fetch("SELECT id FROM posts WHERE id = ? OR slug = ?", [$wp_post['id'], $wp_post['post_name']]);
        if ($check) {
            continue; // Skip already imported articles
        }

        // Clean Elementor codes and Gutenberg shortcode structures from content
        $content = $wp_post['post_content'];
        $content = preg_replace('/\[\/?elementor-template.*?\]/', '', $content); // Strip Elementor shortcodes
        $content = preg_replace('/<!--\s*\/?wp:.*?\s*-->/', '', $content);      // Strip Gutenberg comments
        
        // Fetch Yoast SEO Meta tags
        $meta_title = '';
        $meta_description = '';
        
        $seo_stmt = $wp_db->prepare("SELECT meta_key, meta_value FROM " . WP_TABLE_PREFIX . "postmeta WHERE post_id = ? AND meta_key IN ('_yoast_wpseo_title', '_yoast_wpseo_metadesc', '_thumbnail_id')");
        $seo_stmt->execute([$wp_post['id']]);
        $metadata = $seo_stmt->fetchAll();

        $thumbnail_id = 0;
        foreach ($metadata as $meta) {
            if ($meta['meta_key'] === '_yoast_wpseo_title') {
                $meta_title = $meta['meta_value'];
            } elseif ($meta['meta_key'] === '_yoast_wpseo_metadesc') {
                $meta_description = $meta['meta_value'];
            } elseif ($meta['meta_key'] === '_thumbnail_id') {
                $thumbnail_id = intval($meta['meta_value']);
            }
        }

        // Fetch Featured Image URL path from Attachment metadata
        $featured_image = null;
        if ($thumbnail_id > 0) {
            $img_stmt = $wp_db->prepare("SELECT guid FROM " . WP_TABLE_PREFIX . "posts WHERE id = ?");
            $img_stmt->execute([$thumbnail_id]);
            $img_data = $img_stmt->fetch();
            if ($img_data) {
                // Map local path inside upload directories
                $url_parts = parse_url($img_data['guid']);
                $path = $url_parts['path'] ?? '';
                // Resolve structure: /wp-content/uploads/yyyy/mm/filename.jpg -> uploads/yyyy/mm/filename.jpg
                $featured_image = ltrim(strstr($path, 'uploads/'), '/');
                if (empty($featured_image)) {
                    $featured_image = 'uploads/' . basename($path);
                }
            }
        }

        // Clean Yoast meta codes syntax: %%title%%, %%sep%%, %%sitename%%
        if (!empty($meta_title)) {
            $meta_title = str_replace(
                ['%%title%%', '%%sep%%', '%%sitename%%'],
                [$wp_post['post_title'], '-', 'Urban Office'],
                $meta_title
            );
        }

        // Insert Post record
        Database::insert(
            "INSERT INTO posts (id, author_id, title, slug, excerpt, content, featured_image, meta_title, meta_description, status, published_at, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $wp_post['id'],
                $author_id,
                $wp_post['post_title'],
                $wp_post['post_name'],
                $wp_post['post_excerpt'],
                $content,
                $featured_image,
                $meta_title,
                $meta_description,
                'published',
                $wp_post['post_date'],
                $wp_post['post_date'],
                $wp_post['post_modified']
            ]
        );

        // Fetch Taxonomy relationships and map categories & tags
        $rel_stmt = $wp_db->prepare(
            "SELECT term_taxonomy_id FROM " . WP_TABLE_PREFIX . "term_relationships WHERE object_id = ?"
        );
        $rel_stmt->execute([$wp_post['id']]);
        $relationships = $rel_stmt->fetchAll();

        foreach ($relationships as $rel) {
            $term_id = $rel['term_taxonomy_id'];
            
            // Check if term is category
            $is_cat = Database::fetch("SELECT id FROM categories WHERE id = ?", [$term_id]);
            if ($is_cat) {
                Database::insert("INSERT IGNORE INTO post_categories (post_id, category_id) VALUES (?, ?)", [$wp_post['id'], $term_id]);
            }
            
            // Check if term is tag
            $is_tag = Database::fetch("SELECT id FROM tags WHERE id = ?", [$term_id]);
            if ($is_tag) {
                Database::insert("INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)", [$wp_post['id'], $term_id]);
            }
        }

        $post_count++;
    }
    echo "Successfully migrated $post_count blog posts along with their categories, tags, images, and SEO configurations.\n\n";

    // Enable Foreign Key checks back
    $dest_db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Flush HTML Cache files
    clear_page_cache();
    echo "Migration completed successfully. Page cache flushed.\n";

} catch (PDOException $e) {
    die("Database migration error: " . $e->getMessage() . "\n");
}
