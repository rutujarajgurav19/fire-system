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

// Fetch alerts for testing (this can be customized as needed)
$stmt = $pdo->query("SELECT * FROM alerts ORDER BY timestamp DESC LIMIT 10");
$alerts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Alerts</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Test Alerts</h1>
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
            <h2>Recent Alerts</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($alerts) > 0): ?>
                        <?php foreach ($alerts as $alert): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($alert['timestamp']); ?></td>
                            <td><?php echo htmlspecialchars($alert['type']); ?></td>
                            <td><?php echo htmlspecialchars($alert['message']); ?></td>
                            <td><?php echo htmlspecialchars($alert['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No alerts found.</td>
                        </tr>
                    <?php endif; ?>
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
