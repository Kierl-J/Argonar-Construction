<?php
require_once __DIR__ . '/includes/db.php';

try {
    // Add ref_code to teams table
    $pdo->exec("ALTER TABLE teams ADD COLUMN ref_code VARCHAR(20) DEFAULT NULL AFTER team_logo");
    $pdo->exec("ALTER TABLE teams ADD UNIQUE KEY (ref_code)");
    echo "teams: ref_code column added.\n";
} catch (PDOException $e) {
    echo "teams: " . $e->getMessage() . "\n";
}

try {
    // Add ref_code to solo_players table
    $pdo->exec("ALTER TABLE solo_players ADD COLUMN ref_code VARCHAR(20) DEFAULT NULL AFTER preferred_role");
    $pdo->exec("ALTER TABLE solo_players ADD UNIQUE KEY (ref_code)");
    echo "solo_players: ref_code column added.\n";
} catch (PDOException $e) {
    echo "solo_players: " . $e->getMessage() . "\n";
}

echo "Done.\n";
