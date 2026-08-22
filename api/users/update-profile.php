<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$userId = $_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

if (!$name || !$email) {
    echo json_encode(['success' => false, 'error' => 'Name and email required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email']);
    exit;
}

// Handle photo upload
$photoFilename = null;
if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_photo'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
        echo json_encode(['success' => false, 'error' => 'Only JPG/PNG allowed']);
        exit;
    }
    
    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'error' => 'Max 2MB']);
        exit;
    }
    
    $ext = $mimeType === 'image/jpeg' ? 'jpg' : 'png';
    $photoFilename = 'user_' . $userId . '_' . time() . '.' . $ext;
    $uploadDir = __DIR__ . '/../../assets/images/profiles/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    move_uploaded_file($file['tmp_name'], $uploadDir . $photoFilename);
}

// Update user
if ($photoFilename) {
    $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, profile_photo = ? WHERE id = ?');
    $stmt->execute([$name, $email, $photoFilename, $userId]);
} else {
    $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
    $stmt->execute([$name, $email, $userId]);
}

$_SESSION['name'] = $name;
$_SESSION['email'] = $email;

echo json_encode(['success' => true]);
