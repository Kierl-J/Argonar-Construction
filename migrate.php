<?php
require_once __DIR__ . '/includes/db.php';

$results = [];

$queries = [
    "ALTER TABLE teams ADD COLUMN substitute VARCHAR(100) DEFAULT '' AFTER member_5"
        => 'Add substitute column to teams',
    "ALTER TABLE teams ADD COLUMN checked_in TINYINT(1) DEFAULT 0"
        => 'Add checked_in to teams',
    "ALTER TABLE teams ADD COLUMN checked_in_at DATETIME DEFAULT NULL"
        => 'Add checked_in_at to teams',
    "ALTER TABLE solo_players ADD COLUMN checked_in TINYINT(1) DEFAULT 0"
        => 'Add checked_in to solo_players',
    "ALTER TABLE solo_players ADD COLUMN checked_in_at DATETIME DEFAULT NULL"
        => 'Add checked_in_at to solo_players',
    "CREATE TABLE IF NOT EXISTS disputes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ref_code VARCHAR(20) DEFAULT '',
        player_name VARCHAR(100) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('open', 'reviewed', 'closed') DEFAULT 'open',
        admin_notes TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        => 'Create disputes table',
];

foreach ($queries as $sql => $label) {
    try {
        $pdo->exec($sql);
        $results[] = "OK: $label";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            $results[] = "SKIP: $label (already exists)";
        } else {
            $results[] = "ERR: $label — " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html><head><title>Migration</title></head>
<body style="font-family:monospace; background:#111; color:#0f0; padding:2rem;">
<h2>Database Migration</h2>
<pre><?php foreach ($results as $r) echo htmlspecialchars($r) . "\n"; ?></pre>
<p style="color:#f59e0b; margin-top:1rem;">Delete this file after running.</p>
</body></html>
