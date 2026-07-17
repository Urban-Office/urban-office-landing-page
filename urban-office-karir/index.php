<?php
/**
 * Urban Office - Careers Page
 */

$page_slug = 'urban-office-karir';
require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<style>
/* Custom Styles for Careers Page */
#positions .card-grid {
    align-items: stretch !important;
}
#positions .premium-card {
    display: flex;
    flex-direction: column;
    height: 100%;
}
#positions .premium-card p {
    flex-grow: 1;
    margin-bottom: 20px;
}
#positions .premium-card .btn {
    margin-top: auto;
    justify-content: center !important;
}
</style>

<!-- Hero Section -->
<?php
$hero_tag = 'Join Our Team';
$hero_title = 'Karir di Urban Office';
$hero_desc = 'Mari tumbuh dan berkembang bersama penyedia coworking space paling inovatif di Indonesia. Temukan lowongan yang cocok untuk potensi terbaik Anda.';
$hero_cta_text = 'Lihat Lowongan';
$hero_cta_url = '#positions';
include dirname(dirname(__FILE__)) . '/inc/components/hero.php';
?>

<!-- Careers Grid -->
<section class="section" id="positions">
    <div class="container">
        <h2 class="text-center section-title">Posisi yang Tersedia</h2>
        <p class="text-center section-subtitle">Mari bergabung dengan tim operasional dan support kami yang enerjik.</p>
        
        <div class="card-grid" style="margin-top: 50px;">
            <div class="premium-card">
                <span class="hero-tag" style="background-color: hsl(var(--clr-primary-light)); color: hsl(var(--clr-primary));">Surabaya</span>
                <h3 style="margin-top: 12px;">Manager Working Cafe</h3>
                <p>Mengelola operasional cafe komunal, logistik bahan baku, menu harian, serta kepuasan customer WFA.</p>
                <a href="#contact" class="btn btn-outline" style="width: 100%; margin-top: 20px;">Lamar Sekarang</a>
            </div>
            
            <div class="premium-card">
                <span class="hero-tag" style="background-color: hsl(var(--clr-primary-light)); color: hsl(var(--clr-primary));">Surabaya / Jakarta</span>
                <h3 style="margin-top: 12px;">Senior Business Development</h3>
                <p>Mencari partnership gedung baru, mengelola klien korporat B2B, serta memformulasikan ekspansi bisnis retail kantor.</p>
                <a href="#contact" class="btn btn-outline" style="width: 100%; margin-top: 20px;">Lamar Sekarang</a>
            </div>
            
            <div class="premium-card">
                <span class="hero-tag" style="background-color: hsl(var(--clr-primary-light)); color: hsl(var(--clr-primary));">Surabaya</span>
                <h3 style="margin-top: 12px;">Generalist HRD</h3>
                <p>Mengelola recruitment staf lobi, admin, cleaning service, payroll bulanan, absensi, serta training hospitality.</p>
                <a href="#contact" class="btn btn-outline" style="width: 100%; margin-top: 20px;">Lamar Sekarang</a>
            </div>
        </div>
    </div>
</section>



<!-- Contact Form -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
