<?php
/**
 * Pricing Cards Component Template
 * Parameters:
 *  - $pricing_title: string (optional)
 *  - $packages: array (required) -> format: 
 *    [[
 *      'name' => 'Starter', 
 *      'price' => '385.000', 
 *      'period' => 'Bulan', 
 *      'popular' => true/false, 
 *      'description' => '...',
 *      'features' => ['Feature 1', 'Feature 2'],
 *      'cta_text' => 'Beli Sekarang',
 *      'cta_link' => '...'
 *    ]]
 */

$title = $pricing_title ?? 'Pilih Paket Ruang Kerja Anda';
?>
<section class="section" style="background-color: hsl(var(--clr-bg-secondary));">
    <div class="container">
        <h2 class="text-center section-title"><?php echo sanitize($title); ?></h2>
        <p class="text-center section-subtitle">Penawaran harga terbaik dengan kontrak fleksibel sesuai kebutuhan skala bisnis Anda.</p>
        
        <?php 
        $show_toggle = $has_billing_toggle ?? false;
        if ($show_toggle): 
        ?>
            <!-- Billing Toggle Switch -->
            <div class="billing-toggle-container">
                <div class="billing-toggle-switch">
                    <input type="radio" id="billing-monthly" name="billing-period" value="monthly" checked>
                    <label for="billing-monthly">Bulanan</label>
                    
                    <input type="radio" id="billing-yearly" name="billing-period" value="yearly">
                    <label for="billing-yearly">Tahunan</label>
                    
                    <span class="billing-toggle-slider"></span>
                </div>
                <div class="billing-save-badge">
                    <i class="bi bi-lightning-charge-fill"></i> Sewa 1 Tahun Bayar 9 Bulan!
                </div>
            </div>
        <?php endif; ?>

        <?php
        $grid_style = 'margin-top: 50px; align-items: stretch;';
        if (isset($packages)) {
            if (count($packages) === 2) {
                $grid_style .= ' max-width: 900px; margin-left: auto; margin-right: auto; justify-content: center;';
            } elseif (count($packages) === 1) {
                $grid_style .= ' max-width: 450px; margin-left: auto; margin-right: auto; justify-content: center;';
            }
        }
        ?>
        <div class="card-grid stretch-items <?php echo (isset($packages) && count($packages) === 1) ? 'single-card-grid' : ''; ?>" style="<?php echo $grid_style; ?>">
            <?php foreach ($packages as $pkg): ?>
                <div class="premium-card <?php echo !empty($pkg['popular']) ? 'popular-card' : ''; ?>" style="display: flex; flex-direction: column; justify-content: space-between; position: relative; border-color: <?php echo !empty($pkg['popular']) ? 'hsl(var(--clr-primary))' : 'hsl(var(--clr-border))'; ?>;">
                    
                    <?php if (!empty($pkg['popular'])): ?>
                        <div class="pricing-badge-popular">
                            Populer
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <h3 style="font-size: 1.5rem; margin-bottom: 8px;"><?php echo sanitize($pkg['name']); ?></h3>
                        <p style="font-size: 0.9rem; margin-bottom: 20px;"><?php echo sanitize($pkg['description']); ?></p>
                        
                        <?php if ($show_toggle && isset($pkg['price_monthly']) && isset($pkg['price_yearly'])): ?>
                            <!-- Toggleable Prices -->
                            <div class="price-container" style="margin-bottom: 20px;">
                                <div class="monthly-price-view">
                                    <span style="font-size: 0.85rem; font-weight: 600; color: hsl(var(--clr-primary)); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px; text-align: left;">Mulai dari</span>
                                    <?php if ($pkg['price_monthly'] === 'Negotiable'): ?>
                                        <div class="price-tag" style="margin: 0; white-space: nowrap; font-size: 1.8rem;">
                                            Negotiable
                                        </div>
                                    <?php else: ?>
                                        <div class="price-tag" style="margin: 0; white-space: nowrap; font-size: 1.8rem;">
                                            Rp <?php echo sanitize($pkg['price_monthly']); ?>
                                            <span style="font-size: 1rem; color: hsl(var(--clr-text-muted)); font-weight: 500;">/ <?php echo sanitize($pkg['period_monthly'] ?? 'Bulan'); ?><?php echo empty($pkg['hide_minimum_info']) ? '*' : ''; ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="yearly-price-view" style="display: none;">
                                    <span style="font-size: 0.85rem; font-weight: 600; color: hsl(var(--clr-primary)); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px; text-align: left;">Mulai dari</span>
                                    <?php if ($pkg['price_yearly'] === 'Negotiable'): ?>
                                        <div class="price-tag" style="margin: 0; white-space: nowrap; font-size: 1.8rem;">
                                            Negotiable
                                        </div>
                                    <?php else: ?>
                                        <div class="price-tag" style="margin: 0; white-space: nowrap; font-size: 1.8rem;">
                                            Rp <?php echo sanitize($pkg['price_yearly']); ?>
                                            <span style="font-size: 0.9rem; color: hsl(var(--clr-text-muted)); font-weight: 500;">/ <?php echo sanitize($pkg['period_yearly'] ?? 'Tahun'); ?><?php echo empty($pkg['hide_minimum_info']) ? '*' : ''; ?></span>
                                        </div>
                                        <?php if (isset($pkg['price_yearly_monthly']) && $pkg['price_yearly_monthly'] !== 'Negotiable'): ?>
                                            <div style="font-size: 0.9rem; color: hsl(var(--clr-success)); font-weight: 700; margin-top: 6px; display: flex; align-items: center; gap: 6px; white-space: nowrap;">
                                                <i class="bi bi-tag-fill"></i> Setara Rp <?php echo sanitize($pkg['price_yearly_monthly']); ?> / <?php echo sanitize($pkg['period_monthly'] ?? 'Bulan'); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Standard Price Tag -->
                            <div class="price-container" style="margin-bottom: 20px;">
                                <?php if (isset($pkg['price']) && $pkg['price'] !== 'Negotiable'): ?>
                                    <span style="font-size: 0.85rem; font-weight: 600; color: hsl(var(--clr-primary)); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px; text-align: left;">Mulai dari</span>
                                <?php endif; ?>
                                <div class="price-tag" style="margin: 0; white-space: nowrap;">
                                    <?php if (isset($pkg['price']) && $pkg['price'] === 'Negotiable'): ?>
                                        Negotiable
                                    <?php else: ?>
                                        Rp <?php echo sanitize($pkg['price']); ?>
                                        <span>/ <?php echo sanitize($pkg['period'] ?? 'Bulan'); ?><?php echo empty($pkg['hide_minimum_info']) ? '*' : ''; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (empty($pkg['hide_minimum_info'])): ?>
                            <p class="price-minimum-info">*Minimum sewa 1 tahun</p>
                        <?php endif; ?>
                        
                        <?php if (!empty($pkg['features'])): ?>
                            <ul class="card-features-list">
                                <?php foreach ($pkg['features'] as $feat): ?>
                                    <li><?php echo sanitize($feat); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin-top: 24px; display: flex; flex-direction: column; gap: 10px;">
                        <a href="<?php echo sanitize($pkg['cta_link'] ?? '#contact'); ?>" class="btn <?php echo !empty($pkg['popular']) ? 'btn-primary' : 'btn-outline'; ?>" style="width: 100%;">
                            <?php echo sanitize($pkg['cta_text'] ?? 'Beli Sekarang'); ?>
                        </a>
                        <?php if ($show_toggle || !empty($pkg['show_offer_btn'])): ?>
                            <button type="button" class="btn btn-secondary" style="width: 100%; font-size: 13px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.02em;" onclick="activateOfferMode('<?php echo sanitize($pkg['name']); ?>', '<?php echo sanitize($pkg['service'] ?? 'Private Office'); ?>', '<?php echo sanitize($pkg['branch'] ?? 'MERR (Surabaya Timur)'); ?>')">
                                Ajukan Penawaran
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.popular-card {
    border-width: 2px;
    box-shadow: var(--shadow-lg);
    background: linear-gradient(180deg, hsl(var(--clr-bg-surface)) 0%, hsl(var(--clr-primary-light)) 100%);
}

