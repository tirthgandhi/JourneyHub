<?php
/**
 * Admin User Management Page
 * Lists all users with search and deactivate/reactivate functionality
 */

require_once __DIR__ . '/includes/auth.php';

// Get initial user list (will be replaced by JS search)
$pdo = getPDO();
$stmt = $pdo->query("
    SELECT id, name, email, role, status, created_at
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 50
");
$users = $stmt->fetchAll();

$currentPage = 'admin-users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management — Admin — JourneyHub</title>
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
                <li><a href="users.php" class="nav-link active">Users</a></li>
                <li><a href="trips.php" class="nav-link">Trips</a></li>
                <li><a href="../pages/logout.php" class="nav-link logout">Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>User Management</h1>
                <p class="admin-subtitle">Manage user accounts and permissions</p>
            </div>

            <!-- Users Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2>All Users</h2>
                    <div class="search-box">
                        <input type="text" 
                               id="user-search" 
                               class="search-input" 
                               placeholder="Search users by name or email...">
                        <button class="btn btn-primary" id="search-btn">Search</button>
                    </div>
                </div>

                <div id="loading" style="display: none; padding: 20px; text-align: center;">
                    Loading...
                </div>

                <table class="data-table" id="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody">
                        <?php foreach ($users as $user): ?>
                            <tr data-user-id="<?= $user['id'] ?>" data-role="<?= $user['role'] ?>">
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="status-badge <?= $user['role'] === 'admin' ? 'status-public' : '' ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?= $user['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                                        <?= ucfirst($user['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <?php if ($user['status'] === 'active'): ?>
                                                <button class="btn-deactivate" 
                                                        onclick="changeUserStatus(<?= $user['id'] ?>, 'deactivate')">
                                                    Deactivate
                                                </button>
                                            <?php else: ?>
                                                <button class="btn-reactivate" 
                                                        onclick="changeUserStatus(<?= $user['id'] ?>, 'reactivate')">
                                                    Reactivate
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Admin</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if (empty($users)): ?>
                    <div class="no-data">No users found</div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        const CURRENT_USER_ID = <?= $_SESSION['user_id'] ?>;
    </script>
    <script src="/JourneyHub/assets/js/admin.js"></script>
</body>
</html>