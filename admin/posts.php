<?php
/**
 * Admin Posts CRUD Management
 */

require_once __DIR__ . '/auth.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = '';
$success = '';

// Process Form Submissions (Create and Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['create']) || isset($_POST['update']))) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi CSRF token gagal.';
    } else {
        $title = trim($_POST['title']);
        $slug = trim($_POST['slug']);
        $excerpt = trim($_POST['excerpt']);
        $content = $_POST['content']; // HTML Content (do not strip tags)
        $featured_image = trim($_POST['featured_image']);
        $meta_title = trim($_POST['meta_title']);
        $meta_description = trim($_POST['meta_description']);
        $status = $_POST['status'];
        $published_at = !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d H:i:s');
        $selected_cats = isset($_POST['categories']) ? $_POST['categories'] : [];
        $selected_tags = isset($_POST['tags']) ? $_POST['tags'] : [];

        // Check if a featured image file is uploaded
        if (isset($_FILES['featured_image_file']) && $_FILES['featured_image_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['featured_image_file']['tmp_name'];
            $fileName = $_FILES['featured_image_file']['name'];
            $fileSize = $_FILES['featured_image_file']['size'];
            $fileType = $_FILES['featured_image_file']['type'];
            
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            
            $allowedfileExtensions = ['jpg', 'gif', 'png', 'jpeg', 'webp'];
            if (in_array($fileExtension, $allowedfileExtensions)) {
                $uploadFileDir = dirname(dirname(__FILE__)) . '/assets/images/blog/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Save relative path in DB
                    $featured_image = 'assets/images/blog/' . $newFileName;
                } else {
                    $error = 'Gagal memindahkan file unggahan gambar utama.';
                }
            } else {
                $error = 'Format file gambar utama tidak didukung. Gunakan JPG, PNG, GIF, JPEG, atau WEBP.';
            }
        }

        // Auto-generate slug if blank
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        }

        if (empty($title) || empty($content)) {
            $error = 'Judul dan konten wajib diisi.';
        } else {
            try {
                if (isset($_POST['create'])) {
                    // Check duplicate slug
                    $check = Database::fetch("SELECT id FROM posts WHERE slug = ?", [$slug]);
                    if ($check) {
                        $slug .= '-' . time(); // Append timestamp on duplicate
                    }

                    // Insert post
                    $post_id = Database::insert(
                        "INSERT INTO posts (author_id, title, slug, excerpt, content, featured_image, meta_title, meta_description, status, published_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        [$_SESSION['admin_user_id'], $title, $slug, $excerpt, $content, $featured_image, $meta_title, $meta_description, $status, $published_at]
                    );

                    // Insert Categories relationships
                    foreach ($selected_cats as $cat_id) {
                        Database::insert("INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)", [$post_id, $cat_id]);
                    }

                    // Insert Tags relationships
                    foreach ($selected_tags as $tag_id) {
                        Database::insert("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)", [$post_id, $tag_id]);
                    }

                    log_activity($_SESSION['admin_user_id'], 'create_post', "Created post: " . sanitize($title));
                    clear_page_cache(); // Clear cache to reflect updates
                    header('Location: ' . BASE_URL . 'admin/posts.php?success=1');
                    exit;

                } elseif (isset($_POST['update'])) {
                    $id = intval($_POST['id']);
                    
                    // Check duplicate slug excluding current post
                    $check = Database::fetch("SELECT id FROM posts WHERE slug = ? AND id != ?", [$slug, $id]);
                    if ($check) {
                        $slug .= '-' . time();
                    }

                    // Update post
                    Database::query(
                        "UPDATE posts SET title = ?, slug = ?, excerpt = ?, content = ?, featured_image = ?, meta_title = ?, meta_description = ?, status = ?, published_at = ? 
                         WHERE id = ?",
                        [$title, $slug, $excerpt, $content, $featured_image, $meta_title, $meta_description, $status, $published_at, $id]
                    );

                    // Clear existing relationships
                    Database::query("DELETE FROM post_categories WHERE post_id = ?", [$id]);
                    Database::query("DELETE FROM post_tags WHERE post_id = ?", [$id]);

                    // Insert updated relationships
                    foreach ($selected_cats as $cat_id) {
                        Database::insert("INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)", [$id, $cat_id]);
                    }
                    foreach ($selected_tags as $tag_id) {
                        Database::insert("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)", [$id, $tag_id]);
                    }

                    log_activity($_SESSION['admin_user_id'], 'update_post', "Updated post ID: $id (" . sanitize($title) . ")");
                    clear_page_cache(); // Clear cache
                    header('Location: ' . BASE_URL . 'admin/posts.php?success=2');
                    exit;
                }
            } catch (Exception $e) {
                $error = 'Gagal menyimpan artikel: ' . $e->getMessage();
            }
        }
    }
}

// Action: Delete Post
if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $post = Database::fetch("SELECT title FROM posts WHERE id = ?", [$id]);
        if ($post) {
            Database::query("DELETE FROM posts WHERE id = ?", [$id]);
            log_activity($_SESSION['admin_user_id'], 'delete_post', "Deleted post ID: $id (" . sanitize($post['title']) . ")");
            clear_page_cache();
            header('Location: ' . BASE_URL . 'admin/posts.php?success=3');
            exit;
        }
    } catch (Exception $e) {
        $error = 'Gagal menghapus artikel: ' . $e->getMessage();
    }
}

// Read Actions: Fetch Lists or Individual item details
$post_data = null;
$post_cats_ids = [];
$post_tags_ids = [];

if ($action === 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $post_data = Database::fetch("SELECT * FROM posts WHERE id = ?", [$id]);
    if ($post_data) {
        $search_patterns = [
            'http://localhost:8000/Clone%20Website%20urban%20hanya%20php/',
            'http://localhost:8000/Clone Website urban hanya php/',
            'http://localhost:8000/',
            'http://localhost/Clone%20Website%20urban%20hanya%20php/',
            'http://localhost/Clone Website urban hanya php/',
            'http://localhost/'
        ];
        if (!empty($post_data['content'])) {
            $post_data['content'] = str_replace($search_patterns, BASE_URL, $post_data['content']);
        }
        if (!empty($post_data['featured_image'])) {
            $post_data['featured_image'] = str_replace($search_patterns, '', $post_data['featured_image']);
            $post_data['featured_image'] = ltrim($post_data['featured_image'], '/');
        }

        $cats = Database::fetchAll("SELECT category_id FROM post_categories WHERE post_id = ?", [$id]);
        $post_cats_ids = array_column($cats, 'category_id');
        
        $tags = Database::fetchAll("SELECT tag_id FROM post_tags WHERE post_id = ?", [$id]);
        $post_tags_ids = array_column($tags, 'tag_id');
    } else {
        header('Location: ' . BASE_URL . 'admin/posts.php');
        exit;
    }
}

// Filter, Search & Sort parameters for List view
$status_filter = isset($_GET['status']) ? trim((string) $_GET['status']) : '';
if (!in_array($status_filter, ['', 'published', 'draft', 'scheduled'], true)) {
    $status_filter = '';
}
$search_q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$sort_by  = isset($_GET['sort']) ? trim((string) $_GET['sort']) : 'newest';
$sort_map = [
    'newest'      => 'p.created_at DESC',
    'views_desc'  => 'p.views DESC, p.created_at DESC',
    'views_asc'   => 'p.views ASC, p.created_at DESC',
    'title_asc'   => 'p.title ASC',
];
if (!isset($sort_map[$sort_by])) {
    $sort_by = 'newest';
}
$order_sql = $sort_map[$sort_by];

$posts_kpi = [
    'total'       => 0,
    'published'   => 0,
    'draft'       => 0,
    'scheduled'   => 0,
    'total_views' => 0,
    'this_month'  => 0,
];
$posts_chart_series = [];
$top_viewed_posts   = [];
$total_posts        = 0;
$total_pages        = 1;
$limit              = 10;
$page               = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Get Lists of Categories, Tags, and Posts
try {
    $all_categories = Database::fetchAll("SELECT * FROM categories ORDER BY name ASC");
    $all_tags = Database::fetchAll("SELECT * FROM tags ORDER BY name ASC");

    if ($action === 'list') {
        // Blog KPI summary (global across all posts)
        $kpi_row = Database::fetch(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'published') AS published,
                SUM(status = 'draft') AS draft,
                SUM(status = 'scheduled') AS scheduled,
                COALESCE(SUM(views), 0) AS total_views,
                SUM(DATE_FORMAT(COALESCE(published_at, created_at), '%Y-%m') = ?) AS this_month
             FROM posts",
            [date('Y-m')]
        );
        if ($kpi_row) {
            foreach ($posts_kpi as $k => $_v) {
                $posts_kpi[$k] = (int) ($kpi_row[$k] ?? 0);
            }
        }

        // Monthly publication & views trend (last 12 months, continuous timeline)
        $monthly_raw = Database::fetchAll(
            "SELECT DATE_FORMAT(COALESCE(published_at, created_at), '%Y-%m') AS ym,
                    COUNT(*) AS post_count,
                    COALESCE(SUM(views), 0) AS total_views
             FROM posts
             GROUP BY ym
             ORDER BY ym DESC
             LIMIT 12"
        );
        if (!empty($monthly_raw)) {
            $monthly_raw = array_reverse($monthly_raw);
            $month_map = [];
            foreach ($monthly_raw as $mr) {
                $month_map[$mr['ym']] = $mr;
            }
            $first_ym = $monthly_raw[0]['ym'];
            $last_ym  = max($monthly_raw[count($monthly_raw) - 1]['ym'], date('Y-m'));
            $cursor = new DateTime($first_ym . '-01');
            $end_dt = new DateTime($last_ym . '-01');
            $guard = 0;
            while ($cursor <= $end_dt && $guard < 12) {
                $ym = $cursor->format('Y-m');
                $row = $month_map[$ym] ?? null;
                $posts_chart_series[] = [
                    'label'      => $cursor->format('M Y'),
                    'post_count' => $row ? (int) $row['post_count'] : 0,
                    'views'      => $row ? (int) $row['total_views'] : 0,
                ];
                $cursor->modify('+1 month');
                $guard++;
            }
        }

        // Top 5 articles by views
        $top_viewed_posts = Database::fetchAll(
            "SELECT id, title, slug, views, status
             FROM posts
             ORDER BY views DESC, created_at DESC
             LIMIT 5"
        );

        // Filtered & paginated posts list
        $where_parts = [];
        $where_params = [];
        if ($status_filter !== '') {
            $where_parts[] = 'p.status = ?';
            $where_params[] = $status_filter;
        }
        if ($search_q !== '') {
            $where_parts[] = '(p.title LIKE ? OR p.slug LIKE ?)';
            $like = '%' . $search_q . '%';
            $where_params[] = $like;
            $where_params[] = $like;
        }
        $where_sql = !empty($where_parts) ? ('WHERE ' . implode(' AND ', $where_parts)) : '';

        $total_posts = (int) (Database::fetch("SELECT COUNT(*) AS total FROM posts p {$where_sql}", $where_params)['total'] ?? 0);
        $total_pages = max(1, (int) ceil($total_posts / $limit));
        if ($page > $total_pages) {
            $page = $total_pages;
        }
        $offset = ($page - 1) * $limit;

        $posts_list = Database::fetchAll(
            "SELECT p.id, p.title, p.slug, p.status, p.published_at, p.views, u.username AS author_name
             FROM posts p
             JOIN users u ON p.author_id = u.id
             {$where_sql}
             ORDER BY {$order_sql}
             LIMIT {$limit} OFFSET {$offset}",
            $where_params
        );
    } else {
        $posts_list = [];
    }
} catch (Exception $e) {
    error_log("Posts CRUD loading error: " . $e->getMessage());
    $all_categories = $all_tags = $posts_list = [];
    $total_pages = 1;
}

