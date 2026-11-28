# Next Development Backlog — Urban Office CMS

Catatan pengembangan lanjutan yang disepakati setelah rilis hardening admin & SEO Audit v1 (`2026-11-27`).

---

## 1. Integrasi Google Search Console (GSC) API Gratis (Prioritas Utama)

**Tujuan:** Menggantikan input angka GSC manual di `admin/pages.php` dengan sinkronisasi otomatis dari Google Search Console API (`Search Analytics`), 100% gratis tanpa billing/kartu kredit dan tanpa Composer.

### Arsitektur Ringkas
- **Autentikasi:** Google Cloud **Service Account** (JWT ditandatangani pakai `openssl_sign()` bawaan PHP 8.3 → tukar OAuth2 access token via `curl`).
- **Keamanan credential:** File JSON Service Account disimpan **di luar webroot VPS** (mis. `/www/server/gsc-urbanoffice.json`, tidak pernah masuk Git). Path & property URL dikonfigurasi di `inc/config.php` server:
  ```php
  define('GSC_SERVICE_ACCOUNT_JSON', '/www/server/gsc-urbanoffice.json');
  define('GSC_SITE_URL', 'sc-domain:urbanoffice.co.id');
  ```
- **Akses GSC:** Tambahkan email Service Account sebagai **Restricted User** (read-only) di GSC → *Settings → Users and permissions*.

### Komponen yang Dibangun
1. `inc/gsc_client.php` — helper pure PHP (`gsc_get_access_token()`, `gsc_query_page_daily()`, `gsc_query_page_top_queries()`).
2. `inc/init_db.php` — tabel baru `page_gsc_daily` (`page_id`, `stat_date`, `impressions`, `clicks`, `ctr`, `position`, unique `(page_id, stat_date)`) + kolom opsional `last_gsc_sync_at` di `settings`.
3. `inc/sync_gsc.php` — runner CLI (untuk cron harian aaPanel, misal jam 06:00 WIB) & fungsi bersama yang bisa dipanggil dari tombol admin. Tarik 1–2 query per hari untuk seluruh 36 halaman (`dimensions: ['page', 'date']`) plus backfill historis 90–180 hari saat sync pertama.
4. `admin/pages.php`:
   - Tombol **Sync GSC Sekarang** (POST + CSRF) + timestamp terakhir sync.
   - Mode **Before vs After GSC otomatis** di halaman Audit (termasuk data historis sebelum 20 Nov 2026) + grafik harian `GSC Impr/Klik` & `GSC CTR/Posisi` langsung dari `page_gsc_daily`.
   - Sederhanakan panel kanan ketika GSC API aktif: sembunyikan input angka manual GSC; sisakan **Siklus Next Audit** (`14/30/60/90 hari`) + **Catatan Evaluasi / Action Item** + tombol **Selesaikan Audit & Reset Jadwal** (otomatis mengunci snapshot GSC & first-party saat itu ke `page_seo_audits`).
   - *(Opsional)* Tampilkan tabel ringkas **Top 10 Keyword/Query** per halaman dari GSC.

---

## 2. Sinkronisasi `nginx.conf` Repo dengan aaPanel Produksi (Minor)
- Samakan blok proteksi di `nginx.conf` repo (`location = /docs`, `^~ /docs/`, `^~ /automation/`, `^~ /.agents/`, `^~ /.github/`, `~* \.md$`) agar konsisten dengan config aaPanel produksi yang sudah aktif.
