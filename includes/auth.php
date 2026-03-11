<?php
// Argonar Construction - Auth Helpers
// Requires db.php to be loaded first.

/** Get current logged-in user or null */
function current_user(): ?array {
    global $db;
    if (!isset($_SESSION['user_id'])) return null;

    static $user = false;
    if ($user === false) {
        $stmt = $db->prepare('SELECT id, name, email, company, is_guest, created_at FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
        if (!$user) {
            unset($_SESSION['user_id']);
        }
    }
    return $user;
}

/** Create a guest user account and log them in */
function create_guest_user(): array {
    global $db;
    $guestId = bin2hex(random_bytes(6));
    $name = 'Guest-' . $guestId;
    $email = 'guest_' . $guestId . '@argonar.co';
    $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

    $stmt = $db->prepare('INSERT INTO users (name, email, password, is_guest) VALUES (?, ?, ?, 1)');
    $stmt->execute([$name, $email, $password]);

    $userId = $db->lastInsertId();
    $_SESSION['user_id'] = $userId;
    session_regenerate_id(true);

    return [
        'id' => $userId,
        'name' => $name,
        'email' => $email,
        'company' => null,
        'is_guest' => 1,
        'created_at' => date('Y-m-d H:i:s'),
    ];
}

/** Check if user is logged in */
function is_logged_in(): bool {
    return current_user() !== null;
}

/** Require login - redirect to pricing if not authenticated (guest accounts created at checkout) */
function require_login(): array {
    $user = current_user();
    if (!$user) {
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        flash('warning', 'Subscribe to access this tool. No registration needed.');
        header('Location: ' . url('payment/pricing.php'));
        exit;
    }
    return $user;
}
