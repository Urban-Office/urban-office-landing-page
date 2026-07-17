<?php
/**
 * AJAX Lead Capture Handler
 * Receives POST requests, validates CSRF tokens, and saves leads in SQL
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

// Force POST request only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Read JSON input stream
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid Input Data']);
    exit;
}

// 1. CSRF Token Validation Check
$csrf_token = $data['csrf_token'] ?? '';
if (!verify_csrf_token($csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Validasi CSRF gagal. Silakan muat ulang halaman.']);
    exit;
}

// 2. Honeypot check (Confirm confirm email field is empty)
if (!empty($data['email_confirm'])) {
    // Treat as bot, return silent success
    echo json_encode(['success' => true, 'message' => 'Lead captured.']);
    exit;
}

// 3. Extract and sanitize parameters
$name = sanitize($data['name'] ?? '');
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
$phone = sanitize($data['phone'] ?? '');
$service = sanitize($data['service'] ?? '');
$message = sanitize($data['message'] ?? '');
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// 4. Validate mandatory values
if (empty($name) || !$email || empty($phone) || empty($service)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Harap isi semua kolom wajib dengan data valid.']);
    exit;
}

try {
    // 5. Insert Lead entry into SQL table
    Database::insert(
        "INSERT INTO leads (name, phone, email, service, message, ip_address, status) VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$name, $phone, $email, $service, $message, $ip_address, 'unread']
    );

    // 6. Return response success
    echo json_encode(['success' => true, 'message' => 'Terima kasih! Pesan Anda telah kami terima.']);
    exit;

} catch (Exception $e) {
    error_log("Failed to insert lead: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pesan ke database. Silakan hubungi via WhatsApp.']);
    exit;
}
