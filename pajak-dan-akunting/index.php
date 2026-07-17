<?php
/**
 * Urban Office - Laporan Pajak dan Akunting Landing Page
 */

$page_slug = 'pajak-dan-akunting';
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

/* Layanan Kami section styling */
.layanan-kami-sec {
    padding: 80px 0;
    background-color: hsl(var(--clr-bg-secondary));
    border-top: 1px solid hsl(var(--clr-border));
    border-bottom: 1px solid hsl(var(--clr-border));
}
.services-3col-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    margin-top: 40px;
}
@media (max-width: 991px) {
    .services-3col-grid {
        grid-template-columns: 1fr;
    }
}
.service-box-card {
    background-color: #FFFFFF;
    border-radius: var(--radius-md);
    border: 1px solid hsl(var(--clr-border));
    padding: 40px 30px;
    box-shadow: var(--shadow-sm);
    text-align: left;
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.service-box-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
    border-color: hsl(var(--clr-primary));
}
.service-box-icon {
    font-size: 2.25rem;
    color: hsl(var(--clr-primary));
    background-color: hsl(var(--clr-primary-light));
    width: 65px;
    height: 65px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 25px;
}
.service-box-card h3 {
    font-size: 1.25rem;
    font-weight: 800;
    margin-bottom: 12px;
    color: #111111;
}
.service-box-card p {
    font-size: 0.9rem;
    color: hsl(var(--clr-text-muted));
    line-height: 1.6;
    margin-bottom: 25px;
}

/* Timeline/Kalender Pajak styling */
.calendar-sec {
    padding: 80px 0;
    background-color: #FFFFFF;
}
.timeline-flow {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-top: 50px;
    position: relative;
}
.timeline-flow::before {
    content: '';
    position: absolute;
    top: 65px;
    left: 12%;
    right: 12%;
    height: 3px;
    background: linear-gradient(90deg, hsl(var(--clr-primary-light)) 0%, hsl(var(--clr-primary)) 50%, hsl(var(--clr-primary-light)) 100%);
    z-index: 1;
}
@media (max-width: 991px) {
    .timeline-flow {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    .timeline-flow::before {
        display: none;
    }
}
.timeline-node {
    background: #FFFFFF;
    border-radius: var(--radius-md);
    padding: 30px 20px;
    border: 1px solid hsl(var(--clr-border));
    box-shadow: var(--shadow-sm);
    text-align: center;
    position: relative;
    z-index: 2;
    transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
}
.timeline-node:hover {
    transform: translateY(-5px);
    border-color: hsl(var(--clr-primary));
    box-shadow: var(--shadow-md);
}
.node-circle {
    width: 70px;
    height: 70px;
    border-radius: var(--radius-full);
    background-color: #FFFFFF;
    color: hsl(var(--clr-primary));
    border: 3.5px solid hsl(var(--clr-primary));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    font-weight: 800;
    margin: 0 auto 20px auto;
    box-shadow: 0 4px 12px rgba(255, 107, 0, 0.15);
    z-index: 3;
    position: relative;
}
.timeline-node h3 {
    font-size: 1.1rem;
    font-weight: 800;
    margin-bottom: 8px;
    color: #111111;
}
.timeline-node p {
    font-size: 0.82rem;
    color: hsl(var(--clr-text-muted));
    line-height: 1.5;
    margin: 0;
}

/* Situasi Masalah Pajak styling */
.problems-sec {
    padding: 80px 0;
    background-color: hsl(var(--clr-bg-secondary));
    border-top: 1px solid hsl(var(--clr-border));
    border-bottom: 1px solid hsl(var(--clr-border));
}
.problems-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    margin-top: 40px;
}
@media (max-width: 991px) {
    .problems-grid {
        grid-template-columns: 1fr;
    }
}
.problem-card {
    background-color: #FFFFFF;
    border-radius: var(--radius-md);
    border: 1px solid hsl(var(--clr-border));
    border-top: 4px solid #dd2c00;
    padding: 35px 25px;
    box-shadow: var(--shadow-sm);
    text-align: left;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.problem-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}
.problem-card-icon {
    font-size: 2rem;
    color: #dd2c00;
    margin-bottom: 15px;
    display: block;
}
.problem-card h3 {
    font-size: 1.15rem;
    font-weight: 800;
    color: #111111;
    margin-bottom: 10px;
}
.problem-card p {
    font-size: 0.85rem;
    color: hsl(var(--clr-text-muted));
    line-height: 1.6;
    margin: 0;
}

