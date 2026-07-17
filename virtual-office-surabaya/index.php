<?php
/**
 * Urban Office - Virtual Office Branch Landing Page
 * The default route (no ?branch param, i.e. the existing indexed URL
 * /virtual-office-surabaya/) represents the MERR branch. Other branches are
 * reached via /virtual-office-{slug}/, rewritten by .htaccess to this file
 * with ?branch={slug}. Only the hero section + branch switcher below vary
 * per branch — everything else on the page stays identical.
 */

require_once dirname(dirname(__FILE__)) . '/inc/config.php';
require_once dirname(dirname(__FILE__)) . '/inc/functions.php';
require_once dirname(dirname(__FILE__)) . '/inc/locations_data.php';

// Branches that offer Virtual Office, in display order for the branch switcher
$vo_branch_slugs = ['merr', 'klampis', 'grand-sungkono-lagoon', 'fatmawati', 'gorebiz', 'ptgm-tower', 'medan', 'malang'];

$requested_branch = isset($_GET['branch']) ? trim($_GET['branch']) : 'merr';
if (!in_array($requested_branch, $vo_branch_slugs, true) || !isset($locations_db[$requested_branch])) {
    $requested_branch = 'merr';
}
$vo_branch = $locations_db[$requested_branch];

// Canonical URL slug per branch — merr keeps the existing indexed "surabaya" URL
$vo_branch_urls = [];
foreach ($vo_branch_slugs as $vo_slug) {
    $vo_branch_urls[$vo_slug] = ($vo_slug === 'merr') ? 'virtual-office-surabaya' : 'virtual-office-' . $vo_slug;
}

// Page slug must vary per branch so SEO tags and the page-cache key don't
// collide between branches sharing this one physical file.
$page_slug = $vo_branch_urls[$requested_branch];
require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<!-- Hero Section -->
<?php
$hero_tag = 'Virtual Office ' . $vo_branch['short_title'];
$hero_title = 'Sewa Virtual Office ' . $vo_branch['short_title'] . ' Mulai 385Rb/Bulan*';
$hero_desc = 'Perluas cabang bisnis Anda menggunakan alamat virtual kantor prestisius di ' . $vo_branch['location'] . '. Legalitas 100% aman untuk zonasi PT, CV, PMA, & PKP. <br><span style="font-size: 0.85em; font-weight: 600;">*Minimum sewa 1 tahun</span>';
$hero_cta_text = 'Lihat Pilihan Paket';
$hero_cta_url = '#pricing';
$hero_cta2_text = 'Hubungi Kami';
$hero_cta2_url = 'https://api.whatsapp.com/send?phone=6285107620100&text=' . urlencode('Halo Urban Office, saya tertarik dengan layanan Virtual Office di Cabang ' . $vo_branch['short_title'] . '. Mohon info selengkapnya.');
$hero_cta2_class = 'btn btn-whatsapp-solid';
$hero_cta2_icon = 'bi bi-whatsapp';
$hero_img = $vo_branch['image'];
$card_title = $vo_branch['short_title'];
$card_desc = $vo_branch['address'];
include dirname(dirname(__FILE__)) . '/inc/components/hero.php';
?>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>


<!-- Why VO Slider Section -->
<section class="why-vo-section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Kenapa Orang Beralih ke Virtual Office</h2>
        </div>
        
        <div class="why-vo-slider-container">
            <div class="why-vo-slider-wrap">
                <div class="why-vo-slider" id="why-vo-slider">
                    <!-- Slide 1 -->
                    <div class="why-vo-slide">
                        <div class="why-vo-card">
                            <img src="<?php echo BASE_URL; ?>assets/images/virtualoffice/Buka-Cabang-Lebih-Mudah-4.webp" alt="Kenapa Beralih ke Virtual Office - Hemat Biaya">
                        </div>
                    </div>
                    <!-- Slide 2 -->
                    <div class="why-vo-slide">
                        <div class="why-vo-card">
                            <img src="<?php echo BASE_URL; ?>assets/images/virtualoffice/Buka-Cabang-Lebih-Mudah-5.webp" alt="Kenapa Beralih ke Virtual Office - Buka Cabang">
                        </div>
                    </div>
                    <!-- Slide 3 -->
                    <div class="why-vo-slide">
                        <div class="why-vo-card">
                            <img src="<?php echo BASE_URL; ?>assets/images/virtualoffice/Buka-Cabang-Lebih-Mudah-6.webp" alt="Kenapa Beralih ke Virtual Office - Alamat Strategis">
                        </div>
                    </div>
                    <!-- Slide 4 -->
                    <div class="why-vo-slide">
                        <div class="why-vo-card">
                            <img src="<?php echo BASE_URL; ?>assets/images/virtualoffice/Buka-Cabang-Lebih-Mudah-7.webp" alt="Kenapa Beralih ke Virtual Office - Legalitas PKP">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="why-vo-dots" id="why-vo-dots"></div>
        </div>
    </div>
