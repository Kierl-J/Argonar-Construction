<?php
// Argonar Construction - Auth Helpers
// Requires db.php to be loaded first.

/** Get current logged-in user or null */
function current_user(): ?array {
    global $db;
    if (!isset($_SESSION['user_id'])) return null;

    static $user = false;
    if ($user === false) {
        $stmt = $db->prepare('SELECT id, name, email, company, created_at FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
        if (!$user) {
            unset($_SESSION['user_id']);
        }
    }
    return $user;
}

/** Check if user is logged in */
function is_logged_in(): bool {
    return current_user() !== null;
}

/** Require login - redirect to login page if not authenticated */
function require_login(): array {
    $user = current_user();
    if (!$user) {
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        flash('warning', 'Please log in to continue.');
        header('Location: ' . url('login.php'));
        exit;
    }
    return $user;
}
