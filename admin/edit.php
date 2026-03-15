<?php
require_once __DIR__ . '/../includes/db.php';

// Require admin session
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . base_url('admin/'));
    exit;
}

$type = $_GET['type'] ?? '';
$id   = (int)($_GET['id'] ?? 0);

if (!in_array($type, ['team', 'solo']) || $id <= 0) {
    header('Location: ' . base_url('admin/'));
    exit;
}

$table = $type === 'team' ? 'teams' : 'solo_players';

// Handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($type === 'team') {
        $stmt = $pdo->prepare("UPDATE teams SET game = ?, team_name = ?, contact_number = ?, facebook_link = ?, member_1 = ?, member_2 = ?, member_3 = ?, member_4 = ?, member_5 = ?, status = ? WHERE id = ?");
        $stmt->execute([
            $_POST['game'] ?? '',
            $_POST['team_name'] ?? '',
            $_POST['contact_number'] ?? '',
            $_POST['facebook_link'] ?? '',
            $_POST['member_1'] ?? '',
            $_POST['member_2'] ?? '',
            $_POST['member_3'] ?? '',
            $_POST['member_4'] ?? '',
            $_POST['member_5'] ?? '',
            $_POST['status'] ?? 'pending',
            $id,
        ]);
    } else {
        $stmt = $pdo->prepare("UPDATE solo_players SET game = ?, real_name = ?, player_name = ?, contact_number = ?, facebook_link = ?, rank_tier = ?, preferred_role = ?, status = ? WHERE id = ?");
        $stmt->execute([
            $_POST['game'] ?? '',
            $_POST['real_name'] ?? '',
            $_POST['player_name'] ?? '',
            $_POST['contact_number'] ?? '',
            $_POST['facebook_link'] ?? '',
            $_POST['rank_tier'] ?? '',
            $_POST['preferred_role'] ?? '',
            $_POST['status'] ?? 'pending',
            $id,
        ]);
    }

    header('Location: ' . base_url('admin/'));
    exit;
}

// Load current record
$stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch();

if (!$record) {
    header('Location: ' . base_url('admin/'));
    exit;
}

$valid_games = [
    'valorant'  => 'Valorant',
    'crossfire' => 'CrossFire',
    'dota2'     => 'Dota 2',
];

$pageTitle = 'Edit ' . ($type === 'team' ? 'Team' : 'Solo Player') . ' — Argonar Tournament';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
</head>
<body>

<div class="admin-container">
    <div class="admin-header">
        <div>
            <h1><i class="bi bi-pencil-square"></i> Edit <?= $type === 'team' ? 'Team' : 'Solo Player' ?></h1>
        </div>
        <div class="admin-header-actions">
            <a href="<?= base_url('admin/') ?>" class="btn-back-site"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>

    <div class="admin-section" style="max-width:700px;">
        <form method="POST">
            <div style="display:grid; gap:1rem;">
                <!-- Game -->
                <div>
                    <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Game</label>
                    <select name="game" class="form-control form-select" required>
                        <?php foreach ($valid_games as $slug => $name): ?>
                            <option value="<?= $slug ?>" <?= ($record['game'] ?? '') === $slug ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($type === 'team'): ?>
                    <!-- Team Name -->
                    <div>
                        <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Team Name</label>
                        <input type="text" name="team_name" class="form-control" value="<?= htmlspecialchars($record['team_name'] ?? '') ?>" required>
                    </div>
                    <!-- Contact Number -->
                    <div>
                        <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($record['contact_number'] ?? '') ?>">
                    </div>
                    <!-- Facebook Link -->
                    <div>
                        <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Facebook Link</label>
                        <input type="text" name="facebook_link" class="form-control" value="<?= htmlspecialchars($record['facebook_link'] ?? '') ?>">
                    </div>
                    <!-- Members 1-5 -->
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <div>
                            <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Member <?= $i ?></label>
                            <input type="text" name="member_<?= $i ?>" class="form-control" value="<?= htmlspecialchars($record["member_$i"] ?? '') ?>">
                        </div>
                    <?php endfor; ?>
                <?php else: ?>
                    <!-- Real Name -->
                    <div>
                        <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Real Name</label>
                        <input type="text" name="real_name" class="form-control" value="<?= htmlspecialchars($record['real_name'] ?? '') ?>">
                    </div>
                    <!-- Player Name (IGN) -->
                    <div>
                        <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Player Name (IGN)</label>
                        <input type="text" name="player_name" class="form-control" value="<?= htmlspecialchars($record['player_name'] ?? '') ?>" required>
                    </div>
                    <!-- Contact Number -->
                    <div>
                        <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($record['contact_number'] ?? '') ?>">
                    </div>
                    <!-- Facebook Link -->
                    <div>
                        <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Facebook Link</label>
                        <input type="text" name="facebook_link" class="form-control" value="<?= htmlspecialchars($record['facebook_link'] ?? '') ?>">
                    </div>
                    <!-- Rank/Tier -->
                    <div>
                        <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Rank / Tier</label>
                        <input type="text" name="rank_tier" class="form-control" value="<?= htmlspecialchars($record['rank_tier'] ?? '') ?>">
                    </div>
                    <!-- Preferred Role -->
                    <div>
                        <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Preferred Role</label>
                        <input type="text" name="preferred_role" class="form-control" value="<?= htmlspecialchars($record['preferred_role'] ?? '') ?>">
                    </div>
                <?php endif; ?>

                <!-- Status -->
                <div>
                    <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Status</label>
                    <select name="status" class="form-control form-select">
                        <option value="pending" <?= ($record['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= ($record['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= ($record['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>

                <div style="margin-top:0.5rem;">
                    <button type="submit" class="btn-submit" style="margin-right:0.5rem;">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                    <a href="<?= base_url('admin/') ?>" style="color:var(--text-muted); text-decoration:none; font-size:0.9rem;">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

</body>
</html>
