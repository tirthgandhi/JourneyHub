<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$expenseId = (int)($_POST['expense_id'] ?? 0);

// Validate ownership
$stmt = $pdo->prepare('
    SELECT e.id FROM expenses e
    JOIN trips t ON t.id = e.trip_id
    WHERE e.id = ? AND t.user_id = ?
');
$stmt->execute([$expenseId, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Delete
$stmt = $pdo->prepare('DELETE FROM expenses WHERE id = ?');
$stmt->execute([$expenseId]);

echo json_encode(['success' => true]);
