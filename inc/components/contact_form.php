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
            <div class="contact-testimonial-slider-wrap">
                <div class="contact-slider-container">
                    <button type="button" class="slider-arrow prev-arrow" onclick="prevContactReview()" aria-label="Review Sebelumnya">&#10094;</button>
                    
                    <div class="contact-slider-window">
                        <!-- Slide 1 -->
                        <div class="contact-review-slide active">
                            <div class="review-avatar">
                                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80" alt="Ayu Agustiningsih">
                            </div>
                            <p class="review-quote">"Padahal sewa virtual office disini, tapi benefit yang didapat luar biasa. Penanganan surat rapi dan langsung diinfo via WA!"</p>
                            <div class="review-stars">★★★★★</div>
                            <h4 class="review-author">Ayu Agustiningsih</h4>
                        </div>
                        
                        <!-- Slide 2 -->
                        <div class="contact-review-slide">
                            <div class="review-avatar">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80" alt="Stepanus Budi Raharjo">
                            </div>
                            <p class="review-quote">"Kantor sangat bersih, pelayanan super bagus, staff ramah. Sangat nyaman untuk kerja temporary atau rapat dengan klien."</p>
                            <div class="review-stars">★★★★★</div>
                            <h4 class="review-author">Stepanus Budi</h4>
                        </div>
                        
                        <!-- Slide 3 -->
                        <div class="contact-review-slide">
                            <div class="review-avatar">
                                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80" alt="Lestari Handayani">
                            </div>
                            <p class="review-quote">"Coworking space bagus, adminnya ramah, lokasi strategis di Merr Surabaya. Recommended buat kerja fokus."</p>
                            <div class="review-stars">★★★★★</div>
                            <h4 class="review-author">Lestari Handayani</h4>
                        </div>
                        
                        <!-- Slide 4 -->
                        <div class="contact-review-slide">
                            <div class="review-avatar">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80" alt="Handoko">
                            </div>
                            <p class="review-quote">"Kantor yang sangat bersih Pelayanan super bagus Nyaman buat kerja temporary disini"</p>
                            <div class="review-stars">★★★★★</div>
                            <h4 class="review-author">Handoko</h4>
                        </div>
                    </div>
                    
                    <button type="button" class="slider-arrow next-arrow" onclick="nextContactReview()" aria-label="Review Selanjutnya">&#10095;</button>
                </div>
                
                <!-- Dot Indicators -->
                <div class="contact-slider-dots">
                    <span class="contact-dot active" onclick="goToContactReview(0)"></span>
                    <span class="contact-dot" onclick="goToContactReview(1)"></span>
                    <span class="contact-dot" onclick="goToContactReview(2)"></span>
                    <span class="contact-dot" onclick="goToContactReview(3)"></span>
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
                    
                    <!-- Visit Booking Row (hidden in Offer Mode) -->
                    <div class="form-row" id="visit-booking-row">
                        <div class="form-group">
                            <label for="contact-people">JUMLAH ORANG</label>
                            <input type="number" id="contact-people" name="jumlah_orang" class="form-control" placeholder="Jumlah Orang" min="1">
                        </div>
                        <div class="form-group">
                            <label for="contact-date">TANGGAL (*)</label>
                            <input type="date" id="contact-date" name="tanggal" class="form-control" required>
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
