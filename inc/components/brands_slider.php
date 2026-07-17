<?php
/**
 * Brands, Groups & Featured On Slider Component
 * Displays trusted clients slider, our group logos, and featured media logos.
 */

// Prevent direct access
if (basename($_SERVER['SCRIPT_FILENAME']) === 'brands_slider.php') {
    header('HTTP/1.1 403 Forbidden');
    exit('Direct access forbidden.');
}
?>
<!-- CLIENT BRANDS SLIDER SECTION -->
<section class="section brands-section">
    <div class="container">
        <div class="text-center">
            <span class="badge" style="background-color: #FFD700; color: #111111;">Klien Kami</span>
            <h2>Terpercaya di Kalangan Pimpinan</h2>
            <p class="section-subtitle">Ruang kerja kami tidak hanya menjadi pilihan terpercaya, tetapi juga diandalkan oleh berbagai perusahaan besar untuk meraih kesuksesan luar biasa</p>
        </div>
        
        <div class="brands-slider-container">
            <button class="brands-nav brands-prev" onclick="prevBrandSlide()">&#10094;</button>
            <div class="brands-slider-wrap">
                <div class="brands-slider" id="brands-slider">
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/hydrococo.png" alt="Hydro Coco"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/allianz.png" alt="Allianz"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/brighton.png" alt="Brighton Real Estate"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/shell.webp" alt="Shell"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/wikabeton.png" alt="Wika Beton"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/mitra_adi_perkasa.png" alt="Map Mitra Adi Perkasa"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/lg.webp" alt="LG Electronics"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/mitsubishi.png" alt="Mitsubishi"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/sociolla.png" alt="Sociolla"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/kalbe.png" alt="Kalbe"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/garam_idfood.png" alt="Garam member ID FOOD"></div>
                </div>
            </div>
            <button class="brands-nav brands-next" onclick="nextBrandSlide()">&#10095;</button>
            
            <div class="brands-dots" id="brands-dots"></div>
        </div>

        <!-- OUR GROUP SECTION -->
        <div class="our-group-wrap">
            <h3 class="featured-title">Our Group</h3>
            <div class="our-group-logos-grid">
                <div class="our-group-logo"><img src="<?php echo BASE_URL; ?>assets/images/ourgroup/flaz%20tax%20logo.png" alt="Flaz Tax"></div>
                <div class="our-group-logo"><img src="<?php echo BASE_URL; ?>assets/images/ourgroup/frezcup%20logo.jpg" alt="Frezcup"></div>
                <div class="our-group-logo"><img src="<?php echo BASE_URL; ?>assets/images/ourgroup/logograhavisi.png" alt="Graha Visi"></div>
                <div class="our-group-logo"><img src="<?php echo BASE_URL; ?>assets/images/ourgroup/pt_integre_mahakarya_estetika_logo.jpg" alt="PT Integre Mahakarya Estetika"></div>
            </div>
        </div>

        <!-- FEATURED ON SECTION -->
        <div class="featured-on-wrap">
            <h3 class="featured-title">Featured On</h3>
            <div class="featured-logos-grid">
                <div class="featured-logo"><img src="<?php echo BASE_URL; ?>assets/images/media/detikcom.png" alt="detik.com"></div>
                <div class="featured-logo"><img src="<?php echo BASE_URL; ?>assets/images/media/traveloka.png" alt="traveloka"></div>
                <div class="featured-logo"><img src="<?php echo BASE_URL; ?>assets/images/media/kitalulus.png" alt="kita lulus"></div>
                <div class="featured-logo"><img src="<?php echo BASE_URL; ?>assets/images/media/glints.png" alt="glints"></div>
                <div class="featured-logo"><img src="<?php echo BASE_URL; ?>assets/images/media/nibble.png" alt="nibble"></div>
            </div>
        </div>
    </div>
</section>
