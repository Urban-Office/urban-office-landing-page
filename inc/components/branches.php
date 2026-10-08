<?php
/**
 * Branches Component Template
 * Displays photo and name/location of each Urban Office branch
 */

$branches = [
    [
        'name' => 'Urban Office - MERR',
        'city' => 'Surabaya',
        'location' => 'Surabaya Timur',
        'slug' => 'surabaya',
        'image' => BASE_URL . 'assets/images/branch/merr new.png'
    ],
    [
        'name' => 'Urban Office - Klampis',
        'city' => 'Surabaya',
        'location' => 'Surabaya Timur',
        'slug' => 'surabaya-timur',
        'image' => BASE_URL . 'assets/images/branch/klampis.png'
    ],
    [
        'name' => 'Urban Office - Grand Sungkono Lagoon',
        'city' => 'Surabaya',
        'location' => 'Surabaya Barat',
        'slug' => 'surabaya-barat',
        'image' => BASE_URL . 'assets/images/branch/gsl new.png'
    ],
    [
        'name' => 'Urban Office - Fatmawati',
        'city' => 'Jakarta',
        'location' => 'Jakarta Selatan',
        'slug' => 'jakarta',
        'image' => BASE_URL . 'assets/images/branch/urban office fatmawati.webp'
    ],
    [
        'name' => 'Urban Office - Gorebiz',
        'city' => 'Jakarta',
        'location' => 'Jakarta Timur',
        'slug' => 'jakarta-timur',
        'image' => BASE_URL . 'assets/images/branch/gorebiz.png'
    ],
    [
        'name' => 'Urban Office - PTGM Tower',
        'city' => 'Gresik',
        'location' => 'Gresik',
        'slug' => 'gresik',
        'image' => BASE_URL . 'assets/images/branch/ptgm.png'
    ],
    [
        'name' => 'Urban Office - Medan',
        'city' => 'Medan',
        'location' => 'Sumatera Utara',
        'slug' => 'medan',
        'image' => BASE_URL . 'assets/images/branch/medan.png'
    ],
    [
        'name' => 'Urban Office - Malang',
        'city' => 'Malang',
        'location' => 'Jawa Timur',
        'slug' => 'malang',
        'image' => BASE_URL . 'assets/images/branch/Malang.jpeg'
    ]
];

$city_counts = [];
foreach ($branches as $branch) {
    $city = $branch['city'];
    if (!isset($city_counts[$city])) {
        $city_counts[$city] = 0;
    }
    $city_counts[$city]++;
}

$default_city = 'Surabaya';
$default_city_slug = strtolower($default_city);
?>
<section class="branches-section" data-branch-filter>
    <div class="container">
        <div class="text-left">
            <span class="badge" style="background-color: hsl(var(--clr-primary)); color: #FFFFFF;">Cabang Kami</span>
            <h2 style="color: #111111;">Urban Office</h2>
            <p style="color: #444444;">Urban Office hadir di berbagai lokasi strategis untuk mendukung produktivitas dan pertumbuhan bisnis Anda.</p>
        </div>

        <div class="branch-city-panel">
            <div class="branch-city-tabs" role="tablist" aria-label="Filter cabang berdasarkan kota">
                <?php foreach ($city_counts as $city => $count): 
                    $city_slug = strtolower($city);
                    $is_active = $city === $default_city;
                ?>
                    <button type="button" role="tab" class="branch-city-btn<?php echo $is_active ? ' active' : ''; ?>" data-branch-city="<?php echo sanitize($city_slug); ?>" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
                        <span class="branch-city-icon"><i class="bi bi-geo-alt-fill"></i></span>
                        <span class="branch-city-copy">
                            <strong><?php echo sanitize($city); ?></strong>
                            <small><?php echo (int) $count; ?> cabang</small>
                        </span>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="branch-filter-container" id="branch-filter" style="display: flex; justify-content: flex-start; flex-wrap: wrap; gap: 8px; margin-top: 20px;">
                <!-- Filled by JS -->
            </div>
            <p class="branch-filter-summary" aria-live="polite" style="margin-top: 15px;">
                Menampilkan <?php echo (int) ($city_counts[$default_city] ?? 0); ?> cabang di <?php echo sanitize($default_city); ?>
            </p>
        </div>
        
        <div class="branches-grid">
            <?php foreach ($branches as $branch): ?>
                <?php
                    $branch_city_slug = strtolower($branch['city']);
                    $is_visible = $branch['city'] === $default_city;
                ?>
                <div class="branch-card<?php echo $is_visible ? '' : ' is-hidden'; ?>" data-branch-city="<?php echo sanitize($branch_city_slug); ?>" data-branch-slug="<?php echo isset($branch['slug']) ? sanitize($branch['slug']) : ''; ?>">
                    <div style="display: flex; flex-direction: column; height: 100%;">
                    <div style="display: flex; flex-direction: column; flex-grow: 1;">
                        <div class="branch-img-wrap">
                            <img src="<?php echo $branch['image']; ?>" alt="<?php echo sanitize($branch['name'] . ' (' . $branch['location'] . ')'); ?>" loading="lazy">
                        </div>
                        <div class="branch-body" style="flex-grow: 1;">
                            <h3><?php echo sanitize($branch['name']); ?></h3>
                            <p class="branch-loc"><i class="bi bi-geo-alt-fill"></i> <?php echo sanitize($branch['location']); ?></p>
                        </div>
                    </div>
                    <?php
                        $wa_message = "Halo Urban Office, saya ingin bertanya mengenai ketersediaan layanan dan harga untuk lokasi berikut:\n\nCabang: *" . $branch['name'] . "*\nLokasi: " . $branch['location'];
                        $wa_url = "https://api.whatsapp.com/send?phone=6285107620100&text=" . urlencode($wa_message);
                    ?>
                    <div class="branch-card-footer" style="padding: 0 16px 16px 16px; display: flex; gap: 8px;">
                        <a href="<?php echo BASE_URL; ?>lokasi-urban-office/<?php echo isset($branch['slug']) ? $branch['slug'] : ''; ?>/" class="btn btn-primary" style="flex: 1; justify-content: center; font-size: 12px; padding: 12px 10px; text-align: center; white-space: nowrap;">
                            Lihat Detail Lokasi
                        </a>
                        <a href="<?php echo $wa_url; ?>" target="_blank" rel="noopener" class="btn btn-outline btn-wa-icon-only" title="Tanya via WhatsApp" style="width: 40px; height: 40px; min-width: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-whatsapp" style="font-size: 1.2rem; margin: 0;"></i>
                        </a>
                    </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
