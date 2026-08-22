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

        <button class="navbar-toggle" id="navbar-toggle" aria-label="Toggle navigation" aria-expanded="false">
            ☰
        </button>

        <ul class="navbar-links" id="navbar-links">
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
            <li>
                <a href="/JourneyHub/pages/create-trip.php"
                   class="<?php echo ($currentPage ?? '') === 'create-trip' ? 'active' : ''; ?>"
                   id="nav-create-trip">Create Trip</a>
            </li>
            <li>
                <a href="/JourneyHub/pages/calendar.php?overview=1"
                   class="<?php echo ($currentPage ?? '') === 'calendar' ? 'active' : ''; ?>"
                   id="nav-calendar">Calendar</a>
            </li>
            <li>
                <a href="/JourneyHub/pages/profile.php"
                   class="<?php echo ($currentPage ?? '') === 'profile' ? 'active' : ''; ?>"
                   id="nav-profile">Profile</a>
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

<script>
// Mobile menu toggle
(function() {
    const toggle = document.getElementById('navbar-toggle');
    const menu = document.getElementById('navbar-links');
    
    if (toggle && menu) {
        toggle.addEventListener('click', function() {
            const isExpanded = menu.classList.toggle('show');
            toggle.setAttribute('aria-expanded', isExpanded);
        });
        
        // Close menu when clicking a link
        menu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                menu.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }
})();
</script>
