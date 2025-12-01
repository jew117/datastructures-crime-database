<?php

function register_user($mysqli, $username, $email, $password) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        error_log("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
        return false;
    }


    $stmt->bind_param("sss", $username, $email, $password_hash);
    

    if (!$stmt->execute()) {
     
        if ($stmt->errno === 1062) {
            $stmt->close();
            return false; 
        } else {
            error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
    
    $stmt->close();
    return true; 
}
function authenticate_user($mysqli, $email, $password) {
    $sql = "SELECT user_id, username, password_hash, is_admin FROM users WHERE email = ? LIMIT 1";
    
    
    try {
        $stmt = $mysqli->prepare($sql);

        if (!$stmt) {
            error_log("Login Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
            return false;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            return [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'is_admin' => (int)$user['is_admin']
            ];
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Login Database Error: " . $e->getMessage());
        return false; 
    }

    return false;
}


function display_crime_data($mysqli, $filters = [], $is_admin = false) {
    
    $incidents_query = "SELECT incident_id AS id, department_code, report_number, crime_type, incident_datetime, location, 'official' AS source FROM incidents WHERE 1=1";
    $civobs_query    = "SELECT id, department_code, report_number, crime_type, incident_datetime, location, 'civilian' AS source FROM civObs WHERE 1=1";

    $queries_to_run = []; 
    $params = [];         
    $types = '';          

    $dept_filter = $filters['department'] ?? '';
    $start_date  = $filters['start_date'] ?? ''; 
    $end_date    = $filters['end_date'] ?? '';   

    // Incidents Queries
    if ($dept_filter === '' || $dept_filter === 'APD' || $dept_filter === 'UAPD') {
        $sql = $incidents_query;
        
        if ($dept_filter !== '') {
            $sql .= " AND department_code = ? ";
            $types .= 's'; $params[] = $dept_filter;
        }
        
        if ($start_date !== '') {
            $sql .= " AND incident_datetime >= ? ";
            $types .= 's'; $params[] = $start_date . " 00:00:00";
        }
        if ($end_date !== '') {
            $sql .= " AND incident_datetime <= ? ";
            $types .= 's'; $params[] = $end_date . " 23:59:59";
        }

        if (!empty($filters['crime_type'])) {
            $sql .= " AND crime_type = ? ";
            $types .= 's'; $params[] = $filters['crime_type'];
        }
        if (!empty($filters['location'])) {
            $sql .= " AND location LIKE ? ";
            $types .= 's'; $params[] = '%' . $filters['location'] . '%';
        }
        
        $queries_to_run[] = $sql;
    }

    // Civ Queries
    if ($dept_filter === '' || $dept_filter === 'Civilian Observation') {
        $sql = $civobs_query;

   
        if ($start_date !== '') {
            $sql .= " AND incident_datetime >= ? ";
            $types .= 's'; $params[] = $start_date . " 00:00:00";
        }
        if ($end_date !== '') {
            $sql .= " AND incident_datetime <= ? ";
            $types .= 's'; $params[] = $end_date . " 23:59:59";
        }

        if (!empty($filters['crime_type'])) {
            $sql .= " AND crime_type = ? ";
            $types .= 's'; $params[] = $filters['crime_type'];
        }
        if (!empty($filters['location'])) {
            $sql .= " AND location LIKE ? ";
            $types .= 's'; $params[] = '%' . $filters['location'] . '%';
        }
        
        $queries_to_run[] = $sql;
    }

    if (empty($queries_to_run)) { return "<p>No data found.</p>"; }

    $final_sql = implode(" UNION ALL ", $queries_to_run);
    $final_sql .= " ORDER BY incident_datetime DESC LIMIT 100";

    $stmt = $mysqli->prepare($final_sql);
    if (!$stmt) { return "<p style='color:red'>Query Error</p>"; }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    $output = ""; 
    
    if ($result->num_rows > 0) {
        $action_header = $is_admin ? "<th>Actions</th>" : "";

        $output .= "<table class='crime-table'>";
        $output .= "<thead><tr><th>Department</th><th>Report Number</th><th>Type</th><th>Date/Time</th><th>Location</th>" . $action_header . "</tr></thead><tbody>";
        
        while($row = $result->fetch_assoc()) {
            $row_style = ($row['source'] === 'civilian') ? 'style="background-color: #fff8e1;"' : '';

            $output .= "<tr $row_style>";
            $output .= "<td><strong>" . htmlspecialchars($row['department_code']) . "</strong></td>";
            $output .= "<td>" . htmlspecialchars($row['report_number']) . "</td>";
            $output .= "<td>" . htmlspecialchars($row['crime_type']) . "</td>";
            $output .= "<td>" . htmlspecialchars(date('Y-m-d H:i', strtotime($row['incident_datetime'])) ?? 'N/A') . "</td>";
            $output .= "<td>" . htmlspecialchars($row['location']) . "</td>";
            
            if ($is_admin) {
                 $output .= "<td>
                    <a href='edit_record.php?id=" . $row['id'] . "&source=" . $row['source'] . "'>Edit</a> | 
                    <a href='delete_record.php?id=" . $row['id'] . "&source=" . $row['source'] . "'>Delete</a>
                 </td>";
            }
            $output .= "</tr>";
        }
        $output .= "</tbody></table>";
    } else {
        $output = "<p>No records matched your filters.</p>";
    }
    
    $stmt->close();
    return $output;
}

function get_record_by_id($mysqli, $id, $source) {
    
    if ($source === 'civilian') {
        $sql = "SELECT id, report_number, crime_type, incident_datetime, location, description FROM civObs WHERE id = ?";
    } else {
        $sql = "SELECT incident_id AS id, report_number, department_code, crime_type, incident_datetime, location FROM incidents WHERE incident_id = ?";
    }

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function update_record($mysqli, $id, $source, $data) {
    $type = $data['crime_type'];
    $date = $data['incident_datetime'];
    $loc  = $data['location'];
    
    if ($source === 'civilian') {
        $desc = $data['description'] ?? '';
        $sql = "UPDATE civObs SET crime_type=?, incident_datetime=?, location=?, description=? WHERE id=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssssi", $type, $date, $loc, $desc, $id);
    } else {
        $dept = $data['department'];
        $reportNum = $data['report_number'];
        $sql = "UPDATE incidents SET department_code=?, report_number=?, crime_type=?, incident_datetime=?, location=? WHERE incident_id=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sssssi", $dept, $reportNum, $type, $date, $loc, $id);
    }

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    }
    return "Error updating: " . $mysqli->error;
}

function delete_record($mysqli, $id, $source) {
    if ($source === 'civilian') {
        $sql = "DELETE FROM civObs WHERE id = ?";
    } else {
        $sql = "DELETE FROM incidents WHERE incident_id = ?";
    }
    
    $stmt = $mysqli->prepare($sql);
   
    if (!$stmt) {
        error_log("Delete Prepare Error: " . $mysqli->error);
        return false;
    }
    
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        error_log("Delete Execute Error: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

function add_record($mysqli, $data) {
    $dept = $data['department'];
    $type = $data['crime_type'];
    $date = $data['incident_datetime'];
    $loc  = $data['location'];
    
   
    if ($dept === 'Civilian Observation') {
        $userId = $_SESSION['user_id']; 
        $desc   = $data['description'] ?? '';
        
        $sql = "INSERT INTO civObs (user_id, crime_type, incident_datetime, location, description) VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        
        if (!$stmt) { return "Error preparing civilian query: " . $mysqli->error; }
        
        $stmt->bind_param("issss", $userId, $type, $date, $loc, $desc);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        $stmt->close();
        return "Error adding observation: " . $stmt->error;
    } 
    
    else {

        if (empty($_SESSION['is_admin'])) {
            return "Error: Only administrators can add official police records.";
        }

        $reportNum = $data['report_number'];
        if (empty($reportNum)) return "Report Number is required for official police records.";

        $sql = "INSERT INTO incidents (department_code, report_number, crime_type, incident_datetime, reported_datetime, location) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        
        if (!$stmt) { return "Error preparing incident query: " . $mysqli->error; }

        $stmt->bind_param("ssssss", $dept, $reportNum, $type, $date, $date, $loc);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        
        if ($mysqli->errno === 1062) {
            $stmt->close();
            return "Error: That Report Number already exists.";
        }
        
        $error = $stmt->error;
        $stmt->close();
        return "Error adding incident: " . $error;
    }
}
?>