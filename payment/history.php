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
<div class="alert alert-success d-flex align-items-center mb-4">
    <i class="fas fa-check-circle fa-lg me-3"></i>
    <div>
        <strong>Active: <?= h(PLANS[$activeSub['plan_type']]['name']) ?></strong><br>
        <span class="small">Expires <?= date('M d, Y \a\t g:i A', strtotime($activeSub['expires_at'])) ?></span>
    </div>
</div>
<?php endif; ?>

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
