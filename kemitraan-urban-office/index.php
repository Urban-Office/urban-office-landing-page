<?php
/**
 * Urban Office - Kemitraan Landing Page
 */

$page_slug = 'kemitraan-urban-office';
require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<!-- Hero Section -->
<?php
$hero_tag = 'Property Partnership';
$hero_title = 'Kemitraan Properti Urban Office';
$hero_desc = 'Monetisasi aset gedung atau ruko Anda yang strategis menjadi ruang kerja bersama (coworking space / serviced office) yang menghasilkan pendapatan pasif tinggi.';
$hero_cta_text = 'Ajukan Kemitraan';
$hero_cta_url = 'https://api.whatsapp.com/send?phone=6285107620100&text=Saya%20tertarik%20dengan%20kemitraan%20Urban%20Office';
include dirname(dirname(__FILE__)) . '/inc/components/hero.php';
?>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>


<!-- Info Sections -->
<section class="section">
    <div class="container text-center">
        <h2>Ubah Properti Kosong Menjadi Sumber Keuntungan</h2>
        <p class="section-subtitle">Urban Office menawarkan model bagi hasil transparan dan manajemen penuh operasional ruang kantor.</p>
        
        <div class="card-grid" style="margin-top: 50px;">
            <div class="premium-card">
                <h3>1. Desain & Setup</h3>
                <p>Kami merancang, membangun interior, dan mendekorasi gedung Anda agar sesuai dengan standar premium Urban Office.</p>
            </div>
            <div class="premium-card">
                <h3>2. Pemasaran & Operasional</h3>
                <p>Tim marketing kami mengelola periklanan, pencarian penyewa, administrasi, pembayaran, hingga layanan sehari-hari.</p>
            </div>
            <div class="premium-card">
                <h3>3. Skema Bagi Hasil</h3>
                <p>Nikmati pembagian profit bulanan yang transparan dengan dashboard pemantau performa keterisian ruangan.</p>
            </div>
        </div>
    </div>
</section>



<!-- Contact Form -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
