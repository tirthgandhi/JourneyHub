<?php
/**
 * Logout — JourneyHub
 *
 * Destroys the session and redirects to the login page.
 */

session_start();
session_unset();
session_destroy();

header('Location: /JourneyHub/pages/login.php');
exit;
