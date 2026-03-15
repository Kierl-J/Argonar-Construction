<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Argonar Tournament' ?></title>
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