$build_posts_url = static function (array $overrides = []) use ($status_filter, $search_q, $sort_by): string {
    $q = array_merge([
        'status' => $status_filter,
        'q'      => $search_q,
        'sort'   => $sort_by === 'newest' ? '' : $sort_by,
    ], $overrides);
    $q = array_filter($q, static fn($v) => $v !== '' && $v !== null);
    return BASE_URL . 'admin/posts.php' . (!empty($q) ? ('?' . http_build_query($q)) : '');
};

// Success message templates
if (isset($_GET['success'])) {
    if ($_GET['success'] == 1) $success = 'Artikel berhasil dibuat!';
    if ($_GET['success'] == 2) $success = 'Artikel berhasil diperbarui!';
    if ($_GET['success'] == 3) $success = 'Artikel berhasil dihapus!';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Artikel - Urban Office</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css?v=<?php echo filemtime(dirname(dirname(__FILE__)) . '/assets/css/admin.css'); ?>">
    <?php if ($action === 'new' || $action === 'edit'): ?>
    <!-- Include CKEditor 5 Super-build only on editor views -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/super-build/ckeditor.js"></script>
    <script>
        if (typeof CKEDITOR === 'undefined') {
            document.write('<script src="<?php echo BASE_URL; ?>assets/js/ckeditor.js"><\/script>');
        }
    </script>
    <?php endif; ?>
    <style>
    /* Styling for CKEditor height alignment */
    .ck-editor__editable_inline {
        min-height: 420px;
        background-color: #ffffff !important;
    }
    .ck.ck-editor {
        width: 100% !important;
    }
    /* Image Insert Dialog */
    .img-insert-overlay {
        display: none;
        position: fixed; inset: 0;
        background: rgba(15,23,42,0.55);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    .img-insert-overlay.open { display: flex; }
    .img-insert-modal {
        background: #fff;
        border-radius: 14px;
        padding: 28px 32px;
        width: 480px;
        max-width: 95vw;
        box-shadow: 0 24px 60px rgba(0,0,0,0.2);
    }
    .img-insert-modal h3 { margin: 0 0 20px; font-size: 1.1rem; }
    .img-tab-bar { display: flex; gap: 6px; margin-bottom: 20px; }
    .img-tab-btn {
        flex: 1; padding: 8px; border: 2px solid #e2e8f0;
        background: #f8fafc; border-radius: 8px;
        cursor: pointer; font-size: 0.85rem; font-weight: 600;
        color: #64748b; transition: all 0.2s;
    }
    .img-tab-btn.active {
        border-color: #6366f1; background: #eef2ff; color: #4f46e5;
    }
    .img-tab-panel { display: none; }
    .img-tab-panel.active { display: block; }
    .img-option-row { display: flex; gap: 12px; margin-bottom: 16px; }
    .img-option-row label { flex: 1; }
    .img-option-row select, .img-option-row input[type=number] {
        width: 100%; padding: 8px 10px; border-radius: 7px;
        border: 1.5px solid #e2e8f0; font-size: 0.85rem;
    }
    .img-insert-actions { display: flex; gap: 10px; margin-top: 20px; justify-content: flex-end; }
    /* Featured image tab switcher */
    .feat-tab-bar { display: flex; gap: 0; margin-bottom: 12px; border-radius: 8px; overflow: hidden; border: 1.5px solid #e2e8f0; }
    .feat-tab-btn {
        flex: 1; padding: 7px 4px;
        background: #f8fafc; border: none;
        cursor: pointer; font-size: 0.8rem; font-weight: 600;
        color: #64748b; transition: all 0.2s;
    }
    .feat-tab-btn.active { background: #6366f1; color: #fff; }
    .feat-tab-panel { display: none; }
    .feat-tab-panel.active { display: block; }

    /* Posts List Insights, Chart & Windowed Pagination */
    .posts-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }
    .posts-kpi-card {
        position: relative;
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        border-radius: var(--admin-radius);
        padding: 15px 16px;
        box-shadow: var(--admin-shadow-sm);
        overflow: hidden;
    }
    .posts-kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: var(--pk-accent, #1e40af);
    }
    .posts-kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .posts-kpi-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: var(--admin-muted);
    }
    .posts-kpi-icon {
        width: 28px; height: 28px;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--pk-bg, #eff6ff);
        color: var(--pk-accent, #1e40af);
        flex-shrink: 0;
    }
    .posts-kpi-icon svg { width: 15px; height: 15px; }
    .posts-kpi-val {
        font-family: var(--admin-font-mono);
        font-variant-numeric: tabular-nums;
        font-size: 1.55rem;
        font-weight: 700;
        color: var(--admin-ink);
        margin-top: 8px;
        line-height: 1.1;
    }
    .posts-kpi-sub {
        font-size: 0.74rem;
        color: var(--admin-subtle);
        margin-top: 5px;
    }
    .posts-insights-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(0, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }
    .posts-insights-grid .admin-card {
        margin-bottom: 0;
        display: flex;
        flex-direction: column;
    }
    .posts-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .posts-card-head h2 {
        margin: 0;
        font-size: 0.98rem;
        font-weight: 700;
        color: var(--admin-ink);
    }
    .posts-card-sub {
        margin: 2px 0 0;
        font-size: 0.77rem;
        color: var(--admin-subtle);
    }
    .posts-seg-toggle {
        display: inline-flex;
        background: var(--admin-surface-muted);
        border: 1px solid var(--admin-border);
        border-radius: 8px;
        padding: 3px;
        gap: 2px;
    }
    .posts-seg-btn {
        border: 0;
        background: transparent;
        color: var(--admin-muted);
        font-family: inherit;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 140ms ease, color 140ms ease;
    }
    .posts-seg-btn:hover { color: var(--admin-ink); }
    .posts-seg-btn.active {
        background: var(--admin-surface);
        color: var(--admin-primary);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
    }
    .posts-chart-wrap {
        position: relative;
        height: 235px;
        width: 100%;
    }
    .posts-top-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 2px;
    }
    .posts-top-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .posts-top-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 10px;
    }
    .posts-top-rank {
        font-family: var(--admin-font-mono);
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--admin-primary);
        background: #eff6ff;
        border: 1px solid #dbeafe;
        border-radius: 5px;
        padding: 1px 6px;
        flex-shrink: 0;
    }
    .posts-top-link {
        flex: 1;
        min-width: 0;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--admin-ink);
        text-decoration: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .posts-top-link:hover { color: var(--admin-primary); text-decoration: underline; }
    .posts-top-views {
        font-family: var(--admin-font-mono);
        font-size: 0.77rem;
        font-weight: 700;
        color: var(--admin-ink-2);
        white-space: nowrap;
    }
    .posts-bar-track {
        height: 6px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }
    .posts-bar-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #1e40af 0%, #3b82f6 100%);
    }
    .posts-search-form {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .posts-filter-input {
        height: 35px;
        padding: 0 10px;
        border: 1px solid var(--admin-border-strong);
        border-radius: var(--admin-radius-sm);
        font-family: inherit;
        font-size: 0.82rem;
        color: var(--admin-ink);
        background: var(--admin-surface);
    }
    .posts-filter-input:focus {
        border-color: var(--admin-primary);
        box-shadow: 0 0 0 3px var(--admin-ring);
        outline: none;
    }
    .posts-dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .posts-pager-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid var(--admin-border);
    }
    .posts-pager-info {
        font-size: 0.8rem;
        color: var(--admin-muted);
    }
    .posts-pager-info strong {
        font-family: var(--admin-font-mono);
        color: var(--admin-ink);
    }
    .posts-pager-nav {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        flex-wrap: wrap;
    }
    .posts-page-btn {
        min-width: 34px;
        height: 34px;
        padding: 0 10px;
        border-radius: 8px;
        border: 1px solid var(--admin-border-strong);
        background: var(--admin-surface);
        color: var(--admin-ink-2);
        font-family: var(--admin-font-mono);
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        transition: background-color 140ms ease, border-color 140ms ease, color 140ms ease;
    }
    .posts-page-btn:hover {
        background: var(--admin-surface-muted);
        border-color: #94a3b8;
        color: var(--admin-ink);
    }
    .posts-page-btn.active {
        background: var(--admin-primary);
        border-color: var(--admin-primary);
        color: #ffffff;
    }
    .posts-page-btn.disabled {
        opacity: 0.45;
        pointer-events: none;
        background: var(--admin-light);
    }
    .posts-page-ellipsis {
        padding: 0 6px;
        color: var(--admin-subtle);
        font-weight: 600;
        user-select: none;
    }
    .sr-only {
        position: absolute;
        width: 1px; height: 1px;
        padding: 0; margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    @media (max-width: 1080px) {
        .posts-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .posts-insights-grid { grid-template-columns: 1fr; }
    }
    </style>
    <script>
      // ---- Custom CKEditor Upload Adapter ----
      function CustomUploadAdapterPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
          return new CustomUploadAdapter(loader, '<?php echo BASE_URL; ?>admin/upload.php');
        };
      }

      class CustomUploadAdapter {
        constructor(loader, url) {
          this.loader = loader;
          this.url = url;
          this.xhr = null;
        }
        upload() {
          return this.loader.file.then(file => new Promise((resolve, reject) => {
            this.xhr = new XMLHttpRequest();
            this.xhr.open('POST', this.url, true);
            const data = new FormData();
            data.append('upload', file);
            this.xhr.onreadystatechange = () => {
              if (this.xhr.readyState !== 4) return;
              const resp = JSON.parse(this.xhr.responseText || '{}');
              if (resp.uploaded && resp.url) {
                resolve({ default: resp.url });
              } else {
                reject(resp.error?.message || 'Upload gagal.');
              }
            };
            this.xhr.onerror = () => reject('Upload gagal (network error).');
            this.xhr.send(data);
          }));
        }
        abort() { if (this.xhr) this.xhr.abort(); }
      }

      let ckEditorInstance = null;

      document.addEventListener('DOMContentLoaded', function() {
        if (document.querySelector('#content-editor')) {
          if (typeof CKEDITOR !== 'undefined' && CKEDITOR.ClassicEditor) {
            CKEDITOR.ClassicEditor
              .create(document.querySelector('#content-editor'), {
                extraPlugins: [CustomUploadAdapterPlugin],
                removePlugins: [
                  'TrackChanges', 'TrackChangesData', 'RevisionHistory',
                  'Comments', 'CommentsArchive', 'CommentsOnly',
                  'RealTimeCollaborativeEditing', 'RealTimeCollaborativeComments',
                  'RealTimeCollaborativeRevisionHistory', 'RealTimeCollaborativeTrackChanges',
                  'PresenceList', 'AIAssistant', 'CKBox', 'CKBoxImageEdit',
                  'CKFinder', 'EasyImage', 'ExportPdf', 'ExportWord', 'ImportWord',
                  'Pagination', 'SlashCommand', 'Template', 'FormatPainter',
                  'TableOfContents', 'CaseChange', 'MultiLevelList',
                  'PasteFromOfficeEnhanced',
                  'CloudServices',
                  'WProofreader', 'SpellCheck',
                  'DocumentOutline', 'DocumentOutlineUI'
                ],
                toolbar: {
                  items: [
                    'undo', 'redo', '|',
                    'heading', '|',
                    'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', '|',
                    'bold', 'italic', 'underline', 'strikethrough', 'link', 'insertTable', '|',
                    'alignment', '|',
                    'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                    'blockQuote', 'removeFormat', '|',
                    'uploadImage'
                  ],
                  shouldNotGroupWhenFull: true
                },
                image: {
                  toolbar: [
                    'imageTextAlternative', 'toggleImageCaption', '|',
                    'imageStyle:inline', 'imageStyle:block', 'imageStyle:side', '|',
                    'linkImage'
                  ]
                }
              })
              .then(editor => {
                ckEditorInstance = editor;
              })
              .catch(error => {
                console.warn('Failed to initialize CKEditor:', error);
                if (dbg) {
                  dbg.style.display = 'block';
                  dbg.innerHTML += '❌ CKEditor Init Error: ' + (error.stack || error.message || error) + '\n';
                }
              });
          } else {
            console.error('CKEDITOR global is not defined. Please verify that the CDN or local script loaded successfully.');
            if (dbg) {
              dbg.style.display = 'block';
              dbg.innerHTML += '❌ CKEDITOR global is not defined. Please verify that the CDN at https://cdn.ckeditor.com or the local script in assets/js/ckeditor.js was copied and loaded successfully. (Check browser console for network blocks/404)\n';
            }
          }
        }
      });

      // ---- Image Insert Dialog Logic ----
      const imgOverlay = document.getElementById ? null : null; // init after DOM
      document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.getElementById('img-insert-overlay');
        const btnOpen = document.getElementById('btn-insert-image');
        const btnClose = document.getElementById('img-insert-close');
        const btnInsert = document.getElementById('img-insert-confirm');

        if (!overlay || !btnOpen) return;

        // Tab switching inside dialog
        overlay.querySelectorAll('.img-tab-btn').forEach(btn => {
          btn.addEventListener('click', () => {
            overlay.querySelectorAll('.img-tab-btn').forEach(b => b.classList.remove('active'));
            overlay.querySelectorAll('.img-tab-panel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            overlay.querySelector('#' + btn.dataset.tab).classList.add('active');
          });
        });

        btnOpen.addEventListener('click', () => overlay.classList.add('open'));
        btnClose.addEventListener('click', () => overlay.classList.remove('open'));
        overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); });

        // Upload preview inside dialog
        const fileInput = document.getElementById('img-upload-file');
        const uploadPreview = document.getElementById('img-upload-preview');
        if (fileInput) {
          fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (file) {
              const reader = new FileReader();
              reader.onload = e => { uploadPreview.src = e.target.result; uploadPreview.style.display = 'block'; };
              reader.readAsDataURL(file);
            }
          });
        }

        btnInsert.addEventListener('click', () => {
          if (!ckEditorInstance) { alert('Editor belum siap.'); return; }
          const activeTab = overlay.querySelector('.img-tab-panel.active');
          const align = document.getElementById('img-align').value;
          const width = document.getElementById('img-width').value;
          const alt = document.getElementById('img-alt').value.trim();
          const linkUrl = document.getElementById('img-link-url') ? document.getElementById('img-link-url').value.trim() : '';
          
          // Enforce alt text for SEO compliance
          if (!alt) {
            alert('⚠ Teks Alt (Alt Text) wajib diisi untuk SEO!\n\nAlt text membantu Google memahami isi gambar dan meningkatkan aksesibilitas.');
            document.getElementById('img-alt').focus();
            document.getElementById('img-alt').style.borderColor = '#ef4444';
            return;
          }
          document.getElementById('img-alt').style.borderColor = '#e2e8f0';
          
          const styleAttr = (align !== 'none' ? `float:${align};margin:${align==='right'?'0 0 10px 14px':'0 14px 10px 0'};` : '') + (width ? `width:${width}px;` : 'max-width:100%;');

          if (activeTab && activeTab.id === 'img-tab-url') {
            const url = document.getElementById('img-url-input').value.trim();
            if (!url) { alert('Masukkan URL gambar.'); return; }
            
            let html = `<img src="${url}" alt="${alt}" style="${styleAttr}">`;
            if (linkUrl) {
              html = `<a href="${linkUrl}">${html}</a>`;
            }
            
            try {
              const viewFrag = ckEditorInstance.data.processor.toView(html);
              const modelFrag = ckEditorInstance.data.toModel(viewFrag);
              ckEditorInstance.model.insertContent(modelFrag);
            } catch(e) {}
            overlay.classList.remove('open');
          } else {
            // File upload tab - upload via XHR then insert
            const file = fileInput ? fileInput.files[0] : null;
            if (!file) { alert('Pilih file gambar terlebih dahulu.'); return; }
            const formData = new FormData();
            formData.append('upload', file);
            btnInsert.textContent = 'Mengunggah...';
            btnInsert.disabled = true;
            fetch('<?php echo BASE_URL; ?>admin/upload.php', { method: 'POST', body: formData })
              .then(r => r.json())
              .then(resp => {
                btnInsert.textContent = 'Sisipkan Gambar';
                btnInsert.disabled = false;
                if (resp.uploaded && resp.url) {
                  let html = `<img src="${resp.url}" alt="${alt}" style="${styleAttr}">`;
                  if (linkUrl) {
                    html = `<a href="${linkUrl}">${html}</a>`;
                  }
                  const viewFrag = ckEditorInstance.data.processor.toView(html);
                  const modelFrag = ckEditorInstance.data.toModel(viewFrag);
                  ckEditorInstance.model.insertContent(modelFrag);
                  overlay.classList.remove('open');
                } else {
                  alert('Upload gagal: ' + (resp.error?.message || 'Unknown error'));
                }
              })
              .catch(err => {
                btnInsert.textContent = 'Sisipkan Gambar';
                btnInsert.disabled = false;
                alert('Gagal mengunggah gambar.');
              });
          }
        });

        // Featured Image tab switcher
        document.querySelectorAll('.feat-tab-btn').forEach(btn => {
          btn.addEventListener('click', () => {
            document.querySelectorAll('.feat-tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.feat-tab-panel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(btn.dataset.panel).classList.add('active');
          });
        });

        // Featured image file upload preview
        const featFileInput = document.getElementById('featured-image-file-input');
        if (featFileInput) {
          featFileInput.addEventListener('change', () => {
            const file = featFileInput.files[0];
            if (file) {
              const reader = new FileReader();
              reader.onload = e => {
                // Always re-query to avoid stale DOM reference
                let previewImg = document.getElementById('featured-image-preview-img');
                const previewContainer = document.getElementById('featured-image-preview');
                let placeholder = previewContainer ? previewContainer.querySelector('.image-preview-placeholder') : null;

                if (!previewImg && previewContainer) {
                  previewImg = document.createElement('img');
                  previewImg.id = 'featured-image-preview-img';
                  previewImg.alt = 'Preview';
                  previewImg.style.cssText = 'display:block; width:100%; border-radius:6px; margin-top:8px;';
                  previewContainer.appendChild(previewImg);
                }
                if (previewImg) {
                  previewImg.src = e.target.result;
                  previewImg.style.display = 'block';
                }
                if (placeholder) placeholder.style.display = 'none';
              };
              reader.readAsDataURL(file);
            }
          });
        }

        // Featured image URL live preview
        const featUrlInput = document.getElementById('featured-image-input');
        if (featUrlInput) {
          const updateFeatPreview = () => {
            const val = featUrlInput.value.trim();
            let previewImg = document.getElementById('featured-image-preview-img');
            const previewContainer = document.getElementById('featured-image-preview');
            let placeholder = previewContainer ? previewContainer.querySelector('.image-preview-placeholder') : null;

            if (val) {
              const src = val.startsWith('http') ? val : '<?php echo BASE_URL; ?>' + val;
              if (!previewImg && previewContainer) {
                previewImg = document.createElement('img');
                previewImg.id = 'featured-image-preview-img';
                previewImg.alt = 'Preview';
                previewImg.style.cssText = 'display:block; width:100%; border-radius:6px; margin-top:8px;';
                previewContainer.appendChild(previewImg);
              }
              if (previewImg) {
                previewImg.src = src;
                previewImg.style.display = 'block';
              }
              if (placeholder) placeholder.style.display = 'none';
            } else {
              if (previewImg) previewImg.style.display = 'none';
              if (placeholder) placeholder.style.display = 'flex';
            }
          };
          featUrlInput.addEventListener('input', updateFeatPreview);
          updateFeatPreview();
        }
      });
    </script>
