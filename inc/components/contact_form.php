<?php
/**
 * Contact & Lead Capture Form Component Template
 * Placed in split layout with a Customer Testimonials Slider on the left
 */

require_once dirname(dirname(__FILE__)) . '/functions.php';
?>
<section class="section contact-section" id="contact">
    <div class="container">
        <!-- Section Title centered above the split columns -->
        <h2 class="text-center" style="margin-bottom: 50px;">Tanggapan Mereka Jadi Bagian Urban Office</h2>
        
        <div class="contact-grid">
            <!-- Left Side: Testimonial Slider -->
            <!-- ============================================================================
                 TESTIMONI PELANGGAN — branch-aware: leads with the CURRENT VO branch's own
                 review (from inc/locations_data.php 'testimonial') so the location matches the
                 page, then city-neutral fallbacks fill the slider. Plain testimonials only —
                 NO Google branding (would misrepresent placeholder text as genuine Google
                 reviews). NOTE: content is still PLACEHOLDER — replace the per-branch
                 'testimonial' in locations_data + the neutral fallbacks below with REAL reviews.
                 ============================================================================ -->
            <?php
            // $vo_branch is in scope on VO branch pages (set in virtual-office-surabaya/index.php);
            // unset elsewhere, so the branch review is simply skipped on non-VO pages.
            $reviews = [];
            $reviews_are_google = false;
            if (!empty($vo_branch['google_reviews'])) {
                // Real Google Maps reviews for this branch → show the Google badge (source genuinely IS Google).
                $reviews_are_google = true;
                foreach ($vo_branch['google_reviews'] as $gr) {
                    $reviews[] = [
                        'name'   => $gr['name'] ?? 'Pelanggan',
                        'role'   => '',
                        'text'   => $gr['text'] ?? '',
                        'rating' => (int) ($gr['rating'] ?? 5),
                    ];
                }
            } else {
                // No real Google reviews yet → plain customer testimonials (NO Google badge): the
                // branch's own placeholder testimonial leads, then city-neutral fallbacks.
                if (!empty($vo_branch['testimonial']['text'])) {
                    $bt = $vo_branch['testimonial'];
                    $reviews[] = [
                        'name'   => $bt['name'] ?? 'Pelanggan',
                        'role'   => $bt['role'] ?? (isset($vo_branch['location']) ? 'Cabang ' . $vo_branch['location'] : ''),
                        'text'   => $bt['text'],
                        'rating' => (int) ($bt['rating'] ?? 5),
                    ];
                }
                $reviews[] = ['name' => 'Ayu Agustiningsih', 'role' => 'Pelanggan Virtual Office', 'text' => 'Padahal sewa virtual office di sini, tapi benefit yang didapat luar biasa. Penanganan surat rapi dan langsung diinfo via WA!', 'rating' => 5];
                $reviews[] = ['name' => 'Stepanus Budi', 'role' => 'Pelanggan Private Office', 'text' => 'Kantor sangat bersih, pelayanan super bagus, staff ramah. Sangat nyaman untuk kerja temporary atau rapat dengan klien.', 'rating' => 5];
                $reviews[] = ['name' => 'Handoko', 'role' => 'Pelanggan', 'text' => 'Kantor yang sangat bersih, pelayanan super bagus, nyaman buat kerja temporary di sini.', 'rating' => 5];
            }
            ?>
            <style>
            /* Initial-letter avatar — used for reviewers without a public photo (same default Google
               itself shows). Reuses .review-avatar sizing/border. */
            .review-avatar-initial { display: flex; align-items: center; justify-content: center; background: hsl(var(--clr-primary)); color: #FFFFFF; font-size: 34px; font-weight: 800; line-height: 1; }
            .review-role { font-size: 12.5px; color: #777777; margin: 2px 0 0; }
            /* Google badge overlapping the avatar (only rendered for real Google reviews) */
            .review-avatar-wrap { position: relative; display: inline-block; margin-bottom: 20px; }
            .review-avatar-wrap .review-avatar { margin-bottom: 0; }
            .review-google-badge { position: absolute; right: -2px; bottom: -2px; width: 30px; height: 30px; border-radius: 50%; background: #FFFFFF; border: 0.5px solid #E0E0E0; box-shadow: 0 1px 3px rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center; }
            .review-google-badge svg { width: 18px; height: 18px; display: block; }
            </style>
            <div class="contact-testimonial-slider-wrap">
                <div class="contact-slider-container">
                    <div class="contact-slider-window">
                        <?php foreach ($reviews as $ri => $rev):
                            $initial = function_exists('mb_substr') ? mb_strtoupper(mb_substr(trim($rev['name']), 0, 1)) : strtoupper(substr(trim($rev['name']), 0, 1));
                            $stars = str_repeat('★', max(1, min(5, (int) $rev['rating'])));
                        ?>
                        <div class="contact-review-slide<?php echo $ri === 0 ? ' active' : ''; ?>">
                            <div class="review-avatar-wrap">
                                <div class="review-avatar review-avatar-initial"><?php echo sanitize($initial); ?></div>
                                <?php if ($reviews_are_google): ?>
                                <span class="review-google-badge" aria-label="Ulasan dari Google"><svg viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/><path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/><path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571l6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/></svg></span>
                                <?php endif; ?>
                            </div>
                            <p class="review-quote">"<?php echo sanitize($rev['text']); ?>"</p>
                            <div class="review-stars"><?php echo $stars; ?></div>
                            <h4 class="review-author"><?php echo sanitize($rev['name']); ?></h4>
                            <?php if ($reviews_are_google): ?><p class="review-role">Ulasan dari Google</p><?php elseif (!empty($rev['role'])): ?><p class="review-role"><?php echo sanitize($rev['role']); ?></p><?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        
                    </div>
                    
</div>
                
                <!-- Dot Indicators -->
                <div class="contact-slider-dots">
                    <?php foreach ($reviews as $ri => $rev): ?>
                    <span class="contact-dot<?php echo $ri === 0 ? ' active' : ''; ?>" onclick="goToContactReview(<?php echo $ri; ?>)"></span>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Right Side: Contact Form -->
            <div class="contact-form-wrap">
                <form action="<?php echo BASE_URL; ?>inc/lead_handler.php" method="POST">
                    <?php echo csrf_field(); ?>
                    
                    <!-- Honeypot for spam bots -->
                    <div class="form-group honey-field">
                        <label>Email Confirm</label>
                        <input type="text" name="email_confirm" autocomplete="off">
                    </div>
                    
                    <div class="form-group">
                        <label for="contact-name">DATA (*)</label>
                        <input type="text" id="contact-name" name="name" class="form-control" placeholder="Nama" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact-email">EMAIL (*)</label>
                            <input type="email" id="contact-email" name="email" class="form-control" placeholder="Email" required>
                        </div>
                        <div class="form-group">
                            <label for="contact-phone">TELP / WA (*)</label>
                            <input type="tel" id="contact-phone" name="phone" class="form-control" placeholder="Telp / WA" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="contact-service">PRODUK (*)</label>
                        <?php
                        $current_service = '';
                        if (isset($page_slug)) {
                            if ($page_slug === 'sharing-room-office') {
                                $current_service = 'Sharing Room Office';
                            } elseif ($page_slug === 'sewa-kantor-surabaya') {
                                $current_service = 'Private Office';
                            } elseif ($page_slug === 'virtual-office-surabaya') {
                                $current_service = 'Virtual Office';
                            } elseif ($page_slug === 'meeting-room-surabaya') {
                                $current_service = 'Meeting Room';
                            } elseif ($page_slug === 'coworking-space-urban-office') {
                                $current_service = 'Coworking Space';
                            } elseif ($page_slug === 'event-space-55k-perjam-urbanoffice') {
                                $current_service = 'Event Space';
                            }
                        }
                        ?>
                        <select id="contact-service" name="service" class="form-control" required>
                            <option value="" disabled <?php echo empty($current_service) ? 'selected' : ''; ?>>Pilih Layanan...</option>
                            <option value="Virtual Office" <?php echo ($current_service === 'Virtual Office') ? 'selected' : ''; ?>>Virtual Office (Alamat Bisnis)</option>
                            <option value="Private Office" <?php echo ($current_service === 'Private Office') ? 'selected' : ''; ?>>Private Office (Sewa Ruang Kantor)</option>
                            <option value="Meeting Room" <?php echo ($current_service === 'Meeting Room') ? 'selected' : ''; ?>>Meeting Room (Sewa Ruang Rapat)</option>
                            <option value="Coworking Space" <?php echo ($current_service === 'Coworking Space') ? 'selected' : ''; ?>>Coworking Space (Meja Kerja Bersama)</option>
                            <option value="Event Space" <?php echo ($current_service === 'Event Space') ? 'selected' : ''; ?>>Event Space (Seminar / Workshop)</option>
                            <option value="Sharing Room Office" <?php echo ($current_service === 'Sharing Room Office') ? 'selected' : ''; ?>>Sharing Room Office</option>
                            <option value="Pendirian Legalitas" <?php echo ($current_service === 'Pendirian Legalitas') ? 'selected' : ''; ?>>Pendirian PT / CV / PMA</option>
                            <option value="Pajak dan Akunting" <?php echo ($current_service === 'Pajak dan Akunting') ? 'selected' : ''; ?>>Jasa Pajak & Akuntansi</option>
                        </select>
                    </div>
                    
                    <?php
                    // A visit date only makes sense for services the client physically comes in for.
                    // Virtual Office (and legality/tax pages) are lead/quote flows where forcing a
                    // date is pure friction, so it stays optional there. data-visit-required lets
                    // main.js keep this rule when it toggles Offer Mode on/off.
                    $visit_based_services = ['Meeting Room', 'Coworking Space', 'Event Space', 'Private Office', 'Sharing Room Office'];
                    $is_visit_based = in_array($current_service, $visit_based_services, true);
                    ?>
                    <!-- Visit Booking Row (hidden in Offer Mode) -->
                    <div class="form-row" id="visit-booking-row">
                        <div class="form-group">
                            <label for="contact-people">JUMLAH ORANG</label>
                            <input type="number" id="contact-people" name="jumlah_orang" class="form-control" placeholder="Jumlah Orang" min="1">
                        </div>
                        <div class="form-group">
                            <label for="contact-date"><?php echo $is_visit_based ? 'TANGGAL KUNJUNGAN (*)' : 'TANGGAL KUNJUNGAN (opsional)'; ?></label>
                            <input type="date" id="contact-date" name="tanggal" class="form-control" data-visit-required="<?php echo $is_visit_based ? '1' : '0'; ?>" <?php echo $is_visit_based ? 'required' : ''; ?>>
                        </div>
                    </div>

                    <!-- Offer Mode Rows (visible in Offer Mode) -->
                    <div class="form-group" id="offer-location-group" style="display: none;">
                        <label for="contact-location">LOKASI CABANG</label>
                        <select id="contact-location" name="location" class="form-control">
                            <option value="MERR (Surabaya Timur)">MERR (Surabaya Timur)</option>
                            <option value="Klampis (Surabaya Timur)">Klampis (Surabaya Timur)</option>
                            <option value="Grand Sungkono (Surabaya Barat)">Grand Sungkono (Surabaya Barat)</option>
                            <option value="Fatmawati (Jakarta Selatan)">Fatmawati (Jakarta Selatan)</option>
                            <option value="Gorebiz (Jakarta Timur)">Gorebiz (Jakarta Timur)</option>
                            <option value="PTGM Tower (Gresik)">PTGM Tower (Gresik)</option>
                            <option value="Medan">Medan</option>
                        </select>
                    </div>

                    <div class="form-group" id="offer-budget-group" style="display: none;">
                        <label for="contact-budget">EKSPEKTASI BUDGET (*)</label>
                        <input type="text" id="contact-budget" name="budget" class="form-control" placeholder="Contoh: Rp 350.000">
                    </div>

                    <div class="form-group" id="offer-message-group" style="display: none;">
                        <label for="contact-pesan">PESAN (*)</label>
                        <textarea id="contact-pesan" name="pesan" class="form-control" placeholder="Tulis pesan Anda..." rows="3"></textarea>
                    </div>
                    
                    <!-- Hidden or default message body for compatible DB handling -->
                    <input type="hidden" id="contact-message" name="message" value="">
                    
                    <!-- Offer Mode State Trackers -->
                    <input type="hidden" id="is-offer-mode" name="is_offer_mode" value="0">
                    <input type="hidden" id="offer-package" name="offer_package" value="">
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Kirim & Hubungi via WhatsApp</button>
                </form>
            </div>
        </div>
    </div>
</section>
