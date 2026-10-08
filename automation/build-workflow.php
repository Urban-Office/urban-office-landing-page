<?php
/**
 * Generator untuk automation/workflow.json (n8n importable).
 * Jalankan: php automation/build-workflow.php
 * Memakai json_encode agar escaping dijamin valid.
 */

$SYSTEM_PROMPT = <<<'SYS'
Kamu adalah content strategist senior Urban Office — penyedia Virtual Office, Coworking Space, Private Office, Meeting Room, Event Space, dan layanan legalitas usaha di Indonesia (cabang di Surabaya, Jakarta, Gresik, Malang, Medan).

TUGAS: menulis SATU artikel blog berbahasa Indonesia yang ORISINAL dan informatif, dioptimalkan untuk SEO, AI Overview (AIO), dan Generative Engine Optimization (GEO). Topik sumber dari RSS hanya PEMICU sudut pandang — DILARANG menyalin, menerjemahkan, atau merangkum ulang isi berita sumber (alasan hak cipta & E-E-A-T). Tulis dari sudut pandang Urban Office dengan insight bisnis sendiri.

=== ATURAN SEO ON-PAGE ===
- meta_title: maksimal 60 karakter, mengandung kata kunci utama secara NATURAL.
- meta_description: maksimal 155 karakter, ada CTA halus.
- slug: pendek, lowercase, dipisah tanda hubung.
- Heading pakai <h2> dan <h3> POLOS (tanpa atribut style). Konten dibungkus otomatis oleh .article-body-content yang sudah memberi styling.
- JANGAN pakai H1 (judul dirender terpisah).
- JANGAN sisipkan <img> di dalam content (featured image dirender otomatis di atas artikel oleh sistem).
- Minimal 1 (idealnya 2-3) internal link ke halaman di daftar LINK INTERNAL — pakai URL PERSIS, anchor natural. Ikuti gaya situs: sertakan minimal satu baris "Baca Juga: <a href=\"URL\">anchor lowercase</a>" selain link natural di dalam paragraf.
- HINDARI keyword stuffing (kata kunci utama 2-3x natural). Prioritaskan keterbacaan.
- Panjang artikel 700-1100 kata.

=== ATURAN AIO ===
- Paragraf pembuka LANGSUNG menjawab inti topik dalam 2-3 kalimat (tanpa basa-basi).
- Gunakan daftar berpoin (<ul><li>) untuk info yang mudah di-extract.
- Akhiri dengan FAQ 2-3 pertanyaan bila relevan: <h2>Pertanyaan yang Sering Diajukan</h2> lalu tiap pertanyaan <h3>...</h3> diikuti <p> jawaban.

=== ATURAN GEO ===
- Sebut entitas spesifik (area/cabang, kota, landmark) HANYA bila datanya diberikan.
- Sertakan angka/data konkret (mis. harga mulai Rp385.000/bulan) HANYA dari data yang diberikan — jangan mengarang harga/alamat.
- Selipkan insight khas Urban Office tentang relevansi tren terhadap kebutuhan ruang kerja fleksibel / legalitas usaha di Indonesia.
- Untuk artikel "branch": WAJIB pakai fakta cabang (alamat, keunggulan, harga) dari DATA CABANG dan link ke halaman cabang/produk terkait.

=== GUARDRAIL (WAJIB) ===
- DILARANG frasa "Gratis Pembuatan PT" atau klaim jasa dokumen resmi (PT/CV/PMA/izin) yang GRATIS/PASTI/instan tanpa disclaimer.
- DILARANG klaim medis, hukum, atau finansial spesifik tanpa dasar.
- Jika topik menyinggung politik praktis, SARA, konten sensitif, ATAU tidak relevan dengan Urban Office → kembalikan {"reject": true, "reject_reason": "<alasan>"} dan kosongkan field lain.
- categories dan tags HANYA dari daftar yang diberikan. JANGAN membuat baru. Pilih 1-2 kategori paling tepat dan 6-10 tag paling relevan.