</head>
<body class="admin-body">
    <div class="admin-layout">
        
        <!-- Sidebar Navigation Menu -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Workspace Area -->
        <main class="admin-main">
            <div class="admin-top">
                <h1 class="admin-title">Kelola Artikel</h1>
                <div>
                    <?php if ($action === 'list'): ?>
                        <a href="?action=new" class="btn-admin btn-admin-primary">Tulis Artikel Baru</a>
                    <?php else: ?>
                        <a href="posts.php" class="btn-admin btn-admin-secondary">Kembali ke Daftar</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="admin-alert admin-alert-error" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span><?php echo sanitize($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="admin-alert admin-alert-success" role="status">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span><?php echo sanitize($success); ?></span>
                </div>
            <?php endif; ?>

            <!-- Action: WRITE / EDIT POST Form -->
            <?php if ($action === 'new' || $action === 'edit'): ?>
                <div class="admin-card">
                    <h2><?php echo $action === 'new' ? 'Tulis Artikel Baru' : 'Edit Artikel'; ?></h2>
                    <form action="" method="POST" enctype="multipart/form-data" style="margin-top: 24px;">
                        <?php echo csrf_field(); ?>
                        
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $post_data['id']; ?>">
                        <?php endif; ?>

                        <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 30px;">
                            
                            <!-- Left Column: Core content fields -->
                            <div>
                                <!-- SEO Guide Collapsible Panel -->
                                <div id="seo-guide-panel" style="background: linear-gradient(135deg, #eef2ff 0%, #f0fdf4 100%); border: 1.5px solid #c7d2fe; border-radius: 10px; padding: 16px 20px; margin-bottom: 24px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;" onclick="document.getElementById('seo-guide-body').style.display = document.getElementById('seo-guide-body').style.display === 'none' ? 'block' : 'none'; this.querySelector('.seo-guide-arrow').textContent = document.getElementById('seo-guide-body').style.display === 'none' ? '▼' : '▲';">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span style="font-size: 1.3rem;">📋</span>
                                            <div>
                                                <strong style="font-size: 0.95rem; color: #1e293b;">Panduan SEO Artikel</strong>
                                                <p style="font-size: 0.75rem; color: #64748b; margin: 2px 0 0 0;">Klik untuk buka/tutup panduan lengkap mengisi setiap field agar SEO optimal</p>
                                            </div>
                                        </div>
                                        <span class="seo-guide-arrow" style="font-size: 0.9rem; color: #6366f1; font-weight: bold;">▲</span>
                                    </div>
                                    <div id="seo-guide-body" style="display: none; margin-top: 16px; padding-top: 16px; border-top: 1px solid #c7d2fe;">
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 0.82rem;">
                                            <div style="background: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                                <strong style="color: #1e40af;">📝 Judul Artikel</strong>
                                                <p style="margin: 4px 0 0; color: #475569;">Gunakan kata kunci utama di awal. Maksimal 60 karakter. Contoh: <em>"Sewa Virtual Office Surabaya Murah 2026 - Panduan Lengkap"</em></p>
                                            </div>
                                            <div style="background: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                                <strong style="color: #1e40af;">🔗 Slug / URL</strong>
                                                <p style="margin: 4px 0 0; color: #475569;">Gunakan huruf kecil, pisahkan dengan strip (-). Sertakan kata kunci. Contoh: <em>"sewa-virtual-office-surabaya-murah"</em></p>
                                            </div>
                                            <div style="background: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                                <strong style="color: #1e40af;">📄 Excerpt / Ringkasan</strong>
                                                <p style="margin: 4px 0 0; color: #475569;">2-3 kalimat yang menjelaskan inti artikel. Digunakan sebagai preview di daftar blog dan fallback meta description. Sertakan CTA.</p>
                                            </div>
                                            <div style="background: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                                <strong style="color: #1e40af;">📰 Konten Artikel</strong>
                                                <p style="margin: 4px 0 0; color: #475569;">Minimal <strong>300 kata</strong> (ideal 800+). Gunakan heading H2/H3 untuk sub-bab. Sertakan gambar dengan alt text. Tulis paragraf yang menjawab pertanyaan pembaca.</p>
                                            </div>
                                            <div style="background: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                                <strong style="color: #1e40af;">🏷️ Meta Title (SEO Title)</strong>
                                                <p style="margin: 4px 0 0; color: #475569;">Ini yang tampil di tab browser & hasil Google. <strong>50-60 karakter</strong>. Format: <em>"Kata Kunci Utama | Brand - Slogan"</em></p>
                                            </div>
                                            <div style="background: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                                <strong style="color: #1e40af;">📝 Meta Description</strong>
                                                <p style="margin: 4px 0 0; color: #475569;">Ringkasan yang tampil di bawah judul pada hasil Google. <strong>120-160 karakter</strong>. Tulis kalimat persuasif yang membuat orang klik. Sertakan kata kunci utama + CTA. Contoh: <em>"Panduan lengkap sewa virtual office di Surabaya mulai 350rb/bln. Free meeting room &amp; legalitas PT. Hubungi kami sekarang!"</em></p>
                                            </div>
                                            <div style="background: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                                <strong style="color: #1e40af;">🖼️ Featured Image</strong>
                                                <p style="margin: 4px 0 0; color: #475569;">Gunakan gambar berkualitas tinggi (min. 1200x630px). Wajib isi alt text. Format WEBP/JPG. Gambar ini tampil di thumbnail blog, sosial media, dan Google Discover.</p>
                                            </div>
                                            <div style="background: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                                <strong style="color: #1e40af;">📂 Kategori & Tags</strong>
                                                <p style="margin: 4px 0 0; color: #475569;">Pilih 1-2 kategori utama. Tambahkan 3-5 tags yang relevan. Ini membantu Google memahami topik dan menampilkan artikel terkait.</p>
                                            </div>
                                        </div>
                                        <div style="margin-top: 12px; padding: 10px 14px; background: #fef3c7; border-radius: 8px; border: 1px solid #fde68a; font-size: 0.8rem; color: #92400e;">
                                            <strong>💡 Tips GEO (AI Search):</strong> Tulis kalimat yang jelas dan definitif di awal paragraf, seperti "Virtual Office adalah..." atau "Harga sewa kantor di Surabaya mulai dari...". AI model seperti ChatGPT dan Perplexity lebih mudah mengutip konten yang terstruktur dan informatif.
                                        </div>
                                    </div>
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <label class="form-label">Judul Artikel * <span style="font-weight: 400; font-size: 0.78rem; color: #6366f1;">— Sertakan kata kunci utama di awal judul</span></label>
                                    <input type="text" name="title" class="form-control" value="<?php echo sanitize($post_data['title'] ?? ''); ?>" placeholder="Contoh: Sewa Virtual Office Surabaya Murah - Panduan Lengkap 2026" required>
                                    <div id="slug-preview-box" class="slug-preview-container" style="display: none;">
                                        <span class="slug-preview-label">Preview URL: </span>
                                        <span class="slug-preview-val"><?php echo BASE_URL; ?>blog/<span id="slug-text">...</span>/</span>
                                    </div>
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <label class="form-label">Custom Slug <span style="font-weight: 400; font-size: 0.78rem; color: #94a3b8;">— Kosongkan untuk auto-generate. Gunakan huruf kecil + strip (-)</span></label>
                                    <input type="text" name="slug" class="form-control" value="<?php echo sanitize($post_data['slug'] ?? ''); ?>" placeholder="contoh-slug-artikel-dengan-keyword">
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <label class="form-label">Excerpt / Ringkasan Singkat <span style="font-weight: 400; font-size: 0.78rem; color: #6366f1;">— 2-3 kalimat, ini tampil di daftar blog & fallback meta description</span></label>
                                    <textarea name="excerpt" class="form-control" placeholder="Tuliskan ringkasan artikel yang menarik pembaca untuk klik dan membaca selengkapnya..." style="height:80px;"><?php echo sanitize($post_data['excerpt'] ?? ''); ?></textarea>
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                        <label class="form-label" style="margin-bottom: 0;">Konten Artikel * <span style="font-weight: 400; font-size: 0.78rem; color: #6366f1;">— Minimal 300 kata, gunakan H2/H3 untuk sub-bab</span></label>
                                        <button type="button" id="btn-insert-image" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #eef2ff; color: #4f46e5; border: 1.5px solid #c7d2fe; border-radius: 7px; font-size: 0.82rem; font-weight: 600; cursor: pointer;">
                                            🖼️ Sisipkan Gambar
                                        </button>
                                    </div>
                                    <textarea id="content-editor" name="content"><?php echo $post_data['content'] ?? ''; ?></textarea>
                                </div>


                                <!-- Content Word Count -->
                                <div style="margin-top: -10px; margin-bottom: 16px; display: flex; gap: 16px; font-size: 0.8rem; color: #64748b;">
                                    <span>Word Count: <strong id="word-count">0</strong> kata</span>
                                    <span id="word-count-warning" style="color: #d97706; display: none;">⚠ Minimal 300 kata untuk SEO optimal</span>
                                    <span id="word-count-good" style="color: #16a34a; display: none;">✓ Konten cukup untuk SEO</span>
                                </div>

                                <!-- SEO Section -->
                                <h3 style="margin: 40px 0 16px 0; border-top:1px solid #e2e8f0; padding-top:24px;">Pengaturan SEO</h3>
                                
                                <div style="margin-bottom: 20px;">
                                    <label class="form-label">Meta Title (SEO Title)</label>
                                    <input type="text" name="meta_title" class="form-control" value="<?php echo sanitize($post_data['meta_title'] ?? ''); ?>" placeholder="Masukkan meta title...">
                                    <div class="char-counter">
                                        <span class="char-counter-text">Rekomendasi: 50 - 60 karakter</span>
                                        <span class="char-counter-text"><span id="meta-title-counter" class="char-counter-num safe">0</span>/60</span>
                                    </div>
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="meta_description" class="form-control" placeholder="Masukkan ringkasan untuk Google search..." style="height:80px;"><?php echo sanitize($post_data['meta_description'] ?? ''); ?></textarea>
                                    <div class="char-counter">
                                        <span class="char-counter-text">Rekomendasi: 120 - 160 karakter</span>
                                        <span class="char-counter-text"><span id="meta-desc-counter" class="char-counter-num safe">0</span>/160</span>
                                    </div>
                                </div>

                                <!-- Google SERP Preview -->
                                <div style="margin-bottom: 20px;">
                                    <label class="form-label">Preview Google Search Result</label>
                                    <div id="serp-preview" style="background: #fff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; font-family: Arial, sans-serif; display: flex; gap: 16px; align-items: flex-start;">
                                        <!-- Featured Image Thumbnail (like Google rich results) -->
                                        <div id="serp-img-wrap" style="flex-shrink: 0; width: 108px; height: 108px; border-radius: 8px; overflow: hidden; background: #f1f5f9; display: none;">
                                            <img id="serp-img" src="" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                        <!-- Text Content -->
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 2px;">
                                                <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16'><circle cx='8' cy='8' r='8' fill='%234285f4'/></svg>" width="16" height="16" style="border-radius: 50%;">
                                                <span id="serp-site" style="font-size: 12px; color: #202124; line-height: 1.3;">Urban Office</span>
                                            </div>
                                            <div id="serp-url" style="font-size: 12px; color: #202124; margin-bottom: 4px; line-height: 1.3;"><?php echo BASE_URL; ?>blog/...</div>
                                            <div id="serp-title" style="font-size: 18px; color: #1a0dab; line-height: 1.3; margin-bottom: 4px; cursor: pointer; text-decoration: none; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Judul Artikel - Urban Office</div>
                                            <div id="serp-desc" style="font-size: 13px; color: #4d5156; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">Deskripsi artikel akan tampil di sini sebagai ringkasan di hasil pencarian Google...</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Taxonomy / Meta fields sidebar -->
                            <div>
                                <div style="background-color: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #cbd5e1; margin-bottom: 24px;">
                                    <h4 style="margin-bottom: 12px;">Publishing</h4>
                                    
                                    <div style="margin-bottom: 16px;">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-control" style="background-color: white;">
                                            <option value="draft" <?php echo (isset($post_data['status']) && $post_data['status'] === 'draft') ? 'selected' : ''; ?>>📝 Draft</option>
                                            <option value="published" <?php echo (isset($post_data['status']) && $post_data['status'] === 'published') ? 'selected' : ''; ?>>✅ Published</option>
                                            <option value="scheduled" <?php echo (isset($post_data['status']) && $post_data['status'] === 'scheduled') ? 'selected' : ''; ?>>🕐 Scheduled (Otomatis publish sesuai jadwal)</option>
                                        </select>
                                        <p style="font-size: 0.72rem; color: #64748b; margin-top: 6px;">Pilih <strong>Scheduled</strong> agar artikel otomatis ter-publish saat waktu yang ditentukan di bawah tiba.</p>
                                    </div>

                                    <div style="margin-bottom: 16px;">
                                        <label class="form-label">Tanggal Publish</label>
                                        <input type="datetime-local" name="published_at" class="form-control" value="<?php echo isset($post_data['published_at']) ? date('Y-m-d\TH:i', strtotime($post_data['published_at'])) : date('Y-m-d\TH:i'); ?>" style="background-color: white;">
                                    </div>

                                    <button type="submit" name="<?php echo $action === 'new' ? 'create' : 'update'; ?>" class="btn-admin btn-admin-primary" style="width: 100%; justify-content: center; padding: 10px;">
                                        Simpan Artikel
                                    </button>
                                </div>

                                <!-- SEO Score Checklist -->
                                <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1.5px solid #cbd5e1; margin-bottom: 24px;">
                                    <h4 style="margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">📊 SEO Score</h4>
                                    <div style="margin-bottom: 12px;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                            <span style="font-size: 0.78rem; color: #64748b;">Kesiapan SEO</span>
                                            <span id="seo-score-label" style="font-size: 0.78rem; font-weight: 700; color: #dc2626;">0% (0/7 terpenuhi)</span>
                                        </div>
                                        <div style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                            <div id="seo-score-bar" style="height: 100%; width: 0%; background: #ef4444; border-radius: 4px; transition: width 0.4s ease, background 0.4s ease;"></div>
                                        </div>
                                    </div>
                                    <div style="font-size: 0.8rem; line-height: 2;">
                                        <div><span id="seo-chk-title">⬜</span> Judul (10-70 karakter)</div>
                                        <div><span id="seo-chk-slug">⬜</span> Slug terisi & tanpa spasi</div>
                                        <div><span id="seo-chk-excerpt">⬜</span> Excerpt (min. 50 karakter)</div>
                                        <div><span id="seo-chk-metatitle">⬜</span> Meta Title (30-60 karakter)</div>
                                        <div><span id="seo-chk-metadesc">⬜</span> Meta Description (120-160 karakter)</div>
                                        <div><span id="seo-chk-image">⬜</span> Featured Image terisi</div>
                                        <div><span id="seo-chk-words">⬜</span> Konten min. 300 kata</div>
                                    </div>
                                </div>

                                <div style="background-color: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #cbd5e1; margin-bottom: 24px;">
                                    <h4 style="margin-bottom: 12px;">🖼️ Featured Image</h4>
                                    <p style="font-size: 0.75rem; color:#64748b; margin-bottom: 10px;">Gambar ini tampil sebagai banner utama di halaman artikel dan thumbnail di daftar blog.</p>
                                    <div class="feat-tab-bar">
                                        <button type="button" class="feat-tab-btn active" data-panel="feat-panel-url">🔗 URL / Path</button>
                                        <button type="button" class="feat-tab-btn" data-panel="feat-panel-upload">⬆️ Upload File</button>
                                    </div>
                                    <!-- URL Tab -->
                                    <div class="feat-tab-panel active" id="feat-panel-url">
                                        <input type="text" id="featured-image-input" name="featured_image" class="form-control" value="<?php echo sanitize($post_data['featured_image'] ?? ''); ?>" placeholder="assets/images/... atau URL eksternal" style="font-size:0.85rem;">
                                        <p style="font-size: 0.72rem; color:#94a3b8; margin-top: 6px;">Path relatif (contoh: assets/images/blog/foto.jpg) atau URL lengkap.</p>
                                    </div>
                                    <!-- Upload Tab -->
                                    <div class="feat-tab-panel" id="feat-panel-upload">
                                        <label style="display:block; padding: 16px; border: 2px dashed #cbd5e1; border-radius: 8px; text-align: center; cursor: pointer; color: #64748b; font-size: 0.85rem; background: #fff;">
                                            <input type="file" id="featured-image-file-input" name="featured_image_file" accept="image/*" style="display:none;">
                                            <span style="font-size: 1.5rem;">📁</span><br>
                                            Klik untuk pilih file gambar<br>
                                            <span style="font-size:0.72rem; color:#94a3b8;">JPG, PNG, WEBP, GIF (maks 5MB)</span>
                                        </label>
                                    </div>
                                    <!-- Shared Preview -->
                                    <div id="featured-image-preview" class="image-preview-container" style="margin-top: 12px;">
                                        <div class="image-preview-placeholder">Belum ada gambar terpilih</div>
                                        <img id="featured-image-preview-img" src="" alt="Preview" style="display:none; width:100%; border-radius:6px; margin-top:8px;">
                                    </div>
                                </div>

                                <!-- Categories checkboxes -->
                                <div style="background-color: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #cbd5e1; margin-bottom: 24px;">
                                    <h4 style="margin-bottom: 12px;">Kategori</h4>
                                    <div style="max-height: 150px; overflow-y: auto;">
                                        <?php if (!empty($all_categories)): foreach ($all_categories as $cat): ?>
                                            <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 0.9rem;">
                                                <input type="checkbox" name="categories[]" value="<?php echo $cat['id']; ?>" <?php echo in_array($cat['id'], $post_cats_ids) ? 'checked' : ''; ?>>
                                                <?php echo sanitize($cat['name']); ?>
                                            </label>
                                        <?php endforeach; else: ?>
                                            <p style="font-size: 0.85rem; color: #64748b;">Belum ada kategori.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Tags checkboxes -->
                                <div style="background-color: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #cbd5e1;">
                                    <h4 style="margin-bottom: 12px;">Tags</h4>
                                    <div style="max-height: 150px; overflow-y: auto;">
                                        <?php if (!empty($all_tags)): foreach ($all_tags as $tag): ?>
                                            <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 0.9rem;">
                                                <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>" <?php echo in_array($tag['id'], $post_tags_ids) ? 'checked' : ''; ?>>
                                                #<?php echo sanitize($tag['name']); ?>
                                            </label>
                                        <?php endforeach; else: ?>
                                            <p style="font-size: 0.85rem; color: #64748b;">Belum ada tags.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>

            <!-- Action: LIST POSTS view -->
            <?php else:
                $avg_views = $posts_kpi['total'] > 0 ? round($posts_kpi['total_views'] / $posts_kpi['total'], 1) : 0;
                $max_top_views = !empty($top_viewed_posts) ? max(1, (int) $top_viewed_posts[0]['views']) : 1;
                $showing_from = $total_posts > 0 ? (($page - 1) * $limit + 1) : 0;
                $showing_to   = min($total_posts, $page * $limit);

                // Compute windowed pagination sequence: 1 ... (p-1) p (p+1) ... total_pages
                $pager_items = [];
                if ($total_pages <= 7) {
                    for ($i = 1; $i <= $total_pages; $i++) {
                        $pager_items[] = $i;
                    }
                } else {
                    $pager_items[] = 1;
                    $win_start = max(2, $page - 1);
                    $win_end   = min($total_pages - 1, $page + 1);
                    if ($page <= 3) {
                        $win_end = 4;
                    } elseif ($page >= $total_pages - 2) {
                        $win_start = $total_pages - 3;
                    }
                    if ($win_start > 2) {
                        $pager_items[] = 'ellipsis';
                    }
                    for ($i = $win_start; $i <= $win_end; $i++) {
                        $pager_items[] = $i;
                    }
                    if ($win_end < $total_pages - 1) {
                        $pager_items[] = 'ellipsis';
                    }
                    $pager_items[] = $total_pages;
                }
            ?>
                <!-- 4 Blog KPI Summary Cards -->
                <section class="posts-kpi-grid" aria-label="Ringkasan Statistik Artikel">
                    <div class="posts-kpi-card" style="--pk-accent: #1e40af; --pk-bg: #eff6ff;">
                        <div class="posts-kpi-top">
                            <span class="posts-kpi-label">Total Artikel</span>
                            <span class="posts-kpi-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            </span>
                        </div>
                        <div class="posts-kpi-val"><?php echo number_format($posts_kpi['total']); ?></div>
                        <div class="posts-kpi-sub">Published <strong class="admin-num"><?php echo number_format($posts_kpi['published']); ?></strong> · Draft <strong class="admin-num"><?php echo number_format($posts_kpi['draft']); ?></strong></div>
                    </div>

                    <div class="posts-kpi-card" style="--pk-accent: #059669; --pk-bg: #ecfdf5;">
                        <div class="posts-kpi-top">
                            <span class="posts-kpi-label">Total Views Blog</span>
                            <span class="posts-kpi-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </span>
                        </div>
                        <div class="posts-kpi-val"><?php echo number_format($posts_kpi['total_views']); ?></div>
                        <div class="posts-kpi-sub">Akumulasi pembaca seluruh artikel</div>
                    </div>

                    <div class="posts-kpi-card" style="--pk-accent: #d97706; --pk-bg: #fff7ed;">
                        <div class="posts-kpi-top">
                            <span class="posts-kpi-label">Rata-rata Views</span>
                            <span class="posts-kpi-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                            </span>
                        </div>
                        <div class="posts-kpi-val"><?php echo number_format($avg_views, 1); ?></div>
                        <div class="posts-kpi-sub">Rata-rata views per artikel</div>
                    </div>

                    <div class="posts-kpi-card" style="--pk-accent: #7c3aed; --pk-bg: #f5f3ff;">
                        <div class="posts-kpi-top">
                            <span class="posts-kpi-label">Terbit Bulan Ini</span>
                            <span class="posts-kpi-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            </span>
                        </div>
                        <div class="posts-kpi-val"><?php echo number_format($posts_kpi['this_month']); ?></div>
                        <div class="posts-kpi-sub">Periode <?php echo date('M Y'); ?> · Scheduled <strong class="admin-num"><?php echo number_format($posts_kpi['scheduled']); ?></strong></div>
                    </div>
                </section>

                <!-- 2-Column Insights: Monthly Chart + Top 5 Popular Articles -->
                <section class="posts-insights-grid" aria-label="Grafik dan Performa Artikel">
                    <div class="admin-card">
                        <div class="posts-card-head">
                            <div>
                                <h2>Tren Publikasi &amp; Views Artikel</h2>
                                <p class="posts-card-sub">Performa konten blog per bulan (hingga 12 bulan terakhir)</p>
                            </div>
                            <div class="posts-seg-toggle" role="group" aria-label="Pilih metrik grafik">
                                <button type="button" class="posts-seg-btn active" data-posts-metric="combined" aria-pressed="true">Gabungan</button>
                                <button type="button" class="posts-seg-btn" data-posts-metric="posts" aria-pressed="false">Artikel</button>
                                <button type="button" class="posts-seg-btn" data-posts-metric="views" aria-pressed="false">Views</button>
                            </div>
                        </div>

                        <?php if (!empty($posts_chart_series)): ?>
                            <div class="posts-chart-wrap">
                                <canvas id="postsTrendChart" role="img" aria-label="Grafik bulanan jumlah artikel terbit dan total views blog"></canvas>
                            </div>
                            <table class="sr-only">
                                <caption>Data Bulanan Publikasi dan Views Artikel</caption>
                                <thead><tr><th>Bulan</th><th>Artikel Terbit</th><th>Total Views</th></tr></thead>
                                <tbody>
                                    <?php foreach ($posts_chart_series as $pt): ?>
                                        <tr>
                                            <td><?php echo sanitize($pt['label']); ?></td>
                                            <td><?php echo $pt['post_count']; ?></td>
                                            <td><?php echo $pt['views']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="admin-empty">Belum ada data artikel untuk ditampilkan pada grafik.</div>
                        <?php endif; ?>
                    </div>

                    <div class="admin-card">
                        <div class="posts-card-head">
                            <div>
                                <h2>Top 5 Artikel Terpopuler</h2>
                                <p class="posts-card-sub">Berdasarkan jumlah views tertinggi</p>
                            </div>
                            <a href="<?php echo sanitize($build_posts_url(['sort' => 'views_desc', 'page' => ''])); ?>" class="btn-admin btn-admin-secondary btn-admin-sm">Urutkan Views</a>
                        </div>

                        <?php if (!empty($top_viewed_posts)): ?>
                            <div class="posts-top-list">
                                <?php foreach ($top_viewed_posts as $idx => $tp):
                                    $pct = round(((int) $tp['views'] / $max_top_views) * 100);
                                ?>
                                    <div class="posts-top-item">
                                        <div class="posts-top-row">
                                            <span class="posts-top-rank">#<?php echo $idx + 1; ?></span>
                                            <a href="?action=edit&id=<?php echo (int) $tp['id']; ?>" class="posts-top-link" title="<?php echo sanitize($tp['title']); ?>">
                                                <?php echo sanitize($tp['title']); ?>
                                            </a>
                                            <span class="posts-top-views"><?php echo number_format((int) $tp['views']); ?></span>
                                        </div>
                                        <div class="posts-bar-track" aria-hidden="true">
                                            <div class="posts-bar-fill" style="width: <?php echo max(4, min(100, $pct)); ?>%;"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="admin-empty">Belum ada data views artikel.</div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Filter, Search & Sort Toolbar -->
                <div class="admin-toolbar">
                    <div class="admin-pill-group" role="navigation" aria-label="Filter status artikel">
                        <a href="<?php echo sanitize($build_posts_url(['status' => '', 'page' => ''])); ?>" class="admin-pill<?php echo $status_filter === '' ? ' active' : ''; ?>">Semua</a>
                        <a href="<?php echo sanitize($build_posts_url(['status' => 'published', 'page' => ''])); ?>" class="admin-pill<?php echo $status_filter === 'published' ? ' active' : ''; ?>">Published</a>
                        <a href="<?php echo sanitize($build_posts_url(['status' => 'draft', 'page' => ''])); ?>" class="admin-pill<?php echo $status_filter === 'draft' ? ' active' : ''; ?>">Draft</a>
                        <a href="<?php echo sanitize($build_posts_url(['status' => 'scheduled', 'page' => ''])); ?>" class="admin-pill<?php echo $status_filter === 'scheduled' ? ' active' : ''; ?>">Scheduled</a>
                    </div>

                    <form method="GET" action="<?php echo BASE_URL; ?>admin/posts.php" class="posts-search-form">
                        <?php if ($status_filter !== ''): ?>
                            <input type="hidden" name="status" value="<?php echo sanitize($status_filter); ?>">
                        <?php endif; ?>
                        <input type="search" name="q" value="<?php echo sanitize($search_q); ?>" class="posts-filter-input" style="width: 220px;" placeholder="Cari judul atau slug..." aria-label="Cari artikel">
                        <select name="sort" class="posts-filter-input" aria-label="Urutkan artikel" onchange="this.form.submit()">
                            <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                            <option value="views_desc" <?php echo $sort_by === 'views_desc' ? 'selected' : ''; ?>>Views Terbanyak</option>
                            <option value="views_asc" <?php echo $sort_by === 'views_asc' ? 'selected' : ''; ?>>Views Tersedikit</option>
                            <option value="title_asc" <?php echo $sort_by === 'title_asc' ? 'selected' : ''; ?>>Judul A–Z</option>
                        </select>
                        <button type="submit" class="btn-admin btn-admin-primary btn-admin-sm" style="height: 35px; padding: 0 12px;">Cari</button>
                        <?php if ($search_q !== '' || $status_filter !== '' || $sort_by !== 'newest'): ?>
                            <a href="<?php echo BASE_URL; ?>admin/posts.php" class="btn-admin btn-admin-secondary btn-admin-sm" style="height: 35px; padding: 0 10px;">Reset</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Posts Table Card -->
                <div class="admin-card">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Judul</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Views</th>
                                    <th>Tanggal Publish</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($posts_list)): foreach ($posts_list as $post): ?>
                                    <tr>
                                        <td>
                                            <strong style="color: var(--admin-ink);"><?php echo sanitize($post['title']); ?></strong><br>
                                            <span class="admin-num" style="font-size: 0.77rem; color: var(--admin-subtle);">/blog/<?php echo sanitize($post['slug']); ?>/</span>
                                        </td>
                                        <td><?php echo sanitize($post['author_name']); ?></td>
                                        <td>
                                            <?php
                                                $badge_class = 'badge-warning';
                                                $badge_label = 'Draft';
                                                $dot_color   = '#ca8a04';
                                                if ($post['status'] === 'published') {
                                                    $badge_class = 'badge-success';
                                                    $badge_label = 'Published';
                                                    $dot_color   = '#059669';
                                                } elseif ($post['status'] === 'scheduled') {
                                                    $badge_class = 'badge-info';
                                                    $badge_label = 'Scheduled';
                                                    $dot_color   = '#2563eb';
                                                }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <span class="posts-dot" style="background: <?php echo $dot_color; ?>;" aria-hidden="true"></span>
                                                <?php echo $badge_label; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="admin-num" style="display: inline-flex; align-items: center; gap: 5px; font-size: 0.82rem; color: var(--admin-muted);">
                                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                <?php echo number_format($post['views'] ?? 0); ?>
                                            </span>
                                        </td>
                                        <td class="admin-num" style="font-size: 0.8rem; color: var(--admin-subtle); white-space: nowrap;"><?php echo $post['published_at'] ? date('d M Y H:i', strtotime($post['published_at'])) : '—'; ?></td>
                                        <td style="white-space: nowrap;">
                                            <a href="?action=edit&id=<?php echo $post['id']; ?>" class="btn-admin btn-admin-secondary btn-admin-sm">Edit</a>
                                            <a href="?action=delete&id=<?php echo $post['id']; ?>" class="btn-admin btn-admin-danger btn-admin-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus artikel ini?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="6" class="admin-empty">
                                            <?php echo $search_q !== '' ? 'Tidak ada artikel yang cocok dengan kata kunci tersebut.' : 'Belum ada artikel yang ditulis.'; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Compact Windowed Pagination -->
                    <?php if ($total_posts > 0): ?>
                        <div class="posts-pager-bar">
                            <div class="posts-pager-info">
                                Menampilkan <strong><?php echo number_format($showing_from); ?>–<?php echo number_format($showing_to); ?></strong> dari <strong><?php echo number_format($total_posts); ?></strong> artikel
                                (Halaman <strong><?php echo $page; ?></strong> / <strong><?php echo $total_pages; ?></strong>)
                            </div>

                            <?php if ($total_pages > 1): ?>
                                <nav class="posts-pager-nav" aria-label="Navigasi halaman artikel">
                                    <a href="<?php echo sanitize($build_posts_url(['page' => max(1, $page - 1)])); ?>"
                                       class="posts-page-btn<?php echo $page <= 1 ? ' disabled' : ''; ?>"
                                       aria-label="Halaman sebelumnya"
                                       <?php echo $page <= 1 ? 'aria-disabled="true" tabindex="-1"' : ''; ?>>
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline></svg>
                                        Prev
                                    </a>

                                    <?php foreach ($pager_items as $item): ?>
                                        <?php if ($item === 'ellipsis'): ?>
                                            <span class="posts-page-ellipsis" aria-hidden="true">&hellip;</span>
                                        <?php else: ?>
                                            <a href="<?php echo sanitize($build_posts_url(['page' => $item])); ?>"
                                               class="posts-page-btn<?php echo $item === $page ? ' active' : ''; ?>"
                                               <?php echo $item === $page ? 'aria-current="page"' : ''; ?>>
                                                <?php echo $item; ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>

                                    <a href="<?php echo sanitize($build_posts_url(['page' => min($total_pages, $page + 1)])); ?>"
                                       class="posts-page-btn<?php echo $page >= $total_pages ? ' disabled' : ''; ?>"
                                       aria-label="Halaman berikutnya"
                                       <?php echo $page >= $total_pages ? 'aria-disabled="true" tabindex="-1"' : ''; ?>>
                                        Next
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </a>
                                </nav>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($posts_chart_series)): ?>
                <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
                <script>
                (function () {
                    var canvas = document.getElementById('postsTrendChart');
                    if (!canvas || typeof Chart === 'undefined') return;

                    var series = <?php echo json_encode($posts_chart_series, JSON_UNESCAPED_UNICODE); ?>;
                    var labels = series.map(function (d) { return d.label; });
                    var counts = series.map(function (d) { return d.post_count; });
                    var views  = series.map(function (d) { return d.views; });
                    var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                    var chart = null;

                    function buildChart(mode) {
                        if (chart) chart.destroy();

                        var datasets = [];
                        var scales = {
                            x: {
                                grid: { display: false },
                                border: { color: '#e2e8f0' },
                                ticks: { color: '#64748b', font: { family: "'Fira Sans', sans-serif", size: 11 } }
                            }
                        };

                        if (mode === 'combined' || mode === 'posts') {
                            datasets.push({
                                type: 'bar',
                                label: 'Artikel Terbit',
                                data: counts,
                                yAxisID: 'yPosts',
                                backgroundColor: function (context) {
                                    var c = context.chart;
                                    if (!c.chartArea) return '#2563eb';
                                    var g = c.ctx.createLinearGradient(0, c.chartArea.top, 0, c.chartArea.bottom);
                                    g.addColorStop(0, '#1e40af');
                                    g.addColorStop(1, '#3b82f6');
                                    return g;
                                },
                                hoverBackgroundColor: '#1d4ed8',
                                borderRadius: { topLeft: 6, topRight: 6 },
                                borderSkipped: 'bottom',
                                maxBarThickness: 34
                            });
                            scales.yPosts = {
                                type: 'linear',
                                position: 'left',
                                beginAtZero: true,
                                border: { display: false },
                                grid: { color: '#f1f5f9', drawTicks: false },
                                ticks: { color: '#64748b', precision: 0, padding: 8, font: { family: "'Fira Code', monospace", size: 11 } }
                            };
                        }

                        if (mode === 'combined' || mode === 'views') {
                            datasets.push({
                                type: mode === 'views' ? 'bar' : 'line',
                                label: 'Total Views',
                                data: views,
                                yAxisID: mode === 'combined' ? 'yViews' : 'yPosts',
                                borderColor: '#d97706',
                                backgroundColor: mode === 'views' ? '#f59e0b' : 'rgba(217, 119, 6, 0.14)',
                                hoverBackgroundColor: '#d97706',
                                borderWidth: mode === 'views' ? 0 : 2.5,
                                borderRadius: mode === 'views' ? { topLeft: 6, topRight: 6 } : 0,
                                maxBarThickness: 34,
                                fill: mode === 'combined',
                                tension: 0.35,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#d97706',
                                pointBorderWidth: 2
                            });

                            if (mode === 'combined') {
                                scales.yViews = {
                                    type: 'linear',
                                    position: 'right',
                                    beginAtZero: true,
                                    border: { display: false },
                                    grid: { drawOnChartArea: false },
                                    ticks: { color: '#b45309', precision: 0, padding: 8, font: { family: "'Fira Code', monospace", size: 11 } }
                                };
                            } else {
                                scales.yPosts = {
                                    type: 'linear',
                                    position: 'left',
                                    beginAtZero: true,
                                    border: { display: false },
                                    grid: { color: '#f1f5f9', drawTicks: false },
                                    ticks: { color: '#64748b', precision: 0, padding: 8, font: { family: "'Fira Code', monospace", size: 11 } }
                                };
                            }
                        }

                        chart = new Chart(canvas.getContext('2d'), {
                            data: { labels: labels, datasets: datasets },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: prefersReduced ? false : { duration: 380, easing: 'easeOutQuart' },
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    legend: {
                                        display: mode === 'combined',
                                        position: 'top',
                                        align: 'start',
                                        labels: {
                                            boxWidth: 10,
                                            boxHeight: 10,
                                            usePointStyle: true,
                                            color: '#475569',
                                            font: { family: "'Fira Sans', sans-serif", size: 12, weight: '600' }
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: '#0f172a',
                                        titleColor: '#f8fafc',
                                        bodyColor: '#e2e8f0',
                                        padding: 11,
                                        cornerRadius: 8,
                                        titleFont: { family: "'Fira Sans', sans-serif", size: 12, weight: '600' },
                                        bodyFont: { family: "'Fira Code', monospace", size: 12 }
                                    }
                                },
                                scales: scales
                            }
                        });
                    }

                    var btns = document.querySelectorAll('.posts-seg-btn[data-posts-metric]');
                    btns.forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            var mode = btn.getAttribute('data-posts-metric');
                            btns.forEach(function (b) {
                                var active = b === btn;
                                b.classList.toggle('active', active);
                                b.setAttribute('aria-pressed', active ? 'true' : 'false');
                            });
                            buildChart(mode);
                        });
                    });

                    buildChart('combined');
                })();
                </script>
                <?php endif; ?>
            <?php endif; ?>

        </main>
    </div>

    <!-- Friendly UI Interactive Scripts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const titleInput = document.querySelector('input[name="title"]');
        const slugInput = document.querySelector('input[name="slug"]');
        const slugPreviewBox = document.getElementById('slug-preview-box');
        const slugText = document.getElementById('slug-text');

        const imgInput = document.getElementById('featured-image-input');
        const imgPreview = document.getElementById('featured-image-preview');
        const baseUrl = "<?php echo BASE_URL; ?>";

        const metaTitleInput = document.querySelector('input[name="meta_title"]');
        const metaTitleCounter = document.getElementById('meta-title-counter');
        const metaDescInput = document.querySelector('textarea[name="meta_description"]');
        const metaDescCounter = document.getElementById('meta-desc-counter');

        // 1. Live Slug Preview
        if (titleInput && slugInput && slugPreviewBox && slugText) {
            function updateSlugPreview() {
                let slugVal = slugInput.value.trim();
                if (slugVal === '' && titleInput.value.trim() !== '') {
                    slugVal = titleInput.value.trim()
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                }
                
                if (slugVal !== '') {
                    slugText.textContent = slugVal;
                    slugPreviewBox.style.display = 'block';
                } else {
                    slugPreviewBox.style.display = 'none';
                }
            }

            titleInput.addEventListener('input', updateSlugPreview);
            slugInput.addEventListener('input', updateSlugPreview);
            updateSlugPreview();
        }

        // 2. Live Featured Image Preview (URL input)
        if (imgInput && imgPreview) {
            function updateImagePreview() {
                // Always re-query the img element to avoid stale DOM reference
                let previewImg = document.getElementById('featured-image-preview-img');
                let placeholder = imgPreview.querySelector('.image-preview-placeholder');

                const val = imgInput.value.trim();
                if (val === '') {
                    if (previewImg) previewImg.style.display = 'none';
                    if (placeholder) placeholder.style.display = 'flex';
                } else {
                    let finalUrl = val;
                    if (!val.startsWith('http://') && !val.startsWith('https://') && !val.startsWith('//')) {
                        const cleanVal = val.startsWith('/') ? val.substring(1) : val;
                        finalUrl = baseUrl + cleanVal;
                    }
                    if (!previewImg) {
                        // Recreate img element if it was removed
                        if (placeholder) placeholder.style.display = 'none';
                        previewImg = document.createElement('img');
                        previewImg.id = 'featured-image-preview-img';
                        previewImg.alt = 'Featured Image Preview';
                        previewImg.style.cssText = 'display:block; width:100%; border-radius:6px; margin-top:8px;';
                        imgPreview.appendChild(previewImg);
                    }
                    previewImg.src = finalUrl;
                    previewImg.style.display = 'block';
                    if (placeholder) placeholder.style.display = 'none';
                }
            }

            imgInput.addEventListener('input', updateImagePreview);
            updateImagePreview();
        }

        // 3. SEO Character Counters
        function updateCounter(input, counterEl, minSafe, maxSafe) {
            if (!input || !counterEl) return;
            const len = input.value.length;
            counterEl.textContent = len;
            
            counterEl.className = 'char-counter-num';
            if (len === 0) {
                counterEl.classList.add('safe');
            } else if (len < minSafe) {
                counterEl.classList.add('warn');
            } else if (len <= maxSafe) {
                counterEl.classList.add('safe');
            } else {
                counterEl.classList.add('danger');
            }
        }

        if (metaTitleInput && metaTitleCounter) {
            metaTitleInput.addEventListener('input', () => updateCounter(metaTitleInput, metaTitleCounter, 45, 60));
            updateCounter(metaTitleInput, metaTitleCounter, 45, 60);
        }

        if (metaDescInput && metaDescCounter) {
            metaDescInput.addEventListener('input', () => updateCounter(metaDescInput, metaDescCounter, 120, 160));
            updateCounter(metaDescInput, metaDescCounter, 120, 160);
        }

        // 4. Live Google SERP Preview
        const serpTitle = document.getElementById('serp-title');
        const serpUrl = document.getElementById('serp-url');
        const serpDesc = document.getElementById('serp-desc');
        const serpImg = document.getElementById('serp-img');
        const serpImgWrap = document.getElementById('serp-img-wrap');

        function updateSerpPreview() {
            // Title: use meta_title if filled, else title + suffix
            let title = metaTitleInput && metaTitleInput.value.trim() ? metaTitleInput.value.trim() : (titleInput && titleInput.value.trim() ? titleInput.value.trim() + ' - Urban Office Blog' : 'Judul Artikel - Urban Office');
            if (title.length > 65) title = title.substring(0, 62) + '...';
            if (serpTitle) serpTitle.textContent = title;

            // URL
            let slug = slugInput && slugInput.value.trim() ? slugInput.value.trim() : (titleInput && titleInput.value.trim() ? titleInput.value.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '') : '...');
            if (serpUrl) serpUrl.textContent = baseUrl + 'blog/' + slug + '/';

            // Description: use meta_description if filled, else excerpt
            let desc = metaDescInput && metaDescInput.value.trim() ? metaDescInput.value.trim() : (document.querySelector('textarea[name="excerpt"]') && document.querySelector('textarea[name="excerpt"]').value.trim() ? document.querySelector('textarea[name="excerpt"]').value.trim() : 'Deskripsi artikel akan tampil di sini sebagai ringkasan di hasil pencarian Google...');
            if (desc.length > 165) desc = desc.substring(0, 162) + '...';
            if (serpDesc) serpDesc.textContent = desc;

            // Featured Image in SERP thumbnail
            let imgUrl = imgInput && imgInput.value.trim() ? imgInput.value.trim() : '';
            if (imgUrl && serpImg && serpImgWrap) {
                if (!imgUrl.startsWith('http://') && !imgUrl.startsWith('https://') && !imgUrl.startsWith('//')) {
                    const cleanVal = imgUrl.startsWith('/') ? imgUrl.substring(1) : imgUrl;
                    imgUrl = baseUrl + cleanVal;
                }
                serpImg.src = imgUrl;
                serpImgWrap.style.display = 'block';
            } else if (serpImgWrap) {
                serpImgWrap.style.display = 'none';
            }

            // Update SEO Score
            updateSeoScore();
        }

        if (titleInput) titleInput.addEventListener('input', updateSerpPreview);
        if (slugInput) slugInput.addEventListener('input', updateSerpPreview);
        if (metaTitleInput) metaTitleInput.addEventListener('input', updateSerpPreview);
        if (metaDescInput) metaDescInput.addEventListener('input', updateSerpPreview);
        if (imgInput) imgInput.addEventListener('input', updateSerpPreview);
        const excerptInput = document.querySelector('textarea[name="excerpt"]');
        if (excerptInput) excerptInput.addEventListener('input', updateSerpPreview);
        updateSerpPreview();

        // 5. SEO Score Checklist
        function updateSeoScore() {
            const checks = [
                { id: 'seo-chk-title', pass: titleInput && titleInput.value.trim().length >= 10 && titleInput.value.trim().length <= 70 },
                { id: 'seo-chk-slug', pass: slugInput && slugInput.value.trim().length > 0 && slugInput.value.trim().indexOf(' ') === -1 },
                { id: 'seo-chk-excerpt', pass: excerptInput && excerptInput.value.trim().length >= 50 },
                { id: 'seo-chk-metatitle', pass: metaTitleInput && metaTitleInput.value.length >= 30 && metaTitleInput.value.length <= 60 },
                { id: 'seo-chk-metadesc', pass: metaDescInput && metaDescInput.value.length >= 120 && metaDescInput.value.length <= 160 },
                { id: 'seo-chk-image', pass: imgInput && imgInput.value.trim().length > 0 }
            ];
            let passed = 0;
            checks.forEach(c => {
                const el = document.getElementById(c.id);
                if (el) {
                    el.textContent = c.pass ? '✅' : '⬜';
                    el.style.opacity = c.pass ? '1' : '0.5';
                }
                if (c.pass) passed++;
            });
            // Word count check (async)
            const wcEl = document.getElementById('word-count');
            const wc = wcEl ? parseInt(wcEl.textContent) || 0 : 0;
            const wcCheck = document.getElementById('seo-chk-words');
            if (wcCheck) {
                const wcPass = wc >= 300;
                wcCheck.textContent = wcPass ? '✅' : '⬜';
                wcCheck.style.opacity = wcPass ? '1' : '0.5';
                if (wcPass) passed++;
            } else { passed = Math.min(passed, checks.length); }

            // Update score bar
            const total = checks.length + 1; // +1 for word count
            const pct = Math.round((passed / total) * 100);
            const scoreBar = document.getElementById('seo-score-bar');
            const scoreLabel = document.getElementById('seo-score-label');
            if (scoreBar) {
                scoreBar.style.width = pct + '%';
                scoreBar.style.background = pct >= 80 ? '#22c55e' : (pct >= 50 ? '#f59e0b' : '#ef4444');
            }
            if (scoreLabel) {
                scoreLabel.textContent = pct + '% (' + passed + '/' + total + ' terpenuhi)';
                scoreLabel.style.color = pct >= 80 ? '#16a34a' : (pct >= 50 ? '#d97706' : '#dc2626');
            }
        }

        // 6. Word Count from CKEditor content
        const wordCountEl = document.getElementById('word-count');
        const wordWarning = document.getElementById('word-count-warning');
        const wordGood = document.getElementById('word-count-good');
        const contentTextarea = document.getElementById('content-editor');

        function updateWordCount() {
            let text = '';
            if (ckEditorInstance) {
                // Get text from CKEditor model
                try {
                    const data = ckEditorInstance.getData();
                    text = data.replace(/<[^>]*>/g, ' ').replace(/&nbsp;/g, ' ').trim();
                } catch(e) {
                    text = contentTextarea ? contentTextarea.value.replace(/<[^>]*>/g, ' ').trim() : '';
                }
            } else if (contentTextarea) {
                text = contentTextarea.value.replace(/<[^>]*>/g, ' ').trim();
            }
            const words = text.split(/\s+/).filter(w => w.length > 0).length;
            if (wordCountEl) wordCountEl.textContent = words;
            if (wordWarning) wordWarning.style.display = words < 300 ? 'inline' : 'none';
            if (wordGood) wordGood.style.display = words >= 300 ? 'inline' : 'none';
        }

        // Update word count periodically (CKEditor may load async)
        const wordCountInterval = setInterval(() => {
            if (ckEditorInstance) {
                ckEditorInstance.model.document.on('change:data', () => updateWordCount());
                updateWordCount();
                clearInterval(wordCountInterval);
            }
        }, 1000);
        // Also do initial count from textarea
        updateWordCount();

        // Re-run SEO score after word count updates
        const seoScoreInterval = setInterval(() => {
            if (ckEditorInstance) {
                updateSeoScore();
                clearInterval(seoScoreInterval);
            }
        }, 2000);
    });
    </script>
