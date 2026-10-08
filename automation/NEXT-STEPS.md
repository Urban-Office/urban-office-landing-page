# Automation — Breakdown Next Steps (ditunda)

Fitur yang sudah disepakati tapi belum dipasang. Backend-nya sudah siap; tinggal edit n8n manual saat mau dipakai.

---

## 1. Reject → Generate topik baru (auto-regenerate) — DITUNDA

**Status backend:** ✅ selesai. `_api/_bootstrap.php` sudah diubah supaya `source_url` yang pernah dipakai (termasuk status `rejected`) TIDAK dipilih ulang → regenerate pasti dapat topik berbeda.

**Yang belum:** edit n8n (2 bagian).

### Bagian A — Generator: tambah pintu masuk
1. Buka workflow **Generator** ("Urban Office - Auto Artikel SEO/AIO/GEO").
2. Tambah node **"Execute Workflow Trigger"** (n8n: *"When Executed by Another Workflow"*).
3. Sambungkan output-nya → node **Config** (node yang sama yang disambung Schedule). Config akan punya 2 input.
4. Simpan.

### Bagian B — Handler: reject → panggil Generator
Alur handler sekarang: `... → Moderate → Kirim Balasan`. Tambahkan:
1. Node **IF** setelah **Kirim Balasan**:
   - Sambung: `Kirim Balasan → IF`
   - Condition (String): Value1 `{{ $('Parse Callback').item.json.action }}` · **equals** · Value2 `reject`
2. Node **Execute Workflow**:
   - Sambung: **IF (true) → Execute Workflow**
   - Source: From list → pilih workflow **Generator**.
3. (Opsional) Ubah teks reject di **Kirim Balasan** jadi "❌ Ditolak — mencari topik baru...".
4. Simpan, pastikan handler tetap Active.

**Efek:** tap Decline → tandai rejected → balas di chat → jalankan Generator lagi (pilih topik BEDA) → kirim foto+tombol baru. Approve → berhenti (tidak regenerate). Kalau topik habis, Generator berhenti di "tidak ada topik relevan".

**Pertimbangan:** tiap reject = 1 generate penuh (biaya RSS+LLM+Fal). Self-limit saat topik habis.

---

## 2. Reminder operasional (trycloudflare)
Tiap `cloudflared` restart → URL tunnel berubah → **wajib**:
1. Stop n8n → `set WEBHOOK_URL=<tunnel-baru>` → start n8n.
2. Handler → toggle Active OFF/ON (biar webhook Telegram terdaftar ulang).
3. Cek `https://api.telegram.org/bot<TOKEN>/getWebhookInfo` → field `url` harus terisi tunnel baru.

Untuk produksi: pakai **domain tetap** (bukan quick tunnel) supaya webhook stabil.

---

## 3. Deploy production (belum)
- Upload `_api/`, `inc/init_db.php`, `assets/fonts/`, template hero `assets/images/imgcomponent/seo tren startup 2026.png`.
- Jalankan `php inc/init_db.php` (kolom source_url), `php automation/seed_tags.php`, `php automation/consolidate_categories.php` (script konsolidasi 17→8 — BELUM dibuat).
- `php _api/_setup_token.php <author_id>` → token production.
- (opsional) `php _api/_setup_cloudinary.php <cloud_name> <api_key> <api_secret>` → aktifkan CDN gambar di production juga.
- Config n8n: `siteBaseUrl` → domain, `apiBase/apiModel` → DeepSeek, credential LLM → DeepSeek key.
- Aktifkan Schedule generator.
