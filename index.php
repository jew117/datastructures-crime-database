<?php
session_start(); 
//If you're not logged in...this makes sure you do
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit(); 
}

// Check if Admin 
$is_admin = $_SESSION['is_admin'] ?? false; 
$username = $_SESSION['username'] ?? 'User'; 


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//To Include
require __DIR__ . '/db.php';
require __DIR__ . '/functions.php'; 


$current_filters = [
    'crime_type' => $_GET['crime_type'] ?? '',
    'department' => $_GET['department'] ?? '',
    'location'   => $_GET['search_term'] ?? '', 
];
?> 

<!DOCTYPE html>
<html lang="en">

<head> 
    <!-- The wonderful stuff and things that make this page great--> 
<meta charset="UTF-8"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=BBH+Sans+Bogle&family=Oswald:wght@200..700&family=Titillium+Web:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">
<script src="https://kit.fontawesome.com/1d22a0a09f.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<link rel="stylesheet" href="style.css">

<title>Akron Crime Database Dashboard</title>
</head>

<body> 
    <!-- Top Header Bar --> 
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

        <!-- The Sidebar: Filtesr and Other Magical Things --> 
       <aside class="sidebar">
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                    <!-- Add a record button -- refrences add_record --> 
                    <li><a href="add_record.php"><i class="fas fa-plus-circle"></i> Add Record</a></li>
                </ul>
            </nav>

            <section class="filter-controls">
                <h3>Filter Records</h3>
                <form method="GET" action="index.php" class="filter-form">
                    <!-- Filter: Crime Type--> 
                    <label for="crime-type">Crime Type:</label>
                    <select id="crime-type" name="crime_type" class="filter-select">
                        <option value="">All Crimes</option>
                        <option value="Assault" <?php if (($current_filters['crime_type'] ?? '') === 'Assault') echo 'selected'; ?>>Assault</option>
                        <option value="Aggravated Assault" <?php if (($current_filters['crime_type'] ?? '') === 'Aggravated Assault') echo 'selected'; ?>>Aggravated Assault</option>
                        <option value="Arson" <?php if (($current_filters['crime_type'] ?? '') === 'Arson') echo 'selected'; ?>>Arson</option>
                        <option value="Burglary" <?php if (($current_filters['crime_type'] ?? '') === 'Burglary') echo 'selected'; ?>>Burglary</option>
                        <option value="Domestic Incident" <?php if (($current_filters['crime_type'] ?? '') === 'Domestic Incident') echo 'selected'; ?>>Domestic Incident</option>
                        <option value="Kidnapping" <?php if (($current_filters['crime_type'] ?? '') === 'Kidnapping') echo 'selected'; ?>>Kidnapping</option>
                        <option value="Motor Vehicle Theft" <?php if (($current_filters['crime_type'] ?? '') === 'Motor Vehicle Theft') echo 'selected'; ?>>Motor Vehicle Theft</option>
                        <option value="Rape" <?php if (($current_filters['crime_type'] ?? '') === 'Rape') echo 'selected'; ?>>Rape</option>
                        <option value="Robbery" <?php if (($current_filters['crime_type'] ?? '') === 'Robbery') echo 'selected'; ?>>Robbery</option>
                        <option value="Sexual Assault" <?php if (($current_filters['crime_type'] ?? '') === 'Sexual Assault') echo 'selected'; ?>>Sexual Assault</option>
                        <option value="Suspicious Activity" <?php if (($current_filters['crime_type'] ?? '') === 'Suspicious Activity') echo 'selected'; ?>>Suspicious Activity</option>
                        <option value="Sweetroll Stealing" <?php if (($current_filters['crime_type'] ?? '') === 'Sweetroll Stealing') echo 'selected'; ?>>Sweetroll Stealing</option>
                        <option value="Stalking" <?php if (($current_filters['crime_type'] ?? '') === 'Stalking') echo 'selected'; ?>>Stalking</option>
                        <option value="Theft" <?php if (($current_filters['crime_type'] ?? '') === 'Theft') echo 'selected'; ?>>Theft</option>
                        <option value="Treason" <?php if (($current_filters['crime_type'] ?? '') === 'Treason') echo 'selected'; ?>>Treason</option>
                        <option value="Vandalism" <?php if (($current_filters['crime_type'] ?? '') === 'Vandalism') echo 'selected'; ?>>Vandalism</option>
                         
                       
                      
                    </select>

                    <!-- Filter: Department --> 
                    <label for="department">Department:</label>
                    <select id="department" name="department" class="filter-select">
                        <option value="">All Departments</option>
                        <option value="APD" <?php if (($current_filters['department'] ?? '') === 'APD') echo 'selected'; ?>>APD</option>
                        <option value="UAPD" <?php if (($current_filters['department'] ?? '') === 'UAPD') echo 'selected'; ?>>UAPD</option>
                        <option value="Civilian Observation" <?php if (($current_filters['department'] ?? '') === 'Civilian Observation') echo 'selected'; ?>>Civilian Observation</option>
                    </select>

                    <!-- Filter: Location --> 
                    <label for="location-search">Location Search:</label>
                    <input type="text" id="location-search" name="search_term" class="filter-input" placeholder="Search location..." 
                           value="<?php echo htmlspecialchars($current_filters['location'] ?? ''); ?>">

                    <!-- Filter: Date Range --> 
                    <div class="date-filter-section">
                    <label>Date Range:</label>
                    <div class="date-input-group">
                        <input type="date" name="start_date" class="filter-input" placeholder="Start Date">
                    </div>
                    
                    <div class="date-input-group">
                        <span class="date-label">To:</span>
                        <input type="date" name="end_date" class="filter-input" placeholder="End Date">
                    </div>
                </div>
                     <!-- Button that uses a GET to submit the filters, which refreshes the page --> 
                    <button type="submit" class="primary-btn"><i class="fas fa-filter"></i> Apply Filters</button>
                </form>
            </section>
        </aside>

     <!-- Grpah and Table Data --> 
        <main class="main-content">
            
         <!-- ye olde interactive graph--> 
            <section class="graph-area card">
                <!-- Flex container to align Title and Dropdown -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h2 style="margin: 0;">Interactive Crime Graph</h2>
                    
                    <!-- NEW: Chart Type Selector -->
                    <div class="chart-controls">
                        <select id="chartTypeSelector" class="filter-select" style="width: auto; padding: 5px; font-size: 0.9em;">
                            <option value="bar">Bar Chart</option>
                            <option value="pie">Pie Chart</option>
                            <option value="doughnut">Doughnut Chart</option>
                            <option value="line">Line Chart</option>
                        </select>
                    </div>
                </div>
                
                <div id="crime-chart-container" style="position: relative; height: 300px; width: 100%;">
                    <canvas id="crimeChart"></canvas>
                </div>
            </section>
            
            <!-- I might not use these --> 
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