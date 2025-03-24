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


// Get devices that need maintenance (next_maintenance_date <= current date)
$stmt = $pdo->query("SELECT d.id, d.name, d.type, d.model, d.next_maintenance_date, 
                    l.name AS location_name, l.building, l.floor 
                    FROM devices d 
                    JOIN locations l ON d.location_id = l.id 
                    WHERE d.next_maintenance_date <= CURDATE() 
                    ORDER BY d.next_maintenance_date ASC");
$due_maintenance = $stmt->fetchAll();

// Get upcoming maintenance (next 30 days)
$stmt = $pdo->query("SELECT d.id, d.name, d.type, d.model, d.next_maintenance_date, 
                    l.name AS location_name, l.building, l.floor 
                    FROM devices d 
                    JOIN locations l ON d.location_id = l.id 
                    WHERE d.next_maintenance_date > CURDATE() 
                    AND d.next_maintenance_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
                    ORDER BY d.next_maintenance_date ASC");
$upcoming_maintenance = $stmt->fetchAll();

// Get recent maintenance records
$stmt = $pdo->query("SELECT m.id, m.maintenance_date, m.maintenance_type, m.description, 
                    d.name AS device_name, d.type AS device_type, 
                    u.full_name AS performed_by_name 
                    FROM maintenance_records m 
                    JOIN devices d ON m.device_id = d.id 
                    JOIN users u ON m.performed_by = u.id 
                    ORDER BY m.maintenance_date DESC 
                    LIMIT 10");
$recent_maintenance = $stmt->fetchAll();

// Process form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['log_maintenance'])) {
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Insert maintenance record
        $stmt = $pdo->prepare("INSERT INTO maintenance_records (device_id, maintenance_date, performed_by, 
                              maintenance_type, description, next_maintenance_date) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['device_id'],
            $_POST['maintenance_date'],
            $_SESSION['user_id'],
            $_POST['maintenance_type'],
            $_POST['description'],
            $_POST['next_maintenance_date']
        ]);
        
        // Update device's maintenance dates
        $stmt = $pdo->prepare("UPDATE devices SET 
                              last_maintenance_date = ?, 
                              next_maintenance_date = ? 
                              WHERE id = ?");
        $stmt->execute([
            $_POST['maintenance_date'],
            $_POST['next_maintenance_date'],
            $_POST['device_id']
        ]);
        
        // Commit transaction
        $pdo->commit();
        
        $message = '<div class="alert-success">Maintenance record successfully logged.</div>';
        
        // Redirect to refresh the page
        header("Location: maintenance.php?success=1");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $message = '<div class="alert-error">Error: ' . $e->getMessage() . '</div>';
    }
}

// Show success message on redirect
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = '<div class="alert-success">Maintenance record successfully logged.</div>';
}

