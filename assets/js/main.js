/**
 * Master Script file for UrbanOffice website
 * Manages UI animations, mobile navigation, sliders, accordions, and form submissions
 */

document.addEventListener('DOMContentLoaded', () => {
    initAttributionAndTracking();
    initHeaderScroll();
    initMobileMenu();
    initActiveMenuHighlight();
    initFaqAccordion();
    initContactForm();
    initHeroSlider();
    initContactReviewSlider();
    initBrandsSlider();
    initWhyVoSlider();
    initBenefitsVoSlider();
    initPricingFilter();
    initHomepageSearch();
    initBookingBranchPickers();
    initBranchesFilter();
    initMobileLogosMarquee();
    initWhatsAppModal();
    initOfficeGallerySliders();
    if (typeof resetOfferMode === 'function') {
        resetOfferMode();
    }
});

window.addEventListener('pageshow', (e) => {
    if (e.persisted) {
        if (typeof resetOfferMode === 'function') {
            resetOfferMode();
        }
    }
});

/**
 * Session Traffic Attribution (SEO / Google Ads / Meta Ads / GEO AI / Social / Referral / Direct),
 * UTM & Click-ID Passthrough to Web App, and Structured dataLayer Event Tracking.
 */
function uoDetectAttributionFromEntry() {
    const params = new URLSearchParams(window.location.search);
    const getParam = (k) => (params.get(k) || '').trim();

    const utmSource   = getParam('utm_source');
    const utmMedium   = getParam('utm_medium');
    const utmCampaign = getParam('utm_campaign');
    const utmContent  = getParam('utm_content');
    const utmTerm     = getParam('utm_term');
    const gclid       = getParam('gclid');
    const gbraid      = getParam('gbraid');
    const wbraid      = getParam('wbraid');
    const gadSource   = getParam('gad_source');
    const fbclid      = getParam('fbclid');
    const ttclid      = getParam('ttclid');
    const msclkid     = getParam('msclkid');

    const rawRef = (document.referrer || '').trim();
    let refHost = '';
    if (rawRef) {
        try {
            refHost = new URL(rawRef).hostname.toLowerCase().replace(/^www\./, '');
        } catch (e) {}
    }

    const curHost = (window.location.hostname || '').toLowerCase().replace(/^www\./, '');
    const isInternalRef = !refHost ||
        refHost === curHost ||
        refHost.endsWith('urbanoffice.co.id') ||
        refHost.endsWith('urbanoffice.id');

    const srcLow = utmSource.toLowerCase();
    const medLow = utmMedium.toLowerCase();
    const paidMediums = ['cpc', 'ppc', 'paid', 'paidsearch', 'paid_search', 'paidsocial', 'paid_social', 'display', 'cpm', 'ads'];
    const isPaidMedium = paidMediums.includes(medLow);

    const aiHosts = [
        'chatgpt.com', 'chat.openai.com', 'perplexity.ai', 'gemini.google.com',
        'bard.google.com', 'claude.ai', 'copilot.microsoft.com', 'meta.ai',
        'you.com', 'phind.com', 'grok.com', 'x.ai'
    ];
    const searchHosts = ['google.', 'bing.com', 'yahoo.com', 'duckduckgo.com', 'ecosia.org', 'yandex.'];
    const socialHosts = ['facebook.com', 'm.facebook.com', 'l.facebook.com', 'fb.com', 'instagram.com', 'l.instagram.com', 'tiktok.com', 'linkedin.com', 't.co', 'x.com', 'twitter.com', 'youtube.com', 'threads.net'];

    const matchesHost = (host, list) => host && list.some((h) => host === h || host.endsWith('.' + h) || (h.endsWith('.') && host.includes(h)));
    const matchesAiSource = (s) => ['chatgpt', 'openai', 'perplexity', 'gemini', 'claude', 'copilot', 'meta.ai', 'grok'].some((k) => s.includes(k));

    let channel = 'direct';
    let source  = utmSource || (isInternalRef ? 'direct' : refHost);
    let medium  = utmMedium || (isInternalRef ? 'none' : 'referral');
    let clickIdType = 'none';

    if (gclid || gbraid || wbraid || gadSource) {
        channel = 'google_ads';
        source = utmSource || 'google';
        medium = utmMedium || 'cpc';
        clickIdType = gclid ? 'gclid' : (gbraid ? 'gbraid' : (wbraid ? 'wbraid' : 'gad_source'));
    } else if (isPaidMedium && (srcLow.includes('google') || srcLow === 'adwords')) {
        channel = 'google_ads';
    } else if (
        (fbclid && (isPaidMedium || utmCampaign !== '' || srcLow.includes('fb') || srcLow.includes('ig') || srcLow.includes('meta') || srcLow.includes('facebook') || srcLow.includes('instagram'))) ||
        (isPaidMedium && (srcLow.includes('fb') || srcLow.includes('ig') || srcLow.includes('meta') || srcLow.includes('facebook') || srcLow.includes('instagram')))
    ) {
        channel = 'meta_ads';
        source = utmSource || 'meta';
        medium = utmMedium || 'paid_social';
        if (fbclid) clickIdType = 'fbclid';
    } else if (ttclid || msclkid || isPaidMedium) {
        channel = 'paid_ads';
        if (ttclid) {
            source = utmSource || 'tiktok';
            medium = utmMedium || 'paid_social';
            clickIdType = 'ttclid';
        } else if (msclkid) {
            source = utmSource || 'bing';
            medium = utmMedium || 'cpc';
            clickIdType = 'msclkid';
        }
    } else if (matchesHost(refHost, aiHosts) || matchesAiSource(srcLow) || medLow.includes('ai')) {
        channel = 'geo_ai';
        source = utmSource || refHost || 'ai_assistant';
        medium = utmMedium || 'ai_assistant';
    } else if (matchesHost(refHost, searchHosts) || medLow === 'organic') {
        channel = 'seo_organic';
        source = utmSource || (refHost ? refHost.split('.')[0] : 'google');
        medium = utmMedium || 'organic';
    } else if (matchesHost(refHost, socialHosts) || fbclid || medLow === 'social') {
        channel = 'social_organic';
        source = utmSource || refHost || 'social';
        medium = utmMedium || 'social';
        if (fbclid) clickIdType = 'fbclid';
    } else if (!isInternalRef) {
        channel = 'referral';
        source = utmSource || refHost;
        medium = utmMedium || 'referral';
    }

    const passthrough = {};
    ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'gclid', 'gbraid', 'wbraid', 'fbclid', 'ttclid'].forEach((k) => {
        const val = getParam(k);
        if (val) passthrough[k] = val;
    });

    const hasExplicitCampaignSignal = Boolean(
        utmSource || utmMedium || utmCampaign || gclid || gbraid || wbraid || gadSource || fbclid || ttclid || msclkid
    );

    return {
        channel,
        source: source || 'direct',
        medium: medium || 'none',
        campaign: utmCampaign || '',
        content: utmContent || '',
        term: utmTerm || '',
        click_id_type: clickIdType,
        landing_page: window.location.pathname,
        referrer: isInternalRef ? '' : rawRef,
        passthrough,
        hasExplicitCampaignSignal
    };
}

function uoGetAttribution() {
    const key = 'uo_attribution_v1';
    try {
        const stored = sessionStorage.getItem(key);
        if (stored) {
            return JSON.parse(stored);
        }
    } catch (e) {}
    return uoDetectAttributionFromEntry();
}
window.uoGetAttribution = uoGetAttribution;

function uoFormatAttributionSummary(attr) {
    if (!attr) return 'Direct';
    const labels = {
        google_ads: 'Google Ads',
        meta_ads: 'Meta Ads',
        paid_ads: 'Paid Ads',
        geo_ai: 'GEO / AI Search',
        seo_organic: 'SEO Organic',
        social_organic: 'Social Organic',
        referral: 'Referral',
        direct: 'Direct'
    };
    const chLabel = labels[attr.channel] || attr.channel || 'Direct';
    const parts = [chLabel];
    if (attr.source && attr.source !== 'direct') parts.push(`${attr.source}/${attr.medium || 'none'}`);
    if (attr.campaign) parts.push(`campaign: ${attr.campaign}`);
    if (attr.landing_page) parts.push(`entry: ${attr.landing_page}`);
    return parts.join(' | ');
}

function uoAppendAttributionToUrl(rawUrl) {
    try {
        const url = new URL(rawUrl, window.location.origin);
        const attr = uoGetAttribution();
        const passthrough = (attr && attr.passthrough) ? attr.passthrough : {};
        Object.keys(passthrough).forEach((k) => {
            if (!url.searchParams.has(k) && passthrough[k]) {
                url.searchParams.set(k, passthrough[k]);
            }
        });
        if (attr && attr.channel && !url.searchParams.has('lp_channel')) {
            url.searchParams.set('lp_channel', attr.channel);
        }
        return url.toString();
    } catch (e) {
        return rawUrl;
    }
}

function initAttributionAndTracking() {
    const key = 'uo_attribution_v1';
    const fresh = uoDetectAttributionFromEntry();
    let activeAttr = fresh;

    try {
        const existingRaw = sessionStorage.getItem(key);
        if (existingRaw) {
            const existing = JSON.parse(existingRaw);
            // Keep first-touch attribution for the session unless a new explicit ad/UTM entry arrives
            if (!fresh.hasExplicitCampaignSignal && existing && existing.channel) {
                activeAttr = existing;
            }
        }
        sessionStorage.setItem(key, JSON.stringify(activeAttr));
    } catch (e) {}

    // Expose session attribution immediately to GTM / GA4 dataLayer
    try {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            event: 'uo_session_attribution',
            traffic_channel: activeAttr.channel,
            traffic_source: activeAttr.source,
            traffic_medium: activeAttr.medium,
            traffic_campaign: activeAttr.campaign || '(none)',
            landing_page: activeAttr.landing_page
        });
    } catch (e) {}

    // Track clicks on ALL Web App Booking / Portal links & pass through UTM/gclid seamlessly
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[href*="my.urbanoffice.co.id"]');
        if (!link) return;

        const rawHref = link.getAttribute('href') || '';
        const enrichedHref = uoAppendAttributionToUrl(rawHref);
        if (enrichedHref && enrichedHref !== rawHref) {
            link.setAttribute('href', enrichedHref);
        }

        try {
            const parsed = new URL(enrichedHref, window.location.origin);
            const attr = uoGetAttribution();
            const isBookingForm = parsed.pathname.includes('/dashboard/bookingform');

            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                event: isBookingForm ? 'booking_cta_click' : 'webapp_portal_click',
                booking_service: parsed.searchParams.get('room_type') || 'General',
                booking_city_id: parsed.searchParams.get('city_id') || '',
                booking_location_id: parsed.searchParams.get('location_id') || '',
                cta_text: (link.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 80),
                page_path: window.location.pathname,
                traffic_channel: attr.channel,
                traffic_source: attr.source,
                traffic_medium: attr.medium,
                traffic_campaign: attr.campaign || '(none)',
                landing_page: attr.landing_page
            });
        } catch (err) {}
    });
}

/**
 * Change header background opacity on scroll
 */
function initHeaderScroll() {
    const header = document.getElementById('navbar');
    if (!header) return;

    const handleScroll = () => {
        if (window.scrollY > 20) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    };

    window.addEventListener('scroll', handleScroll);
    handleScroll(); // Run initially
}

/**
 * Mobile Navigation Toggle menu and sub-dropdowns
 */
function initMobileMenu() {
    const toggleBtn = document.getElementById('hamburger-toggle');
    const navMenu = document.getElementById('nav-menu');
    if (!toggleBtn || !navMenu) return;

    toggleBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        navMenu.classList.toggle('active');
        toggleBtn.innerHTML = navMenu.classList.contains('active') ? '✕' : '&#9776;';
    });

    // Mobile dropdown submenus toggle on click
    const navLinksWithDropdown = document.querySelectorAll('.nav-menu > .nav-item > .nav-link');
    navLinksWithDropdown.forEach(link => {
        link.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                const parent = link.parentElement;
                const hasDropdown = parent.querySelector('.dropdown');
                if (hasDropdown) {
                    e.preventDefault();
                    parent.classList.toggle('active');
                }
            }
        });
    });

    // Mobile nested flyout submenus (e.g. Virtual Office branch list) toggle on click
    const subDropdownToggles = document.querySelectorAll('.dropdown-item-has-sub');
    subDropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                toggle.parentElement.classList.toggle('active');
            }
        });
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (navMenu.classList.contains('active') && !navMenu.contains(e.target) && e.target !== toggleBtn) {
            navMenu.classList.remove('active');
            toggleBtn.innerHTML = '&#9776;';
        }
    });

    // Close mobile drawer on link click (unless it has dropdown)
    const allNavLinks = document.querySelectorAll('.nav-menu a');
    allNavLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const parent = link.parentElement;
            const hasDropdown = parent.querySelector('.dropdown, .dropdown-submenu');
            if (!hasDropdown || window.innerWidth > 768) {
                navMenu.classList.remove('active');
                toggleBtn.innerHTML = '&#9776;';
            }
        });
    });
}

