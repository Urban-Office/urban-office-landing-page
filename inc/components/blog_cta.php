<?php
/**
 * Contextual Conversion CTA Component for Blog Articles
 * Automatically tailors the offer, Web App booking link, and Sales WA CTA
 * based on the article's categories, tags, title, and mentioned city/branch.
 */

if (!isset($post) || !is_array($post)) {
    return;
}

$article_title = (string) ($post['title'] ?? '');
$article_slug  = (string) ($post['slug'] ?? '');
$cat_names     = !empty($post_cats) ? array_column($post_cats, 'name') : [];
$tag_names     = !empty($post_tags) ? array_column($post_tags, 'name') : [];

$context_haystack = strtolower(implode(' ', array_merge(
    [$article_title, str_replace('-', ' ', $article_slug)],
    $cat_names,
    $tag_names
)));

// Detect mentioned city/branch (default: surabaya MERR)
$cta_branch = 'surabaya';
$cta_city   = 'surabaya';
if (strpos($context_haystack, 'jakarta') !== false || strpos($context_haystack, 'cakung') !== false || strpos($context_haystack, 'gorebiz') !== false) {
    $cta_branch = 'jakarta-timur';
    $cta_city   = 'jakarta';
} elseif (strpos($context_haystack, 'gresik') !== false || strpos($context_haystack, 'ptgm') !== false) {
    $cta_branch = 'gresik';
    $cta_city   = 'gresik';
} elseif (strpos($context_haystack, 'medan') !== false) {
    $cta_branch = 'medan';
    $cta_city   = 'medan';
} elseif (strpos($context_haystack, 'malang') !== false) {
    $cta_branch = 'malang';
    $cta_city   = 'malang';
} elseif (strpos($context_haystack, 'sungkono') !== false || strpos($context_haystack, 'surabaya barat') !== false) {
    $cta_branch = 'surabaya-barat';
    $cta_city   = 'surabaya';
} elseif (strpos($context_haystack, 'klampis') !== false || strpos($context_haystack, 'surabaya timur') !== false) {
    $cta_branch = 'surabaya-timur';
    $cta_city   = 'surabaya';
}

$wa_sales_num = defined('WHATSAPP_NUMBER_SALES') ? WHATSAPP_NUMBER_SALES : '628212338100';
$make_sales_wa = static function (string $topic) use ($wa_sales_num, $article_title): string {
    $msg = "Halo Tim Sales Urban Office, saya baru membaca artikel \"{$article_title}\" dan ingin konsultasi mengenai {$topic}. Mohon info selengkapnya.";
    return 'https://api.whatsapp.com/send?phone=' . $wa_sales_num . '&text=' . rawurlencode($msg);
};

// Match article topic to the most relevant service offer
$cta_badge       = 'Solusi Bisnis Urban Office';
$cta_title       = 'Bangun & Kembangkan Bisnis Lebih Efisien Bersama Urban Office';
$cta_desc        = 'Tersedia Virtual Office, Private Office siap pakai, Ruang Meeting, Coworking Space, hingga layanan pendirian PT/CV dan perpajakan dalam satu ekosistem.';
$cta_primary_txt = 'Booking Online';
$cta_primary_url = get_webapp_home_url();
$cta_primary_ext = true;
$cta_primary_ico = 'bi-calendar2-check';
$cta_second_txt  = 'Konsultasi Gratis via WA';
$cta_second_url  = $make_sales_wa('layanan ruang kerja dan bisnis Urban Office');
$cta_second_ext  = true;
$cta_second_ico  = 'bi-whatsapp';

$has_any = static function (string $haystack, array $needles): bool {
    foreach ($needles as $n) {
        if (strpos($haystack, $n) !== false) {
            return true;
        }
    }
    return false;
};

