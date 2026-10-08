<?php
/**
 * Urban Office - Blog Tag Archives Page
 */

$page_slug = 'blog';
// Tag archives are thin (just a post list) — keep them out of the index to
// avoid "Discovered - currently not indexed" bloat, but still follow links.
$GLOBALS['seo_robots_override'] = 'noindex, follow';
require_once dirname(dirname(__FILE__)) . '/inc/header.php';

$tag_slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($tag_slug)) {
    header('Location: ' . BASE_URL . 'blog/');
    exit;
}

// Pagination parameters
$limit = 4;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

try {
    // Fetch Tag details
    $tag = Database::fetch("SELECT * FROM tags WHERE slug = ?", [$tag_slug]);

    if (!$tag) {
        // Redirect to main blog if not found
        header('Location: ' . BASE_URL . 'blog/');
        exit;
    }

    // Query and count posts under this tag
    $count_sql = "SELECT COUNT(*) as total FROM posts p 
                  JOIN post_tags pt ON p.id = pt.post_id 
                  WHERE pt.tag_id = ? AND p.status = 'published'";
    
    $count_row = Database::fetch($count_sql, [$tag['id']]);
    $total_rows = (int)($count_row['total'] ?? 0);
    $total_pages = max(1, ceil($total_rows / $limit));

    $posts_sql = "SELECT p.*, u.username as author_name FROM posts p 
                  JOIN users u ON p.author_id = u.id 
                  JOIN post_tags pt ON p.id = pt.post_id 
                  WHERE pt.tag_id = ? AND p.status = 'published' 
                  ORDER BY p.published_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    
    $posts = Database::fetchAll($posts_sql, [$tag['id']]);

    // Fetch all categories for sidebar widget
    $categories = Database::fetchAll("SELECT * FROM categories ORDER BY name ASC");
    // Fetch only tags that have published posts
    $tags = Database::fetchAll("SELECT t.*, COUNT(pt.post_id) as post_count FROM tags t JOIN post_tags pt ON t.id = pt.tag_id GROUP BY t.id HAVING post_count > 0 ORDER BY post_count DESC LIMIT 10");

} catch (Exception $e) {
    error_log("Failed loading tag archives: " . $e->getMessage());
    $posts = [];
    $tag = ['name' => 'Tag'];
    $total_pages = 1;
    $categories = [];
    $tags = [];
}
?>

<style>
.cat-pill:hover {
    background-color: #ea580c !important;
    color: #ffffff !important;
    border-color: #ea580c !important;
}
@media (max-width: 991px) {
    .blog-grid-container {
        grid-template-columns: 1fr !important;
        gap: 24px !important;
    }
    .blog-sidebar {
        display: none !important;
    }
    .category-pills-bar {
        justify-content: flex-start !important;
        overflow-x: auto !important;
        flex-wrap: nowrap !important;
        padding-bottom: 8px !important;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .cat-pill {
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }
}
@media (max-width: 640px) {
    .blog-post-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 12px !important;
    }
    .blog-card-item {
        border-radius: 10px !important;
    }
    .blog-card-img-wrap {
        width: 100% !important;
        aspect-ratio: 2 / 1 !important;
        height: auto !important;
    }
    .blog-card-body {
        padding: 12px 10px !important;
    }
    .blog-card-title {
        font-size: 0.88rem !important;
        line-height: 1.3 !important;
        margin-bottom: 6px !important;
    }
    .blog-card-meta {
        font-size: 0.7rem !important;
        margin-bottom: 4px !important;
    }
    .blog-card-excerpt {
        display: none !important;
    }
    .blog-card-link {
        font-size: 0.78rem !important;
        margin-top: 6px !important;
    }
}
</style>

