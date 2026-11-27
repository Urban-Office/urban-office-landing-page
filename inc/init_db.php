<?php
/**
 * Database Setup & Initialization Script
 * Run this script via browser or command line to initialize tables
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

// CLI or Local Admin only validation
if (php_sapi_name() !== 'cli' && (!empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access restricted to localhost or command line.');
}

try {
    $db = Database::getConnection();
    echo "Connected successfully to database host.\n";

    // 1. Create Users Table
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        role VARCHAR(20) DEFAULT 'editor',
        status TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");
    echo "Users table verified.\n";

    // 2. Create Pages Table
    $db->exec("CREATE TABLE IF NOT EXISTS pages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(150) NOT NULL,
        slug VARCHAR(150) NOT NULL UNIQUE,
        meta_title VARCHAR(255) NULL,
        meta_description VARCHAR(255) NULL,
        canonical_url VARCHAR(255) NULL,
        og_title VARCHAR(255) NULL,
        og_description VARCHAR(255) NULL,
        og_image VARCHAR(255) NULL,
        schema_faq JSON NULL,
        audit_interval_days INT NOT NULL DEFAULT 30,
        last_audited_at DATETIME NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_page_slug (slug)
    ) ENGINE=InnoDB;");
    echo "Pages table verified.\n";

    // 3. Create Categories Table
    $db->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        slug VARCHAR(100) NOT NULL UNIQUE,
        INDEX idx_cat_slug (slug)
    ) ENGINE=InnoDB;");
    echo "Categories table verified.\n";

    // 4. Create Tags Table
    $db->exec("CREATE TABLE IF NOT EXISTS tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        slug VARCHAR(100) NOT NULL UNIQUE,
        INDEX idx_tag_slug (slug)
    ) ENGINE=InnoDB;");
    echo "Tags table verified.\n";

    // 5. Create Posts Table
    $db->exec("CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        author_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        excerpt TEXT NULL,
        content LONGTEXT NOT NULL,
        featured_image VARCHAR(255) NULL,
        meta_title VARCHAR(255) NULL,
        meta_description VARCHAR(255) NULL,
        status VARCHAR(20) DEFAULT 'draft',
        views INT DEFAULT 0,
        source_url VARCHAR(512) NULL,
        published_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_post_slug (slug),
        INDEX idx_post_status (status, published_at DESC),
        INDEX idx_post_source (source_url)
    ) ENGINE=InnoDB;");
    echo "Posts table verified.\n";

    // 5.5 Ensure views column exists on older installations
    try {
        $db->exec("ALTER TABLE posts ADD COLUMN IF NOT EXISTS views INT DEFAULT 0 AFTER status");
    } catch (Exception $e) {
        // Column already exists, safe to ignore
    }

    // 5.6 Ensure source_url column + index exist on older installations.
    // Used by the automation pipeline (_api/ingest.php) to dedup RSS topics
    // so the same source article is never turned into two posts.
    // NOTE: "ADD COLUMN IF NOT EXISTS" is a MariaDB-only extension and errors
    // on Oracle MySQL, so we probe information_schema first (portable to both).
    try {
        $hasCol = Database::fetch(
            "SELECT COUNT(*) AS c FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'source_url'",
            [DB_NAME]
        );
        if (!$hasCol || (int)$hasCol['c'] === 0) {
            $db->exec("ALTER TABLE posts ADD COLUMN source_url VARCHAR(512) NULL AFTER views");
            echo "posts.source_url column added.\n";
        } else {
            // Widen legacy 255-wide columns (RSS source URLs, esp. Google News, exceed 255).
            $len = Database::fetch(
                "SELECT CHARACTER_MAXIMUM_LENGTH AS len FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'source_url'",
                [DB_NAME]
            );
            if ($len && (int)$len['len'] < 512) {
                $db->exec("ALTER TABLE posts MODIFY source_url VARCHAR(512) NULL");
                echo "posts.source_url widened to 512.\n";
            }
        }
        $hasIdx = Database::fetch(
            "SELECT COUNT(*) AS c FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'posts' AND INDEX_NAME = 'idx_post_source'",
            [DB_NAME]
        );
        if (!$hasIdx || (int)$hasIdx['c'] === 0) {
            $db->exec("ALTER TABLE posts ADD INDEX idx_post_source (source_url)");
            echo "posts.idx_post_source index added.\n";
        }
    } catch (Exception $e) {
        echo "source_url migration skipped: " . $e->getMessage() . "\n";
    }

    // 6. Create Post Categories Join Table
    $db->exec("CREATE TABLE IF NOT EXISTS post_categories (
        post_id INT NOT NULL,
        category_id INT NOT NULL,
        PRIMARY KEY (post_id, category_id),
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    echo "Post Categories junction table verified.\n";

    // 7. Create Post Tags Join Table
    $db->exec("CREATE TABLE IF NOT EXISTS post_tags (
        post_id INT NOT NULL,
        tag_id INT NOT NULL,
        PRIMARY KEY (post_id, tag_id),
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    echo "Post Tags junction table verified.\n";

    // 8. Create Media Table
    $db->exec("CREATE TABLE IF NOT EXISTS media (
        id INT AUTO_INCREMENT PRIMARY KEY,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        file_type VARCHAR(50) NOT NULL,
        file_size INT NOT NULL,
        uploaded_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (uploaded_by) REFERENCES users(id)
    ) ENGINE=InnoDB;");
    echo "Media table verified.\n";

    // 9. Create Leads Table
    $db->exec("CREATE TABLE IF NOT EXISTS leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL,
        service VARCHAR(100) NOT NULL,
        message TEXT NULL,
        ip_address VARCHAR(45) NULL,
        status VARCHAR(20) DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");
    echo "Leads table verified.\n";

    // 10. Create Redirects Table
    $db->exec("CREATE TABLE IF NOT EXISTS redirects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        old_url VARCHAR(255) NOT NULL UNIQUE,
        new_url VARCHAR(255) NOT NULL,
        type INT DEFAULT 301,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_old_url (old_url)
    ) ENGINE=InnoDB;");
    echo "Redirects table verified.\n";

    // 11. Create Activity Logs Table
    $db->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        action VARCHAR(100) NOT NULL,
        details TEXT NULL,
        ip_address VARCHAR(45) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_activity_created (created_at),
        INDEX idx_activity_login_ip (action, ip_address, created_at),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");
    echo "Activity Logs table verified.\n";

    // Ensure indexes exist on legacy activity_logs tables (speeds up audit pagination & login rate-limit)
    try {
        foreach (['idx_activity_created' => '(created_at)', 'idx_activity_login_ip' => '(action, ip_address, created_at)'] as $idxName => $idxCols) {
            $hasIdx = Database::fetch(
                "SELECT COUNT(*) AS c FROM information_schema.STATISTICS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'activity_logs' AND INDEX_NAME = ?",
                [DB_NAME, $idxName]
            );
            if (!$hasIdx || (int)$hasIdx['c'] === 0) {
                $db->exec("ALTER TABLE activity_logs ADD INDEX {$idxName} {$idxCols}");
                echo "activity_logs.{$idxName} index added.\n";
            }
        }
    } catch (Exception $e) {
        echo "activity_logs index migration skipped: " . $e->getMessage() . "\n";
    }

    // 12. Create Settings Table
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        value TEXT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");
    echo "Settings table verified.\n";

    // 13. Create Popups Table
    $db->exec("CREATE TABLE IF NOT EXISTS popups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(150) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        image_path VARCHAR(255) NOT NULL,
        target_url TEXT NULL,
        link_type VARCHAR(20) DEFAULT 'url',
        wa_number VARCHAR(50) DEFAULT '085107620100',
        wa_message TEXT NULL,
        alt_text VARCHAR(255) NULL,
        display_target VARCHAR(50) DEFAULT 'all',
        target_pages TEXT NULL,
        frequency VARCHAR(20) DEFAULT 'daily',
        frequency_days INT DEFAULT 1,
        delay_seconds INT DEFAULT 2,
        start_date DATETIME NULL,
        end_date DATETIME NULL,
        status TINYINT DEFAULT 1,
        views_count INT DEFAULT 0,
        clicks_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_popup_status (status, start_date, end_date),
        INDEX idx_popup_slug (slug)
    ) ENGINE=InnoDB;");
    echo "Popups table verified.\n";

    // Seed Default Admin User
    $check_admin = Database::fetch("SELECT id FROM users WHERE username = 'admin'");
    if (!$check_admin) {
        $generated_password = bin2hex(random_bytes(6));
        Database::insert("INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, ?, ?)", [
            'admin',
            password_hash($generated_password, PASSWORD_DEFAULT),
            'admin@urbanoffice.co.id',
            'administrator',
            1
        ]);
        echo "Default admin user seeded (Username: admin, Password: $generated_password). Save this now — it is not stored anywhere else. Change it via /admin after your first login.\n";
    }

    // Seed Core Static Page Metadata
    $pages_seed = [
        [
            'title' => 'Sewa Virtual Office & Ruang Kantor Surabaya',
            'slug' => '',
            'meta_title' => 'Sewa Virtual Office & Ruang Kantor Surabaya - Urban Office',
            'meta_description' => 'Sewa Virtual Office Surabaya Murah. Dapatkan alamat bisnis prestisius, gratis pembuatan PT/CV, meeting room, & coworking space di Urban Office.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Virtual Office Surabaya',
            'slug' => 'virtual-office-surabaya',
            'meta_title' => 'Virtual Office Surabaya | Mulai 350Rb Perbulan - Urban Office',
            'meta_description' => 'Sewa Virtual Office Surabaya Murah ✅ Gratis Pembuatan PT ✅ Gratis Ruang Meeting Dimana saja ✅ Coworking Space ✅ Mulai 350rb.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Sewa Kantor Surabaya',
            'slug' => 'sewa-kantor-surabaya',
            'meta_title' => 'Sewa Kantor Surabaya | Sewa Ruang Kantor Murah - Urban Office',
            'meta_description' => 'Sewa private office dan coworking space di Surabaya. Fasilitas lengkap, lokasi strategis CBD, receptionist, WiFi cepat, dan fully furnished.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Meeting Room Surabaya',
            'slug' => 'meeting-room-surabaya',
            'meta_title' => 'Sewa Ruang Meeting Surabaya Murah - Urban Office',
            'meta_description' => 'Sewa meeting room di Surabaya. Dilengkapi proyektor, papan tulis, free flow drinks, internet cepat, dan staff support.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Coworking Space Surabaya',
            'slug' => 'coworking-space-urban-office',
            'meta_title' => 'Sewa Coworking Space Surabaya | Harian & Bulanan - Urban Office',
            'meta_description' => 'Temukan ruang kerja bersama di Surabaya. Cocok untuk freelancer, remote worker, dan startup. Akses WiFi cepat & free flow drinks.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Tentang Kami',
            'slug' => 'about',
            'meta_title' => 'Tentang Urban Office | Solusi Ruang Kerja & Bisnis Terpadu',
            'meta_description' => 'Pelajari visi, misi, dan nilai-nilai Urban Office sebagai penyedia Virtual Office, Coworking, dan Legalitas Usaha tepercaya di Surabaya.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Blog & Artikel Bisnis',
            'slug' => 'blog',
            'meta_title' => 'Blog & Artikel Bisnis Terbaru - Urban Office',
            'meta_description' => 'Dapatkan tips wirausaha, panduan perpajakan, legalitas pendirian PT/CV, dan tren WFA terbaru di Surabaya dari Urban Office.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Sewa Event Space',
            'slug' => 'event-space-55k-perjam-urbanoffice',
            'meta_title' => 'Sewa Event Space Surabaya | Ruang Seminar & Workshop - Urban Office',
            'meta_description' => 'Sewa Event Space Surabaya Murah. Kapasitas 30-80 Pax. Dilengkapi sound system, proyektor HD, whiteboard, dan layout fleksibel untuk seminar Anda.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Galeri Foto Fasilitas',
            'slug' => 'gallery',
            'meta_title' => 'Galeri Foto Fasilitas Kantor & Coworking - Urban Office',
            'meta_description' => 'Lihat foto-foto interior Virtual Office, Meeting Room, Private Office, dan area Coworking Space di cabang-cabang Urban Office.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Kemitraan Properti',
            'slug' => 'kemitraan-urban-office',
            'meta_title' => 'Kemitraan Properti Urban Office | Ubah Gedung Jadi Profit',
            'meta_description' => 'Mari bermitra untuk mengelola gedung kosong Anda menjadi ruang kerja bersama (Coworking/Office) berpenghasilan pasif maksimal.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Lokasi Cabang',
            'slug' => 'lokasi-urban-office',
            'meta_title' => 'Lokasi Cabang Virtual Office & Ruang Kantor - Urban Office',
            'meta_description' => 'Temukan lokasi kantor pusat dan cabang-cabang strategis Urban Office di Surabaya (MERR, Klampis, Grand Sungkono) dan kota lainnya.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Jasa Pajak & Akunting',
            'slug' => 'pajak-dan-akunting',
            'meta_title' => 'Jasa Pembukuan, Akuntansi, & Laporan Perpajakan Surabaya - Urban Office',
            'meta_description' => 'Layanan akuntansi profesional, pembuatan laporan keuangan bulanan/tahunan, SPT Badan/Pribadi, dan konsultasi pajak tepercaya di Surabaya.',
            'canonical_url' => null,
        ],
        [
            'title' => 'PT Perorangan + Virtual Office',
            'slug' => 'pendirian-perorangan-plus-virtual-office',
            'meta_title' => 'PT Perorangan + Virtual Office Bundling Murah - Urban Office',
            'meta_description' => 'Paket bundling pendirian PT Perorangan terlengkap + sewa Virtual Office dengan alamat bisnis prestisius. Dapatkan SK Kemenkumham, NIB, NPWP, & surat domisili resmi.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Pendirian PT + Virtual Office',
            'slug' => 'pendirian-pt-include-virtual-office',
            'meta_title' => 'Pendirian PT Badan Usaha + Virtual Office Murah - Urban Office',
            'meta_description' => 'Paket bundling pendirian PT Badan Usaha (Persekutuan Modal) terlengkap + sewa Virtual Office dengan alamat bisnis prestisius. Legalitas akta notaris, SK Kemenkumham, NIB, & NPWP resmi.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Pendirian CV + Virtual Office',
            'slug' => 'pendirian-cv-virtual-office',
            'meta_title' => 'Pendirian CV + Virtual Office Bundling Murah - Urban Office',
            'meta_description' => 'Paket bundling pendirian CV (Persekutuan Komanditer) terlengkap + sewa Virtual Office dengan alamat bisnis prestisius. Proses cepat dengan akta notaris, SK Kemenkumham, & NIB.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Pendirian PMA + Virtual Office',
            'slug' => 'pendirian-pma-plus-virtual-office',
            'meta_title' => 'Pendirian PT PMA (Foreign Investment) + Virtual Office - Urban Office',
            'meta_description' => 'Seamless PT PMA (Foreign Owned Enterprise) incorporation in Indonesia with premium Virtual Office address. Complete setup, notarial deed, Kemenkumham approval, and NIB.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Sewa Sharing Room Office',
            'slug' => 'sharing-room-office',
            'meta_title' => 'Sewa Sharing Room Office Surabaya | Hemat & Berperabot - Urban Office',
            'meta_description' => 'Sewa sharing office desk murah di Surabaya. Solusi ruang kerja bersama berperabot lengkap, AC, WiFi, dan resepsionis untuk tim kecil.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Karir & Lowongan Kerja',
            'slug' => 'urban-office-karir',
            'meta_title' => 'Karir & Lowongan Kerja Terbaru - Urban Office',
            'meta_description' => 'Bergabunglah dengan tim dinamis Urban Office. Temukan lowongan kerja terbaru untuk editor, legal consultant, front desk, dan sales.',
            'canonical_url' => null,
        ],
        [
            'title' => 'Berlangganan Newsletter',
            'slug' => 'newsletter',
            'meta_title' => 'Berlangganan Newsletter Info Bisnis & WFA - Urban Office',
            'meta_description' => 'Berlangganan newsletter gratis untuk mendapatkan info regulasi perpajakan terbaru, tips hukum legalitas, serta promo sewa kantor eksklusif.',
            'canonical_url' => null,
        ]
    ];

    foreach ($pages_seed as $p) {
        $check_page = Database::fetch("SELECT id FROM pages WHERE slug = ?", [$p['slug']]);
        if (!$check_page) {
            Database::insert(
                "INSERT INTO pages (title, slug, meta_title, meta_description, canonical_url) VALUES (?, ?, ?, ?, ?)",
                [$p['title'], $p['slug'], $p['meta_title'], $p['meta_description'], $p['canonical_url']]
            );
            echo "Seeded default meta for page: " . ($p['slug'] ?: 'home') . "\n";
        }
    }

    // One-time idempotent SEO & GEO page meta migration (GSC query optimization + Jakarta Timur / Malang sync)
    $migrationKey = 'seo_geo_migration_2026_11_v1';
    $hasMigrated = Database::fetch("SELECT value FROM settings WHERE name = ?", [$migrationKey]);
    if (!$hasMigrated) {
        $seo_updates = [
            ''                                         => ['Home', 'Virtual Office & Sewa Kantor Surabaya, Jakarta, Malang | Urban Office', 'Sewa Virtual Office mulai 6Rb/hari, Private Office siap pakai mulai 4 Jt/bln, Meeting Room & Coworking di Surabaya, Jakarta Timur, Malang, Gresik & Medan.'],
            'virtual-office-surabaya'                  => ['Virtual Office Surabaya', 'Virtual Office Surabaya Mulai 6Rb/Hari - Resmi PT/CV | Urban Office', 'Sewa Virtual Office Surabaya murah resmi untuk NIB & NPWP PT/CV di MERR, Klampis & Grand Sungkono. Mulai 6Rb–8Rb/hari, gratis Meeting Room & website.'],
            'sewa-kantor-surabaya'                     => ['Sewa Kantor Surabaya', 'Sewa Kantor & Private Office Surabaya Mulai 4 Jt/Bln | Urban Office', 'Sewa Private Office & Serviced Office fully furnished di MERR Surabaya mulai 4 Jt/bln (2–6 pax). WiFi fiber 100 Mbps, resepsionis, gratis listrik & IPL!'],
            'meeting-room-surabaya'                    => ['Sewa Ruang Meeting', 'Sewa Meeting Room Surabaya Mulai 125Rb/Jam & Coffee | Urban Office', 'Sewa ruang meeting Surabaya murah kapasitas 6–18 pax di MERR (Café Lt.1), Klampis & Sungkono. Mulai 125Rb/jam atau 45Rb/pax, proyektor & WiFi 100 Mbps.'],
            'coworking-space-urban-office'             => ['Coworking Space Surabaya', 'Coworking Space Surabaya Mulai 10Rb/Jam, Harian 45Rb | Urban Office', 'Cari coworking space Surabaya terdekat untuk WFA & tim? Tersedia di MERR & Klampis mulai 10Rb/jam, 45Rb/hari, atau 750Rb/bln. WiFi 100 Mbps & free kopi!'],
            'event-space-55k-perjam-urbanoffice'       => ['Sewa Event Space', 'Sewa Event Space & Aula Surabaya Mulai 45Rb/Pax | Urban Office', 'Sewa aula & event space murah di Surabaya dan Malang kapasitas 30–80 orang untuk seminar & workshop. Mulai 45Rb/pax (4 jam), lengkap proyektor HD & sound!'],
            'sharing-room-office'                      => ['Sewa Sharing Room Office', 'Sewa Sharing Office Surabaya Mulai 1 Jt/Bln Siap Kerja | Urban Office', 'Dedicated desk di ruang kantor bersama ber-AC di MERR Surabaya mulai 1 Jt/bln. Termasuk WiFi fiber 100 Mbps, resepsionis, free kopi/teh & bebas biaya IPL.'],
            'pajak-dan-akunting'                       => ['Jasa Pajak & Akunting', 'Jasa Konsultan Pajak, Pembukuan & SP2DK Resmi (NDA) | Urban Office', 'Jasa laporan pajak bulanan/tahunan SPT Badan, pembukuan, & pendampingan SP2DK ditangani tim ahli berlisensi BKP dengan jaminan kerahasiaan NDA.'],
            'pendirian-perorangan-plus-virtual-office' => ['PT Perorangan + Virtual Office', 'Pendirian PT Perorangan + Virtual Office Resmi NIB/NPWP | Urban Office', 'Paket bundling pendirian PT Perorangan cepat (±1 minggu) plus Virtual Office alamat resmi usaha. Termasuk SK Kemenkumham, NIB OSS, NPWP & domisili.'],
            'pendirian-pt-include-virtual-office'      => ['Pendirian PT + Virtual Office', 'Jasa Pendirian PT + Virtual Office Resmi NIB & NPWP | Urban Office', 'Paket lengkap pendirian PT Badan Usaha + Virtual Office resmi ±1 minggu selesai. Termasuk akta notaris, SK Kemenkumham, NIB OSS, NPWP & kuota meeting room.'],
            'pendirian-cv-virtual-office'              => ['Pendirian CV + Virtual Office', 'Jasa Pendirian CV + Virtual Office Murah & Cepat | Urban Office', 'Paket bundling pendirian CV + Virtual Office resmi untuk domisili usaha & perizinan NIB. Proses cepat ±1 minggu, lengkap akta notaris & SK Kemenkumham.'],
            'pendirian-pma-plus-virtual-office'        => ['Pendirian PMA + Virtual Office', 'Jasa Pendirian PT PMA + Virtual Office Indonesia | Urban Office', 'Solusi lengkap pendirian PT PMA (investor asing) plus alamat Virtual Office komersial resmi di Indonesia. Termasuk akta, SK Kemenkumham, NIB & NPWP.'],
            'virtual-office-surabaya-timur'            => ['Virtual Office Surabaya Timur', 'Virtual Office Klampis Surabaya Timur Mulai 6Rb/Hari | Urban Office', 'Virtual Office termurah di Ruko Klampis Megah Surabaya Timur dekat ITS & UNAIR mulai 6Rb/hari. Alamat bisnis resmi PT/CV, NIB & NPWP plus notifikasi WA.'],
            'virtual-office-surabaya-barat'            => ['Virtual Office Surabaya Barat', 'Virtual Office Surabaya Barat Grand Sungkono 12Rb/Hari | Urban Office', 'Sewa Virtual Office prestisius di Grand Sungkono Lagoon Surabaya Barat dekat Tol Satelit mulai 12Rb/hari. Lengkap resepsionis, domisili resmi & meeting room.'],
            'virtual-office-jakarta-timur'             => ['Virtual Office Jakarta Timur', 'Sewa Virtual Office Jakarta Timur Cakung Mulai 12Rb/Hari | Urban Office', 'Sewa Virtual Office resmi Jakarta Timur (Jl. Raya Bekasi KM 17 Cakung dekat Pulogadung) mulai 12Rb/hari. Alamat legal PT/CV, nomor 021 & gratis meeting room.'],
            'virtual-office-gresik'                    => ['Virtual Office Gresik', 'Sewa Virtual Office Gresik PTGM Tower Mulai 12Rb/Hari | Urban Office', 'Sewa Virtual Office di PTGM Tower Kebomas Gresik mulai 12Rb/hari. Alamat bisnis komersial resmi untuk PT/CV, dekat Pemkab Gresik & akses Tol Romokalisari.'],
            'virtual-office-medan'                     => ['Virtual Office Medan', 'Sewa Virtual Office Medan Jl. Sutrisno Mulai 12Rb/Hari | Urban Office', 'Sewa Virtual Office strategis di Medan Area (Jl. Sutrisno) mulai 12Rb/hari. Lengkap domisili usaha resmi PT/CV, resepsionis, notifikasi surat & meeting room.'],
            'virtual-office-malang'                    => ['Virtual Office Malang', 'Sewa Virtual Office Malang Blimbing Mulai 12Rb/Hari | Urban Office', 'Sewa Virtual Office resmi di Blimbing Kota Malang mulai 12Rb/hari. Alamat bisnis legal untuk PT/CV & NIB, penanganan surat WA real-time & kuota meeting room.'],
            'sewa-kantor-jakarta'                      => ['Sewa Kantor Jakarta Timur', 'Sewa Kantor & Private Office Jakarta Timur Siap Pakai | Urban Office', 'Sewa Private Office & Serviced Office fully furnished di Cakung, Pulogadung, Jakarta Timur mulai 4 Jt/bln. WiFi fiber, resepsionis, bebas biaya listrik & IPL.'],
            'sewa-kantor-gresik'                       => ['Sewa Kantor Gresik', 'Sewa Kantor & Private Office Gresik PTGM Tower | Urban Office', 'Sewa ruang kantor privat fully furnished di PTGM Tower Kebomas Gresik mulai 4 Jt/bln. Lengkap WiFi cepat, AC, resepsionis & bebas biaya utilitas kantor.'],
            'sewa-kantor-malang'                       => ['Sewa Kantor Malang', 'Sewa Kantor & Private Office Malang Blimbing Siap Pakai | Urban Office', 'Sewa Private Office fully furnished di Blimbing Indah Megah Kota Malang mulai 4 Jt/bln (2–6 pax). WiFi fiber 100 Mbps, resepsionis & gratis ruang meeting!'],
            'meeting-room-surabaya-barat'              => ['Meeting Room Surabaya Barat', 'Sewa Meeting Room Surabaya Barat Mulai 125Rb/Jam | Urban Office', 'Sewa ruang rapat di Grand Sungkono Lagoon Surabaya Barat mulai 125Rb/jam (6–15 pax). Dekat Tol Satelit, lengkap proyektor/TV LED, whiteboard & coffee break.'],
            'meeting-room-jakarta'                     => ['Meeting Room Jakarta Timur', 'Sewa Meeting Room Jakarta Timur Mulai 125Rb/Jam - Cakung | Urban Office', 'Sewa ruang meeting siap pakai di Cakung, Pulogadung, Jakarta Timur mulai 125Rb/jam atau 45Rb/pax. Lengkap Smart TV LED, WiFi cepat, whiteboard & snack.'],
            'meeting-room-gresik'                      => ['Meeting Room Gresik', 'Sewa Meeting Room Gresik PTGM Tower Mulai 125Rb/Jam | Urban Office', 'Sewa ruang rapat & seminar di PTGM Tower Kebomas Gresik mulai 125Rb/jam atau 45Rb/pax. Lengkap proyektor HD, Smart TV, WiFi cepat & opsi coffee break.'],
            'meeting-room-malang'                      => ['Meeting Room Malang', 'Sewa Meeting Room Malang Blimbing Mulai 125Rb/Jam | Urban Office', 'Sewa ruang meeting di Blimbing Kota Malang per jam (125Rb/jam) atau paket 4–8 jam. Lengkap proyektor/TV LED, WiFi cepat, whiteboard & opsi coffee break.'],
            'coworking-space-surabaya-timur'           => ['Coworking Space Surabaya Timur', 'Coworking Space Klampis Surabaya Timur Mulai 45Rb/Hari | Urban Office', 'Kerja fokus di coworking space Klampis Surabaya Timur dekat ITS & UNAIR mulai 45Rb/hari atau 750Rb/bln. Meja ergonomis, WiFi 100 Mbps & free flow kopi/teh.'],
            'coworking-space-malang'                   => ['Coworking Space Malang', 'Coworking Space Malang Mulai 10Rb/Jam & 45Rb/Hari | Urban Office', 'Cari tempat WFA di Blimbing Kota Malang? Sewa Coworking Space mulai 10Rb/jam, 45Rb/hari, atau 750Rb/bln. WiFi fiber 100 Mbps & free kopi/teh sepuasnya!'],
            'event-space-malang'                       => ['Event Space Malang', 'Sewa Event Space & Aula Malang Mulai 45Rb/Pax (30–80 Org) | Urban Office', 'Sewa aula & event space di Blimbing Kota Malang kapasitas 30–80 orang mulai 45Rb/pax (Half-Day). Lengkap proyektor HD, sound system, wireless mic & layout bebas.'],
        ];

        foreach ($seo_updates as $up_slug => [$up_title, $up_mt, $up_md]) {
            $exists = Database::fetch("SELECT id FROM pages WHERE slug = ?", [$up_slug]);
            if ($exists) {
                Database::query(
                    "UPDATE pages SET meta_title = ?, meta_description = ?, og_title = ?, og_description = ? WHERE slug = ?",
                    [$up_mt, $up_md, $up_mt, $up_md, $up_slug]
                );
            } else {
                Database::insert(
                    "INSERT INTO pages (title, slug, meta_title, meta_description, og_title, og_description) VALUES (?, ?, ?, ?, ?, ?)",
                    [$up_title, $up_slug, $up_mt, $up_md, $up_mt, $up_md]
                );
            }
        }
        Database::query("DELETE FROM pages WHERE slug IN ('coworking-space-jakarta', 'event-space-jakarta')");
        Database::query("INSERT INTO settings (name, value) VALUES (?, ?)", [$migrationKey, date('Y-m-d H:i:s')]);
        echo "Applied SEO/GEO meta migration ($migrationKey) for " . count($seo_updates) . " pages.\n";
    }

    // 14. First-Party Analytics Events table (permanent storage, indexed for fast reporting)
    $db->exec("CREATE TABLE IF NOT EXISTS analytics_events (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        session_hash VARCHAR(64) NOT NULL,
        event_name VARCHAR(50) NOT NULL,
        traffic_channel VARCHAR(32) NOT NULL DEFAULT 'direct',
        traffic_source VARCHAR(100) NOT NULL DEFAULT 'direct',
        traffic_medium VARCHAR(64) NOT NULL DEFAULT 'none',
        traffic_campaign VARCHAR(150) DEFAULT NULL,
        page_path VARCHAR(255) NOT NULL DEFAULT '/',
        landing_page VARCHAR(255) DEFAULT NULL,
        service_name VARCHAR(100) DEFAULT NULL,
        branch_slug VARCHAR(80) DEFAULT NULL,
        wa_channel VARCHAR(20) DEFAULT NULL,
        event_detail VARCHAR(255) DEFAULT NULL,
        device_type VARCHAR(16) NOT NULL DEFAULT 'desktop',
        ip_address VARCHAR(45) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ae_created_event (created_at, event_name),
        INDEX idx_ae_channel_created (traffic_channel, created_at),
        INDEX idx_ae_page_created (page_path(120), created_at),
        INDEX idx_ae_session_created (session_hash, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Verified table: analytics_events\n";

    // 15. Page SEO Audit & Revision History (Next Audit schedule + Before/After tracking)
    try {
        foreach ([
            'audit_interval_days' => 'INT NOT NULL DEFAULT 30 AFTER schema_faq',
            'last_audited_at'     => 'DATETIME NULL AFTER audit_interval_days',
        ] as $colName => $colDef) {
            $hasCol = Database::fetch(
                "SELECT COUNT(*) AS c FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'pages' AND COLUMN_NAME = ?",
                [DB_NAME, $colName]
            );
            if (!$hasCol || (int)$hasCol['c'] === 0) {
                $db->exec("ALTER TABLE pages ADD COLUMN {$colName} {$colDef}");
                echo "pages.{$colName} column added.\n";
            }
        }
    } catch (Exception $e) {
        echo "pages audit column migration skipped: " . $e->getMessage() . "\n";
    }

    $db->exec("CREATE TABLE IF NOT EXISTS page_seo_audits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page_id INT NOT NULL,
        event_type VARCHAR(20) NOT NULL DEFAULT 'audit_check',
        user_id INT NULL,
        prev_meta_title VARCHAR(255) NULL,
        prev_meta_description VARCHAR(255) NULL,
        new_meta_title VARCHAR(255) NULL,
        new_meta_description VARCHAR(255) NULL,
        gsc_period_days INT NOT NULL DEFAULT 28,
        gsc_impressions INT NULL,
        gsc_clicks INT NULL,
        gsc_ctr DECIMAL(5,2) NULL,
        gsc_position DECIMAL(6,2) NULL,
        fp_organic_views INT NOT NULL DEFAULT 0,
        fp_geo_views INT NOT NULL DEFAULT 0,
        fp_total_views INT NOT NULL DEFAULT 0,
        fp_conversions INT NOT NULL DEFAULT 0,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_psa_page_created (page_id, created_at DESC),
        FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Verified table: page_seo_audits\n";

    echo "Database initialization completed successfully.\n";

} catch (Exception $e) {
    die("Setup failed: " . $e->getMessage() . "\n");
}
