<?php
/**
 * Urban Office - Private Office Detail Page
 */

$page_slug = 'sewa-kantor-surabaya';
require_once dirname(dirname(__FILE__)) . '/inc/header.php';

$type = isset($_GET['type']) ? (int)$_GET['type'] : 1;
if ($type < 1 || $type > 8) {
    $type = 1;
}

$types_data = [
    1 => [
        'name' => 'Private Office Small (2 Pax)',
        'img' => 'private-office-small.webp',
        'pax' => '2 Pax',
        'capacity' => '2 Orang',
        'size' => '6m²',
        'monthly' => '4.000.000',
        'yearly' => '36.000.000',
        'desc' => 'Ruang kantor privat berukuran 6m² yang fully-furnished, ideal untuk startup kecil atau tim cabang dengan kapasitas 2 orang. Dilengkapi dengan furnitur ergonomis premium, sambungan listrik & AC dedicated, serta akses internet super cepat.'
    ],
    2 => [
        'name' => 'Private Office Medium (4 Pax)',
        'img' => 'private-office-medium.webp',
        'pax' => '4 Pax',
        'capacity' => '4 Orang',
        'size' => '9m²',
        'monthly' => '5.500.000',
        'yearly' => '49.500.000',
        'desc' => 'Ruang kerja privat berukuran 9m² yang sempurna untuk tim berkembang yang terdiri dari 4-5 orang. Dilengkapi dengan furnitur lengkap, AC dedicated, dan layanan daily cleaning service untuk kenyamanan kerja tim Anda.'
    ],
    3 => [
        'name' => 'Private Office Corporate (6 Pax)',
        'img' => 'private-office-corporate.webp',
        'pax' => '6 Pax',
        'capacity' => '6 Orang',
        'size' => '14m²',
        'monthly' => '10.000.000',
        'yearly' => '90.000.000',
        'desc' => 'Ruang kantor berukuran 14m² dengan desain mewah berkelas korporat. Sangat cocok untuk tim berskala menengah hingga 6 orang. Dilengkapi layout premium, furniture eksekutif, dan akses smart door lock mandiri.'
    ],
    4 => [
        'name' => 'Private Office Medium (3 Pax)',
        'img' => 'private-office-4.webp',
        'pax' => '3 Pax',
        'capacity' => '3 Orang',
        'size' => '8m²',
        'monthly' => '5.500.000',
        'yearly' => '49.500.000',
        'desc' => 'Ruang kerja privat modern berkapasitas 3 orang dengan sekat kaca eksklusif. Menawarkan pencahayaan alami yang melimpah dan desain interior minimalis untuk produktivitas tim startup Anda.'
    ],
    5 => [
        'name' => 'Private Office Corporate (6 Pax)',
        'img' => 'private-office-5.webp',
        'pax' => '6 Pax',
        'capacity' => '6 Orang',
        'size' => '15m²',
        'monthly' => '10.000.000',
        'yearly' => '90.000.000',
        'desc' => 'Workspace luas berkapasitas hingga 6 orang dengan meja yang tertata rapi. Sangat nyaman untuk kolaborasi tim IT, marketing, atau operasional perusahaan.'
    ],
    6 => [
        'name' => 'Private Office Small (2 Pax)',
        'img' => 'private-office-6.webp',
        'pax' => '2 Pax',
        'capacity' => '2 Orang',
        'size' => '6.5m²',
        'monthly' => '4.000.000',
        'yearly' => '36.000.000',
        'desc' => 'Ruang kerja privat berkapasitas 2 orang dengan pemandangan langsung ke gedung pencakar langit kota. Memberikan kesan prestisius dan profesional bagi bisnis Anda.'
    ],
    7 => [
        'name' => 'Private Office Small (1 Pax)',
        'img' => 'private-office-7.webp',
        'pax' => '1 Pax',
        'capacity' => '1 Orang (Eksekutif)',
        'size' => '5m²',
        'monthly' => '4.000.000',
        'yearly' => '36.000.000',
        'desc' => 'Ruang kerja privat kelas eksekutif yang didesain khusus untuk para profesional perorangan, direktur, atau konsultan. Dilengkapi meja kayu kokoh, kursi kulit premium, dan suasana kedap suara.'
    ],
    8 => [
        'name' => 'Private Office Medium (4 Pax)',
        'img' => 'private-office-8.webp',
        'pax' => '4 Pax',
        'capacity' => '4 Orang',
        'size' => '10m²',
        'monthly' => '5.500.000',
        'yearly' => '49.500.000',
        'desc' => 'Kabin kerja kolaboratif berkapasitas 4 orang dengan meja kayu hangat dan partisi kaca buram untuk privasi kerja yang terjaga namun tetap dinamis.'
    ]
];

