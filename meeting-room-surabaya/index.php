<?php
/**
 * Urban Office - Meeting Room Surabaya Landing Page
 */

require_once dirname(dirname(__FILE__)) . '/inc/config.php';
require_once dirname(dirname(__FILE__)) . '/inc/functions.php';
require_once dirname(dirname(__FILE__)) . '/inc/locations_data.php';

// Per-city Meeting Room landing (mirrors Private Office). /meeting-room-{city}/ is rewritten to
// this file with ?lokasi={city}; the real /meeting-room-surabaya/ folder is the Surabaya default.
$svc_category  = 'meeting-room';
$svc_city_slug = isset($_GET['lokasi']) ? strtolower(trim($_GET['lokasi'])) : 'surabaya';
$svc_branches  = service_city_branches($svc_category, $svc_city_slug);
if (empty($svc_branches)) {
    $svc_city_slug = 'surabaya';
    $svc_branches  = service_city_branches($svc_category, $svc_city_slug);
}
$mr_city_label = !empty($svc_branches) ? $svc_branches[0]['city'] : 'Surabaya';

// Per-city page slug so SEO tags + canonical vary per city (and cache keys don't collide).
$page_slug = 'meeting-room-' . $svc_city_slug;
require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<!-- Expose current city so the "Cabang Kami" filter defaults to it -->
<script>window.currentBranchCity = <?php echo json_encode($svc_city_slug); ?>;</script>

<!-- Hero Section -->
<?php
$hero_tag = 'Premium Meeting Room ' . $mr_city_label;
$hero_title = 'Sewa Ruang Meeting di ' . $mr_city_label . ' Mulai 125Rb/Jam';
$hero_desc = 'Sewa ruang meeting harian atau per jam di ' . $mr_city_label . '. Dilengkapi layar LED/Proyektor, papan tulis, internet cepat, air mineral gratis, dan penataan ruangan profesional.';
$hero_cta_text = 'Pesan Jam Rapat';
$hero_cta_url = '#pricing';
// Hero image + floating card follow the city's branch so they change per location.
if (!empty($svc_branches[0]['image'])) {
    $hero_img = $svc_branches[0]['image'];
}
$card_title = 'Meeting Room ' . $mr_city_label;
$card_desc = !empty($svc_branches[0]['address']) ? $svc_branches[0]['address'] : '';
include dirname(dirname(__FILE__)) . '/inc/components/hero.php';
?>


<!-- Pricing Configurations -->
<!-- Meeting Room pricing has two axes (durasi + coffee break). We keep just 2 products
     (Small / Big) and switch the whole price list with a "coffee break" toggle, instead of
     multiplying cards. All prices are crawlable HTML text (better for SEO/LPE than an image). -->
