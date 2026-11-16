<?php
/**
 * Shared Local GEO Direct-Answer & Anti-Doorway Specification Block
 * Input variables expected before include:
 *   $geo_service_cat : string ('virtual-office'|'private-office'|'meeting-room'|'coworking'|'event-space')
 *   $geo_city_slug   : string
 *   $geo_branch      : array (from locations_data.php)
 */
if (!empty($geo_service_cat) && !empty($geo_city_slug) && !empty($geo_branch) && function_exists('get_city_geo_context_block')):
    $geo_block = get_city_geo_context_block($geo_service_cat, $geo_city_slug, $geo_branch);
?>
<section class="geo-summary-sec">
    <div class="container">
        <div class="geo-summary-card">
            <span class="badge" style="margin-bottom: 10px;"><?php echo sanitize($geo_block['badge']); ?></span>
            <h2 class="geo-summary-title"><?php echo sanitize($geo_block['heading']); ?></h2>
            <p class="geo-summary-text"><?php echo sanitize($geo_block['summary']); ?></p>
            <div class="geo-specs-grid">
                <?php foreach ($geo_block['specs'] as $spec): ?>
                <div class="geo-spec-box">
                    <div class="geo-spec-label"><?php echo sanitize($spec['label']); ?></div>
                    <div class="geo-spec-value"><?php echo sanitize($spec['value']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<style>
.geo-summary-sec {
    padding: 36px 0;
    background-color: #FFFFFF;
    border-top: 1px solid hsl(var(--clr-border));
    border-bottom: 1px solid hsl(var(--clr-border));
}
.geo-summary-card {
    background-color: hsl(var(--clr-bg-secondary));
    border: 1px solid hsl(var(--clr-border));
    border-left: 4px solid hsl(var(--clr-primary));
    border-radius: var(--radius-md);
    padding: 28px 30px;
}
.geo-summary-title {
    font-size: 1.45rem;
    font-weight: 800;
    color: #111111;
    margin: 0 0 10px 0;
    line-height: 1.3;
}
.geo-summary-text {
    font-size: 0.96rem;
    line-height: 1.7;
    color: #333333;
    margin: 0 0 22px 0;
}
.geo-specs-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}
.geo-spec-box {
    background-color: #FFFFFF;
    border: 1px solid hsl(var(--clr-border));
    border-radius: var(--radius-sm);
    padding: 14px 16px;
}
.geo-spec-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: hsl(var(--clr-primary));
    margin-bottom: 6px;
}
.geo-spec-value {
    font-size: 0.86rem;
    font-weight: 600;
    color: #111111;
    line-height: 1.45;
}
@media (max-width: 991px) {
    .geo-specs-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 576px) {
    .geo-summary-sec { padding: 24px 0; }
    .geo-summary-card { padding: 20px 18px; }
    .geo-summary-title { font-size: 1.15rem; }
    .geo-summary-text { font-size: 0.86rem; margin-bottom: 16px; }
    .geo-specs-grid { grid-template-columns: 1fr; gap: 10px; }
}
</style>
<?php endif; ?>
