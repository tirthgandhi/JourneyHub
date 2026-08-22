<?php
require_once __DIR__ . '/config/db.php';
echo '<h1>Password Fix</h1>';
$plainPassword = 'password123';
$newHash = password_hash($plainPassword, PASSWORD_DEFAULT);
echo '<p>New hash: ' . $newHash . '</p>';
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email IN ('rahul.patel@journeyhub.test', 'admin@journeyhub.test')");
$stmt->execute([$newHash]);
echo '<p style=color:green>✅ Passwords updated!</p>';
$stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = 'rahul.patel@journeyhub.test'");
$stmt->execute();
$user = $stmt->fetch();
if ($user && password_verify($plainPassword, $user['password'])) {
    echo '<h2 style=color:green>✅ LOGIN WORKS!</h2>';
    echo '<p><strong>Email:</strong> rahul.patel@journeyhub.test</p>';
    echo '<p><strong>Password:</strong> password123</p>';
    echo '<p><a href=/JourneyHub/pages/login.php>Go to Login</a></p>';
} else {
    echo '<p style=color:red>❌ Still not working</p>';
}
?>