<section class="section" id="pricing" style="background-color: hsl(var(--clr-bg-secondary));">
    <div class="container">
        <h2 class="text-center section-title">Paket Sewa Ruang Meeting</h2>
        <p class="text-center section-subtitle">Harga transparan per durasi. Pilih dengan atau tanpa coffee break sesuai kebutuhan rapat Anda.</p>

        <!-- Coffee break toggle (switches the price list in both cards) -->
        <div class="mr-cb-toggle-container">
            <div class="mr-cb-toggle-switch">
                <input type="radio" id="mr-nocb" name="mr-cb" value="nocb" checked>
                <label for="mr-nocb">Tanpa Coffee Break</label>
                <input type="radio" id="mr-cb" name="mr-cb" value="cb">
                <label for="mr-cb">Dengan Coffee Break</label>
                <span class="mr-cb-slider"></span>
            </div>
        </div>

        <div class="card-grid stretch-items" style="margin-top: 40px; max-width: 900px; margin-left: auto; margin-right: auto; justify-content: center;">
            <!-- SMALL MEETING -->
            <div class="premium-card" style="display: flex; flex-direction: column; justify-content: space-between; position: relative;">
                <div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 8px;">Small Meeting</h3>
                    <p style="font-size: 0.9rem; margin-bottom: 20px;">Ruang meeting privat untuk tim kecil — cocok untuk wawancara, diskusi, atau presentasi klien.</p>

                    <div class="mr-price-block mr-price-nocb">
                        <span class="mr-mulai">Mulai dari</span>
                        <div class="price-tag" style="margin: 0;">Rp 125.000 <span style="font-size: 1rem; color: hsl(var(--clr-text-muted)); font-weight: 500;">/ jam</span></div>
                        <ul class="mr-price-list">
                            <li><span>Per jam</span><b>Rp 125.000</b></li>
                            <li><span>4 jam</span><b>Rp 300.000</b></li>
                            <li><span>8 jam</span><b>Rp 550.000</b></li>
                        </ul>
                    </div>
                    <div class="mr-price-block mr-price-cb" style="display: none;">
                        <span class="mr-mulai">Mulai dari</span>
                        <div class="price-tag" style="margin: 0;">Rp 350.000 <span style="font-size: 1rem; color: hsl(var(--clr-text-muted)); font-weight: 500;">/ 4 jam</span></div>
                        <ul class="mr-price-list">
                            <li><span>4 jam <em>(1x coffee break)</em></span><b>Rp 350.000</b></li>
                            <li><span>8 jam <em>(1x coffee break)</em></span><b>Rp 650.000</b></li>
                        </ul>
                    </div>
                    <p class="mr-note">+ Extra lunch Rp 30.000 / pax (opsional)</p>
                </div>
                <div style="margin-top: 24px;">
                    <a href="#contact" class="btn btn-outline" style="width: 100%;">Booking Sekarang</a>
                </div>
            </div>

            <!-- BIG MEETING -->
            <div class="premium-card popular-card" style="display: flex; flex-direction: column; justify-content: space-between; position: relative; border-color: hsl(var(--clr-primary));">
                <div class="pricing-badge-popular">Populer</div>
                <div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 8px;">Big Meeting</h3>
                    <p style="font-size: 0.9rem; margin-bottom: 20px;">Ruang meeting luas untuk grup besar — ideal untuk seminar, workshop, atau rapat direksi. Harga dihitung per orang (pax).</p>

                    <div class="mr-price-block mr-price-nocb">
                        <span class="mr-mulai">Mulai dari</span>
                        <div class="price-tag" style="margin: 0;">Rp 45.000 <span style="font-size: 1rem; color: hsl(var(--clr-text-muted)); font-weight: 500;">/ pax</span></div>
                        <ul class="mr-price-list">
                            <li><span>4 jam</span><b>Rp 45.000 / pax</b></li>
                            <li><span>8 jam</span><b>Rp 90.000 / pax</b></li>
                        </ul>
                    </div>
                    <div class="mr-price-block mr-price-cb" style="display: none;">
                        <span class="mr-mulai">Mulai dari</span>
                        <div class="price-tag" style="margin: 0;">Rp 55.000 <span style="font-size: 1rem; color: hsl(var(--clr-text-muted)); font-weight: 500;">/ pax</span></div>
                        <ul class="mr-price-list">
                            <li><span>4 jam <em>(1x coffee break)</em></span><b>Rp 55.000 / pax</b></li>
                            <li><span>8 jam <em>(1x coffee break)</em></span><b>Rp 100.000 / pax</b></li>
                            <li><span>8 jam <em>(2x coffee break)</em></span><b>Rp 110.000 / pax</b></li>
                        </ul>
                    </div>
                    <p class="mr-note">+ Over time Rp 21.000 / orang / jam</p>
                </div>
                <div style="margin-top: 24px;">
                    <a href="#contact" class="btn btn-primary" style="width: 100%;">Booking Sekarang</a>
                </div>
            </div>
        </div>

        <p class="text-center" style="margin-top: 28px; font-size: 0.85rem; color: hsl(var(--clr-text-muted)); max-width: 720px; margin-left: auto; margin-right: auto;">
            <i class="bi bi-check-circle-fill" style="color: hsl(var(--clr-primary));"></i> Semua paket sudah termasuk: multimedia projector, WiFi cepat, wireless mic &amp; sound system. Coffee break (kopi, teh, snack &amp; air mineral) tersedia sesuai paket.
        </p>
    </div>

    <style>
    .mr-cb-toggle-container { display: flex; justify-content: center; margin-top: 24px; }
    .mr-cb-toggle-switch { position: relative; display: inline-flex; background: hsl(var(--clr-bg-surface)); border: 1px solid hsl(var(--clr-border)); border-radius: var(--radius-full); padding: 4px; }
    .mr-cb-toggle-switch input { position: absolute; opacity: 0; pointer-events: none; }
    .mr-cb-toggle-switch label { position: relative; z-index: 2; padding: 9px 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; border-radius: var(--radius-full); color: hsl(var(--clr-text-muted)); transition: color 0.3s ease; white-space: nowrap; }
    .mr-cb-slider { position: absolute; top: 4px; bottom: 4px; left: 4px; width: calc(50% - 4px); background: hsl(var(--clr-primary)); border-radius: var(--radius-full); transition: transform 0.3s ease; z-index: 1; }
    .mr-cb-toggle-switch input#mr-cb:checked ~ .mr-cb-slider { transform: translateX(100%); }
    .mr-cb-toggle-switch input#mr-nocb:checked + label[for="mr-nocb"],
    .mr-cb-toggle-switch input#mr-cb:checked + label[for="mr-cb"] { color: #FFFFFF; }
    .mr-mulai { font-size: 0.8rem; font-weight: 700; color: hsl(var(--clr-primary)); text-transform: uppercase; letter-spacing: 0.05em; display: block; text-align: left; margin-bottom: 4px; }
    .mr-price-list { list-style: none; padding: 0; margin: 16px 0 0; }
    .mr-price-list li { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-bottom: 1px dashed hsl(var(--clr-border)); font-size: 0.9rem; }
    .mr-price-list li:last-child { border-bottom: none; }
    .mr-price-list li em { color: hsl(var(--clr-text-muted)); font-style: normal; font-size: 0.78rem; }
    .mr-price-list b { white-space: nowrap; }
    .mr-note { font-size: 0.78rem; color: hsl(var(--clr-text-muted)); margin-top: 14px; }
    @media (max-width: 480px) {
        .mr-cb-toggle-switch label { padding: 8px 12px; font-size: 0.78rem; }
    }
    </style>
    <script>
    (function () {
        var cbRadio = document.getElementById('mr-cb');
        var radios = document.querySelectorAll('input[name="mr-cb"]');
        if (!cbRadio || !radios.length) return;
        function apply() {
            var withCb = cbRadio.checked;
            document.querySelectorAll('.mr-price-nocb').forEach(function (el) { el.style.display = withCb ? 'none' : 'block'; });
            document.querySelectorAll('.mr-price-cb').forEach(function (el) { el.style.display = withCb ? 'block' : 'none'; });
        }
        radios.forEach(function (r) { r.addEventListener('change', apply); });
        apply();
    })();
    </script>
