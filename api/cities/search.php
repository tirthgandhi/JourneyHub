<?php
/**
 * City Search API
 * GET endpoint to search cities from MySQL database
 * 
 * Parameters:
 * - q (optional): Search term matched against city_name, state_name, country_name
 * - country (optional): Filter to exact country_name
 * 
 * Returns JSON with cities array ordered by popularity DESC, limited to 10 results
 */

header('Content-Type: application/json');

// Authentication check
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

try {
    // Get and validate parameters
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $country = isset($_GET['country']) ? trim($_GET['country']) : '';
    
    // If no search term and no country filter, return empty array
    if (strlen($q) < 2 && empty($country)) {
        echo json_encode(['success' => true, 'cities' => []]);
        exit;
    }
    
    // Build dynamic query
    $sql = "SELECT id, city_name, state_name, country_name, cost_index, popularity, image 
            FROM cities WHERE 1=1";
    $params = [];
    
    // Add search term condition
    if (strlen($q) >= 2) {
        $sql .= " AND (city_name LIKE ? OR state_name LIKE ? OR country_name LIKE ?)";
        $searchTerm = '%' . $q . '%';
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    }
    
    // Add country filter condition
    if (!empty($country)) {
        $sql .= " AND country_name = ?";
        $params[] = $country;
    }
    
    // Add ordering and limit
    $sql .= " ORDER BY popularity DESC LIMIT 10";
    
    // Execute query
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cities = $stmt->fetchAll();
    
    // Return results
    echo json_encode([
        'success' => true,
        'cities' => $cities
    ]);
    
} catch (Exception $e) {
    // Never expose raw database errors to client
    error_log("City search error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Search failed'
    ]);
}
?>