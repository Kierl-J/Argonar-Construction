<?php
require_once __DIR__ . '/includes/db.php';

$valid_games = [
    'valorant'  => 'Valorant',
    'crossfire' => 'CrossFire',
    'dota2'     => 'Dota 2',
];

$game = $_GET['game'] ?? '';
$pageTitle = 'Tournament Brackets — Argonar Tournament';

$round_labels = [
    1 => 'Round 1',
    2 => 'Quarterfinals',
    3 => 'Semifinals',
    4 => 'Finals',
];

if ($game && isset($valid_games[$game])) {
    $pageTitle = $valid_games[$game] . ' Bracket — Argonar Tournament';
    $stmt = $pdo->prepare("SELECT * FROM matches WHERE game = ? ORDER BY bracket_side ASC, round ASC, match_order ASC");
    $stmt->execute([$game]);
    $matches = $stmt->fetchAll();

    // Group by bracket side then round
    $bracket_data = [];
    foreach ($matches as $m) {
        $side = $m['bracket_side'] ?? 'winners';
        $bracket_data[$side][$m['round']][] = $m;
    }
    $rounds = $bracket_data; // for empty check
}

include __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 2rem 1rem;">

<?php if (!$game || !isset($valid_games[$game])): ?>
    <!-- Game Selection -->
    <div class="hero">
        <h1>Tournament Brackets</h1>
        <p>Select a game to view the bracket</p>
    </div>

    <div class="games-grid">
        <?php foreach ($valid_games as $slug => $name): ?>
            <a href="<?= base_url('bracket.php?game=' . $slug) ?>" class="game-card">
                <div class="game-banner" style="height:120px; justify-content:center;">
                    <span class="game-title"><?= $name ?></span>
                </div>
                <div class="game-body">
                    <p class="desc">View the <?= $name ?> tournament bracket</p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

<?php else: ?>
    <!-- Bracket View -->
    <a href="<?= base_url('bracket.php') ?>" class="back-link"><i class="bi bi-arrow-left"></i> All Brackets</a>
    <h1 style="font-size:1.75rem; font-weight:800; margin-bottom:1.5rem;">
        <?= htmlspecialchars($valid_games[$game]) ?> — Bracket
    </h1>

    <?php if (empty($bracket_data)): ?>
        <div class="reg-card" style="text-align:center; padding:3rem 2rem;">
            <i class="bi bi-hourglass-split" style="font-size:3rem; color:var(--accent-light); display:block; margin-bottom:1rem;"></i>
            <h3 style="margin-bottom:0.5rem;">Bracket Coming Soon</h3>
            <p style="color:var(--text-muted);">Bracket will be revealed once registration closes.</p>
        </div>
    <?php else: ?>
        <?php
        $side_labels = ['winners' => 'Winners Bracket', 'losers' => 'Losers Bracket', 'grand' => 'Grand Finals'];
        $side_colors = ['winners' => 'var(--success)', 'losers' => 'var(--warning)', 'grand' => 'var(--accent-light)'];
        foreach (['winners', 'losers', 'grand'] as $side):
            if (empty($bracket_data[$side])) continue;
            $side_rounds = $bracket_data[$side];
        ?>
        <h2 style="font-size:1.25rem; font-weight:800; color:<?= $side_colors[$side] ?>; margin:2rem 0 1rem;">
            <i class="bi <?= $side === 'winners' ? 'bi-trophy' : ($side === 'losers' ? 'bi-arrow-repeat' : 'bi-star-fill') ?>"></i>
            <?= $side_labels[$side] ?>
        </h2>
        <div class="bracket-container">
            <?php
            $round_keys = array_keys($side_rounds);
            $total_rounds = count($round_keys);
            foreach ($round_keys as $idx => $round_num):
                $round_matches = $side_rounds[$round_num];
                if ($side === 'grand') {
                    $label = 'Grand Finals (Bo3)';
                } elseif ($idx === $total_rounds - 1 && $side === 'winners') {
                    $label = 'WB Finals';
                } elseif ($idx === $total_rounds - 1 && $side === 'losers') {
                    $label = 'LB Finals';
                } else {
                    $label = ($side === 'winners' ? 'WB' : 'LB') . ' Round ' . $round_num;
                }
            ?>
                <div class="bracket-round">
                    <div class="bracket-round-title"><?= $label ?></div>
                    <?php foreach ($round_matches as $m): ?>
                        <div class="bracket-match <?= $m['status'] ?>">
                            <div class="team-row <?= ($m['winner'] && $m['winner'] === $m['team1_name']) ? 'winner' : '' ?>">
                                <span class="team-name"><?= htmlspecialchars($m['team1_name'] ?: 'TBD') ?></span>
                                <span class="team-score"><?= $m['team1_score'] ?></span>
                            </div>
                            <div class="match-vs">VS</div>
                            <div class="team-row <?= ($m['winner'] && $m['winner'] === $m['team2_name']) ? 'winner' : '' ?>">
                                <span class="team-name"><?= htmlspecialchars($m['team2_name'] ?: 'TBD') ?></span>
                                <span class="team-score"><?= $m['team2_score'] ?></span>
                            </div>
                            <div class="match-footer">
                                <span class="match-status match-status-<?= $m['status'] ?>"><?= ucfirst($m['status']) ?></span>
                                <?php if ($m['scheduled_at']): ?>
                                    <span class="match-time"><i class="bi bi-clock"></i> <?= date('M j, g:i A', strtotime($m['scheduled_at'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

<?php endif; ?>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
