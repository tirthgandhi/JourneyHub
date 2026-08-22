<?php
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/db.php';

$trip_id = isset($_GET['trip_id']) ? (int)$_GET['trip_id'] : 0;
if (!$trip_id) { header('Location: my-trips.php'); exit; }

$stmt = getPDO()->prepare('SELECT * FROM trips WHERE id = ? AND user_id = ?');
$stmt->execute([$trip_id, $_SESSION['user_id']]);
$trip = $stmt->fetch();
if (!$trip) { header('Location: my-trips.php'); exit; }

$stmt = getPDO()->prepare('SELECT * FROM expenses WHERE trip_id = ? ORDER BY expense_date DESC');
$stmt->execute([$trip_id]);
$expenses = $stmt->fetchAll();

$stmt = getPDO()->prepare('SELECT category, SUM(amount) as total FROM expenses WHERE trip_id = ? GROUP BY category');
$stmt->execute([$trip_id]);
$breakdown = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$totalSpent = array_sum($breakdown);
$budget = (float)$trip['budget'];
$remaining = $budget - $totalSpent;
$percentage = $budget > 0 ? ($totalSpent / $budget) * 100 : 0;

$icons = ['transport'=>'🚗','stay'=>'🏨','activities'=>'🎯','meals'=>'🍽️','shopping'=>'🛍️','other'=>'💼'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Budget — <?= htmlspecialchars($trip['name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
<link rel="stylesheet" href="/JourneyHub/assets/css/components.css">
<link rel="stylesheet" href="/JourneyHub/assets/css/navbar-compact.css">
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
<main style="max-width:1200px;margin:0 auto;padding:32px 24px;">
<h1 style="margin-bottom:24px;">💰 <?= htmlspecialchars($trip['name']) ?> — Budget</h1>

<!-- Budget Summary -->
<div class="budget-summary" style="margin-bottom:32px;">
<h2 style="color:#fff;margin-bottom:24px;">Budget Overview</h2>
<div style="display:grid;gap:24px;">
<div><div style="font-size:12px;text-transform:uppercase;color:rgba(255,255,255,0.7);">Total Budget</div>
<div style="font-size:36px;font-weight:700;color:#fff;">₹<?= number_format($budget, 0) ?></div></div>
<div><div style="font-size:12px;text-transform:uppercase;color:rgba(255,255,255,0.7);">Spent</div>
<div style="font-size:36px;font-weight:700;color:#fff;">₹<?= number_format($totalSpent, 0) ?></div></div>
<div><div style="font-size:12px;text-transform:uppercase;color:rgba(255,255,255,0.7);">Remaining</div>
<div style="font-size:36px;font-weight:700;color:#fff;">₹<?= number_format($remaining, 0) ?></div></div>
</div>
<div style="margin-top:16px;">
<div style="height:8px;background:rgba(255,255,255,0.2);border-radius:4px;overflow:hidden;">
<div style="height:100%;background:linear-gradient(90deg,#F9D2BA,#F7EAE0);width:<?= min($percentage,100) ?>%;transition:width 0.3s;"></div>
</div>
<div style="margin-top:8px;font-size:14px;color:rgba(255,255,255,0.8);"><?= number_format($percentage, 1) ?>% used</div>
</div>

<!-- Breakdown -->
<div style="margin-top:24px;padding-top:24px;border-top:1px solid rgba(255,255,255,0.2);">
<h3 style="color:#fff;margin-bottom:16px;">Breakdown</h3>
<?php foreach(['transport','stay','activities','meals','shopping','other'] as $cat): ?>
<?php $amt = $breakdown[$cat] ?? 0; if($amt > 0): ?>
<div style="display:flex;justify-content:space-between;padding:8px 0;">
<span style="color:rgba(255,255,255,0.9);"><?= $icons[$cat] ?> <?= ucfirst($cat) ?></span>
<span style="font-weight:600;color:#fff;">₹<?= number_format($amt, 0) ?></span>
</div>
<?php endif; endforeach; ?>
</div>
</div>

<!-- Add Expense Form -->
<div class="glass-card" style="margin-bottom:32px;">
<h2 style="margin-bottom:16px;">➕ Add Expense</h2>
<form id="expense-form" style="display:grid;gap:16px;">
<input type="hidden" name="trip_id" value="<?= $trip_id ?>">
<div class="form-grid form-grid-2">
<div><label>Category</label>
<select name="category" required>
<option value="transport">🚗 Transport</option>
<option value="stay">🏨 Accommodation</option>
<option value="activities">🎯 Activities</option>
<option value="meals">🍽️ Food & Meals</option>
<option value="shopping">🛍️ Shopping</option>
<option value="other">💼 Other</option>
</select></div>
<div><label>Amount (₹)</label><input type="number" name="amount" min="0" step="1" required placeholder="500"></div>
</div>
<div><label>Description</label><input type="text" name="description" placeholder="Taxi to airport"></div>
<div><label>Date</label><input type="date" name="expense_date" value="<?= date('Y-m-d') ?>"></div>
<button type="submit" class="btn btn-primary">Add Expense</button>
</form>
</div>

<!-- Expenses List -->
<div class="glass-card">
<h2 style="margin-bottom:16px;">📋 All Expenses</h2>
<div id="expenses-list">
<?php if (empty($expenses)): ?>
<div style="text-align:center;padding:48px;color:var(--color-text-muted);">
<div style="font-size:48px;margin-bottom:16px;">💸</div>
<p>No expenses yet</p>
</div>
<?php else: ?>
<?php foreach($expenses as $exp): ?>
<div class="expense-card">
<div class="expense-icon"><?= $icons[$exp['category']] ?></div>
<div class="expense-details">
<div class="expense-category-name"><?= ucfirst($exp['category']) ?></div>
<div class="expense-description"><?= htmlspecialchars($exp['description']) ?></div>
<div style="font-size:12px;color:var(--color-text-muted);margin-top:4px;"><?= date('M d, Y', strtotime($exp['expense_date'])) ?></div>
</div>
<div class="expense-amount">₹<?= number_format($exp['amount'], 0) ?></div>
<button class="btn btn-sm btn-danger" onclick="deleteExpense(<?= $exp['id'] ?>)">✕</button>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
</div>

</main>
<script>
document.getElementById('expense-form').addEventListener('submit', async function(e) {
e.preventDefault();
const formData = new FormData(this);
const response = await fetch('/JourneyHub/api/expenses/create.php', {method:'POST',body:formData});
const data = await response.json();
if (data.success) { location.reload(); } else { alert(data.error); }
});

async function deleteExpense(id) {
if (!confirm('Delete this expense?')) return;
const formData = new FormData();
formData.append('expense_id', id);
const response = await fetch('/JourneyHub/api/expenses/delete.php', {method:'POST',body:formData});
const data = await response.json();
if (data.success) { location.reload(); } else { alert(data.error); }
}
</script>
</body>
</html>
