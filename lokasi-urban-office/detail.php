<?php
/**
 * Urban Office - Dynamic Location Detail Template
 * Displays detailed specifications, photo galleries, pricing lists, FAQs, and testimonials of a specific branch.
 */

require_once dirname(dirname(__FILE__)) . '/inc/config.php';
require_once dirname(dirname(__FILE__)) . '/inc/functions.php';
require_once dirname(dirname(__FILE__)) . '/inc/locations_data.php';

// Fetch the slug from request
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

// 404 check: verify if slug is defined and exists in data store
if (empty($slug) || !isset($locations_db[$slug])) {
    header("HTTP/1.1 404 Not Found");
    $page_slug = '404';
    require_once dirname(dirname(__FILE__)) . '/inc/header.php';
    ?>
    <section class="section container text-center" style="padding: 120px 24px 80px; margin-top: 80px;">
        <div style="font-size: 4rem; color: #FF6B00; margin-bottom: 20px;"><i class="bi bi-exclamation-triangle"></i></div>
        <h2 style="font-weight: 800; font-size: 2rem; margin-bottom: 12px;">Cabang Tidak Ditemukan</h2>
        <p style="color: #666666; max-width: 500px; margin: 0 auto 30px;">Maaf, lokasi cabang yang Anda cari tidak tersedia, dinonaktifkan, atau telah dipindahkan.</p>
        <a href="<?php echo BASE_URL; ?>lokasi-urban-office/" class="btn btn-primary" style="text-transform: none; font-weight: 700;">
            <i class="bi bi-arrow-left"></i> Kembali ke Jaringan Lokasi
        </a>
    </section>
    <?php
    require_once dirname(dirname(__FILE__)) . '/inc/footer.php';
    exit;
}

// Retrieve selected branch details
$branch = $locations_db[$slug];

// Define SEO dynamic parameters for header
$page_slug = $slug;

// Filter packages based on branch slug rules
$allowed_pricing_categories = [
    'surabaya' => ['virtual-office', 'coworking', 'private-office', 'meeting-room', 'event-space', 'sharing-room-office'],
    'surabaya-timur' => ['virtual-office', 'meeting-room', 'coworking'],
    'surabaya-barat' => ['virtual-office', 'meeting-room'],
    'gresik' => ['virtual-office', 'meeting-room', 'private-office'],
    'jakarta' => ['virtual-office', 'coworking', 'private-office', 'meeting-room', 'event-space', 'sharing-room-office'],
    'jakarta-timur' => ['virtual-office', 'meeting-room', 'private-office'],
    'medan' => ['virtual-office']
];

$branch_slug = $branch['slug'];
$allowed_cats = isset($allowed_pricing_categories[$branch_slug]) ? $allowed_pricing_categories[$branch_slug] : [];

// Filter package list and track active categories
$filtered_pricing = [];
$active_categories = [];
foreach ($branch['pricing'] as $pkg) {
    if (empty($allowed_cats) || in_array($pkg['category'], $allowed_cats)) {
        $filtered_pricing[] = $pkg;
        $active_categories[$pkg['category']] = true;
    }
}

// Build a natural-language product list for the hero subtitle, based only on categories actually offered at this branch
$hero_category_labels = [
    'virtual-office' => 'Virtual Office resmi',
    'private-office' => 'Private Office modern',
    'coworking' => 'area Coworking Space kolaboratif',
    'meeting-room' => 'Ruang Meeting eksklusif',
    'event-space' => 'Event Space representatif',
    'sharing-room-office' => 'Sharing Room Office nyaman'
];
$hero_category_items = [];
foreach ($hero_category_labels as $cat_key => $cat_label) {
    if (isset($active_categories[$cat_key])) {
        $hero_category_items[] = $cat_label;
    }
}
$hero_items_count = count($hero_category_items);
if ($hero_items_count === 0) {
    $hero_products_text = 'ruang kerja profesional';
} elseif ($hero_items_count === 1) {
    $hero_products_text = 'sewa ' . $hero_category_items[0];
} else {
    $hero_last_item = array_pop($hero_category_items);
    $hero_products_text = 'sewa ' . implode(', ', $hero_category_items) . ', serta ' . $hero_last_item;
}

require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<!-- Load Custom Detail Page Styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/location-detail.css?v=<?php echo filemtime(dirname(dirname(__FILE__)) . '/assets/css/location-detail.css'); ?>">

