<?php
/**
 * Shared Admin Sidebar Component
 * Grouped navigation, SVG icons, live unread leads badge, scrollable menu, and responsive mobile drawer.
 */
$current_page = basename($_SERVER['PHP_SELF']);
$sidebar_username = $_SESSION['admin_username'] ?? 'Admin';
$sidebar_role = $_SESSION['admin_role'] ?? 'administrator';
$sidebar_initial = strtoupper(substr((string) $sidebar_username, 0, 1));

$unread_leads_badge = 0;
try {
    $unread_row = Database::fetch("SELECT COUNT(*) AS total FROM leads WHERE status = 'unread'");
    if ($unread_row) {
        $unread_leads_badge = (int) $unread_row['total'];
    }
} catch (Exception $e) {
    $unread_leads_badge = 0;
}
?>
<!-- Mobile Header Bar (< 960px) -->
<div class="admin-mobile-bar">
    <a href="<?php echo BASE_URL; ?>admin/index.php" class="sidebar-brand-link">
        <span class="sidebar-brand-mark" aria-hidden="true">UO</span>
        <span class="sidebar-brand-title">Urban Office CMS</span>
    </a>
    <button type="button" class="admin-menu-toggle" id="adminSidebarToggle" aria-controls="adminSidebar" aria-expanded="false">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        Menu
    </button>
</div>
<div class="admin-sidebar-backdrop" id="adminSidebarBackdrop" aria-hidden="true"></div>

<aside class="admin-sidebar" id="adminSidebar" aria-label="Navigasi CMS">
    <div class="sidebar-brand">
        <a href="<?php echo BASE_URL; ?>admin/index.php" class="sidebar-brand-link">
            <span class="sidebar-brand-mark" aria-hidden="true">UO</span>
            <span class="sidebar-brand-text">
                <span class="sidebar-brand-title">URBAN OFFICE</span>
                <span class="sidebar-brand-sub">Content &amp; Growth CMS</span>
            </span>
        </a>
        <button type="button" class="sidebar-close-btn" id="adminSidebarClose" aria-label="Tutup menu navigasi">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>

    <div class="sidebar-scroll">
        <!-- Utama -->
        <div class="sidebar-group">
            <div class="sidebar-group-label">Utama</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="<?php echo BASE_URL; ?>admin/index.php" class="sidebar-link<?php echo $current_page === 'index.php' ? ' active' : ''; ?>">
                        <span class="sidebar-link-main">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg>
                            <span>Overview</span>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>admin/analytics.php" class="sidebar-link<?php echo $current_page === 'analytics.php' ? ' active' : ''; ?>">
                        <span class="sidebar-link-main">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                            <span>Traffic &amp; Events</span>
                        </span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Konversi & Leads -->
        <div class="sidebar-group">
            <div class="sidebar-group-label">Konversi</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="<?php echo BASE_URL; ?>admin/leads.php" class="sidebar-link<?php echo $current_page === 'leads.php' ? ' active' : ''; ?>">
                        <span class="sidebar-link-main">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                            <span>Lead Capture</span>
                        </span>
                        <?php if ($unread_leads_badge > 0): ?>
                            <span class="sidebar-badge" title="<?php echo $unread_leads_badge; ?> leads belum dibaca"><?php echo $unread_leads_badge > 99 ? '99+' : $unread_leads_badge; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>admin/popups.php" class="sidebar-link<?php echo $current_page === 'popups.php' ? ' active' : ''; ?>">
                        <span class="sidebar-link-main">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><circle cx="7" cy="6.5" r="0.8" fill="currentColor"></circle><circle cx="10" cy="6.5" r="0.8" fill="currentColor"></circle></svg>
                            <span>Pop-up Banners</span>
                        </span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Konten & SEO -->
        <div class="sidebar-group">
            <div class="sidebar-group-label">Konten &amp; SEO</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="<?php echo BASE_URL; ?>admin/posts.php" class="sidebar-link<?php echo $current_page === 'posts.php' ? ' active' : ''; ?>">
                        <span class="sidebar-link-main">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                            <span>Manage Posts</span>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>admin/categories.php" class="sidebar-link<?php echo $current_page === 'categories.php' ? ' active' : ''; ?>">
                        <span class="sidebar-link-main">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                            <span>Categories</span>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>admin/tags.php" class="sidebar-link<?php echo $current_page === 'tags.php' ? ' active' : ''; ?>">
                        <span class="sidebar-link-main">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                            <span>Tags</span>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>admin/pages.php" class="sidebar-link<?php echo $current_page === 'pages.php' ? ' active' : ''; ?>">
                        <span class="sidebar-link-main">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                            <span>SEO Landing Pages</span>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>admin/media.php" class="sidebar-link<?php echo $current_page === 'media.php' ? ' active' : ''; ?>">
                        <span class="sidebar-link-main">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                            <span>Media Library</span>
                        </span>
                    </a>
                </li>
                <?php if ($sidebar_role === 'administrator'): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>admin/redirects.php" class="sidebar-link<?php echo $current_page === 'redirects.php' ? ' active' : ''; ?>">
                        <span class="sidebar-link-main">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 14 20 9 15 4"></polyline><path d="M4 20v-7a4 4 0 0 1 4-4h12"></path></svg>
                            <span>Redirect Map</span>
                        </span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <?php if ($sidebar_role === 'administrator'): ?>
        <!-- Sistem -->
        <div class="sidebar-group">
            <div class="sidebar-group-label">Sistem</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="<?php echo BASE_URL; ?>admin/settings.php" class="sidebar-link<?php echo $current_page === 'settings.php' ? ' active' : ''; ?>">
                        <span class="sidebar-link-main">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                            <span>Settings</span>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>admin/users.php" class="sidebar-link<?php echo $current_page === 'users.php' ? ' active' : ''; ?>">
                        <span class="sidebar-link-main">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            <span>Manage Users</span>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>admin/logs.php" class="sidebar-link<?php echo $current_page === 'logs.php' ? ' active' : ''; ?>">
                        <span class="sidebar-link-main">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            <span>Activity Log</span>
                        </span>
                    </a>
                </li>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <span class="sidebar-avatar" aria-hidden="true"><?php echo sanitize($sidebar_initial); ?></span>
            <div class="sidebar-user-meta">
                <div class="sidebar-user-name"><?php echo sanitize($sidebar_username); ?></div>
                <div class="sidebar-user-role"><?php echo sanitize($sidebar_role); ?></div>
            </div>
        </div>
        <form method="POST" action="<?php echo BASE_URL; ?>admin/logout.php" style="margin: 0; display: inline-flex;">
            <?php echo csrf_field(); ?>
            <button type="submit" class="sidebar-logout-btn" title="Keluar dari CMS" style="cursor: pointer; font-family: inherit;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>

<script>
(function () {
    var sidebar = document.getElementById('adminSidebar');
    var toggleBtn = document.getElementById('adminSidebarToggle');
    var closeBtn = document.getElementById('adminSidebarClose');
    var backdrop = document.getElementById('adminSidebarBackdrop');
    if (!sidebar || !toggleBtn) return;

    function setOpen(open) {
        sidebar.classList.toggle('open', open);
        if (backdrop) backdrop.classList.toggle('open', open);
        toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    toggleBtn.addEventListener('click', function () {
        setOpen(!sidebar.classList.contains('open'));
    });
    if (closeBtn) closeBtn.addEventListener('click', function () { setOpen(false); });
    if (backdrop) backdrop.addEventListener('click', function () { setOpen(false); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) {
            setOpen(false);
        }
    });
})();
</script>
