<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Argonar Tournament' ?></title>

    <?php
    $defaultDescription = 'Argonar Gaming Tournament — Valorant, CrossFire & Dota 2. Register your team or enter solo. Cash prize or free paragliding experience.';
    $metaDescription = $pageDescription ?? $defaultDescription;
    $metaKeywords = 'Argonar, tournament, gaming, esports, Valorant, CrossFire, Dota 2, Cebu, Philippines, paragliding, cash prize, registration';
    $metaOgImage = $ogImage ?? base_url('images/paragliding1.jpg');
    $metaCanonical = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'argonar.co') . ($_SERVER['REQUEST_URI'] ?? '/');
    ?>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($metaKeywords) ?>">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Argonar Software OPC">
    <meta name="theme-color" content="#7c3aed">
    <link rel="canonical" href="<?= htmlspecialchars($metaCanonical) ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Argonar Tournament">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? 'Argonar Tournament') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($metaOgImage) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($metaCanonical) ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle ?? 'Argonar Tournament') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($metaDescription) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($metaOgImage) ?>">

    <link rel="icon" type="image/svg+xml" href="<?= base_url('images/favicon.svg') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
</head>
<body>

<nav class="navbar">
    <div class="container nav-container">
        <a class="navbar-brand" href="<?= base_url() ?>">
            <i class="bi bi-controller"></i> Argonar <span>Tournament</span>
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <i class="bi bi-list"></i>
        </button>
        <div class="nav-links" id="navLinks">
            <a href="<?= base_url() ?>#games" class="nav-link">Games</a>
            <a href="<?= base_url('rules.php') ?>" class="nav-link">Rules</a>
            <a href="<?= base_url('bracket.php') ?>" class="nav-link">Bracket</a>
            <a href="<?= base_url('leaderboard.php') ?>" class="nav-link">Leaderboard</a>
            <a href="<?= base_url('contact.php') ?>" class="nav-link">Contact</a>
            <a href="<?= base_url('status.php') ?>" class="nav-link">Status Check</a>
        </div>
    </div>
</nav>
