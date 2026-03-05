<?php
require __DIR__ . '/includes/db.php';

$db->exec("ALTER TABLE subscriptions ADD COLUMN IF NOT EXISTS auto_renew TINYINT(1) NOT NULL DEFAULT 0");
$db->exec("ALTER TABLE subscriptions ADD COLUMN IF NOT EXISTS renewal_session_id VARCHAR(100) DEFAULT NULL");

echo "OK - auto_renew and renewal_session_id columns added.\n";
