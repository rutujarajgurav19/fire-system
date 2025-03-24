<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'db_connection.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Error: No device ID provided.');
}

$device_id = intval($_GET['id']);

// Fetch device details
$stmt = $pdo->prepare("SELECT d.id, d.name, d.type, d.status, d.installation_date, d.last_maintenance_date, 
                        d.next_maintenance_date, l.name as location 
                        FROM devices d 
                        JOIN locations l ON d.location_id = l.id 
                        WHERE d.id = ?");
$stmt->execute([$device_id]);
$device = $stmt->fetch();

if (!$device) {
    die('Error: Device not found.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Details - <?php echo htmlspecialchars($device['name']); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Device Details</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="devices.php">Devices</a></li>
                    <li><a href="alerts.php">Alerts</a></li>
                    <li><a href="maintenance.php">Maintenance</a></li>
                    <li><a href="reports.php?report=alerts_by_type">Reports</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <h2><?php echo htmlspecialchars($device['name']); ?></h2>
            <p><strong>Type:</strong> <?php echo htmlspecialchars($device['type']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($device['status']); ?></p>
            <p><strong>Installation Date:</strong> <?php echo htmlspecialchars($device['installation_date']); ?></p>
            <p><strong>Last Maintenance Date:</strong> <?php echo htmlspecialchars($device['last_maintenance_date']); ?></p>
            <p><strong>Next Maintenance Date:</strong> <?php echo htmlspecialchars($device['next_maintenance_date']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($device['location']); ?></p>
        </main>
        
        <footer>
            <p>&copy; 2025 Fire System Management. All rights reserved.</p>
        </footer>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
