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
$limit = 6;
$page = 1;
if (!empty($_GET['page'])) {
    $page = max(1, intval($_GET['page']));
} elseif (preg_match('/[?&]page=(\d+)/i', $_SERVER['REQUEST_URI'] ?? '', $m)) {
    $page = max(1, intval($m[1]));
} elseif (preg_match('/[?&]page=(\d+)/i', $_SERVER['QUERY_STRING'] ?? '', $m)) {
    $page = max(1, intval($m[1]));
}
$offset = ($page - 1) * $limit;

// Search Query
$search = '';
if (!empty($_GET['q'])) {
    $search = trim($_GET['q']);
} elseif (preg_match('/[?&]q=([^&]+)/i', $_SERVER['REQUEST_URI'] ?? '', $m)) {
    $search = trim(urldecode($m[1]));
}

try {
    // Build SQL Query
    if (!empty($search)) {
        $count_sql = "SELECT COUNT(*) as total FROM posts WHERE status = 'published' AND (title LIKE ? OR content LIKE ?)";
        $search_param = '%' . $search . '%';
        $count_row = Database::fetch($count_sql, [$search_param, $search_param]);
        $total_rows = (int)($count_row['total'] ?? 0);
        
        $posts_sql = "SELECT p.*, u.username as author_name FROM posts p 
                      JOIN users u ON p.author_id = u.id 
                      WHERE p.status = 'published' AND (p.title LIKE ? OR p.content LIKE ?) 
                      ORDER BY p.published_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        $posts = Database::fetchAll($posts_sql, [$search_param, $search_param]);
    } else {
        $count_row = Database::fetch("SELECT COUNT(*) as total FROM posts WHERE status = 'published'");
        $total_rows = (int)($count_row['total'] ?? 0);
        
        $posts = Database::fetchAll(
            "SELECT p.*, u.username as author_name FROM posts p 
             JOIN users u ON p.author_id = u.id 
             WHERE p.status = 'published' 
             ORDER BY p.published_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset
        );
    }
    
    $total_pages = max(1, ceil($total_rows / $limit));

    // Fetch all categories for sidebar
    $categories = Database::fetchAll("SELECT * FROM categories ORDER BY name ASC");
    // Fetch only popular tags that have published posts
    $tags = Database::fetchAll("SELECT t.*, COUNT(pt.post_id) as post_count FROM tags t JOIN post_tags pt ON t.id = pt.tag_id GROUP BY t.id HAVING post_count > 0 ORDER BY post_count DESC LIMIT 10");
    
} catch (Exception $e) {
    error_log("Failed loading blog posts: " . $e->getMessage());
    $posts = [];
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
        <span class="hero-tag">Artikel & Berita</span>
        <h1 style="font-size: 2.5rem; margin-top: 10px;">Urban Office Blog</h1>
        <p class="section-subtitle" style="margin-bottom: 20px;">Kumpulan artikel edukatif, panduan bisnis, regulasi pajak, dan informasi seputar WFA.</p>
        
        <!-- Search Form -->
        <form action="<?php echo BASE_URL; ?>blog/" method="GET" style="max-width: 500px; margin: 0 auto; display: flex; gap: 8px;">
            <input type="text" name="q" class="form-control" placeholder="Cari artikel..." value="<?php echo sanitize($search); ?>" style="background-color: white;">
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">Cari</button>
        </form>

        <!-- Horizontal Category Filter Pills -->
        <?php if (!empty($categories)): ?>
            <div class="category-pills-bar" style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-top: 24px;">
                <a href="<?php echo BASE_URL; ?>blog/" class="cat-pill" style="padding: 6px 14px; border-radius: 20px; font-size: 0.82rem; font-weight: 700; background: #ea580c; color: #ffffff; text-decoration: none; border: 1px solid #ea580c;">Semua</a>
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
        
        <!-- Main Blog Posts List -->
        <div>
            <?php if (!empty($search)): ?>
                <p style="margin-bottom: 24px;">Menampilkan hasil pencarian untuk: <strong>"<?php echo sanitize($search); ?>"</strong> (<?php echo $total_rows; ?> artikel ditemukan)</p>
            <?php endif; ?>

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

                <!-- Pagination Links -->
                <?php if ($total_pages > 1): ?>
                    <div style="display: flex; gap: 10px; justify-content: center; margin-top: 50px; flex-wrap: wrap;">
                        <?php if ($page > 1): ?>
                            <a href="<?php echo BASE_URL; ?>blog/?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&q='.urlencode($search) : ''; ?>" class="btn btn-outline" style="padding: 8px 16px;">&laquo; Prev</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="<?php echo BASE_URL; ?>blog/?page=<?php echo $i; ?><?php echo !empty($search) ? '&q='.urlencode($search) : ''; ?>" class="btn <?php echo (int)$i === (int)$page ? 'btn-primary' : 'btn-outline'; ?>" style="padding: 8px 16px; <?php if ((int)$i === (int)$page) echo 'background:#ea580c; color:#fff; border-color:#ea580c;'; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="<?php echo BASE_URL; ?>blog/?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&q='.urlencode($search) : ''; ?>" class="btn btn-outline" style="padding: 8px 16px;">Next &raquo;</a>
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
                        <li><a href="<?php echo BASE_URL . 'category/' . $cat['slug'] . '/'; ?>" style="font-weight: 500; font-size: 0.95rem; color: hsl(var(--clr-text-main)); text-decoration: none;">&bull; <?php echo sanitize($cat['name']); ?></a></li>
                    <?php endforeach; else: ?>
                        <li style="font-size: 0.9rem; color: hsl(var(--clr-text-muted));">Tidak ada kategori.</li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Tags Widget (Top 8 popular tags only) -->
            <div style="background-color: hsl(var(--clr-bg-surface)); padding: 24px; border-radius: var(--radius-md); border: 1px solid hsl(var(--clr-border));">
                <h4 style="border-bottom: 2px solid hsl(var(--clr-primary)); padding-bottom: 8px; margin-bottom: 16px; font-size: 1.1rem;">Tags Populer</h4>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <?php if (!empty($tags)): foreach (array_slice($tags, 0, 8) as $tag): ?>
                        <a href="<?php echo BASE_URL . 'tag/' . $tag['slug'] . '/'; ?>" class="hero-tag" style="margin: 0; font-size: 0.75rem; text-transform: none; text-decoration: none; padding: 4px 10px; background-color: hsl(var(--clr-bg-secondary)); color: hsl(var(--clr-text-muted)); border: 1px solid hsl(var(--clr-border)); border-radius: 6px;">
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
