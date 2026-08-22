<?php
/**
 * API: Create Trip — JourneyHub
 *
 * POST handler. Validates all fields server-side, handles cover photo upload,
 * inserts into the trips table. Returns JSON.
 */

header('Content-Type: application/json');

session_start();

// --- Auth check ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$userId = $_SESSION['user_id'];
$errors = [];

// --- Collect & validate inputs ---
$name        = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$startDate   = trim($_POST['start_date'] ?? '');
$endDate     = trim($_POST['end_date'] ?? '');
$budget      = trim($_POST['budget'] ?? '0');
$destinations = isset($_POST['destinations']) ? json_decode($_POST['destinations'], true) : [];

if ($name === '') {
    $errors[] = 'Trip name is required.';
} elseif (strlen($name) > 150) {
    $errors[] = 'Trip name must be 150 characters or fewer.';
}

if ($startDate === '') {
    $errors[] = 'Start date is required.';
}
if ($endDate === '') {
    $errors[] = 'End date is required.';
}

if ($startDate !== '' && $endDate !== '') {
    if (strtotime($endDate) < strtotime($startDate)) {
        $errors[] = 'End date must be on or after the start date.';
    }
}

// Validate budget
if ($budget !== '' && (!is_numeric($budget) || $budget < 0)) {
    $errors[] = 'Budget must be a positive number.';
}

// --- Handle cover photo upload ---
$coverFilename = null;

if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $file     = $_FILES['cover_image'];
    $maxSize  = 2 * 1024 * 1024; // 2 MB

    // Validate file size
    if ($file['size'] > $maxSize) {
        $errors[] = 'Cover photo must be 2 MB or smaller.';
    }

    // Validate MIME type using finfo
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];

    if (!in_array($mimeType, $allowedMimes, true)) {
        $errors[] = 'Cover photo must be a JPG, PNG, or GIF image.';
    }

    if (empty($errors)) {
        $ext = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            default      => 'jpg',
        };

        // Generate a unique filename
        $coverFilename = 'trip_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $uploadDir     = __DIR__ . '/../../assets/images/covers/';
        $destination   = $uploadDir . $coverFilename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $errors[] = 'Failed to save cover photo. Please try again.';
            $coverFilename = null;
        }
    }
}

// --- Return errors if any ---
if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// --- Insert trip ---
$stmt = $pdo->prepare('
    INSERT INTO trips (user_id, name, description, start_date, end_date, budget, cover_image)
    VALUES (?, ?, ?, ?, ?, ?, ?)
');
$stmt->execute([$userId, $name, $description, $startDate, $endDate, $budget, $coverFilename]);

$tripId = $pdo->lastInsertId();

// --- Insert trip stops (destinations) ---
if (!empty($destinations) && is_array($destinations)) {
    $stmtStop = $pdo->prepare('
        INSERT INTO trip_stops (trip_id, city_id, start_date, end_date, stop_order)
        VALUES (?, ?, ?, ?, ?)
    ');
    
    foreach ($destinations as $index => $cityId) {
        // For now, use trip start/end dates for each stop
        // In future iterations, users can specify individual stop dates
        $stmtStop->execute([$tripId, $cityId, $startDate, $endDate, $index + 1]);
    }
}

echo json_encode(['success' => true, 'trip_id' => (int) $tripId]);
