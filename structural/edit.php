<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';

$user = require_login();
require_access();

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT * FROM structural_estimates WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);
$estimate = $stmt->fetch();

if (!$estimate) {
    flash('danger', 'Structural estimate not found.');
    redirect('structural/index.php');
}

// Fetch existing items
$stmt = $db->prepare('SELECT * FROM structural_estimate_items WHERE estimate_id = ? ORDER BY item_no');
$stmt->execute([$estimate['id']]);
$existingItems = $stmt->fetchAll();

// Default unit map
$categoryUnits = [
    'concrete' => 'cu.m',
    'steel'    => 'kg',
    'formwork' => 'sq.m',
];

$errors = [];

// Handle POST — update estimate
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

    // Parse items
    $categories   = $_POST['item_category'] ?? [];
    $memberNames  = $_POST['item_member_name'] ?? [];
    $quantities   = $_POST['item_quantity'] ?? [];
    $units        = $_POST['item_unit'] ?? [];
    $unitCosts    = $_POST['item_unit_cost'] ?? [];
    $descriptions = $_POST['item_description'] ?? [];

    $items = [];
    $totalConcrete = 0;
    $totalSteel    = 0;
    $totalFormwork = 0;

    for ($i = 0; $i < count($categories); $i++) {
        $cat        = $categories[$i] ?? 'concrete';
        $memberName = trim($memberNames[$i] ?? '');
        $qty        = floatval($quantities[$i] ?? 0);
        $unit       = trim($units[$i] ?? $categoryUnits[$cat] ?? 'cu.m');
        $unitCost   = floatval($unitCosts[$i] ?? 0);
        $amount     = $qty * $unitCost;

        if (!$memberName && $qty <= 0) continue;

        if ($cat === 'concrete') $totalConcrete += $amount;
        elseif ($cat === 'steel') $totalSteel += $amount;
        else $totalFormwork += $amount;

        $items[] = [
            'item_no'     => count($items) + 1,
            'category'    => $cat,
            'member_name' => $memberName,
            'quantity'    => $qty,
            'unit'        => $unit,
            'unit_cost'   => $unitCost,
            'amount'      => $amount,
            'description' => trim($descriptions[$i] ?? ''),
        ];
    }

    if (empty($items)) $errors[] = 'At least one item is required.';

    if (!$errors) {
        $subtotal          = $totalConcrete + $totalSteel + $totalFormwork;
        $contingencyAmount = $subtotal * ($contingency_percentage / 100);
        $grandTotal        = $subtotal + $contingencyAmount;

        $db->beginTransaction();
        try {
            $stmt = $db->prepare('UPDATE structural_estimates SET title=?, project_name=?, location=?, prepared_by=?, checked_by=?, date_prepared=?, contingency_percentage=?, total_concrete=?, total_steel=?, total_formwork=?, subtotal=?, contingency_amount=?, grand_total=?, status=? WHERE id=? AND user_id=?');
            $stmt->execute([
                $title, $project_name ?: null, $location ?: null,
                $prepared_by ?: null, $checked_by ?: null, $date_prepared ?: null,
                $contingency_percentage, $totalConcrete, $totalSteel, $totalFormwork,
                $subtotal, $contingencyAmount, $grandTotal, $status,
                $estimate['id'], $user['id']
            ]);

            // Delete old items and re-insert
            $db->prepare('DELETE FROM structural_estimate_items WHERE estimate_id = ?')->execute([$estimate['id']]);

            $itemStmt = $db->prepare('INSERT INTO structural_estimate_items (estimate_id, item_no, category, member_name, quantity, unit, unit_cost, amount, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            foreach ($items as $item) {
                $itemStmt->execute([
                    $estimate['id'], $item['item_no'], $item['category'], $item['member_name'],
                    $item['quantity'], $item['unit'], $item['unit_cost'],
                    $item['amount'], $item['description'] ?: null
                ]);
            }

            $db->commit();
            flash('success', 'Structural Estimate updated successfully.');
            redirect('structural/view.php?id=' . $estimate['id']);
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Failed to update. Please try again.';
        }
    }
}

