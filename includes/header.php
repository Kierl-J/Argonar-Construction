<?php
// Argonar Construction - Page Header (full top: HTML head, sidebar, navbar)
// Set $pageTitle before including this file.
$currentUser = current_user();
$flashMessages = get_flash();
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));

// Load notifications for logged-in users
$_notifCount = 0;
$_notifs = [];
if ($currentUser) {
    require_once __DIR__ . '/notifications.php';
    $_notifCount = unread_count($GLOBALS['db'], $currentUser['id']);
    $_notifs = get_notifications($GLOBALS['db'], $currentUser['id'], 8);
}

function navActive(string $page, string $dir = ''): string {
    global $currentPage, $currentDir;
    if ($dir && $currentDir === $dir) return 'active';
    if (!$dir && $currentPage === $page && !in_array($currentDir, ['boq', 'rebar', 'structural', 'architectural', 'documents', 'templates'])) return 'active';
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
    <meta name="description" content="<?= h($pageDescription ?? 'Online construction tools for Filipino engineers and contractors. BOQ Generator, Structural & Architectural Estimates, Document Generator, Rebar Cutting List, and Excel Templates.') ?>">
    <meta name="keywords" content="construction tools, BOQ generator, structural estimate, architectural estimate, rebar cutting list, document generator, Philippines, civil engineering">
    <meta name="author" content="Argonar Construction">
    <link rel="canonical" href="https://argonar.co<?= $_SERVER['REQUEST_URI'] ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= APP_NAME ?>">
    <meta property="og:title" content="<?= isset($pageTitle) ? h($pageTitle) . ' | ' : '' ?><?= APP_NAME ?>">
    <meta property="og:description" content="<?= h($pageDescription ?? 'Online construction tools for Filipino engineers and contractors. BOQ, estimates, documents, and more.') ?>">
    <meta property="og:url" content="https://argonar.co<?= $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:image" content="https://argonar.co/images/fb/post_tools_1080.png">

    <!-- Google Search Console -->
    <?php if (GSC_VERIFICATION): ?>
    <meta name="google-site-verification" content="<?= h(GSC_VERIFICATION) ?>">
    <?php endif; ?>

    <!-- Google Analytics (GA4) -->
    <?php if (GA_MEASUREMENT_ID): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= h(GA_MEASUREMENT_ID) ?>"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= h(GA_MEASUREMENT_ID) ?>');</script>
    <?php endif; ?>

    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
        <a href="<?= url('rebar/index.php') ?>" class="nav-link <?= navActive('', 'rebar') ?>">
            <i class="fas fa-ruler-combined"></i> Rebar Cutting List
        </a>
        <a href="<?= url('structural/index.php') ?>" class="nav-link <?= navActive('', 'structural') ?>">
            <i class="fas fa-building"></i> Structural Estimate
        </a>
        <a href="<?= url('architectural/index.php') ?>" class="nav-link <?= navActive('', 'architectural') ?>">
            <i class="fas fa-drafting-compass"></i> Architectural Estimate
        </a>
        <a href="<?= url('documents/index.php') ?>" class="nav-link <?= navActive('', 'documents') ?>">
            <i class="fas fa-file-alt"></i> Document Generator
        </a>
        <a href="<?= url('templates/index.php') ?>" class="nav-link <?= navActive('', 'templates') ?>">
            <i class="fas fa-file-excel"></i> Excel Templates
        </a>

        <?php if ($currentUser): ?>
        <div class="nav-section">Account</div>
        <a href="<?= url('notifications.php') ?>" class="nav-link <?= $currentPage === 'notifications' ? 'active' : '' ?>">
            <i class="fas fa-bell"></i> Notifications <?php if ($_notifCount > 0): ?><span class="badge bg-danger ms-1" style="font-size:.65rem;"><?= $_notifCount ?></span><?php endif; ?>
        </a>
        <a href="<?= url('payment/history.php') ?>" class="nav-link <?= $currentPage === 'history' ? 'active' : '' ?>">
            <i class="fas fa-receipt"></i> My Subscription
        </a>
        <?php if (!empty($currentUser['is_guest'])): ?>
        <a href="<?= url('claim.php') ?>" class="nav-link <?= $currentPage === 'claim' ? 'active' : '' ?>">
            <i class="fas fa-user-plus"></i> Claim Account
        </a>
        <?php endif; ?>
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
            <!-- Notifications Bell -->
            <div class="dropdown">
                <button class="btn btn-link text-dark p-0 position-relative" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-label="Notifications">
                    <i class="fas fa-bell fa-lg"></i>
                    <?php if ($_notifCount > 0): ?>
                    <span class="notif-badge"><?= $_notifCount > 9 ? '9+' : $_notifCount ?></span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end notif-dropdown" style="width:340px;max-height:420px;overflow-y:auto;padding:0;">
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                        <strong class="small">Notifications</strong>
                        <?php if ($_notifCount > 0): ?>
                        <a href="<?= url('notifications.php?action=read_all') ?>" class="small text-decoration-none">Mark all read</a>
                        <?php endif; ?>
                    </div>
                    <?php if (empty($_notifs)): ?>
                    <div class="text-center text-muted py-4 small">No notifications yet.</div>
                    <?php else: ?>
                    <?php foreach ($_notifs as $n): ?>
                    <a href="<?= $n['link'] ? url($n['link']) . (str_contains($n['link'], '?') ? '&' : '?') . 'mark_notif=' . $n['id'] : url('notifications.php?mark=' . $n['id']) ?>" class="dropdown-item notif-item <?= $n['is_read'] ? '' : 'notif-unread' ?> px-3 py-2">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-<?= match($n['type']) { 'warning' => 'exclamation-triangle text-warning', 'success' => 'check-circle text-success', 'danger' => 'times-circle text-danger', 'renewal' => 'sync-alt text-primary', default => 'info-circle text-info' } ?> mt-1"></i>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold small text-truncate"><?= h($n['title']) ?></div>
                                <div class="text-muted small" style="white-space:normal;line-height:1.3;"><?= h($n['message']) ?></div>
                                <div class="text-muted" style="font-size:.7rem;"><?= time_ago($n['created_at']) ?></div>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <a href="<?= url('notifications.php') ?>" class="dropdown-item text-center small py-2 border-top">View All</a>
                    <?php endif; ?>
                </div>
            </div>
            <!-- User Dropdown -->
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
            <a href="<?= url('login.php') ?>" class="btn btn-primary btn-sm">Log In / Register</a>
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
