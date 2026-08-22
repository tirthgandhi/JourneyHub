<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$tripId = (int)($_GET['trip_id'] ?? 0);

// Validate ownership
$stmt = $pdo->prepare('SELECT id FROM trips WHERE id = ? AND user_id = ?');
$stmt->execute([$tripId, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get all expenses
$stmt = $pdo->prepare('
    SELECT id, category, amount, description, expense_date, created_at
    FROM expenses
    WHERE trip_id = ?
    ORDER BY expense_date DESC, created_at DESC
');
$stmt->execute([$tripId]);
$expenses = $stmt->fetchAll();

// Calculate totals by category
$stmt = $pdo->prepare('
    SELECT category, SUM(amount) as total
    FROM expenses
    WHERE trip_id = ?
    GROUP BY category
');
$stmt->execute([$tripId]);
$breakdown = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$stmt = $pdo->prepare('SELECT SUM(amount) as total FROM expenses WHERE trip_id = ?');
$stmt->execute([$tripId]);
$totalSpent = (float)$stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'expenses' => $expenses,
    'breakdown' => $breakdown,
    'total_spent' => $totalSpent
]);
