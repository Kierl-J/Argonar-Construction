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

    <a href="<?= base_url() ?>" class="btn-register" style="width: auto; display: inline-block; padding: 0.75rem 2rem;">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
