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
$chart_type  = $_POST['chart_type'] ?? 'bar';

// If no crime types selected (All Crimes) and line chart, get all unique crime types from database
if ($chart_type === 'line' && empty($crime_types)) {
    $crime_types = [];
    
    // Get all unique crime types
    $sql = "SELECT DISTINCT crime_type FROM incidents WHERE 1=1 ";
    $p = []; $t = '';
    
    if (!empty($location))   { $sql .= " AND location LIKE ? "; $p[] = "%$location%"; $t .= 's'; }
    if ($dept_filter !== '' && ($dept_filter === 'APD' || $dept_filter === 'UAPD')) { 
        $sql .= " AND department_code = ? "; $p[] = $dept_filter; $t .= 's'; 
    }
    if ($start_date !== '')  { $sql .= " AND incident_datetime >= ? "; $p[] = $start_date." 00:00:00"; $t .= 's'; }
    if ($end_date !== '')    { $sql .= " AND incident_datetime <= ? "; $p[] = $end_date." 23:59:59"; $t .= 's'; }
    
    $stmt = $mysqli->prepare($sql);
    if ($t) $stmt->bind_param($t, ...$p);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $crime_types[] = $row['crime_type'];
    }
    $stmt->close();
    
    // Also get from civObs if needed
    if ($dept_filter === '' || $dept_filter === 'Civilian Observation') {
        $sql = "SELECT DISTINCT crime_type FROM civObs WHERE 1=1 ";
        $p = []; $t = '';
        
        if (!empty($location))   { $sql .= " AND location LIKE ? "; $p[] = "%$location%"; $t .= 's'; }
        if ($start_date !== '')  { $sql .= " AND incident_datetime >= ? "; $p[] = $start_date." 00:00:00"; $t .= 's'; }
        if ($end_date !== '')    { $sql .= " AND incident_datetime <= ? "; $p[] = $end_date." 23:59:59"; $t .= 's'; }
        
        $stmt = $mysqli->prepare($sql);
        if ($t) $stmt->bind_param($t, ...$p);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            if (!in_array($row['crime_type'], $crime_types)) {
                $crime_types[] = $row['crime_type'];
            }
        }
        $stmt->close();
    }
    
    sort($crime_types);
}

// For line chart with crime types, we need separate data for each crime
if ($chart_type === 'line' && !empty($crime_types)) {
    // Multi-line chart: separate dataset for each crime type
    $datasets = [];
    $all_dates = [];
    
    foreach ($crime_types as $crime_type) {
        $tally = [];
        
        // Fetch from incidents
        if ($dept_filter === '' || $dept_filter === 'APD' || $dept_filter === 'UAPD') {
            $sql = "SELECT DATE(incident_datetime) as date_label, COUNT(*) as count FROM incidents WHERE 1=1 ";
            $p = []; $t = '';
            
            $sql .= " AND crime_type = ? ";
            $p[] = $crime_type;
            $t .= 's';
            
            if (!empty($location))   { $sql .= " AND location LIKE ? "; $p[] = "%$location%"; $t .= 's'; }
            if ($dept_filter !== '') { $sql .= " AND department_code = ? "; $p[] = $dept_filter; $t .= 's'; }
            if ($start_date !== '')  { $sql .= " AND incident_datetime >= ? "; $p[] = $start_date." 00:00:00"; $t .= 's'; }
            if ($end_date !== '')    { $sql .= " AND incident_datetime <= ? "; $p[] = $end_date." 23:59:59"; $t .= 's'; }
            
            $sql .= " GROUP BY date_label ORDER BY date_label ASC";
            
            $stmt = $mysqli->prepare($sql);
            if ($t) $stmt->bind_param($t, ...$p);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $date = $row['date_label'];
                $count = $row['count'];
                $tally[$date] = $count;
                $all_dates[$date] = true;
            }
            $stmt->close();
        }
        
        // Fetch from civObs
        if ($dept_filter === '' || $dept_filter === 'Civilian Observation') {
            $sql = "SELECT DATE(incident_datetime) as date_label, COUNT(*) as count FROM civObs WHERE 1=1 ";
            $p = []; $t = '';
            
            $sql .= " AND crime_type = ? ";
            $p[] = $crime_type;
            $t .= 's';
            
            if (!empty($location))   { $sql .= " AND location LIKE ? "; $p[] = "%$location%"; $t .= 's'; }
            if ($start_date !== '')  { $sql .= " AND incident_datetime >= ? "; $p[] = $start_date." 00:00:00"; $t .= 's'; }
            if ($end_date !== '')    { $sql .= " AND incident_datetime <= ? "; $p[] = $end_date." 23:59:59"; $t .= 's'; }
            
            $sql .= " GROUP BY date_label ORDER BY date_label ASC";
            
            $stmt = $mysqli->prepare($sql);
            if ($t) $stmt->bind_param($t, ...$p);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $date = $row['date_label'];
                $count = $row['count'];
                if (!isset($tally[$date])) $tally[$date] = 0;
                $tally[$date] += $count;
                $all_dates[$date] = true;
            }
            $stmt->close();
        }
        
        $datasets[] = [
            'label' => $crime_type,
            'data' => $tally
        ];
    }
    
    // Sort dates
    $all_dates = array_keys($all_dates);
    sort($all_dates);
    
    echo json_encode([
        'labels' => $all_dates,
        'datasets' => $datasets,
        'multiLine' => true
    ]);
    
} else {
    // Single dataset for bar/pie/doughnut or line with all crimes
    $tally = [];
    
    function addToTally($mysqli, $sql, $types, $params, &$tally, $groupByDate = false) {
        $stmt = $mysqli->prepare($sql);
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $key = $groupByDate ? $row['date_label'] : $row['crime_type'];
            $count = $row['count'];
            
            if (!isset($tally[$key])) $tally[$key] = 0;
            $tally[$key] += $count;
        }
        $stmt->close();
    }
    
    $groupByDate = ($chart_type === 'line');
    
    if ($groupByDate) {
        $select_clause = "SELECT DATE(incident_datetime) as date_label, COUNT(*) as count";
        $group_clause  = "GROUP BY date_label ORDER BY date_label ASC";
    } else {
        $select_clause = "SELECT crime_type, COUNT(*) as count";
        $group_clause  = "GROUP BY crime_type";
    }
    
    // Fetch Incidents
    if ($dept_filter === '' || $dept_filter === 'APD' || $dept_filter === 'UAPD') {
        $sql = "$select_clause FROM incidents WHERE 1=1 ";
        $p = []; $t = '';
        
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
        if ($end_date !== '')    { $sql .= " AND incident_datetime <= ? "; $p[] = $end_date." 23:59:59"; $t .= 's'; }
        
        $sql .= " $group_clause";
        addToTally($mysqli, $sql, $t, $p, $tally, $groupByDate);
    }
    
    // Fetch Civilian
    if ($dept_filter === '' || $dept_filter === 'Civilian Observation') {
        $sql = "$select_clause FROM civObs WHERE 1=1 ";
        $p = []; $t = '';
        
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
        if ($end_date !== '')    { $sql .= " AND incident_datetime <= ? "; $p[] = $end_date." 23:59:59"; $t .= 's'; }
        
        $sql .= " $group_clause";
        addToTally($mysqli, $sql, $t, $p, $tally, $groupByDate);
    }
    
    if ($groupByDate) {
        ksort($tally);
    }
    
    $labels = array_keys($tally);
    $data = array_values($tally);
    
    echo json_encode([
        'labels' => $labels,
        'data' => $data,
        'multiLine' => false
    ]);
}
?>
