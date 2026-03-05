<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('payment/history.php');
}

csrf_check();

$subId = (int)($_POST['sub_id'] ?? 0);
$enabled = ($_POST['auto_renew'] ?? '0') === '1';

$stmt = $db->prepare('SELECT * FROM subscriptions WHERE id = ? AND user_id = ? AND status = "active"');
$stmt->execute([$subId, $user['id']]);
$sub = $stmt->fetch();

if (!$sub) {
    flash('danger', 'Subscription not found.');
    redirect('payment/history.php');
}

toggle_auto_renew($db, $subId, $user['id'], $enabled);

flash('success', $enabled ? 'Auto-renewal enabled. You\'ll get a payment link before your plan expires.' : 'Auto-renewal disabled.');
redirect('payment/history.php');
