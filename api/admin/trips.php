<?php
/**
 * Admin Trips API
 * GET: List/search trips
 * POST: Remove trips
 */

header('Content-Type: application/json');

// Admin authorization check
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

try {
    $pdo = getPDO();
    
    // Re-verify admin role from database
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Admin access required']);
        exit;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Authorization check failed']);
    exit;
}

// Handle GET request (list/search trips)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        
        $sql = "SELECT t.id, t.name, u.name as owner_name, t.is_public, t.created_at
                FROM trips t 
                JOIN users u ON u.id = t.user_id
                WHERE 1=1";
        $params = [];
        
        if (!empty($q)) {
            $sql .= " AND t.name LIKE ?";
            $params[] = '%' . $q . '%';
        }
        
        $sql .= " ORDER BY t.created_at DESC LIMIT 50";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $trips = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'trips' => $trips
        ]);
        
    } catch (Exception $e) {
        error_log("Admin trips GET error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Failed to load trips']);
    }
}

// Handle POST request (remove trip)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $trip_id = isset($_POST['trip_id']) ? (int)$_POST['trip_id'] : 0;
        $action = isset($_POST['action']) ? trim($_POST['action']) : '';
        
        if (!$trip_id) {
            echo json_encode(['success' => false, 'error' => 'Trip ID is required']);
            exit;
        }
        
        if ($action !== 'remove') {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            exit;
        }
        
        // Verify trip exists
        $stmt = $pdo->prepare("SELECT id FROM trips WHERE id = ?");
        $stmt->execute([$trip_id]);
        $trip = $stmt->fetch();
        
        if (!$trip) {
            echo json_encode(['success' => false, 'error' => 'Trip not found']);
            exit;
        }
        
        // Delete trip (cascade should handle related records)
        $stmt = $pdo->prepare("DELETE FROM trips WHERE id = ?");
        $stmt->execute([$trip_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to remove trip']);
        }
        
    } catch (Exception $e) {
        error_log("Admin trips POST error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Failed to remove trip']);
    }
}

else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>