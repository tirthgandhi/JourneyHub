<?php
/**
 * JourneyHub — Entry Point
 *
 * Redirects logged-in users to the dashboard,
 * everyone else to the login page.
 */

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /JourneyHub/pages/dashboard.php');
} else {
    header('Location: /JourneyHub/pages/login.php');
}
exit;
