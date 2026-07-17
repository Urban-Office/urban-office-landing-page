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

    // Virtual Office branch landing pages: /virtual-office-{slug}/
    // (virtual-office-surabaya/ itself is a real folder and never reaches this
    // fallback, since Nginx finds it directly before falling back to index.php)
    if ($parts[0] !== 'virtual-office-surabaya' && strpos($parts[0], 'virtual-office-') === 0) {
        $_GET['branch'] = substr($parts[0], strlen('virtual-office-'));
        require_once __DIR__ . '/virtual-office-surabaya/index.php';
        exit;
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
                        <h1>Bangun Bisnis Impian Anda Bersama Urban Office</h1>
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
                        <p>Dapatkan alamat bisnis legal dan prestisius mulai dari Rp 290.000/bulan. Sudah termasuk penanganan surat menyurat dan akses ruang rapat.</p>
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
                    <button type="button" class="search-tab active" onclick="switchSearchTab(this)" data-service="Virtual Office" data-target="<?php echo BASE_URL; ?>virtual-office-surabaya/" data-placeholder="Masukkan kota untuk alamat Virtual Office">
                        <span class="search-tab-icon"><i class="bi bi-building-check"></i></span>
                        <span>Virtual Office</span>
                    </button>
                    <button type="button" class="search-tab" onclick="switchSearchTab(this)" data-service="Private Office" data-target="<?php echo BASE_URL; ?>sewa-kantor-surabaya/" data-placeholder="Masukkan kota untuk Private Office">
                        <span class="search-tab-icon"><i class="bi bi-buildings"></i></span>
                        <span>Private Office</span>
                    </button>
                    <button type="button" class="search-tab" onclick="switchSearchTab(this)" data-service="Ruang Meeting" data-target="<?php echo BASE_URL; ?>meeting-room-surabaya/" data-placeholder="Masukkan kota untuk Ruang Meeting">
                        <span class="search-tab-icon"><i class="bi bi-people-fill"></i></span>
                        <span>Ruang Meeting</span>
                    </button>
                    <button type="button" class="search-tab" onclick="switchSearchTab(this)" data-service="Coworking Space" data-target="<?php echo BASE_URL; ?>coworking-space-urban-office/" data-placeholder="Masukkan kota untuk Coworking Space">
                        <span class="search-tab-icon"><i class="bi bi-laptop"></i></span>
                        <span>Coworking Space</span>
                    </button>
                    <button type="button" class="search-tab" onclick="switchSearchTab(this)" data-service="Event Space" data-target="<?php echo BASE_URL; ?>event-space-55k-perjam-urbanoffice/" data-placeholder="Masukkan kota untuk Event Space">
                        <span class="search-tab-icon"><i class="bi bi-calendar-event"></i></span>
                        <span>Event Space</span>
                    </button>
                    <button type="button" class="search-tab" onclick="switchSearchTab(this)" data-service="Sharing Room Office" data-target="<?php echo BASE_URL; ?>sharing-room-office/" data-placeholder="Masukkan kota untuk Sharing Room Office">
                        <span class="search-tab-icon"><i class="bi bi-door-open"></i></span>
                        <span>Sharing Room Office</span>
                    </button>
                </div>
                <form class="search-form" autocomplete="off">
                    <label class="search-input-wrapper" for="search-location-input">
                        <span class="search-icon-marker"><i class="bi bi-geo-alt-fill"></i></span>
                        <input type="text" id="search-location-input" class="search-input" placeholder="Masukkan kota, contoh: Surabaya" autocomplete="address-level2">
                    </label>
                    <button type="button" class="nearby-btn" onclick="setSearchNearMe(this)">
                        <i class="bi bi-crosshair"></i>
                        <span>Di Dekat Saya</span>
                    </button>
                    <button type="submit" class="search-btn">
                        <i class="bi bi-search"></i>
                        <span>Telusuri</span>
                    </button>
                </form>
            </div>
        </div>
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
                        <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=600&q=80" alt="Virtual Office">
                    </div>
                    <div class="service-card-body">
                        <div>
                            <h3>Virtual Office</h3>
                            <p>Dapatkan alamat bisnis prestisius, penanganan surat masuk, dan kuota pemakaian meeting room.</p>
                        </div>
                        <div>
                            <div class="service-price">Mulai dari<span>Rp 290.000 / bln</span></div>
                            <a href="<?php echo BASE_URL; ?>virtual-office-surabaya/" class="service-link">Selengkapnya &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="service-card">
                    <div class="service-card-img">
                        <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=600&q=80" alt="Private Office">
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
                        <img src="https://images.unsplash.com/photo-1431540015161-0bf868a2d407?auto=format&fit=crop&w=600&q=80" alt="Meeting Room">
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
                        <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=600&q=80" alt="Event Space">
                    </div>
                    <div class="service-card-body">
                        <div>
                            <h3>Event Space</h3>
                            <p>Tempat yang lapang dan representatif untuk mengadakan seminar, workshop, atau pelatihan bisnis.</p>
                        </div>
                        <div>
                            <div class="service-price">Mulai dari<span>Rp 55.000 / pax</span></div>
                            <a href="<?php echo BASE_URL; ?>event-space-55k-perjam-urbanoffice/" class="service-link">Selengkapnya &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Card 5 -->
                <div class="service-card">
                    <div class="service-card-img">
                        <img src="https://images.unsplash.com/photo-1527192491265-7e15c55b1ed2?auto=format&fit=crop&w=600&q=80" alt="Coworking Space">
                    </div>
                    <div class="service-card-body">
                        <div>
                            <h3>Coworking Space</h3>
                            <p>Meja kerja komunal yang dinamis dengan suasana nyaman untuk meningkatkan fokus kerja harian.</p>
                        </div>
                        <div>
                            <div class="service-price">Mulai dari<span>Rp 45.000 / hari</span></div>
                            <a href="<?php echo BASE_URL; ?>coworking-space-urban-office/" class="service-link">Selengkapnya &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Card 6 -->
                <div class="service-card">
                    <div class="service-card-img">
                        <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=600&q=80" alt="Sharing Room">
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
                                    <img src="<?php echo $post_image; ?>" alt="<?php echo sanitize($post['title']); ?>">
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
                                <img src="<?php echo BASE_URL; ?>assets/images/og-default.png" alt="Tempat WFA Surabaya">
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
                                <img src="<?php echo BASE_URL; ?>assets/images/og-default.png" alt="Private Office Surabaya">
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
                                <img src="<?php echo BASE_URL; ?>assets/images/og-default.png" alt="Virtual Office Terbaik">
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
    $faqs = [
        [
            'question' => 'Di mana saja lokasi Urban Office?',
            'answer' => 'Urban Office saat ini tersedia di Fatmawati Jakarta, Merr Surabaya, Klampis Surabaya, Grand Sungkono Lagoon Surabaya, Gresik, dan Medan.'
        ],
        [
            'question' => 'Bagaimana jam operasional Urban Office?',
            'answer' => 'Jam operasional seluruh cabang adalah Senin - Sabtu pukul 08:00 - 15:00 WIB, khusus untuk cabang Urban Office - MERR dan Urban Office - Klampis beroperasi pukul 08:00 - 17:00 WIB.'
        ],
        [
            'question' => 'Apakah tersedia parkir?',
            'answer' => 'Ya, tempat parkir tersedia dan gratis (free).'
        ],
        [
            'question' => 'Apakah lokasi strategis / mudah dijangkau?',
            'answer' => 'Ya, seluruh lokasi Urban Office berada di titik strategis dan sangat mudah dijangkau.'
        ],
        [
            'question' => 'Metode pembayaran apa saja yang diterima?',
            'answer' => 'Pembayaran bisa langsung melalui portal my.urbanoffice.id yang mendukung Virtual Account berbagai bank (Mandiri, BNI, BRI, Permata Bank, CIMB Niaga, BSI), QRIS, serta Gopay.'
        ],
        [
            'question' => 'Bagaimana cara mendapatkan invoice / kwitansi?',
            'answer' => 'Anda dapat langsung mengakses dan mengunduh invoice serta kwitansi pembayaran di portal pelanggan my.urbanoffice.id.'
        ],
        [
            'question' => 'Apakah ada promo atau diskon yang sedang berjalan?',
            'answer' => 'Setiap informasi promo atau diskon terbaru akan kami sampaikan secara berkala, atau Anda bisa langsung memantaunya melalui portal resmi my.urbanoffice.id.'
        ],
        [
            'question' => 'Bagaimana cara komplain jika ada masalah dengan layanan?',
            'answer' => 'Anda bisa langsung datang ke cabang Urban Office tempat Anda melakukan transaksi atau menghubungi kami melalui nomor WhatsApp resmi.'
        ],
        [
            'question' => 'Apakah ada customer service yang bisa dihubungi di luar jam kerja?',
            'answer' => 'Ya, Anda dapat menghubungi tim customer service kami di luar jam kerja melalui WhatsApp di nomor 0851-0762-0100.'
        ]
    ];
    include __DIR__ . '/inc/components/faq.php';
    ?>

    <!-- CONTACT SECTION -->
    <?php include __DIR__ . '/inc/components/contact_form.php'; ?>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
