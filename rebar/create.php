<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';

$user = require_login();
require_access();

$pageTitle = 'Create Rebar Cutting List';
$errors = [];

// Philippine standard rebar weights (kg/m)
$rebarWeights = [
    '10mm' => 0.617,
    '12mm' => 0.888,
    '16mm' => 1.578,
    '20mm' => 2.466,
    '25mm' => 3.853,
    '28mm' => 4.834,
    '32mm' => 6.313,
    '36mm' => 7.990,
];

// Handle POST — save rebar list
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $title             = trim($_POST['title'] ?? '');
    $project_name      = trim($_POST['project_name'] ?? '');
    $structural_member = trim($_POST['structural_member'] ?? '');
    $prepared_by       = trim($_POST['prepared_by'] ?? '');
    $checked_by        = trim($_POST['checked_by'] ?? '');
    $date_prepared     = $_POST['date_prepared'] ?? '';
    $status            = ($_POST['status'] ?? 'draft') === 'final' ? 'final' : 'draft';

    if (!$title) $errors[] = 'Title is required.';

    // Parse items
    $bar_sizes   = $_POST['item_bar_size'] ?? [];
    $pieces      = $_POST['item_pieces'] ?? [];
    $lengths     = $_POST['item_length'] ?? [];
    $descs       = $_POST['item_description'] ?? [];

    $items = [];
    $totalWeight = 0;
    for ($i = 0; $i < count($bar_sizes); $i++) {
        $barSize = $bar_sizes[$i] ?? '10mm';
        $pcs     = intval($pieces[$i] ?? 0);
        $lenPc   = floatval($lengths[$i] ?? 0);
        if ($pcs <= 0 && $lenPc <= 0) continue;

        $wpm = $rebarWeights[$barSize] ?? 0.617;
        $totalLen = $pcs * $lenPc;
        $itemWeight = $totalLen * $wpm;
        $totalWeight += $itemWeight;

        $items[] = [
            'item_no'          => count($items) + 1,
            'bar_size'         => $barSize,
            'no_of_pieces'     => $pcs,
            'length_per_pc'    => $lenPc,
            'total_length'     => $totalLen,
            'weight_per_meter' => $wpm,
            'total_weight'     => $itemWeight,
            'description'      => trim($descs[$i] ?? ''),
        ];
    }

    if (empty($items)) $errors[] = 'At least one item is required.';

    if (!$errors) {
        $db->beginTransaction();
        try {
            $stmt = $db->prepare('INSERT INTO rebar_lists (user_id, title, project_name, structural_member, prepared_by, checked_by, date_prepared, total_weight, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $user['id'], $title, $project_name ?: null, $structural_member ?: null,
                $prepared_by ?: null, $checked_by ?: null, $date_prepared ?: null, $totalWeight, $status
            ]);
            $listId = $db->lastInsertId();

            $itemStmt = $db->prepare('INSERT INTO rebar_items (rebar_list_id, item_no, bar_size, no_of_pieces, length_per_pc, total_length, weight_per_meter, total_weight, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            foreach ($items as $item) {
                $itemStmt->execute([
                    $listId, $item['item_no'], $item['bar_size'], $item['no_of_pieces'],
                    $item['length_per_pc'], $item['total_length'], $item['weight_per_meter'],
                    $item['total_weight'], $item['description'] ?: null
                ]);
            }

            $db->commit();
            flash('success', 'Rebar Cutting List created successfully.');
            redirect('rebar/view.php?id=' . $listId);
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Failed to save. Please try again.';
        }
    }
}

$extraJs = ['rebar.js'];
require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('rebar/index.php') ?>" class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Back to Rebar Lists
        </a>
        <h5 class="fw-bold mt-1 mb-0">Create Rebar Cutting List</h5>
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
                    <input type="text" class="form-control" name="title" value="<?= h($_POST['title'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Project Name</label>
                    <input type="text" class="form-control" name="project_name" value="<?= h($_POST['project_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Structural Member</label>
                    <input type="text" class="form-control" name="structural_member" value="<?= h($_POST['structural_member'] ?? '') ?>" placeholder="e.g. Column C1, Beam B1">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Date Prepared</label>
                    <input type="date" class="form-control" name="date_prepared" value="<?= h($_POST['date_prepared'] ?? date('Y-m-d')) ?>">
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

    <!-- Rebar Items -->
    <div class="card card-custom mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Bar Items</h6>
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
                            <th style="width:120px">Bar Size</th>
                            <th style="width:100px">Pieces</th>
                            <th style="width:120px">Length/pc (m)</th>
                            <th style="width:130px">Total Length (m)</th>
                            <th style="width:120px">Wt/m (kg)</th>
                            <th style="width:140px">Total Wt (kg)</th>
                            <th>Description</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr class="item-row">
                            <td class="row-number">1</td>
                            <td>
                                <select name="item_bar_size[]" class="form-select form-select-sm bar-size-input">
                                    <option value="10mm">10mm</option>
                                    <option value="12mm">12mm</option>
                                    <option value="16mm">16mm</option>
                                    <option value="20mm">20mm</option>
                                    <option value="25mm">25mm</option>
                                    <option value="28mm">28mm</option>
                                    <option value="32mm">32mm</option>
                                    <option value="36mm">36mm</option>
                                </select>
                            </td>
                            <td><input type="number" name="item_pieces[]" class="form-control form-control-sm pieces-input" min="0" value="0"></td>
                            <td><input type="number" name="item_length[]" class="form-control form-control-sm length-input" step="0.001" min="0" value="0"></td>
                            <td><input type="text" class="form-control form-control-sm total-length-display" readonly value="0.000"></td>
                            <td><input type="text" class="form-control form-control-sm wpm-display" readonly value="0.617"></td>
                            <td><input type="text" class="form-control form-control-sm total-weight-display" readonly value="0.000"></td>
                            <td><input type="text" name="item_description[]" class="form-control form-control-sm" placeholder="Optional"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="card card-custom mb-4">
        <div class="card-header"><h6 class="fw-bold mb-0">Summary</h6></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="bg-light rounded p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Items:</span>
                            <span class="fw-bold" id="totalItems">1</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total Weight:</span>
                            <span class="fw-bold text-primary" id="totalWeight">0.000 kg</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Save Rebar List
        </button>
        <a href="<?= url('rebar/index.php') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