</section>

<!-- Per-city local content: real branch address, map & advantages (unique per city, from
     locations_data.php) so each city page is genuinely differentiated, not a thin duplicate. -->
<section class="section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Lokasi Meeting Room di <?php echo sanitize($mr_city_label); ?></h2>
            <p class="section-subtitle">Ruang meeting Urban Office di <?php echo sanitize($mr_city_label); ?> berada di lokasi strategis dan mudah diakses. Berikut cabang yang melayani sewa ruang meeting di <?php echo sanitize($mr_city_label); ?>.</p>
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
                    ['img' => 'Small Meeting.jpeg', 'title' => 'Small Meeting', 'desc' => 'Mulai Rp 125.000 / jam', 'pax' => '', 'pkg' => 'Small Meeting Room', 'type_id' => 1],
                    ['img' => 'Big Meeting.jpeg', 'title' => 'Big Meeting', 'desc' => 'Mulai Rp 45.000 / pax', 'pax' => '', 'pkg' => 'Big Meeting Room', 'type_id' => 2],
                    ['img' => 'Small Meeting (2).jpeg', 'title' => 'Small Meeting', 'desc' => 'Mulai Rp 125.000 / jam', 'pax' => '', 'pkg' => 'Small Meeting Room', 'type_id' => 3],
                    ['img' => 'Big Meeting (2).jpeg', 'title' => 'Big Meeting', 'desc' => 'Mulai Rp 45.000 / pax', 'pax' => '', 'pkg' => 'Big Meeting Room', 'type_id' => 4],
                    ['img' => 'Big Meeting (3).jpeg', 'title' => 'Big Meeting', 'desc' => 'Mulai Rp 45.000 / pax', 'pax' => '', 'pkg' => 'Big Meeting Room', 'type_id' => 5],
                    ['img' => 'Big Meeting (4).jpeg', 'title' => 'Big Meeting', 'desc' => 'Mulai Rp 45.000 / pax', 'pax' => '', 'pkg' => 'Big Meeting Room', 'type_id' => 6],
                ];
                
                foreach ($gallery_items as $item):
                ?>
                <div class="gallery-card-slide">
                    <div class="gallery-item-card">
                        <!-- Image Container with Pax Badge -->
                        <div class="gallery-img-container">
                            <img src="<?php echo BASE_URL; ?>assets/images/meetingroom/<?php echo $item['img']; ?>" alt="<?php echo $item['title']; ?>" draggable="false">
                            <?php if (!empty($item['pax'])): ?>
                            <span class="gallery-pax-badge">
                                <?php echo $item['pax']; ?>
                            </span>
                            <?php endif; ?>
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
        'question' => 'Berapa harga sewa meeting room di Urban Office ' . $mr_city_label . '?',
        'answer' => 'Small Meeting mulai Rp 125.000 per jam (Rp 300.000 untuk 4 jam, Rp 550.000 untuk 8 jam). Big Meeting dihitung per orang, mulai Rp 45.000/pax untuk 4 jam dan Rp 90.000/pax untuk 8 jam. Tersedia juga paket dengan coffee break.'
    ],
    [
        'question' => 'Apa perbedaan paket dengan dan tanpa coffee break?',
        'answer' => 'Paket dengan coffee break sudah termasuk kopi, teh, snack, dan air mineral untuk peserta. Contoh: Small Meeting 4 jam Rp 350.000 (dengan coffee break) vs Rp 300.000 (tanpa). Big Meeting 4 jam Rp 55.000/pax (dengan) vs Rp 45.000/pax (tanpa).'
    ],
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