/**
 * Active Navigation Highlight and Page Reload handling
 */
function initActiveMenuHighlight() {
    const navLinks = document.querySelectorAll('.nav-menu a');
    if (!navLinks.length) return;

    // 1. Detect if this is a manual browser reload/refresh
    let isReload = false;
    if (window.performance && window.performance.navigation) {
        if (window.performance.navigation.type === 1) { // TYPE_RELOAD
            isReload = true;
        }
    }
    if (window.performance && window.performance.getEntriesByType) {
        const navEntries = window.performance.getEntriesByType('navigation');
        if (navEntries.length > 0 && navEntries[0].type === 'reload') {
            isReload = true;
        }
    }

    if (isReload) {
        sessionStorage.removeItem('activeNavLink');
    }

    // 2. Track link clicks to save active link state
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            const href = link.getAttribute('href');
            // Do not record hash links or login buttons as active pages
            if (href && href !== '#' && !href.startsWith('javascript:') && !link.closest('.nav-cta-btn')) {
                sessionStorage.setItem('activeNavLink', link.href);
            }
        });
    });

    // 3. Apply active highlights on page load
    const activeHref = sessionStorage.getItem('activeNavLink');
    if (activeHref) {
        // Normalize for matching: strip hash and trailing slash but KEEP the query string,
        // so location variants of the same page (e.g. ?lokasi=jakarta vs ?lokasi=surabaya)
        // are treated as distinct and only the link actually clicked gets highlighted.
        const cleanUrl = (url) => url.split('#')[0].replace(/\/+$/, '');
        const targetCleanUrl = cleanUrl(activeHref);
        const currentCleanUrl = cleanUrl(window.location.href);

        // Only highlight if the stored link is actually for the current page
        if (targetCleanUrl === currentCleanUrl) {
            navLinks.forEach(link => {
                // Skip hash/JS anchors (e.g. the "#" dropdown toggles): their resolved href is
                // the current page URL, so they would falsely match on every page. The correct
                // parent menu still gets highlighted via the dropdown-item branch below.
                const rawHref = link.getAttribute('href') || '';
                if (rawHref === '' || rawHref.charAt(0) === '#' || rawHref.startsWith('javascript:')) {
                    return;
                }
                if (cleanUrl(link.href) === targetCleanUrl) {
                    link.classList.add('nav-active');

                    // If it is a dropdown subpage, highlight and auto-expand the parent menu
                    if (link.classList.contains('dropdown-item')) {
                        const parentItem = link.closest('.nav-item');
                        if (parentItem) {
                            const parentLink = parentItem.querySelector('.nav-link');
                            if (parentLink) {
                                parentLink.classList.add('nav-active');
                            }
                            // Auto expand on mobile
                            if (window.innerWidth <= 768) {
                                parentItem.classList.add('active');
                            }
                        }
                    }
                }
            });
        }
    }
}

/**
 * FAQ Accordion slide-down transition
 */
function initFaqAccordion() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    
    faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
            const item = question.parentElement;
            const isActive = item.classList.contains('active');
            
            // Close all active items
            document.querySelectorAll('.faq-item').forEach(i => {
                i.classList.remove('active');
                i.querySelector('.faq-answer').style.maxHeight = null;
            });
            
            if (!isActive) {
                item.classList.add('active');
                const answer = item.querySelector('.faq-answer');
                answer.style.maxHeight = answer.scrollHeight + 'px';
            }
        });
    });
}

/**
 * AJAX Contact Form Submission
 * Submits lead data to backend database and forwards user to WhatsApp
 */
function initContactForm() {
    const form = document.querySelector('.contact-form-wrap form');
    if (!form) return;

    // Rupiah formatter helper
    const formatRupiah = (value) => {
        const digits = value.replace(/\D/g, '');
        if (!digits) return '';
        const formatted = new Intl.NumberFormat('id-ID').format(digits);
        return 'Rp ' + formatted;
    };

    const budgetInput = form.querySelector('[name="budget"]');
    if (budgetInput) {
        budgetInput.addEventListener('input', (e) => {
            e.target.value = formatRupiah(e.target.value);
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Simple validation checks
        const nameInput = form.querySelector('[name="name"]');
        const emailInput = form.querySelector('[name="email"]');
        const phoneInput = form.querySelector('[name="phone"]');
        const serviceInput = form.querySelector('[name="service"]');
        const csrfInput = form.querySelector('[name="csrf_token"]');
        const honeyInput = form.querySelector('[name="email_confirm"]');
        
        // New fields
        const peopleInput = form.querySelector('[name="jumlah_orang"]');
        const dateInput = form.querySelector('[name="tanggal"]');
        
        // Offer mode trackers & fields
        const isOfferModeInput = form.querySelector('[name="is_offer_mode"]');
        const offerPackageInput = form.querySelector('[name="offer_package"]');
        const isOfferMode = isOfferModeInput && isOfferModeInput.value === '1';
        const offerPackage = offerPackageInput ? offerPackageInput.value : '';

        const locationInput = form.querySelector('[name="location"]');
        const budgetInput = form.querySelector('[name="budget"]');
        const pesanInput = form.querySelector('[name="pesan"]');

        const name = nameInput ? nameInput.value.trim() : '';
        const email = emailInput ? emailInput.value.trim() : '';
        const phone = phoneInput ? phoneInput.value.trim() : '';
        const service = serviceInput ? serviceInput.value : '';
        const csrfToken = csrfInput ? csrfInput.value : '';
        const honey = honeyInput ? honeyInput.value : '';
        const peopleVal = peopleInput ? peopleInput.value : '';
        const dateVal = dateInput ? dateInput.value : '';
        const locationVal = locationInput ? locationInput.value.trim() : '';
        const budgetVal = budgetInput ? budgetInput.value.trim() : '';
        const pesanVal = pesanInput ? pesanInput.value.trim() : '';

        const feedbackBox = document.getElementById('contact-form-feedback');
        const showFormFeedback = (html, type) => {
            if (!feedbackBox) return;
            feedbackBox.innerHTML = html;
            feedbackBox.classList.remove('is-error', 'is-success');
            if (type) feedbackBox.classList.add(`is-${type}`);
            feedbackBox.hidden = false;
        };
        if (feedbackBox) {
            feedbackBox.hidden = true;
            feedbackBox.innerHTML = '';
        }

        if (honey) {
            // Bot detected, silently block submission
            showFormFeedback('<strong>Terima kasih!</strong> Pesan Anda berhasil dikirim.', 'success');
            form.reset();
            return;
        }

        if (isOfferMode) {
            if (!name || !phone || !service || !budgetVal || !pesanVal) {
                showFormFeedback('Harap isi semua kolom wajib (*).', 'error');
                return;
            }
        } else {
            const requiresVisitDate = Boolean(dateInput && dateInput.required);
            if (!name || !phone || !service || (requiresVisitDate && !dateVal)) {
                showFormFeedback('Harap isi semua kolom wajib (*).', 'error');
                return;
            }
        }

        // Format message for DB compatibility (append session traffic source for internal sales/marketing research)
        const sessionAttr = (typeof uoGetAttribution === 'function') ? uoGetAttribution() : null;
        const attrLine = sessionAttr ? `\n[Sumber: ${uoFormatAttributionSummary(sessionAttr)}]` : '';
        let message = '';
        if (isOfferMode) {
            message = `[Pengajuan Penawaran]\nCabang / Lokasi: ${locationVal}\nPaket: ${offerPackage || '-'}\nEkspektasi Budget: ${budgetVal}\nPesan: ${pesanVal}${attrLine}`;
        } else {
            message = `[Pemesanan Kunjungan]\nJumlah Orang: ${peopleVal || '-'}\nTanggal: ${dateVal}${attrLine}`;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        const origBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Mengirim...';

        try {
            // Fetch a fresh per-session CSRF token so statically cached HTML pages never
            // fail with 403 due to another visitor's cached CSRF token.
            let activeCsrfToken = csrfToken;
            const csrfUrl = form.dataset.csrfUrl;
            if (csrfUrl) {
                try {
                    const tokenRes = await fetch(csrfUrl, {
                        method: 'GET',
                        credentials: 'same-origin',
                        cache: 'no-store'
                    });
                    if (tokenRes.ok) {
                        const tokenJson = await tokenRes.json();
                        if (tokenJson && tokenJson.csrf_token) {
                            activeCsrfToken = tokenJson.csrf_token;
                            if (csrfInput) csrfInput.value = activeCsrfToken;
                        }
                    }
                } catch (tokenErr) {
                    // Fallback to hidden input token if endpoint is temporarily unavailable
                }
            }

            // Post data to our AJAX lead handler script
            const response = await fetch(form.getAttribute('action') || `${window.location.origin}/inc/lead_handler.php`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name, email, phone, service, message, csrf_token: activeCsrfToken
                })
            });

            const result = await response.json();

            if (result.success) {
                // Formatting WhatsApp pre-filled text
                let waText = '';
                if (isOfferMode) {
                    waText = `Halo Urban Office, saya *${name}*.\n\n*Saya ingin mengajukan penawaran untuk:* ${service}`;
                    if (offerPackage) {
                        waText += ` (Paket ${offerPackage})`;
                    }
                    waText += `\n*Cabang / Lokasi:* ${locationVal}`;
                    waText += `\n*Ekspektasi Budget:* ${budgetVal}`;
                    waText += `\n*Pesan:* ${pesanVal}`;
                    if (email) {
                        waText += `\n\n*Email:* ${email}`;
                    }
                    waText += `\n*No. HP:* ${phone}`;
                } else {
                    waText = `Halo Urban Office, saya *${name}*.\n\n*Saya ingin berkonsultasi mengenai:* ${service}`;
                    if (email) {
                        waText += `\n*Email:* ${email}`;
                    }
                    waText += `\n*No. HP:* ${phone}`;
                    if (peopleVal) {
                        waText += `\n*Jumlah Orang:* ${peopleVal} Orang`;
                    }
                    if (dateVal) {
                        waText += `\n*Tanggal:* ${dateVal}`;
                    }
                }
                
                // Reset form and offer mode
                form.reset();
                if (typeof resetOfferMode === 'function') {
                    resetOfferMode();
                }
                submitBtn.innerHTML = 'Terkirim!';

                // Push GA4/GTM conversion events with full session channel attribution
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    event: 'lead_form_submit',
                    lead_service: service,
                    lead_mode: isOfferMode ? 'offer' : 'consultation',
                    page_path: window.location.pathname,
                    traffic_channel: sessionAttr ? sessionAttr.channel : 'direct',
                    traffic_source: sessionAttr ? sessionAttr.source : 'direct',
                    traffic_medium: sessionAttr ? sessionAttr.medium : 'none',
                    traffic_campaign: (sessionAttr && sessionAttr.campaign) ? sessionAttr.campaign : '(none)',
                    landing_page: sessionAttr ? sessionAttr.landing_page : window.location.pathname
                });
                if (typeof window.uoTrackWhatsAppClick === 'function') {
                    window.uoTrackWhatsAppClick('sales', 'contact_form', service);
                }

                const salesNumber = document.getElementById('uo-wa-modal')?.getAttribute('data-wa-sales') || '628212338100';
                const waFallbackHref = `https://api.whatsapp.com/send?phone=${salesNumber}&text=${encodeURIComponent(waText)}`;

                // Inline confirmation + 1-click fallback in case browser blocks async popup tab
                showFormFeedback(
                    `<span><strong>Data Anda berhasil tersimpan!</strong> Menghubungkan ke WhatsApp Tim Sales...</span>
                     <a href="${waFallbackHref}" target="_blank" rel="noopener" data-wa-bypass="1" class="contact-form-wa-fallback">
                        <i class="bi bi-whatsapp"></i> Buka WhatsApp Sales
                     </a>`,
                    'success'
                );

                // Direct launch to Sales WhatsApp (no extra modal step)
                if (typeof window.uoDirectOpenWhatsApp === 'function') {
                    window.uoDirectOpenWhatsApp(salesNumber, waText);
                } else {
                    window.open(waFallbackHref, '_blank', 'noopener,noreferrer');
                }
            } else {
                showFormFeedback('Gagal mengirim pesan: ' + escapeVoHtml(result.message || 'Error tidak diketahui.'), 'error');
            }
        } catch (error) {
            console.error('Submission error:', error);
            showFormFeedback('Terjadi kesalahan saat mengirim pesan. Silakan coba lagi atau hubungi langsung via WhatsApp.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origBtnText;
        }
    });
}

/**
 * Hero Slider Logic (Homepage Only)
 */
let currentSlide = 0;
let slideInterval = null;

function initHeroSlider() {
    const slides = document.querySelectorAll('.slide');
    if (slides.length === 0) return;

    slideInterval = setInterval(nextSlide, 5000);
}

function showSlide(index) {
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    if (slides.length === 0) return;

    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    slides[index].classList.add('active');
    if (dots[index]) {
        dots[index].classList.add('active');
    }
    currentSlide = index;
}

