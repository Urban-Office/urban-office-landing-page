# Urban Office — Landing Page & CMS

Website Urban Office (PT. Urban Kreasi Bersama) — dibangun ulang dari WordPress ke PHP 8.3+ murni (tanpa framework) dan MySQL 8, dengan CMS admin custom untuk blog, leads, dan halaman SEO.

Stack: PHP murni (folder-based routing), MySQL 8 via PDO, Vanilla CSS/JS. Tidak ada Composer/npm — semua dependency (CKEditor) di-vendor langsung di `assets/js/`.

## Menjalankan secara lokal

```bash
php router.php   # dev server bawaan PHP: php -S localhost:8000 router.php
php inc/init_db.php   # bootstrap skema DB + seed admin (lihat output terminal untuk password)
```

Konfigurasi ada di [inc/config.php](inc/config.php) (bukan `.env` — lihat catatan keamanan di bagian bawah).

---

## Peta Folder & Modul

Tidak ada router terpusat untuk halaman fitur — setiap folder top-level punya `index.php` sendiri yang di-serve langsung oleh Apache (`.htaccess`) atau `router.php` (dev server), berdasarkan pencocokan path filesystem. Hanya beberapa route dinamis (blog, lokasi, virtual-office-{branch}) yang punya rewrite rule eksplisit — didefinisikan **dua kali** secara paralel: di [.htaccess](.htaccess) (produksi/Apache) dan [router.php](router.php) (dev server). Keduanya harus disunting bersamaan kalau menambah route dinamis baru.

### Shared core (`inc/`)

