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

// Handle dispute actions (form POST, not AJAX)
if ($type === 'dispute' && $id > 0) {
    if ($action === 'review_dispute') {
        $pdo->prepare("UPDATE disputes SET status = 'reviewed' WHERE id = ?")->execute([$id]);
    } elseif ($action === 'close_dispute') {
        $pdo->prepare("UPDATE disputes SET status = 'closed' WHERE id = ?")->execute([$id]);
    }
    header('Location: ' . base_url('admin/'));
    exit;
}

// Validate inputs
if (!in_array($type, ['team', 'solo'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid type']);
    exit;
}

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    exit;
}

if (!in_array($action, ['approve', 'reject', 'delete', 'rate'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

$table = $type === 'team' ? 'teams' : 'solo_players';

try {
    if ($action === 'rate') {
        $rating = max(0, min(10, (int)($_POST['rating'] ?? 0)));
        $stmt = $pdo->prepare("UPDATE solo_players SET admin_rating = ? WHERE id = ?");
        $stmt->execute([$rating, $id]);
        echo json_encode(['success' => true, 'action' => 'rate', 'rating' => $rating]);
        exit;
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
    } else {
        $new_status = $action === 'approve' ? 'approved' : 'rejected';
        $stmt = $pdo->prepare("UPDATE {$table} SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
    }

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'error' => 'Record not found']);
        exit;
    }

    echo json_encode(['success' => true, 'action' => $action]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
