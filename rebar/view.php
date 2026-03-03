<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';

$user = require_login();

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT * FROM rebar_lists WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);
$list = $stmt->fetch();

if (!$list) {
    flash('danger', 'Rebar list not found.');
    redirect('rebar/index.php');
}

// Fetch items
$stmt = $db->prepare('SELECT * FROM rebar_items WHERE rebar_list_id = ? ORDER BY item_no');
$stmt->execute([$list['id']]);
$items = $stmt->fetchAll();

$pageTitle = $list['title'];
require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('rebar/index.php') ?>" class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Back to Rebar Lists
        </a>
        <h5 class="fw-bold mt-1 mb-0"><?= h($list['title']) ?></h5>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('rebar/edit.php?id=' . $list['id']) ?>" class="btn btn-warning btn-sm">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <a href="<?= url('rebar/export.php?id=' . $list['id']) ?>" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel me-1"></i> Export
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print
        </button>
    </div>
</div>

<!-- Info -->
<div class="card card-custom mb-4">
    <div class="card-body">
        <div class="row g-3">
            <?php if ($list['project_name']): ?>
            <div class="col-md-6">
                <span class="text-muted small">Project Name</span>
                <p class="mb-0 fw-bold"><?= h($list['project_name']) ?></p>
            </div>
            <?php endif; ?>
            <?php if ($list['structural_member']): ?>
            <div class="col-md-6">
                <span class="text-muted small">Structural Member</span>
                <p class="mb-0 fw-bold"><?= h($list['structural_member']) ?></p>
            </div>
            <?php endif; ?>
            <div class="col-md-3">
                <span class="text-muted small">Prepared By</span>
                <p class="mb-0 fw-bold"><?= h($list['prepared_by'] ?: '-') ?></p>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Checked By</span>
                <p class="mb-0 fw-bold"><?= h($list['checked_by'] ?: '-') ?></p>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Date Prepared</span>
                <p class="mb-0 fw-bold"><?= $list['date_prepared'] ? date('M d, Y', strtotime($list['date_prepared'])) : '-' ?></p>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Status</span>
                <p class="mb-0">
                    <span class="badge bg-<?= $list['status'] === 'final' ? 'success' : 'secondary' ?>">
                        <?= ucfirst($list['status']) ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Items Table -->
<div class="card card-custom mb-4">
    <div class="card-header"><h6 class="fw-bold mb-0">Bar Items</h6></div>
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th style="width:50px">#</th>
                    <th>Bar Size</th>
                    <th class="text-end">No. of Pieces</th>
                    <th class="text-end">Length/pc (m)</th>
                    <th class="text-end">Total Length (m)</th>
                    <th class="text-end">Wt/m (kg)</th>
                    <th class="text-end">Total Weight (kg)</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $item['item_no'] ?></td>
                    <td><?= h($item['bar_size']) ?></td>
                    <td class="text-end"><?= $item['no_of_pieces'] ?></td>
                    <td class="text-end"><?= fmt($item['length_per_pc'], 3) ?></td>
                    <td class="text-end"><?= fmt($item['total_length'], 3) ?></td>
                    <td class="text-end"><?= number_format($item['weight_per_meter'], 4) ?></td>
                    <td class="text-end fw-bold"><?= fmt($item['total_weight'], 3) ?></td>
                    <td><?= h($item['description'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Total Weight -->
<div class="card card-custom">
    <div class="card-body">
        <div class="row justify-content-end">
            <div class="col-md-5">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Items:</span>
                    <span class="fw-bold"><?= count($items) ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold fs-5">Total Weight:</span>
                    <span class="fw-bold fs-5 text-primary"><?= fmt($list['total_weight'], 3) ?> kg</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
