<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';

$user = require_login();

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT * FROM boqs WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);
$boq = $stmt->fetch();

if (!$boq) {
    flash('danger', 'BOQ not found.');
    redirect('boq/index.php');
}

// Fetch items
$stmt = $db->prepare('SELECT * FROM boq_items WHERE boq_id = ? ORDER BY item_no');
$stmt->execute([$boq['id']]);
$items = $stmt->fetchAll();

$pageTitle = $boq['title'];
require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('boq/index.php') ?>" class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Back to BOQs
        </a>
        <h5 class="fw-bold mt-1 mb-0"><?= h($boq['title']) ?></h5>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('boq/edit.php?id=' . $boq['id']) ?>" class="btn btn-warning btn-sm">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <a href="<?= url('boq/export.php?id=' . $boq['id']) ?>" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel me-1"></i> Export
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print
        </button>
    </div>
</div>

<!-- BOQ Info -->
<div class="card card-custom mb-4">
    <div class="card-body">
        <div class="row g-3">
            <?php if ($boq['description']): ?>
            <div class="col-12">
                <span class="text-muted small">Description</span>
                <p class="mb-0"><?= h($boq['description']) ?></p>
            </div>
            <?php endif; ?>
            <div class="col-md-3">
                <span class="text-muted small">Prepared By</span>
                <p class="mb-0 fw-bold"><?= h($boq['prepared_by'] ?: '-') ?></p>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Checked By</span>
                <p class="mb-0 fw-bold"><?= h($boq['checked_by'] ?: '-') ?></p>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Date Prepared</span>
                <p class="mb-0 fw-bold"><?= $boq['date_prepared'] ? date('M d, Y', strtotime($boq['date_prepared'])) : '-' ?></p>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Status</span>
                <p class="mb-0">
                    <span class="badge bg-<?= $boq['status'] === 'final' ? 'success' : 'secondary' ?>">
                        <?= ucfirst($boq['status']) ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Items Table -->
<div class="card card-custom mb-4">
    <div class="card-header"><h6 class="fw-bold mb-0">Line Items</h6></div>
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th style="width:50px">#</th>
                    <th>Description</th>
                    <th style="width:80px">Unit</th>
                    <th style="width:100px" class="text-end">Quantity</th>
                    <th style="width:120px" class="text-end">Unit Cost</th>
                    <th style="width:130px" class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $item['item_no'] ?></td>
                    <td><?= h($item['description']) ?></td>
                    <td><?= h($item['unit']) ?></td>
                    <td class="text-end"><?= fmt($item['quantity'], 3) ?></td>
                    <td class="text-end"><?= currency($item['unit_cost']) ?></td>
                    <td class="text-end fw-bold"><?= currency($item['amount']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Totals -->
<div class="card card-custom">
    <div class="card-body">
        <div class="row justify-content-end">
            <div class="col-md-5">
                <?php
                $markup = $boq['total_amount'] * ($boq['markup_percentage'] / 100);
                $after_markup = $boq['total_amount'] + $markup;
                $vat = $after_markup * ($boq['vat_percentage'] / 100);
                ?>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <span class="fw-bold"><?= currency($boq['total_amount']) ?></span>
                </div>
                <?php if ($boq['markup_percentage'] > 0): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Markup (<?= fmt($boq['markup_percentage']) ?>%):</span>
                    <span class="fw-bold"><?= currency($markup) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($boq['vat_percentage'] > 0): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">VAT (<?= fmt($boq['vat_percentage']) ?>%):</span>
                    <span class="fw-bold"><?= currency($vat) ?></span>
                </div>
                <?php endif; ?>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold fs-5">Grand Total:</span>
                    <span class="fw-bold fs-5 text-primary"><?= currency($boq['grand_total']) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
