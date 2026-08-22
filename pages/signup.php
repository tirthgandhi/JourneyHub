<?php
/**
 * Signup Page
 * User registration with validation
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
$success = '';
$form_data = [
    'name' => '',
    'email' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Store form data for repopulation
    $form_data['name'] = $name;
    $form_data['email'] = $email;
    
    // Backend validation
    if (empty($name)) {
        $errors[] = 'Name is required';
    } elseif (strlen($name) < 2) {
        $errors[] = 'Name must be at least 2 characters';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email address is already registered';
        }
    }
    
    // If no errors, create user
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, role) 
                VALUES (?, ?, ?, 'user')
            ");
            $stmt->execute([$name, $email, $hashed_password]);
            
            // Get the new user's ID
            $user_id = $pdo->lastInsertId();
            
            // Create session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['role'] = 'user';
            $_SESSION['profile_photo'] = null;
            
            // Redirect to dashboard
            header('Location: /JourneyHub/pages/dashboard.php');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = 'Registration failed. Please try again.';
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
    <title>Sign Up - JourneyHub</title>
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Create Account</h1>
                <p>Join JourneyHub and start planning your adventures</p>
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
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo escape_html($success); ?>
                </div>
            <?php endif; ?>
            
            <form id="signup-form" method="POST" action="" novalidate>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-control"
                        value="<?php echo escape_html($form_data['name']); ?>"
                        required
                    >
                    <span class="error-message" id="name-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control"
                        value="<?php echo escape_html($form_data['email']); ?>"
                        required
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
                    <small class="form-hint">Must be at least 8 characters</small>
                    <span class="error-message" id="password-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-control"
                        required
                    >
                    <span class="error-message" id="confirm-password-error"></span>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Create Account
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="/JourneyHub/pages/login.php">Sign in</a></p>
            </div>
        </div>
    </div>
    
    <script src="/JourneyHub/assets/js/auth.js"></script>
    <script>
        // Initialize signup form validation
        if (typeof AuthValidator !== 'undefined') {
            AuthValidator.initSignup();
        }
    </script>
</body>
</html>