=== FORMAT OUTPUT (WAJIB) ===
Keluarkan HANYA satu objek JSON valid (tanpa teks lain, tanpa markdown fence):
{
  "reject": false,
  "reject_reason": "",
  "title": "Judul (bukan clickbait)",
  "slug": "slug-pendek",
  "excerpt": "Ringkasan 1-2 kalimat.",
  "content": "<h2>...</h2><p>...</p>",
  "meta_title": "<= 60 karakter",
  "meta_description": "<= 155 karakter",
  "categories": ["<dari daftar>"],
  "tags": ["<dari daftar>"],
  "cta": "1 kalimat soft-selling HALUS yang mengaitkan topik artikel dengan produk/layanan Urban Office yang relevan (mengundang, tidak memaksa, tanpa klaim gratis/pasti)",
  "image_prompt": "English cover image prompt",
  "internal_links_used": ["/url-dipakai/"]
}

image_prompt: Bahasa Inggris, foto sampul profesional realistis (modern office/coworking/suasana bisnis Indonesia), fotografis, cahaya natural, 16:9, TANPA teks/logo. content = HTML fragment (tanpa <html>/<body>), TANPA <img>, hanya <h2>/<h3>/<p>/<ul>/<ol>/<li>/<strong>/<em>/<a href>.
SYS;

// ---- Code node JS bodies -----------------------------------------------------

$PICK_TOPIC_JS = <<<'JS'
const cfg = $('Config').first().json;
const tax = $('GET Taxonomy').first().json;
const branches = ($('GET Branches').first().json.branches) || [];
const items = $input.all().map(i => i.json);

const KW = ['virtual office','coworking','private office','sewa kantor','ruang kerja','kantor virtual','umkm','startup','wirausaha','ekspansi bisnis','legalitas','pt perorangan','pma','wfa','work from anywhere','ruang meeting','event space','produktivitas','pajak','npwp','izin usaha','bisnis','ekonomi','investasi'];
function score(it){
  const text = ((it.title||'') + ' ' + (it.contentSnippet||it.content||'')).toLowerCase();
  let s = 0;
  for (const k of KW){ if (text.includes(k)) s += (k.length > 10 ? 2 : 1); }
  return s;
}
let scored = items.map(it => ({it, s: score(it)})).filter(x => x.s > 0).sort((a,b) => b.s - a.s);
const seen = new Set();
scored = scored.filter(x => { const t = (x.it.title||'').toLowerCase().trim(); if(!t||seen.has(t)) return false; seen.add(t); return true; });

let chosen = null;
for (const x of scored.slice(0, 10)) {
  const link = x.it.link || '';
  const title = x.it.title || '';
  try {
    const res = await this.helpers.httpRequest({
      method: 'GET',
      url: cfg.siteBaseUrl + '/_api/check.php',
      qs: { source_url: link, title: title },
      headers: { 'X-Automation-Token': cfg.automationToken },
      json: true,
    });
    if (res && res.duplicate) continue;
  } catch (e) { /* jika check gagal, izinkan lanjut */ }
  chosen = x.it; break;
}
if (!chosen) return [{ json: { hasTopic: false } }];

const doy = Math.floor(Date.now() / 86400000);
const isBranch = Math.random() < 0.30;
let target_type = 'general', branch = null;
if (isBranch && branches.length) {
  const priority = ['surabaya','jakarta','malang','surabaya-timur','surabaya-barat','jakarta-timur','gresik','medan'];
  const ordered = priority.map(k => branches.find(b => b.slug === k)).filter(Boolean);
  const pool = ordered.length ? ordered : branches;
  branch = pool[doy % pool.length];
  target_type = 'branch';
}
return [{ json: {
  hasTopic: true,
  topic_title: chosen.title || '',
  topic_summary: (chosen.contentSnippet || chosen.content || '').toString().replace(/<[^>]+>/g,'').slice(0, 500),
  source_url: chosen.link || '',
  target_type,
  branch,
} }];
JS;

