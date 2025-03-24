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

// Fetch device history
$stmt = $pdo->prepare("SELECT h.timestamp, h.action, h.details 
                        FROM device_history h 
                        WHERE h.device_id = ? 
                        ORDER BY h.timestamp DESC");
$stmt->execute([$device_id]);
$history = $stmt->fetchAll();

if (!$history) {
    die('Error: No history found for this device.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device History - ID <?php echo htmlspecialchars($device_id); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Device History</h1>
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
            <h2>History for Device ID: <?php echo htmlspecialchars($device_id); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $entry): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($entry['timestamp']); ?></td>
                        <td><?php echo htmlspecialchars($entry['action']); ?></td>
                        <td><?php echo htmlspecialchars($entry['details']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
        
        <footer>
            <p>&copy; 2025 Fire System Management. All rights reserved.</p>
        </footer>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
