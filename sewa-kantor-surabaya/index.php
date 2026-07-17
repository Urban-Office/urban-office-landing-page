<?php
/**
 * Urban Office - Private Office (Sewa Kantor Surabaya) Landing Page
 */

$page_slug = 'sewa-kantor-surabaya';
require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<!-- Hero Section -->
<?php
$hero_tag = 'Serviced Office';
$hero_title = 'Sewa Ruang Kantor Privat (Fully Furnished)';
$hero_desc = 'Kantor privat siap pakai (ready-to-work) dengan desain modern. Lengkap dengan meja kursi premium, AC, jaringan internet serat optik, dan gratis biaya utilitas (listrik/air).';
$hero_cta_text = 'Dapatkan Price List';
$hero_cta_url = '#pricing';
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
                'Ukuran Ruangan 9m² - 14m²'
            ],
            'cta_text' => 'Pesan Ruangan',
            'cta_link' => '#contact'
        ],
        [
            'name' => 'Private Office Corporate',
            'price_monthly' => '10.000.000',
            'price_yearly' => '90.000.000',
            'price_yearly_monthly' => '7.500.000',
            'branch' => 'MERR',
            'period' => 'Bulan',
            'description' => 'Sangat ideal untuk tim korporat berskala menengah dengan kapasitas hingga 10 orang.',
            'features' => [
                'Ukuran Ruangan 16m²'
            ],
            'cta_text' => 'Pesan Ruangan',
            'cta_link' => '#contact'
        ]
    ];
    include dirname(dirname(__FILE__)) . '/inc/components/pricing_cards.php';
    ?>
</div>

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
                    ['img' => 'private-office-small.webp', 'title' => 'Private Office Small', 'desc' => 'Kapasitas: 2 Orang', 'pax' => '2 Pax', 'type_id' => 1],
                    ['img' => 'private-office-medium.webp', 'title' => 'Private Office Medium', 'desc' => 'Kapasitas: 4 Orang', 'pax' => '4 Pax', 'type_id' => 2],
                    ['img' => 'private-office-corporate.webp', 'title' => 'Private Office Corporate', 'desc' => 'Kapasitas: 6 Orang', 'pax' => '6 Pax', 'type_id' => 3],
                    ['img' => 'private-office-4.webp', 'title' => 'Private Office Medium', 'desc' => 'Kapasitas: 3 Orang', 'pax' => '3 Pax', 'type_id' => 4],
                    ['img' => 'private-office-5.webp', 'title' => 'Private Office Corporate', 'desc' => 'Kapasitas: 6 Orang', 'pax' => '6 Pax', 'type_id' => 5],
                    ['img' => 'private-office-6.webp', 'title' => 'Private Office Small', 'desc' => 'Kapasitas: 2 Orang', 'pax' => '2 Pax', 'type_id' => 6],
                    ['img' => 'private-office-7.webp', 'title' => 'Private Office Small', 'desc' => 'Kapasitas: 1 Orang (Eksekutif)', 'pax' => '1 Pax', 'type_id' => 7],
                    ['img' => 'private-office-8.webp', 'title' => 'Private Office Medium', 'desc' => 'Kapasitas: 4 Orang', 'pax' => '4 Pax', 'type_id' => 8],
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