$office = $types_data[$type];
?>

<!-- Detail Banner Hero -->
<div class="container" style="margin-top: 120px; margin-bottom: 60px;">
    <!-- Breadcrumb back link -->
    <a href="<?php echo BASE_URL; ?>sewa-kantor-surabaya/" class="back-breadcrumb">
        <i class="bi bi-arrow-left"></i> Kembali ke Halaman Utama
    </a>
    
    <div class="hero-detail-grid">
        <!-- Left details column -->
        <div class="hero-details-col">
            <span class="badge" style="background-color: hsl(var(--clr-primary)); color: white; border-radius: var(--radius-sm); margin-bottom: 12px;">Private Office Room</span>
            <h1 style="font-size: 2.2rem; line-height: 1.25; margin: 0 0 10px 0; font-weight: 800; color: #111111;"><?php echo sanitize($office['name']); ?></h1>
            <p style="font-size: 1.05rem; color: #555555; margin-bottom: 24px;">
                <i class="bi bi-geo-alt-fill" style="color: hsl(var(--clr-primary));"></i> Jawa Timur, Indonesia
            </p>
            
            <!-- Price box -->
            <div style="background-color: hsl(var(--clr-bg-secondary)); padding: 24px; border-radius: var(--radius-md); border-left: 4px solid hsl(var(--clr-primary)); box-shadow: var(--shadow-sm); margin-bottom: 30px;">
                <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800; color: hsl(var(--clr-primary));">Mulai Dari</div>
                <div style="font-size: 2.3rem; font-weight: 800; color: #111111; line-height: 1.1; margin: 6px 0;">
                    Rp <?php echo sanitize($office['monthly']); ?> <span style="font-size: 1.2rem; color: #666666; font-weight: 500;">/ Bln</span>
                </div>
                <p style="font-size: 0.75rem; color: #666666; margin-top: 8px; margin-bottom: 0;">*Minimum kontrak 1 tahun. Sudah termasuk promo bayar 9 bulan untuk sewa 1 tahun.</p>
            </div>
            
            <!-- Call to Actions -->
            <div class="hero-cta-container">
                <a href="#contact" class="btn btn-primary" onclick="window.activateDetailOfferMode('<?php echo sanitize($office['name']); ?>')">Ajukan Penawaran</a>
                <a href="https://api.whatsapp.com/send?phone=6285107620100&text=Halo%20Urban%20Office%2C%20saya%20tertarik%20dengan%20<?php echo urlencode($office['name']); ?>.%20Mohon%20info%20selengkapnya." class="btn btn-outline" target="_blank">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                </a>
            </div>
        </div>
        
        <!-- Right Image Column -->
        <div class="hero-image-col">
            <img src="<?php echo BASE_URL; ?>assets/images/privateoffice/<?php echo $office['img']; ?>" alt="<?php echo $office['name']; ?>" style="width: 100%; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); aspect-ratio: 4/3; object-fit: cover; border: 1px solid hsl(var(--clr-border));">
            <span style="position: absolute; top: 20px; right: 20px; background-color: hsl(var(--clr-primary)); color: #FFFFFF; font-size: 0.85rem; font-weight: 800; padding: 6px 14px; border-radius: var(--radius-full); text-transform: uppercase; box-shadow: var(--shadow-md);">
                <?php echo $office['pax']; ?>
            </span>
        </div>
    </div>
