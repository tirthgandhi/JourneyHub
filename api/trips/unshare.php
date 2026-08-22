<?php
/**
 * Unshare Trip API
 * 
 * POST /api/trips/unshare.php
 * 
 * Disables public sharing for a trip by setting is_public = 0
 * Keeps the share_token for potential reuse
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
    // Parse JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required field
    $trip_id = isset($input['trip_id']) ? (int)$input['trip_id'] : 0;
    
    if (!$trip_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'trip_id is required']);
        exit;
    }
    
    // Ownership verification
    $stmt = getPDO()->prepare(
        "SELECT id FROM trips WHERE id = ? AND user_id = ?"
    );
    $stmt->execute([$trip_id, $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied or trip not found']);
        exit;
    }
    
    // Disable public sharing (keep share_token for potential reuse)
    $updateStmt = getPDO()->prepare(
        "UPDATE trips SET is_public = 0 WHERE id = ? AND user_id = ?"
    );
    $updateStmt->execute([$trip_id, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    error_log("Unshare trip error: " . $e->getMessage());
}