/* Split layout Layanan Profesional styling */
.pro-service-sec {
    padding: 80px 0;
    background-color: #FFFFFF;
}
.pro-service-grid {
    display: grid;
    grid-template-columns: 1fr 1.1fr;
    gap: 60px;
    align-items: center;
}
@media (max-width: 991px) {
    .pro-service-grid {
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
    gap: 24px;
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
    padding: 24px 20px;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    min-height: 560px;
}
.pricing-card-custom:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
    border-color: hsl(var(--clr-primary));
}
.pricing-card-custom.popular-card {
    border-color: hsl(var(--clr-primary));
}
.pricing-card-custom > div > div:first-child {
    margin-top: -24px !important;
    margin-left: -20px !important;
    margin-right: -20px !important;
    border-top-left-radius: var(--radius-md) !important;
    border-top-right-radius: var(--radius-md) !important;
    border-bottom-left-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
    border: none !important;
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
    font-size: 1.25rem;
    font-weight: 800;
    color: #111111;
    margin-bottom: 10px;
    letter-spacing: 0.01em;
}
.pricing-card-price {
    font-size: 1.75rem;
    font-weight: 800;
    color: hsl(var(--clr-primary));
    margin: 10px 0;
}
.pricing-card-price span {
    font-size: 0.85rem;
    font-weight: 400;
    color: hsl(var(--clr-text-muted));
}
.pricing-card-desc {
    font-size: 0.82rem;
    color: hsl(var(--clr-text-muted));
    line-height: 1.5;
    margin-bottom: 15px;
    border-bottom: 1px solid hsl(var(--clr-border));
    padding-bottom: 12px;
    min-height: 55px;
}
.pricing-features-list {
    list-style: none;
    margin-bottom: 15px;
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

/* Side-by-side buttons */
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
    border: 2px solid #FF6B00 !important;
    color: #FF6B00 !important;
    background-color: transparent !important;
}
.btn-wa-icon:hover {
    border-color: #25d366 !important;
    background-color: #25d366 !important;
    color: #FFFFFF !important;
    box-shadow: 0 8px 20px rgba(37, 211, 102, 0.2) !important;
    transform: translateY(-2px) !important;
}

