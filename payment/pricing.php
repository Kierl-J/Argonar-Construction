<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';

$pageTitle = 'Pricing';
$user = current_user();
$activeSub = $user ? get_active_subscription($db, $user['id']) : null;

require __DIR__ . '/../includes/header.php';
?>

<?php if ($activeSub): ?>
<div class="alert alert-success d-flex align-items-center mb-4">
    <i class="fas fa-check-circle fa-lg me-3"></i>
    <div>
        <strong>You have active access!</strong><br>
        <span class="small">Your <?= h(PLANS[$activeSub['plan_type']]['name']) ?> is active until <strong><?= date('M d, Y \a\t g:i A', strtotime($activeSub['expires_at'])) ?></strong>.</span>
    </div>
</div>
<?php endif; ?>

<div class="text-center mb-4">
    <h4 class="fw-bold">Choose Your Plan</h4>
    <p class="text-muted">Get full access to all construction tools. Pay only for what you need.</p>
</div>

<div class="row g-4 justify-content-center mb-4">
    <!-- Daily Plan -->
    <div class="col-md-5 col-lg-4">
        <div class="pricing-card">
            <div class="pricing-header">
                <h5 class="fw-bold mb-1">24-Hour Pass</h5>
                <p class="text-muted small mb-0">Perfect for quick projects</p>
            </div>
            <div class="pricing-price">
                <span class="price-amount">₱20</span>
                <span class="price-period">/ 24 hours</span>
            </div>
            <ul class="pricing-features">
                <li><i class="fas fa-check text-success me-2"></i>BOQ Generator</li>
                <li><i class="fas fa-check text-success me-2"></i>Excel Export</li>
                <li><i class="fas fa-check text-success me-2"></i>Unlimited Documents</li>
                <li><i class="fas fa-check text-success me-2"></i>All Future Tools</li>
            </ul>
            <?php if ($activeSub): ?>
                <button class="btn btn-outline-secondary w-100" disabled>Already Active</button>
            <?php else: ?>
                <form method="POST" action="<?= url('payment/checkout.php') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="plan" value="daily">
                    <button type="submit" class="btn btn-outline-primary w-100 fw-bold">Get 24-Hour Pass</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Monthly Plan -->
    <div class="col-md-5 col-lg-4">
        <div class="pricing-card pricing-card-featured">
            <div class="pricing-badge">Best Value</div>
            <div class="pricing-header">
                <h5 class="fw-bold mb-1">Monthly Plan</h5>
                <p class="text-muted small mb-0">For ongoing projects</p>
            </div>
            <div class="pricing-price">
                <span class="price-amount">₱500</span>
                <span class="price-period">/ 30 days</span>
            </div>
            <ul class="pricing-features">
                <li><i class="fas fa-check text-success me-2"></i>BOQ Generator</li>
                <li><i class="fas fa-check text-success me-2"></i>Excel Export</li>
                <li><i class="fas fa-check text-success me-2"></i>Unlimited Documents</li>
                <li><i class="fas fa-check text-success me-2"></i>All Future Tools</li>
                <li><i class="fas fa-star text-warning me-2"></i>Save 17% vs Daily</li>
            </ul>
            <?php if ($activeSub): ?>
                <button class="btn btn-outline-secondary w-100" disabled>Already Active</button>
            <?php else: ?>
                <form method="POST" action="<?= url('payment/checkout.php') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="plan" value="monthly">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Get Monthly Plan</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="text-center">
    <p class="text-muted small mb-1">Payments are securely processed by PayRex. We accept GCash, Maya, Cards, and QRPH.</p>
    <div class="d-flex justify-content-center gap-3 text-muted">
        <span><i class="fas fa-shield-alt me-1"></i>Secure Payment</span>
        <span><i class="fas fa-bolt me-1"></i>Instant Access</span>
        <span><i class="fas fa-undo me-1"></i>No Auto-Renewal</span>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
