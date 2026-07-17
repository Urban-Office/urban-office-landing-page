<?php
/**
 * Shared Footer Template
 * Outputs footer sections, social icons, mobile sticky action bar, and runs script imports
 */

require_once dirname(__FILE__) . '/config.php';

// Prevent direct access
if (basename($_SERVER['SCRIPT_FILENAME']) === 'footer.php') {
    header('HTTP/1.1 403 Forbidden');
    exit('Direct access forbidden.');
}
?>

    <!-- Main Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Col 1: About & Socials -->
                <div class="footer-col footer-about">
                    <a href="<?php echo BASE_URL; ?>" class="footer-logo" aria-label="Urban Office Homepage" style="margin-bottom: 20px; display: block;">
                        <img src="<?php echo BASE_URL; ?>assets/images/imgcomponent/urban%20office%20new%20logo.png" alt="Urban Office Logo" style="max-height: 50px; width: auto;">
                    </a>
                    <p>Urban Office adalah penyedia layanan ruang kerja profesional. Menyediakan Ruang Kerja Virtual Office, Meeting Room, Private Office, dan Event Space, Coworking Space di berbagai lokasi strategis, dan layanan bisnis lainnya.</p>
                    <div class="footer-socials">
                        <a href="https://www.facebook.com/urbanoffice.co.id/" target="_blank" class="footer-social-btn fb-btn" aria-label="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://www.instagram.com/urbanoffice.co.id/" target="_blank" class="footer-social-btn ig-btn" aria-label="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="https://x.com/urbanofficecoid" target="_blank" class="footer-social-btn tw-btn" aria-label="Twitter">
                            <i class="bi bi-twitter-x"></i>
                        </a>
                        <a href="https://id.linkedin.com/company/urbanofficeid" target="_blank" class="footer-social-btn ln-btn" aria-label="LinkedIn">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <a href="https://www.youtube.com/channel/UCxIPwkXzEu-0mmBlIZL2yYw" target="_blank" class="footer-social-btn yt-btn" aria-label="YouTube">
                            <i class="bi bi-youtube"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Col 2: Services (Ruang Kerja) -->
                <div class="footer-col">
                    <h3>Layanan</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>virtual-office-surabaya/">Virtual Office</a></li>
                        <li><a href="<?php echo BASE_URL; ?>sewa-kantor-surabaya/">Sewa Kantor</a></li>
                        <li><a href="<?php echo BASE_URL; ?>meeting-room-surabaya/">Ruang Meeting</a></li>
                        <li><a href="<?php echo BASE_URL; ?>event-space-55k-perjam-urbanoffice/">Event Space</a></li>
                        <li><a href="<?php echo BASE_URL; ?>coworking-space-urban-office/">Coworking Space</a></li>
                        <li><a href="<?php echo BASE_URL; ?>sharing-room-office/">Sharing Room Office</a></li>
                    </ul>
                </div>
                
                <!-- Col 3: Useful Links (Layanan Bisnis & Lainnya) -->
                <div class="footer-col">
                    <h3>Lainnya</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>pendirian-perorangan-plus-virtual-office/">Perusahaan Perorangan</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pendirian-pt-include-virtual-office/">Pendirian PT</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pendirian-cv-virtual-office/">Pendirian CV</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pendirian-pma-plus-virtual-office/">Pendirian PMA</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pajak-dan-akunting/">Jasa Pajak & Akunting</a></li>
                        <li><a href="<?php echo BASE_URL; ?>lokasi-urban-office/">Lokasi</a></li>
                        <li class="footer-inline-links">
                            <a href="<?php echo BASE_URL; ?>blog/">Artikel</a>
                            <span>|</span>
                            <a href="<?php echo BASE_URL; ?>urban-office-karir/">Karir</a>
                            <span>|</span>
                            <a href="https://my.urbanoffice.id" target="_blank">My Urban Office</a>
                        </li>
                    </ul>
                </div>
                
                <!-- Col 4: Location & Map -->
                <div class="footer-col">
                    <h3>Head Office</h3>
                    <div class="footer-office-info">
                        <strong>Gedung Urban Office</strong>
                        <p>Jl. Dr. Ir. H. Soekarno No.470, Kedung Baruk, Surabaya, Jawa Timur 60298, Indonesia</p>
                        <p>Phone : 62-31-87855578</p>
                        <p>WA : 081 0762 0100</p>
                    </div>
                    <div class="footer-map-wrapper" style="position: relative; margin-top: 12px;">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3957.348638974577!2d112.78023107476097!3d-7.318892692689255!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7fa535f29910d%3A0x8bbd360efbe36368!2sUrban%20Office%20Surabaya!5e0!3m2!1sid!2sid!4v1718000000000!5m2!1sid!2sid" width="100%" height="110" style="border:0; border-radius: var(--radius-sm); display: block;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        <a href="https://maps.app.goo.gl/nACcB9LqEPn27REY9" target="_blank" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10; cursor: pointer;" aria-label="Buka Google Maps"></a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="copyright">
                    Copyright &copy; 2026 PT. Urban Kreasi Bersama. All Rights Reserved.
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Widget -->
    <a href="https://api.whatsapp.com/send?phone=6285107620100&text=Halo%20Urban%20Office%2C%20saya%20tertarik%20dengan%20layanan%20Urban%20Office.%20Mohon%20info%20selengkapnya." 
       target="_blank" 
       class="floating-wa-widget" 
       aria-label="Hubungi kami melalui WhatsApp">
        <div class="wa-button">
            <span class="wa-text">Hubungi CS</span>
            <span class="wa-icon"><i class="bi bi-whatsapp"></i></span>
        </div>
    </a>

    <!-- Frontend Script file -->
    <script src="<?php echo BASE_URL; ?>assets/js/main.js?v=<?php echo filemtime(dirname(__FILE__) . '/../assets/js/main.js'); ?>" defer></script>
</body>
</html>
<?php
// Capture, minify, and save to local Cache folder
end_page_cache($page_slug);
?>
