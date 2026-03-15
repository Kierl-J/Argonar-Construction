<?php
require_once __DIR__ . '/includes/db.php';

$valid_games = [
    'valorant'  => 'Valorant',
    'crossfire' => 'CrossFire',
    'dota2'     => 'Dota 2',
];

$pageTitle = 'Check Registration Status';
$query = trim($_GET['q'] ?? '');
$result = null;

if ($query !== '') {
    // Search teams by ref_code or team_name
    $stmt = $pdo->prepare("SELECT ref_code, team_name AS name, game, status, created_at, 'team' AS type FROM teams WHERE ref_code = ? OR team_name LIKE ? LIMIT 1");
    $stmt->execute([$query, $query]);
    $result = $stmt->fetch();

    // If not found in teams, search solo_players
    if (!$result) {
        $stmt = $pdo->prepare("SELECT ref_code, player_name AS name, game, status, created_at, 'solo' AS type FROM solo_players WHERE ref_code = ? OR player_name LIKE ? LIMIT 1");
        $stmt->execute([$query, $query]);
        $result = $stmt->fetch();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="reg-container">
    <a href="<?= base_url() ?>" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to games
    </a>

    <div class="reg-card">
        <h2>Check Registration Status</h2>
        <p class="subtitle">Enter your reference code or team/player name to look up your registration.</p>

        <form method="GET" action="<?= base_url('status.php') ?>">
            <div class="mb-3">
                <label class="form-label">Reference Code or Name</label>
                <input type="text" name="q" class="form-control" placeholder="e.g. VAL-T-A1B2 or Shadow Wolves"
                       value="<?= htmlspecialchars($query) ?>" required>
            </div>
            <button type="submit" class="btn-submit">
                <i class="bi bi-search"></i> Look Up
            </button>
        </form>

        <?php if ($query !== '' && $result): ?>
            <div class="status-result">
                <div class="section-label">Registration Found</div>

                <?php if ($result['ref_code']): ?>
                    <div class="status-row">
                        <span class="status-label">Reference Code</span>
                        <span class="status-value" style="font-weight:800; letter-spacing:1px; color:var(--accent-light);"><?= htmlspecialchars($result['ref_code']) ?></span>
                    </div>
                <?php endif; ?>

                <div class="status-row">
                    <span class="status-label"><?= $result['type'] === 'team' ? 'Team Name' : 'Player Name' ?></span>
                    <span class="status-value"><?= htmlspecialchars($result['name']) ?></span>
                </div>

                <div class="status-row">
                    <span class="status-label">Game</span>
                    <span class="status-value"><?= htmlspecialchars($valid_games[$result['game']] ?? $result['game']) ?></span>
                </div>

                <div class="status-row">
                    <span class="status-label">Type</span>
                    <span class="status-value"><?= $result['type'] === 'team' ? 'Team Registration' : 'Solo Matchmaking' ?></span>
                </div>

                <div class="status-row">
                    <span class="status-label">Status</span>
                    <span class="status-value">
                        <span class="status-badge status-<?= htmlspecialchars($result['status']) ?>">
                            <?= htmlspecialchars(ucfirst($result['status'])) ?>
                        </span>
                    </span>
                </div>

                <div class="status-row">
                    <span class="status-label">Registered</span>
                    <span class="status-value"><?= date('M j, Y \a\t g:i A', strtotime($result['created_at'])) ?></span>
                </div>
            </div>
        <?php elseif ($query !== ''): ?>
            <div class="alert-custom alert-danger" style="margin-top:1.5rem;">
                <i class="bi bi-exclamation-circle"></i>
                No registration found for "<strong><?= htmlspecialchars($query) ?></strong>". Please double-check your reference code or name and try again.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
