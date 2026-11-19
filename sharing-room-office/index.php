<?php
/**
 * Urban Office - Sharing Room Office Landing Page
 */

$page_slug = 'sharing-room-office';
require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<!-- Hero Section -->
<?php
$hero_tag = 'Sharing Office';
$hero_title = 'Sewa Sharing Room Office';
// Reflect the ?lokasi city (validated against locations_data) in the hero for relevance.
$svc_city = service_city_from_query('sharing-room-office');
if ($svc_city !== '') { $hero_tag .= ' ' . $svc_city; $hero_title .= ' di ' . $svc_city; }
$hero_desc = 'Ruang kantor bersama yang didesain untuk beberapa startup atau perusahaan dalam satu area kerja. Hemat biaya operasional, lengkap dengan furniture, listrik, internet, dan kebersihan.';
$hero_cta_text = 'Pesan Meja Sharing';
$hero_cta_url = '#pricing';
include dirname(dirname(__FILE__)) . '/inc/components/hero.php';
?>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>


<!-- Pricing Cards -->
<div id="pricing">
    <?php
    $sr_lokasi_q   = isset($_GET['lokasi']) ? strtolower(trim((string) $_GET['lokasi'])) : 'surabaya';
    $sr_branches   = service_city_branches('sharing-room-office', $sr_lokasi_q);
    if (empty($sr_branches)) {
        $sr_branches = service_city_branches('sharing-room-office', 'surabaya');
    }
    $sr_active_branch = resolve_active_booking_branch($sr_branches) ?: ($sr_branches[0] ?? ['slug' => 'surabaya', 'short_title' => 'MERR', 'location' => 'Surabaya Timur']);
    $sr_active_slug   = (string) ($sr_active_branch['slug'] ?? 'surabaya');
    $sr_has_webapp    = has_webapp_booking($sr_active_slug);
    $sr_booking_url   = $sr_has_webapp ? build_webapp_booking_url($sr_active_slug, 'sharing-room-office') : '#contact';
    $sr_cta_target    = $sr_has_webapp ? '_blank' : '';
    $sr_branch_label  = ($sr_active_branch['short_title'] ?? 'MERR') . ' (' . ($sr_active_branch['location'] ?? 'Surabaya Timur') . ')';

    $pricing_title = 'Pilihan Paket Sharing Room Office';
    $has_billing_toggle = true;
    $packages = [
        [
            'name' => 'Sharing Desk Starter',
            'price_monthly' => '1.000.000',
            'price_yearly' => '12.000.000',
            'period_monthly' => 'pax/bulan',
            'period_yearly' => 'pax/tahun',
            'hide_minimum_info' => true,
            'description' => 'Meja kerja khusus di dalam ruangan kantor bersama (sharing office) untuk 1 pax.',
            'features' => [],
            'cta_text' => $sr_has_webapp ? 'Booking Online' : 'Booking Sekarang',
            'cta_link' => $sr_booking_url,
            'cta_target' => $sr_cta_target,
            'is_webapp_booking' => $sr_has_webapp,
            'service' => 'Sharing Room Office',
            'branch' => $sr_branch_label
        ]
    ];
    include dirname(dirname(__FILE__)) . '/inc/components/pricing_cards.php';
    ?>
</div>

<!-- Features Component -->
<?php
$features_title = 'Fasilitas Layanan Sharing Room Office';
$features_list = [
    [
        'title' => 'Dedicated Workstation',
        'desc' => 'Meja kerja khusus lengkap dengan laci penyimpanan pribadi, tidak digeser atau ditempati member lain.',
        'icon' => '🪑'
    ],
    [
        'title' => 'Kursi Kerja Ergonomis',
        'desc' => 'Kursi kerja ergonomis yang dirancang untuk menjaga kenyamanan dan postur tubuh Anda selama jam kerja.',
        'icon' => '🛋️'
    ],
    [
        'title' => 'Internet Tanpa Batas',
        'desc' => 'Koneksi internet nirkabel fiber optic berkecepatan tinggi tanpa kuota untuk produktivitas kerja harian Anda.',
        'icon' => '📶'
    ],
    [
        'title' => 'Free Flow Coffee & Tea',
        'desc' => 'Akses gratis tak terbatas ke pantry untuk menyeduh kopi, teh, dan air mineral berkualitas sepanjang hari.',
        'icon' => '☕'
    ],
    [
        'title' => 'Domisili & Surat Bisnis',
        'desc' => 'Gunakan alamat gedung kami sebagai alamat korespondensi bisnis resmi dan layanan penerimaan dokumen surat.',
        'icon' => '🏢'
    ],
    [
        'title' => 'Kuota Meeting Room',
        'desc' => 'Akses gratis penggunaan Ruang Meeting (Meeting Room) secara bulanan untuk kebutuhan rapat formal Anda bersama tim.',
        'icon' => '🤝'
    ],
    [
        'title' => 'Loker Penyimpanan Aman',
        'desc' => 'Loker berkunci khusus yang aman untuk menyimpan berkas penting atau barang berharga saat Anda meninggalkan meja.',
        'icon' => '🔒'
    ],
    [
        'title' => 'Resepsionis Profesional',
        'desc' => 'Staf lobi yang profesional siap menyambut tamu bisnis Anda dan mengarahkan paket kiriman ke meja Anda.',
        'icon' => '🛎️'
    ]
];
include dirname(dirname(__FILE__)) . '/inc/components/features.php';
?>

