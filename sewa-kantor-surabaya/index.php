<?php
/**
 * Urban Office - Private Office (Sewa Kantor Surabaya) Landing Page
 */

require_once dirname(dirname(__FILE__)) . '/inc/config.php';
require_once dirname(dirname(__FILE__)) . '/inc/functions.php';
require_once dirname(dirname(__FILE__)) . '/inc/locations_data.php';

// Per-city Private Office landing (pilot). The clean URL /sewa-kantor-{city}/ is rewritten to
// this file with ?lokasi={city}; the real /sewa-kantor-surabaya/ folder is the Surabaya default.
$svc_category  = 'private-office';
$svc_city_slug = isset($_GET['lokasi']) ? strtolower(trim($_GET['lokasi'])) : 'surabaya';
$svc_branches  = service_city_branches($svc_category, $svc_city_slug);
if (empty($svc_branches)) {
    $svc_city_slug = 'surabaya';
    $svc_branches  = service_city_branches($svc_category, $svc_city_slug);
}
$po_city_label = !empty($svc_branches) ? $svc_branches[0]['city'] : 'Surabaya';

// Per-city page slug so SEO tags + canonical vary per city (and cache keys don't collide).
$page_slug = 'sewa-kantor-' . $svc_city_slug;
require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<!-- Expose current city so the "Cabang Kami" filter defaults to it -->
<script>window.currentBranchCity = <?php echo json_encode($svc_city_slug); ?>;</script>

<!-- Hero Section -->
<?php
$hero_tag = 'Serviced Office ' . $po_city_label;
$hero_title = 'Sewa Ruang Kantor Privat (Fully Furnished) di ' . $po_city_label;
$hero_desc = 'Kantor privat siap pakai (ready-to-work) di ' . $po_city_label . ' dengan desain modern. Lengkap dengan meja kursi premium, AC, jaringan internet serat optik, dan gratis biaya utilitas (listrik/air).';
$hero_cta_text = 'Dapatkan Price List';
$hero_cta_url = '#pricing';
// Hero image + floating card follow the city's branch so they change per location.
if (!empty($svc_branches[0]['image'])) {
    $hero_img = $svc_branches[0]['image'];
}
$card_title = 'Private Office ' . $po_city_label;
$card_desc = !empty($svc_branches[0]['address']) ? $svc_branches[0]['address'] : '';
include dirname(dirname(__FILE__)) . '/inc/components/hero.php';
?>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>


<!-- Pricing Card Configuration -->
<div id="pricing">
    <?php
    $pricing_title = 'Pilihan Ruang Private Office';
    $has_billing_toggle = true;
    $packages = [
        [
            'name' => 'Private Office Small',
            'price_monthly' => '4.000.000',
            'price_yearly' => '36.000.000',
            'price_yearly_monthly' => '3.000.000',
            'branch' => 'MERR',
            'period' => 'Bulan',
            'description' => 'Ideal untuk startup kecil atau cabang perwakilan dengan kapasitas 2 orang.',
            'features' => [
                'Kapasitas 2 orang',
                'Ukuran Ruangan 6m²'
            ],
            'cta_text' => 'Pesan Ruangan',
            'cta_link' => '#contact'
        ],
        [
            'name' => 'Private Office Medium',
            'price_monthly' => '5.500.000',
            'price_yearly' => '49.500.000',
            'price_yearly_monthly' => '4.125.000',
            'branch' => 'MERR',
            'period' => 'Bulan',
            'popular' => true,
            'description' => 'Sempurna untuk tim berkembang dengan kapasitas 4-5 orang.',
            'features' => [
                'Kapasitas 4-5 orang',
                'Ukuran Ruangan 10m² - 12m²'
            ],
            'cta_text' => 'Pesan Ruangan',
            'cta_link' => '#contact'
        ],
        [
            'name' => 'Private Office Corporate',
            'price_monthly' => '6.000.000',
            'price_yearly' => '54.000.000',
            'price_yearly_monthly' => '4.500.000',
            'branch' => 'MERR',
            'period' => 'Bulan',
            'description' => 'Ruang lega berkelas korporat untuk tim 5-6 orang di ruangan terluas.',
            'features' => [
                'Kapasitas 5-6 orang',
                'Ukuran Ruangan 14m² - 16m²'
            ],
            'cta_text' => 'Pesan Ruangan',
            'cta_link' => '#contact'
        ]
    ];
    include dirname(dirname(__FILE__)) . '/inc/components/pricing_cards.php';
    ?>
</div>

