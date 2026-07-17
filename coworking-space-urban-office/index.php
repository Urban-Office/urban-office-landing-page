<?php
/**
 * Urban Office - Coworking Space Landing Page
 */

$page_slug = 'coworking-space-urban-office';
require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<!-- Hero Section -->
<?php
$hero_tag = 'Premium Coworking Space';
$hero_title = 'Sewa Coworking Space Mulai 10Rb/Jam';
$hero_desc = 'Temukan meja kerja per jam, harian, atau bulanan yang fleksibel. Sangat cocok untuk freelancer, remote worker, mahasiswa, dan startup founder. Nikmati WiFi cepat dan free flow coffee & tea sepuasnya.';
$hero_cta_text = 'Lihat Paket Desk';
$hero_cta_url = '#pricing';
include dirname(dirname(__FILE__)) . '/inc/components/hero.php';
?>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>


<!-- Pricing Card Configuration -->
<div id="pricing">
    <?php
    $pricing_title = 'Paket Coworking Space';
    $packages = [
        [
            'name' => 'Coworking Hours',
            'price' => '10.000',
            'period' => 'pax/jam',
            'description' => 'Akses kerja fleksibel per jam di area komunal, sangat cocok untuk produktivitas singkat.',
            'features' => [],
            'hide_minimum_info' => true,
            'cta_text' => 'Sewa Per Jam',
            'cta_link' => '#contact'
        ],
        [
            'name' => 'Coworking Daily',
            'price' => '45.000',
            'period' => 'pax/hari',
            'popular' => true,
            'description' => 'Akses hot-desking harian di area komunal dengan fasilitas lengkap selama jam operasional.',
            'features' => [],
            'hide_minimum_info' => true,
            'cta_text' => 'Beli Daily Pass',
            'cta_link' => '#contact'
        ],
        [
            'name' => 'Coworking Monthly',
            'price' => '750.000',
            'period' => 'pax/bulan',
            'description' => 'Akses kerja bulanan tanpa batas, pilihan ideal untuk freelancer dan startup professional.',
            'features' => [],
            'hide_minimum_info' => true,
            'cta_text' => 'Daftar Bulanan',
            'cta_link' => '#contact'
        ]
    ];
    include dirname(dirname(__FILE__)) . '/inc/components/pricing_cards.php';
    ?>
</div>

<!-- Features Component -->
<?php
$features_title = 'Fasilitas Coworking Space Kami';
$features_list = [
    [
        'title' => 'Meja & Kursi Ergonomis',
        'desc' => 'Bekerja berjam-jam tanpa lelah berkat kursi ergonomis premium dan meja kerja yang luas.',
        'icon' => '🪑'
    ],
    [
        'title' => 'WiFi berkecepatan Tinggi',
        'desc' => 'Didukung koneksi internet serat optik berkecepatan tinggi tanpa kuota untuk produktivitas Anda.',
        'icon' => '📶'
    ],
    [
        'title' => 'Free Flow Kopi & Teh',
        'desc' => 'Nikmati kopi, teh, dan air mineral sepuasnya secara gratis di area pantry yang disediakan.',
        'icon' => '☕'
    ],
    [
        'title' => 'Power Socket Berlimpah',
        'desc' => 'Setiap sudut meja dilengkapi dengan colokan listrik memadai untuk memastikan daya gadget Anda selalu terisi.',
        'icon' => '🔌'
    ]
];
include dirname(dirname(__FILE__)) . '/inc/components/features.php';
?>

