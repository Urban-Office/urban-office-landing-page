<?php
/**
 * Urban Office - Pendirian PT PMA (Foreign Investment) + Virtual Office Landing Page
 */

$page_slug = 'pendirian-pma-plus-virtual-office';
require_once dirname(dirname(__FILE__)) . '/inc/header.php';
?>

<!-- Custom Styles for Landing Page -->
<style>
/* Hero Section styling overrides */
.bundle-hero {
    padding: 100px 0 60px 0;
    background-color: #FFFFFF;
}
.hero-grid {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 40px;
    align-items: center;
}
.hero-ctas {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 15px;
}
.hero-ctas .btn-primary:hover,
.hero-ctas .btn-primary:active {
    background-color: #FFFFFF !important;
    color: #FF6B00 !important;
    border-color: #FF6B00 !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 107, 0, 0.15) !important;
}
.hero-ctas .btn-outline:hover,
.hero-ctas .btn-outline:active {
    background-color: #FF6B00 !important;
    color: #FFFFFF !important;
    border-color: #FF6B00 !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 107, 0, 0.3) !important;
}
.hero-content {
    text-align: left;
}
.hero-content h1 {
    margin-bottom: 20px;
    font-weight: 800;
    font-size: clamp(2rem, 4vw, 2.5rem);
    line-height: 1.15;
}
.hero-content p {
    font-size: 15px;
    color: #444444;
    margin-bottom: 20px;
    line-height: 1.6;
}
.hero-img-wrap-desktop {
    position: relative;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
    border: 1px solid #EAEAEA;
}
.hero-img-wrap-desktop img {
    width: 100%;
    height: auto;
    object-fit: cover;
    aspect-ratio: 16/10;
    display: block;
}
.hero-img-wrap-mobile {
    display: none;
}
.hero-floating-card {
    position: absolute;
    bottom: 15px;
    right: 15px;
    left: auto;
    width: 70%;
    max-width: 240px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.4);
    text-align: left;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
.hero-floating-card .floating-card-title {
    margin: 0 0 3px 0 !important;
    font-weight: 800;
    color: #FF6B00;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.hero-floating-card .floating-card-desc {
    margin: 0 !important;
    font-size: 9px;
    color: #444444;
    line-height: 1.35;
}
@media (max-width: 991px) {
    .hero-grid {
        grid-template-columns: 1fr;
        gap: 40px;
        text-align: center;
    }
    .hero-grid .hero-ctas {
        justify-content: center;
    }
    .hero-img-wrap-desktop {
        display: none !important;
    }
    .hero-img-wrap-mobile {
        display: block !important;
        position: relative;
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        border: 1px solid #EAEAEA;
        max-width: 420px !important;
        width: 100% !important;
        margin: 15px auto !important;
    }
    .hero-img-wrap-mobile img {
        width: 100% !important;
        height: auto !important;
        aspect-ratio: 16/10 !important;
        object-fit: cover !important;
        display: block !important;
    }
    .hero-floating-card {
        bottom: 12px !important;
        right: 12px !important;
        left: auto !important;
        width: 65% !important;
        max-width: 200px !important;
        padding: 9px 10px !important;
        border-radius: 7px !important;
    }
    .hero-floating-card .floating-card-title {
        font-size: 8.5px !important;
        margin-bottom: 2px !important;
    }
    .hero-floating-card .floating-card-desc {
        font-size: 8px !important;
        line-height: 1.3 !important;
    }
}

/* Steps workflow section styling */
.steps-section {
    background-color: hsl(var(--clr-bg-secondary));
    border-top: 1px solid hsl(var(--clr-border));
    border-bottom: 1px solid hsl(var(--clr-border));
    padding: 70px 0;
}
.steps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    margin-top: 40px;
}
.step-card {
    background-color: #FFFFFF;
    border-radius: var(--radius-md);
    border: 1px solid hsl(var(--clr-border));
    padding: 30px 24px;
    box-shadow: var(--shadow-sm);
    text-align: center;
    position: relative;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.step-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
    border-color: hsl(var(--clr-primary));
}
.step-number-circle {
    width: 50px;
    height: 50px;
    background-color: hsl(var(--clr-primary-light));
    color: hsl(var(--clr-primary));
    font-size: 1.3rem;
    font-weight: 800;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px auto;
    border: 2px solid hsl(var(--clr-primary));
}

