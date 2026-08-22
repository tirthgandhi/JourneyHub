<?php
/**
 * Database Configuration and Connection
 * PDO connection for JourneyHub
 * 
 * This file provides a reusable database connection for the entire application.
 * It uses PDO with prepared statements for security and utf8mb4 for international support.
 */

// Database credentials - update these for your environment
define('DB_HOST', 'localhost');
define('DB_NAME', 'journeyhub');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP has no password

/**
 * Get database connection (singleton pattern)
 * 
 * @return PDO Database connection object
 * @throws PDOException If connection fails
 */
function getDBConnection() {
    static $pdo = null;
    
    // Return existing connection if already established
    if ($pdo !== null) {
        return $pdo;
    }
    
    try {
        // Build DSN (Data Source Name)
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        
        // PDO options for security and convenience
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Fetch as associative arrays
            PDO::ATTR_EMULATE_PREPARES => false,  // Use real prepared statements
        ];
        
        // Create new PDO instance
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        return $pdo;
        
    } catch (PDOException $e) {
        // In production, log this error instead of displaying it
        die("Database connection failed: " . $e->getMessage() . "\n\nPlease check:\n- MySQL is running in XAMPP\n- Database 'journeyhub' exists\n- Credentials in config/db.php are correct");
    }
}

// Initialize connection and make it available as $pdo for scripts that include this file
$pdo = getDBConnection();