<?php
require_once 'db_connection.php'; // Ensure this file exists and correctly sets up $pdo

// Enable error reporting for debugging (only in development environment)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$report_type = '';
$date_from = date('Y-m-d', strtotime('-30 days'));
$date_to = date('Y-m-d');
$report_title = 'System Report';
$report_data = [];
$chart_data = ['labels' => [], 'values' => [], 'colors' => []];
$colors = ['#d32f2f', '#ff9800', '#4caf50', '#2196f3', '#9c27b0', '#795548'];
$color_index = 0;
$error_message = '';

// Check for required parameters
if (!isset($_GET['report'])) {
    $error_message = 'No report type provided. Please select a report type.';
} else {
    $report_type = htmlspecialchars($_GET['report']);
    
    // Process date parameters if they exist
    if (isset($_GET['date_from']) && strtotime($_GET['date_from'])) {
        $date_from = $_GET['date_from'];
    }
    
    if (isset($_GET['date_to']) && strtotime($_GET['date_to'])) {
        $date_to = $_GET['date_to'];
    }

    try {
        switch ($report_type) {
            case 'alerts_by_type':
                $report_title = 'Alerts by Type';
                $stmt = $pdo->prepare("SELECT type, COUNT(*) as count FROM alerts 
                                      WHERE timestamp BETWEEN ? AND ? 
                                      GROUP BY type ORDER BY count DESC");
                $stmt->execute([$date_from . ' 00:00:00', $date_to . ' 23:59:59']);
                break;

            case 'alerts_by_device':
                $report_title = 'Alerts by Device';
                $stmt = $pdo->prepare("SELECT d.name, d.type, COUNT(*) as count FROM alerts a 
                                      JOIN devices d ON a.device_id = d.id 
                                      WHERE a.timestamp BETWEEN ? AND ? 
                                      GROUP BY d.name ORDER BY count DESC");
                $stmt->execute([$date_from . ' 00:00:00', $date_to . ' 23:59:59']);
                break;

            case 'maintenance_by_device':
                $report_title = 'Maintenance by Device';
                $stmt = $pdo->prepare("SELECT d.name, d.type, d.next_maintenance_date FROM devices d 
                                      WHERE d.next_maintenance_date BETWEEN ? AND ? 
                                      ORDER BY d.next_maintenance_date ASC");
                $stmt->execute([$date_from, $date_to]);
                break;

            default:
                $error_message = 'Invalid report type selected.';
                break;
        }

        if (!isset($error_message) || empty($error_message)) {
            $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Prepare chart data if we have results
            if (!empty($report_data)) {
                foreach ($report_data as $row) {
                    $chart_data['labels'][] = htmlspecialchars($row['name'] ?? $row['type']);
                    $chart_data['values'][] = $row['count'] ?? $row['next_maintenance_date'];
                    $chart_data['colors'][] = $colors[$color_index % count($colors)];
                    $color_index++;
                }
            }
        }
    } catch (PDOException $e) {
        $error_message = 'Database Error: ' . htmlspecialchars($e->getMessage());
    }
}

// Save the query data for debugging if needed
$debug_info = [
    'report_type' => $report_type,
    'date_range' => "$date_from to $date_to",
    'data_count' => count($report_data)
];

// HTML Output starts here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $report_title; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .date-filter {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
    </style>
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
                    <li><a href="maintenance.php">Maintenance</a></li>
                    <li><a href="reports.php" class="active">Reports</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav> 
        </header>

        <main>
            <h1><?php echo $report_title; ?></h1>
            
            <!-- Date Filter Form -->
            <div class="date-filter">
                <form method="GET" action="">
                    <input type="hidden" name="report" value="<?php echo htmlspecialchars($report_type); ?>">
                    <label for="date_from">From:</label>
                    <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                    
                    <label for="date_to">To:</label>
                    <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                    
                    <button type="submit">Update Report</button>
                </form>
            </div>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert-error">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($report_data) && empty($error_message)): ?>
                <div class="alert-info">
                    <p>No data found for this report in the selected date range (<?php echo $date_from; ?> to <?php echo $date_to; ?>).</p>
                    <p>Try expanding your date range or check if data exists in the system.</p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($report_data)): ?>
                <img src="img/report.jpg" alt="Report Image" style="max-width: 100%; height: auto;">
                <table>
                    <tr>
                        <th>Label</th>
                        <th><?php echo ($report_type == 'maintenance_by_device') ? 'Next Maintenance Date' : 'Count'; ?></th>
                    </tr>
                    <?php foreach ($report_data as $index => $data): ?>
                        <tr>
                            <td><?php echo $chart_data['labels'][$index]; ?></td>
                            <td><?php echo $chart_data['values'][$index]; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
            
            <?php if (isset($_GET['debug']) && $_GET['debug'] == 1): ?>
                <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border: 1px solid #ddd;">
                    <h3>Debug Information</h3>
                    <pre><?php print_r($debug_info); ?></pre>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>