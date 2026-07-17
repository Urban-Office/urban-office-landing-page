<?php
/**
 * Urban Office - Event Space Landing Page
 */

$page_slug = 'event-space-55k-perjam-urbanoffice';
require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<!-- Hero Section -->
<?php
$hero_tag = 'Premium Event Space';
$hero_title = 'Sewa Event Space Mulai 45Rb/Pax';
$hero_desc = 'Miliki ruang seminar, workshop, launching produk, atau rapat pemegang saham berkapasitas 30-80 orang dengan fasilitas lengkap. Berada di lokasi strategis dan mudah diakses.';
$hero_cta_text = 'Dapatkan Quote Harga';
$hero_cta_url = '#pricing';
include dirname(dirname(__FILE__)) . '/inc/components/hero.php';
?>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>


<!-- Pricing Configuration -->
<div id="pricing">
    <?php
    $pricing_title = 'Pilihan Ruang Event Space';
    $packages = [
        [
            'name' => 'Event Space Half-Day',
            'price' => '45.000',
            'period' => 'pax/4 jam',
            'description' => 'Sangat cocok untuk seminar, workshop, atau presentasi bisnis dengan durasi setengah hari (kapasitas 30 - 80 orang).',
            'features' => [],
            'hide_minimum_info' => true,
            'cta_text' => 'Pesan Sekarang',
            'cta_link' => '#contact'
        ],
        [
            'name' => 'Event Space Full-Day',
            'price' => '90.000',
            'period' => 'pax/8 jam',
            'popular' => true,
            'description' => 'Paket terbaik untuk pelatihan penuh seharian, press conference, atau gathering perusahaan (kapasitas 30 - 80 orang).',
            'features' => [],
            'hide_minimum_info' => true,
            'cta_text' => 'Pesan Sekarang',
            'cta_link' => '#contact'
        ]
    ];
    include dirname(dirname(__FILE__)) . '/inc/components/pricing_cards.php';
    ?>
