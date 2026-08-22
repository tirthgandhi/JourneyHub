<?php
/**
 * Add Trip Stop API
 * POST endpoint to add a new city stop to a trip
 * 
 * Fields: trip_id, city_id
 * Returns the created stop with city information
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
    $city_id = isset($_POST['city_id']) ? (int)$_POST['city_id'] : 0;
    
    if (!$trip_id || !$city_id) {
        echo json_encode(['success' => false, 'error' => 'Trip ID and City ID are required']);
        exit;
    }
    
    // 1. Verify trip exists and belongs to logged-in user
    $stmt = $pdo->prepare("SELECT id, start_date, end_date FROM trips WHERE id = ? AND user_id = ?");
    $stmt->execute([$trip_id, $_SESSION['user_id']]);
    $trip = $stmt->fetch();
    
    if (!$trip) {
        echo json_encode(['success' => false, 'error' => 'Trip not found or access denied']);
        exit;
    }
    
    // 2. Verify city exists
    $stmt = $pdo->prepare("SELECT id, city_name, state_name, country_name FROM cities WHERE id = ?");
    $stmt->execute([$city_id]);
    $city = $stmt->fetch();
    
    if (!$city) {
        echo json_encode(['success' => false, 'error' => 'City not found']);
        exit;
    }
    
    // 3. Check for duplicate city in this trip
    $stmt = $pdo->prepare("SELECT id FROM trip_stops WHERE trip_id = ? AND city_id = ?");
    $stmt->execute([$trip_id, $city_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'This city is already in your itinerary']);
        exit;
    }
    
    // 4. Get current max stop_order
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(stop_order), 0) FROM trip_stops WHERE trip_id = ?");
    $stmt->execute([$trip_id]);
    $maxOrder = $stmt->fetchColumn();
    $newOrder = $maxOrder + 1;
    
    // 5. Insert new trip stop with trip's start/end dates as defaults
    $stmt = $pdo->prepare("
        INSERT INTO trip_stops (trip_id, city_id, start_date, end_date, stop_order)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$trip_id, $city_id, $trip['start_date'], $trip['end_date'], $newOrder]);
    $newStopId = $pdo->lastInsertId();
    
    // 6. Return the created stop with city information
    echo json_encode([
        'success' => true,
        'stop' => [
            'id' => (int)$newStopId,
            'city_id' => $city_id,
            'city_name' => $city['city_name'],
            'state_name' => $city['state_name'],
            'country_name' => $city['country_name'],
            'start_date' => $trip['start_date'],
            'end_date' => $trip['end_date'],
            'stop_order' => $newOrder
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Add stop error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to add stop']);
}
?>