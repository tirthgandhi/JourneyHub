<?php
/**
 * JourneyHub - Entry Point
 * Redirects to dashboard if logged in, otherwise to login page
 */

session_start();

// Check if user is logged in
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    // User is logged in, redirect to dashboard
    header('Location: /JourneyHub/pages/dashboard.php');
    exit;
} else {
    // User is not logged in, redirect to login page
    header('Location: /JourneyHub/pages/login.php');
    exit;
}
