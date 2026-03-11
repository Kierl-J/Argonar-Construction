<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';
require __DIR__ . '/../vendor/autoload.php';

$user = require_login();

$session_id = $_GET['session_id'] ?? '';

if (!$session_id) {
    flash('danger', 'Invalid session.');
    redirect('payment/pricing.php');
}

// Look up the pending subscription
$stmt = $db->prepare(
    'SELECT * FROM subscriptions WHERE payrex_checkout_session_id = ? AND user_id = ? LIMIT 1'
);
$stmt->execute([$session_id, $user['id']]);
$sub = $stmt->fetch();

if (!$sub) {
    flash('danger', 'Subscription not found.');
    redirect('payment/pricing.php');
}

// If already activated (e.g. by webhook), just show success
if ($sub['status'] === 'active') {
    $pageTitle = 'Payment Successful';
    require __DIR__ . '/../includes/header.php';
    showSuccess($sub);
    require __DIR__ . '/../includes/footer.php';
    exit;
}

// Verify with PayRex API
$activated = false;
try {
    $payrex = new \Payrex\PayrexClient(PAYREX_SECRET_KEY);
    $session = $payrex->checkoutSessions->retrieve($session_id);

    if ($session->status === 'completed' || $session->status === 'paid') {
        // Activate the subscription
        $plan = PLANS[$sub['plan_type']];
        $hours = $plan['hours'];
        $now = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', strtotime("+{$hours} hours"));

        $update = $db->prepare(
            'UPDATE subscriptions SET status = ?, payrex_payment_intent_id = ?, starts_at = ?, expires_at = ? WHERE id = ?'
        );
        $update->execute(['active', $session->payment_intent ?? null, $now, $expires, $sub['id']]);

        $sub['status'] = 'active';
        $sub['starts_at'] = $now;
        $sub['expires_at'] = $expires;
        $activated = true;
    }
} catch (\Exception $e) {
    // API call failed — still show a pending message
}

$pageTitle = $activated ? 'Payment Successful' : 'Payment Processing';
require __DIR__ . '/../includes/header.php';

if ($activated):
    showSuccess($sub);
else: ?>
<div class="card card-custom">
    <div class="card-body text-center py-5">
        <i class="fas fa-clock fa-3x text-warning mb-3"></i>
        <h5 class="fw-bold">Payment Processing</h5>
        <p class="text-muted mb-4">Your payment is being processed. Access will be activated shortly.<br>If you completed payment, please refresh this page in a moment.</p>
        <a href="<?= h($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary me-2">
            <i class="fas fa-sync-alt me-1"></i> Refresh
        </a>
        <a href="<?= url('payment/pricing.php') ?>" class="btn btn-outline-secondary">Back to Pricing</a>
    </div>
</div>
<?php endif;

require __DIR__ . '/../includes/footer.php';

function showSuccess(array $sub): void { ?>
<div class="card card-custom">
    <div class="card-body text-center py-5">
        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
        <h5 class="fw-bold">Payment Successful!</h5>
        <p class="text-muted mb-2">Your <?= h(PLANS[$sub['plan_type']]['name']) ?> has been activated.</p>
        <p class="mb-3">
            <strong>Access until:</strong> <?= date('M d, Y \a\t g:i A', strtotime($sub['expires_at'])) ?>
        </p>
        <?php if (!$sub['auto_renew']): ?>
        <div class="mb-4">
            <form method="POST" action="<?= url('payment/toggle-renew.php') ?>" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="sub_id" value="<?= $sub['id'] ?>">
                <input type="hidden" name="auto_renew" value="1">
                <button type="submit" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-sync-alt me-1"></i> Enable Auto-Renewal
                </button>
            </form>
            <p class="text-muted small mt-2 mb-0">Get a payment link before expiry so you never lose access.</p>
        </div>
        <?php else: ?>
        <p class="text-success small mb-4"><i class="fas fa-sync-alt me-1"></i> Auto-renewal is on. You'll get a payment link before expiry.</p>
        <?php endif; ?>
        <a href="<?= url('boq/index.php') ?>" class="btn btn-primary me-2">
            <i class="fas fa-file-invoice-dollar me-1"></i> Go to BOQ Generator
        </a>
        <a href="<?= url('index.php') ?>" class="btn btn-outline-secondary">Tool Hub</a>
    </div>
</div>
<?php
    global $currentUser;
    if (!empty($currentUser['is_guest'])): ?>
<div class="card card-custom mt-3">
    <div class="card-body p-4 text-center">
        <h6 class="fw-bold mb-1">Save your account</h6>
        <p class="text-muted small mb-3">Set an email and password so you can log back in later and keep your data.</p>
        <a href="<?= url('claim.php') ?>" class="btn btn-success btn-sm"><i class="fas fa-user-plus me-1"></i>Claim Account</a>
    </div>
</div>
<?php endif;
}
