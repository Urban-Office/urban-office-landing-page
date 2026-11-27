<?php
/**
 * Admin CKEditor Image Upload Handler
 * CSRF-protected, MIME-verified, size-capped image upload endpoint returning JSON.
 */

define('ADMIN_JSON_ENDPOINT', true);
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['uploaded' => false, 'error' => ['message' => 'Metode tidak diizinkan.']]);
    exit;
}

$csrf_token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if (!verify_csrf_token($csrf_token)) {
    http_response_code(403);
    echo json_encode(['uploaded' => false, 'error' => ['message' => 'Validasi CSRF gagal. Silakan muat ulang halaman editor.']]);
    exit;
}

if (!isset($_FILES['upload'])) {
    http_response_code(400);
    echo json_encode(['uploaded' => false, 'error' => ['message' => 'Tidak ada file gambar yang dikirim.']]);
    exit;
}

$file = $_FILES['upload'];
$check = admin_validate_image_upload($file, 5 * 1024 * 1024);
if (!$check['ok']) {
    http_response_code(422);
    echo json_encode(['uploaded' => false, 'error' => ['message' => $check['error']]]);
    exit;
}

$newFileName = bin2hex(random_bytes(16)) . '.' . $check['ext'];
$uploadDir = DIR_ROOT . 'assets/images/blog/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$destPath = $uploadDir . $newFileName;
$relPath = 'assets/images/blog/' . $newFileName;

if (move_uploaded_file($file['tmp_name'], $destPath)) {
    try {
        Database::insert(
            "INSERT INTO media (file_name, file_path, file_type, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?)",
            [basename((string)$file['name']), $relPath, $check['mime'], (int)$file['size'], (int)$_SESSION['admin_user_id']]
        );
        log_activity((int)$_SESSION['admin_user_id'], 'upload_media', "Uploaded CKEditor image: " . basename((string)$file['name']) . " -> $relPath");
    } catch (Exception $e) {
        error_log("CKEditor media DB log failed: " . $e->getMessage());
    }

    echo json_encode([
        'uploaded' => true,
        'url'      => BASE_URL . $relPath,
    ]);
    exit;
}

http_response_code(500);
echo json_encode([
    'uploaded' => false,
    'error'    => ['message' => 'Gagal menyimpan file gambar di server.'],
]);
exit;