if ($has_any($context_haystack, ['pajak', 'spt', 'pph', 'ppn', 'sp2dk', 'akunting', 'akuntansi', 'pembukuan', 'laporan keuangan'])) {
    $cta_badge       = 'Jasa Pajak & Akunting';
    $cta_title       = 'Butuh Bantuan Pembukuan & Pelaporan Pajak Perusahaan?';
    $cta_desc        = 'Serahkan pembukuan bulanan, pelaporan SPT masa/tahunan, hingga pendampingan SP2DK kepada tim profesional Urban Office agar Anda bisa fokus mengembangkan bisnis.';
    $cta_primary_txt = 'Konsultasi Pajak Gratis';
    $cta_primary_url = $make_sales_wa('layanan Jasa Pajak & Akunting');
    $cta_primary_ext = true;
    $cta_primary_ico = 'bi-whatsapp';
    $cta_second_txt  = 'Lihat Layanan Pajak';
    $cta_second_url  = BASE_URL . 'pajak-dan-akunting/';
    $cta_second_ext  = false;
    $cta_second_ico  = 'bi-arrow-right';
} elseif ($has_any($context_haystack, ['pendirian', 'pt pma', ' pma', 'pt perorangan', 'buat pt', 'bikin pt', 'pendirian cv', 'legalitas', 'nib', 'akta perusahaan', 'perizinan', 'badan usaha'])) {
    $legal_slug  = 'pendirian-pt-include-virtual-office';
    $legal_label = 'Pendirian PT + Virtual Office';
    if ($has_any($context_haystack, ['pma', 'penanaman modal asing'])) {
        $legal_slug  = 'pendirian-pma-plus-virtual-office';
        $legal_label = 'Pendirian PT PMA + Virtual Office';
    } elseif ($has_any($context_haystack, ['perorangan'])) {
        $legal_slug  = 'pendirian-perorangan-plus-virtual-office';
        $legal_label = 'Pendirian PT Perorangan + VO';
    } elseif ($has_any($context_haystack, [' cv', 'cv ', 'pendirian cv'])) {
        $legal_slug  = 'pendirian-cv-virtual-office';
        $legal_label = 'Pendirian CV + Virtual Office';
    } elseif ($has_any($context_haystack, ['perizinan', 'perubahan akta', 'perubahan perusahaan'])) {
        $legal_slug  = 'perizinan-dan-perubahan-perusahaan';
        $legal_label = 'Perizinan & Perubahan Perusahaan';
    }

    $cta_badge       = 'Layanan Legalitas Usaha';
    $cta_title       = 'Urus ' . $legal_label . ' Tanpa Ribet?';
    $cta_desc        = 'Paket lengkap pengurusan legalitas usaha dibantu tim berpengalaman, transparan, dan langsung terintegrasi dengan alamat bisnis prestisius Urban Office.';
    $cta_primary_txt = 'Konsultasi Legalitas Gratis';
    $cta_primary_url = $make_sales_wa($legal_label);
    $cta_primary_ext = true;
    $cta_primary_ico = 'bi-whatsapp';
    $cta_second_txt  = 'Lihat Detail Paket';
    $cta_second_url  = BASE_URL . $legal_slug . '/';
    $cta_second_ext  = false;
    $cta_second_ico  = 'bi-arrow-right';
} elseif ($has_any($context_haystack, ['meeting room', 'ruang meeting', 'ruang rapat'])) {
    $mr_branch       = in_array($cta_branch, ['surabaya', 'surabaya-timur', 'surabaya-barat', 'jakarta-timur', 'gresik', 'malang'], true) ? $cta_branch : 'surabaya';
    $mr_city         = ($mr_branch === 'jakarta-timur') ? 'jakarta' : (in_array($mr_branch, ['gresik', 'malang'], true) ? $mr_branch : 'surabaya');
    $mr_has_app      = has_webapp_booking($mr_branch);

    $cta_badge       = 'Sewa Ruang Meeting';
    $cta_title       = 'Butuh Ruang Meeting Profesional Mulai Rp 125.000/jam?';
    $cta_desc        = 'Lengkap dengan proyektor, WiFi serat optik cepat, papan tulis, serta opsi coffee break & lunch untuk presentasi klien maupun rapat tim.';
    $cta_primary_txt = $mr_has_app ? 'Booking Online' : 'Tanya via WA';
    $cta_primary_url = $mr_has_app ? build_webapp_booking_url($mr_branch, 'meeting-room') : $make_sales_wa('Sewa Ruang Meeting');
    $cta_primary_ext = true;
    $cta_primary_ico = $mr_has_app ? 'bi-calendar2-check' : 'bi-whatsapp';
    $cta_second_txt  = 'Lihat Paket';
    $cta_second_url  = BASE_URL . 'meeting-room-' . $mr_city . '/';
    $cta_second_ext  = false;
    $cta_second_ico  = 'bi-arrow-right';
} elseif ($has_any($context_haystack, ['event space', 'seminar', 'workshop', 'ruang acara', 'aula'])) {
    $es_branch       = ($cta_branch === 'malang') ? 'malang' : 'surabaya';
    $es_has_app      = has_webapp_booking($es_branch);

    $cta_badge       = 'Sewa Event Space';
    $cta_title       = 'Cari Ruang Seminar & Workshop Mulai Rp 45.000/pax?';
    $cta_desc        = 'Kapasitas fleksibel 30–80 pax dengan tata letak theater/classroom, proyektor HD, sound system, serta staf support on-site.';
    $cta_primary_txt = $es_has_app ? 'Booking Online' : 'Tanya via WA';
    $cta_primary_url = $es_has_app ? build_webapp_booking_url($es_branch, 'event-space') : $make_sales_wa('Sewa Event Space');
    $cta_primary_ext = true;
    $cta_primary_ico = $es_has_app ? 'bi-calendar2-check' : 'bi-whatsapp';
    $cta_second_txt  = 'Lihat Paket';
    $cta_second_url  = BASE_URL . 'event-space-55k-perjam-urbanoffice/';
    $cta_second_ext  = false;
    $cta_second_ico  = 'bi-arrow-right';
} elseif ($has_any($context_haystack, ['coworking', 'wfa', 'wfh', 'remote work', 'sharing room', 'meja kerja'])) {
    $cs_branch       = in_array($cta_branch, ['surabaya', 'surabaya-timur', 'malang'], true) ? $cta_branch : 'surabaya';
    $cs_has_app      = has_webapp_booking($cs_branch);

    $cta_badge       = 'Coworking Space & WFA';
    $cta_title       = 'Cari Tempat WFA & Coworking Nyaman Mulai Rp 10.000/jam?';
    $cta_desc        = 'Tersedia paket per jam, harian (Daily Pass), hingga bulanan dengan internet fiber optic cepat dan free flow kopi & teh.';
    $cta_primary_txt = $cs_has_app ? 'Booking Online' : 'Tanya via WA';
    $cta_primary_url = $cs_has_app ? build_webapp_booking_url($cs_branch, 'coworking') : $make_sales_wa('Coworking Space');
    $cta_primary_ext = true;
    $cta_primary_ico = $cs_has_app ? 'bi-calendar2-check' : 'bi-whatsapp';
    $cta_second_txt  = 'Lihat Paket';
    $cta_second_url  = BASE_URL . 'coworking-space-urban-office/';
    $cta_second_ext  = false;
    $cta_second_ico  = 'bi-arrow-right';
} elseif ($has_any($context_haystack, ['private office', 'serviced office', 'sewa kantor', 'ruang kantor', 'kantor privat'])) {
    $po_branch       = in_array($cta_branch, ['surabaya', 'jakarta-timur', 'gresik', 'malang'], true) ? $cta_branch : 'surabaya';
    $po_city         = ($po_branch === 'jakarta-timur') ? 'jakarta' : (in_array($po_branch, ['gresik', 'malang'], true) ? $po_branch : 'surabaya');
    $po_has_app      = has_webapp_booking($po_branch);

    $cta_badge       = 'Private Office Siap Pakai';
    $cta_title       = 'Cari Ruang Kantor Privat Fully Furnished & Bebas Biaya Utilitas?';
    $cta_desc        = 'Langsung siap kerja untuk tim 2–6 orang. Sudah termasuk meja-kursi ergonomis, AC, listrik, WiFi cepat, resepsionis, dan ruang meeting.';
    $cta_primary_txt = $po_has_app ? 'Booking Online' : 'Tanya via WA';
    $cta_primary_url = $po_has_app ? build_webapp_booking_url($po_branch, 'private-office') : $make_sales_wa('Private Office');
    $cta_primary_ext = true;
    $cta_primary_ico = $po_has_app ? 'bi-calendar2-check' : 'bi-whatsapp';
    $cta_second_txt  = 'Lihat Paket';
    $cta_second_url  = BASE_URL . 'sewa-kantor-' . $po_city . '/';
    $cta_second_ext  = false;
    $cta_second_ico  = 'bi-arrow-right';
} elseif ($has_any($context_haystack, ['virtual office', 'kantor virtual', 'alamat bisnis', 'domisili', 'kpp', 'pkp'])) {
    $vo_has_app      = has_webapp_booking($cta_branch);

    $cta_badge       = 'Virtual Office Urban Office';
    $cta_title       = 'Butuh Alamat Bisnis Prestisius Mulai Rp 6.000-an/hari?';
    $cta_desc        = 'Hemat biaya operasional kantor hingga 90%. Tersedia di Surabaya, Jakarta Timur, Gresik, Medan, hingga Malang — lengkap penanganan surat & kuota ruang meeting.';
    $cta_primary_txt = $vo_has_app ? 'Booking Online' : 'Tanya via WA';
    $cta_primary_url = $vo_has_app ? build_webapp_booking_url($cta_branch, 'virtual-office') : $make_sales_wa('Virtual Office');
    $cta_primary_ext = true;
    $cta_primary_ico = $vo_has_app ? 'bi-calendar2-check' : 'bi-whatsapp';
    $cta_second_txt  = 'Lihat Paket VO';
    $cta_second_url  = BASE_URL . 'virtual-office-' . $cta_branch . '/';
    $cta_second_ext  = false;
    $cta_second_ico  = 'bi-arrow-right';
}
?>

