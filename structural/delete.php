<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';

$user = require_login();

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('structural/index.php');
}

csrf_check();

$id = intval($_POST['id'] ?? 0);

$stmt = $db->prepare('DELETE FROM structural_estimates WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);

if ($stmt->rowCount() > 0) {
    flash('success', 'Structural estimate deleted successfully.');
} else {
    flash('danger', 'Structural estimate not found.');
}

redirect('structural/index.php');
