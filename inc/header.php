<?php
/**
 * Shared Header Template
 * Outputs head tags, SEO metadata, preloads, and main navigation
 */

require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/database.php';
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/seo.php';
require_once dirname(__FILE__) . '/locations_data.php';

// Set default page slug if not defined
if (!isset($page_slug)) {
    $page_slug = '';
}

// Build the "Ruang Kerja" (workspace) dropdown from locations_data.php so every option only
// lists the locations that actually offer it (single source of truth).
//   - Virtual Office has per-branch landing pages -> branch-level flyout (city-area names).
//   - Other services have one page + a city filter (?lokasi=) -> city-level flyout.
$nav_vo_branches = [];     // slug => city-area label, e.g. 'surabaya-timur' => 'Surabaya Timur'
$nav_service_cities = [];  // category => [cityLower => 'City Name']
foreach ($locations_db as $nav_loc_slug => $nav_loc) {
    if (empty($nav_loc['pricing'])) {
        continue;
    }
    $nav_loc_cats = [];
    foreach ($nav_loc['pricing'] as $nav_pkg) {
        if (isset($nav_pkg['category'])) {
            $nav_loc_cats[$nav_pkg['category']] = true;
        }
    }
    if (isset($nav_loc_cats['virtual-office'])) {
        // Label follows the URL slug (city name) for keyword-rich internal links.
        $nav_vo_branches[$nav_loc_slug] = ucwords(str_replace('-', ' ', $nav_loc_slug));
    }
    foreach ($nav_loc_cats as $nav_cat => $_present) {
        if ($nav_cat === 'virtual-office') {
            continue;
        }
        $nav_service_cities[$nav_cat][strtolower($nav_loc['city'])] = $nav_loc['city'];
    }
}

// Non-VO workspace options in menu order: [category, landing page slug, label].
$nav_workspace_services = [
    ['cat' => 'private-office',      'url' => 'sewa-kantor-surabaya',               'label' => 'Private Office', 'clean_base' => 'sewa-kantor'],
    ['cat' => 'meeting-room',        'url' => 'meeting-room-surabaya',              'label' => 'Ruang Meeting', 'clean_base' => 'meeting-room'],
    ['cat' => 'coworking',           'url' => 'coworking-space-urban-office',       'label' => 'Coworking Space', 'clean_base' => 'coworking-space'],
    ['cat' => 'event-space',         'url' => 'event-space-55k-perjam-urbanoffice', 'label' => 'Event Space', 'clean_base' => 'event-space'],
    ['cat' => 'sharing-room-office', 'url' => 'sharing-room-office',                'label' => 'Sharing Room Office'],
];