</div>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>

<!-- Specification details and Facilities inclusions -->
<!-- Specification details and Facilities inclusions -->
<section class="section specs-facilities-sec">
    <div class="container">
        <div class="specs-facilities-grid">
            <!-- Left Info Block -->
            <div class="specs-info-col">
                <h3 class="specs-title">Tentang Ruang Kantor Ini</h3>
                <p class="specs-desc"><?php echo sanitize($office['desc']); ?></p>
                
                <h3 class="specs-subtitle">Informasi Gedung & Spesifikasi</h3>
                <table class="specs-table">
                    <tr>
                        <td class="spec-label">Alamat Lengkap</td>
                        <td class="spec-value">Jl. Dr. Ir. H. Soekarno No.470, Kedung Baruk, Rungkut</td>
                    </tr>
                    <tr>
                        <td class="spec-label">Kapasitas Ruangan</td>
                        <td class="spec-value"><?php echo sanitize($office['capacity']); ?></td>
                    </tr>
                    <tr>
                        <td class="spec-label">Ukuran Fisik</td>
                        <td class="spec-value"><?php echo sanitize($office['size']); ?></td>
                    </tr>
                    <tr>
                        <td class="spec-label">Kondisi Ruangan</td>
                        <td class="spec-value">Fully Furnished & Siap Kerja</td>
                    </tr>
                    <tr>
                        <td class="spec-label">Sistem Pendingin (AC)</td>
                        <td class="spec-value">Dedicated Split AC</td>
                    </tr>
                    <tr>
                        <td class="spec-label">Akses Gedung</td>
                        <td class="spec-value">24/7 (Menggunakan Smart Access)</td>
                    </tr>
                </table>
            </div>
            
            <!-- Right Info Block -->
            <div class="facilities-col">
                <div class="facility-card-wrap">
                    <h4 class="facility-card-title">Fasilitas Termasuk</h4>
                    
                    <ul class="facility-card-list">
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Meja, Kursi Ergonomis, & Laci Dokumen</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Bebas Biaya Listrik, Air, & Maintenance AC</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Layanan Resepsionis & Penerimaan Surat/Paket</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Internet Fiber Optic Dedicated Speed</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Free-Flow Kopi, Teh, & Air Mineral Harian</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Daily Cleaning Service (Pembersihan Harian)</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Bebas Menggunakan Pantry & Lounge Bersama</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Serviced Office Section -->
<section class="section" style="background-color: #FFFFFF; border-bottom: 1px solid hsl(var(--clr-border)); padding: 60px 0; overflow: hidden; position: relative;">
    <div class="container" style="position: relative;">
        <h3 class="text-center" style="font-size: 1.8rem; margin-bottom: 40px; font-weight: 800; color: #111111;">Galeri Serviced Office</h3>
        
        <!-- Slider Container Wrapper -->
        <div style="position: relative; width: 100%;">
              <!-- Navigation Button Left -->
            <button id="serviced-gallery-prev" class="slider-arrow arrow-left" aria-label="Previous Slide">
                <i class="bi bi-chevron-left"></i>
            </button>
            
            <!-- Window/Viewport for Slider -->
            <div class="serviced-gallery-window" style="overflow: hidden; cursor: grab; user-select: none; width: 100%;">
                <div id="serviced-gallery-slider" style="display: flex; transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1); will-change: transform;">
                    
                    <!-- Slide 1: Meeting Room -->
                    <div class="serviced-card-slide">
                        <div class="serviced-gallery-item">
                            <div class="image-wrapper">
                                <img src="<?php echo BASE_URL; ?>assets/images/privateoffice/serviced-office-meeting.png" alt="Meeting Room" draggable="false">
                            </div>
                            <h4>Meeting Room</h4>
                        </div>
                    </div>

                    <!-- Slide 2: Serviced Office -->
                    <div class="serviced-card-slide">
                        <div class="serviced-gallery-item">
                            <div class="image-wrapper">
                                <img src="<?php echo BASE_URL; ?>assets/images/privateoffice/serviced-office-desk.png" alt="Serviced Office" draggable="false">
                            </div>
                            <h4>Serviced Office</h4>
                        </div>
                    </div>

                    <!-- Slide 3: Pantry -->
                    <div class="serviced-card-slide">
                        <div class="serviced-gallery-item">
                            <div class="image-wrapper">
                                <img src="<?php echo BASE_URL; ?>assets/images/privateoffice/serviced-office-pantry.png" alt="Pantry" draggable="false">
                            </div>
                            <h4>Pantry</h4>
                        </div>
                    </div>

                    <!-- Slide 4: Resepsionis -->
                    <div class="serviced-card-slide">
                        <div class="serviced-gallery-item">
                            <div class="image-wrapper">
                                <img src="<?php echo BASE_URL; ?>assets/images/privateoffice/serviced-office-receptionist.png" alt="Resepsionis" draggable="false">
                            </div>
                            <h4>Resepsionis</h4>
                        </div>
                    </div>

                    <!-- Slide 5: Outdoor Area -->
                    <div class="serviced-card-slide">
                        <div class="serviced-gallery-item">
                            <div class="image-wrapper">
                                <img src="<?php echo BASE_URL; ?>assets/images/privateoffice/serviced-office-outdoor.png" alt="Outdoor Area" draggable="false">
                            </div>
                            <h4>Outdoor Area</h4>
                        </div>
                    </div>

                </div>
            </div>
            
            <!-- Navigation Button Right -->
            <button id="serviced-gallery-next" class="slider-arrow arrow-right" aria-label="Next Slide">
                <i class="bi bi-chevron-right"></i>
            </button>
            
        </div>
    </div>
