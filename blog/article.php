<?php
/**
 * Urban Office - Single Blog Article Template
 */

require_once dirname(dirname(__FILE__)) . '/inc/config.php';
require_once dirname(dirname(__FILE__)) . '/inc/database.php';
require_once dirname(dirname(__FILE__)) . '/inc/functions.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: ' . BASE_URL . 'blog/');
    exit;
}

try {
    // Fetch active published post
    $post = Database::fetch(
        "SELECT p.*, u.username as author_name, u.email as author_email FROM posts p 
         JOIN users u ON p.author_id = u.id 
         WHERE p.slug = ? AND p.status = 'published'",
        [$slug]
    );

    if ($post) {
        $search_patterns = [
            'http://localhost:8000/Clone%20Website%20urban%20hanya%20php/',
            'http://localhost:8000/Clone Website urban hanya php/',
            'http://localhost:8000/',
            'http://localhost/Clone%20Website%20urban%20hanya%20php/',
            'http://localhost/Clone Website urban hanya php/',
            'http://localhost/'
        ];
        
        if (!empty($post['content'])) {
            $post['content'] = str_replace($search_patterns, BASE_URL, $post['content']);
            
            // Fallback: If any image has an alt attribute that is a URL, wrap it in an anchor link safely
            $post['content'] = preg_replace_callback(
                '/<img[^>]+>/i',
                function($matches) {
                    $img_tag = $matches[0];
                    if (preg_match('/alt=["\'](https?:\/\/[^\s"\']+)["\']/i', $img_tag, $alt_matches)) {
                        $url = $alt_matches[1];
                        return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank">' . $img_tag . '</a>';
                    }
                    return $img_tag;
                },
                $post['content']
            );
        }
        
        if (!empty($post['featured_image'])) {
            $post['featured_image'] = str_replace($search_patterns, '', $post['featured_image']);
            $post['featured_image'] = ltrim($post['featured_image'], '/');
        }
    }

    if (!$post) {
        // Return 404 Error page
        header("HTTP/1.1 404 Not Found");
        $page_slug = '404';
        require_once dirname(dirname(__FILE__)) . '/inc/header.php';
        echo '<section class="section container text-center" style="padding:100px 0;">
                <h2>Artikel Tidak Ditemukan</h2>
                <p>Maaf, artikel yang Anda cari tidak tersedia atau telah dipindahkan.</p>
                <a href="'.BASE_URL.'blog/" class="btn btn-primary" style="margin-top:20px;">Kembali ke Blog</a>
              </section>';
        require_once dirname(dirname(__FILE__)) . '/inc/footer.php';
        exit;
    }

    // Set page slug variable for SEO header resolver
    $page_slug = $post['slug'];
    require_once dirname(dirname(__FILE__)) . '/inc/header.php';

    // Increment view counter once per user session to prevent refresh spamming
    if (!isset($_SESSION['viewed_posts'])) {
        $_SESSION['viewed_posts'] = [];
    }
    if (!in_array($post['id'], $_SESSION['viewed_posts'])) {
        Database::query("UPDATE posts SET views = views + 1 WHERE id = ?", [$post['id']]);
        $_SESSION['viewed_posts'][] = $post['id'];
    }
    $view_count = Database::fetch("SELECT views FROM posts WHERE id = ?", [$post['id']])['views'];

    $pub_date = date('d M Y', strtotime($post['published_at'] ?: $post['created_at']));
    $image_path = $post['featured_image'] ? BASE_URL . $post['featured_image'] : BASE_URL . 'assets/images/og-default.png';

    // Check if featured image is already inserted inside the article body content to prevent double rendering
    $featured_in_content = false;
    if ($post['featured_image']) {
        $img_filename = basename($post['featured_image']);
        if (strpos($post['content'], $img_filename) !== false) {
            $featured_in_content = true;
        }
    }

    // ALSO check if the post content starts with any image tag to avoid stacked header banners
    if (!$featured_in_content && !empty($post['content'])) {
        $trimmed_content = trim($post['content']);
        if (preg_match('/^(?:<p[^>]*>|<div[^>]*>|<figure[^>]*>)?\s*<img\s+/i', $trimmed_content)) {
            $featured_in_content = true;
        }
    }

    // Fetch tags associated with this post
    $post_tags = Database::fetchAll(
        "SELECT t.name, t.slug FROM tags t 
         JOIN post_tags pt ON t.id = pt.tag_id 
         WHERE pt.post_id = ?",
        [$post['id']]
    );

    // Fetch categories associated with this post
    $post_cats = Database::fetchAll(
        "SELECT c.name, c.slug, c.id FROM categories c 
         JOIN post_categories pc ON c.id = pc.category_id 
         WHERE pc.post_id = ?",
        [$post['id']]
    );

    // Fetch related articles (same category, excluding current post)
    $related_posts = [];
    if (!empty($post_cats)) {
        $cat_ids = array_column($post_cats, 'id');
        $in_clause = implode(',', array_fill(0, count($cat_ids), '?'));
        
        $related_sql = "SELECT DISTINCT p.title, p.slug, p.featured_image, p.published_at FROM posts p
                        JOIN post_categories pc ON p.id = pc.post_id
                        WHERE pc.category_id IN ($in_clause) AND p.id != ? AND p.status = 'published'
                        ORDER BY p.published_at DESC LIMIT 3";
        
        $params = array_merge($cat_ids, [$post['id']]);
        $related_posts = Database::fetchAll($related_sql, $params);
    }

} catch (Exception $e) {
    error_log("Failed loading article view: " . $e->getMessage());
    die("An error occurred loading the page.");
}
?>

