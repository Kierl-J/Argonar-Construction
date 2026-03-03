<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/subscription.php';

$pageTitle = 'Tool Hub';
$user = current_user();
$hasAccess = $user ? has_active_access($db, $user['id']) : false;
require __DIR__ . '/includes/header.php';
?>

<!-- Welcome Section -->
<div class="mb-4">
    <h4 class="fw-bold text-dark">Construction Tools</h4>
    <p class="text-muted mb-0">Select a tool to get started with your construction project.</p>
</div>

<!-- Tools Grid -->
<div class="row g-4">

    <!-- BOQ Generator -->
    <div class="col-md-6 col-lg-4">
        <a href="<?= url('boq/index.php') ?>" class="tool-card">
            <div class="tool-icon" style="background: rgba(52,152,219,0.1); color: #3498DB;">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <h6 class="fw-bold mb-1">BOQ Generator</h6>
            <p class="text-muted small mb-0">Create Bill of Quantities with auto-calculations and Excel export.</p>
            <?php if (!$hasAccess): ?>
            <span class="tool-lock-badge"><i class="fas fa-lock me-1"></i>Subscribe to access</span>
            <?php endif; ?>
        </a>
    </div>

    <!-- Rebar Cutting List -->
    <div class="col-md-6 col-lg-4">
        <a href="<?= url('rebar/index.php') ?>" class="tool-card">
            <div class="tool-icon" style="background: rgba(239,68,68,0.1); color: #ef4444;">
                <i class="fas fa-ruler-combined"></i>
            </div>
            <h6 class="fw-bold mb-1">Rebar Cutting List</h6>
            <p class="text-muted small mb-0">Generate rebar cutting lists with weight calculations.</p>
            <?php if (!$hasAccess): ?>
            <span class="tool-lock-badge"><i class="fas fa-lock me-1"></i>Subscribe to access</span>
            <?php endif; ?>
        </a>
    </div>

    <!-- Structural Estimate (Coming Soon) -->
    <div class="col-md-6 col-lg-4">
        <div class="tool-card" style="opacity: 0.6; cursor: default;">
            <div class="tool-icon" style="background: rgba(39,174,96,0.1); color: #27AE60;">
                <i class="fas fa-building"></i>
            </div>
            <h6 class="fw-bold mb-1">Structural Estimate <span class="badge badge-coming-soon">Coming Soon</span></h6>
            <p class="text-muted small mb-0">Quick structural cost estimates for concrete, steel, and formwork.</p>
        </div>
    </div>

    <!-- Architectural Estimate (Coming Soon) -->
    <div class="col-md-6 col-lg-4">
        <div class="tool-card" style="opacity: 0.6; cursor: default;">
            <div class="tool-icon" style="background: rgba(155,89,182,0.1); color: #9B59B6;">
                <i class="fas fa-drafting-compass"></i>
            </div>
            <h6 class="fw-bold mb-1">Architectural Estimate <span class="badge badge-coming-soon">Coming Soon</span></h6>
            <p class="text-muted small mb-0">Estimate architectural finishes: masonry, tiling, painting, roofing.</p>
        </div>
    </div>

    <!-- Document Generator (Coming Soon) -->
    <div class="col-md-6 col-lg-4">
        <div class="tool-card" style="opacity: 0.6; cursor: default;">
            <div class="tool-icon" style="background: rgba(230,126,34,0.1); color: #E67E22;">
                <i class="fas fa-file-alt"></i>
            </div>
            <h6 class="fw-bold mb-1">Document Generator <span class="badge badge-coming-soon">Coming Soon</span></h6>
            <p class="text-muted small mb-0">Generate construction documents and reports.</p>
        </div>
    </div>

    <!-- Excel Templates (Coming Soon) -->
    <div class="col-md-6 col-lg-4">
        <div class="tool-card" style="opacity: 0.6; cursor: default;">
            <div class="tool-icon" style="background: rgba(46,204,113,0.1); color: #2ECC71;">
                <i class="fas fa-file-excel"></i>
            </div>
            <h6 class="fw-bold mb-1">Excel Templates <span class="badge badge-coming-soon">Coming Soon</span></h6>
            <p class="text-muted small mb-0">Download ready-made Excel templates for construction projects.</p>
        </div>
    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
