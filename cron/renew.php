<?php
/**
 * Auto-Renewal Cron Job
 * Run every hour: php /var/www/argonar/cron/renew.php
 *
 * Finds subscriptions expiring within 2 hours that have auto_renew=1
 * and no renewal_session_id yet, creates a checkout session, and
 * sends an in-app notification with the payment link.
 */

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/subscription.php';
require __DIR__ . '/../includes/notifications.php';

// Find auto-renew subscriptions expiring within 2 hours (or already expired within last 24h)
$stmt = $db->prepare("
    SELECT s.*, u.name, u.email
    FROM subscriptions s
    JOIN users u ON s.user_id = u.id
    WHERE s.auto_renew = 1
      AND s.status IN ('active', 'expired')
      AND s.renewal_session_id IS NULL
      AND s.expires_at BETWEEN DATE_SUB(NOW(), INTERVAL 24 HOUR) AND DATE_ADD(NOW(), INTERVAL 2 HOUR)
    ORDER BY s.expires_at ASC
");
$stmt->execute();
$subs = $stmt->fetchAll();

if (empty($subs)) {
    echo "No renewals to process.\n";
    exit;
}

$count = 0;
foreach ($subs as $sub) {
    $renewalUrl = create_renewal_session($db, $sub);

    if (!$renewalUrl) {
        echo "FAILED: Could not create renewal for sub #{$sub['id']} (user: {$sub['email']})\n";
        continue;
    }

    // Send in-app notification
    $plan = PLANS[$sub['plan_type']];
    notify(
        $db,
        $sub['user_id'],
        'Time to renew your ' . $plan['name'],
        'Your ' . $plan['name'] . ' is expiring soon. Click to renew for ' . $plan['display'] . '.',
        'renewal',
        'payment/pricing.php'
    );

    echo "OK: Renewal + notification created for {$sub['email']} (sub #{$sub['id']})\n";
    $count++;
}

echo "Done. Processed {$count} renewal(s).\n";
