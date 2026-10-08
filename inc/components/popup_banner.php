<?php
/**
 * Promotional Pop-up Banner Component
 * Resolves active pop-ups based on targeting, frequency rules, URL indexing deep-link, and user session.
 */

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/database.php';
require_once dirname(__DIR__) . '/functions.php';

// Resolve current page slug & clean path
$cur_slug = isset($page_slug) ? trim((string)$page_slug) : '';
$req_uri = $_SERVER['REQUEST_URI'] ?? '';
$req_path = trim(urldecode(parse_url($req_uri, PHP_URL_PATH) ?? ''), '/');
if (!empty(DIR_SUBFOLDER)) {
    $sf = trim(DIR_SUBFOLDER, '/');
    if ($sf !== '' && strpos($req_path, $sf) === 0) {
        $req_path = trim(substr($req_path, strlen($sf)), '/');
    }
}

// 1. Check if deep-link promo slug is requested via URL (?promo=slug)
$promo_query = isset($_GET['promo']) ? trim($_GET['promo']) : '';
$active_popup = null;
$is_forced_by_url = false;

try {
    if (!empty($promo_query)) {
        // Direct promo link requested -> fetch specific popup regardless of page target
        $active_popup = Database::fetch(
            "SELECT * FROM popups WHERE slug = ? AND status = 1 LIMIT 1",
            [$promo_query]
        );
        if ($active_popup) {
            $is_forced_by_url = true;
        }
    }

    // 2. If no direct promo query, query active scheduled popups
    if (!$active_popup) {
        $candidate_popups = Database::fetchAll(
            "SELECT * FROM popups 
             WHERE status = 1 
               AND (start_date IS NULL OR start_date <= NOW()) 
               AND (end_date IS NULL OR end_date >= NOW()) 
             ORDER BY id DESC"
        );

        foreach ($candidate_popups as $p) {
            $target_mode = $p['display_target'];

            // Match Mode: All Pages
            if ($target_mode === 'all') {
                $active_popup = $p;
                break;
            }

            // Match Mode: Home Only
            if ($target_mode === 'home_only') {
                if ($cur_slug === '' || $req_path === '' || $req_path === 'index.php') {
                    $active_popup = $p;
                    break;
                }
                continue;
            }

            // Match Mode: Specific Pages
            if ($target_mode === 'specific') {
                $target_pages = json_decode($p['target_pages'] ?? '[]', true);
                if (is_array($target_pages)) {
                    // Check direct slug match
                    if (in_array($cur_slug, $target_pages, true) || in_array($req_path, $target_pages, true)) {
                        $active_popup = $p;
                        break;
                    }
                    // Check virtual office branch slug (e.g. virtual-office-jakarta)
                    if (!empty($_GET['branch']) && in_array('virtual-office-' . $_GET['branch'], $target_pages, true)) {
                        $active_popup = $p;
                        break;
                    }
                    // Check per-city workspace services (?lokasi=jakarta / clean URL rewrite)
                    if (!empty($_GET['lokasi'])) {
                        $lok_val = strtolower(trim($_GET['lokasi']));
                        if (
                            in_array('sewa-kantor-' . $lok_val, $target_pages, true) ||
                            in_array('meeting-room-' . $lok_val, $target_pages, true) ||
                            in_array('coworking-space-' . $lok_val, $target_pages, true) ||
                            in_array('event-space-' . $lok_val, $target_pages, true)
                        ) {
                            $active_popup = $p;
                            break;
                        }
                    }
                    // Check location detail directory page (/lokasi-urban-office/{slug}/)
                    if (!empty($_GET['slug'])) {
                        $slug_val = strtolower(trim($_GET['slug']));
                        if (in_array($slug_val, $target_pages, true) || in_array('lokasi-' . $slug_val, $target_pages, true)) {
                            $active_popup = $p;
                            break;
                        }
                    }
                    // Check homepage if target_pages has empty string
                    if (($cur_slug === '' || $req_path === '') && in_array('', $target_pages, true)) {
                        $active_popup = $p;
                        break;
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Failed loading popup banner: " . $e->getMessage());
    $active_popup = null;
}

// Exit if no popup matched
if (!$active_popup || empty($active_popup['image_path'])) {
    return;
}

$p_id        = (int)$active_popup['id'];
$p_slug      = $active_popup['slug'];
$p_title     = $active_popup['title'];
$p_alt       = !empty($active_popup['alt_text']) ? $active_popup['alt_text'] : $p_title;
$p_img_url   = media_url($active_popup['image_path']);
$p_link_type = $active_popup['link_type'] ?? 'url';
$wa_num = '6285107620100';
$wa_raw_msg = (string)($active_popup['wa_message'] ?? '');
if ($p_link_type === 'whatsapp') {
    $wa_num = preg_replace('/[^0-9]/', '', (string)($active_popup['wa_number'] ?? '085107620100'));
    if (strpos($wa_num, '0') === 0) {
        $wa_num = '62' . substr($wa_num, 1);
    }
    if (empty($wa_num)) {
        $wa_num = '6285107620100';
    }
    // Direct bypass link to WhatsApp Web (bypasses api.whatsapp.com landing page)
    $p_link_url = 'https://web.whatsapp.com/send?phone=' . $wa_num;
    if ($wa_raw_msg !== '') {
        $p_link_url .= '&text=' . rawurlencode($wa_raw_msg);
    }
} else {
    $p_link_url  = !empty($active_popup['target_url']) ? trim((string)$active_popup['target_url']) : 'https://my.urbanoffice.co.id/beforelogin';
}
$p_freq      = $active_popup['frequency'];
$p_days      = max(1, (int)$active_popup['frequency_days']);
$p_delay     = max(2, (int)$active_popup['delay_seconds']);
?>
<!-- Urban Office Promotional Pop-up Modal Component -->
<div id="uo-popup-modal" class="uo-popup-wrapper" role="dialog" aria-modal="true" aria-label="<?php echo sanitize($p_title); ?>" style="display: none;">
    <div class="uo-popup-backdrop" id="uo-popup-backdrop"></div>
    <div class="uo-popup-container">
        <!-- Close Button with 5-Second Countdown -->
        <button type="button" class="uo-popup-close-btn uo-counting" id="uo-popup-close-btn" aria-label="Tutup Banner Promosi" title="Dapat ditutup dalam 5 detik">
            <span id="uo-popup-timer-text">5s</span>
        </button>
        
        <!-- Banner Content -->
        <div class="uo-popup-card">
            <?php if (!empty($p_link_url)): ?>
                <a href="<?php echo sanitize($p_link_url); ?>" id="uo-popup-banner-link" class="uo-popup-link" target="_blank" rel="noopener" data-link-type="<?php echo sanitize($p_link_type); ?>" data-wa-phone="<?php echo sanitize($wa_num); ?>" data-wa-msg="<?php echo htmlspecialchars($wa_raw_msg, ENT_QUOTES, 'UTF-8'); ?>">
                    <img src="<?php echo $p_img_url; ?>" alt="<?php echo sanitize($p_alt); ?>" class="uo-popup-img" loading="eager">
                </a>
            <?php else: ?>
                <img src="<?php echo $p_img_url; ?>" alt="<?php echo sanitize($p_alt); ?>" class="uo-popup-img" loading="eager">
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Scoped styles for Urban Office Pop-up Modal */
.uo-popup-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    box-sizing: border-box;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.35s ease, visibility 0.35s ease;
}
.uo-popup-wrapper.uo-popup-visible {
    opacity: 1;
    visibility: visible;
}
.uo-popup-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(15, 23, 42, 0.78);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    cursor: pointer;
}
.uo-popup-container {
    position: relative;
    width: -moz-fit-content;
    width: fit-content;
    max-width: min(580px, 92vw);
    z-index: 10;
    margin-top: 52px; /* Ruang untuk tombol silang melayang di atas */
    transform: scale(0.92) translateY(12px);
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    flex-shrink: 0;
}
.uo-popup-wrapper.uo-popup-visible .uo-popup-container {
    transform: scale(1) translateY(0);
}
.uo-popup-card {
    background: transparent;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.1);
    display: block;
    line-height: 0;
}
.uo-popup-link {
    display: block;
    cursor: pointer;
    text-decoration: none;
    line-height: 0;
}
.uo-popup-img {
    display: block;
    width: auto;
    height: auto;
    max-width: min(580px, 92vw);
    max-height: 75vh;
    border-radius: 16px;
    background-color: transparent;
    transition: transform 0.25s ease;
}
.uo-popup-link:hover .uo-popup-img {
    filter: brightness(1.02);
}
.uo-popup-close-btn {
    position: absolute;
    top: -50px;
    right: 0px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
    z-index: 20;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    padding: 0;
    outline: none;
    user-select: none;
}
.uo-popup-close-btn.uo-counting {
    background-color: rgba(15, 23, 42, 0.85);
    color: #ffffff;
    border: 2px solid rgba(255, 255, 255, 0.7);
    font-size: 14px;
    font-weight: 700;
    font-family: inherit;
    cursor: not-allowed;
    letter-spacing: -0.5px;
}
.uo-popup-close-btn.uo-ready {
    background-color: rgba(255, 255, 255, 0.95);
    color: #1e293b;
    border: none;
    font-size: 26px;
    line-height: 1;
    cursor: pointer;
}
.uo-popup-close-btn.uo-ready:hover {
    background-color: #ffffff;
    color: #ef4444;
    transform: scale(1.1);
}

/* Mobile viewport adjustments */
@media (max-width: 640px) {
    .uo-popup-container {
        max-width: 92vw;
        margin-top: 48px;
    }
    .uo-popup-close-btn {
        top: -46px;
        right: 0px;
        width: 38px;
        height: 38px;
    }
    .uo-popup-close-btn.uo-counting {
        font-size: 13px;
    }
    .uo-popup-close-btn.uo-ready {
        font-size: 22px;
    }
    .uo-popup-img {
        max-height: 75vh;
    }
}
</style>

<script>
(function() {
    const config = {
        id: <?php echo $p_id; ?>,
        slug: <?php echo json_encode($p_slug); ?>,
        frequency: <?php echo json_encode($p_freq); ?>,
        frequencyDays: <?php echo $p_days; ?>,
        delaySeconds: <?php echo $p_delay; ?>,
        targetUrl: <?php echo json_encode($p_link_url); ?>,
        trackUrl: <?php echo json_encode(BASE_URL . 'inc/popup_track.php'); ?>,
        isForced: <?php echo $is_forced_by_url ? 'true' : 'false'; ?>
    };

    const modalWrapper = document.getElementById('uo-popup-modal');
    const closeBtn     = document.getElementById('uo-popup-close-btn');
    const backdrop     = document.getElementById('uo-popup-backdrop');
    const bannerLink   = document.getElementById('uo-popup-banner-link');

    if (!modalWrapper) return;

    let countdownSeconds = 5;
    let countdownInterval = null;
    let isCloseAllowed = false;

    /* 1. Evaluate Frequency / Storage eligibility */
    function shouldShowPopup() {
        if (config.isForced) {
            return true; /* Deep link forced via ?promo=slug */
        }

        /* Allow instant display for testing via ?test_popup=1 */
        if (window.location.search.indexOf('test_popup') !== -1) {
            return true;
        }

        if (config.frequency === 'always') {
            return true;
        }

        if (config.frequency === 'session') {
            const seen = sessionStorage.getItem('uo_popup_seen_' + config.id);
            return !seen;
        }

        if (config.frequency === 'daily' || config.frequency === 'custom') {
            const lastTimeStr = localStorage.getItem('uo_popup_time_' + config.id);
            if (!lastTimeStr) return true;
            const lastTime = parseInt(lastTimeStr, 10);
            if (isNaN(lastTime)) return true;
            const gapMs = (config.frequencyDays || 1) * 24 * 60 * 60 * 1000;
            return (Date.now() - lastTime) >= gapMs;
        }

        return true;
    }

    if (!shouldShowPopup()) {
        return;
    }

    /* Countdown Timer Handler (5 seconds) */
    function startCountdown() {
        countdownSeconds = 5;
        isCloseAllowed = false;
        if (closeBtn) {
            closeBtn.classList.remove('uo-ready');
            closeBtn.classList.add('uo-counting');
            closeBtn.setAttribute('title', 'Dapat ditutup dalam ' + countdownSeconds + ' detik');
            closeBtn.innerHTML = '<span>' + countdownSeconds + 's</span>';
        }

        if (countdownInterval) clearInterval(countdownInterval);

        countdownInterval = setInterval(() => {
            countdownSeconds--;
            if (countdownSeconds > 0) {
                if (closeBtn) {
                    closeBtn.innerHTML = '<span>' + countdownSeconds + 's</span>';
                    closeBtn.setAttribute('title', 'Dapat ditutup dalam ' + countdownSeconds + ' detik');
                }
            } else {
                clearInterval(countdownInterval);
                countdownInterval = null;
                isCloseAllowed = true;
                if (closeBtn) {
                    closeBtn.classList.remove('uo-counting');
                    closeBtn.classList.add('uo-ready');
                    closeBtn.setAttribute('title', 'Tutup Pop-up Banner');
                    closeBtn.innerHTML = '&times;';
                }
            }
        }, 1000);
    }

    /* 2. Open Popup Function */
    function openPopup() {
        modalWrapper.style.display = 'flex';
        /* Trigger animation */
        requestAnimationFrame(() => {
            modalWrapper.classList.add('uo-popup-visible');
        });

        /* Start 5-second countdown on close button */
        startCountdown();

        /* Dynamic URL Update (without page refresh, SEO friendly) */
        try {
            const curUrl = new URL(window.location.href);
            if (curUrl.searchParams.get('promo') !== config.slug) {
                curUrl.searchParams.set('promo', config.slug);
                window.history.replaceState(
                    { uo_popup_open: true, original_search: window.location.search },
                    '',
                    curUrl.toString()
                );
            }
        } catch (e) {}

        /* Send View Analytics Event (non-blocking) */
        sendTrackEvent('view');
    }

    /* 3. Close Popup Function */
    function closePopup() {
        if (!isCloseAllowed) {
            /* Pulse animation feedback if user tries to close early */
            if (closeBtn) {
                closeBtn.style.transform = 'scale(1.22)';
                setTimeout(() => { closeBtn.style.transform = ''; }, 200);
            }
            return;
        }

        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }

        modalWrapper.classList.remove('uo-popup-visible');
        setTimeout(() => {
            modalWrapper.style.display = 'none';
        }, 360);

        /* Record User Frequency Flag in Storage */
        try {
            if (config.frequency === 'session') {
                sessionStorage.setItem('uo_popup_seen_' + config.id, '1');
            } else if (config.frequency === 'daily' || config.frequency === 'custom') {
                localStorage.setItem('uo_popup_time_' + config.id, Date.now().toString());
            }
        } catch (e) {}

        /* Restore URL parameter to original clean state */
        try {
            const curUrl = new URL(window.location.href);
            if (curUrl.searchParams.get('promo') === config.slug) {
                curUrl.searchParams.delete('promo');
                const cleanHref = curUrl.pathname + (curUrl.search ? curUrl.search : '') + curUrl.hash;
                window.history.replaceState({}, '', cleanHref);
            }
        } catch (e) {}
    }

    /* 4. Send Event Tracker Helper */
    function sendTrackEvent(evtName) {
        if (!config.trackUrl) return;
        try {
            const payload = JSON.stringify({ popup_id: config.id, event: evtName });
            if (navigator.sendBeacon) {
                const blob = new Blob([payload], { type: 'application/json' });
                navigator.sendBeacon(config.trackUrl, blob);
            } else {
                fetch(config.trackUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: payload,
                    keepalive: true
                }).catch(() => {});
            }
        } catch (e) {}
    }

    /* 5. Event Listeners */
    if (closeBtn) {
        closeBtn.addEventListener('click', closePopup);
    }
    if (backdrop) {
        backdrop.addEventListener('click', closePopup);
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalWrapper.classList.contains('uo-popup-visible')) {
            closePopup();
        }
    });

    if (bannerLink) {
        bannerLink.addEventListener('click', function(e) {
            sendTrackEvent('click');
            try {
                if (config.frequency === 'session') {
                    sessionStorage.setItem('uo_popup_seen_' + config.id, '1');
                } else if (config.frequency === 'daily' || config.frequency === 'custom') {
                    localStorage.setItem('uo_popup_time_' + config.id, Date.now().toString());
                }
            } catch (e) {}

            const linkType = this.getAttribute('data-link-type');
            if (linkType === 'whatsapp') {
                e.preventDefault();
                const phone = this.getAttribute('data-wa-phone') || '6285107620100';
                const msg = this.getAttribute('data-wa-msg') || '';
                const encMsg = msg ? encodeURIComponent(msg) : '';

                /* Deteksi apakah pengunjung menggunakan Smartphone / HP */
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

                if (isMobile) {
                    /* Di Smartphone: Langsung buka aplikasi WhatsApp (Bypass halaman perantara web) */
                    const appUrl = 'whatsapp://send?phone=' + phone + (encMsg ? '&text=' + encMsg : '');
                    window.location.href = appUrl;

                    /* Fallback jika belum menginstall WhatsApp di HP */
                    setTimeout(function() {
                        if (document.hidden) return;
                        window.location.href = 'https://api.whatsapp.com/send?phone=' + phone + (encMsg ? '&text=' + encMsg : '');
                    }, 1500);
                } else {
                    /* Di Laptop/PC: Langsung buka WhatsApp Web (Bypass halaman perantara api.whatsapp.com) */
                    const webUrl = 'https://web.whatsapp.com/send?phone=' + phone + (encMsg ? '&text=' + encMsg : '');
                    window.open(webUrl, '_blank', 'noopener,noreferrer');
                }
            }
        });
    }

    /* 6. Schedule Display */
    const isTestMode = config.isForced || (window.location.search.indexOf('test_popup') !== -1);
    const delayMs = isTestMode ? 100 : (config.delaySeconds * 1000);
    setTimeout(openPopup, delayMs);
})();
</script>
