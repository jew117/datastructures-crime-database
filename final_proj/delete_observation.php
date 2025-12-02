<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $observation_id = $_POST['observation_id'] ?? 0;
    $user_id = $_SESSION['user_id'];
    $is_admin = $_SESSION['is_admin'] ?? false;
    
    // Check if user has permission to delete this observation
    $check_sql = "SELECT user_id FROM incident_observations WHERE id = ?";
    $check_stmt = $mysqli->prepare($check_sql);
    $check_stmt->bind_param("i", $observation_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $observation = $result->fetch_assoc();
    $check_stmt->close();
    
    if (!$observation) {
        echo json_encode(['success' => false, 'message' => 'Observation not found']);
        exit();
    }
    
    // Only allow delete if user is admin OR if user owns the observation
    if (!$is_admin && $observation['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this observation']);
        exit();
    }
    
    // Delete the observation
    $sql = "DELETE FROM incident_observations WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit();
    }
    
    $stmt->bind_param("i", $observation_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Observation deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete observation']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
