<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';

$message = '';
$is_success = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = add_record($mysqli, $_POST);
    
    if ($result === true) {
        $is_success = true;
        $message = "Record added successfully!";
    } else {
        $message = $result; 
    }
}

$username = $_SESSION['username'] ?? 'User';
$is_admin = $_SESSION['is_admin'] ?? false;
?>

<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=BBH+Sans+Bogle&family=Oswald:wght@200..700&family=Titillium+Web:wght@200;400;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/1d22a0a09f.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <title>Add Record - Akron Crime Database</title>
</head>

<body> 

    <header class="global-header-bar">
        <h1 class="website-title">Akron Crime Database</h1>
        <div class="user-greeting">
            Hello, **<?php echo htmlspecialchars($username); ?>**
        </div>
        <nav class="user-nav">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </header>

    <div class="page-container">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                    <li><a href="add_record.php" class="active"><i class="fas fa-plus-circle"></i> Add Record</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <section class="card" style="max-width: 600px; margin: 0 auto;">
                <h2><i class="fas fa-file-medical"></i> Add New Record</h2>
                
                <?php if ($message): ?>
                    <div style="padding: 10px; margin-bottom: 15px; border-radius: 4px; 
                        background-color: <?php echo $is_success ? '#d4edda' : '#f8d7da'; ?>; 
                        color: <?php echo $is_success ? '#155724' : '#721c24'; ?>;">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="add_record.php">

                    <label for="department" style="display:block; margin-top:10px;">Department / Source:</label>
                    <select id="department" name="department" class="filter-select" required onchange="toggleFields()">
                        <?php if ($is_admin): ?>
                            <option value="">-- Select Department --</option>
                            <option value="APD">Akron Police Dept (APD)</option>
                            <option value="UAPD">University Police (UAPD)</option>
                            <option value="Civilian Observation">Civilian Observation</option>
                        <?php else: ?>

                            <option value="Civilian Observation" selected>Civilian Observation</option>
                        <?php endif; ?>
                    </select>

                    <div id="report-group">
                        <label for="report_number" style="display:block; margin-top:10px;">Report Number:</label>
                        <input type="text" id="report_number" name="report_number" class="filter-input" placeholder="e.g. 2025-01-001">
                    </div>

                    <label for="crime_type" style="display:block; margin-top:10px;">Crime Type:</label>
                    <select id="crime_type" name="crime_type" class="filter-select" required>
                        <option value="Theft">Theft</option>
                        <option value="Assault">Assault</option>
                        <option value="Robbery">Robbery</option>
                        <option value="Vandalism">Vandalism</option>
                        <option value="Suspicious Activity">Suspicious Activity</option>
                        <option value="Other">Other</option>
                    </select>

                    <label for="incident_datetime" style="display:block; margin-top:10px;">Date & Time:</label>
                    <input type="datetime-local" id="incident_datetime" name="incident_datetime" class="filter-input" required>

                    <label for="location" style="display:block; margin-top:10px;">Location / Address:</label>
                    <input type="text" id="location" name="location" class="filter-input" placeholder="e.g. 123 Main St" required>

                    <div id="desc-group" style="display:none;">
                        <label for="description" style="display:block; margin-top:10px;">Description / Notes:</label>
                        <textarea id="description" name="description" class="filter-input" rows="4" placeholder="Describe what you saw..."></textarea>
                    </div>

                    <button type="submit" class="primary-btn" style="margin-top: 20px;">Submit Record</button>
                </form>
            </section>
        </main>
    </div>

    <script>
        function toggleFields() {
            const dept = document.getElementById('department').value;
            const reportGroup = document.getElementById('report-group');
            const descGroup = document.getElementById('desc-group');
            const reportInput = document.getElementById('report_number');

            if (dept === 'Civilian Observation') {
        
                descGroup.style.display = 'block';
                reportGroup.style.display = 'none';
                reportInput.required = false; 
            } else {
           
                descGroup.style.display = 'none';
                reportGroup.style.display = 'block';
                reportInput.required = true; 
            }
        }
        
        window.onload = toggleFields;
    </script>
</body>
</html>