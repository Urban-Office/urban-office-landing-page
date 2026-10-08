<?php
/**
 * Automation — content enrichment helpers (server-side, GD).
 *   - uo_build_hero_image(): branded hero (orange card + logo wordmark + title
 *     rendered as text + Fal.ai photo on the right).
 *   - uo_insert_midcontent_card(): inject a clickable HTML card linking to the
 *     most relevant internal page, at the article midpoint.
 * Requires GD with FreeType; font at assets/fonts/Poppins-*.ttf.
 */

if (!defined('UO_FONT_BOLD'))    define('UO_FONT_BOLD', DIR_ROOT . 'assets/fonts/Poppins-Bold.ttf');
if (!defined('UO_FONT_XBOLD'))   define('UO_FONT_XBOLD', DIR_ROOT . 'assets/fonts/Poppins-ExtraBold.ttf');
if (!defined('UO_FONT_SEMI'))    define('UO_FONT_SEMI', DIR_ROOT . 'assets/fonts/Poppins-SemiBold.ttf');
if (!defined('UO_FONT_REG'))     define('UO_FONT_REG',  DIR_ROOT . 'assets/fonts/Poppins-Regular.ttf');
if (!defined('UO_HERO_TEMPLATE')) define('UO_HERO_TEMPLATE', DIR_ROOT . 'assets/images/imgcomponent/seo tren startup 2026.png');

/* ---------- Cloudinary (optional CDN storage) ---------- */

/** Read a settings value (empty string if absent / on error). */
function uo_setting(string $name): string {
    try {
        $r = Database::fetch("SELECT value FROM settings WHERE name = ?", [$name]);
        return $r ? (string)$r['value'] : '';
    } catch (Exception $e) {
        return '';
    }
}

/**
 * Upload a local image to Cloudinary and return its secure_url (CDN URL).
 * Returns null when Cloudinary is not configured (settings) or on any failure,
 * so callers can fall back to local storage. Uses a signed upload (no preset).
 */
function uo_upload_to_cloudinary(string $absPath): ?string {
    if (!function_exists('curl_init') || !is_file($absPath)) return null;
    $cloud  = uo_setting('cloudinary_cloud_name');
    $key    = uo_setting('cloudinary_api_key');
    $secret = uo_setting('cloudinary_api_secret');
    if ($cloud === '' || $key === '' || $secret === '') return null; // not configured

    $ts     = time();
    $folder = 'blog';
    // Signature = sha1 of alphabetically-sorted params (excl. file/api_key/cloud) + api_secret.
    $sig = sha1("folder={$folder}&timestamp={$ts}" . $secret);

    $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloud}/image/upload");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_TIMEOUT        => 45,
        CURLOPT_POSTFIELDS     => [
            'file'      => new CURLFile($absPath),
            'api_key'   => $key,
            'timestamp' => (string)$ts,
            'folder'    => $folder,
            'signature' => $sig,
        ],
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($resp === false || $code >= 300) {
        error_log("Cloudinary upload failed (HTTP {$code}): " . ($err ?: $resp));
        return null;
    }
    $data = json_decode($resp, true);
    return isset($data['secure_url']) ? (string)$data['secure_url'] : null;
}

/* ---------- small GD helpers ---------- */

function uo_fill_roundrect($im, $x, $y, $w, $h, $r, $c): void {
    imagefilledrectangle($im, $x + $r, $y, $x + $w - $r, $y + $h, $c);
    imagefilledrectangle($im, $x, $y + $r, $x + $w, $y + $h - $r, $c);
    imagefilledellipse($im, $x + $r,      $y + $r,      $r * 2, $r * 2, $c);
    imagefilledellipse($im, $x + $w - $r, $y + $r,      $r * 2, $r * 2, $c);
    imagefilledellipse($im, $x + $r,      $y + $h - $r, $r * 2, $r * 2, $c);
    imagefilledellipse($im, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $c);
}