<!-- Per-city local content: real branch address, map & advantages (unique per city, from
     locations_data.php) so each city page is genuinely differentiated, not a thin duplicate. -->
<section class="section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Lokasi Private Office di <?php echo sanitize($po_city_label); ?></h2>
            <p class="section-subtitle">Kantor privat Urban Office di <?php echo sanitize($po_city_label); ?> berada di lokasi strategis dengan alamat bisnis prestisius. Berikut cabang yang melayani sewa kantor di <?php echo sanitize($po_city_label); ?>.</p>
        </div>
        <div class="local-branch-grid<?php echo count($svc_branches) === 1 ? ' local-branch-grid--single' : ''; ?>">
            <?php foreach ($svc_branches as $svc_b): ?>
            <div class="premium-card" style="text-align: left; display: flex; flex-direction: column;">
                <h3 style="font-size: 1.2rem; margin-bottom: 8px;"><?php echo sanitize($svc_b['title'] ?? ('Urban Office ' . $svc_b['short_title'])); ?></h3>
                <p style="color: hsl(var(--clr-text-muted)); font-size: 0.9rem; margin-bottom: 10px;"><i class="bi bi-geo-alt-fill"></i> <?php echo sanitize($svc_b['address']); ?></p>
                <?php if (!empty($svc_b['rating'])): ?>
                <p style="font-size: 0.9rem; margin-bottom: 12px;">⭐ <?php echo sanitize($svc_b['rating']); ?> · <?php echo sanitize($svc_b['reviews_count'] ?? '0'); ?> ulasan Google</p>
                <?php endif; ?>
                <?php if (!empty($svc_b['advantages'])): ?>
                <ul class="card-features-list">
                    <?php foreach (array_slice($svc_b['advantages'], 0, 5) as $svc_adv): ?>
                    <li><?php echo sanitize($svc_adv); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                <?php if (!empty($svc_b['map_embed'])): ?>
                <div style="margin-top: auto; padding-top: 16px; border-radius: var(--radius-sm); overflow: hidden;">
                    <iframe src="<?php echo sanitize($svc_b['map_embed']); ?>" width="100%" height="180" style="border:0; display:block; border-radius: var(--radius-sm);" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Peta <?php echo sanitize($svc_b['short_title']); ?>"></iframe>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Component -->
<?php
$features_title = 'Fasilitas Layanan Serviced Office';
$features_list = [
    [
        'title' => 'Ruangan Siap Pakai',
        'desc' => 'Kantor sudah fully furnished (meja, kursi, lemari dokumen). Tinggal bawa laptop dan langsung bekerja.',
        'icon' => '🛋️'
    ],
    [
        'title' => 'Bebas Biaya Listrik & AC',
        'desc' => 'Tidak perlu memikirkan tagihan bulanan. Biaya sewa sudah mencakup listrik, air, AC, dan maintenance.',
        'icon' => '💡'
    ],
    [
        'title' => 'Resepsionis & Lobby',
        'desc' => 'Lobby penerima tamu yang luas dan mewah dengan staf resepsionis profesional menyambut kolega bisnis Anda.',
        'icon' => '🛎️'
    ],
    [
        'title' => 'Internet Serat Optik',
        'desc' => 'Koneksi internet fiber optic berkecepatan tinggi gratis untuk menunjang kelancaran bisnis Anda.',
        'icon' => '📶'
    ],
    [
        'title' => 'Trial Harian & Mingguan',
        'desc' => 'Tersedia opsi uji coba harian atau mingguan sebelum Anda memutuskan sewa jangka panjang.',
        'icon' => '<i class="bi bi-calendar3"></i>'
    ],
    [
        'title' => 'Keamanan & Cleaning',
        'desc' => 'Gedung diawasi CCTV 24 jam dengan petugas keamanan di gerbang, ditambah cleaning service harian untuk kebersihan kubikel.',
        'icon' => '🧹'
    ],
    [
        'title' => 'Free Flow Beverage',
        'desc' => 'Nikmati kopi, teh, dan air mineral berkualitas premium secara gratis sepuasnya untuk Anda dan karyawan Anda.',
        'icon' => '☕'
    ],
    [
        'title' => 'Pantry & Lounge',
        'desc' => 'Gunakan area pantry bersama, microwave, kulkas, dan ruang tunggu komunal yang santai untuk beristirahat.',
        'icon' => '🍎'
    ]
];
include dirname(dirname(__FILE__)) . '/inc/components/features.php';
?>

