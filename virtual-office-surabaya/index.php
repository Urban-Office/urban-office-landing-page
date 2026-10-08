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
$vo_branch_slugs = ['surabaya', 'surabaya-timur', 'surabaya-barat', 'jakarta', 'jakarta-timur', 'gresik', 'medan', 'malang'];

$requested_branch = isset($_GET['branch']) ? trim($_GET['branch']) : 'surabaya';
if (!in_array($requested_branch, $vo_branch_slugs, true) || !isset($locations_db[$requested_branch])) {
    $requested_branch = 'surabaya';
}
$vo_branch = $locations_db[$requested_branch];

// Canonical URL slug per branch — the 'surabaya' key keeps the existing
// indexed /virtual-office-surabaya/ URL without needing a special case.
$vo_branch_urls = [];
foreach ($vo_branch_slugs as $vo_slug) {
    $vo_branch_urls[$vo_slug] = 'virtual-office-' . $vo_slug;
}

// Page slug must vary per branch so SEO tags and the page-cache key don't
// collide between branches sharing this one physical file.
$page_slug = $vo_branch_urls[$requested_branch];
require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<!-- Current branch context: lets the pricing switcher in main.js default to the branch
     in the URL (?branch=) instead of always resetting the cards to Surabaya on load. -->
<script>
  window.currentBranchId = <?php echo json_encode($requested_branch); ?>;
  window.currentBranchCity = <?php echo json_encode(strtolower($vo_branch['city'])); ?>;
</script>

<!-- Hero Section -->
<?php
// SEO/SEM: the branch label follows the city in the URL slug (e.g. "surabaya-timur" ->
// "Surabaya Timur") instead of the building name, so the H1/hero matches the targeted keyword.
$vo_city_name = ucwords(str_replace('-', ' ', $requested_branch));
$hero_tag = 'Virtual Office ' . $vo_city_name;
$hero_title = 'Virtual Office ' . $vo_city_name;
$hero_desc = 'Perluas cabang bisnis Anda menggunakan alamat virtual kantor prestisius di ' . $vo_city_name . '. Alamat kantor bergengsi untuk mendukung ekspansi &amp; citra profesional bisnis Anda. <br><span style="font-size: 0.85em; font-weight: 600;">*Minimum sewa 1 tahun</span>';
$hero_cta_text = 'Lihat Pilihan Paket';
$hero_cta_url = '#pricing';
$hero_cta2_text = 'Hubungi Kami';
$hero_cta2_url = 'https://api.whatsapp.com/send?phone=6285107620100&text=' . urlencode('Halo Urban Office, saya tertarik dengan layanan Virtual Office di Cabang ' . $vo_branch['short_title'] . '. Mohon info selengkapnya.');
$hero_cta2_class = 'btn btn-whatsapp-solid';
$hero_cta2_icon = 'bi bi-whatsapp';
$hero_img = $vo_branch['image'];
$card_title = $vo_city_name;
$card_desc = $vo_branch['address'];
// Branch-specific subheading under the generic H1 so what the visitor sees on arrival matches
// the meta title/description (exact building + area). Built from verified branch data; the
// optional 'nearby' landmark is only appended where locations_data actually provides one.
$hero_subheading = $vo_branch['short_title'] . ', ' . $vo_branch['location'];
if (!empty($vo_branch['nearby'])) {
    $hero_subheading .= ' — ' . $vo_branch['nearby'];
}
include dirname(dirname(__FILE__)) . '/inc/components/hero.php';
?>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>


<!-- Why VO Slider Section -->
<style>
/* Real-text captions under each slider image so Google & screen readers can read the
   value props that were previously baked into the images only (SEO relevance + a11y). */
