<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';

$user = require_login();

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('rebar/index.php');
}

csrf_check();

$id = intval($_POST['id'] ?? 0);

$stmt = $db->prepare('DELETE FROM rebar_lists WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);

if ($stmt->rowCount() > 0) {
    flash('success', 'Rebar list deleted successfully.');
} else {
    flash('danger', 'Rebar list not found.');
}

redirect('rebar/index.php');
