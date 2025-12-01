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
$crime_type  = $_POST['crime_type'] ?? '';
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

//When a specifc crime is selected sort by time. 
$groupByDate = !empty($crime_type);

// Select Clause
if ($groupByDate) {
    // Group by Day (YYYY-MM-DD)
    $select_clause = "SELECT DATE_FORMAT(incident_datetime, '%Y-%m-%d') as date_label, COUNT(*) as count";
    $group_clause  = "GROUP BY date_label ORDER BY date_label ASC";
} else {
    // Group by Crime Type
    $select_clause = "SELECT crime_type, COUNT(*) as count";
    $group_clause  = "GROUP BY crime_type";
}

//Fetch Incidents
if ($dept_filter === '' || $dept_filter === 'APD' || $dept_filter === 'UAPD') {
    $sql = "$select_clause FROM incidents WHERE 1=1 ";
    $p = []; $t = '';
    
    if (!empty($crime_type)) { $sql .= " AND crime_type = ? "; $p[] = $crime_type; $t .= 's'; }
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
    
    if (!empty($crime_type)) { $sql .= " AND crime_type = ? "; $p[] = $crime_type; $t .= 's'; }
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