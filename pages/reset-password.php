<?php
/**
 * Reset Password Page
 * Verify token and allow user to set new password
 */

session_start();

require_once '../config/database.php';
require_once '../includes/auth-check.php';

$errors = [];
$success = '';
$token = $_GET['token'] ?? '';
$valid_token = false;
$email = '';

// Verify token
if (!empty($token)) {
    try {
        $stmt = $pdo->prepare("
            SELECT email, expires_at, used 
            FROM password_resets 
            WHERE token = ? AND used = 0
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();
        
        if ($reset) {
            // Check if token has expired
            if (strtotime($reset['expires_at']) > time()) {
                $valid_token = true;
                $email = $reset['email'];
            } else {
                $errors[] = 'This password reset link has expired. Please request a new one.';
            }
        } else {
            $errors[] = 'Invalid or already used reset link.';
        }
    } catch (PDOException $e) {
        $errors[] = 'Failed to verify reset link.';
    }
} else {
    $errors[] = 'No reset token provided.';
}

// Handle password reset submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    if (empty($errors)) {
        try {
            // Update user password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashed_password, $email]);
            
            // Mark token as used
            $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $stmt->execute([$token]);
            
            $success = 'Password reset successful! You can now login with your new password.';
            $valid_token = false; // Prevent form from showing again
            
        } catch (PDOException $e) {
            $errors[] = 'Failed to reset password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - JourneyHub</title>
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Reset Password</h1>
                <p>Enter your new password</p>
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
                    <br><br>
                    <a href="/JourneyHub/pages/login.php" class="btn btn-primary">Go to Login</a>
                </div>
            <?php endif; ?>
            
            <?php if ($valid_token): ?>
                <form id="reset-password-form" method="POST" action="">
                    <input type="hidden" name="token" value="<?php echo escape_html($token); ?>">
                    
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control"
                            required
                            autofocus
                        >
                        <small class="form-hint">Must be at least 8 characters</small>
                        <span class="error-message" id="password-error"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
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
                        Reset Password
                    </button>
                </form>
            <?php endif; ?>
            
            <div class="auth-footer">
                <p><a href="/JourneyHub/pages/login.php">Back to Login</a></p>
            </div>
        </div>
    </div>
    
    <script src="/JourneyHub/assets/js/auth.js"></script>
    <script>
        // Initialize reset password form validation
        if (typeof AuthValidator !== 'undefined') {
            AuthValidator.initResetPassword();
        }
    </script>
</body>
</html>