</section>

<!-- Process Workflow -->
<section class="section">
    <div class="container">
        <h2 class="text-center section-title">Proses Pengaktifan Cepat</h2>
        <p class="section-subtitle" style="text-align: left; margin-left: 0; margin-right: 0;">Virtual office Anda aktif dalam 3 langkah mudah kurang dari 24 jam.</p>
        
        <div class="activation-grid text-center">
            <div class="activation-card">
                <div class="activation-step-number">1</div>
                <h4 class="activation-card-title">Minta Penawaran</h4>
                <p class="activation-card-desc">Isi formulir kontak atau hubungi kami di WhatsApp. Tim kami akan menyiapkan penawaran resmi.</p>
            </div>
            <div class="activation-card">
                <div class="activation-step-number">2</div>
                <h4 class="activation-card-title">Pembayaran Invoice</h4>
                <p class="activation-card-desc">Invoice tagihan dikirim secara instan via WhatsApp/Email. Lakukan pembayaran transfer aman.</p>
            </div>
            <div class="activation-card">
                <div class="activation-step-number">3</div>
                <h4 class="activation-card-title">Tanda Tangan & Aktif</h4>
                <p class="activation-card-desc">Tanda tangani perjanjian sewa digital/fisik. Dokumen domisili terbit dan alamat siap digunakan.</p>
            </div>
        </div>
    </div>
</section>

<!-- Branches Section -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/branches.php'; ?>

