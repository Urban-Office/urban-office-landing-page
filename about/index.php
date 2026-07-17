<?php
/**
 * Urban Office - About Us Page
 */

$page_slug = 'about';
require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<!-- Hero Section -->
<?php
$hero_tag = 'Tentang Kami';
$hero_title = 'Mitra Terbaik Pertumbuhan Bisnis Anda';
$hero_desc = 'Urban Office didirikan untuk membebaskan pebisnis dari beban overhead sewa kantor fisik tradisional, memberikan alternatif ruang kerja modern yang efisien, berkelas, dan dinamis.';
$hero_cta_text = 'Hubungi Tim Kami';
$hero_cta_url = '#contact';
include dirname(dirname(__FILE__)) . '/inc/components/hero.php';
?>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>


<!-- Vision Section -->
<section class="section">
    <div class="container text-center">
        <h2>Visi & Misi Kami</h2>
        <p class="section-subtitle">Menjadi pionir penyedia ruang kerja fleksibel berstandar premium dengan dukungan legalitas terlengkap di Indonesia.</p>
        
        <div class="card-grid" style="margin-top: 50px;">
            <div class="premium-card">
                <h3>Visi</h3>
                <p>Menjadi ekosistem perkantoran nomor satu pilihan pengusaha, startup, dan investor global untuk meluncurkan bisnis mereka di Indonesia.</p>
            </div>
            <div class="premium-card">
                <h3>Misi</h3>
                <p>Menyediakan workspace modern terjangkau dengan pelayanan prima, membantu pengurusan izin usaha legal, dan menyelenggarakan program networking komunitas.</p>
            </div>
        </div>
    </div>
</section>



<!-- Contact Form -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
