<?php
/**
 * Reorder Trip Stops API
 * POST endpoint to update stop_order for multiple stops in a trip
 * 
 * Fields: trip_id, stops (JSON string with array of {stop_id, order} objects)
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
    $trip_id = isset($_POST['trip_id']) ? (int)$_POST['trip_id'] : 0;
    $stops_json = isset($_POST['stops']) ? $_POST['stops'] : '';
    
    if (!$trip_id || !$stops_json) {
        echo json_encode(['success' => false, 'error' => 'Trip ID and stops data are required']);
        exit;
    }
    
    // 1. Verify trip belongs to user
    $stmt = $pdo->prepare("SELECT id FROM trips WHERE id = ? AND user_id = ?");
    $stmt->execute([$trip_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Trip not found or access denied']);
        exit;
    }
    
    // 2. Parse and validate stops JSON
    $stops = json_decode($stops_json, true);
    if (!is_array($stops)) {
        echo json_encode(['success' => false, 'error' => 'Invalid stops data format']);
        exit;
    }
    
    // 3. Get all stop IDs that belong to this trip for validation
    $stmt = $pdo->prepare("SELECT id FROM trip_stops WHERE trip_id = ?");
    $stmt->execute([$trip_id]);
    $validStopIds = array_column($stmt->fetchAll(), 'id');
    
    // 4. Validate that all submitted stop IDs belong to this trip
    foreach ($stops as $stopData) {
        if (!isset($stopData['stop_id']) || !isset($stopData['order'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid stop data structure']);
            exit;
        }
        
        $stopId = (int)$stopData['stop_id'];
        if (!in_array($stopId, $validStopIds)) {
            echo json_encode(['success' => false, 'error' => 'Invalid stop ID provided']);
            exit;
        }
    }
    
    // 5. Update stop orders using prepared statement in loop
    $stmt = $pdo->prepare("UPDATE trip_stops SET stop_order = ? WHERE id = ? AND trip_id = ?");
    
    foreach ($stops as $stopData) {
        $stopId = (int)$stopData['stop_id'];
        $order = (int)$stopData['order'];
        $stmt->execute([$order, $stopId, $trip_id]);
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log("Reorder stops error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to reorder stops']);
}
?>