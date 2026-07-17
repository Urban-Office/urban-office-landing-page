<?php
/**
 * Urban Office - Blog Category Archives Page
 */

$page_slug = 'blog';
require_once dirname(dirname(__FILE__)) . '/inc/header.php';

$cat_slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($cat_slug)) {
    header('Location: ' . BASE_URL . 'blog/');
    exit;
}

// Pagination parameters
$limit = 4;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

try {
    // Fetch Category details
    $category = Database::fetch("SELECT * FROM categories WHERE slug = ?", [$cat_slug]);

    if (!$category) {
        // Redirect to main blog if not found
        header('Location: ' . BASE_URL . 'blog/');
        exit;
    }

    // Query and count posts under this category
    $count_sql = "SELECT COUNT(*) FROM posts p 
                  JOIN post_categories pc ON p.id = pc.post_id 
                  WHERE pc.category_id = ? AND p.status = 'published'";
    
    $total_rows = Database::fetch($count_sql, [$category['id']])['COUNT(*)'];
    $total_pages = ceil($total_rows / $limit);

    $posts_sql = "SELECT p.*, u.username as author_name FROM posts p 
                  JOIN users u ON p.author_id = u.id 
                  JOIN post_categories pc ON p.id = pc.post_id 
                  WHERE pc.category_id = ? AND p.status = 'published' 
                  ORDER BY p.published_at DESC LIMIT ? OFFSET ?";
    
    $posts = Database::fetchAll($posts_sql, [$category['id'], $limit, $offset]);

    // Fetch all categories for sidebar widget
    $categories = Database::fetchAll("SELECT * FROM categories ORDER BY name ASC");
    // Fetch all tags for sidebar widget
    $tags = Database::fetchAll("SELECT * FROM tags ORDER BY name ASC");

} catch (Exception $e) {
    error_log("Failed loading category archives: " . $e->getMessage());
    $posts = [];
    $category = ['name' => 'Kategori'];
    $total_pages = 1;
    $categories = [];
    $tags = [];
}
?>

<section class="hero-sec" style="padding: 120px 0 60px 0; background: linear-gradient(135deg, hsl(var(--clr-primary-light)) 0%, hsl(var(--clr-bg-primary)) 100%);">
    <div class="container text-center">
        <span class="hero-tag">Arsip Kategori</span>
        <h1 style="font-size: 2.5rem; margin-top: 10px;">Kategori: <?php echo sanitize($category['name']); ?></h1>
        <p class="section-subtitle" style="margin-bottom: 0;">Menampilkan seluruh artikel di dalam kategori "<?php echo sanitize($category['name']); ?>".</p>
    </div>
</section>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>


<section class="section">
    <div class="container" style="display: grid; grid-template-columns: 3fr 1fr; gap: 40px;">
        
        <!-- Posts List -->
        <div>
            <?php if (!empty($posts)): ?>
                <div class="blog-post-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                    <?php foreach ($posts as $post): 
                        $image_path = $post['featured_image'] ? BASE_URL . $post['featured_image'] : BASE_URL . 'assets/images/og-default.png';
                        $pub_date = date('d M Y', strtotime($post['published_at'] ?: $post['created_at']));
                    ?>
                        <div class="premium-card" style="padding:0; overflow:hidden; display:flex; flex-direction:column; justify-content:space-between; border-color:hsl(var(--clr-border));">
                            <div style="height: 180px; overflow:hidden; background-color: #ffffff; display: flex; align-items: center; justify-content: center;">
                                <img src="<?php echo $image_path; ?>" alt="<?php echo sanitize($post['title']); ?>" style="width:100%; height:100%; object-fit:contain;">
                            </div>
                            <div style="padding: 24px; flex-grow:1; display:flex; flex-direction:column; justify-content:space-between;">
                                <div>
                                    <div style="font-size:0.8rem; color:hsl(var(--clr-text-muted)); margin-bottom: 8px;">
                                        By <strong><?php echo sanitize($post['author_name']); ?></strong> &bull; <?php echo $pub_date; ?>
                                    </div>
                                    <h3 style="font-size: 1.15rem; line-height: 1.4; margin-bottom: 12px;">
                                        <a href="<?php echo BASE_URL . 'blog/' . $post['slug'] . '/'; ?>" style="color: hsl(var(--clr-text-main));">
                                            <?php echo sanitize($post['title']); ?>
                                        </a>
                                    </h3>
                                    <p style="font-size: 0.9rem; margin-bottom: 20px; line-height: 1.5;">
                                        <?php echo sanitize($post['excerpt'] ?: substr(strip_tags($post['content']), 0, 100) . '...'); ?>
                                    </p>
                                </div>
                                <a href="<?php echo BASE_URL . 'blog/' . $post['slug'] . '/'; ?>" style="font-weight:600; font-size:0.9rem; display:inline-flex; align-items:center; gap:6px;">
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
                    <p style="font-size:1.2rem; color:hsl(var(--clr-text-muted));">Belum ada artikel dalam kategori ini.</p>
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
                        <li><a href="<?php echo BASE_URL . 'category/' . $cat['slug'] . '/'; ?>" style="font-weight: 500; font-size: 0.95rem; color: <?php echo $cat['slug'] === $cat_slug ? 'hsl(var(--clr-primary))' : 'hsl(var(--clr-text-main))'; ?>;">&bull; <?php echo sanitize($cat['name']); ?></a></li>
                    <?php endforeach; endif; ?>
                </ul>
            </div>

            <!-- Tags Widget -->
            <div style="background-color: hsl(var(--clr-bg-surface)); padding: 24px; border-radius: var(--radius-md); border: 1px solid hsl(var(--clr-border));">
                <h4 style="border-bottom: 2px solid hsl(var(--clr-primary)); padding-bottom: 8px; margin-bottom: 16px; font-size: 1.1rem;">Tags Populer</h4>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <?php if (!empty($tags)): foreach ($tags as $tag): ?>
                        <a href="<?php echo BASE_URL . 'tag/' . $tag['slug'] . '/'; ?>" class="hero-tag" style="margin: 0; font-size: 0.75rem; text-transform: none; text-decoration: none; padding: 4px 10px; background-color: hsl(var(--clr-bg-secondary)); color: hsl(var(--clr-text-muted)); border: 1px solid hsl(var(--clr-border));">
                            #<?php echo sanitize($tag['name']); ?>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </aside>

    </div>
</section>



<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
