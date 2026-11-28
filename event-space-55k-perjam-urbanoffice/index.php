<?php
/**
 * Urban Office - Event Space Landing Page
 */

require_once dirname(dirname(__FILE__)) . '/inc/config.php';
require_once dirname(dirname(__FILE__)) . '/inc/functions.php';
require_once dirname(dirname(__FILE__)) . '/inc/locations_data.php';

$svc_category  = 'event-space';
$svc_city_slug = isset($_GET['lokasi']) ? strtolower(trim($_GET['lokasi'])) : '';

if (!empty($svc_city_slug) && $svc_city_slug !== '55k-perjam-urbanoffice') {
    $svc_branches = service_city_branches($svc_category, $svc_city_slug);
    if (empty($svc_branches)) {
        header('Location: ' . BASE_URL . 'event-space-55k-perjam-urbanoffice/', true, 301);
        exit;
    }
    $es_city_slug = $svc_city_slug;
    $page_slug    = 'event-space-' . $es_city_slug;
} else {
    $es_city_slug = 'surabaya';
    $svc_branches = service_city_branches($svc_category, $es_city_slug);
    $page_slug    = 'event-space-55k-perjam-urbanoffice';
}

$es_city_label = !empty($svc_branches[0]['city']) ? $svc_branches[0]['city'] : 'Surabaya';
$is_es_city_landing = ($page_slug !== 'event-space-55k-perjam-urbanoffice');

require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<!-- Expose current city so the "Cabang Kami" filter defaults to it -->
<script>window.currentBranchCity = <?php echo json_encode($es_city_slug); ?>;</script>

<!-- Hero Section -->
<?php
$hero_tag = 'Premium Event Space';
$hero_title = 'Sewa Event Space Mulai 45Rb/Pax';
$hero_desc = 'Miliki ruang seminar, workshop, launching produk, atau rapat pemegang saham berkapasitas 30-80 orang dengan fasilitas lengkap di Surabaya & Malang. Berada di lokasi strategis dan mudah diakses.';
if ($is_es_city_landing) {
    $hero_tag   .= ' ' . $es_city_label;
    $hero_title  = 'Sewa Event Space di ' . $es_city_label . ' Mulai 45Rb/Pax';
    $hero_desc   = 'Miliki ruang seminar, workshop, launching produk, atau rapat pemegang saham di ' . $es_city_label . ' berkapasitas 30-80 orang dengan fasilitas lengkap. Berada di lokasi strategis dan mudah diakses.';
    if (!empty($svc_branches[0]['image'])) {
        $hero_img = encode_asset_url($svc_branches[0]['image']);
    }
    $card_title = 'Event Space ' . $es_city_label;
    $card_desc  = !empty($svc_branches[0]['address']) ? $svc_branches[0]['address'] : '';
}
$hero_cta_text = 'Dapatkan Quote Harga';
$hero_cta_url = '#pricing';
include dirname(dirname(__FILE__)) . '/inc/components/hero.php';
?>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>


<!-- Pricing Configuration -->
<div id="pricing">
    <?php
    $es_active_branch = resolve_active_booking_branch($svc_branches) ?: $svc_branches[0];
    $es_active_slug   = (string) ($es_active_branch['slug'] ?? 'surabaya');
    $es_has_webapp    = has_webapp_booking($es_active_slug);
    $es_booking_url   = $es_has_webapp ? build_webapp_booking_url($es_active_slug, 'event-space') : '#contact';
    $es_cta_target    = $es_has_webapp ? '_blank' : '';
    $es_cta_label     = $es_has_webapp ? 'Booking Online' : 'Pesan Sekarang';

    $active_booking_branch_slug = $es_active_slug;
    $pricing_branch_options = [];
    if (count($svc_branches) > 1) {
        foreach ($svc_branches as $sb) {
            $sb_slug = (string) ($sb['slug'] ?? '');
            $pricing_branch_options[] = [
                'slug'        => $sb_slug,
                'label'       => ($sb['short_title'] ?? ucwords(str_replace('-', ' ', $sb_slug))) . ' (' . ($sb['location'] ?? $sb['city']) . ')',
                'address'     => (string) ($sb['address'] ?? ''),
                'booking_url' => has_webapp_booking($sb_slug) ? build_webapp_booking_url($sb_slug, 'event-space') : '#contact',
            ];
        }
    }

    $pricing_title = 'Pilihan Ruang Event Space';
    $packages = [
        [
            'name' => 'Event Space Half-Day',
            'price' => '45.000',
            'period' => 'pax/4 jam',
            'description' => 'Sangat cocok untuk seminar, workshop, atau presentasi bisnis dengan durasi setengah hari (kapasitas 30 - 80 orang).',
            'features' => [],
            'hide_minimum_info' => true,
            'cta_text' => $es_cta_label,
            'cta_link' => $es_booking_url,
            'cta_target' => $es_cta_target,
            'is_webapp_booking' => $es_has_webapp
        ],
        [
            'name' => 'Event Space Full-Day',
            'price' => '90.000',
            'period' => 'pax/8 jam',
            'popular' => true,
            'description' => 'Paket terbaik untuk pelatihan penuh seharian, press conference, atau gathering perusahaan (kapasitas 30 - 80 orang).',
            'features' => [],
            'hide_minimum_info' => true,
            'cta_text' => $es_cta_label,
            'cta_link' => $es_booking_url,
            'cta_target' => $es_cta_target,
            'is_webapp_booking' => $es_has_webapp
        ]
    ];
    include dirname(dirname(__FILE__)) . '/inc/components/pricing_cards.php';
    ?>