<!-- Sharing Room Gallery Section -->
<section class="section" style="background-color: hsl(var(--clr-bg-surface)); border-top: 1px solid hsl(var(--clr-border)); overflow: hidden; padding: 60px 0;">
    <div class="container">
        <!-- Header row with title -->
        <div style="margin-bottom: 40px; text-align: center;">
            <h2 class="section-title" style="margin: 0;">Galeri Foto Sharing Room Office</h2>
            <p style="margin: 10px auto 0 auto; max-width: 600px; color: hsl(var(--clr-text-muted)); font-size: 0.95rem;">Jelajahi suasana ruang kantor bersama yang nyaman dan produktif di Urban Office.</p>
        </div>
        
        <!-- Slider Window (draggable/swipeable) -->
        <div class="office-gallery-window" style="overflow: hidden; margin: 0 -10px; padding: 10px 0; cursor: grab; user-select: none;">
            <div class="office-gallery-wrapper" id="office-gallery-slider" data-gallery-slider data-slide-selector=".gallery-card-slide" data-visible-desktop="4" data-visible-laptop="3" data-visible-tablet="2.2" data-visible-mobile="1.3" style="display: flex; transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1); will-change: transform;">
                
                <!-- Card 1 -->
                <div class="gallery-card-slide">
                    <div class="gallery-item-card">
                        <!-- Image Container -->
                        <div class="gallery-img-container">
                            <img src="<?php echo BASE_URL; ?>assets/images/sharingroom/sharing-room-desk.webp" alt="Sharing Desk Area" width="400" height="300" loading="lazy" decoding="async" draggable="false">
                            <span class="gallery-pax-badge">Dedicated Desk</span>
                        </div>
                        
                        <!-- Body Content -->
                        <div class="gallery-body-content">
                            <div class="gallery-text-wrap">
                                <h4 class="gallery-card-title">Sharing Desk Area</h4>
                                <p class="gallery-card-desc">Ruang kerja terdedikasi yang dilengkapi dengan sekat partisi ergonomis untuk menjaga fokus kerja Anda dan tim.</p>
                            </div>
                            
                            <a href="#contact" class="btn btn-primary gallery-cta-btn" draggable="false" onclick="activateOfferMode('Sharing Desk Starter', 'Sharing Room Office')">
                                Ajukan Penawaran <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Card 2 -->
                <div class="gallery-card-slide">
                    <div class="gallery-item-card">
                        <!-- Image Container -->
                        <div class="gallery-img-container">
                            <img src="<?php echo BASE_URL; ?>assets/images/sharingroom/sharing-room-lounge.webp" alt="Sharing Lounge & Pantry" width="400" height="300" loading="lazy" decoding="async" draggable="false">
                            <span class="gallery-pax-badge">Shared Space</span>
                        </div>
                        
                        <!-- Body Content -->
                        <div class="gallery-body-content">
                            <div class="gallery-text-wrap">
                                <h4 class="gallery-card-title">Sharing Lounge & Pantry</h4>
                                <p class="gallery-card-desc">Area istirahat komunal yang nyaman dan luas untuk menikmati kopi gratis sambil berjejaring dengan pelaku bisnis lainnya.</p>
                            </div>
                            
                            <a href="#contact" class="btn btn-primary gallery-cta-btn" draggable="false" onclick="activateOfferMode('Sharing Desk Starter', 'Sharing Room Office')">
                                Ajukan Penawaran <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
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
    height: 150px;
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
    padding: 14px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    flex-grow: 1;
    text-align: left;
}

.gallery-card-title {
    font-size: 0.95rem;
    margin-bottom: 4px;
    font-weight: 700;
    color: #111111;
}

.gallery-card-desc {
    font-size: 0.78rem;
    color: hsl(var(--clr-text-muted));
    margin-bottom: 12px;
    line-height: 1.4;
}

.gallery-cta-btn {
    padding: 8px 12px !important;
    font-size: 0.72rem !important;
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

/* Responsive Overrides for Gallery Cards */
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

/* Advantages Section styling */
.advantages-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.advantage-card {
    background-color: #FFFFFF;
    border: 1px solid hsl(var(--clr-border));
    padding: 20px;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    display: flex;
    gap: 15px;
    align-items: flex-start;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.advantage-icon-box {
    width: 38px;
    height: 38px;
    background-color: rgba(255, 107, 0, 0.1);
    color: hsl(var(--clr-primary));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
    box-shadow: 0 4px 8px rgba(255, 107, 0, 0.08);
}

.advantage-text-content {
    text-align: left;
}

.advantage-title {
    font-size: 1rem;
    margin-bottom: 6px;
    font-weight: 700;
    color: #111111;
    line-height: 1.3;
}

.advantage-desc {
    font-size: 0.8rem;
    color: hsl(var(--clr-text-muted));
    margin: 0;
    line-height: 1.5;
}

.advantage-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md) !important;
    border-color: hsl(var(--clr-primary)) !important;
}