</section>

<style>
/* Breadcrumb Link */
.back-breadcrumb {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 700;
    font-size: 0.9rem;
    text-transform: uppercase;
    margin-bottom: 20px;
    color: hsl(var(--clr-primary));
    transition: color 0.2s ease;
}
.back-breadcrumb:hover {
    color: #cc5400;
}

/* Specs & Facilities Section Styling */
.specs-facilities-sec {
    background-color: hsl(var(--clr-bg-secondary));
    border-top: 1px solid hsl(var(--clr-border));
    border-bottom: 1px solid hsl(var(--clr-border));
    padding: 60px 0;
}
.specs-facilities-grid {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 50px;
}
.specs-info-col {
    text-align: left;
}
.specs-title {
    font-size: 1.8rem;
    margin-bottom: 16px;
    font-weight: 800;
    color: #111111;
}
.specs-desc {
    font-size: 1.05rem;
    line-height: 1.75;
    color: #444444;
    margin-bottom: 40px;
}
.specs-subtitle {
    font-size: 1.6rem;
    margin-bottom: 20px;
    font-weight: 800;
    color: #111111;
}
.specs-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
    font-size: 0.95rem;
}
.specs-table tr {
    border-bottom: 1px solid hsl(var(--clr-border));
}
.spec-label {
    padding: 12px 0;
    font-weight: 700;
    color: #111111;
    width: 40%;
}
.spec-value {
    padding: 12px 0;
    color: #444444;
}
.facilities-col {
    text-align: left;
}
.facility-card-wrap {
    background-color: #FFFFFF;
    padding: 35px 30px;
    border-radius: var(--radius-md);
    border: 1px solid hsl(var(--clr-border));
    box-shadow: var(--shadow-sm);
}
.facility-card-title {
    font-size: 1.35rem;
    margin-bottom: 20px;
    font-weight: 800;
    color: #111111;
    text-align: center;
}
.facility-card-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.facility-card-list li {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.facility-card-list li i {
    color: hsl(var(--clr-primary));
    font-size: 1.15rem;
    flex-shrink: 0;
}
.facility-card-list li span {
    font-size: 0.95rem;
    color: #444444;
    line-height: 1.4;
}

/* Hero Detail Banner Grid & CTA Styles */
.hero-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: center;
    gap: 40px;
    margin-top: 10px;
}
.hero-image-col {
    position: relative;
}
.hero-cta-container {
    display: flex;
    gap: 15px;
    flex-wrap: nowrap;
}
.hero-cta-container .btn {
    flex: 1;
    justify-content: center;
    text-align: center;
    white-space: nowrap;
}