.why-vo-section .why-vo-card { display: flex; flex-direction: column; background-color: #FFFFFF; }
.why-vo-section .why-vo-card img { border-radius: var(--radius-lg) var(--radius-lg) 0 0; }
.why-vo-caption { padding: 16px 18px 18px; text-align: left; }
.why-vo-caption h3 { font-size: 1.05rem; margin: 0 0 6px; color: #111111; line-height: 1.3; }
.why-vo-caption p { font-size: 0.9rem; line-height: 1.55; color: #444444; margin: 0; }
/* Mobile: shrink the card and let the next card peek in so users know it's swipeable.
   The slider JS measures actual slide width, so this <100% basis stays snapped correctly. */
@media (max-width: 768px) {
    .why-vo-section .why-vo-slide { flex: 0 0 82%; }
    .why-vo-caption { padding: 12px 14px 14px; }
    .why-vo-caption h3 { font-size: 0.95rem; }
    .why-vo-caption p { font-size: 0.82rem; line-height: 1.5; }
}
</style>
<section class="why-vo-section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Kenapa Orang Beralih ke Virtual Office</h2>
            <p class="section-subtitle">Empat alasan utama pelaku usaha memilih virtual office dibanding menyewa kantor fisik konvensional.</p>
        </div>
        <div class="why-vo-slider-container">
            <div class="why-vo-slider-wrap">
                <div class="why-vo-slider" id="why-vo-slider">
                    <!-- Slide 1: Efisiensi biaya -->
                    <div class="why-vo-slide">
                        <div class="why-vo-card">
                            <img src="<?php echo BASE_URL; ?>assets/images/virtualoffice/Buka-Cabang-Lebih-Mudah-4.webp" alt="Efisiensi biaya operasional kantor lebih hemat hingga 90% dengan virtual office" loading="lazy">
                            <div class="why-vo-caption">
                                <h3>Efisiensi Biaya Operasional hingga 90%</h3>
                                <p>Punya alamat kantor prestisius tanpa biaya sewa ruang fisik, listrik, dan perawatan — pangkas pengeluaran operasional hingga 90% dibanding kantor konvensional.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Slide 2: Buka cabang -->
                    <div class="why-vo-slide">
                        <div class="why-vo-card">
                            <img src="<?php echo BASE_URL; ?>assets/images/virtualoffice/Buka-Cabang-Lebih-Mudah-5.webp" alt="Buka cabang kantor baru lebih mudah dan murah dengan virtual office" loading="lazy">
                            <div class="why-vo-caption">
                                <h3>Buka Cabang Baru Lebih Mudah &amp; Murah</h3>
                                <p>Perluas bisnis ke kota atau area baru cukup dengan alamat virtual office, tanpa perlu menyewa dan mengelola kantor fisik di setiap lokasi.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Slide 3: Alamat strategis -->
                    <div class="why-vo-slide">
                        <div class="why-vo-card">
                            <img src="<?php echo BASE_URL; ?>assets/images/virtualoffice/Buka-Cabang-Lebih-Mudah-6.webp" alt="Alamat kantor strategis meningkatkan kepercayaan klien, mitra, dan calon investor" loading="lazy">
                            <div class="why-vo-caption">
                                <h3>Alamat Strategis Meningkatkan Kepercayaan</h3>
                                <p>Alamat di gedung perkantoran komersial ternama membangun citra profesional dan meningkatkan kepercayaan klien, mitra, serta calon investor.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Slide 4: Fasilitas & Fleksibilitas -->
                    <div class="why-vo-slide">
                        <div class="why-vo-card">
                            <img src="<?php echo BASE_URL; ?>assets/images/virtualoffice/Buka-Cabang-Lebih-Mudah-7.webp" alt="Fasilitas kantor lengkap dan fleksibilitas operasional bisnis" loading="lazy">
                            <div class="why-vo-caption">
                                <h3>Fasilitas Lengkap &amp; Fleksibel</h3>
                                <p>Didukung fasilitas ruang pertemuan, penanganan surat masuk, dan layanan resepsionis profesional yang siap mendukung operasional harian bisnis Anda.</p>
                            </div>
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
                <h3 class="activation-card-title">Minta Penawaran</h3>
                <p class="activation-card-desc">Isi formulir kontak atau hubungi kami di WhatsApp. Tim kami akan menyiapkan penawaran resmi.</p>
            </div>
            <div class="activation-card">
                <div class="activation-step-number">2</div>
                <h3 class="activation-card-title">Pembayaran Invoice</h3>
                <p class="activation-card-desc">Invoice tagihan dikirim secara instan via WhatsApp/Email. Lakukan pembayaran transfer aman.</p>
            </div>
            <div class="activation-card">
                <div class="activation-step-number">3</div>
                <h3 class="activation-card-title">Tanda Tangan & Aktif</h3>
                <p class="activation-card-desc">Tanda tangani perjanjian sewa digital/fisik. Alamat kantor Anda langsung aktif dan siap digunakan.</p>
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
        <?php
        // Branch-aware WhatsApp CTA (server-rendered so crawlers & no-JS visitors get the
        // correct branch; main.js refines these per package on load). Keeps the Jakarta page
        // from advertising the Surabaya/MERR branch.
        $vo_wa_text = 'Halo Urban Office, saya tertarik dengan layanan Virtual Office di Cabang ' . $vo_branch['short_title'] . ' (' . $vo_branch['location'] . '). Mohon info penawaran selengkapnya.';
        $vo_wa_url = 'https://api.whatsapp.com/send?phone=6285107620100&text=' . urlencode($vo_wa_text);
        ?>
        <!-- Pricing Cards Grid -->
        <div class="card-grid" id="pricing-grid" style="margin-top: 40px;">
            <?php
            // Every VO branch offers the SAME 3 tiers (Starter / Luxury / Priority); only the
            // address, KPP and WhatsApp CTA vary per branch. The tier structure is sourced from
            // the 'surabaya' reference so all branches render 3 identical cards.
            // main.js targets the price-/features-/cta- IDs for the branch switcher.
            $vo_tier_source = isset($locations_db['surabaya']['pricing']) ? $locations_db['surabaya']['pricing'] : $vo_branch['pricing'];
            $vo_tiers = array_values(array_filter($vo_tier_source, function ($p) {
                return isset($p['category']) && $p['category'] === 'virtual-office';
            }));
            $vo_tier_ids = ['starter', 'luxury', 'priority'];
            foreach ($vo_tiers as $vo_i => $vo_tier):
                $vo_tid        = isset($vo_tier_ids[$vo_i]) ? $vo_tier_ids[$vo_i] : 'tier' . $vo_i;
                $vo_is_popular = ($vo_i === 0);
                $vo_btn_class  = ($vo_i === 1) ? 'btn-primary' : 'btn-outline';
                $vo_cta_label  = ($vo_i === 0) ? 'Dapatkan Promo' : 'Beli Sekarang';
                $vo_tier_wa    = 'https://api.whatsapp.com/send?phone=6285107620100&text=' . urlencode('Halo Urban Office, saya tertarik dengan ' . $vo_tier['name'] . ' di Cabang ' . $vo_branch['short_title'] . ' (' . $vo_branch['location'] . '). Mohon info penawaran selengkapnya.');
                $vo_border     = $vo_is_popular ? 'hsl(var(--clr-primary))' : 'hsl(var(--clr-border))';
            ?>
            <div class="premium-card <?php echo $vo_is_popular ? 'popular-card' : ''; ?>" style="display: flex; flex-direction: column; justify-content: space-between; position: relative; border-color: <?php echo $vo_border; ?>;">
                <?php if ($vo_is_popular): ?>
                <div class="pricing-badge-popular">Populer</div>
                <?php endif; ?>
                <div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 8px;"><?php echo sanitize($vo_tier['name']); ?></h3>
                    <p style="font-size: 0.9rem; margin-bottom: 20px;"><?php echo sanitize($vo_tier['desc']); ?></p>

                    <div class="price-tag" id="price-<?php echo $vo_tid; ?>">
                        Rp <?php echo sanitize($vo_tier['price']); ?>
                        <span>/ Bulan*</span>
                    </div>
                    <p class="price-minimum-info">*Minimum sewa 1 tahun</p>
                    <p class="kpp-info" style="font-weight: bold; color: #FF6B00; margin-top: 5px; font-size: 0.9rem; display: none;"><?php echo sanitize(isset($vo_branch['kpp']) ? $vo_branch['kpp'] : ''); ?></p>

                    <button class="btn-toggle-features" onclick="togglePricingFeatures(this)">
                        <span>Lihat Detail</span> <i class="bi bi-chevron-down"></i>
                    </button>

                    <div class="pricing-features-collapse">
                        <ul class="card-features-list" id="features-<?php echo $vo_tid; ?>">
                            <li class="address-feature">Alamat Bisnis: <?php echo sanitize($vo_branch['address']); ?></li>
                            <?php foreach ($vo_tier['features'] as $vo_feat): ?>
                            <li><?php echo sanitize($vo_feat); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div style="margin-top: 24px; display: flex; flex-direction: column; gap: 10px;">
                    <a href="<?php echo $vo_tier_wa; ?>" class="btn <?php echo $vo_btn_class; ?> cta-btn" style="width: 100%;" target="_blank" id="cta-<?php echo $vo_tid; ?>">
                        <?php echo $vo_cta_label; ?>
                    </a>
                    <button type="button" class="btn btn-secondary offer-btn" style="width: 100%; font-size: 13px;" onclick="activateOfferMode('<?php echo sanitize(ucfirst($vo_tid)); ?>')">
                        Ajukan Penawaran
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<!-- Benefits + Branch Advantages (two-column: "Keuntungan" image slider LEFT, per-branch
     "Keunggulan Cabang" advantages RIGHT) -->
<section class="benefits-vo-section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Keuntungan &amp; Keunggulan Virtual Office</h2>
            <p class="section-subtitle">Fasilitas dan nilai tambah yang Anda dapatkan dari setiap paket virtual office Urban Office.</p>
        </div>

        <div class="benefits-vo-split">
            <!-- LEFT: Keuntungan (image slider) -->
            <div class="benefits-vo-slider-container">
                <div class="benefits-vo-slider-wrap">
                    <div class="benefits-vo-slider" id="benefits-vo-slider">
                        <div class="benefits-vo-slide">
                            <div class="benefits-vo-card">
                                <img src="<?php echo BASE_URL; ?>assets/images/virtualoffice/ekspansi-bisnis-lebih-luas-1.webp" alt="Alamat gedung perkantoran prestisius untuk meningkatkan citra profesional bisnis" loading="lazy">
                            </div>
                        </div>
                        <div class="benefits-vo-slide">
                            <div class="benefits-vo-card">
                                <img src="<?php echo BASE_URL; ?>assets/images/virtualoffice/ekspansi-bisnis-lebih-luas-3.webp" alt="Layanan resepsionis dan surat menyurat menerima paket, dokumen, dan telepon" loading="lazy">
                            </div>
                        </div>
                        <div class="benefits-vo-slide">
                            <div class="benefits-vo-card">
                                <img src="<?php echo BASE_URL; ?>assets/images/virtualoffice/ekspansi-bisnis-lebih-luas-5.webp" alt="Fasilitas ruang meeting dan coworking space tersedia saat dibutuhkan" loading="lazy">
                            </div>
                        </div>
                        <div class="benefits-vo-slide">
                            <div class="benefits-vo-card">
                                <img src="<?php echo BASE_URL; ?>assets/images/virtualoffice/ekspansi-bisnis-lebih-luas.webp" alt="Ekspansi bisnis lebih luas, mudah membuka kantor cabang di tempat lain" loading="lazy">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="benefits-vo-dots" id="benefits-vo-dots"></div>
            </div>

            <!-- RIGHT: single combined list — per-branch advantages + generic VO benefits
                 (moved out of the default slider's image captions into real crawlable text) -->
            <div class="vo-branch-advantages">
                <h3>Keunggulan Cabang <?php echo sanitize($vo_branch['short_title']); ?> (<?php echo sanitize($vo_branch['location']); ?>)</h3>
                <ul class="card-features-list">
                    <?php if (!empty($vo_branch['advantages'])): foreach ($vo_branch['advantages'] as $vo_adv): ?>
                    <li><?php echo sanitize($vo_adv); ?></li>
                    <?php endforeach; endif; ?>
                    <li>Alamat gedung prestisius di lokasi strategis untuk meningkatkan citra profesional bisnis</li>
                    <li>Akses fasilitas ruang meeting atau coworking space saat dibutuhkan</li>
                    <li>Ekspansi bisnis lebih luas tanpa perlu menyewa kantor fisik baru</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Features List Section -->
<?php
$features_title = 'Fasilitas Eksklusif Virtual Office';
$features_list = [
    [
        'title' => 'Alamat Gedung Perkantoran',
        'desc' => 'Alamat gedung perkantoran komersial di lokasi strategis untuk kartu nama, korespondensi bisnis, dan meningkatkan citra profesional perusahaan Anda.',
        'icon' => '🏢'
    ],
    [
        'title' => 'Professional Mail Logging',
        'desc' => 'Setiap surat dan paket penting yang masuk difoto lalu diinformasikan melalui notifikasi WhatsApp di hari yang sama.',
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
        'desc' => 'Resepsionis kami standby menyambut tamu, kurir paket, serta kunjungan verifikasi bisnis atau instansi rekanan.',
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
        'answer' => 'Paket Starter, Luxury, dan Priority semuanya telah mencakup alamat bisnis prestisius, penanganan & pemberitahuan surat/paket masuk beserta kunjungan klien, perjanjian sewa alamat, resepsionis profesional, nomor telepon kantor bersama, serta akses komunitas. Kelebihannya, paket Luxury menyertakan tambahan Akses Meeting Room sebanyak 2 kali (durasi 2 jam/bulan selama setahun). Sementara paket Priority memiliki semua kelebihan tersebut plus tambahan satu bonus khusus yang bisa dipilih: Konsultasi Bisnis, Digital Marketing, Kirim Dokumen, Virtual Assistant, dan lainnya.'
    ],
    [
        'question' => 'Apa saja yang termasuk dalam layanan Virtual Office?',
        'answer' => 'Virtual Office mencakup penggunaan alamat kantor prestisius, penanganan surat & paket masuk dengan notifikasi WhatsApp, resepsionis profesional, serta akses ruang meeting sesuai paket. Untuk kebutuhan ruang kerja fisik, tersedia layanan Private Office.'
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

<!-- Compliance Disclaimer for Advertising Transparency -->
<section style="padding: 24px 0; background-color: #f9f9f9; border-top: 1px solid #e5e7eb;">
    <div class="container">
        <p style="font-size: 12px; color: #6b7280; line-height: 1.6; text-align: center; margin: 0; max-width: 920px; margin-left: auto; margin-right: auto;">
            <strong>Informasi Layanan &amp; Penafian (Disclaimer):</strong> Urban Office adalah penyedia layanan ruang kerja fleksibel, serviced office, dan sewa alamat bisnis komersial milik entitas swasta independen. Urban Office bukan merupakan situs, perwakilan, atau bagian dari instansi/lembaga pemerintah Republik Indonesia dan tidak menerbitkan izin, sertifikasi, atau dokumen resmi kenegaraan.
        </p>
    </div>
</section>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
