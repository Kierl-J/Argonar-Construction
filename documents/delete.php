<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('documents/index.php');
}

csrf_check();

$id = intval($_POST['id'] ?? 0);

$stmt = $db->prepare('DELETE FROM documents WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);

if ($stmt->rowCount() > 0) {
    flash('success', 'Document deleted successfully.');
} else {
    flash('danger', 'Document not found.');
}

redirect('documents/index.php');
