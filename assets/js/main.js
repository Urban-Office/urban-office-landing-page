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
        // Strip trailing slash, query params, and hash to ensure perfect matching
        const cleanUrl = (url) => url.split('?')[0].split('#')[0].replace(/\/+$/, '');
        const targetCleanUrl = cleanUrl(activeHref);
        const currentCleanUrl = cleanUrl(window.location.href);

        // Only highlight if the stored link is actually for the current page
        if (targetCleanUrl === currentCleanUrl) {
            navLinks.forEach(link => {
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
            if (!name || !email || !phone || !service || !budgetVal || !pesanVal) {
                alert('Harap isi semua kolom wajib (*).');
                return;
            }
        } else {
            if (!name || !email || !phone || !service || !dateVal) {
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
                    waText += `\n\n*Email:* ${email}\n*No. HP:* ${phone}`;
                } else {
                    waText = `Halo Urban Office, saya *${name}*.\n\n*Saya ingin berkonsultasi mengenai:* ${service}\n*Email:* ${email}\n*No. HP:* ${phone}`;
                    if (peopleVal) {
                        waText += `\n*Jumlah Orang:* ${peopleVal} Orang`;
                    }
                    if (dateVal) {
                        waText += `\n*Tanggal:* ${dateVal}`;
                    }
                }
                
                const waUrl = `https://api.whatsapp.com/send/?phone=6285107620100&text=${encodeURIComponent(waText)}`;
                
                // Reset form and offer mode
                form.reset();
                if (typeof resetOfferMode === 'function') {
                    resetOfferMode();
                }
                submitBtn.innerHTML = 'Sukses! Mengarahkan...';
                
                // Open WhatsApp in a new tab/window
                window.open(waUrl, '_blank');
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

        const setActiveCity = (citySlug) => {
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
                renderBranchButtons(citySlug);
            }
        };

        buttons.forEach(button => {
            button.addEventListener('click', () => {
                setActiveCity(button.dataset.branchCity);
            });
        });

        const queryLocation = normalizeCity(new URLSearchParams(window.location.search).get('lokasi'));
        const queryMatch = queryLocation
            ? Array.from(buttons).find(button => normalizeCity(button.dataset.branchCity) === queryLocation || normalizeCity(button.textContent).includes(queryLocation))
            : null;
        const activeButton = queryMatch || section.querySelector('.branch-city-btn.active') || buttons[0];

        setActiveCity(activeButton.dataset.branchCity);
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
    const slideWidth = 100 / visibleCount;
    slider.style.transform = `translateX(-${index * slideWidth}%)`;

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
    const width = window.innerWidth;
    if (width > 991) return benefitsVoVisibleCount.desktop;
    if (width > 768) return benefitsVoVisibleCount.tablet;
    return benefitsVoVisibleCount.mobile;
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
    const visibleCount = getBenefitsVoVisibleCount();
    const maxDots = Math.max(1, totalSlides - visibleCount + 1);
    
    for (let i = 0; i < maxDots; i++) {
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
    const visibleCount = getBenefitsVoVisibleCount();
    
    // Bounds check
    const maxIndex = Math.max(0, totalSlides - visibleCount);
    if (index > maxIndex) {
        index = 0;
    } else if (index < 0) {
        index = maxIndex;
    }

    currentBenefitsVoIndex = index;
    const slideWidth = 100 / visibleCount;
    slider.style.transform = `translateX(-${index * slideWidth}%)`;

    // Update active dot
    const dots = document.querySelectorAll('.benefits-vo-dot');
    dots.forEach((dot, idx) => {
        if (idx === index) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

function nextBenefitsVoSlide() {
    const slider = document.getElementById('benefits-vo-slider');
    if (!slider) return;
    const slides = slider.querySelectorAll('.benefits-vo-slide');
    const totalSlides = slides.length;
    const visibleCount = getBenefitsVoVisibleCount();
    const maxIndex = Math.max(0, totalSlides - visibleCount);

    if (currentBenefitsVoIndex >= maxIndex) {
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
const branchPricingData = {
    'surabaya-pusat': {
        name: 'Surabaya Pusat',
        address: 'Pusat Kota Surabaya',
        prices: { starter: '385.000', luxury: '620.000', priority: '770.000' }
    },
    'merr': {
        name: 'MERR (Surabaya Timur)',
        address: 'Jl. Dr. Ir. H. Soekarno No.470, Kedung Baruk, Rungkut, Surabaya',
        kpp: 'KPP Rungkut',
        prices: { starter: '385.000', luxury: '620.000', priority: '770.000' }
    },
    'klampis': {
        name: 'Klampis (Surabaya Timur)',
        address: 'Ruko Klampis Megah, Jl. Klampis Jaya blok B-20, Sukolilo, Surabaya',
        kpp: 'KPP Gubeng',
        prices: { starter: '385.000', luxury: '620.000', priority: '770.000' }
    },
    'grand-sungkono-lagoon': {
        name: 'Grand Sungkono Lagoon (Surabaya Barat)',
        address: 'Grand Sungkono Lagoon, Jl. KH Abdul Wahab Siamin Surabaya',
        kpp: 'KPP Karang Pilang',
        prices: { starter: '415.000', luxury: '675.000', priority: '830.000' }
    },
    'fatmawati': {
        name: 'Fatmawati (Jakarta Selatan)',
        address: 'Jl. RS. Fatmawati Raya No.35A, Cilandak, Jakarta Selatan',
        kpp: 'KPP Cilandak',
        prices: { starter: '385.000', luxury: '620.000', priority: '770.000' }
    },
    'gorebiz': {
        name: 'Gorebiz (Jakarta Timur)',
        address: 'Jl. Raya Bekasi KM.17, Jatinegara, Jakarta Timur',
        kpp: 'KPP Cakung 1',
        prices: { starter: '385.000', luxury: '620.000', priority: '770.000' }
    },
    'ptgm-tower': {
        name: 'PTGM Tower (Gresik)',
        address: 'PTGM Tower, Jl. Dr. Wahidin Sudirohusodo, Gresik',
        kpp: 'KPP Madya Gresik',
        prices: { starter: '385.000', luxury: '620.000', priority: '770.000' }
    },
    'medan': {
        name: 'Medan',
        address: 'Kota Medan, Sumatera Utara',
        prices: { starter: '385.000', luxury: '620.000', priority: '770.000' }
    },
    'malang': {
        name: 'Malang',
        address: 'Kota Malang, Jawa Timur',
        prices: { starter: '385.000', luxury: '620.000', priority: '770.000' }
    }
};

const cityBranches = {
    'surabaya': [
        { id: 'merr', label: 'MERR (Surabaya Timur)' },
        { id: 'klampis', label: 'Klampis (Surabaya Timur)' },
        { id: 'grand-sungkono-lagoon', label: 'Grand Sungkono (Surabaya Barat)' }
    ],
    'jakarta': [
        { id: 'fatmawati', label: 'Fatmawati (Jakarta Selatan)' },
        { id: 'gorebiz', label: 'Gorebiz (Jakarta Timur)' }
    ],
    'gresik': [
        { id: 'ptgm-tower', label: 'PTGM Tower (Gresik)' }
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

function renderBranchButtons(city) {
    const branchContainer = document.getElementById('branch-filter');
    if (!branchContainer) return;

    branchContainer.innerHTML = '';
    const branches = cityBranches[city] || [];

    if (branches.length > 1) {
        branchContainer.style.display = 'flex';
        branches.forEach((br, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'branch-city-btn branch-detail-btn-small';
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
        updatePricingCards(branches[0].id, false);
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

    if (priceStarterEl) priceStarterEl.innerHTML = `Rp ${data.prices.starter} <span>/ Bulan*</span>`;
    if (priceLuxuryEl) priceLuxuryEl.innerHTML = `Rp ${data.prices.luxury} <span>/ Bulan*</span>`;
    if (pricePriorityEl) pricePriorityEl.innerHTML = `Rp ${data.prices.priority} <span>/ Bulan*</span>`;

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
            const activeBranch = branchPricingData[window.activeBranchId || 'merr'];
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

    // Restore required status
    const dateInput = document.getElementById('contact-date');
    if (dateInput) dateInput.required = true;

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

