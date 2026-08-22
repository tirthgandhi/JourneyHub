<?php
/**
 * Add Expense API
 * POST endpoint to insert a new expense
 * 
 * Fields: trip_id, category, amount, description, expense_date
 * Returns the created expense
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
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $amount = isset($_POST['amount']) ? trim($_POST['amount']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $expense_date = isset($_POST['expense_date']) ? trim($_POST['expense_date']) : '';
    
    if (!$trip_id) {
        echo json_encode(['success' => false, 'error' => 'Trip ID is required']);
        exit;
    }
    
    // Verify trip exists and belongs to logged-in user
    $stmt = $pdo->prepare("SELECT id FROM trips WHERE id = ? AND user_id = ?");
    $stmt->execute([$trip_id, $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Trip not found or access denied']);
        exit;
    }
    
    // Validate category (exact match to 5 allowed values)
    $allowed_categories = ['transport', 'stay', 'activities', 'meals', 'other'];
    if (!in_array($category, $allowed_categories)) {
        echo json_encode(['success' => false, 'error' => 'Invalid category. Must be one of: ' . implode(', ', $allowed_categories)]);
        exit;
    }
    
    // Validate amount
    if ($amount === '' || !is_numeric($amount) || floatval($amount) < 0) {
        echo json_encode(['success' => false, 'error' => 'Amount must be a valid number >= 0']);
        exit;
    }
    
    $amount = floatval($amount);
    
    // Validate expense_date
    if (!$expense_date) {
        echo json_encode(['success' => false, 'error' => 'Expense date is required']);
        exit;
    }
    
    $dateTime = DateTime::createFromFormat('Y-m-d', $expense_date);
    if (!$dateTime || $dateTime->format('Y-m-d') !== $expense_date) {
        echo json_encode(['success' => false, 'error' => 'Invalid date format. Use YYYY-MM-DD']);
        exit;
    }
    
    // Validate description length
    if (strlen($description) > 255) {
        echo json_encode(['success' => false, 'error' => 'Description must be 255 characters or less']);
        exit;
    }
    
    // Insert expense
    $stmt = $pdo->prepare("
        INSERT INTO expenses (trip_id, category, amount, description, expense_date)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$trip_id, $category, $amount, $description, $expense_date]);
    $expense_id = $pdo->lastInsertId();
    
    // Return the created expense
    echo json_encode([
        'success' => true,
        'expense' => [
            'id' => (int)$expense_id,
            'category' => $category,
            'amount' => number_format($amount, 2, '.', ''),
            'description' => $description,
            'expense_date' => $expense_date
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Add expense error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to add expense']);
}
?>