<!-- HERO SECTION -->
<section class="loc-hero">
    <div class="container">
        <div class="loc-hero-grid">
            <div class="loc-rating-badge">
                <i class="bi bi-star-fill" style="color: #FFD700;"></i>
                <span><strong><?php echo sanitize($branch['rating']); ?></strong> / 5.0 (<?php echo (int) $branch['reviews_count']; ?> Google Reviews)</span>
            </div>
            
            <h1>Workspace Premium di <br><span><?php echo sanitize($branch['short_title']); ?></span></h1>
            
            <p class="loc-hero-subtitle">
                Nikmati <?php echo sanitize($hero_products_text); ?> dengan fasilitas super lengkap di <?php echo sanitize($branch['city']); ?>.
            </p>
            
            <!-- Key Highlights Checklist -->
            <ul class="loc-highlights-list">
                <?php foreach ($branch['advantages'] as $adv): ?>
                    <li>
                        <i class="bi bi-check-circle-fill"></i>
                        <span><?php echo sanitize($adv); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <!-- Action Buttons -->
            <?php 
                $general_wa_msg = "Halo Urban Office, saya tertarik untuk bertanya seputar layanan dan fasilitas di cabang " . $branch['short_title'];
                $wa_hero_url = "https://api.whatsapp.com/send?phone=" . WHATSAPP_NUMBER . "&text=" . urlencode($general_wa_msg);
            ?>
            <div class="loc-hero-btns">
                <a href="<?php echo $wa_hero_url; ?>" target="_blank" rel="noopener" class="btn btn-primary">
                    <i class="bi bi-whatsapp"></i> Hubungi Cabang Ini
                </a>
                <a href="#pricing-packages" class="btn btn-outline">
                    Lihat Paket & Harga
                </a>
            </div>
            
            <!-- Share Widget -->
            <div class="loc-share-block">
                <span>Bagikan Halaman:</span>
                <div class="loc-share-links">
                    <button type="button" class="loc-share-btn" onclick="sharePage('facebook')" aria-label="Share on Facebook">
                        <i class="bi bi-facebook"></i>
                    </button>
                    <button type="button" class="loc-share-btn" onclick="sharePage('whatsapp')" aria-label="Share on WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </button>
                    <button type="button" class="loc-share-btn" onclick="sharePage('twitter')" aria-label="Share on Twitter">
                        <i class="bi bi-twitter-x"></i>
                    </button>
                    <button type="button" class="loc-share-btn copied-target" onclick="sharePage('copy')" aria-label="Salin Link">
                        <i class="bi bi-link-45deg"></i>
                    </button>
                </div>
            </div>
            
            <!-- Right Panel: Image Display -->
            <div class="loc-hero-img-wrapper">
                <img src="<?php echo $branch['image']; ?>" alt="<?php echo sanitize($branch['title']); ?>">
            </div>
        </div>
    </div>
</section>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>


<!-- MINI INFO CARDS -->
<section class="loc-mini-info-section">
    <div class="container">
        <div class="loc-mini-grid">
            <!-- Card 1: Address Details -->
            <div class="loc-mini-card">
                <div class="loc-mini-icon">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div class="loc-mini-details">
                    <h4>Alamat Lengkap</h4>
                    <p><?php echo sanitize($branch['address']); ?></p>
                    <a href="<?php echo $branch['map_link']; ?>" target="_blank" rel="noopener" class="btn btn-outline" style="padding: 8px 18px; font-size: 0.8rem; text-transform: none; gap: 6px;">
                        <i class="bi bi-map"></i> Buka Google Maps
                    </a>
                </div>
            </div>
            
            <!-- Card 2: Rating Info -->
            <div class="loc-mini-card">
                <div class="loc-mini-icon">
                    <i class="bi bi-hand-thumbs-up-fill"></i>
                </div>
                <div class="loc-mini-details">
                    <h4>Kepuasan Pelanggan</h4>
                    <p style="font-size: 1.15rem; color: #FF6B00; font-weight: 800; margin-bottom: 4px;">99.8% PUAS</p>
                    <p style="font-size: 0.85rem; color: #555555; font-weight: 500;">Berdasarkan review penyewa kantor aktif dan kunjungan harian.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- GALLERY SLIDER -->
<section class="section gallery-section">
    <div class="container">
        <div class="section-header-wrap">
            <span class="badge" style="background-color: #FF6B00; color: #FFFFFF;">Galeri Area</span>
            <h2 class="section-title">Foto Ruangan & Area Kerja</h2>
            <p class="section-subtitle">Intip keindahan desain interior yang modern dan lingkungan kerja yang kondusif di <?php echo sanitize($branch['short_title']); ?>.</p>
        </div>
        
        <div class="gallery-slider-container">
            <div class="gallery-slider-wrapper">
                <div class="gallery-track">
                    <?php foreach ($branch['gallery'] as $index => $img_url): 
                        // Map categories for names
                        $cap_title = "Workspace Area";
                        if ($index === 0) $cap_title = "Lobby & Reception Area";
                        elseif ($index === 1) $cap_title = "Coworking Space / Collaboration Area";
                        elseif ($index === 2) $cap_title = "Exclusive Private Office";
                        elseif ($index === 3) $cap_title = "Modern Meeting Room Area";
                    ?>
                        <div class="gallery-item">
                            <img src="<?php echo $img_url; ?>" alt="Galeri <?php echo $index + 1; ?>" loading="lazy" draggable="false">
                            <div class="gallery-caption">
                                <h4 style="color: #FFFFFF !important; font-size: 1.05rem; font-weight: 700; margin-bottom: 4px;"><?php echo $cap_title; ?></h4>
                                <p style="color: rgba(255,255,255,0.8); font-size: 0.8rem; margin: 0;">Fasilitas berkelas dengan standar kenyamanan tinggi.</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Arrow Navigation -->
            <button type="button" class="gallery-arrow prev" onclick="prevGallSlide()" aria-label="Previous Slide">
                <i class="bi bi-arrow-left"></i>
            </button>
            <button type="button" class="gallery-arrow next" onclick="nextGallSlide()" aria-label="Next Slide">
                <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>
