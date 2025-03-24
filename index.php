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


// Get count of total devices
$stmt = $pdo->query("SELECT COUNT(*) FROM devices");
$total_devices = $stmt->fetchColumn();

// Get count of active alerts
$stmt = $pdo->query("SELECT COUNT(*) FROM alerts WHERE status IN ('critical', 'warning') AND resolved = 0");
$active_alerts = $stmt->fetchColumn();

// Get count of maintenance due
$stmt = $pdo->query("SELECT COUNT(*) FROM devices WHERE next_maintenance_date <= CURDATE()");
$maintenance_due = $stmt->fetchColumn();

// Get system status
$system_status = "Operational";
if ($active_alerts > 5) {
    $system_status = "Critical";
} else if ($active_alerts > 0) {
    $system_status = "Warning";
}

// Get recent alerts
$stmt = $pdo->query("SELECT a.id, a.timestamp, a.type, a.status, a.resolved, d.name as device_name, l.name as location_name 
                     FROM alerts a 
                     JOIN devices d ON a.device_id = d.id 
                     JOIN locations l ON d.location_id = l.id 
                     ORDER BY a.timestamp DESC 
                     LIMIT 5");
$recent_alerts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fire System Management - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Fire System Management</h1>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Dashboard</a></li>
                    <li><a href="devices.php">Devices</a></li>
                    <li><a href="alerts.php">Alerts</a></li>
                    <li><a href="maintenance.php">Maintenance</a></li>
                    <li><a href="reports.php?report=alerts_by_type">Reports</a></li>

                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <section class="dashboard">
                <h2>System Overview</h2>
                <div class="stats-container">
                    <div class="stat-box">
                        <h3>Total Devices</h3>
                        <p class="stat-number"><?php echo $total_devices; ?></p>
                    </div>
                    <div class="stat-box">
                        <h3>Active Alerts</h3>
                        <p class="stat-number <?php echo $active_alerts > 0 ? 'alert' : ''; ?>"><?php echo $active_alerts; ?></p>
                    </div>
                    <div class="stat-box">
                        <h3>Maintenance Due</h3>
                        <p class="stat-number <?php echo $maintenance_due > 0 ? 'warning' : ''; ?>"><?php echo $maintenance_due; ?></p>
                    </div>
                    <div class="stat-box">
                        <h3>System Status</h3>
                        <p class="stat-text <?php echo strtolower($system_status); ?>"><?php echo $system_status; ?></p>
                    </div>
                </div>
                
                <div class="recent-alerts">
                    <h3>Recent Alerts</h3>
                    <?php if (count($recent_alerts) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Device</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_alerts as $alert): ?>
                            <tr class="<?php echo $alert['status']; ?>">
                                <td><?php echo $alert['timestamp']; ?></td>
                                <td><?php echo htmlspecialchars($alert['device_name']); ?></td>
                                <td><?php echo htmlspecialchars($alert['location_name']); ?></td>
                                <td><?php echo htmlspecialchars($alert['type']); ?></td>
                                <td><?php echo ucfirst($alert['status']); ?></td>
                                <td>
                                    <a href="alert_details.php?id=<?php echo $alert['id']; ?>" class="btn-secondary">View</a>
                                    <?php if ($alert['resolved'] == 0): ?>
                                    <a href="resolve_alert.php?id=<?php echo $alert['id']; ?>" class="btn">Resolve</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p>No recent alerts found.</p>
                    <?php endif; ?>
                    <p><a href="alerts.php" class="btn">View All Alerts</a></p>
                </div>
            </section>
        </main>
        
        <footer>
            <p>&copy; 2025 Fire System Management. All rights reserved.</p>
        </footer>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
