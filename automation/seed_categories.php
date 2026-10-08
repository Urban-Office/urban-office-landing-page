<?php
/**
 * Seed the canonical CLEAN category set (idempotent, CLI only).
 * FOR LOCAL / fresh installs only. Adds any missing categories by slug.
 *
 * DO NOT run this on production that already has the messy 17-category set —
 * use automation/consolidate_categories.php there instead (it migrates 17 -> 8
 * with post reassignment + 301 redirects). Plain seeding on production would
 * just ADD to the existing mess.
 *
 * Run:  php automation/seed_categories.php
 */

require_once __DIR__ . '/../inc/database.php';

if (php_sapi_name() !== 'cli') {
    header('HTTP/1.1 403 Forbidden');
    exit('CLI only.');
}

function cat_slugify(string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

// The 8 clean, broad categories: product-based + editorial (for 70% general).
$categories = [
    'Virtual Office',
    'Coworking Space',
    'Sewa Kantor',                 // private/serviced/sharing office rental
    'Meeting & Event Space',       // meeting room + event/seminar/workshop
    'Legalitas Usaha',             // PT/CV/PMA/perorangan/perizinan
    'Pajak & Akunting',
    'Tips Bisnis',                 // editorial (general RSS content)
    'Produktivitas',               // editorial
];

$added = 0; $skipped = 0;
foreach ($categories as $name) {
    $slug = cat_slugify($name);
    $exists = Database::fetch(
        "SELECT id FROM categories WHERE slug = ? OR LOWER(name) = LOWER(?)",
        [$slug, $name]
    );
    if ($exists) { $skipped++; continue; }
    Database::insert("INSERT INTO categories (name, slug) VALUES (?, ?)", [$name, $slug]);
    $added++;
    echo "  + {$name}  (/{$slug})\n";
}

echo "\nSelesai. Ditambahkan: {$added}, dilewati (sudah ada): {$skipped}.\n";
echo "Kategori sekarang:\n";
foreach (Database::fetchAll("SELECT id, name, slug FROM categories ORDER BY name") as $c) {
    echo "  #{$c['id']}  {$c['name']}  (/{$c['slug']})\n";
}
