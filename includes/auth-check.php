<?php
/**
 * Authentication Helper
 * Guards pages that require login and provides auth utility functions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require login - redirect to login page if not logged in
 * @param string $redirect_to URL to redirect to after login
 */
function require_login($redirect_to = '') {
    if (!is_logged_in()) {
        $redirect_url = '/JourneyHub/pages/login.php';
        if (!empty($redirect_to)) {
            $redirect_url .= '?redirect=' . urlencode($redirect_to);
        }
        header('Location: ' . $redirect_url);
        exit;
    }
}

/**
 * Require admin role - redirect if not admin
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        header('Location: /JourneyHub/pages/dashboard.php');
        exit;
    }
}

/**
 * Get current user ID
 * @return int|null
 */
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 * @return array|null
 */
function get_current_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['role'] ?? 'user',
        'profile_photo' => $_SESSION['profile_photo'] ?? null,
    ];
}

/**
 * Sanitize output for HTML display (XSS prevention)
 * @param string $string
 * @return string
 */
function escape_html($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect helper
 * @param string $url
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}
