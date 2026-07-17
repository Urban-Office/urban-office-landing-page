<?php
require_once dirname(dirname(__FILE__)) . '/inc/config.php';

// Safe routing fallback: if Apache/Nginx MultiViews loads blog/index.php for /blog/slug/ URLs
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$request_path = urldecode(parse_url($request_uri, PHP_URL_PATH) ?? '');

$subfolder = DIR_SUBFOLDER;
if (!empty($subfolder) && strpos($request_path, $subfolder) === 0) {
    $request_path = substr($request_path, strlen($subfolder));
}
$request_path = trim($request_path, '/');

$parts = explode('/', $request_path);
if (count($parts) >= 2 && $parts[0] === 'blog' && $parts[1] !== 'index.php' && $parts[1] !== 'article.php') {
    $_GET['slug'] = $parts[1];
    require_once __DIR__ . '/article.php';
    exit;
}

$page_slug = 'blog';
require_once dirname(dirname(__FILE__)) . '/inc/header.php';

// Pagination settings
$limit = 4;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Search Query
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    // Build SQL Query
    if (!empty($search)) {
        $count_sql = "SELECT COUNT(*) FROM posts WHERE status = 'published' AND (title LIKE ? OR content LIKE ?)";
        $posts_sql = "SELECT p.*, u.username as author_name FROM posts p 
                      JOIN users u ON p.author_id = u.id 
                      WHERE p.status = 'published' AND (p.title LIKE ? OR p.content LIKE ?) 
                      ORDER BY p.published_at DESC LIMIT ? OFFSET ?";
        
        $search_param = '%' . $search . '%';
        $total_rows = Database::fetch($count_sql, [$search_param, $search_param])['COUNT(*)'];
        $posts = Database::fetchAll($posts_sql, [$search_param, $search_param, $limit, $offset]);
    } else {
        $total_rows = Database::fetch("SELECT COUNT(*) FROM posts WHERE status = 'published'")['COUNT(*)'];
        $posts = Database::fetchAll(
            "SELECT p.*, u.username as author_name FROM posts p 
             JOIN users u ON p.author_id = u.id 
             WHERE p.status = 'published' 
             ORDER BY p.published_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }
    
    $total_pages = ceil($total_rows / $limit);

    // Fetch all categories for sidebar
    $categories = Database::fetchAll("SELECT * FROM categories ORDER BY name ASC");
    // Fetch all tags for sidebar
    $tags = Database::fetchAll("SELECT * FROM tags ORDER BY name ASC");
    
} catch (Exception $e) {
    error_log("Failed loading blog posts: " . $e->getMessage());
    $posts = [];
    $total_pages = 1;
    $categories = [];
    $tags = [];
}
?>

<style>
@media (max-width: 991px) {
    .blog-grid-container {
        grid-template-columns: 1fr !important;
        gap: 30px !important;
    }
}
</style>

<section class="hero-sec" style="padding: 120px 0 60px 0; background: linear-gradient(135deg, hsl(var(--clr-primary-light)) 0%, hsl(var(--clr-bg-primary)) 100%);">
    <div class="container text-center">
        <span class="hero-tag">Artikel & Berita</span>
        <h1 style="font-size: 2.5rem; margin-top: 10px;">Urban Office Blog</h1>
        <p class="section-subtitle" style="margin-bottom: 20px;">Kumpulan artikel edukatif, panduan bisnis, regulasi pajak, dan informasi seputar WFA.</p>
        
        <!-- Search Form -->
        <form action="<?php echo BASE_URL; ?>blog/" method="GET" style="max-width: 500px; margin: 0 auto; display: flex; gap: 8px;">
            <input type="text" name="q" class="form-control" placeholder="Cari artikel..." value="<?php echo sanitize($search); ?>" style="background-color: white;">
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">Cari</button>
        </form>
    </div>
</section>

<section class="section">
    <div class="container blog-grid-container" style="display: grid; grid-template-columns: 3fr 1fr; gap: 40px;">
        
        <!-- Main Blog Posts List -->
        <div>
            <?php if (!empty($search)): ?>
                <p style="margin-bottom: 24px;">Menampilkan hasil pencarian untuk: <strong>"<?php echo sanitize($search); ?>"</strong> (<?php echo $total_rows; ?> artikel ditemukan)</p>
            <?php endif; ?>

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

                <!-- Pagination Links -->
                <?php if ($total_pages > 1): ?>
                    <div style="display: flex; gap: 10px; justify-content: center; margin-top: 50px;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&q='.urlencode($search) : ''; ?>" class="btn btn-outline" style="padding: 8px 16px;">&laquo; Prev</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&q='.urlencode($search) : ''; ?>" class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?>" style="padding: 8px 16px;"><?php echo $i; ?></a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&q='.urlencode($search) : ''; ?>" class="btn btn-outline" style="padding: 8px 16px;">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center" style="padding: 40px; background-color: hsl(var(--clr-bg-surface)); border-radius: var(--radius-md); border:1px dashed hsl(var(--clr-border));">
                    <p style="font-size:1.2rem; color:hsl(var(--clr-text-muted));">Belum ada artikel yang dipublikasikan.</p>
                    <?php if (!empty($search)): ?>
                        <a href="<?php echo BASE_URL; ?>blog/" class="btn btn-primary" style="margin-top:16px;">Lihat Semua Artikel</a>
                    <?php endif; ?>
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
                        <li><a href="<?php echo BASE_URL . 'category/' . $cat['slug'] . '/'; ?>" style="font-weight: 500; font-size: 0.95rem; color: hsl(var(--clr-text-main));">&bull; <?php echo sanitize($cat['name']); ?></a></li>
                    <?php endforeach; else: ?>
                        <li style="font-size: 0.9rem; color: hsl(var(--clr-text-muted));">Tidak ada kategori.</li>
                    <?php endif; ?>
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
                    <?php endforeach; else: ?>
                        <span style="font-size: 0.9rem; color: hsl(var(--clr-text-muted));">Tidak ada tags.</span>
                    <?php endif; ?>
                </div>
            </div>
        </aside>

    </div>
</section>



<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
