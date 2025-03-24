<?php
require_once 'db_connection.php'; // Ensure this file exists and correctly sets up $pdo

// Define the date range
$date_from = date('Y-m-d', strtotime('-30 days'));
$date_to = date('Y-m-d');

// Fetch alerts data for the specified date range
$stmt = $pdo->prepare("SELECT * FROM alerts WHERE timestamp BETWEEN ? AND ?");
$stmt->execute([$date_from . ' 00:00:00', $date_to . ' 23:59:59']);
$alerts_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($alerts_data)) {
    echo "No alerts found for the date range: {$date_from} to {$date_to}.";
} else {
    echo "Alerts found for the date range: {$date_from} to {$date_to}:<br>";
    foreach ($alerts_data as $alert) {
        echo "ID: " . htmlspecialchars($alert['id']) . " - Type: " . htmlspecialchars($alert['type']) . " - Timestamp: " . htmlspecialchars($alert['timestamp']) . "<br>";
    }
}
?>
