<?php
/**
 * Shared Native Per-City Branch Card Section (.local-branch-grid + .premium-card)
 * Matches Private Office & Meeting Room design system cleanly.
 * Expects:
 *   $local_section_title    : string
 *   $local_section_subtitle : string
 *   $svc_branches           : array
 *   $local_city_slug        : string
 */
if (!empty($svc_branches) && is_array($svc_branches)):
?>
<section class="section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title"><?php echo sanitize($local_section_title ?? 'Lokasi Cabang'); ?></h2>
            <?php if (!empty($local_section_subtitle)): ?>
            <p class="section-subtitle"><?php echo sanitize($local_section_subtitle); ?></p>
            <?php endif; ?>
        </div>
        <div class="local-branch-grid<?php echo count($svc_branches) === 1 ? ' local-branch-grid--single' : ''; ?>">
            <?php foreach ($svc_branches as $svc_b):
                $svc_b_heading = ($local_city_slug === 'jakarta')
                    ? ('Urban Office ' . get_private_office_branch_label($local_city_slug, $svc_b))
                    : ($svc_b['title'] ?? ('Urban Office ' . $svc_b['short_title']));
                $svc_b_map_title = ($local_city_slug === 'jakarta')
                    ? get_private_office_branch_label($local_city_slug, $svc_b)
                    : $svc_b['short_title'];
            ?>
            <div class="premium-card" style="text-align: left; display: flex; flex-direction: column;">
                <h3 style="font-size: 1.2rem; margin-bottom: 8px;"><?php echo sanitize($svc_b_heading); ?></h3>
                <p style="color: hsl(var(--clr-text-muted)); font-size: 0.9rem; margin-bottom: 10px;"><i class="bi bi-geo-alt-fill"></i> <?php echo sanitize($svc_b['address']); ?></p>
                <?php if (!empty($svc_b['rating'])): ?>
                <p style="font-size: 0.9rem; margin-bottom: 12px;">⭐ <?php echo sanitize($svc_b['rating']); ?> · <?php echo sanitize($svc_b['reviews_count'] ?? '0'); ?> ulasan Google</p>
                <?php endif; ?>
                <?php if (!empty($svc_b['advantages'])): ?>
                <ul class="card-features-list">
                    <?php foreach (array_slice($svc_b['advantages'], 0, 5) as $svc_adv): ?>
                    <li><?php echo sanitize($svc_adv); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                <?php if (!empty($svc_b['map_embed'])): ?>
                <div style="margin-top: auto; padding-top: 16px; border-radius: var(--radius-sm); overflow: hidden;">
                    <iframe src="<?php echo sanitize($svc_b['map_embed']); ?>" width="100%" height="180" style="border:0; display:block; border-radius: var(--radius-sm);" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Peta <?php echo sanitize($svc_b_map_title); ?>"></iframe>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