$BUILD_PROMPT_JS = <<<'JS'
const cfg = $('Config').first().json;
const tax = $('GET Taxonomy').first().json;
const topic = $json;
const cats = (tax.categories || []).map(c => c.name).join(', ');
const tags = (tax.tags || []).map(t => t.name).join(', ');
let links = (tax.link_targets || []).map(l => ({ title: l.title, url: l.url }));
let branchJson = '';
if (topic.target_type === 'branch' && topic.branch) {
  branchJson = JSON.stringify(topic.branch, null, 2);
  if (topic.branch.detail_url) links.unshift({ title: topic.branch.name + ' (halaman cabang)', url: topic.branch.detail_url });
}
const user = [
  'TOPIK SUMBER (pemicu sudut pandang — JANGAN disalin/rangkum):',
  '- judul: ' + (topic.topic_title || ''),
  '- ringkasan: ' + (topic.topic_summary || ''),
  '- source_url: ' + (topic.source_url || ''),
  '',
  'JENIS ARTIKEL: ' + topic.target_type,
  '',
  'DATA CABANG (fakta ASLI — pakai untuk GEO/E-E-A-T; kosong jika general):',
  branchJson,
  '',
  'KATEGORI TERSEDIA (pilih 1-2 dari sini saja):',
  cats,
  '',
  'TAG TERSEDIA (pilih 6-10 paling relevan, HANYA dari sini):',
  tags,
  '',
  'LINK INTERNAL TERSEDIA (pakai URL persis, pilih yang relevan):',
  JSON.stringify(links),
  '',
  'Tulis artikel sesuai seluruh aturan di system prompt. Keluarkan HANYA JSON.'
].join('\n');
return [{ json: { system: cfg.systemPrompt, user, source_url: topic.source_url, target_type: topic.target_type, topic_title: topic.topic_title } }];
JS;

$PARSE_LLM_JS = <<<'JS'
const raw = ($json.choices && $json.choices[0] && $json.choices[0].message && $json.choices[0].message.content) || '{}';
let txt = String(raw).trim();
if (txt.startsWith('```')) { txt = txt.replace(/^```(json)?/i, '').replace(/```$/,'').trim(); }
// Extract the JSON object (first "{" .. last "}") so any prose/reasoning the
// model prints around it is ignored.
const a = txt.indexOf('{'); const b = txt.lastIndexOf('}');
if (a >= 0 && b > a) { txt = txt.slice(a, b + 1); }
let d;
try { d = JSON.parse(txt); }
catch (e) { d = { reject: true, reject_reason: 'Output LLM bukan JSON valid: ' + e.message }; }
d.source_url = $('Build Prompt').item.json.source_url || '';
if (typeof d.categories === 'string') d.categories = [d.categories];
if (typeof d.tags === 'string') d.tags = [d.tags];
if (!Array.isArray(d.categories)) d.categories = [];
if (!Array.isArray(d.tags)) d.tags = [];
d.reject = d.reject === true;
return [{ json: d }];
JS;

$FEED_LIST_JS = <<<'JS'
const feeds = [
  'https://news.google.com/rss/search?q=virtual+office+OR+coworking+space+indonesia&hl=id&gl=ID&ceid=ID:id',
  'https://news.google.com/rss/search?q=UMKM+OR+startup+indonesia+bisnis&hl=id&gl=ID&ceid=ID:id',
  'https://news.google.com/rss/search?q=sewa+kantor+OR+ruang+kerja+fleksibel+indonesia&hl=id&gl=ID&ceid=ID:id',
  'https://finance.detik.com/rss',
  'https://www.antaranews.com/rss/ekonomi',
  'https://www.cnbcindonesia.com/rss',
];
return feeds.map(u => ({ json: { feedUrl: u } }));
JS;

