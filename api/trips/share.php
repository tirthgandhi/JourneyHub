<?php
/**
 * Share Trip API
 * 
 * POST /api/trips/share.php
 * 
 * Generates a unique share token for a trip and makes it public
 * Returns the shareable URL
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
    
    // Ownership verification + fetch current share_token
    $stmt = getPDO()->prepare(
        "SELECT id, share_token FROM trips WHERE id = ? AND user_id = ?"
    );
    $stmt->execute([$trip_id, $_SESSION['user_id']]);
    $trip = $stmt->fetch();
    
    if (!$trip) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied or trip not found']);
        exit;
    }
    
    // If share_token already exists, reuse it; otherwise generate new one
    if (empty($trip['share_token'])) {
        $share_token = bin2hex(random_bytes(32));
    } else {
        $share_token = $trip['share_token'];
    }
    
    // Update trip to be public with the share token
    $updateStmt = getPDO()->prepare(
        "UPDATE trips SET share_token = ?, is_public = 1 WHERE id = ? AND user_id = ?"
    );
    $updateStmt->execute([$share_token, $trip_id, $_SESSION['user_id']]);
    
    // Build share URL dynamically
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Derive base path (assuming the app is at /JourneyHub)
    $scriptPath = dirname(dirname($_SERVER['SCRIPT_NAME'])); // removes /api/trips
    $basePath = dirname($scriptPath); // removes /api
    
    $share_url = $protocol . '://' . $host . $basePath . '/pages/shared-trip.php?token=' . $share_token;
    
    echo json_encode([
        'success' => true,
        'share_url' => $share_url
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    error_log("Share trip error: " . $e->getMessage());
}
