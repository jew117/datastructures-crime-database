<?php 



define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'crime_analytics_akron');

$mysqli = mysqli_connect(DB_HOST, DB_USER,DB_PASS,DB_NAME);

if(!$mysqli) {
    die('Database connection failed: ' . mysqli_connect_error()); 
} 

mysqli_set_charset($mysqli, 'utf8mb4');

?>