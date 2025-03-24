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
    die('Error: No alert ID provided.');
}

$alert_id = intval($_GET['id']);

// Update alert status to resolved
$stmt = $pdo->prepare("UPDATE alerts SET resolved = 1 WHERE id = ?");
$stmt->execute([$alert_id]);

// Redirect back to alerts page with a success message
header("Location: alerts.php?message=Alert resolved successfully.");
exit();
?>
