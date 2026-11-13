<?php
// config/database.php

$host = 'localhost';
$db   = 'tinysprout_db'; // Change to your database name
$user = 'root';                // Change to your database username
$pass = '';                    // Change to your database password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If connection fails, stop execution and show error
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>