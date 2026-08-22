<?php
/**
 * Signup Page — JourneyHub
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

    $name            = trim($_POST['name'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        // Check for duplicate email
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $hashedPassword]);

            // Auto-login after signup
            $userId = $pdo->lastInsertId();
            $_SESSION['user_id'] = $userId;
            $_SESSION['name']    = $name;
            $_SESSION['email']   = $email;

            header('Location: /JourneyHub/pages/dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up — JourneyHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/components.css">
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
            <p class="auth-subtitle">Create your account and start planning trips.</p>

            <?php if (!empty($errors)): ?>
                <div class="auth-errors">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form" id="signup-form">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                           value="<?php echo htmlspecialchars($name ?? ''); ?>"
                           placeholder="Your full name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars($email ?? ''); ?>"
                           placeholder="you@example.com" required>
                </div>
                <div class="form-group password-toggle">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="At least 6 characters" required>
                    <button type="button" class="password-toggle-btn" onclick="togglePassword('password')">Show</button>
                </div>
                <div class="form-group password-toggle">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="Re-enter your password" required>
                    <button type="button" class="password-toggle-btn" onclick="togglePassword('confirm_password')">Show</button>
                </div>
                <button type="submit" class="btn btn-primary btn-block" id="signup-btn">Create Account</button>
            </form>
            <p class="auth-footer">
                Already have an account?
                <a href="/JourneyHub/pages/login.php">Log in</a>
            </p>
        </div>
    </div>
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const btn = input.nextElementSibling;
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