function nextSlide() {
    const slides = document.querySelectorAll('.slide');
    if (slides.length === 0) return;
    let next = (currentSlide + 1) % slides.length;
    showSlide(next);
}

function prevSlide() {
    const slides = document.querySelectorAll('.slide');
    if (slides.length === 0) return;
    let prev = (currentSlide - 1 + slides.length) % slides.length;
    showSlide(prev);
}

/* Arrow button handlers for hero slider */
function nextHeroSlide() {
    clearInterval(slideInterval);
    nextSlide();
    slideInterval = setInterval(nextSlide, 5000);
}

function prevHeroSlide() {
    clearInterval(slideInterval);
    prevSlide();
    slideInterval = setInterval(nextSlide, 5000);
}

function goToSlide(index) {
    clearInterval(slideInterval);
    showSlide(index);
    slideInterval = setInterval(nextSlide, 5000);
}


/**
 * Smart Homepage Search Bar (Option A)
 * Cascading Service Tab -> Available Branches dropdown + Haversine "Di Dekat Saya"
 * Routes directly to the exact branch/city Landing Page URL.
 */
const uoSearchState = {
    category: 'virtual-office',
    serviceName: 'Virtual Office',
    targetFallback: '',
    selectedSlug: '',
    userCoords: null,
    highlightedIndex: -1,
    filteredBranches: []
};

function uoCalcDistanceKm(lat1, lng1, lat2, lng2) {
    const toRad = (deg) => (deg * Math.PI) / 180;
    const R = 6371;
    const dLat = toRad(lat2 - lat1);
    const dLng = toRad(lng2 - lng1);
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
        Math.sin(dLng / 2) * Math.sin(dLng / 2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function uoFormatDistanceKm(km) {
    if (km === null || typeof km !== 'number' || Number.isNaN(km)) return '';
    if (km < 1) return `${Math.round(km * 1000)} m`;
    if (km < 10) return `${km.toFixed(1).replace('.', ',')} km`;
    return `${Math.round(km)} km`;
}

function uoGetSearchBranchesForCategory(category) {
    const catalog = Array.isArray(window.uoSearchCatalog) ? window.uoSearchCatalog : [];
    const list = catalog
        .filter((branch) => branch && branch.services && branch.services[category])
        .map((branch) => {
            let distanceKm = null;
            if (uoSearchState.userCoords && typeof branch.lat === 'number' && typeof branch.lng === 'number') {
                distanceKm = uoCalcDistanceKm(
                    uoSearchState.userCoords.lat,
                    uoSearchState.userCoords.lng,
                    branch.lat,
                    branch.lng
                );
            }
            return { ...branch, distanceKm };
        });

    if (uoSearchState.userCoords) {
        list.sort((a, b) => (a.distanceKm ?? Infinity) - (b.distanceKm ?? Infinity));
    }
    return list;
}

function uoSetSearchFeedback(message, type = '') {
    const feedback = document.getElementById('search-feedback');
    if (!feedback) return;
    feedback.textContent = message || '';
    feedback.classList.remove('is-visible', 'is-error', 'is-success');
    if (message) {
        feedback.classList.add('is-visible');
        if (type === 'error') feedback.classList.add('is-error');
        if (type === 'success') feedback.classList.add('is-success');
    }
}

function uoOpenSearchDropdown(open) {
    const combobox = document.getElementById('search-combobox');
    const dropdown = document.getElementById('search-location-dropdown');
    const input = document.getElementById('search-location-input');
    if (!combobox || !dropdown || !input) return;

    if (open) {
        dropdown.hidden = false;
        combobox.classList.add('is-open');
        input.setAttribute('aria-expanded', 'true');
    } else {
        dropdown.hidden = true;
        combobox.classList.remove('is-open');
        input.setAttribute('aria-expanded', 'false');
        uoSearchState.highlightedIndex = -1;
    }
}

function uoSelectSearchBranch(branch, distanceNote = false) {
    const input = document.getElementById('search-location-input');
    if (!input || !branch) return;
    uoSearchState.selectedSlug = branch.slug;
    const distLabel = (distanceNote && branch.distanceKm !== null)
        ? ` (${uoFormatDistanceKm(branch.distanceKm)})`
        : '';
    input.value = branch.label + distLabel;
    input.classList.remove('search-input-error');
    uoOpenSearchDropdown(false);
}

function uoRenderSearchDropdown(query = '') {
    const dropdown = document.getElementById('search-location-dropdown');
    if (!dropdown) return;

    const allForService = uoGetSearchBranchesForCategory(uoSearchState.category);
    const q = query.trim().toLowerCase();

    // If the input already matches the currently selected branch label, show all options so user can switch easily
    const selectedBranch = allForService.find((b) => b.slug === uoSearchState.selectedSlug);
    const effectiveQuery = (selectedBranch && q.startsWith(selectedBranch.label.toLowerCase())) ? '' : q;

    const matches = effectiveQuery
        ? allForService.filter((b) =>
            b.label.toLowerCase().includes(effectiveQuery) ||
            b.city.toLowerCase().includes(effectiveQuery) ||
            b.location.toLowerCase().includes(effectiveQuery) ||
            b.shortTitle.toLowerCase().includes(effectiveQuery) ||
            (b.keywords && b.keywords.includes(effectiveQuery))
        )
        : allForService;

    uoSearchState.filteredBranches = matches;
    uoSearchState.highlightedIndex = matches.length > 0 ? 0 : -1;

    if (!matches.length) {
        dropdown.innerHTML = `<div class="search-loc-empty">Belum ada cabang <strong>${escapeVoHtml(uoSearchState.serviceName)}</strong> di "${escapeVoHtml(query.trim())}". Pilih dari ${allForService.length} lokasi tersedia.</div>`;
        return;
    }

    const headerHtml = `<div class="search-loc-header">Lokasi ${escapeVoHtml(uoSearchState.serviceName)} Tersedia (${matches.length} cabang)</div>`;
    const itemsHtml = matches.map((b, idx) => {
        const isSelected = b.slug === uoSearchState.selectedSlug;
        const distBadge = (b.distanceKm !== null)
            ? `<span class="search-loc-distance"><i class="bi bi-geo-alt"></i> ${escapeVoHtml(uoFormatDistanceKm(b.distanceKm))}</span>`
            : '';
        return `
            <button type="button" class="search-loc-option${isSelected ? ' is-selected' : ''}${idx === 0 ? ' is-highlighted' : ''}" role="option" aria-selected="${isSelected ? 'true' : 'false'}" data-branch-slug="${escapeVoHtml(b.slug)}" data-idx="${idx}">
                <span class="search-loc-main">
                    <span class="search-loc-title">${escapeVoHtml(b.label)}</span>
                    <span class="search-loc-sub">${escapeVoHtml(b.sublabel || b.location)}</span>
                </span>
                <span class="search-loc-meta">${distBadge}</span>
            </button>
        `;
    }).join('');

    dropdown.innerHTML = headerHtml + itemsHtml;
}

function initHomepageSearch() {
    const form = document.querySelector('.search-form');
    const activeTab = document.querySelector('.search-tab.active') || document.querySelector('.search-tab');
    const input = document.getElementById('search-location-input');
    const dropdown = document.getElementById('search-location-dropdown');
    const toggleBtn = document.getElementById('search-combobox-toggle');
    const combobox = document.getElementById('search-combobox');

    if (activeTab) {
        switchSearchTab(activeTab);
    }

    if (form) {
        form.addEventListener('submit', triggerSearch);
    }

    if (input) {
        input.addEventListener('focus', () => {
            uoRenderSearchDropdown(input.value);
            uoOpenSearchDropdown(true);
        });

        input.addEventListener('click', () => {
            uoRenderSearchDropdown(input.value);
            uoOpenSearchDropdown(true);
        });

        input.addEventListener('input', () => {
            uoSearchState.selectedSlug = '';
            input.classList.remove('search-input-error');
            uoSetSearchFeedback('');
            uoRenderSearchDropdown(input.value);
            uoOpenSearchDropdown(true);
        });

        input.addEventListener('keydown', (e) => {
            if (!dropdown || dropdown.hidden) {
                if (e.key === 'ArrowDown') {
                    uoRenderSearchDropdown(input.value);
                    uoOpenSearchDropdown(true);
                    e.preventDefault();
                }
                return;
            }

            const options = dropdown.querySelectorAll('.search-loc-option');
            if (!options.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                uoSearchState.highlightedIndex = (uoSearchState.highlightedIndex + 1) % options.length;
                options.forEach((opt, i) => opt.classList.toggle('is-highlighted', i === uoSearchState.highlightedIndex));
                options[uoSearchState.highlightedIndex]?.scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                uoSearchState.highlightedIndex = (uoSearchState.highlightedIndex - 1 + options.length) % options.length;
                options.forEach((opt, i) => opt.classList.toggle('is-highlighted', i === uoSearchState.highlightedIndex));
                options[uoSearchState.highlightedIndex]?.scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter' && uoSearchState.highlightedIndex >= 0 && !uoSearchState.selectedSlug) {
                const chosen = uoSearchState.filteredBranches[uoSearchState.highlightedIndex];
                if (chosen) {
                    e.preventDefault();
                    uoSelectSearchBranch(chosen, Boolean(uoSearchState.userCoords));
                    triggerSearch();
                }
            } else if (e.key === 'Escape') {
                uoOpenSearchDropdown(false);
            }
        });
    }

    if (toggleBtn && input) {
        toggleBtn.addEventListener('click', () => {
            const isOpen = combobox && combobox.classList.contains('is-open');
            if (isOpen) {
                uoOpenSearchDropdown(false);
            } else {
                uoRenderSearchDropdown('');
                uoOpenSearchDropdown(true);
                input.focus();
            }
        });
    }

    if (dropdown) {
        dropdown.addEventListener('mousedown', (e) => {
            const optionBtn = e.target.closest('.search-loc-option');
            if (!optionBtn) return;
            e.preventDefault();
            const slug = optionBtn.dataset.branchSlug;
            const branch = uoGetSearchBranchesForCategory(uoSearchState.category).find((b) => b.slug === slug);
            if (branch) {
                uoSelectSearchBranch(branch, Boolean(uoSearchState.userCoords));
                uoSetSearchFeedback('');
            }
        });
    }

    document.addEventListener('click', (e) => {
        if (combobox && !combobox.contains(e.target)) {
            uoOpenSearchDropdown(false);
        }
    });
}

function switchSearchTab(button, serviceName, placeholderText) {
    if (!button) return;

    const tabs = document.querySelectorAll('.search-tab');
    tabs.forEach(t => t.classList.remove('active'));
    button.classList.add('active');

    const category = button.dataset.category || 'virtual-office';
    const service = serviceName || button.dataset.service || button.textContent.trim();
    const placeholder = placeholderText || button.dataset.placeholder || 'Pilih atau ketik kota/cabang...';

    uoSearchState.category = category;
    uoSearchState.serviceName = service;
    uoSearchState.targetFallback = button.dataset.target || '';

    const input = document.getElementById('search-location-input');
    const available = uoGetSearchBranchesForCategory(category);

    if (input) {
        input.placeholder = placeholder;
        input.dataset.service = service;
        input.dataset.category = category;
        input.dataset.target = button.dataset.target || '';

        // Keep selected branch if it also offers the newly selected service; otherwise clear cleanly
        if (uoSearchState.selectedSlug) {
            const stillValid = available.find((b) => b.slug === uoSearchState.selectedSlug);
            if (!stillValid) {
                uoSearchState.selectedSlug = '';
                input.value = '';
                if (uoSearchState.userCoords && available.length > 0) {
                    uoSelectSearchBranch(available[0], true);
                    uoSetSearchFeedback(
                        `Cabang terdekat untuk ${service}: ${available[0].label} (${uoFormatDistanceKm(available[0].distanceKm)})`,
                        'success'
                    );
                } else {
                    uoSetSearchFeedback(`Tersedia di ${available.length} cabang untuk ${service}.`);
                }
            }
        } else {
            uoSetSearchFeedback('');
        }
    }

    const combobox = document.getElementById('search-combobox');
    if (combobox && combobox.classList.contains('is-open')) {
        uoRenderSearchDropdown(input ? input.value : '');
    }

    window.selectedService = service;
    window.selectedServiceTarget = button.dataset.target || '';
}

function triggerSearch(event) {
    if (event) {
        event.preventDefault();
    }

    const input = document.getElementById('search-location-input');
    const rawQuery = input ? input.value.trim() : '';
    const available = uoGetSearchBranchesForCategory(uoSearchState.category);

    let chosenBranch = null;

    // 1. Exact selected branch from dropdown / "Di Dekat Saya"
    if (uoSearchState.selectedSlug) {
        chosenBranch = available.find((b) => b.slug === uoSearchState.selectedSlug) || null;
    }

    // 2. Smart text match against available branches for this service
    if (!chosenBranch && rawQuery) {
        const q = rawQuery.toLowerCase().replace(/^kota\s+/, '');
        chosenBranch =
            available.find((b) => b.slug === q || b.label.toLowerCase() === q || b.city.toLowerCase() === q || b.shortTitle.toLowerCase() === q) ||
            available.find((b) => b.label.toLowerCase().includes(q) || b.location.toLowerCase().includes(q) || (b.keywords && b.keywords.includes(q))) ||
            null;
    }

    const searchAttr = (typeof uoGetAttribution === 'function') ? uoGetAttribution() : null;

    if (chosenBranch && chosenBranch.services && chosenBranch.services[uoSearchState.category]) {
        try {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                event: 'homepage_search',
                search_service: uoSearchState.serviceName,
                search_category: uoSearchState.category,
                search_branch: chosenBranch.slug,
                search_city: chosenBranch.city,
                search_query: rawQuery || chosenBranch.label,
                search_matched: true,
                used_nearby: Boolean(uoSearchState.userCoords),
                traffic_channel: searchAttr ? searchAttr.channel : 'direct',
                traffic_source: searchAttr ? searchAttr.source : 'direct',
                traffic_medium: searchAttr ? searchAttr.medium : 'none',
                landing_page: searchAttr ? searchAttr.landing_page : window.location.pathname
            });
        } catch (err) {}
        window.location.href = chosenBranch.services[uoSearchState.category].landingUrl;
        return;
    }

    if (rawQuery) {
        try {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                event: 'homepage_search',
                search_service: uoSearchState.serviceName,
                search_category: uoSearchState.category,
                search_branch: '',
                search_city: '',
                search_query: rawQuery,
                search_matched: false,
                used_nearby: Boolean(uoSearchState.userCoords),
                traffic_channel: searchAttr ? searchAttr.channel : 'direct',
                traffic_source: searchAttr ? searchAttr.source : 'direct',
                traffic_medium: searchAttr ? searchAttr.medium : 'none',
                landing_page: searchAttr ? searchAttr.landing_page : window.location.pathname
            });
        } catch (err) {}
    }

    // Show clean inline guidance & open dropdown instead of browser alert()
    if (input) {
        input.classList.add('search-input-error');
        input.focus();
        setTimeout(() => input.classList.remove('search-input-error'), 1800);
    }
    uoRenderSearchDropdown(rawQuery);
    uoOpenSearchDropdown(true);

    if (!rawQuery) {
        uoSetSearchFeedback(`Silakan pilih salah satu dari ${available.length} cabang ${uoSearchState.serviceName} di atas, atau klik "Di Dekat Saya".`, 'error');
    } else {
        const citiesList = [...new Set(available.map((b) => b.city))].join(', ');
        uoSetSearchFeedback(`Layanan ${uoSearchState.serviceName} saat ini tersedia di: ${citiesList}. Pilih salah satu dari daftar.`, 'error');
    }
}

