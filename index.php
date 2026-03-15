<?php
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Argonar Tournament';

// Count registered teams per game
$counts = [];
$stmt = $pdo->query("SELECT game, COUNT(*) as total FROM teams GROUP BY game");
while ($row = $stmt->fetch()) {
    $counts[$row['game']] = $row['total'];
}

// Count solo players waiting per game
$solo_counts = [];
$stmt = $pdo->query("SELECT game, COUNT(*) as total FROM solo_players GROUP BY game");
while ($row = $stmt->fetch()) {
    $solo_counts[$row['game']] = $row['total'];
}

// Get registered teams per game
$registered_teams = [];
$stmt = $pdo->query("SELECT game, team_name, status FROM teams ORDER BY created_at DESC");
while ($row = $stmt->fetch()) {
    $registered_teams[$row['game']][] = $row;
}

// Get solo players per game
$solo_players = [];
$stmt = $pdo->query("SELECT game, player_name, rank_tier, preferred_role, status FROM solo_players ORDER BY created_at DESC");
while ($row = $stmt->fetch()) {
    $solo_players[$row['game']][] = $row;
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
        <div class="venue-address">
            <i class="bi bi-geo-alt-fill"></i> Brgy. Inayawan, Inayawan Central, Cebu City, 6000
        </div>
    </div>
</div>

<div class="hero">
    <h1>Tournament Registration</h1>
    <p>Pick your game and join the tournament. Register as a team or enter solo and get matched by rank.</p>
    <div class="winner-banner">
        <i class="bi bi-trophy-fill"></i> Winner Takes All — One champion per game. No runner-up, no second place.
    </div>
    <div class="prize-pick">
        <div class="prize-pick-title">The winning team picks ONE reward:</div>
        <div class="prize-options">
            <div class="prize-option">
                <div class="prize-icon"><i class="bi bi-cash-stack"></i></div>
                <div class="prize-amount">&#8369;9,000 Cash</div>
                <div class="prize-desc">Split among the team</div>
            </div>
            <div class="prize-or">OR</div>
            <div class="prize-option">
                <div class="prize-icon"><i class="bi bi-wind"></i></div>
                <div class="prize-amount">Paragliding Experience</div>
                <div class="prize-desc">Free tickets for the whole team — by OCPD Oslob Cebu</div>
            </div>
        </div>
        <div class="prize-note">Winners must choose one. You cannot claim both.</div>
    </div>
</div>

<div class="games-grid">
    <?php foreach ($games as $game): ?>
        <div class="game-card">
            <div class="game-banner">
                <img src="<?= base_url($game['logo']) ?>" alt="<?= $game['name'] ?>" class="game-logo">
                <div class="game-title"><?= $game['name'] ?></div>
            </div>
            <div class="game-body">
                <p class="desc"><?= $game['desc'] ?></p>
                <div class="game-stats">
                    <span class="teams-count">
                        <i class="bi bi-people-fill"></i> <?= $counts[$game['slug']] ?? 0 ?> team(s)
                    </span>
                    <span class="teams-count">
                        <i class="bi bi-person-fill"></i> <?= $solo_counts[$game['slug']] ?? 0 ?> solo player(s)
                    </span>
                </div>
                <div class="game-actions">
                    <a href="<?= base_url('register.php') ?>?game=<?= $game['slug'] ?>" class="btn-register">
                        <i class="bi bi-people-fill"></i> Register Team <span class="btn-price">&#8369;500</span>
                    </a>
                    <a href="<?= base_url('matchmaking.php') ?>?game=<?= $game['slug'] ?>" class="btn-solo">
                        <i class="bi bi-person-fill"></i> Solo Entry <span class="btn-price">&#8369;100</span>
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="orgs-section">
    <h2>Participating Organizations</h2>
    <div class="orgs-grid">
        <a href="https://www.facebook.com/argonarsoftwarepublishing" target="_blank" rel="noopener" class="org-card">
            <img src="<?= base_url('images/argonar-logo.svg') ?>" alt="Argonar Software OPC" class="org-logo">
            <div class="org-info">
                <div class="org-name">Argonar Software OPC</div>
                <span class="org-link"><i class="bi bi-facebook"></i> Facebook Page</span>
            </div>
        </a>
        <a href="https://www.facebook.com/oslobparagliding" target="_blank" rel="noopener" class="org-card">
            <img src="<?= base_url('images/ocpd.jpg') ?>" alt="OCPD" class="org-logo">
            <div class="org-info">
                <div class="org-name">Oslob Cebu Paragliding Development Com</div>
                <span class="org-link"><i class="bi bi-facebook"></i> Facebook Page</span>
            </div>
        </a>
    </div>
</div>

<div class="registered-section">
    <h2>Registered Participants</h2>

    <?php foreach ($games as $game): ?>
        <?php
        $teams = $registered_teams[$game['slug']] ?? [];
        $solos = $solo_players[$game['slug']] ?? [];
        if (empty($teams) && empty($solos)) continue;
        ?>
        <div class="registered-game">
            <h3><i class="bi bi-controller"></i> <?= $game['name'] ?></h3>
            <div class="registered-list">
                <?php foreach ($teams as $team): ?>
                    <div class="registered-team">
                        <div class="team-name"><?= htmlspecialchars($team['team_name']) ?></div>
                        <div class="team-type"><i class="bi bi-people-fill"></i> Team</div>
                        <span class="team-status <?= $team['status'] ?>"><?= $team['status'] ?></span>
                    </div>
                <?php endforeach; ?>
                <?php foreach ($solos as $solo): ?>
                    <div class="registered-team">
                        <div class="team-name"><?= htmlspecialchars($solo['player_name']) ?></div>
                        <div class="team-type"><i class="bi bi-person-fill"></i> Solo &middot; <?= htmlspecialchars($solo['rank_tier']) ?><?= !empty($solo['preferred_role']) ? ' &middot; ' . htmlspecialchars($solo['preferred_role']) : '' ?></div>
                        <span class="team-status <?= $solo['status'] ?>"><?= $solo['status'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($registered_teams) && empty($solo_players)): ?>
        <p class="no-teams" style="text-align:center;">No participants registered yet. Be the first!</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