@media (max-width: 768px) {
    .back-breadcrumb {
        font-size: 0.75rem !important;
        margin-bottom: 12px !important;
    }
    .hero-detail-grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }
    .hero-image-col {
        order: -1;
    }
    .hero-cta-container {
        gap: 8px;
    }
    .hero-cta-container .btn {
        padding: 8px 10px !important;
        font-size: 0.72rem !important;
    }
    
    /* Mobile Specs & Facilities responsive styles */
    .specs-facilities-grid {
        grid-template-columns: 1fr !important;
        gap: 30px !important;
    }
    .specs-title {
        font-size: 1.4rem !important;
        margin-bottom: 10px !important;
    }
    .specs-desc {
        font-size: 0.85rem !important;
        line-height: 1.5 !important;
        margin-bottom: 24px !important;
    }
    .specs-subtitle {
        font-size: 1.25rem !important;
        margin-bottom: 14px !important;
    }
    .specs-table {
        font-size: 0.82rem !important;
    }
    .spec-label, .spec-value {
        padding: 8px 0 !important;
    }
    .facility-card-wrap {
        padding: 20px 16px !important;
    }
    .facility-card-title {
        font-size: 1.1rem !important;
        margin-bottom: 14px !important;
    }
    .facility-card-list {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 12px 10px !important;
    }
    .facility-card-list li {
        gap: 6px !important;
    }
    .facility-card-list li i {
        font-size: 0.9rem !important;
        margin-top: 2px;
    }
    .facility-card-list li span {
        font-size: 0.72rem !important;
        line-height: 1.3 !important;
    }
}

@media (max-width: 480px) {
    .specs-title {
        font-size: 1.25rem !important;
    }
    .specs-desc {
        font-size: 0.8rem !important;
    }
    .specs-subtitle {
        font-size: 1.15rem !important;
    }
    .specs-table {
        font-size: 0.78rem !important;
    }
    .facility-card-list li span {
        font-size: 0.68rem !important;
    }
}

/* Serviced Gallery Slider & Cards */
.serviced-card-slide {
    padding: 0 8px;
    box-sizing: border-box;
}

.serviced-gallery-item {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.serviced-gallery-item .image-wrapper {
    width: 100%;
    border-radius: var(--radius-md);
    overflow: hidden;
    aspect-ratio: 4/3;
    margin-bottom: 10px;
    border: 1px solid hsl(var(--clr-border));
    box-shadow: var(--shadow-sm);
    transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
}

.serviced-gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.serviced-gallery-item h4 {
    font-size: 0.85rem !important;
    font-weight: 700;
    color: #111111;
    text-align: center;
    margin: 0;
}

.serviced-gallery-item:hover .image-wrapper {
    border-color: hsl(var(--clr-primary));
    box-shadow: var(--shadow-md);
}

.serviced-gallery-item:hover img {
    transform: scale(1.05);
}

.serviced-gallery-window:active {
    cursor: grabbing;
}

/* Hide Navigation Arrows on Desktop, Tablet, and Mobile */
#serviced-gallery-prev,
#serviced-gallery-next {
    display: none !important;
}

