<?php
/**
 * JourneyHub Database Validation Test
 * 
 * This script verifies that the database is properly set up with all required data.
 * Run this after importing schema.sql and seed.sql to confirm everything is correct.
 * 
 * Usage:
 * - Command line: php database/test.php
 * - Browser: http://localhost/JourneyHub/database/test.php
 */

// Set content type for proper display
header('Content-Type: text/plain; charset=utf-8');

// Include database connection
require_once __DIR__ . '/../config/db.php';

// Initialize results tracking
$results = [];
$allPassed = true;

echo "====================================================================\n";
echo "JourneyHub Database Validation Test\n";
echo "====================================================================\n\n";

// ====================================================================
// Test 1: Database Connection
// ====================================================================
try {
    $stmt = $pdo->query("SELECT 1");
    $results[] = [
        'test' => 'Test 1: Database connection',
        'status' => 'PASS',
        'message' => 'Successfully connected to database'
    ];
} catch (Exception $e) {
    $results[] = [
        'test' => 'Test 1: Database connection',
        'status' => 'FAIL',
        'message' => 'Connection failed: ' . $e->getMessage()
    ];
    $allPassed = false;
}

// ====================================================================
// Test 2: All 7 Tables Exist
// ====================================================================
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $expectedTables = ['users', 'trips', 'cities', 'trip_stops', 'activities', 'trip_activities', 'expenses'];
    $missingTables = array_diff($expectedTables, $tables);
    
    if (empty($missingTables) && count($tables) >= 7) {
        $results[] = [
            'test' => 'Test 2: All 7 tables exist',
            'status' => 'PASS',
            'message' => 'Found all required tables: ' . implode(', ', $expectedTables)
        ];
    } else {
        $results[] = [
            'test' => 'Test 2: All 7 tables exist',
            'status' => 'FAIL',
            'message' => 'Missing tables: ' . implode(', ', $missingTables)
        ];
        $allPassed = false;
    }
} catch (Exception $e) {
    $results[] = [
        'test' => 'Test 2: All 7 tables exist',
        'status' => 'FAIL',
        'message' => 'Error checking tables: ' . $e->getMessage()
    ];
    $allPassed = false;
}

// ====================================================================
// Test 3: 50 Normal Users Exist
// ====================================================================
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
    $count = $stmt->fetch()['count'];
    
    if ($count == 50) {
        $results[] = [
            'test' => 'Test 3: 50 normal users exist',
            'status' => 'PASS',
            'message' => "Verified $count normal users with role='user'"
        ];
    } else {
        $results[] = [
            'test' => 'Test 3: 50 normal users exist',
            'status' => 'FAIL',
            'message' => "Expected 50 normal users, found $count"
        ];
        $allPassed = false;
    }
} catch (Exception $e) {
    $results[] = [
        'test' => 'Test 3: 50 normal users exist',
        'status' => 'FAIL',
        'message' => 'Error checking users: ' . $e->getMessage()
    ];
    $allPassed = false;
}

// ====================================================================
// Test 4: Admin User Exists
// ====================================================================
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE email = 'admin@journeyhub.test' AND role = 'admin'");
    $count = $stmt->fetch()['count'];
    
    if ($count == 1) {
        $results[] = [
            'test' => 'Test 4: Admin user exists',
            'status' => 'PASS',
            'message' => 'Admin account (admin@journeyhub.test) verified'
        ];
    } else {
        $results[] = [
            'test' => 'Test 4: Admin user exists',
            'status' => 'FAIL',
            'message' => 'Admin account not found or duplicated'
        ];
        $allPassed = false;
    }
} catch (Exception $e) {
    $results[] = [
        'test' => 'Test 4: Admin user exists',
        'status' => 'FAIL',
        'message' => 'Error checking admin: ' . $e->getMessage()
    ];
    $allPassed = false;
}

// ====================================================================
// Test 5: Cities Count >= 70
// ====================================================================
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cities");
    $count = $stmt->fetch()['count'];
    
    if ($count >= 70) {
        $results[] = [
            'test' => 'Test 5: Cities count >= 70',
            'status' => 'PASS',
            'message' => "Found $count cities (minimum 70 required)"
        ];
    } else {
        $results[] = [
            'test' => 'Test 5: Cities count >= 70',
            'status' => 'FAIL',
            'message' => "Expected at least 70 cities, found $count"
        ];
        $allPassed = false;
    }
} catch (Exception $e) {
    $results[] = [
        'test' => 'Test 5: Cities count >= 70',
        'status' => 'FAIL',
        'message' => 'Error checking cities: ' . $e->getMessage()
    ];
    $allPassed = false;
}

// ====================================================================
// Test 6: Activities Exist
// ====================================================================
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM activities");
    $count = $stmt->fetch()['count'];
    
    if ($count > 0) {
        $results[] = [
            'test' => 'Test 6: Activities exist',
            'status' => 'PASS',
            'message' => "Found $count activities in the database"
        ];
    } else {
        $results[] = [
            'test' => 'Test 6: Activities exist',
            'status' => 'FAIL',
            'message' => 'No activities found in database'
        ];
        $allPassed = false;
    }
} catch (Exception $e) {
    $results[] = [
        'test' => 'Test 6: Activities exist',
        'status' => 'FAIL',
        'message' => 'Error checking activities: ' . $e->getMessage()
    ];
    $allPassed = false;
}

// ====================================================================
// Bonus: Gujarat Cities Verification
// ====================================================================
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cities WHERE state_name = 'Gujarat' AND country_name = 'India'");
    $count = $stmt->fetch()['count'];
    
    if ($count >= 30) {
        $results[] = [
            'test' => 'Bonus: Gujarat cities (state_name=Gujarat)',
            'status' => 'PASS',
            'message' => "Found $count Gujarat cities"
        ];
    } else {
        $results[] = [
            'test' => 'Bonus: Gujarat cities (state_name=Gujarat)',
            'status' => 'WARNING',
            'message' => "Expected 30 Gujarat cities, found $count"
        ];
    }
} catch (Exception $e) {
    $results[] = [
        'test' => 'Bonus: Gujarat cities',
        'status' => 'SKIP',
        'message' => 'Could not verify Gujarat cities'
    ];
}

// ====================================================================
// Display Results
// ====================================================================
echo "\n";
foreach ($results as $result) {
    $symbol = $result['status'] === 'PASS' ? '✓' : ($result['status'] === 'WARNING' ? '⚠' : '✗');
    $status = str_pad($result['status'], 8);
    echo "$symbol {$result['test']} - $status\n";
    echo "  └─ {$result['message']}\n\n";
}

echo "====================================================================\n";
if ($allPassed) {
    echo "✓ All tests passed! Database is ready for use.\n";
    echo "====================================================================\n";
    exit(0);
} else {
    echo "✗ Some tests failed. Please check the errors above.\n";
    echo "====================================================================\n";
    exit(1);
}
