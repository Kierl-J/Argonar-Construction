<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';

$user = require_login();

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT * FROM documents WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);
$document = $stmt->fetch();

if (!$document) {
    flash('danger', 'Document not found.');
    redirect('documents/index.php');
}

$stmt = $db->prepare('SELECT * FROM document_items WHERE document_id = ? ORDER BY item_no');
$stmt->execute([$document['id']]);
$items = $stmt->fetchAll();

$docTypeLabels = [
    'scope_of_work'        => 'Scope of Work',
    'material_requisition'  => 'Material Requisition',
    'progress_report'       => 'Progress Report',
    'change_order'          => 'Change Order',
];

$docTypeBadges = [
    'scope_of_work'        => 'bg-primary',
    'material_requisition'  => 'bg-info',
    'progress_report'       => 'bg-warning text-dark',
    'change_order'          => 'bg-danger',
];

$fieldLabels = [
    'contractor'             => 'Contractor',
    'client'                 => 'Client',
    'scope_description'      => 'Scope Description',
    'signatory_1_name'       => 'Signatory 1 Name',
    'signatory_1_title'      => 'Signatory 1 Title',
    'signatory_2_name'       => 'Signatory 2 Name',
    'signatory_2_title'      => 'Signatory 2 Title',
    'requested_by'           => 'Requested By',
    'date_needed'            => 'Date Needed',
    'approved_by'            => 'Approved By',
    'report_date'            => 'Report Date',
    'period_from'            => 'Period From',
    'period_to'              => 'Period To',
    'prepared_by'            => 'Prepared By',
    'weather_conditions'     => 'Weather Conditions',
    'issues'                 => 'Issues / Notes',
    'change_order_no'        => 'Change Order No.',
    'date'                   => 'Date',
    'reason'                 => 'Reason for Change',
    'original_contract_amount'=> 'Original Contract Amount',
    'revised_amount'         => 'Revised Amount',
];

// Item columns per type
$itemColumnConfig = [
    'scope_of_work'        => ['description', 'remarks'],
    'material_requisition'  => ['description', 'quantity', 'unit', 'remarks'],
    'progress_report'       => ['description', 'percentage_complete', 'remarks'],
    'change_order'          => ['description', 'quantity', 'unit', 'unit_cost', 'amount'],
];

$type = $document['doc_type'];
$docData = json_decode($document['data'] ?? '{}', true) ?: [];
$columns = $itemColumnConfig[$type] ?? ['description'];

$pageTitle = $document['title'];
require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('documents/index.php') ?>" class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Back to Documents
        </a>
        <h5 class="fw-bold mt-1 mb-0"><?= h($document['title']) ?></h5>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('documents/edit.php?id=' . $document['id']) ?>" class="btn btn-warning btn-sm">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <a href="<?= url('documents/export.php?id=' . $document['id']) ?>" class="btn btn-success btn-sm">
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
            <div class="col-md-6">
                <span class="text-muted small">Document Type</span>
                <p class="mb-0">
                    <span class="badge <?= $docTypeBadges[$type] ?? 'bg-secondary' ?>">
                        <?= $docTypeLabels[$type] ?? $type ?>
                    </span>
                </p>
            </div>
            <?php if ($document['project_name']): ?>
            <div class="col-md-6">
                <span class="text-muted small">Project Name</span>
                <p class="mb-0 fw-bold"><?= h($document['project_name']) ?></p>
            </div>
            <?php endif; ?>
            <div class="col-md-3">
                <span class="text-muted small">Status</span>
                <p class="mb-0">
                    <span class="badge bg-<?= $document['status'] === 'final' ? 'success' : 'secondary' ?>">
                        <?= ucfirst($document['status']) ?>
                    </span>
                </p>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Last Updated</span>
                <p class="mb-0 fw-bold"><?= date('M d, Y', strtotime($document['updated_at'])) ?></p>
            </div>
            <?php foreach ($docData as $key => $val): ?>
            <?php if ($val): ?>
            <div class="col-md-6">
                <span class="text-muted small"><?= h($fieldLabels[$key] ?? ucfirst(str_replace('_', ' ', $key))) ?></span>
                <p class="mb-0 fw-bold"><?= nl2br(h($val)) ?></p>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Items Table -->
<div class="card card-custom">
    <div class="card-header"><h6 class="fw-bold mb-0">Items</h6></div>
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th style="width:50px">#</th>
                    <th>Description</th>
                    <?php if (in_array('quantity', $columns)): ?><th class="text-end">Qty</th><?php endif; ?>
                    <?php if (in_array('unit', $columns)): ?><th>Unit</th><?php endif; ?>
                    <?php if (in_array('unit_cost', $columns)): ?><th class="text-end">Unit Cost (&#8369;)</th><?php endif; ?>
                    <?php if (in_array('amount', $columns)): ?><th class="text-end">Amount (&#8369;)</th><?php endif; ?>
                    <?php if (in_array('percentage_complete', $columns)): ?><th class="text-end">% Complete</th><?php endif; ?>
                    <?php if (in_array('remarks', $columns)): ?><th>Remarks</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $item['item_no'] ?></td>
                    <td><?= h($item['description']) ?></td>
                    <?php if (in_array('quantity', $columns)): ?><td class="text-end"><?= fmt($item['quantity'], 3) ?></td><?php endif; ?>
                    <?php if (in_array('unit', $columns)): ?><td><?= h($item['unit'] ?? '') ?></td><?php endif; ?>
                    <?php if (in_array('unit_cost', $columns)): ?><td class="text-end"><?= fmt($item['unit_cost']) ?></td><?php endif; ?>
                    <?php if (in_array('amount', $columns)): ?><td class="text-end fw-bold"><?= fmt($item['amount']) ?></td><?php endif; ?>
                    <?php if (in_array('percentage_complete', $columns)): ?><td class="text-end"><?= number_format($item['percentage_complete'], 2) ?>%</td><?php endif; ?>
                    <?php if (in_array('remarks', $columns)): ?><td><?= h($item['remarks'] ?? '') ?></td><?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if ($type === 'change_order'): ?>
                <tr class="table-light">
                    <td colspan="4" class="text-end fw-bold">Total:</td>
                    <td class="text-end fw-bold">&#8369;<?= fmt(array_sum(array_column($items, 'amount'))) ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
