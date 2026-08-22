<?php
/**
 * Test Login Verification
 * This tests if the password hash works correctly
 */

require_once __DIR__ . '/config/db.php';

$testEmail = 'rahul.patel@journeyhub.test';
$testPassword = 'password123';

echo "<h2>Testing Login for: $testEmail</h2>";
echo "<p>Testing password: $testPassword</p>";

// Get user from database
$stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
$stmt->execute([$testEmail]);
$user = $stmt->fetch();

if ($user) {
    echo "<p>✅ User found in database</p>";
    echo "<p>User ID: " . $user['id'] . "</p>";
    echo "<p>Name: " . htmlspecialchars($user['name']) . "</p>";
    echo "<p>Email: " . htmlspecialchars($user['email']) . "</p>";
    echo "<p>Password hash (first 50 chars): " . substr($user['password'], 0, 50) . "...</p>";
    
    // Test password verification
    if (password_verify($testPassword, $user['password'])) {
        echo "<p style='color: green; font-weight: bold;'>✅ PASSWORD VERIFICATION SUCCESSFUL!</p>";
        echo "<p>You can log in with:</p>";
        echo "<ul>";
        echo "<li><strong>Email:</strong> rahul.patel@journeyhub.test</li>";
        echo "<li><strong>Password:</strong> password123</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ PASSWORD VERIFICATION FAILED!</p>";
        echo "<p>The password hash in the database does not match 'password123'</p>";
    }
} else {
    echo "<p style='color: red;'>❌ User not found in database</p>";
}

echo "<hr>";
echo "<h2>Testing Admin Account</h2>";

$adminEmail = 'admin@journeyhub.test';
$stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
$stmt->execute([$adminEmail]);
$admin = $stmt->fetch();

if ($admin) {
    echo "<p>✅ Admin user found</p>";
    echo "<p>Admin ID: " . $admin['id'] . "</p>";
    echo "<p>Name: " . htmlspecialchars($admin['name']) . "</p>";
    
    if (password_verify($testPassword, $admin['password'])) {
        echo "<p style='color: green; font-weight: bold;'>✅ ADMIN PASSWORD VERIFICATION SUCCESSFUL!</p>";
        echo "<p>You can log in as admin with:</p>";
        echo "<ul>";
        echo "<li><strong>Email:</strong> admin@journeyhub.test</li>";
        echo "<li><strong>Password:</strong> password123</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ ADMIN PASSWORD VERIFICATION FAILED!</p>";
    }
}

echo "<hr>";
echo "<p><a href='/JourneyHub/pages/login.php'>Go to Login Page</a></p>";
?>
