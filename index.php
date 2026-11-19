<?php
/**
 * Urban Office - Homepage Template
 * Refactored to use dynamic shared headers/footers and external style.css
 */

require_once __DIR__ . '/inc/config.php';

// Dynamic routing fallback for clean SEO URLs in environments where web server rewrites are not configured
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$request_path = urldecode(parse_url($request_uri, PHP_URL_PATH) ?? '');

$subfolder = DIR_SUBFOLDER;
if (!empty($subfolder) && strpos($request_path, $subfolder) === 0) {
    $request_path = substr($request_path, strlen($subfolder));
}
$request_path = trim($request_path, '/');

if (!empty($request_path)) {
    // Check for sitemap
    if ($request_path === 'sitemap.xml') {
        require_once __DIR__ . '/sitemap.php';
        exit;
    }
    
    $parts = explode('/', $request_path);

    // Dynamic service landing pages: /{prefix}{city-or-branch}/
    // Real folders (e.g. virtual-office-surabaya/, sewa-kantor-surabaya/) are excluded
    // because the web server serves them directly before falling back to index.php.
    $service_clean_routes = [
        ['prefix' => 'virtual-office-',  'exclude' => 'virtual-office-surabaya',            'param' => 'branch', 'target' => '/virtual-office-surabaya/index.php'],
        ['prefix' => 'sewa-kantor-',     'exclude' => 'sewa-kantor-surabaya',               'param' => 'lokasi', 'target' => '/sewa-kantor-surabaya/index.php'],
        ['prefix' => 'meeting-room-',    'exclude' => 'meeting-room-surabaya',              'param' => 'lokasi', 'target' => '/meeting-room-surabaya/index.php'],
        ['prefix' => 'coworking-space-', 'exclude' => 'coworking-space-urban-office',       'param' => 'lokasi', 'target' => '/coworking-space-urban-office/index.php'],
        ['prefix' => 'event-space-',     'exclude' => 'event-space-55k-perjam-urbanoffice', 'param' => 'lokasi', 'target' => '/event-space-55k-perjam-urbanoffice/index.php'],
    ];

    if (count($parts) === 1) {
        foreach ($service_clean_routes as $route) {
            if ($parts[0] !== $route['exclude'] && strpos($parts[0], $route['prefix']) === 0) {
                $route_value = substr($parts[0], strlen($route['prefix']));
                if ($route_value !== '' && preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]*$/', $route_value)) {
                    $_GET[$route['param']] = $route_value;
                    require_once __DIR__ . $route['target'];
                    exit;
                }
            }
        }
    }

    if (count($parts) >= 2) {
        if ($parts[0] === 'lokasi-urban-office' && $parts[1] !== 'index.php') {
            $_GET['slug'] = $parts[1];
            require_once __DIR__ . '/lokasi-urban-office/detail.php';
            exit;
        }
        if ($parts[0] === 'blog' && $parts[1] !== 'index.php' && $parts[1] !== 'article.php') {
            $_GET['slug'] = $parts[1];
            require_once __DIR__ . '/blog/article.php';
            exit;
        }
        if ($parts[0] === 'category') {
            $_GET['slug'] = $parts[1];
            require_once __DIR__ . '/blog/category.php';
            exit;
        }
        if ($parts[0] === 'tag') {
            $_GET['slug'] = $parts[1];
            require_once __DIR__ . '/blog/tag.php';
            exit;
        }
    }
}

require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/functions.php';

