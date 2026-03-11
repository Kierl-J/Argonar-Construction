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

/** Toggle auto-renew for a subscription */
function toggle_auto_renew(PDO $db, int $sub_id, int $user_id, bool $enabled): bool {
    $stmt = $db->prepare('UPDATE subscriptions SET auto_renew = ? WHERE id = ? AND user_id = ?');
    return $stmt->execute([$enabled ? 1 : 0, $sub_id, $user_id]);
}

/** Get the most recent subscription for a user (active or expired) */
function get_latest_subscription(PDO $db, int $user_id): ?array {
    $stmt = $db->prepare(
        'SELECT * FROM subscriptions WHERE user_id = ? AND status IN ("active","expired") ORDER BY created_at DESC LIMIT 1'
    );
    $stmt->execute([$user_id]);
    return $stmt->fetch() ?: null;
}

/** Create a renewal checkout session for an expiring/expired subscription */
function create_renewal_session(PDO $db, array $sub): ?string {
    if (!isset(PLANS[$sub['plan_type']])) return null;

    $plan = PLANS[$sub['plan_type']];

    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        $payrex = new \Payrex\PayrexClient(PAYREX_SECRET_KEY);

        $session = $payrex->checkoutSessions->create([
            'currency' => 'PHP',
            'line_items' => [
                [
                    'name' => 'Renewal: ' . $plan['name'] . ' - ' . APP_NAME,
                    'amount' => $plan['amount'],
                    'quantity' => 1,
                ],
            ],
            'success_url' => 'https://argonar.co/payment/success.php?session_id={id}',
            'cancel_url' => 'https://argonar.co/payment/pricing.php',
            'billing_details_collection' => 'auto',
            'payment_methods' => ['card', 'gcash', 'shopeepay', 'qrph', 'billease'],
            'metadata' => [
                'user_id' => (string)$sub['user_id'],
                'plan_type' => $sub['plan_type'],
                'renewal_of' => (string)$sub['id'],
            ],
        ]);

        // Create pending subscription for this renewal
        $stmt = $db->prepare(
            'INSERT INTO subscriptions (user_id, plan_type, amount_paid, payrex_checkout_session_id, status, auto_renew) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $sub['user_id'],
            $sub['plan_type'],
            $plan['amount'] / 100,
            $session->id,
            'pending',
            1,
        ]);

        // Store session ID on the original sub for tracking
        $db->prepare('UPDATE subscriptions SET renewal_session_id = ? WHERE id = ?')
            ->execute([$session->id, $sub['id']]);

        return $session->url;
    } catch (\Exception $e) {
        return null;
    }
}

/** Require active access — redirect to pricing page if none */
function require_access(): void {
    global $db;
    $user = current_user();
    if (!$user) {
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        flash('warning', 'Subscribe to access this tool. No registration needed.');
        header('Location: ' . url('payment/pricing.php'));
        exit;
    }
    if (!has_active_access($db, $user['id'])) {
        flash('warning', 'You need an active subscription to access this tool. Choose a plan below.');
        header('Location: ' . url('payment/pricing.php'));
        exit;
    }
}
