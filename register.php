<?php
require_once __DIR__ . '/includes/db.php';

$valid_games = [
    'valorant'  => 'Valorant',
    'crossfire' => 'CrossFire',
    'dota2'     => 'Dota 2',
];

$game_slug = $_GET['game'] ?? '';
if (!isset($valid_games[$game_slug])) {
    header('Location: ' . base_url());
    exit;
}

$rank_tiers = [
    'valorant'  => ['Iron', 'Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond', 'Ascendant', 'Immortal', 'Radiant'],
    'crossfire' => ['Trainee', 'Rookie', 'Soldier', 'Veteran', 'Hero', 'Legend', 'Master', 'Grandmaster'],
    'dota2'     => ['Herald', 'Guardian', 'Crusader', 'Archon', 'Legend', 'Ancient', 'Divine', 'Immortal'],
];

$game_prefixes = [
    'valorant'  => 'VAL',
    'crossfire' => 'CF',
    'dota2'     => 'DOTA',
];

$game_name = $valid_games[$game_slug];
$pageTitle = "Register — $game_name";
$pageDescription = "Register your team for $game_name tournament. ₱500 entry fee. Double elimination format.";
$errors = [];

function generate_ref_code($pdo, $prefix, $type) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    for ($attempt = 0; $attempt < 20; $attempt++) {
        $rand = '';
        for ($i = 0; $i < 4; $i++) {
            $rand .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $code = $prefix . '-' . $type . '-' . $rand;
        // Check uniqueness in both tables
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
    $team_name = trim($_POST['team_name'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $facebook_link = trim($_POST['facebook_link'] ?? '');
    $members = [];
    $member_ranks = [];
    for ($i = 1; $i <= 5; $i++) {
        $members[$i] = trim($_POST["member_$i"] ?? '');
        $member_ranks[$i] = trim($_POST["member_rank_$i"] ?? '');
    }

    // Validate
    if ($team_name === '') {
        $errors[] = 'Team name is required.';
    }
    foreach ($members as $i => $m) {
        if ($m === '') {
            $errors[] = "Member $i name is required.";
        }
    }

    // Check duplicate team name per game
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM teams WHERE game = ? AND team_name = ?");
        $check->execute([$game_slug, $team_name]);
        if ($check->fetch()) {
            $errors[] = "Team name \"$team_name\" is already registered for $game_name.";
        }
    }

    // Payment proof is handled on the ticket page after registration
    $upload_path = '';

    // Handle team logo upload (optional)
    $logo_path = '';
    if (empty($errors) && isset($_FILES['team_logo']) && $_FILES['team_logo']['error'] === UPLOAD_ERR_OK) {
        $logo = $_FILES['team_logo'];
        $logo_allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $logo_max = 2 * 1024 * 1024; // 2MB

        if (!in_array($logo['type'], $logo_allowed)) {
            $errors[] = 'Team logo must be JPG, PNG, or WebP.';
        } elseif ($logo['size'] > $logo_max) {
            $errors[] = 'Team logo is too large. Maximum 5MB.';
        } else {
            $logo_dir = __DIR__ . '/uploads/team_logos';
            if (!is_dir($logo_dir)) {
                mkdir($logo_dir, 0755, true);
            }
            $logo_ext = pathinfo($logo['name'], PATHINFO_EXTENSION);
            $logo_filename = $game_slug . '_' . preg_replace('/[^a-z0-9]/', '', strtolower($team_name)) . '_' . time() . '.' . $logo_ext;
            $logo_dest = $logo_dir . '/' . $logo_filename;

            if (move_uploaded_file($logo['tmp_name'], $logo_dest)) {
                $logo_path = 'uploads/team_logos/' . $logo_filename;
            }
        }
    }

    // Insert
    if (empty($errors)) {
        try {
            $ref_code = generate_ref_code($pdo, $game_prefixes[$game_slug], 'T');

            $members_data = '';
            for ($i = 1; $i <= 5; $i++) {
                $members_data .= ($i > 1 ? '|' : '') . $members[$i] . ':' . $member_ranks[$i];
            }

            $stmt = $pdo->prepare("INSERT INTO teams (game, team_name, team_logo, ref_code, contact_number, facebook_link, member_1, member_2, member_3, member_4, member_5, members_ranks, payment_proof) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $game_slug,
                $team_name,
                $logo_path,
                $ref_code,
                $contact_number,
                $facebook_link,
                $members[1], $members[2], $members[3], $members[4], $members[5],
                $members_data,
                $upload_path,
            ]);

            $_SESSION['ref_code'] = $ref_code;
            header("Location: " . base_url("ticket.php?ref=$ref_code&type=team&game=$game_slug"));
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
        <h2><?= htmlspecialchars($game_name) ?></h2>
        <p class="subtitle">Register your team for the tournament</p>

        <?php if (!empty($errors)): ?>
            <div class="alert-custom alert-danger">
                <i class="bi bi-exclamation-circle"></i>
                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="section-label">Team Info</div>
            <div class="mb-3">
                <label class="form-label">Team Name</label>
                <input type="text" name="team_name" class="form-control" placeholder="e.g. Shadow Wolves"
                       value="<?= htmlspecialchars($_POST['team_name'] ?? '') ?>" required>
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
            <div class="mb-3">
                <label class="form-label">Team Logo <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                <input type="file" name="team_logo" class="form-control" accept="image/*">
                <div class="form-text text-muted" style="font-size:0.8rem; margin-top:0.4rem;">
                    JPG, PNG, or WebP. Max 5MB. Will be shown on the registered teams list.
                </div>
            </div>

            <div class="section-label">Members (5 Players)</div>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="mb-3" style="background:rgba(255,255,255,0.02); border:1px solid var(--border); border-radius:10px; padding:0.75rem 1rem;">
                    <label class="form-label" style="margin-bottom:0.5rem;">
                        <?= $i === 1 ? 'Team Captain' : "Member $i" ?>
                    </label>
                    <input type="text" name="member_<?= $i ?>" class="form-control" style="margin-bottom:0.5rem;"
                           placeholder="Full name or in-game name"
                           value="<?= htmlspecialchars($_POST["member_$i"] ?? '') ?>" required>
                    <select name="member_rank_<?= $i ?>" class="form-control form-select" required>
                        <option value="">Select rank</option>
                        <?php foreach ($rank_tiers[$game_slug] as $rank): ?>
                            <option value="<?= htmlspecialchars($rank) ?>"
                                <?= (($_POST["member_rank_$i"] ?? '') === $rank) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rank) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endfor; ?>

            <div class="alert-custom alert-danger" style="margin-top:0.5rem; font-size:0.8rem;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <strong>Rank Integrity Warning:</strong> Make sure your submitted rank is genuine and reflects your actual in-game rank. Any form of rank manipulation, smurfing, or dishonesty will result in disqualification and penalties at the discretion of the organizers.
            </div>

            <div class="section-label">Payment</div>
            <div class="payment-info">
                <div class="fee">&#8369;500.00</div>
                <p>Entry fee per team. You'll be directed to the payment page after registering.</p>
                <div class="gcash-number"><i class="bi bi-phone"></i> GCash auto-detect or InstaPay QR</div>
                <div class="gcash-number" style="margin-top:0.4rem; background:rgba(34,197,94,0.1); border-color:rgba(34,197,94,0.25); color:var(--success);"><i class="bi bi-shop"></i> Or pay <strong>on-site</strong> at Hide Out Cybernet Cafe</div>
            </div>

            <div class="terms-section">
                <div class="terms-title"><i class="bi bi-shield-check"></i> Terms &amp; Consent</div>
                <div class="terms-body">
                    <p>By registering, you agree to the following:</p>
                    <ul>
                        <li><strong>Media Release:</strong> You consent to being photographed, filmed, and/or recorded during the tournament. All media may be used for promotional, social media, and public purposes by the organizers.</li>
                        <li><strong>Fair Play &amp; Integrity:</strong> You commit to playing with honesty and sportsmanship. Any form of cheating, rank manipulation, or unsportsmanlike behavior may result in disqualification.</li>
                        <li><strong>Violations &amp; Penalties:</strong> Rank manipulation, submitting false information, smurfing, or any form of dishonesty will be subject to penalties — including disqualification and prize forfeiture — at the discretion of Argonar Software OPC and OCPD.</li>
                        <li><strong>Pay Later Option:</strong> You may register now and pay before the deadline via GCash, InstaPay QR, or on-site at the venue. Unpaid registrations by the deadline may be forfeited.</li>
                        <li><strong>Build Your Reputation:</strong> This tournament is your stage. Your performance, conduct, and teamwork build your credibility as a player in the community. Play with honor.</li>
                    </ul>
                </div>
                <label class="terms-checkbox">
                    <input type="checkbox" name="agree_terms" required>
                    <span>I agree to the terms above and give my consent.</span>
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-check-circle"></i> Submit Registration
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