function setSearchNearMe(button) {
    const input = document.getElementById('search-location-input');

    if (!navigator.geolocation) {
        uoSetSearchFeedback('Browser Anda belum mendukung fitur lokasi otomatis. Silakan pilih cabang dari daftar.', 'error');
        uoRenderSearchDropdown('');
        uoOpenSearchDropdown(true);
        return;
    }

    const originalHtml = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="bi bi-crosshair"></i><span>Mencari terdekat...</span>';

    navigator.geolocation.getCurrentPosition(
        (position) => {
            uoSearchState.userCoords = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };

            const sorted = uoGetSearchBranchesForCategory(uoSearchState.category);
            if (sorted.length > 0) {
                const nearest = sorted[0];
                uoSelectSearchBranch(nearest, true);
                uoSetSearchFeedback(
                    `Cabang terdekat untuk ${uoSearchState.serviceName}: ${nearest.label} (${uoFormatDistanceKm(nearest.distanceKm)}) — klik Telusuri untuk melihat paket.`,
                    'success'
                );
            } else if (input) {
                input.value = '';
                uoSetSearchFeedback('Belum ada cabang tersedia untuk layanan ini.', 'error');
            }

            button.disabled = false;
            button.innerHTML = originalHtml;
        },
        () => {
            uoSetSearchFeedback('Akses lokasi belum diizinkan. Silakan pilih kota atau cabang dari daftar di atas.', 'error');
            uoRenderSearchDropdown('');
            uoOpenSearchDropdown(true);
            button.disabled = false;
            button.innerHTML = originalHtml;
        },
        {
            enableHighAccuracy: false,
            timeout: 10000,
            maximumAge: 300000
        }
    );
}

window.setSearchNearMe = setSearchNearMe;

/**
 * Multi-Branch Booking Pill Switcher (City Landing Pages with >1 Branch)
 * Updates all Web App booking CTA links inside the pricing/service section to match
 * the chosen branch's exact city_id & location_id.
 */