</section>

<!-- MOBILE APP PROMO BANNER -->
<section class="app-promo-section">
    <div class="container">
        <div class="app-promo-banner">
            <h3>Nikmati Kemudahan Booking Mudah via Aplikasi</h3>
            <p class="app-promo-desc">Unduh aplikasi mobile **My Urban Office** di handphone Anda untuk melakukan pemesanan ruang meeting harian, pembayaran tagihan virtual office, hingga berinteraksi dengan komunitas member kami secara langsung.</p>
            <div class="app-badge-row">
                <a href="#" class="app-badge-link" onclick="showDevelopmentToast(); return false;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg" alt="App Store Button">
                </a>
                <a href="#" class="app-badge-link" onclick="showDevelopmentToast(); return false;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Play Store Button">
                </a>
            </div>
            
            <div class="app-promo-mockup">
                <img src="<?php echo BASE_URL; ?>assets/images/imgcomponent/app-component.webp" alt="My Urban Office App Mockup" style="display: block; filter: drop-shadow(0px 15px 30px rgba(0,0,0,0.3));">
            </div>
        </div>
    </div>
</section>

<!-- BOOKING STEPS -->
<section class="section steps-section">
    <div class="container">
        <div class="section-header-wrap">
            <span class="badge" style="background-color: #FFD700; color: #111111;">Cara Memesan</span>
            <h2 class="section-title">Alur Mudah Pendaftaran Workspace</h2>
            <p class="section-subtitle">Mulailah bekerja di area representatif kami hanya dengan 3 tahap yang sangat simpel.</p>
        </div>
        
        <div class="steps-grid">
            <!-- Step 1 -->
            <div class="step-card">
                <span class="step-number">01</span>
                <div class="step-icon-wrap">
                    <i class="bi bi-grid-1x2-fill"></i>
                </div>
                <h3>1. Pilih Layanan & Paket</h3>
                <p>Pilih kategori sewa yang Anda inginkan (Virtual Office, Private Office harian/bulanan, Ruang Meeting, atau Coworking Desk).</p>
            </div>
            
            <!-- Step 2 -->
            <div class="step-card">
                <span class="step-number">02</span>
                <div class="step-icon-wrap">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
                <h3>2. Jadwalkan Kunjungan</h3>
                <p>Konsultasikan kebutuhan Anda dengan customer support kami dan jadwalkan kunjungan (survei) fisik ke cabang secara langsung.</p>
            </div>
            
            <!-- Step 3 -->
            <div class="step-card">
                <span class="step-number">03</span>
                <div class="step-icon-wrap">
                    <i class="bi bi-file-earmark-check-fill"></i>
                </div>
                <h3>3. Aktivasi & Bekerja</h3>
                <p>Lakukan konfirmasi pemesanan, bayar invoice secara praktis, dan Anda siap bekerja di hari yang sama dengan tenang.</p>
            </div>
        </div>
    </div>
</section>