/* Advantages Split layout styling */
.adv-split-grid {
    display: grid;
    grid-template-columns: 1fr 1.1fr;
    gap: 60px;
    align-items: center;
    padding: 70px 0;
}
@media (max-width: 991px) {
    .adv-split-grid {
        grid-template-columns: 1fr;
        gap: 40px;
    }
}
.adv-list {
    list-style: none;
    margin-top: 25px;
}
.adv-list li {
    position: relative;
    padding-left: 32px;
    margin-bottom: 20px;
    text-align: left;
}
.adv-list li::before {
    content: '✓';
    position: absolute;
    left: 0;
    top: 0;
    width: 22px;
    height: 22px;
    background-color: hsl(var(--clr-primary-light));
    color: hsl(var(--clr-primary));
    font-weight: 800;
    font-size: 0.85rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
}
.adv-list h4 {
    margin-bottom: 4px;
    font-size: 1.1rem;
    font-weight: 700;
    color: #111111;
}
.adv-list p {
    font-size: 0.9rem;
    margin: 0;
    color: hsl(var(--clr-text-muted));
}

/* Facility columns section */
.facility-cols-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 30px;
    margin-top: 40px;
}
.facility-col-card {
    background-color: #FFFFFF;
    border-radius: var(--radius-md);
    border: 1px solid hsl(var(--clr-border));
    padding: 40px 30px;
    box-shadow: var(--shadow-sm);
    text-align: left;
    transition: box-shadow 0.3s ease, border-color 0.3s ease;
}
.facility-col-card:hover {
    box-shadow: var(--shadow-md);
    border-color: hsl(var(--clr-primary));
}
.facility-col-header {
    display: flex;
    align-items: center;
    gap: 15px;
    border-bottom: 2px solid hsl(var(--clr-border));
    padding-bottom: 20px;
    margin-bottom: 25px;
}
.facility-col-icon {
    font-size: 2rem;
    color: hsl(var(--clr-primary));
    background-color: hsl(var(--clr-primary-light));
    width: 60px;
    height: 60px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
}
.facility-col-header h3 {
    margin: 0;
    font-size: 1.35rem;
    font-weight: 800;
}
.facility-list {
    list-style: none;
}
.facility-list li {
    position: relative;
    padding-left: 28px;
    margin-bottom: 14px;
    font-size: 0.95rem;
    color: hsl(var(--clr-text-muted));
}
.facility-list li::before {
    content: "•";
    position: absolute;
    left: 8px;
    color: hsl(var(--clr-primary));
    font-size: 1.5rem;
    line-height: 1;
    top: -2px;
}

/* Pricing Grid Custom CSS */
.pricing-sec {
    background-color: hsl(var(--clr-bg-secondary));
    border-top: 1px solid hsl(var(--clr-border));
    border-bottom: 1px solid hsl(var(--clr-border));
    padding: 80px 0;
}
.pricing-grid-custom {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-top: 40px;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
    align-items: start !important;
}
@media (max-width: 991px) {
    .pricing-grid-custom {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    }
}
.pricing-card-custom {
    background-color: #FFFFFF;
    border-radius: var(--radius-md);
    border: 2px solid hsl(var(--clr-border));
    width: 100%;
    max-width: 100%;
    padding: 30px 20px;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    min-height: 350px;
}
.pricing-card-custom:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
    border-color: hsl(var(--clr-primary));
}
.pricing-card-custom.popular-card {
    border-color: hsl(var(--clr-primary));
}
.pricing-badge-popular {
    position: absolute;
    top: -12px;
    right: -8px;
    background-color: hsl(var(--clr-accent));
    color: #111111;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 5px 14px;
    border-radius: var(--radius-full);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    box-shadow: 0 4px 10px rgba(255, 107, 0, 0.2);
    z-index: 10;
    white-space: nowrap;
}
.pricing-card-header h3 {
    font-size: 1.15rem;
    font-weight: 800;
    color: hsl(var(--clr-primary));
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.pricing-card-price {
    font-size: 1.85rem;
    font-weight: 800;
    color: #111111;
    margin: 10px 0;
}
.pricing-card-price span {
    font-size: 0.9rem;
    font-weight: 400;
    color: hsl(var(--clr-text-muted));
}
.pricing-card-desc {
    font-size: 0.8rem;
    color: hsl(var(--clr-text-muted));
    line-height: 1.4;
    margin-bottom: 20px;
    border-bottom: 1px solid hsl(var(--clr-border));
    padding-bottom: 12px;
}
.pricing-features-list {
    list-style: none;
    margin-bottom: 25px;
}
.pricing-features-list li {
    position: relative;
    padding-left: 22px;
    margin-bottom: 8px;
    font-size: 0.82rem;
    color: hsl(var(--clr-text-muted));
    text-align: left;
}
.pricing-features-list li::before {
    content: "✓";
    position: absolute;
    left: 0;
    color: hsl(var(--clr-primary));
    font-weight: 800;
    font-size: 1rem;
}
.pricing-features-list li.sub-item {
    font-weight: 600;
    color: #111111;
}



/* Pricing list card details toggle styling */
.pricing-features-collapse {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
}
.btn-toggle-features {
    background: none;
    border: none;
    color: hsl(var(--clr-primary));
    font-weight: 700;
    font-size: 0.82rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
    margin: 15px 0 10px auto;
    padding: 0;
    width: auto;
    transition: color 0.2s ease;
}
.btn-toggle-features:hover {
    color: hsl(var(--clr-primary-dark, var(--clr-primary)));
    text-decoration: underline;
    background: none;
}
.btn-toggle-features i {
    transition: transform 0.2s ease;
    display: inline-block;
}
.btn-toggle-features.active i {
    transform: rotate(180deg);
}

.btn-wa-icon {
    width: 48px !important;
    height: 48px !important;
    min-width: 48px !important;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-md) !important;
    font-size: 1.25rem !important;
    transition: all 0.2s ease !important;
}
.btn-wa-icon:hover {
    border-color: #25d366 !important;
    background-color: #25d366 !important;
    color: #FFFFFF !important;
    box-shadow: 0 8px 20px rgba(37, 211, 102, 0.2) !important;
    transform: translateY(-2px) !important;
}

