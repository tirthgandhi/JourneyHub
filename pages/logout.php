<?php
/**
 * Logout Script
 * Destroys session and redirects to login
 */

session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: /JourneyHub/pages/login.php');
exit;