function initBookingBranchPickers() {
    const pickers = document.querySelectorAll('[data-booking-branch-picker]');
    if (!pickers.length) return;

    pickers.forEach((picker) => {
        const pills = picker.querySelectorAll('.booking-branch-pill');
        const addressEl = picker.querySelector('.booking-branch-address');
        const scopeSelector = picker.dataset.targetScope || '';
        const scopeEl = scopeSelector ? document.querySelector(scopeSelector) : (picker.closest('section') || document);

        if (!pills.length || !scopeEl) return;

        const applyBranch = (pill) => {
            const bookingUrl = pill.dataset.bookingUrl || '';
            const branchLabel = pill.dataset.branchLabel || '';
            const branchAddress = pill.dataset.branchAddress || '';

            pills.forEach((p) => {
                const isActive = p === pill;
                p.classList.toggle('active', isActive);
                p.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            if (addressEl && branchAddress) {
                addressEl.innerHTML = `<i class="bi bi-geo-alt-fill" style="color: hsl(var(--clr-primary));"></i> <strong>${escapeVoHtml(branchLabel)}:</strong> ${escapeVoHtml(branchAddress)}`;
            }

            if (bookingUrl) {
                scopeEl.querySelectorAll('[data-webapp-booking-link]').forEach((link) => {
                    link.setAttribute('href', bookingUrl);
                });
            }
        };

        pills.forEach((pill) => {
            pill.addEventListener('click', () => applyBranch(pill));
        });

        const initialPill = picker.querySelector('.booking-branch-pill.active') || pills[0];
        if (initialPill) {
            applyBranch(initialPill);
        }
    });
}

/**
 * Homepage Branch City Filter
 */
function initBranchesFilter() {
    const filterSections = document.querySelectorAll('[data-branch-filter]');
    if (!filterSections.length) return;

    const normalizeCity = (value) => (value || '')
        .toString()
        .trim()
        .toLowerCase()
        .replace(/^kota\s+/, '');

    filterSections.forEach(section => {
        const buttons = section.querySelectorAll('.branch-city-tabs .branch-city-btn');
        const cards = section.querySelectorAll('.branch-card[data-branch-city]');
        const summary = section.querySelector('.branch-filter-summary');
        if (!buttons.length || !cards.length) return;

        const setActiveCity = (citySlug, preferredBranchId = null) => {
            let activeLabel = '';
            let visibleCount = 0;

            buttons.forEach(button => {
                const isActive = button.dataset.branchCity === citySlug;
                button.classList.toggle('active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
                if (isActive) {
                    activeLabel = button.querySelector('strong')?.textContent?.trim() || button.textContent.trim();
                }
            });

            cards.forEach(card => {
                const isVisible = card.dataset.branchCity === citySlug;
                card.classList.toggle('is-hidden', !isVisible);
                if (isVisible) visibleCount++;
            });

            if (summary) {
                summary.textContent = `Menampilkan ${visibleCount} cabang di ${activeLabel}`;
            }

            if (typeof renderBranchButtons === 'function') {
                renderBranchButtons(citySlug, preferredBranchId);
            }
        };

        buttons.forEach(button => {
            button.addEventListener('click', () => {
                setActiveCity(button.dataset.branchCity);
            });
        });

        // The VO landing template exposes the current branch (from ?branch=) via these globals.
        // Prefer them so a branch page opens on its own city/branch instead of always Surabaya.
        const currentCity = window.currentBranchCity ? normalizeCity(window.currentBranchCity) : null;
        const currentCityBtn = currentCity
            ? Array.from(buttons).find(button => normalizeCity(button.dataset.branchCity) === currentCity)
            : null;

        const queryLocation = normalizeCity(new URLSearchParams(window.location.search).get('lokasi'));
        const queryMatch = queryLocation
            ? Array.from(buttons).find(button => normalizeCity(button.dataset.branchCity) === queryLocation || normalizeCity(button.textContent).includes(queryLocation))
            : null;
        const activeButton = currentCityBtn || queryMatch || section.querySelector('.branch-city-btn.active') || buttons[0];

        setActiveCity(activeButton.dataset.branchCity, window.currentBranchId || null);
    });
}

/**
 * Contact Form Review Slider Logic
 */
let currentContactReview = 0;
let contactReviewInterval = null;

function initContactReviewSlider() {
    const slides = document.querySelectorAll('.contact-review-slide');
    if (slides.length === 0) return;

    contactReviewInterval = setInterval(nextContactReview, 6000);
}

function showContactReview(index) {
    const slides = document.querySelectorAll('.contact-review-slide');
    const dots = document.querySelectorAll('.contact-dot');
    if (slides.length === 0) return;

    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));

    slides[index].classList.add('active');
    if (dots[index]) {
        dots[index].classList.add('active');
    }
    currentContactReview = index;
}

function nextContactReview() {
    const slides = document.querySelectorAll('.contact-review-slide');
    if (slides.length === 0) return;
    let next = (currentContactReview + 1) % slides.length;
    showContactReview(next);
}

function prevContactReview() {
    const slides = document.querySelectorAll('.contact-review-slide');
    if (slides.length === 0) return;
    let prev = (currentContactReview - 1 + slides.length) % slides.length;
    showContactReview(prev);
}

function goToContactReview(index) {
    clearInterval(contactReviewInterval);
    showContactReview(index);
    contactReviewInterval = setInterval(nextContactReview, 6000);
}

/**
 * Client Brands Slider Logic
 */
let currentBrandIndex = 0;
let brandSliderInterval = null;
const brandVisibleCount = {
    desktop: 5,
    tabletLandscape: 4,
    tabletPortrait: 3,
    mobile: 2
};

function getBrandVisibleCount() {
    const width = window.innerWidth;
    if (width > 991) return brandVisibleCount.desktop;
    if (width > 768) return brandVisibleCount.tabletLandscape;
    if (width > 576) return brandVisibleCount.tabletPortrait;
    return brandVisibleCount.mobile;
}

function initBrandsSlider() {
    const slider = document.getElementById('brands-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.brand-slide');
    const totalSlides = slides.length;
    if (totalSlides === 0) return;

    // Build dots
    initBrandsSliderDots(totalSlides);

    // Set transition handler
    window.addEventListener('resize', () => {
        initBrandsSliderDots(totalSlides);
        showBrandSlide(currentBrandIndex);
    });

    brandSliderInterval = setInterval(nextBrandSlide, 4000);
}

function initBrandsSliderDots(totalSlides) {
    const dotsContainer = document.getElementById('brands-dots');
    if (!dotsContainer) return;
    dotsContainer.innerHTML = '';
    const visibleCount = getBrandVisibleCount();
    const maxDots = Math.max(1, totalSlides - visibleCount + 1);
    
    for (let i = 0; i < maxDots; i++) {
        const dot = document.createElement('span');
        dot.className = 'brand-dot' + (i === currentBrandIndex ? ' active' : '');
        dot.setAttribute('onclick', `goToBrandSlide(${i})`);
        dotsContainer.appendChild(dot);
    }
}

function showBrandSlide(index) {
    const slider = document.getElementById('brands-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.brand-slide');
    const totalSlides = slides.length;
    const visibleCount = getBrandVisibleCount();
    
    // Bounds check
    const maxIndex = Math.max(0, totalSlides - visibleCount);
    if (index > maxIndex) {
        index = 0; // Wrap around to first
    } else if (index < 0) {
        index = maxIndex;
    }

    currentBrandIndex = index;
    const slideWidth = 100 / visibleCount;
    slider.style.transform = `translateX(-${index * slideWidth}%)`;

    // Update active dot
    const dots = document.querySelectorAll('.brand-dot');
    dots.forEach((dot, idx) => {
        if (idx === index) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

function nextBrandSlide() {
    const slider = document.getElementById('brands-slider');
    if (!slider) return;
    const slides = slider.querySelectorAll('.brand-slide');
    const totalSlides = slides.length;
    const visibleCount = getBrandVisibleCount();
    const maxIndex = Math.max(0, totalSlides - visibleCount);

    if (currentBrandIndex >= maxIndex) {
        showBrandSlide(0);
    } else {
        showBrandSlide(currentBrandIndex + 1);
    }
}

function prevBrandSlide() {
    const slider = document.getElementById('brands-slider');
    if (!slider) return;
    const slides = slider.querySelectorAll('.brand-slide');
    const totalSlides = slides.length;
    const visibleCount = getBrandVisibleCount();
    const maxIndex = Math.max(0, totalSlides - visibleCount);

    if (currentBrandIndex <= 0) {
        showBrandSlide(maxIndex);
    } else {
        showBrandSlide(currentBrandIndex - 1);
    }
}

function goToBrandSlide(index) {
    clearInterval(brandSliderInterval);
    showBrandSlide(index);
    brandSliderInterval = setInterval(nextBrandSlide, 4000);
}

/**
 * Why VO Slider Logic (Virtual Office page only)
 */
let currentWhyVoIndex = 0;
let whyVoSliderInterval = null;
const whyVoVisibleCount = {
    desktop: 3,
    tablet: 2,
    mobile: 1
};

function getWhyVoVisibleCount() {
    const width = window.innerWidth;
    if (width > 991) return whyVoVisibleCount.desktop;
    if (width > 768) return whyVoVisibleCount.tablet;
    return whyVoVisibleCount.mobile;
}

function initWhyVoSlider() {
    const slider = document.getElementById('why-vo-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.why-vo-slide');
    const totalSlides = slides.length;
    if (totalSlides === 0) return;

    // Build dots
    initWhyVoSliderDots(totalSlides);

    // Set transition handler
    window.addEventListener('resize', () => {
        initWhyVoSliderDots(totalSlides);
        showWhyVoSlide(currentWhyVoIndex);
    });

    whyVoSliderInterval = setInterval(nextWhyVoSlide, 5000);
}

function initWhyVoSliderDots(totalSlides) {
    const dotsContainer = document.getElementById('why-vo-dots');
    if (!dotsContainer) return;
    dotsContainer.innerHTML = '';
    const visibleCount = getWhyVoVisibleCount();
    const maxDots = Math.max(1, totalSlides - visibleCount + 1);
    
    for (let i = 0; i < maxDots; i++) {
        const dot = document.createElement('span');
        dot.className = 'why-vo-dot' + (i === currentWhyVoIndex ? ' active' : '');
        dot.setAttribute('onclick', `goToWhyVoSlide(${i})`);
        dotsContainer.appendChild(dot);
    }
}

function showWhyVoSlide(index) {
    const slider = document.getElementById('why-vo-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.why-vo-slide');
    const totalSlides = slides.length;
    const visibleCount = getWhyVoVisibleCount();
    
    // Bounds check
    const maxIndex = Math.max(0, totalSlides - visibleCount);
    if (index > maxIndex) {
        index = 0;
    } else if (index < 0) {
        index = maxIndex;
    }

    currentWhyVoIndex = index;
    // Derive the per-slide step from the actual rendered slide width (relative to the
    // viewport wrap) so any CSS flex-basis — including the mobile "peek" width (<100%) —
    // stays snapped. On desktop/tablet this equals the old 100/visibleCount value.
    const wrap = slider.parentElement;
    const wrapPx = wrap ? wrap.getBoundingClientRect().width : 0;
    const slidePx = slides[0] ? slides[0].getBoundingClientRect().width : 0;
    const slideWidth = (wrapPx > 0 && slidePx > 0) ? (slidePx / wrapPx) * 100 : (100 / visibleCount);
    let offset = index * slideWidth;
    // Mobile peek mode (one card + a sliver of the next): center the LAST card so the
    // previous card peeks on the left, instead of the card being stuck to the left edge
    // with blank space on the right.
    if (visibleCount === 1 && slideWidth < 99 && index === maxIndex && index > 0) {
        offset = index * slideWidth - (100 - slideWidth) / 2;
        if (offset < 0) offset = 0;
    }
    slider.style.transform = `translateX(-${offset}%)`;

    // Update active dot
    const dots = document.querySelectorAll('.why-vo-dot');
    dots.forEach((dot, idx) => {
        if (idx === index) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

function nextWhyVoSlide() {
    const slider = document.getElementById('why-vo-slider');
    if (!slider) return;
    const slides = slider.querySelectorAll('.why-vo-slide');
    const totalSlides = slides.length;
    const visibleCount = getWhyVoVisibleCount();
    const maxIndex = Math.max(0, totalSlides - visibleCount);

    if (currentWhyVoIndex >= maxIndex) {
        showWhyVoSlide(0);
    } else {
        showWhyVoSlide(currentWhyVoIndex + 1);
    }
}

function goToWhyVoSlide(index) {
    clearInterval(whyVoSliderInterval);
    showWhyVoSlide(index);
    whyVoSliderInterval = setInterval(nextWhyVoSlide, 5000);
}

/**
 * Benefits VO Slider Logic (Virtual Office page only)
 */
let currentBenefitsVoIndex = 0;
let benefitsVoSliderInterval = null;
const benefitsVoVisibleCount = {
    desktop: 3,
    tablet: 2,
    mobile: 1
};

function getBenefitsVoVisibleCount() {
    // Always 1: this slider now sits in a half-width column (two-column "Keuntungan &
    // Keunggulan" section), so it shows one image at a time on every breakpoint.
    return 1;
}

function initBenefitsVoSlider() {
    const slider = document.getElementById('benefits-vo-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.benefits-vo-slide');
    const totalSlides = slides.length;
    if (totalSlides === 0) return;

    // Build dots
    initBenefitsVoSliderDots(totalSlides);

    // Set transition handler
    window.addEventListener('resize', () => {
        initBenefitsVoSliderDots(totalSlides);
        showBenefitsVoSlide(currentBenefitsVoIndex);
    });

    benefitsVoSliderInterval = setInterval(nextBenefitsVoSlide, 5000);
}

function initBenefitsVoSliderDots(totalSlides) {
    const dotsContainer = document.getElementById('benefits-vo-dots');
    if (!dotsContainer) return;
    dotsContainer.innerHTML = '';
    // One dot per slide — every slide can become the primary one (the next slide just peeks).
    for (let i = 0; i < totalSlides; i++) {
        const dot = document.createElement('span');
        dot.className = 'benefits-vo-dot' + (i === currentBenefitsVoIndex ? ' active' : '');
        dot.setAttribute('onclick', `goToBenefitsVoSlide(${i})`);
        dotsContainer.appendChild(dot);
    }
}

function showBenefitsVoSlide(index) {
    const slider = document.getElementById('benefits-vo-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.benefits-vo-slide');
    const totalSlides = slides.length;
    if (!totalSlides) return;

    // Every slide can be primary; the CSS slide width (<100%) makes the next one peek.
    const maxIndex = totalSlides - 1;
    if (index > maxIndex) {
        index = 0;
    } else if (index < 0) {
        index = maxIndex;
    }

    currentBenefitsVoIndex = index;
    // Pixel-based transform reads the real rendered slide width, so the peek stays exact on
    // every breakpoint regardless of the CSS percentage.
    const slideWidth = slides[0].getBoundingClientRect().width;
    slider.style.transform = `translateX(-${index * slideWidth}px)`;

    const dots = document.querySelectorAll('.benefits-vo-dot');
    dots.forEach((dot, idx) => dot.classList.toggle('active', idx === index));
}

function nextBenefitsVoSlide() {
    const slider = document.getElementById('benefits-vo-slider');
    if (!slider) return;
    const totalSlides = slider.querySelectorAll('.benefits-vo-slide').length;

    if (currentBenefitsVoIndex >= totalSlides - 1) {
        showBenefitsVoSlide(0);
    } else {
        showBenefitsVoSlide(currentBenefitsVoIndex + 1);
    }
}

function goToBenefitsVoSlide(index) {
    clearInterval(benefitsVoSliderInterval);
    showBenefitsVoSlide(index);
    benefitsVoSliderInterval = setInterval(nextBenefitsVoSlide, 5000);
}

/**
 * Pricing Location Filter Logic
 */
// Pricing data for the branch switcher now comes from PHP (inc/footer.php), generated from
// inc/locations_data.php so there is a single source of truth (prices, address, KPP, tier
// count). Falls back to an empty object if the inline script wasn't emitted.
const branchPricingData = window.branchPricingData || {};

const cityBranches = {
    'surabaya': [
        { id: 'surabaya', label: 'MERR (Surabaya Timur)' },
        { id: 'surabaya-timur', label: 'Klampis (Surabaya Timur)' },
        { id: 'surabaya-barat', label: 'Grand Sungkono (Surabaya Barat)' }
    ],
    'jakarta': [
        { id: 'jakarta-timur', label: 'Gorebiz (Jakarta Timur)' }
    ],
    'gresik': [
        { id: 'gresik', label: 'PTGM Tower (Gresik)' }
    ],
    'medan': [
        { id: 'medan', label: 'Medan' }
    ],
    'malang': [
        { id: 'malang', label: 'Malang' }
    ]
};

function initPricingFilter() {
    // Legacy pricing filter init (removed as it's now integrated with branches.php city filter)
}

function renderBranchButtons(city, preferredBranchId = null) {
    const branchContainer = document.getElementById('branch-filter');
    if (!branchContainer) return;

    branchContainer.innerHTML = '';
    const branches = cityBranches[city] || [];

    // Which branch's pricing to show first: the one from the page URL (?branch=) when it
    // belongs to this city, otherwise the city's first branch. This keeps a Jakarta Timur
    // (Gorebiz) landing page from defaulting to another branch, etc.
    const initialId = branches.some(b => b.id === preferredBranchId) ? preferredBranchId : (branches[0] ? branches[0].id : null);

    if (branches.length > 1) {
        branchContainer.style.display = 'flex';
        branches.forEach((br) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'branch-city-btn branch-detail-btn-small';
            if (br.id === initialId) btn.classList.add('active');
            btn.setAttribute('data-branch', br.id);
            btn.innerHTML = `<span class="branch-city-icon"><i class="bi bi-geo-alt-fill"></i></span>
                             <span class="branch-city-copy"><strong>${br.label}</strong></span>`;
            btn.addEventListener('click', () => {
                document.querySelectorAll('#branch-filter .branch-city-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                updatePricingCards(br.id, true);

                // Filter the branch cards grid based on the detail location clicked
                const gridCards = document.querySelectorAll('.branches-grid .branch-card');
                if (gridCards.length > 0) {
                    gridCards.forEach(card => {
                        if (card.dataset.branchSlug === br.id) {
                            card.classList.remove('is-hidden');
                        } else {
                            card.classList.add('is-hidden');
                        }
                    });
                }
            });
            branchContainer.appendChild(btn);
        });
        if (initialId) updatePricingCards(initialId, false);
    } else if (branches.length === 1) {
        branchContainer.style.display = 'none';
        updatePricingCards(branches[0].id, false);
    }
}

function escapeVoHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function buildVoPricingCardHtml(tier, branchData, showKpp) {
    const isPopular = Boolean(tier.popular);
    const btnClass = isPopular ? 'btn-primary' : 'btn-outline';
    const hasWebapp = Boolean(branchData.hasWebappBooking && branchData.bookingUrl);
    const ctaLabel = isPopular
        ? (hasWebapp ? 'Booking Paket Populer' : 'Pilih Paket Populer')
        : (hasWebapp ? 'Booking Online' : 'Beli Sekarang');
    const borderColor = isPopular ? 'hsl(var(--clr-primary))' : 'hsl(var(--clr-border))';
    const period = tier.period || 'hari';

    const waMsg = tier.cta_wa
        ? tier.cta_wa
        : `Halo Urban Office, saya tertarik dengan ${tier.name} di Cabang ${branchData.shortTitle || branchData.name} (${branchData.location || ''}). Mohon info penawaran selengkapnya.`;
    const waUrl = `https://api.whatsapp.com/send?phone=6285107620100&text=${encodeURIComponent(waMsg)}`;
    const primaryHref = hasWebapp ? branchData.bookingUrl : waUrl;

    const kppText = branchData.kpp || '';
    const kppStyle = `font-weight: bold; color: #FF6B00; margin-top: 5px; font-size: 0.9rem; display: ${(showKpp && kppText) ? 'block' : 'none'};`;

    const highlights = Array.isArray(tier.highlights) && tier.highlights.length > 0
        ? tier.highlights
        : (tier.features || []).slice(0, 3);
    const highlightsHtml = highlights.length > 0
        ? `<ul class="vo-highlights-list">${highlights.map(h => `<li><i class="bi bi-check-circle-fill"></i><span>${escapeVoHtml(h)}</span></li>`).join('')}</ul>`
        : '';

    const featuresItems = [
        `<li class="address-feature">Alamat Bisnis: ${escapeVoHtml(branchData.address)}</li>`,
        ...((tier.features || []).map(f => `<li>${escapeVoHtml(f)}</li>`))
    ].join('');

    return `
        <div class="premium-card ${isPopular ? 'popular-card' : ''}" style="display: flex; flex-direction: column; justify-content: space-between; position: relative; border-color: ${borderColor};">
            ${isPopular ? '<div class="pricing-badge-popular">Populer</div>' : ''}
            <div class="vo-card-body">
                <h3 class="vo-card-title">${escapeVoHtml(tier.name)}</h3>
                <p class="vo-card-desc">${escapeVoHtml(tier.desc)}</p>

                <div class="vo-price-block">
                    <span class="vo-price-label">Hanya</span>
                    <div class="price-tag vo-price-highlight">
                        Rp ${escapeVoHtml(tier.price)}-an
                        <span>/ ${escapeVoHtml(period)}</span>
                    </div>
                    <p class="price-minimum-info">*Minimum sewa 1 tahun</p>
                </div>
                <p class="kpp-info" style="${kppStyle}">${escapeVoHtml(kppText)}</p>
                ${highlightsHtml}

                <button type="button" class="btn-toggle-features" onclick="togglePricingFeatures(this)">
                    <span>Lihat Detail</span> <i class="bi bi-chevron-down"></i>
                </button>

                <div class="pricing-features-collapse">
                    <ul class="card-features-list">${featuresItems}</ul>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; flex-direction: column; gap: 10px;">
                <a href="${escapeVoHtml(primaryHref)}" class="btn ${btnClass} cta-btn" style="width: 100%;" target="_blank" rel="noopener">
                    ${escapeVoHtml(ctaLabel)}
                </a>
                <button type="button" class="btn btn-secondary offer-btn" style="width: 100%; font-size: 13px;" data-package="${escapeVoHtml(tier.name)}" onclick="activateOfferMode(this.dataset.package)">
                    Ajukan Penawaran
                </button>
            </div>
        </div>
    `;
}

function updatePricingCards(branchId, showKpp = true) {
    window.activeBranchId = branchId;
    const data = branchPricingData[branchId];
    if (!data || !Array.isArray(data.tiers)) return;

    const gridEl = document.getElementById('pricing-grid');
    if (!gridEl) return;

    if (gridEl.dataset.branchId === branchId) {
        gridEl.querySelectorAll('.kpp-info').forEach(el => {
            el.textContent = data.kpp || '';
            el.style.display = (showKpp && data.kpp) ? 'block' : 'none';
        });
        return;
    }

    gridEl.dataset.branchId = branchId;
    gridEl.classList.remove('has-expanded', 'card-grid--four', 'card-grid--two', 'card-grid--one');
    if (data.tiers.length >= 4) {
        gridEl.classList.add('card-grid--four');
    } else if (data.tiers.length === 2) {
        gridEl.classList.add('card-grid--two');
    } else if (data.tiers.length === 1) {
        gridEl.classList.add('card-grid--one');
    }

    gridEl.innerHTML = data.tiers.map(tier => buildVoPricingCardHtml(tier, data, showKpp)).join('');
    gridEl.scrollLeft = 0;
}

/**
 * Offer Mode Handler Function
 */
window.activateOfferMode = function(packageName, serviceName = 'Virtual Office', branchName = '') {
    const isOfferModeEl = document.getElementById('is-offer-mode');
    const offerPackageEl = document.getElementById('offer-package');
    if (isOfferModeEl) isOfferModeEl.value = '1';
    if (offerPackageEl) offerPackageEl.value = packageName;

    // Toggle fields
    const bookingRow = document.getElementById('visit-booking-row');
    if (bookingRow) bookingRow.style.display = 'none';

    const locGroup = document.getElementById('offer-location-group');
    const budgetGroup = document.getElementById('offer-budget-group');
    const msgGroup = document.getElementById('offer-message-group');

    if (locGroup) locGroup.style.display = 'block';
    if (budgetGroup) budgetGroup.style.display = 'block';
    if (msgGroup) msgGroup.style.display = 'block';

    // Auto fill location
    const locInput = document.getElementById('contact-location');
    if (locInput) {
        let val = '';
        if (branchName) {
            val = branchName;
        } else {
            const activeBranch = branchPricingData[window.activeBranchId || 'surabaya'];
            val = activeBranch ? activeBranch.name : 'MERR (Surabaya Timur)';
        }

        if (locInput.tagName === 'SELECT') {
            for (let option of locInput.options) {
                if (option.value === val || option.value.startsWith(val)) {
                    option.selected = true;
                    break;
                }
            }
        } else {
            locInput.value = val;
        }
    }

    // Toggle required validations
    const dateInput = document.getElementById('contact-date');
    if (dateInput) dateInput.required = false;

    const budgetInput = document.getElementById('contact-budget');
    if (budgetInput) budgetInput.required = true;

    const pesanInput = document.getElementById('contact-pesan');
    if (pesanInput) pesanInput.required = true;

    // Set service dropdown to designated serviceName
    const serviceInput = document.getElementById('contact-service');
    if (serviceInput) {
        serviceInput.value = serviceName;
    }

    // Scroll to contact form
    const contactSection = document.getElementById('contact');
    if (contactSection) {
        contactSection.scrollIntoView({ behavior: 'smooth' });
    }
};

/**
 * Reset Offer Mode Function
 */
window.resetOfferMode = function() {
    const isOfferModeEl = document.getElementById('is-offer-mode');
    const offerPackageEl = document.getElementById('offer-package');
    if (isOfferModeEl) isOfferModeEl.value = '0';
    if (offerPackageEl) offerPackageEl.value = '';

    // Show booking row
    const bookingRow = document.getElementById('visit-booking-row');
    if (bookingRow) bookingRow.style.display = 'flex';

    // Hide offer rows
    const locGroup = document.getElementById('offer-location-group');
    const budgetGroup = document.getElementById('offer-budget-group');
    const msgGroup = document.getElementById('offer-message-group');

    if (locGroup) locGroup.style.display = 'none';
    if (budgetGroup) budgetGroup.style.display = 'none';
    if (msgGroup) msgGroup.style.display = 'none';

    // Clear values
    const locInput = document.getElementById('contact-location');
    if (locInput) locInput.value = '';

    const budgetInput = document.getElementById('contact-budget');
    if (budgetInput) budgetInput.value = '';

    const pesanInput = document.getElementById('contact-pesan');
    if (pesanInput) pesanInput.value = '';

    // Restore required status — but only for visit-based services. Virtual Office and other
    // lead/quote pages mark the date optional via data-visit-required="0", so don't force it.
    const dateInput = document.getElementById('contact-date');
    if (dateInput) dateInput.required = (dateInput.dataset.visitRequired !== '0');

    const budgetInput2 = document.getElementById('contact-budget');
    if (budgetInput2) budgetInput2.required = false;

    const pesanInput2 = document.getElementById('contact-pesan');
    if (pesanInput2) pesanInput2.required = false;
};

/**
 * Infinite scrolling marquee for Our Group and Featured On logos on mobile
 */
function initMobileLogosMarquee() {
    const runMarquee = () => {
        const grids = document.querySelectorAll('.our-group-logos-grid, .featured-logos-grid');
        grids.forEach(grid => {
            if (window.innerWidth <= 768) {
                if (grid.dataset.marqueeInitialized === 'true') return;
                
                // Save original HTML before cloning
                grid.dataset.originalHtml = grid.innerHTML;
                
                // Clone the child elements to create an infinite loop
                const children = Array.from(grid.children);
                children.forEach(child => {
                    const clone = child.cloneNode(true);
                    grid.appendChild(clone);
                });
                
                grid.classList.add('marquee-active');
                grid.dataset.marqueeInitialized = 'true';
            } else {
                // Restore to original layout on desktop
                if (grid.dataset.marqueeInitialized === 'true') {
                    grid.innerHTML = grid.dataset.originalHtml;
                    grid.classList.remove('marquee-active');
                    grid.dataset.marqueeInitialized = 'false';
                }
            }
        });
    };
    
    runMarquee();
    window.addEventListener('resize', runMarquee);
}

/**
 * Global Pricing Cards Details Toggle
 */
function togglePricingFeatures(btn) {
    const card = btn.closest('.premium-card') || btn.closest('.pricing-card-custom') || btn.closest('.pricing-card');
    if (!card) return;
    const collapse = card.querySelector('.pricing-features-collapse');
    if (!collapse) return;
    const label = btn.querySelector('span');
    const grid = card.closest('.pricing-grid-custom') || card.closest('.pricing-grid') || card.closest('.card-grid');
    
    const isOpening = !(collapse.style.maxHeight && collapse.style.maxHeight !== '0px');
    
    if (isOpening) {
        collapse.style.maxHeight = collapse.scrollHeight + 'px';
        if (label) label.textContent = 'Sembunyikan Detail';
        btn.classList.add('active');
        card.classList.add('expanded');
        
        if (grid) {
            grid.classList.add('has-expanded');
        }
    } else {
        collapse.style.maxHeight = '0px';
        if (label) label.textContent = 'Lihat Detail';
        btn.classList.remove('active');
        card.classList.remove('expanded');
        
        if (grid) {
            // Wait for the collapse animation (300ms) to finish before resetting container alignment
            // Using 400ms buffer to ensure collapse transition has fully ended and avoided layout snaps
            setTimeout(() => {
                const anyExpanded = Array.from(grid.querySelectorAll('.btn-toggle-features')).some(b => b.classList.contains('active'));
                if (!anyExpanded) {
                    grid.classList.remove('has-expanded');
                }
            }, 400);
        }
    }
}

window.togglePricingFeatures = togglePricingFeatures;

/**
 * Direct WhatsApp Bypass Helper
 * - On Mobile: launches WhatsApp app directly via whatsapp:// URI scheme (skips interstitial browser prompt)
 * - On Desktop: opens WhatsApp Web directly via web.whatsapp.com (skips api.whatsapp.com 'Continue to Chat' page)
 */
function uoDirectOpenWhatsApp(phone, message) {
    let cleanPhone = String(phone || '').replace(/\D/g, '');
    if (cleanPhone.startsWith('0')) {
        cleanPhone = '62' + cleanPhone.substring(1);
    }
    if (!cleanPhone) {
        cleanPhone = '628212338100';
    }

    const encodedMsg = message ? encodeURIComponent(message) : '';
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

    if (isMobile) {
        const appUrl = `whatsapp://send?phone=${cleanPhone}${encodedMsg ? '&text=' + encodedMsg : ''}`;
        const fallbackUrl = `https://api.whatsapp.com/send?phone=${cleanPhone}${encodedMsg ? '&text=' + encodedMsg : ''}`;
        
        const start = Date.now();
        window.location.href = appUrl;

        // Fallback for mobile devices without WhatsApp installed
        setTimeout(() => {
            if (Date.now() - start < 2200 && document.hasFocus()) {
                window.location.href = fallbackUrl;
            }
        }, 1500);
    } else {
        const webUrl = `https://web.whatsapp.com/send?phone=${cleanPhone}${encodedMsg ? '&text=' + encodedMsg : ''}`;
        window.open(webUrl, '_blank', 'noopener,noreferrer');
    }
}
window.uoDirectOpenWhatsApp = uoDirectOpenWhatsApp;

/**
 * Smart WhatsApp Message Template Generator
 * Builds tailored, context-aware messages for Sales vs Customer Service based on:
 * - Specific branch location (MERR, Klampis, Grand Sungkono Lagoon, Fatmawati, Gorebiz, Gresik, Medan, Malang, etc.)
 * - Specific service / package / product (Virtual Office, Serviced Office, Coworking Space, Meeting Room, Legalitas PT/CV, Kemitraan)
 * - Form submission payload
 * - Current page and DOM context
 */
function uoBuildWhatsAppMessages(rawText, triggerEl = null) {
    const raw = (typeof rawText === 'string') ? rawText.trim() : '';

    // 1. Detect if this is a submitted contact form
    const isForm = raw.includes('[Pengajuan Penawaran]') ||
                   raw.includes('[Pemesanan Kunjungan]') ||
                   raw.includes('Saya ingin mengajukan penawaran') ||
                   raw.includes('Saya ingin berkonsultasi mengenai:');

    if (isForm) {
        return {
            summary: 'Formulir Pengajuan Website',
            salesMessage: raw,
            csMessage: `Halo Tim Customer Service Urban Office, saya telah mengisi formulir di website dan ingin berkonsultasi / menyampaikan kendala operasional:\n\n${raw}`
        };
    }

    // 2. Extract Branch context
    let branchName = '';
    let branchLoc = '';

    // Check DOM trigger first (closest .branch-card / .location-card)
    if (triggerEl && typeof triggerEl.closest === 'function') {
        const branchCard = triggerEl.closest('.branch-card, .location-card, .lokasi-card');
        if (branchCard) {
            const h = branchCard.querySelector('h3, h4, .branch-title, .location-title');
            const l = branchCard.querySelector('.branch-loc, .location-address, p');
            if (h) branchName = h.innerText.replace(/[\r\n]+/g, ' ').trim();
            if (l) branchLoc = l.innerText.replace(/[\r\n]+/g, ' ').trim();
        }
    }

    // Extract branch from rawText if not found in DOM
    if (!branchName && raw) {
        const m1 = raw.match(/Cabang:\s*\*?([^\n*]+)\*?/i);
        const m2 = raw.match(/(?:di\s+)?Cabang\s+([A-Za-z0-9\s\-]+?)(?:\s*\(|\.|\n|$)/i);
        const m3 = raw.match(/(Urban\s+Office\s*-\s*[A-Za-z0-9\s]+?)(?:\s*\(|\.|\n|$)/i);

        if (m1) {
            branchName = m1[1].trim();
        } else if (m2) {
            branchName = 'Cabang ' + m2[1].trim();
        } else if (m3) {
            branchName = m3[1].trim();
        }

        const locMatch = raw.match(/Lokasi:\s*\*?([^\n*]+)\*?/i);
        if (locMatch) {
            branchLoc = locMatch[1].trim();
        }
    }

    if (branchName) {
        branchName = branchName.replace(/\s+/g, ' ').trim().replace(/[.,;:]$/, '');
    }

    // 3. Extract Product / Service context
    let productName = '';

    // Check DOM trigger for pricing or package cards
    if (triggerEl && typeof triggerEl.closest === 'function') {
        const pkgCard = triggerEl.closest('.pricing-card, .package-card, .price-card, .service-card');
        if (pkgCard) {
            const pkgTitle = pkgCard.querySelector('h3, h4, .package-name, .pricing-title, .title');
            if (pkgTitle) productName = pkgTitle.innerText.replace(/[\r\n]+/g, ' ').trim();
        }
    }

    // Check rawText
    if (!productName && raw) {
        const p1 = raw.match(/tertarik\s+dengan\s+(?:paket\s+|layanan\s+)?\*?([^\n*.]+)\*?/i);
        if (p1) {
            productName = p1[1].trim();
        } else if (raw.toLowerCase().includes('kemitraan')) {
            productName = 'Kemitraan Urban Office';
        } else if (raw.toLowerCase().includes('virtual office')) {
            productName = 'Virtual Office';
        } else if (raw.toLowerCase().includes('coworking')) {
            productName = 'Coworking Space';
        } else if (raw.toLowerCase().includes('sewa kantor') || raw.toLowerCase().includes('serviced office')) {
            productName = 'Sewa Kantor / Serviced Office';
        } else if (raw.toLowerCase().includes('meeting room') || raw.toLowerCase().includes('ruang meeting')) {
            productName = 'Meeting Room';
        }
    }

    // Check page URL if neither branch nor product found yet
    const path = window.location.pathname.toLowerCase();
    if (!productName) {
        if (path.includes('virtual-office')) {
            productName = 'Virtual Office';
        } else if (path.includes('coworking')) {
            productName = 'Coworking Space';
        } else if (path.includes('sewa-kantor')) {
            productName = 'Sewa Kantor / Serviced Office';
        } else if (path.includes('meeting-room')) {
            productName = 'Meeting Room';
        } else if (path.includes('pendirian-pt')) {
            productName = 'Pendirian PT + Virtual Office';
        } else if (path.includes('pendirian-cv')) {
            productName = 'Pendirian CV + Virtual Office';
        } else if (path.includes('pendirian-perorangan')) {
            productName = 'PT Perorangan + Virtual Office';
        } else if (path.includes('pendirian-pma')) {
            productName = 'PT PMA + Virtual Office';
        } else if (path.includes('pajak-dan-akunting')) {
            productName = 'Pajak & Akunting';
        } else if (path.includes('perizinan-dan-perubahan')) {
            productName = 'Perizinan & Perubahan Perusahaan';
        }
    }

    // If still no branch, check if on branch detail page
    if (!branchName && path.includes('lokasi-urban-office')) {
        const pageHeading = document.querySelector('h1, .page-title, .hero-title');
        if (pageHeading) {
            const hText = pageHeading.innerText.replace(/[\r\n]+/g, ' ').trim();
            if (hText.toLowerCase().includes('urban office') || hText.toLowerCase().includes('cabang')) {
                branchName = hText;
            }
        }
    }

    // 4. Construct message for Sales and Customer Service
    // Sales Message: Tetap pertahankan setup kalimat asli bawaan website dari masing-masing tombol / link
    const salesMsg = raw || 'Halo Urban Office, saya tertarik dengan layanan Urban Office. Mohon info selengkapnya.';

    // Customer Service Message: Disesuaikan secara relevan dengan konteks trigger (cabang / layanan / operasional / kendala)
    let summary = '';
    let csMsg = '';

    const isKemitraan = (productName && productName.toLowerCase().includes('kemitraan')) || raw.toLowerCase().includes('kemitraan');

    if (isKemitraan) {
        summary = 'Kemitraan Bisnis Urban Office';
        csMsg = 'Halo Tim Customer Service Urban Office, saya mitra / pengelola ingin berkoordinasi dan menyampaikan pertanyaan operasional terkait kemitraan Urban Office. Mohon dibantu.';
    } else if (branchName && productName) {
        summary = `${productName} • ${branchName}`;
        csMsg = `Halo Tim Customer Service Urban Office, saya pengguna/member layanan *${productName}* di *${branchName}* dan butuh bantuan / ingin menyampaikan pertanyaan operasional. Mohon dibantu.`;
    } else if (branchName) {
        summary = `Cabang: ${branchName}`;
        csMsg = `Halo Tim Customer Service Urban Office, saya butuh bantuan / ingin menyampaikan pertanyaan operasional maupun kendala fasilitas terkait cabang *${branchName}*. Mohon dibantu.`;
    } else if (productName) {
        summary = `Layanan: ${productName}`;
        csMsg = `Halo Tim Customer Service Urban Office, saya ingin bertanya seputar informasi operasional / butuh bantuan terkait layanan *${productName}*. Mohon dibantu.`;
    } else {
        summary = '';
        csMsg = 'Halo Tim Customer Service Urban Office, saya butuh bantuan operasional / ingin menyampaikan kendala atau keluhan terkait layanan Urban Office. Mohon bantuannya.';
    }

    return {
        summary,
        branchName,
        productName,
        salesMessage: salesMsg,
        csMessage: csMsg
    };
}
window.uoBuildWhatsAppMessages = uoBuildWhatsAppMessages;

/**
 * Global WhatsApp Channel Selector Modal
 * Intercepts all WhatsApp button triggers across the site and lets the visitor select:
 * 1. Sales & Konsultasi Produk (0821-2338-100 / 628212338100)
 * 2. Customer Service & Komplain (0851-0762-0100 / 6285107620100)
 */
function initWhatsAppModal() {
    const modal = document.getElementById('uo-wa-modal');
    if (!modal) return;

    const salesNumber = modal.getAttribute('data-wa-sales') || '628212338100';
    const csNumber    = modal.getAttribute('data-wa-cs') || '6285107620100';

    let pendingSalesMessage = '';
    let pendingCsMessage = '';

    function openModal(message, mode = 'all', triggerEl = null) {
        // Build distinct, context-relevant messages for Sales vs CS
        const msgData = uoBuildWhatsAppMessages(message, triggerEl);
        pendingSalesMessage = msgData.salesMessage;
        pendingCsMessage = msgData.csMessage;

        // Determine active mode
        let activeMode = 'all';
        if (mode === 'sales_only' || mode === 'sales') {
            activeMode = 'sales_only';
        } else if (mode === 'cs_only' || mode === 'cs') {
            activeMode = 'cs_only';
        } else {
            activeMode = 'all';
        }

        modal.setAttribute('data-mode', activeMode);

        const titleEl   = document.getElementById('uo-wa-modal-title');
        const descEl    = document.getElementById('uo-wa-modal-desc');
        const badgeEl   = document.getElementById('uo-wa-header-badge');
        const pillEl    = document.getElementById('uo-wa-header-pill');
        const contextEl = document.getElementById('uo-wa-context-box');
        const contextTx = document.getElementById('uo-wa-context-text');
        const altLinkEl = document.getElementById('uo-wa-alt-link');

        // Update context badge text
        if (contextEl && contextTx) {
            if (msgData.summary) {
                contextTx.textContent = msgData.summary;
                contextEl.style.display = 'inline-flex';
            } else {
                contextEl.style.display = 'none';
            }
        }

        if (activeMode === 'sales_only') {
            const isForm = (typeof message === 'string') && (
                message.includes('Saya ingin mengajukan penawaran') ||
                message.includes('Saya ingin berkonsultasi mengenai') ||
                message.includes('[Pengajuan Penawaran]') ||
                message.includes('[Pemesanan Kunjungan]')
            );
            if (titleEl) titleEl.textContent = isForm ? 'Formulir Berhasil Terkirim!' : 'Hubungi Tim Sales Produk';
            if (descEl) descEl.textContent = isForm 
                ? 'Data pengajuan Anda sudah tersimpan. Klik tombol di bawah untuk langsung terhubung dengan Tim Sales via WhatsApp:'
                : 'Terhubung langsung dengan Tim Sales kami untuk info ketersediaan ruang, penawaran harga resmi, dan survey:';
            if (badgeEl) badgeEl.textContent = 'Sales WhatsApp';
            if (pillEl) {
                pillEl.className = 'uo-wa-header-pill uo-wa-header-pill--sales';
            }
            if (altLinkEl) {
                altLinkEl.innerHTML = '<button type="button" class="uo-wa-text-btn" data-wa-switch="cs">Bukan calon penyewa atau butuh bantuan kendala operasional? <span>Hubungi Customer Service</span></button>';
                altLinkEl.style.display = 'block';
            }
        } else if (activeMode === 'cs_only') {
            if (titleEl) titleEl.textContent = 'Customer Service & Komplain';
            if (descEl) descEl.textContent = 'Tim Customer Service siap membantu member aktif, operasional surat & paket masuk, dan penyelesaian kendala:';
            if (badgeEl) badgeEl.textContent = 'CS & Komplain';
            if (pillEl) {
                pillEl.className = 'uo-wa-header-pill uo-wa-header-pill--cs';
            }
            if (altLinkEl) {
                altLinkEl.innerHTML = '<button type="button" class="uo-wa-text-btn" data-wa-switch="sales">Ingin sewa ruang kantor baru atau tanya penawaran? <span>Hubungi Sales Produk</span></button>';
                altLinkEl.style.display = 'block';
            }
        } else {
            // Mode 'all' (Default for branch cards, pricing packages, floating widget)
            if (titleEl) titleEl.textContent = 'Hubungi Tim Urban Office';
            if (descEl) descEl.textContent = 'Silakan pilih jalur komunikasi yang sesuai agar pesan Anda langsung ditangani oleh tim yang tepat:';
            if (badgeEl) badgeEl.textContent = 'Direct WhatsApp';
            if (pillEl) {
                pillEl.className = 'uo-wa-header-pill';
            }
            if (altLinkEl) {
                altLinkEl.style.display = 'none';
            }
        }

        modal.classList.add('is-active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('is-active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    window.uoOpenWaModal = openModal;
    window.uoCloseWaModal = closeModal;

    // Close triggers
    modal.querySelectorAll('[data-wa-close]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal();
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('is-active')) {
            closeModal();
        }
    });

/**
 * Pushes structured WhatsApp conversion events to GTM / GA4 dataLayer.
 */
function uoTrackWhatsAppClick(channel, source, context = '') {
    try {
        const attr = (typeof uoGetAttribution === 'function') ? uoGetAttribution() : null;
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            event: 'whatsapp_click',
            wa_channel: channel || 'sales',
            wa_source: source || 'direct_cta',
            wa_context: context || document.title || window.location.pathname,
            page_path: window.location.pathname,
            traffic_channel: attr ? attr.channel : 'direct',
            traffic_source: attr ? attr.source : 'direct',
            traffic_medium: attr ? attr.medium : 'none',
            traffic_campaign: (attr && attr.campaign) ? attr.campaign : '(none)',
            landing_page: attr ? attr.landing_page : window.location.pathname
        });
    } catch (e) {}
}
window.uoTrackWhatsAppClick = uoTrackWhatsAppClick;

    // Choice 1: Sales & Konsultasi Produk
    const salesBtn = modal.querySelector('[data-wa-choice="sales"]');
    if (salesBtn) {
        salesBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const msg = pendingSalesMessage || 'Halo Tim Sales Urban Office, saya tertarik untuk konsultasi layanan dan sewa ruang kerja.';
            uoTrackWhatsAppClick('sales', 'modal_selector', document.getElementById('uo-wa-context-text')?.textContent || 'General');
            uoDirectOpenWhatsApp(salesNumber, msg);
            closeModal();
        });
    }

    // Choice 2: Customer Service & Komplain
    modal.querySelectorAll('[data-wa-choice="cs"]').forEach((csBtn) => {
        csBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const msg = pendingCsMessage || 'Halo Tim Customer Service Urban Office, saya ingin bertanya / menyampaikan kendala terkait layanan Urban Office.';
            uoTrackWhatsAppClick('cs', 'modal_selector', document.getElementById('uo-wa-context-text')?.textContent || 'General');
            uoDirectOpenWhatsApp(csNumber, msg);
            closeModal();
        });
    });

    // Handle dynamic switch link (e.g. switch between single modes)
    modal.addEventListener('click', (e) => {
        const switchBtn = e.target.closest('[data-wa-switch]');
        if (switchBtn) {
            e.preventDefault();
            const targetMode = switchBtn.getAttribute('data-wa-switch') === 'cs' ? 'cs_only' : 'sales_only';
            modal.setAttribute('data-mode', targetMode);

            const titleEl = document.getElementById('uo-wa-modal-title');
            const descEl  = document.getElementById('uo-wa-modal-desc');
            const badgeEl = document.getElementById('uo-wa-header-badge');
            const pillEl  = document.getElementById('uo-wa-header-pill');
            const altLinkEl = document.getElementById('uo-wa-alt-link');

            if (targetMode === 'cs_only') {
                if (titleEl) titleEl.textContent = 'Customer Service & Komplain';
                if (descEl) descEl.textContent = 'Tim Customer Service siap membantu member aktif, operasional surat & paket masuk, dan penyelesaian kendala:';
                if (badgeEl) badgeEl.textContent = 'CS & Komplain';
                if (pillEl) pillEl.className = 'uo-wa-header-pill uo-wa-header-pill--cs';
                if (altLinkEl) {
                    altLinkEl.innerHTML = '<button type="button" class="uo-wa-text-btn" data-wa-switch="sales">Ingin sewa ruang kantor baru atau tanya penawaran? <span>Hubungi Sales Produk</span></button>';
                }
            } else {
                if (titleEl) titleEl.textContent = 'Hubungi Tim Sales Produk';
                if (descEl) descEl.textContent = 'Terhubung langsung dengan Tim Sales kami untuk info ketersediaan ruang, penawaran harga resmi, dan survey:';
                if (badgeEl) badgeEl.textContent = 'Sales WhatsApp';
                if (pillEl) pillEl.className = 'uo-wa-header-pill uo-wa-header-pill--sales';
                if (altLinkEl) {
                    altLinkEl.innerHTML = '<button type="button" class="uo-wa-text-btn" data-wa-switch="cs">Bukan calon penyewa atau butuh bantuan kendala operasional? <span>Hubungi Customer Service</span></button>';
                }
            }
        }
    });

    // Intent-Based Global WhatsApp Click Router:
    // 1. Floating WA widget & Footer general WA links -> open 2-option modal (Sales vs CS & Komplain).
    // 2. Explicit CS triggers (e.g. "Tanya Cara Klaim Jatah" / data-wa-channel="cs") -> direct to CS WhatsApp.
    // 3. All product/branch/service/legalitas/partnership/blog CTAs -> direct to Sales WhatsApp (zero extra modal click).
    document.addEventListener('click', (e) => {
        // Do not intercept clicks inside the WA modal, promo popup modal, or explicit bypass links
        if (e.target.closest('#uo-wa-modal, #uo-popup-modal, [data-wa-bypass="1"]')) return;

        // Check if the clicked target is a WhatsApp link or button
        const trigger = e.target.closest(
            'a[href*="api.whatsapp.com"], a[href*="web.whatsapp.com"], a[href*="wa.me"], a[href*="whatsapp://"], .floating-wa-widget, .btn-wa-icon, .btn-wa-icon-only, [data-wa-trigger]'
        );

        if (!trigger) return;

        // Intercept action
        e.preventDefault();
        e.stopPropagation();

        // Extract message from href query param (?text=...) or data attribute
        let extractedMsg = '';
        const href = trigger.getAttribute('href') || '';
        if (href) {
            try {
                const parsed = new URL(href, window.location.origin);
                extractedMsg = parsed.searchParams.get('text') || '';
            } catch (err) {
                const match = href.match(/[?&]text=([^&]+)/);
                if (match) {
                    extractedMsg = decodeURIComponent(match[1].replace(/\+/g, ' '));
                }
            }
        }

        if (!extractedMsg && trigger.getAttribute('data-wa-text')) {
            extractedMsg = trigger.getAttribute('data-wa-text');
        }

        const explicitChannel = (trigger.getAttribute('data-wa-channel') || '').toLowerCase();
        const explicitMode = (trigger.getAttribute('data-wa-mode') || '').toLowerCase();
        const isGeneralContactTrigger =
            trigger.classList.contains('floating-wa-widget') ||
            Boolean(trigger.closest('footer, .footer')) ||
            explicitMode === 'all';

        // 1. General contact entry points -> show Sales vs CS selector modal
        if (isGeneralContactTrigger && explicitChannel !== 'sales' && explicitChannel !== 'cs') {
            openModal(extractedMsg, 'all', trigger);
            return;
        }

        const msgData = uoBuildWhatsAppMessages(extractedMsg, trigger);
        const triggerLabel = (trigger.textContent || '').toLowerCase();
        const rawLower = (extractedMsg || '').toLowerCase();

        // 2. Customer Service / existing member claim triggers -> direct to CS WhatsApp
        const isCsDirect =
            explicitChannel === 'cs' ||
            explicitMode === 'cs_only' ||
            triggerLabel.includes('klaim jatah') ||
            rawLower.includes('klaim jatah') ||
            rawLower.includes('komplain');

        if (isCsDirect) {
            const csMsg = extractedMsg || msgData.csMessage;
            uoTrackWhatsAppClick('cs', 'direct_cta', msgData.summary || triggerLabel.trim());
            uoDirectOpenWhatsApp(csNumber, csMsg);
            return;
        }

        // 3. High-intent product/branch/service/blog/partnership CTAs -> direct to Sales WhatsApp
        uoTrackWhatsAppClick('sales', 'direct_cta', msgData.summary || triggerLabel.trim());
        uoDirectOpenWhatsApp(salesNumber, msgData.salesMessage);
    }, true);
}

/**
 * Unified Gallery & Advantages Horizontal Card Sliders
 * Fixes mobile swipe axis-locking, fractional maxIndex premature resets,
 * edge rubber-banding, and centers galleries with < 3 items (or <= visible slots).
 */
function initOfficeGallerySliders() {
    const sliders = document.querySelectorAll('[data-gallery-slider]');
    if (!sliders.length) return;

    sliders.forEach((slider) => {
        const container = slider.parentElement;
        const slideSelector = slider.getAttribute('data-slide-selector') || '.gallery-card-slide';
        const slides = slider.querySelectorAll(slideSelector);
        if (!container || !slides.length) return;

        const visibleCfg = {
            desktop: parseFloat(slider.getAttribute('data-visible-desktop')) || 4,
            laptop:  parseFloat(slider.getAttribute('data-visible-laptop'))  || 3,
            tablet:  parseFloat(slider.getAttribute('data-visible-tablet'))  || 2,
            mobile:  parseFloat(slider.getAttribute('data-visible-mobile'))  || 1.3,
        };
        const enableAutoPlay = slider.getAttribute('data-autoplay') !== 'false';
        const transitionCss = 'transform 0.45s cubic-bezier(0.16, 1, 0.3, 1)';

        container.style.touchAction = 'pan-y';

        let currentIdx = 0;
        let autoPlayInterval = null;
        let isDragging = false;
        let axisLocked = null;
        let startX = 0;
        let startY = 0;
        let currentX = 0;
        let wasDragged = false;
        let lastTouchTime = 0;

        function getVisibleCount() {
            const width = window.innerWidth;
            if (width > 1200) return visibleCfg.desktop;
            if (width > 991)  return visibleCfg.laptop;
            if (width > 768)  return visibleCfg.tablet;
            return visibleCfg.mobile;
        }

        function getSlideWidth() {
            return container.clientWidth / getVisibleCount();
        }

        function isScrollable() {
            return slides.length > getVisibleCount();
        }

        function getMaxIndex() {
            return Math.max(0, Math.ceil(slides.length - getVisibleCount()));
        }

        function getTranslationForIndex(idx) {
            if (!isScrollable()) {
                return 0;
            }
            const containerWidth = container.clientWidth;
            const slideWidth = getSlideWidth();

            // When a gallery has fewer than 3 items (e.g. 2 cards on mobile), keep the active card centered
            if (slides.length < 3 && window.innerWidth <= 768) {
                const leftOffset = (containerWidth - slideWidth) / 2;
                return -idx * slideWidth + leftOffset;
            }

            const maxScrollPx = Math.max(0, slides.length * slideWidth - containerWidth);
            return Math.max(-maxScrollPx, Math.min(0, -idx * slideWidth));
        }

        function updateSlideStyles() {
            const visibleCount = getVisibleCount();
            const basisPct = 100 / visibleCount;
            slides.forEach((slide) => {
                slide.style.flex = `0 0 ${basisPct}%`;
                slide.style.width = `${basisPct}%`;
            });

            if (!isScrollable()) {
                slider.style.justifyContent = 'center';
                container.style.cursor = 'default';
            } else {
                slider.style.justifyContent = 'flex-start';
                container.style.cursor = 'grab';
            }
        }

        function showSlide(idx, wrap = false) {
            const maxIndex = getMaxIndex();
            if (!isScrollable()) {
                currentIdx = 0;
                slider.style.transition = transitionCss;
                slider.style.transform = 'translateX(0px)';
                return;
            }

            if (wrap) {
                if (idx > maxIndex) idx = 0;
                else if (idx < 0) idx = maxIndex;
            } else {
                idx = Math.max(0, Math.min(maxIndex, idx));
            }

            currentIdx = idx;
            slider.style.transition = transitionCss;
            slider.style.transform = `translateX(${getTranslationForIndex(currentIdx)}px)`;
        }

        function stopAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
                autoPlayInterval = null;
            }
        }

        function startAutoPlay() {
            stopAutoPlay();
            if (enableAutoPlay && isScrollable()) {
                autoPlayInterval = setInterval(() => {
                    showSlide(currentIdx + 1, true);
                }, 4500);
            }
        }

        function getPoint(e) {
            if (e.touches && e.touches.length) {
                return { x: e.touches[0].clientX, y: e.touches[0].clientY };
            }
            if (e.changedTouches && e.changedTouches.length) {
                return { x: e.changedTouches[0].clientX, y: e.changedTouches[0].clientY };
            }
            return { x: e.clientX, y: e.clientY };
        }

        function handleDragStart(e) {
            if (!isScrollable()) return;
            const isTouch = e.type === 'touchstart';
            if (isTouch) {
                lastTouchTime = Date.now();
            } else {
                if (Date.now() - lastTouchTime < 500) return;
                if (e.button !== 0) return;
            }

            const pt = getPoint(e);
            isDragging = true;
            axisLocked = isTouch ? null : 'x';
            startX = pt.x;
            startY = pt.y;
            currentX = pt.x;
            wasDragged = false;
            stopAutoPlay();
            if (!isTouch) {
                slider.style.transition = 'none';
            }
        }

        function handleDragMove(e) {
            if (!isDragging) return;
            const isTouch = e.type === 'touchmove';
            const pt = getPoint(e);

            if (isTouch && axisLocked === null) {
                const absDx = Math.abs(pt.x - startX);
                const absDy = Math.abs(pt.y - startY);
                if (absDx < 6 && absDy < 6) return;
                if (absDy > absDx) {
                    // Vertical scroll gesture: release slider immediately so page scroll stays smooth
                    axisLocked = 'y';
                    isDragging = false;
                    showSlide(currentIdx, false);
                    startAutoPlay();
                    return;
                }
                axisLocked = 'x';
                slider.style.transition = 'none';
            }

            if (axisLocked !== 'x') return;
            if (isTouch && e.cancelable) {
                e.preventDefault();
            }

            currentX = pt.x;
            const diffX = currentX - startX;
            if (Math.abs(diffX) > 6) {
                wasDragged = true;
            }

            const baseTranslation = getTranslationForIndex(currentIdx);
            const maxIndex = getMaxIndex();
            const atLeftEdge = currentIdx === 0 && diffX > 0;
            const atRightEdge = currentIdx >= maxIndex && diffX < 0;
            const effectiveDiffX = (atLeftEdge || atRightEdge) ? diffX * 0.35 : diffX;

            slider.style.transform = `translateX(${baseTranslation + effectiveDiffX}px)`;
        }

        function handleDragEnd() {
            if (!isDragging) return;
            isDragging = false;

            const diffX = currentX - startX;
            const threshold = Math.min(40, Math.max(24, getSlideWidth() * 0.18));

            if (axisLocked === 'x' && Math.abs(diffX) > threshold) {
                if (diffX < 0) {
                    showSlide(currentIdx + 1, false);
                } else {
                    showSlide(currentIdx - 1, false);
                }
            } else {
                showSlide(currentIdx, false);
            }

            axisLocked = null;
            if (wasDragged) {
                setTimeout(() => { wasDragged = false; }, 60);
            }
            startAutoPlay();
        }

        container.addEventListener('click', (e) => {
            if (wasDragged) {
                e.preventDefault();
                e.stopPropagation();
                wasDragged = false;
            }
        }, true);

        container.addEventListener('mousedown', handleDragStart);
        window.addEventListener('mousemove', handleDragMove);
        window.addEventListener('mouseup', handleDragEnd);

        container.addEventListener('touchstart', handleDragStart, { passive: true });
        container.addEventListener('touchmove', handleDragMove, { passive: false });
        container.addEventListener('touchend', handleDragEnd);
        container.addEventListener('touchcancel', handleDragEnd);

        window.addEventListener('resize', () => {
            updateSlideStyles();
            showSlide(currentIdx, false);
            startAutoPlay();
        });

        container.addEventListener('mouseenter', stopAutoPlay);
        container.addEventListener('mouseleave', startAutoPlay);

        updateSlideStyles();
        showSlide(currentIdx, false);
        startAutoPlay();
    });
}



