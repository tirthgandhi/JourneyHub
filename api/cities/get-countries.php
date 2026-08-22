<?php
/**
 * Get Countries API
 * GET endpoint to retrieve distinct countries for filter dropdown
 * 
 * No parameters required
 * Returns JSON with countries array ordered alphabetically
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
    $pdo = getDBConnection();
    
    // Get distinct countries ordered alphabetically
    $stmt = $pdo->prepare("SELECT DISTINCT country_name FROM cities ORDER BY country_name ASC");
    $stmt->execute();
    $countries = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'countries' => $countries
    ]);
    
} catch (Exception $e) {
    // Never expose raw database errors to client
    error_log("Get countries error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load countries'
    ]);
}
?>