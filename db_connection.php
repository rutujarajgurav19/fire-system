<?php
$host = 'localhost';
$db_name = 'fire_system_db';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

// DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";

// Options for PDO
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    // Create a PDO instance (connect to the database)
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // If there is an error with the connection, stop the script and display the error
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}