<!-- Pricing Component Section with Location Filters -->
<section class="section" style="background-color: hsl(var(--clr-bg-secondary));" id="pricing">
    <div class="container">
        <h2 class="text-center section-title" id="pricing-title">Paket Sewa Virtual Office</h2>
        <p class="text-center section-subtitle">Penawaran harga terbaik dengan alamat kantor prestisius di berbagai kota besar.</p>
        <!-- Pricing Cards Grid -->
        <div class="card-grid" id="pricing-grid" style="margin-top: 40px; align-items: flex-start;">
            <!-- Card 1: Starter -->
            <div class="premium-card popular-card" style="display: flex; flex-direction: column; justify-content: space-between; position: relative; border-color: hsl(var(--clr-primary));">
                <div class="pricing-badge-popular">
                    Populer
                </div>
                <div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 8px;">Virtual Office Starter</h3>
                    <p style="font-size: 0.9rem; margin-bottom: 20px;">Paket hemat alamat domisili hukum perkantoran resmi untuk PT/CV baru.</p>
                    
                    <div class="price-tag" id="price-starter">
                        Rp 385.000
                        <span>/ Bulan*</span>
                    </div>
                    <p class="price-minimum-info">*Minimum sewa 1 tahun</p>
                    <p class="kpp-info" style="font-weight: bold; color: #FF6B00; margin-top: 5px; font-size: 0.9rem; display: none;">KPP Rungkut</p>
                    
                    <button class="btn-toggle-features" onclick="togglePricingFeatures(this)">
                        <span>Lihat Detail</span> <i class="bi bi-chevron-down"></i>
                    </button>
                    
                    <div class="pricing-features-collapse">
                        <ul class="card-features-list" id="features-starter">
                            <li class="address-feature">Alamat Bisnis: Jl. Dr. Ir. H. Soekarno No.470, Kedung Baruk, Rungkut, Surabaya</li>
                            <li>Penanganan Surat & Paket Masuk</li>
                            <li>Notifikasi Instan via WhatsApp/Email</li>
                            <li>Surat Keterangan Domisili Gedung</li>
                            <li>Surat Perjanjian Sewa (Legalisasi)</li>
                            <li>Akses Komunitas WFA</li>
                            <li>Resepsionis Profesional</li>
                            <li>Akses Meeting Room 2 kali 2 Jam / Bulan</li>
                        </ul>
                    </div>
                </div>
                
                <div style="margin-top: 24px; display: flex; flex-direction: column; gap: 10px;">
                    <a href="https://api.whatsapp.com/send?phone=6285107620100&text=Halo%20Urban%20Office%2C%20saya%20tertarik%20dengan%20layanan%20Virtual%20Office%20di%20cabang%20MERR%20(Surabaya%20Timur).%20Mohon%20info%20penawaran%20selengkapnya." class="btn btn-outline cta-btn" style="width: 100%;" target="_blank" id="cta-starter">
                        Dapatkan Promo
                    </a>
                    <button type="button" class="btn btn-secondary offer-btn" style="width: 100%; font-size: 13px;" onclick="activateOfferMode('Starter')">
                        Ajukan Penawaran
                    </button>
                </div>
            </div>
            
            <!-- Card 2: Luxury -->
            <div class="premium-card" style="display: flex; flex-direction: column; justify-content: space-between; position: relative; border-color: hsl(var(--clr-border));">

                <div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 8px;">Virtual Office Luxury</h3>
                    <p style="font-size: 0.9rem; margin-bottom: 20px;">Sempurna untuk startup berkembang yang butuh fasilitas ruang rapat.</p>
                    
                    <div class="price-tag" id="price-luxury">
                        Rp 620.000
                        <span>/ Bulan*</span>
                    </div>
                    <p class="price-minimum-info">*Minimum sewa 1 tahun</p>
                    <p class="kpp-info" style="font-weight: bold; color: #FF6B00; margin-top: 5px; font-size: 0.9rem; display: none;">KPP Rungkut</p>
                    
                    <button class="btn-toggle-features" onclick="togglePricingFeatures(this)">
                        <span>Lihat Detail</span> <i class="bi bi-chevron-down"></i>
                    </button>
                    
                    <div class="pricing-features-collapse">
                        <ul class="card-features-list" id="features-luxury">
                            <li class="address-feature">Alamat Bisnis: Jl. Dr. Ir. H. Soekarno No.470, Kedung Baruk, Rungkut, Surabaya</li>
                            <li>Penanganan Surat & Paket Masuk</li>
                            <li>Notifikasi Instan via WhatsApp/Email</li>
                            <li>Surat Keterangan Domisili Gedung</li>
                            <li>Surat Perjanjian Sewa (Legalisasi)</li>
                            <li>Akses Komunitas WFA</li>
                            <li>Resepsionis Profesional</li>
                            <li>Akses Meeting Room 2 kali 3 Jam / Bulan</li>
                            <li>Nomor Telepon Kantor Bersama</li>
                        </ul>
                    </div>
                </div>
                
                <div style="margin-top: 24px; display: flex; flex-direction: column; gap: 10px;">
                    <a href="https://api.whatsapp.com/send?phone=6285107620100&text=Halo%20Urban%20Office%2C%20saya%20tertarik%20dengan%20layanan%20Virtual%20Office%20di%20cabang%20MERR%20(Surabaya%20Timur).%20Mohon%20info%20penawaran%20selengkapnya." class="btn btn-primary cta-btn" style="width: 100%;" target="_blank" id="cta-luxury">
                        Beli Sekarang
                    </a>
                    <button type="button" class="btn btn-secondary offer-btn" style="width: 100%; font-size: 13px;" onclick="activateOfferMode('Luxury')">
                        Ajukan Penawaran
                    </button>
                </div>
            </div>
            
            <!-- Card 3: Priority -->
            <div class="premium-card" style="display: flex; flex-direction: column; justify-content: space-between; position: relative; border-color: hsl(var(--clr-border));">
                <div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 8px;">Virtual Office Priority</h3>
                    <p style="font-size: 0.9rem; margin-bottom: 20px;">Layanan premium lengkap termasuk bantuan konsultasi legalitas/pajak.</p>
                    
                    <div class="price-tag" id="price-priority">
                        Rp 770.000
                        <span>/ Bulan*</span>
                    </div>
                    <p class="price-minimum-info">*Minimum sewa 1 tahun</p>
                    <p class="kpp-info" style="font-weight: bold; color: #FF6B00; margin-top: 5px; font-size: 0.9rem; display: none;">KPP Rungkut</p>
                    
                    <button class="btn-toggle-features" onclick="togglePricingFeatures(this)">
                        <span>Lihat Detail</span> <i class="bi bi-chevron-down"></i>
                    </button>
                    
                    <div class="pricing-features-collapse">
                        <ul class="card-features-list" id="features-priority">
                            <li class="address-feature">Alamat Bisnis: Jl. Dr. Ir. H. Soekarno No.470, Kedung Baruk, Rungkut, Surabaya</li>
                            <li>Penanganan Surat & Paket Masuk</li>
                            <li>Notifikasi Instan via WhatsApp/Email</li>
                            <li>Surat Keterangan Domisili Gedung</li>
                            <li>Surat Perjanjian Sewa (Legalisasi)</li>
                            <li>Akses Komunitas WFA</li>
                            <li>Resepsionis Profesional</li>
                            <li>Akses Meeting Room 2 kali 4 Jam / Bulan</li>
                            <li>Nomor Telepon Kantor Bersama</li>
                            <li>Tax starter kit (live consultation) / Digital Marketing / Kirim Dokumen Free / Pendirian Perusahaan / Virtual Assistant (Pilih Salah Satu)</li>
                        </ul>
                    </div>
                </div>
                
                <div style="margin-top: 24px; display: flex; flex-direction: column; gap: 10px;">
                    <a href="https://api.whatsapp.com/send?phone=6285107620100&text=Halo%20Urban%20Office%2C%20saya%20tertarik%20dengan%20layanan%20Virtual%20Office%20di%20cabang%20MERR%20(Surabaya%20Timur).%20Mohon%20info%20penawaran%20selengkapnya." class="btn btn-outline cta-btn" style="width: 100%;" target="_blank" id="cta-priority">
                        Beli Sekarang
                    </a>
                    <button type="button" class="btn btn-secondary offer-btn" style="width: 100%; font-size: 13px;" onclick="activateOfferMode('Priority')">
                        Ajukan Penawaran
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Benefits VO Slider Section -->
<section class="benefits-vo-section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Keuntungan Virtual Office di Urban Office</h2>
        </div>
        
        <div class="benefits-vo-slider-container">
            <div class="benefits-vo-slider-wrap">
                <div class="benefits-vo-slider" id="benefits-vo-slider">
                    <!-- Slide 1 -->
                    <div class="benefits-vo-slide">
                        <div class="benefits-vo-card">
                            <img src="<?php echo BASE_URL; ?>assets/images/virtualoffice/ekspansi-bisnis-lebih-luas-1.webp" alt="Keuntungan Virtual Office - Alamat Gedung Prestisius">
                        </div>
                    </div>
                    <!-- Slide 2 -->
                    <div class="benefits-vo-slide">
                        <div class="benefits-vo-card">
                            <img src="<?php echo BASE_URL; ?>assets/images/virtualoffice/ekspansi-bisnis-lebih-luas-3.webp" alt="Keuntungan Virtual Office - Layanan Resepsionis & Surat Menyurat">
                        </div>
                    </div>
                    <!-- Slide 3 -->
                    <div class="benefits-vo-slide">
                        <div class="benefits-vo-card">
                            <img src="<?php echo BASE_URL; ?>assets/images/virtualoffice/ekspansi-bisnis-lebih-luas-5.webp" alt="Keuntungan Virtual Office - Fasilitas Ruang Meeting">
                        </div>
                    </div>
                    <!-- Slide 4 -->
                    <div class="benefits-vo-slide">
                        <div class="benefits-vo-card">
                            <img src="<?php echo BASE_URL; ?>assets/images/virtualoffice/ekspansi-bisnis-lebih-luas.webp" alt="Keuntungan Virtual Office - Ekspansi Bisnis Lebih Luas">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="benefits-vo-dots" id="benefits-vo-dots"></div>
        </div>
    </div>
