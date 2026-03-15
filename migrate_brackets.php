<?php
require_once __DIR__ . '/includes/db.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS matches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            game VARCHAR(50) NOT NULL,
            round INT NOT NULL,
            match_order INT NOT NULL,
            team1_name VARCHAR(100) NOT NULL,
            team2_name VARCHAR(100) NOT NULL,
            team1_score INT NOT NULL DEFAULT 0,
            team2_score INT NOT NULL DEFAULT 0,
            winner VARCHAR(100) NOT NULL DEFAULT '',
            status ENUM('pending', 'live', 'completed') NOT NULL DEFAULT 'pending',
            scheduled_at DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "matches table created.\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tournament_results (
            id INT AUTO_INCREMENT PRIMARY KEY,
            game VARCHAR(50) NOT NULL,
            season VARCHAR(50) NOT NULL DEFAULT 'Season 1',
            placement INT NOT NULL,
            team_name VARCHAR(100) NOT NULL,
            prize VARCHAR(255) NOT NULL DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "tournament_results table created.\n";

    echo "Migration complete!";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
