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
    $image_path = media_url($post['featured_image']) ?: BASE_URL . 'assets/images/og-default.png';

    // Clean duplicate leading title or duplicate image from body content
    $display_content = $post['content'];
    
    // Strip duplicate heading at the very beginning of the body content if present
    $display_content = preg_replace('/^\s*<h[1-3][^>]*>[\s\S]*?<\/h[1-3]>/iu', '', $display_content, 1);
    
    // Strip duplicate leading image / figure at the very beginning of the body content
    $display_content = preg_replace('/^\s*(?:<p[^>]*>|<div[^>]*>|<figure[^>]*>)?\s*<img[^>]+>\s*(?:<\/figure>|<\/div>|<\/p>)?/iu', '', $display_content, 1);

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
            <?php if (!empty($post_cats)): 
                $primary_cat = $post_cats[0];
            ?>
                <div style="margin-bottom: 14px;">
                    <a href="<?php echo BASE_URL . 'category/' . $primary_cat['slug'] . '/'; ?>" 
                       style="display: inline-flex; align-items: center; background: #fff2ea; color: #ea580c; border: 1px solid #fdba74; font-size: 0.78rem; font-weight: 700; padding: 4px 14px; border-radius: 20px; text-decoration: none; text-transform: uppercase; letter-spacing: 0.04em; transition: all 0.2s ease;">
                        <?php echo sanitize($primary_cat['name']); ?>
                    </a>
                </div>
            <?php endif; ?>
            <h1 style="font-size: clamp(1.85rem, 3.8vw, 2.8rem); margin-bottom: 14px; line-height: 1.25;"><?php echo sanitize($post['title']); ?></h1>
            <p style="margin: 0; font-size: 0.92rem; color: hsl(var(--clr-text-muted)); display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                <span>Ditulis oleh <strong style="color: hsl(var(--clr-text-main));"><?php echo sanitize($post['author_name'] ?? 'Admin'); ?></strong></span>
                <span>&bull;</span>
                <span><?php echo $pub_date; ?></span>
                <span>&bull;</span>
                <span style="display: inline-flex; align-items: center; gap: 4px;">👁 <?php echo number_format($view_count); ?> views</span>
            </p>
        </div>
    </header>

    <!-- Main Content Grid -->
    <div class="container" style="max-width: 1200px; padding-top: 40px; padding-bottom: 50px;">
        
        <!-- Official Single Featured Hero Image under Author Meta -->
        <div style="border-radius: var(--radius-md); overflow: hidden; margin-bottom: 40px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); background: #ffffff;">
            <img src="<?php echo $image_path; ?>" alt="<?php echo sanitize($post['title']); ?>" style="width: 100%; height: auto; display: block; border-radius: 12px;">
        </div>

        <!-- HTML Body Content -->
        <div class="article-body-content" style="line-height: 1.8; font-size: 1.075rem; color: hsl(var(--clr-text-main));">
            <?php echo $display_content; ?>
        </div>

        <!-- Tags / Topic Cloud at Bottom of Article -->
        <?php if (!empty($post_tags) || count($post_cats) > 1): ?>
            <div style="margin-top: 40px; padding-top: 24px; border-top: 1px solid hsl(var(--clr-border)); display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                <span style="font-size: 0.85rem; font-weight: 700; color: hsl(var(--clr-text-muted)); margin-right: 4px;">Topik:</span>
                <?php foreach ($post_tags as $t): ?>
                    <a href="<?php echo BASE_URL . 'tag/' . $t['slug'] . '/'; ?>" style="font-size: 0.78rem; font-weight: 600; padding: 4px 12px; background: hsl(var(--clr-bg-secondary)); border: 1px solid hsl(var(--clr-border)); color: hsl(var(--clr-text-muted)); border-radius: 6px; text-decoration: none; transition: all 0.2s ease;">
                        #<?php echo sanitize($t['name']); ?>
                    </a>
                <?php endforeach; ?>
                <?php for ($ci = 1; $ci < count($post_cats); $ci++): ?>
                    <a href="<?php echo BASE_URL . 'category/' . $post_cats[$ci]['slug'] . '/'; ?>" style="font-size: 0.78rem; font-weight: 600; padding: 4px 12px; background: hsl(var(--clr-bg-secondary)); border: 1px solid hsl(var(--clr-border)); color: hsl(var(--clr-text-muted)); border-radius: 6px; text-decoration: none; transition: all 0.2s ease;">
                        #<?php echo sanitize($post_cats[$ci]['name']); ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
        
    </div>
