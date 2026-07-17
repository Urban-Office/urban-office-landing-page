<?php
/**
 * Urban Office - Meeting Room Surabaya Landing Page
 */

$page_slug = 'meeting-room-surabaya';
require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<!-- Hero Section -->
<?php
$hero_tag = 'Premium Meeting Room';
$hero_title = 'Sewa Ruang Meeting Mulai 125Rb/Jam';
$hero_desc = 'Sewa ruang meeting harian atau per jam. Dilengkapi layar LED/Proyektor, papan tulis, internet cepat, air mineral gratis, dan penataan ruangan profesional.';
$hero_cta_text = 'Pesan Jam Rapat';
$hero_cta_url = '#pricing';
include dirname(dirname(__FILE__)) . '/inc/components/hero.php';
?>


<!-- Pricing Configurations -->
<div id="pricing">
    <?php
    $pricing_title = 'Paket Sewa Ruang Meeting';
    $packages = [
        [
            'name' => 'Small Meeting Room',
            'price' => '125.000',
            'period' => 'Jam',
            'hide_minimum_info' => true,
            'show_offer_btn' => true,
            'service' => 'Meeting Room',
            'branch' => 'MERR',
            'description' => 'Ideal untuk diskusi tim kecil, wawancara kerja, atau negosiasi klien (kapasitas max 6 orang).',
            'features' => [],
            'cta_text' => 'Booking Sekarang',
            'cta_link' => '#contact'
        ],
        [
            'name' => 'Big Meeting Room',
            'price' => '45.000',
            'period' => 'Pax',
            'hide_minimum_info' => true,
            'show_offer_btn' => true,
            'service' => 'Meeting Room',
            'branch' => 'MERR',
            'popular' => true,
            'description' => 'Sempurna untuk rapat skala besar, presentasi bisnis penting, seminar, atau pelatihan.',
            'features' => [],
            'cta_text' => 'Booking Sekarang',
            'cta_link' => '#contact'
        ]
    ];
    include dirname(dirname(__FILE__)) . '/inc/components/pricing_cards.php';
    ?>
</div>

<!-- Real-Time Booking Banner Section -->
<section class="section" style="padding: 60px 0 30px 0; background-color: #FFFFFF;">
    <div class="container">
        <div class="rt-booking-banner">
            <!-- Left Content Area -->
            <div class="rt-booking-content">
                <h2 style="font-size: 2.5rem; font-weight: 800; color: #FFFFFF; margin-top: 0; margin-bottom: 15px; line-height: 1.25;">
                    Real Time Meeting Room Booking
                </h2>
                <p style="font-size: 1.1rem; color: rgba(255, 255, 255, 0.95); margin-bottom: 30px; line-height: 1.6; font-weight: 500;">
                    Lebih dari 40+ Lokasi di Indonesia. Pilih lokasi terdekat dan sesuai kebutuhan anda
                </p>
                <a href="#contact" class="btn" style="background-color: #FFFFFF; color: #FF6B00; padding: 14px 28px; font-weight: 800; font-size: 0.95rem; border-radius: var(--radius-full); display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: none; text-decoration: none; text-transform: uppercase; letter-spacing: 0.05em; transition: transform 0.2s ease, box-shadow: 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)';">
                    Book Now <i class="bi bi-cart-plus" style="font-size: 1.2rem; -webkit-text-stroke: 0.5px;"></i>
                </a>
            </div>
            
            <!-- Right Image Area -->
            <div class="rt-booking-image-wrap">
                <img src="<?php echo BASE_URL; ?>assets/images/imgcomponent/WhatsApp_Image_2026-06-08_at_09.32.25-removebg-preview.png" alt="Real Time Booking Flow" class="rt-booking-image">
            </div>
        </div>
    </div>
</section>

