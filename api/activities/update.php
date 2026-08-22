<?php
/**
 * Update Trip Activity API
 * 
 * POST /api/activities/update.php
 * 
 * Updates the date/time of an existing trip activity
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
    
    // Validate required fields
    $trip_activity_id = isset($input['trip_activity_id']) ? (int)$input['trip_activity_id'] : 0;
    $activity_date = isset($input['activity_date']) ? trim($input['activity_date']) : '';
    $activity_time = isset($input['activity_time']) ? trim($input['activity_time']) : null;
    
    if (!$trip_activity_id || !$activity_date) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'trip_activity_id and activity_date are required']);
        exit;
    }
    
    // Ownership verification + fetch stop date range
    $ownerCheck = getPDO()->prepare(
        "SELECT ta.id, ts.start_date, ts.end_date
         FROM trip_activities ta
         JOIN trip_stops ts ON ts.id = ta.trip_stop_id
         JOIN trips t ON t.id = ts.trip_id
         WHERE ta.id = ? AND t.user_id = ?"
    );
    $ownerCheck->execute([$trip_activity_id, $_SESSION['user_id']]);
    $tripActivity = $ownerCheck->fetch();
    
    if (!$tripActivity) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied or trip activity not found']);
        exit;
    }
    
    // Validate activity_date format
    $dateObj = DateTime::createFromFormat('Y-m-d', $activity_date);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $activity_date) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid date format. Use YYYY-MM-DD']);
        exit;
    }
    
    // Validate date is within stop's date range
    if ($activity_date < $tripActivity['start_date'] || $activity_date > $tripActivity['end_date']) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'error' => 'Activity date must be between ' . 
                       date('M d', strtotime($tripActivity['start_date'])) . ' and ' . 
                       date('M d', strtotime($tripActivity['end_date']))
        ]);
        exit;
    }
    
    // Validate activity_time if provided
    if ($activity_time !== null && $activity_time !== '') {
        $timeObj = DateTime::createFromFormat('H:i', $activity_time);
        if (!$timeObj || $timeObj->format('H:i') !== $activity_time) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid time format. Use HH:MM (24-hour)']);
            exit;
        }
        // Convert to HH:MM:SS for database
        $activity_time = $activity_time . ':00';
    } else {
        $activity_time = null;
    }
    
    // Update the trip activity
    $stmt = getPDO()->prepare(
        "UPDATE trip_activities 
         SET activity_date = ?, activity_time = ?
         WHERE id = ?"
    );
    $stmt->execute([$activity_date, $activity_time, $trip_activity_id]);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    error_log("Update activity error: " . $e->getMessage());
}
