<?php
/**
 * Shared Admin Sidebar Component
 */
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <div class="sidebar-brand">URBAN OFFICE CMS</div>
    <ul class="sidebar-menu">
        <li><a href="<?php echo BASE_URL; ?>admin/index.php" class="sidebar-link<?php echo $current_page === 'index.php' ? ' active' : ''; ?>">Overview</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/posts.php" class="sidebar-link<?php echo $current_page === 'posts.php' ? ' active' : ''; ?>">Manage Posts</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/categories.php" class="sidebar-link<?php echo $current_page === 'categories.php' ? ' active' : ''; ?>">Categories</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/tags.php" class="sidebar-link<?php echo $current_page === 'tags.php' ? ' active' : ''; ?>">Tags</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/pages.php" class="sidebar-link<?php echo $current_page === 'pages.php' ? ' active' : ''; ?>">SEO Landing Pages</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/popups.php" class="sidebar-link<?php echo $current_page === 'popups.php' ? ' active' : ''; ?>">Pop-up Banners</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/leads.php" class="sidebar-link<?php echo $current_page === 'leads.php' ? ' active' : ''; ?>">Lead Capture</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/media.php" class="sidebar-link<?php echo $current_page === 'media.php' ? ' active' : ''; ?>">Media Library</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/redirects.php" class="sidebar-link<?php echo $current_page === 'redirects.php' ? ' active' : ''; ?>">Redirect Map</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/settings.php" class="sidebar-link<?php echo $current_page === 'settings.php' ? ' active' : ''; ?>">Settings</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/users.php" class="sidebar-link<?php echo $current_page === 'users.php' ? ' active' : ''; ?>">Manage Users</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/logs.php" class="sidebar-link<?php echo $current_page === 'logs.php' ? ' active' : ''; ?>">Activity Log</a></li>
        <li style="margin-top: 40px;"><a href="<?php echo BASE_URL; ?>admin/logout.php" class="sidebar-link" style="color: #ef4444;">Logout</a></li>
    </ul>
</aside>