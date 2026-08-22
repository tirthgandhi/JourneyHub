<?php
/**
 * API: Delete Trip — JourneyHub
 *
 * POST handler. Deletes a trip after verifying ownership.
 * Also removes the cover image file if it exists. Returns JSON.
 */

header('Content-Type: application/json');

session_start();

// --- Auth check ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$userId = $_SESSION['user_id'];
$tripId = (int) ($_POST['trip_id'] ?? 0);

if ($tripId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid trip ID.']);
    exit;
}

// --- Fetch trip to verify ownership and get cover image path ---
$stmt = $pdo->prepare('SELECT id, cover_image FROM trips WHERE id = ? AND user_id = ?');
$stmt->execute([$tripId, $userId]);
$trip = $stmt->fetch();

if (!$trip) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Trip not found or access denied.']);
    exit;
}

// --- Delete the trip ---
$stmt = $pdo->prepare('DELETE FROM trips WHERE id = ? AND user_id = ?');
$stmt->execute([$tripId, $userId]);

// --- Remove cover image file if it exists ---
if ($trip['cover_image']) {
    $coverPath = __DIR__ . '/../../assets/images/covers/' . $trip['cover_image'];
    if (file_exists($coverPath)) {
        unlink($coverPath);
    }
}

echo json_encode(['success' => true]);
