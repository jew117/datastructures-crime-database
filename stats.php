<?php
include 'db.php';

// Default: show all time stats (if no date selected)
$start = $_GET['start'] ?? '2000-01-01';
$end   = $_GET['end'] ?? date('Y-m-d');

// Prepare stored procedure call
$stmt = $mysqli->prepare("CALL GetCrimeStats(?, ?)");
$stmt->bind_param("ss", $start, $end);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Crime Stats</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Crime Statistics (Using Stored Procedure)</h1>

<form method="GET" action="stats.php" style="margin-bottom:20px;">
    <label>Start Date:</label>
    <input type="date" name="start" value="<?php echo $start; ?>">

    <label>End Date:</label>
    <input type="date" name="end" value="<?php echo $end; ?>">

    <button type="submit" class="table-btn">Update Stats</button>
</form>

<p><i>Data generated using MySQL Stored Procedure: <b>GetCrimeStats()</b></i></p>

<table class="crime-table">
    <thead>
        <tr>
            <th>Crime Type</th>
            <th>Total Incidents</th>
        </tr>
    </thead>
    <tbody>

<?php
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['crime_type'] . "</td>";
    echo "<td>" . $row['total_incidents'] . "</td>";
    echo "</tr>";
}
?>

    </tbody>
</table>

</body>
</html>

<?php
$stmt->close();
$mysqli->close();
?>
