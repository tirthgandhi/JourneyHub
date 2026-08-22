<?php
/**
 * Update Expense API
 * POST endpoint to edit an existing expense
 * 
 * Fields: expense_id, category, amount, description, expense_date
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
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $amount = isset($_POST['amount']) ? trim($_POST['amount']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $expense_date = isset($_POST['expense_date']) ? trim($_POST['expense_date']) : '';
    
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
    
    // Validate category
    $allowed_categories = ['transport', 'stay', 'activities', 'meals', 'other'];
    if (!in_array($category, $allowed_categories)) {
        echo json_encode(['success' => false, 'error' => 'Invalid category']);
        exit;
    }
    
    // Validate amount
    if ($amount === '' || !is_numeric($amount) || floatval($amount) < 0) {
        echo json_encode(['success' => false, 'error' => 'Amount must be a valid number >= 0']);
        exit;
    }
    
    // Validate date
    if (!$expense_date) {
        echo json_encode(['success' => false, 'error' => 'Date is required']);
        exit;
    }
    
    $dateTime = DateTime::createFromFormat('Y-m-d', $expense_date);
    if (!$dateTime || $dateTime->format('Y-m-d') !== $expense_date) {
        echo json_encode(['success' => false, 'error' => 'Invalid date format']);
        exit;
    }
    
    // Validate description length
    if (strlen($description) > 255) {
        echo json_encode(['success' => false, 'error' => 'Description too long']);
        exit;
    }
    
    // Update expense
    $stmt = $pdo->prepare("
        UPDATE expenses 
        SET category = ?, amount = ?, description = ?, expense_date = ?
        WHERE id = ?
    ");
    $stmt->execute([$category, floatval($amount), $description, $expense_date, $expense_id]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log("Update expense error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to update expense']);
}
?>