<?php
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Check-In — Argonar Tournament';
$pageDescription = 'Confirm your attendance for the Argonar Tournament on tournament day.';

$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ref = strtoupper(trim($_POST['ref_code'] ?? ''));

    if (empty($ref)) {
        $error = 'Please enter your reference code.';
    } else {
        // Check teams
        $stmt = $pdo->prepare("SELECT id, team_name, game, status, checked_in, checked_in_at FROM teams WHERE ref_code = ?");
        $stmt->execute([$ref]);
        $row = $stmt->fetch();

        if ($row) {
            if ($row['status'] !== 'approved') {
                $error = 'Your registration is not yet approved. Please wait for confirmation.';
            } elseif ($row['checked_in']) {
                $result = [
                    'type' => 'team',
                    'name' => $row['team_name'],
                    'game' => $row['game'],
                    'already' => true,
                    'time' => $row['checked_in_at'],
                ];
            } else {
                $pdo->prepare("UPDATE teams SET checked_in = 1, checked_in_at = NOW() WHERE id = ?")->execute([$row['id']]);
                $result = [
                    'type' => 'team',
                    'name' => $row['team_name'],
                    'game' => $row['game'],
                    'already' => false,
                ];
            }
        } else {
            // Check solo players
            $stmt = $pdo->prepare("SELECT id, player_name, game, status, checked_in, checked_in_at FROM solo_players WHERE ref_code = ?");
            $stmt->execute([$ref]);
            $row = $stmt->fetch();

            if ($row) {
                if ($row['status'] === 'pending') {
                    $error = 'Your registration is still pending approval.';
                } elseif ($row['checked_in']) {
                    $result = [
                        'type' => 'solo',
                        'name' => $row['player_name'],
                        'game' => $row['game'],
                        'already' => true,
                        'time' => $row['checked_in_at'],
                    ];
                } else {
                    $pdo->prepare("UPDATE solo_players SET checked_in = 1, checked_in_at = NOW() WHERE id = ?")->execute([$row['id']]);
                    $result = [
                        'type' => 'solo',
                        'name' => $row['player_name'],
                        'game' => $row['game'],
                        'already' => false,
                    ];
                }
            } else {
                $error = 'Reference code not found. Please check and try again.';
            }
        }
    }
}

$valid_games = ['valorant' => 'Valorant', 'crossfire' => 'CrossFire', 'dota2' => 'Dota 2'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="reg-container" style="max-width:550px;">
    <a href="<?= base_url() ?>" class="back-link"><i class="bi bi-arrow-left"></i> Back to Home</a>

    <div class="reg-card" style="text-align:center;">
        <h2><i class="bi bi-check2-square"></i> Tournament Check-In</h2>
        <p class="subtitle">Enter your reference code to confirm your attendance on tournament day.</p>

        <?php if ($result): ?>
            <?php if ($result['already']): ?>
                <div class="alert-custom alert-success" style="margin-bottom:1rem;">
                    <i class="bi bi-check-circle-fill"></i>
                    <strong><?= htmlspecialchars($result['name']) ?></strong> is already checked in!
                    <div style="font-size:0.8rem; margin-top:0.3rem;">Checked in at <?= date('g:i A', strtotime($result['time'])) ?></div>
                </div>
            <?php else: ?>
                <div class="alert-custom alert-success" style="margin-bottom:1rem;">
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>Check-in successful!</strong>
                    <div style="font-size:0.85rem; margin-top:0.3rem;">
                        <?= htmlspecialchars($result['name']) ?> — <?= $valid_games[$result['game']] ?? $result['game'] ?>
                        (<?= $result['type'] === 'team' ? 'Team' : 'Solo' ?>)
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-custom alert-danger" style="margin-bottom:1rem;">
                <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <input type="text" name="ref_code" class="form-control" placeholder="e.g. DOTA-T-A1B2"
                       value="<?= htmlspecialchars($_POST['ref_code'] ?? '') ?>" required autofocus
                       style="text-align:center; font-size:1.1rem; font-weight:700; letter-spacing:1px; text-transform:uppercase;">
            </div>
            <button type="submit" class="btn-submit">
                <i class="bi bi-check2-square"></i> Check In
            </button>
        </form>

        <div style="margin-top:1.5rem; font-size:0.8rem; color:var(--text-muted);">
            Your reference code was given after registration. Check your registration confirmation page or contact the admin.
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