/* Billing Toggle CSS */
.billing-toggle-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin: 30px auto 10px auto;
}

.billing-toggle-switch {
    position: relative;
    display: flex;
    background-color: rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(0, 0, 0, 0.08);
    padding: 4px;
    border-radius: 50px;
    width: 260px;
    height: 46px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.billing-toggle-switch input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.billing-toggle-switch label {
    flex: 1;
    z-index: 2;
    font-size: 0.9rem;
    font-weight: 700;
    color: hsl(var(--clr-text-muted));
    text-align: center;
    line-height: 36px;
    cursor: pointer;
    transition: color 0.3s ease;
    user-select: none;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.billing-toggle-switch input[type="radio"]:checked + label {
    color: #FFFFFF;
}

.billing-toggle-slider {
    position: absolute;
    top: 3px;
    left: 3px;
    width: calc(50% - 3px);
    height: calc(100% - 6px);
    background-color: hsl(var(--clr-primary));
    border-radius: 50px;
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 1;
    box-shadow: 0 4px 12px rgba(255, 107, 0, 0.35);
}

.billing-toggle-switch input#billing-yearly:checked ~ .billing-toggle-slider {
    transform: translateX(100%);
}

.billing-save-badge {
    background: linear-gradient(135deg, hsl(var(--clr-accent)) 0%, #FFB800 100%);
    color: hsl(var(--clr-text-main));
    font-size: 0.75rem;
    font-weight: 800;
    padding: 5px 14px;
    border-radius: var(--radius-full);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    box-shadow: 0 4px 10px rgba(255, 215, 0, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    animation: billing-pulse 2s infinite;
}

.billing-save-badge i {
    font-size: 0.85rem;
}

@keyframes billing-pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 4px 10px rgba(255, 215, 0, 0.3);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 6px 15px rgba(255, 215, 0, 0.5);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 4px 10px rgba(255, 215, 0, 0.3);
    }
}

/* Single Card Grid Mobile layout (centered card when there is only 1 package) */
@media (max-width: 768px) {
    .single-card-grid {
        display: block !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        max-width: 250px !important;
    }
    .single-card-grid .premium-card {
        flex: none !important;
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 auto !important;
    }
}
</style>

<?php if ($show_toggle): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlyRadio = document.getElementById('billing-monthly');
    const yearlyRadio = document.getElementById('billing-yearly');
    const monthlyViews = document.querySelectorAll('.monthly-price-view');
    const yearlyViews = document.querySelectorAll('.yearly-price-view');
    
    function togglePrices() {
        if (monthlyRadio.checked) {
            monthlyViews.forEach(el => el.style.display = 'block');
            yearlyViews.forEach(el => el.style.display = 'none');
        } else if (yearlyRadio.checked) {
            monthlyViews.forEach(el => el.style.display = 'none');
            yearlyViews.forEach(el => el.style.display = 'block');
        }
    }
    
    monthlyRadio.addEventListener('change', togglePrices);
    yearlyRadio.addEventListener('change', togglePrices);
});
</script>
<?php endif; ?>
