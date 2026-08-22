<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

$cityId = isset($_GET['city_id']) ? (int)$_GET['city_id'] : 0;

if (!$cityId) {
    echo json_encode(['success' => false, 'activities' => []]);
    exit;
}

$stmt = $pdo->prepare('
    SELECT id, name, type, description, cost, duration
    FROM activities
    WHERE city_id = ?
    ORDER BY type, name
');
$stmt->execute([$cityId]);
$activities = $stmt->fetchAll();

echo json_encode(['success' => true, 'activities' => $activities]);