// ---- Node builders -----------------------------------------------------------

function httpEndpoint($name, $pos, $method, $url, $bodyExpr = null) {
    $params = [
        'method' => $method,
        'url'    => $url,
        'sendHeaders' => true,
        'headerParameters' => ['parameters' => [
            ['name' => 'X-Automation-Token', 'value' => '={{ $(\'Config\').first().json.automationToken }}'],
        ]],
        'options' => [],
    ];
    if ($method === 'POST' && $bodyExpr !== null) {
        $params['sendBody'] = true;
        $params['specifyBody'] = 'json';
        $params['jsonBody'] = $bodyExpr;
    }
    return [
        'parameters' => $params,
        'id' => bin2hex(random_bytes(8)),
        'name' => $name,
        'type' => 'n8n-nodes-base.httpRequest',
        'typeVersion' => 4.2,
        'position' => $pos,
    ];
}

function httpExternal($name, $pos, $url, $bodyExpr) {
    return [
        'parameters' => [
            'method' => 'POST',
            'url' => $url,
            'authentication' => 'genericCredentialType',
            'genericAuthType' => 'httpHeaderAuth',
            'sendBody' => true,
            'specifyBody' => 'json',
            'jsonBody' => $bodyExpr,
            'options' => [],
        ],
        'id' => bin2hex(random_bytes(8)),
        'name' => $name,
        'type' => 'n8n-nodes-base.httpRequest',
        'typeVersion' => 4.2,
        'position' => $pos,
    ];
}

function codeNode($name, $pos, $js) {
    return [
        'parameters' => ['jsCode' => $js],
        'id' => bin2hex(random_bytes(8)),
        'name' => $name,
        'type' => 'n8n-nodes-base.code',
        'typeVersion' => 2,
        'position' => $pos,
    ];
}

function ifBool($name, $pos, $leftExpr) {
    return [
        'parameters' => [
            'conditions' => [
                'options' => ['caseSensitive' => true, 'leftValue' => '', 'typeValidation' => 'loose'],
                'combinator' => 'and',
                'conditions' => [[
                    'id' => bin2hex(random_bytes(6)),
                    'leftValue' => $leftExpr,
                    'rightValue' => true,
                    'operator' => ['type' => 'boolean', 'operation' => 'true', 'singleValue' => true],
                ]],
            ],
            'options' => [],
        ],
        'id' => bin2hex(random_bytes(8)),
        'name' => $name,
        'type' => 'n8n-nodes-base.if',
        'typeVersion' => 2.2,
        'position' => $pos,
    ];
}

function telegramSend($name, $pos, $textExpr) {
    return [
        'parameters' => [
            'chatId' => '={{ $(\'Config\').first().json.telegramChatId }}',
            'text' => $textExpr,
            'additionalFields' => [],
        ],
        'id' => bin2hex(random_bytes(8)),
        'name' => $name,
        'type' => 'n8n-nodes-base.telegram',
        'typeVersion' => 1.2,
        'position' => $pos,
    ];
}

function telegramApproval($name, $pos, $textExpr) {
    return [
        'parameters' => [
            'resource' => 'message',
            'operation' => 'sendAndWait',
            'chatId' => '={{ $(\'Config\').first().json.telegramChatId }}',
            'message' => $textExpr,
            'responseType' => 'approval',
            'approvalOptions' => ['values' => [
                'approvalType' => 'double',
                'buttonApproveLabel' => 'Approve',
                'buttonDisapproveLabel' => 'Reject',
            ]],
            'options' => [],
        ],
        'id' => bin2hex(random_bytes(8)),
        'name' => $name,
        'type' => 'n8n-nodes-base.telegram',
        'typeVersion' => 1.2,
        'position' => $pos,
    ];
}

