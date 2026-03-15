<?php
require_once __DIR__ . '/includes/db.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS solo_players (
            id INT AUTO_INCREMENT PRIMARY KEY,
            game VARCHAR(50) NOT NULL,
            player_name VARCHAR(100) NOT NULL,
            rank_tier VARCHAR(50) NOT NULL,
            payment_proof VARCHAR(255) NOT NULL,
            status ENUM('pending', 'matched', 'approved') DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "Migration successful: solo_players table created.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