</div>

<!-- Per-city local branch address, map & advantages -->
<?php
$local_section_title    = 'Lokasi Event Space di ' . $es_city_label;
$local_section_subtitle = 'Aula dan ruang acara Urban Office di ' . $es_city_label . ' berada di lokasi strategis dan mudah dijangkau peserta acara.';
$local_city_slug        = $es_city_slug;
include DIR_ROOT . 'inc/components/local_branch_section.php';
?>

<!-- Features Component -->
<?php
$features_title = 'Fasilitas Pendukung Event Space';
$features_list = [
    [
        'title' => 'Projector & Sound System',
        'desc' => 'Didukung proyektor HD, layar lebar, microphone wireless, dan sound system premium untuk kelancaran presentasi Anda.',
        'icon' => '🔊'
    ],
    [
        'title' => 'Tata Letak Fleksibel',
        'desc' => 'Tata ruang yang dapat disesuaikan (Theater, Classroom, U-Shape, Boardroom) dengan kapasitas 30 - 80 orang.',
        'icon' => '📐'
    ],
    [
        'title' => 'WiFi Kapasitas Besar',
        'desc' => 'Koneksi internet serat optik berkecepatan tinggi yang stabil untuk seluruh peserta event.',
        'icon' => '📶'
    ],
    [
        'title' => 'Staf Support Siaga',
        'desc' => 'Pendampingan teknisi on-site untuk membantu pengaturan alat dan sistem audio visual selama acara berlangsung.',
        'icon' => '⚙️'
    ],
    [
        'title' => 'Pantry & Coffee Break',
        'desc' => 'Pilihan penyediaan air mineral, snack box, kopi/teh, hingga buffet makan siang/malam dari vendor terpercaya.',
        'icon' => '🍽️'
    ],
    [
        'title' => 'Lobby & Meja Registrasi',
        'desc' => 'Tersedia area khusus pendaftaran di depan pintu masuk event space untuk meregistrasi peserta Anda.',
        'icon' => '✍️'
    ],
    [
        'title' => 'Alat Tulis & Papan Tulis',
        'desc' => 'Fasilitas whiteboard, spidol warna-warni, serta flipchart gratis untuk keperluan brainstorming.',
        'icon' => '📝'
    ],
    [
        'title' => 'Layanan Kebersihan Ekstra',
        'desc' => 'Layanan pembersihan dan sterilisasi ruangan sebelum (pre) dan sesudah (post) pelaksanaan acara.',
        'icon' => '🧹'
    ]
];
include dirname(dirname(__FILE__)) . '/inc/components/features.php';
?>

