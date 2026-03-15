<?php
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Check admin session
if (empty($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$type   = $_POST['type'] ?? '';
$id     = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

// Validate inputs
if (!in_array($type, ['team', 'solo'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid type']);
    exit;
}

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    exit;
}

if (!in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

$new_status = $action === 'approve' ? 'approved' : 'rejected';
$table = $type === 'team' ? 'teams' : 'solo_players';

try {
    $stmt = $pdo->prepare("UPDATE {$table} SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $id]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'error' => 'Record not found']);
        exit;
    }

    echo json_encode(['success' => true, 'status' => $new_status]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
