<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'cities' => []]);
    exit;
}

$stmt = $pdo->prepare('
    SELECT id, city_name, state_name, country_name, cost_index, popularity
    FROM cities
    WHERE city_name LIKE ? OR state_name LIKE ? OR country_name LIKE ?
    ORDER BY popularity DESC, city_name ASC
    LIMIT 20
');
$searchTerm = '%' . $query . '%';
$stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
$cities = $stmt->fetchAll();

echo json_encode(['success' => true, 'cities' => $cities]);