<!-- Gallery Coworking Space Section -->
<section class="section" style="background-color: hsl(var(--clr-bg-secondary)); border-top: 1px solid hsl(var(--clr-border)); overflow: hidden; padding: 60px 0;">
    <div class="container">
        <!-- Header row with title -->
        <div style="margin-bottom: 30px;">
            <h2 class="section-title" style="margin: 0;">Galeri Foto Coworking Space</h2>
            <p style="margin: 5px 0 0 0; max-width: 600px; color: hsl(var(--clr-text-muted)); font-size: 0.95rem;">Jelajahi area kerja bersama (Shared Desk) yang dirancang untuk produktivitas maksimal, kenyamanan, dan kolaborasi.</p>
        </div>
        
        <!-- Slider Window (draggable/swipeable) -->
        <div class="office-gallery-window" style="overflow: hidden; margin: 0 -10px; padding: 10px 0; cursor: grab; user-select: none;">
            <div class="office-gallery-wrapper" id="office-gallery-slider" style="display: flex; transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1); will-change: transform;">
                
                <!-- Gallery Item 1 -->
                <div class="gallery-card-slide">
                    <div class="gallery-item-card">
                        <!-- Image Container with Badge -->
                        <div class="gallery-img-container">
                            <img src="<?php echo BASE_URL; ?>assets/images/coworkingspace/Foto Coworking Space.webp" alt="Coworking Shared Area" draggable="false">
                            <span class="gallery-pax-badge">Hot Desk</span>
                        </div>
                        <!-- Body Content -->
                        <div class="gallery-body-content">
                            <div class="gallery-text-wrap">
                                <h4 class="gallery-card-title">Coworking Shared Area</h4>
                                <p class="gallery-card-desc">Area komunal terbuka untuk kerja per jam yang nyaman dan dinamis.</p>
                            </div>
                            <a href="<?php echo BASE_URL; ?>coworking-space-urban-office/detail.php?type=1" class="btn btn-primary gallery-cta-btn" draggable="false">
                                Detail <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Gallery Item 2 -->
                <div class="gallery-card-slide">
                    <div class="gallery-item-card">
                        <!-- Image Container with Badge -->
                        <div class="gallery-img-container">
                            <img src="<?php echo BASE_URL; ?>assets/images/coworkingspace/Foto Coworking Space Klampis(2).webp" alt="Coworking Space Klampis" draggable="false">
                            <span class="gallery-pax-badge">Klampis Area</span>
                        </div>
                        <!-- Body Content -->
                        <div class="gallery-body-content">
                            <div class="gallery-text-wrap">
                                <h4 class="gallery-card-title">Coworking Space Klampis</h4>
                                <p class="gallery-card-desc">Workspace harian komunal dengan pencahayaan alami dan tenang.</p>
                            </div>
                            <a href="<?php echo BASE_URL; ?>coworking-space-urban-office/detail.php?type=2" class="btn btn-primary gallery-cta-btn" draggable="false">
                                Detail <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Gallery Item 3 -->
                <div class="gallery-card-slide">
                    <div class="gallery-item-card">
                        <!-- Image Container with Badge -->
                        <div class="gallery-img-container">
                            <img src="<?php echo BASE_URL; ?>assets/images/coworkingspace/Foto Coworking Space Jakarta Selatan Fatmawati.webp" alt="Coworking Space Fatmawati" draggable="false">
                            <span class="gallery-pax-badge">Fatmawati Area</span>
                        </div>
                        <!-- Body Content -->
                        <div class="gallery-body-content">
                            <div class="gallery-text-wrap">
                                <h4 class="gallery-card-title">Coworking Space Fatmawati</h4>
                                <p class="gallery-card-desc">Workspace bulanan fleksibel di lokasi prestisius dan tenang.</p>
                            </div>
                            <a href="<?php echo BASE_URL; ?>coworking-space-urban-office/detail.php?type=3" class="btn btn-primary gallery-cta-btn" draggable="false">
                                Detail <i class="bi bi-arrow-right"></i>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('office-gallery-slider');
    const container = document.querySelector('.office-gallery-window');
    const slides = slider.querySelectorAll('.gallery-card-slide');
    
    let currentIdx = 0;
    let autoPlayInterval = null;
    
    function getVisibleCount() {
        const width = window.innerWidth;
        if (width > 1200) return 3;  // Since we have only 3 cards, display all 3
        if (width > 991) return 3;   // Display 3
        if (width > 768) return 3;   // Display 3
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
        if (slides.length > getVisibleCount()) {
            autoPlayInterval = setInterval(nextSlide, 4500); // Auto slide every 4.5 seconds
        }
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
        if (slides.length <= getVisibleCount()) return;
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
        'question' => 'Apa saja fasilitas yang tersedia di coworking space?',
        'answer' => 'Fasilitas utama yang disediakan meliputi akses area kerja nyaman, free flow air mineral, koneksi WiFi berkecepatan tinggi, serta colokan listrik (electrical socket) di setiap meja.'
    ],
    [
        'question' => 'Apakah tersedia fasilitas WiFi? Berapa kecepatannya?',
        'answer' => 'Ya, kami menyediakan koneksi WiFi super cepat up to 100 Mbps untuk mendukung semua aktivitas WFA Anda.'
    ],
    [
        'question' => 'Apakah tersedia café atau tempat makan di dalam gedung?',
        'answer' => 'Ya, tersedia café dan tempat makan di Lantai 1 gedung yang menyatu dengan akses coworking space.'
    ],
    [
        'question' => 'Apakah bisa melakukan trial atau coba dulu sebelum berlangganan bulanan?',
        'answer' => 'Tentu saja bisa. Anda bisa mencoba menggunakan area kerja coworking space kami dengan pilihan Daily Pass sebelum memutuskan untuk berlangganan bulanan.'
    ]
];
include dirname(dirname(__FILE__)) . '/inc/components/faq.php';
?>



<!-- Testimonials Section -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/branches.php'; ?>

<!-- Contact Form -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
