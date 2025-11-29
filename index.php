<?php
session_start(); 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit(); 
}


$is_admin = $_SESSION['is_admin'] ?? false; 
$username = $_SESSION['username'] ?? 'User'; 


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/db.php';
require __DIR__ . '/functions.php'; 


$current_filters = [
    'crime_type' => $_GET['crime_type'] ?? '',
    'department' => $_GET['department'] ?? '',
    'location'   => $_GET['search_term'] ?? '', 
    'is_closed'  => $_GET['is_closed'] ?? '',
];
?> 

<!DOCTYPE html>
<html lang="en">

<head> 
<meta charset="UTF-8"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=BBH+Sans+Bogle&family=Oswald:wght@200..700&family=Titillium+Web:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">
<script src="https://kit.fontawesome.com/1d22a0a09f.js" crossorigin="anonymous"></script>

<link rel="stylesheet" href="style.css">

<title>Akron Crime Database Dashboard</title>
</head>

<body> 
    <header class="global-header-bar">
        <h1 class="website-title">Akron Crime Database</h1>
        <div class="user-greeting">
            Hello, <?php echo htmlspecialchars($username); ?>
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
                    <li><a href="add_record.php"><i class="fas fa-plus-circle"></i> Add Record</a></li>
                </ul>
            </nav>

            <section class="filter-controls">
                <h3>Filter Records</h3>
                <form method="GET" action="index.php" class="filter-form">
                    
                    <label for="crime-type">Crime Type:</label>
                    <select id="crime-type" name="crime_type" class="filter-select">
                        <option value="">All Crimes</option>
                        <option value="Arson" <?php if (($current_filters['crime_type'] ?? '') === 'Arson') echo 'selected'; ?>>Arson</option>
                        <option value="Robbery" <?php if (($current_filters['crime_type'] ?? '') === 'Robbery') echo 'selected'; ?>>Robbery</option>
                        <option value="Sexual Assault" <?php if (($current_filters['crime_type'] ?? '') === 'Sexual Assault') echo 'selected'; ?>>Sexual Assault</option>
                        <option value="Larceny" <?php if (($current_filters['crime_type'] ?? '') === 'Larceny') echo 'selected'; ?>>Larceny</option>
                        <option value="Motor Vehicle Theft" <?php if (($current_filters['crime_type'] ?? '') === 'Motor Vehicle Theft') echo 'selected'; ?>>Motor Vehicle Theft</option>
                    </select>
                    
                    <label for="department">Department:</label>
                    <select id="department" name="department" class="filter-select">
                        <option value="">All Departments</option>
                        <option value="APD" <?php if (($current_filters['department'] ?? '') === 'APD') echo 'selected'; ?>>APD</option>
                        <option value="UAPD" <?php if (($current_filters['department'] ?? '') === 'UAPD') echo 'selected'; ?>>UAPD</option>
                        <option value="Civilian Observation" <?php if (($current_filters['department'] ?? '') === 'Civilian Observation') echo 'selected'; ?>>Civilian Observation</option>
                    </select>

                    <label for="location-search">Location Search:</label>
                    <input type="text" id="location-search" name="search_term" class="filter-input" placeholder="Search location..." 
                           value="<?php echo htmlspecialchars($current_filters['location'] ?? ''); ?>">
                    
                    <label for="is-closed">Is Closed?</label>
                    <select id="is-closed" name="is_closed" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="1" <?php if (($current_filters['is_closed'] ?? '') === '1') echo 'selected'; ?>>Yes</option>
                        <option value="0" <?php if (($current_filters['is_closed'] ?? '') === '0') echo 'selected'; ?>>No</option>
                    </select>
                    
                 
                    <button type="submit" class="primary-btn"><i class="fas fa-filter"></i> Apply Filters</button>
                </form>
            </section>
        </aside>

 
        <main class="main-content">
            
        
            <section class="graph-area card">
                <h2>Interactive Crime Graph</h2>
                <div id="crime-chart-container">
                    <p style="text-align: center; color: #7f8c8d;">I will someday be a beautiful interactive graph</p>
                </div>
            </section>
            
     
            <div class="table-manipulation-buttons">
                <?php if ($is_admin):?>
                <?php endif; ?>
            </div>

            <section class="data-table-area card">
                <h2>Selected Crime Records</h2>
                <div class="crime-table-wrapper">
                    <?php 
                    echo display_crime_data($mysqli, $current_filters, $is_admin); 
                    ?>
                </div>
            </section>

        </main>
    </div>

    <script src="app.js"></script>
</body>
</html>