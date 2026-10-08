# ⭐ VERSI CALLBACK (terbaru) — 2 workflow

Approval sekarang pakai **tombol callback** (bukan Send&Wait), jadi: **tap Approve/Decline → tanpa popup**, bot **balas di chat**, dan **hero image dikirim** ke Telegram. Ada 2 file:

- `workflow.json` — **Generator** (berakhir dengan kirim foto hero + tombol Approve/Decline).
- `workflow-callback.json` — **Handler** (menangkap tap tombol → moderate → balas di chat).

### Cara pasang
1. **Import kedua file.**
2. Di **Generator** (`workflow.json`): isi node **Config** (siteBaseUrl, automationToken, telegramChatId, apiBase, apiModel, falModel) + pasang credential di **LLM Generate**, **Fal.ai Image**, **Telegram Kirim Preview**, **Notif: Tidak Ada Topik**, **Notif: Ditolak LLM**.
3. Di **Handler** (`workflow-callback.json`): isi node **Config** (siteBaseUrl, automationToken, telegramChatId) + pasang credential Telegram di **Telegram Trigger**, **Answer Callback**, **Kirim Balasan**.
4. **AKTIFKAN** workflow Handler (toggle Active) — wajib, supaya Telegram Trigger mendaftarkan webhook bot (butuh `WEBHOOK_URL`/cloudflared aktif). Generator cukup di-**Execute** (tidak perlu Active untuk testing).

### Alur
Generator kirim foto+tombol → Anda tap **Approve/Decline** (tanpa popup) → Handler jalan → `Answer Callback` (hentikan loading) → `Moderate` (publish/reject) → `Kirim Balasan` ("✅ Disetujui & dipublish: <url>" / "❌ Ditолak").

### ⚠️ Node yang perlu diverifikasi di UI (tidak bisa saya tes dari luar n8n)
Parameter node Telegram bisa beda antar-versi. Kalau ada yang tidak ter-set otomatis, set manual:
- **Telegram Kirim Preview** (sendPhoto): Binary Data = **ON**, Binary Property = `data`; Reply Markup = **Inline Keyboard**, 2 tombol dengan **callback_data** `approve:{{ $('Ingest Draft').item.json.post_id }}` dan `reject:{{ ... }}`.
- **Get Hero Image** (HTTP): Response Format = **File**, output ke properti `data`.
- **Telegram Trigger**: Updates = **callback_query**; workflow harus **Active**.
- **Answer Callback**: Resource **Callback** → Operation **Answer Query**; Query Id dari `Parse Callback`.
- Bot hanya boleh punya **1 webhook** — dipegang oleh Handler. Jangan ada Telegram Trigger lain aktif untuk bot yang sama.

> Catatan: tombol tetap ada setelah di-tap. `moderate.php` idempoten untuk approve (aman kalau ter-tap 2x). Kalau mau tombol hilang setelah dipakai, bisa ditambah node Edit Message (opsional).

---

# (LAMA) Import & Setup — workflow.json (n8n)

File `automation/workflow.json` = workflow lengkap yang tinggal di-import.
Alur: Schedule → taxonomy+branches → RSS (6 feed) → pilih topik + cek dedup → rotasi 70/30 → LLM → Fal.ai → ingest draft → **Telegram Approve/Reject (Send & Wait)** → moderate.

> Butuh **n8n ≥ 1.15** (untuk node Telegram "Send and Wait for Response").

---

## 1. Import
n8n → menu **⋮ → Import from File** → pilih `workflow.json`.

## 2. Isi node **Config** (klik node Config)
| Field | Local (testing) | Production |
|---|---|---|
| `siteBaseUrl` | `http://host.docker.internal:8000` | `https://urbanoffice.co.id` |
| `automationToken` | token dari `php _api/_setup_token.php` | token production (setup ulang di VPS) |
| `apiBase` | `https://api.groq.com/openai/v1` | `https://api.deepseek.com` |
| `apiModel` | `llama-3.3-70b-versatile` | `deepseek-chat` |
| `temperature` | `0.6` | `0.6` |
| `falModel` | `fal-ai/flux/schnell` | (sama / model FLUX lain) |
| `telegramChatId` | chat id approver | chat id approver |
| `systemPrompt` | (sudah terisi — boleh diedit) | — |

> `host.docker.internal` = cara container n8n menembus ke host. Di VPS Linux tambahkan `--add-host=host.docker.internal:host-gateway` saat run container, atau pakai domain production langsung.

## 3. Buat & pasang 3 credential
n8n tidak menyimpan API key di dalam file (aman). Buat di **Credentials**, lalu assign ke node:

1. **LLM (Groq/DeepSeek)** → tipe **Header Auth**
   - Name: `Authorization`  ·  Value: `Bearer <API_KEY>`
   - Pasang di node **LLM Generate**.
2. **Fal.ai** → tipe **Header Auth**
   - Name: `Authorization`  ·  Value: `Key <FAL_KEY>`
   - Pasang di node **Fal.ai Image**.
3. **Telegram** → tipe **Telegram API** (bot token dari @BotFather)
   - Pasang di 3 node Telegram: **Telegram Approval**, **Notif: Tidak Ada Topik**, **Notif: Ditolak LLM**.

> Token endpoint kita (`X-Automation-Token`) sudah dilewatkan otomatis dari node Config ke semua node `_api/*`. Untuk keamanan lebih, Anda boleh memindahkannya ke Header Auth credential tersendiri.

## 4. Uji jalan (fase Testing)
1. Pastikan server PHP lokal jalan: `php -S 0.0.0.0:8000 router.php` (pakai `0.0.0.0` agar terjangkau dari container, bukan `127.0.0.1`).
2. Di n8n klik **Execute Workflow** (jangan aktifkan Schedule dulu).
3. Cek pesan preview masuk Telegram → klik **Approve/Reject**.
4. Verifikasi: draft muncul di `/admin/posts.php`; setelah approve → tampil di `/blog/`.

## 5. Catatan operasional
- **Tombol Approve/Reject** memakai mekanisme *wait/webhook* n8n. n8n harus punya `WEBHOOK_URL` yang bisa diakses browser saat Anda klik tombol. Untuk testing lokal, klik dari browser di mesin yang sama (bisa menjangkau n8n). Di production set `WEBHOOK_URL` ke domain n8n Anda.
- **Node "Telegram Approval"**: jika setelah import operationnya tidak ter-set (beda versi n8n), buka node → pilih Resource **Message** → Operation **Send and Wait for Response** → Response Type **Approval**.
- **Ganti ke Production**: cukup ubah `apiBase` + `apiModel` di Config dan ganti value credential LLM ke DeepSeek key. Node lain tidak berubah.
- **Biaya**: `check.php` dipanggil sebelum LLM untuk skip duplikat (hemat). Fal.ai `flux/schnell` murah untuk testing.
- **Regenerasi file**: `php automation/build-workflow.php` menulis ulang `workflow.json` (mis. jika ingin ubah daftar feed / prompt di generator).

## 6. Ringkas mapping node → endpoint
| Node | Endpoint |
|---|---|
| GET Taxonomy | `GET /_api/taxonomy.php` |
| GET Branches | `GET /_api/branches.php` |
| Pick Topic (internal) | `GET /_api/check.php` (via Code) |
| Ingest Draft | `POST /_api/ingest.php` |
| Moderate Approve/Reject | `POST /_api/moderate.php` |