/* ===== Mobile Responsive Fixes ===== */
@media (max-width: 768px) {
    .bundle-hero { padding: 70px 0 30px 0 !important; margin-top: 60px !important; }
    .hero-grid { grid-template-columns: 1fr; gap: 24px !important; text-align: center !important; }

    .hero-content .badge { font-size: 0.7rem !important; padding: 4px 10px !important; display: block !important; width: fit-content !important; margin: 0 auto 15px 0 !important; }
    .hero-content h1 { font-size: 1.6rem !important; margin-bottom: 12px !important; }
    .hero-content p { font-size: 0.9rem !important; line-height: 1.6 !important; margin-bottom: 20px !important; }
    .hero-ctas { justify-content: flex-start !important; gap: 8px !important; margin-top: 15px !important; }
    .hero-ctas .btn {
        padding: 6px 10px !important;
        font-size: 0.7rem !important;
        width: auto !important;
    }
    
    .hero-img-wrap-mobile {
        max-width: 360px !important;
        margin: 15px auto !important;
    }
    .hero-floating-card {
        bottom: 10px !important;
        right: 10px !important;
        width: 60% !important;
        max-width: 170px !important;
        padding: 8px !important;
        border-radius: 6px !important;
    }
    .hero-floating-card .floating-card-title { font-size: 7.5px !important; }
    .hero-floating-card .floating-card-desc { font-size: 7px !important; line-height: 1.25 !important; }

    .steps-grid { grid-template-columns: 1fr; gap: 20px; }
    .step-card { padding: 20px; }
    .adv-split-grid { grid-template-columns: 1fr; gap: 30px; }
    .facility-cols-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
    .pricing-sec { padding: 50px 0; }
    .pricing-grid-custom { grid-template-columns: 1fr; gap: 24px; }
    .pricing-card-custom { padding: 24px; }
    .pricing-card-custom h3 { font-size: 1.3rem; }
    .pricing-card-custom .price-tag { font-size: 1.8rem; }
    .loc-card { flex-direction: column; }
}