/** sendPhoto (from binary property "data") with two inline callback buttons. */
function telegramSendPhoto($name, $pos, $captionExpr, $approveCb, $rejectCb) {
    return [
        'parameters' => [
            'resource' => 'message',
            'operation' => 'sendPhoto',
            'chatId' => '={{ $(\'Config\').first().json.telegramChatId }}',
            'binaryData' => true,
            'binaryPropertyName' => 'data',
            'additionalFields' => ['caption' => $captionExpr],
            'replyMarkup' => 'inlineKeyboard',
            'inlineKeyboard' => ['rows' => [
                ['row' => ['buttons' => [
                    ['text' => 'Approve', 'additionalFields' => ['callback_data' => $approveCb]],
                    ['text' => 'Decline', 'additionalFields' => ['callback_data' => $rejectCb]],
                ]]],
            ]],
        ],
        'id' => bin2hex(random_bytes(8)),
        'name' => $name,
        'type' => 'n8n-nodes-base.telegram',
        'typeVersion' => 1.2,
        'position' => $pos,
    ];
}

// ---- Assemble nodes ----------------------------------------------------------

$nodes = [];

$nodes[] = [
    'parameters' => ['rule' => ['interval' => [
        // Fire twice daily at 08:00 and 15:00 (server timezone = Asia/Jakarta).
        ['field' => 'cronExpression', 'expression' => '0 8,15 * * *'],
    ]]],
    'id' => bin2hex(random_bytes(8)),
    'name' => 'Schedule 2x/hari',
    'type' => 'n8n-nodes-base.scheduleTrigger',
    'typeVersion' => 1.2,
    'position' => [240, 300],
];

$nodes[] = [
    'parameters' => [
        'assignments' => ['assignments' => [
            ['id' => bin2hex(random_bytes(6)), 'name' => 'siteBaseUrl',     'type' => 'string', 'value' => 'http://host.docker.internal:8000'],
            ['id' => bin2hex(random_bytes(6)), 'name' => 'automationToken', 'type' => 'string', 'value' => 'PASTE_AUTOMATION_TOKEN'],
            ['id' => bin2hex(random_bytes(6)), 'name' => 'apiBase',         'type' => 'string', 'value' => 'https://api.groq.com/openai/v1'],
            ['id' => bin2hex(random_bytes(6)), 'name' => 'apiModel',        'type' => 'string', 'value' => 'llama-3.3-70b-versatile'],
            ['id' => bin2hex(random_bytes(6)), 'name' => 'temperature',     'type' => 'string', 'value' => '0.6'],
            ['id' => bin2hex(random_bytes(6)), 'name' => 'falModel',        'type' => 'string', 'value' => 'fal-ai/flux/schnell'],
            ['id' => bin2hex(random_bytes(6)), 'name' => 'telegramChatId',  'type' => 'string', 'value' => 'PASTE_TELEGRAM_CHAT_ID'],
            ['id' => bin2hex(random_bytes(6)), 'name' => 'systemPrompt',    'type' => 'string', 'value' => $SYSTEM_PROMPT],
        ]],
        'options' => [],
    ],
    'id' => bin2hex(random_bytes(8)),
    'name' => 'Config',
    'type' => 'n8n-nodes-base.set',
    'typeVersion' => 3.4,
    'position' => [460, 300],
];

$nodes[] = httpEndpoint('GET Taxonomy', [680, 300], 'GET', '={{ $(\'Config\').first().json.siteBaseUrl }}/_api/taxonomy.php');
$nodes[] = httpEndpoint('GET Branches', [900, 300], 'GET', '={{ $(\'Config\').first().json.siteBaseUrl }}/_api/branches.php');
$nodes[] = codeNode('Feed List', [1120, 300], $FEED_LIST_JS);

