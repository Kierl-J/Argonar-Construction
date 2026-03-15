<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=argonar_construction;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $cols = $pdo->query("SHOW COLUMNS FROM matches LIKE 'bracket_side'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE matches ADD COLUMN bracket_side ENUM('winners', 'losers', 'grand') DEFAULT 'winners' AFTER game");
        echo "Migration successful: bracket_side column added.";
    } else {
        echo "Column already exists, skipping.";
    }
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
