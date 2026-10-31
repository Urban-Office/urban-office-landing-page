<?php
/**
 * WhatsApp Channel Selector Modal Component
 * Displays a popup selector when any WhatsApp button across the site is clicked:
 * 1. Sales & Konsultasi Produk (0821-2338-100 / 628212338100)
 * 2. Customer Service & Komplain (0851-0762-0100 / 6285107620100)
 */

$wa_sales_num = defined('WHATSAPP_NUMBER_SALES') ? WHATSAPP_NUMBER_SALES : '628212338100';
$wa_cs_num    = defined('WHATSAPP_NUMBER_CS') ? WHATSAPP_NUMBER_CS : (defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '6285107620100');
?>

<!-- WhatsApp Channel Selector Modal -->
<div id="uo-wa-modal" class="uo-wa-modal" aria-hidden="true" role="dialog" aria-labelledby="uo-wa-modal-title" data-wa-sales="<?php echo htmlspecialchars($wa_sales_num, ENT_QUOTES, 'UTF-8'); ?>" data-wa-cs="<?php echo htmlspecialchars($wa_cs_num, ENT_QUOTES, 'UTF-8'); ?>" data-mode="sales_only">
    <div class="uo-wa-modal-backdrop" data-wa-close tabindex="-1"></div>
    <div class="uo-wa-modal-container" role="document">
        <div class="uo-wa-modal-dialog">
            <button type="button" class="uo-wa-modal-close" data-wa-close aria-label="Tutup jendela pop up">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>

            <!-- Modal Header (Dynamic based on mode) -->
            <div class="uo-wa-modal-header">
                <div class="uo-wa-header-pill" id="uo-wa-header-pill">
                    <span class="uo-wa-header-icon"><i class="bi bi-whatsapp"></i></span>
                    <span class="uo-wa-header-badge" id="uo-wa-header-badge">Direct WhatsApp</span>
                </div>
                <h3 id="uo-wa-modal-title" class="uo-wa-modal-title">Hubungi Tim Sales Produk</h3>
                <p id="uo-wa-modal-desc" class="uo-wa-modal-desc">Terhubung langsung dengan Tim Sales kami untuk info ketersediaan ruang, penawaran resmi, dan jadwal survey:</p>
                <div id="uo-wa-context-box" class="uo-wa-context-box" style="display: none;">
                    <i class="bi bi-chat-left-text-fill"></i> <span id="uo-wa-context-text"></span>
                </div>
            </div>

            <div class="uo-wa-modal-options">
                <!-- Option 1: Sales Produk -->
                <button type="button" class="uo-wa-card uo-wa-card--sales" data-wa-choice="sales" aria-label="Hubungi Sales dan Konsultasi Produk">
                    <div class="uo-wa-card-topbar">
                        <span class="uo-wa-card-cat"><i class="bi bi-stars"></i> Konsultasi Layanan & Sewa</span>
                        <span class="uo-wa-card-badge"><span class="uo-wa-dot uo-wa-dot--sales"></span> Respon Cepat</span>
                    </div>
                    <div class="uo-wa-card-main">
                        <div class="uo-wa-card-icon uo-wa-card-icon--sales">
                            <i class="bi bi-briefcase-fill"></i>
                        </div>
                        <div class="uo-wa-card-body">
                            <div class="uo-wa-card-title">Sales & Konsultasi Produk</div>
                            <div class="uo-wa-card-number">0821-2338-100</div>
                            <p class="uo-wa-card-desc">Sewa Kantor, Virtual Office, Meeting Room, Legalitas PT/CV & Penawaran Baru</p>
                        </div>
                        <div class="uo-wa-card-action">
                            <span class="uo-wa-arrow-btn" aria-hidden="true">
                                <i class="bi bi-chevron-right"></i>
                            </span>
                        </div>
                    </div>
                    <div class="uo-wa-card-cta-btn uo-wa-card-cta-btn--sales" aria-hidden="true">
                        <span>Lanjut Chat WhatsApp Sales</span> <i class="bi bi-arrow-right"></i>
                    </div>
                </button>

                <!-- Option 2: Customer Service / Complain -->
                <button type="button" class="uo-wa-card uo-wa-card--cs" data-wa-choice="cs" aria-label="Hubungi Customer Service dan Layanan Komplain">
                    <div class="uo-wa-card-topbar">
                        <span class="uo-wa-card-cat"><i class="bi bi-shield-check"></i> Member & Operasional</span>
                        <span class="uo-wa-card-badge"><span class="uo-wa-dot uo-wa-dot--cs"></span> Pusat Bantuan</span>
                    </div>
                    <div class="uo-wa-card-main">
                        <div class="uo-wa-card-icon uo-wa-card-icon--cs">
                            <i class="bi bi-headset"></i>
                        </div>
                        <div class="uo-wa-card-body">
                            <div class="uo-wa-card-title">Customer Service & Komplain</div>
                            <div class="uo-wa-card-number">0851-0762-0100</div>
                            <p class="uo-wa-card-desc">Bantuan Member Aktif, Layanan Operasional, Surat & Paket Masuk, serta Kendala/Komplain</p>
                        </div>
                        <div class="uo-wa-card-action">
                            <span class="uo-wa-arrow-btn" aria-hidden="true">
                                <i class="bi bi-chevron-right"></i>
                            </span>
                        </div>
                    </div>
                    <div class="uo-wa-card-cta-btn uo-wa-card-cta-btn--cs" aria-hidden="true">
                        <span>Lanjut Chat WhatsApp CS</span> <i class="bi bi-arrow-right"></i>
                    </div>
                </button>
            </div>

            <!-- Alternative Switch Link (when in single mode) -->
            <div class="uo-wa-alt-link" id="uo-wa-alt-link" style="display: none;"></div>

            <div class="uo-wa-modal-footer">
                <div class="uo-wa-notice">
                    <i class="bi bi-lightning-charge-fill"></i> Langsung meluncur ke chat WhatsApp tanpa halaman perantara
                </div>
            </div>
        </div>
    </div>
</div>
