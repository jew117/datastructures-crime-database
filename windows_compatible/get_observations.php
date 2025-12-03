<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require __DIR__ . '/db.php';

$incident_id = $_GET['incident_id'] ?? 0;
$source = $_GET['source'] ?? '';

$observations = [];

if ($source === 'civilian') {
    $sql = "SELECT description FROM civObs WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $incident_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $observations[] = ['text' => $row['description'] ?? 'No observations yet'];
    }
    $stmt->close();
} else {
    // Check if observations table exists, if not create it
    $check_table = "SHOW TABLES LIKE 'incident_observations'";
    $result = $mysqli->query($check_table);
    
    if ($result->num_rows == 0) {
        // Create the table with updated_at field
        $create_table = "CREATE TABLE incident_observations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            incident_id BIGINT NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            observation TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            FOREIGN KEY (incident_id) REFERENCES incidents(incident_id),
            FOREIGN KEY (user_id) REFERENCES users(user_id)
        )";
        $mysqli->query($create_table);
    } else {
        // Check if updated_at column exists, if not add it
        $check_column = "SHOW COLUMNS FROM incident_observations LIKE 'updated_at'";
        $col_result = $mysqli->query($check_column);
        if ($col_result->num_rows == 0) {
            $mysqli->query("ALTER TABLE incident_observations ADD COLUMN updated_at DATETIME DEFAULT NULL");
        }
    }
    
    $sql = "SELECT o.id, o.observation, o.created_at, o.updated_at, o.user_id, u.username 
            FROM incident_observations o 
            JOIN users u ON o.user_id = u.user_id 
            WHERE o.incident_id = ? 
            ORDER BY o.created_at DESC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $incident_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $observations[] = [
            'id' => $row['id'],
            'text' => $row['observation'],
            'username' => $row['username'],
            'user_id' => $row['user_id'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
    $stmt->close();
}

echo json_encode(['success' => true, 'observations' => $observations]);
?>
