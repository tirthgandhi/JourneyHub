<?php
/**
 * Add Activity to Trip API
 * 
 * POST /api/activities/add.php
 * 
 * Adds a master activity to a specific trip stop with date/time
 * Creates entry in trip_activities table
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
    $trip_stop_id = isset($input['trip_stop_id']) ? (int)$input['trip_stop_id'] : 0;
    $activity_id = isset($input['activity_id']) ? (int)$input['activity_id'] : 0;
    $activity_date = isset($input['activity_date']) ? trim($input['activity_date']) : '';
    $activity_time = isset($input['activity_time']) ? trim($input['activity_time']) : null;
    
    if (!$trip_stop_id || !$activity_id || !$activity_date) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'trip_stop_id, activity_id, and activity_date are required']);
        exit;
    }
    
    // Ownership verification + fetch stop details and city_id
    $ownerCheck = getPDO()->prepare(
        "SELECT ts.id, ts.start_date, ts.end_date, ts.city_id
         FROM trip_stops ts
         JOIN trips t ON t.id = ts.trip_id
         WHERE ts.id = ? AND t.user_id = ?"
    );
    $ownerCheck->execute([$trip_stop_id, $_SESSION['user_id']]);
    $stop = $ownerCheck->fetch();
    
    if (!$stop) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied or stop not found']);
        exit;
    }
    
    // Verify activity exists and belongs to the same city as the stop
    $activityCheck = getPDO()->prepare(
        "SELECT id, name, type, cost, duration, city_id
         FROM activities
         WHERE id = ?"
    );
    $activityCheck->execute([$activity_id]);
    $activity = $activityCheck->fetch();
    
    if (!$activity) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Activity not found']);
        exit;
    }
    
    // Validate activity belongs to stop's city
    if ($activity['city_id'] != $stop['city_id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Activity does not belong to this stop\'s city']);
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
    if ($activity_date < $stop['start_date'] || $activity_date > $stop['end_date']) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'error' => 'Activity date must be between ' . 
                       date('M d', strtotime($stop['start_date'])) . ' and ' . 
                       date('M d', strtotime($stop['end_date']))
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
    
    // Check for duplicate: same activity on same date in same stop
    $dupCheck = getPDO()->prepare(
        "SELECT id FROM trip_activities 
         WHERE trip_stop_id = ? AND activity_id = ? AND activity_date = ?"
    );
    $dupCheck->execute([$trip_stop_id, $activity_id, $activity_date]);
    
    if ($dupCheck->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'This activity is already added for this date']);
        exit;
    }
    
    // Insert into trip_activities
    $stmt = getPDO()->prepare(
        "INSERT INTO trip_activities (trip_stop_id, activity_id, activity_date, activity_time)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$trip_stop_id, $activity_id, $activity_date, $activity_time]);
    
    $new_id = getPDO()->lastInsertId();
    
    // Return the newly added trip activity with full details
    echo json_encode([
        'success' => true,
        'trip_activity' => [
            'id' => (int)$new_id,
            'activity_id' => $activity_id,
            'name' => $activity['name'],
            'type' => $activity['type'],
            'activity_date' => $activity_date,
            'activity_time' => $activity_time,
            'cost' => $activity['cost'],
            'duration' => $activity['duration']
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    error_log("Add activity error: " . $e->getMessage());
}