<!-- Event Space Gallery Slider Section -->
<section class="section" id="gallery" style="background-color: hsl(var(--clr-bg-secondary)); border-top: 1px solid hsl(var(--clr-border)); overflow: hidden; padding: 60px 0; scroll-margin-top: 90px;">
    <div class="container">
        <?php
        $es_gallery_cities = get_event_space_cities();
        $es_branch_name    = !empty($svc_branches[0]) ? get_private_office_branch_label($es_city_slug, $svc_branches[0]) : 'Surabaya';
        $gallery_items     = !empty($svc_branches[0]) ? get_event_space_city_gallery($es_city_slug, $svc_branches[0]) : [];
        ?>
        <!-- Header row with title + per-city SEO navigation tabs -->
        <div class="gallery-section-header">
            <div>
                <h2 class="section-title" style="margin: 0;">Galeri Foto Event Space <?php echo sanitize($es_city_label); ?></h2>
                <p style="margin: 5px 0 0 0; max-width: 620px; color: hsl(var(--clr-text-muted)); font-size: 0.95rem;">Jelajahi berbagai pilihan tata letak dan dokumentasi area event space kami di Cabang <?php echo sanitize($es_branch_name); ?> (<?php echo sanitize($es_city_label); ?>).</p>
            </div>
            <?php if (count($es_gallery_cities) > 1): ?>
            <nav class="gallery-city-tabs" aria-label="Pilih galeri wilayah Event Space">
                <?php foreach ($es_gallery_cities as $es_c_slug => $es_c_name):
                    $es_c_url = ($es_c_slug === 'surabaya')
                        ? BASE_URL . 'event-space-55k-perjam-urbanoffice/#gallery'
                        : BASE_URL . 'event-space-' . $es_c_slug . '/#gallery';
                    $es_c_active = ($es_c_slug === $es_city_slug);
                ?>
                <a href="<?php echo sanitize($es_c_url); ?>" class="gallery-city-tab<?php echo $es_c_active ? ' active' : ''; ?>"<?php echo $es_c_active ? ' aria-current="page"' : ''; ?>>
                    <i class="bi bi-geo-alt-fill"></i> <?php echo sanitize($es_c_name); ?>
                </a>
                <?php endforeach; ?>
            </nav>
            <?php endif; ?>
        </div>
        
        <!-- Slider Window (draggable/swipeable) -->
        <div class="office-gallery-window" style="overflow: hidden; margin: 0 -10px; padding: 10px 0; cursor: grab; user-select: none;">
            <div class="office-gallery-wrapper" id="office-gallery-slider" data-gallery-slider data-slide-selector=".gallery-card-slide" data-visible-desktop="4" data-visible-laptop="4" data-visible-tablet="3" data-visible-mobile="1.3" style="display: flex; transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1); will-change: transform;">
                <?php foreach ($gallery_items as $item): ?>
                <div class="gallery-card-slide">
                    <div class="gallery-item-card">
                        <!-- Image Container with Pax Badge -->
                        <div class="gallery-img-container">
                            <img src="<?php echo sanitize(encode_asset_url(BASE_URL . $item['img'])); ?>" alt="<?php echo sanitize($item['alt']); ?>" width="400" height="260" loading="lazy" decoding="async" draggable="false">
                            <?php if (!empty($item['pax'])): ?>
                            <span class="gallery-pax-badge">
                                <?php echo sanitize($item['pax']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Body Content -->
                        <div class="gallery-body-content">
                            <div class="gallery-text-wrap">
                                <h4 class="gallery-card-title"><?php echo sanitize($item['title']); ?></h4>
                                <p class="gallery-card-desc"><?php echo sanitize($item['desc']); ?></p>
                            </div>
                            
                            <!-- Action button connecting to booking -->
                            <a href="<?php echo sanitize($es_booking_url); ?>"<?php echo $es_has_webapp ? ' target="_blank" rel="noopener"' : ''; ?> class="btn btn-primary gallery-cta-btn" draggable="false">
                                <?php echo $es_has_webapp ? 'Booking Online' : 'Pesan Sekarang'; ?> <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<style>
.gallery-section-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-end;
    gap: 18px;
    margin-bottom: 24px;
}

.gallery-city-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.gallery-city-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: var(--radius-full);
    background-color: #FFFFFF;
    border: 1px solid hsl(var(--clr-border));
    color: #333333;
    font-size: 0.85rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: var(--shadow-sm);
}

.gallery-city-tab:hover {
    border-color: hsl(var(--clr-primary));
    color: hsl(var(--clr-primary));
    transform: translateY(-1px);
}

.gallery-city-tab.active {
    background-color: hsl(var(--clr-primary));
    border-color: hsl(var(--clr-primary));
    color: #FFFFFF;
}

/* Base Styles for Slider Cards */
.gallery-card-slide {
    padding: 0 8px;
    box-sizing: border-box;
}

.gallery-item-card {
    background-color: #FFFFFF;
    border-radius: var(--radius-md);
    border: 1px solid hsl(var(--clr-border));
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.gallery-img-container {
    position: relative;
    overflow: hidden;
    height: 150px; /* Shrunk from 200px */
}

.gallery-img-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.gallery-pax-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: hsl(var(--clr-primary));
    color: #FFFFFF;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: var(--radius-full);
    text-transform: uppercase;
}

