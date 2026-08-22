<?php
/**
 * Create / Edit Trip — JourneyHub
 * Enhanced with Multiple Destination Selection
 * 
 * Shows a form to create a new trip or edit an existing one.
 * Edit mode is activated by ?edit=TRIP_ID in the URL.
 */

require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/db.php';

$userId   = $_SESSION['user_id'];
$editMode = false;
$trip     = null;
$selectedStops = [];

// --- Edit mode: load existing trip data ---
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM trips WHERE id = ? AND user_id = ?');
    $stmt->execute([$editId, $userId]);
    $trip = $stmt->fetch();

    if ($trip) {
        $editMode = true;
        
        // Load existing trip stops
        $stmtStops = $pdo->prepare('
            SELECT ts.*, c.city_name, c.state_name, c.country_name
            FROM trip_stops ts
            JOIN cities c ON ts.city_id = c.id
            WHERE ts.trip_id = ?
            ORDER BY ts.stop_order ASC
        ');
        $stmtStops->execute([$editId]);
        $selectedStops = $stmtStops->fetchAll();
    }
}

$currentPage = 'create-trip';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $editMode ? 'Edit Trip' : 'Create Trip'; ?> — JourneyHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/components.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/navbar-compact.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/dashboard.css">
    <style>
        .create-trip-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .destination-list {
            display: flex;
            flex-direction: column;
            gap: var(--space-md);
        }
        
        .destination-item {
            display: flex;
            align-items: center;
            gap: var(--space-lg);
            padding: var(--space-lg);
            background: rgba(249, 210, 186, 0.2);
            border: 1px solid rgba(249, 210, 186, 0.4);
            border-radius: 12px;
            transition: all 0.2s ease;
        }
        
        .destination-item:hover {
            background: rgba(249, 210, 186, 0.3);
            border-color: rgba(249, 210, 186, 0.6);
        }
        
        .destination-number {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-dark);
            color: #fff;
            border-radius: 50%;
            font-weight: 700;
            font-size: var(--font-sm);
            flex-shrink: 0;
        }
        
        .destination-info {
            flex: 1;
        }
        
        .destination-name {
            font-size: var(--font-md);
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: var(--space-xs);
        }
        
        .destination-location {
            font-size: var(--font-sm);
            color: var(--color-text-muted);
        }
        
        .destination-dates {
            display: flex;
            gap: var(--space-md);
            font-size: var(--font-sm);
        }
        
        .destination-actions {
            display: flex;
            gap: var(--space-sm);
        }
        
        .search-results {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            background: #fff;
            margin-top: var(--space-sm);
            display: none;
        }
        
        .search-results.active {
            display: block;
        }
        
        .search-result-item {
            padding: var(--space-md);
            cursor: pointer;
            border-bottom: 1px solid var(--color-border);
            transition: background 0.2s ease;
        }
        
        .search-result-item:hover {
            background: var(--color-bg);
        }
        
        .search-result-item:last-child {
            border-bottom: none;
        }
        
        .search-result-name {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: var(--space-xs);
        }
        
        .search-result-location {
            font-size: var(--font-sm);
            color: var(--color-text-muted);
        }
        
        .empty-destinations {
            text-align: center;
            padding: var(--space-3xl);
            color: var(--color-text-muted);
            background: rgba(247, 234, 224, 0.5);
            border: 2px dashed rgba(29, 69, 51, 0.2);
            border-radius: 12px;
        }
        
        .empty-destinations-icon {
            font-size: 48px;
            margin-bottom: var(--space-lg);
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="dashboard-main">
        <div class="create-trip-container">
            <h1 class="section-title-glass"><?php echo $editMode ? '✏️ Edit Trip' : '✈️ Plan a New Journey'; ?></h1>

            <!-- Inline error container -->
            <div class="alert alert-error" id="form-errors" style="display: none;"></div>
            <div class="alert alert-success" id="form-success" style="display: none;"></div>

            <form id="trip-form" data-mode="<?php echo $editMode ? 'edit' : 'create'; ?>" data-trip-id="<?php echo $editMode ? $trip['id'] : ''; ?>">
                
                <!-- SECTION 1: Trip Details -->
                <div class="glass-card" style="margin-bottom: var(--space-2xl);">
                    <h2 style="margin-bottom: var(--space-xl); color: var(--primary-dark);">📝 Trip Details</h2>
                    
                    <div class="form-group">
                        <label for="trip-name">Trip Name <span style="color: var(--color-danger);">*</span></label>
                        <input type="text" id="trip-name" name="name" maxlength="150"
                               value="<?php echo $editMode ? htmlspecialchars($trip['name']) : ''; ?>"
                               placeholder="e.g., Rajasthan Adventure" required>
                    </div>

                    <div class="form-group">
                        <label for="trip-description">Description</label>
                        <textarea id="trip-description" name="description" rows="4"
                                  placeholder="Tell us about your journey..."><?php echo $editMode ? htmlspecialchars($trip['description'] ?? '') : ''; ?></textarea>
                    </div>

                    <div class="form-grid form-grid-2">
                        <div class="form-group">
                            <label for="trip-start-date">Start Date <span style="color: var(--color-danger);">*</span></label>
                            <input type="date" id="trip-start-date" name="start_date"
                                   value="<?php echo $editMode ? htmlspecialchars($trip['start_date']) : ''; ?>"
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="trip-end-date">End Date <span style="color: var(--color-danger);">*</span></label>
                            <input type="date" id="trip-end-date" name="end_date"
                                   value="<?php echo $editMode ? htmlspecialchars($trip['end_date']) : ''; ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="trip-budget">Estimated Budget (₹)</label>
                        <input type="number" id="trip-budget" name="budget" min="0" step="100"
                               value="<?php echo $editMode ? htmlspecialchars($trip['budget'] ?? '0') : ''; ?>"
                               placeholder="15000">
                    </div>

                    <div class="form-group">
                        <label for="trip-cover">Cover Photo (JPG, PNG, GIF - Max 2MB)</label>
                        <input type="file" id="trip-cover" name="cover_image"
                               accept="image/jpeg,image/png,image/gif">
                        <?php if ($editMode && $trip['cover_image']): ?>
                            <p style="margin-top: var(--space-sm); font-size: var(--font-sm); color: var(--color-text-muted);">
                                Current cover image: <?php echo htmlspecialchars($trip['cover_image']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- SECTION 2: Destinations -->
                <div class="glass-card" style="margin-bottom: var(--space-2xl);">
                    <h2 style="margin-bottom: var(--space-xl); color: var(--primary-dark);">🗺️ Destinations</h2>
                    
                    <div class="form-group">
                        <label for="destination-search">Search Cities</label>
                        <input type="text" 
                               id="destination-search" 
                               class="search-input-glass" 
                               placeholder="🔍 Type to search cities (e.g., Jaipur, Mumbai, Paris)..."
                               autocomplete="off">
                        <div class="search-results" id="search-results"></div>
                    </div>
                    
                    <div id="destinations-container">
                        <?php if (!empty($selectedStops)): ?>
                            <?php foreach ($selectedStops as $index => $stop): ?>
                                <div class="destination-item" data-city-id="<?php echo $stop['city_id']; ?>">
                                    <div class="destination-number"><?php echo $index + 1; ?></div>
                                    <div class="destination-info">
                                        <div class="destination-name"><?php echo htmlspecialchars($stop['city_name']); ?></div>
                                        <div class="destination-location"><?php echo htmlspecialchars($stop['state_name'] . ', ' . $stop['country_name']); ?></div>
                                    </div>
                                    <div class="destination-actions">
                                        <button type="button" class="btn btn-sm btn-danger btn-remove-destination" onclick="removeDestination(this)">✕ Remove</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-destinations" id="empty-destinations">
                                <div class="empty-destinations-icon">🌍</div>
                                <p>No destinations selected yet</p>
                                <p style="font-size: var(--font-sm); margin-top: var(--space-sm);">Search and add cities to your itinerary</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- SECTION 3: Form Actions -->
                <div style="display: flex; gap: var(--space-lg); justify-content: center;">
                    <button type="submit" class="btn btn-primary btn-lg" id="trip-submit-btn">
                        <?php echo $editMode ? '💾 Save Changes' : '✈️ Create Trip'; ?>
                    </button>
                    <a href="/JourneyHub/pages/my-trips.php" class="btn btn-secondary btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <script src="/JourneyHub/assets/js/trips.js"></script>
    <script>
        // Destination management
        let selectedDestinations = <?php echo json_encode(array_map(function($stop) {
            return [
                'id' => $stop['city_id'],
                'name' => $stop['city_name'],
                'state' => $stop['state_name'],
                'country' => $stop['country_name']
            ];
        }, $selectedStops)); ?>;
        
        // Search functionality
        const searchInput = document.getElementById('destination-search');
        const searchResults = document.getElementById('search-results');
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchResults.classList.remove('active');
                return;
            }
            
            searchTimeout = setTimeout(() => searchCities(query), 300);
        });
        
        async function searchCities(query) {
            try {
                const response = await fetch(`/JourneyHub/api/cities/search.php?q=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                if (data.success && data.cities.length > 0) {
                    displaySearchResults(data.cities);
                } else {
                    searchResults.innerHTML = '<div style="padding: var(--space-md); text-align: center; color: var(--color-text-muted);">No cities found</div>';
                    searchResults.classList.add('active');
                }
            } catch (error) {
                console.error('Search error:', error);
            }
        }
        
        function displaySearchResults(cities) {
            searchResults.innerHTML = cities.map(city => `
                <div class="search-result-item" onclick="addDestination(${city.id}, '${escapeHtml(city.city_name)}', '${escapeHtml(city.state_name)}', '${escapeHtml(city.country_name)}')">
                    <div class="search-result-name">${escapeHtml(city.city_name)}</div>
                    <div class="search-result-location">${escapeHtml(city.state_name)}, ${escapeHtml(city.country_name)}</div>
                </div>
            `).join('');
            searchResults.classList.add('active');
        }
        
        function addDestination(id, name, state, country) {
            // Check if already added
            if (selectedDestinations.find(d => d.id === id)) {
                alert('This destination is already in your trip!');
                return;
            }
            
            selectedDestinations.push({ id, name, state, country });
            renderDestinations();
            
            // Clear search
            searchInput.value = '';
            searchResults.classList.remove('active');
        }
        
        function removeDestination(button) {
            const item = button.closest('.destination-item');
            const cityId = parseInt(item.dataset.cityId);
            
            selectedDestinations = selectedDestinations.filter(d => d.id !== cityId);
            renderDestinations();
        }
        
        function renderDestinations() {
            const container = document.getElementById('destinations-container');
            
            if (selectedDestinations.length === 0) {
                container.innerHTML = `
                    <div class="empty-destinations" id="empty-destinations">
                        <div class="empty-destinations-icon">🌍</div>
                        <p>No destinations selected yet</p>
                        <p style="font-size: var(--font-sm); margin-top: var(--space-sm);">Search and add cities to your itinerary</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = selectedDestinations.map((dest, index) => `
                <div class="destination-item" data-city-id="${dest.id}">
                    <div class="destination-number">${index + 1}</div>
                    <div class="destination-info">
                        <div class="destination-name">${escapeHtml(dest.name)}</div>
                        <div class="destination-location">${escapeHtml(dest.state)}, ${escapeHtml(dest.country)}</div>
                    </div>
                    <div class="destination-actions">
                        <button type="button" class="btn btn-sm btn-danger btn-remove-destination" onclick="removeDestination(this)">✕ Remove</button>
                    </div>
                </div>
            `).join('');
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.remove('active');
            }
        });
    </script>
</body>
</html>
