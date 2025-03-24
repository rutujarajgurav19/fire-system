<?php
// Start session
session_start();

// Include database connection
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get username and password from form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute query to check credentials
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

    
    if ($stmt->rowCount() > 0) {
        // Successful login
        $_SESSION['user_id'] = $user['id'];

        header("Location: index.html"); // Redirect to the main page
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
<input type="text" name="username" id="username" required autocomplete="username">

            </div>
            <div class="form-group">
                <label for="password">Password:</label>
<input type="password" name="password" id="password" required autocomplete="current-password">

            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
