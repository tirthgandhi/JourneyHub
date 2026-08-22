<?php
/**
 * Admin Trip Management Page
 * Lists all trips with search and remove functionality
 */

require_once __DIR__ . '/includes/auth.php';

// Get initial trip list (will be replaced by JS search)
$pdo = getPDO();
$stmt = $pdo->query("
    SELECT t.id, t.name, u.name as owner_name, t.is_public, t.created_at
    FROM trips t 
    JOIN users u ON u.id = t.user_id
    ORDER BY t.created_at DESC 
    LIMIT 50
");
$trips = $stmt->fetchAll();

$currentPage = 'admin-trips';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Management — Admin — JourneyHub</title>
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
                <li><a href="index.php" class="nav-link">Dashboard</a></li>
                <li><a href="users.php" class="nav-link">Users</a></li>
                <li><a href="trips.php" class="nav-link active">Trips</a></li>
                <li><a href="../pages/logout.php" class="nav-link logout">Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Trip Management</h1>
                <p class="admin-subtitle">Manage user trips and content</p>
            </div>

            <!-- Trips Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2>All Trips</h2>
                    <div class="search-box">
                        <input type="text" 
                               id="trip-search" 
                               class="search-input" 
                               placeholder="Search trips by name...">
                        <button class="btn btn-primary" id="search-btn">Search</button>
                    </div>
                </div>

                <div id="loading" style="display: none; padding: 20px; text-align: center;">
                    Loading...
                </div>

                <table class="data-table" id="trips-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Trip Name</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="trips-tbody">
                        <?php foreach ($trips as $trip): ?>
                            <tr data-trip-id="<?= $trip['id'] ?>">
                                <td><?= $trip['id'] ?></td>
                                <td><?= htmlspecialchars($trip['name']) ?></td>
                                <td><?= htmlspecialchars($trip['owner_name']) ?></td>
                                <td>
                                    <span class="status-badge <?= $trip['is_public'] ? 'status-public' : 'status-private' ?>">
                                        <?= $trip['is_public'] ? 'Public' : 'Private' ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($trip['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-remove" 
                                                onclick="removeTrip(<?= $trip['id'] ?>, '<?= htmlspecialchars($trip['name'], ENT_QUOTES) ?>')">
                                            Remove
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if (empty($trips)): ?>
                    <div class="no-data">No trips found</div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="/JourneyHub/assets/js/admin.js"></script>
</body>
</html>