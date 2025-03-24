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
    die('Error: No alert ID provided.');
}

$alert_id = intval($_GET['id']);

// Fetch alert details
$stmt = $pdo->prepare("SELECT a.id, a.timestamp, a.type, a.message, a.status, a.resolved, 
                        d.name as device_name, d.type as device_type, l.name as location_name 
                        FROM alerts a 
                        JOIN devices d ON a.device_id = d.id 
                        JOIN locations l ON d.location_id = l.id 
                        WHERE a.id = ?");
$stmt->execute([$alert_id]);
$alert = $stmt->fetch();

if (!$alert) {
    die('Error: Alert not found.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert Details - <?php echo htmlspecialchars($alert['message']); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Alert Details</h1>
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
            <h2>Alert Information</h2>
            <p><strong>Date/Time:</strong> <?php echo htmlspecialchars($alert['timestamp']); ?></p>
            <p><strong>Device:</strong> <?php echo htmlspecialchars($alert['device_name']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($alert['location_name']); ?></p>
            <p><strong>Type:</strong> <?php echo htmlspecialchars($alert['type']); ?></p>
            <p><strong>Message:</strong> <?php echo htmlspecialchars($alert['message']); ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($alert['status']); ?></p>
            <p><strong>Resolution:</strong> <?php echo $alert['resolved'] ? 'Resolved' : 'Active'; ?></p>
        </main>
        
        <footer>
            <p>&copy; 2025 Fire System Management. All rights reserved.</p>
        </footer>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
