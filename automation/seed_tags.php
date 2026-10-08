<?php
/**
 * Seed a curated tag vocabulary for the automation (idempotent, CLI only).
 * The AI is restricted to EXISTING tags, so this list defines what it can pick.
 * Run:  php automation/seed_tags.php
 * Safe to run repeatedly — existing tags (by slug) are skipped.
 */

require_once __DIR__ . '/../inc/database.php';

if (php_sapi_name() !== 'cli') {
    header('HTTP/1.1 403 Forbidden');
    exit('CLI only.');
}

function tag_slugify(string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

// Curated, clean, non-overlapping tags relevant to Urban Office.
$tags = [
    'Virtual Office', 'Kantor Virtual', 'Sewa Kantor', 'Coworking Space', 'Private Office',
    'Serviced Office', 'Meeting Room', 'Ruang Meeting', 'Event Space', 'Sharing Office',
    'Alamat Bisnis', 'Domisili Usaha', 'Legalitas Usaha', 'Pendirian PT', 'PT Perorangan',
    'Pendirian CV', 'PMA', 'NIB', 'NPWP', 'Izin Usaha',
    'UMKM', 'Startup', 'Wirausaha', 'Freelancer', 'Remote Working',
    'WFA', 'Produktivitas Kerja', 'Efisiensi Biaya', 'Ekspansi Bisnis', 'Kantor Fleksibel',
    'Surabaya', 'Jakarta', 'Jakarta Selatan', 'Gresik', 'Malang', 'Medan',
    'Tips Bisnis', 'Perpajakan', 'Perizinan Usaha',
];

$added = 0; $skipped = 0;
foreach ($tags as $name) {
    $slug = tag_slugify($name);
    $exists = Database::fetch(
        "SELECT id FROM tags WHERE slug = ? OR LOWER(name) = LOWER(?)",
        [$slug, $name]
    );
    if ($exists) { $skipped++; continue; }
    try {
        Database::insert("INSERT INTO tags (name, slug) VALUES (?, ?)", [$name, $slug]);
        $added++;
        echo "  + {$name}  (/{$slug})\n";
    } catch (Exception $e) {
        echo "  ! gagal: {$name} — " . $e->getMessage() . "\n";
    }
}

$total = Database::fetch("SELECT COUNT(*) c FROM tags")['c'];
echo "\nSelesai. Ditambahkan: {$added}, dilewati (sudah ada): {$skipped}. Total tag sekarang: {$total}.\n";
