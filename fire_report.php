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

// Query to fetch fire reports
$query = "SELECT message, location, report_date FROM fire_reports"; // Adjust the query as needed

$stmt = $pdo->prepare($query);
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fire Reports</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Fire Reports</h1>
        </header>
        
        <main>
            <section>
                <h2>Report Messages</h2>
                <?php if (count($reports) > 0): ?>
                    <ul>
                        <?php foreach ($reports as $report): ?>
                            <li>
                                <strong>Location:</strong> <?php echo htmlspecialchars($report['location']); ?><br>
                                <strong>Message:</strong> <?php echo htmlspecialchars($report['message']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No reports found.</p>
                <?php endif; ?>
            </section>
        </main>
        
        <footer>
            <p>&copy; 2025 Fire System Management. All rights reserved.</p>
        </footer>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