// Detect if accessing Virtual Office landing page or any of its location branches
$is_vo_page = (
    (isset($page_slug) && strpos($page_slug, 'virtual-office') === 0) ||
    (isset($_GET['branch']) && !empty($_GET['branch'])) ||
    (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/virtual-office') !== false)
);

// Auto-publish scheduled posts whose published_at has arrived
auto_publish_scheduled_posts();

// Start capturing page content for minification & caching
start_page_cache($page_slug);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#FF6B00">
    
    <!-- Favicon / Brand Icon (Browser tab & Google Search Result icon) -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL; ?>assets/images/imgcomponent/favicon-32x32.png">
    <link rel="shortcut icon" type="image/png" href="<?php echo BASE_URL; ?>assets/images/imgcomponent/favicon-32x32.png">
    
    <?php 
    // Render SEO Meta tags dynamically
    render_seo_tags($page_slug);
    // Render JSON-LD Schema markup dynamically
    render_schema_markup($page_slug);
    ?>

    <!-- Google Fonts: Inter (loaded via <link> in <head> instead of CSS @import so the
         request starts during initial HTML parse, in parallel with style.css, cutting LCP) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">

    <!-- CSS Stylesheet -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo filemtime(dirname(__FILE__) . '/../assets/css/style.css'); ?>">
    
    <!-- Bootstrap Icons for premium vector support -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-WWSSXT7P');</script>
    <!-- End Google Tag Manager -->


</head>
<body>

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe title="Google Tag Manager" src="https://www.googletagmanager.com/ns.html?id=GTM-WWSSXT7P"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <!-- NAVBAR -->
    <nav class="navbar" id="navbar">
        <div class="container nav-container">
            <a href="<?php echo BASE_URL; ?>" class="logo" aria-label="Urban Office Homepage">
                <img src="<?php echo BASE_URL; ?>assets/images/imgcomponent/urban%20office%20new%20logo.png" alt="Urban Office Logo">
            </a>
            
            <button class="mobile-hamburger" id="hamburger-toggle" aria-label="Buka Menu">
                &#9776;
            </button>
            
            <ul class="nav-menu" id="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        Ruang Kerja <span>▼</span>
                    </a>
                    <div class="dropdown">
                        <div class="dropdown-item-wrap">
                            <a href="<?php echo BASE_URL; ?>virtual-office-surabaya/" class="dropdown-item dropdown-item-has-sub">
                                Virtual Office <span class="dropdown-sub-arrow">▸</span>
                            </a>
                            <div class="dropdown-submenu">
                                <div class="dropdown-submenu-panel">
                                    <?php foreach ($nav_vo_branches as $nav_vo_slug => $nav_vo_label):
                                        $nav_vo_url = 'virtual-office-' . $nav_vo_slug;
                                    ?>
                                        <a href="<?php echo BASE_URL . $nav_vo_url; ?>/" class="dropdown-item"><?php echo sanitize($nav_vo_label); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php foreach ($nav_workspace_services as $nav_svc):
                            $svc_cities = isset($nav_service_cities[$nav_svc['cat']]) ? $nav_service_cities[$nav_svc['cat']] : [];
                            $svc_base = BASE_URL . $nav_svc['url'] . '/';
                        ?>
                            <?php if (!empty($svc_cities)): ?>
                            <div class="dropdown-item-wrap">
                                <a href="<?php echo $svc_base; ?>" class="dropdown-item dropdown-item-has-sub">
                                    <?php echo $nav_svc['label']; ?> <span class="dropdown-sub-arrow">▸</span>
                                </a>
                                <div class="dropdown-submenu">
                                    <div class="dropdown-submenu-panel">
                                        <?php foreach ($svc_cities as $svc_city_lower => $svc_city_label):
                                            // Services with a clean_base use real per-city URLs (/sewa-kantor-jakarta/);
                                            // the rest still use the ?lokasi filter until they are converted too.
                                            $svc_city_href = isset($nav_svc['clean_base'])
                                                ? BASE_URL . $nav_svc['clean_base'] . '-' . $svc_city_lower . '/'
                                                : $svc_base . '?lokasi=' . urlencode($svc_city_lower);
                                        ?>
                                            <a href="<?php echo $svc_city_href; ?>" class="dropdown-item"><?php echo sanitize($svc_city_label); ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <a href="<?php echo $svc_base; ?>" class="dropdown-item"><?php echo $nav_svc['label']; ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </li>
                <?php if (!$is_vo_page): ?>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        Layanan Bisnis <span>▼</span>
                    </a>
                    <div class="dropdown">
                        <a href="<?php echo BASE_URL; ?>pendirian-perorangan-plus-virtual-office/" class="dropdown-item">Perusahaan Perorangan + VO</a>
                        <a href="<?php echo BASE_URL; ?>pendirian-pt-include-virtual-office/" class="dropdown-item">Pendirian PT + VO</a>
                        <a href="<?php echo BASE_URL; ?>pendirian-cv-virtual-office/" class="dropdown-item">Pendirian CV + VO</a>
                        <a href="<?php echo BASE_URL; ?>pendirian-pma-plus-virtual-office/" class="dropdown-item">Pendirian PMA + VO</a>
                        <a href="<?php echo BASE_URL; ?>perizinan-dan-perubahan-perusahaan/" class="dropdown-item">Perizinan & Perubahan Perusahaan</a>
                        <a href="<?php echo BASE_URL; ?>pajak-dan-akunting/" class="dropdown-item">Pajak & Akunting</a>
                    </div>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>lokasi-urban-office/" class="nav-link">Lokasi</a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>blog/" class="nav-link">Artikel</a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>urban-office-karir/" class="nav-link">Karir</a>
                </li>
                <li class="nav-cta-btn">
                    <a href="https://my.urbanoffice.id" class="btn btn-primary" style="padding: 10px 20px;">Login App</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main content landmark (closed in inc/footer.php). Improves accessibility
         (screen-reader "skip to main" + Lighthouse main-landmark audit). -->
    <main id="main-content">
