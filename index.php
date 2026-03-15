<?php
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Argonar Tournament';

// Count registered teams per game
$counts = [];
$stmt = $pdo->query("SELECT game, COUNT(*) as total FROM teams GROUP BY game");
while ($row = $stmt->fetch()) {
    $counts[$row['game']] = $row['total'];
}

$games = [
    [
        'slug'    => 'valorant',
        'name'    => 'Valorant',
        'logo'    => 'images/valorant.png',
        'desc'    => '5v5 tactical shooter. Show your aim and strategy.',
        'banner'  => 'valorant',
    ],
    [
        'slug'    => 'crossfire',
        'name'    => 'CrossFire',
        'logo'    => 'images/crossfire.png',
        'desc'    => 'Classic FPS action on GameClub. Lock and load.',
        'banner'  => 'crossfire',
    ],
    [
        'slug'    => 'dota2',
        'name'    => 'Dota 2',
        'logo'    => 'images/dota.webp',
        'desc'    => '5v5 MOBA battle. Outplay, outfarm, outdraft.',
        'banner'  => 'dota2',
    ],
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="sponsors-bar">
    <div class="sponsor-block">
        <span class="sponsor-label">Presented by</span>
        <div class="sponsor-logo">
            <img src="<?= base_url('images/argonar-logo.svg') ?>" alt="Argonar Software OPC">
            <div class="sponsor-text">
                <strong>ARGONAR</strong>
                <span>SOFTWARE OPC</span>
            </div>
        </div>
    </div>
    <div class="sponsor-divider"></div>
    <div class="sponsor-block">
        <span class="sponsor-label">Venue hosted by</span>
        <div class="sponsor-logo">
            <img src="<?= base_url('images/hideout.jpg') ?>" alt="Hide Out Cybernet Cafe" class="venue-logo">
            <div class="sponsor-text">
                <strong>HIDE OUT</strong>
                <span>CYBERNET CAFE</span>
            </div>
        </div>
    </div>
</div>

<div class="hero">
    <h1>Tournament Registration</h1>
    <p>Pick your game, form your squad, and compete. Entry fee is <strong>&#8369;500</strong> per team.</p>
</div>

<div class="games-grid">
    <?php foreach ($games as $game): ?>
        <a href="<?= base_url('register.php') ?>?game=<?= $game['slug'] ?>" class="game-card">
            <div class="game-banner <?= $game['banner'] ?>">
                <img src="<?= base_url($game['logo']) ?>" alt="<?= $game['name'] ?>" class="game-logo">
                <div class="game-title"><?= $game['name'] ?></div>
            </div>
            <div class="game-body">
                <div class="meta">
                    <span class="badge-game"><?= strtoupper($game['slug']) ?></span>
                    <span class="entry-fee">&#8369;500</span>
                </div>
                <p class="desc"><?= $game['desc'] ?></p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="teams-count">
                        <i class="bi bi-people-fill"></i>
                        <?= $counts[$game['slug']] ?? 0 ?> team(s) registered
                    </span>
                    <span class="btn-register" style="width:auto; padding: 0.5rem 1.25rem;">Register</span>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
