<?php
/**
 * Auto-Renewal Cron Job
 * Run every hour: php /var/www/argonar/cron/renew.php
 *
 * Finds subscriptions expiring within 2 hours that have auto_renew=1
 * and no renewal_session_id yet, creates a checkout session, and
 * sends a renewal email with the payment link.
 */

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/subscription.php';

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

    // Send renewal email
    $plan = PLANS[$sub['plan_type']];
    $html = "
        <div style='font-family:Inter,Arial,sans-serif;max-width:480px;margin:0 auto;'>
            <h2 style='color:#0f172a;'>Renew Your {$plan['name']}</h2>
            <p>Hi {$sub['name']},</p>
            <p>Your <strong>{$plan['name']}</strong> on Argonar Construction is expiring soon.</p>
            <p>Click below to renew with one click ({$plan['display']}):</p>
            <p style='margin:1.5rem 0;'>
                <a href='{$renewalUrl}' style='background:#3b82f6;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;display:inline-block;'>Renew Now</a>
            </p>
            <p style='color:#64748b;font-size:14px;'>If you don't want to renew, you can turn off auto-renewal in your <a href='https://argonar.co/payment/history.php'>subscription settings</a>.</p>
            <hr style='border:none;border-top:1px solid #e2e8f0;margin:1.5rem 0;'>
            <p style='color:#94a3b8;font-size:12px;'>Argonar Construction - argonar.co</p>
        </div>
    ";

    $headers = implode("\r\n", [
        'From: Argonar Construction <noreply@argonar.co>',
        'Reply-To: support@argonar.co',
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
    ]);

    mail($sub['email'], 'Renew Your ' . $plan['name'] . ' - Argonar Construction', $html, $headers);

    echo "OK: Renewal created for {$sub['email']} (sub #{$sub['id']})\n";
    $count++;
}

echo "Done. Processed {$count} renewal(s).\n";
