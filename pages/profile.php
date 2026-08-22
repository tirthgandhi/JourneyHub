<?php
/**
 * Profile / Settings Page
 * User profile management, photo upload, preferences, account deletion
 */

require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/db.php';

$errors = [];
$success = '';
$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: /JourneyHub/pages/login.php');
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Update profile information
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $language = $_POST['language'] ?? 'en';
        $saved_destinations = trim($_POST['saved_destinations'] ?? '');
        
        // Validation
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
        
        // Check if email changed and is unique
        if ($email !== $user['email']) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                $errors[] = 'Email address is already in use';
            }
        }
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, language = ?, saved_destinations = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $email, $language, $saved_destinations, $user_id]);
                
                // Update session
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                
                $success = 'Profile updated successfully!';
                
            } catch (PDOException $e) {
                $errors[] = 'Failed to update profile. Please try again.';
            }
        }
    }
    
    // Upload profile photo
    elseif ($action === 'upload_photo') {
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_photo'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            // Validate file type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime_type, $allowed_types)) {
                $errors[] = 'Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.';
            } elseif ($file['size'] > $max_size) {
                $errors[] = 'File size exceeds 5MB limit.';
            } else {
                // Create upload directory if it doesn't exist
                $upload_dir = '../assets/images/profiles/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // Delete old photo if exists
                    if (!empty($user['profile_photo']) && file_exists('../assets/images/profiles/' . $user['profile_photo'])) {
                        unlink('../assets/images/profiles/' . $user['profile_photo']);
                    }
                    
                    // Update database
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
                        $stmt->execute([$filename, $user_id]);
                        
                        // Update session
                        $_SESSION['profile_photo'] = $filename;
                        
                        // Refresh user data
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $user = $stmt->fetch();
                        
                        $success = 'Profile photo updated successfully!';
                        
                    } catch (PDOException $e) {
                        $errors[] = 'Failed to save photo to database.';
                    }
                } else {
                    $errors[] = 'Failed to upload file.';
                }
            }
        } else {
            $errors[] = 'No file uploaded or upload error occurred.';
        }
    }
    
    // Delete profile photo
    elseif ($action === 'delete_photo') {
        if (!empty($user['profile_photo'])) {
            $photo_path = '../assets/images/profiles/' . $user['profile_photo'];
            if (file_exists($photo_path)) {
                unlink($photo_path);
            }
            
            try {
                $stmt = $pdo->prepare("UPDATE users SET profile_photo = NULL WHERE id = ?");
                $stmt->execute([$user_id]);
                
                // Update session
                $_SESSION['profile_photo'] = null;
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                
                $success = 'Profile photo removed successfully!';
                
            } catch (PDOException $e) {
                $errors[] = 'Failed to remove photo.';
            }
        }
    }
    
    // Delete account
    elseif ($action === 'delete_account') {
        $confirm = $_POST['confirm_delete'] ?? '';
        
        if ($confirm === 'DELETE') {
            try {
                // Delete profile photo if exists
                if (!empty($user['profile_photo'])) {
                    $photo_path = '../assets/images/profiles/' . $user['profile_photo'];
                    if (file_exists($photo_path)) {
                        unlink($photo_path);
                    }
                }
                
                // Delete user
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                
                // Destroy session
                session_destroy();
                
                // Redirect to signup with message
                header('Location: /JourneyHub/pages/login.php?deleted=1');
                exit;
                
            } catch (PDOException $e) {
                $errors[] = 'Failed to delete account. Please try again.';
            }
        } else {
            $errors[] = 'Please type DELETE to confirm account deletion.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile & Settings - JourneyHub</title>
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/components.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/auth.css">
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>Profile & Settings</h1>
            <div class="profile-nav">
                <a href="/JourneyHub/pages/dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="/JourneyHub/pages/logout.php" class="btn btn-secondary">Logout</a>
            </div>
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
        
        <div class="profile-grid">
            <!-- Profile Photo Section -->
            <div class="profile-card">
                <h2>Profile Photo</h2>
                <div class="photo-section">
                    <?php if (!empty($user['profile_photo'])): ?>
                        <img src="/JourneyHub/assets/images/profiles/<?php echo escape_html($user['profile_photo']); ?>" 
                             alt="Profile Photo" 
                             class="profile-photo-display">
                    <?php else: ?>
                        <div class="profile-photo-placeholder">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" class="photo-form">
                        <input type="hidden" name="action" value="upload_photo">
                        <input type="file" name="profile_photo" id="profile_photo" accept="image/*" required>
                        <button type="submit" class="btn btn-primary btn-sm">Upload Photo</button>
                    </form>
                    
                    <?php if (!empty($user['profile_photo'])): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete_photo">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Remove profile photo?')">
                                Remove Photo
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <small class="form-hint">Max 5MB. JPEG, PNG, GIF, or WebP.</small>
                </div>
            </div>
            
            <!-- Profile Information -->
            <div class="profile-card">
                <h2>Profile Information</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-control"
                            value="<?php echo escape_html($user['name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control"
                            value="<?php echo escape_html($user['email']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="language">Language Preference</label>
                        <select id="language" name="language" class="form-control">
                            <option value="en" <?php echo $user['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                            <option value="es" <?php echo $user['language'] === 'es' ? 'selected' : ''; ?>>Español</option>
                            <option value="fr" <?php echo $user['language'] === 'fr' ? 'selected' : ''; ?>>Français</option>
                            <option value="de" <?php echo $user['language'] === 'de' ? 'selected' : ''; ?>>Deutsch</option>
                            <option value="it" <?php echo $user['language'] === 'it' ? 'selected' : ''; ?>>Italiano</option>
                            <option value="pt" <?php echo $user['language'] === 'pt' ? 'selected' : ''; ?>>Português</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="saved_destinations">Saved Destinations</label>
                        <textarea 
                            id="saved_destinations" 
                            name="saved_destinations" 
                            class="form-control"
                            rows="4"
                            placeholder="Enter your favorite destinations, one per line"
                        ><?php echo escape_html($user['saved_destinations'] ?? ''); ?></textarea>
                        <small class="form-hint">List your favorite travel destinations</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        Save Changes
                    </button>
                </form>
            </div>
            
            <!-- Account Information -->
            <div class="profile-card">
                <h2>Account Information</h2>
                <div class="info-group">
                    <label>Account Type</label>
                    <p><span class="badge badge-<?php echo $user['role'] === 'admin' ? 'admin' : 'user'; ?>">
                        <?php echo escape_html(ucfirst($user['role'])); ?>
                    </span></p>
                </div>
                
                <div class="info-group">
                    <label>Member Since</label>
                    <p><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>
            
            <!-- Danger Zone -->
            <div class="profile-card danger-zone">
                <h2>Danger Zone</h2>
                <p>Once you delete your account, there is no going back. Please be certain.</p>
                
                <button type="button" class="btn btn-danger" onclick="showDeleteModal()">
                    Delete Account
                </button>
            </div>
        </div>
    </div>
    
    <!-- Delete Account Modal -->
    <div id="delete-modal" class="modal" style="display:none;">
        <div class="modal-content">
            <h2>Delete Account</h2>
            <p>This action cannot be undone. All your data will be permanently deleted.</p>
            <p>Type <strong>DELETE</strong> to confirm:</p>
            
            <form method="POST" id="delete-form">
                <input type="hidden" name="action" value="delete_account">
                <div class="form-group">
                    <input 
                        type="text" 
                        name="confirm_delete" 
                        id="confirm_delete" 
                        class="form-control"
                        placeholder="Type DELETE"
                        required
                    >
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="hideDeleteModal()">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete My Account</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function showDeleteModal() {
            document.getElementById('delete-modal').style.display = 'flex';
        }
        
        function hideDeleteModal() {
            document.getElementById('delete-modal').style.display = 'none';
            document.getElementById('confirm_delete').value = '';
        }
        
        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('delete-modal');
            if (event.target === modal) {
                hideDeleteModal();
            }
        }
    </script>
</body>
</html>