<!-- PRICING PACKAGES & FILTER TABS -->
<section class="section pricing-section" id="pricing-packages">
    <div class="container">
        <div class="section-header-wrap">
            <span class="badge" style="background-color: #FF6B00; color: #FFFFFF;">Paket Workspace</span>
            <h2 class="section-title">Pilihan Paket & Harga Sewa</h2>
            <p class="section-subtitle">Pilih paket terbaik di cabang <?php echo sanitize($branch['short_title']); ?> yang dirancang untuk mendukung operasional bisnis Anda.</p>
        </div>
        
        <!-- Tab Filter Buttons -->
        <?php if (count($active_categories) > 1): ?>
        <div class="pricing-tabs">
            <button type="button" class="pricing-tab-btn active" onclick="filterPackages('all', this)">Semua Layanan</button>
            <?php if (isset($active_categories['virtual-office'])): ?>
                <button type="button" class="pricing-tab-btn" onclick="filterPackages('virtual-office', this)">Virtual Office</button>
            <?php endif; ?>
            <?php if (isset($active_categories['coworking'])): ?>
                <button type="button" class="pricing-tab-btn" onclick="filterPackages('coworking', this)">Coworking Space</button>
            <?php endif; ?>
            <?php if (isset($active_categories['private-office'])): ?>
                <button type="button" class="pricing-tab-btn" onclick="filterPackages('private-office', this)">Private Office</button>
            <?php endif; ?>
            <?php if (isset($active_categories['meeting-room'])): ?>
                <button type="button" class="pricing-tab-btn" onclick="filterPackages('meeting-room', this)">Ruang Meeting</button>
            <?php endif; ?>
            <?php if (isset($active_categories['event-space'])): ?>
                <button type="button" class="pricing-tab-btn" onclick="filterPackages('event-space', this)">Event Space</button>
            <?php endif; ?>
            <?php if (isset($active_categories['sharing-room-office'])): ?>
                <button type="button" class="pricing-tab-btn" onclick="filterPackages('sharing-room-office', this)">Sharing Room</button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Pricing Card Grid -->
        <div class="pricing-grid">
            <?php 
            $product_features_map = [
                'virtual-office' => [
                    'Domisili Gedung Resmi',
                    'Professional Mail Logging',
                    'Shared Telephone Line',
                    'Akses Meeting Room',
                    'Signage Perusahaan',
                    'Resepsionis Profesional'
                ],
                'coworking' => [
                    'Meja & Kursi Ergonomis',
                    'WiFi berkecepatan Tinggi',
                    'Free Flow Kopi & Teh',
                    'Power Socket Berlimpah'
                ],
                'private-office' => [
                    'Ruangan Siap Pakai',
                    'Bebas Biaya Listrik & AC',
                    'Resepsionis & Lobby',
                    'Internet Serat Optik',
                    'Trial Harian & Mingguan',
                    'Keamanan & Cleaning',
                    'Free Flow Beverage'
                ],
                'meeting-room' => [
                    'Layar LED & HDMI',
                    'WiFi Kecepatan Tinggi',
                    'Alat Tulis Rapat',
                    'Penyambutan Tamu',
                    'Free Flow Minuman',
                    'Conference Speakerphone',
                    'Sound System & Mic'
                ],
                'event-space' => [
                    'Projector & Sound System',
                    'Tata Letak Fleksibel',
                    'WiFi Kapasitas Besar',
                    'Staf Support Siaga',
                    'Pantry & Coffee Break',
                    'Lobby & Meja Registrasi',
                    'Alat Tulis & Papan Tulis',
                    'Layanan Kebersihan Ekstra'
                ],
                'sharing-room-office' => [
                    'Dedicated Workstation',
                    'Kursi Kerja Ergonomis',
                    'Internet Tanpa Batas',
                    'Free Flow Coffee & Tea',
                    'Domisili & Surat Bisnis',
                    'Kuota Meeting Room',
                    'Loker Penyimpanan Aman',
                    'Resepsionis Profesional'
                ]
            ];

            $card_count = 0;
            foreach ($filtered_pricing as $pkg):
                $card_count++;
                $is_popular = ($pkg['category'] === 'virtual-office' && strpos(strtolower($pkg['name']), 'pro') !== false) || ($pkg['category'] === 'private-office');
                $is_claim_only = isset($pkg['booking_mode']) && $pkg['booking_mode'] === 'claim-only';
                $pkg_wa_url = "https://api.whatsapp.com/send?phone=" . WHATSAPP_NUMBER . "&text=" . urlencode($pkg['cta_wa']);

                // Override features list based on package category to align with landing pages
                if (isset($product_features_map[$pkg['category']])) {
                    $pkg['features'] = $product_features_map[$pkg['category']];
                }
            ?>
                <div class="pkg-card<?php echo $is_popular ? ' popular' : ''; ?>" data-category="<?php echo sanitize($pkg['category']); ?>">
                    <?php if ($is_popular): ?>
                        <span class="pkg-badge">Best Seller</span>
                    <?php endif; ?>

                    <div class="pkg-card-top">
                        <h3><?php echo sanitize($pkg['name']); ?></h3>
                        <p class="pkg-desc"><?php echo sanitize($pkg['desc']); ?></p>

                        <?php if ($is_claim_only): ?>
                            <div class="pkg-price-wrap">
                                <span class="pkg-price" style="font-size: 1.1rem;">Eksklusif Member VO</span>
                            </div>
                            <p class="pkg-minimum-info">*Hanya tersedia sebagai klaim jatah bulanan Virtual Office, tidak dapat disewa lepas per jam.</p>
                        <?php else: ?>
                            <div class="pkg-price-wrap">
                                <span class="pkg-currency">Rp</span>
                                <span class="pkg-price"><?php echo sanitize($pkg['price']); ?></span>
                                <span class="pkg-period">/ <?php echo sanitize($pkg['period']); ?></span>
                            </div>
                            <?php if (in_array($pkg['category'], ['virtual-office', 'private-office'])): ?>
                                <p class="pkg-minimum-info">*Minimal sewa 1 tahun</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="pkg-features-wrap">
                        <p class="pkg-features-title">Fasilitas Termasuk:</p>
                        <ul class="pkg-features-list">
                            <?php foreach ($pkg['features'] as $feat): ?>
                                <li>
                                    <i class="bi bi-check-lg"></i>
                                    <span><?php echo sanitize($feat); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="pkg-actions">
                        <?php if ($is_claim_only): ?>
                            <a href="<?php echo $pkg_wa_url; ?>" target="_blank" rel="noopener" class="btn btn-booking" style="width: 100%; justify-content: center;">
                                <i class="bi bi-whatsapp"></i> Tanya Cara Klaim Jatah
                            </a>
                        <?php else: ?>
                            <a href="https://my.urbanoffice.co.id/beforelogin" target="_blank" rel="noopener" class="btn btn-booking">
                                Booking Now
                            </a>
                            <a href="<?php echo $pkg_wa_url; ?>" target="_blank" rel="noopener" class="btn btn-whatsapp-icon" aria-label="Hubungi via WhatsApp">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="pricing-empty" id="pricing-empty-msg" style="display: none;">
                <p>Maaf, kategori paket belum tersedia di cabang ini untuk saat ini.</p>
            </div>
        </div>
    </div>