// Get all devices for dropdown
$stmt = $pdo->query("SELECT d.id, d.name, l.name AS location 
                    FROM devices d 
                    JOIN locations l ON d.location_id = l.id 
                    ORDER BY d.name ASC");
$all_devices = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fire System Management - Maintenance</title>
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
                    <li><a href="alerts.php">Alerts</a></li>
                    <li><a href="maintenance.php" class="active">Maintenance</a></li>
                    <li><a href="reports.php?report=alerts_by_type">Reports</a></li>

                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <?php if ($message) echo $message; ?>
            
            <section>
                <div class="section-header">
                    <h2>Maintenance Management</h2>
                </div>
            
                    <!-- Due Maintenance Tab -->
                    <div class="tab-content active" id="due-tab">
                        <h3>Maintenance Due <span class="count">(<?php echo count($due_maintenance); ?>)</span></h3>
                        <?php if (count($due_maintenance) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Device Name</th>
                                    <th>Type</th>
                                    <th>Model</th>
                                    <th>Location</th>
                                    <th>Due Date</th>
                                    <th>Days Overdue</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($due_maintenance as $device): 
                                    $due_date = new DateTime($device['next_maintenance_date']);
                                    $today = new DateTime('now');
                                    $days_overdue = $today->diff($due_date)->format("%r%a");
                                ?>
                                <tr class="<?php echo $days_overdue <= -30 ? 'critical' : 'warning'; ?>">
                                    <td><?php echo htmlspecialchars($device['name']); ?></td>
                                    <td><?php echo ucwords(str_replace('_', ' ', $device['type'])); ?></td>
                                    <td><?php echo htmlspecialchars($device['model']); ?></td>
                                    <td><?php echo htmlspecialchars($device['location_name']) . ' (' . 
                                               htmlspecialchars($device['building']) . ', Floor ' . 
                                               htmlspecialchars($device['floor']) . ')'; ?></td>
                                    <td><?php echo $device['next_maintenance_date']; ?></td>
                                    <td><?php echo abs($days_overdue); ?> days</td>
                                    <td>
                                        <a href="#" class="btn log-btn" data-id="<?php echo $device['id']; ?>" 
                                           data-name="<?php echo htmlspecialchars($device['name']); ?>">Log Maintenance</a>
                                        <a href="device_details.php?id=<?php echo $device['id']; ?>" class="btn-secondary">View Device</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p>No maintenance due at this time.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Upcoming Maintenance Tab -->
                    <div class="tab-content" id="upcoming-tab">
                        <h3>Upcoming Maintenance <span class="count">(<?php echo count($upcoming_maintenance); ?>)</span></h3>
                        <?php if (count($upcoming_maintenance) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Device Name</th>
                                    <th>Type</th>
                                    <th>Model</th>
                                    <th>Location</th>
                                    <th>Due Date</th>
                                    <th>Days Until Due</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcoming_maintenance as $device): 
                                    $due_date = new DateTime($device['next_maintenance_date']);
                                    $today = new DateTime('now');
                                    $days_until = $today->diff($due_date)->days;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($device['name']); ?></td>
                                    <td><?php echo ucwords(str_replace('_', ' ', $device['type'])); ?></td>
                                    <td><?php echo htmlspecialchars($device['model']); ?></td>
                                    <td><?php echo htmlspecialchars($device['location_name']) . ' (' . 
                                               htmlspecialchars($device['building']) . ', Floor ' . 
                                               htmlspecialchars($device['floor']) . ')'; ?></td>
                                    <td><?php echo $device['next_maintenance_date']; ?></td>
                                    <td><?php echo $days_until; ?> days</td>
                                    <td>
                                        <a href="#" class="btn log-btn" data-id="<?php echo $device['id']; ?>" 
                                           data-name="<?php echo htmlspecialchars($device['name']); ?>">Log Maintenance</a>
                                        <a href="device_details.php?id=<?php echo $device['id']; ?>" class="btn-secondary">View Device</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p>No upcoming maintenance in the next 30 days.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Maintenance History Tab -->
                    <div class="tab-content" id="history-tab">
                        <h3>Recent Maintenance History</h3>
                        <?php if (count($recent_maintenance) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Device</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Performed By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_maintenance as $record): ?>
                                <tr>
                                    <td><?php echo $record['maintenance_date']; ?></td>
                                    <td><?php echo htmlspecialchars($record['device_name']); ?></td>
                                    <td><?php echo ucfirst($record['maintenance_type']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($record['description'], 0, 50)) . 
                                               (strlen($record['description']) > 50 ? '...' : ''); ?></td>
                                    <td><?php echo htmlspecialchars($record['performed_by_name']); ?></td>
                                    <td>
                                        <a href="maintenance_details.php?id=<?php echo $record['id']; ?>" class="btn-secondary">View Details</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p><a href="maintenance_history.php" class="btn">View Full History</a></p>
                        <?php else: ?>
                        <p>No maintenance records found.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Log Maintenance Tab -->
                    <div class="tab-content" id="log-tab">
                        <h3>Log New Maintenance</h3>
                        <form action="" method="post" class="form">
                            <div class="form-group">
                                <label for="device_id">Device:</label>
                                <select name="device_id" id="device_id" required>
                                    <option value="">Select Device</option>
                                    <?php foreach ($all_devices as $device): ?>
                                    <option value="<?php echo $device['id']; ?>">
                                        <?php echo htmlspecialchars($device['name']) . ' (' . htmlspecialchars($device['location']) . ')'; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="maintenance_date">Maintenance Date:</label>
                                <input type="date" name="maintenance_date" id="maintenance_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="maintenance_type">Maintenance Type:</label>
                                <select name="maintenance_type" id="maintenance_type" required>
                                    <option value="routine">Routine</option>
                                    <option value="repair">Repair</option>
                                    <option value="inspection">Inspection</option>
                                    <option value="replacement">Replacement</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description:</label>
                                <textarea name="description" id="description" rows="4" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="next_maintenance_date">Next Maintenance Date:</label>
                                <input type="date" name="next_maintenance_date" id="next_maintenance_date" 
                                       value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>" required>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="log_maintenance" class="btn">Log Maintenance</button>
                                <button type="reset" class="btn-secondary">Reset</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>
        
        <footer>
            <p>&copy; 2025 Fire System Management. All rights reserved.</p>
        </footer>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab functionality
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Add active class to clicked button and corresponding content
                button.classList.add('active');
                const tabId = button.getAttribute('data-tab');
                document.getElementById(tabId + '-tab').classList.add('active');
            });
        });
        
        // Log maintenance button functionality
        const logButtons = document.querySelectorAll('.log-btn');
        
        logButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Set the device in the form
                const deviceId = button.getAttribute('data-id');
                const deviceSelect = document.getElementById('device_id');
                deviceSelect.value = deviceId;
                
                // Switch to log tab
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                document.querySelector('[data-tab="log"]').classList.add('active');
                document.getElementById('log-tab').classList.add('active');
                
                // Scroll to form
                document.getElementById('log-tab').scrollIntoView({behavior: 'smooth'});
            });
        });
    });
    </script>
</body>
</html>
