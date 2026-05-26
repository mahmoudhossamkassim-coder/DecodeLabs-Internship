<?php
// db.php

// MySQL configuration for XAMPP
$host = '127.0.0.1';
$dbname = 'decodelabs_db'; // Name of the database you want to create
$user = 'root';            // Default XAMPP username
$pass = '';                // Default XAMPP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // 1. Connect to MySQL Server (without selecting a specific database yet)
    $db = new PDO($dsn, $user, $pass, $options);
    
    // 2. Create the database if it doesn't exist
    $db->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    
    // 3. Select the database to use
    $db->exec("USE `$dbname`");
    
    // 4. Create the 'users' table if it doesn't exist
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL
    )");

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'MySQL Database connection failed: ' . $e->getMessage()]);
    exit();
}
?>
