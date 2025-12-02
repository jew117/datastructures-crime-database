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
    'crime_type' => isset($_GET['crime_type']) && is_array($_GET['crime_type']) ? $_GET['crime_type'] : [],
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
                    <!-- Filter: Crime Type with Dropdown Checkboxes -->
                    <label for="crime-type">Crime Type:</label>
                    <div class="multi-select-wrapper">
                        <button type="button" id="crime-type-dropdown" class="multi-select-button">
                            <span id="selected-crimes-display">All Crimes</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div id="crime-type-options" class="multi-select-dropdown" style="display: none;">
                            <div class="multi-select-search">
                                <input type="text" id="crime-search" placeholder="Search..." />
                            </div>
                            <div class="multi-select-options">
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="" id="all-crimes" checked>
                                    <span>All Crimes</span>
                                </label>
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="Assault">
                                    <span>Assault</span>
                                </label>
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="Aggravated Assault">
                                    <span>Aggravated Assault</span>
                                </label>
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="Arson">
                                    <span>Arson</span>
                                </label>
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="Burglary">
                                    <span>Burglary</span>
                                </label>
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="Domestic Incident">
                                    <span>Domestic Incident</span>
                                </label>
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="Kidnapping">
                                    <span>Kidnapping</span>
                                </label>
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="Motor Vehicle Theft">
                                    <span>Motor Vehicle Theft</span>
                                </label>
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="Rape">
                                    <span>Rape</span>
                                </label>
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="Robbery">
                                    <span>Robbery</span>
                                </label>
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="Sexual Assault">
                                    <span>Sexual Assault</span>
                                </label>
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="Suspicious Activity">
                                    <span>Suspicious Activity</span>
                                </label>
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="Sweetroll Stealing">
                                    <span>Sweetroll Stealing</span>
                                </label>
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="Stalking">
                                    <span>Stalking</span>
                                </label>
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="Theft">
                                    <span>Theft</span>
                                </label>
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="Treason">
                                    <span>Treason</span>
                                </label>
                                <label class="multi-select-option">
                                    <input type="checkbox" name="crime_type[]" value="Vandalism">
                                    <span>Vandalism</span>
                                </label>
                            </div>
                        </div>
                    </div>

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

     <!-- Graph and Table Data -->
        <main class="main-content">

         <!-- Interactive Crime Graph-->
            <section class="graph-area card">
                <!-- Flex container to align Title and Dropdown -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0;">Interactive Crime Graph</h2>

                    <!-- Chart Type Selector -->
                    <div class="chart-controls">
                        <select id="chartTypeSelector" class="filter-select" style="width: auto; padding: 8px 12px; font-size: 0.9em;">
                            <option value="bar">Bar Chart</option>
                            <option value="pie">Pie Chart</option>
                            <option value="doughnut">Doughnut Chart</option>
                            <option value="line">Line Chart</option>
                        </select>
                    </div>
                </div>

                <div id="crime-chart-container" style="position: relative; height: 450px; width: 100%; max-width: 100%; padding: 20px; box-sizing: border-box;">
                    <canvas id="crimeChart" style="max-width: 100%; max-height: 100%;"></canvas>
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

    <!-- Observation Modal -->
    <div id="observationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-eye"></i> Add Observation</h2>
                <span class="close-modal" onclick="closeObservationModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p class="modal-info">Record ID: <span id="modalRecordId"></span></p>

                <!-- View existing observations -->
                <div id="existingObservations" class="existing-observations">
                    <h3>Existing Observations:</h3>
                    <div id="observationsList" class="observations-list">
                        <p class="loading">Loading observations...</p>
                    </div>
                </div>

                <!-- Add new observation -->
                <div class="add-observation-section">
                    <h3>Add Your Observation:</h3>
                    <textarea id="observationText" class="observation-textarea" placeholder="Enter your observation or notes about this incident..." rows="4"></textarea>
                    <div class="modal-actions">
                        <button onclick="closeObservationModal()" class="btn-secondary">Cancel</button>
                        <button onclick="submitObservation()" class="btn-primary">
                            <i class="fas fa-save"></i> Save Observation
                        </button>
                    </div>
                </div>

                <div id="observationMessage" class="observation-message"></div>
            </div>
        </div>
    </div>

    <script>
        // Pass PHP session data to JavaScript
        const currentUserId = <?php echo $_SESSION['user_id']; ?>;
        const isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;
        console.log('Current User ID:', currentUserId, 'Is Admin:', isAdmin);
    </script>
    
    <script>
        // Multi-Select Dropdown Logic
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownButton = document.getElementById('crime-type-dropdown');
            const dropdownOptions = document.getElementById('crime-type-options');
            const allCrimesCheckbox = document.getElementById('all-crimes');
            const crimeCheckboxes = document.querySelectorAll('.multi-select-option input[type="checkbox"]:not(#all-crimes)');
            const selectedDisplay = document.getElementById('selected-crimes-display');
            const searchInput = document.getElementById('crime-search');
            
            // Toggle dropdown
            dropdownButton.addEventListener('click', function(e) {
                e.preventDefault();
                const isOpen = dropdownOptions.style.display === 'block';
                dropdownOptions.style.display = isOpen ? 'none' : 'block';
                dropdownButton.classList.toggle('active');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.multi-select-wrapper')) {
                    dropdownOptions.style.display = 'none';
                    dropdownButton.classList.remove('active');
                }
            });
            
            // Update display text
            function updateDisplay() {
                const checkedBoxes = Array.from(crimeCheckboxes).filter(cb => cb.checked);
                
                if (allCrimesCheckbox.checked || checkedBoxes.length === 0) {
                    selectedDisplay.textContent = 'All Crimes';
                } else if (checkedBoxes.length === 1) {
                    selectedDisplay.textContent = checkedBoxes[0].value;
                } else if (checkedBoxes.length === 2) {
                    selectedDisplay.textContent = checkedBoxes.map(cb => cb.value).join(', ');
                } else {
                    selectedDisplay.textContent = `${checkedBoxes.length} crimes selected`;
                }
            }
            
            // Handle "All Crimes" checkbox
            allCrimesCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    crimeCheckboxes.forEach(cb => cb.checked = false);
                }
                updateDisplay();
            });
            
            // Handle specific crime checkboxes
            crimeCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        allCrimesCheckbox.checked = false;
                    } else {
                        // If no checkboxes are checked, check "All Crimes"
                        const anyChecked = Array.from(crimeCheckboxes).some(cb => cb.checked);
                        if (!anyChecked) {
                            allCrimesCheckbox.checked = true;
                        }
                    }
                    updateDisplay();
                });
            });
            
            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const options = document.querySelectorAll('.multi-select-option');
                
                options.forEach((option, index) => {
                    // Skip the first option (All Crimes)
                    if (index === 0) return;
                    
                    const text = option.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        option.style.display = 'flex';
                    } else {
                        option.style.display = 'none';
                    }
                });
            });
        });
    </script>
    
    <script src="app.js"></script>
</body>
</html>