<style>
.rt-booking-banner {
    background: linear-gradient(135deg, #FF6B00 0%, #FFA800 60%, #FFD700 100%);
    border-radius: 24px;
    padding: 50px 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
    position: relative;
    box-shadow: 0 15px 30px rgba(255, 107, 0, 0.15);
}
.rt-booking-content {
    flex: 1;
    max-width: 550px;
    color: #FFFFFF;
    text-align: left;
    z-index: 2;
}
.rt-booking-image-wrap {
    position: absolute;
    right: 50px;
    bottom: 0;
    height: 100%;
    width: 450px;
    display: flex;
    justify-content: flex-end;
    align-items: flex-end;
    z-index: 1;
    pointer-events: none;
}
.rt-booking-image {
    max-height: 105%;
    width: auto;
    object-fit: contain;
    display: block;
    transform: translateY(31px);
}
@media (max-width: 991px) {
    .rt-booking-banner {
        flex-direction: row;
        padding: 35px 25px;
        text-align: left;
        align-items: center;
        justify-content: space-between;
    }
    .rt-booking-content {
        text-align: left;
        max-width: 55%;
        margin-bottom: 0;
    }
    .rt-booking-content a {
        margin: 0;
        padding: 10px 20px !important;
        font-size: 0.8rem !important;
    }
    .rt-booking-content a i {
        font-size: 0.95rem !important;
    }
    .rt-booking-image-wrap {
        position: absolute;
        right: 20px;
        bottom: 0;
        width: 42%;
        height: 100% !important;
        display: flex;
        justify-content: flex-end;
        align-items: flex-end;
        margin-bottom: 0 !important;
        margin-top: 0 !important;
    }
    .rt-booking-image {
        max-height: 120%;
        width: auto;
        max-width: 100%;
        transform: translateY(26px) !important;
    }
}
@media (max-width: 768px) {
    .rt-booking-banner { 
        padding: 20px 16px !important; 
    }
    .rt-booking-content {
        max-width: 56% !important;
    }
    .rt-booking-content h2 { 
        font-size: 0.95rem !important; 
        margin-bottom: 10px !important;
    }
    .rt-booking-content p { 
        font-size: 0.7rem !important; 
        margin-bottom: 12px !important;
        line-height: 1.4 !important;
    }
    .rt-booking-content a {
        padding: 6px 14px !important;
        font-size: 0.72rem !important;
    }
    .rt-booking-content a i {
        font-size: 0.82rem !important;
    }
    .rt-booking-image-wrap {
        right: 10px !important;
        width: 42% !important;
    }
    .rt-booking-image {
        max-height: 125% !important;
        transform: translateY(22px) !important;
    }
}
@media (max-width: 480px) {
    .rt-booking-banner { 
        padding: 16px 12px !important; 
        border-radius: 16px !important;
    }
    .rt-booking-content {
        max-width: 58% !important;
    }
    .rt-booking-content h2 { 
        font-size: 0.8rem !important; 
        margin-bottom: 6px !important;
    }
    .rt-booking-content p { 
        font-size: 0.62rem !important; 
        margin-bottom: 8px !important;
        line-height: 1.35 !important;
    }
    .rt-booking-content a {
        padding: 4px 10px !important;
        font-size: 0.65rem !important;
    }
    .rt-booking-content a i {
        font-size: 0.75rem !important;
    }
    .rt-booking-image-wrap {
        right: 6px !important;
        width: 40% !important;
    }
    .rt-booking-image {
        max-height: 130% !important;
        transform: translateY(18px) !important;
    }
}
</style>


<!-- Features Component -->
<?php
$features_title = 'Fasilitas Pendukung Ruang Meeting';
$features_list = [
    [
        'title' => 'Layar LED & HDMI',
        'desc' => 'Tingkatkan kualitas presentasi Anda dengan Smart TV resolusi tinggi dan koneksi kabel HDMI yang stabil.',
        'icon' => '📺'
    ],
    [
        'title' => 'WiFi Kecepatan Tinggi',
        'desc' => 'Menghubungkan peserta meeting lokal dengan tim jarak jauh secara lancar melalui panggilan Zoom tanpa buffer.',
        'icon' => '📶'
    ],
    [
        'title' => 'Alat Tulis Rapat',
        'desc' => 'Setiap ruang meeting sudah dilengkapi dengan whiteboard, spidol non-permanen, penghapus, dan notes kecil.',
        'icon' => '📝'
    ],
    [
        'title' => 'Penyambutan Tamu',
        'desc' => 'Tamu bisnis Anda akan disambut ramah oleh tim resepsionis kami dan diantarkan langsung ke ruang rapat.',
        'icon' => '🛎️'
    ],
    [
        'title' => 'Free Flow Minuman',
        'desc' => 'Dapatkan air mineral botol gratis untuk seluruh peserta, ditambah akses kopi/teh sepuasnya di pantry.',
        'icon' => '☕'
    ],
    [
        'title' => 'Conference Speakerphone',
        'desc' => 'Tersedia speaker konferensi bluetooth khusus dengan mikrofon 360 derajat untuk panggilan suara jernih.',
        'icon' => '🎙️'
    ],
    [
        'title' => 'Sound System & Mic',
        'desc' => 'Dilengkapi sound system berkualitas dan mikrofon nirkabel untuk memastikan suara terdengar jelas ke seluruh ruangan.',
        'icon' => '<i class="bi bi-mic"></i>'
    ],
    [
        'title' => 'Tata Ruang Fleksibel',
        'desc' => 'Layout meja dan kursi dapat disesuaikan dengan kebutuhan acara Anda (U-Shape, Classroom, Theater, dll).',
        'icon' => '<i class="bi bi-sliders"></i>'
    ]
];
include dirname(dirname(__FILE__)) . '/inc/components/features.php';
?>

<!-- Meeting Room Gallery Slider Section -->
<section class="section" style="background-color: hsl(var(--clr-bg-secondary)); border-top: 1px solid hsl(var(--clr-border)); overflow: hidden; padding: 60px 0;">
    <div class="container">
        <!-- Header row with title -->
        <div style="margin-bottom: 30px;">
            <h2 class="section-title" style="margin: 0;">Galeri Foto Ruang Meeting</h2>
            <p style="margin: 5px 0 0 0; max-width: 600px; color: hsl(var(--clr-text-muted)); font-size: 0.95rem;">Jelajahi berbagai pilihan tata ruang meeting profesional kami.</p>
        </div>
        
        <!-- Slider Window (draggable/swipeable) -->
        <div class="office-gallery-window" style="overflow: hidden; margin: 0 -10px; padding: 10px 0; cursor: grab; user-select: none;">
            <div class="office-gallery-wrapper" id="office-gallery-slider" style="display: flex; transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1); will-change: transform;">
                <?php
                $gallery_items = [
                    ['img' => 'Small Meeting.jpeg', 'title' => 'Small Meeting', 'desc' => 'Kapasitas: s/d 6 Orang', 'pax' => '6 Pax', 'pkg' => 'Small Meeting Room', 'type_id' => 1],
                    ['img' => 'Big Meeting.jpeg', 'title' => 'Big Meeting', 'desc' => 'Kapasitas: s/d 15 Orang', 'pax' => '15 Pax', 'pkg' => 'Big Meeting Room', 'type_id' => 2],
                    ['img' => 'Small Meeting (2).jpeg', 'title' => 'Small Meeting', 'desc' => 'Kapasitas: s/d 6 Orang', 'pax' => '6 Pax', 'pkg' => 'Small Meeting Room', 'type_id' => 3],
                    ['img' => 'Big Meeting (2).jpeg', 'title' => 'Big Meeting', 'desc' => 'Kapasitas: s/d 15 Orang', 'pax' => '15 Pax', 'pkg' => 'Big Meeting Room', 'type_id' => 4],
                    ['img' => 'Big Meeting (3).jpeg', 'title' => 'Big Meeting', 'desc' => 'Kapasitas: s/d 15 Orang', 'pax' => '15 Pax', 'pkg' => 'Big Meeting Room', 'type_id' => 5],
                    ['img' => 'Big Meeting (4).jpeg', 'title' => 'Big Meeting', 'desc' => 'Kapasitas: s/d 15 Orang', 'pax' => '15 Pax', 'pkg' => 'Big Meeting Room', 'type_id' => 6],
                ];
                
                foreach ($gallery_items as $item):
                ?>
                <div class="gallery-card-slide">
                    <div class="gallery-item-card">
                        <!-- Image Container with Pax Badge -->
                        <div class="gallery-img-container">
                            <img src="<?php echo BASE_URL; ?>assets/images/meetingroom/<?php echo $item['img']; ?>" alt="<?php echo $item['title']; ?>" draggable="false">
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
                            <a href="<?php echo BASE_URL; ?>meeting-room-surabaya/detail.php?type=<?php echo $item['type_id']; ?>" class="btn btn-primary gallery-cta-btn" draggable="false">
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
    height: 180px;
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
        height: 160px;
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
        if (width > 1200) return 4;  // 4 cards on desktop (was 5)
        if (width > 991) return 3;   // 3 cards on small desktop (was 4)
        if (width > 768) return 2;   // 2 cards on tablet (was 3)
        return 1.3;                  // 1.3 cards on mobile to peek next slide (was 2)
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
        'question' => 'Apakah sewa Meeting Room sudah termasuk fasilitas proyektor dan whiteboard?',
        'answer' => 'Ya, seluruh tipe Meeting Room kami sudah dilengkapi dengan proyektor/layar TV LED modern serta papan tulis (whiteboard) beserta alat tulis rapat.'
    ],
    [
        'question' => 'Apakah ada fasilitas WiFi? Berapa kecepatannya?',
        'answer' => 'Ya, seluruh peserta rapat akan mendapatkan akses WiFi berkecepatan tinggi up to 100 Mbps secara gratis.'
    ],
    [
        'question' => 'Apakah tersedia café atau tempat makan di dalam gedung?',
        'answer' => 'Ya, Anda dan tamu rapat dapat mengunjungi café atau tempat makan yang tersedia di Lantai 1 gedung.'
    ]
];
include dirname(dirname(__FILE__)) . '/inc/components/faq.php';
?>

<!-- Testimonials Section -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/branches.php'; ?>

<!-- Contact Form -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