<!-- Private Office Gallery Slider Section -->
<section class="section" style="background-color: hsl(var(--clr-bg-surface)); border-top: 1px solid hsl(var(--clr-border)); overflow: hidden; padding: 60px 0;">
    <div class="container">
        <!-- Header row with title -->
        <div style="margin-bottom: 30px;">
            <h2 class="section-title" style="margin: 0;">Galeri Foto Private Office</h2>
            <p style="margin: 5px 0 0 0; max-width: 600px; color: hsl(var(--clr-text-muted)); font-size: 0.95rem;">Jelajahi suasana ruang kantor privat modern siap pakai kami.</p>
        </div>
        
        <!-- Slider Window (draggable/swipeable) -->
        <div class="office-gallery-window" style="overflow: hidden; margin: 0 -10px; padding: 10px 0; cursor: grab; user-select: none;">
            <div class="office-gallery-wrapper" id="office-gallery-slider" style="display: flex; transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1); will-change: transform;">
                <?php
                $gallery_items = [
                    ['img' => 'private-office-small.webp', 'title' => 'Private Office Small', 'desc' => 'Kapasitas 2 Orang · 6m²', 'pax' => '2 Pax', 'type_id' => 1],
                    ['img' => 'private-office-medium.webp', 'title' => 'Private Office Corporate', 'desc' => 'Kapasitas 5 Orang · 14m²', 'pax' => '5 Pax', 'type_id' => 2],
                    ['img' => 'private-office-corporate.webp', 'title' => 'Private Office Corporate', 'desc' => 'Kapasitas 5 Orang · 14m²', 'pax' => '5 Pax', 'type_id' => 3],
                    ['img' => 'private-office-4.webp', 'title' => 'Private Office Medium', 'desc' => 'Kapasitas 4 Orang · 12m²', 'pax' => '4 Pax', 'type_id' => 4],
                    ['img' => 'private-office-5.webp', 'title' => 'Private Office Corporate', 'desc' => 'Kapasitas 6 Orang · 16m²', 'pax' => '6 Pax', 'type_id' => 5],
                    ['img' => 'private-office-6.webp', 'title' => 'Private Office Small', 'desc' => 'Kapasitas 2 Orang · 6m²', 'pax' => '2 Pax', 'type_id' => 6],
                    ['img' => 'private-office-7.webp', 'title' => 'Private Office Small', 'desc' => 'Kapasitas 2 Orang · 6m²', 'pax' => '2 Pax', 'type_id' => 7],
                    ['img' => 'private-office-8.webp', 'title' => 'Private Office Medium', 'desc' => 'Kapasitas 5 Orang · 10m²', 'pax' => '5 Pax', 'type_id' => 8],
                ];
                
                foreach ($gallery_items as $item):
                ?>
                <div class="gallery-card-slide">
                    <div class="gallery-item-card">
                        <!-- Image Container with Pax Badge -->
                        <div class="gallery-img-container">
                            <img src="<?php echo BASE_URL; ?>assets/images/privateoffice/<?php echo $item['img']; ?>" alt="<?php echo $item['title']; ?>" draggable="false">
                            <span class="gallery-pax-badge">
                                <?php echo $item['pax']; ?>
                            </span>
                        </div>
                        
                        <!-- Body Content -->
                        <div class="gallery-body-content">
                            <div class="gallery-text-wrap">
                                <h4 class="gallery-card-title"><?php echo $item['title']; ?></h4>
                                <p class="gallery-card-desc"><?php echo $item['desc']; ?></p>
                            </div>
                            
                            <!-- Action button connecting to detail page -->
                            <a href="<?php echo BASE_URL; ?>sewa-kantor-surabaya/detail.php?type=<?php echo $item['type_id']; ?>" class="btn btn-primary gallery-cta-btn" draggable="false">
                                Detail <i class="bi bi-arrow-right"></i>
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
    aspect-ratio: 4 / 3; /* Match detail hero framing so the card preview == the detail crop */
}