$page_slug = ''; // Homepage identifier
require_once __DIR__ . '/inc/header.php';
?>

    <!-- HERO SLIDER -->
    <section class="hero-slider">
        <div class="slider-wrapper">
            <!-- Slide 1 -->
            <div class="slide active">
                <div class="slide-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/privateoffice/private-office-corporate.webp');"></div>
                <div class="slide-inner">
                    <div class="slide-content">
                        <span class="badge">Pilihan Terbaik Surabaya</span>
                        <h1>Bangun Bisnis Impian Bersama Urban Office</h1>
                        <p>Ruang kerja fleksibel yang dirancang untuk mendukung produktivitas dan kreativitas bagi para profesional, pekerja lepas, serta tim kerja dari berbagai ukuran.</p>
                        <div class="hero-btns">
                            <a href="#services-grid" class="btn btn-primary">Mulai Sewa</a>
                            <a href="#contact" class="btn btn-hero-outline">Minta Penawaran</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="slide" aria-hidden="true">
                <div class="slide-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/privateoffice/private-office-small.webp');"></div>
                <div class="slide-inner">
                    <div class="slide-content">
                        <span class="badge">Virtual Office</span>
                        <h2>Virtual Office Di Lokasi Prestisius</h2>
                        <p>Dapatkan alamat bisnis legal dan prestisius mulai dari Rp 6.000-an/hari. Sudah termasuk penanganan surat menyurat dan akses ruang rapat.</p>
                        <div class="hero-btns">
                            <a href="<?php echo BASE_URL; ?>virtual-office-surabaya/" class="btn btn-primary">Lihat Paket</a>
                            <a href="#contact" class="btn btn-hero-outline">Konsultasi Gratis</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="slide" aria-hidden="true">
                <div class="slide-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/coworkingspace/Foto Coworking Space.webp');"></div>
                <div class="slide-inner">
                    <div class="slide-content">
                        <span class="badge">Layanan Bisnis Lengkap</span>
                        <h2>All-In-One Business Solution</h2>
                        <p>Pengurusan legalitas pendirian PT/CV, manajemen pembukuan akuntansi, serta pelaporan pajak terpadu dalam satu atap layanan profesional.</p>
                        <div class="hero-btns">
                            <a href="#contact" class="btn btn-primary">Konsultasi Gratis</a>
                            <a href="<?php echo BASE_URL; ?>pajak-dan-akunting/" class="btn btn-hero-outline">Pelajari Layanan</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slider Indicators (inside wrapper) -->
            <div class="slider-dots">
                <span class="dot active" onclick="goToSlide(0)"></span>
                <span class="dot" onclick="goToSlide(1)"></span>
                <span class="dot" onclick="goToSlide(2)"></span>
            </div>
        </div>
    </section>


    <!-- SEARCH BAR -->
    <section class="search-section" aria-label="Filter layanan ruang kerja">
        <div class="container">
            <div class="workspace-search-card">
                <div class="search-tabs" role="tablist" aria-label="Pilihan ruang kerja">
                    <button type="button" class="search-tab active" onclick="switchSearchTab(this)" data-category="virtual-office" data-service="Virtual Office" data-target="<?php echo BASE_URL; ?>virtual-office-surabaya/" data-placeholder="Pilih atau ketik kota/cabang Virtual Office...">
                        <span class="search-tab-icon"><i class="bi bi-building-check"></i></span>
                        <span>Virtual Office</span>
                    </button>
                    <button type="button" class="search-tab" onclick="switchSearchTab(this)" data-category="private-office" data-service="Private Office" data-target="<?php echo BASE_URL; ?>sewa-kantor-surabaya/" data-placeholder="Pilih atau ketik kota/cabang Private Office...">
                        <span class="search-tab-icon"><i class="bi bi-buildings"></i></span>
                        <span>Private Office</span>
                    </button>
                    <button type="button" class="search-tab" onclick="switchSearchTab(this)" data-category="meeting-room" data-service="Ruang Meeting" data-target="<?php echo BASE_URL; ?>meeting-room-surabaya/" data-placeholder="Pilih atau ketik kota/cabang Ruang Meeting...">
                        <span class="search-tab-icon"><i class="bi bi-people-fill"></i></span>
                        <span>Ruang Meeting</span>
                    </button>
                    <button type="button" class="search-tab" onclick="switchSearchTab(this)" data-category="coworking" data-service="Coworking Space" data-target="<?php echo BASE_URL; ?>coworking-space-urban-office/" data-placeholder="Pilih atau ketik kota/cabang Coworking Space...">
                        <span class="search-tab-icon"><i class="bi bi-laptop"></i></span>
                        <span>Coworking Space</span>
                    </button>
                    <button type="button" class="search-tab" onclick="switchSearchTab(this)" data-category="event-space" data-service="Event Space" data-target="<?php echo BASE_URL; ?>event-space-55k-perjam-urbanoffice/" data-placeholder="Pilih atau ketik kota/cabang Event Space...">
                        <span class="search-tab-icon"><i class="bi bi-calendar-event"></i></span>
                        <span>Event Space</span>
                    </button>
                    <button type="button" class="search-tab" onclick="switchSearchTab(this)" data-category="sharing-room-office" data-service="Sharing Room Office" data-target="<?php echo BASE_URL; ?>sharing-room-office/" data-placeholder="Pilih atau ketik kota/cabang Sharing Room Office...">
                        <span class="search-tab-icon"><i class="bi bi-door-open"></i></span>
                        <span>Sharing Room Office</span>
                    </button>
                </div>
                <form class="search-form" autocomplete="off">
                    <div class="search-input-wrapper">
                        <span class="search-icon-marker"><i class="bi bi-geo-alt-fill"></i></span>
                        <div class="search-combobox" id="search-combobox">
                            <input type="text" id="search-location-input" class="search-input" placeholder="Pilih atau ketik kota/cabang Virtual Office..." role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="search-location-dropdown" autocomplete="off">
                            <button type="button" class="search-combobox-caret" id="search-combobox-toggle" aria-label="Tampilkan daftar cabang tersedia" tabindex="-1">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div id="search-location-dropdown" class="search-location-dropdown" role="listbox" aria-label="Daftar cabang tersedia" hidden></div>
                        </div>
                    </div>
                    <button type="button" class="nearby-btn" onclick="setSearchNearMe(this)">
                        <i class="bi bi-crosshair"></i>
                        <span>Di Dekat Saya</span>
                    </button>
                    <button type="submit" class="search-btn">
                        <i class="bi bi-search"></i>
                        <span>Telusuri</span>
                    </button>
                </form>
                <p id="search-feedback" class="search-feedback" aria-live="polite"></p>
            </div>
        </div>
        <script>window.uoSearchCatalog = <?php echo json_encode(get_homepage_search_catalog(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;</script>
    </section>

    <!-- ABOUT SECTION -->
    <section class="section">
        <div class="container">
            <div class="about-grid">
                <!-- Left stats -->
                <div class="about-stats">
                    <div class="stat-card">
                        <div class="stat-number">10+</div>
                        <div class="stat-label">Lokasi Cabang</div>
                        <p class="stat-desc">Tersebar di wilayah-wilayah bisnis strategis Indonesia.</p>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">5000+</div>
                        <div class="stat-label">Klien Aktif</div>
                        <p class="stat-desc">Dipercaya oleh startup, UKM, hingga perusahaan korporasi global.</p>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Zonasi Perkantoran</div>
                        <p class="stat-desc">Menjamin legalitas alamat usaha untuk pendaftaran PKP.</p>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">All-In-One</div>
                        <div class="stat-label">Service Terpadu</div>
                        <p class="stat-desc">Ruang kerja, pengurusan legalitas, akuntansi, dan pajak dalam satu atap.</p>
                    </div>
                </div>
                <!-- Right text -->
                <div class="about-text">
                    <span class="badge">Mengapa Kami</span>
                    <h2>Solusi Ruang Kerja Fleksibel & Premium</h2>
                    <p>Urban Office hadir untuk mendefinisikan ulang cara kerja modern di Surabaya. Kami memfasilitasi bisnis Anda untuk terus tumbuh pesat melalui ekosistem ruang kerja bersama, kantor privat yang modern, aman, serta terkelola dengan sangat baik.</p>
                    <p>Kami tidak sekadar menyewakan ruang fisik, melainkan menjadi partner strategis operasional bisnis Anda dengan menyediakan infrastruktur legalitas pendirian usaha, administrasi resepsionis, hingga penanganan pelaporan pajak berkala.</p>
                    <a href="#contact" class="btn btn-primary" style="margin-top: 10px;">Konsultasi Gratis</a>
                </div>
            </div>
        </div>
    </section>

    <!-- APP SECTION -->
    <?php include DIR_ROOT . 'inc/components/app_section.php'; ?>

    <!-- SERVICES GRID -->
    <section class="section services-section" id="services-grid">
        <div class="container">
            <div class="text-center">
                <span class="badge">Layanan Ruang Kerja</span>
                <h2>Pilihan Layanan Terbaik Untuk Anda</h2>
                <p class="section-desc">Pilih produk ruang kerja yang paling sesuai dengan kebutuhan pertumbuhan bisnis Anda saat ini.</p>
            </div>
            
            <div class="services-grid">
                <!-- Card 1 -->
                <div class="service-card">
                    <div class="service-card-img">
                        <img src="<?php echo BASE_URL; ?>assets/images/branch/merr%20new.webp" alt="Virtual Office Surabaya, Jakarta Timur & Malang - Urban Office" width="600" height="400" loading="lazy" decoding="async">
                    </div>
                    <div class="service-card-body">
                        <div>
                            <h3>Virtual Office</h3>
                            <p>Dapatkan alamat bisnis prestisius, penanganan surat masuk, dan kuota pemakaian meeting room.</p>
                        </div>
                        <div>
                            <div class="service-price">Mulai dari<span>Rp 6.000-an / hari</span></div>
                            <a href="<?php echo BASE_URL; ?>virtual-office-surabaya/" class="service-link">Selengkapnya &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="service-card">
                    <div class="service-card-img">
                        <img src="<?php echo BASE_URL; ?>assets/images/privateoffice/offie%20301%20people.webp" alt="Private Office Siap Pakai Fully Furnished - Urban Office" width="600" height="400" loading="lazy" decoding="async">
                    </div>
                    <div class="service-card-body">
                        <div>
                            <h3>Private Office</h3>
                            <p>Ruang kantor pribadi berperabot lengkap, ber-AC, aman, dan eksklusif untuk tim Anda.</p>
                        </div>
                        <div>
                            <div class="service-price">Mulai dari<span>Rp 4.000.000 / bln</span></div>
                            <a href="<?php echo BASE_URL; ?>sewa-kantor-surabaya/" class="service-link">Selengkapnya &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="service-card">
                    <div class="service-card-img">
                        <img src="<?php echo BASE_URL; ?>assets/images/meetingroom/Meeting%2018%20pax.webp" alt="Sewa Ruang Meeting Lengkap Proyektor & WiFi - Urban Office" width="600" height="400" loading="lazy" decoding="async">
                    </div>
                    <div class="service-card-body">
                        <div>
                            <h3>Meeting Room</h3>
                            <p>Ruang rapat premium lengkap dengan proyektor, whiteboard, internet cepat, dan free drink.</p>
                        </div>
                        <div>
                            <div class="service-price">Mulai dari<span>Rp 125.000 / jam</span></div>
                            <a href="<?php echo BASE_URL; ?>meeting-room-surabaya/" class="service-link">Selengkapnya &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="service-card">
                    <div class="service-card-img">
                        <img src="<?php echo BASE_URL; ?>assets/images/eventspace/Grand%20Event%20Space.webp" alt="Sewa Event Space & Aula Seminar 30-80 Pax - Urban Office" width="600" height="400" loading="lazy" decoding="async">
                    </div>
                    <div class="service-card-body">
                        <div>
                            <h3>Event Space</h3>
                            <p>Tempat yang lapang dan representatif untuk mengadakan seminar, workshop, atau pelatihan bisnis.</p>
                        </div>
                        <div>
                            <div class="service-price">Mulai dari<span>Rp 45.000 / pax</span></div>
                            <a href="<?php echo BASE_URL; ?>event-space-55k-perjam-urbanoffice/" class="service-link">Selengkapnya &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Card 5 -->
                <div class="service-card">
                    <div class="service-card-img">
                        <img src="<?php echo BASE_URL; ?>assets/images/coworkingspace/Foto%20Coworking%20Space.webp" alt="Coworking Space Harian & Bulanan WFA - Urban Office" width="600" height="400" loading="lazy" decoding="async">
                    </div>
                    <div class="service-card-body">
                        <div>
                            <h3>Coworking Space</h3>
                            <p>Meja kerja komunal yang dinamis dengan suasana nyaman untuk meningkatkan fokus kerja harian.</p>
                        </div>
                        <div>
                            <div class="service-price">Mulai dari<span>Rp 10.000 / jam</span></div>
                            <a href="<?php echo BASE_URL; ?>coworking-space-urban-office/" class="service-link">Selengkapnya &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Card 6 -->
                <div class="service-card">
                    <div class="service-card-img">
                        <img src="<?php echo BASE_URL; ?>assets/images/sharingroom/Sharing%20Room%20Office.webp" alt="Sharing Room Office Dedicated Desk Hemat - Urban Office" width="600" height="400" loading="lazy" decoding="async">
                    </div>
                    <div class="service-card-body">
                        <div>
                            <h3>Sharing Room</h3>
                            <p>Solusi meja kerja pribadi dalam satu ruangan kantor bersama dengan harga hemat.</p>
                        </div>
                        <div>
                            <div class="service-price">Mulai dari<span>Rp 1.000.000 / bln</span></div>
                            <a href="<?php echo BASE_URL; ?>sharing-room-office/" class="service-link">Selengkapnya &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS SECTION -->
    <?php include __DIR__ . '/inc/components/branches.php'; ?>

    <!-- BLOG PREVIEW -->
    <section class="section">
        <div class="container">
            <div class="text-center">
                <span class="badge">Artikel & Edukasi</span>
                <h2>Informasi Bisnis & WFA Terkini</h2>
                <p>Ikuti tips wirausaha, tren startup, dan panduan perpajakan legalitas di Surabaya.</p>
            </div>
            
            <div class="blog-grid">
                <?php
                try {
                    // Fetch latest 3 published articles
                    $latest_posts = Database::fetchAll(
                        "SELECT title, slug, excerpt, featured_image, published_at FROM posts 
                         WHERE status = 'published' 
                         ORDER BY published_at DESC LIMIT 3"
                    );

                    if (!empty($latest_posts)):
                        foreach ($latest_posts as $post):
                            $post_image = $post['featured_image'] ? BASE_URL . $post['featured_image'] : BASE_URL . 'assets/images/og-default.png';
                            $pub_date = date('d M Y', strtotime($post['published_at']));
                ?>
                            <div class="blog-card">
                                <div class="blog-img">
                                    <img src="<?php echo $post_image; ?>" alt="<?php echo sanitize($post['title']); ?>" width="400" height="250" loading="lazy" decoding="async">
                                </div>
                                <div class="blog-body">
                                    <div>
                                        <span class="blog-category">Bisnis</span>
                                        <h3>
                                            <a href="<?php echo BASE_URL . 'blog/' . $post['slug'] . '/'; ?>">
                                                <?php echo sanitize($post['title']); ?>
                                            </a>
                                        </h3>
                                        <p><?php echo sanitize($post['excerpt'] ?: substr(strip_tags($post['content']), 0, 95) . '...'); ?></p>
                                    </div>
                                    <div class="blog-footer">
                                        <span class="blog-date"><?php echo $pub_date; ?></span>
                                        <a href="<?php echo BASE_URL . 'blog/' . $post['slug'] . '/'; ?>" class="blog-link">Baca &rarr;</a>
                                    </div>
                                </div>
                            </div>
                <?php
                        endforeach;
                    else:
                        // Display placeholder cards when no database posts are present yet
                ?>
                        <!-- Fallback Card 1 -->
                        <div class="blog-card">
                            <div class="blog-img">
                                <img src="<?php echo BASE_URL; ?>assets/images/coworkingspace/Foto%20Coworking%20Space.webp" alt="Tempat WFA Surabaya" width="400" height="250" loading="lazy" decoding="async">
                            </div>
                            <div class="blog-body">
                                <div>
                                    <span class="blog-category">Workspace</span>
                                    <h3><a href="#">Cari Tempat WFA di Surabaya? Urban Office Solusinya</a></h3>
                                    <p>Temukan coworking space strategis dengan fasilitas WFA lengkap di kawasan Merr Surabaya Timur.</p>
                                </div>
                                <div class="blog-footer">
                                    <span class="blog-date">06 Jun 2026</span>
                                    <a href="#" class="blog-link">Baca &rarr;</a>
                                </div>
                            </div>
                        </div>

                        <!-- Fallback Card 2 -->
                        <div class="blog-card">
                            <div class="blog-img">
                                <img src="<?php echo BASE_URL; ?>assets/images/privateoffice/offie%20301%20people.webp" alt="Private Office Surabaya" width="400" height="250" loading="lazy" decoding="async">
                            </div>
                            <div class="blog-body">
                                <div>
                                    <span class="blog-category">Tips Bisnis</span>
                                    <h3><a href="#">Rekomendasi Private Office & Virtual Office Surabaya</a></h3>
                                    <p>Bagaimana kiat cerdas menentukan alamat strategis untuk domisili legalitas badan hukum PT/CV Anda?</p>
                                </div>
                                <div class="blog-footer">
                                    <span class="blog-date">05 Jun 2026</span>
                                    <a href="#" class="blog-link">Baca &rarr;</a>
                                </div>
                            </div>
                        </div>

                        <!-- Fallback Card 3 -->
                        <div class="blog-card">
                            <div class="blog-img">
                                <img src="<?php echo BASE_URL; ?>assets/images/branch/merr%20new.webp" alt="Virtual Office Terbaik" width="400" height="250" loading="lazy" decoding="async">
                            </div>
                            <div class="blog-body">
                                <div>
                                    <span class="blog-category">Legalitas</span>
                                    <h3><a href="#">Virtual Office Terbaik di Surabaya Timur Free Meeting Room</a></h3>
                                    <p>Maksimalkan anggaran operasional startup Anda dengan benefit gratis pemakaian ruang meeting bulanan.</p>
                                </div>
                                <div class="blog-footer">
                                    <span class="blog-date">04 Jun 2026</span>
                                    <a href="#" class="blog-link">Baca &rarr;</a>
                                </div>
                            </div>
                        </div>
                <?php
                    endif;
                } catch (Exception $e) {
                    // Silent fallback
                }
                ?>
            </div>
        </div>
    </section>

    <!-- PARTNERSHIP CTA BANNER -->
    <section class="partnership-section">
        <div class="container">
            <h2>Kembangkan Aset Properti Anda Bersama Kami</h2>
            <p>Punya gedung kosong di Surabaya? Mari bermitra untuk mengubahnya menjadi ruang kerja produktif berpenghasilan maksimal.</p>
            <a href="https://api.whatsapp.com/send?phone=6285107620100&text=Saya%20tertarik%20dengan%20kemitraan%20Urban%20Office" target="_blank" class="btn btn-primary">Ajukan Kemitraan Properti</a>
        </div>
    </section>

    <!-- CLIENT BRANDS SLIDER SECTION -->
    <?php include __DIR__ . '/inc/components/brands_slider.php'; ?>

    <!-- FAQ SECTION -->
    <?php
    $faqs = get_homepage_faqs();
    include __DIR__ . '/inc/components/faq.php';
    ?>

    <!-- CONTACT SECTION -->
    <?php include __DIR__ . '/inc/components/contact_form.php'; ?>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
