<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';

$pageTitle = 'BOQ Generator';
$user = current_user();
$hasAccess = $user ? has_active_access($db, $user['id']) : false;

// If logged in, fetch user's BOQs
$boqs = [];
if ($user) {
    $stmt = $db->prepare('SELECT * FROM boqs WHERE user_id = ? ORDER BY updated_at DESC');
    $stmt->execute([$user['id']]);
    $boqs = $stmt->fetchAll();
}

require __DIR__ . '/../includes/header.php';
?>

<?php if (!$hasAccess): ?>
<div class="card card-custom">
    <div class="card-body text-center py-5">
        <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
        <h5 class="fw-bold">BOQ Generator</h5>
        <p class="text-muted mb-4">Create detailed Bill of Quantities with automatic calculations and Excel export.<br>Subscribe to get started - no registration needed.</p>
        <a href="<?= url('payment/pricing.php') ?>" class="btn btn-primary"><i class="fas fa-tags me-1"></i>View Pricing</a>
        <?php if (!$user): ?><div class="mt-2"><a href="<?= url('login.php') ?>" class="text-muted small">Already have an account? Log in</a></div><?php endif; ?>
    </div>
</div>
<?php else: ?>
<!-- Logged-in: BOQ list -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">My BOQs</h5>
        <p class="text-muted small mb-0"><?= count($boqs) ?> document<?= count($boqs) !== 1 ? 's' : '' ?></p>
    </div>
    <a href="<?= url('boq/create.php') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> New BOQ
    </a>
</div>

<?php if (empty($boqs)): ?>
<div class="empty-state">
    <i class="fas fa-folder-open"></i>
    <h6>No BOQs yet</h6>
    <p class="text-muted">Create your first Bill of Quantities to get started.</p>
    <a href="<?= url('boq/create.php') ?>" class="btn btn-primary btn-sm">Create BOQ</a>
</div>
<?php else: ?>
<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date Prepared</th>
                    <th>Grand Total</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($boqs as $boq): ?>
                <tr>
                    <td>
                        <a href="<?= url('boq/view.php?id=' . $boq['id']) ?>" class="fw-bold text-decoration-none">
                            <?= h($boq['title']) ?>
                        </a>
                    </td>
                    <td><?= $boq['date_prepared'] ? date('M d, Y', strtotime($boq['date_prepared'])) : '-' ?></td>
                    <td class="fw-bold"><?= currency($boq['grand_total']) ?></td>
                    <td>
                        <span class="badge bg-<?= $boq['status'] === 'final' ? 'success' : 'secondary' ?>">
                            <?= ucfirst($boq['status']) ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="<?= url('boq/view.php?id=' . $boq['id']) ?>" class="btn btn-sm btn-outline-primary" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?= url('boq/edit.php?id=' . $boq['id']) ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?= url('boq/export.php?id=' . $boq['id']) ?>" class="btn btn-sm btn-outline-success" title="Export Excel">
                            <i class="fas fa-file-excel"></i>
                        </a>
                        <form id="delete-<?= $boq['id'] ?>" method="POST" action="<?= url('boq/delete.php') ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $boq['id'] ?>">
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                    onclick="confirmDelete('delete-<?= $boq['id'] ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