/* Responsive Serviced Gallery */
@media (max-width: 991px) {
    .serviced-gallery-item h4 {
        font-size: 0.78rem !important;
    }
}
@media (max-width: 576px) {
    .serviced-gallery-item h4 {
        font-size: 0.72rem !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('serviced-gallery-slider');
    const container = document.querySelector('.serviced-gallery-window');
    const slides = slider.querySelectorAll('.serviced-card-slide');
    const btnPrev = document.getElementById('serviced-gallery-prev');
    const btnNext = document.getElementById('serviced-gallery-next');
    
    let currentIdx = 0;
    let autoPlayInterval = null;
    
    function getVisibleCount() {
        const width = window.innerWidth;
        if (width > 1200) return 4;  // 4 cards on desktop (was 5)
        if (width > 991) return 3;   // 3 cards on small desktop / tablet landscape (was 4)
        if (width > 768) return 2;   // 2 cards on tablet (was 3)
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
        autoPlayInterval = setInterval(nextSlide, 5000); // Auto slide every 5 seconds
    }
    
    function stopAutoPlay() {
        if (autoPlayInterval) {
            clearInterval(autoPlayInterval);
        }
    }
    
    // Add Click listeners for buttons
    btnPrev.addEventListener('click', function() {
        prevSlide();
        startAutoPlay(); // Reset autoplay timer
    });
    
    btnNext.addEventListener('click', function() {
        nextSlide();
        startAutoPlay(); // Reset autoplay timer
    });
    
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

<!-- Advantages / Keuntungan Section -->
<section class="section" style="background-color: hsl(var(--clr-bg-surface)); border-bottom: 1px solid hsl(var(--clr-border)); padding: 60px 0;">
    <div class="container">
        <h3 class="text-center" style="font-size: 1.8rem; margin-bottom: 40px; font-weight: 800; color: #111111;">Keuntungan Sewa Private Office Disini</h3>
        
        <div class="advantages-grid">
            <!-- Advantage 1 -->
            <div class="advantage-card">
                <div class="advantage-icon-box">
                    <i class="bi bi-check-lg" style="-webkit-text-stroke: 1px;"></i>
                </div>
                <div class="advantage-text-content">
                    <h4 class="advantage-title">Full Furnished Siap Pakai</h4>
                    <p class="advantage-desc">Urban Office menyediakan ruang kerja lengkap dengan meja, kursi, dan perabot lainnya. Cukup bawa laptop, Anda siap bekerja dengan nyaman dan profesional.</p>
                </div>
            </div>
            
            <!-- Advantage 2 -->
            <div class="advantage-card">
                <div class="advantage-icon-box">
                    <i class="bi bi-check-lg" style="-webkit-text-stroke: 1px;"></i>
                </div>
                <div class="advantage-text-content">
                    <h4 class="advantage-title">Bebas Biaya Service Charge</h4>
                    <p class="advantage-desc">Kami tidak membebankan service charge biaya operasional (listrik, wifi, air) sudah kami tanggung demi kenyamanan Anda. Ruangan siap pakai</p>
                </div>
            </div>
            
            <!-- Advantage 3 -->
            <div class="advantage-card">
                <div class="advantage-icon-box">
                    <i class="bi bi-check-lg" style="-webkit-text-stroke: 1px;"></i>
                </div>
                <div class="advantage-text-content">
                    <h4 class="advantage-title">Sewa 1 Tahun Cukup Bayar 9 Bulan</h4>
                    <p class="advantage-desc">Cocok untuk bisnis yang ingin rencana jangka panjang tanpa biaya besar di depan. Hemat 3 bulan sewa—lebih untung, lebih nyaman.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
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
    .advantage-title {
        font-size: 0.88rem !important;
        margin-bottom: 6px !important;
    }
    .advantage-desc {
        font-size: 0.72rem !important;
        line-height: 1.4 !important;
    }
}
</style>


<!-- Contact Capture Form -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<script>
window.activateDetailOfferMode = function(packageName) {
    if (typeof activateOfferMode === 'function') {
        // Triggers the form Offer Mode dynamically for Private Office
        activateOfferMode(packageName, 'Private Office', 'MERR');
    }
};
</script>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