@media (max-width: 576px) {
    .bundle-hero { padding: 50px 0 20px 0 !important; }
    .hero-grid { text-align: center !important; }
    .hero-content .badge { font-size: 0.65rem !important; padding: 3px 8px !important; }
    .hero-content h1 { font-size: 1.4rem !important; }
    .hero-content p { font-size: 0.85rem !important; line-height: 1.5 !important; }
    .hero-ctas { justify-content: flex-start !important; gap: 6px !important; flex-wrap: nowrap !important; }
    .hero-ctas .btn {
        padding: 5px 8px !important;
        font-size: 0.65rem !important;
        width: auto !important;
    }
    .hero-img-wrap-mobile { max-width: 300px !important; margin: 15px auto !important; }
    .hero-floating-card {
        bottom: 8px !important;
        right: 8px !important;
        width: 58% !important;
        max-width: 150px !important;
        padding: 6px 8px !important;
    }
    .hero-floating-card .floating-card-title { font-size: 7px !important; }
    .hero-floating-card .floating-card-desc { font-size: 6.5px !important; }
    .steps-section { padding: 40px 0; }
    .step-card { padding: 16px; }
    .step-card h4 { font-size: 1rem; }
    .facility-cols-grid { grid-template-columns: 1fr; gap: 14px; }
    .pricing-sec { padding: 40px 0; }
    
    /* Mobile responsive pricing card slider improvements */
    .pricing-grid-custom {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        justify-content: flex-start !important;
        align-items: stretch !important;
        overflow-x: auto !important;
        scroll-snap-type: x mandatory !important;
        -webkit-overflow-scrolling: touch !important;
        gap: 16px !important;
        padding-bottom: 24px !important;
        margin-left: -16px !important;
        margin-right: -16px !important;
        padding-left: 10% !important;
        padding-right: 10% !important;
        scrollbar-width: none !important;
    }
    .pricing-grid-custom.has-expanded {
        align-items: flex-start !important;
    }
    .pricing-grid-custom::-webkit-scrollbar {
        display: none !important;
    }
    .pricing-card-custom {
        flex: 0 0 80% !important;
        max-width: 80% !important;
        min-width: 80% !important;
        scroll-snap-align: center !important;
        padding: 28px 22px !important;
        min-height: auto !important;
        height: auto !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important; /* Ensure CTA content aligns nicely when stretched */
    }
    
    /* Price and text size enhancements on mobile */
    .pricing-card-custom .pricing-card-header h3 {
        font-size: 1.2rem !important;
        margin-bottom: 12px !important;
    }
    .pricing-card-custom .pricing-card-price {
        font-size: 1.5rem !important;
        font-weight: 800 !important;
        margin: 12px 0 !important;
        color: #111111 !important;
    }
    .pricing-card-custom .pricing-card-price span {
        font-size: 0.85rem !important;
        color: hsl(var(--clr-text-muted)) !important;
    }
    .pricing-card-custom .pricing-card-desc {
        font-size: 0.82rem !important;
        line-height: 1.5 !important;
        margin-bottom: 20px !important;
        padding-bottom: 16px !important;
    }
    .pricing-card-custom .pricing-features-list li {
        font-size: 0.82rem !important;
        line-height: 1.5 !important;
    }
    .pricing-card-custom .pricing-features-list li.sub-item {
        font-size: 0.85rem !important;
        margin-top: 18px !important;
    }
    .pricing-card-custom .btn-toggle-features {
        font-size: 0.82rem !important;
        padding: 6px 12px !important;
        margin-top: 15px !important;
    }
    .pricing-card-custom .btn {
        font-size: 0.85rem !important;
        padding: 12px 16px !important;
    }
    .pricing-card-custom .btn-wa-icon {
        padding: 12px !important;
    }
    .loc-card { padding: 0 !important; }
}
</style>

