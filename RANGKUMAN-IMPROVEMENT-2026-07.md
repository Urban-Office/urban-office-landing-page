# Rangkuman Improvement — Urban Office (Sesi Juli 2026)

> **Tujuan utama:** Menaikkan **Quality Score** Google Ads (fokus kampanye Jakarta & layanan lain) dengan memperbaiki relevansi landing page per lokasi, sekaligus membuat konten data-driven dari satu sumber (`inc/locations_data.php`).

---

## 1. Baseline Google Ads (patokan "sebelum")
- Akun: **Urban Account (846-570-9843)** di bawah MCC **urban office (226-975-8268)**.
- Ditarik 2026-07-29 (30 hari). Disimpan di `google-ads-qs-baseline-2026-07-29.md`.
- Temuan kunci: VO Jakarta keyword "virtual office" = **QS 5, Landing Page Experience Below Average** (Ad Relevance sudah bagus → masalah murni di halaman). Cluster **Private Office** & **Meeting Room** juga banyak LPE *Below Average* dengan volume besar.

---

## 2. Virtual Office — perbaikan inti
- **SEO per-cabang** (`inc/seo.php`): title/description/canonical diambil dari `locations_data` per cabang (dulu semua jatuh ke default "Surabaya"). Bonus: memperbaiki bug SEO halaman `/lokasi-urban-office/{cabang}/` (require_once scope).
- **Pricing branch-aware & 3 tier untuk SEMUA cabang**: alamat, CTA WhatsApp, KPP mengikuti cabang; semua cabang tampil **3 tier identik** (Starter/Luxury/Priority) dari referensi Surabaya. Switcher JS default ke cabang di URL (bukan reset ke Surabaya).
- **Teks HTML crawlable** (P3): intro + daftar "Keunggulan Cabang" dari `advantages` per cabang.
- **Form** (P5): field `TANGGAL` tidak lagi wajib untuk VO/lead (tetap wajib untuk layanan kunjungan). CTA WhatsApp above-fold.
- **Kecepatan** (P4): lazy-load gambar slider.
- **Nama lokasi ikut URL** (SEO/SEM): hero H1/badge + dropdown pakai nama kota dari slug URL (mis. "Surabaya Timur", bukan "Klampis").

---

## 3. Dropdown "Ruang Kerja" — breakdown per kota
- Setiap opsi (Virtual Office, Private Office, Ruang Meeting, Coworking, Event Space, Sharing Room) kini punya **sub-flyout kota**, isinya **hanya kota yang benar-benar menyediakan** layanan itu — sumber: `locations_data.php`.
- VO = per-cabang (URL per-cabang). Layanan lain = per-kota.

---

## 4. URL per-kota nyata (SEM) — Private Office & Meeting Room
Meniru pola VO. Konten dibedakan lewat **section lokal** (alamat + peta + keunggulan cabang) agar tidak dianggap duplikat.

| Layanan | URL | Kota |
|---|---|---|
| Private Office | `/sewa-kantor-{kota}/` | Surabaya, Jakarta, Gresik, Malang |
| Meeting Room | `/meeting-room-{kota}/` | Surabaya, Jakarta, Gresik, Malang |

Tiap halaman: title/H1/canonical per kota, **gambar hero berubah per kota**, section "Lokasi {Layanan} di {Kota}" di bawah price list, masuk sitemap, dropdown pakai URL bersih, redirect `?lokasi=` lama ditangani via canonical.

> **Masih `?lokasi` (belum per-kota URL):** Coworking, Event Space, Sharing Room.

---

## 5. Perbaikan layout & UX
- **Section lokal (kartu cabang):** lebar kartu dibatasi & center (tidak melebar penuh saat 1–2 cabang), responsif desktop/tablet/mobile.
- **Halaman Virtual Office — dua kolom:** "Keuntungan" (image slider) di kiri + "Keunggulan Cabang" (advantages) di kanan, jadi satu section.
- **Peek slider:** slider gambar menampilkan potongan slide berikutnya (~13% desktop/tablet, 15% mobile), transform berbasis pixel, bisa mencapai slide terakhir.
- **Gambar hero per lokasi:** VO & Private Office & Meeting Room — gambar hero mengikuti foto cabang (dari field `image` di `locations_data`).

---

