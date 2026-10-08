<?php
/**
 * AJAX Pop-up View & Click Event Tracker
 * Increments view and click statistics for pop-up campaigns
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || empty($data['popup_id']) || empty($data['event'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid payload']);
    exit;
}

$popup_id = intval($data['popup_id']);
$event = strtolower(trim($data['event']));

if (!in_array($event, ['view', 'click'], true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid event type']);
    exit;
}

try {
    if ($event === 'view') {
        Database::query("UPDATE popups SET views_count = views_count + 1 WHERE id = ?", [$popup_id]);
    } else {
        Database::query("UPDATE popups SET clicks_count = clicks_count + 1 WHERE id = ?", [$popup_id]);
    }
    echo json_encode(['success' => true]);
    exit;
} catch (Exception $e) {
    error_log("Failed tracking popup event: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}
