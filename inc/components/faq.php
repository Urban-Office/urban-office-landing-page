<?php
/**
 * FAQ Accordion Component Template
 * Parameters:
 *  - $faqs: array (optional) -> format: [['question' => '...', 'answer' => '...']]
 */

$default_faqs = [
    [
        'question' => 'Apa syarat untuk mendaftar Virtual Office di Urban Office?',
        'answer' => 'Untuk perorangan, Anda cukup melampirkan KTP dan NPWP Pribadi. Sedangkan untuk badan usaha (PT/CV), dokumen yang dibutuhkan meliputi Akta Pendirian, SK Kemenkumham, NIB, dan NPWP Badan Usaha.'
    ],
    [
        'question' => 'Berapa lama proses aktivasi Virtual Office?',
        'answer' => 'Proses aktivasi sangat cepat. Surat Perjanjian Sewa dan Surat Domisili Gedung akan diterbitkan dan siap dalam waktu 1x24 jam setelah pembayaran dan dokumen persyaratan lengkap diterima.'
    ],
    [
        'question' => 'Apakah alamat Virtual Office bisa digunakan untuk PKP?',
        'answer' => 'Ya, seluruh lokasi Virtual Office kami berada di zona bisnis (zonasi perkantoran resmi) sehingga legal dan mendukung pengurusan Pengukuhan Pengusaha Kena Pajak (PKP).'
    ],
    [
        'question' => 'Bagaimana jika ada surat atau paket masuk untuk perusahaan saya?',
        'answer' => 'Tim resepsionis kami akan mendata dan mengamankan surat/paket Anda. Anda akan langsung menerima notifikasi real-time via WhatsApp/Email. Anda dapat mengambilnya langsung atau meminta kami memindai (scan) isi surat tersebut.'
    ],
    [
        'question' => 'Apakah saya mendapatkan fasilitas Ruang Meeting gratis?',
        'answer' => 'Ya, tergantung pada paket yang Anda pilih (Luxury & Priority), Anda akan mendapatkan kuota akses gratis penggunaan Ruang Rapat (Meeting Room) premium di seluruh cabang Urban Office.'
    ]
];

$faq_items = $faqs ?? $default_faqs;
?>
<section class="section">
    <div class="container">
        <h2 class="text-center section-title">Pertanyaan yang Sering Diajukan</h2>
        <p class="text-center section-subtitle">Temukan jawaban cepat untuk pertanyaan-pertanyaan umum seputar layanan Urban Office.</p>
        
        <div class="faq-list" style="margin-top: 50px;">
            <?php foreach ($faq_items as $index => $item): ?>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false" style="width: 100%; border: none; background: none; text-align: left;">
                        <?php echo sanitize($item['question']); ?>
                    </button>
                    <div class="faq-answer">
                        <p style="margin: 0; padding-top: 8px; line-height: 1.7;"><?php echo sanitize($item['answer']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
