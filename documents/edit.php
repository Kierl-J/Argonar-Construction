<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';

$user = require_login();
require_access();

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
$existingItems = $stmt->fetchAll();

$docTypes = [
    'scope_of_work' => [
        'label' => 'Scope of Work',
        'fields' => [
            'contractor'       => ['label' => 'Contractor', 'type' => 'text'],
            'client'           => ['label' => 'Client', 'type' => 'text'],
            'scope_description'=> ['label' => 'Scope Description', 'type' => 'textarea'],
            'signatory_1_name' => ['label' => 'Signatory 1 Name', 'type' => 'text'],
            'signatory_1_title'=> ['label' => 'Signatory 1 Title', 'type' => 'text'],
            'signatory_2_name' => ['label' => 'Signatory 2 Name', 'type' => 'text'],
            'signatory_2_title'=> ['label' => 'Signatory 2 Title', 'type' => 'text'],
        ],
        'item_columns' => ['description', 'remarks'],
    ],
    'material_requisition' => [
        'label' => 'Material Requisition',
        'fields' => [
            'requested_by' => ['label' => 'Requested By', 'type' => 'text'],
            'date_needed'  => ['label' => 'Date Needed', 'type' => 'date'],
            'approved_by'  => ['label' => 'Approved By', 'type' => 'text'],
        ],
        'item_columns' => ['description', 'quantity', 'unit', 'remarks'],
    ],
    'progress_report' => [
        'label' => 'Progress Report',
        'fields' => [
            'report_date'       => ['label' => 'Report Date', 'type' => 'date'],
            'period_from'       => ['label' => 'Period From', 'type' => 'date'],
            'period_to'         => ['label' => 'Period To', 'type' => 'date'],
            'prepared_by'       => ['label' => 'Prepared By', 'type' => 'text'],
            'weather_conditions'=> ['label' => 'Weather Conditions', 'type' => 'text'],
            'issues'            => ['label' => 'Issues / Notes', 'type' => 'textarea'],
        ],
        'item_columns' => ['description', 'percentage_complete', 'remarks'],
    ],
    'change_order' => [
        'label' => 'Change Order',
        'fields' => [
            'change_order_no'        => ['label' => 'Change Order No.', 'type' => 'text'],
            'date'                   => ['label' => 'Date', 'type' => 'date'],
            'reason'                 => ['label' => 'Reason for Change', 'type' => 'textarea'],
            'original_contract_amount'=> ['label' => 'Original Contract Amount', 'type' => 'number'],
            'revised_amount'         => ['label' => 'Revised Amount', 'type' => 'number'],
            'approved_by'            => ['label' => 'Approved By', 'type' => 'text'],
        ],
        'item_columns' => ['description', 'quantity', 'unit', 'unit_cost', 'amount'],
    ],
];

