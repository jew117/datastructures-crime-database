<?php
session_start();

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    die("Access Denied: You must be an administrator to delete records.");
}

require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';

$id = $_GET['id'] ?? 0;
$source = $_GET['source'] ?? 'official';
$confirm = $_GET['confirm'] ?? false;

if ($confirm === 'yes') {
    if (delete_record($mysqli, $id, $source)) {
        header("Location: index.php");
        exit();
    } else {
        die("Error deleting record.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <title>Confirm Delete</title>
</head>
<body style="background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh;">

    <div class="card" style="max-width: 400px; text-align: center; padding: 40px;">
        <h2 style="color: #c0392b;">⚠️ Confirm Deletion</h2>
        <p>Are you sure you want to permanently delete this record?</p>
        <p><strong>ID:</strong> <?php echo htmlspecialchars($id); ?> (<?php echo htmlspecialchars($source); ?>)</p>
        
        <div style="margin-top: 30px;">
            <a href="index.php" class="table-btn" style="background-color: #95a5a6; margin-right: 10px;">Cancel</a>
            <a href="delete_record.php?id=<?php echo $id; ?>&source=<?php echo $source; ?>&confirm=yes" class="table-btn" style="background-color: #c0392b;">Yes, Delete</a>
        </div>
    </div>

</body>
</html>