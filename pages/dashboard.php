<?php
/**
 * Dashboard Page
 * Main landing page after login - stub for future features
 */

session_start();

require_once '../includes/auth-check.php';

// Require login
require_login();

$user = get_current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - JourneyHub</title>
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/auth.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="header-content">
                <h1>JourneyHub</h1>
                <nav class="dashboard-nav">
                    <a href="/JourneyHub/pages/dashboard.php" class="nav-link active">Dashboard</a>
                    <a href="/JourneyHub/pages/profile.php" class="nav-link">Profile</a>
                    <?php if (is_admin()): ?>
                        <a href="/JourneyHub/admin/" class="nav-link">Admin</a>
                    <?php endif; ?>
                    <a href="/JourneyHub/pages/logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </header>
        
        <main class="dashboard-main">
            <div class="welcome-section">
                <div class="welcome-header">
                    <?php if (!empty($user['profile_photo'])): ?>
                        <img src="/JourneyHub/assets/images/profiles/<?php echo escape_html($user['profile_photo']); ?>" 
                             alt="Profile" 
                             class="welcome-avatar">
                    <?php else: ?>
                        <div class="welcome-avatar-placeholder">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h2>Welcome back, <?php echo escape_html($user['name']); ?>! 👋</h2>
                        <p class="welcome-subtitle">Ready to plan your next adventure?</p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-icon">🗺️</div>
                    <h3>My Trips</h3>
                    <p>View and manage your travel plans</p>
                    <button class="btn btn-primary" disabled>Coming Soon</button>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-icon">📍</div>
                    <h3>Destinations</h3>
                    <p>Explore amazing places to visit</p>
                    <button class="btn btn-primary" disabled>Coming Soon</button>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-icon">✈️</div>
                    <h3>Itineraries</h3>
                    <p>Create detailed travel itineraries</p>
                    <button class="btn btn-primary" disabled>Coming Soon</button>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-icon">💰</div>
                    <h3>Budget Tracker</h3>
                    <p>Track your travel expenses</p>
                    <button class="btn btn-primary" disabled>Coming Soon</button>
                </div>
            </div>
            
            <div class="info-banner">
                <h3>🎉 Authentication System Complete!</h3>
                <p>The feature/auth branch is now fully functional. You can:</p>
                <ul>
                    <li>Create an account and login securely</li>
                    <li>Update your profile and upload a photo</li>
                    <li>Reset your password if forgotten</li>
                    <li>Manage your account settings</li>
                </ul>
                <p><strong>Next steps:</strong> Future branches will add trip planning, destination browsing, and more!</p>
            </div>
        </main>
    </div>
</body>
</html>
