<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=argonar_construction;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $cols = $pdo->query("SHOW COLUMNS FROM teams LIKE 'team_logo'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE teams ADD COLUMN team_logo VARCHAR(255) DEFAULT '' AFTER team_name");
        echo "Migration successful: team_logo column added.";
    } else {
        echo "Column already exists, skipping.";
    }
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
