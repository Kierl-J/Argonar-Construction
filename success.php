<?php
require_once __DIR__ . '/includes/db.php';

$valid_games = [
    'valorant'  => 'Valorant',
    'crossfire' => 'CrossFire',
    'dota2'     => 'Dota 2',
];

$game_slug = $_GET['game'] ?? '';
$game_name = $valid_games[$game_slug] ?? 'Tournament';
$type = $_GET['type'] ?? 'team';
$pageTitle = 'Registration Submitted';

$flash = get_flash();
$ref_code = $_SESSION['ref_code'] ?? null;
unset($_SESSION['ref_code']);

require_once __DIR__ . '/includes/header.php';
?>

<div class="success-container">
    <div class="success-icon"><?= $type === 'solo' ? '&#127919;' : '&#127942;' ?></div>
    <h2><?= $type === 'solo' ? "You're on the List!" : "You're In!" ?></h2>

    <?php if ($flash): ?>
        <p style="color: var(--success); font-weight: 600;"><?= htmlspecialchars($flash['message']) ?></p>
    <?php elseif ($type === 'solo'): ?>
        <p>You've been registered for <?= htmlspecialchars($game_name) ?> solo matchmaking. We'll match you with players of similar rank and notify you soon.</p>
    <?php else: ?>
        <p>Your team has been registered for <?= htmlspecialchars($game_name) ?>. We'll review your payment and confirm shortly.</p>
    <?php endif; ?>

    <?php if ($ref_code): ?>
        <div class="ref-code-display">
            <div class="ref-code-label">Your Reference Code</div>
            <div class="ref-code-value"><?= htmlspecialchars($ref_code) ?></div>
            <div class="ref-code-hint">Save this code! Use it to check your registration status.</div>
        </div>
    <?php endif; ?>

    <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; margin-top: 1.5rem;">
        <a href="<?= base_url('status.php') ?>" class="btn-register" style="width: auto; display: inline-flex; padding: 0.75rem 2rem;">
            <i class="bi bi-search"></i> Check Status
        </a>
        <a href="<?= base_url() ?>" class="btn-solo" style="width: auto; display: inline-flex; padding: 0.75rem 2rem;">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
