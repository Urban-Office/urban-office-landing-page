<?php
/**
 * Urban Office - Gallery Page
 */

$page_slug = 'gallery';
require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<!-- Hero Section -->
<?php
$hero_tag = 'Workspace Gallery';
$hero_title = 'Galeri Ruang Kerja Urban Office';
$hero_desc = 'Lihat interior premium, area kolaboratif, ruang rapat representatif, dan suasana kerja yang nyaman di seluruh cabang kami.';
$hero_cta_text = 'Pesan Tur Kantor';
$hero_cta_url = '#contact';
include dirname(dirname(__FILE__)) . '/inc/components/hero.php';
?>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>


<!-- Gallery Grid -->
<section class="section">
    <div class="container">
        <h2 class="text-center section-title">Dokumentasi Fasilitas Kami</h2>
        <p class="text-center section-subtitle">Interior estetik yang dirancang secara detail untuk memaksimalkan produktivitas harian Anda.</p>
        
        <div class="card-grid" style="margin-top: 50px;">
            <div class="premium-card" style="padding: 0; overflow: hidden;">
                <img src="<?php echo BASE_URL; ?>assets/images/og-default.png" alt="Coworking Space Area" style="width: 100%; aspect-ratio: 4/3; object-fit: cover;">
                <div style="padding: 16px;">
                    <h4 style="margin:0;">Lobby & Commmunity Lounge</h4>
                </div>
            </div>
            <div class="premium-card" style="padding: 0; overflow: hidden;">
                <img src="<?php echo BASE_URL; ?>assets/images/og-default.png" alt="Serviced Office Space" style="width: 100%; aspect-ratio: 4/3; object-fit: cover;">
                <div style="padding: 16px;">
                    <h4 style="margin:0;">Serviced Office Room</h4>
                </div>
            </div>
            <div class="premium-card" style="padding: 0; overflow: hidden;">
                <img src="<?php echo BASE_URL; ?>assets/images/og-default.png" alt="Meeting Room Facility" style="width: 100%; aspect-ratio: 4/3; object-fit: cover;">
                <div style="padding: 16px;">
                    <h4 style="margin:0;">Conference Boardroom</h4>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- Contact Form -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