<!-- Contextual Conversion CTA Box -->
<aside class="blog-conversion-cta" aria-label="Penawaran layanan relevan Urban Office">
    <div class="blog-cta-inner">
        <div class="blog-cta-copy">
            <span class="blog-cta-badge"><?php echo sanitize($cta_badge); ?></span>
            <h3 class="blog-cta-title"><?php echo sanitize($cta_title); ?></h3>
            <p class="blog-cta-desc"><?php echo sanitize($cta_desc); ?></p>
        </div>
        <div class="blog-cta-actions">
            <a href="<?php echo sanitize($cta_primary_url); ?>"<?php echo $cta_primary_ext ? ' target="_blank" rel="noopener"' : ''; ?> class="btn btn-primary blog-cta-btn-primary">
                <i class="bi <?php echo sanitize($cta_primary_ico); ?>"></i> <?php echo sanitize($cta_primary_txt); ?>
            </a>
            <a href="<?php echo sanitize($cta_second_url); ?>"<?php echo $cta_second_ext ? ' target="_blank" rel="noopener"' : ''; ?> class="btn btn-outline blog-cta-btn-secondary">
                <?php echo sanitize($cta_second_txt); ?> <i class="bi <?php echo sanitize($cta_second_ico); ?>"></i>
            </a>
        </div>
    </div>
