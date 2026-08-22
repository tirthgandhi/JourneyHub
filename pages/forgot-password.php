<?php
/**
 * Forgot Password Page
 * Generate password reset token
 */

session_start();

require_once '../config/database.php';
require_once '../includes/auth-check.php';

$errors = [];
$success = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($errors)) {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store token in database
                $stmt = $pdo->prepare("
                    INSERT INTO password_resets (email, token, expires_at) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$email, $token, $expires_at]);
                
                // In production, send email with reset link
                // For demo purposes, we'll show the link
                $reset_link = "http://localhost/JourneyHub/pages/reset-password.php?token=" . $token;
                
                $success = "Password reset link generated! (In production, this would be emailed)<br>
                           <strong>Demo Link:</strong> <a href='{$reset_link}' target='_blank'>Click here to reset password</a><br>
                           <small>Link expires in 1 hour</small>";
            } else {
                // Don't reveal if email doesn't exist (security best practice)
                $success = "If an account exists with that email, you will receive a password reset link.";
            }
            
        } catch (PDOException $e) {
            $errors[] = 'Failed to process request. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - JourneyHub</title>
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Forgot Password?</h1>
                <p>Enter your email to receive a password reset link</p>
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
                    <?php echo $success; // Intentionally not escaped for demo link ?>
                </div>
            <?php endif; ?>
            
            <form id="forgot-password-form" method="POST" action="">
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
                
                <button type="submit" class="btn btn-primary btn-block">
                    Send Reset Link
                </button>
            </form>
            
            <div class="auth-footer">
                <p><a href="/JourneyHub/pages/login.php">Back to Login</a></p>
            </div>
        </div>
    </div>
    
    <script src="/JourneyHub/assets/js/auth.js"></script>
</body>
</html>
