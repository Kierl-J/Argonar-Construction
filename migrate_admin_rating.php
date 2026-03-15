<?php
require_once __DIR__ . '/includes/db.php';

try {
    $pdo->exec("ALTER TABLE solo_players ADD COLUMN admin_rating INT DEFAULT 0");
    echo "Migration successful: admin_rating column added to solo_players.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column admin_rating already exists. Nothing to do.";
    } else {
        echo "Migration failed: " . $e->getMessage();
    }
}
