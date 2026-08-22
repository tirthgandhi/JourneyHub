<?php
/**
 * Itinerary Builder — JourneyHub
 * 
 * Main page for building and managing trip itineraries
 * Two-column layout: city search (left) + itinerary management (right)
 */

require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/db.php';

// 1. Validate trip_id
$trip_id = isset($_GET['trip_id']) ? (int)$_GET['trip_id'] : 0;
if (!$trip_id) {
    header('Location: my-trips.php');
    exit;
}

// 2. Verify ownership and fetch trip
$stmt = getPDO()->prepare(
    "SELECT id, name, start_date, end_date, description 
     FROM trips WHERE id = ? AND user_id = ?"
);
$stmt->execute([$trip_id, $_SESSION['user_id']]);
$trip = $stmt->fetch();
if (!$trip) {
    header('Location: my-trips.php');
    exit;
}

// 3. Fetch existing stops with city info
$stmt2 = getPDO()->prepare(
    "SELECT ts.id, ts.start_date, ts.end_date, ts.stop_order,
            c.id as city_id, c.city_name, c.state_name, c.country_name, c.cost_index
     FROM trip_stops ts
     JOIN cities c ON c.id = ts.city_id
     WHERE ts.trip_id = ?
     ORDER BY ts.stop_order ASC"
);
$stmt2->execute([$trip_id]);
$stops = $stmt2->fetchAll();

$currentPage = 'itinerary';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($trip['name']) ?> — Itinerary — JourneyHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/itinerary.css">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="itinerary-main">
        <div class="itinerary-header">
            <h1><?= htmlspecialchars($trip['name']) ?></h1>
            <p class="trip-dates">
                <?= date('M d, Y', strtotime($trip['start_date'])) ?> → 
                <?= date('M d, Y', strtotime($trip['end_date'])) ?>
            </p>
            <?php if ($trip['description']): ?>
                <p class="trip-description"><?= htmlspecialchars($trip['description']) ?></p>
            <?php endif; ?>
        </div>

        <div class="itinerary-layout">
            <!-- Left Column: Add Destination -->
            <div class="destination-search">
                <div class="search-panel">
                    <h2>Add Destination</h2>
                    
                    <div class="search-form">
                        <div class="search-input-group">
                            <input type="text" 
                                   id="city-search" 
                                   placeholder="🔍 Search city..."
                                   class="search-input">
                        </div>
                        
                        <div class="country-filter-group">
                            <label for="country-filter">Country:</label>
                            <select id="country-filter" class="country-select">
                                <option value="">All Countries</option>
                                <!-- Populated by JavaScript -->
                            </select>
                        </div>
                    </div>

                    <div class="search-results" id="search-results">
                        <div class="results-header">Results</div>
                        <div class="results-list" id="results-list">
                            <div class="no-results" id="no-results">
                                Type at least 2 characters to search cities
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Your Itinerary -->
            <div class="itinerary-panel">
                <h2>Your Itinerary</h2>
                
                <div class="itinerary-stops" id="itinerary-stops">
                    <?php if (empty($stops)): ?>
                        <div class="empty-itinerary" id="empty-itinerary">
                            <div class="empty-icon">📍</div>
                            <p>No destinations added yet</p>
                            <p class="empty-subtitle">Search and add cities to build your itinerary</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($stops as $stop): ?>
                            <div class="stop-card" 
                                 id="stop-<?= $stop['id'] ?>"
                                 data-stop-id="<?= $stop['id'] ?>"
                                 data-city-id="<?= $stop['city_id'] ?>">
                                <div class="stop-number">
                                    <span class="stop-badge"><?= $stop['stop_order'] ?></span>
                                </div>
                                
                                <div class="stop-content">
                                    <div class="stop-header">
                                        <h3 class="city-name"><?= htmlspecialchars($stop['city_name']) ?></h3>
                                        <p class="city-location">
                                            <?= htmlspecialchars($stop['state_name']) ?>, 
                                            <?= htmlspecialchars($stop['country_name']) ?>
                                        </p>
                                    </div>
                                    
                                    <div class="stop-dates" id="dates-display-<?= $stop['id'] ?>">
                                        <span class="date-icon">📅</span>
                                        <span class="date-range">
                                            <?= date('M d', strtotime($stop['start_date'])) ?> → 
                                            <?= date('M d', strtotime($stop['end_date'])) ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Hidden date edit form -->
                                    <div class="stop-dates-edit" id="dates-edit-<?= $stop['id'] ?>" style="display: none;">
                                        <div class="date-inputs">
                                            <div class="date-input-group">
                                                <label>Start Date:</label>
                                                <input type="date" 
                                                       id="start-date-<?= $stop['id'] ?>"
                                                       value="<?= $stop['start_date'] ?>"
                                                       min="<?= $trip['start_date'] ?>"
                                                       max="<?= $trip['end_date'] ?>">
                                            </div>
                                            <div class="date-input-group">
                                                <label>End Date:</label>
                                                <input type="date" 
                                                       id="end-date-<?= $stop['id'] ?>"
                                                       value="<?= $stop['end_date'] ?>"
                                                       min="<?= $trip['start_date'] ?>"
                                                       max="<?= $trip['end_date'] ?>">
                                            </div>
                                        </div>
                                        <div class="date-actions">
                                            <button class="btn btn-sm btn-primary" 
                                                    onclick="saveDates(<?= $stop['id'] ?>)">Save</button>
                                            <button class="btn btn-sm btn-secondary" 
                                                    onclick="cancelEditDates(<?= $stop['id'] ?>)">Cancel</button>
                                        </div>
                                    </div>
                                    
                                    <div class="stop-actions">
                                        <button class="btn btn-sm btn-secondary" 
                                                onclick="editDates(<?= $stop['id'] ?>)">Edit Dates</button>
                                        <button class="btn btn-sm btn-secondary" 
                                                onclick="moveStopUp(<?= $stop['id'] ?>)"
                                                <?= $stop['stop_order'] == 1 ? 'disabled' : '' ?>>↑</button>
                                        <button class="btn btn-sm btn-secondary" 
                                                onclick="moveStopDown(<?= $stop['id'] ?>)"
                                                <?= $stop['stop_order'] == count($stops) ? 'disabled' : '' ?>>↓</button>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="removeStop(<?= $stop['id'] ?>, '<?= htmlspecialchars($stop['city_name']) ?>')">🗑 Remove</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($stops)): ?>
                    <div class="itinerary-footer">
                        <a href="#" class="btn btn-primary">+ Add Activities →</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Pass trip data to JavaScript -->
    <script>
        const TRIP_ID = <?= $trip['id'] ?>;
        const TRIP_START = "<?= $trip['start_date'] ?>";
        const TRIP_END = "<?= $trip['end_date'] ?>";
        
        // Track which cities are already added (for disabling Add buttons)
        const addedCityIds = new Set(<?= json_encode(array_column($stops, 'city_id')) ?>);
    </script>
    
    <script src="/JourneyHub/assets/js/cities.js"></script>
    <script src="/JourneyHub/assets/js/itinerary.js"></script>
</body>
</html>