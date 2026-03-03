<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';
require __DIR__ . '/../vendor/autoload.php';

$user = current_user();
if (!$user) {
    $user = create_guest_user();
}

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('payment/pricing.php');
}

csrf_check();

$plan_type = $_POST['plan'] ?? '';
if (!isset(PLANS[$plan_type])) {
    flash('danger', 'Invalid plan selected.');
    redirect('payment/pricing.php');
}

$plan = PLANS[$plan_type];

try {
    $payrex = new \Payrex\PayrexClient(PAYREX_SECRET_KEY);

    $session = $payrex->checkoutSessions->create([
        'currency' => 'PHP',
        'line_items' => [
            [
                'name' => $plan['name'] . ' - ' . APP_NAME,
                'amount' => $plan['amount'],
                'quantity' => 1,
            ],
        ],
        'success_url' => 'https://argonar.co/payment/success.php?session_id={id}',
        'cancel_url' => 'https://argonar.co/payment/cancel.php',
        'billing_details_collection' => 'never',
        'payment_methods' => ['gcash', 'card', 'maya', 'qrph'],
        'metadata' => [
            'user_id' => (string)$user['id'],
            'plan_type' => $plan_type,
        ],
    ]);

    // Insert pending subscription
    $stmt = $db->prepare(
        'INSERT INTO subscriptions (user_id, plan_type, amount_paid, payrex_checkout_session_id, status) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $user['id'],
        $plan_type,
        $plan['amount'] / 100, // Convert centavos to pesos
        $session->id,
        'pending',
    ]);

    // Redirect to PayRex hosted checkout
    header('Location: ' . $session->url);
    exit;
} catch (\Exception $e) {
    flash('danger', 'Unable to create checkout session. Please try again.');
    redirect('payment/pricing.php');
}
