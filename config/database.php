<?php
/**
 * Database Configuration and Connection
 * PDO connection for JourneyHub
 */

// Database credentials - update these for your local environment
define('DB_HOST', 'localhost');
define('DB_NAME', 'journeyhub');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP has no password

// Create PDO connection
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error in production, display for development
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

// Test connection and return PDO instance
$pdo = getDBConnection();
