<?php
/**
 * Shared Navigation Bar — JourneyHub
 *
 * Include this file at the top of every protected page.
 * Expects $_SESSION['name'] to be set (guaranteed by auth-check.php).
 *
 * Usage:  $currentPage = 'dashboard';  // or 'my-trips', 'create-trip'
 *         require_once __DIR__ . '/../includes/navbar.php';
 */

$userName = htmlspecialchars($_SESSION['name'] ?? 'User');
?>
<nav class="navbar" id="main-navbar">
    <div class="navbar-inner">
        <a href="/JourneyHub/pages/dashboard.php" class="navbar-brand">JourneyHub</a>

        <ul class="navbar-links">
            <li>
                <a href="/JourneyHub/pages/dashboard.php"
                   class="<?php echo ($currentPage ?? '') === 'dashboard' ? 'active' : ''; ?>"
                   id="nav-dashboard">Dashboard</a>
            </li>
            <li>
                <a href="/JourneyHub/pages/my-trips.php"
                   class="<?php echo ($currentPage ?? '') === 'my-trips' ? 'active' : ''; ?>"
                   id="nav-my-trips">My Trips</a>
            </li>
            <?php
            // Show Admin link only for admin users (cosmetic only - real security is in backend)
            if (isset($_SESSION['user_id'])) {
                require_once __DIR__ . '/../config/db.php';
                $stmt = getPDO()->prepare("SELECT role FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                if ($user && $user['role'] === 'admin') {
                    $adminActive = in_array($currentPage ?? '', ['admin-dashboard', 'admin-users', 'admin-trips']);
                    echo '<li><a href="/JourneyHub/admin/index.php" class="' . ($adminActive ? 'active' : '') . '" id="nav-admin">Admin</a></li>';
                }
            }
            ?>
        </ul>

        <div class="navbar-user">
            <span class="navbar-username" id="nav-username"><?php echo $userName; ?> ▾</span>
            <a href="/JourneyHub/pages/logout.php" class="navbar-logout" id="nav-logout">Logout</a>
        </div>
    </div>
</nav>
