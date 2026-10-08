<?php
/**
 * Cloudinary credentials setup (CLI ONLY).
 * Stores cloud_name / api_key / api_secret in the `settings` table so ingest.php
 * uploads generated images to Cloudinary (CDN). Without these, storage stays local.
 *
 * Usage:
 *   php _api/_setup_cloudinary.php <cloud_name> <api_key> <api_secret>
 *   php _api/_setup_cloudinary.php            # show current status
 *   php _api/_setup_cloudinary.php --clear    # disable (revert to local storage)
 */

require_once __DIR__ . '/../inc/database.php';

if (php_sapi_name() !== 'cli') {
    header('HTTP/1.1 403 Forbidden');
    exit('CLI only.');
}

function s_get(string $n): string {
    $r = Database::fetch("SELECT value FROM settings WHERE name = ?", [$n]);
    return $r ? (string)$r['value'] : '';
}
function s_put(string $n, string $v): void {
    if (Database::fetch("SELECT id FROM settings WHERE name = ?", [$n])) {
        Database::query("UPDATE settings SET value = ? WHERE name = ?", [$v, $n]);
    } else {
        Database::insert("INSERT INTO settings (name, value) VALUES (?, ?)", [$n, $v]);
    }
}

$keys = ['cloudinary_cloud_name', 'cloudinary_api_key', 'cloudinary_api_secret'];

if (!empty($argv[1]) && $argv[1] === '--clear') {
    foreach ($keys as $k) s_put($k, '');
    echo "Cloudinary dinonaktifkan — penyimpanan kembali ke lokal (assets/images/blog/).\n";
    exit(0);
}

if (count($argv) >= 4) {
    s_put('cloudinary_cloud_name', trim($argv[1]));
    s_put('cloudinary_api_key',    trim($argv[2]));
    s_put('cloudinary_api_secret', trim($argv[3]));
    echo "Cloudinary DISET:\n";
    echo "  cloud_name : " . trim($argv[1]) . "\n";
    echo "  api_key    : " . trim($argv[2]) . "\n";
    echo "  api_secret : " . str_repeat('*', max(0, strlen(trim($argv[3])) - 4)) . substr(trim($argv[3]), -4) . "\n";
    echo "Gambar artikel berikutnya akan diunggah ke Cloudinary. Fallback ke lokal jika upload gagal.\n";
    exit(0);
}

// Status
$cloud = s_get('cloudinary_cloud_name');
echo "Status Cloudinary: " . ($cloud !== '' ? "AKTIF (cloud_name={$cloud})" : "belum diset (penyimpanan lokal)") . "\n";
echo "Set dengan: php _api/_setup_cloudinary.php <cloud_name> <api_key> <api_secret>\n";
