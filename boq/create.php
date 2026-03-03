<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';

$user = require_login();
require_access();

$pageTitle = 'Create BOQ';
$errors = [];

// Handle POST — save BOQ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prepared_by = trim($_POST['prepared_by'] ?? '');
    $checked_by  = trim($_POST['checked_by'] ?? '');
    $date_prepared = $_POST['date_prepared'] ?? '';
    $markup_pct  = floatval($_POST['markup_percentage'] ?? 0);
    $vat_pct     = floatval($_POST['vat_percentage'] ?? 12);
    $status      = ($_POST['status'] ?? 'draft') === 'final' ? 'final' : 'draft';

    if (!$title) $errors[] = 'Title is required.';

    // Parse items
    $descriptions = $_POST['item_description'] ?? [];
    $units        = $_POST['item_unit'] ?? [];
    $quantities   = $_POST['item_quantity'] ?? [];
    $unit_costs   = $_POST['item_unit_cost'] ?? [];

    $items = [];
    $subtotal = 0;
    for ($i = 0; $i < count($descriptions); $i++) {
        $desc = trim($descriptions[$i] ?? '');
        if (!$desc) continue;
        $qty  = floatval($quantities[$i] ?? 0);
        $cost = floatval($unit_costs[$i] ?? 0);
        $amt  = $qty * $cost;
        $subtotal += $amt;
        $items[] = [
            'item_no'     => count($items) + 1,
            'description' => $desc,
            'unit'        => $units[$i] ?? 'lot',
            'quantity'    => $qty,
            'unit_cost'   => $cost,
            'amount'      => $amt,
        ];
    }

    if (empty($items)) $errors[] = 'At least one item is required.';

    if (!$errors) {
        $markup = $subtotal * ($markup_pct / 100);
        $after_markup = $subtotal + $markup;
        $vat = $after_markup * ($vat_pct / 100);
        $grand_total = $after_markup + $vat;

        $db->beginTransaction();
        try {
            $stmt = $db->prepare('INSERT INTO boqs (user_id, title, description, prepared_by, checked_by, date_prepared, markup_percentage, vat_percentage, total_amount, grand_total, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $user['id'], $title, $description ?: null, $prepared_by ?: null, $checked_by ?: null,
                $date_prepared ?: null, $markup_pct, $vat_pct, $subtotal, $grand_total, $status
            ]);
            $boq_id = $db->lastInsertId();

            $itemStmt = $db->prepare('INSERT INTO boq_items (boq_id, item_no, description, unit, quantity, unit_cost, amount) VALUES (?, ?, ?, ?, ?, ?, ?)');
            foreach ($items as $item) {
                $itemStmt->execute([
                    $boq_id, $item['item_no'], $item['description'], $item['unit'],
                    $item['quantity'], $item['unit_cost'], $item['amount']
                ]);
            }

            $db->commit();
            flash('success', 'BOQ created successfully.');
            redirect('boq/view.php?id=' . $boq_id);
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Failed to save BOQ. Please try again.';
        }
    }
}

$extraJs = ['boq.js'];
require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('boq/index.php') ?>" class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Back to BOQs
        </a>
        <h5 class="fw-bold mt-1 mb-0">Create New BOQ</h5>
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

    <!-- BOQ Details -->
    <div class="card card-custom mb-4">
        <div class="card-header"><h6 class="fw-bold mb-0">BOQ Details</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title" value="<?= h($_POST['title'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Date Prepared</label>
                    <input type="date" class="form-control" name="date_prepared" value="<?= h($_POST['date_prepared'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold small">Description</label>
                    <textarea class="form-control" name="description" rows="2"><?= h($_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Prepared By</label>
                    <input type="text" class="form-control" name="prepared_by" value="<?= h($_POST['prepared_by'] ?? $user['name']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Checked By</label>
                    <input type="text" class="form-control" name="checked_by" value="<?= h($_POST['checked_by'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Status</label>
                    <select class="form-select" name="status">
                        <option value="draft">Draft</option>
                        <option value="final">Final</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items -->
    <div class="card card-custom mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Line Items</h6>
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
                            <th>Description</th>
                            <th style="width:110px">Unit</th>
                            <th style="width:110px">Quantity</th>
                            <th style="width:120px">Unit Cost</th>
                            <th style="width:120px">Amount</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr class="item-row">
                            <td class="row-number">1</td>
                            <td><input type="text" name="item_description[]" class="form-control form-control-sm" placeholder="Item description" required></td>
                            <td>
                                <select name="item_unit[]" class="form-select form-select-sm">
                                    <option value="lot">lot</option><option value="pc">pc</option><option value="set">set</option>
                                    <option value="cu.m">cu.m</option><option value="sq.m">sq.m</option><option value="lin.m">lin.m</option>
                                    <option value="kg">kg</option><option value="bag">bag</option><option value="sheet">sheet</option>
                                    <option value="length">length</option><option value="day">day</option><option value="trip">trip</option>
                                </select>
                            </td>
                            <td><input type="number" name="item_quantity[]" class="form-control form-control-sm qty-input" step="0.001" min="0" value="0"></td>
                            <td><input type="number" name="item_unit_cost[]" class="form-control form-control-sm cost-input" step="0.01" min="0" value="0"></td>
                            <td><input type="text" class="form-control form-control-sm amount-display" readonly value="0.00"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Totals -->
    <div class="card card-custom mb-4">
        <div class="card-header"><h6 class="fw-bold mb-0">Totals</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Markup %</label>
                    <input type="number" class="form-control" id="markupPct" name="markup_percentage" step="0.01" min="0" value="<?= h($_POST['markup_percentage'] ?? '0') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">VAT %</label>
                    <input type="number" class="form-control" id="vatPct" name="vat_percentage" step="0.01" min="0" value="<?= h($_POST['vat_percentage'] ?? '12') ?>">
                </div>
                <div class="col-md-6">
                    <div class="bg-light rounded p-3 mt-2">
                        <div class="d-flex justify-content-between mb-1 small">
                            <span>Subtotal:</span> <span id="subtotal">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 small">
                            <span>Markup:</span> <span id="markupAmount">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 small">
                            <span>VAT:</span> <span id="vatAmount">₱0.00</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Grand Total:</span> <span id="grandTotal">₱0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Save BOQ
        </button>
        <a href="<?= url('boq/index.php') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
