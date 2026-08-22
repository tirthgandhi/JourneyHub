<?php
/**
 * Auth Guard — include at the top of every protected page.
 *
 * Starts the session (if not already started) and checks that the user
 * is logged in. If not, redirects to the login page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /JourneyHub/pages/login.php');
    exit;
}
