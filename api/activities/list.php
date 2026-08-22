<?php
/**
 * List Trip Activities API
 * 
 * GET /api/activities/list.php
 * 
 * Returns all activities already added to a specific trip stop
 * Joins trip_activities with activities table for full details
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
    $stop_id = isset($_GET['stop_id']) ? (int)$_GET['stop_id'] : 0;
    
    if (!$stop_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'stop_id is required']);
        exit;
    }
    
    // Ownership verification: stop must belong to user's trip
    $ownerCheck = getPDO()->prepare(
        "SELECT ts.id 
         FROM trip_stops ts
         JOIN trips t ON t.id = ts.trip_id
         WHERE ts.id = ? AND t.user_id = ?"
    );
    $ownerCheck->execute([$stop_id, $_SESSION['user_id']]);
    
    if (!$ownerCheck->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }
    
    // Fetch trip activities with full activity details
    $stmt = getPDO()->prepare(
        "SELECT ta.id, ta.activity_date, ta.activity_time,
                a.id as activity_id, a.name, a.type, a.description, a.cost, a.duration, a.image
         FROM trip_activities ta
         JOIN activities a ON a.id = ta.activity_id
         WHERE ta.trip_stop_id = ?
         ORDER BY ta.activity_date ASC, ta.activity_time ASC"
    );
    $stmt->execute([$stop_id]);
    $trip_activities = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'trip_activities' => $trip_activities
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    error_log("List trip activities error: " . $e->getMessage());
}
