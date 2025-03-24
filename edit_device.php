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

// Fetch device details for editing
$stmt = $pdo->prepare("SELECT * FROM devices WHERE id = ?");
$stmt->execute([$device_id]);
$device = $stmt->fetch();

if (!$device) {
    die('Error: Device not found.');
}

// Handle form submission for updating device details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $location_id = $_POST['location_id'];

    // Update device details in the database
    $stmt = $pdo->prepare("UPDATE devices SET name = ?, type = ?, location_id = ? WHERE id = ?");
    $stmt->execute([$name, $type, $location_id, $device_id]);

    // Redirect back to devices page with a success message
    header("Location: devices.php?message=Device updated successfully.");
    exit();
}

// Get locations for dropdown
$stmt = $pdo->query("SELECT id, name FROM locations ORDER BY name");
$locations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Device - <?php echo htmlspecialchars($device['name']); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Edit Device</h1>
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
            <h2>Edit Device Details</h2>
            <form action="" method="post">
                <div class="form-group">
                    <label for="name">Device Name:</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($device['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="type">Device Type:</label>
                    <input type="text" name="type" id="type" value="<?php echo htmlspecialchars($device['type']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="location_id">Location:</label>
                    <select name="location_id" id="location_id" required>
                        <?php foreach ($locations as $location): ?>
                        <option value="<?php echo $location['id']; ?>" <?php echo $device['location_id'] == $location['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($location['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn">Update Device</button>
            </form>
        </main>
        
        <footer>
            <p>&copy; 2025 Fire System Management. All rights reserved.</p>
        </footer>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
