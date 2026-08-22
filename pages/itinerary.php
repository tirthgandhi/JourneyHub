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

// 2. Verify ownership and fetch trip (including sharing info)
$stmt = getPDO()->prepare(
    "SELECT id, name, start_date, end_date, description, is_public, share_token 
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

// 4. Fetch all activities grouped by date for day-by-day view
$dayByDayActivities = [];
$tripActivityTotal = 0;

if (!empty($stops)) {
    $stmt3 = getPDO()->prepare(
        "SELECT ta.activity_date, ta.activity_time, a.name, a.cost, a.type, c.city_name
         FROM trip_activities ta
         JOIN activities a ON a.id = ta.activity_id
         JOIN trip_stops ts ON ts.id = ta.trip_stop_id
         JOIN cities c ON c.id = ts.city_id
         WHERE ts.trip_id = ?
         ORDER BY ta.activity_date ASC, ta.activity_time ASC"
    );
    $stmt3->execute([$trip_id]);
    $activities = $stmt3->fetchAll();
    
    // Group by date
    foreach ($activities as $activity) {
        $date = $activity['activity_date'];
        if (!isset($dayByDayActivities[$date])) {
            $dayByDayActivities[$date] = [
                'activities' => [],
                'total' => 0
            ];
        }
        $dayByDayActivities[$date]['activities'][] = $activity;
        $dayByDayActivities[$date]['total'] += $activity['cost'];
        $tripActivityTotal += $activity['cost'];
    }
}

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
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">`n    <link rel="stylesheet" href="/JourneyHub/assets/css/navbar.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/itinerary.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/activities.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/sharing.css">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="itinerary-main">
        <div class="itinerary-header">
            <div class="header-content">
                <h1><?= htmlspecialchars($trip['name']) ?></h1>
                <p class="trip-dates">
                    <?= date('M d, Y', strtotime($trip['start_date'])) ?> → 
                    <?= date('M d, Y', strtotime($trip['end_date'])) ?>
                </p>
                <?php if ($trip['description']): ?>
                    <p class="trip-description"><?= htmlspecialchars($trip['description']) ?></p>
                <?php endif; ?>
            </div>
            
            <div class="trip-nav-links">
                <a href="budget.php?trip_id=<?= htmlspecialchars($trip['id']) ?>" class="btn btn-secondary">💰 Budget</a>
                <a href="calendar.php?trip_id=<?= htmlspecialchars($trip['id']) ?>" class="btn btn-secondary">📅 Calendar</a>
                <button class="btn btn-secondary" onclick="toggleSharePanel()">🔗 Share Trip</button>
            </div>
        </div>

        <!-- Share Panel -->
        <div class="share-panel hidden" id="share-panel">
            <h3>Share Trip</h3>
            
            <?php
            // Build share URL if trip is shared
            $shareUrl = '';
            if ($trip['is_public'] && !empty($trip['share_token'])) {
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $basePath = dirname(dirname($_SERVER['SCRIPT_NAME'])); // removes /pages
                $shareUrl = $protocol . '://' . $host . $basePath . '/pages/shared-trip.php?token=' . $trip['share_token'];
            }
            ?>
            
            <div id="share-url-container">
                <?php if ($shareUrl): ?>
                    <input type="text" 
                           class="share-url-input" 
                           id="share-url-input"
                           value="<?= htmlspecialchars($shareUrl) ?>" 
                           readonly>
                <?php endif; ?>
            </div>
            
            <div id="share-actions" class="share-actions">
                <?php if ($shareUrl): ?>
                    <button class="btn btn-primary" onclick="copyShareLink()">
                        📋 Copy Link
                    </button>
                    <button class="btn btn-danger" onclick="disableSharing()">
                        🔒 Disable Sharing
                    </button>
                    <span class="share-success-msg" id="copy-success" style="display: none;">
                        ✓ Link copied!
                    </span>
                <?php else: ?>
                    <button class="btn btn-primary" id="generate-share-btn" onclick="generateShareLink()">
                        🔗 Generate Share Link
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="share-error-msg" id="share-error" style="display: none;"></div>
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
                
                <!-- Day-by-Day View Section -->
                <?php if (!empty($dayByDayActivities)): ?>
                    <div class="day-by-day-section" style="margin-top: 32px;">
                        <div class="section-header">
                            <h2>Day-by-Day View</h2>
                            <button class="btn btn-sm btn-secondary" onclick="toggleDayByDayView()" id="toggle-day-view-btn">
                                Show Day-by-Day
                            </button>
                        </div>
                        
                        <div class="day-by-day-content hidden" id="day-by-day-content">
                            <?php 
                            $dayNumber = 1;
                            foreach ($dayByDayActivities as $date => $dayData): 
                            ?>
                                <div class="day-section">
                                    <div class="day-header">
                                        <h3>Day <?= $dayNumber ?> — <?= date('d M Y', strtotime($date)) ?></h3>
                                    </div>
                                    <div class="day-activities">
                                        <?php foreach ($dayData['activities'] as $activity): ?>
                                            <div class="day-activity-item">
                                                <span class="activity-icon">
                                                    <?php
                                                    $icons = [
                                                        'sightseeing' => '🏛',
                                                        'food' => '🍽',
                                                        'adventure' => '⛰',
                                                        'culture' => '🎭',
                                                        'shopping' => '🛍',
                                                        'entertainment' => '🎪',
                                                        'nature' => '🌳'
                                                    ];
                                                    echo $icons[$activity['type']] ?? '📍';
                                                    ?>
                                                </span>
                                                <span class="activity-name"><?= htmlspecialchars($activity['name']) ?></span>
                                                <span class="activity-city"><?= htmlspecialchars($activity['city_name']) ?></span>
                                                <?php if ($activity['activity_time']): ?>
                                                    <span class="activity-time"><?= date('g:i A', strtotime($activity['activity_time'])) ?></span>
                                                <?php endif; ?>
                                                <span class="activity-cost">₹<?= number_format($activity['cost'], 0, '.', ',') ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="day-total">
                                        <strong>Day <?= $dayNumber ?> Total:</strong>
                                        <span>₹<?= number_format($dayData['total'], 0, '.', ',') ?></span>
                                    </div>
                                </div>
                            <?php 
                            $dayNumber++;
                            endforeach; 
                            ?>
                            
                            <div class="trip-total-section">
                                <div class="trip-total">
                                    <strong>Trip Activity Total:</strong>
                                    <span class="total-amount">₹<?= number_format($tripActivityTotal, 0, '.', ',') ?></span>
                                </div>
                                <div class="budget-link">
                                    <a href="budget.php?trip_id=<?= htmlspecialchars($trip['id']) ?>" class="btn btn-sm btn-primary">
                                        See full Budget breakdown →
                                    </a>
                                </div>
                            </div>
                        </div>
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
    <script src="/JourneyHub/assets/js/activities.js"></script>
    <script src="/JourneyHub/assets/js/sharing.js"></script>
</body>
</html>