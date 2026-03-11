<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';

$pageTitle = 'Document Generator';
$user = current_user();
$hasAccess = $user ? has_active_access($db, $user['id']) : false;

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

// Filter
$filterType = $_GET['type'] ?? '';
$documents = [];
if ($user) {
    $sql = 'SELECT * FROM documents WHERE user_id = ?';
    $params = [$user['id']];
    if ($filterType && isset($docTypeLabels[$filterType])) {
        $sql .= ' AND doc_type = ?';
        $params[] = $filterType;
    }
    $sql .= ' ORDER BY updated_at DESC';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $documents = $stmt->fetchAll();
}

require __DIR__ . '/../includes/header.php';
?>

<?php if (!$user || !$hasAccess): ?>
<div class="card card-custom">
    <div class="card-body text-center py-5">
        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
        <h5 class="fw-bold">Document Generator</h5>
        <p class="text-muted mb-4">Generate construction documents: scope of work, material requisitions, progress reports, and change orders.<br>Subscribe to get started - no registration needed.</p>
        <a href="<?= url('payment/pricing.php') ?>" class="btn btn-primary"><i class="fas fa-tags me-1"></i>View Pricing</a>
        <?php if (!$user): ?><div class="mt-2"><a href="<?= url('login.php') ?>" class="text-muted small">Already have an account? Log in</a></div><?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">My Documents</h5>
        <p class="text-muted small mb-0"><?= count($documents) ?> document<?= count($documents) !== 1 ? 's' : '' ?></p>
    </div>
    <div class="d-flex gap-2">
        <!-- Filter -->
        <select class="form-select form-select-sm" style="width:auto" onchange="window.location='<?= url('documents/index.php') ?>' + (this.value ? '?type=' + this.value : '')">
            <option value="">All Types</option>
            <?php foreach ($docTypeLabels as $val => $label): ?>
            <option value="<?= $val ?>" <?= $filterType === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <!-- New Document dropdown -->
        <div class="dropdown">
            <button class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-plus me-1"></i> New Document
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <?php foreach ($docTypeLabels as $val => $label): ?>
                <li><a class="dropdown-item" href="<?= url('documents/create.php?type=' . $val) ?>"><?= $label ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php if (empty($documents)): ?>
<div class="empty-state">
    <i class="fas fa-folder-open"></i>
    <h6>No documents yet</h6>
    <p class="text-muted">Create your first document to get started.</p>
</div>
<?php else: ?>
<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Project</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $doc): ?>
                <tr>
                    <td>
                        <a href="<?= url('documents/view.php?id=' . $doc['id']) ?>" class="fw-bold text-decoration-none">
                            <?= h($doc['title']) ?>
                        </a>
                    </td>
                    <td>
                        <span class="badge <?= $docTypeBadges[$doc['doc_type']] ?? 'bg-secondary' ?>">
                            <?= $docTypeLabels[$doc['doc_type']] ?? $doc['doc_type'] ?>
                        </span>
                    </td>
                    <td><?= h($doc['project_name'] ?: '-') ?></td>
                    <td><?= date('M d, Y', strtotime($doc['updated_at'])) ?></td>
                    <td>
                        <span class="badge bg-<?= $doc['status'] === 'final' ? 'success' : 'secondary' ?>">
                            <?= ucfirst($doc['status']) ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="<?= url('documents/view.php?id=' . $doc['id']) ?>" class="btn btn-sm btn-outline-primary" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?= url('documents/edit.php?id=' . $doc['id']) ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?= url('documents/export.php?id=' . $doc['id']) ?>" class="btn btn-sm btn-outline-success" title="Export Excel">
                            <i class="fas fa-file-excel"></i>
                        </a>
                        <form id="delete-<?= $doc['id'] ?>" method="POST" action="<?= url('documents/delete.php') ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                    onclick="confirmDelete('delete-<?= $doc['id'] ?>')">
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
