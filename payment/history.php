<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';

$user = require_login();

$stmt = $db->prepare('SELECT * FROM subscriptions WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$subscriptions = $stmt->fetchAll();

$activeSub = get_active_subscription($db, $user['id']);

$pageTitle = 'Payment History';
require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Payment History</h5>
        <p class="text-muted small mb-0"><?= count($subscriptions) ?> transaction<?= count($subscriptions) !== 1 ? 's' : '' ?></p>
    </div>
    <a href="<?= url('payment/pricing.php') ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> New Subscription
    </a>
</div>

<?php if ($activeSub): ?>
<div class="alert alert-success mb-4">
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle fa-lg me-3"></i>
            <div>
                <strong>Active: <?= h(PLANS[$activeSub['plan_type']]['name']) ?></strong><br>
                <span class="small">Expires <?= date('M d, Y \a\t g:i A', strtotime($activeSub['expires_at'])) ?></span>
            </div>
        </div>
        <form method="POST" action="<?= url('payment/toggle-renew.php') ?>" class="ms-3">
            <?= csrf_field() ?>
            <input type="hidden" name="sub_id" value="<?= $activeSub['id'] ?>">
            <input type="hidden" name="auto_renew" value="<?= $activeSub['auto_renew'] ? '0' : '1' ?>">
            <button type="submit" class="btn btn-sm <?= $activeSub['auto_renew'] ? 'btn-outline-success' : 'btn-outline-secondary' ?>">
                <i class="fas fa-<?= $activeSub['auto_renew'] ? 'sync-alt' : 'redo' ?> me-1"></i>
                Auto-Renew: <?= $activeSub['auto_renew'] ? 'ON' : 'OFF' ?>
            </button>
        </form>
    </div>
    <?php if ($activeSub['auto_renew']): ?>
    <div class="small text-success mt-2">
        <i class="fas fa-info-circle me-1"></i> You'll receive a payment link via email before your plan expires.
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
// Show renewal banner if subscription expired and has auto-renew
if (!$activeSub) {
    $latestSub = get_latest_subscription($db, $user['id']);
    if ($latestSub && $latestSub['status'] === 'expired' && $latestSub['auto_renew'] && $latestSub['renewal_session_id']):
        // Find the pending renewal
        $renewStmt = $db->prepare('SELECT * FROM subscriptions WHERE payrex_checkout_session_id = ? AND status = "pending" LIMIT 1');
        $renewStmt->execute([$latestSub['renewal_session_id']]);
        $pendingRenewal = $renewStmt->fetch();
?>
<div class="alert alert-warning d-flex align-items-center mb-4">
    <i class="fas fa-exclamation-circle fa-lg me-3"></i>
    <div class="flex-grow-1">
        <strong>Your plan has expired.</strong><br>
        <span class="small">A renewal is ready. Complete the payment to restore access.</span>
    </div>
    <a href="<?= url('payment/pricing.php') ?>" class="btn btn-warning btn-sm ms-3 fw-bold">Renew Now</a>
</div>
<?php endif; } ?>

<?php if (empty($subscriptions)): ?>
<div class="empty-state">
    <i class="fas fa-receipt"></i>
    <h6>No Payments Yet</h6>
    <p class="text-muted">You haven't made any payments. <a href="<?= url('payment/pricing.php') ?>">View pricing</a></p>
</div>
<?php else: ?>
<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Access Period</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscriptions as $sub): ?>
                <tr>
                    <td><?= date('M d, Y g:i A', strtotime($sub['created_at'])) ?></td>
                    <td>
                        <span class="fw-bold"><?= h(PLANS[$sub['plan_type']]['name'] ?? $sub['plan_type']) ?></span>
                    </td>
                    <td class="fw-bold"><?= currency($sub['amount_paid']) ?></td>
                    <td>
                        <?php if ($sub['status'] === 'active'): ?>
                            <span class="badge bg-success">Active</span>
                        <?php elseif ($sub['status'] === 'pending'): ?>
                            <span class="badge bg-warning text-dark">Pending</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Expired</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($sub['starts_at']): ?>
                            <?= date('M d g:i A', strtotime($sub['starts_at'])) ?> &mdash; <?= date('M d g:i A', strtotime($sub['expires_at'])) ?>
                        <?php else: ?>
                            <span class="text-muted">&mdash;</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
