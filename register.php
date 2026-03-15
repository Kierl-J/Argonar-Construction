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

$game_prefixes = [
    'valorant'  => 'VAL',
    'crossfire' => 'CF',
    'dota2'     => 'DOTA',
];

$game_name = $valid_games[$game_slug];
$pageTitle = "Register — $game_name";
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
    $members = [];
    for ($i = 1; $i <= 5; $i++) {
        $members[$i] = trim($_POST["member_$i"] ?? '');
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
                $filename = $game_slug . '_' . preg_replace('/[^a-z0-9]/', '', strtolower($team_name)) . '_' . time() . '.' . $ext;
                $dest = $upload_dir . '/' . $filename;

                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    $errors[] = 'Failed to upload file. Please try again.';
                } else {
                    $upload_path = 'uploads/payment_proofs/' . $filename;
                }
            }
        }
    }

    // Handle team logo upload (optional)
    $logo_path = '';
    if (empty($errors) && isset($_FILES['team_logo']) && $_FILES['team_logo']['error'] === UPLOAD_ERR_OK) {
        $logo = $_FILES['team_logo'];
        $logo_allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $logo_max = 2 * 1024 * 1024; // 2MB

        if (!in_array($logo['type'], $logo_allowed)) {
            $errors[] = 'Team logo must be JPG, PNG, or WebP.';
        } elseif ($logo['size'] > $logo_max) {
            $errors[] = 'Team logo is too large. Maximum 2MB.';
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
        $ref_code = generate_ref_code($pdo, $game_prefixes[$game_slug], 'T');

        $stmt = $pdo->prepare("INSERT INTO teams (game, team_name, team_logo, ref_code, member_1, member_2, member_3, member_4, member_5, payment_proof) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $game_slug,
            $team_name,
            $logo_path,
            $ref_code,
            $members[1], $members[2], $members[3], $members[4], $members[5],
            $upload_path,
        ]);

        $_SESSION['ref_code'] = $ref_code;
        flash('success', "Team \"$team_name\" registered for $game_name! We'll review your payment and confirm shortly.");
        header("Location: " . base_url("success.php?game=$game_slug"));
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
                <label class="form-label">Team Logo <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                <input type="file" name="team_logo" class="form-control" accept="image/*">
                <div class="form-text text-muted" style="font-size:0.8rem; margin-top:0.4rem;">
                    JPG, PNG, or WebP. Max 2MB. Will be shown on the registered teams list.
                </div>
            </div>

            <div class="section-label">Members (5 Players)</div>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="mb-3">
                    <label class="form-label">
                        <?= $i === 1 ? 'Team Captain' : "Member $i" ?>
                    </label>
                    <input type="text" name="member_<?= $i ?>" class="form-control"
                           placeholder="Full name or in-game name"
                           value="<?= htmlspecialchars($_POST["member_$i"] ?? '') ?>" required>
                </div>
            <?php endfor; ?>

            <div class="section-label">Payment</div>
            <div class="payment-info">
                <div class="fee">&#8369;500.00</div>
                <p>Entry fee per team. Send payment via GCash then upload your proof below.</p>
                <div class="gcash-number"><i class="bi bi-phone"></i> GCash: <strong>0927 872 8916</strong></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Payment Proof</label>
                <input type="file" name="payment_proof" class="form-control" accept="image/*,.pdf" required>
                <div class="form-text text-muted" style="font-size:0.8rem; margin-top:0.4rem;">
                    JPG, PNG, WebP, or PDF. Max 5MB.
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
                <i class="bi bi-check-circle"></i> Submit Registration
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
