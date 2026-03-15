<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=argonar_construction;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $msgs = [];

    // Teams: contact_number, facebook_link, members_ranks
    $cols = $pdo->query("SHOW COLUMNS FROM teams LIKE 'contact_number'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE teams ADD COLUMN contact_number VARCHAR(20) DEFAULT '' AFTER ref_code");
        $msgs[] = 'teams.contact_number added';
    }
    $cols = $pdo->query("SHOW COLUMNS FROM teams LIKE 'facebook_link'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE teams ADD COLUMN facebook_link VARCHAR(255) DEFAULT '' AFTER contact_number");
        $msgs[] = 'teams.facebook_link added';
    }
    $cols = $pdo->query("SHOW COLUMNS FROM teams LIKE 'members_ranks'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE teams ADD COLUMN members_ranks TEXT DEFAULT NULL AFTER member_5");
        $msgs[] = 'teams.members_ranks added';
    }

    // Solo: contact_number, facebook_link
    $cols = $pdo->query("SHOW COLUMNS FROM solo_players LIKE 'contact_number'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE solo_players ADD COLUMN contact_number VARCHAR(20) DEFAULT '' AFTER player_name");
        $msgs[] = 'solo_players.contact_number added';
    }
    $cols = $pdo->query("SHOW COLUMNS FROM solo_players LIKE 'facebook_link'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE solo_players ADD COLUMN facebook_link VARCHAR(255) DEFAULT '' AFTER contact_number");
        $msgs[] = 'solo_players.facebook_link added';
    }

    echo 'Migration complete: ' . (empty($msgs) ? 'nothing to do' : implode(', ', $msgs));
} catch (PDOException $e) {
    echo 'Migration failed: ' . $e->getMessage();
}