</section>

<!-- Features List Section -->
<?php
$features_title = 'Fasilitas Eksklusif Virtual Office';
$features_list = [
    [
        'title' => 'Domisili Gedung Resmi',
        'desc' => 'Zonasi perkantoran komersial 100% legal untuk pendaftaran NIB, Akta Notaris, NPWP, dan PKP di KPP Surabaya.',
        'icon' => '🏢'
    ],
    [
        'title' => 'Professional Mail Logging',
        'desc' => 'Setiap surat resmi pajak/hukum masuk difoto dan diinformasikan melalui WA notifikasi di hari yang sama.',
        'icon' => '✉️'
    ],
    [
        'title' => 'Shared Telephone Line',
        'desc' => 'Gunakan nomor telepon kantor bersama di kartu nama Anda. Panggilan tamu akan disaring oleh operator kami.',
        'icon' => '📞'
    ],
    [
        'title' => 'Akses Meeting Room',
        'desc' => 'Temui klien penting Anda di ruang rapat formal kami dengan menyewa slot gratis dari benefit paket Anda.',
        'icon' => '🤝'
    ],
    [
        'title' => 'Signage Perusahaan',
        'desc' => 'Nama brand Anda akan dipasang di papan lobi utama gedung sebagai representasi formal operasional fisik.',
        'icon' => '🏷️'
    ],
    [
        'title' => 'Resepsionis Profesional',
        'desc' => 'Resepsionis kami standby menyambut tamu, kurir paket, dan pejabat dinas pajak yang melakukan verifikasi.',
        'icon' => '👩‍💼'
    ]
];
include dirname(dirname(__FILE__)) . '/inc/components/features.php';
?>

