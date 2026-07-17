<?php
/**
 * Urban Office - Newsletter Subscription Page
 */

$page_slug = 'newsletter';
require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<!-- Hero Section -->
<?php
$hero_tag = 'Newsletter Subscriptions';
$hero_title = 'Dapatkan Tips Bisnis & Promo Menarik';
$hero_desc = 'Berlangganan buletin berkala kami untuk menerima informasi terbaru mengenai regulasi perpajakan, hukum korporasi, tips manajemen WFA, serta diskon sewa ruang kerja khusus.';
$hero_cta_text = 'Berlangganan Gratis';
$hero_cta_url = '#subscribe-section';
include dirname(dirname(__FILE__)) . '/inc/components/hero.php';
?>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>


<!-- Newsletter subscription box -->
<section class="section" id="subscribe-section">
    <div class="container text-center">
        <div class="contact-form-card" style="max-width: 500px;">
            <h3>Masukkan Email Anda</h3>
            <p style="margin-bottom: 24px;">Kami menghargai privasi Anda dan tidak akan mengirimkan spam.</p>
            
            <form action="#" method="POST" onsubmit="alert('Terima kasih! Pendaftaran WFA newsletter Anda sukses.'); this.reset(); return false;">
                <div class="form-group">
                    <input type="email" class="form-control" placeholder="ayu@perusahaan.com" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; margin-top: 10px;">Daftar Sekarang</button>
            </form>
        </div>
    </div>
</section>



<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
