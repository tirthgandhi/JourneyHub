  <?php
/**
 * Admin Authorization Guard
 * 
 * Include this at the top of every admin page and admin API.
 * Verifies both session existence AND admin role from database.
 * 
 * Redirects non-admins to appropriate pages.
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

try {
    // Re-verify admin role from database (never trust session data alone)
    $stmt = getPDO()->prepare("SELECT role FROM users WHERE id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Reject if user not found, inactive, or not admin
    if (!$user || $user['role'] !== 'admin') {
        header('Location: ../pages/dashboard.php');
        exit;
    }
    
} catch (Exception $e) {
    // Database error - reject access
    error_log("Admin auth error: " . $e->getMessage());
    header('Location: ../pages/dashboard.php');
    exit;
}

// Admin access verified - continue to page content
?>