$type = $document['doc_type'];
$config = $docTypes[$type];
$docData = json_decode($document['data'] ?? '{}', true) ?: [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $title        = trim($_POST['title'] ?? '');
    $project_name = trim($_POST['project_name'] ?? '');
    $status       = ($_POST['status'] ?? 'draft') === 'final' ? 'final' : 'draft';

    if (!$title) $errors[] = 'Title is required.';

    $data = [];
    foreach ($config['fields'] as $key => $fieldConfig) {
        $data[$key] = trim($_POST["data_{$key}"] ?? '');
    }

    $itemDescs      = $_POST['item_description'] ?? [];
    $itemQtys       = $_POST['item_quantity'] ?? [];
    $itemUnits      = $_POST['item_unit'] ?? [];
    $itemUnitCosts  = $_POST['item_unit_cost'] ?? [];
    $itemAmounts    = $_POST['item_amount'] ?? [];
    $itemPcts       = $_POST['item_percentage_complete'] ?? [];
    $itemRemarks    = $_POST['item_remarks'] ?? [];

    $items = [];
    for ($i = 0; $i < count($itemDescs); $i++) {
        $desc = trim($itemDescs[$i] ?? '');
        if (!$desc) continue;

        $item = ['item_no' => count($items) + 1, 'description' => $desc];

        if (in_array('quantity', $config['item_columns']))
            $item['quantity'] = floatval($itemQtys[$i] ?? 0);
        if (in_array('unit', $config['item_columns']))
            $item['unit'] = trim($itemUnits[$i] ?? '');
        if (in_array('unit_cost', $config['item_columns']))
            $item['unit_cost'] = floatval($itemUnitCosts[$i] ?? 0);
        if (in_array('amount', $config['item_columns']))
            $item['amount'] = ($item['quantity'] ?? 0) * ($item['unit_cost'] ?? 0);
        if (in_array('percentage_complete', $config['item_columns']))
            $item['percentage_complete'] = floatval($itemPcts[$i] ?? 0);
        if (in_array('remarks', $config['item_columns']))
            $item['remarks'] = trim($itemRemarks[$i] ?? '');

        $items[] = $item;
    }

    if (empty($items)) $errors[] = 'At least one item is required.';

    if (!$errors) {
        $db->beginTransaction();
        try {
            $stmt = $db->prepare('UPDATE documents SET title=?, project_name=?, data=?, status=? WHERE id=? AND user_id=?');
            $stmt->execute([
                $title, $project_name ?: null, json_encode($data), $status,
                $document['id'], $user['id']
            ]);

            $db->prepare('DELETE FROM document_items WHERE document_id = ?')->execute([$document['id']]);

            $itemStmt = $db->prepare('INSERT INTO document_items (document_id, item_no, description, quantity, unit, unit_cost, amount, percentage_complete, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            foreach ($items as $item) {
                $itemStmt->execute([
                    $document['id'], $item['item_no'], $item['description'],
                    $item['quantity'] ?? null, $item['unit'] ?? null,
                    $item['unit_cost'] ?? null, $item['amount'] ?? null,
                    $item['percentage_complete'] ?? null, $item['remarks'] ?? null
                ]);
            }

            $db->commit();
            flash('success', $config['label'] . ' updated successfully.');
            redirect('documents/view.php?id=' . $document['id']);
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Failed to update. Please try again.';
        }
    }
}

$pageTitle = 'Edit: ' . $document['title'];
$extraJs = ['documents.js'];
require __DIR__ . '/../includes/header.php';

