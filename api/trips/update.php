<?php
/**
 * API: Update Trip — JourneyHub
 *
 * POST handler. Validates all fields server-side, handles optional cover photo
 * re-upload, updates the trip. Enforces ownership check. Returns JSON.
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

$tripId      = (int) ($_POST['trip_id'] ?? 0);
$name        = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$startDate   = trim($_POST['start_date'] ?? '');
$endDate     = trim($_POST['end_date'] ?? '');

if ($tripId <= 0) {
    $errors[] = 'Invalid trip ID.';
}
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

// --- Verify ownership ---
if ($tripId > 0) {
    $stmt = $pdo->prepare('SELECT id, cover_image FROM trips WHERE id = ? AND user_id = ?');
    $stmt->execute([$tripId, $userId]);
    $existingTrip = $stmt->fetch();

    if (!$existingTrip) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Trip not found or access denied.']);
        exit;
    }
}

// --- Handle cover photo upload (optional) ---
$coverFilename = $existingTrip['cover_image']; // keep existing by default

if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $file    = $_FILES['cover_image'];
    $maxSize = 2 * 1024 * 1024;

    if ($file['size'] > $maxSize) {
        $errors[] = 'Cover photo must be 2 MB or smaller.';
    }

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

        $coverFilename = 'trip_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $uploadDir     = __DIR__ . '/../../assets/images/covers/';
        $destination   = $uploadDir . $coverFilename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $errors[] = 'Failed to save cover photo. Please try again.';
            $coverFilename = $existingTrip['cover_image'];
        } else {
            // Delete old cover image if it existed
            if ($existingTrip['cover_image']) {
                $oldPath = $uploadDir . $existingTrip['cover_image'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
        }
    }
}

// --- Return errors if any ---
if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// --- Update trip (ownership enforced in WHERE clause) ---
$stmt = $pdo->prepare('
    UPDATE trips
    SET name = ?, description = ?, start_date = ?, end_date = ?, cover_image = ?
    WHERE id = ? AND user_id = ?
');
$stmt->execute([$name, $description, $startDate, $endDate, $coverFilename, $tripId, $userId]);

echo json_encode(['success' => true, 'trip_id' => $tripId]);
