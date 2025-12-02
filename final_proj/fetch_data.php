<?php
session_start();

// Security check: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); 
    echo "<p style='color: red;'>Session expired. Please log in again.</p>";
    exit();
}

// Include database connection and helper functions
require __DIR__ . '/db.php';
require __DIR__ . '/functions.php'; 

// Is a user admin?
$is_admin = $_SESSION['is_admin'] ?? false;

// Collect filters
$ajax_filters = [
    'crime_type' => $_POST['crime_type'] ?? '',
    'department' => $_POST['department'] ?? '',
    'location'   => $_POST['search_term'] ?? '',
    'start_date' => $_POST['start_date'] ?? '',
    'end_date'   => $_POST['end_date'] ?? ''
];


$table_html = display_crime_data($mysqli, $ajax_filters, $is_admin);

echo $table_html;
?>