<!-- <?php
/**
 * Features & Facilities Component Template
 * Parameters:
 *  - $features_title: string (optional)
 *  - $features_list: array (optional) -> format: [['title' => '...', 'icon' => '...']]
 */

$default_features = [
    [
        'title' => 'Alamat Bisnis Prestisius',
        'icon' => '🏢'
    ],
    [
        'title' => 'Resepsionis Profesional',
        'icon' => '👩‍💼'
    ],
    [
        'title' => 'Internet Kecepatan Tinggi',
        'icon' => '⚡'
    ],
    [
        'title' => 'Mail & Visitor Handling',
        'icon' => '📦'
    ],
    [
        'title' => 'Ruang Meeting Gratis',
        'icon' => '🤝'
    ],
    [
        'title' => 'Akses Coworking Space',
        'icon' => '☕'
    ]
];

$title = $features_title ?? 'Fasilitas di Urban Office';
$list = $features_list ?? $default_features;

/**
 * Translates standard emojis into vector Bootstrap Icons to support dynamic CSS coloring
 */
if (!function_exists('get_vector_icon')) {
    function get_vector_icon(string $icon): string {
        $icon = trim($icon);
        
        // If already formatted as HTML, return as-is
        if (strpos($icon, '<') !== false) {
            return $icon;
        }
        
        $mapping = [
            '🏢' => '<i class="bi bi-building"></i>',
            '👩‍💼' => '<i class="bi bi-person-workspace"></i>',
            '⚡' => '<i class="bi bi-lightning-charge"></i>',
            '📦' => '<i class="bi bi-box-seam"></i>',
            '🤝' => '<i class="bi bi-people"></i>',
            '☕' => '<i class="bi bi-cup-hot"></i>',
            '✉️' => '<i class="bi bi-envelope-paper"></i>',
            '✉' => '<i class="bi bi-envelope-paper"></i>',
            '📞' => '<i class="bi bi-telephone"></i>',
            '🏷️' => '<i class="bi bi-tag"></i>',
            '🏷' => '<i class="bi bi-tag"></i>',
            '📄' => '<i class="bi bi-file-earmark-text"></i>',
            '💼' => '<i class="bi bi-briefcase"></i>',
            '✒️' => '<i class="bi bi-pencil-square"></i>',
            '✒' => '<i class="bi bi-pencil-square"></i>',
            '🎧' => '<i class="bi bi-headset"></i>',
            '📶' => '<i class="bi bi-wifi"></i>'
        ];
        
        return $mapping[$icon] ?? '<i class="bi bi-check-circle"></i>';
    }
}
?>
<section class="section" style="background-color: #FFFFFF; padding: 80px 0;">
    <div class="container">
        <div class="text-center" style="margin-bottom: 50px;">
            <h2 class="section-title"><?php echo sanitize($title); ?></h2>
        </div>
        
        <div class="facilities-grid-centered">
            <?php 
            $index = 0;
            foreach ($list as $feat): 
                // Alternate backgrounds (Orange `#FF6B00` vs Gold `#FFD700`)
                $bg_color = ($index % 2 === 0) ? '#FF6B00' : '#FFD700';
                
                // White color for icons inside orange boxes, Orange color for icons inside yellow boxes
                $text_color = ($index % 2 === 0) ? '#FFFFFF' : '#FF6B00';
                
                $index++;
                $vector_icon = get_vector_icon($feat['icon'] ?? '');
            ?>
                <div class="facility-grid-item">
                    <div class="facility-icon-box" style="background-color: <?php echo $bg_color; ?>; color: <?php echo $text_color; ?>;">
                        <?php echo $vector_icon; ?>
                    </div>
                    <h4 class="facility-grid-label"><?php echo sanitize($feat['title']); ?></h4>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section> -->
