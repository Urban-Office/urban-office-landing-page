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

// Branches offering Virtual Office, for the nav dropdown's branch flyout
$nav_vo_branches = [];
foreach ($locations_db as $nav_loc_slug => $nav_loc) {
    foreach ($nav_loc['pricing'] as $nav_pkg) {
        if ($nav_pkg['category'] === 'virtual-office') {
            $nav_vo_branches[$nav_loc_slug] = $nav_loc['short_title'];
            break;
        }
    }
}

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

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- CSS Stylesheet -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo filemtime(dirname(__FILE__) . '/../assets/css/style.css'); ?>">
    
    <!-- Bootstrap Icons for premium vector support -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

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
                                        $nav_vo_url = ($nav_vo_slug === 'merr') ? 'virtual-office-surabaya' : 'virtual-office-' . $nav_vo_slug;
                                    ?>
                                        <a href="<?php echo BASE_URL . $nav_vo_url; ?>/" class="dropdown-item"><?php echo sanitize($nav_vo_label); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo BASE_URL; ?>sewa-kantor-surabaya/" class="dropdown-item">Private Office</a>
                        <a href="<?php echo BASE_URL; ?>meeting-room-surabaya/" class="dropdown-item">Ruang Meeting</a>
                        <a href="<?php echo BASE_URL; ?>coworking-space-urban-office/" class="dropdown-item">Coworking Space</a>
                        <a href="<?php echo BASE_URL; ?>event-space-55k-perjam-urbanoffice/" class="dropdown-item">Event Space</a>
                        <a href="<?php echo BASE_URL; ?>sharing-room-office/" class="dropdown-item">Sharing Room Office</a>
                    </div>
                </li>
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
