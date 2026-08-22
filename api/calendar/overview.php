<?php
/**
 * Calendar Overview API
 * 
 * GET /api/calendar/overview.php
 * 
 * Returns all trips for the logged-in user with status classification
 */

header('Content-Type: application/json');
session_start();

// Authentication check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

try {
    // Get month parameter (optional, defaults to current month)
    $month = isset($_GET['month']) ? trim($_GET['month']) : date('Y-m');
    
    // Validate month format
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid month format. Use YYYY-MM']);
        exit;
    }
    
    // Fetch all trips for the user
    $stmt = getPDO()->prepare(
        "SELECT id, name, start_date, end_date, description
         FROM trips
         WHERE user_id = ?
         ORDER BY start_date ASC"
    );
    $stmt->execute([$_SESSION['user_id']]);
    $trips = $stmt->fetchAll();
    
    // Classify trips by status
    $today = date('Y-m-d');
    $classified = [
        'upcoming' => [],
        'ongoing' => [],
        'completed' => []
    ];
    
    foreach ($trips as $trip) {
        if ($trip['start_date'] > $today) {
            $classified['upcoming'][] = $trip;
        } elseif ($trip['end_date'] < $today) {
            $classified['completed'][] = $trip;
        } else {
            $classified['ongoing'][] = $trip;
        }
    }
    
    echo json_encode([
        'success' => true,
        'trips' => $classified,
        'all_trips' => $trips,
        'current_month' => $month
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    error_log("Calendar overview error: " . $e->getMessage());
}
