<?php
session_start();

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: index.php'); 
    exit();
}

require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';

$message = '';
$is_success = false;
$id = $_GET['id'] ?? $_POST['id'] ?? 0;
$source = $_GET['source'] ?? $_POST['source'] ?? 'official';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = update_record($mysqli, $id, $source, $_POST);
    
    if ($result === true) {
        $is_success = true;
        $message = "Record updated successfully!";
    } else {
        $message = $result;
    }
}
$record = get_record_by_id($mysqli, $id, $source);
if (!$record) {
    die("Record not found.");
}

function val($key, $record) {
    return htmlspecialchars($record[$key] ?? '');
}
?>

<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/1d22a0a09f.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <title>Edit Record</title>
</head>

<body> 
    <header class="global-header-bar">
        <h1 class="website-title">Edit Record</h1>
        <nav class="user-nav"><a href="index.php">Back to Dashboard</a></nav>
    </header>

    <div class="page-container">
        <main class="main-content" style="display: block;">
            <section class="card" style="max-width: 600px; margin: 20px auto;">
                <h2>Editing: <?php echo ($source === 'civilian') ? 'Civilian Obs' : 'Official Report'; ?> #<?php echo $id; ?></h2>
                
                <?php if ($message): ?>
                    <div style="padding: 10px; margin-bottom: 15px; background-color: <?php echo $is_success ? '#d4edda' : '#f8d7da'; ?>;">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <input type="hidden" name="source" value="<?php echo $source; ?>">

                    <?php if ($source !== 'civilian'): ?>
                        <label>Department:</label>
                        <select name="department" class="filter-select">
                            <option value="APD" <?php if (($record['department_code']??'') == 'APD') echo 'selected'; ?>>APD</option>
                            <option value="UAPD" <?php if (($record['department_code']??'') == 'UAPD') echo 'selected'; ?>>UAPD</option>
                        </select>

                        <label>Report Number:</label>
                        <input type="text" name="report_number" class="filter-input" value="<?php echo val('report_number', $record); ?>">
                    <?php endif; ?>

                    <label>Crime Type:</label>
                    <select name="crime_type" class="filter-select">
                        <?php 
                        $types = ['Assault','Aggravted Assault', 'Arson', 'Burglary','Domestic Incident', 'Homicide', 'Kidnapping', 'Motor Vehicle Theft', 'Rape', 'Robbery', 'Sexual Assault', 'Suspicious Activity', 'Sweetroll Stealing', 'Theft', 'Treason', 'Vandalism', 'Other'];
                        foreach($types as $t) {
                            $sel = ($record['crime_type'] == $t) ? 'selected' : '';
                            echo "<option value='$t' $sel>$t</option>";
                        }
                        ?>
                    </select>

                    <label>Date & Time:</label>
                    <?php 
                        $dt_value = date('Y-m-d\TH:i', strtotime($record['incident_datetime'])); 
                    ?>
                    <input type="datetime-local" name="incident_datetime" class="filter-input" value="<?php echo $dt_value; ?>">

                    <label>Location:</label>
                    <input type="text" name="location" class="filter-input" value="<?php echo val('location', $record); ?>">

                    <?php if ($source === 'civilian'): ?>
                        <label>Description:</label>
                        <textarea name="description" class="filter-input" rows="4"><?php echo val('description', $record); ?></textarea>
                    <?php endif; ?>

                    <button type="submit" class="primary-btn" style="margin-top: 20px;">Save Changes</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>