</aside>

<style>
.blog-conversion-cta {
    margin-top: 44px;
    padding: 30px 32px;
    border-radius: 16px;
    background: linear-gradient(135deg, #FFF7F0 0%, #FFFFFF 60%, #FFF2E6 100%);
    border: 1.5px solid rgba(255, 107, 0, 0.28);
    box-shadow: 0 12px 32px rgba(255, 107, 0, 0.08);
}
.blog-cta-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 28px;
}
.blog-cta-copy {
    flex: 1;
    min-width: 0;
}
.blog-cta-badge {
    display: inline-block;
    background: rgba(255, 107, 0, 0.12);
    color: #E05E00;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 5px 12px;
    border-radius: 999px;
    margin-bottom: 10px;
}
.blog-cta-title {
    margin: 0 0 8px;
    font-size: clamp(1.2rem, 2.2vw, 1.55rem);
    font-weight: 800;
    line-height: 1.3;
    color: #111111;
}
.blog-cta-desc {
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.6;
    color: #4B5563;
}
.blog-cta-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex-shrink: 0;
    min-width: 230px;
}
.blog-cta-btn-primary,
.blog-cta-btn-secondary {
    width: 100%;
    justify-content: center;
    text-align: center;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    font-size: 0.88rem;
}
@media (max-width: 768px) {
    .blog-conversion-cta {
        padding: 16px 14px;
        margin-top: 24px;
        border-radius: 12px;
    }
    .blog-cta-inner {
        flex-direction: row;
        align-items: center;
        gap: 12px;
    }
    .blog-cta-badge {
        font-size: 0.62rem;
        padding: 3px 8px;
        margin-bottom: 6px;
    }
    .blog-cta-title {
        font-size: 0.92rem;
        line-height: 1.28;
        margin-bottom: 5px;
    }
    .blog-cta-desc {
        font-size: 0.75rem;
        line-height: 1.42;
    }
    .blog-cta-actions {
        flex: 0 0 34%;
        min-width: 118px;
        max-width: 152px;
        gap: 6px;
    }
    .blog-cta-btn-primary,
    .blog-cta-btn-secondary {
        padding: 6px 9px !important;
        font-size: 0.63rem !important;
        line-height: 1.2;
        border-radius: 7px !important;
        white-space: nowrap;
        gap: 4px;
        min-height: 30px;
    }
}
@media (max-width: 400px) {
    .blog-conversion-cta {
        padding: 12px 10px;
    }
    .blog-cta-inner {
        gap: 10px;
    }
    .blog-cta-title {
        font-size: 0.84rem;
    }
    .blog-cta-desc {
        font-size: 0.7rem;
    }
    .blog-cta-actions {
        flex: 0 0 36%;
        min-width: 108px;
        max-width: 136px;
    }
    .blog-cta-btn-primary,
    .blog-cta-btn-secondary {
        padding: 5px 7px !important;
        font-size: 0.59rem !important;
        min-height: 28px;
    }
}
</style>
