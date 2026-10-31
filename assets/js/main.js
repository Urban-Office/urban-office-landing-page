/**
 * Master Script file for UrbanOffice website
 * Manages UI animations, mobile navigation, sliders, accordions, and form submissions
 */

document.addEventListener('DOMContentLoaded', () => {
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
    initBranchesFilter();
    initMobileLogosMarquee();
    initWhatsAppModal();
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

        if (honey) {
            // Bot detected, silently block submission
            alert('Pesan Anda berhasil dikirim!');
            form.reset();
            return;
        }

        if (isOfferMode) {
            if (!name || !phone || !service || !budgetVal || !pesanVal) {
                alert('Harap isi semua kolom wajib (*).');
                return;
            }
        } else {
            const requiresVisitDate = Boolean(dateInput && dateInput.required);
            if (!name || !phone || !service || (requiresVisitDate && !dateVal)) {
                alert('Harap isi semua kolom wajib (*).');
                return;
            }
        }

        // Format message for DB compatibility
        let message = '';
        if (isOfferMode) {
            message = `[Pengajuan Penawaran]\nCabang / Lokasi: ${locationVal}\nPaket: ${offerPackage || '-'}\nEkspektasi Budget: ${budgetVal}\nPesan: ${pesanVal}`;
        } else {
            message = `[Pemesanan Kunjungan]\nJumlah Orang: ${peopleVal || '-'}\nTanggal: ${dateVal}`;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        const origBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Mengirim...';

        try {
            // Post data to our AJAX lead handler script
            const response = await fetch(form.getAttribute('action') || `${window.location.origin}/inc/lead_handler.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name, email, phone, service, message, csrf_token: csrfToken
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
                submitBtn.innerHTML = 'Sukses!';

                // Direct to WhatsApp via Channel Selector Modal (Khusus Sales untuk pengajuan formulir)
                if (typeof window.uoOpenWaModal === 'function') {
                    window.uoOpenWaModal(waText, 'sales_only');
                } else if (typeof window.uoDirectOpenWhatsApp === 'function') {
                    window.uoDirectOpenWhatsApp('628212338100', waText);
                } else {
                    window.open(`https://api.whatsapp.com/send/?phone=628212338100&text=${encodeURIComponent(waText)}`, '_blank');
                }
            } else {
                alert('Gagal mengirim pesan: ' + (result.message || 'Error tidak diketahui.'));
            }
        } catch (error) {
            console.error('Submission error:', error);
            alert('Terjadi kesalahan saat mengirim pesan. Silakan hubungi langsung via WhatsApp.');
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
 * Search Bar Tab Switcher (Homepage Only)
 */
function initHomepageSearch() {
    const form = document.querySelector('.search-form');
    const activeTab = document.querySelector('.search-tab.active') || document.querySelector('.search-tab');
    const input = document.getElementById('search-location-input');

    if (activeTab) {
        switchSearchTab(activeTab);
    }

    if (form) {
        form.addEventListener('submit', triggerSearch);
    }

    if (input) {
        input.addEventListener('input', () => {
            delete input.dataset.lat;
            delete input.dataset.lng;
        });
    }
}

function switchSearchTab(button, serviceName, placeholderText) {
    if (!button) return;

    const tabs = document.querySelectorAll('.search-tab');
    tabs.forEach(t => t.classList.remove('active'));
    
    button.classList.add('active');

    const service = serviceName || button.dataset.service || button.textContent.trim();
    const placeholder = placeholderText || button.dataset.placeholder || 'Masukkan kota lokasi bisnis';

    const input = document.getElementById('search-location-input');
    if (input) {
        input.placeholder = placeholder;
        input.dataset.service = service;
        input.dataset.target = button.dataset.target || '';
    }
    
    window.selectedService = service;
    window.selectedServiceTarget = button.dataset.target || '';
}

function triggerSearch(event) {
    if (event) {
        event.preventDefault();
    }

    const input = document.getElementById('search-location-input');
    const activeTab = document.querySelector('.search-tab.active');
    const city = input ? input.value.trim() : '';
    const target = activeTab?.dataset.target || window.selectedServiceTarget;

    if (!city) {
        if (input) {
            input.classList.add('search-input-error');
            input.focus();
            setTimeout(() => input.classList.remove('search-input-error'), 1600);
        }
        alert('Masukkan kota terlebih dahulu, atau gunakan tombol Di Dekat Saya.');
        return;
    }

    if (target) {
        const targetUrl = new URL(target, window.location.href);
        targetUrl.searchParams.set('lokasi', city);

        if (input?.dataset.lat && input?.dataset.lng) {
            targetUrl.searchParams.set('lat', input.dataset.lat);
            targetUrl.searchParams.set('lng', input.dataset.lng);
        }

        window.location.href = targetUrl.toString();
        return;
    }

    const contactSection = document.getElementById('contact');
    if (contactSection) {
        contactSection.scrollIntoView({ behavior: 'smooth' });
    }
}

function setSearchNearMe(button) {
    const input = document.getElementById('search-location-input');

    if (!navigator.geolocation) {
        alert('Browser Anda belum mendukung fitur lokasi otomatis.');
        return;
    }

    const originalHtml = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="bi bi-crosshair"></i><span>Mengambil lokasi...</span>';

    navigator.geolocation.getCurrentPosition(
        (position) => {
            if (input) {
                input.value = 'Lokasi saya saat ini';
                input.dataset.lat = position.coords.latitude.toFixed(6);
                input.dataset.lng = position.coords.longitude.toFixed(6);
            }
            button.disabled = false;
            button.innerHTML = originalHtml;
        },
        () => {
            alert('Lokasi belum bisa diakses. Silakan izinkan akses lokasi atau ketik kota secara manual.');
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

function updatePricingCards(branchId, showKpp = true) {
    window.activeBranchId = branchId;
    const data = branchPricingData[branchId];
    if (!data) return;

    const priceStarterEl = document.getElementById('price-starter');
    const priceLuxuryEl = document.getElementById('price-luxury');
    const pricePriorityEl = document.getElementById('price-priority');

    if (priceStarterEl && data.prices.starter) priceStarterEl.innerHTML = `Rp ${data.prices.starter} <span>/ Bulan*</span>`;
    if (priceLuxuryEl && data.prices.luxury) priceLuxuryEl.innerHTML = `Rp ${data.prices.luxury} <span>/ Bulan*</span>`;
    if (pricePriorityEl && data.prices.priority) pricePriorityEl.innerHTML = `Rp ${data.prices.priority} <span>/ Bulan*</span>`;

    // Hide tiers a branch doesn't offer (e.g. Jakarta has no Priority, Malang only Starter).
    // Only relevant when switching branches on a page that rendered 3 cards — a branch's own
    // page already renders just its real tiers server-side.
    [[priceLuxuryEl, data.prices.luxury], [pricePriorityEl, data.prices.priority]].forEach(([el, price]) => {
        const card = el ? el.closest('.premium-card') : null;
        if (card) card.style.display = price ? '' : 'none';
    });

    // Update Address features
    const addressStarter = document.querySelector('#features-starter .address-feature');
    const addressLuxury = document.querySelector('#features-luxury .address-feature');
    const addressPriority = document.querySelector('#features-priority .address-feature');
    if (addressStarter) addressStarter.innerText = `Alamat Bisnis: ${data.address}`;
    if (addressLuxury) addressLuxury.innerText = `Alamat Bisnis: ${data.address}`;
    if (addressPriority) addressPriority.innerText = `Alamat Bisnis: ${data.address}`;

    // Update KPP info
    const kppEls = document.querySelectorAll('.kpp-info');
    kppEls.forEach(el => {
        if (showKpp && data.kpp) {
            el.innerText = data.kpp;
            el.style.display = 'block';
        } else {
            el.style.display = 'none';
        }
    });

    // Update CTA WhatsApp Links
    const whatsappBase = "https://api.whatsapp.com/send?phone=6285107620100&text=";
    
    const textStarter = encodeURIComponent(`Halo Urban Office, saya tertarik dengan layanan Virtual Office di cabang ${data.name} (Paket Starter). Mohon info penawaran selengkapnya.`);
    const textLuxury = encodeURIComponent(`Halo Urban Office, saya tertarik dengan layanan Virtual Office di cabang ${data.name} (Paket Luxury). Mohon info penawaran selengkapnya.`);
    const textPriority = encodeURIComponent(`Halo Urban Office, saya tertarik dengan layanan Virtual Office di cabang ${data.name} (Paket Priority). Mohon info penawaran selengkapnya.`);

    const ctaStarter = document.getElementById('cta-starter');
    const ctaLuxury = document.getElementById('cta-luxury');
    const ctaPriority = document.getElementById('cta-priority');

    if (ctaStarter) ctaStarter.setAttribute('href', whatsappBase + textStarter);
    if (ctaLuxury) ctaLuxury.setAttribute('href', whatsappBase + textLuxury);
    if (ctaPriority) ctaPriority.setAttribute('href', whatsappBase + textPriority);
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

    // Choice 1: Sales & Konsultasi Produk
    const salesBtn = modal.querySelector('[data-wa-choice="sales"]');
    if (salesBtn) {
        salesBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const msg = pendingSalesMessage || 'Halo Tim Sales Urban Office, saya tertarik untuk konsultasi layanan dan sewa ruang kerja.';
            uoDirectOpenWhatsApp(salesNumber, msg);
            closeModal();
        });
    }

    // Choice 2: Customer Service & Komplain
    modal.querySelectorAll('[data-wa-choice="cs"]').forEach((csBtn) => {
        csBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const msg = pendingCsMessage || 'Halo Tim Customer Service Urban Office, saya ingin bertanya / menyampaikan kendala terkait layanan Urban Office.';
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

    // Global click interceptor across ALL WhatsApp links & buttons on the website
    document.addEventListener('click', (e) => {
        // Do not intercept clicks inside the modal itself
        if (e.target.closest('#uo-wa-modal')) return;

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

        // Mode Determination:
        // - Semua tombol WA dari lokasi cabang, opsi paket produk, maupun floating pojok kanan bawah
        //   TETAP MENAMPILKAN DUA OPSI (mode: 'all').
        // - Khusus yang hanya ke Sales adalah hasil pengisian formulir kontak (mode: 'sales_only').
        let detectedMode = 'all';
        const explicitChannel = trigger.getAttribute('data-wa-channel');
        const explicitMode = trigger.getAttribute('data-wa-mode');

        if (explicitMode === 'sales_only') {
            detectedMode = 'sales_only';
        } else if (explicitChannel === 'cs' || explicitMode === 'cs_only') {
            detectedMode = 'cs_only';
        } else {
            detectedMode = 'all';
        }

        openModal(extractedMsg, detectedMode, trigger);
    }, true);
}