</section>

<!-- OTHER BRANCHES -->
<section class="section other-branches-section">
    <div class="container">
        <div class="section-header-wrap">
            <span class="badge" style="background-color: #FFD700; color: #111111;">Jaringan Cabang</span>
            <h2 class="section-title">Cabang Urban Office Lainnya</h2>
            <p class="section-subtitle">Selain di cabang <?php echo sanitize($branch['short_title']); ?>, Anda juga dapat menyewa ruang kerja di lokasi strategis berikut.</p>
        </div>
        
        <div class="branches-slider-container">
            <div class="branches-slider-wrapper">
                <div class="branches-track">
                    <?php 
                    foreach ($locations_db as $key => $loc):
                        if ($key === $slug) continue;
                        
                        $wa_message = "Halo Urban Office, saya tertarik dengan layanan di Cabang *" . $loc['title'] . "*";
                        $wa_url = "https://api.whatsapp.com/send?phone=" . WHATSAPP_NUMBER . "&text=" . urlencode($wa_message);
                    ?>
                        <div class="branch-card">
                            <div>
                                <div class="branch-img-wrap">
                                    <img src="<?php echo $loc['image']; ?>" alt="<?php echo sanitize($loc['title']); ?>" loading="lazy">
                                </div>
                                <div class="branch-body-content">
                                    <h3 style="font-size: 16px; margin-bottom: 8px; font-weight: 700;"><?php echo sanitize($loc['title']); ?></h3>
                                    <div class="address-container" style="margin-bottom: 8px;">
                                        <p class="address-text" style="font-size: 0.82rem; line-height: 1.4; color: #666666; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; height: 1.4em;"><?php echo sanitize($loc['address']); ?></p>
                                    </div>
                                    <p style="font-size: 0.8rem; font-weight: 600; color: #FF6B00; margin: 0;">
                                        Layanan: <?php echo sanitize($loc['services']); ?>
                                    </p>
                                </div>
                            </div>
                            <div style="padding: 0 24px 24px; display: flex; flex-direction: column; gap: 8px;">
                                <a href="<?php echo BASE_URL; ?>lokasi-urban-office/<?php echo $loc['slug']; ?>/" class="btn btn-primary" style="width: 100%; justify-content: center; font-size: 12px; padding: 10px 16px;">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ SECTION ACCORDION -->
