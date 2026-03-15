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

$roles = [
    'valorant'  => ['Duelist', 'Initiator', 'Controller', 'Sentinel', 'Flexible (Any)'],
    'crossfire' => ['Rifler', 'Sniper', 'Support', 'Entry Fragger', 'Flexible (Any)'],
    'dota2'     => ['Carry (Pos 1)', 'Mid (Pos 2)', 'Offlane (Pos 3)', 'Soft Support (Pos 4)', 'Hard Support (Pos 5)', 'Flexible (Any)'],
];

$game_prefixes = [
    'valorant'  => 'VAL',
    'crossfire' => 'CF',
    'dota2'     => 'DOTA',
];

$game_slug = $_GET['game'] ?? '';
if (!isset($valid_games[$game_slug])) {
    header('Location: ' . base_url());
    exit;
}

$game_name = $valid_games[$game_slug];
$pageTitle = "Solo Matchmaking — $game_name";
$errors = [];

function generate_ref_code($pdo, $prefix, $type) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    for ($attempt = 0; $attempt < 20; $attempt++) {
        $rand = '';
        for ($i = 0; $i < 4; $i++) {
            $rand .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $code = $prefix . '-' . $type . '-' . $rand;
        $check1 = $pdo->prepare("SELECT 1 FROM teams WHERE ref_code = ?");
        $check1->execute([$code]);
        $check2 = $pdo->prepare("SELECT 1 FROM solo_players WHERE ref_code = ?");
        $check2->execute([$code]);
        if (!$check1->fetch() && !$check2->fetch()) {
            return $code;
        }
    }
    return $prefix . '-' . $type . '-' . strtoupper(bin2hex(random_bytes(2)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $real_name      = trim($_POST['real_name'] ?? '');
    $player_name    = trim($_POST['player_name'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $facebook_link  = trim($_POST['facebook_link'] ?? '');
    $rank_tier      = trim($_POST['rank_tier'] ?? '');
    $preferred_role = trim($_POST['preferred_role'] ?? '');

    // Validate
    if ($real_name === '') {
        $errors[] = 'Real name is required.';
    }
    if ($player_name === '') {
        $errors[] = 'In-game name is required.';
    }
    if ($rank_tier === '' || !in_array($rank_tier, $rank_tiers[$game_slug])) {
        $errors[] = 'Please select a valid rank.';
    }
    if ($preferred_role === '' || !in_array($preferred_role, $roles[$game_slug])) {
        $errors[] = 'Please select your preferred role.';
    }

    // Handle payment proof (file upload OR text reason)
    $upload_path = '';
    $payment_note = trim($_POST['payment_note'] ?? '');
    if (empty($errors)) {
        $has_file = isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK;

        if (!$has_file && $payment_note === '') {
            $errors[] = 'Please upload payment proof or provide a reason why you cannot.';
        } elseif ($has_file) {
            $file = $_FILES['payment_proof'];
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($file['type'], $allowed)) {
                $errors[] = 'Payment proof must be JPG, PNG, WebP, or PDF.';
            } elseif ($file['size'] > $max_size) {
                $errors[] = 'File is too large. Maximum 2MB.';
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
        } else {
            $upload_path = 'NOTE: ' . $payment_note;
        }
    }

    // Insert
    if (empty($errors)) {
        try {
            $ref_code = generate_ref_code($pdo, $game_prefixes[$game_slug], 'S');

            // Auto-calculate skill rating from rank (1-10 scale)
            $rank_index = array_search($rank_tier, $rank_tiers[$game_slug]);
            $total_ranks = count($rank_tiers[$game_slug]);
            $admin_rating = ($rank_index !== false) ? (int)round(1 + ($rank_index / max(1, $total_ranks - 1)) * 9) : 5;

            $stmt = $pdo->prepare("INSERT INTO solo_players (game, real_name, player_name, contact_number, facebook_link, rank_tier, preferred_role, ref_code, admin_rating, payment_proof) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $game_slug,
                $real_name,
                $player_name,
                $contact_number,
                $facebook_link,
                $rank_tier,
                $preferred_role,
                $ref_code,
                $admin_rating,
                $upload_path,
            ]);

            $_SESSION['ref_code'] = $ref_code;
            flash('success', "You've been registered for $game_name solo matchmaking! We'll match you with players of similar rank.");
            header("Location: " . base_url("success.php?type=solo&game=$game_slug"));
            exit;
        } catch (Exception $e) {
            $errors[] = 'Registration failed. Please try again. Error: ' . $e->getMessage();
        }
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
                <label class="form-label">Real Name</label>
                <input type="text" name="real_name" class="form-control" placeholder="Your full real name"
                       value="<?= htmlspecialchars($_POST['real_name'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">In-Game Name (IGN)</label>
                <input type="text" name="player_name" class="form-control" placeholder="Your in-game name / gamertag"
                       value="<?= htmlspecialchars($_POST['player_name'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contact Number <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                <input type="tel" name="contact_number" class="form-control" placeholder="e.g. 09XX XXX XXXX"
                       value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Facebook Profile Link <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                <input type="url" name="facebook_link" class="form-control" placeholder="https://facebook.com/yourprofile"
                       value="<?= htmlspecialchars($_POST['facebook_link'] ?? '') ?>">
            </div>

            <div class="rank-notice">
                <div class="rank-notice-title">
                    <i class="bi bi-shield-check"></i> Keep it real!
                </div>
                <p>Be honest about your rank! We'll have experienced players to gauge skill levels during the tournament. Playing fair keeps the matches fun for everyone and protects your rep as a player. Let's keep it real!</p>
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

            <div class="mb-3">
                <label class="form-label">Preferred Role</label>
                <select name="preferred_role" class="form-control form-select" required>
                    <option value="">Select your preferred role</option>
                    <?php foreach ($roles[$game_slug] as $role): ?>
                        <option value="<?= htmlspecialchars($role) ?>"
                            <?= (($_POST['preferred_role'] ?? '') === $role) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text" style="font-size:0.8rem; margin-top:0.4rem; color: var(--accent-light);">
                    This helps us balance teams. You can still negotiate roles with your teammates.
                </div>
            </div>

            <div class="section-label">Payment</div>
            <div class="payment-info">
                <div class="fee">&#8369;100.00</div>
                <p>Entry fee per player. Pay via GCash or on-site at the venue.</p>
                <div class="gcash-number"><i class="bi bi-phone"></i> GCash: <strong>0927 872 8916</strong></div>
                <div class="gcash-number" style="margin-top:0.4rem; background:rgba(34,197,94,0.1); border-color:rgba(34,197,94,0.25); color:var(--success);"><i class="bi bi-shop"></i> Or pay <strong>on-site</strong> at Hide Out Cybernet Cafe on tournament day</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Payment Proof <span style="color:var(--text-muted); font-weight:400;">(upload screenshot)</span></label>
                <input type="file" name="payment_proof" class="form-control" accept="image/*,.pdf">
                <div class="form-text" style="font-size:0.8rem; margin-top:0.4rem; color: var(--warning);">
                    JPG, PNG, WebP, or PDF. Max 5MB.
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Or explain why you can't upload proof <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                <textarea name="payment_note" class="form-control" rows="2" placeholder="e.g. Will send proof later, paid in person, etc."><?= htmlspecialchars($_POST['payment_note'] ?? '') ?></textarea>
                <div class="form-text" style="font-size:0.8rem; margin-top:0.4rem; color: var(--warning);">
                    If you can't upload proof right now, tell us why. You must provide either a file or a reason.
                </div>
            </div>

            <div class="terms-section">
                <div class="terms-title"><i class="bi bi-shield-check"></i> Terms &amp; Consent</div>
                <div class="terms-body">
                    <p>By registering, you agree to the following:</p>
                    <ul>
                        <li><strong>Media Release:</strong> You consent to being photographed, filmed, and/or recorded during the tournament. All media may be used for promotional, social media, and public purposes by the organizers.</li>
                        <li><strong>Fair Play &amp; Integrity:</strong> You commit to playing with honesty and sportsmanship. Any form of cheating, rank manipulation, or unsportsmanlike behavior may result in disqualification.</li>
                        <li><strong>Build Your Reputation:</strong> This tournament is your stage. Your performance, conduct, and teamwork build your credibility as a player in the community. Play with honor.</li>
                    </ul>
                </div>
                <label class="terms-checkbox">
                    <input type="checkbox" name="agree_terms" required>
                    <span>I agree to the terms above and give my consent.</span>
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-check-circle"></i> Find Me a Team
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