## 6. Pembersihan pelanggaran kebijakan Google Ads
**Kebijakan "Dokumen dan Layanan Resmi Pemerintah"** memicu pelanggaran karena halaman terlihat memfasilitasi dokumen resmi. Semua pemicu kuat dibersihkan di **seluruh cabang** (`index.php` + `locations_data.php`):
- Dihapus/diganti: NIB, Akta Notaris, NPWP, Kemenkumham, "Sertifikat/Surat Keterangan Domisili", "Legalitas 100%", "zonasi ... resmi", "pengurusan/registrasi PKP", "izin usaha", "Tax Consultation", dll → diganti bahasa **sewa kantor / alamat prestisius / kawasan strategis**.
- Terverifikasi: 8 halaman VO cabang + halaman lokasi detail = **0 pemicu kuat**.

**Yang masih perlu Anda lakukan (di luar website):**
1. Perbaiki **judul iklan** di Google Ads (mis. "Zonasi Perkantoran Resmi").
2. Setelah deploy → **ajukan Peninjauan Ulang** iklan.

---

## 7. Status Deployment (PENTING)
- Produksi = **Nginx (aaPanel)** → **`.htaccess` diabaikan**. `router.php` hanya untuk `php -S`.
- **Bug aktif:** URL per-kota (mis. `/sewa-kantor-jakarta/`) redirect ke beranda karena **rewrite belum ditambahkan ke konfigurasi Nginx produksi**.
- **Solusi (sedang dikerjakan):** tambahkan rewrite di aaPanel → **Website → urbanoffice.co.id → URL rewrite**, isi:
  ```nginx
  rewrite ^/virtual-office-([a-zA-Z0-9\-]+)/?$ /virtual-office-surabaya/index.php?branch=$1 last;
  rewrite ^/sewa-kantor-([a-zA-Z0-9\-]+)/?$ /sewa-kantor-surabaya/index.php?lokasi=$1 last;
  rewrite ^/meeting-room-([a-zA-Z0-9\-]+)/?$ /meeting-room-surabaya/index.php?lokasi=$1 last;
  ```
  Lalu Save (reload Nginx). *(`nginx.conf` di project sudah diupdate sebagai acuan, tapi yang berlaku adalah config aaPanel.)*
- Setelah upload file PHP: **hapus `cache/*.html`** (jangan hapus `cache/sessions/`) + **reset OPcache**.

---

## 8. File yang berubah (untuk deploy)
**Mesin bersama:**
`.htaccess` (Apache — tidak dipakai di Nginx), `nginx.conf` (acuan), `router.php` (php -S saja), `inc/functions.php`, `inc/seo.php`, `inc/header.php`, `inc/footer.php`, `inc/locations_data.php`, `inc/components/contact_form.php`, `assets/js/main.js`, `assets/css/style.css`, `sitemap.php`

**Halaman:**
`virtual-office-surabaya/index.php`, `sewa-kantor-surabaya/index.php`, `meeting-room-surabaya/index.php`, `coworking-space-urban-office/index.php`, `event-space-55k-perjam-urbanoffice/index.php`, `sharing-room-office/index.php`

**Sudah berubah sebelum sesi ini (cek jika belum live):** `admin/pages.php`, `inc/components/branches.php`, `lokasi-urban-office/detail.php`

**JANGAN di-upload:** `inc/config.php` (spesifik server), file `.md` (dokumen internal).

---

## 9. TODO / perlu verifikasi Anda
- [ ] **Nginx rewrite** produksi (aaPanel URL rewrite) — sedang dikerjakan.
- [ ] **Google Maps:** ganti `map_embed` per cabang dengan embed asli (Google Maps → Share → Embed). Sebagian masih perkiraan/koordinat.
- [ ] **Nama KPP:** Cakung & Gresik ada `// TODO: verifikasi`; Medan & Malang kosong.
- [ ] **Judul iklan Google Ads** + ajukan peninjauan ulang.
- [ ] (Opsional) URL per-kota untuk Coworking / Event Space / Sharing Room.
- [ ] (Opsional) Ganti testimoni stok Unsplash dengan review asli (marker `TESTIMONI: GANTI DENGAN REVIEW ASLI` di `contact_form.php`).
- [ ] (Opsional) Foto asli per cabang untuk hero & slider (agar tidak pakai foto gedung generik).
- [ ] Pantau **Landing Page Experience & Quality Score** di Google Ads 2–4 minggu setelah deploy; bandingkan dengan baseline.

---

*Belum ada perubahan yang di-commit ke git — semua masih di working tree. Disarankan commit agar deploy jelas & mudah rollback.*
