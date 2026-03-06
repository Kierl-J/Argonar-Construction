<?php
// PayRex Webhook Handler — no layout, JSON responses only.
// For local XAMPP, this requires ngrok or similar tunnel.
// success.php handles activation as fallback.

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/notifications.php';
require __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_PAYREX_SIGNATURE'] ?? '';

if (!$payload || !$sigHeader) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing payload or signature']);
    exit;
}

try {
    $event = \Payrex\Webhook::parseEvent($payload, $sigHeader, PAYREX_WEBHOOK_SECRET);
} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

if ($event->type === 'payment_intent.succeeded') {
    $paymentIntent = $event->data->resource;
    $piId = $paymentIntent->id;
    $metadata = $paymentIntent->metadata ?? [];

    $userId = $metadata['user_id'] ?? null;
    $planType = $metadata['plan_type'] ?? null;

    if ($userId && $planType && isset(PLANS[$planType])) {
        // Find the pending subscription for this user and plan
        $stmt = $db->prepare(
            'SELECT * FROM subscriptions WHERE user_id = ? AND plan_type = ? AND status = ? ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute([$userId, $planType, 'pending']);
        $sub = $stmt->fetch();

        if ($sub) {
            $plan = PLANS[$planType];
            $hours = $plan['hours'];
            $now = date('Y-m-d H:i:s');
            $expires = date('Y-m-d H:i:s', strtotime("+{$hours} hours"));

            $update = $db->prepare(
                'UPDATE subscriptions SET status = ?, payrex_payment_intent_id = ?, starts_at = ?, expires_at = ? WHERE id = ?'
            );
            $update->execute(['active', $piId, $now, $expires, $sub['id']]);

            // Send in-app notification
            $planName = $plan['name'];
            $expiresFormatted = date('M d, Y g:i A', strtotime($expires));
            notify($db, (int)$userId, 'Subscription Activated', "Your {$planName} is now active until {$expiresFormatted}.", 'success', 'payment/history.php');
        }
    }
}

http_response_code(200);
echo json_encode(['received' => true]);
