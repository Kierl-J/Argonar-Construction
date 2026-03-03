<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';

$user = require_login();

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('boq/index.php');
}

csrf_check();

$id = intval($_POST['id'] ?? 0);

$stmt = $db->prepare('DELETE FROM boqs WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);

if ($stmt->rowCount() > 0) {
    flash('success', 'BOQ deleted successfully.');
} else {
    flash('danger', 'BOQ not found.');
}

redirect('boq/index.php');