.gallery-img-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center; /* Same focal point as the detail hero */
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('office-gallery-slider');
    const container = document.querySelector('.office-gallery-window');
    const slides = slider.querySelectorAll('.gallery-card-slide');
    
    let currentIdx = 0;
    let autoPlayInterval = null;
    
    function getVisibleCount() {
        const width = window.innerWidth;
        if (width > 1200) return 5;  // 5 cards on desktop (was 4)
        if (width > 991) return 4;   // 4 cards on small desktop / tablet landscape
        if (width > 768) return 3;   // 3 cards on tablet (was 2)
        return 1.3;                  // 1.3 cards on mobile to make it larger and peek next slide (was 2)
    }
    
    function getSlideWidth() {
        return container.clientWidth / getVisibleCount();
    }
    
    function updateSlideStyles() {
        const visibleCount = getVisibleCount();
        slides.forEach(slide => {
            slide.style.flex = `0 0 ${100 / visibleCount}%`;
            slide.style.width = `${100 / visibleCount}%`;
        });
    }
    
    function showSlide(idx) {
        const visibleCount = getVisibleCount();
        const maxIndex = Math.max(0, slides.length - visibleCount);
        
        if (idx > maxIndex) {
            idx = 0; // Wrap around to first
        } else if (idx < 0) {
            idx = maxIndex; // Wrap around to last
        }
        
        currentIdx = idx;
        const slideWidth = getSlideWidth();
        slider.style.transition = 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1)';
        slider.style.transform = `translateX(-${currentIdx * slideWidth}px)`;
    }
    
    function nextSlide() {
        showSlide(currentIdx + 1);
    }
    
    function prevSlide() {
        showSlide(currentIdx - 1);
    }
    
    function startAutoPlay() {
        stopAutoPlay();
        autoPlayInterval = setInterval(nextSlide, 4500); // Auto slide every 4.5 seconds
    }
    
    function stopAutoPlay() {
        if (autoPlayInterval) {
            clearInterval(autoPlayInterval);
        }
    }
    
    // Drag and swipe logic
    let isDragging = false;
    let startX = 0;
    let currentX = 0;
    let dragThreshold = 50; // pixels to trigger slide change
    
    function handleDragStart(e) {
        isDragging = true;
        stopAutoPlay();
        startX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
        slider.style.transition = 'none'; // disable transitions while dragging
    }
    
    function handleDragMove(e) {
        if (!isDragging) return;
        currentX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
        const diffX = currentX - startX;
        
        const slideWidth = getSlideWidth();
        const currentTranslation = -currentIdx * slideWidth;
        slider.style.transform = `translateX(${currentTranslation + diffX}px)`;
    }
    
    function handleDragEnd() {
        if (!isDragging) return;
        isDragging = false;
        slider.style.transition = 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1)'; // restore transition
        
        const diffX = currentX - startX;
        if (Math.abs(diffX) > dragThreshold) {
            if (diffX < 0) {
                nextSlide();
            } else {
                prevSlide();
            }
        } else {
            showSlide(currentIdx);
        }
        startAutoPlay();
    }
    
    // Mouse dragging
    container.addEventListener('mousedown', handleDragStart);
    window.addEventListener('mousemove', handleDragMove);
    window.addEventListener('mouseup', handleDragEnd);
    
    // Touch swiping
    container.addEventListener('touchstart', handleDragStart, { passive: true });
    container.addEventListener('touchmove', handleDragMove, { passive: true });
    container.addEventListener('touchend', handleDragEnd);
    
    // Resize responsiveness
    window.addEventListener('resize', () => {
        updateSlideStyles();
        showSlide(currentIdx);
    });
    
    // Initial calls
    updateSlideStyles();
    showSlide(currentIdx);
    startAutoPlay();
    
    // Pause on hover
    container.addEventListener('mouseenter', stopAutoPlay);
    container.addEventListener('mouseleave', startAutoPlay);
});
</script>

<!-- FAQs Section -->
<?php
$faqs = [
    [
        'question' => 'Bagaimana jika ingin melakukan perpanjangan sewa?',
        'answer' => 'Untuk perpanjangan sewa saat ini dapat dilakukan langsung melalui tim admin kami dengan memberikan konfirmasi "Perpanjangan Sewa".'
    ],
    [
        'question' => 'Apakah ada koneksi WiFi? Kecepatannya berapa?',
        'answer' => 'Ya, tersedia koneksi WiFi berkecepatan tinggi hingga 100 Mbps di seluruh area kerja.'
    ],
    [
        'question' => 'Apakah tersedia café atau tempat makan di dalam gedung?',
        'answer' => 'Ya, tersedia café dan tempat makan yang terletak di Lantai 1 gedung untuk kenyamanan Anda.'
    ],
    [
        'question' => 'Apakah tersedia area parkir untuk kendaraan?',
        'answer' => 'Ya, area parkir tersedia luas dan dapat digunakan secara gratis (free) bagi para penyewa.'
    ]
];
include dirname(dirname(__FILE__)) . '/inc/components/faq.php';
?>



<!-- Testimonials Section -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/branches.php'; ?>

<!-- Contact Form -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
