<?php
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/db.php';

// Check if overview mode (all trips) or single trip mode
$overviewMode = isset($_GET['overview']) && $_GET['overview'] == '1';

if (!$overviewMode) {
    // Single trip calendar mode
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
} else {
    // Overview mode - fetch all trips for status grouping
    $stmt = getPDO()->prepare(
        "SELECT id, name, start_date, end_date
         FROM trips
         WHERE user_id = ?
         ORDER BY start_date ASC"
    );
    $stmt->execute([$_SESSION['user_id']]);
    $allTrips = $stmt->fetchAll();
    
    // Classify trips
    $today = date('Y-m-d');
    $tripsByStatus = [
        'upcoming' => [],
        'ongoing' => [],
        'completed' => []
    ];
    
    foreach ($allTrips as $t) {
        if ($t['start_date'] > $today) {
            $tripsByStatus['upcoming'][] = $t;
        } elseif ($t['end_date'] < $today) {
            $tripsByStatus['completed'][] = $t;
        } else {
            $tripsByStatus['ongoing'][] = $t;
        }
    }
}

$currentPage = 'calendar';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($trip['name']) ?> — Calendar — JourneyHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/calendar.css">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="calendar-main">
        <div class="calendar-header">
            <?php if ($overviewMode): ?>
                <h1>My Trips Calendar</h1>
                <div class="nav-links">
                    <a href="dashboard.php" class="nav-link">← Dashboard</a>
                </div>
            <?php else: ?>
                <h1>Trip Calendar — <?= htmlspecialchars($trip['name']) ?></h1>
                <div class="nav-links">
                    <a href="itinerary.php?trip_id=<?= $trip_id ?>" class="nav-link">← Itinerary</a>
                    <a href="budget.php?trip_id=<?= $trip_id ?>" class="nav-link">💰 Budget</a>
                    <a href="calendar.php?overview=1" class="nav-link">📅 All Trips</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($overviewMode): ?>
            <!-- Trip Status Groups -->
            <div class="trip-groups">
                <?php if (!empty($tripsByStatus['ongoing'])): ?>
                    <div class="trip-group ongoing">
                        <h3>Ongoing Trips</h3>
                        <?php foreach ($tripsByStatus['ongoing'] as $t): ?>
                            <div class="trip-group-item" onclick="window.location.href='itinerary.php?trip_id=<?= $t['id'] ?>'">
                                <strong><?= htmlspecialchars($t['name']) ?></strong>
                                <span><?= date('M d', strtotime($t['start_date'])) ?> - <?= date('M d, Y', strtotime($t['end_date'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($tripsByStatus['upcoming'])): ?>
                    <div class="trip-group upcoming">
                        <h3>Upcoming Trips</h3>
                        <?php foreach ($tripsByStatus['upcoming'] as $t): ?>
                            <div class="trip-group-item" onclick="window.location.href='itinerary.php?trip_id=<?= $t['id'] ?>'">
                                <strong><?= htmlspecialchars($t['name']) ?></strong>
                                <span><?= date('M d', strtotime($t['start_date'])) ?> - <?= date('M d, Y', strtotime($t['end_date'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($tripsByStatus['completed'])): ?>
                    <div class="trip-group completed">
                        <h3>Completed Trips</h3>
                        <?php foreach ($tripsByStatus['completed'] as $t): ?>
                            <div class="trip-group-item" onclick="window.location.href='itinerary.php?trip_id=<?= $t['id'] ?>'">
                                <strong><?= htmlspecialchars($t['name']) ?></strong>
                                <span><?= date('M d', strtotime($t['start_date'])) ?> - <?= date('M d, Y', strtotime($t['end_date'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="calendar-layout">
            <!-- Calendar Grid -->
            <div class="calendar-panel">
                <div class="calendar-header-controls">
                    <button class="btn btn-secondary" id="prev-month">‹</button>
                    <h2 id="current-month">Loading...</h2>
                    <button class="btn btn-secondary" id="next-month">›</button>
                </div>
                
                <div class="calendar-grid" id="calendar-grid">
                    <!-- Generated by JavaScript -->
                </div>
            </div>

            <!-- Day Details -->
            <div class="day-details-panel">
                <h3 id="selected-date">Select a date</h3>
                <div class="activities-list" id="activities-list">
                    <p class="no-selection">Click on a date to see activities</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        <?php if ($overviewMode): ?>
            const OVERVIEW_MODE = true;
            const ALL_TRIPS = <?= json_encode($allTrips) ?>;
        <?php else: ?>
            const OVERVIEW_MODE = false;
            const TRIP_ID = <?= $trip['id'] ?>;
            const TRIP_START = "<?= $trip['start_date'] ?>";
            const TRIP_END = "<?= $trip['end_date'] ?>";
        <?php endif; ?>
    </script>
    <script src="/JourneyHub/assets/js/calendar.js"></script>
</body>
</html>