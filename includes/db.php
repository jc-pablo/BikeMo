<?php

// Rental dates are displayed in Philippine local time.
date_default_timezone_set('Asia/Manila');

$host = 'localhost';
$dbname = 'bikemo';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Database connection failed.');
}