/* Layanan Lainnya styling */
.other-services-sec {
    padding: 80px 0;
    background-color: #FFFFFF;
}
.other-services-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-top: 40px;
}
@media (max-width: 991px) {
    .other-services-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 576px) {
    .other-services-grid {
        grid-template-columns: 1fr;
    }
}
.other-service-card {
    background: hsl(var(--clr-bg-secondary));
    border: 1px solid hsl(var(--clr-border));
    border-radius: var(--radius-md);
    padding: 25px 20px;
    text-align: center;
    transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.other-service-card:hover {
    transform: translateY(-4px);
    border-color: hsl(var(--clr-primary));
    box-shadow: var(--shadow-sm);
}
.other-service-card i {
    font-size: 1.75rem;
    color: hsl(var(--clr-primary));
    margin-bottom: 15px;
    display: block;
}
.other-service-card h4 {
    font-size: 0.95rem;
    font-weight: 700;
    margin-bottom: 8px;
    color: #111111;
}
.other-service-card p {
    font-size: 0.8rem;
    color: hsl(var(--clr-text-muted));
    line-height: 1.4;
    margin-bottom: 15px;
}

/* Brands section and featured on section styling overrides */
.brands-sec-landing {
    padding: 60px 0;
    background-color: hsl(var(--clr-bg-secondary));
    border-top: 1px solid hsl(var(--clr-border));
}
.brands-grid-landing {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 20px;
    align-items: center;
    margin-top: 30px;
}
@media (max-width: 768px) {
    .brands-grid-landing {
        grid-template-columns: repeat(3, 1fr);
    }
}
.brand-logo-landing {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 10px;
}
.brand-logo-landing img {
    max-height: 35px;
    width: auto;
    filter: grayscale(1);
    opacity: 0.6;
    transition: filter 0.3s ease, opacity 0.3s ease;
}
.brand-logo-landing img:hover {
    filter: grayscale(0);
    opacity: 1;
}

.featured-grid-landing {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 20px;
    align-items: center;
    margin-top: 30px;
    border-top: 1px dashed hsl(var(--clr-border));
    padding-top: 30px;
}
@media (max-width: 768px) {
    .featured-grid-landing {
        grid-template-columns: repeat(3, 1fr);
    }
}
.featured-logo-landing {
    display: flex;
    justify-content: center;
    align-items: center;
}
.featured-logo-landing img {
    max-height: 30px;
    width: auto;
    filter: grayscale(1);
    opacity: 0.5;
    transition: filter 0.3s ease, opacity 0.3s ease;
}
.featured-logo-landing img:hover {
    filter: grayscale(0);
    opacity: 0.9;
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

    .services-3col-grid { grid-template-columns: 1fr; gap: 20px; }
    .timeline-flow { grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .problems-grid { grid-template-columns: 1fr; gap: 20px; }
    .pro-service-grid { grid-template-columns: 1fr; gap: 30px; }
    .pro-service-mobile-img { display: block !important; max-width: 280px !important; margin: 0 auto 20px auto !important; }
    .pro-service-mobile-img img { width: 100% !important; aspect-ratio: 3/2 !important; object-fit: cover !important; display: block !important; }
    .pro-service-desktop-img { display: none !important; }
    .pro-service-sec { padding: 45px 0 !important; }
    .pro-service-sec h2 { font-size: 1.35rem !important; margin-bottom: 12px !important; }
    .pro-service-sec p { font-size: 0.82rem !important; line-height: 1.5 !important; }
    .adv-list { margin-top: 15px !important; }
    .adv-list li { padding-left: 24px !important; margin-bottom: 15px !important; }
    .adv-list li::before { width: 18px !important; height: 18px !important; font-size: 0.75rem !important; line-height: 18px !important; }
    .adv-list li h4 { font-size: 0.95rem !important; margin-bottom: 4px !important; }
    .adv-list li p { font-size: 0.80rem !important; line-height: 1.45 !important; margin: 0 !important; }
    
    .pricing-sec { padding: 50px 0; }
    .pricing-grid-custom { grid-template-columns: 1fr; gap: 24px; }
    .pricing-card-custom { padding: 24px; }
    .pricing-card-custom h3 { font-size: 1.3rem; }
    .pricing-card-custom .price-tag { font-size: 1.8rem; }
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
    .timeline-flow { grid-template-columns: 1fr; gap: 16px; }
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
        min-height: 480px !important;
        height: auto !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        overflow: hidden !important;
        transition: height 0.3s ease;
    }
    .pricing-card-custom.expanded {
        height: auto !important;
        min-height: 480px !important;
        overflow: visible !important;
    }
    .pricing-card-custom > div > div:first-child {
        margin-top: -28px !important;
        margin-left: -22px !important;
        margin-right: -22px !important;
        height: 140px !important;
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
    .service-box-card { padding: 25px 20px; }
    .problem-card { padding: 25px 20px; }
    .pro-service-mobile-img { max-width: 240px !important; }
}
</style>

<!-- Hero Section -->
<section class="bundle-hero">
    <div class="container">
        <div class="hero-grid">
            <!-- Left Text Box -->
            <div class="hero-content">
                <span class="badge" style="background-color: hsl(var(--clr-primary-light)); color: hsl(var(--clr-primary)); border: 1px solid hsl(var(--clr-primary));">
                    Layanan Akuntansi & Perpajakan
                </span>
                <h1>Jasa Konsultan Pajak</h1>
                
                <!-- Mobile Hero Image -->
                <div class="hero-img-wrap-mobile">
                    <img src="<?php echo BASE_URL; ?>assets/images/pajak%26accounting/hero-tax.png" alt="Jasa Konsultan Pajak">
                    <div class="hero-floating-card">
                        <p class="floating-card-title">Praktek Profesional Resmi</p>
                        <p class="floating-card-desc">Seluruh pengerjaan dan pelaporan dipandu oleh Konsultan Pajak bersertifikat BKP terdaftar.</p>
                    </div>
                </div>

                <p>
                    Urban Office menyediakan Jasa konsultan pajak bulanan & tahunan secara profesional untuk membantu Anda mengelola pembukuan keuangan bulanan, SPT Pajak bulanan & tahunan secara akurat, legal, aman dan terhindar dari denda administrasi.
                </p>
                <div class="hero-ctas">
                    <a href="#pricing" class="btn btn-primary">Lihat Pilihan Paket</a>
                    <a href="https://api.whatsapp.com/send?phone=6285107620100&text=Halo%20Urban%20Office%2C%20saya%20tertarik%20dengan%20Jasa%20Konsultan%20Pajak.%20Mohon%20info%20selengkapnya." 
                       target="_blank" class="btn btn-outline" style="background-color: #FFFFFF;">
                        <i class="bi bi-whatsapp" style="margin-right: 5px;"></i> Hubungi Kami
                    </a>
                </div>
            </div>
            
            <!-- Right Image Representation -->
            <div class="hero-img-wrap-desktop">
                <img src="<?php echo BASE_URL; ?>assets/images/pajak%26accounting/hero-tax.png" alt="Jasa Konsultan Pajak">
                <div class="hero-floating-card">
                    <p class="floating-card-title">Praktek Profesional Resmi</p>
                    <p class="floating-card-desc">Seluruh pengerjaan dan pelaporan dipandu oleh Konsultan Pajak bersertifikat BKP terdaftar.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- App Promo Section -->
<?php include DIR_ROOT . 'inc/components/app_section.php'; ?>


<!-- Layanan Kami Section -->
<section class="layanan-kami-sec">
    <div class="container text-center">
        <span class="badge" style="display: block; margin: 0 auto 15px auto; width: fit-content; background-color: hsl(var(--clr-primary-light)); color: hsl(var(--clr-primary));">Layanan Kami</span>
        <h2 class="section-title">Solusi Terintegrasi Keuangan & Perpajakan</h2>
        <p class="section-subtitle">Dapatkan dukungan operasional terpercaya untuk mengawal kestabilan dan legalitas keuangan bisnis Anda.</p>
        
        <div class="services-3col-grid">
            <!-- Card 1 -->
            <div class="service-box-card">
                <div>
                    <div class="service-box-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <h3>Konsultasi Perpajakan</h3>
                    <p>Pendampingan penuh oleh konsultan pajak berlisensi (BKP) untuk kepatuhan bulanan, review transaksi usaha, serta tax planning yang legal.</p>
                </div>
                <a href="#contact" class="btn btn-outline" style="width: 100%; text-align: center;">Lihat Selengkapnya</a>
            </div>
            
            <!-- Card 2 -->
            <div class="service-box-card">
                <div>
                    <div class="service-box-icon">
                        <i class="bi bi-calculator"></i>
                    </div>
                    <h3>Laporan Keuangan</h3>
                    <p>Penyusunan jurnal, buku besar, neraca, serta laporan laba rugi bulanan dan tahunan secara akurat sesuai standar akuntansi yang berlaku.</p>
                </div>
                <a href="#contact" class="btn btn-outline" style="width: 100%; text-align: center;">Lihat Selengkapnya</a>
            </div>
            
            <!-- Card 3 -->
            <div class="service-box-card">
                <div>
                    <div class="service-box-icon">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <h3>Pembuatan PKP & SPT</h3>
                    <p>Pengurusan pengukuhan Pengusaha Kena Pajak (PKP) resmi, aktivasi akun e-Faktur, pelaporan SPT bulanan (Masa) dan SPT PPh Badan tahunan.</p>
                </div>
                <a href="#contact" class="btn btn-outline" style="width: 100%; text-align: center;">Lihat Selengkapnya</a>
            </div>
        </div>
    </div>
</section>

<!-- Kalender Pajak Section -->
<section class="calendar-sec">
    <div class="container text-center">
        <span class="badge" style="display: block; margin: 0 auto 15px auto; width: fit-content; background-color: hsl(var(--clr-primary-light)); color: hsl(var(--clr-primary));">Tax Calendar</span>
        <h2 class="section-title">Kalender Pajak</h2>
        <p class="section-subtitle">Jadwal penting pembayaran dan pelaporan Pajak untuk Perusahaan Anda agar terhindar dari sanksi denda administrasi.</p>
        
        <div class="timeline-flow">
            <!-- Node 1 -->
            <div class="timeline-node">
                <div class="node-circle">Tgl 10</div>
                <h3>SPT Masa PPh</h3>
                <p>Batas akhir pembayaran PPh Pasal 21, 23, 25, 4 ayat 2, dan PPh Final 0.5% atas transaksi bulan sebelumnya.</p>
            </div>
            
            <!-- Node 2 -->
            <div class="timeline-node">
                <div class="node-circle">Tgl 15</div>
                <h3>Pembayaran PPN</h3>
                <p>Batas akhir pembayaran Pajak Pertambahan Nilai (PPN) Masa bagi Pengusaha Kena Pajak (PKP).</p>
            </div>
            
            <!-- Node 3 -->
            <div class="timeline-node">
                <div class="node-circle">Tgl 20</div>
                <h3>Pelaporan SPT Masa</h3>
                <p>Batas akhir pelaporan SPT Masa bulanan untuk seluruh jenis PPh dan PPN yang telah disetorkan.</p>
            </div>
            
            <!-- Node 4 -->
            <div class="timeline-node">
                <div class="node-circle">30 Apr</div>
                <h3>SPT Tahunan Badan</h3>
                <p>Batas akhir pembayaran dan pelaporan SPT Tahunan PPh Badan Usaha (4 bulan setelah akhir tahun buku).</p>
            </div>
        </div>
    </div>
</section>

<!-- Situasi Masalah Pajak Section -->
<section class="problems-sec">
    <div class="container text-center">
        <span class="badge" style="display: block; margin: 0 auto 15px auto; width: fit-content; background-color: #ffebee; color: #dd2c00; border: 1px solid #ffcdd2;">Pencegahan Risiko</span>
        <h2 class="section-title">Situasi yang Sering Menyebabkan Masalah Pajak & Akuntansi</h2>
        <p class="section-subtitle">Hindari potensi pemeriksaan pajak mendadak atau denda administrasi berat akibat kesalahan operasional berikut ini.</p>
        
        <div class="problems-grid">
            <!-- Card 1 -->
            <div class="problem-card">
                <i class="bi bi-person-exclamation problem-card-icon"></i>
                <h3>Abaikan Pembukuan Bulanan</h3>
                <p>Bila tidak menyusun pembukuan bulanan secara kontinu, Anda akan kesulitan menyusun laporan keuangan akhir tahun. Hal ini mengakibatkan SPT Tahunan tidak akurat dan rentan sanksi pemeriksaan.</p>
            </div>
            
            <!-- Card 2 -->
            <div class="problem-card">
                <i class="bi bi-info-circle problem-card-icon"></i>
                <h3>Kurang Pemahaman Perpajakan</h3>
                <p>Aturan perpajakan Indonesia sangat dinamis. Kesalahan menafsirkan peraturan PPh, PPN, atau tarif potong-pungut dapat memicu kurang bayar pajak beserta denda bunga bulanan.</p>
            </div>
            
            <!-- Card 3 -->
            <div class="problem-card">
                <i class="bi bi-hourglass-bottom problem-card-icon"></i>
                <h3>Denda & Sanksi Keterlambatan</h3>
                <p>Keterlambatan bayar pajak dikenakan sanksi tarif bunga per bulan dari DJP, dan keterlambatan lapor SPT dikenakan denda administratif flat yang dapat membebani kelancaran arus kas bisnis Anda.</p>
            </div>
        </div>
    </div>
</section>

<!-- Nikmati Layanan Perpajakan Profesional Kami Section -->
<section class="pro-service-sec">
    <div class="container">
        <div class="pro-service-grid">
            <!-- Left content -->
            <div style="text-align: left;">
                <span class="badge" style="background-color: hsl(var(--clr-primary-light)); color: hsl(var(--clr-primary));">Layanan Profesional</span>
                <h2 style="font-weight: 800; font-size: 2rem; margin-bottom: 15px;">Nikmati Layanan Perpajakan Profesional Kami.</h2>
                
                <!-- Mobile Image Wrap -->
                <div class="pro-service-mobile-img" style="display: none; border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-md); border: 1px solid hsl(var(--clr-border)); margin: 0 auto 20px auto; max-width: 280px;">
                    <img src="<?php echo BASE_URL; ?>assets/images/pajak%26accounting/pro-desk.png" alt="Layanan Pajak Profesional" style="width: 100%; height: auto; display: block;">
                </div>

                <p style="color: hsl(var(--clr-text-muted)); font-size: 0.95rem; line-height: 1.6;">
                    Menghindari kesalahan pelaporan keuangan & perpajakan sangat krusial bagi kelangsungan bisnis. Layanan kami memberikan jaminan keamanan data dan keandalan pelaporan hukum secara penuh dari konsultan terakreditasi.
                </p>
                
                <ul class="adv-list">
                    <li>
                        <h4>Konsultan Pajak Resmi Berlisensi BKP</h4>
                        <p>Seluruh pelaporan dikawal langsung oleh konsultan pajak resmi yang terdaftar di Ikatan Konsultan Pajak Indonesia (IKPI).</p>
                    </li>
                    <li>
                        <h4>Penyusunan Jurnal & Laporan Keuangan</h4>
                        <p>Tim akuntan kami merancang neraca dan laporan laba rugi bulanan dengan rapi dan terstandarisasi untuk kebutuhan internal maupun eksternal (bank/investor).</p>
                    </li>
                    <li>
                        <h4>Kerahasiaan Data 100% Terjamin</h4>
                        <p>Kami menerapkan protokol keamanan data yang ketat serta penandatanganan Non-Disclosure Agreement (NDA) demi menjaga integritas data bisnis Anda.</p>
                    </li>
                    <li>
                        <h4>Harga Terjangkau & Transparan</h4>
                        <p>Skema biaya yang jelas, transparan, dan kompetitif disesuaikan khusus dengan skala omzet serta jenis transaksi usaha Anda.</p>
                    </li>
                </ul>
            </div>
            
            <!-- Right Image -->
            <div class="pro-service-desktop-img" style="border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-md); border: 1px solid hsl(var(--clr-border));">
                <img src="<?php echo BASE_URL; ?>assets/images/pajak%26accounting/pro-desk.png" alt="Layanan Pajak Profesional" style="width: 100%; height: auto; display: block; object-fit: cover; aspect-ratio: 4/3;">
            </div>
        </div>
    </div>
</section>

<!-- Pricing Bundle Cards -->
<section class="pricing-sec" id="pricing">
    <div class="container text-center">
        <span class="badge" style="display: block; margin: 0 auto 15px auto; width: fit-content; background-color: hsl(var(--clr-primary-light)); color: hsl(var(--clr-primary));">Pilihan Paket</span>
        <h2 class="section-title">Informasi Pilihan Paket Price List</h2>
        <p class="section-subtitle">Pilih paket layanan perpajakan yang sesuai dengan skala dan kompleksitas bisnis Anda secara transparan.</p>
        
        <div class="pricing-grid-custom">
            
            <!-- Card 1: Jasa Konsultan Pajak -->
            <div class="pricing-card-custom">
                <div>
                    <div style="height: 180px; overflow: hidden; border-radius: var(--radius-sm); margin-bottom: 15px; border: 1px solid #eee;">
                        <img src="<?php echo BASE_URL; ?>assets/images/pajak%26accounting/konsultan%20pajak.webp" alt="Jasa Konsultan Pajak" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div class="pricing-card-header">
                        <h3>Jasa Konsultan Pajak</h3>
                    </div>
                    
                    <div class="pricing-card-price">
                        IDR 399.000
                        <span>/ Sesi 30 Menit *</span>
                    </div>
                    
                    <p class="pricing-card-desc">
                        Layanan konsultasi perpajakan tatap muka secara online menggunakan Zoom, Google Meet, Skype, atau Google Duo.
                    </p>
                    
                    <button class="btn-toggle-features" onclick="togglePricingFeatures(this)">
                        <span>Lihat Detail</span> <i class="bi bi-chevron-down"></i>
                    </button>
                    
                    <div class="pricing-features-collapse">
                        <ul class="pricing-features-list">
                            <li class="sub-item">Detail Layanan:</li>
                            <li>Menggunakan Zoom, Google Meet, Skype, atau Google Duo</li>
                            <li>Pilihan Topik Perpajakan Dalam atau Luar Negeri</li>
                            <li>Sesi Tatap Muka Online Selama 30 Menit</li>
                            <li>Konsultasi Bersama Ahli Pajak Terdaftar</li>
                        </ul>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; width: 100%; align-items: center; margin-top: 15px;">
                    <a href="#contact" class="btn btn-primary" style="flex: 1; text-align: center; justify-content: center;" onclick="activateOfferMode('Jasa Konsultan Pajak', 'Layanan Perpajakan')">
                        Pilih Layanan
                    </a>
                    <a href="https://api.whatsapp.com/send?phone=6285107620100&text=Halo%20Urban%20Office%2C%20saya%20ingin%20tanya%20detail%20mengenai%20layanan%20Jasa%20Konsultan%20Pajak%20(Rp%20399.000%2F30%20Menit)." 
                       target="_blank" class="btn btn-outline btn-wa-icon" title="Tanya via WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </div>

            <!-- Card 2: Paket Pintar Pajak -->
            <div class="pricing-card-custom">
                <div>
                    <div style="height: 180px; overflow: hidden; border-radius: var(--radius-sm); margin-bottom: 15px; border: 1px solid #eee;">
                        <img src="<?php echo BASE_URL; ?>assets/images/pajak%26accounting/paket%20pintar%20pajak.webp" alt="Paket Pintar Pajak" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div class="pricing-card-header">
                        <h3>Paket Pintar Pajak</h3>
                    </div>
                    
                    <div class="pricing-card-price">
                        IDR 5.470.000
                        <span>/ Tahun (Mulai Dari)</span>
                    </div>
                    
                    <p class="pricing-card-desc">
                        Layanan pembukuan bulanan esensial, konsultasi rutin, serta pelaporan SPT Tahunan PPh Badan untuk UMKM Non-PKP.
                    </p>
                    
                    <button class="btn-toggle-features" onclick="togglePricingFeatures(this)">
                        <span>Lihat Detail</span> <i class="bi bi-chevron-down"></i>
                    </button>
                    
                    <div class="pricing-features-collapse">
                        <ul class="pricing-features-list">
                            <li class="sub-item">Detail Layanan:</li>
                            <li>Pajak Bulanan PPh</li>
                            <li>Konsultasi Perpajakan</li>
                            <li>SPT Tahunan PPh Badan</li>
                        </ul>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; width: 100%; align-items: center; margin-top: 15px;">
                    <a href="#contact" class="btn btn-primary" style="flex: 1; text-align: center; justify-content: center;" onclick="activateOfferMode('Paket Pintar Pajak', 'Layanan Perpajakan')">
                        Pilih Layanan
                    </a>
                    <a href="https://api.whatsapp.com/send?phone=6285107620100&text=Halo%20Urban%20Office%2C%20saya%20ingin%20tanya%20detail%20mengenai%20layanan%20Paket%20Pintar%20Pajak%20(Mulai%20Rp%205.470.000%2Ftahun)." 
                       target="_blank" class="btn btn-outline btn-wa-icon" title="Tanya via WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </div>

            <!-- Card 3: Paket Tuntas Pajak -->
            <div class="pricing-card-custom popular-card">
                <span class="pricing-badge-popular">Populer</span>
                <div>
                    <div style="height: 180px; overflow: hidden; border-radius: var(--radius-sm); margin-bottom: 15px; border: 1px solid #eee;">
                        <img src="<?php echo BASE_URL; ?>assets/images/pajak%26accounting/paket%20tuntas%20pajak.webp" alt="Paket Tuntas Pajak" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div class="pricing-card-header">
                        <h3>Paket Tuntas Pajak</h3>
                    </div>
                    
                    <div class="pricing-card-price">
                        IDR 11.910.000
                        <span>/ Tahun (Mulai Dari)</span>
                    </div>
                    
                    <p class="pricing-card-desc">
                        Layanan perpajakan badan lengkap ditambah penyusunan laporan keuangan teratur dan review pembukuan bulanan.
                    </p>
                    
                    <button class="btn-toggle-features" onclick="togglePricingFeatures(this)">
                        <span>Lihat Detail</span> <i class="bi bi-chevron-down"></i>
                    </button>
                    
                    <div class="pricing-features-collapse">
                        <ul class="pricing-features-list">
                            <li class="sub-item">Detail Layanan:</li>
                            <li>Pajak Bulanan PPh</li>
                            <li>Laporan Keuangan Bulanan & Tahunan</li>
                            <li>Konsultasi Perpajakan</li>
                            <li>SPT Tahunan PPh Badan</li>
                        </ul>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; width: 100%; align-items: center; margin-top: 15px;">
                    <a href="#contact" class="btn btn-primary" style="flex: 1; text-align: center; justify-content: center;" onclick="activateOfferMode('Paket Tuntas Pajak', 'Layanan Perpajakan')">
                        Pilih Layanan
                    </a>
                    <a href="https://api.whatsapp.com/send?phone=6285107620100&text=Halo%20Urban%20Office%2C%20saya%20ingin%20tanya%20detail%20mengenai%20layanan%20Paket%20Tuntas%20Pajak%20(Mulai%20Rp%2011.910.000%2Ftahun)." 
                       target="_blank" class="btn btn-outline btn-wa-icon" title="Tanya via WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </div>

            <!-- Card 4: Paket Lengkap Pajak -->
            <div class="pricing-card-custom">
                <div>
                    <div style="height: 180px; overflow: hidden; border-radius: var(--radius-sm); margin-bottom: 15px; border: 1px solid #eee;">
                        <img src="<?php echo BASE_URL; ?>assets/images/pajak%26accounting/paket%20lengkap%20pajak.webp" alt="Paket Lengkap Pajak" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div class="pricing-card-header">
                        <h3>Paket Lengkap Pajak</h3>
                    </div>
                    
                    <div class="pricing-card-price">
                        IDR 13.760.000
                        <span>/ Tahun (Mulai Dari)</span>
                    </div>
                    
                    <p class="pricing-card-desc">
                        Layanan akuntansi komprehensif bulanan, laporan e-Faktur PPN, konsultasi pajak, serta SPT Tahunan untuk PKP.
                    </p>
                    
                    <button class="btn-toggle-features" onclick="togglePricingFeatures(this)">
                        <span>Lihat Detail</span> <i class="bi bi-chevron-down"></i>
                    </button>
                    
                    <div class="pricing-features-collapse">
                        <ul class="pricing-features-list">
                            <li class="sub-item">Detail Layanan:</li>
                            <li>Pajak Bulanan PPh</li>
                            <li>Pajak Bulanan PPN</li>
                            <li>Laporan Keuangan Bulanan & Tahunan</li>
                            <li>Konsultasi Perpajakan</li>
                            <li>SPT Tahunan PPh Badan</li>
                        </ul>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; width: 100%; align-items: center; margin-top: 15px;">
                    <a href="#contact" class="btn btn-primary" style="flex: 1; text-align: center; justify-content: center;" onclick="activateOfferMode('Paket Lengkap Pajak', 'Layanan Perpajakan')">
                        Pilih Layanan
                    </a>
                    <a href="https://api.whatsapp.com/send?phone=6285107620100&text=Halo%20Urban%20Office%2C%20saya%20ingin%20tanya%20detail%20mengenai%20layanan%20Paket%20Lengkap%20Pajak%20(Mulai%20Rp%2013.760.000%2Ftahun)." 
                       target="_blank" class="btn btn-outline btn-wa-icon" title="Tanya via WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </div>

            <!-- Card 5: Paket PMA -->
            <div class="pricing-card-custom">
                <div>
                    <div style="height: 180px; overflow: hidden; border-radius: var(--radius-sm); margin-bottom: 15px; border: 1px solid #eee;">
                        <img src="<?php echo BASE_URL; ?>assets/images/pajak%26accounting/paket%20pma.webp" alt="Paket PMA" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div class="pricing-card-header">
                        <h3>Paket PMA</h3>
                    </div>
                    
                    <div class="pricing-card-price">
                        Hubungi Kami
                        <span>/ Penawaran Khusus</span>
                    </div>
                    
                    <p class="pricing-card-desc">
                        Layanan akuntansi dan kepatuhan pajak khusus perusahaan Penanaman Modal Asing dengan laporan dwi-bahasa & BKPM.
                    </p>
                    
                    <button class="btn-toggle-features" onclick="togglePricingFeatures(this)">
                        <span>Lihat Detail</span> <i class="bi bi-chevron-down"></i>
                    </button>
                    
                    <div class="pricing-features-collapse">
                        <ul class="pricing-features-list">
                            <li class="sub-item">Detail Layanan:</li>
                            <li>Khusus Penanaman Modal Asing (PT PMA)</li>
                            <li>Pelaporan Pajak Bulanan & Tahunan PPh</li>
                            <li>Penyusunan Laporan Keuangan Multicurrency</li>
                            <li>Pelaporan LKPM BKPM per Kuartal</li>
                            <li>Konsultasi Perpajakan Internasional</li>
                        </ul>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; width: 100%; align-items: center; margin-top: 15px;">
                    <a href="#contact" class="btn btn-primary" style="flex: 1; text-align: center; justify-content: center;" onclick="activateOfferMode('Paket PMA', 'Layanan Perpajakan')">
                        Pilih Layanan
                    </a>
                    <a href="https://api.whatsapp.com/send?phone=6285107620100&text=Halo%20Urban%20Office%2C%20saya%20ingin%20tanya%20detail%20mengenai%20Paket%20PMA%20perpajakan." 
                       target="_blank" class="btn btn-outline btn-wa-icon" title="Tanya via WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </div>

            <!-- Card 6: Pembuatan PKP -->
            <div class="pricing-card-custom">
                <div>
                    <div style="height: 180px; overflow: hidden; border-radius: var(--radius-sm); margin-bottom: 15px; border: 1px solid #eee;">
                        <img src="<?php echo BASE_URL; ?>assets/images/pajak%26accounting/pembuatan%20pkp.webp" alt="Pembuatan PKP" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div class="pricing-card-header">
                        <h3>Pembuatan PKP</h3>
                    </div>
                    
                    <div class="pricing-card-price">
                        IDR 700.000
                        <span>/ Sekali Bayar (Mulai Dari)</span>
                    </div>
                    
                    <p class="pricing-card-desc">
                        Layanan pengurusan pengukuhan status Pengusaha Kena Pajak (PKP) resmi, aktivasi e-Faktur, dan pendampingan.
                    </p>
                    
                    <button class="btn-toggle-features" onclick="togglePricingFeatures(this)">
                        <span>Lihat Detail</span> <i class="bi bi-chevron-down"></i>
                    </button>
                    
                    <div class="pricing-features-collapse">
                        <ul class="pricing-features-list">
                            <li class="sub-item">Detail Layanan:</li>
                            <li>Pengurusan Status PKP</li>
                            <li>Permohonan PKP ke KPP</li>
                            <li>Aktivasi Akun & E-Faktur</li>
                            <li>Pendampingan Proses Survei Petugas Pajak</li>
                        </ul>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; width: 100%; align-items: center; margin-top: 15px;">
                    <a href="#contact" class="btn btn-primary" style="flex: 1; text-align: center; justify-content: center;" onclick="activateOfferMode('Pembuatan PKP', 'Layanan Perpajakan')">
                        Pilih Layanan
                    </a>
                    <a href="https://api.whatsapp.com/send?phone=6285107620100&text=Halo%20Urban%20Office%2C%20saya%20ingin%20tanya%20detail%20mengenai%20Jasa%20Pembuatan%20PKP%20(Mulai%20Rp%20700.000)." 
                       target="_blank" class="btn btn-outline btn-wa-icon" title="Tanya via WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Locations Showcase Section -->
<?php
$package_name = 'Pajak & Akunting';
include DIR_ROOT . 'inc/components/locations_showcase.php';
?>

<!-- FAQ Section -->
<?php
$faqs = [
    [
        'question' => 'Dokumen apa saja yang diperlukan untuk pengurusan pelaporan pajak bulanan?',
        'answer' => 'Untuk pelaporan rutin bulanan, Anda cukup menyiapkan mutasi rekening bank (rekening koran), rekap penjualan & pembelian (invoice/faktur), serta bukti pemotongan pajak dari pihak ketiga (bila ada).'
    ],
    [
        'question' => 'Kapan batas waktu pelaporan dan pembayaran pajak badan usaha?',
        'answer' => 'Pembayaran PPh bulanan paling lambat tanggal 10 atau 15 bulan berikutnya, sedangkan pelaporannya paling lambat tanggal 20. Untuk SPT Tahunan PPh Badan, batas waktu pelaporan dan pembayarannya adalah 30 April tahun berikutnya.'
    ],
    [
        'question' => 'Apakah perusahaan Non-PKP tetap wajib melaporkan pajak?',
        'answer' => 'Ya, perusahaan Non-PKP tetap berkewajiban melakukan pelaporan SPT Tahunan PPh Badan dan membayar pajak atas penghasilan bruto perusahaan sesuai ketentuan perpajakan (seperti PPh Final 0.5%). Mereka hanya tidak memungut/melaporkan PPN.'
    ],
    [
        'question' => 'Apa itu SP2DK dan bagaimana tim Urban Office dapat membantu?',
        'answer' => 'SP2DK adalah Surat Permintaan Penjelasan atas Data dan/atau Keterangan yang diterbitkan oleh KPP apabila ditemukan ketidaksesuaian laporan. Tim ahli perpajakan kami (berlisensi BKP) akan menganalisis data keuangan Anda dan menyusun surat tanggapan formal serta mendampingi pertemuan dengan pihak pajak.'
    ],
    [
        'question' => 'Apakah layanan ini bisa sekaligus membantu pembukuan perusahaan?',
        'answer' => 'Bisa. Tim kami dapat membantu merapikan pembukuan, merekap transaksi, menyusun laporan keuangan dasar, dan menyiapkan data yang dibutuhkan untuk pelaporan pajak bulanan maupun tahunan.'
    ],
    [
        'question' => 'Apakah data keuangan perusahaan saya dijamin kerahasiaannya?',
        'answer' => 'Tentu saja. Kami menjamin 100% kerahasiaan seluruh dokumen, data transaksi, dan laporan perpajakan Anda. Kerahasiaan ini dituangkan secara resmi dalam Non-Disclosure Agreement (NDA) yang ditandatangani bersama di awal kerjasama.'
    ]
];
include dirname(dirname(__FILE__)) . '/inc/components/faq.php';
?>

<!-- Layanan Lainnya Section -->
<section class="other-services-sec">
    <div class="container text-center">
        <span class="badge" style="display: block; margin: 0 auto 15px auto; width: fit-content; background-color: hsl(var(--clr-primary-light)); color: hsl(var(--clr-primary));">Layanan Lainnya</span>
        <h2 class="section-title">Temukan Layanan Bisnis Lainnya</h2>
        <p class="section-subtitle">Dukung ekspansi bisnis Anda dengan layanan ruang kerja dan legalitas terpercaya dari Urban Office.</p>
        
        <div class="other-services-grid">
            <!-- Service 1 -->
            <a href="<?php echo BASE_URL; ?>virtual-office-surabaya/" class="other-service-card">
                <div>
                    <i class="bi bi-building"></i>
                    <h4>Virtual Office Surabaya</h4>
                    <p>Alamat bisnis prestisius, penanganan surat menyurat, dan kuota ruang meeting.</p>
                </div>
                <span style="font-size: 0.85rem; font-weight: 700; color: hsl(var(--clr-primary)); margin-top: 10px; display: block;">Selengkapnya &rarr;</span>
            </a>
            
            <!-- Service 2 -->
            <a href="<?php echo BASE_URL; ?>pendirian-pt-include-virtual-office/" class="other-service-card">
                <div>
                    <i class="bi bi-briefcase"></i>
                    <h4>Pendirian PT Badan</h4>
                    <p>Paket lengkap legalitas pendirian PT di Surabaya termasuk alamat kantor virtual.</p>
                </div>
                <span style="font-size: 0.85rem; font-weight: 700; color: hsl(var(--clr-primary)); margin-top: 10px; display: block;">Selengkapnya &rarr;</span>
            </a>
            
            <!-- Service 3 -->
            <a href="<?php echo BASE_URL; ?>pendirian-cv-virtual-office/" class="other-service-card">
                <div>
                    <i class="bi bi-folder-check"></i>
                    <h4>Pendirian CV</h4>
                    <p>Kemudahan pengurusan perizinan pendirian CV lengkap dengan domisili hukum resmi.</p>
                </div>
                <span style="font-size: 0.85rem; font-weight: 700; color: hsl(var(--clr-primary)); margin-top: 10px; display: block;">Selengkapnya &rarr;</span>
            </a>
            
            <!-- Service 4 -->
            <a href="<?php echo BASE_URL; ?>pendirian-pma-plus-virtual-office/" class="other-service-card">
                <div>
                    <i class="bi bi-globe"></i>
                    <h4>Pendirian PMA</h4>
                    <p>Legalitas penanaman modal asing aman, cepat, dan sesuai kepatuhan hukum Indonesia.</p>
                </div>
                <span style="font-size: 0.85rem; font-weight: 700; color: hsl(var(--clr-primary)); margin-top: 10px; display: block;">Selengkapnya &rarr;</span>
            </a>
        </div>
    </div>
</section>

<!-- Lead & Contact Form component -->
<?php include dirname(dirname(__FILE__)) . '/inc/components/contact_form.php'; ?>

<?php require_once dirname(dirname(__FILE__)) . '/inc/footer.php'; ?>
