<?php
/**
 * Dashboard — JourneyHub
 *
 * Shows welcome message, upcoming trips, recent trips, and action buttons.
 * All trip data is queried from MySQL for the logged-in user.
 */

require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/db.php';

$userId   = $_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['name']);

// --- Upcoming trips: start_date >= today, ordered soonest first, limit 3 ---
$stmtUpcoming = $pdo->prepare('
    SELECT id, name, description, start_date, end_date, cover_image
    FROM trips
    WHERE user_id = ? AND start_date >= CURDATE()
    ORDER BY start_date ASC
    LIMIT 3
');
$stmtUpcoming->execute([$userId]);
$upcomingTrips = $stmtUpcoming->fetchAll();

// --- Recent trips: ordered by created_at DESC, limit 5 ---
$stmtRecent = $pdo->prepare('
    SELECT id, name, description, start_date, end_date, cover_image, created_at
    FROM trips
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 5
');
$stmtRecent->execute([$userId]);
$recentTrips = $stmtRecent->fetchAll();

// --- Total trip count for stats ---
$stmtCount = $pdo->prepare('SELECT COUNT(*) as total FROM trips WHERE user_id = ?');
$stmtCount->execute([$userId]);
$totalTrips = $stmtCount->fetch()['total'];

$currentPage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — JourneyHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/dashboard.css">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="dashboard-main">
        <!-- Welcome Section -->
        <section class="welcome-section" id="welcome-section">
            <h1>Welcome back, <?php echo $userName; ?> 👋</h1>
            <p>Here's a snapshot of your travel plans.</p>
            <div class="welcome-actions">
                <a href="/JourneyHub/pages/create-trip.php" class="btn btn-primary" id="btn-new-trip">
                    ✈️ Plan New Trip
                </a>
                <a href="/JourneyHub/pages/my-trips.php" class="btn btn-secondary" id="btn-my-trips">
                    📋 My Trips
                </a>
            </div>
        </section>

        <!-- Stats Cards -->
        <section class="stats-row" id="stats-row">
            <div class="stat-card">
                <span class="stat-number"><?php echo $totalTrips; ?></span>
                <span class="stat-label">Total Trips</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo count($upcomingTrips); ?></span>
                <span class="stat-label">Upcoming</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo count($recentTrips); ?></span>
                <span class="stat-label">Recent</span>
            </div>
        </section>

        <!-- Upcoming Trips -->
        <section class="dashboard-section" id="upcoming-trips-section">
            <h2>Upcoming Trips</h2>
            <?php if (empty($upcomingTrips)): ?>
                <div class="empty-state">
                    <p>No upcoming trips. <a href="/JourneyHub/pages/create-trip.php">Plan one now!</a></p>
                </div>
            <?php else: ?>
                <div class="trip-cards-grid">
                    <?php foreach ($upcomingTrips as $trip): ?>
                        <?php
                            $coverSrc = $trip['cover_image']
                                ? '/JourneyHub/assets/images/covers/' . htmlspecialchars($trip['cover_image'])
                                : '';
                            $startDate = date('M d, Y', strtotime($trip['start_date']));
                            $endDate   = date('M d, Y', strtotime($trip['end_date']));
                            $days = (int) ((strtotime($trip['end_date']) - strtotime($trip['start_date'])) / 86400) + 1;
                        ?>
                        <div class="trip-card" data-trip-id="<?php echo $trip['id']; ?>">
                            <div class="trip-card-cover">
                                <?php if ($coverSrc): ?>
                                    <img src="<?php echo $coverSrc; ?>"
                                         alt="<?php echo htmlspecialchars($trip['name']); ?>">
                                <?php else: ?>
                                    <div class="trip-card-placeholder">🌍</div>
                                <?php endif; ?>
                            </div>
                            <div class="trip-card-body">
                                <h3><?php echo htmlspecialchars($trip['name']); ?></h3>
                                <p class="trip-dates"><?php echo $startDate; ?> → <?php echo $endDate; ?></p>
                                <p class="trip-duration"><?php echo $days; ?> day<?php echo $days !== 1 ? 's' : ''; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Recent Trips -->
        <section class="dashboard-section" id="recent-trips-section">
            <h2>Recent Trips</h2>
            <?php if (empty($recentTrips)): ?>
                <div class="empty-state">
                    <p>You haven't created any trips yet. <a href="/JourneyHub/pages/create-trip.php">Get started!</a></p>
                </div>
            <?php else: ?>
                <div class="recent-trips-list">
                    <?php foreach ($recentTrips as $trip): ?>
                        <?php
                            $startDate = date('M d, Y', strtotime($trip['start_date']));
                            $endDate   = date('M d, Y', strtotime($trip['end_date']));
                            $days = (int) ((strtotime($trip['end_date']) - strtotime($trip['start_date'])) / 86400) + 1;
                        ?>
                        <div class="recent-trip-item" data-trip-id="<?php echo $trip['id']; ?>">
                            <div class="recent-trip-info">
                                <h4><?php echo htmlspecialchars($trip['name']); ?></h4>
                                <span class="trip-dates"><?php echo $startDate; ?> → <?php echo $endDate; ?></span>
                                <span class="trip-duration"><?php echo $days; ?> day<?php echo $days !== 1 ? 's' : ''; ?></span>
                            </div>
                            <a href="/JourneyHub/pages/create-trip.php?edit=<?php echo $trip['id']; ?>"
                               class="btn btn-sm">Edit</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
