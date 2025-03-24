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


// Fetch all devices
$stmt = $pdo->query("SELECT d.id, d.name, d.type, d.status, d.installation_date, d.last_maintenance_date, 
                     d.next_maintenance_date, l.name as location 
                     FROM devices d 
                     JOIN locations l ON d.location_id = l.id 
                     ORDER BY d.name ASC");
$devices = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fire System Management - Devices</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Fire System Management</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="devices.php" class="active">Devices</a></li>
                    <li><a href="alerts.php">Alerts</a></li>
                    <li><a href="maintenance.php">Maintenance</a></li>
                    <li><a href="reports.php?report=alerts_by_type">Reports</a></li>

                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <section>
                <div class="section-header">
                    <h2>All Devices</h2>
                    <a href="add_device.php" class="btn">Add New Device</a>
                </div>
                
                <div class="filter-section">
                    <form action="" method="get">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="filter-type">Filter by Type:</label>
                                <select name="type" id="filter-type">
                                    <option value="">All Types</option>
                                    <option value="smoke_detector">Smoke Detector</option>
                                    <option value="heat_sensor">Heat Sensor</option>
                                    <option value="sprinkler">Sprinkler</option>
                                    <option value="fire_alarm">Fire Alarm</option>
                                    <option value="extinguisher">Fire Extinguisher</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="filter-location">Filter by Location:</label>
                                <select name="location" id="filter-location">
                                    <option value="">All Locations</option>
                                    <!-- Locations would be populated dynamically -->
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="filter-status">Filter by Status:</label>
                                <select name="status" id="filter-status">
                                    <option value="">All Statuses</option>
                                    <option value="operational">Operational</option>
                                    <option value="warning">Warning</option>
                                    <option value="critical">Critical</option>
                                    <option value="maintenance">Under Maintenance</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn">Apply Filters</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="device-list">
                    <?php if (count($devices) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Device Name</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Last Maintenance</th>
                                <th>Next Maintenance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($devices as $device): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($device['name']); ?></td>
                                <td><?php echo ucwords(str_replace('_', ' ', $device['type'])); ?></td>
                                <td><?php echo htmlspecialchars($device['location']); ?></td>
                                <td class="<?php echo $device['status']; ?>"><?php echo ucfirst($device['status']); ?></td>
                                <td><?php echo $device['last_maintenance_date']; ?></td>
                                <td><?php 
                                    $next_date = new DateTime($device['next_maintenance_date']);
                                    $today = new DateTime('now');
                                    $date_class = ($next_date < $today) ? 'warning' : '';
                                    echo "<span class='$date_class'>" . $device['next_maintenance_date'] . "</span>";
                                ?></td>
                                <td>
                                    <a href="device_details.php?id=<?php echo $device['id']; ?>" class="btn-secondary">View</a>
                                    <a href="edit_device.php?id=<?php echo $device['id']; ?>" class="btn-secondary">Edit</a>
                                    <a href="device_history.php?id=<?php echo $device['id']; ?>" class="btn-secondary">History</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p>No devices found.</p>
                    <?php endif; ?>
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