<section class="section faq-section">
    <div class="container">
        <div class="section-header-wrap center-align">
            <span class="badge" style="background-color: #FFD700; color: #111111;">FAQ</span>
            <h2 class="section-title">Pertanyaan Sering Diajukan</h2>
            <p class="section-subtitle">Beberapa informasi penting seputar fasilitas dan penyewaan ruang kerja di cabang ini.</p>
        </div>
        
        <div class="faq-list">
            <?php foreach ($branch['faq'] as $index => $faq_item): ?>
                <div class="faq-card-item">
                    <button type="button" class="faq-card-header" onclick="toggleFaqCard(this)">
                        <h3><?php echo sanitize($faq_item['question']); ?></h3>
                        <span class="faq-card-icon"><i class="bi bi-chevron-down"></i></span>
                    </button>
                    <div class="faq-card-content">
                        <div class="faq-card-body">
                            <?php echo sanitize($faq_item['answer']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>



<!-- CONTACT SECTION -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<!-- CLIENT BRANDS SLIDER SECTION -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/brands_slider.php'; ?>

<!-- Custom Script for Page Animations & Interaction -->
<script>
    // --- 1. Gallery Slider Logic (Multi-Item Carousel with Grab Drag & Touch Swipe) ---
    let currentGallSlide = 0;
    const track = document.querySelector('.gallery-track');
    const items = document.querySelectorAll('.gallery-item');
    const sliderContainer = document.querySelector('.gallery-slider-container');
    
    let gallInterval = null;
    let isDraggingGall = false;
    let startXGall = 0;
    let currentXGall = 0;
    const dragThreshold = 50; // pixels to trigger slide change
    
    function getItemsPerView() {
        if (window.innerWidth <= 576) return 1;
        if (window.innerWidth <= 991) return 2;
        return 3;
    }
    
    function showGallSlide(index, isResize = false) {
        if (!track || items.length === 0) return;
        
        const itemsPerView = getItemsPerView();
        const maxIndex = Math.max(0, items.length - itemsPerView);
        
        if (isResize) {
            if (index > maxIndex) index = maxIndex;
        } else {
            // Normal slide navigation: wrap around
            if (index > maxIndex) index = 0;
            if (index < 0) index = maxIndex;
        }
        
        currentGallSlide = index;
        
        const wrapper = track.parentElement;
        const wrapperWidth = wrapper.getBoundingClientRect().width;
        const itemWidth = items[0].getBoundingClientRect().width;
        const gap = 20; // 20px gap
        
        let amountToTranslate = currentGallSlide * (itemWidth + gap);
        
        // Center the last slide on mobile view
        if (window.innerWidth <= 576 && currentGallSlide === maxIndex) {
            const offset = (wrapperWidth - itemWidth) / 2;
            amountToTranslate = (currentGallSlide * (itemWidth + gap)) - offset;
        }
        
        track.style.transition = 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1)';
        track.style.transform = `translateX(-${amountToTranslate}px)`;
    }
    
    function nextGallSlide() {
        showGallSlide(currentGallSlide + 1);
    }
    
    function prevGallSlide() {
        showGallSlide(currentGallSlide - 1);
    }
    
    function startAutoPlay() {
        stopAutoPlay();
        gallInterval = setInterval(nextGallSlide, 5000);
    }
    
    function stopAutoPlay() {
        if (gallInterval) {
            clearInterval(gallInterval);
        }
    }
    
    // Drag & Swipe Event Handlers
    function getEventX(e) {
        if (e.touches && e.touches.length > 0) {
            return e.touches[0].clientX;
        }
        if (e.changedTouches && e.changedTouches.length > 0) {
            return e.changedTouches[0].clientX;
        }
        return e.clientX;
    }

    function handleDragStart(e) {
        if (!track) return;
        isDraggingGall = true;
        stopAutoPlay();
        startXGall = getEventX(e);
        currentXGall = startXGall;
        track.style.transition = 'none'; // disable transitions while dragging
        
        if (e.type === 'mousedown') {
            e.preventDefault();
        }
    }
    
    function handleDragMove(e) {
        if (!isDraggingGall || !track || items.length === 0) return;
        currentXGall = getEventX(e);
        const diffX = currentXGall - startXGall;
        
        const wrapper = track.parentElement;
        const wrapperWidth = wrapper.getBoundingClientRect().width;
        const itemWidth = items[0].getBoundingClientRect().width;
        const gap = 20;
        
        const itemsPerView = getItemsPerView();
        const maxIndex = Math.max(0, items.length - itemsPerView);
        
        let currentTranslation = -currentGallSlide * (itemWidth + gap);
        if (window.innerWidth <= 576 && currentGallSlide === maxIndex) {
            const offset = (wrapperWidth - itemWidth) / 2;
            currentTranslation = -(currentGallSlide * (itemWidth + gap)) + offset;
        }
        
        track.style.transform = `translateX(${currentTranslation + diffX}px)`;
    }
    
    function handleDragEnd(e) {
        if (!isDraggingGall) return;
        isDraggingGall = false;
        if (track) {
            track.style.transition = 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1)';
        }
        
        if (e) {
            currentXGall = getEventX(e);
        }
        
        const diffX = currentXGall - startXGall;
        if (Math.abs(diffX) > dragThreshold) {
            if (diffX < 0) {
                nextGallSlide();
            } else {
                prevGallSlide();
            }
        } else {
            showGallSlide(currentGallSlide);
        }
        startAutoPlay();
    }
    
    if (sliderContainer) {
        // Mouse dragging
        sliderContainer.addEventListener('mousedown', handleDragStart);
        window.addEventListener('mousemove', handleDragMove);
        window.addEventListener('mouseup', handleDragEnd);
        
        // Touch swiping
        sliderContainer.addEventListener('touchstart', handleDragStart, { passive: true });
        sliderContainer.addEventListener('touchmove', handleDragMove, { passive: true });
        sliderContainer.addEventListener('touchend', handleDragEnd);
        
        // Pause on hover
        sliderContainer.addEventListener('mouseenter', stopAutoPlay);
        sliderContainer.addEventListener('mouseleave', startAutoPlay);
    }
    
    // Stop default image/element drag behavior inside gallery cards
    if (track) {
        track.addEventListener('dragstart', (e) => e.preventDefault());
    }
    
    window.addEventListener('resize', () => {
        showGallSlide(currentGallSlide, true);
    });
    
    // Initialize
    setTimeout(() => {
        showGallSlide(0, true);
        startAutoPlay();
    }, 100);

    // --- 2. Package Category Filter Logic ---
    function filterPackages(category, btn) {
        // Update active tab styles
        const tabs = document.querySelectorAll('.pricing-tab-btn');
        tabs.forEach(tab => tab.classList.remove('active'));
        btn.classList.add('active');
        
        // Filter package cards
        const cards = document.querySelectorAll('.pkg-card');
        let count = 0;
        
        cards.forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.style.display = 'flex';
                count++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Display empty message if no packages match category
        const emptyMsg = document.getElementById('pricing-empty-msg');
        if (emptyMsg) {
            emptyMsg.style.display = (count === 0) ? 'block' : 'none';
        }
    }

    // --- 3. FAQ Accordion Toggle ---
    function toggleFaqCard(button) {
        const item = button.closest('.faq-card-item');
        const content = item.querySelector('.faq-card-content');
        const isActive = item.classList.contains('active');
        
        // Close all faq items
        document.querySelectorAll('.faq-card-item').forEach(faqItem => {
            faqItem.classList.remove('active');
            faqItem.querySelector('.faq-card-content').style.maxHeight = null;
        });
        
        // If it wasn't active, open it
        if (!isActive) {
            item.classList.add('active');
            content.style.maxHeight = content.scrollHeight + "px";
        }
    }

    // --- 4. Page Social Sharing Link Router ---
    function sharePage(platform) {
        const url = encodeURIComponent(window.location.href);
        const title = encodeURIComponent(document.title);
        let shareUrl = '';
        
        if (platform === 'facebook') {
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
        } else if (platform === 'whatsapp') {
            shareUrl = `https://api.whatsapp.com/send?text=${title}%20${url}`;
        } else if (platform === 'twitter') {
            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
        } else if (platform === 'copy') {
            navigator.clipboard.writeText(window.location.href).then(() => {
                const btn = document.querySelector('.copied-target');
                btn.classList.add('copied');
                btn.innerHTML = '<i class="bi bi-check2"></i>';
                alert('Link halaman berhasil disalin!');
                setTimeout(() => {
                    btn.classList.remove('copied');
                    btn.innerHTML = '<i class="bi bi-link-45deg"></i>';
                }, 3000);
            });
            return;
        }
        
        if (shareUrl) {
            window.open(shareUrl, '_blank');
        }
    }

    // --- 5. Custom Premium Toast Notification for Development ---
    function showDevelopmentToast() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.position = 'fixed';
            container.style.top = '24px';
            container.style.right = '24px';
            container.style.zIndex = '99999';
            container.style.display = 'flex';
            container.style.flexDirection = 'column';
            container.style.gap = '10px';
            container.style.pointerEvents = 'none';
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        toast.className = 'custom-toast';
        toast.style.background = '#FFFFFF';
        toast.style.color = '#111111';
        toast.style.borderLeft = '4px solid #FF6B00';
        toast.style.padding = '14px 20px';
        toast.style.borderRadius = '8px';
        toast.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.15)';
        toast.style.fontFamily = 'system-ui, -apple-system, sans-serif';
        toast.style.fontSize = '0.88rem';
        toast.style.fontWeight = '600';
        toast.style.minWidth = '290px';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(40px)';
        toast.style.transition = 'all 0.35s cubic-bezier(0.16, 1, 0.3, 1)';
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        toast.style.gap = '10px';
        toast.style.pointerEvents = 'auto';
        
        toast.innerHTML = `
            <i class="bi bi-info-circle-fill" style="color: #FF6B00; font-size: 1.1rem;"></i>
            <span>Terima kasih, aplikasi dalam proses development</span>
        `;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        }, 50);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                toast.remove();
            }, 350);
        }, 4000);
    }

    // --- 6. Other Branches Slider Logic (Carousel with Mouse Drag & Touch Swipe) ---
    let currentBranchSlide = 0;
    const branchesTrack = document.querySelector('.branches-track');
    const branchItems = document.querySelectorAll('.branches-track .branch-card');
    const branchesWrapper = document.querySelector('.branches-slider-wrapper');
    
    function getBranchItemsPerView() {
        if (window.innerWidth <= 576) return 1;
        if (window.innerWidth <= 991) return 2;
        return 3;
    }
    
    function showBranchSlide(index, isResize = false) {
        if (!branchesTrack || branchItems.length === 0) return;
        
        const itemsPerView = getBranchItemsPerView();
        const maxIndex = Math.max(0, branchItems.length - itemsPerView);
        
        if (isResize) {
            if (index > maxIndex) index = maxIndex;
        } else {
            // Normal slide navigation: wrap around
            if (index > maxIndex) index = 0;
            if (index < 0) index = maxIndex;
        }
        
        currentBranchSlide = index;
        
        const wrapper = branchesTrack.parentElement;
        const wrapperWidth = wrapper.getBoundingClientRect().width;
        const itemWidth = branchItems[0].getBoundingClientRect().width;
        const gap = 20; // 20px gap
        
        let amountToTranslate = currentBranchSlide * (itemWidth + gap);
        
        // Center the last slide on mobile view
        if (window.innerWidth <= 576 && currentBranchSlide === maxIndex) {
            const offset = (wrapperWidth - itemWidth) / 2;
            amountToTranslate = (currentBranchSlide * (itemWidth + gap)) - offset;
        }
        
        branchesTrack.style.transition = 'transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        branchesTrack.style.transform = `translateX(-${amountToTranslate}px)`;
    }
    
    function nextBranchSlide() {
        showBranchSlide(currentBranchSlide + 1);
    }
    
    function prevBranchSlide() {
        showBranchSlide(currentBranchSlide - 1);
    }
    
    // Auto slide branches every 6 seconds
    let branchesInterval = setInterval(nextBranchSlide, 6000);
    
    // Drag & Swipe Logic
    let isDragging = false;
    let startX = 0;
    let currentX = 0;
    let translateVal = 0;
    
    if (branchesWrapper) {
        branchesWrapper.style.cursor = 'grab';
        
        // Helper to get X coordinate from event (mouse or touch)
        const getX = (e) => e.touches ? e.touches[0].clientX : e.clientX;
        
        const dragStart = (e) => {
            isDragging = true;
            branchesWrapper.style.cursor = 'grabbing';
            clearInterval(branchesInterval);
            
            startX = getX(e);
            currentX = startX; // Initialize currentX to prevent jumps
            
            // Get current transform value
            const style = window.getComputedStyle(branchesTrack);
            const matrix = new WebKitCSSMatrix(style.transform);
            translateVal = matrix.m41; // horizontal translation
            
            // Disable transitions during drag for real-time tracking
            branchesTrack.style.transition = 'none';
        };
        
        const dragMove = (e) => {
            if (!isDragging) return;
            currentX = getX(e);
            const deltaX = currentX - startX;
            branchesTrack.style.transform = `translateX(${translateVal + deltaX}px)`;
        };
        
        const dragEnd = (e) => {
            if (!isDragging) return;
            isDragging = false;
            branchesWrapper.style.cursor = 'grab';
            
            const deltaX = currentX - startX;
            const threshold = 60; // drag threshold in pixels
            
            // Re-enable transition
            branchesTrack.style.transition = 'transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
            
            const itemsPerView = getBranchItemsPerView();
            const maxIndex = Math.max(0, branchItems.length - itemsPerView);
            
            if (deltaX < -threshold && currentBranchSlide < maxIndex) {
                // Dragged left -> Next
                showBranchSlide(currentBranchSlide + 1);
            } else if (deltaX > threshold && currentBranchSlide > 0) {
                // Dragged right -> Prev
                showBranchSlide(currentBranchSlide - 1);
            } else {
                // Reset to current active slide position
                showBranchSlide(currentBranchSlide);
            }
            
            // Restart autoplay
            clearInterval(branchesInterval);
            branchesInterval = setInterval(nextBranchSlide, 6000);
        };
        
        // Mouse Listeners
        branchesWrapper.addEventListener('mousedown', dragStart);
        window.addEventListener('mousemove', dragMove);
        window.addEventListener('mouseup', dragEnd);
        
        // Touch Listeners (for mobile/tablet)
        branchesWrapper.addEventListener('touchstart', dragStart, { passive: true });
        branchesWrapper.addEventListener('touchmove', dragMove, { passive: true });
        branchesWrapper.addEventListener('touchend', dragEnd);
        
        // Stop default image drag behavior inside cards
        branchesTrack.addEventListener('dragstart', (e) => e.preventDefault());
    }
    
    const branchesContainer = document.querySelector('.branches-slider-container');
    if (branchesContainer) {
        branchesContainer.addEventListener('mouseenter', () => clearInterval(branchesInterval));
        branchesContainer.addEventListener('mouseleave', () => {
            if (!isDragging) {
                clearInterval(branchesInterval);
                branchesInterval = setInterval(nextBranchSlide, 6000);
            }
        });
    }
    
    window.addEventListener('resize', () => {
        showBranchSlide(currentBranchSlide, true);
    });
    
    // Initialize positioning after rendering
    setTimeout(() => {
        showBranchSlide(0, true);
    }, 150);
</script>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>