<!-- Hero Section -->
<section class="bundle-hero">
    <div class="container">
        <div class="hero-grid">
            <!-- Left Text Box -->
            <div class="hero-content">
                <span class="badge" style="background-color: hsl(var(--clr-primary-light)); color: hsl(var(--clr-primary)); border: 1px solid hsl(var(--clr-primary));">
                    Legalitas PT PMA + VO
                </span>
                <h1>Pendirian PT PMA + Virtual Office</h1>
                
                <!-- Mobile Hero Image -->
                <div class="hero-img-wrap-mobile">
                    <img src="<?php echo BASE_URL; ?>assets/images/privateoffice/private-office-corporate.webp" alt="Urban Office Corporate Office">
                    <div class="hero-floating-card">
                        <p class="floating-card-title">Compliant & Professional Setup</p>
                        <p class="floating-card-desc">Full corporate compliance according to BKPM and Ministry of Law & Human Rights (Kemenkumham) standards.</p>
                    </div>
                </div>

                <p>
                    Establish your foreign-owned enterprise (PT PMA) in Indonesia seamlessly. Get a complete legal setup combined with a prestigious Virtual Office address in premium CBD areas for compliance, tax, and operational requirements.
                </p>
                <div class="hero-ctas">
                    <a href="#pricing" class="btn btn-primary">Lihat Pilihan Paket</a>
                    <a href="https://api.whatsapp.com/send?phone=6285107620100&text=Halo%20Urban%20Office%2C%20saya%20tertarik%20dengan%20paket%20Bundling%20PT%20PMA%20%2B%20Virtual%20Office.%20Mohon%20info%20selengkapnya." 
                       target="_blank" class="btn btn-outline" style="background-color: #FFFFFF;">
                        <i class="bi bi-whatsapp" style="margin-right: 5px;"></i> Hubungi Kami
                    </a>
                </div>
            </div>
            
            <!-- Right Office Image Representation -->
            <div class="hero-img-wrap-desktop">
                <img src="<?php echo BASE_URL; ?>assets/images/privateoffice/private-office-corporate.webp" alt="Urban Office Corporate Office">
                <div class="hero-floating-card">
                    <p class="floating-card-title">Compliant & Professional Setup</p>
                    <p class="floating-card-desc">Full corporate compliance according to BKPM and Ministry of Law & Human Rights (Kemenkumham) standards.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>


<!-- Steps Workflow Section -->
<section class="steps-section">
    <div class="container text-center">
        <h2 class="section-title">Langkah Mudah Pendirian PT PMA & VO</h2>
        <p class="section-subtitle">Dapatkan legalitas PT PMA resmi beserta alamat Virtual Office untuk kepatuhan hukum dalam 3 langkah mudah.</p>
        
        <div class="steps-grid">
            <!-- Step 1 -->
            <div class="step-card">
                <div class="step-number-circle">1</div>
                <h3 style="font-size: 1.15rem; margin-bottom: 10px; font-weight: 700;">Konsultasi & Strukturisasi</h3>
                <p style="font-size: 0.85rem; color: hsl(var(--clr-text-muted)); line-height: 1.6; margin: 0;">
                    Hubungi konsultan kami, review persentase kepemilikan saham asing berdasarkan KBLI (BKPM), serta tentukan cabang kantor virtual.
                </p>
            </div>
            
            <!-- Step 2 -->
            <div class="step-card">
                <div class="step-number-circle">2</div>
                <h3 style="font-size: 1.15rem; margin-bottom: 10px; font-weight: 700;">Penyusunan Akta & Modal</h3>
                <p style="font-size: 0.85rem; color: hsl(var(--clr-text-muted)); line-height: 1.6; margin: 0;">
                    Kirimkan paspor pemegang saham (asing) / KTP pengurus (lokal). Kami menyusun draf akta notaris, rencana investasi, dan memproses penandatanganan.
                </p>
            </div>
            
            <!-- Step 3 -->
            <div class="step-card">
                <div class="step-number-circle">3</div>
                <h3 style="font-size: 1.15rem; margin-bottom: 10px; font-weight: 700;">Legalitas Terbit & Aktif</h3>
                <p style="font-size: 0.85rem; color: hsl(var(--clr-text-muted)); line-height: 1.6; margin: 0;">
                    Akta Notaris, SK Kemenkumham, NPWP PMA, NIB, & surat sewa VO selesai diterbitkan. Bisnis Anda siap beroperasi penuh dan mensponsori visa/KITAS!
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Advantages / Keuntungan Section -->
<section class="section" style="background-color: #FFFFFF;">
    <div class="container">
        <div class="adv-split-grid">
            <!-- Left Hand side visual representation -->
            <div style="border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-md); border: 1px solid hsl(var(--clr-border));">
                <img src="<?php echo BASE_URL; ?>assets/images/privateoffice/serviced-office-desk.png" alt="Why Choose PT PMA" style="width: 100%; height: auto; display: block; object-fit: cover; aspect-ratio: 4/3;">
            </div>
            
            <!-- Right Hand list of details -->
            <div style="text-align: left;">
                <span class="badge" style="background-color: hsl(var(--clr-primary-light)); color: hsl(var(--clr-primary));">PMA Legal Entity</span>
                <h2 style="font-weight: 800; font-size: 2rem; margin-bottom: 15px;">Mengapa Mendirikan PT PMA?</h2>
                <p style="color: hsl(var(--clr-text-muted)); font-size: 0.95rem; line-height: 1.6;">
                    Perseroan Terbatas Penanaman Modal Asing (PT PMA) merupakan satu-satunya badan usaha legal yang memungkinkan investor asing melakukan bisnis komersial secara penuh dan memiliki aset legal di Indonesia.
                </p>
                
                <ul class="adv-list">
                    <li>
                        <h4>Kepemilikan Asing Hingga 100%</h4>
                        <p>Memungkinkan investor luar negeri menguasai persentase saham mayoritas hingga 100% penuh (tergantung ketentuan bidang usaha KBLI).</p>
                    </li>
                    <li>
                        <h4>Sponsor Visa & KITAS Investor</h4>
                        <p>PT PMA berhak secara sah mengajukan izin kerja, visa bisnis, dan mensponsori kartu tinggal terbatas (KITAS Kerja/Investor) bagi ekspatriat.</p>
                    </li>
                    <li>
                        <h4>Kemudahan Kepemilikan Aset</h4>
                        <p>Perusahaan dapat mengajukan hak sewa gedung, hak pakai, dan hak guna bangunan (HGB) atas nama badan hukum PMA secara resmi.</p>
                    </li>
                    <li>
                        <h4>Skala Bisnis Internasional</h4>
                        <p>Sangat tepercaya untuk transaksi perdagangan ekspor-impor, kemitraan global, serta memperoleh fasilitas perlindungan investasi asing.</p>
                    </li>
                </ul>
                
                <!-- Highlight Banner -->
                <div style="background-color: hsl(var(--clr-primary-light)); border-left: 4px solid hsl(var(--clr-primary)); padding: 20px; border-radius: var(--radius-sm); margin-top: 30px;">
                    <p style="margin: 0; font-weight: 700; color: #111111; font-size: 0.95rem; line-height: 1.5;">
                        Get foreign investment authorization, standard notarial deeds, and commercial office domicile in a highly cost-efficient package.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Included Facilities Section -->
