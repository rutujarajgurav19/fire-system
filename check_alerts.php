<?php
// Database connection
require_once 'db_connection.php';

// Query to fetch alerts within the specified date range
$stmt = $pdo->prepare("SELECT * FROM alerts WHERE alert_date BETWEEN '2025-02-21' AND '2025-03-23'");
$stmt->execute();
$alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Display results
if (count($alerts) > 0) {
    echo "<h1>Alerts Found:</h1>";
    foreach ($alerts as $alert) {
        echo "<p>Alert ID: " . htmlspecialchars($alert['id']) . " - Date: " . htmlspecialchars($alert['alert_date']) . "</p>";
    }
} else {
    echo "<h1>No alerts found for the specified date range.</h1>";
}
?>
