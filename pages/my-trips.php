<?php
/**
 * My Trips — JourneyHub
 *
 * Lists all trips belonging to the logged-in user.
 * Provides Edit and Delete actions for each trip.
 */

require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/db.php';

$userId = $_SESSION['user_id'];

// --- Fetch all trips for this user, newest start_date first ---
$stmt = $pdo->prepare('
    SELECT id, name, description, start_date, end_date, cover_image, created_at
    FROM trips
    WHERE user_id = ?
    ORDER BY start_date DESC
');
$stmt->execute([$userId]);
$trips = $stmt->fetchAll();

$currentPage = 'my-trips';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Trips — JourneyHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/components.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/navbar-compact.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/dashboard.css">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="dashboard-main">
        <section class="page-header">
            <h1>My Trips</h1>
            <a href="/JourneyHub/pages/create-trip.php" class="btn btn-primary" id="btn-new-trip">
                ✈️ Plan New Trip
            </a>
        </section>

        <?php if (empty($trips)): ?>
            <div class="empty-state" id="empty-state">
                <div class="empty-state-icon">🧳</div>
                <h2>No trips yet</h2>
                <p>Start planning your first adventure!</p>
                <a href="/JourneyHub/pages/create-trip.php" class="btn btn-primary">Create Your First Trip</a>
            </div>
        <?php else: ?>
            <div class="trip-cards-grid" id="trips-grid">
                <?php foreach ($trips as $trip): ?>
                    <?php
                        $coverSrc = $trip['cover_image']
                            ? '/JourneyHub/assets/images/covers/' . htmlspecialchars($trip['cover_image'])
                            : '';
                        $startDate = date('M d, Y', strtotime($trip['start_date']));
                        $endDate   = date('M d, Y', strtotime($trip['end_date']));
                        $days = (int) ((strtotime($trip['end_date']) - strtotime($trip['start_date'])) / 86400) + 1;
                    ?>
                    <div class="trip-card" data-trip-id="<?php echo $trip['id']; ?>" id="trip-card-<?php echo $trip['id']; ?>">
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
                            <p class="trip-destinations">0 destinations</p>
                        </div>
                        <div class="trip-card-actions">
                            <a href="/JourneyHub/pages/itinerary.php?trip_id=<?php echo $trip['id']; ?>"
                               class="btn btn-sm btn-primary" id="btn-view-<?php echo $trip['id']; ?>">View</a>
                            <a href="/JourneyHub/pages/create-trip.php?edit=<?php echo $trip['id']; ?>"
                               class="btn btn-sm btn-secondary" id="btn-edit-<?php echo $trip['id']; ?>">Edit</a>
                            <button class="btn btn-sm btn-danger btn-delete-trip"
                                    data-trip-id="<?php echo $trip['id']; ?>"
                                    data-trip-name="<?php echo htmlspecialchars($trip['name']); ?>"
                                    id="btn-delete-<?php echo $trip['id']; ?>">Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script src="/JourneyHub/assets/js/trips.js"></script>
</body>
</html>