/* Responsive Advantages: Horizontal slider on mobile */
@media (max-width: 768px) {
    .advantages-grid {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        overflow-x: auto !important;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        gap: 16px !important;
        padding: 10px 16px 20px 16px !important;
        margin-left: -24px !important;
        margin-right: -24px !important;
        scrollbar-width: none; /* Hide scrollbar for Firefox */
    }
    .advantages-grid::-webkit-scrollbar {
        display: none; /* Hide scrollbar for Chrome/Safari */
    }
    .advantage-card {
        flex: 0 0 calc(50% - 8px) !important; /* 2 cards visible in viewport */
        min-width: calc(50% - 8px) !important;
        max-width: calc(50% - 8px) !important;
        scroll-snap-align: center !important;
        flex-direction: column !important; /* Stack icon and text vertically */
        align-items: center !important;
        text-align: center !important;
        padding: 20px 12px !important;
        box-sizing: border-box;
    }
    .advantage-text-content {
        text-align: center !important;
    }
    .advantage-icon-box {
        width: 40px !important;
        height: 40px !important;
        font-size: 1.2rem !important;
        margin-bottom: 12px !important;
    }
    .advantage-title {
        font-size: 0.88rem !important;
        margin-bottom: 6px !important;
    }
    .advantage-desc {
        font-size: 0.72rem !important;
        line-height: 1.4 !important;
    }
}

@media (max-width: 576px) {
    .advantages-grid {
        gap: 12px !important;
        padding-left: 20px !important;
        padding-right: 20px !important;
    }
    .advantage-card {
        flex: 0 0 72% !important;
        min-width: 72% !important;
        max-width: 72% !important;
        scroll-snap-align: center !important;
        padding: 20px 14px !important;
    }
    .advantage-icon-box {
        width: 32px !important;
        height: 32px !important;
        font-size: 1rem !important;
        margin-bottom: 8px !important;
    }
}
</style>

<!-- Gallery slider initialized via assets/js/main.js (initOfficeGallerySliders) -->

<!-- Advantages / Keuntungan Section -->
<section class="section" style="background-color: hsl(var(--clr-bg-secondary)); border-top: 1px solid hsl(var(--clr-border)); border-bottom: 1px solid hsl(var(--clr-border)); padding: 60px 0;">
    <div class="container">
        <h3 class="text-center" style="font-size: 1.8rem; margin-bottom: 40px; font-weight: 800; color: #111111;">Keuntungan Sharing Room Office di Urban Office</h3>
        
        <div class="advantages-grid">
            <!-- Advantage 1 -->
            <div class="advantage-card">
                <div class="advantage-icon-box">
                    <i class="bi bi-check-lg" style="-webkit-text-stroke: 1px;"></i>
                </div>
                <div class="advantage-text-content">
                    <h4 class="advantage-title">Hemat Biaya Operasional</h4>
                    <p class="advantage-desc">Dapatkan fasilitas kantor eksklusif lengkap dengan biaya sewa jauh lebih hemat dibanding menyewa kantor privat konvensional. Cocok untuk perorangan maupun tim kecil.</p>
                </div>
            </div>
            
            <!-- Advantage 2 -->
            <div class="advantage-card">
                <div class="advantage-icon-box">
                    <i class="bi bi-check-lg" style="-webkit-text-stroke: 1px;"></i>
                </div>
                <div class="advantage-text-content">
                    <h4 class="advantage-title">Kolaborasi & Networking</h4>
                    <p class="advantage-desc">Bekerja berdampingan dengan pelaku bisnis, startup, dan profesional dari berbagai sektor. Buka kesempatan kerja sama dan perluas koneksi bisnis Anda secara alami.</p>
                </div>
            </div>
            
            <!-- Advantage 3 -->
            <div class="advantage-card">
                <div class="advantage-icon-box">
                    <i class="bi bi-check-lg" style="-webkit-text-stroke: 1px;"></i>
                </div>
                <div class="advantage-text-content">
                    <h4 class="advantage-title">Skalabilitas yang Fleksibel</h4>
                    <p class="advantage-desc">Sewa meja kerja sesuai jumlah tim saat ini dan tambah kapasitas meja dengan mudah seiring pertumbuhan usaha Anda tanpa perlu pindah gedung.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQs Section -->
<?php
$faqs = get_sharing_room_faqs();
include dirname(dirname(__FILE__)) . '/inc/components/faq.php';
?>



<!-- Contact Form -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
