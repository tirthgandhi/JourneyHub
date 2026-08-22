<?php
/**
 * List Expenses + Budget Summary API
 * GET endpoint to fetch all expenses for a trip with budget analysis
 * 
 * Parameter: trip_id (required)
 * Returns budget, expenses, totals, and category breakdown
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
    
    // Verify trip exists and belongs to logged-in user, get budget
    $stmt = $pdo->prepare("SELECT budget FROM trips WHERE id = ? AND user_id = ?");
    $stmt->execute([$trip_id, $_SESSION['user_id']]);
    $trip = $stmt->fetch();
    
    if (!$trip) {
        echo json_encode(['success' => false, 'error' => 'Trip not found or access denied']);
        exit;
    }
    
    $budget = floatval($trip['budget']);
    
    // Get all expenses for this trip
    $stmt = $pdo->prepare("
        SELECT id, category, amount, description, expense_date, created_at
        FROM expenses 
        WHERE trip_id = ?
        ORDER BY expense_date DESC, created_at DESC
    ");
    $stmt->execute([$trip_id]);
    $expenses = $stmt->fetchAll();
    
    // Calculate total spent
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE trip_id = ?");
    $stmt->execute([$trip_id]);
    $total_spent = floatval($stmt->fetchColumn());
    
    // Calculate spending by category
    $stmt = $pdo->prepare("
        SELECT category, SUM(amount) as total 
        FROM expenses 
        WHERE trip_id = ? 
        GROUP BY category
    ");
    $stmt->execute([$trip_id]);
    $category_data = $stmt->fetchAll();
    
    // Build by_category array with all 5 categories
    $by_category = [
        'transport' => 0.00,
        'stay' => 0.00,
        'activities' => 0.00,
        'meals' => 0.00,
        'other' => 0.00
    ];
    
    foreach ($category_data as $row) {
        $by_category[$row['category']] = floatval($row['total']);
    }
    
    // Calculate remaining and over-budget status
    $remaining = $budget - $total_spent;
    $is_over_budget = $remaining < 0;
    
    echo json_encode([
        'success' => true,
        'budget' => $budget,
        'total_spent' => $total_spent,
        'remaining' => $remaining,
        'is_over_budget' => $is_over_budget,
        'expenses' => $expenses,
        'by_category' => $by_category
    ]);
    
} catch (Exception $e) {
    error_log("List expenses error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to load expenses']);
}
?>