<?php
require_once __DIR__ . '/../includes/db.php';

$admin_password = 'argonar2026';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: ' . base_url('admin/'));
        exit;
    } else {
        $login_error = 'Incorrect password.';
    }
}

// Show login form if not authenticated
if (empty($_SESSION['admin_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login — Argonar Tournament</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    </head>
    <body>
        <div style="max-width:400px; margin:80px auto; padding:0 1rem;">
            <div class="reg-card" style="text-align:center;">
                <h2><i class="bi bi-shield-lock"></i> Admin Login</h2>
                <p class="subtitle">Enter password to access the dashboard</p>
                <?php if (!empty($login_error)): ?>
                    <div class="alert-custom alert-danger"><?= htmlspecialchars($login_error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="admin_login" value="1">
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" required autofocus>
                    </div>
                    <button type="submit" class="btn-submit">Login</button>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// --- Admin Dashboard ---

$game_filter = $_GET['game'] ?? 'all';
$valid_games = [
    'valorant'  => 'Valorant',
    'crossfire' => 'CrossFire',
    'dota2'     => 'Dota 2',
];

$game_icons = [
    'valorant'  => 'bi-crosshair',
    'crossfire' => 'bi-bullseye',
    'dota2'     => 'bi-shield-shaded',
];

// Handle admin add team
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_add_team'])) {
    $add_game = $_POST['add_game'] ?? '';
    $add_team = trim($_POST['add_team_name'] ?? '');
    $add_status = $_POST['add_status'] ?? 'approved';

    if (isset($valid_games[$add_game]) && $add_team !== '') {
        $prefixes = ['valorant' => 'VAL', 'crossfire' => 'CF', 'dota2' => 'DOTA'];
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $rand = '';
        for ($i = 0; $i < 4; $i++) $rand .= $chars[random_int(0, strlen($chars) - 1)];
        $ref = $prefixes[$add_game] . '-T-' . $rand;

        $stmt = $pdo->prepare("INSERT INTO teams (game, team_name, ref_code, member_1, member_2, member_3, member_4, member_5, payment_proof, status) VALUES (?, ?, ?, '', '', '', '', '', '', ?)");
        $stmt->execute([$add_game, $add_team, $ref, $add_status]);
        header('Location: ' . base_url('admin/?game=' . $add_game));
        exit;
    }
}

// Summary counts
$total_teams = (int)$pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
$total_solo  = (int)$pdo->query("SELECT COUNT(*) FROM solo_players")->fetchColumn();
$approved_total = (int)$pdo->query("SELECT (SELECT COUNT(*) FROM teams WHERE status='approved') + (SELECT COUNT(*) FROM solo_players WHERE status='approved')")->fetchColumn();
$pending_payments = (int)$pdo->query("SELECT (SELECT COUNT(*) FROM teams WHERE status='pending') + (SELECT COUNT(*) FROM solo_players WHERE status='pending')")->fetchColumn();
$rejected_total = (int)$pdo->query("SELECT (SELECT COUNT(*) FROM teams WHERE status='rejected') + (SELECT COUNT(*) FROM solo_players WHERE status='rejected')")->fetchColumn();

// Per-game counts
$game_stats = [];
foreach ($valid_games as $slug => $name) {
    $tc = (int)$pdo->prepare("SELECT COUNT(*) FROM teams WHERE game = ?");
    $tc = $pdo->prepare("SELECT COUNT(*) FROM teams WHERE game = ?");
    $tc->execute([$slug]);
    $team_count = (int)$tc->fetchColumn();

    $sc = $pdo->prepare("SELECT COUNT(*) FROM solo_players WHERE game = ?");
    $sc->execute([$slug]);
    $solo_count = (int)$sc->fetchColumn();

    $ac = $pdo->prepare("SELECT COUNT(*) FROM teams WHERE game = ? AND status = 'approved'");
    $ac->execute([$slug]);
    $approved_teams = (int)$ac->fetchColumn();

    $game_stats[$slug] = [
        'teams' => $team_count,
        'solos' => $solo_count,
        'approved' => $approved_teams,
        'effective' => $approved_teams + floor($solo_count / 5),
    ];
}

// Fetch teams
if ($game_filter !== 'all' && isset($valid_games[$game_filter])) {
    $stmt = $pdo->prepare("SELECT * FROM teams WHERE game = ? ORDER BY created_at DESC");
    $stmt->execute([$game_filter]);
} else {
    $stmt = $pdo->query("SELECT * FROM teams ORDER BY created_at DESC");
}
$teams = $stmt->fetchAll();

// Fetch solo players
if ($game_filter !== 'all' && isset($valid_games[$game_filter])) {
    $stmt2 = $pdo->prepare("SELECT * FROM solo_players WHERE game = ? ORDER BY created_at DESC");
    $stmt2->execute([$game_filter]);
} else {
    $stmt2 = $pdo->query("SELECT * FROM solo_players ORDER BY created_at DESC");
}
$solos = $stmt2->fetchAll();

// Check-in counts
$checkedin_teams = (int)$pdo->query("SELECT COUNT(*) FROM teams WHERE checked_in = 1")->fetchColumn();
$checkedin_solos = (int)$pdo->query("SELECT COUNT(*) FROM solo_players WHERE checked_in = 1")->fetchColumn();

// Disputes
$open_disputes = (int)$pdo->query("SELECT COUNT(*) FROM disputes WHERE status = 'open'")->fetchColumn();
$disputes = $pdo->query("SELECT * FROM disputes ORDER BY created_at DESC LIMIT 10")->fetchAll();

// Recent activity (last 5 registrations)
$recent = $pdo->query("
    (SELECT 'team' as type, team_name as name, game, status, created_at FROM teams ORDER BY created_at DESC LIMIT 5)
    UNION ALL
    (SELECT 'solo' as type, player_name as name, game, status, created_at FROM solo_players ORDER BY created_at DESC LIMIT 5)
    ORDER BY created_at DESC LIMIT 8
")->fetchAll();

$pageTitle = 'Admin Dashboard — Argonar Tournament';
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
    <!-- Header -->
    <div class="admin-header">
        <div>
            <h1><i class="bi bi-speedometer2"></i> Admin Dashboard</h1>
            <p style="margin:0; font-size:0.8rem; color:var(--text-muted);">Tournament Management</p>
        </div>
        <div class="admin-header-actions">
            <a href="<?= base_url('admin/brackets.php') ?>" class="btn-back-site"><i class="bi bi-diagram-3"></i> Brackets</a>
            <a href="<?= base_url('admin/matchmaking.php') ?>" class="btn-back-site"><i class="bi bi-puzzle"></i> Matchmaking</a>
            <a href="<?= base_url() ?>" class="btn-back-site"><i class="bi bi-arrow-left"></i> Site</a>
            <a href="<?= base_url('admin/logout.php') ?>" class="btn-logout"><i class="bi bi-box-arrow-right"></i></a>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-icon"><i class="bi bi-people-fill"></i></div>
            <div class="summary-info">
                <div class="summary-number"><?= $total_teams ?></div>
                <div class="summary-label">Teams</div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon" style="background:rgba(59,130,246,0.15); color:#3b82f6;"><i class="bi bi-person-fill"></i></div>
            <div class="summary-info">
                <div class="summary-number"><?= $total_solo ?></div>
                <div class="summary-label">Solo Players</div>
            </div>
        </div>
        <div class="summary-card" style="border-color:rgba(34,197,94,0.3);">
            <div class="summary-icon" style="background:rgba(34,197,94,0.15); color:var(--success);"><i class="bi bi-check-circle-fill"></i></div>
            <div class="summary-info">
                <div class="summary-number"><?= $approved_total ?></div>
                <div class="summary-label">Approved</div>
            </div>
        </div>
        <div class="summary-card summary-card-warning">
            <div class="summary-icon"><i class="bi bi-clock-fill"></i></div>
            <div class="summary-info">
                <div class="summary-number"><?= $pending_payments ?></div>
                <div class="summary-label">Pending</div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon" style="background:rgba(16,185,129,0.15); color:#10b981;"><i class="bi bi-check2-square"></i></div>
            <div class="summary-info">
                <div class="summary-number"><?= $checkedin_teams + $checkedin_solos ?></div>
                <div class="summary-label">Checked In</div>
            </div>
        </div>
        <?php if ($open_disputes > 0): ?>
        <div class="summary-card" style="border-color:rgba(239,68,68,0.3);">
            <div class="summary-icon" style="background:rgba(239,68,68,0.15); color:var(--danger);"><i class="bi bi-flag-fill"></i></div>
            <div class="summary-info">
                <div class="summary-number"><?= $open_disputes ?></div>
                <div class="summary-label">Open Disputes</div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Per-Game Breakdown -->
    <div class="admin-game-cards">
        <?php foreach ($valid_games as $slug => $name):
            $gs = $game_stats[$slug];
            $max = 16;
            $pct = min(100, round(($gs['effective'] / $max) * 100));
        ?>
        <a href="<?= base_url('admin/?game=' . $slug) ?>" class="admin-game-card <?= $game_filter === $slug ? 'admin-game-card-active' : '' ?>">
            <div class="admin-game-card-header">
                <i class="bi <?= $game_icons[$slug] ?>"></i>
                <span><?= $name ?></span>
            </div>
            <div class="admin-game-card-stats">
                <div class="admin-game-stat">
                    <span class="admin-game-stat-num"><?= $gs['teams'] ?></span>
                    <span class="admin-game-stat-label">Teams</span>
                </div>
                <div class="admin-game-stat">
                    <span class="admin-game-stat-num"><?= $gs['solos'] ?></span>
                    <span class="admin-game-stat-label">Solo</span>
                </div>
                <div class="admin-game-stat">
                    <span class="admin-game-stat-num"><?= $gs['effective'] ?>/<?= $max ?></span>
                    <span class="admin-game-stat-label">Slots</span>
                </div>
            </div>
            <div class="admin-game-bar">
                <div class="admin-game-bar-fill" style="width:<?= $pct ?>%"></div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Recent Activity + Quick Add -->
    <div class="admin-two-col">
        <!-- Recent Activity -->
        <div class="admin-panel">
            <div class="admin-panel-header">
                <i class="bi bi-clock-history"></i> Recent Registrations
            </div>
            <div class="admin-panel-body">
                <?php if (empty($recent)): ?>
                    <p class="no-data">No registrations yet.</p>
                <?php else: ?>
                    <?php foreach ($recent as $r): ?>
                        <div class="admin-activity-item">
                            <div class="admin-activity-icon">
                                <i class="bi <?= $r['type'] === 'team' ? 'bi-people-fill' : 'bi-person-fill' ?>"></i>
                            </div>
                            <div class="admin-activity-info">
                                <div class="admin-activity-name"><?= htmlspecialchars($r['name']) ?></div>
                                <div class="admin-activity-meta">
                                    <?= $valid_games[$r['game']] ?? $r['game'] ?> &middot; <?= $r['type'] === 'team' ? 'Team' : 'Solo' ?> &middot; <?= date('M j, g:ia', strtotime($r['created_at'])) ?>
                                </div>
                            </div>
                            <span class="status-badge status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Add Team -->
        <div class="admin-panel">
            <div class="admin-panel-header">
                <i class="bi bi-plus-circle"></i> Quick Add Team
            </div>
            <div class="admin-panel-body">
                <form method="POST">
                    <input type="hidden" name="admin_add_team" value="1">
                    <div class="mb-3">
                        <label style="font-size:0.75rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Game</label>
                        <select name="add_game" class="form-control form-select" required>
                            <?php foreach ($valid_games as $slug => $name): ?>
                                <option value="<?= $slug ?>"><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label style="font-size:0.75rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Team Name</label>
                        <input type="text" name="add_team_name" class="form-control" placeholder="Enter team name" required>
                    </div>
                    <div class="mb-3">
                        <label style="font-size:0.75rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Status</label>
                        <select name="add_status" class="form-control form-select">
                            <option value="approved">Approved</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-approve" style="width:100%; justify-content:center; padding:0.6rem 1rem; font-size:0.85rem;">
                        <i class="bi bi-plus-lg"></i> Add Team
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="<?= base_url('admin/') ?>" class="filter-tab <?= $game_filter === 'all' ? 'active' : '' ?>">
            <i class="bi bi-grid-3x3-gap"></i> All
        </a>
        <?php foreach ($valid_games as $slug => $name): ?>
            <a href="<?= base_url('admin/?game=' . $slug) ?>" class="filter-tab <?= $game_filter === $slug ? 'active' : '' ?>">
                <i class="bi <?= $game_icons[$slug] ?>"></i> <?= $name ?>
                <span class="filter-tab-count"><?= $game_stats[$slug]['teams'] + $game_stats[$slug]['solos'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Teams Table -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="bi bi-people"></i> Teams <span class="admin-count"><?= count($teams) ?></span></h2>
        </div>
        <?php if (empty($teams)): ?>
            <p class="no-data">No teams found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Ref</th>
                            <th>Game</th>
                            <th>Team</th>
                            <th>Members</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teams as $t): ?>
                            <tr id="team-row-<?= $t['id'] ?>">
                                <td><code style="font-size:0.7rem; color:var(--accent-light);"><?= htmlspecialchars($t['ref_code'] ?? '—') ?></code></td>
                                <td>
                                    <span class="admin-game-tag admin-game-<?= $t['game'] ?>">
                                        <i class="bi <?= $game_icons[$t['game']] ?? 'bi-controller' ?>"></i>
                                        <?= htmlspecialchars($valid_games[$t['game']] ?? $t['game']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:0.5rem;">
                                        <?php if (!empty($t['team_logo'])): ?>
                                            <img src="<?= base_url($t['team_logo']) ?>" alt="" style="width:24px; height:24px; border-radius:6px; object-fit:cover;">
                                        <?php endif; ?>
                                        <strong><?= htmlspecialchars($t['team_name']) ?></strong>
                                    </div>
                                </td>
                                <td class="members-cell">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if (!empty($t["member_$i"])): ?>
                                            <span class="member-tag"><?= htmlspecialchars($t["member_$i"]) ?></span>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </td>
                                <td>
                                    <?php if (!empty($t['payment_proof']) && $t['payment_proof'] === 'GCASH-AUTO'): ?>
                                        <span style="color:var(--success); font-size:0.75rem; font-weight:700;">
                                            <i class="bi bi-check-circle-fill"></i> Auto
                                        </span>
                                    <?php elseif (!empty($t['payment_proof']) && strpos($t['payment_proof'], 'NOTE:') === 0): ?>
                                        <span style="color:var(--warning); font-size:0.8rem;" title="<?= htmlspecialchars(substr($t['payment_proof'], 6)) ?>">
                                            <i class="bi bi-chat-left-text"></i> Note
                                        </span>
                                    <?php elseif (!empty($t['payment_proof'])): ?>
                                        <a href="<?= base_url('admin/view-proof.php?file=' . urlencode($t['payment_proof'])) ?>" target="_blank" class="proof-link">
                                            <i class="bi bi-file-earmark-image"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:0.75rem;">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $t['status'] ?>" id="team-status-<?= $t['id'] ?>">
                                        <?= ucfirst($t['status']) ?>
                                    </span>
                                </td>
                                <td style="font-size:0.75rem; color:var(--text-muted);">
                                    <?= date('M j', strtotime($t['created_at'])) ?><br>
                                    <span style="font-size:0.65rem;"><?= date('g:ia', strtotime($t['created_at'])) ?></span>
                                </td>
                                <td class="actions-cell" id="team-actions-<?= $t['id'] ?>">
                                    <a href="<?= base_url('admin/view.php?type=team&id=' . $t['id']) ?>" class="btn-view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= base_url('admin/edit.php?type=team&id=' . $t['id']) ?>" class="btn-edit" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($t['status'] === 'pending'): ?>
                                        <button class="btn-approve" onclick="doAction('team', <?= $t['id'] ?>, 'approve')" title="Approve">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button class="btn-reject" onclick="doAction('team', <?= $t['id'] ?>, 'reject')" title="Reject">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn-delete" onclick="doAction('team', <?= $t['id'] ?>, 'delete')" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Solo Players Table -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="bi bi-person"></i> Solo Players <span class="admin-count"><?= count($solos) ?></span></h2>
        </div>
        <?php if (empty($solos)): ?>
            <p class="no-data">No solo players found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Ref</th>
                            <th>Game</th>
                            <th>Player</th>
                            <th>Rank</th>
                            <th>Role</th>
                            <th>Skill</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solos as $s): ?>
                            <tr id="solo-row-<?= $s['id'] ?>">
                                <td><code style="font-size:0.7rem; color:var(--accent-light);"><?= htmlspecialchars($s['ref_code'] ?? '—') ?></code></td>
                                <td>
                                    <span class="admin-game-tag admin-game-<?= $s['game'] ?>">
                                        <i class="bi <?= $game_icons[$s['game']] ?? 'bi-controller' ?>"></i>
                                        <?= htmlspecialchars($valid_games[$s['game']] ?? $s['game']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($s['player_name']) ?></strong>
                                        <?php if (!empty($s['real_name'])): ?>
                                            <div style="font-size:0.7rem; color:var(--text-muted);"><?= htmlspecialchars($s['real_name']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><span class="member-tag"><?= htmlspecialchars($s['rank_tier']) ?></span></td>
                                <td style="font-size:0.8rem;"><?= htmlspecialchars($s['preferred_role'] ?? '—') ?></td>
                                <td>
                                    <div class="skill-gauge">
                                        <input type="number" min="0" max="10" value="<?= (int)($s['admin_rating'] ?? 0) ?>" id="rating-<?= $s['id'] ?>">
                                        <button onclick="saveRating(<?= $s['id'] ?>)"><i class="bi bi-check-lg"></i></button>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($s['payment_proof']) && $s['payment_proof'] === 'GCASH-AUTO'): ?>
                                        <span style="color:var(--success); font-size:0.75rem; font-weight:700;">
                                            <i class="bi bi-check-circle-fill"></i> Auto
                                        </span>
                                    <?php elseif (!empty($s['payment_proof']) && strpos($s['payment_proof'], 'NOTE:') === 0): ?>
                                        <span style="color:var(--warning); font-size:0.8rem;" title="<?= htmlspecialchars(substr($s['payment_proof'], 6)) ?>">
                                            <i class="bi bi-chat-left-text"></i> Note
                                        </span>
                                    <?php elseif (!empty($s['payment_proof'])): ?>
                                        <a href="<?= base_url('admin/view-proof.php?file=' . urlencode($s['payment_proof'])) ?>" target="_blank" class="proof-link">
                                            <i class="bi bi-file-earmark-image"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:0.75rem;">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $s['status'] ?>" id="solo-status-<?= $s['id'] ?>">
                                        <?= ucfirst($s['status']) ?>
                                    </span>
                                </td>
                                <td style="font-size:0.75rem; color:var(--text-muted);">
                                    <?= date('M j', strtotime($s['created_at'])) ?><br>
                                    <span style="font-size:0.65rem;"><?= date('g:ia', strtotime($s['created_at'])) ?></span>
                                </td>
                                <td class="actions-cell" id="solo-actions-<?= $s['id'] ?>">
                                    <a href="<?= base_url('admin/view.php?type=solo&id=' . $s['id']) ?>" class="btn-view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= base_url('admin/edit.php?type=solo&id=' . $s['id']) ?>" class="btn-edit" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($s['status'] === 'pending'): ?>
                                        <button class="btn-approve" onclick="doAction('solo', <?= $s['id'] ?>, 'approve')" title="Approve">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button class="btn-reject" onclick="doAction('solo', <?= $s['id'] ?>, 'reject')" title="Reject">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn-delete" onclick="doAction('solo', <?= $s['id'] ?>, 'delete')" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Disputes -->
    <?php if (!empty($disputes)): ?>
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="bi bi-flag-fill" style="color:var(--danger);"></i> Disputes <span class="admin-count"><?= count($disputes) ?></span></h2>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>Ref</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($disputes as $d): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($d['player_name']) ?></strong></td>
                            <td><code style="font-size:0.7rem;"><?= htmlspecialchars($d['ref_code'] ?: '—') ?></code></td>
                            <td><?= htmlspecialchars($d['subject']) ?></td>
                            <td style="max-width:250px; font-size:0.8rem; color:var(--text-muted);" title="<?= htmlspecialchars($d['message']) ?>">
                                <?= htmlspecialchars(substr($d['message'], 0, 80)) ?><?= strlen($d['message']) > 80 ? '...' : '' ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $d['status'] === 'open' ? 'pending' : ($d['status'] === 'reviewed' ? 'approved' : 'rejected') ?>">
                                    <?= ucfirst($d['status']) ?>
                                </span>
                            </td>
                            <td style="font-size:0.75rem; color:var(--text-muted);"><?= date('M j, g:ia', strtotime($d['created_at'])) ?></td>
                            <td>
                                <form method="POST" action="<?= base_url('admin/action.php') ?>" style="display:inline;">
                                    <input type="hidden" name="type" value="dispute">
                                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                    <?php if ($d['status'] === 'open'): ?>
                                        <button name="action" value="review_dispute" class="btn-approve" title="Mark Reviewed"><i class="bi bi-check-lg"></i></button>
                                    <?php endif; ?>
                                    <button name="action" value="close_dispute" class="btn-reject" title="Close"><i class="bi bi-x-lg"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function doAction(type, id, action) {
    if (!confirm('Are you sure you want to ' + action + ' this ' + (type === 'team' ? 'team' : 'player') + '?')) {
        return;
    }

    const formData = new FormData();
    formData.append('type', type);
    formData.append('id', id);
    formData.append('action', action);

    fetch('<?= base_url("admin/action.php") ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (action === 'delete') {
                document.getElementById(type + '-row-' + id).remove();
            } else {
                const statusEl = document.getElementById(type + '-status-' + id);
                const newStatus = action === 'approve' ? 'approved' : 'rejected';
                statusEl.className = 'status-badge status-' + newStatus;
                statusEl.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

                const actionsEl = document.getElementById(type + '-actions-' + id);
                actionsEl.querySelector('.btn-approve')?.remove();
                actionsEl.querySelector('.btn-reject')?.remove();
            }
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(err => {
        alert('Request failed: ' + err.message);
    });
}

function saveRating(id) {
    const val = document.getElementById('rating-' + id).value;
    const formData = new FormData();
    formData.append('type', 'solo');
    formData.append('id', id);
    formData.append('action', 'rate');
    formData.append('rating', val);

    fetch('<?= base_url("admin/action.php") ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(err => {
        alert('Request failed: ' + err.message);
    });
}
</script>

</body>
</html>
