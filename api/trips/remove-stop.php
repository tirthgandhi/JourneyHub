<?php
/**
 * Remove Trip Stop API
 * POST endpoint to remove a stop from a trip and renumber remaining stops
 * 
 * Fields: stop_id, trip_id
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
    
    if (!$stop_id || !$trip_id) {
        echo json_encode(['success' => false, 'error' => 'Stop ID and Trip ID are required']);
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
    
    // 2. Delete the stop
    $stmt = $pdo->prepare("DELETE FROM trip_stops WHERE id = ? AND trip_id = ?");
    $stmt->execute([$stop_id, $trip_id]);
    
    // 3. Renumber remaining stops sequentially
    $stmt = $pdo->prepare("
        SELECT id FROM trip_stops 
        WHERE trip_id = ? 
        ORDER BY stop_order ASC
    ");
    $stmt->execute([$trip_id]);
    $remainingStops = $stmt->fetchAll();
    
    // Update each stop with sequential order (1, 2, 3...)
    $updateStmt = $pdo->prepare("UPDATE trip_stops SET stop_order = ? WHERE id = ?");
    foreach ($remainingStops as $index => $stop) {
        $newOrder = $index + 1;
        $updateStmt->execute([$newOrder, $stop['id']]);
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log("Remove stop error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to remove stop']);
}
?>