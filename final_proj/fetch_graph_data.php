<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require __DIR__ . '/db.php';

// Collect filters
$dept_filter = $_POST['department'] ?? '';
$crime_types = isset($_POST['crime_type']) && is_array($_POST['crime_type']) ? $_POST['crime_type'] : [];
// Filter out empty values
$crime_types = array_filter($crime_types);
$location    = $_POST['search_term'] ?? '';
$start_date  = $_POST['start_date'] ?? ''; 
$end_date    = $_POST['end_date'] ?? '';   

$tally = []; 


function addToTally($mysqli, $sql, $types, $params, &$tally, $groupByDate = false) {
    $stmt = $mysqli->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // If grouping by date, use 'date_label', otherwise use 'crime_type'
        $key = $groupByDate ? $row['date_label'] : $row['crime_type'];
        $count = $row['count'];
        
        if (!isset($tally[$key])) $tally[$key] = 0;
        $tally[$key] += $count;
    }
    $stmt->close();
}

// Always group by crime type (not by date)
$groupByDate = false;

// Select Clause - always group by crime type
$select_clause = "SELECT crime_type, COUNT(*) as count";
$group_clause  = "GROUP BY crime_type";

//Fetch Incidents
if ($dept_filter === '' || $dept_filter === 'APD' || $dept_filter === 'UAPD') {
    $sql = "$select_clause FROM incidents WHERE 1=1 ";
    $p = []; $t = '';
    
    // Handle multiple crime types
    if (!empty($crime_types)) {
        $placeholders = implode(',', array_fill(0, count($crime_types), '?'));
        $sql .= " AND crime_type IN ($placeholders) ";
        foreach ($crime_types as $crime_type) {
            $p[] = $crime_type;
            $t .= 's';
        }
    }
    
    if (!empty($location))   { $sql .= " AND location LIKE ? "; $p[] = "%$location%"; $t .= 's'; }
    if ($dept_filter !== '') { $sql .= " AND department_code = ? "; $p[] = $dept_filter; $t .= 's'; }
    if ($start_date !== '')  { $sql .= " AND incident_datetime >= ? "; $p[] = $start_date." 00:00:00"; $t .= 's'; }
    if ($end_date !== '')    { $sql .= " AND incident_datetime <= ? "; $p[] = $end_date." 23:59:59";   $t .= 's'; }
    
    $sql .= " $group_clause";
    addToTally($mysqli, $sql, $t, $p, $tally, $groupByDate);
}

//Fetch Civilian
if ($dept_filter === '' || $dept_filter === 'Civilian Observation') {
    $sql = "$select_clause FROM civObs WHERE 1=1 ";
    $p = []; $t = '';
    
    // Handle multiple crime types
    if (!empty($crime_types)) {
        $placeholders = implode(',', array_fill(0, count($crime_types), '?'));
        $sql .= " AND crime_type IN ($placeholders) ";
        foreach ($crime_types as $crime_type) {
            $p[] = $crime_type;
            $t .= 's';
        }
    }
    
    if (!empty($location))   { $sql .= " AND location LIKE ? "; $p[] = "%$location%"; $t .= 's'; }
    if ($start_date !== '')  { $sql .= " AND incident_datetime >= ? "; $p[] = $start_date." 00:00:00"; $t .= 's'; }
    if ($end_date !== '')    { $sql .= " AND incident_datetime <= ? "; $p[] = $end_date." 23:59:59";   $t .= 's'; }
    
    $sql .= " $group_clause";
    addToTally($mysqli, $sql, $t, $p, $tally, $groupByDate);
}

if ($groupByDate) {
    ksort($tally);
}

$labels = array_keys($tally);
$data = array_values($tally);

echo json_encode(['labels' => $labels, 'data' => $data]);
?>