<!-- FAQs Component -->
<?php
$faqs = [
    [
        'question' => 'Apa bedanya Virtual Office Starter, Luxury, dan Priority?',
        'answer' => 'Paket Starter, Luxury, dan Priority semuanya telah mencakup alamat bisnis prestisius, penanganan & pemberitahuan surat/paket masuk beserta kunjungan klien, surat domisili gedung, surat perjanjian sewa, resepsionis profesional, nomor telepon kantor bersama, serta akses komunitas. Kelebihannya, paket Luxury menyertakan tambahan Akses Meeting Room sebanyak 2 kali (durasi 2 jam/bulan selama setahun). Sementara paket Priority memiliki semua kelebihan tersebut plus tambahan satu bonus khusus yang bisa dipilih: Tax Starter Kit (live consultation), Digital Marketing, Kirim Dokumen Free, Pendirian Perusahaan Perorangan, atau Virtual Assistant.'
    ],
    [
        'question' => 'Apakah Virtual Office bisa digunakan untuk daftar PKP pajak?',
        'answer' => 'Tidak bisa, Virtual Office hanya diperuntukkan bagi badan usaha non-PKP. Jika Anda membutuhkan pengurusan PKP pajak, kami menyarankan untuk memilih layanan sewa ruang kantor fisik (Private Office).'
    ],
    [
        'question' => 'Berapa lama proses aktivasi Virtual Office setelah pembayaran?',
        'answer' => 'Layanan Virtual Office Anda bisa langsung aktif segera setelah pembayaran dinyatakan sukses/settlement di portal resmi my.urbanoffice.id dan masuk ke rekening resmi PT URBAN KREASI BERSAMA.'
    ],
    [
        'question' => 'Bagaimana jika ingin melakukan perpanjangan sewa?',
        'answer' => 'Untuk saat ini, perpanjangan sewa Virtual Office dapat dilakukan dengan mudah menghubungi tim admin kami dengan konfirmasi "Perpanjangan Sewa".'
    ]
];
include dirname(dirname(__FILE__)) . '/inc/components/faq.php';
?>





<!-- Contact Form component -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
