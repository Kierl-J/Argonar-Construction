<?php
require_once __DIR__ . '/includes/db.php';

$valid_games = [
    'valorant'  => 'Valorant',
    'crossfire' => 'CrossFire',
    'dota2'     => 'Dota 2',
];

$rank_tiers = [
    'valorant'  => ['Iron', 'Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond', 'Ascendant', 'Immortal', 'Radiant'],
    'crossfire' => ['Trainee', 'Rookie', 'Soldier', 'Veteran', 'Hero', 'Legend', 'Master', 'Grandmaster'],
    'dota2'     => ['Herald', 'Guardian', 'Crusader', 'Archon', 'Legend', 'Ancient', 'Divine', 'Immortal'],
];

$game_slug = $_GET['game'] ?? '';
if (!isset($valid_games[$game_slug])) {
    header('Location: ' . base_url());
    exit;
}

$game_name = $valid_games[$game_slug];
$pageTitle = "Solo Matchmaking — $game_name";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $player_name = trim($_POST['player_name'] ?? '');
    $rank_tier   = trim($_POST['rank_tier'] ?? '');

    // Validate
    if ($player_name === '') {
        $errors[] = 'Player name is required.';
    }
    if ($rank_tier === '' || !in_array($rank_tier, $rank_tiers[$game_slug])) {
        $errors[] = 'Please select a valid rank.';
    }

    // Handle file upload
    $upload_path = '';
    if (empty($errors)) {
        if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Payment proof is required.';
        } else {
            $file = $_FILES['payment_proof'];
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($file['type'], $allowed)) {
                $errors[] = 'Payment proof must be JPG, PNG, WebP, or PDF.';
            } elseif ($file['size'] > $max_size) {
                $errors[] = 'File is too large. Maximum 5MB.';
            } else {
                $upload_dir = __DIR__ . '/uploads/payment_proofs';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'solo_' . $game_slug . '_' . preg_replace('/[^a-z0-9]/', '', strtolower($player_name)) . '_' . time() . '.' . $ext;
                $dest = $upload_dir . '/' . $filename;

                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    $errors[] = 'Failed to upload file. Please try again.';
                } else {
                    $upload_path = 'uploads/payment_proofs/' . $filename;
                }
            }
        }
    }

    // Insert
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO solo_players (game, player_name, rank_tier, payment_proof) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $game_slug,
            $player_name,
            $rank_tier,
            $upload_path,
        ]);

        flash('success', "You've been registered for $game_name solo matchmaking! We'll match you with players of similar rank.");
        header("Location: " . base_url("success.php?type=solo&game=$game_slug"));
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="reg-container">
    <a href="<?= base_url() ?>" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to games
    </a>

    <div class="reg-card">
        <h2><?= htmlspecialchars($game_name) ?> — Solo Matchmaking</h2>
        <p class="subtitle">Register as a solo player and we'll match you with a team</p>

        <?php if (!empty($errors)): ?>
            <div class="alert-custom alert-danger">
                <i class="bi bi-exclamation-circle"></i>
                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="section-label">Player Info</div>
            <div class="mb-3">
                <label class="form-label">Player Name</label>
                <input type="text" name="player_name" class="form-control" placeholder="Full name or in-game name"
                       value="<?= htmlspecialchars($_POST['player_name'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Rank</label>
                <select name="rank_tier" class="form-control form-select" required>
                    <option value="">Select your rank</option>
                    <?php foreach ($rank_tiers[$game_slug] as $rank): ?>
                        <option value="<?= htmlspecialchars($rank) ?>"
                            <?= (($_POST['rank_tier'] ?? '') === $rank) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($rank) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="section-label">Payment</div>
            <div class="payment-info">
                <div class="fee">&#8369;100.00</div>
                <p>Entry fee per player. Upload your payment proof below (GCash, bank transfer, etc.)</p>
            </div>
            <div class="mb-3">
                <label class="form-label">Payment Proof</label>
                <input type="file" name="payment_proof" class="form-control" accept="image/*,.pdf" required>
                <div class="form-text text-muted" style="font-size:0.8rem; margin-top:0.4rem;">
                    JPG, PNG, WebP, or PDF. Max 5MB.
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-check-circle"></i> Find Me a Team
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
