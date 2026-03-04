<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';

$user = require_login();

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT * FROM architectural_estimates WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);
$estimate = $stmt->fetch();

if (!$estimate) {
    flash('danger', 'Architectural estimate not found.');
    redirect('architectural/index.php');
}

$stmt = $db->prepare('SELECT * FROM architectural_estimate_items WHERE estimate_id = ? ORDER BY item_no');
$stmt->execute([$estimate['id']]);
$items = $stmt->fetchAll();

$badgeColors = [
    'masonry'       => 'bg-primary',
    'tiling'        => 'bg-info',
    'painting'      => 'bg-warning text-dark',
    'roofing'       => 'bg-danger',
    'plastering'    => 'bg-secondary',
    'ceiling'       => 'bg-dark',
    'doors_windows' => 'bg-success',
];

$categoryLabels = [
    'masonry'       => 'Masonry',
    'tiling'        => 'Tiling',
    'painting'      => 'Painting',
    'roofing'       => 'Roofing',
    'plastering'    => 'Plastering',
    'ceiling'       => 'Ceiling',
    'doors_windows' => 'Doors & Windows',
];

$pageTitle = $estimate['title'];
require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('architectural/index.php') ?>" class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Back to Estimates
        </a>
        <h5 class="fw-bold mt-1 mb-0"><?= h($estimate['title']) ?></h5>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('architectural/edit.php?id=' . $estimate['id']) ?>" class="btn btn-warning btn-sm">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <a href="<?= url('architectural/export.php?id=' . $estimate['id']) ?>" class="btn btn-success btn-sm">
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
            <?php if ($estimate['project_name']): ?>
            <div class="col-md-6">
                <span class="text-muted small">Project Name</span>
                <p class="mb-0 fw-bold"><?= h($estimate['project_name']) ?></p>
            </div>
            <?php endif; ?>
            <?php if ($estimate['location']): ?>
            <div class="col-md-6">
                <span class="text-muted small">Location</span>
                <p class="mb-0 fw-bold"><?= h($estimate['location']) ?></p>
            </div>
            <?php endif; ?>
            <div class="col-md-3">
                <span class="text-muted small">Prepared By</span>
                <p class="mb-0 fw-bold"><?= h($estimate['prepared_by'] ?: '-') ?></p>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Checked By</span>
                <p class="mb-0 fw-bold"><?= h($estimate['checked_by'] ?: '-') ?></p>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Date Prepared</span>
                <p class="mb-0 fw-bold"><?= $estimate['date_prepared'] ? date('M d, Y', strtotime($estimate['date_prepared'])) : '-' ?></p>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Status</span>
                <p class="mb-0">
                    <span class="badge bg-<?= $estimate['status'] === 'final' ? 'success' : 'secondary' ?>">
                        <?= ucfirst($estimate['status']) ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Items Table -->
<div class="card card-custom mb-4">
    <div class="card-header"><h6 class="fw-bold mb-0">Estimate Items</h6></div>
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th style="width:50px">#</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th class="text-end">Qty</th>
                    <th>Unit</th>
                    <th class="text-end">Unit Cost (&#8369;)</th>
                    <th class="text-end">Amount (&#8369;)</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $item['item_no'] ?></td>
                    <td><span class="badge <?= $badgeColors[$item['category']] ?? 'bg-secondary' ?>"><?= $categoryLabels[$item['category']] ?? ucfirst($item['category']) ?></span></td>
                    <td><?= h($item['description']) ?></td>
                    <td class="text-end"><?= fmt($item['quantity'], 3) ?></td>
                    <td><?= h($item['unit']) ?></td>
                    <td class="text-end"><?= fmt($item['unit_cost']) ?></td>
                    <td class="text-end fw-bold"><?= fmt($item['amount']) ?></td>
                    <td><?= h($item['remarks'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Cost Summary -->
<div class="card card-custom">
    <div class="card-body">
        <div class="row justify-content-end">
            <div class="col-md-5">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Masonry:</span>
                    <span class="fw-bold">&#8369;<?= fmt($estimate['total_masonry']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tiling:</span>
                    <span class="fw-bold">&#8369;<?= fmt($estimate['total_tiling']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Painting:</span>
                    <span class="fw-bold">&#8369;<?= fmt($estimate['total_painting']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Roofing:</span>
                    <span class="fw-bold">&#8369;<?= fmt($estimate['total_roofing']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Plastering:</span>
                    <span class="fw-bold">&#8369;<?= fmt($estimate['total_plastering']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Ceiling:</span>
                    <span class="fw-bold">&#8369;<?= fmt($estimate['total_ceiling']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Doors & Windows:</span>
                    <span class="fw-bold">&#8369;<?= fmt($estimate['total_doors_windows']) ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold">Subtotal:</span>
                    <span class="fw-bold">&#8369;<?= fmt($estimate['subtotal']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Contingency (<?= number_format($estimate['contingency_percentage'], 2) ?>%):</span>
                    <span class="fw-bold">&#8369;<?= fmt($estimate['contingency_amount']) ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold fs-5">Grand Total:</span>
                    <span class="fw-bold fs-5 text-primary">&#8369;<?= fmt($estimate['grand_total']) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
