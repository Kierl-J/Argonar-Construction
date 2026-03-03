<?php
// Argonar Construction - Page Header (full top: HTML head, sidebar, navbar)
// Set $pageTitle before including this file.
$currentUser = current_user();
$flashMessages = get_flash();
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));

function navActive(string $page, string $dir = ''): string {
    global $currentPage, $currentDir;
    if ($dir && $currentDir === $dir) return 'active';
    if (!$dir && $currentPage === $page && $currentDir !== 'boq') return 'active';
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' | ' : '' ?><?= APP_NAME ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-brand">
        <img src="<?= asset('images/logo.svg') ?>" alt="<?= APP_NAME ?>" height="36">
    </div>
    <div class="sidebar-nav">
        <div class="nav-section">Main</div>
        <a href="<?= url('index.php') ?>" class="nav-link <?= navActive('index') ?>">
            <i class="fas fa-th-large"></i> Home
        </a>
        <a href="<?= url('payment/pricing.php') ?>" class="nav-link <?= $currentPage === 'pricing' ? 'active' : '' ?>">
            <i class="fas fa-tags"></i> Pricing
        </a>

        <div class="nav-section">Tools</div>
        <a href="<?= url('boq/index.php') ?>" class="nav-link <?= navActive('', 'boq') ?>">
            <i class="fas fa-file-invoice-dollar"></i> BOQ Generator
        </a>

        <?php if ($currentUser): ?>
        <div class="nav-section">Account</div>
        <a href="<?= url('payment/history.php') ?>" class="nav-link <?= $currentPage === 'history' ? 'active' : '' ?>">
            <i class="fas fa-receipt"></i> My Subscription
        </a>
        <a href="<?= url('logout.php') ?>" class="nav-link">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
        <?php endif; ?>
    </div>
</nav>
<div class="sidebar-overlay"></div>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-link text-dark d-lg-none p-0" id="sidebarToggle">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            <h6 class="page-title mb-0"><?= h($pageTitle ?? 'Dashboard') ?></h6>
        </div>
        <div class="d-flex align-items-center gap-3">
            <?php if ($currentUser): ?>
            <span class="d-none d-md-inline text-muted small">
                <?= h($currentUser['company'] ?: $currentUser['name']) ?>
            </span>
            <div class="dropdown">
                <button class="btn btn-link text-dark dropdown-toggle p-0" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle fa-lg"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text fw-bold"><?= h($currentUser['name']) ?></span></li>
                    <li><span class="dropdown-item-text small text-muted"><?= h($currentUser['email']) ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= url('logout.php') ?>"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
            <?php else: ?>
            <a href="<?= url('login.php') ?>" class="btn btn-outline-primary btn-sm">Log In</a>
            <a href="<?= url('register.php') ?>" class="btn btn-primary btn-sm">Register</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($flashMessages)): ?>
    <div class="flash-container">
        <?php foreach ($flashMessages as $type => $message): ?>
        <div class="alert alert-<?= $type ?> alert-dismissible fade show mb-2" role="alert">
            <?php if ($type === 'success'): ?><i class="fas fa-check-circle me-1"></i>
            <?php elseif ($type === 'danger'): ?><i class="fas fa-exclamation-circle me-1"></i>
            <?php elseif ($type === 'warning'): ?><i class="fas fa-exclamation-triangle me-1"></i>
            <?php else: ?><i class="fas fa-info-circle me-1"></i>
            <?php endif; ?>
            <?= h($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Content Area -->
    <div class="content-area">