<section class="section" style="background-color: hsl(var(--clr-bg-secondary)); border-top: 1px solid hsl(var(--clr-border)); border-bottom: 1px solid hsl(var(--clr-border));">
    <div class="container text-center">
        <h2 class="section-title">Persyaratan & Kelengkapan Legalitas</h2>
        <p class="section-subtitle">Dokumen yang perlu disiapkan dan kelengkapan legalitas resmi yang akan Anda dapatkan.</p>
        
        <div class="facility-cols-grid">
            <!-- Document Requirements Card -->
            <div class="facility-col-card">
                <div class="facility-col-header">
                    <div class="facility-col-icon">📋</div>
                    <div>
                        <h3>Dokumen Persyaratan</h3>
                        <p style="font-size: 0.8rem; color: hsl(var(--clr-text-muted)); margin: 0;">Dokumen yang Perlu Disiapkan</p>
                    </div>
                </div>
                <ul class="facility-list">
                    <li>Nama PT</li>
                    <li>Modal Dasar (Min. 10 Miliar)</li>
                    <li>Modal Disetor (Min. 10 Miliar)</li>
                    <li>Bidang Usaha</li>
                    <li>KTP & NPWP Pengurus PT</li>
                    <li>KTP & NPWP Pemegang Saham (Minimal 1 Orang WNA)</li>
                    <li>Komposisi Pemegang Saham</li>
                    <li>Alamat Lengkap PT</li>
                    <li>No Telephone PT</li>
                    <li>Alamat Email dan Password</li>
                </ul>
            </div>
            
            <!-- PT PMA Features Card -->
            <div class="facility-col-card">
                <div class="facility-col-header">
                    <div class="facility-col-icon">📄</div>
                    <div>
                        <h3>PT PMA Usaha</h3>
                        <p style="font-size: 0.8rem; color: hsl(var(--clr-text-muted)); margin: 0;">Dokumen Legalitas Resmi</p>
                    </div>
                </div>
                <ul class="facility-list">
                    <li>Akta Pendirian</li>
                    <li>Surat Keputusan (SK) Pengesahan Badan Hukum</li>
                    <li>NIB Perusahaan</li>
                    <li>Sertifikat Standar atau Izin Usaha</li>
                    <li>NPWP Perusahaan</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Bundle Cards -->
