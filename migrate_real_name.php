<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=argonar_construction;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $cols = $pdo->query("SHOW COLUMNS FROM solo_players LIKE 'real_name'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE solo_players ADD COLUMN real_name VARCHAR(100) DEFAULT '' AFTER game");
        echo "Migration successful: real_name column added.";
    } else {
        echo "Column already exists, skipping.";
    }
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
