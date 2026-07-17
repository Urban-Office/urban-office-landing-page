<?php
/**
 * Shared Hero Component Template
 * Parameters:
 *  - $hero_tag: string (optional)
 *  - $hero_title: string (required)
 *  - $hero_desc: string (required)
 *  - $hero_cta_text: string (optional)
 *  - $hero_cta_url: string (optional)
 */

$hero_images = [
    'virtual-office-surabaya' => 'assets/images/branch/urban new (2).png',
    'sewa-kantor-surabaya' => 'assets/images/privateoffice/offie 301 people.png',
    'meeting-room-surabaya' => 'assets/images/meetingroom/Meeting 18 pax.png',
    'coworking-space-urban-office' => 'assets/images/coworkingspace/Foto Coworking Space Jakarta Selatan Fatmawati.jpeg',
    'event-space-55k-perjam-urbanoffice' => 'assets/images/eventspace/Grand Event Space.png',
    'sharing-room-office' => 'assets/images/sharingroom/Sharing Room Office.png'
];

$hero_card_content = [
    'virtual-office-surabaya' => [
        'title' => 'Alamat Bisnis Prestisius',
        'desc' => 'Legalitas aman zonasi perkantoran komersial, lengkap dengan layanan manajemen surat menyurat.'
    ],
    'sewa-kantor-surabaya' => [
        'title' => 'Kantor Privat Eksklusif',
        'desc' => 'Ruang kerja modern berperabot lengkap, ber-AC, dan siap pakai untuk mendukung produktivitas tim Anda.'
    ],
    'meeting-room-surabaya' => [
        'title' => 'Ruang Rapat Profesional',
        'desc' => 'Fasilitas multimedia lengkap, proyektor nirkabel, internet cepat, dan free-flow refreshments.'
    ],
    'coworking-space-urban-office' => [
        'title' => 'Konektivitas Kolaboratif',
        'desc' => 'Meja kerja komunal yang dinamis dan nyaman untuk tingkatkan fokus dan memperluas jaringan bisnis.'
    ],
    'event-space-55k-perjam-urbanoffice' => [
        'title' => 'Ruang Acara Fleksibel',
        'desc' => 'Kapasitas luas dan representatif untuk seminar, pelatihan, presentasi bisnis, atau workshop.'
    ],
    'sharing-room-office' => [
        'title' => 'Solusi Hemat Kolaborasi',
        'desc' => 'Meja kerja personal terdedikasi dalam ruangan bersama dengan harga sewa yang sangat bersahabat.'
    ]
];

$current_slug = $page_slug ?? '';

// Callers may pre-set $hero_img / $card_title / $card_desc (e.g. per-branch pages)
// to override the static per-slug maps above.
if (empty($hero_img)) {
    $hero_img = $hero_images[$current_slug] ?? 'assets/images/og-default.png';
}
// $hero_img may already be a full URL (e.g. BASE_URL-prefixed branch data) — only
// prepend BASE_URL when it's a relative asset path like the static map entries above.
$hero_img_src = (strpos($hero_img, 'http://') === 0 || strpos($hero_img, 'https://') === 0) ? $hero_img : BASE_URL . $hero_img;

$default_card = [
    'title' => 'Lokasi Strategis CBD Utama',
    'desc' => 'Infrastruktur bisnis terlengkap, kenyamanan ruang kerja modern, dan aksesibilitas mudah.'
];

$card_title = $card_title ?? ($hero_card_content[$current_slug]['title'] ?? $default_card['title']);
$card_desc = $card_desc ?? ($hero_card_content[$current_slug]['desc'] ?? $default_card['desc']);
?>
<section class="hero-sec">
    <div class="container hero-container-grid">
        <div class="hero-content">
            <?php if (!empty($hero_tag)): ?>
                <span class="badge"><?php echo sanitize($hero_tag); ?></span>
            <?php endif; ?>
            <h1 style="margin-bottom: 20px; line-height: 1.25;"><?php echo sanitize($hero_title); ?></h1>
            
            <!-- Mobile Hero Image Representation -->
            <div class="hero-img-wrap-mobile">
                <img src="<?php echo $hero_img_src; ?>" alt="Urban Office Workspace">
                <div class="hero-floating-card">
                    <p class="floating-card-title"><?php echo sanitize($card_title); ?></p>
                    <p class="floating-card-desc"><?php echo sanitize($card_desc); ?></p>
                </div>
            </div>

            <p style="font-size: 16px; color: #444444; margin-bottom: 30px;"><?php echo $hero_desc; ?></p>
            
            <?php if (!empty($hero_cta_text) && !empty($hero_cta_url)): ?>
                <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                    <a href="<?php echo sanitize($hero_cta_url); ?>" class="btn btn-primary"><?php echo sanitize($hero_cta_text); ?></a>
                    <?php if (!empty($hero_cta2_text) && !empty($hero_cta2_url)): ?>
                        <a href="<?php echo sanitize($hero_cta2_url); ?>" class="<?php echo !empty($hero_cta2_class) ? sanitize($hero_cta2_class) : 'btn btn-outline'; ?>" target="_blank">
                            <?php if (!empty($hero_cta2_icon)): ?><i class="<?php echo sanitize($hero_cta2_icon); ?>"></i> <?php endif; ?><?php echo sanitize($hero_cta2_text); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Desktop Hero Image Representation -->
        <div class="hero-img-wrap-desktop">
            <img src="<?php echo $hero_img_src; ?>" alt="Urban Office Workspace">
            <div class="hero-floating-card">
                <p class="floating-card-title"><?php echo sanitize($card_title); ?></p>
                <p class="floating-card-desc"><?php echo sanitize($card_desc); ?></p>
            </div>
        </div>
    </div>