</article>

<!-- Related Posts Widget -->
<?php if (!empty($related_posts)): ?>
    <section class="section" style="background-color: hsl(var(--clr-bg-secondary)); border-top: 1px solid hsl(var(--clr-border)); padding: 60px 0;">
        <div class="container" style="max-width: 1200px;">
            <div style="text-align: center; margin-bottom: 36px;">
                <span class="hero-tag" style="margin-bottom: 8px;">Rekomendasi</span>
                <h3 style="font-size: 1.8rem; font-weight: 800; margin: 6px 0 0; color: hsl(var(--clr-text-main));">Artikel Terkait Lainnya</h3>
            </div>
            <div class="related-articles-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
                <?php foreach ($related_posts as $rel): 
                    $rel_image = media_url($rel['featured_image']) ?: BASE_URL . 'assets/images/og-default.png';
                    $rel_date = date('d M Y', strtotime($rel['published_at'] ?: date('Y-m-d')));
                    // Fetch excerpt for related post
                    $rel_full = Database::fetch("SELECT excerpt, content, author_id FROM posts WHERE slug = ? AND status = 'published'", [$rel['slug']]);
                    $rel_excerpt = '';
                    $rel_author = '';
                    if ($rel_full) {
                        $rel_excerpt = $rel_full['excerpt'] ?: substr(strip_tags($rel_full['content']), 0, 90) . '...';
                        $rel_author_data = Database::fetch("SELECT username FROM users WHERE id = ?", [$rel_full['author_id']]);
                        $rel_author = $rel_author_data ? $rel_author_data['username'] : '';
                    }
                ?>
                    <div class="related-card-item" style="width: 100%; padding: 0 !important; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid hsl(var(--clr-border)); border-radius: 12px; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                        <div class="related-card-img" style="width: 100%; aspect-ratio: 2 / 1; overflow: hidden; background-color: #f8fafc; margin: 0; padding: 0;">
                            <img src="<?php echo $rel_image; ?>" alt="<?php echo sanitize($rel['title']); ?>" style="width: 100%; height: 100%; object-fit: cover; display: block; margin: 0; padding: 0;">
                        </div>
                        <div class="related-card-content" style="padding: 20px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="font-size: 0.78rem; color: hsl(var(--clr-text-muted)); margin-bottom: 8px;">
                                    <?php if ($rel_author): ?>By <strong><?php echo sanitize($rel_author); ?></strong> &bull; <?php endif; ?><?php echo $rel_date; ?>
                                </div>
                                <h4 style="font-size: 1.05rem; line-height: 1.35; margin-bottom: 10px; font-weight: 700;">
                                    <a href="<?php echo BASE_URL . 'blog/' . $rel['slug'] . '/'; ?>" style="color: hsl(var(--clr-text-main)); text-decoration: none;">
                                        <?php echo sanitize($rel['title']); ?>
                                    </a>
                                </h4>
                                <?php if ($rel_excerpt): ?>
                                <p style="font-size: 0.85rem; margin-bottom: 16px; line-height: 1.5; color: hsl(var(--clr-text-muted));">
                                    <?php echo sanitize($rel_excerpt); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo BASE_URL . 'blog/' . $rel['slug'] . '/'; ?>" style="font-weight: 700; font-size: 0.85rem; color: #ea580c; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
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
@media (max-width: 991px) {
    .related-articles-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 16px !important;
    }
}
@media (max-width: 640px) {
    .related-articles-grid {
        display: flex !important;
        overflow-x: auto !important;
        scroll-snap-type: x mandatory !important;
        gap: 10px !important;
        padding-bottom: 14px !important;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        justify-content: flex-start !important;
    }
    .related-card-item {
        flex: 0 0 46% !important;
        min-width: 140px !important;
        max-width: 185px !important;
        scroll-snap-align: start !important;
        border-radius: 10px !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .related-card-img {
        width: 100% !important;
        aspect-ratio: 2 / 1 !important;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .related-card-img img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .related-card-content {
        padding: 10px 8px !important;
    }
    .related-card-content h4 {
        font-size: 0.82rem !important;
        line-height: 1.25 !important;
        margin-bottom: 4px !important;
    }
    .related-card-content p {
        display: none !important;
    }
    .related-card-content a {
        font-size: 0.75rem !important;
        margin-top: 4px !important;
    }
}
</style>



<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
