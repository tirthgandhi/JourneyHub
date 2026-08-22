<?php
/**
 * Calendar List API
 * GET endpoint to fetch all trip activities grouped by date
 * 
 * Parameter: trip_id (required)
 * Returns activities organized by date for calendar display
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
    $pdo = getPDO();
    
    // Get and validate trip_id
    $trip_id = isset($_GET['trip_id']) ? (int)$_GET['trip_id'] : 0;
    
    if (!$trip_id) {
        echo json_encode(['success' => false, 'error' => 'Trip ID is required']);
        exit;
    }
    
    // Verify trip exists and belongs to logged-in user
    $stmt = $pdo->prepare("SELECT start_date, end_date FROM trips WHERE id = ? AND user_id = ?");
    $stmt->execute([$trip_id, $_SESSION['user_id']]);
    $trip = $stmt->fetch();
    
    if (!$trip) {
        echo json_encode(['success' => false, 'error' => 'Trip not found or access denied']);
        exit;
    }
    
    // Get all activities for this trip
    $stmt = $pdo->prepare("
        SELECT ta.activity_date, ta.activity_time,
               a.name, a.type, a.cost,
               c.city_name
        FROM trip_activities ta
        JOIN trip_stops ts ON ts.id = ta.trip_stop_id
        JOIN activities a ON a.id = ta.activity_id
        JOIN cities c ON c.id = ts.city_id
        WHERE ts.trip_id = ?
        ORDER BY ta.activity_date ASC, ta.activity_time ASC
    ");
    $stmt->execute([$trip_id]);
    $activities = $stmt->fetchAll();
    
    // Group activities by date
    $days = [];
    foreach ($activities as $activity) {
        $date = $activity['activity_date'];
        if (!isset($days[$date])) {
            $days[$date] = [];
        }
        
        $days[$date][] = [
            'time' => $activity['activity_time'],
            'name' => $activity['name'],
            'city' => $activity['city_name'],
            'cost' => $activity['cost'],
            'type' => $activity['type']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'trip_start' => $trip['start_date'],
        'trip_end' => $trip['end_date'],
        'days' => $days
    ]);
    
} catch (Exception $e) {
    error_log("Calendar list error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to load calendar data']);
}
?>