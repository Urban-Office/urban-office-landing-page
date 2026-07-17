<?php
/**
 * Urban Office - Locations Page
 * Displays all 8 branches with photos, addresses, and direct WhatsApp query redirects
 */

// Dynamic routing fallback for location detail pages
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$request_path = urldecode(parse_url($request_uri, PHP_URL_PATH) ?? '');

require_once dirname(dirname(__FILE__)) . '/inc/config.php';
$subfolder = DIR_SUBFOLDER;
if (!empty($subfolder) && strpos($request_path, $subfolder) === 0) {
    $request_path = substr($request_path, strlen($subfolder));
}
$request_path = trim($request_path, '/'); // e.g. "lokasi-urban-office/merr"

$parts = explode('/', $request_path);
if (count($parts) >= 2 && $parts[0] === 'lokasi-urban-office') {
    $slug = $parts[1];
    if (!empty($slug) && $slug !== 'index.php') {
        $_GET['slug'] = $slug;
        require_once __DIR__ . '/detail.php';
        exit;
    }
}

$page_slug = 'lokasi-urban-office';
require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<style>
/* Custom Styles for Locations Page */
#locations .card-grid {
    align-items: stretch !important;
}
.branch-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
}
#locations .branch-card {
    min-height: 500px;
}
.branch-body-content {
    padding: 24px;
    text-align: left;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.branch-body-content h3 {
    font-size: 18px;
    margin-bottom: 12px;
    color: #111111;
    line-height: 1.3;
    font-weight: 700;
    margin-top: 0;
}
.address-container {
    margin-bottom: 15px;
    flex-grow: 1;
}
.address-text {
    font-size: 0.9rem;
    color: #555555;
    line-height: 1.5;
    margin: 0;
}
.btn-toggle-address {
    display: none;
    background: none;
    border: none;
    color: #FF6B00;
    font-weight: 700;
    font-size: 0.82rem;
    padding: 0;
    margin-top: 5px;
    cursor: pointer;
    text-decoration: underline;
    transition: color 0.2s ease;
}
.btn-toggle-address:hover {
    color: #e05e00;
}
.branch-card-footer {
    padding: 0 24px 24px 24px;
    display: flex;
    flex-direction: row;
    gap: 10px;
    align-items: center;
}
.btn-wa-icon-only {
    width: 44px !important;
    height: 44px !important;
    min-width: 44px !important;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    border-color: #25d366 !important;
    color: #25d366 !important;
    background-color: transparent !important;
    border-radius: var(--radius-md) !important;
    transition: all 0.2s ease !important;
}
.btn-wa-icon-only:hover {
    background-color: #25d366 !important;
    color: #FFFFFF !important;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.2) !important;
}

@media (max-width: 768px) {
    #locations .card-grid {
        align-items: stretch !important;
    }
    #locations .branch-card {
        height: auto !important;
        min-height: 420px !important;
        flex: 0 0 240px !important;
        max-width: 240px !important;
    }
    .branch-body-content {
        padding: 16px 12px !important;
    }
    .branch-body-content h3 {
        font-size: 15px !important;
        margin-bottom: 8px !important;
    }
    .address-text {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        font-size: 0.8rem !important;
        line-height: 1.4 !important;
    }
    .btn-toggle-address {
        display: inline-block;
    }
    .branch-card.is-expanded .address-text {
        display: block !important;
        -webkit-line-clamp: unset !important;
        overflow: visible !important;
    }
    .services-text {
        font-size: 0.78rem !important;
        margin-top: 8px !important;
    }
    .branch-card-footer {
        padding: 0 12px 16px 12px !important;
        gap: 8px !important;
    }
    .branch-card-footer .btn-wa-icon-only {
        width: 38px !important;
        height: 38px !important;
        min-width: 38px !important;
    }
}
</style>

<?php
require_once dirname(dirname(__FILE__)) . '/inc/locations_data.php';
$locations = array_values($locations_db);
?>

<!-- Hero Section -->
<?php
$hero_tag = 'Our Office Locations';
$hero_title = 'Lokasi Cabang Urban Office';
$hero_desc = 'Pilih lokasi kantor virtual atau workspace fisik terdekat di kota Anda. Seluruh cabang berada di pusat bisnis strategis dengan perizinan resmi.';
$hero_cta_text = 'Lihat Lokasi';
$hero_cta_url = '#locations';
include dirname(dirname(__FILE__)) . '/inc/components/hero.php';
?>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>


<!-- Locations Grid -->
<section class="section" id="locations">
    <div class="container">
        <h2 class="text-center section-title">Jaringan Cabang Kami</h2>
        <p class="text-center section-subtitle">Temukan representasi kantor yang ideal untuk bisnis Anda di berbagai kota strategis Indonesia.</p>
        
        <div class="card-grid" style="margin-top: 50px;">
            <?php foreach ($locations as $loc): 
                $wa_message = "Halo Urban Office, saya ingin bertanya mengenai ketersediaan layanan dan harga untuk lokasi berikut:\n\nCabang: *" . $loc['title'] . "*\nAlamat: " . $loc['address'];
                $wa_url = "https://api.whatsapp.com/send?phone=6285107620100&text=" . urlencode($wa_message);
            ?>
                <div class="branch-card">
                    <div style="display: flex; flex-direction: column; flex-grow: 1;">
                        <div class="branch-img-wrap">
                            <img src="<?php echo $loc['image']; ?>" alt="<?php echo sanitize($loc['title']); ?>" loading="lazy">
                        </div>
                        <div class="branch-body-content">
                            <h3><?php echo sanitize($loc['title']); ?></h3>
                            <div class="address-container">
                                <p style="font-size: 0.82rem; color: #777777; font-weight: 700; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.03em;">Alamat</p>
                                <p class="address-text"><?php echo sanitize($loc['address']); ?></p>
                                <button type="button" class="btn-toggle-address" onclick="toggleAddress(this)">Lihat Alamat</button>
                            </div>
                            <p class="services-text" style="font-weight: 600; font-size: 0.85rem; color: #FF6B00; margin: 0;">
                                Layanan: <?php echo sanitize($loc['services']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="branch-card-footer">
                        <a href="<?php echo BASE_URL; ?>lokasi-urban-office/<?php echo $loc['slug']; ?>/" class="btn btn-primary" style="flex: 1; justify-content: center; font-size: 12px; padding: 12px 10px; text-align: center; white-space: nowrap;">
                            Lihat Detail Lokasi
                        </a>
                        <a href="<?php echo $wa_url; ?>" target="_blank" rel="noopener" class="btn btn-outline btn-wa-icon-only" title="Tanya via WhatsApp">
                            <i class="bi bi-whatsapp" style="font-size: 1.2rem; margin: 0;"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>



<!-- Contact Form -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<script>
function toggleAddress(btn) {
    const card = btn.closest('.branch-card');
    card.classList.toggle('is-expanded');
    if (card.classList.contains('is-expanded')) {
        btn.textContent = 'Sembunyikan';
    } else {
        btn.textContent = 'Lihat Alamat';
    }
}
</script>

<!-- Client Brands Slider -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/brands_slider.php'; ?>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
