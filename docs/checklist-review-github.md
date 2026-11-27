# Checklist Review Project Sebelum Upload ke GitHub

Hasil review project **Urban Office — Landing Page & CMS** sebelum push ke repo `urban-office-landing-page`. Diisi berdasarkan audit langsung terhadap isi file config, credential, ukuran file yang ter-track git, struktur folder, dependency, dan integrasi pihak ketiga.

---

## Project: Urban Office — Landing Page & CMS

### 1. Info Dasar
- Stack: [x] PHP Native &nbsp; [ ] Laravel
- Versi PHP yang dipakai: **8.3+** (lokal terpasang 8.3.16)
- Versi framework (jika Laravel): — (tidak pakai framework)
- Database: [x] MySQL &nbsp; [ ] MariaDB &nbsp; [ ] Lainnya — nama DB: `urban_office`, akses via PDO

### 2. Cek File Sensitif (WAJIB, jangan sampai ter-upload)
- [ ] Ada file `.env` dengan credential asli? → **Tidak ada.** Project ini memang tidak pakai pola `.env` sama sekali — semua konstanta didefinisikan langsung di `inc/config.php`.
- [x] Ada file config lain yang hardcode password/API key? → Ya, 2 lokasi:
  - `inc/config.php:49-53` — `DB_HOST=localhost`, `DB_USER=root`, `DB_PASS=''` (default lokal XAMPP/Laragon, **bukan** credential produksi asli)
  - `inc/importer.php:18-23` — pola sama (`WP_DB_*`), tool CLI sekali-pakai untuk migrasi dari WordPress, default `root`/kosong juga
  - Tidak ditemukan API key, SMTP credential, atau token asli di manapun di codebase.
- [ ] Ada file `auth.json`, credential Composer, atau token API tersimpan di kode? → Tidak ada (project tidak pakai Composer).

**Verdict:** aman untuk di-upload. Karena pola hardcode-constant (bukan `.env`), pastikan siapa pun yang clone tahu harus edit `inc/config.php` manual saat deploy — sudah dijelaskan di README & Installation.md.

### 3. Cek File Besar (Jangan Ikut Commit)
- [ ] Ada folder upload user (foto produk, dokumen, dll)? Lokasi: `uploads/` (dibuat runtime via konstanta `DIR_UPLOADS`)
  → Estimasi ukuran total: **0 (belum ada, dibuat otomatis saat runtime)** — sudah masuk `.gitignore`
- [ ] Ada dump/backup database (`.sql`)? Lokasi: **tidak ditemukan** di manapun dalam project
- [x] Ada video, gambar resolusi tinggi, atau asset media besar? Lokasi: `assets/images/` — **53 MB total, ikut ter-track git**. Ini konten situs asli (bukan hasil upload user), jadi memang wajar ikut commit. File terbesar cuma ~6.9MB, tidak ada masalah.
- [ ] Ada file tunggal di atas 100MB? → Tidak ada.

**Item tambahan ditemukan** — backup/duplikat lama, kecil tapi sudah dipastikan **tidak ter-track** git (`git ls-files` diverifikasi kosong untuk pattern ini):
| Item | Ukuran | Status |
|---|---|---|
| `admin.zip` | 48 KB | sudah di `.gitignore`, tidak ter-track |
| `perizinan-dan-perubahan-perusahaan.zip` | 12 KB | sudah di `.gitignore`, tidak ter-track |
| `test_zip/` (duplikat lama folder `admin/`) | 201 KB | sudah di `.gitignore`, tidak ter-track |

**Tindakan:** semua sudah masuk `.gitignore` dan terverifikasi aman. Rekomendasi tambahan: hapus fisik ketiga item di atas dari working directory karena sudah tidak relevan (lihat Catatan Tambahan).

### 4. Cek Struktur Folder
- [x] Struktur project custom (bukan standar framework, karena PHP native) — folder-per-halaman, setiap top-level folder = satu landing page mandiri dengan `index.php` sendiri.
- Pemetaan folder utama:
  ```
  inc/            → core: config, database (PDO singleton), functions, header/footer, seo, komponen view partial
  admin/          → CMS admin panel (auth, CRUD posts/categories/tags/leads/pages/redirects/users/settings, upload, logs)
  assets/         → css, js (termasuk CKEditor vendored), images
  blog/           → CMS blog publik (index, article, category, tag)
  cache/          → HTML page cache hasil compile + session storage (runtime, gitignored)
  [nama-fitur]/   → satu folder per landing page produk (virtual office, meeting room, coworking, dst — masing2 index.php + kadang detail.php)
  scratch/        → script one-off/debug lama (gitignored, sebaiknya dihapus fisik)
  router.php      → entry point dev server (php -S)
  .htaccess / nginx.conf → rewrite rules produksi (didefinisikan paralel, harus disunting bersamaan)
  ```
  Struktur lengkap per-file sudah didokumentasikan sangat detail di `README.md`.
