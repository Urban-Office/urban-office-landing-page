<?php
/**
 * Urban Office - Centralized Locations Data Store
 * Provides rich metadata, gallery, pricing packages, FAQs, and testimonials for all branches.
 * Aligned with official pricing:
 * - VO Starter: 385.000 / bln, Luxury: 620.000 / bln, Priority: 770.000 / bln
 * - Coworking: Harian 45.000 / hari, Bulanan 750.000 / bln
 * - Sharing Room: 1.000.000 / bln
 * - Meeting Room: 125.000 / jam
 * - Event Space: 45.000 (4 jam), 90.000 (8 jam)
 * - Private Office: Negotiable
 */

// Prevent direct access
if (basename($_SERVER['SCRIPT_FILENAME']) === 'locations_data.php') {
    header('HTTP/1.1 403 Forbidden');
    exit('Direct access forbidden.');
}

$locations_db = [
    'surabaya' => [
        'slug' => 'surabaya',
        'title' => 'Urban Office - MERR (Surabaya Timur)',
        // Real Google Maps reviews (verbatim). Shown with the Google badge because the source IS Google.
        'google_reviews' => [
            ['name' => 'Rahmi Izzah Fatihiyah', 'rating' => 5, 'text' => 'Beberapa kali pakai event space di sini, pelayanannya ramah, bersih, nyaman, dan harga terjangkau. Package-nya lengkap dengan coffee break dan lunch.'],
            ['name' => 'kang kewok1922', 'rating' => 5, 'text' => 'Kantor yang nyaman dan asik, cocok buat yang dinas dari luar kota dan butuh kantor untuk meeting. Harga terjangkau, parkir gratis mobil atau motor.'],
            ['name' => 'Marta Yudha', 'rating' => 5, 'text' => 'Sewa meeting room di sini sudah ketiga kalinya. Proses booking cepat, admin ramah, dan ruangan nyaman, harga oke.'],
            ['name' => 'fara diba', 'rating' => 5, 'text' => 'Tempatnya cozy sekali, coworking di sini waktu zoom webinar bareng teman. Menu cafenya juga ramah di kantong.'],
            ['name' => 'Ayu Masyithoh', 'rating' => 5, 'text' => 'Temboknya penuh desain yang instagramable. Tempatnya cukup luas, banyak colokan, pencahayaan cukup, dan parkir cukup untuk mobil dan motor.'],
        ],
        'short_title' => 'MERR',
        'city' => 'Surabaya',
        'location' => 'Surabaya Timur',
        'address' => 'Jl. Dr. Ir. H. Soekarno No.470, Kedung Baruk, Kec. Rungkut, Surabaya, Jawa Timur 60298',
        'kpp' => 'KPP Rungkut',
        'rating' => '4.9',
        'reviews_count' => '156',
        'image' => BASE_URL . 'assets/images/branch/merr new.png',
        'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3957.348638974577!2d112.78023107476097!3d-7.318892692689255!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7fa535f29910d%3A0x8bbd360efbe36368!2sUrban%20Office%20Surabaya!5e0!3m2!1sid!2sid!4v1718000000000!5m2!1sid!2sid',
        'map_link' => 'https://maps.app.goo.gl/nACcB9LqEPn27REY9',
        'services' => 'VO, Serviced Office, Meeting Room, Coworking, Event Space, Sharing Room Office',
        'advantages' => [
            'Lokasi Super Strategis di jalan utama MERR (CBD Timur Surabaya)',
            'Area parkir luas, aman, dengan keamanan 24 jam & CCTV',
            'Layanan Resepsionis Profesional & Penanganan Surat/Dokumen',
            'Koneksi Internet High-Speed Dedicated (Up to 100 Mbps)',
            'Free Flow Drinks (Kopi Khas, Teh, & Air Mineral)',
            'Lounge Area & Pantry Modern untuk bersantai'
        ],
        'gallery' => [
            BASE_URL . 'assets/images/privateoffice/resepsionis.jpeg',
            BASE_URL . 'assets/images/coworkingspace/Foto Coworking Space.jpeg',
            BASE_URL . 'assets/images/privateoffice/offie 301 people.png',
            BASE_URL . 'assets/images/meetingroom/Meeting 18 pax.png',
        ],
        'seo' => [
            'title' => 'Sewa Virtual Office & Private Office MERR Surabaya Timur - Urban Office',
            'description' => 'Sewa Virtual Office & Private Office murah di MERR Surabaya Timur. Fasilitas lengkap: Alamat Kantor Prestisius, Meeting Room, Internet Cepat, & Free Flow Drinks.',
            'keywords' => 'virtual office merr, sewa kantor surabaya timur, coworking space merr, meeting room rungkut'
        ],
        'pricing' => [
            [
                'category' => 'virtual-office',
                'name' => 'Virtual Office Starter',
                'price' => '385.000',
                'period' => 'bulan',
                'desc' => 'Paket hemat alamat kantor prestisius di gedung komersial.',
                'features' => [
                    'Alamat Bisnis Prestisius & Strategis',
                    'Penanganan Surat & Paket Masuk',
                    'Notifikasi Surat Masuk via Email/WA',
                    'Hak Penggunaan Alamat Kantor',
                    'Resepsionis Profesional',
                    'Akses Meeting Room 2x 2 Jam / Bulan'
                ],
                'cta_wa' => 'Halo Urban Office, saya ingin memesan Virtual Office Starter di Cabang MERR.'
            ],
            [
                'category' => 'virtual-office',
                'name' => 'Virtual Office Luxury',
                'price' => '620.000',
                'period' => 'bulan',
                'desc' => 'Sempurna untuk startup berkembang yang butuh fasilitas ruang rapat lebih banyak.',
                'features' => [
                    'Semua Layanan Paket Starter',
                    'Akses Meeting Room 2x 3 Jam / Bulan',
                    'Nomor Telepon Kantor Bersama (Shared Line)',
                    'Layanan Resepsionis untuk Penerimaan Tamu',
                    'Hak Penggunaan Alamat Kantor'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik dengan Virtual Office Luxury di Cabang MERR.'
            ],
            [
                'category' => 'virtual-office',
                'name' => 'Virtual Office Priority',
                'price' => '770.000',
                'period' => 'bulan',
                'desc' => 'Layanan premium lengkap dengan bonus layanan pendukung bisnis pilihan.',
                'features' => [
                    'Semua Layanan Paket Luxury',
                    'Akses Meeting Room 2x 4 Jam / Bulan',
                    'Nomor Telepon Kantor Bersama',
                    'Pilihan Layanan Pendukung Bisnis (Digital Marketing / Kirim Dokumen / Virtual Assistant)'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik dengan Virtual Office Priority di Cabang MERR.'
            ],
            [
                'category' => 'coworking',
                'name' => 'Hot Desk Harian',
                'price' => '45.000',
                'period' => 'pax/hari',
                'desc' => 'Ruang kerja fleksibel di area open space yang dinamis. Cocok untuk freelancer.',
                'features' => [
                    'Akses Fleksibel ke Open Workspace',
                    'High-Speed Wi-Fi Dedicated',
                    'Free Flow Coffee, Tea & Mineral Water',
                    'Akses ke Collab Zone & Lounge',
                    'Tersedia Colokan Listrik di Setiap Meja'
                ],
                'cta_wa' => 'Halo Urban Office, saya mau booking Hot Desk harian di Cabang MERR.'
            ],
            [
                'category' => 'coworking',
                'name' => 'Coworking Bulanan',
                'price' => '750.000',
                'period' => 'pax/bulan',
                'desc' => 'Akses kerja tanpa batas selama sebulan penuh dengan fasilitas lengkap.',
                'features' => [
                    'Akses Bulanan Penuh (Open Area)',
                    'High-Speed Wi-Fi Dedicated',
                    'Free Flow Coffee, Tea & Mineral Water',
                    'Akses Lounge & Ruang Kerja Bersama',
                    'Layanan Resepsionis Penerimaan Surat'
                ],
                'cta_wa' => 'Halo Urban Office, saya mau sewa Coworking Bulanan di Cabang MERR.'
            ],
            [
                'category' => 'private-office',
                'name' => 'Private Serviced Office',
                'price' => 'Negotiable',
                'period' => 'bulan',
                'desc' => 'Ruang kantor pribadi berfurnitur lengkap (furnished) untuk tim kecil hingga menengah.',
                'features' => [
                    'Ruangan Terkunci Mandiri & Private (Kapasitas 2-6 Orang)',
                    'Meja & Kursi Kerja Ergonomis Lengkap',
                    'Gratis Akses Wi-Fi Dedicated & LAN',
                    'Layanan Pembersihan Harian (Cleaning Service)',
                    'Bebas Biaya Utilitas (Listrik/AC/Air)',
                    'Free Kuota Penggunaan Meeting Room'
                ],
                'cta_wa' => 'Halo Urban Office, saya ingin survey Private Serviced Office di Cabang MERR.'
            ],
            [
                'category' => 'meeting-room',
                'name' => 'Small Meeting Room',
                'price' => '125.000',
                'period' => 'jam',
                'desc' => 'Ruangan rapat formal ber-AC dengan perlengkapan multimedia lengkap untuk presentasi.',
                'features' => [
                    'Kapasitas hingga 6 - 8 Orang',
                    'Smart TV LED HD / Proyektor',
                    'Whiteboard & Spidol Lengkap',
                    'Free Flow Drinks untuk Seluruh Peserta',
                    'High-Speed Wi-Fi'
                ],
                'cta_wa' => 'Halo Urban Office, saya mau sewa Meeting Room di Cabang MERR.'
            ],
            [
                'category' => 'event-space',
                'name' => 'Event Space (Half-Day)',
                'price' => '45.000',
                'period' => 'pax/4 jam',
                'desc' => 'Area luas dan fleksibel untuk seminar, workshop, talkshow, maupun gathering selama 4 jam.',
                'features' => [
                    'Kapasitas hingga 30 - 50 Orang',
                    'Sound System Standard & Wireless Mic',
                    'Proyektor Layar Besar',
                    'Layout Kursi Fleksibel',
                    'Fasilitas Penerimaan Tamu (Registrasi)'
                ],
                'cta_wa' => 'Halo Urban Office, saya ingin menanyakan sewa Event Space Half-Day di Cabang MERR.'
            ],
            [
                'category' => 'event-space',
                'name' => 'Event Space (Full-Day)',
                'price' => '90.000',
                'period' => 'pax/8 jam',
                'desc' => 'Sewa ruang serbaguna full day selama 8 jam untuk seminar korporat skala besar.',
                'features' => [
                    'Kapasitas hingga 30 - 50 Orang',
                    'Sound System Standard & Wireless Mic',
                    'Proyektor Layar Besar',
                    'Layout Kursi Fleksibel',
                    'Pantry & Lounge Support'
                ],
                'cta_wa' => 'Halo Urban Office, saya ingin menanyakan sewa Event Space Full-Day di Cabang MERR.'
            ],
            [
                'category' => 'sharing-room-office',
                'name' => 'Sharing Room Office Desk',
                'price' => '1.000.000',
                'period' => 'pax/bulan',
                'desc' => 'Meja kerja khusus dalam ruang kantor bersama yang tenang dengan harga hemat.',
                'features' => [
                    'Meja Kerja Khusus (Dedicated Desk)',
                    'Internet Wi-Fi Cepat',
                    'Layanan Penerimaan Resepsionis',
                    'Bebas Biaya IPL, Listrik, & Air',
                    'Free Flow Beverages (Teh/Kopi)'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik menyewa Sharing Room Office di Cabang MERR.'
            ]
        ],
        'faq' => [
            [
                'question' => 'Apakah lokasi MERR memiliki area parkir yang memadai?',
                'answer' => 'Ya, cabang MERR memiliki area parkir motor dan mobil yang luas dan aman di depan gedung dengan penjagaan security 24 jam.'
            ],
            [
                'question' => 'Jam berapa operasional cabang MERR?',
                'answer' => 'Operasional resepsionis dan layanan Virtual Office aktif Senin-Jumat jam 08.00 - 17.00. Untuk member Private Office memiliki akses khusus sesuai kesepakatan kontrak.'
            ],
            [
                'question' => 'Apakah harga sewa sudah termasuk pajak (PPN)?',
                'answer' => 'Harga yang tercantum belum termasuk PPN 11%, namun sudah bebas biaya IPL, air, listrik, dan kebersihan untuk kategori Serviced Office.'
            ]
        ],
        'testimonial' => [
            'name' => 'Rian Kurniawan',
            'role' => 'CEO Tech Startup Indonesia',
            'text' => 'Menggunakan Serviced Office di cabang MERR sangat mendongkrak produktivitas tim kami. Lokasinya dekat pintu tol, internetnya super stabil, dan kopi gratisnya sangat membantu fokus kerja!',
            'rating' => 5,
            'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80'
        ]
    ],
    'surabaya-timur' => [
        'slug' => 'surabaya-timur',
        'title' => 'Urban Office - Klampis (Surabaya Timur)',
        // Real Google Maps reviews (verbatim). Shown with the Google badge because the source IS Google.
        'google_reviews' => [
            ['name' => 'Salim Sholahudin', 'rating' => 5, 'text' => 'Meeting room pilihan di Surabaya, dekat dengan pusat kota, jadi tidak jauh-jauh kalau mau balik ke sini lagi.'],
            ['name' => 'Ninna Rohali', 'rating' => 5, 'text' => 'Tempatnya nyaman banget buat working, rekomended sih ini. Bikin makin semangat kerja.'],
            ['name' => 'Dad HHans', 'rating' => 5, 'text' => 'Salah satu pilihan kalau butuh virtual office dan meeting mendadak di sekitar Klampis Jaya.'],
            ['name' => 'Ayu Agustiningsih', 'rating' => 5, 'text' => 'Akhirnya nemu juga tempat kerja fleksibel, gak ribet, cozy dan ramah di kantong.'],
            ['name' => 'anisa nurbaiti rahman', 'rating' => 5, 'text' => 'Meeting di sini nyaman banget, ruangannya oke.'],
        ],
        'short_title' => 'Klampis',
        'city' => 'Surabaya',
        'location' => 'Surabaya Timur',
        'address' => 'Ruko Klampis Megah, Jl. Klampis Jaya blok B-20, Klampis Ngasem, Kec. Sukolilo, Surabaya, Jawa Timur 60117',
        'kpp' => 'KPP Gubeng',
        'rating' => '4.8',
        'reviews_count' => '112',
        'image' => BASE_URL . 'assets/images/branch/klampis.png',
        'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3957.6974712411874!2d112.77494497476063!3d-7.2752175927318725!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7fa2032e5ab75%3A0x6bfe76e033e08f23!2sRuko%20Klampis%20Megah!5e0!3m2!1sid!2sid!4v1718000000001!5m2!1sid!2sid',
        'map_link' => 'https://maps.app.goo.gl/9R684o1w4Z4P79cW6',
        'services' => 'VO, Coworking Space, Meeting Room (Klaim Jatah VO)',
        'advantages' => [
            'Terletak di pusat kuliner & bisnis Ruko Klampis Megah Surabaya',
            'Lingkungan kerja yang tenang dan kondusif untuk konsentrasi',
            'Resepsionis ramah untuk menyapa klien & handling dokumen Anda',
            'Internet berkecepatan tinggi Wi-Fi Dedicated',
            'Dapur mini (pantry) dengan akses kopi/teh gratis harian',
            'Dekat dengan kampus ternama (ITS, UNAIR Kampus C)'
        ],
        'gallery' => [
            'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1527192491265-7e15c55b1ed2?auto=format&fit=crop&w=800&q=80'
        ],
        'seo' => [
            'title' => 'Sewa Kantor & Virtual Office Klampis Surabaya - Urban Office',
            'description' => 'Sewa kantor murah & Virtual Office di Ruko Klampis Megah, Surabaya. Lengkap dengan Meeting Room, lingkungan bisnis strategis, dan akses mudah.',
            'keywords' => 'virtual office klampis, sewa kantor klampis jaya, coworking space klampis'
        ],
        'pricing' => [
            [
                'category' => 'virtual-office',
                'name' => 'Virtual Office Starter',
                'price' => '385.000',
                'period' => 'bulan',
                'desc' => 'Dapatkan alamat bisnis prestisius di area strategis Klampis Jaya.',
                'features' => [
                    'Alamat Bisnis Prestisius di Gedung Komersial',
                    'Layanan Penanganan Surat & Paket',
                    'Notifikasi Chat WhatsApp Surat Masuk',
                    'Notifikasi Surat Masuk via WhatsApp',
                    'Diskon Ruang Meeting Khusus Member'
                ],
                'cta_wa' => 'Halo Urban Office, saya ingin memesan Virtual Office Starter di Cabang Klampis.'
            ],
            [
                'category' => 'virtual-office',
                'name' => 'Virtual Office Luxury',
                'price' => '620.000',
                'period' => 'bulan',
                'desc' => 'Sempurna untuk bisnis yang sedang berkembang dengan akses ruang rapat.',
                'features' => [
                    'Alamat Bisnis Komersial Klampis Megah',
                    'Penerimaan Surat Menyurat & Paket',
                    'Pemberitahuan Instan via WhatsApp/Email',
                    'Akses Rapat 2x 3 Jam/Bulan di Surabaya',
                    'Nomor Telepon Kantor Bersama'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik dengan Virtual Office Luxury di Cabang Klampis.'
            ],
            [
                'category' => 'coworking',
                'name' => 'Coworking Shared Desk',
                'price' => '45.000',
                'period' => 'hari',
                'desc' => 'Meja kerja harian yang nyaman di area coworking space Klampis.',
                'features' => [
                    'Meja Kerja dengan Colokan Mandiri',
                    'Internet Wi-Fi Cepat',
                    'Free Flow Drinks (Kopi & Teh)',
                    'Lounge Area Nyaman',
                    'Lingkungan Startup Dinamis'
                ],
                'cta_wa' => 'Halo Urban Office, saya mau sewa Hot Desk harian di Cabang Klampis.'
            ],
            [
                'category' => 'coworking',
                'name' => 'Coworking Bulanan',
                'price' => '750.000',
                'period' => 'pax/bulan',
                'desc' => 'Ruang kerja bulanan hemat dengan akses internet cepat dan free beverages.',
                'features' => [
                    'Akses Open Desk Bulanan Penuh',
                    'Internet Kecepatan Tinggi Dedicated',
                    'Free Flow Minuman di Pantry',
                    'Penanganan Surat & Resepsionis Support'
                ],
                'cta_wa' => 'Halo Urban Office, saya mau sewa Coworking Bulanan di Cabang Klampis.'
            ],
            [
                'category' => 'meeting-room',
                'name' => 'Small Meeting Room',
                'price' => '125.000',
                'period' => 'jam',
                'desc' => 'Fasilitas eksklusif member Virtual Office, digunakan melalui klaim jatah bulanan (tidak dijual sewa per jam secara umum).',
                'booking_mode' => 'claim-only',
                'features' => [
                    'Kapasitas hingga 6 Orang',
                    'Layar LED TV HD',
                    'Koneksi Internet Cepat & LAN',
                    'Whiteboard & Spidol',
                    'Free Air Mineral'
                ],
                'cta_wa' => 'Halo Urban Office, saya ingin bertanya cara klaim jatah Meeting Room dari paket Virtual Office di Cabang Klampis.'
            ]
        ],
        'faq' => [
            [
                'question' => 'Apakah lokasi Ruko Klampis mudah diakses angkutan umum?',
                'answer' => 'Ya, lokasi Ruko Klampis Megah berada di jalan protokol Klampis Jaya yang banyak dilalui transportasi online maupun sarana transportasi umum Surabaya.'
            ],
            [
                'question' => 'Apakah ada biaya tambahan untuk penggunaan AC?',
                'answer' => 'Tidak. Untuk paket Serviced Office, semua biaya utilitas termasuk listrik, AC, kebersihan, dan IPL sudah menjadi tanggung jawab kami.'
            ]
        ],
        'testimonial' => [
            'name' => 'Agatha Christie',
            'role' => 'Founder Creative Studio',
            'text' => 'Suasana kerja di cabang Klampis sangat tenang. Sangat cocok bagi desainer grafis seperti saya yang membutuhkan ketenangan untuk mencari inspirasi. Staff resepsionisnya pun ramah-ramah.',
            'rating' => 4,
            'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80'
        ]
    ],
    'surabaya-barat' => [
        'slug' => 'surabaya-barat',
        'title' => 'Urban Office - Grand Sungkono Lagoon (Surabaya Barat)',
        'short_title' => 'Grand Sungkono Lagoon',
        'city' => 'Surabaya',
        'location' => 'Surabaya Barat',
        'address' => 'PP54+MJW, Jl. KH Abdul Wahab Siamin Surabaya, Dukuh Pakis, Kec. Dukuhpakis, Surabaya, Jawa Timur 60225',
        'kpp' => 'KPP Karang Pilang',
        'rating' => '4.9',
        'reviews_count' => '94',
        'image' => BASE_URL . 'assets/images/branch/gsl new.png',
        'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3957.51479860682!2d112.69532587476075!3d-7.295880492711311!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7fde2e8b0e12f%3A0xe54d89694ea519ee!2sGrand%20Sungkono%20Lagoon!5e0!3m2!1sid!2sid!4v1718000000002!5m2!1sid!2sid',
        'map_link' => 'https://maps.app.goo.gl/tJ9DksjK31GjLdC16',
        'services' => 'VO, Meeting Room (Klaim Jatah VO)',
        'advantages' => [
            'Berada di kawasan prestisius Terpadu Grand Sungkono Lagoon Surabaya Barat',
            'Alamat bisnis kelas premium yang meningkatkan citra bonafide perusahaan',
            'Keamanan tingkat tinggi 24 jam dengan sistem akses smart card',
            'Integrasi area komersial, kafe, mal, dan apartemen mewah',
            'Fasilitas resepsionis eksekutif untuk menyambut tamu VIP Anda',
            'Akses cepat menuju gerbang tol satelit (hanya 5 menit)'
        ],
        'gallery' => [
            'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=800&q=80'
        ],
        'seo' => [
            'title' => 'Virtual Office & Kantor Sungkono Surabaya Barat - Urban Office',
            'description' => 'Sewa Virtual Office prestisius & Serviced Office di Grand Sungkono Lagoon, Surabaya Barat. Alamat bergengsi dekat Gerbang Tol Satelit.',
            'keywords' => 'virtual office surabaya barat, grand sungkono lagoon office, sewa kantor sungkono'
        ],
        'pricing' => [
            [
                'category' => 'virtual-office',
                'name' => 'Virtual Office Starter',
                'price' => '385.000',
                'period' => 'bulan',
                'desc' => 'Alamat kantor virtual di kawasan mixed-use terpadu Sungkono Lagoon.',
                'features' => [
                    'Alamat Bisnis Prestisius & Strategis',
                    'Penerimaan & Penyimpanan Surat Profesional',
                    'Pemberitahuan Instan Surat via WA/Email',
                    'Akses Meeting Room 2x 2 Jam/Bulan',
                    'Resepsionis Bilingual (Indonesia - Inggris)'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik dengan Virtual Office Starter di Grand Sungkono Lagoon.'
            ],
            [
                'category' => 'virtual-office',
                'name' => 'Virtual Office Luxury',
                'price' => '620.000',
                'period' => 'bulan',
                'desc' => 'Sewa Virtual Office premium untuk kredibilitas tingkat tinggi.',
                'features' => [
                    'Semua Fasilitas Paket Starter',
                    'Nomor Telepon Kantor Bersama (Shared Line)',
                    'Resepsionis Profesional Penerima Tamu',
                    'Akses Meeting Room 2x 3 Jam/Bulan'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik dengan Virtual Office Luxury di Grand Sungkono Lagoon.'
            ],
            [
                'category' => 'meeting-room',
                'name' => 'Small Meeting Room',
                'price' => '125.000',
                'period' => 'jam',
                'desc' => 'Fasilitas eksklusif member Virtual Office, digunakan melalui klaim jatah bulanan (tidak dijual sewa per jam secara umum).',
                'booking_mode' => 'claim-only',
                'features' => [
                    'Kapasitas hingga 6 Orang',
                    'Smart TV Layar Lebar HD',
                    'Papan Tulis & Spidol',
                    'Koneksi Internet Cepat',
                    'Penyambutan Tamu oleh Resepsionis'
                ],
                'cta_wa' => 'Halo Urban Office, saya ingin bertanya cara klaim jatah Meeting Room dari paket Virtual Office di Grand Sungkono Lagoon.'
            ]
        ],
        'faq' => [
            [
                'question' => 'Apa keunggulan alamat kantor di Grand Sungkono Lagoon?',
                'answer' => 'Grand Sungkono Lagoon berada di kawasan perkantoran komersial premium Surabaya Barat dengan citra bisnis yang kuat dan akses yang mudah bagi klien Anda.'
            ],
            [
                'question' => 'Apakah area gedung terhubung langsung dengan pusat perbelanjaan?',
                'answer' => 'Ya, kompleks Grand Sungkono Lagoon terintegrasi dengan pusat gaya hidup, restoran premium, dan area mal ritel.'
            ]
        ],
        'testimonial' => [
            'name' => 'Hendra Wijaya',
            'role' => 'Managing Partner Law Firm Wijaya',
            'text' => 'Mengalihkan alamat kantor perusahaan kami ke Grand Sungkono Lagoon adalah keputusan terbaik. Citra perusahaan kami di mata klien sangat meningkat berkat representasi alamat prestisius ini.',
            'rating' => 5,
            'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80'
        ]
    ],
    'jakarta' => [
        'slug' => 'jakarta',
        'title' => 'Urban Office - Fatmawati (Jakarta Selatan)',
        'short_title' => 'Fatmawati',
        'city' => 'Jakarta',
        'location' => 'Jakarta Selatan',
        'nearby' => 'dekat MRT Fatmawati & TB Simatupang',
        // Real Google Maps reviews (verbatim). Shown with the Google badge because the source IS Google.
        'google_reviews' => [
            ['name' => 'Muhammad Heriyanto', 'rating' => 5, 'text' => 'Pelayanannya mantap dan timnya murah senyum.'],
            ['name' => 'Hermin Yuliawati', 'rating' => 5, 'text' => 'Letak sangat strategis, harga cukup bersaing, parkir mudah.'],
            ['name' => 'Frezcup', 'rating' => 5, 'text' => 'Ternyata bisa juga buat bantu urus legalitas, jadi gak pusing ke depannya.'],
            ['name' => 'Maulana Sechuti', 'rating' => 5, 'text' => 'Pelayanan ramah dan sesuai dengan yang saya inginkan.'],
            ['name' => 'Techno Clip', 'rating' => 5, 'text' => 'Tempatnya masih baru namun pelayanannya sudah profesional. Rekomended untuk tempat kumpul bagi yang punya bisnis startup.'],
        ],
        'address' => 'Jl. RS. Fatmawati Raya No.35A 2, RT.2/RW.5, Cilandak Bar., Kec. Cilandak, Jakarta, Daerah Khusus Ibukota Jakarta 12430',
        'kpp' => 'KPP Cilandak',
        'rating' => '4.8',
        'reviews_count' => '108',
        'image' => BASE_URL . 'assets/images/branch/urban office fatmawati.webp',
        'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.955375497274!2d106.79426917475143!3d-6.269601093719049!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f1a0e9b9cf99%3A0x6b453e1a63c64e6b!2sJl.%20R.S.%20Fatmawati%20Raya%2C%20Cilandak%2C%20Kota%20Jakarta%20Selatan%2C%20Daerah%20Khusus%20Ibukota%20Jakarta!5e0!3m2!1sid!2sid!4v1718000000003!5m2!1sid!2sid',
        'map_link' => 'https://maps.app.goo.gl/e7m1Bcy75H9zRjC8A',
        'services' => 'VO, Serviced Office, Meeting Room, Coworking, Event Space, Sharing Room Office',
        'advantages' => [
            'Sangat dekat dengan Stasiun MRT Fatmawati (Akses transportasi bebas macet)',
            'Pusat bisnis strategis Jakarta Selatan dekat kawasan perkantoran TB Simatupang',
            'Penanganan surat menyurat profesional khas Urban Office',
            'Internet dedicated stabil berkecepatan tinggi',
            'Sistem keamanan gedung terintegrasi dengan akses lift terkontrol',
            'Layanan reception yang fasih berbahasa Inggris'
        ],
        'gallery' => [
            'https://images.unsplash.com/photo-1539635278303-d4002c07eae3?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=800&q=80'
        ],
        'seo' => [
            'title' => 'Virtual Office Jakarta Selatan MRT Fatmawati - Urban Office',
            'description' => 'Sewa Virtual Office & Ruang Kantor di Fatmawati Jakarta Selatan. Strategis dekat Stasiun MRT & CBD TB Simatupang. Alamat kantor prestisius.',
            'keywords' => 'virtual office fatmawati, sewa kantor jakarta selatan, office dekat mrt fatmawati'
        ],
        'pricing' => [
            [
                'category' => 'virtual-office',
                'name' => 'Virtual Office Starter',
                'price' => '385.000',
                'period' => 'bulan',
                'desc' => 'Dapatkan alamat kantor prestisius di Jakarta Selatan untuk bisnis Anda.',
                'features' => [
                    'Alamat Bisnis Sah & Zoned Perkantoran Jaksel',
                    'Penerimaan Dokumen & Paket Surat Menyurat',
                    'Scan & Email Dokumen Instan',
                    'Akses Meeting Room 2x 2 Jam/Bulan di Jakarta',
                    'Call Forwarding & Resepsionis Ramah'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik Virtual Office Starter di Cabang Fatmawati Jakarta Selatan.'
            ],
            [
                'category' => 'virtual-office',
                'name' => 'Virtual Office Luxury',
                'price' => '620.000',
                'period' => 'bulan',
                'desc' => 'Alamat bisnis prestisius di Jakarta Selatan lengkap dengan nomor telepon bersama.',
                'features' => [
                    'Semua Fasilitas Paket Starter',
                    'Nomor Telepon Bersama Jakarta (021)',
                    'Penyambutan Tamu Profesional',
                    'Akses Meeting Room 2x 3 Jam/Bulan'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik Virtual Office Luxury di Cabang Fatmawati Jakarta Selatan.'
            ],
            [
                'category' => 'private-office',
                'name' => 'Private Serviced Office',
                'price' => 'Negotiable',
                'period' => 'bulan',
                'desc' => 'Kantor siap pakai yang nyaman dan privat di kawasan Cilandak Fatmawati.',
                'features' => [
                    'Fully Furnished Office Room + AC (2-6 Pax)',
                    'Free Flow Beverages (Teh, Kopi, Air)',
                    'Akses Internet Cepat & LAN Cable',
                    'Resepsionis Support & Cleaning Harian',
                    'Bebas Biaya Utilitas (Listrik/Air/AC)',
                    'Kuota Free Ruang Meeting Bulanan'
                ],
                'cta_wa' => 'Halo Urban Office, saya ingin menjadwalkan kunjungan Serviced Office di Fatmawati.'
            ],
            [
                'category' => 'coworking',
                'name' => 'Hot Desk Harian',
                'price' => '45.000',
                'period' => 'pax/hari',
                'desc' => 'Ruang kerja fleksibel di area open space yang dinamis. Cocok untuk freelancer.',
                'features' => [
                    'Akses Fleksibel ke Open Workspace',
                    'High-Speed Wi-Fi Dedicated',
                    'Free Flow Coffee, Tea & Mineral Water',
                    'Akses ke Collab Zone & Lounge',
                    'Tersedia Colokan Listrik di Setiap Meja'
                ],
                'cta_wa' => 'Halo Urban Office, saya mau booking Hot Desk harian di Cabang Fatmawati.'
            ],
            [
                'category' => 'coworking',
                'name' => 'Coworking Bulanan',
                'price' => '750.000',
                'period' => 'pax/bulan',
                'desc' => 'Akses kerja tanpa batas selama sebulan penuh dengan fasilitas lengkap.',
                'features' => [
                    'Akses Bulanan Penuh (Open Area)',
                    'High-Speed Wi-Fi Dedicated',
                    'Free Flow Coffee, Tea & Mineral Water',
                    'Akses Lounge & Ruang Kerja Bersama',
                    'Layanan Resepsionis Penerimaan Surat'
                ],
                'cta_wa' => 'Halo Urban Office, saya mau sewa Coworking Bulanan di Cabang Fatmawati.'
            ],
            [
                'category' => 'meeting-room',
                'name' => 'Small Meeting Room',
                'price' => '125.000',
                'period' => 'jam',
                'desc' => 'Sewa ruang meeting eksklusif dekat MRT Fatmawati.',
                'features' => [
                    'Kapasitas hingga 6 Orang',
                    'Smart TV LED Monitor HD',
                    'Internet Cepat Dedicated',
                    'Whiteboard & Spidol',
                    'Free flow Drinks'
                ],
                'cta_wa' => 'Halo Urban Office, saya ingin booking Meeting Room di Cabang Fatmawati.'
            ],
            [
                'category' => 'event-space',
                'name' => 'Event Space (Half-Day)',
                'price' => '45.000',
                'period' => 'pax/4 jam',
                'desc' => 'Area luas dan fleksibel untuk seminar, workshop, talkshow, maupun gathering selama 4 jam.',
                'features' => [
                    'Kapasitas hingga 30 - 50 Orang',
                    'Sound System Standard & Wireless Mic',
                    'Proyektor Layar Besar',
                    'Layout Kursi Fleksibel',
                    'Fasilitas Penerimaan Tamu (Registrasi)'
                ],
                'cta_wa' => 'Halo Urban Office, saya ingin menanyakan sewa Event Space Half-Day di Cabang Fatmawati.'
            ],
            [
                'category' => 'event-space',
                'name' => 'Event Space (Full-Day)',
                'price' => '90.000',
                'period' => 'pax/8 jam',
                'desc' => 'Sewa ruang serbaguna full day selama 8 jam untuk seminar korporat skala besar.',
                'features' => [
                    'Kapasitas hingga 30 - 50 Orang',
                    'Sound System Standard & Wireless Mic',
                    'Proyektor Layar Besar',
                    'Layout Kursi Fleksibel',
                    'Pantry & Lounge Support'
                ],
                'cta_wa' => 'Halo Urban Office, saya ingin menanyakan sewa Event Space Full-Day di Cabang Fatmawati.'
            ],
            [
                'category' => 'sharing-room-office',
                'name' => 'Sharing Room Office Desk',
                'price' => '1.000.000',
                'period' => 'pax/bulan',
                'desc' => 'Meja kerja khusus dalam ruang kantor bersama yang tenang dengan harga hemat.',
                'features' => [
                    'Meja Kerja Khusus (Dedicated Desk)',
                    'Internet Wi-Fi Cepat',
                    'Layanan Penerimaan Resepsionis',
                    'Bebas Biaya IPL, Listrik, & Air',
                    'Free Flow Beverages (Teh/Kopi)'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik menyewa Sharing Room Office di Cabang Fatmawati.'
            ]
        ],
        'faq' => [
            [
                'question' => 'Seberapa dekat lokasi kantor dengan stasiun MRT?',
                'answer' => 'Lokasi cabang Fatmawati kami hanya berjarak kurang dari 5 menit berjalan kaki dari stasiun MRT Fatmawati.'
            ]
        ],
        'testimonial' => [
            'name' => 'Sarah Amalia',
            'role' => 'Direktur PT Cahaya Digital',
            'text' => 'Kantor yang sangat strategis. Transportasi dengan MRT sangat memudahkan tim kami untuk bertemu klien di pusat kota Jakarta tanpa khawatir terjebak macet. Sangat direkomendasikan!',
            'rating' => 5,
            'image' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80'
        ]
    ],
    'jakarta-timur' => [
        'slug' => 'jakarta-timur',
        'title' => 'Urban Office - Gorebiz (Jakarta Timur)',
        'short_title' => 'Gorebiz',
        'city' => 'Jakarta',
        'location' => 'Jakarta Timur',
        'address' => 'Jl. Raya Bekasi.KM.17, RT.4/RW.3, Jatinegara, Kec. Cakung, Kota Jakarta Timur, Daerah Khusus Ibukota Jakarta 13930',
        'kpp' => 'KPP Cakung Satu', // TODO: verifikasi nama KPP yang benar
        'rating' => '4.7',
        'reviews_count' => '78',
        'image' => BASE_URL . 'assets/images/branch/gorebiz.png',
        'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.5298950868203!2d106.90227187475083!3d-6.1935933937941785!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f0a6727d3b07%3A0xe6f600494ebcefb8!2sJl.%20Raya%20Bekasi%20KM.17%2C%20Jatinegara%2C%20Kec.%20Cakung%2C%20Kota%20Jakarta%20Timur!5e0!3m2!1sid!2sid!4v1718000000004!5m2!1sid!2sid',
        'map_link' => 'https://maps.app.goo.gl/9o1eGj7W4x62iV3d8',
        'services' => 'VO, Serviced Office, Meeting Room',
        'advantages' => [
            'Akses langsung ke jalan arteri utama Jl. Raya Bekasi KM 17',
            'Kawasan bisnis komersial strategis di Jakarta Timur (Cakung/Jatinegara)',
            'Layanan surat-menyurat aman dan tepercaya dengan pemberitahuan real-time',
            'Fasilitas internet serat optik dedicated',
            'Pantry bersama & Free Flow minuman hangat/dingin',
            'Dekat kawasan industri Pulogadung (lokasi strategis untuk distribusi)'
        ],
        'gallery' => [
            'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1527192491265-7e15c55b1ed2?auto=format&fit=crop&w=800&q=80'
        ],
        'seo' => [
            'title' => 'Virtual Office Jakarta Timur Murah PT/CV - Urban Office',
            'description' => 'Sewa Virtual Office murah di Cakung Jatinegara Jakarta Timur. Alamat kantor prestisius di kawasan bisnis komersial Jakarta Timur.',
            'keywords' => 'virtual office jakarta timur, sewa kantor cakung, virtual office murah resignation'
        ],
        'pricing' => [
            [
                'category' => 'virtual-office',
                'name' => 'Virtual Office Starter',
                'price' => '385.000',
                'period' => 'bulan',
                'desc' => 'Solusi alamat bisnis prestisius di wilayah strategis Jakarta Timur.',
                'features' => [
                    'Alamat Bisnis Prestisius di Gedung Komersial',
                    'Penyimpanan Surat & Notifikasi via WA',
                    'Gratis Hak Penggunaan Alamat Kantor',
                    'Diskon Member untuk Rental Meeting Room'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik dengan paket Virtual Office Starter di Gorebiz Jakarta Timur.'
            ],
            [
                'category' => 'virtual-office',
                'name' => 'Virtual Office Luxury',
                'price' => '620.000',
                'period' => 'bulan',
                'desc' => 'Paket kantor virtual premium dengan fasilitas lengkap di Jakarta Timur.',
                'features' => [
                    'Semua Fasilitas Paket Starter',
                    'Nomor Telepon Bersama Jakarta (021)',
                    'Resepsionis Standby & Penerima Surat',
                    'Akses Meeting Room 2x 3 Jam/Bulan'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik dengan paket Virtual Office Luxury di Gorebiz Jakarta Timur.'
            ],
            [
                'category' => 'private-office',
                'name' => 'Private Serviced Office',
                'price' => 'Negotiable',
                'period' => 'bulan',
                'desc' => 'Ruang kerja privat berfurnitur lengkap yang berdekatan dengan kawasan industri Pulogadung.',
                'features' => [
                    'Fully Furnished Workspace (2-6 Pax)',
                    'Akses Wi-Fi Kecepatan Tinggi Dedicated',
                    'Bebas IPL, Listrik, & Kebersihan',
                    'Penerimaan Tamu oleh Frontdesk Staff',
                    'Free flow Kopi/Teh/Air Mineral harian',
                    'Bebas Kuota Meeting Room Bulanan'
                ],
                'cta_wa' => 'Halo Urban Office, saya ingin survey kantor di Gorebiz Jakarta Timur.'
            ],
            [
                'category' => 'meeting-room',
                'name' => 'Small Meeting Room',
                'price' => '125.000',
                'period' => 'jam',
                'desc' => 'Ruangan rapat ber-AC lengkap di Gorebiz, dapat disewa langsung per jam atau digunakan lewat klaim jatah bulanan Virtual Office.',
                'features' => [
                    'Kapasitas hingga 6 Orang',
                    'Smart TV LED HD Monitor',
                    'Papan Tulis & Alat Tulis',
                    'Internet Wi-Fi Cepat',
                    'Free Air Mineral'
                ],
                'cta_wa' => 'Halo Urban Office, saya mau sewa Meeting Room di Cabang Gorebiz.'
            ]
        ],
        'faq' => [
            [
                'question' => 'Apakah lokasi Jl. Raya Bekasi cocok untuk usaha logistik dan perdagangan?',
                'answer' => 'Ya, lokasi Gorebiz berada di kawasan komersial perdagangan dan industri sehingga sangat ideal untuk perusahaan perdagangan besar, logistik, maupun penyedia jasa lainnya.'
            ]
        ],
        'testimonial' => [
            'name' => 'Brawijaya',
            'role' => 'Direktur PT Logistik Nusantara',
            'text' => 'Lokasi yang strategis dekat Pulogadung sangat mempermudah operasional surat menyurat perusahaan logistik kami. Harganya bersahabat untuk ukuran Jakarta.',
            'rating' => 4,
            'image' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=150&q=80'
        ]
    ],
    'gresik' => [
        'slug' => 'gresik',
        'title' => 'Urban Office - PTGM Tower (Gresik)',
        'short_title' => 'PTGM Tower',
        'city' => 'Gresik',
        'location' => 'Gresik',
        'address' => 'Jl. Dr. Wahidin Sudirohusodo No.708, Kembangan, Kec. Kebomas, Kabupaten Gresik, Jawa Timur 61161',
        'kpp' => 'KPP Gresik Utara', // TODO: verifikasi nama KPP yang benar
        'rating' => '4.8',
        'reviews_count' => '86',
        'image' => BASE_URL . 'assets/images/branch/ptgm.png',
        'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3958.8358487311145!2d112.61633517475829!3d-7.144961592859424!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e77ffd000000001%3A0xe5a363dbf15777bd!2sPTGM%20Tower!5e0!3m2!1sid!2sid!4v1718000000005!5m2!1sid!2sid',
        'map_link' => 'https://maps.app.goo.gl/wS78nch47T66bS148',
        'services' => 'VO, Serviced Office, Meeting Room',
        'advantages' => [
            'Berada di gedung perkantoran prestisius PTGM Tower Kebomas Gresik',
            'Kawasan perkantoran modern sangat cocok bagi industri manufaktur & pelayaran',
            'Alamat bisnis strategis di pusat administrasi Kabupaten Gresik',
            'Fasilitas resepsionis profesional dan penanganan paket dokumen korporat',
            'Internet Dedicated Wi-Fi & ruang meeting ber-AC lengkap',
            'Akses mudah menuju Tol Romokalisari dan Pelabuhan Gresik'
        ],
        'gallery' => [
            'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=800&q=80'
        ],
        'seo' => [
            'title' => 'Virtual Office Gresik PTGM Tower Kebomas - Urban Office',
            'description' => 'Sewa Virtual Office & Serviced Office premium di Kebomas Gresik. Strategis dekat Kantor Pemkab Gresik dan akses tol Surabaya-Gresik.',
            'keywords' => 'virtual office gresik, sewa kantor kebomas, ptgm tower gresik'
        ],
        'pricing' => [
            [
                'category' => 'virtual-office',
                'name' => 'Virtual Office Starter',
                'price' => '385.000',
                'period' => 'bulan',
                'desc' => 'Dapatkan alamat kantor representatif di gedung perkantoran utama di Gresik.',
                'features' => [
                    'Alamat Bisnis Prestisius PTGM Tower',
                    'Layanan Handling Surat Menyurat & Paket',
                    'Notifikasi Real-time via WhatsApp',
                    'Hak Penggunaan Alamat Kantor di Gresik',
                    'Akses Area Lounge Kerja'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik dengan Virtual Office Starter di PTGM Tower Gresik.'
            ],
            [
                'category' => 'virtual-office',
                'name' => 'Virtual Office Luxury',
                'price' => '620.000',
                'period' => 'bulan',
                'desc' => 'Alamat kantor virtual premium di Gresik dengan fasilitas call forwarding.',
                'features' => [
                    'Semua Fasilitas Paket Starter',
                    'Nomor Telepon Bersama Gresik',
                    'Akses Meeting Room 2x 3 Jam/Bulan',
                    'Layanan Resepsionis Penerima Tamu'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik dengan Virtual Office Luxury di PTGM Tower Gresik.'
            ],
            [
                'category' => 'private-office',
                'name' => 'Private Serviced Office',
                'price' => 'Negotiable',
                'period' => 'bulan',
                'desc' => 'Ruang kantor privat berfurnitur lengkap (furnished) dengan view pemandangan kawasan industri Gresik.',
                'features' => [
                    'Ruang Kantor Privat Eksklusif (2-6 Pax)',
                    'Meja, Kursi Ergonomis, & Loker Dokumen',
                    'Koneksi Internet dedicated Wi-Fi & LAN',
                    'Free IPL & Pembersihan Harian',
                    'Bebas Biaya Utilitas (Listrik/AC/Air)',
                    'Akses Meeting Room Bulanan Gratis'
                ],
                'cta_wa' => 'Halo Urban Office, saya ingin menjadwalkan kunjungan kantor di PTGM Tower Gresik.'
            ],
            [
                'category' => 'meeting-room',
                'name' => 'Small Meeting Room',
                'price' => '125.000',
                'period' => 'jam',
                'desc' => 'Ruangan rapat ber-AC lengkap di PTGM Tower, dapat disewa langsung per jam atau digunakan lewat klaim jatah bulanan Virtual Office.',
                'features' => [
                    'Kapasitas hingga 6 Orang',
                    'Smart TV LED HD Monitor',
                    'Papan Tulis & Alat Tulis',
                    'Internet Wi-Fi Cepat',
                    'Free Air Mineral'
                ],
                'cta_wa' => 'Halo Urban Office, saya mau sewa Meeting Room di PTGM Tower Gresik.'
            ]
        ],
        'faq' => [
            [
                'question' => 'Apakah lokasi kantor dekat dengan kantor pemerintahan Gresik?',
                'answer' => 'Ya, lokasi PTGM Tower sangat strategis di pusat Kota Gresik, dekat dengan kawasan pemerintahan dan pusat bisnis utama.'
            ]
        ],
        'testimonial' => [
            'name' => 'Yusuf Habibi',
            'role' => 'Operations Manager PT Pelayaran Selat',
            'text' => 'Sebagai perusahaan pelayaran baru, beralamat di PTGM Tower memberikan kredibilitas instan saat kami berurusan dengan otoritas pelabuhan dan bea cukai Gresik.',
            'rating' => 5,
            'image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=150&q=80'
        ]
    ],
    'medan' => [
        'slug' => 'medan',
        'title' => 'Urban Office - Medan',
        'short_title' => 'Medan',
        'city' => 'Medan',
        'location' => 'Sumatera Utara',
        'address' => 'Jl. Sutrisno No.258, Medan Area, Kota Medan, Sumatera Utara 20211',
        'kpp' => '',
        'rating' => '4.8',
        'reviews_count' => '64',
        'image' => BASE_URL . 'assets/images/branch/medan.png',
        'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3982.023805908381!2d98.69234857473523!3d3.582061096392305!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x303131bf4e8c148f%3A0xe54d89694ea519a7!2sJl.%20Sutrisno%20No.258%2C%20Kota%20Medan%2C%20Sumatera%20Utara!5e0!3m2!1sid!2sid!4v1718000000007!5m2!1sid!2sid',
        'map_link' => 'https://maps.app.goo.gl/Kk6PswQ17F7249Hw9',
        'services' => 'VO',
        'advantages' => [
            'Berlokasi di pusat niaga utama Jl. Sutrisno Medan (Akses mudah)',
            'Kawasan komersial strategis di pusat bisnis Kota Medan',
            'Pemberitahuan surat & dokumen masuk real-time via email & WA',
            'Koneksi internet dedicated Wi-Fi berkecepatan tinggi',
            'Pantry modern dengan air mineral & minuman hangat gratis',
            'Dekat stasiun kereta api bandara Kualanamu (20 menit)'
        ],
        'gallery' => [
            'https://images.unsplash.com/photo-1527192491265-7e15c55b1ed2?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=800&q=80'
        ],
        'seo' => [
            'title' => 'Virtual Office Medan Sutrisno Murah - Urban Office',
            'description' => 'Sewa Virtual Office & Serviced Office murah di Medan Area. Alamat bisnis prestisius, penanganan surat profesional, dan lokasi strategis di pusat bisnis Medan.',
            'keywords' => 'virtual office medan, sewa kantor medan area, virtual office murah medan'
        ],
        'pricing' => [
            [
                'category' => 'virtual-office',
                'name' => 'Virtual Office Starter',
                'price' => '385.000',
                'period' => 'bulan',
                'desc' => 'Dapatkan alamat kantor prestisius di kota Medan untuk kebutuhan bisnis Anda.',
                'features' => [
                    'Alamat Bisnis Komersial Sah di Jl. Sutrisno',
                    'Layanan Penerimaan Surat & Paket Masuk',
                    'Notifikasi Chat WhatsApp Real-Time',
                    'Hak Penggunaan Alamat Kantor di Medan',
                    'Resepsionis Penerima Tamu',
                    'Akses Meeting Room 2x 2 Jam/Bulan'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik dengan Virtual Office Starter di Cabang Medan.'
            ],
            [
                'category' => 'virtual-office',
                'name' => 'Virtual Office Luxury',
                'price' => '620.000',
                'period' => 'bulan',
                'desc' => 'Alamat kantor virtual premium di Medan dengan nomor telepon bersama.',
                'features' => [
                    'Semua Fasilitas Paket Starter',
                    'Nomor Telepon Bersama Medan (061)',
                    'Penyambutan Tamu Profesional',
                    'Akses Meeting Room 2x 3 Jam/Bulan'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik dengan Virtual Office Luxury di Cabang Medan.'
            ]
        ],
        'faq' => [
            [
                'question' => 'Apa keunggulan alamat kantor di Medan Area?',
                'answer' => 'Cabang Medan kami berada di kawasan komersial strategis Medan Area dengan citra bisnis yang kuat dan akses yang mudah.'
            ]
        ],
        'testimonial' => [
            'name' => 'Rudy Sinaga',
            'role' => 'Pemilik CV Medan Jaya Mandiri',
            'text' => 'Menyewa Virtual Office Urban sangat cepat dan mudah. Alamatnya prestisius dan staffnya sangat sigap memberikan update surat masuk.',
            'rating' => 4,
            'image' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=150&q=80'
        ]
    ],
    'malang' => [
        'slug' => 'malang',
        'title' => 'Urban Office - Malang',
        'short_title' => 'Malang',
        'city' => 'Malang',
        'location' => 'Jawa Timur',
        'address' => 'Jl. Blimbing Indah Megah No.10A Blok B7, Polowijen, Blimbing, Malang City, East Java 65126',
        'kpp' => '',
        'rating' => '4.8',
        'reviews_count' => '45',
        'image' => BASE_URL . 'assets/images/branch/Malang.jpeg',
        'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3951.520421715494!2d112.645731!3d-7.945037!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zN8KwNTYnNDIuMSJTIDExMsKwMzgnNDQuNiJF!5e0!3m2!1sid!2sid!4v1680000000000!5m2!1sid!2sid',
        'map_link' => 'https://maps.google.com/',
        'services' => 'VO, Serviced Office, Meeting Room, Coworking, Event Space, Sharing Room Office',
        'advantages' => [
            'Lokasi premium di kawasan bisnis Blimbing Indah Megah Malang',
            'Akses mudah dan dekat dengan pusat perbelanjaan',
            'Internet berkecepatan tinggi',
            'Lingkungan asri dan nyaman khas kota Malang',
            'Free flow minuman teh/kopi'
        ],
        'gallery' => [
            'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=800&q=80'
        ],
        'seo' => [
            'title' => 'Virtual Office & Coworking Space Malang - Urban Office',
            'description' => 'Sewa Virtual Office, Coworking Space, & Private Office di Blimbing Malang. Fasilitas lengkap dan lokasi strategis.',
            'keywords' => 'virtual office malang, coworking space blimbing malang, sewa kantor malang'
        ],
        'pricing' => [
            [
                'category' => 'virtual-office',
                'name' => 'Virtual Office Starter',
                'price' => '385.000',
                'period' => 'bulan',
                'desc' => 'Dapatkan alamat bisnis sah di Kota Malang.',
                'features' => [
                    'Alamat Bisnis Prestisius',
                    'Penerimaan Surat Menyurat',
                    'Notifikasi via WhatsApp/Email',
                    'Gratis Hak Penggunaan Alamat Kantor'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik dengan Virtual Office Starter di Cabang Malang.'
            ],
            [
                'category' => 'coworking',
                'name' => 'Hot Desk Harian',
                'price' => '45.000',
                'period' => 'pax/hari',
                'desc' => 'Ruang kerja fleksibel di area open space yang dinamis. Cocok untuk freelancer.',
                'features' => [
                    'Akses Fleksibel ke Open Workspace',
                    'High-Speed Wi-Fi Dedicated',
                    'Free Flow Coffee, Tea & Mineral Water',
                    'Akses ke Collab Zone & Lounge',
                    'Tersedia Colokan Listrik di Setiap Meja'
                ],
                'cta_wa' => 'Halo Urban Office, saya mau booking Hot Desk harian di Cabang Malang.'
            ],
            [
                'category' => 'coworking',
                'name' => 'Coworking Bulanan',
                'price' => '750.000',
                'period' => 'pax/bulan',
                'desc' => 'Akses kerja tanpa batas selama sebulan penuh dengan fasilitas lengkap.',
                'features' => [
                    'Akses Bulanan Penuh (Open Area)',
                    'High-Speed Wi-Fi Dedicated',
                    'Free Flow Coffee, Tea & Mineral Water',
                    'Akses Lounge & Ruang Kerja Bersama',
                    'Layanan Resepsionis Penerimaan Surat'
                ],
                'cta_wa' => 'Halo Urban Office, saya mau sewa Coworking Bulanan di Cabang Malang.'
            ],
            [
                'category' => 'private-office',
                'name' => 'Private Serviced Office',
                'price' => 'Negotiable',
                'period' => 'bulan',
                'desc' => 'Ruang kantor pribadi berfurnitur lengkap (furnished) untuk tim kecil hingga menengah.',
                'features' => [
                    'Ruangan Terkunci Mandiri & Private (Kapasitas 2-6 Orang)',
                    'Meja & Kursi Kerja Ergonomis Lengkap',
                    'Gratis Akses Wi-Fi Dedicated & LAN',
                    'Layanan Pembersihan Harian (Cleaning Service)',
                    'Bebas Biaya Utilitas (Listrik/AC/Air)',
                    'Free Kuota Penggunaan Meeting Room'
                ],
                'cta_wa' => 'Halo Urban Office, saya ingin survey Private Serviced Office di Cabang Malang.'
            ],
            [
                'category' => 'meeting-room',
                'name' => 'Small Meeting Room',
                'price' => '125.000',
                'period' => 'jam',
                'desc' => 'Ruangan rapat formal ber-AC dengan perlengkapan multimedia lengkap untuk presentasi.',
                'features' => [
                    'Kapasitas hingga 6 - 8 Orang',
                    'Smart TV LED HD / Proyektor',
                    'Whiteboard & Spidol Lengkap',
                    'Free Flow Drinks untuk Seluruh Peserta',
                    'High-Speed Wi-Fi'
                ],
                'cta_wa' => 'Halo Urban Office, saya mau sewa Meeting Room di Cabang Malang.'
            ],
            [
                'category' => 'event-space',
                'name' => 'Event Space (Half-Day)',
                'price' => '45.000',
                'period' => 'pax/4 jam',
                'desc' => 'Area luas dan fleksibel untuk seminar, workshop, talkshow, maupun gathering selama 4 jam.',
                'features' => [
                    'Kapasitas hingga 30 - 50 Orang',
                    'Sound System Standard & Wireless Mic',
                    'Proyektor Layar Besar',
                    'Layout Kursi Fleksibel',
                    'Fasilitas Penerimaan Tamu (Registrasi)'
                ],
                'cta_wa' => 'Halo Urban Office, saya ingin menanyakan sewa Event Space Half-Day di Cabang Malang.'
            ],
            [
                'category' => 'event-space',
                'name' => 'Event Space (Full-Day)',
                'price' => '90.000',
                'period' => 'pax/8 jam',
                'desc' => 'Sewa ruang serbaguna full day selama 8 jam untuk seminar korporat skala besar.',
                'features' => [
                    'Kapasitas hingga 30 - 50 Orang',
                    'Sound System Standard & Wireless Mic',
                    'Proyektor Layar Besar',
                    'Layout Kursi Fleksibel',
                    'Pantry & Lounge Support'
                ],
                'cta_wa' => 'Halo Urban Office, saya ingin menanyakan sewa Event Space Full-Day di Cabang Malang.'
            ],
            [
                'category' => 'sharing-room-office',
                'name' => 'Sharing Room Office Desk',
                'price' => '1.000.000',
                'period' => 'pax/bulan',
                'desc' => 'Meja kerja khusus dalam ruang kantor bersama yang tenang dengan harga hemat.',
                'features' => [
                    'Meja Kerja Khusus (Dedicated Desk)',
                    'Internet Wi-Fi Cepat',
                    'Layanan Penerimaan Resepsionis',
                    'Bebas Biaya IPL, Listrik, & Air',
                    'Free Flow Beverages (Teh/Kopi)'
                ],
                'cta_wa' => 'Halo Urban Office, saya tertarik menyewa Sharing Room Office di Cabang Malang.'
            ]
        ],
        'faq' => [
            [
                'question' => 'Apakah lokasi Malang memiliki fasilitas parkir?',
                'answer' => 'Ya, lokasi kami memiliki area parkir yang memadai untuk mobil dan motor.'
            ]
        ],
        'testimonial' => [
            'name' => 'Budi Santoso',
            'role' => 'Freelancer',
            'text' => 'Tempatnya nyaman banget buat kerja, koneksi internet stabil, dan free kopinya enak!',
            'rating' => 5,
            'image' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=150&q=80'
        ]
    ]
];
