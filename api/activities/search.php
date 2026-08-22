<?php
/**
 * Search Master Activities API
 * 
 * GET /api/activities/search.php
 * 
 * Search the master activities catalog by city with optional filters
 * Returns activities from the activities table (master data)
 */

header('Content-Type: application/json');
session_start();

// Authentication check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

try {
    // Validate required parameter
    $city_id = isset($_GET['city_id']) ? (int)$_GET['city_id'] : 0;
    
    if (!$city_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'city_id is required']);
        exit;
    }
    
    // Build query with optional filters
    $sql = "SELECT id, name, type, description, cost, duration, image
            FROM activities WHERE city_id = ?";
    $params = [$city_id];
    
    // Optional search term filter
    if (!empty($_GET['q'])) {
        $sql .= " AND name LIKE ?";
        $params[] = '%' . trim($_GET['q']) . '%';
    }
    
    // Optional type filter
    if (!empty($_GET['type'])) {
        $sql .= " AND type = ?";
        $params[] = trim($_GET['type']);
    }
    
    // Optional max cost filter
    if (!empty($_GET['max_cost']) && is_numeric($_GET['max_cost'])) {
        $sql .= " AND cost <= ?";
        $params[] = (float)$_GET['max_cost'];
    }
    
    // Order by cost and limit results
    $sql .= " ORDER BY cost ASC LIMIT 20";
    
    $stmt = getPDO()->prepare($sql);
    $stmt->execute($params);
    $activities = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    error_log("Search activities error: " . $e->getMessage());
}
