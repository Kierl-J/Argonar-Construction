<?php
require __DIR__ . '/includes/db.php';

$results = [];

try {
    // Drop old tables if they exist (items first due to FK)
    $db->exec('SET FOREIGN_KEY_CHECKS=0');
    $db->exec('DROP TABLE IF EXISTS rebar_cutting_patterns');
    $db->exec('DROP TABLE IF EXISTS rebar_items');
    $db->exec('DROP TABLE IF EXISTS rebar_lists');
    $db->exec('SET FOREIGN_KEY_CHECKS=1');
    $results[] = 'Dropped old rebar tables.';

    $db->exec("CREATE TABLE rebar_lists (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(200) NOT NULL,
        project_name VARCHAR(200) DEFAULT NULL,
        structural_member VARCHAR(200) DEFAULT NULL,
        prepared_by VARCHAR(100) DEFAULT NULL,
        checked_by VARCHAR(100) DEFAULT NULL,
        date_prepared DATE DEFAULT NULL,
        total_weight DECIMAL(12,3) NOT NULL DEFAULT 0,
        status ENUM('draft','final') NOT NULL DEFAULT 'draft',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $results[] = 'Created rebar_lists table.';

    $db->exec("CREATE TABLE rebar_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rebar_list_id INT NOT NULL,
        item_no INT NOT NULL DEFAULT 0,
        bar_size VARCHAR(10) NOT NULL DEFAULT '10mm',
        no_of_pieces INT NOT NULL DEFAULT 0,
        length_per_pc DECIMAL(8,3) NOT NULL DEFAULT 0,
        total_length DECIMAL(12,3) NOT NULL DEFAULT 0,
        weight_per_meter DECIMAL(8,4) NOT NULL DEFAULT 0,
        total_weight DECIMAL(12,3) NOT NULL DEFAULT 0,
        description VARCHAR(255) DEFAULT NULL,
        FOREIGN KEY (rebar_list_id) REFERENCES rebar_lists(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $results[] = 'Created rebar_items table.';

    $results[] = 'Migration complete!';
} catch (Exception $e) {
    $results[] = 'ERROR: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html><head><title>Rebar Migration</title></head>
<body style="font-family:monospace;padding:2rem">
<h2>Rebar Table Migration</h2>
<?php foreach ($results as $r): ?>
<p><?= htmlspecialchars($r) ?></p>
<?php endforeach; ?>
<p><br><strong>Delete this file after running.</strong></p>
</body></html>