</body>

<!-- ===== Image Insert Dialog Modal ===== -->
<div class="img-insert-overlay" id="img-insert-overlay">
    <div class="img-insert-modal">
        <h3>🖼️ Sisipkan Gambar ke Artikel</h3>

        <!-- Source tabs -->
        <div class="img-tab-bar">
            <button type="button" class="img-tab-btn active" data-tab="img-tab-url">🔗 Dari URL</button>
            <button type="button" class="img-tab-btn" data-tab="img-tab-upload">⬆️ Upload File</button>
        </div>

        <!-- URL panel -->
        <div class="img-tab-panel active" id="img-tab-url">
            <label style="display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 600; color: #374151;">URL Gambar</label>
            <input type="text" id="img-url-input" placeholder="https://... atau assets/images/blog/foto.jpg"
                style="width:100%; padding: 9px 12px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 0.85rem; margin-bottom: 0;">
        </div>

        <!-- Upload panel -->
        <div class="img-tab-panel" id="img-tab-upload">
            <label style="display:block; padding: 18px; border: 2px dashed #cbd5e1; border-radius: 8px; text-align: center; cursor: pointer; color: #64748b; font-size: 0.85rem; background: #f8fafc;">
                <input type="file" id="img-upload-file" accept="image/*" style="display:none;">
                <span style="font-size: 2rem;">📁</span><br>
                Klik untuk pilih file gambar<br>
                <span style="font-size:0.75rem; color:#94a3b8;">JPG, PNG, WEBP, GIF (maks 5MB)</span>
            </label>
            <img id="img-upload-preview" src="" alt="Preview" style="display:none; width:100%; border-radius:8px; margin-top:10px; max-height: 160px; object-fit:contain;">
        </div>

        <!-- Shared options -->
        <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid #f1f5f9;">
            <div style="margin-bottom: 10px;">
                <label style="display:block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Teks Alt (Alt Text)</label>
                <input type="text" id="img-alt" placeholder="Deskripsi singkat gambar untuk SEO"
                    style="width:100%; padding: 8px 10px; border-radius: 7px; border: 1.5px solid #e2e8f0; font-size: 0.85rem;">
            </div>
            <div style="margin-bottom: 10px;">
                <label style="display:block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Tautan Link Gambar (Optional)</label>
                <input type="text" id="img-link-url" placeholder="https://contoh-link.com"
                    style="width:100%; padding: 8px 10px; border-radius: 7px; border: 1.5px solid #e2e8f0; font-size: 0.85rem;">
            </div>
            <div class="img-option-row">
                <label>
                    <span style="display:block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Alignment</span>
                    <select id="img-align">
                        <option value="none">Tanpa Align (Full Width)</option>
                        <option value="left">Kiri (Float Left)</option>
                        <option value="right">Kanan (Float Right)</option>
                    </select>
                </label>
                <label>
                    <span style="display:block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Lebar (px) – kosong = auto</span>
                    <input type="number" id="img-width" placeholder="mis: 400" min="50" max="1200">
                </label>
            </div>
        </div>

        <div class="img-insert-actions">
            <button type="button" id="img-insert-close" style="padding: 9px 20px; border-radius: 8px; border: 1.5px solid #e2e8f0; background: #f8fafc; color: #64748b; font-size: 0.9rem; font-weight: 600; cursor: pointer;">Batal</button>
            <button type="button" id="img-insert-confirm" style="padding: 9px 20px; border-radius: 8px; border: none; background: #6366f1; color: #fff; font-size: 0.9rem; font-weight: 600; cursor: pointer;">Sisipkan Gambar</button>
        </div>
    </div>
</div>

</html>