- [x] Ada folder/file yang sudah tidak dipakai (dead code, file testing lama, folder backup manual)? → sebaiknya dihapus sebelum upload:
  - `scratch/` — 12 script one-off (download logo, cek warna gambar, dll) + 2 file `.txt` hasil search
  - `test_zip/` — duplikat lama folder `admin/`
  - `admin.zip`, `perizinan-dan-perubahan-perusahaan.zip` — backup snapshot lama

### 5. Dependency
- [ ] Ada `composer.json`? [ ] Ya &nbsp; [x] Tidak — tidak ada dependency PHP eksternal, murni PHP native.
- [ ] Ada `package.json`? [ ] Ya &nbsp; [x] Tidak — tidak ada build step frontend.
- [x] Ada dependency/library pihak ketiga yang di-include manual? → **CKEditor** (`assets/js/ckeditor.js`, ~4MB), di-vendor langsung tanpa CDN/npm, dipakai admin panel untuk editor blog. Sudah didokumentasikan di README.

### 6. Environment Variable yang Dipakai
Project ini **tidak pakai `.env`**, jadi tidak ada `.env.example` untuk dibuat. Sebagai gantinya, ini daftar konstanta di `inc/config.php` yang wajib disesuaikan per environment saat deploy:

| Nama Variable (konstanta) | Fungsi | Contoh Nilai (bukan yang asli) |
|---|---|---|
| `DB_HOST` / `DB_PORT` | Host & port MySQL | `localhost` / `3306` |
| `DB_NAME` | Nama database | `urban_office` |
| `DB_USER` / `DB_PASS` | Credential MySQL | `db_user_prod` / `********` |
| `DEV_MODE` | Toggle error display (true/false) | `false` di produksi |
| `WHATSAPP_NUMBER` | Nomor WA untuk tombol CTA | `6281234567890` |
| `BASE_URL` | Auto-detect dari `$_SERVER`, biasanya tidak perlu diubah manual | — |

### 7. Integrasi Pihak Ketiga
- [ ] Ada integrasi payment gateway? → **Tidak ditemukan.** Tidak ada integrasi payment gateway apa pun di codebase.
- [ ] Ada integrasi API eksternal lain (WhatsApp, email, maps, dll)? → Tidak ada integrasi API sungguhan yang ditemukan:
  - Tombol WhatsApp hanya link click-to-chat (`wa.me`) dengan nomor hardcoded — bukan API.
  - Form newsletter (`newsletter/index.php`) hanya `alert()` JS di sisi client, **belum benar-benar terhubung** ke backend/email service manapun.
- [ ] Ada cron job / scheduled task yang berjalan? → Tidak ada cron job eksplisit. Ada fungsi `auto_publish_scheduled_posts()` (`inc/functions.php:159`) yang dipanggil di **setiap page load** untuk auto-publish artikel terjadwal — bukan scheduled task sungguhan, tapi perilakunya mirip.

### 8. Status Kesiapan Upload
- [x] Sudah tidak ada file sensitif tersisa (credential yang ada hanya default lokal kosong, bukan credential produksi asli)
- [x] Sudah tidak ada file besar bermasalah tersisa (semua backup/zip sudah di-gitignore & terverifikasi tidak ter-track git)
- [x] `.gitignore` project ini sudah disesuaikan dari template — bukan generic, sudah cover `.env`, `cache/`, `uploads/`, ketiga file backup, `scratch/`, `*.bak`, dll secara spesifik
- [x] Siap untuk `git init` dan push ke repo `urban-office-landing-page` — bahkan repo ini **sudah punya remote** (`origin` → `github.com/Heri2803/urban-office-landing-page`) dengan 12 commit yang sudah ter-push sebelumnya

### Catatan Tambahan
- Kondisi kode secara umum **rapi dan terdokumentasi sangat baik** — `README.md`, `Installation.md`, `implementation.md`, dan `vps_installation_guide.md` semuanya lengkap dan up to date. Jarang ditemukan project PHP native dengan dokumentasi selengkap ini.
- Rekomendasi utama: hapus fisik `scratch/`, `test_zip/`, `admin.zip`, dan `perizinan-dan-perubahan-perusahaan.zip` dari working directory. Sudah aman (gitignored), tapi jadi clutter yang tidak perlu ikut disimpan di folder project.
- Folder `perizinan-dan-perubahan-perusahaan/` (versi live, bukan zip-nya) menurut catatan README adalah hasil copy dari `pendirian-pt-include-virtual-office` dan mungkin masih menyisakan sisa penamaan lama — bukan blocker upload, tapi worth di-cleanup kalau ada waktu luang.

---

## Ringkasan

| Project | File Sensitif Ditemukan | File Besar Ditemukan | Siap Upload? |
|---|---|---|---|
| Urban Office (Landing Page & CMS) | Tidak ada (hanya credential lokal default, sudah didokumentasikan) | Tidak ada di luar `.gitignore` (asset gambar 53MB wajar sebagai konten situs) | ✅ Ya |
