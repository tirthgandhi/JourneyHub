<?php
/**
 * Admin Dashboard - Main Page
 * Shows platform-wide statistics and recent activity
 */

require_once __DIR__ . '/includes/auth.php';

// Get platform statistics from database
$pdo = getPDO();

// Stat queries - all live from MySQL
$stats = [];

// Total active users
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
$stats['active_users'] = $stmt->fetchColumn();

// Total trips
$stmt = $pdo->query("SELECT COUNT(*) FROM trips");
$stats['total_trips'] = $stmt->fetchColumn();

// Total cities
$stmt = $pdo->query("SELECT COUNT(*) FROM cities");
$stats['total_cities'] = $stmt->fetchColumn();

// Public trips
$stmt = $pdo->query("SELECT COUNT(*) FROM trips WHERE is_public = 1");
$stats['public_trips'] = $stmt->fetchColumn();

// Recent users (last 5)
$stmt = $pdo->query("
    SELECT id, name, email, created_at 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recent_users = $stmt->fetchAll();

// Recent trips (last 5)
$stmt = $pdo->query("
    SELECT t.id, t.name, u.name as owner_name, t.created_at
    FROM trips t 
    JOIN users u ON u.id = t.user_id
    ORDER BY t.created_at DESC 
    LIMIT 5
");
$recent_trips = $stmt->fetchAll();

// Popular Cities (Top 10 by trip count)
$stmt = $pdo->query("
    SELECT c.name, COUNT(DISTINCT ts.trip_id) as trip_count
    FROM cities c
    JOIN trip_stops ts ON ts.city_id = c.id
    GROUP BY c.id, c.name
    ORDER BY trip_count DESC
    LIMIT 10
");
$popular_cities = $stmt->fetchAll();

// Find max count for chart scaling
$max_city_count = !empty($popular_cities) ? $popular_cities[0]['trip_count'] : 1;

// Popular Activities (Top 10 by usage count)
$stmt = $pdo->query("
    SELECT a.name, COUNT(ta.id) as usage_count
    FROM activities a
    JOIN trip_activities ta ON ta.activity_id = a.id
    GROUP BY a.id, a.name
    ORDER BY usage_count DESC
    LIMIT 10
");
$popular_activities = $stmt->fetchAll();

// Find max count for chart scaling
$max_activity_count = !empty($popular_activities) ? $popular_activities[0]['usage_count'] : 1;

$currentPage = 'admin-dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — JourneyHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Admin Sidebar Navigation -->
        <nav class="admin-sidebar">
            <div class="admin-nav-header">
                <h2>JourneyHub Admin</h2>
                <button class="mobile-nav-toggle" id="mobile-nav-toggle">☰</button>
            </div>
            
            <ul class="admin-nav-menu" id="admin-nav-menu">
                <li><a href="index.php" class="nav-link active">Dashboard</a></li>
                <li><a href="users.php" class="nav-link">Users</a></li>
                <li><a href="trips.php" class="nav-link">Trips</a></li>
                <li><a href="../pages/logout.php" class="nav-link logout">Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Platform Overview</h1>
                <p class="admin-subtitle">JourneyHub administrative dashboard</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($stats['active_users']) ?></div>
                    <div class="stat-label">Active Users</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($stats['total_trips']) ?></div>
                    <div class="stat-label">Total Trips</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($stats['total_cities']) ?></div>
                    <div class="stat-label">Cities Available</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($stats['public_trips']) ?></div>
                    <div class="stat-label">Public Trips</div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <div class="recent-section">
                    <h2>Recent Users</h2>
                    <div class="recent-list">
                        <?php if (empty($recent_users)): ?>
                            <p class="no-data">No users found</p>
                        <?php else: ?>
                            <?php foreach ($recent_users as $user): ?>
                                <div class="recent-item">
                                    <div class="item-info">
                                        <strong><?= htmlspecialchars($user['name']) ?></strong>
                                        <span class="item-meta"><?= htmlspecialchars($user['email']) ?></span>
                                    </div>
                                    <div class="item-date">
                                        <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="recent-section">
                    <h2>Recent Trips</h2>
                    <div class="recent-list">
                        <?php if (empty($recent_trips)): ?>
                            <p class="no-data">No trips found</p>
                        <?php else: ?>
                            <?php foreach ($recent_trips as $trip): ?>
                                <div class="recent-item">
                                    <div class="item-info">
                                        <strong><?= htmlspecialchars($trip['name']) ?></strong>
                                        <span class="item-meta">by <?= htmlspecialchars($trip['owner_name']) ?></span>
                                    </div>
                                    <div class="item-date">
                                        <?= date('M j, Y', strtotime($trip['created_at'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Analytics Charts -->
            <div class="analytics-grid">
                <div class="analytics-section">
                    <h2>Popular Cities</h2>
                    <div class="chart-container">
                        <?php if (empty($popular_cities)): ?>
                            <p class="no-data">No city data available</p>
                        <?php else: ?>
                            <?php foreach ($popular_cities as $city): ?>
                                <div class="chart-bar-row">
                                    <div class="chart-label"><?= htmlspecialchars($city['name']) ?></div>
                                    <div class="chart-bar-wrapper">
                                        <div class="chart-bar" style="width: <?= ($city['trip_count'] / $max_city_count) * 100 ?>%">
                                            <span class="chart-value"><?= $city['trip_count'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="analytics-section">
                    <h2>Popular Activities</h2>
                    <div class="chart-container">
                        <?php if (empty($popular_activities)): ?>
                            <p class="no-data">No activity data available</p>
                        <?php else: ?>
                            <?php foreach ($popular_activities as $activity): ?>
                                <div class="chart-bar-row">
                                    <div class="chart-label"><?= htmlspecialchars($activity['name']) ?></div>
                                    <div class="chart-bar-wrapper">
                                        <div class="chart-bar" style="width: <?= ($activity['usage_count'] / $max_activity_count) * 100 ?>%">
                                            <span class="chart-value"><?= $activity['usage_count'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/JourneyHub/assets/js/admin.js"></script>
</body>
</html>