/** Word-wrap text to a max pixel width for a given font/size. */
function uo_wrap_text(string $font, float $size, string $text, int $maxw): array {
    $words = preg_split('/\s+/', trim($text));
    $lines = [];
    $cur = '';
    foreach ($words as $w) {
        $try = $cur === '' ? $w : $cur . ' ' . $w;
        $bb = imagettfbbox($size, 0, $font, $try);
        if (($bb[2] - $bb[0]) > $maxw && $cur !== '') {
            $lines[] = $cur;
            $cur = $w;
        } else {
            $cur = $try;
        }
    }
    if ($cur !== '') $lines[] = $cur;
    return $lines;
}

/**
 * Build a branded hero by compositing onto the ready-made template:
 *   assets/images/imgcomponent/seo tren startup 2026.png  (1456x728)
 * The generated photo goes into the right white slot (x>=798); the "Judul
 * Artikel" placeholder is covered with the card's orange and the real title
 * is rendered in its place.
 * Returns the RELATIVE path (assets/images/blog/xxx.jpg) or null on failure.
 */
function uo_build_hero_image(string $title, ?string $bgPhotoPath, string $outAbsPath): ?string {
    if (!function_exists('imagecreatetruecolor') || !is_file(UO_FONT_BOLD) || !is_file(UO_HERO_TEMPLATE)) {
        return null;
    }
    $tpl = @imagecreatefromstring(@file_get_contents(UO_HERO_TEMPLATE));
    if (!$tpl) return null;
    imagepalettetotruecolor($tpl);
    $W = imagesx($tpl); $H = imagesy($tpl);

    $font = UO_FONT_BOLD; // Poppins (standard title weight)
    $white = imagecolorallocate($tpl, 255, 255, 255);

    // 1) Photo fills the WHOLE white slot, following the card's curved edge.
    //    We cover-fit the photo across the full right region (from x=705, the
    //    card's minimum extent) then copy it ONLY over pixels that are white in
    //    the template — so the orange card (incl. its rounded corners) stays on
    //    top and the corner slivers get filled with the photo instead of white.
    $rx = 705; $rw = $W - $rx; $rh = $H;
    if ($bgPhotoPath && is_file($bgPhotoPath)) {
        $data = @file_get_contents($bgPhotoPath);
        $src = $data ? @imagecreatefromstring($data) : false;
        if ($src) {
            $sw = imagesx($src); $sh = imagesy($src);
            $scale = max($rw / $sw, $rh / $sh);
            $nw = (int)ceil($sw * $scale); $nh = (int)ceil($sh * $scale);
            $region = imagecreatetruecolor($rw, $rh);
            imagecopyresampled($region, $src, (int)(($rw - $nw) / 2), (int)(($rh - $nh) / 2), 0, 0, $nw, $nh, $sw, $sh);
            for ($dx = 0; $dx < $rw; $dx++) {
                $x = $rx + $dx;
                for ($y = 0; $y < $rh; $y++) {
                    $tc = imagecolorat($tpl, $x, $y);
                    if ((($tc >> 16) & 255) > 235 && (($tc >> 8) & 255) > 235 && ($tc & 255) > 235) {
                        imagesetpixel($tpl, $x, $y, imagecolorat($region, $dx, $y));
                    }
                }
            }
            imagedestroy($region);
            imagedestroy($src);
        }
    }

    // 2) Erase the "Judul Artikel" placeholder seamlessly: reconstruct the
    //    card's orange gradient by interpolating, per column, between two clean
    //    reference rows just above (y=286) and just below (y=481) the band.
    //    This rebuilds both the vertical AND horizontal gradient with no patch.
    $yTop = 286; $yBot = 481;
    for ($x = 40; $x <= 752; $x++) {
        $ct = imagecolorat($tpl, $x, $yTop);
        $cb = imagecolorat($tpl, $x, $yBot);
        $rt = ($ct >> 16) & 255; $gt = ($ct >> 8) & 255; $bt = $ct & 255;
        $rb = ($cb >> 16) & 255; $gb = ($cb >> 8) & 255; $bb = $cb & 255;
        for ($y = $yTop + 1; $y < $yBot; $y++) {
            $t = ($y - $yTop) / ($yBot - $yTop);
            $r = (int)($rt + ($rb - $rt) * $t);
            $g = (int)($gt + ($gb - $gt) * $t);
            $b = (int)($bt + ($bb - $bt) * $t);
            imagesetpixel($tpl, $x, $y, ($r << 16) | ($g << 8) | $b);
        }
    }

    // 3) Render the real title where the placeholder was (auto-fit + wrap).
    $px = 55; $innerW = 700; $top = 318; $bottomLimit = 600;
    $size = 54; $lines = [];
    for (; $size >= 30; $size -= 2) {
        $lines = uo_wrap_text($font, $size, $title, $innerW);
        $lh = $size * 1.16;
        if (count($lines) <= 4 && ($top + count($lines) * $lh) <= $bottomLimit) break;
    }
    $lh = $size * 1.16;
    $ty = $top + $size;
    foreach ($lines as $ln) {
        imagettftext($tpl, $size, 0, $px, (int)$ty, $white, $font, $ln);
        $ty += $lh;
    }

    if (!is_dir(dirname($outAbsPath))) @mkdir(dirname($outAbsPath), 0755, true);
    $ok = imagejpeg($tpl, $outAbsPath, 88);
    imagedestroy($tpl);
    if (!$ok) return null;
    return ltrim(str_replace(DIR_ROOT, '', $outAbsPath), '/\\');
}

