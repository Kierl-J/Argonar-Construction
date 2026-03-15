<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS argonar_construction");
    $pdo->exec("USE argonar_construction");

    $pdo->exec("CREATE TABLE IF NOT EXISTS teams (
        id INT AUTO_INCREMENT PRIMARY KEY,
        game VARCHAR(50) NOT NULL,
        team_name VARCHAR(100) NOT NULL,
        member_1 VARCHAR(100) NOT NULL,
        member_2 VARCHAR(100) NOT NULL,
        member_3 VARCHAR(100) NOT NULL,
        member_4 VARCHAR(100) NOT NULL,
        member_5 VARCHAR(100) NOT NULL,
        payment_proof VARCHAR(255) NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "Migration successful: teams table created.";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
