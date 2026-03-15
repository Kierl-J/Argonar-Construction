<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=argonar_construction;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if column already exists
    $cols = $pdo->query("SHOW COLUMNS FROM solo_players LIKE 'preferred_role'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE solo_players ADD COLUMN preferred_role VARCHAR(50) DEFAULT '' AFTER rank_tier");
        echo "Migration successful: preferred_role column added.";
    } else {
        echo "Column already exists, skipping.";
    }
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
