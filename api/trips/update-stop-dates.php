<?php
/**
 * Update Stop Dates API
 * POST endpoint to update start_date and end_date for a trip stop
 * 
 * Fields: stop_id, trip_id, start_date, end_date
 * Returns success confirmation
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get and validate input
    $stop_id = isset($_POST['stop_id']) ? (int)$_POST['stop_id'] : 0;
    $trip_id = isset($_POST['trip_id']) ? (int)$_POST['trip_id'] : 0;
    $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
    $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
    
    if (!$stop_id || !$trip_id || !$start_date || !$end_date) {
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        exit;
    }
    
    // 1. Verify ownership via JOIN
    $stmt = $pdo->prepare("
        SELECT ts.id FROM trip_stops ts
        JOIN trips t ON t.id = ts.trip_id
        WHERE ts.id = ? AND ts.trip_id = ? AND t.user_id = ?
    ");
    $stmt->execute([$stop_id, $trip_id, $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Stop not found or access denied']);
        exit;
    }
    
    // 2. Validate date formats
    $startDateTime = DateTime::createFromFormat('Y-m-d', $start_date);
    $endDateTime = DateTime::createFromFormat('Y-m-d', $end_date);
    
    if (!$startDateTime || !$endDateTime) {
        echo json_encode(['success' => false, 'error' => 'Invalid date format. Use YYYY-MM-DD']);
        exit;
    }
    
    // 3. Validate end_date >= start_date
    if ($endDateTime < $startDateTime) {
        echo json_encode(['success' => false, 'error' => 'End date must be on or after start date']);
        exit;
    }
    
    // 4. Get parent trip dates and validate stop dates are within trip range
    $stmt = $pdo->prepare("SELECT start_date, end_date FROM trips WHERE id = ? AND user_id = ?");
    $stmt->execute([$trip_id, $_SESSION['user_id']]);
    $trip = $stmt->fetch();
    
    if (!$trip) {
        echo json_encode(['success' => false, 'error' => 'Trip not found']);
        exit;
    }
    
    $tripStart = new DateTime($trip['start_date']);
    $tripEnd = new DateTime($trip['end_date']);
    
    if ($startDateTime < $tripStart || $endDateTime > $tripEnd) {
        echo json_encode([
            'success' => false, 
            'error' => 'Stop dates must be within trip dates (' . $trip['start_date'] . ' to ' . $trip['end_date'] . ')'
        ]);
        exit;
    }
    
    // 5. Update the stop dates
    $stmt = $pdo->prepare("
        UPDATE trip_stops 
        SET start_date = ?, end_date = ? 
        WHERE id = ? AND trip_id = ?
    ");
    $stmt->execute([$start_date, $end_date, $stop_id, $trip_id]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log("Update stop dates error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to update dates']);
}
?>