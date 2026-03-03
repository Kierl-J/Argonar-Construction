<?php
// Argonar Construction - Subscription / Access Control Helpers
// Requires db.php and auth.php to be loaded first.

/** Auto-expire any overdue active subscriptions */
function expire_stale_subscriptions(): void {
    global $db;
    $db->exec("UPDATE subscriptions SET status='expired' WHERE status='active' AND expires_at < NOW()");
}

/** Get the user's current active (non-expired) subscription, or null */
function get_active_subscription(PDO $db, int $user_id): ?array {
    expire_stale_subscriptions();
    $stmt = $db->prepare(
        'SELECT * FROM subscriptions WHERE user_id = ? AND status = ? AND expires_at > NOW() ORDER BY expires_at DESC LIMIT 1'
    );
    $stmt->execute([$user_id, 'active']);
    return $stmt->fetch() ?: null;
}

/** Does this user have active (paid) access right now? */
function has_active_access(PDO $db, int $user_id): bool {
    return get_active_subscription($db, $user_id) !== null;
}

/** Require active access — redirect to pricing page if none */
function require_access(): void {
    global $db;
    $user = current_user();
    if (!$user) {
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        flash('warning', 'Please log in to continue.');
        header('Location: ' . url('login.php'));
        exit;
    }
    if (!has_active_access($db, $user['id'])) {
        flash('warning', 'You need an active subscription to access this tool. Choose a plan below.');
        header('Location: ' . url('payment/pricing.php'));
        exit;
    }
}