$nodes[] = [
    'parameters' => ['url' => '={{ $json.feedUrl }}', 'options' => []],
    'id' => bin2hex(random_bytes(8)),
    'name' => 'RSS Read',
    'type' => 'n8n-nodes-base.rssFeedRead',
    'typeVersion' => 1.1,
    'position' => [1340, 300],
    // A single slow/unreachable feed must not kill the whole run — skip it and
    // keep the items from the feeds that did load.
    'onError' => 'continueRegularOutput',
    'retryOnFail' => true,
    'maxTries' => 2,
];

$nodes[] = codeNode('Pick Topic', [1560, 300], $PICK_TOPIC_JS);
$nodes[] = ifBool('Ada Topik?', [1780, 300], '={{ $json.hasTopic }}');
$nodes[] = telegramSend('Notif: Tidak Ada Topik', [1780, 520], 'Automation: tidak ada topik relevan hari ini.');
$nodes[] = codeNode('Build Prompt', [2000, 300], $BUILD_PROMPT_JS);

// NB: no response_format:json_object — Groq reasoning models (gpt-oss) fail that
// mode ("Failed to generate JSON"). We rely on the prompt + robust extraction in
// Parse LLM instead, which works across gpt-oss / llama / deepseek alike.
$llmBody = "={{ JSON.stringify({ model: \$('Config').first().json.apiModel, temperature: Number(\$('Config').first().json.temperature), max_tokens: 4000, messages: [ { role: 'system', content: \$json.system }, { role: 'user', content: \$json.user } ] }) }}";
$nodes[] = httpExternal('LLM Generate', [2220, 300], "={{ \$('Config').first().json.apiBase }}/chat/completions", $llmBody);

$nodes[] = codeNode('Parse LLM', [2440, 300], $PARSE_LLM_JS);
$nodes[] = ifBool('LLM Reject?', [2660, 300], '={{ $json.reject }}');
$nodes[] = telegramSend('Notif: Ditolak LLM', [2660, 520], '={{ "Automation: topik dilewati oleh LLM. Alasan: " + ($json.reject_reason || "-") }}');

$falBody = "={{ JSON.stringify({ prompt: \$('Parse LLM').item.json.image_prompt, image_size: 'landscape_16_9', num_images: 1 }) }}";
$nodes[] = httpExternal('Fal.ai Image', [2880, 300], "=https://fal.run/{{ \$('Config').first().json.falModel }}", $falBody);

$ingestBody = "={{ JSON.stringify({ title: \$('Parse LLM').item.json.title, slug: \$('Parse LLM').item.json.slug, excerpt: \$('Parse LLM').item.json.excerpt, content: \$('Parse LLM').item.json.content, meta_title: \$('Parse LLM').item.json.meta_title, meta_description: \$('Parse LLM').item.json.meta_description, categories: \$('Parse LLM').item.json.categories, tags: \$('Parse LLM').item.json.tags, source_url: \$('Parse LLM').item.json.source_url, image_url: (\$json.images && \$json.images[0] ? \$json.images[0].url : '') }) }}";
$nodes[] = httpEndpoint('Ingest Draft', [3100, 300], 'POST', '={{ $(\'Config\').first().json.siteBaseUrl }}/_api/ingest.php', $ingestBody);

// Approval is now callback-based (see workflow-callback.json): the generator
// just downloads the hero and sends it with inline callback buttons, then ends.
$nodes[] = [
    'parameters' => [
        // featured_image may be absolute (Cloudinary) or relative (local); only
        // prepend siteBaseUrl for the relative case.
        'url' => '={{ $(\'Ingest Draft\').item.json.featured_image.startsWith(\'http\') ? $(\'Ingest Draft\').item.json.featured_image : $(\'Config\').first().json.siteBaseUrl + \'/\' + $(\'Ingest Draft\').item.json.featured_image }}',
        'options' => ['response' => ['response' => ['responseFormat' => 'file', 'outputPropertyName' => 'data']]],
    ],
    'id' => bin2hex(random_bytes(8)),
    'name' => 'Get Hero Image',
    'type' => 'n8n-nodes-base.httpRequest',
    'typeVersion' => 4.2,
    'position' => [3320, 300],
];
$previewCaption = '={{ "📝 " + $(\'Parse LLM\').item.json.title + "\n\n" + ($(\'Parse LLM\').item.json.meta_description || "") + "\n\nEdit: " + $(\'Ingest Draft\').item.json.admin_edit_url + "\n\nSetujui untuk publish?" }}';
$nodes[] = telegramSendPhoto('Telegram Kirim Preview', [3540, 300], $previewCaption,
    '=approve:{{ $(\'Ingest Draft\').item.json.post_id }}',
    '=reject:{{ $(\'Ingest Draft\').item.json.post_id }}');