.gallery-body-content {
    padding: 14px; /* Shrunk from 20px */
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    flex-grow: 1;
    text-align: left;
}

.gallery-card-title {
    font-size: 0.95rem; /* Shrunk from 1.15rem */
    margin-bottom: 4px;
    font-weight: 700;
    color: #111111;
}

.gallery-card-desc {
    font-size: 0.78rem; /* Shrunk from 0.85rem */
    color: hsl(var(--clr-text-muted));
    margin-bottom: 12px; /* Shrunk from 20px */
    line-height: 1.4;
}

.gallery-cta-btn {
    padding: 8px 12px !important; /* Shrunk from 10px 16px */
    font-size: 0.72rem !important; /* Shrunk from 0.8rem */
    width: 100%;
    border-radius: var(--radius-sm);
    text-align: center;
    text-decoration: none;
}

.gallery-cta-btn i {
    margin-left: 4px;
}

/* Hover Effects */
.gallery-item-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md) !important;
    border-color: hsl(var(--clr-primary)) !important;
}

.gallery-item-card:hover img {
    transform: scale(1.08);
}

.office-gallery-window:active {
    cursor: grabbing;
}

/* Responsive Overrides via CSS */
@media (max-width: 991px) {
    .gallery-img-container {
        height: 130px;
    }
    .gallery-body-content {
        padding: 12px;
    }
    .gallery-card-title {
        font-size: 0.88rem;
    }
    .gallery-card-desc {
        font-size: 0.74rem;
        margin-bottom: 8px;
    }
}

@media (max-width: 768px) {
    .gallery-card-slide {
        padding: 0 6px;
    }
}

@media (max-width: 576px) {
    .gallery-img-container {
        height: 150px;
    }
    .gallery-body-content {
        padding: 16px 12px;
    }
    .gallery-card-title {
        font-size: 0.98rem;
    }
    .gallery-card-desc {
        font-size: 0.82rem;
        margin-bottom: 12px;
    }
    .gallery-cta-btn {
        padding: 8px 12px !important;
        font-size: 0.74rem !important;
    }
}
</style>

