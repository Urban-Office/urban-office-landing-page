<?php
/**
 * Automation API — Token Setup (CLI ONLY)
 * ------------------------------------------------------------------
 * Generates (or rotates) the shared automation token stored in the
 * `settings` table, and optionally wires the AI-writer author_id.
 *
 * Usage:
 *   php _api/_setup_token.php            # create/rotate token
 *   php _api/_setup_token.php 2          # ...and set automation_author_id = 2
 *
 * The token is printed ONCE. Copy it into your n8n credential as the
 * request header:  X-Automation-Token: <token>
 */

require_once __DIR__ . '/../inc/database.php'; // pulls config.php (CLI-safe, no redirect interceptor)

if (php_sapi_name() !== 'cli') {
    header('HTTP/1.1 403 Forbidden');
    exit('This setup script runs on the command line only.');
}

function setting_put(string $name, string $value): void {
    $exists = Database::fetch("SELECT id FROM settings WHERE name = ?", [$name]);
    if ($exists) {
        Database::query("UPDATE settings SET value = ? WHERE name = ?", [$value, $name]);
    } else {
        Database::insert("INSERT INTO settings (name, value) VALUES (?, ?)", [$name, $value]);
    }
}

try {
    $token   = bin2hex(random_bytes(24)); // 48-char hex
    $existed = (bool)Database::fetch("SELECT id FROM settings WHERE name = 'automation_api_token'");
    setting_put('automation_api_token', $token);

    echo $existed
        ? "Token automation DI-ROTATE (token lama tidak berlaku lagi).\n"
        : "Token automation dibuat.\n";
    echo "\n  TOKEN: {$token}\n\n";
    echo "Simpan sebagai header di n8n:  X-Automation-Token: {$token}\n";
    echo "(Token ini tidak ditampilkan lagi — simpan sekarang.)\n";

    // Optional: set automation author id from argv
    if (!empty($argv[1])) {
        if (ctype_digit($argv[1])) {
            $uid = (int)$argv[1];
            $u = Database::fetch("SELECT id, username FROM users WHERE id = ?", [$uid]);
        } else {
            $name = trim($argv[1]);
            $u = Database::fetch("SELECT id, username FROM users WHERE LOWER(username) = LOWER(?)", [$name]);
            if (!$u) {
                $email = strtolower(preg_replace('/[^a-z0-9]/', '', $name)) . '@urbanoffice.co.id';
                $dummy_pass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
                $newId = (int)Database::insert(
                    "INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, 'editor', 1)",
                    [$name, $dummy_pass, $email]
                );
                $u = ['id' => $newId, 'username' => $name];
            }
            $uid = (int)$u['id'];
        }

        if ($u) {
            setting_put('automation_author_id', (string)$uid);
            echo "\nAuthor automation berhasil diset ke ID #{$uid} ({$u['username']}).\n";
        }
    } else {
        $cur = Database::fetch("SELECT value FROM settings WHERE name = 'automation_author_id'");
        if (!$cur) {
            echo "\nCatatan: automation_author_id belum diset. Endpoint akan memakai fallback\n";
            echo "(user 'AI/writer/bot' bila ada, atau user id terkecil). Set eksplisit dgn:\n";
            echo "  php _api/_setup_token.php <user_id>\n";
        }
    }
    echo "\nSelesai.\n";
} catch (Exception $e) {
    fwrite(STDERR, "GAGAL: " . $e->getMessage() . "\n");
    exit(1);
}
