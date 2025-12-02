<?php
// Turn on error reporting to see what happens
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/db.php';

// The specific CSV file we organized earlier
$csvFile = 'akronpd_data.csv';

if (!file_exists($csvFile)) {
    die("Error: File '$csvFile' not found. Please make sure the CSV file is in the same folder.");
}

echo "<h1>Starting Data Import...</h1>";

// Open the file
if (($handle = fopen($csvFile, "r")) !== FALSE) {
    
    // 1. Skip the Header Row (Agency, Report, Date, Type, Location)
    fgetcsv($handle, 1000, ","); 

    $row_count = 0;
    $success_count = 0;
    $skip_count = 0;

    // Prepare the SQL statement
    // We use INSERT IGNORE to skip duplicates if the Report Number already exists
    // We do NOT include 'is_closed' as we removed that column
    $sql = "INSERT IGNORE INTO incidents 
            (department_code, report_number, incident_datetime, reported_datetime, crime_type, location) 
            VALUES (?, ?, ?, ?, ?, ?)";
            
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        die("SQL Prepare Failed: " . $mysqli->error);
    }

    // 2. Loop through every row in the CSV
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $row_count++;

        // Map CSV columns to variables based on your structure:
        // Agency[0], Report[1], Date[2], Type[3], Location[4]
        
        // Ensure row has enough columns
        if (count($data) < 5) {
            continue; 
        }

        $dept_code = trim($data[0]); 
        $report_num = trim($data[1]);
        $raw_date = trim($data[2]);
        $crime_type = trim($data[3]);
        $location = trim($data[4]);

        // 3. Validation & Cleaning
        
        // Skip empty rows or rows without a report number
        if (empty($report_num) || empty($dept_code)) {
            echo "<p style='color:orange'>Skipping Row $row_count: Missing Report Number or Department</p>";
            $skip_count++;
            continue;
        }

        // Format Date: Convert "01/01/2025 00:01" to "2025-01-01 00:01:00"
        $formatted_date = null;
        if (!empty($raw_date)) {
            // Try standard format found in your CSV (MM/DD/YYYY HH:MM)
            $dt = DateTime::createFromFormat('m/d/Y H:i', $raw_date);
            if ($dt) {
                $formatted_date = $dt->format('Y-m-d H:i:s');
            } else {
                // Try date only if time is missing
                $dt = DateTime::createFromFormat('m/d/Y', $raw_date);
                if ($dt) {
                    $formatted_date = $dt->format('Y-m-d 00:00:00');
                } else {
                    // Try YYYY-MM-DD format just in case
                    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $raw_date);
                    if($dt) $formatted_date = $dt->format('Y-m-d H:i:s');
                }
            }
        }

        // If date is completely missing/invalid, we cannot insert into a NOT NULL column
        if (!$formatted_date) {
            echo "<p style='color:red'>Skipping Row $row_count: Invalid Date format ($raw_date)</p>";
            $skip_count++;
            continue;
        }

        // 4. Bind and Execute
        // We use $formatted_date for BOTH incident and reported time for simplicity
        $stmt->bind_param("ssssss", $dept_code, $report_num, $formatted_date, $formatted_date, $crime_type, $location);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $success_count++;
            } else {
                echo "<p style='color:gray'>Row $row_count: Duplicate skipped ($report_num)</p>";
                $skip_count++;
            }
        } else {
            echo "<p style='color:red'>Error on Row $row_count: " . $stmt->error . "</p>";
        }
    }

    fclose($handle);
    $stmt->close();

    echo "<hr>";
    echo "<h2>Import Complete</h2>";
    echo "<ul>";
    echo "<li>Total Rows Read: $row_count</li>";
    echo "<li>Successfully Inserted: <strong>$success_count</strong></li>";
    echo "<li>Skipped (Duplicates/Errors): $skip_count</li>";
    echo "</ul>";
    echo "<a href='index.php'>Go to Dashboard</a>";

} else {
    echo "Could not open the CSV file: $csvFile";
}
?>