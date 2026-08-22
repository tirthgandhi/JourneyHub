<?php
/**
 * Login Page
 * User authentication
 */

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /JourneyHub/pages/dashboard.php');
    exit;
}

require_once '../config/database.php';
require_once '../includes/auth-check.php';

$errors = [];
$email = '';
$redirect = $_GET['redirect'] ?? '/JourneyHub/pages/dashboard.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Basic validation
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    // Attempt authentication
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, password, role, profile_photo FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Successful login - create session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_photo'] = $user['profile_photo'];
                
                // Redirect to requested page or dashboard
                header('Location: ' . $redirect);
                exit;
            } else {
                // Generic error message for security (don't reveal if email exists)
                $errors[] = 'Invalid email or password';
            }
            
        } catch (PDOException $e) {
            $errors[] = 'Login failed. Please try again.';
            // Log error for debugging: error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JourneyHub</title>
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Welcome Back</h1>
                <p>Sign in to continue your journey</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo escape_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form id="login-form" method="POST" action="" novalidate>
                <input type="hidden" name="redirect" value="<?php echo escape_html($redirect); ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control"
                        value="<?php echo escape_html($email); ?>"
                        required
                        autofocus
                    >
                    <span class="error-message" id="email-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control"
                        required
                    >
                    <span class="error-message" id="password-error"></span>
                </div>
                
                <div class="form-options">
                    <a href="/JourneyHub/pages/forgot-password.php" class="forgot-link">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Sign In
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="/JourneyHub/pages/signup.php">Sign up</a></p>
            </div>
        </div>
    </div>
    
    <script src="/JourneyHub/assets/js/auth.js"></script>
    <script>
        // Initialize login form validation
        if (typeof AuthValidator !== 'undefined') {
            AuthValidator.initLogin();
        }
    </script>
</body>
</html>
