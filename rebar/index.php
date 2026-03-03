<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';

$pageTitle = 'Rebar Cutting List';
$user = current_user();
$hasAccess = $user ? has_active_access($db, $user['id']) : false;

// If logged in, fetch user's rebar lists
$lists = [];
if ($user) {
    $stmt = $db->prepare('SELECT * FROM rebar_lists WHERE user_id = ? ORDER BY updated_at DESC');
    $stmt->execute([$user['id']]);
    $lists = $stmt->fetchAll();
}

require __DIR__ . '/../includes/header.php';
?>

<?php if (!$user): ?>
<!-- Guest prompt -->
<div class="card card-custom">
    <div class="card-body text-center py-5">
        <i class="fas fa-ruler-combined fa-3x text-muted mb-3"></i>
        <h5 class="fw-bold">Rebar Cutting List</h5>
        <p class="text-muted mb-4">Generate rebar cutting lists with automatic weight calculations and Excel export.<br>Please log in or register to get started.</p>
        <a href="<?= url('login.php') ?>" class="btn btn-primary">Log In / Register</a>
    </div>
</div>
<?php elseif (!$hasAccess): ?>
<!-- No active subscription prompt -->
<div class="card card-custom">
    <div class="card-body text-center py-5">
        <i class="fas fa-lock fa-3x text-warning mb-3"></i>
        <h5 class="fw-bold">Subscription Required</h5>
        <p class="text-muted mb-4">You need an active subscription to use the Rebar Cutting List tool.<br>Choose a plan to get started.</p>
        <a href="<?= url('payment/pricing.php') ?>" class="btn btn-primary">
            <i class="fas fa-tags me-1"></i> View Pricing Plans
        </a>
    </div>
</div>
<?php else: ?>
<!-- Logged-in: Rebar list -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">My Rebar Lists</h5>
        <p class="text-muted small mb-0"><?= count($lists) ?> document<?= count($lists) !== 1 ? 's' : '' ?></p>
    </div>
    <a href="<?= url('rebar/create.php') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> New Rebar List
    </a>
</div>

<?php if (empty($lists)): ?>
<div class="empty-state">
    <i class="fas fa-folder-open"></i>
    <h6>No rebar lists yet</h6>
    <p class="text-muted">Create your first Rebar Cutting List to get started.</p>
    <a href="<?= url('rebar/create.php') ?>" class="btn btn-primary btn-sm">Create Rebar List</a>
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
                    <th class="text-end">Total Weight (kg)</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lists as $list): ?>
                <tr>
                    <td>
                        <a href="<?= url('rebar/view.php?id=' . $list['id']) ?>" class="fw-bold text-decoration-none">
                            <?= h($list['title']) ?>
                        </a>
                    </td>
                    <td><?= h($list['project_name'] ?: '-') ?></td>
                    <td><?= $list['date_prepared'] ? date('M d, Y', strtotime($list['date_prepared'])) : '-' ?></td>
                    <td class="text-end fw-bold"><?= fmt($list['total_weight'], 3) ?></td>
                    <td>
                        <span class="badge bg-<?= $list['status'] === 'final' ? 'success' : 'secondary' ?>">
                            <?= ucfirst($list['status']) ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="<?= url('rebar/view.php?id=' . $list['id']) ?>" class="btn btn-sm btn-outline-primary" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?= url('rebar/edit.php?id=' . $list['id']) ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?= url('rebar/export.php?id=' . $list['id']) ?>" class="btn btn-sm btn-outline-success" title="Export Excel">
                            <i class="fas fa-file-excel"></i>
                        </a>
                        <form id="delete-<?= $list['id'] ?>" method="POST" action="<?= url('rebar/delete.php') ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $list['id'] ?>">
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                    onclick="confirmDelete('delete-<?= $list['id'] ?>')">
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