<!-- Gallery slider initialized via assets/js/main.js (initOfficeGallerySliders) -->
<!-- Advantages / Keuntungan Section -->
<section class="section" style="background-color: hsl(var(--clr-bg-surface)); border-bottom: 1px solid hsl(var(--clr-border)); padding: 60px 0;">
    <div class="container" style="position: relative;">
        <h3 class="text-center" style="font-size: 1.8rem; margin-bottom: 40px; font-weight: 800; color: #111111;">Keuntungan Sewa Event Space Disini</h3>
        
        <!-- Slider Window (draggable/swipeable) -->
        <div class="advantages-gallery-window" style="overflow: hidden; margin: 0 -12px; padding: 10px 0; cursor: grab; user-select: none; width: 100%;">
            <div id="advantages-gallery-slider" data-gallery-slider data-slide-selector=".advantages-card-slide" data-visible-desktop="3" data-visible-laptop="3" data-visible-tablet="2" data-visible-mobile="1.4" data-autoplay="false" style="display: flex; transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1); will-change: transform;">
                
                <!-- Advantage 1 -->
                <div class="advantages-card-slide" style="flex: 0 0 33.333%; width: 33.333%; padding: 0 12px; box-sizing: border-box;">
                    <div style="background-color: #FFFFFF; border: 1px solid hsl(var(--clr-border)); padding: 28px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); display: flex; gap: 20px; align-items: flex-start; transition: transform 0.3s ease, box-shadow: 0.3s ease; height: 100%;" class="event-advantage-card">
                        <div style="width: 48px; height: 48px; background-color: rgba(255, 107, 0, 0.1); color: hsl(var(--clr-primary)); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(255, 107, 0, 0.1);">
                            <i class="bi bi-check-lg" style="-webkit-text-stroke: 1px;"></i>
                        </div>
                        <div style="text-align: left;">
                            <h4 style="font-size: 1.15rem; margin-bottom: 10px; font-weight: 700; color: #111111; line-height: 1.3;">Lokasi Strategis</h4>
                            <p style="font-size: 0.9rem; color: hsl(var(--clr-text-muted)); margin: 0; line-height: 1.6;">Akses mudah bagi seluruh peserta event, dikelilingi fasilitas publik memadai, serta dilengkapi area parkir yang aman dan luas.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Advantage 2 -->
                <div class="advantages-card-slide" style="flex: 0 0 33.333%; width: 33.333%; padding: 0 12px; box-sizing: border-box;">
                    <div style="background-color: #FFFFFF; border: 1px solid hsl(var(--clr-border)); padding: 28px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); display: flex; gap: 20px; align-items: flex-start; transition: transform 0.3s ease, box-shadow: 0.3s ease; height: 100%;" class="event-advantage-card">
                        <div style="width: 48px; height: 48px; background-color: rgba(255, 107, 0, 0.1); color: hsl(var(--clr-primary)); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(255, 107, 0, 0.1);">
                            <i class="bi bi-check-lg" style="-webkit-text-stroke: 1px;"></i>
                        </div>
                        <div style="text-align: left;">
                            <h4 style="font-size: 1.15rem; margin-bottom: 10px; font-weight: 700; color: #111111; line-height: 1.3;">Operator Profesional</h4>
                            <p style="font-size: 0.9rem; color: hsl(var(--clr-text-muted)); margin: 0; line-height: 1.6;">Staf dan teknisi profesional yang siaga membantu persiapan logistik, sistem audio-visual, hingga jalannya acara dari awal hingga selesai.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Advantage 3 -->
                <div class="advantages-card-slide" style="flex: 0 0 33.333%; width: 33.333%; padding: 0 12px; box-sizing: border-box;">
                    <div style="background-color: #FFFFFF; border: 1px solid hsl(var(--clr-border)); padding: 28px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); display: flex; gap: 20px; align-items: flex-start; transition: transform 0.3s ease, box-shadow: 0.3s ease; height: 100%;" class="event-advantage-card">
                        <div style="width: 48px; height: 48px; background-color: rgba(255, 107, 0, 0.1); color: hsl(var(--clr-primary)); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(255, 107, 0, 0.1);">
                            <i class="bi bi-check-lg" style="-webkit-text-stroke: 1px;"></i>
                        </div>
                        <div style="text-align: left;">
                            <h4 style="font-size: 1.15rem; margin-bottom: 10px; font-weight: 700; color: #111111; line-height: 1.3;">Peralatan Lengkap</h4>
                            <p style="font-size: 0.9rem; color: hsl(var(--clr-text-muted)); margin: 0; line-height: 1.6;">Dilengkapi proyektor HD, screen lebar, sound system berkualitas tinggi, high-speed WiFi dedicated, whiteboard, hingga flipchart.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Advantage 4 -->
                <div class="advantages-card-slide" style="flex: 0 0 33.333%; width: 33.333%; padding: 0 12px; box-sizing: border-box;">
                    <div style="background-color: #FFFFFF; border: 1px solid hsl(var(--clr-border)); padding: 28px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); display: flex; gap: 20px; align-items: flex-start; transition: transform 0.3s ease, box-shadow: 0.3s ease; height: 100%;" class="event-advantage-card">
                        <div style="width: 48px; height: 48px; background-color: rgba(255, 107, 0, 0.1); color: hsl(var(--clr-primary)); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(255, 107, 0, 0.1);">
                            <i class="bi bi-check-lg" style="-webkit-text-stroke: 1px;"></i>
                        </div>
                        <div style="text-align: left;">
                            <h4 style="font-size: 1.15rem; margin-bottom: 10px; font-weight: 700; color: #111111; line-height: 1.3;">Bebas Set-up Layouts</h4>
                            <p style="font-size: 0.9rem; color: hsl(var(--clr-text-muted)); margin: 0; line-height: 1.6;">Sesuaikan susunan tata letak ruangan (Theater, Classroom, U-shape, atau Roundtable) secara bebas sesuai kebutuhan formal maupun kasual acara Anda.</p>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</section>

<style>
.event-advantage-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md) !important;
    border-color: hsl(var(--clr-primary)) !important;
}
.advantages-gallery-window:active {
    cursor: grabbing;
}

/* Mobile Responsive - Advantages Slider */
@media (max-width: 768px) {
    .event-advantage-card { padding: 20px !important; }
}
@media (max-width: 576px) {
    .event-advantage-card { padding: 16px !important; }
    .event-advantage-card h4 { font-size: 1rem !important; }
    .event-advantage-card p { font-size: 0.85rem !important; }
}
</style>

<!-- Advantages slider initialized via assets/js/main.js (initOfficeGallerySliders) -->

<!-- FAQs Section -->
<?php
$faqs = !empty($svc_branches[0]) ? get_event_space_city_faqs($es_city_slug, $svc_branches[0]) : [];
include dirname(dirname(__FILE__)) . '/inc/components/faq.php';
?>



<!-- Testimonials Section -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/branches.php'; ?>

<!-- Contact Form -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
