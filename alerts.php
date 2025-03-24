<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Include database connection
    require_once 'db_connection.php';
} catch (PDOException $e) {
    echo '<div class="alert-error">Database connection failed: ' . $e->getMessage() . '</div>';
    exit();
}

// Set default filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$device_filter = isset($_GET['device_type']) ? $_GET['device_type'] : '';
$location_filter = isset($_GET['location']) ? $_GET['location'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$resolved = isset($_GET['resolved']) ? $_GET['resolved'] : 'all';

// Build query based on filters
$query = "SELECT a.id, a.timestamp, a.type, a.message, a.status, a.resolved, 
          d.name as device_name, d.type as device_type, l.name as location_name 
          FROM alerts a 
          JOIN devices d ON a.device_id = d.id 
          JOIN locations l ON d.location_id = l.id 
          WHERE 1=1";

$params = [];

if ($status_filter) {
    $query .= " AND a.status = ?";
    $params[] = $status_filter;
}

if ($device_filter) {
    $query .= " AND d.type = ?";
    $params[] = $device_filter;
}

if ($location_filter) {
    $query .= " AND d.location_id = ?";
    $params[] = $location_filter;
}

if ($date_from) {
    $query .= " AND a.timestamp >= ?";
    $params[] = $date_from . ' 00:00:00';
}

if ($date_to) {
    $query .= " AND a.timestamp <= ?";
    $params[] = $date_to . ' 23:59:59';
}

if ($resolved != 'all') {
    $query .= " AND a.resolved = ?";
    $params[] = ($resolved == 'resolved') ? 1 : 0;
}

$query .= " ORDER BY a.timestamp DESC";

// Execute the query with parameters
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$alerts = $stmt->fetchAll(); // Fetch alerts from the database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fire System Management - Alerts</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Fire System Management</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="devices.php">Devices</a></li>
                    <li><a href="alerts.php" class="active">Alerts</a></li>
                    <li><a href="maintenance.php">Maintenance</a></li>
                    <li><a href="reports.php?report=alerts_by_type">Reports</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <section>
                <div class="section-header">
                    <h2>Alert Management</h2>
                    <div>
                        <a href="test_alert.php" class="btn">Test Alert</a>
                    </div>
                </div>
                
                <div class="filter-section">
                    <h3>Filter Alerts</h3>
                    <form action="" method="get">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="status">Alert Status:</label>
                                <select name="status" id="status">
                                    <option value="">All Statuses</option>
                                    <option value="info" <?php echo $status_filter == 'info' ? 'selected' : ''; ?>>Info</option>
                                    <option value="warning" <?php echo $status_filter == 'warning' ? 'selected' : ''; ?>>Warning</option>
                                    <option value="critical" <?php echo $status_filter == 'critical' ? 'selected' : ''; ?>>Critical</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="device_type">Device Type:</label>
                                <select name="device_type" id="device_type">
                                    <option value="">All Types</option>
                                    <?php foreach ($device_types as $type): ?>
                                    <option value="<?php echo $type['type']; ?>" <?php echo $device_filter == $type['type'] ? 'selected' : ''; ?>>
                                        <?php echo ucwords(str_replace('_', ' ', $type['type'])); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="location">Location:</label>
                                <select name="location" id="location">
                                    <option value="">All Locations</option>
                                    <?php foreach ($locations as $location): ?>
                                    <option value="<?php echo $location['id']; ?>" <?php echo $location_filter == $location['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($location['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="date_from">Date From:</label>
                                <input type="date" name="date_from" id="date_from" value="<?php echo $date_from; ?>">
                            </div>
                            <div class="form-group">
                                <label for="date_to">Date To:</label>
                                <input type="date" name="date_to" id="date_to" value="<?php echo $date_to; ?>">
                            </div>
                            <div class="form-group">
                                <label for="resolved">Resolution Status:</label>
                                <select name="resolved" id="resolved">
                                    <option value="all" <?php echo $resolved == 'all' ? 'selected' : ''; ?>>All</option>
                                    <option value="active" <?php echo $resolved == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="resolved" <?php echo $resolved == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn">Apply Filters</button>
                                <a href="alerts.php" class="btn-secondary">Clear Filters</a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="alerts-list">
                    <?php if (count($alerts) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Device</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Resolution</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alerts as $alert): ?>
                            <tr class="<?php echo $alert['status']; ?>">
                                <td><?php echo $alert['timestamp']; ?></td>
                                <td><?php echo htmlspecialchars($alert['device_name']); ?></td>
                                <td><?php echo htmlspecialchars($alert['location_name']); ?></td>
                                <td><?php echo htmlspecialchars($alert['type']); ?></td>
                                <td><?php echo htmlspecialchars($alert['message']); ?></td>
                                <td><?php echo ucfirst($alert['status']); ?></td>
                                <td><?php echo $alert['resolved'] ? 'Resolved' : 'Active'; ?></td>
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
                    <p>No alerts found matching your criteria.</p>
                    <?php endif; ?>
                </div>
                
                <div class="alert-summary">
                    <h3>Alert Summary</h3>
                    <div class="stats-container">
                        <?php
                        // Get counts for summary
                        $stmt = $pdo->query("SELECT COUNT(*) FROM alerts WHERE resolved = 0 AND status = 'critical'");
                        $critical_count = $stmt->fetchColumn();
                        
                        $stmt = $pdo->query("SELECT COUNT(*) FROM alerts WHERE resolved = 0 AND status = 'warning'");
                        $warning_count = $stmt->fetchColumn();
                        
                        $stmt = $pdo->query("SELECT COUNT(*) FROM alerts WHERE resolved = 0 AND status = 'info'");
                        $info_count = $stmt->fetchColumn();
                        
                        $stmt = $pdo->query("SELECT COUNT(*) FROM alerts WHERE resolved = 1");
                        $resolved_count = $stmt->fetchColumn();
                        ?>
                        
                        <div class="stat-box">
                            <h3>Critical Alerts</h3>
                            <p class="stat-number alert"><?php echo $critical_count; ?></p>
                        </div>
                        <div class="stat-box">
                            <h3>Warning Alerts</h3>
                            <p class="stat-number warning"><?php echo $warning_count; ?></p>
                        </div>
                        <div class="stat-box">
                            <h3>Info Alerts</h3>
                            <p class="stat-number"><?php echo $info_count; ?></p>
                        </div>
                        <div class="stat-box">
                            <h3>Resolved</h3>
                            <p class="stat-number operational"><?php echo $resolved_count; ?></p>
                        </div>
                    </div>
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
