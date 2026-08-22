<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$activityId = (int)($_POST['activity_id'] ?? 0);

// Validate ownership before delete
$stmt = $pdo->prepare('
    SELECT ta.id FROM trip_activities ta
    JOIN trip_stops ts ON ts.id = ta.trip_stop_id
    JOIN trips t ON t.id = ts.trip_id
    WHERE ta.id = ? AND t.user_id = ?
');
$stmt->execute([$activityId, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Delete
$stmt = $pdo->prepare('DELETE FROM trip_activities WHERE id = ?');
$stmt->execute([$activityId]);

echo json_encode(['success' => true]);