// ---- Connections -------------------------------------------------------------
function conn($to) { return ['main' => [[['node' => $to, 'type' => 'main', 'index' => 0]]]]; }
function connIf($t, $f) { return ['main' => [
    [['node' => $t, 'type' => 'main', 'index' => 0]],
    [['node' => $f, 'type' => 'main', 'index' => 0]],
]]; }

$connections = [
    'Schedule 2x/hari' => conn('Config'),
    'Config' => conn('GET Taxonomy'),
    'GET Taxonomy' => conn('GET Branches'),
    'GET Branches' => conn('Feed List'),
    'Feed List' => conn('RSS Read'),
    'RSS Read' => conn('Pick Topic'),
    'Pick Topic' => conn('Ada Topik?'),
    'Ada Topik?' => connIf('Build Prompt', 'Notif: Tidak Ada Topik'),
    'Build Prompt' => conn('LLM Generate'),
    'LLM Generate' => conn('Parse LLM'),
    'Parse LLM' => conn('LLM Reject?'),
    'LLM Reject?' => connIf('Notif: Ditolak LLM', 'Fal.ai Image'),
    'Fal.ai Image' => conn('Ingest Draft'),
    'Ingest Draft' => conn('Get Hero Image'),
    'Get Hero Image' => conn('Telegram Kirim Preview'),
];

$workflow = [
    'name' => 'Urban Office - Auto Artikel SEO/AIO/GEO',
    'nodes' => $nodes,
    'connections' => $connections,
    'active' => false,
    'settings' => ['executionOrder' => 'v1'],
    'meta' => ['templateId' => 'urban-office-auto-artikel'],
    'tags' => [],
];