<section class="pricing-sec" id="pricing">
    <div class="container text-center">
        <h2 class="section-title">Pilihan Paket Harga Terbaik</h2>
        <p class="section-subtitle">Pilih layanan murni jasa atau paket bundling hemat lengkap dengan alamat Virtual Office.</p>
        
        <?php
        $pma_packages = [
            // Pendirian PMA
            [
                'name' => 'Jasa Pendirian PMA',
                'price' => 'Hubungi Admin',
                'desc' => 'Hanya jasa Pendirian PT PMA, tanpa Virtual Office.',
                'features_legal' => ['Akta Notaris Resmi & SK Kemenkumham', 'NPWP Perusahaan PMA & SKT', 'NIB Perusahaan & Kode Akses OSS', 'Sertifikat Standar (KBLI Risiko Rendah)'],
                'features_vo' => [],
                'popular' => false,
                'wa_text' => 'Halo Urban Office, saya tertarik paket Jasa Pendirian PMA.'
            ],
            [
                'name' => 'PT PMA + VO Starter',
                'price' => 'Hubungi Admin',
                'desc' => 'Solusi efisien pengurusan izin PT PMA lengkap dengan alamat Virtual Office Starter selama 1 tahun.',
                'features_legal' => ['Akta Notaris Resmi & SK Kemenkumham', 'NPWP Perusahaan PMA & SKT', 'NIB Perusahaan & Kode Akses OSS', 'Sertifikat Standar (KBLI Risiko Rendah)'],
                'features_vo' => ['Alamat Bisnis Prestisius', 'Penanganan Surat & Paket Masuk', 'Notifikasi Instan via WA/Email', 'Surat Keterangan Domisili', 'Meeting Room: 2 x 2 Jam / Bulan'],
                'popular' => false,
                'wa_text' => 'Halo Urban Office, saya tertarik paket PT PMA + VO Starter.'
            ],
            [
                'name' => 'PT PMA + VO Luxury',
                'price' => 'Hubungi Admin',
                'desc' => 'Paket ideal PMA untuk operasional aktif, dilengkapi kuota ruang rapat bulanan dan nomor telepon.',
                'features_legal' => ['Akta Notaris Resmi & SK Kemenkumham', 'NPWP Perusahaan PMA & SKT', 'NIB Perusahaan & Kode Akses OSS', 'Sertifikat Standar (KBLI Risiko Rendah)'],
                'features_vo' => ['Alamat Bisnis Prestisius', 'Penanganan Surat & Paket', 'Surat Keterangan Domisili', 'Meeting Room: 2 x 3 Jam / Bulan', 'Nomor Telepon Kantor Bersama'],
                'popular' => true,
                'wa_text' => 'Halo Urban Office, saya tertarik paket PT PMA + VO Luxury.'
            ],
            [
                'name' => 'PT PMA + VO Priority',
                'price' => 'Hubungi Admin',
                'desc' => 'Paket prioritas lengkap untuk bisnis berkembang, gratis konsultasi pajak/layanan ekstra.',
                'features_legal' => ['Akta Notaris Resmi & SK Kemenkumham', 'NPWP Perusahaan PMA & SKT', 'NIB Perusahaan & Kode Akses OSS', 'Sertifikat Standar (KBLI Risiko Rendah)'],
                'features_vo' => ['Alamat Bisnis Prestisius', 'Penanganan Surat & Paket', 'Meeting Room: 2 x 4 Jam / Bulan', 'Nomor Telepon Kantor Bersama', 'Bonus: Tax Starter Kit / Lainnya'],
                'popular' => false,
                'wa_text' => 'Halo Urban Office, saya tertarik paket PT PMA + VO Priority.'
            ]
        ];
        
        $jasa_packages = array_filter($pma_packages, function($p) { return empty($p['features_vo']); });
        $bundling_packages = array_filter($pma_packages, function($p) { return !empty($p['features_vo']); });
        
        // Define a function to render card to avoid duplicating large HTML block
        if (!function_exists('render_card_legalitas')) {
            function render_card_legalitas($pkg, $type_label) {
                ?>
                <div class="pricing-card-custom <?php echo $pkg['popular'] ? 'popular-card' : ''; ?>">
                    <?php if ($pkg['popular']): ?>
                    <span class="pricing-badge-popular">Populer</span>
                    <?php endif; ?>
                    <div>
                        <div class="pricing-card-header">
                            <h3 style="font-size:1.05rem;"><?php echo $pkg['name']; ?></h3>
                        </div>
                        
                        <div class="pricing-card-price" style="font-size:1.4rem;">
                            <?php echo $pkg['price']; ?>
                        </div>
                        
                        <p class="pricing-card-desc">
                            <?php echo $pkg['desc']; ?>
                        </p>
                        
                        <button class="btn-toggle-features" onclick="togglePricingFeatures(this)">
                            <span>Lihat Detail</span> <i class="bi bi-chevron-down"></i>
                        </button>
                        
                        <div class="pricing-features-collapse">
                            <ul class="pricing-features-list">
                                <li class="sub-item"><?php echo $type_label; ?>:</li>
                                <?php foreach($pkg['features_legal'] as $f): ?>
                                <li><?php echo $f; ?></li>
                                <?php endforeach; ?>
                                
                                <?php if(!empty($pkg['features_vo'])): ?>
                                <li class="sub-item" style="margin-top: 15px;">Fasilitas Virtual Office:</li>
                                <?php foreach($pkg['features_vo'] as $f): ?>
                                <li><?php echo $f; ?></li>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px; width: 100%; align-items: center; margin-top:20px;">
                        <a href="#contact" class="btn btn-primary" style="flex: 1; text-align: center; justify-content: center; font-size:0.85rem; padding:10px;" onclick="activateOfferMode('<?php echo sanitize($pkg['name']); ?>', 'Pendirian Legalitas')">
                            Hubungi Kami
                        </a>
                        <a href="https://api.whatsapp.com/send?phone=6285107620100&text=<?php echo urlencode($pkg['wa_text']); ?>" 
                           target="_blank" class="btn btn-outline btn-wa-icon" title="Tanya via WhatsApp" style="width:40px !important; height:40px !important; min-width:40px !important;">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                </div>
                <?php
            }
        }
        ?>

        <!-- Bagian Bundling VO -->
        <h3 class="section-title" style="font-size: 1.5rem; margin-top: 30px; margin-bottom: 20px; color: hsl(var(--clr-primary));">Paket Bundling (Termasuk Virtual Office 1 Tahun)</h3>
        <div class="pricing-grid-custom" style="margin-bottom: 40px;">
            <?php 
            foreach ($bundling_packages as $pkg) {
                render_card_legalitas($pkg, 'Legalitas PT PMA');
            }
            ?>
        </div>

        <hr style="border-top: 1px solid #eaeaea; margin: 40px 0;">

        <!-- Bagian Jasa Saja -->
        <h3 class="section-title" style="font-size: 1.5rem; margin-top: 20px; margin-bottom: 20px; color: hsl(var(--clr-primary));">Pilihan Layanan Jasa (Tanpa Virtual Office)</h3>
        <div class="pricing-grid-custom" style="justify-content: center;">
            <?php 
            foreach ($jasa_packages as $pkg) {
                render_card_legalitas($pkg, 'Legalitas PT PMA');
            }
            ?>
        </div>
    </div>
