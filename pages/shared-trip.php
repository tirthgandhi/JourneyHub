<?php
/**
 * Shared Trip Page — PUBLIC, NO LOGIN REQUIRED
 * 
 * Displays a read-only view of a shared trip
 * Accessible via share token, does not require authentication
 */

require_once __DIR__ . '/../config/db.php';

// Get and validate token
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $errorMessage = 'Trip not found';
    $trip = null;
} else {
    // Fetch trip if token is valid and trip is public
    try {
        $stmt = getPDO()->prepare(
            "SELECT id, name, description, start_date, end_date 
             FROM trips 
             WHERE share_token = ? AND is_public = 1"
        );
        $stmt->execute([$token]);
        $trip = $stmt->fetch();
        
        if (!$trip) {
            $errorMessage = 'This trip is no longer available';
        }
    } catch (PDOException $e) {
        $errorMessage = 'Unable to load trip';
        error_log("Shared trip load error: " . $e->getMessage());
        $trip = null;
    }
}

// If trip is valid, fetch stops, activities, and budget
$stops = [];
$tripActivities = [];
$budgetSummary = ['budget' => 0, 'spent' => 0];

if ($trip) {
    try {
        // Fetch trip stops with city information
        $stopsStmt = getPDO()->prepare(
            "SELECT ts.id, ts.start_date, ts.end_date, ts.stop_order,
                    c.city_name, c.state_name, c.country_name
             FROM trip_stops ts
             JOIN cities c ON c.id = ts.city_id
             WHERE ts.trip_id = ?
             ORDER BY ts.stop_order ASC"
        );
        $stopsStmt->execute([$trip['id']]);
        $stops = $stopsStmt->fetchAll();
        
        // Fetch activities for all stops
        if (!empty($stops)) {
            $stopIds = array_column($stops, 'id');
            $placeholders = implode(',', array_fill(0, count($stopIds), '?'));
            
            $activitiesStmt = getPDO()->prepare(
                "SELECT ta.trip_stop_id, ta.activity_date, ta.activity_time,
                        a.name, a.type, a.cost, a.duration
                 FROM trip_activities ta
                 JOIN activities a ON a.id = ta.activity_id
                 WHERE ta.trip_stop_id IN ($placeholders)
                 ORDER BY ta.activity_date ASC, ta.activity_time ASC"
            );
            $activitiesStmt->execute($stopIds);
            
            // Group activities by stop
            foreach ($activitiesStmt->fetchAll() as $activity) {
                $tripActivities[$activity['trip_stop_id']][] = $activity;
            }
        }
        
        // Fetch budget summary
        $budgetStmt = getPDO()->prepare(
            "SELECT COALESCE(SUM(amount), 0) as total_spent
             FROM expenses
             WHERE trip_id = ?"
        );
        $budgetStmt->execute([$trip['id']]);
        $budgetResult = $budgetStmt->fetch();
        $budgetSummary['spent'] = $budgetResult['total_spent'] ?? 0;
        
        // Note: We don't have a budget column in the trips table based on original schema
        // If budget tracking is needed, it would be calculated from planned expenses
        // For now, we'll show total spent only
        
    } catch (PDOException $e) {
        error_log("Shared trip data load error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $trip ? htmlspecialchars($trip['name']) . ' — Shared Trip' : 'Trip Not Found' ?> — JourneyHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/sharing.css">
</head>
<body class="shared-trip-body">
    <main class="shared-trip-container">
        <?php if (!$trip): ?>
            <!-- Error state -->
            <div class="shared-trip-error">
                <div class="error-icon">🔒</div>
                <h1><?= htmlspecialchars($errorMessage) ?></h1>
                <p>This link may have been disabled by the trip owner, or the trip may not exist.</p>
                <a href="/JourneyHub/pages/login.php" class="btn btn-primary">Go to JourneyHub</a>
            </div>
        <?php else: ?>
            <!-- Trip content -->
            <div class="shared-trip-header">
                <div class="shared-badge">Shared Trip</div>
                <h1><?= htmlspecialchars($trip['name']) ?></h1>
                <p class="trip-dates">
                    <?= date('M d, Y', strtotime($trip['start_date'])) ?> → 
                    <?= date('M d, Y', strtotime($trip['end_date'])) ?>
                </p>
                <?php if ($trip['description']): ?>
                    <p class="trip-description"><?= htmlspecialchars($trip['description']) ?></p>
                <?php endif; ?>
            </div>

            <?php if (empty($stops)): ?>
                <div class="shared-trip-empty">
                    <p>This trip doesn't have any destinations yet.</p>
                </div>
            <?php else: ?>
                <div class="shared-trip-itinerary">
                    <h2>Itinerary</h2>
                    
                    <?php foreach ($stops as $stop): ?>
                        <div class="shared-stop-card">
                            <div class="shared-stop-header">
                                <div class="stop-number-badge"><?= $stop['stop_order'] ?></div>
                                <div class="stop-info">
                                    <h3><?= htmlspecialchars($stop['city_name']) ?></h3>
                                    <p class="stop-location">
                                        <?= htmlspecialchars($stop['state_name']) ?>, 
                                        <?= htmlspecialchars($stop['country_name']) ?>
                                    </p>
                                    <p class="stop-dates">
                                        📅 <?= date('M d', strtotime($stop['start_date'])) ?> → 
                                        <?= date('M d', strtotime($stop['end_date'])) ?>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if (isset($tripActivities[$stop['id']]) && !empty($tripActivities[$stop['id']])): ?>
                                <div class="shared-activities">
                                    <h4>Activities</h4>
                                    <ul class="activity-list">
                                        <?php foreach ($tripActivities[$stop['id']] as $activity): ?>
                                            <li class="activity-item">
                                                <span class="activity-name"><?= htmlspecialchars($activity['name']) ?></span>
                                                <span class="activity-meta">
                                                    <?= date('M d', strtotime($activity['activity_date'])) ?>
                                                    <?php if ($activity['activity_time']): ?>
                                                        · <?= date('g:i A', strtotime($activity['activity_time'])) ?>
                                                    <?php endif; ?>
                                                    <?php if ($activity['cost'] > 0): ?>
                                                        · ₹<?= number_format($activity['cost'], 0) ?>
                                                    <?php else: ?>
                                                        · Free
                                                    <?php endif; ?>
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($budgetSummary['spent'] > 0): ?>
                    <div class="shared-trip-budget">
                        <h2>Budget Summary</h2>
                        <div class="budget-summary">
                            <div class="budget-item">
                                <span class="budget-label">Total Spent:</span>
                                <span class="budget-value">₹<?= number_format($budgetSummary['spent'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="shared-trip-footer">
                <p>Powered by <strong>JourneyHub</strong></p>
                <a href="/JourneyHub/pages/login.php" class="btn btn-secondary">Create Your Own Trip</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