</section>

<style>
/* Grid Container */
.hero-container-grid {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 50px;
    align-items: center;
}

/* Hero Content Styles */
.hero-content {
    text-align: left;
}

/* Hide mobile image wrap by default (desktop view) */
.hero-img-wrap-mobile {
    display: none;
}

/* Desktop image wrap styles */
.hero-img-wrap-desktop {
    position: relative;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
    border: 1px solid #EAEAEA;
    width: 100%;
}

.hero-img-wrap-desktop img {
    width: 100%;
    height: auto;
    object-fit: cover;
    aspect-ratio: 16/10;
    display: block;
}

/* Floating Card design following the exact style from bundle pages */
.hero-floating-card {
    position: absolute;
    bottom: 15px;
    right: 15px;
    left: auto;
    width: 70%;
    max-width: 240px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.4);
    text-align: left;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    z-index: 2;
}

.hero-floating-card .floating-card-title {
    margin: 0 0 3px 0 !important;
    font-weight: 800;
    color: #FF6B00;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.hero-floating-card .floating-card-desc {
    margin: 0 !important;
    font-size: 9px;
    color: #444444;
    line-height: 1.35;
}

/* Tablet Responsive (max-width: 991px) */
@media (max-width: 991px) {
    .hero-container-grid {
        grid-template-columns: 1fr !important;
        gap: 30px !important;
    }
    
    .hero-img-wrap-desktop {
        display: none !important;
    }
    
    .hero-img-wrap-mobile {
        display: block !important;
        position: relative;
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        border: 1px solid #EAEAEA;
        max-width: 420px !important;
        width: 100% !important;
        margin: 15px auto 25px auto !important;
    }
    
    .hero-img-wrap-mobile img {
        width: 100% !important;
        height: auto !important;
        aspect-ratio: 16/10 !important;
        object-fit: cover !important;
        display: block !important;
    }
    
    .hero-floating-card {
        bottom: 12px !important;
        right: 12px !important;
        left: auto !important;
        width: 65% !important;
        max-width: 200px !important;
        padding: 9px 10px !important;
        border-radius: 7px !important;
    }
    
    .hero-floating-card .floating-card-title {
        font-size: 8.5px !important;
        margin-bottom: 2px !important;
    }
    
    .hero-floating-card .floating-card-desc {
        font-size: 8px !important;
        line-height: 1.3 !important;
    }
}

/* Mobile Responsive (max-width: 768px) */
@media (max-width: 768px) {
    .hero-img-wrap-mobile {
        max-width: 360px !important;
    }
    
    .hero-floating-card {
        bottom: 10px !important;
        right: 10px !important;
        width: 60% !important;
        max-width: 170px !important;
        padding: 8px !important;
        border-radius: 6px !important;
    }
    
    .hero-floating-card .floating-card-title {
        font-size: 7.5px !important;
    }
    
    .hero-floating-card .floating-card-desc {
        font-size: 7px !important;
        line-height: 1.25 !important;
    }
}

/* Small Mobile Responsive (max-width: 576px) */
@media (max-width: 576px) {
    .hero-img-wrap-mobile {
        max-width: 300px !important;
    }
    
    .hero-floating-card {
        bottom: 8px !important;
        right: 8px !important;
        width: 58% !important;
        max-width: 150px !important;
        padding: 6px 8px !important;
    }
    
    .hero-floating-card .floating-card-title {
        font-size: 7px !important;
    }
    
    .hero-floating-card .floating-card-desc {
        font-size: 6.5px !important;
    }
}
</style>
