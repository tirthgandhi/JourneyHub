<?php
/**
 * Delete Trip Activity API
 * 
 * POST /api/activities/delete.php
 * 
 * Removes an activity from a trip stop
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
    $trip_activity_id = isset($input['trip_activity_id']) ? (int)$input['trip_activity_id'] : 0;
    
    if (!$trip_activity_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'trip_activity_id is required']);
        exit;
    }
    
    // Ownership verification via full chain
    $ownerCheck = getPDO()->prepare(
        "SELECT ta.id
         FROM trip_activities ta
         JOIN trip_stops ts ON ts.id = ta.trip_stop_id
         JOIN trips t ON t.id = ts.trip_id
         WHERE ta.id = ? AND t.user_id = ?"
    );
    $ownerCheck->execute([$trip_activity_id, $_SESSION['user_id']]);
    
    if (!$ownerCheck->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied or trip activity not found']);
        exit;
    }
    
    // Delete the trip activity
    $stmt = getPDO()->prepare("DELETE FROM trip_activities WHERE id = ?");
    $stmt->execute([$trip_activity_id]);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    error_log("Delete activity error: " . $e->getMessage());
}