// Use POST data if available, else existing document data
$formTitle       = $_POST['title'] ?? $document['title'];
$formProjectName = $_POST['project_name'] ?? $document['project_name'];
$formStatus      = $_POST['status'] ?? $document['status'];
$formData        = $_SERVER['REQUEST_METHOD'] === 'POST' ? [] : $docData;
$formItems       = $_SERVER['REQUEST_METHOD'] === 'POST' ? [] : $existingItems;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('documents/view.php?id=' . $document['id']) ?>" class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Back to Document
        </a>
        <h5 class="fw-bold mt-1 mb-0">Edit <?= h($config['label']) ?></h5>
    </div>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger">
    <?php foreach ($errors as $e): ?>
    <div><?= h($e) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST">
    <?= csrf_field() ?>

    <!-- Details -->
    <div class="card card-custom mb-4">
        <div class="card-header"><h6 class="fw-bold mb-0">Details</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title" value="<?= h($formTitle) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Project Name</label>
                    <input type="text" class="form-control" name="project_name" value="<?= h($formProjectName) ?>">
                </div>
                <?php foreach ($config['fields'] as $key => $field): ?>
                <?php $val = $_POST["data_{$key}"] ?? ($formData[$key] ?? ''); ?>
                <div class="col-md-<?= $field['type'] === 'textarea' ? '12' : '6' ?>">
                    <label class="form-label fw-bold small"><?= h($field['label']) ?></label>
                    <?php if ($field['type'] === 'textarea'): ?>
                    <textarea class="form-control" name="data_<?= $key ?>" rows="3"><?= h($val) ?></textarea>
                    <?php elseif ($field['type'] === 'date'): ?>
                    <input type="date" class="form-control" name="data_<?= $key ?>" value="<?= h($val) ?>">
                    <?php elseif ($field['type'] === 'number'): ?>
                    <input type="number" class="form-control" name="data_<?= $key ?>" step="0.01" value="<?= h($val) ?>">
                    <?php else: ?>
                    <input type="text" class="form-control" name="data_<?= $key ?>" value="<?= h($val) ?>">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Status</label>
                    <select class="form-select" name="status">
                        <option value="draft" <?= $formStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="final" <?= $formStatus === 'final' ? 'selected' : '' ?>>Final</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Items -->
    <div class="card card-custom mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Items</h6>
            <button type="button" class="btn btn-sm btn-primary" id="addRow">
                <i class="fas fa-plus me-1"></i> Add Row
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0" data-doc-type="<?= h($type) ?>">
                    <thead>
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Description</th>
                            <?php if (in_array('quantity', $config['item_columns'])): ?>
                            <th style="width:100px">Qty</th>
                            <?php endif; ?>
                            <?php if (in_array('unit', $config['item_columns'])): ?>
                            <th style="width:80px">Unit</th>
                            <?php endif; ?>
                            <?php if (in_array('unit_cost', $config['item_columns'])): ?>
                            <th style="width:130px">Unit Cost (&#8369;)</th>
                            <?php endif; ?>
                            <?php if (in_array('amount', $config['item_columns'])): ?>
                            <th style="width:140px">Amount (&#8369;)</th>
                            <?php endif; ?>
                            <?php if (in_array('percentage_complete', $config['item_columns'])): ?>
                            <th style="width:100px">% Complete</th>
                            <?php endif; ?>
                            <?php if (in_array('remarks', $config['item_columns'])): ?>
                            <th>Remarks</th>
                            <?php endif; ?>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <?php foreach ($formItems as $idx => $item): ?>
                        <tr class="item-row">
                            <td class="row-number"><?= $idx + 1 ?></td>
                            <td><input type="text" name="item_description[]" class="form-control form-control-sm" value="<?= h($item['description']) ?>" required></td>
                            <?php if (in_array('quantity', $config['item_columns'])): ?>
                            <td><input type="number" name="item_quantity[]" class="form-control form-control-sm qty-input" step="0.001" min="0" value="<?= $item['quantity'] ?? 0 ?>"></td>
                            <?php endif; ?>
                            <?php if (in_array('unit', $config['item_columns'])): ?>
                            <td><input type="text" name="item_unit[]" class="form-control form-control-sm" value="<?= h($item['unit'] ?? 'lot') ?>"></td>
                            <?php endif; ?>
                            <?php if (in_array('unit_cost', $config['item_columns'])): ?>
                            <td><input type="number" name="item_unit_cost[]" class="form-control form-control-sm unit-cost-input" step="0.01" min="0" value="<?= $item['unit_cost'] ?? 0 ?>"></td>
                            <?php endif; ?>
                            <?php if (in_array('amount', $config['item_columns'])): ?>
                            <td><input type="text" name="item_amount[]" class="form-control form-control-sm amount-display" readonly value="<?= number_format($item['amount'] ?? 0, 2, '.', '') ?>"></td>
                            <?php endif; ?>
                            <?php if (in_array('percentage_complete', $config['item_columns'])): ?>
                            <td><input type="number" name="item_percentage_complete[]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="<?= $item['percentage_complete'] ?? 0 ?>"></td>
                            <?php endif; ?>
                            <?php if (in_array('remarks', $config['item_columns'])): ?>
                            <td><input type="text" name="item_remarks[]" class="form-control form-control-sm" value="<?= h($item['remarks'] ?? '') ?>"></td>
                            <?php endif; ?>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($formItems)): ?>
                        <tr class="item-row">
                            <td class="row-number">1</td>
                            <td><input type="text" name="item_description[]" class="form-control form-control-sm" placeholder="Description" required></td>
                            <?php if (in_array('quantity', $config['item_columns'])): ?>
                            <td><input type="number" name="item_quantity[]" class="form-control form-control-sm qty-input" step="0.001" min="0" value="0"></td>
                            <?php endif; ?>
                            <?php if (in_array('unit', $config['item_columns'])): ?>
                            <td><input type="text" name="item_unit[]" class="form-control form-control-sm" value="lot"></td>
                            <?php endif; ?>
                            <?php if (in_array('unit_cost', $config['item_columns'])): ?>
                            <td><input type="number" name="item_unit_cost[]" class="form-control form-control-sm unit-cost-input" step="0.01" min="0" value="0"></td>
                            <?php endif; ?>
                            <?php if (in_array('amount', $config['item_columns'])): ?>
                            <td><input type="text" name="item_amount[]" class="form-control form-control-sm amount-display" readonly value="0.00"></td>
                            <?php endif; ?>
                            <?php if (in_array('percentage_complete', $config['item_columns'])): ?>
                            <td><input type="number" name="item_percentage_complete[]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="0"></td>
                            <?php endif; ?>
                            <?php if (in_array('remarks', $config['item_columns'])): ?>
                            <td><input type="text" name="item_remarks[]" class="form-control form-control-sm" placeholder="Optional"></td>
                            <?php endif; ?>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Update Document
        </button>
        <a href="<?= url('documents/view.php?id=' . $document['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