<article style="padding-top: 80px;">
    
    <!-- Title Area & Header Meta -->
    <header style="background-color: hsl(var(--clr-bg-secondary)); padding: 40px 0; border-bottom: 1px solid hsl(var(--clr-border));">
        <div class="container" style="max-width: 1200px; text-align: left;">
            <h1 style="font-size: clamp(2rem, 4vw, 3rem); margin-bottom: 16px; line-height: 1.25;"><?php echo sanitize($post['title']); ?></h1>
            <p style="margin: 0; font-size: 0.95rem; display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                Dipublikasikan pada <strong><?php echo $pub_date; ?></strong>
                <span style="display: inline-flex; align-items: center; gap: 5px; color: hsl(var(--clr-text-muted)); font-size: 0.85rem;">👁 <?php echo number_format($view_count); ?> views</span>
            </p>
        </div>
    </header>

    <!-- Main Content Grid -->
    <div class="container" style="max-width: 1200px; padding-top: 50px; padding-bottom: 50px;">
        
        <!-- Featured Image -->
        <?php if (!$featured_in_content): ?>
        <div style="border-radius: var(--radius-md); overflow: hidden; margin-bottom: 40px; box-shadow: var(--shadow-sm);">
            <img src="<?php echo $image_path; ?>" alt="<?php echo sanitize($post['title']); ?>" style="width: 100%; height: auto; display: block;">
        </div>
        <?php endif; ?>

        <!-- HTML Body Content (Output unescaped since it's HTML content from WP/Editor) -->
        <div class="article-body-content" style="line-height: 1.8; font-size: 1.075rem; color: hsl(var(--clr-text-main));">
            <?php echo $post['content']; ?>
        </div>

        <!-- Categories & Tags Section -->
        <?php if (!empty($post_cats) || !empty($post_tags)): ?>
            <div style="margin-top: 48px; padding-top: 28px; border-top: 2px solid #fed7aa;">
                
                <!-- Categories -->
                <?php if (!empty($post_cats)): ?>
                    <div style="margin-bottom: <?php echo !empty($post_tags) ? '16px' : '0'; ?>;">
                        <span style="font-weight: 700; font-size: 0.9rem; color: #9a3412; margin-right: 10px;">Kategori:</span>
                        <?php foreach ($post_cats as $i => $cat): ?>
                            <a href="<?php echo BASE_URL . 'category/' . $cat['slug'] . '/'; ?>" 
                               style="font-weight: 600; font-size: 0.9rem; color: #ea580c; text-decoration: none;"><?php echo sanitize($cat['name']); ?></a><?php echo $i < count($post_cats) - 1 ? '<span style="color: #fdba74; margin: 0 6px;">•</span>' : ''; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Tags -->
                <?php if (!empty($post_tags)): ?>
                    <div>
                        <span style="font-weight: 700; font-size: 0.9rem; color: #9a3412; margin-right: 10px;">Tags:</span>
                        <?php foreach ($post_tags as $i => $tag): ?>
                            <a href="<?php echo BASE_URL . 'tag/' . $tag['slug'] . '/'; ?>" 
                               style="font-weight: 600; font-size: 0.9rem; color: #f97316; text-decoration: none;"><?php echo sanitize($tag['name']); ?></a><?php echo $i < count($post_tags) - 1 ? '<span style="color: #fdba74; margin: 0 6px;">•</span>' : ''; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    </div>
</article>

<!-- Related Posts Widget -->
<?php if (!empty($related_posts)): ?>
    <section class="section" style="background-color: hsl(var(--clr-bg-secondary)); border-top: 1px solid hsl(var(--clr-border));">
        <div class="container" style="max-width: 900px;">
            <h3 style="font-size: 1.5rem; margin-bottom: 30px;" class="text-center">Artikel Terkait</h3>
            <div class="blog-post-grid related-articles-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); justify-content: center; gap: 20px;">
                <?php foreach ($related_posts as $rel): 
                    $rel_image = $rel['featured_image'] ? BASE_URL . $rel['featured_image'] : BASE_URL . 'assets/images/og-default.png';
                    $rel_date = date('d M Y', strtotime($rel['published_at'] ?: date('Y-m-d')));
                    // Fetch excerpt for related post
                    $rel_full = Database::fetch("SELECT excerpt, content, author_id FROM posts WHERE slug = ? AND status = 'published'", [$rel['slug']]);
                    $rel_excerpt = '';
                    $rel_author = '';
                    if ($rel_full) {
                        $rel_excerpt = $rel_full['excerpt'] ?: substr(strip_tags($rel_full['content']), 0, 100) . '...';
                        $rel_author_data = Database::fetch("SELECT username FROM users WHERE id = ?", [$rel_full['author_id']]);
                        $rel_author = $rel_author_data ? $rel_author_data['username'] : '';
                    }
                ?>
                    <div class="premium-card related-card" style="max-width: 300px; width: 100%; margin: 0 auto; padding: 0; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; border-color: hsl(var(--clr-border));">
                        <div class="related-card-img-wrap" style="height: 140px; overflow: hidden; background-color: #ffffff; display: flex; align-items: center; justify-content: center;">
                            <img src="<?php echo $rel_image; ?>" alt="<?php echo sanitize($rel['title']); ?>" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>
                        <div style="padding: 18px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="font-size: 0.75rem; color: hsl(var(--clr-text-muted)); margin-bottom: 6px;">
                                    <?php if ($rel_author): ?>By <strong><?php echo sanitize($rel_author); ?></strong> &bull; <?php endif; ?><?php echo $rel_date; ?>
                                </div>
                                <h4 style="font-size: 0.95rem; line-height: 1.35; margin-bottom: 6px;">
                                    <a href="<?php echo BASE_URL . 'blog/' . $rel['slug'] . '/'; ?>" style="color: hsl(var(--clr-text-main));">
                                        <?php echo sanitize($rel['title']); ?>
                                    </a>
                                </h4>
                                <?php if ($rel_excerpt): ?>
                                <p style="font-size: 0.8rem; margin-bottom: 10px; line-height: 1.45;">
                                    <?php echo sanitize($rel_excerpt); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo BASE_URL . 'blog/' . $rel['slug'] . '/'; ?>" style="font-weight: 600; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 6px;">
                                Baca Artikel &rarr;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Styling overrides for WordPress Gutenberg content inside dynamic article views -->
<style>
.article-body-content p {
    margin-bottom: 1.5rem;
    font-size: 1.075rem;
    color: hsl(var(--clr-text-main));
}
.article-body-content h2, .article-body-content h3 {
    margin-top: 2rem;
    margin-bottom: 1rem;
}
.article-body-content ul, .article-body-content ol {
    margin-left: 24px;
    margin-bottom: 1.5rem;
}
.article-body-content li {
    margin-bottom: 8px;
}
.article-body-content blockquote {
    border-left: 4px solid hsl(var(--clr-primary));
    padding-left: 20px;
    font-style: italic;
    color: hsl(var(--clr-text-muted));
    margin: 2rem 0;
}
.article-body-content figure {
    margin: 2rem 0;
    border-radius: var(--radius-md);
    overflow: hidden;
}
.article-body-content figcaption {
    text-align: center;
    font-size: 0.85rem;
    color: hsl(var(--clr-text-muted));
    margin-top: 8px;
}
@media (max-width: 768px) {
    .related-articles-grid {
        grid-template-columns: 1fr !important;
    }
}
@media (max-width: 576px) {
    .blog-post-grid .premium-card.related-card {
        max-width: 250px !important;
    }
}
</style>



<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
