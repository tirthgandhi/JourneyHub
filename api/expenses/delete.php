<?php
/**
 * Delete Expense API
 * POST endpoint to remove an expense
 * 
 * Fields: expense_id
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
    $pdo = getPDO();
    
    // Get and validate input
    $expense_id = isset($_POST['expense_id']) ? (int)$_POST['expense_id'] : 0;
    
    if (!$expense_id) {
        echo json_encode(['success' => false, 'error' => 'Expense ID is required']);
        exit;
    }
    
    // Verify ownership via JOIN (expense → trip → user)
    $stmt = $pdo->prepare("
        SELECT e.id FROM expenses e
        JOIN trips t ON t.id = e.trip_id
        WHERE e.id = ? AND t.user_id = ?
    ");
    $stmt->execute([$expense_id, $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Expense not found or access denied']);
        exit;
    }
    
    // Delete expense
    $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->execute([$expense_id]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log("Delete expense error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to delete expense']);
}
?>