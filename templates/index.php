<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';

$pageTitle = 'Excel Templates';
$user = current_user();
$hasAccess = $user ? has_active_access($db, $user['id']) : false;

$templates = [
    [
        'file'  => 'boq_template.xlsx',
        'name'  => 'BOQ Template',
        'desc'  => 'Bill of Quantities with item number, description, quantity, unit cost, and amount columns.',
        'icon'  => 'fa-file-invoice-dollar',
        'color' => '#3498DB',
    ],
    [
        'file'  => 'cost_estimate_template.xlsx',
        'name'  => 'Cost Estimate Template',
        'desc'  => 'Project cost estimation worksheet with categories, subtotals, and contingency.',
        'icon'  => 'fa-calculator',
        'color' => '#27AE60',
    ],
    [
        'file'  => 'project_schedule_template.xlsx',
        'name'  => 'Project Schedule Template',
        'desc'  => 'Gantt-style project scheduling template with tasks, duration, and timeline.',
        'icon'  => 'fa-calendar-alt',
        'color' => '#9B59B6',
    ],
    [
        'file'  => 'daily_report_template.xlsx',
        'name'  => 'Daily Report Template',
        'desc'  => 'Daily construction progress report form with weather, manpower, and activities.',
        'icon'  => 'fa-clipboard-list',
        'color' => '#E67E22',
    ],
    [
        'file'  => 'material_requisition_template.xlsx',
        'name'  => 'Material Requisition Template',
        'desc'  => 'Material request form with item, quantity, unit, and approval fields.',
        'icon'  => 'fa-truck-loading',
        'color' => '#E74C3C',
    ],
];

require __DIR__ . '/../includes/header.php';
?>

<?php if (!$user): ?>
<div class="card card-custom">
    <div class="card-body text-center py-5">
        <i class="fas fa-file-excel fa-3x text-muted mb-3"></i>
        <h5 class="fw-bold">Excel Templates</h5>
        <p class="text-muted mb-4">Download ready-made Excel templates for construction projects.<br>Please log in or register to get started.</p>
        <a href="<?= url('login.php') ?>" class="btn btn-primary">Log In / Register</a>
    </div>
</div>
<?php elseif (!$hasAccess): ?>
<div class="card card-custom">
    <div class="card-body text-center py-5">
        <i class="fas fa-lock fa-3x text-warning mb-3"></i>
        <h5 class="fw-bold">Subscription Required</h5>
        <p class="text-muted mb-4">You need an active subscription to download Excel templates.<br>Choose a plan to get started.</p>
        <a href="<?= url('payment/pricing.php') ?>" class="btn btn-primary">
            <i class="fas fa-tags me-1"></i> View Pricing Plans
        </a>
    </div>
</div>
<?php else: ?>
<div class="mb-4">
    <h5 class="fw-bold mb-1">Excel Templates</h5>
    <p class="text-muted small mb-0">Download ready-made templates for your construction projects.</p>
</div>

<div class="row g-4">
    <?php foreach ($templates as $tpl): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card card-custom h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;background:<?= $tpl['color'] ?>1a;">
                        <i class="fas <?= $tpl['icon'] ?>" style="color:<?= $tpl['color'] ?>;font-size:1.2rem"></i>
                    </div>
                    <h6 class="fw-bold mb-0"><?= h($tpl['name']) ?></h6>
                </div>
                <p class="text-muted small flex-grow-1"><?= h($tpl['desc']) ?></p>
                <a href="<?= url('templates/download.php?file=' . urlencode($tpl['file'])) ?>" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-download me-1"></i> Download
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
