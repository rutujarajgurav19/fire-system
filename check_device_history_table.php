<?php
// Include database connection
require_once 'db_connection.php';

// Check if the device_history table exists
$table_check = $pdo->query("SHOW TABLES LIKE 'device_history'")->rowCount();

if ($table_check) {
    echo "The device_history table exists.";
} else {
    echo "The device_history table does not exist.";
}
?>
