<?php
require __DIR__ . '/includes/db.php';

echo "Running structural estimates migration...\n";

try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS structural_estimates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            project_name VARCHAR(200) DEFAULT NULL,
            location VARCHAR(200) DEFAULT NULL,
            prepared_by VARCHAR(100) DEFAULT NULL,
            checked_by VARCHAR(100) DEFAULT NULL,
            date_prepared DATE DEFAULT NULL,
            contingency_percentage DECIMAL(5,2) NOT NULL DEFAULT 10,
            total_concrete DECIMAL(15,2) NOT NULL DEFAULT 0,
            total_steel DECIMAL(15,2) NOT NULL DEFAULT 0,
            total_formwork DECIMAL(15,2) NOT NULL DEFAULT 0,
            subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
            contingency_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            grand_total DECIMAL(15,2) NOT NULL DEFAULT 0,
            status ENUM('draft','final') NOT NULL DEFAULT 'draft',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "Created structural_estimates table.\n";

    $db->exec("
        CREATE TABLE IF NOT EXISTS structural_estimate_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            estimate_id INT NOT NULL,
            item_no INT NOT NULL DEFAULT 0,
            category ENUM('concrete','steel','formwork') NOT NULL,
            member_name VARCHAR(100) NOT NULL,
            quantity DECIMAL(12,3) NOT NULL DEFAULT 0,
            unit VARCHAR(20) NOT NULL DEFAULT 'cu.m',
            unit_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
            amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            description VARCHAR(255) DEFAULT NULL,
            FOREIGN KEY (estimate_id) REFERENCES structural_estimates(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "Created structural_estimate_items table.\n";

    echo "Migration complete!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
