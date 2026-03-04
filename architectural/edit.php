<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';

$user = require_login();
require_access();

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
$existingItems = $stmt->fetchAll();

$categoryUnits = [
    'masonry'       => 'sq.m',
    'tiling'        => 'sq.m',
    'painting'      => 'sq.m',
    'roofing'       => 'sq.m',
    'plastering'    => 'sq.m',
    'ceiling'       => 'sq.m',
    'doors_windows' => 'pcs',
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

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $title                  = trim($_POST['title'] ?? '');
    $project_name           = trim($_POST['project_name'] ?? '');
    $location               = trim($_POST['location'] ?? '');
    $prepared_by            = trim($_POST['prepared_by'] ?? '');
    $checked_by             = trim($_POST['checked_by'] ?? '');
    $date_prepared          = $_POST['date_prepared'] ?? '';
    $contingency_percentage = floatval($_POST['contingency_percentage'] ?? 10);
    $status                 = ($_POST['status'] ?? 'draft') === 'final' ? 'final' : 'draft';

    if (!$title) $errors[] = 'Title is required.';

    $categories    = $_POST['item_category'] ?? [];
    $descriptions  = $_POST['item_description'] ?? [];
    $quantities    = $_POST['item_quantity'] ?? [];
    $units         = $_POST['item_unit'] ?? [];
    $unitCosts     = $_POST['item_unit_cost'] ?? [];
    $remarksArr    = $_POST['item_remarks'] ?? [];

    $items = [];
    $totals = array_fill_keys(array_keys($categoryUnits), 0);

    for ($i = 0; $i < count($categories); $i++) {
        $cat  = $categories[$i] ?? 'masonry';
        $desc = trim($descriptions[$i] ?? '');
        $qty  = floatval($quantities[$i] ?? 0);
        $unit = trim($units[$i] ?? $categoryUnits[$cat] ?? 'sq.m');
        $uc   = floatval($unitCosts[$i] ?? 0);
        $amt  = $qty * $uc;

        if (!$desc && $qty <= 0) continue;

        $totals[$cat] += $amt;

        $items[] = [
            'item_no'     => count($items) + 1,
            'category'    => $cat,
            'description' => $desc,
            'quantity'    => $qty,
            'unit'        => $unit,
            'unit_cost'   => $uc,
            'amount'      => $amt,
            'remarks'     => trim($remarksArr[$i] ?? ''),
        ];
    }

    if (empty($items)) $errors[] = 'At least one item is required.';

    if (!$errors) {
        $subtotal          = array_sum($totals);
        $contingencyAmount = $subtotal * ($contingency_percentage / 100);
        $grandTotal        = $subtotal + $contingencyAmount;

        $db->beginTransaction();
        try {
            $stmt = $db->prepare('UPDATE architectural_estimates SET title=?, project_name=?, location=?, prepared_by=?, checked_by=?, date_prepared=?, contingency_percentage=?, total_masonry=?, total_tiling=?, total_painting=?, total_roofing=?, total_plastering=?, total_ceiling=?, total_doors_windows=?, subtotal=?, contingency_amount=?, grand_total=?, status=? WHERE id=? AND user_id=?');
            $stmt->execute([
                $title, $project_name ?: null, $location ?: null,
                $prepared_by ?: null, $checked_by ?: null, $date_prepared ?: null,
                $contingency_percentage,
                $totals['masonry'], $totals['tiling'], $totals['painting'], $totals['roofing'],
                $totals['plastering'], $totals['ceiling'], $totals['doors_windows'],
                $subtotal, $contingencyAmount, $grandTotal, $status,
                $estimate['id'], $user['id']
            ]);

            $db->prepare('DELETE FROM architectural_estimate_items WHERE estimate_id = ?')->execute([$estimate['id']]);

            $itemStmt = $db->prepare('INSERT INTO architectural_estimate_items (estimate_id, item_no, category, description, quantity, unit, unit_cost, amount, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            foreach ($items as $item) {
                $itemStmt->execute([
                    $estimate['id'], $item['item_no'], $item['category'], $item['description'],
                    $item['quantity'], $item['unit'], $item['unit_cost'],
                    $item['amount'], $item['remarks'] ?: null
                ]);
            }

            $db->commit();
            flash('success', 'Architectural Estimate updated successfully.');
            redirect('architectural/view.php?id=' . $estimate['id']);
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Failed to update. Please try again.';
        }
    }
}

$pageTitle = 'Edit: ' . $estimate['title'];
$extraJs = ['architectural.js'];
require __DIR__ . '/../includes/header.php';

