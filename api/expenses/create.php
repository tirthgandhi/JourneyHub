<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$tripId = (int)($_POST['trip_id'] ?? 0);
$category = trim($_POST['category'] ?? '');
$amount = trim($_POST['amount'] ?? '0');
$description = trim($_POST['description'] ?? '');
$expenseDate = trim($_POST['expense_date'] ?? date('Y-m-d'));

// Validate ownership
$stmt = $pdo->prepare('SELECT id FROM trips WHERE id = ? AND user_id = ?');
$stmt->execute([$tripId, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Validate category
$validCategories = ['transport', 'stay', 'activities', 'meals', 'shopping', 'other'];
if (!in_array($category, $validCategories)) {
    echo json_encode(['success' => false, 'error' => 'Invalid category']);
    exit;
}

// Validate amount
if (!is_numeric($amount) || $amount < 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid amount']);
    exit;
}

// Insert expense
$stmt = $pdo->prepare('
    INSERT INTO expenses (trip_id, category, amount, description, expense_date)
    VALUES (?, ?, ?, ?, ?)
');
$stmt->execute([$tripId, $category, $amount, $description, $expenseDate]);

echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
