<?php
require __DIR__ . '/includes/db.php';

echo "Running new tools migration...\n";

$queries = [
    "CREATE TABLE IF NOT EXISTS architectural_estimates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(200) NOT NULL,
        project_name VARCHAR(200) DEFAULT NULL,
        location VARCHAR(200) DEFAULT NULL,
        prepared_by VARCHAR(100) DEFAULT NULL,
        checked_by VARCHAR(100) DEFAULT NULL,
        date_prepared DATE DEFAULT NULL,
        contingency_percentage DECIMAL(5,2) NOT NULL DEFAULT 10,
        total_masonry DECIMAL(15,2) NOT NULL DEFAULT 0,
        total_tiling DECIMAL(15,2) NOT NULL DEFAULT 0,
        total_painting DECIMAL(15,2) NOT NULL DEFAULT 0,
        total_roofing DECIMAL(15,2) NOT NULL DEFAULT 0,
        total_plastering DECIMAL(15,2) NOT NULL DEFAULT 0,
        total_ceiling DECIMAL(15,2) NOT NULL DEFAULT 0,
        total_doors_windows DECIMAL(15,2) NOT NULL DEFAULT 0,
        subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
        contingency_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
        grand_total DECIMAL(15,2) NOT NULL DEFAULT 0,
        status ENUM('draft','final') NOT NULL DEFAULT 'draft',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS architectural_estimate_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        estimate_id INT NOT NULL,
        item_no INT NOT NULL DEFAULT 0,
        category ENUM('masonry','tiling','painting','roofing','plastering','ceiling','doors_windows') NOT NULL,
        description VARCHAR(255) NOT NULL,
        quantity DECIMAL(12,3) NOT NULL DEFAULT 0,
        unit VARCHAR(20) NOT NULL DEFAULT 'sq.m',
        unit_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
        amount DECIMAL(15,2) NOT NULL DEFAULT 0,
        remarks VARCHAR(255) DEFAULT NULL,
        FOREIGN KEY (estimate_id) REFERENCES architectural_estimates(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        doc_type ENUM('scope_of_work','material_requisition','progress_report','change_order') NOT NULL,
        title VARCHAR(200) NOT NULL,
        project_name VARCHAR(200) DEFAULT NULL,
        data JSON DEFAULT NULL,
        status ENUM('draft','final') NOT NULL DEFAULT 'draft',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS document_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        document_id INT NOT NULL,
        item_no INT NOT NULL DEFAULT 0,
        description TEXT DEFAULT NULL,
        quantity DECIMAL(12,3) DEFAULT NULL,
        unit VARCHAR(20) DEFAULT NULL,
        unit_cost DECIMAL(12,2) DEFAULT NULL,
        amount DECIMAL(15,2) DEFAULT NULL,
        percentage_complete DECIMAL(5,2) DEFAULT NULL,
        remarks TEXT DEFAULT NULL,
        FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",
];

foreach ($queries as $i => $sql) {
    try {
        $db->exec($sql);
        echo "Table " . ($i + 1) . " of 4 created OK.\n";
    } catch (PDOException $e) {
        echo "Table " . ($i + 1) . " error: " . $e->getMessage() . "\n";
    }
}

echo "Migration complete.\n";