$formData = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : [
    'title'                  => $estimate['title'],
    'project_name'           => $estimate['project_name'],
    'location'               => $estimate['location'],
    'prepared_by'            => $estimate['prepared_by'],
    'checked_by'             => $estimate['checked_by'],
    'date_prepared'          => $estimate['date_prepared'],
    'contingency_percentage' => $estimate['contingency_percentage'],
    'status'                 => $estimate['status'],
];
$formItems = $_SERVER['REQUEST_METHOD'] === 'POST' ? [] : $existingItems;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('architectural/view.php?id=' . $estimate['id']) ?>" class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Back to Estimate
        </a>
        <h5 class="fw-bold mt-1 mb-0">Edit Architectural Estimate</h5>
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
                    <input type="text" class="form-control" name="title" value="<?= h($formData['title'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Project Name</label>
                    <input type="text" class="form-control" name="project_name" value="<?= h($formData['project_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Location</label>
                    <input type="text" class="form-control" name="location" value="<?= h($formData['location'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Date Prepared</label>
                    <input type="date" class="form-control" name="date_prepared" value="<?= h($formData['date_prepared'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Prepared By</label>
                    <input type="text" class="form-control" name="prepared_by" value="<?= h($formData['prepared_by'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Checked By</label>
                    <input type="text" class="form-control" name="checked_by" value="<?= h($formData['checked_by'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Contingency (%)</label>
                    <input type="number" class="form-control" name="contingency_percentage" id="contingencyPct" step="0.01" min="0" max="100" value="<?= h($formData['contingency_percentage'] ?? '10') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Status</label>
                    <select class="form-select" name="status">
                        <option value="draft" <?= ($formData['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="final" <?= ($formData['status'] ?? '') === 'final' ? 'selected' : '' ?>>Final</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Estimate Items -->
    <div class="card card-custom mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Estimate Items</h6>
            <button type="button" class="btn btn-sm btn-primary" id="addRow">
                <i class="fas fa-plus me-1"></i> Add Row
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th style="width:50px">#</th>
                            <th style="width:150px">Category</th>
                            <th>Description</th>
                            <th style="width:100px">Qty</th>
                            <th style="width:80px">Unit</th>
                            <th style="width:130px">Unit Cost (&#8369;)</th>
                            <th style="width:140px">Amount (&#8369;)</th>
                            <th>Remarks</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <?php foreach ($formItems as $idx => $item): ?>
                        <tr class="item-row">
                            <td class="row-number"><?= $idx + 1 ?></td>
                            <td>
                                <select name="item_category[]" class="form-select form-select-sm category-input">
                                    <?php foreach ($categoryLabels as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $item['category'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="item_description[]" class="form-control form-control-sm" value="<?= h($item['description']) ?>"></td>
                            <td><input type="number" name="item_quantity[]" class="form-control form-control-sm qty-input" step="0.001" min="0" value="<?= $item['quantity'] ?>"></td>
                            <td><input type="text" name="item_unit[]" class="form-control form-control-sm unit-input" value="<?= h($item['unit']) ?>" readonly></td>
                            <td><input type="number" name="item_unit_cost[]" class="form-control form-control-sm unit-cost-input" step="0.01" min="0" value="<?= $item['unit_cost'] ?>"></td>
                            <td><input type="text" class="form-control form-control-sm amount-display" readonly value="<?= number_format($item['amount'], 2, '.', '') ?>"></td>
                            <td><input type="text" name="item_remarks[]" class="form-control form-control-sm" value="<?= h($item['remarks'] ?? '') ?>"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($formItems)): ?>
                        <tr class="item-row">
                            <td class="row-number">1</td>
                            <td>
                                <select name="item_category[]" class="form-select form-select-sm category-input">
                                    <?php foreach ($categoryLabels as $val => $label): ?>
                                    <option value="<?= $val ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="item_description[]" class="form-control form-control-sm" placeholder="e.g. CHB Laying"></td>
                            <td><input type="number" name="item_quantity[]" class="form-control form-control-sm qty-input" step="0.001" min="0" value="0"></td>
                            <td><input type="text" name="item_unit[]" class="form-control form-control-sm unit-input" value="sq.m" readonly></td>
                            <td><input type="number" name="item_unit_cost[]" class="form-control form-control-sm unit-cost-input" step="0.01" min="0" value="0"></td>
                            <td><input type="text" class="form-control form-control-sm amount-display" readonly value="0.00"></td>
                            <td><input type="text" name="item_remarks[]" class="form-control form-control-sm" placeholder="Optional"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="card card-custom mb-4">
        <div class="card-header"><h6 class="fw-bold mb-0">Cost Summary</h6></div>
        <div class="card-body">
            <div class="row justify-content-end">
                <div class="col-md-6">
                    <div class="bg-light rounded p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Masonry:</span>
                            <span class="fw-bold" id="totalMasonry">&#8369;0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tiling:</span>
                            <span class="fw-bold" id="totalTiling">&#8369;0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Painting:</span>
                            <span class="fw-bold" id="totalPainting">&#8369;0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Roofing:</span>
                            <span class="fw-bold" id="totalRoofing">&#8369;0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Plastering:</span>
                            <span class="fw-bold" id="totalPlastering">&#8369;0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Ceiling:</span>
                            <span class="fw-bold" id="totalCeiling">&#8369;0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Doors & Windows:</span>
                            <span class="fw-bold" id="totalDoorsWindows">&#8369;0.00</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Subtotal:</span>
                            <span class="fw-bold" id="subtotal">&#8369;0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Contingency (<span id="contingencyLabel">10</span>%):</span>
                            <span class="fw-bold" id="contingencyAmount">&#8369;0.00</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold fs-5">Grand Total:</span>
                            <span class="fw-bold fs-5 text-primary" id="grandTotal">&#8369;0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Update Estimate
        </button>
        <a href="<?= url('architectural/view.php?id=' . $estimate['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
