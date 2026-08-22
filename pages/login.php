<?php
/**
 * Login Page — JourneyHub
 */

session_start();

// Already logged in? Go to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /JourneyHub/pages/dashboard.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/db.php';

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if ($email === '') {
        $errors[] = 'Email is required.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];

            header('Location: /JourneyHub/pages/dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — JourneyHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/auth.css">
    <style>
        .password-toggle {
            position: relative;
        }
        .password-toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--color-text-muted);
            cursor: pointer;
            font-size: 0.9rem;
            padding: 4px 8px;
        }
        .password-toggle-btn:hover {
            color: var(--primary-dark);
        }
    </style>
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-logo">JourneyHub</h1>
            <p class="auth-subtitle">Welcome back — log in to plan your next adventure.</p>

            <?php if (!empty($errors)): ?>
                <div class="auth-errors">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form" id="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars($email ?? ''); ?>"
                           placeholder="you@example.com" required>
                </div>
                <div class="form-group password-toggle">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Your password" required>
                    <button type="button" class="password-toggle-btn" onclick="togglePassword()">Show</button>
                </div>
                <button type="submit" class="btn btn-primary btn-block" id="login-btn">Log In</button>
            </form>
            <p class="auth-footer">
                Don't have an account?
                <a href="/JourneyHub/pages/signup.php">Sign up</a>
            </p>
        </div>
    </div>
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const btn = document.querySelector('.password-toggle-btn');
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = 'Hide';
            } else {
                input.type = 'password';
                btn.textContent = 'Show';
            }
        }
    </script>
</body>
</html>