$json = json_encode($workflow, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
file_put_contents(__DIR__ . '/workflow.json', $json);
echo "workflow.json written (" . strlen($json) . " bytes, " . count($nodes) . " nodes)\n";


// =============================================================================
//  CALLBACK HANDLER WORKFLOW (workflow-callback.json)
//  Receives Approve/Decline taps (callback_query) → moderate → reply in chat.
//  Must be ACTIVATED so its Telegram Trigger registers the bot webhook.
// =============================================================================

$PARSE_CB_JS = <<<'JS'
const cb = $json.callback_query || {};
const data = String(cb.data || '');
const idx = data.indexOf(':');
const action = idx >= 0 ? data.slice(0, idx) : data;
const post_id = idx >= 0 ? parseInt(data.slice(idx + 1), 10) : 0;
return [{ json: {
  action,
  post_id,
  callback_query_id: cb.id || '',
  chat_id: (cb.message && cb.message.chat) ? cb.message.chat.id : $json.telegramChatId,
  siteBaseUrl: $json.siteBaseUrl,
  automationToken: $json.automationToken,
} }];
JS;

$cb = [];

$cb[] = [
    'parameters' => ['updates' => ['callback_query'], 'additionalFields' => []],
    'id' => bin2hex(random_bytes(8)),
    'name' => 'Telegram Trigger',
    'type' => 'n8n-nodes-base.telegramTrigger',
    'typeVersion' => 1.2,
    'position' => [240, 300],
    'webhookId' => bin2hex(random_bytes(8)),
];

$cb[] = [
    'parameters' => [
        'includeOtherFields' => true,
        'assignments' => ['assignments' => [
            ['id' => bin2hex(random_bytes(6)), 'name' => 'siteBaseUrl',     'type' => 'string', 'value' => 'http://localhost:8000'],
            ['id' => bin2hex(random_bytes(6)), 'name' => 'automationToken', 'type' => 'string', 'value' => 'PASTE_AUTOMATION_TOKEN'],
            ['id' => bin2hex(random_bytes(6)), 'name' => 'telegramChatId',  'type' => 'string', 'value' => 'PASTE_TELEGRAM_CHAT_ID'],
        ]],
        'options' => [],
    ],
    'id' => bin2hex(random_bytes(8)),
    'name' => 'Config',
    'type' => 'n8n-nodes-base.set',
    'typeVersion' => 3.4,
    'position' => [460, 300],
];

$cb[] = codeNode('Parse Callback', [680, 300], $PARSE_CB_JS);

$cb[] = [
    'parameters' => [
        'resource' => 'callback',
        'operation' => 'answerQuery',
        'queryId' => '={{ $(\'Parse Callback\').item.json.callback_query_id }}',
        'additionalFields' => ['text' => 'Memproses...'],
    ],
    'id' => bin2hex(random_bytes(8)),
    'name' => 'Answer Callback',
    'type' => 'n8n-nodes-base.telegram',
    'typeVersion' => 1.2,
    'position' => [900, 300],
];

$cbModerateBody = "={{ JSON.stringify({ post_id: \$('Parse Callback').item.json.post_id, action: \$('Parse Callback').item.json.action }) }}";
$cb[] = [
    'parameters' => [
        'method' => 'POST',
        'url' => '={{ $(\'Parse Callback\').item.json.siteBaseUrl }}/_api/moderate.php',
        'sendHeaders' => true,
        'headerParameters' => ['parameters' => [
            ['name' => 'X-Automation-Token', 'value' => '={{ $(\'Parse Callback\').item.json.automationToken }}'],
        ]],
        'sendBody' => true,
        'specifyBody' => 'json',
        'jsonBody' => $cbModerateBody,
        'options' => [],
    ],
    'id' => bin2hex(random_bytes(8)),
    'name' => 'Moderate',
    'type' => 'n8n-nodes-base.httpRequest',
    'typeVersion' => 4.2,
    'position' => [1120, 300],
];

$cbReply = '={{ $(\'Parse Callback\').item.json.action === "approve" ? "✅ Disetujui & dipublish: " + ($json.public_url || "") : "❌ Ditolak — artikel tidak dipublish." }}';
$cb[] = [
    'parameters' => [
        'chatId' => '={{ $(\'Parse Callback\').item.json.chat_id }}',
        'text' => $cbReply,
        'additionalFields' => [],
    ],
    'id' => bin2hex(random_bytes(8)),
    'name' => 'Kirim Balasan',
    'type' => 'n8n-nodes-base.telegram',
    'typeVersion' => 1.2,
    'position' => [1340, 300],
];

$cbConnections = [
    'Telegram Trigger' => conn('Config'),
    'Config'           => conn('Parse Callback'),
    'Parse Callback'   => conn('Answer Callback'),
    'Answer Callback'  => conn('Moderate'),
    'Moderate'         => conn('Kirim Balasan'),
];

$cbWorkflow = [
    'name' => 'Urban Office - Approval Callback Handler',
    'nodes' => $cb,
    'connections' => $cbConnections,
    'active' => false,
    'settings' => ['executionOrder' => 'v1'],
    'meta' => ['templateId' => 'urban-office-approval-callback'],
    'tags' => [],
];

$json2 = json_encode($cbWorkflow, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
file_put_contents(__DIR__ . '/workflow-callback.json', $json2);
echo "workflow-callback.json written (" . strlen($json2) . " bytes, " . count($cb) . " nodes)\n";