/* ---------- mid-content relevant card ---------- */

/**
 * Pick the internal page most relevant to the article.
 * Preference: a page whose slug the article already links to; otherwise the
 * page with the best keyword overlap against the title/content.
 * Returns ['url','title','image'] or null.
 */
function uo_pick_relevant_page(string $content, string $title): ?array {
    try {
        $pages = Database::fetchAll(
            "SELECT slug, title, meta_title, og_image FROM pages WHERE slug <> '' AND slug <> 'blog'"
        );
    } catch (Exception $e) { return null; }
    if (!$pages) return null;

    $titleLow   = strtolower($title);
    $contentLow = strtolower(strip_tags($content));
    
    $best = null;
    $bestScore = -1;

    foreach ($pages as $p) {
        $score = 0;
        // Split slug and title keywords (skip common short words)
        $words = preg_split('/[^a-z0-9]+/', strtolower($p['title'] . ' ' . $p['slug']));
        $uniqueWords = array_unique(array_filter($words, fn($w) => strlen($w) >= 3 && !in_array($w, ['dan', 'plus', 'yang', 'untuk', 'dengan', 'urban', 'office'], true)));

        // 1. Keyword match in Title (High priority: +10 pts per matching keyword)
        foreach ($uniqueWords as $w) {
            if (strpos($titleLow, $w) !== false) {
                $score += 10;
            }
        }

        // 2. Frequency of keyword in Content (+1 pt per occurrence)
        foreach ($uniqueWords as $w) {
            $score += min(substr_count($contentLow, $w), 5); // cap at 5 per word to prevent spam bias
        }

        // 3. Bonus if page URL is explicitly linked in the article (+5 pts)
        if (strpos($content, '/' . $p['slug'] . '/') !== false) {
            $score += 5;
        }

        if ($score > $bestScore) {
            $bestScore = $score;
            $best = $p;
        }
    }

    return $best ? uo_page_card_data($best) : uo_page_card_data($pages[0]);
}

function uo_page_card_data(array $p): array {
    $img = !empty($p['og_image'])
        ? (strpos($p['og_image'], 'http') === 0 ? $p['og_image'] : BASE_URL . ltrim($p['og_image'], '/'))
        : BASE_URL . 'assets/images/imgcomponent/app-component.png';
    return [
        'url'   => BASE_URL . ($p['slug'] === '' ? '' : $p['slug'] . '/'),
        'title' => $p['title'],
        'image' => $img,
    ];
}