$pageTitle = 'Edit: ' . $estimate['title'];
$extraJs = ['structural.js'];
require __DIR__ . '/../includes/header.php';

// Use POST data if available, else existing estimate data
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

$categories = ['concrete', 'steel', 'formwork'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('structural/view.php?id=' . $estimate['id']) ?>" class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Back to Estimate
        </a>
        <h5 class="fw-bold mt-1 mb-0">Edit Structural Estimate</h5>
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
                            <th style="width:130px">Category</th>
                            <th>Member Name</th>
                            <th style="width:100px">Qty</th>
                            <th style="width:80px">Unit</th>
                            <th style="width:130px">Unit Cost (₱)</th>
                            <th style="width:140px">Amount (₱)</th>
                            <th>Description</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <?php foreach ($formItems as $idx => $item): ?>
                        <tr class="item-row">
                            <td class="row-number"><?= $idx + 1 ?></td>
                            <td>
                                <select name="item_category[]" class="form-select form-select-sm category-input">
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat ?>" <?= $item['category'] === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="item_member_name[]" class="form-control form-control-sm" value="<?= h($item['member_name']) ?>"></td>
                            <td><input type="number" name="item_quantity[]" class="form-control form-control-sm qty-input" step="0.001" min="0" value="<?= $item['quantity'] ?>"></td>
                            <td><input type="text" name="item_unit[]" class="form-control form-control-sm unit-input" value="<?= h($item['unit']) ?>" readonly></td>
                            <td><input type="number" name="item_unit_cost[]" class="form-control form-control-sm unit-cost-input" step="0.01" min="0" value="<?= $item['unit_cost'] ?>"></td>
                            <td><input type="text" class="form-control form-control-sm amount-display" readonly value="<?= number_format($item['amount'], 2, '.', '') ?>"></td>
                            <td><input type="text" name="item_description[]" class="form-control form-control-sm" value="<?= h($item['description'] ?? '') ?>"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($formItems)): ?>
                        <tr class="item-row">
                            <td class="row-number">1</td>
                            <td>
                                <select name="item_category[]" class="form-select form-select-sm category-input">
                                    <option value="concrete">Concrete</option>
                                    <option value="steel">Steel</option>
                                    <option value="formwork">Formwork</option>
                                </select>
                            </td>
                            <td><input type="text" name="item_member_name[]" class="form-control form-control-sm" placeholder="e.g. Column C1"></td>
                            <td><input type="number" name="item_quantity[]" class="form-control form-control-sm qty-input" step="0.001" min="0" value="0"></td>
                            <td><input type="text" name="item_unit[]" class="form-control form-control-sm unit-input" value="cu.m" readonly></td>
                            <td><input type="number" name="item_unit_cost[]" class="form-control form-control-sm unit-cost-input" step="0.01" min="0" value="0"></td>
                            <td><input type="text" class="form-control form-control-sm amount-display" readonly value="0.00"></td>
                            <td><input type="text" name="item_description[]" class="form-control form-control-sm" placeholder="Optional"></td>
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
                            <span class="text-muted">Concrete:</span>
                            <span class="fw-bold" id="totalConcrete">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Steel:</span>
                            <span class="fw-bold" id="totalSteel">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Formwork:</span>
                            <span class="fw-bold" id="totalFormwork">₱0.00</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Subtotal:</span>
                            <span class="fw-bold" id="subtotal">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Contingency (<span id="contingencyLabel">10</span>%):</span>
                            <span class="fw-bold" id="contingencyAmount">₱0.00</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold fs-5">Grand Total:</span>
                            <span class="fw-bold fs-5 text-primary" id="grandTotal">₱0.00</span>
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
        <a href="<?= url('structural/view.php?id=' . $estimate['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