| File | Peran | Tanggung jawab |
|---|---|---|
| [inc/config.php](inc/config.php) | Bootstrap | Konstanta (`BASE_URL`, `DB_*`), toggle error reporting via `DEV_MODE`, session hardening, generate CSRF token, **redirect-map interceptor** (jalan di setiap request) |
| [inc/database.php](inc/database.php) | Model | Kelas `Database` — PDO singleton dengan helper statis `query/fetch/fetchAll/insert`, dipakai semua modul lain |
| [inc/functions.php](inc/functions.php) | Service | `sanitize()`, `is_admin()`/`check_admin_auth()`, CSRF helpers, `minify_html()`, `log_activity()`, page-file-cache (`start_page_cache`/`end_page_cache`/`clear_page_cache`), `auto_publish_scheduled_posts()` |
| [inc/header.php](inc/header.php) | View | `<head>` + navbar bersama; wiring config/database/functions/seo/locations_data; bangun dropdown nav cabang VO; mulai page cache |
| [inc/footer.php](inc/footer.php) | View | Footer, social links, widget WhatsApp mengambang; tutup buffer page cache |
| [inc/seo.php](inc/seo.php) | Service | `get_page_seo()`/`render_seo_tags()`/`render_schema_markup()` — resolve meta title/description/OG/canonical/JSON-LD per halaman |
| [inc/locations_data.php](inc/locations_data.php) | Model | Array statis `$locations_db` (~1000 baris) — satu entry per cabang: alamat, rating, galeri, `advantages`, `services`, `pricing`, `seo` |
| [inc/lead_handler.php](inc/lead_handler.php) | Controller | Endpoint AJAX POST (JSON) untuk form leads — validasi CSRF + honeypot, sanitasi input, insert ke tabel `leads` |
| [inc/components/*.php](inc/components/) | View partials | `hero.php`, `features.php`, `pricing_cards.php`, `faq.php`, `branches.php`, `locations_showcase.php`, `brands_slider.php`, `contact_form.php`, `app_section.php` — masing-masing include fragment yang mengharapkan variabel dari pemanggil |
| [inc/init_db.php](inc/init_db.php) | CLI tool | **Bukan bagian request path.** Bootstrapper skema DB, dijalankan manual via `php inc/init_db.php` saat setup awal |
| [inc/importer.php](inc/importer.php) | CLI tool | **Bukan bagian request path.** Migrator WordPress → skema baru (posts/categories/tags/Yoast SEO), dijalankan manual, lihat [Installation.md](Installation.md) |

### Halaman fitur (folder-based routing)

Setiap folder berikut = satu landing page mandiri dengan `index.php` sendiri (kecuali disebutkan lain):

- **about** — halaman "Tentang Kami"
- **blog** — CMS blog (`index.php` listing, `article.php` single post, `category.php`, `tag.php`) — baca dari tabel `posts` yang sama dengan yang ditulis `admin/posts.php`
- **coworking-space-urban-office**, **event-space-55k-perjam-urbanoffice**, **sharing-room-office** — landing page produk masing-masing
- **gallery** — galeri foto
- **kemitraan-urban-office** — landing program kemitraan/franchise
- **lokasi-urban-office** — direktori cabang (`index.php`) + detail per cabang (`detail.php`), datanya dari `inc/locations_data.php`
- **meeting-room-surabaya** — landing + detail Meeting Room
- **newsletter** — halaman subscribe newsletter
- **pajak-dan-akunting** — landing jasa pajak & akunting
- **pendirian-cv-virtual-office**, **pendirian-perorangan-plus-virtual-office**, **pendirian-pma-plus-virtual-office**, **pendirian-pt-include-virtual-office** — landing page paket pendirian badan usaha + VO
- **perizinan-dan-perubahan-perusahaan** — landing jasa perizinan & perubahan perusahaan (lihat catatan di bawah — hasil copy dari `pendirian-pt-include-virtual-office`, kontennya sudah beda tapi ada sisa penamaan dari asalnya)
- **sewa-kantor-surabaya** — landing + detail Private Office. **Punya data pricing sendiri** (`$types_data` lokal di `detail.php`) — tidak memakai `inc/locations_data.php`, jangan disamakan
- **urban-office-karir** — halaman karir
- **virtual-office-surabaya** — landing Virtual Office cabang MERR; juga jadi **template bersama** untuk semua landing cabang VO lain lewat route dinamis `/virtual-office-{branch}/`

### Admin panel (`admin/`)

Tidak ada router admin — setiap file adalah script berdiri sendiri yang langsung diakses via URL, dan masing-masing `require_once admin/auth.php` di baris paling atas untuk self-enforce login (kecuali `index.php` dan `logout.php`, yang menangani sendiri kondisi pra-login/logout).

| File | Peran |
|---|---|
| [admin/auth.php](admin/auth.php) | Middleware — enforce login via `check_admin_auth()`, di-`require` semua halaman admin lain |
| [admin/index.php](admin/index.php) | Login form + dashboard metrics/recent leads |
| [admin/posts.php](admin/posts.php) | CRUD blog (create/update/delete/list), integrasi CKEditor |
| [admin/categories.php](admin/categories.php), [admin/tags.php](admin/tags.php) | CRUD taksonomi blog |
| [admin/leads.php](admin/leads.php) | Kelola/lihat data leads dari `inc/lead_handler.php` |
| [admin/pages.php](admin/pages.php) | CRUD metadata SEO halaman statis (dikonsumsi `inc/seo.php`) |
| [admin/redirects.php](admin/redirects.php) | CRUD tabel `redirects` (dikonsumsi interceptor di `inc/config.php`) |
| [admin/media.php](admin/media.php) | Manajer media library — **tidak ada link menu di `sidebar.php`**, hanya bisa diakses via URL langsung |
| [admin/users.php](admin/users.php) | CRUD user, digerbang tambahan: hanya role `administrator` |
| [admin/settings.php](admin/settings.php) | Editor setting global situs |
| [admin/logs.php](admin/logs.php) | Audit trail dari `activity_logs` |
| [admin/upload.php](admin/upload.php) | Endpoint JSON upload gambar untuk editor CKEditor di `posts.php` |
| [admin/sidebar.php](admin/sidebar.php), [admin/logout.php](admin/logout.php) | Partial nav bersama; handler logout |

---

## Flow yang paling gampang bikin bingung

### 1. Redirect interceptor jalan di *setiap* request, sebelum apa pun di-render

Di [inc/config.php:105-130](inc/config.php:105), setiap request non-CLI di-cek dulu ke tabel `redirects` sebelum halaman dibangun — karena `config.php` di-include (transitif lewat `header.php`/`auth.php`) oleh **semua** halaman. Kalau ada baris di tabel `redirects` yang cocok dengan path saat ini, halaman langsung `header('Location: ...')` + `exit`, dan sisa kode di halaman itu tidak pernah jalan. Kegagalan query (misal tabel belum ada) di-*swallow* diam-diam lewat try/catch — jadi kalau redirect "tidak jalan" padahal sudah didaftarkan di admin, cek dulu apakah tabelnya benar-benar ada isinya, bukan asumsikan errornya akan terlihat.

### 2. Page cache file-based, dengan kondisi bypass yang harus dihafal

`inc/header.php` memanggil `start_page_cache()` dan `inc/footer.php` memanggil `end_page_cache()` ([inc/functions.php:95-131](inc/functions.php:95)). HTML hasil compile disimpan sebagai file `.html` di `/cache/`, keyed by `md5($page_slug)`. Cache **dilewati** (selalu render fresh) kalau salah satu benar: `DEV_MODE` aktif, user sedang login sebagai admin, atau request-nya POST. Ini penting dipahami sebelum debug "kenapa perubahan saya di halaman tidak muncul" — kemungkinan besar itu HTML lama yang ke-serve dari cache, bukan bug di kode. `clear_page_cache()` dipanggil manual dari dashboard admin ("clear cache").

### 3. Blog CMS dan halaman publik berbagi tabel `posts` yang sama, plus auto-publish yang jalan diam-diam

`admin/posts.php` (INSERT/UPDATE/DELETE) dan `blog/article.php`/`blog/index.php` (SELECT, filter `status = 'published'`) membaca-menulis tabel `posts` yang identik. Tidak ada tombol "Publish" terpisah untuk artikel terjadwal — `auto_publish_scheduled_posts()` ([inc/functions.php:159-183](inc/functions.php:159)) dipanggil di **setiap** page load (baik publik lewat `header.php`, maupun admin lewat `auth.php`), dan mengubah status dari `scheduled` ke `published` begitu `published_at` terlewati. Lihat juga bagian validasi bisnis di bawah — ada aturan catch-up tambahan untuk draft lama yang gampang salah paham.

---

## Validasi bisnis yang gampang salah kalau disentuh tanpa tahu detailnya

- **Auto-publish punya dua kondisi terpisah, jangan digabung** ([inc/functions.php:159-183](inc/functions.php:159)): query pertama mem-publish post `status = 'scheduled'` begitu `published_at` lewat — itu wajar. Query kedua adalah *catch-up* khusus untuk post lama `status = 'draft'` yang `published_at`-nya di masa lalu **dan** `created_at`-nya sebelum hari ini (`DATE(created_at) < CURDATE()`). Syarat `created_at < CURDATE()` ini sengaja dipasang supaya draft yang baru dibuat hari ini dengan `published_at` di masa lalu (misal admin salah isi tanggal) **tidak** langsung auto-publish. Kalau syarat ini dihapus karena dikira redundan, efeknya draft yang sedang ditulis hari ini bisa tiba-tiba tayang ke publik tanpa sengaja.

- **CSRF token satu per sesi, bukan per-request** ([inc/config.php:100-103](inc/config.php:100), [inc/functions.php:51-56](inc/functions.php:51)): token digenerate sekali saat sesi mulai (`if (empty($_SESSION['csrf_token']))`) dan dipakai ulang untuk semua form selama sesi itu berjalan, divalidasi via `hash_equals()`. Jangan berasumsi token berubah tiap submit — kalau butuh rotasi token per-aksi, itu bukan behavior yang ada sekarang.

- **Honeypot di form leads jangan dihapus/di-rename tanpa update JS-nya**: `inc/lead_handler.php:39` menolak submission secara *silent* (tetap balas `success: true` biar bot tidak tahu ditolak) kalau field tersembunyi `email_confirm` terisi. Field ini didefinisikan di [inc/components/contact_form.php](inc/components/contact_form.php) dan harus tetap kosong secara visual (hidden via CSS, bukan `display:none` polos yang gampang dideteksi bot) — kalau nama field diganti di satu sisi tapi tidak di sisi lain, honeypot berhenti berfungsi tanpa ada error yang kelihatan.

- **Field wajib leads divalidasi manual, bukan lewat skema DB** ([inc/lead_handler.php:54](inc/lead_handler.php:54)): `name`, `email` (harus lolos `FILTER_VALIDATE_EMAIL`), `phone`, `service` wajib tidak kosong; `message` **opsional**. Kalau menambah kolom wajib baru di tabel `leads`, validasi manual ini harus disentuh juga — tidak otomatis ter-enforce oleh constraint DB.

- **`users.php` role gate**: hanya role `administrator` yang boleh CRUD user lain — role lain (`editor`, dst.) hanya login-gated via `admin/auth.php` tapi tidak boleh masuk `admin/users.php`. Jangan asumsikan "sudah login admin" = "boleh kelola user".

- **`sewa-kantor-surabaya` tidak memakai `inc/locations_data.php`** — folder ini punya `$types_data` sendiri di `detail.php`-nya. Kalau update harga/fasilitas cabang lewat `locations_data.php`, halaman Private Office ini **tidak ikut berubah** — harus diedit terpisah.

---

## Catatan keamanan

- Tidak ada `.env` — kredensial DB (`DB_USER`/`DB_PASS`) hardcoded di [inc/config.php](inc/config.php). Untuk deployment produksi, ganti nilainya sesuai panduan di [vps_installation_guide.md](vps_installation_guide.md).
- Password admin default sekarang **digenerate acak** saat `inc/init_db.php` dijalankan (dicetak sekali ke terminal, tidak disimpan di tempat lain selain hash-nya di DB) — segera ganti lewat `/admin` setelah login pertama.
- `admin.zip`, `perizinan-dan-perubahan-perusahaan.zip`, `test_zip/`, `scratch/`, `cache/`, dan file `*.bak` sengaja tidak ikut version control (lihat `.gitignore`) — semuanya backup/scratch artifact yang redundant dengan source yang sudah ter-track.
