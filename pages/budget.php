<?php
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/db.php';

// Validate trip_id
$trip_id = isset($_GET['trip_id']) ? (int)$_GET['trip_id'] : 0;
if (!$trip_id) {
    header('Location: my-trips.php');
    exit;
}

// Verify ownership and fetch trip
$stmt = getPDO()->prepare("SELECT id, name, start_date, end_date FROM trips WHERE id = ? AND user_id = ?");
$stmt->execute([$trip_id, $_SESSION['user_id']]);
$trip = $stmt->fetch();
if (!$trip) {
    header('Location: my-trips.php');
    exit;
}

$currentPage = 'budget';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($trip['name']) ?> — Budget — JourneyHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">`n    <link rel="stylesheet" href="/JourneyHub/assets/css/navbar.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/budget.css">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="budget-main">
        <div class="budget-header">
            <h1><?= htmlspecialchars($trip['name']) ?> — Budget</h1>
            <div class="nav-links">
                <a href="itinerary.php?trip_id=<?= $trip_id ?>" class="nav-link">← Itinerary</a>
                <a href="calendar.php?trip_id=<?= $trip_id ?>" class="nav-link">📅 Calendar</a>
            </div>
        </div>

        <div class="budget-layout">
            <!-- Budget Summary -->
            <div class="budget-summary" id="budget-summary">
                <div class="budget-card">
                    <h2>Budget Overview</h2>
                    <div class="budget-display">
                        <div class="budget-amount">
                            <label>Budget: ₹</label>
                            <span id="budget-value">0</span>
                            <button class="btn btn-sm btn-secondary" id="edit-budget-btn">Edit</button>
                        </div>
                        <div class="budget-edit" id="budget-edit" style="display: none;">
                            <input type="number" id="budget-input" min="0" step="0.01" placeholder="0.00">
                            <button class="btn btn-sm btn-primary" id="save-budget-btn">Save</button>
                            <button class="btn btn-sm btn-secondary" id="cancel-budget-btn">Cancel</button>
                        </div>
                    </div>
                    
                    <div class="budget-stats">
                        <div class="stat">
                            <label>Spent:</label>
                            <span id="total-spent">₹0</span>
                        </div>
                        <div class="stat">
                            <label>Remaining:</label>
                            <span id="remaining" class="remaining-amount">₹0</span>
                        </div>
                    </div>
                </div>

                <!-- Category Breakdown -->
                <div class="category-card">
                    <h3>By Category</h3>
                    <div class="category-list" id="category-list">
                        <div class="category-item">
                            <span>Transport</span>
                            <span id="cat-transport">₹0</span>
                        </div>
                        <div class="category-item">
                            <span>Stay</span>
                            <span id="cat-stay">₹0</span>
                        </div>
                        <div class="category-item">
                            <span>Activities</span>
                            <span id="cat-activities">₹0</span>
                        </div>
                        <div class="category-item">
                            <span>Meals</span>
                            <span id="cat-meals">₹0</span>
                        </div>
                        <div class="category-item">
                            <span>Other</span>
                            <span id="cat-other">₹0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expenses List -->
            <div class="expenses-panel">
                <div class="expenses-header">
                    <h2>All Expenses</h2>
                    <button class="btn btn-primary" id="add-expense-btn">+ Add Expense</button>
                </div>

                <!-- Add Expense Form -->
                <div class="expense-form" id="expense-form" style="display: none;">
                    <div class="form-row">
                        <select id="expense-category">
                            <option value="">Select Category</option>
                            <option value="transport">Transport</option>
                            <option value="stay">Stay</option>
                            <option value="activities">Activities</option>
                            <option value="meals">Meals</option>
                            <option value="other">Other</option>
                        </select>
                        <input type="number" id="expense-amount" placeholder="Amount" min="0" step="0.01">
                        <input type="date" id="expense-date" min="<?= $trip['start_date'] ?>" max="<?= $trip['end_date'] ?>">
                    </div>
                    <div class="form-row">
                        <input type="text" id="expense-description" placeholder="Description (optional)" maxlength="255">
                        <button class="btn btn-primary" id="save-expense-btn">Save</button>
                        <button class="btn btn-secondary" id="cancel-expense-btn">Cancel</button>
                    </div>
                </div>

                <!-- Expenses List -->
                <div class="expenses-list" id="expenses-list">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
        </div>
    </main>

    <script>
        const TRIP_ID = <?= $trip['id'] ?>;
        const TRIP_START = "<?= $trip['start_date'] ?>";
        const TRIP_END = "<?= $trip['end_date'] ?>";
    </script>
    <script src="/JourneyHub/assets/js/budget.js"></script>
</body>
</html>