<?php
require_once __DIR__ . '/includes/db.php';

$valid_games = [
    'valorant'  => 'Valorant',
    'crossfire' => 'CrossFire',
    'dota2'     => 'Dota 2',
];

$pageTitle = 'Leaderboard — Argonar Tournament';

$results = $pdo->query("SELECT * FROM tournament_results ORDER BY game ASC, season DESC, placement ASC")->fetchAll();

// Group by game then season
$grouped = [];
foreach ($results as $r) {
    $grouped[$r['game']][$r['season']][] = $r;
}

include __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 2rem 1rem;">

    <div class="hero">
        <h1>Leaderboard</h1>
        <p>Tournament results and standings</p>
    </div>

    <?php if (empty($grouped)): ?>
        <div class="reg-card" style="text-align:center; padding:3rem 2rem; max-width:600px; margin:0 auto;">
            <i class="bi bi-trophy" style="font-size:3rem; color:var(--accent-light); display:block; margin-bottom:1rem;"></i>
            <h3 style="margin-bottom:0.5rem;">No Results Yet</h3>
            <p style="color:var(--text-muted);">Results will be posted after the tournament.</p>
        </div>
    <?php else: ?>
        <?php foreach ($grouped as $game => $seasons): ?>
            <div style="max-width:800px; margin:0 auto 2.5rem;">
                <h2 style="font-size:1.4rem; font-weight:800; margin-bottom:1.25rem; color:var(--accent-light);">
                    <i class="bi bi-controller"></i> <?= htmlspecialchars($valid_games[$game] ?? $game) ?>
                </h2>

                <?php foreach ($seasons as $season => $placements): ?>
                    <div style="margin-bottom:1.5rem;">
                        <h3 style="font-size:1rem; font-weight:700; color:var(--text-muted); margin-bottom:0.75rem;">
                            <?= htmlspecialchars($season) ?>
                        </h3>
                        <div class="table-responsive">
                            <table class="leaderboard-table">
                                <thead>
                                    <tr>
                                        <th style="width:80px;">Place</th>
                                        <th>Team</th>
                                        <th>Prize</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($placements as $p): ?>
                                        <tr>
                                            <td>
                                                <?php if ($p['placement'] === 1): ?>
                                                    <span class="medal-1"><i class="bi bi-trophy-fill"></i> 1st</span>
                                                <?php elseif ($p['placement'] === 2): ?>
                                                    <span class="medal-2"><i class="bi bi-trophy-fill"></i> 2nd</span>
                                                <?php elseif ($p['placement'] === 3): ?>
                                                    <span class="medal-3"><i class="bi bi-trophy-fill"></i> 3rd</span>
                                                <?php else: ?>
                                                    <span style="color:var(--text-muted);"><?= $p['placement'] ?>th</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?= htmlspecialchars($p['team_name']) ?></strong></td>
                                            <td style="color:var(--text-muted);"><?= htmlspecialchars($p['prize'] ?: '—') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
