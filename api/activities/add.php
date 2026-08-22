<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$stopId = (int)($_POST['stop_id'] ?? 0);
$activityId = (int)($_POST['activity_id'] ?? 0);
$activityDate = trim($_POST['activity_date'] ?? '');
$activityTime = trim($_POST['activity_time'] ?? null);

// Validate ownership
$stmt = $pdo->prepare('
    SELECT ts.id FROM trip_stops ts
    JOIN trips t ON t.id = ts.trip_id
    WHERE ts.id = ? AND t.user_id = ?
');
$stmt->execute([$stopId, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Insert activity
$stmt = $pdo->prepare('
    INSERT INTO trip_activities (trip_stop_id, activity_id, activity_date, activity_time)
    VALUES (?, ?, ?, ?)
');
$stmt->execute([$stopId, $activityId, $activityDate, $activityTime]);

echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
