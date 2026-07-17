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
        published_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_post_slug (slug),
        INDEX idx_post_status (status, published_at DESC)
    ) ENGINE=InnoDB;");
    echo "Posts table verified.\n";

    // 5.5 Ensure views column exists on older installations
    try {
        $db->exec("ALTER TABLE posts ADD COLUMN IF NOT EXISTS views INT DEFAULT 0 AFTER status");
    } catch (Exception $e) {
        // Column already exists, safe to ignore
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
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");
    echo "Activity Logs table verified.\n";

    // 12. Create Settings Table
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        value TEXT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");
    echo "Settings table verified.\n";

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

    echo "Database initialization completed successfully.\n";

} catch (Exception $e) {
    die("Setup failed: " . $e->getMessage() . "\n");
}
