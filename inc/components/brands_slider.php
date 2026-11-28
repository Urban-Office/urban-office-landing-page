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
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/hydrococo.webp" alt="Hydro Coco" width="300" height="151" loading="lazy" decoding="async"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/allianz.webp" alt="Allianz" width="300" height="151" loading="lazy" decoding="async"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/brighton.webp" alt="Brighton Real Estate" width="300" height="151" loading="lazy" decoding="async"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/shell.webp" alt="Shell" width="300" height="151" loading="lazy" decoding="async"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/wikabeton.webp" alt="Wika Beton" width="300" height="151" loading="lazy" decoding="async"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/mitra_adi_perkasa.webp" alt="Map Mitra Adi Perkasa" width="300" height="151" loading="lazy" decoding="async"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/lg.webp" alt="LG Electronics" width="300" height="151" loading="lazy" decoding="async"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/mitsubishi.webp" alt="Mitsubishi" width="909" height="274" loading="lazy" decoding="async"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/sociolla.webp" alt="Sociolla" width="300" height="151" loading="lazy" decoding="async"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/kalbe.webp" alt="Kalbe" width="300" height="151" loading="lazy" decoding="async"></div>
                    <div class="brand-slide"><img src="<?php echo BASE_URL; ?>assets/images/brands/garam_idfood.webp" alt="Garam member ID FOOD" width="300" height="151" loading="lazy" decoding="async"></div>
                </div>
            </div>
            <button class="brands-nav brands-next" onclick="nextBrandSlide()">&#10095;</button>
            
            <div class="brands-dots" id="brands-dots"></div>
        </div>

        <!-- OUR GROUP SECTION -->
        <div class="our-group-wrap">
            <h3 class="featured-title">Our Group</h3>
            <div class="our-group-logos-grid">
                <div class="our-group-logo"><img src="<?php echo BASE_URL; ?>assets/images/ourgroup/flaz%20tax%20logo.png" alt="Flaz Tax" width="225" height="225" loading="lazy" decoding="async"></div>
                <div class="our-group-logo"><img src="<?php echo BASE_URL; ?>assets/images/ourgroup/frezcup%20logo.webp" alt="Frezcup" width="225" height="225" loading="lazy" decoding="async"></div>
                <div class="our-group-logo"><img src="<?php echo BASE_URL; ?>assets/images/ourgroup/logograhavisi.webp" alt="Graha Visi" width="888" height="325" loading="lazy" decoding="async"></div>
                <div class="our-group-logo"><img src="<?php echo BASE_URL; ?>assets/images/ourgroup/pt_integre_mahakarya_estetika_logo.webp" alt="PT Integre Mahakarya Estetika" width="200" height="200" loading="lazy" decoding="async"></div>
            </div>
        </div>

        <!-- FEATURED ON SECTION -->
        <div class="featured-on-wrap">
            <h3 class="featured-title">Featured On</h3>
            <div class="featured-logos-grid">
                <div class="featured-logo"><img src="<?php echo BASE_URL; ?>assets/images/media/detikcom.webp" alt="detik.com" width="189" height="47" loading="lazy" decoding="async"></div>
                <div class="featured-logo"><img src="<?php echo BASE_URL; ?>assets/images/media/traveloka.webp" alt="traveloka" width="197" height="44" loading="lazy" decoding="async"></div>
                <div class="featured-logo"><img src="<?php echo BASE_URL; ?>assets/images/media/kitalulus.webp" alt="kita lulus" width="173" height="42" loading="lazy" decoding="async"></div>
                <div class="featured-logo"><img src="<?php echo BASE_URL; ?>assets/images/media/glints.webp" alt="glints" width="143" height="68" loading="lazy" decoding="async"></div>
                <div class="featured-logo"><img src="<?php echo BASE_URL; ?>assets/images/media/nibble.webp" alt="nibble" width="148" height="45" loading="lazy" decoding="async"></div>
            </div>
        </div>
    </div>
</section>
