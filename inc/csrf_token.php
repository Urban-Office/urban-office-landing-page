<?php
/**
 * Dynamic CSRF Token Endpoint
 * Returns the current visitor's session CSRF token without caching so static HTML
 * cached pages (cache/*.html) never submit a stale CSRF token from another visitor's session.
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

echo json_encode([
    'success'    => true,
    'csrf_token' => $_SESSION['csrf_token'] ?? '',
]);