</section>

<!-- Locations Showcase Section -->
<?php
$package_name = 'PT PMA';
include DIR_ROOT . 'inc/components/locations_showcase.php';
?>

<!-- FAQ Section -->
<?php
$faqs = [
    [
        'question' => 'Berapa lama proses pendirian PT / CV selesai?',
        'answer' => 'Proses pendirian PT / CV kurang lebih membutuhkan waktu 1 minggu setelah semua persyaratan dokumen dinyatakan lengkap.'
    ],
    [
        'question' => 'Dokumen apa saja yang dibutuhkan untuk mendirikan PT PMA?',
        'answer' => 'Dokumen dan data yang diperlukan untuk pendirian PT PMA meliputi: nama PT, modal dasar minimal 10 Miliar, modal disetor minimal 10 Miliar, bidang usaha (KBLI), KTP & NPWP pengurus PT, KTP/Paspor & NPWP pemegang saham (minimal 1 orang Warga Negara Asing), komposisi pemegang saham, alamat lengkap PT, nomor telepon PT, serta alamat email dan password.'
    ],
    [
        'question' => 'Apakah bisa konsultasi terlebih dahulu sebelum memutuskan jenis badan usaha?',
        'answer' => 'Tentu saja bisa. Anda dapat melakukan konsultasi gratis terlebih dahulu dengan tim legal kami untuk menentukan jenis badan usaha yang paling tepat untuk model bisnis Anda.'
    ]
];
include dirname(dirname(__FILE__)) . '/inc/components/faq.php';
?>

<!-- Lead & Contact Form component -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