<section class="hero-sec" style="padding: 120px 0 50px 0; background: linear-gradient(135deg, hsl(var(--clr-primary-light)) 0%, hsl(var(--clr-bg-primary)) 100%);">
    <div class="container text-center">
        <span class="hero-tag">Arsip Tag</span>
        <h1 style="font-size: 2.5rem; margin-top: 10px;">Tag: #<?php echo sanitize($tag['name']); ?></h1>
        <p class="section-subtitle" style="margin-bottom: 20px;">Menampilkan seluruh artikel dengan tag "#<?php echo sanitize($tag['name']); ?>".</p>

        <!-- Horizontal Category Filter Pills -->
        <?php if (!empty($categories)): ?>
            <div class="category-pills-bar" style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-top: 24px;">
                <a href="<?php echo BASE_URL; ?>blog/" class="cat-pill" style="padding: 6px 14px; border-radius: 20px; font-size: 0.82rem; font-weight: 600; background: #ffffff; color: hsl(var(--clr-text-main)); border: 1px solid hsl(var(--clr-border)); text-decoration: none;">Semua</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?php echo BASE_URL . 'category/' . $cat['slug'] . '/'; ?>" class="cat-pill" style="padding: 6px 14px; border-radius: 20px; font-size: 0.82rem; font-weight: 600; background: #ffffff; color: hsl(var(--clr-text-main)); border: 1px solid hsl(var(--clr-border)); text-decoration: none; transition: all 0.2s ease;">
                        <?php echo sanitize($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container blog-grid-container" style="display: grid; grid-template-columns: 3fr 1fr; gap: 40px;">
        
        <!-- Posts List -->
        <div>
            <?php if (!empty($posts)): ?>
                <div class="blog-post-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                    <?php foreach ($posts as $post): 
                        $image_path = media_url($post['featured_image']) ?: BASE_URL . 'assets/images/og-default.png';
                        $pub_date = date('d M Y', strtotime($post['published_at'] ?: $post['created_at']));
                    ?>
                        <div class="blog-card-item" style="padding: 0 !important; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid hsl(var(--clr-border)); border-radius: 12px; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                            <div class="blog-card-img-wrap" style="height: 180px; width: 100%; overflow: hidden; background-color: #f8fafc; margin: 0; padding: 0;">
                                <img src="<?php echo $image_path; ?>" alt="<?php echo sanitize($post['title']); ?>" style="width: 100%; height: 100%; object-fit: cover; display: block; margin: 0; padding: 0;">
                            </div>
                            <div class="blog-card-body" style="padding: 24px; flex-grow:1; display:flex; flex-direction:column; justify-content:space-between;">
                                <div>
                                    <div class="blog-card-meta" style="font-size:0.8rem; color:hsl(var(--clr-text-muted)); margin-bottom: 8px;">
                                        By <strong><?php echo sanitize($post['author_name']); ?></strong> &bull; <?php echo $pub_date; ?>
                                    </div>
                                    <h3 class="blog-card-title" style="font-size: 1.15rem; line-height: 1.4; margin-bottom: 12px;">
                                        <a href="<?php echo BASE_URL . 'blog/' . $post['slug'] . '/'; ?>" style="color: hsl(var(--clr-text-main));">
                                            <?php echo sanitize($post['title']); ?>
                                        </a>
                                    </h3>
                                    <p class="blog-card-excerpt" style="font-size: 0.9rem; margin-bottom: 20px; line-height: 1.5; color: hsl(var(--clr-text-muted));">
                                        <?php echo sanitize($post['excerpt'] ?: substr(strip_tags($post['content']), 0, 100) . '...'); ?>
                                    </p>
                                </div>
                                <a href="<?php echo BASE_URL . 'blog/' . $post['slug'] . '/'; ?>" class="blog-card-link" style="font-weight:600; font-size:0.9rem; color: #ea580c; display:inline-flex; align-items:center; gap:6px; text-decoration: none;">
                                    Baca Artikel &rarr;
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div style="display: flex; gap: 10px; justify-content: center; margin-top: 50px;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="btn btn-outline" style="padding: 8px 16px;">&laquo; Prev</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?>" style="padding: 8px 16px;"><?php echo $i; ?></a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="btn btn-outline" style="padding: 8px 16px;">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center" style="padding: 40px; background-color: hsl(var(--clr-bg-surface)); border-radius: var(--radius-md); border:1px dashed hsl(var(--clr-border));">
                    <p style="font-size:1.2rem; color:hsl(var(--clr-text-muted));">Belum ada artikel dengan tag ini.</p>
                    <a href="<?php echo BASE_URL; ?>blog/" class="btn btn-primary" style="margin-top:16px;">Lihat Semua Artikel</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <aside class="blog-sidebar">
            <!-- Categories Widget -->
            <div style="background-color: hsl(var(--clr-bg-surface)); padding: 24px; border-radius: var(--radius-md); border: 1px solid hsl(var(--clr-border)); margin-bottom: 30px;">
                <h4 style="border-bottom: 2px solid hsl(var(--clr-primary)); padding-bottom: 8px; margin-bottom: 16px; font-size: 1.1rem;">Kategori</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px;">
                    <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                        <li><a href="<?php echo BASE_URL . 'category/' . $cat['slug'] . '/'; ?>" style="font-weight: 500; font-size: 0.95rem; color: hsl(var(--clr-text-main)); text-decoration: none;">&bull; <?php echo sanitize($cat['name']); ?></a></li>
                    <?php endforeach; endif; ?>
                </ul>
            </div>

            <!-- Tags Widget (Top 8 popular tags only) -->
            <div style="background-color: hsl(var(--clr-bg-surface)); padding: 24px; border-radius: var(--radius-md); border: 1px solid hsl(var(--clr-border));">
                <h4 style="border-bottom: 2px solid hsl(var(--clr-primary)); padding-bottom: 8px; margin-bottom: 16px; font-size: 1.1rem;">Tags Populer</h4>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <?php if (!empty($tags)): foreach (array_slice($tags, 0, 8) as $t): 
                        $isActive = ($t['slug'] === $tag_slug);
                    ?>
                        <a href="<?php echo BASE_URL . 'tag/' . $t['slug'] . '/'; ?>" class="hero-tag" style="margin: 0; font-size: 0.75rem; text-transform: none; text-decoration: none; padding: 4px 10px; background-color: <?php echo $isActive ? '#ea580c' : 'hsl(var(--clr-bg-secondary))'; ?>; color: <?php echo $isActive ? '#ffffff' : 'hsl(var(--clr-text-muted))'; ?>; border: 1px solid <?php echo $isActive ? '#ea580c' : 'hsl(var(--clr-border))'; ?>; border-radius: 6px;">
                            #<?php echo sanitize($t['name']); ?>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </aside>

    </div>
</section>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