/**
 * Insert a clickable relevant-page card near the middle of the article HTML.
 * Non-fatal: returns original content if anything is missing.
 */
function uo_insert_midcontent_card(string $content, string $title, string $bgUrl = '', string $cta = ''): string {
    $page = uo_pick_relevant_page($content, $title);
    if (!$page) return $content;

    // Room/office photo as background (orange overlay keeps it branded + readable);
    // falls back to a flat orange gradient when no photo is available.
    // Left-heavy gradient: darker over the text (left), fading to reveal the
    // photo on the right — so the room/office image stays clearly visible.
    // Background style: on desktop, prioritize right-top alignment so faces/office photos don't get cut off.
    $bgStyle = $bgUrl !== ''
        ? "background-image:linear-gradient(90deg,rgba(205,63,10,.96) 0%,rgba(226,78,18,.88) 40%,rgba(235,90,20,.45) 75%,rgba(242,105,28,.15) 100%),url('" . htmlspecialchars($bgUrl, ENT_QUOTES) . "');background-size:cover;background-position:right 25%;"
        : 'background:linear-gradient(135deg,#F2691C,#E24E12);';

    // Soft-sell line: use the AI-written one when provided, else a gentle default.
    $desc = $cta !== ''
        ? $cta
        : 'Dukung langkah bisnis Anda dengan fasilitas lengkap, alamat prestisius, dan layanan siap pakai dari Urban Office.';

    $card =
        '<div class="uo-midcard" style="margin:1.8rem 0;padding:20px 24px;border-radius:14px;' . $bgStyle . 'display:flex;align-items:center;justify-content:space-between;gap:18px;box-shadow:0 6px 20px rgba(226,78,18,.18);">'
        . '<div style="flex:1;min-width:200px;">'
        . '<div style="font-size:.68rem;font-weight:800;letter-spacing:.08em;color:#FFD400;text-transform:uppercase;margin-bottom:4px;">Solusi Urban Office</div>'
        . '<div style="font-size:1.15rem;font-weight:800;color:#ffffff;line-height:1.25;margin-bottom:6px;text-shadow:0 1px 3px rgba(0,0,0,.35);">' . htmlspecialchars($page['title'], ENT_QUOTES) . '</div>'
        . '<div style="color:#ffffff;opacity:.95;font-size:.88rem;line-height:1.45;text-shadow:0 1px 3px rgba(0,0,0,.35);max-width:680px;">' . htmlspecialchars($desc, ENT_QUOTES) . '</div>'
        . '</div>'
        . '<a href="' . htmlspecialchars($page['url'], ENT_QUOTES) . '" style="flex:0 0 auto;background:#ffffff;color:#E24E12;font-weight:700;font-size:.82rem;padding:8px 16px;border-radius:20px;text-decoration:none;white-space:nowrap;box-shadow:0 2px 6px rgba(0,0,0,.15);">Selengkapnya &rarr;</a>'
        . '</div>'
        . '<style>'
        . '@media (max-width: 640px) {'
        . '  .uo-midcard { margin: 1.3rem 0 !important; padding: 14px 16px !important; flex-direction: column !important; align-items: flex-start !important; gap: 10px !important; border-radius: 12px !important; }'
        . '  .uo-midcard > div > div:nth-child(2) { font-size: 1.05rem !important; margin-bottom: 4px !important; }'
        . '  .uo-midcard > div > div:nth-child(3) { font-size: 0.82rem !important; line-height: 1.38 !important; }'
        . '  .uo-midcard > a { font-size: 0.78rem !important; padding: 6px 14px !important; align-self: flex-end !important; margin-top: 4px !important; }'
        . '}'
        . '</style>';

    // Find block-level closing tags; insert after the one nearest the midpoint.
    if (!preg_match_all('/<\/(p|ul|ol|h2|h3|figure|blockquote)>/i', $content, $m, PREG_OFFSET_CAPTURE)) {
        return $content . $card;
    }
    $mid = strlen($content) / 2;
    $bestPos = null; $bestDist = PHP_INT_MAX;
    foreach ($m[0] as $match) {
        $end = $match[1] + strlen($match[0]);
        $dist = abs($end - $mid);
        if ($dist < $bestDist) { $bestDist = $dist; $bestPos = $end; }
    }
    if ($bestPos === null) return $content . $card;
    return substr($content, 0, $bestPos) . $card . substr($content, $bestPos);
}

