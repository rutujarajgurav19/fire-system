<?php
require_once 'db_connection.php'; // Ensure this file exists and correctly sets up $pdo

// Fetch all alerts data
$stmt = $pdo->query("SELECT * FROM alerts");
$alerts_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($alerts_data)) {
    echo "No alerts found in the database.";
} else {
    echo "Alerts found in the database:<br>";
    foreach ($alerts_data as $alert) {
        echo "ID: " . htmlspecialchars($alert['id']) . " - Type: " . htmlspecialchars($alert['type']) . " - Timestamp: " . htmlspecialchars($alert['timestamp']) . "<br>";
    }
}
?>