// Structured data (GEO/SEO): FAQPage + Product/Offer, generated from the same source data so
// the markup and the visible page never drift. Prices as crawlable JSON-LD help AI/rich results.
$mr_faq_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(function ($f) {
        return [
            '@type' => 'Question',
            'name' => $f['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer']],
        ];
    }, $faqs),
];
$mr_img = BASE_URL . 'assets/images/meetingroom/' . str_replace(' ', '%20', 'Big Meeting.jpeg');
$mr_product_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => 'Sewa Meeting Room ' . $mr_city_label . ' - Urban Office',
    'description' => 'Sewa ruang meeting (Small & Big Meeting Room) di Urban Office ' . $mr_city_label . '. Fasilitas multimedia projector, WiFi, wireless mic, sound system, dengan opsi coffee break.',
    'image' => $mr_img,
    'brand' => ['@type' => 'Brand', 'name' => 'Urban Office'],
    'offers' => [
        '@type' => 'AggregateOffer',
        'priceCurrency' => 'IDR',
        'lowPrice' => '45000',
        'highPrice' => '650000',
        'offerCount' => '2',
        'availability' => 'https://schema.org/InStock',
    ],
];
echo '<script type="application/ld+json">' . json_encode([$mr_faq_schema, $mr_product_schema], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
?>

<!-- Testimonials Section -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/branches.php'; ?>

<!-- Contact Form -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