/**
 * Build the closing CTA block (branch locations + WhatsApp button), appended
 * to the end of every article. Locations come from inc/locations_data.php.
 */
function uo_build_end_cta(): string {
    $cities = [];
    $path = DIR_ROOT . 'inc/locations_data.php';
    if (is_file($path)) {
        require_once $path;
        if (isset($locations_db) && is_array($locations_db)) {
            foreach ($locations_db as $b) {
                $city = trim($b['city'] ?? '');
                if ($city === '') continue;
                $area = trim($b['short_title'] ?? ($b['location'] ?? ''));
                if ($area !== '') $cities[$city][] = $area;
                elseif (!isset($cities[$city])) $cities[$city] = [];
            }
        }
    }
    $locHtml = '';
    foreach ($cities as $city => $areas) {
        $areas = array_values(array_unique($areas));
        $areaTxt = $areas
            ? ': <em style="color:#666666;">' . htmlspecialchars(implode(', ', $areas), ENT_QUOTES) . '</em>'
            : '';
        $locHtml .= '<li style="margin-bottom:6px;"><strong>' . htmlspecialchars($city, ENT_QUOTES) . '</strong>' . $areaTxt . '</li>';
    }

    $wa    = defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '';
    $waMsg = rawurlencode('Halo Urban Office, saya tertarik dengan layanan kantor & virtual office. Boleh info lengkapnya?');
    $waLink = $wa ? 'https://wa.me/' . $wa . '?text=' . $waMsg : '#';

    // Editorial style (like the production reference): orange left-border accent,
    // dark text on the page background — NOT a filled orange box.
    return
        '<div style="margin:2.6rem 0 1rem;padding:6px 0 6px 24px;border-left:5px solid #F2691C;">'
        . '<h3 style="margin:0 0 14px;font-size:1.5rem;font-weight:800;color:#1a1a1a;line-height:1.3;">Siap Tingkatkan Kredibilitas Bisnis Anda Bersama Urban Office?</h3>'
        . '<p style="margin:0 0 18px;line-height:1.7;color:#333333;">Dapatkan alamat bisnis strategis, legalitas usaha, dan layanan operasional profesional tanpa biaya sewa gedung yang mahal. <strong>Urban Office</strong> menyediakan layanan lengkap dengan pengelolaan surat terintegrasi serta akses ruang rapat modern di berbagai kota strategis.</p>'
        . '<p style="margin:0 0 10px;font-weight:700;color:#1a1a1a;">&#128205; Lokasi Urban Office Tersedia Di:</p>'
        . '<ul style="margin:0 0 18px;padding-left:22px;color:#333333;line-height:1.75;list-style:disc;">' . $locHtml . '</ul>'
        . '<p style="margin:0 0 12px;font-weight:700;color:#1a1a1a;">&#128640; Gunakan layanan Urban Office sekarang!</p>'
        . '<p style="margin:0;line-height:1.7;color:#333333;">Hubungi tim <strong>Urban Office</strong> untuk berkonsultasi dan dapatkan penawaran paket kantor terbaik sesuai lokasi pilihan Anda &mdash; <a href="' . $waLink . '" style="color:#E24E12;font-weight:700;">chat via WhatsApp</a>.</p>'
        . '</div>';
}