</div>

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
<section class="section" style="background-color: hsl(var(--clr-bg-secondary)); border-top: 1px solid hsl(var(--clr-border)); overflow: hidden; padding: 60px 0;">
    <div class="container">
        <!-- Header row with title -->
        <div style="margin-bottom: 30px;">
            <h2 class="section-title" style="margin: 0;">Galeri Foto Event Space</h2>
            <p style="margin: 5px 0 0 0; max-width: 600px; color: hsl(var(--clr-text-muted)); font-size: 0.95rem;">Jelajahi berbagai pilihan tata letak dan dokumentasi area event space kami.</p>
        </div>
        
        <!-- Slider Window (draggable/swipeable) -->
        <div class="office-gallery-window" style="overflow: hidden; margin: 0 -10px; padding: 10px 0; cursor: grab; user-select: none;">
            <div class="office-gallery-wrapper" id="office-gallery-slider" style="display: flex; transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1); will-change: transform;">
                <?php
                $gallery_items = [
                    ['img' => 'Foto Event Space (2).jpeg', 'title' => 'Event Space Classroom', 'desc' => 'Tata letak meja-kursi untuk kelas/workshop.', 'pax' => '30-80 Pax'],
                    ['img' => 'Foto Event Space (4).jpeg', 'title' => 'Grand Event Space', 'desc' => 'Area luas dengan kelengkapan panggung & audio visual.', 'pax' => '30-80 Pax'],
                    ['img' => 'Foto Event Space Jakarta Selatan Fatmawati.jpeg', 'title' => 'Event Space Fatmawati', 'desc' => 'Event space modern kelas premium di lokasi eksklusif.', 'pax' => '30-80 Pax'],
                    ['img' => 'WhatsApp Image 2026-06-08 at 11.44.08.jpeg', 'title' => 'Event Space Boardroom', 'desc' => 'Layout U-shape/boardroom untuk rapat besar.', 'pax' => '30-80 Pax'],
                    ['img' => 'WhatsApp Image 2026-06-08 at 11.44.58.jpeg', 'title' => 'Theater Event Layout', 'desc' => 'Tata ruang Theater untuk konferensi & press release.', 'pax' => '30-80 Pax'],
                ];
                
                foreach ($gallery_items as $item):
                ?>
                <div class="gallery-card-slide">
                    <div class="gallery-item-card">
                        <!-- Image Container with Pax Badge -->
                        <div class="gallery-img-container">
                            <img src="<?php echo BASE_URL; ?>assets/images/eventspace/<?php echo $item['img']; ?>" alt="<?php echo $item['title']; ?>" draggable="false">
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
                            
                            <!-- Action button connecting to contact -->
                            <a href="#contact" class="btn btn-primary gallery-cta-btn" draggable="false">
                                Pesan Sekarang <i class="bi bi-arrow-right"></i>
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
        if (width > 1200) return 5;  // 5 cards on desktop
        if (width > 991) return 4;   // 4 cards on small desktop / tablet landscape
        if (width > 768) return 3;   // 3 cards on tablet
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
<!-- Advantages / Keuntungan Section -->
<section class="section" style="background-color: hsl(var(--clr-bg-surface)); border-bottom: 1px solid hsl(var(--clr-border)); padding: 60px 0;">
    <div class="container" style="position: relative;">
        <h3 class="text-center" style="font-size: 1.8rem; margin-bottom: 40px; font-weight: 800; color: #111111;">Keuntungan Sewa Event Space Disini</h3>
        
        <!-- Slider Window (draggable/swipeable) -->
        <div class="advantages-gallery-window" style="overflow: hidden; margin: 0 -12px; padding: 10px 0; cursor: grab; user-select: none; width: 100%;">
            <div id="advantages-gallery-slider" style="display: flex; transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1); will-change: transform;">
                
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('advantages-gallery-slider');
    const container = document.querySelector('.advantages-gallery-window');
    const slides = slider.querySelectorAll('.advantages-card-slide');
    
    if (!slider || !container || slides.length === 0) return;

    let currentIdx = 0;
    
    function getVisibleCount() {
        const width = window.innerWidth;
        if (width > 991) return 3;   // 3 cards visible on desktop (matching Coworking size!)
        if (width > 768) return 2;   // 2 cards on tablet
        return 1.4;                  // 1.4 cards on mobile to reduce width slightly (was 1.3)
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
            idx = maxIndex; 
        } else if (idx < 0) {
            idx = 0; 
        }
        
        currentIdx = idx;
        const slideWidth = getSlideWidth();
        slider.style.transition = 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1)';
        slider.style.transform = `translateX(-${currentIdx * slideWidth}px)`;
    }
    
    // Drag and swipe logic
    let isDragging = false;
    let startX = 0;
    let currentX = 0;
    let dragThreshold = 50;
    
    function handleDragStart(e) {
        if (slides.length <= getVisibleCount()) return;
        isDragging = true;
        startX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
        slider.style.transition = 'none';
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
        slider.style.transition = 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1)';
        
        const diffX = currentX - startX;
        if (Math.abs(diffX) > dragThreshold) {
            if (diffX < 0) {
                showSlide(currentIdx + 1);
            } else {
                showSlide(currentIdx - 1);
            }
        } else {
            showSlide(currentIdx);
        }
    }
    
    container.addEventListener('mousedown', handleDragStart);
    window.addEventListener('mousemove', handleDragMove);
    window.addEventListener('mouseup', handleDragEnd);
    
    container.addEventListener('touchstart', handleDragStart, { passive: true });
    container.addEventListener('touchmove', handleDragMove, { passive: true });
    container.addEventListener('touchend', handleDragEnd);
    
    window.addEventListener('resize', () => {
        updateSlideStyles();
        showSlide(currentIdx);
    });
    
    updateSlideStyles();
    showSlide(currentIdx);
});
</script>

<!-- FAQs Section -->
<?php
$faqs = [
    [
        'question' => 'Apakah tersedia koneksi WiFi di area Event Space? Kecepatannya berapa?',
        'answer' => 'Ya, kami menyediakan fasilitas WiFi berkecepatan tinggi up to 100 Mbps yang mampu menampung kapasitas akses banyak pengguna sekaligus.'
    ],
    [
        'question' => 'Apakah tersedia café atau tempat makan di dalam gedung?',
        'answer' => 'Ya, tersedia café dan tempat makan di Lantai 1 gedung yang dapat digunakan oleh panitia maupun peserta acara.'
    ],
    [
        'question' => 'Apakah tersedia area parkir untuk peserta event?',
        'answer' => 'Ya, kami menyediakan tempat parkir yang memadai di lokasi gedung dan fasilitas parkir ini gratis (free) bagi semua tamu.'
    ]
];
include dirname(dirname(__FILE__)) . '/inc/components/faq.php';
?>



<!-- Testimonials Section -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/branches.php'; ?>

<!-- Contact Form -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
