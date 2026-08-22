<?php
/**
 * Admin Users API
 * GET: List/search users
 * POST: Deactivate/reactivate users
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

// Handle GET request (list/search users)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $role = isset($_GET['role']) ? trim($_GET['role']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $sortBy = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'created_at';
        $sortOrder = isset($_GET['sort_order']) ? trim($_GET['sort_order']) : 'DESC';
        
        // Validate sort column against allowed list
        $allowedSort = ['created_at', 'name', 'email', 'role', 'status'];
        if (!in_array($sortBy, $allowedSort)) {
            $sortBy = 'created_at';
        }
        
        // Validate sort order
        if (!in_array(strtoupper($sortOrder), ['ASC', 'DESC'])) {
            $sortOrder = 'DESC';
        }
        
        $sql = "SELECT id, name, email, role, status, created_at FROM users WHERE 1=1";
        $params = [];
        
        if (!empty($q)) {
            $sql .= " AND (name LIKE ? OR email LIKE ?)";
            $term = '%' . $q . '%';
            $params[] = $term;
            $params[] = $term;
        }
        
        if (!empty($role) && in_array($role, ['user', 'admin'])) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        
        if (!empty($status) && in_array($status, ['active', 'inactive'])) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY " . $sortBy . " " . strtoupper($sortOrder) . " LIMIT 100";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'users' => $users
        ]);
        
    } catch (Exception $e) {
        error_log("Admin users GET error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Failed to load users']);
    }
}

// Handle POST request (deactivate/reactivate user)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        $action = isset($_POST['action']) ? trim($_POST['action']) : '';
        
        if (!$user_id) {
            echo json_encode(['success' => false, 'error' => 'User ID is required']);
            exit;
        }
        
        if (!in_array($action, ['deactivate', 'reactivate'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            exit;
        }
        
        // Check that target user exists
        $stmt = $pdo->prepare("SELECT id, role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $targetUser = $stmt->fetch();
        
        if (!$targetUser) {
            echo json_encode(['success' => false, 'error' => 'User not found']);
            exit;
        }
        
        // Never allow deactivating an admin account
        if ($targetUser['role'] === 'admin') {
            echo json_encode(['success' => false, 'error' => 'Cannot deactivate admin accounts']);
            exit;
        }
        
        // Update user status
        $newStatus = $action === 'deactivate' ? 'inactive' : 'active';
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role != 'admin'");
        $stmt->execute([$newStatus, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update user status']);
        }
        
    } catch (Exception $e) {
        error_log("Admin users POST error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Failed to update user']);
    }
}

else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>