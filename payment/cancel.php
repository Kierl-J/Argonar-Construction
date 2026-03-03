<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';

$pageTitle = 'Payment Cancelled';
require __DIR__ . '/../includes/header.php';
?>

<div class="card card-custom">
    <div class="card-body text-center py-5">
        <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
        <h5 class="fw-bold">Payment Cancelled</h5>
        <p class="text-muted mb-4">Your payment was not completed. No charges were made.</p>
        <a href="<?= url('payment/pricing.php') ?>" class="btn btn-primary me-2">
            <i class="fas fa-arrow-left me-1"></i> Back to Pricing
        </a>
        <a href="<?= url('index.php') ?>" class="btn btn-outline-secondary">Tool Hub</a>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
