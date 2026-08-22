<?php
/**
 * Set/Update Trip Budget API
 * POST endpoint to update trips.budget
 * 
 * Fields: trip_id, budget
 * Returns updated budget amount
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
    $pdo = getPDO();
    
    // Get and validate input
    $trip_id = isset($_POST['trip_id']) ? (int)$_POST['trip_id'] : 0;
    $budget = isset($_POST['budget']) ? trim($_POST['budget']) : '';
    
    if (!$trip_id) {
        echo json_encode(['success' => false, 'error' => 'Trip ID is required']);
        exit;
    }
    
    if ($budget === '' || !is_numeric($budget) || floatval($budget) < 0) {
        echo json_encode(['success' => false, 'error' => 'Budget must be a valid number >= 0']);
        exit;
    }
    
    $budget = floatval($budget);
    
    // Verify trip exists and belongs to logged-in user
    $stmt = $pdo->prepare("SELECT id FROM trips WHERE id = ? AND user_id = ?");
    $stmt->execute([$trip_id, $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Trip not found or access denied']);
        exit;
    }
    
    // Update budget
    $stmt = $pdo->prepare("UPDATE trips SET budget = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$budget, $trip_id, $_SESSION['user_id']]);
    
    echo json_encode([
        'success' => true,
        'budget' => number_format($budget, 2, '.', '')
    ]);
    
} catch (Exception $e) {
    error_log("Set budget error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to update budget']);
}
?>