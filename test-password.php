<?php
/**
 * Password Verification Test
 * Test if password123 matches the hash in database
 */

require_once __DIR__ . '/config/db.php';

echo "<h2>Password Verification Test</h2>";

// Get user from database
$email = 'rahul.patel@journeyhub.test';
$password = 'password123';

$stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

echo "<h3>Database User Data:</h3>";
if ($user) {
    echo "<pre>";
    echo "ID: " . $user['id'] . "\n";
    echo "Name: " . $user['name'] . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Password Hash: " . $user['password'] . "\n";
    echo "Hash Length: " . strlen($user['password']) . " characters\n";
    echo "</pre>";
    
    echo "<h3>Password Verification:</h3>";
    echo "<pre>";
    echo "Testing password: '$password'\n";
    
    if (password_verify($password, $user['password'])) {
        echo "✅ SUCCESS: Password 'password123' is CORRECT!\n";
        echo "\n";
        echo "This means the hash in database is valid.\n";
        echo "Check your login.php code if login still fails.\n";
    } else {
        echo "❌ FAILED: Password 'password123' does NOT match!\n";
        echo "\n";
        echo "The hash in database might be corrupted.\n";
        echo "Run this SQL to fix it:\n\n";
        echo "UPDATE users \n";
        echo "SET password = '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' \n";
        echo "WHERE email = 'rahul.patel@journeyhub.test';\n";
    }
    echo "</pre>";
    
} else {
    echo "<p style='color:red;'>❌ User not found in database!</p>";
    echo "<p>Run the seed.sql file to create test users.</p>";
}

echo "<hr>";
echo "<h3>Expected Hash (from seed.sql):</h3>";
echo "<pre>\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi</pre>";

echo "<hr>";
echo "<h3>Test Other Users:</h3>";
echo "<pre>";
$stmt = $pdo->query('SELECT id, name, email FROM users LIMIT 5');
$users = $stmt->fetchAll();
foreach ($users as $u) {
    echo "ID: {$u['id']} | {$u['name']} | {$u['email']}\n";
}
echo "</pre>";
?>
