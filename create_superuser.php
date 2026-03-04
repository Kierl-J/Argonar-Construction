<?php
require __DIR__ . '/includes/db.php';

$name     = 'Super Admin';
$email    = 'admin@argonar.co';
$password = 'Argonar2026!';

// Check if already exists
$stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
$existing = $stmt->fetch();

if ($existing) {
    $userId = $existing['id'];
    echo "User already exists (ID: $userId). Updating subscription...\n";
} else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $stmt->execute([$name, $email, $hash]);
    $userId = $db->lastInsertId();
    echo "Created user (ID: $userId)\n";
}

// Expire any existing subscriptions
$db->prepare('UPDATE subscriptions SET status = "expired" WHERE user_id = ?')->execute([$userId]);

// Create 1-year active subscription
$stmt = $db->prepare(
    'INSERT INTO subscriptions (user_id, plan_type, amount_paid, status, starts_at, expires_at) VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR))'
);
$stmt->execute([$userId, 'monthly', 0, 'active']);

echo "Active subscription created (expires in 1 year)\n";
echo "\nLogin:\n  Email: $email\n  Password: $password\n";
echo "\nDELETE THIS FILE AFTER USE.\n";
