<?php
require_once __DIR__ . '/config/db.php';
$testEmail = 'rahul.patel@journeyhub.test';
$testPassword = 'password123';
$stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
$stmt->execute([$testEmail]);
$user = $stmt->fetch();
echo "<h1>LOGIN TEST RESULT</h1>";
if ($user) {
    echo "<p>User found: " . htmlspecialchars($user['name']) . "</p>";
    echo "<p>Email: " . htmlspecialchars($user['email']) . "</p>";
    if (password_verify($testPassword, $user['password'])) {
        echo "<h2 style='color:green'>SUCCESS! Password is correct!</h2>";
        echo "<p><strong>Email:</strong> rahul.patel@journeyhub.test</p>";
        echo "<p><strong>Password:</strong> password123</p>";
        echo "<p><a href='/JourneyHub/pages/login.php' style='font-size:20px; background:#1D4533; color:white; padding:10px 20px; text-decoration:none; border-radius:8px;'>GO TO LOGIN PAGE</a></p>";
    } else {
        echo "<h2 style='color:red'>FAILED! Password does not match!</h2>";
        echo "<p>Hash in DB: " . substr($user['password'], 0, 50) . "...</p>";
    }
} else {
    echo "<h2 style='color:red'>User not found!</h2>";
}
?>
