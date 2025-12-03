<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $incident_id = $_POST['incident_id'] ?? 0;
    $source = $_POST['source'] ?? '';
    $observation = trim($_POST['observation'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    if (empty($observation)) {
        echo json_encode(['success' => false, 'message' => 'Observation cannot be empty']);
        exit();
    }
    
    // Determine which table to update
    if ($source === 'civilian') {
        $sql = "UPDATE civObs SET description = CONCAT(COALESCE(description, ''), '\n[', NOW(), ' - ', ?, ']: ', ?) WHERE id = ?";
    } else {
        // For official records, we'll store observations in a separate field or table
        // For now, we'll create an observations table
        $sql = "INSERT INTO incident_observations (incident_id, user_id, observation, created_at) VALUES (?, ?, ?, NOW())";
    }
    
    $stmt = $mysqli->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit();
    }
    
    if ($source === 'civilian') {
        $username = $_SESSION['username'];
        $stmt->bind_param("ssi", $username, $observation, $incident_id);
    } else {
        $stmt->bind_param("iis", $incident_id, $user_id, $observation);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Observation added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add observation']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
