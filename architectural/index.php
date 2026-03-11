<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';

$pageTitle = 'Architectural Estimate';
$user = current_user();
$hasAccess = $user ? has_active_access($db, $user['id']) : false;

// If logged in, fetch user's estimates
$estimates = [];
if ($user) {
    $stmt = $db->prepare('SELECT * FROM architectural_estimates WHERE user_id = ? ORDER BY updated_at DESC');
    $stmt->execute([$user['id']]);
    $estimates = $stmt->fetchAll();
}

require __DIR__ . '/../includes/header.php';
?>

<?php if (!$hasAccess): ?>
<div class="card card-custom">
    <div class="card-body text-center py-5">
        <i class="fas fa-drafting-compass fa-3x text-muted mb-3"></i>
        <h5 class="fw-bold">Architectural Estimate</h5>
        <p class="text-muted mb-4">Cost estimates for masonry, tiling, painting, roofing, and more.<br>Subscribe to get started - no registration needed.</p>
        <a href="<?= url('payment/pricing.php') ?>" class="btn btn-primary"><i class="fas fa-tags me-1"></i>View Pricing</a>
        <?php if (!$user): ?><div class="mt-2"><a href="<?= url('login.php') ?>" class="text-muted small">Already have an account? Log in</a></div><?php endif; ?>
    </div>
</div>
<?php else: ?>
<!-- Logged-in: Estimates list -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">My Architectural Estimates</h5>
        <p class="text-muted small mb-0"><?= count($estimates) ?> document<?= count($estimates) !== 1 ? 's' : '' ?></p>
    </div>
    <a href="<?= url('architectural/create.php') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> New Estimate
    </a>
</div>

<?php if (empty($estimates)): ?>
<div class="empty-state">
    <i class="fas fa-folder-open"></i>
    <h6>No estimates yet</h6>
    <p class="text-muted">Create your first Architectural Estimate to get started.</p>
    <a href="<?= url('architectural/create.php') ?>" class="btn btn-primary btn-sm">Create Estimate</a>
</div>
<?php else: ?>
<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Project</th>
                    <th>Date</th>
                    <th class="text-end">Grand Total (&#8369;)</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($estimates as $est): ?>
                <tr>
                    <td>
                        <a href="<?= url('architectural/view.php?id=' . $est['id']) ?>" class="fw-bold text-decoration-none">
                            <?= h($est['title']) ?>
                        </a>
                    </td>
                    <td><?= h($est['project_name'] ?: '-') ?></td>
                    <td><?= $est['date_prepared'] ? date('M d, Y', strtotime($est['date_prepared'])) : '-' ?></td>
                    <td class="text-end fw-bold"><?= fmt($est['grand_total']) ?></td>
                    <td>
                        <span class="badge bg-<?= $est['status'] === 'final' ? 'success' : 'secondary' ?>">
                            <?= ucfirst($est['status']) ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="<?= url('architectural/view.php?id=' . $est['id']) ?>" class="btn btn-sm btn-outline-primary" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?= url('architectural/edit.php?id=' . $est['id']) ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?= url('architectural/export.php?id=' . $est['id']) ?>" class="btn btn-sm btn-outline-success" title="Export Excel">
                            <i class="fas fa-file-excel"></i>
                        </a>
                        <form id="delete-<?= $est['id'] ?>" method="POST" action="<?= url('architectural/delete.php') ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $est['id'] ?>">
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                    onclick="confirmDelete('delete-<?= $est['id'] ?>')">
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
