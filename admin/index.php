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

// Summary counts
$total_teams = $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
$total_solo  = $pdo->query("SELECT COUNT(*) FROM solo_players")->fetchColumn();
$pending_payments = $pdo->query("SELECT (SELECT COUNT(*) FROM teams WHERE status='pending') + (SELECT COUNT(*) FROM solo_players WHERE status='pending')")->fetchColumn();

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
    <div class="admin-header">
        <div>
            <h1><i class="bi bi-speedometer2"></i> Admin Dashboard</h1>
        </div>
        <div class="admin-header-actions">
            <a href="<?= base_url() ?>" class="btn-back-site"><i class="bi bi-arrow-left"></i> Back to Site</a>
            <a href="<?= base_url('admin/logout.php') ?>" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-icon"><i class="bi bi-people-fill"></i></div>
            <div class="summary-info">
                <div class="summary-number"><?= $total_teams ?></div>
                <div class="summary-label">Total Teams</div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon"><i class="bi bi-person-fill"></i></div>
            <div class="summary-info">
                <div class="summary-number"><?= $total_solo ?></div>
                <div class="summary-label">Solo Players</div>
            </div>
        </div>
        <div class="summary-card summary-card-warning">
            <div class="summary-icon"><i class="bi bi-clock-fill"></i></div>
            <div class="summary-info">
                <div class="summary-number"><?= $pending_payments ?></div>
                <div class="summary-label">Pending Payments</div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="<?= base_url('admin/') ?>" class="filter-tab <?= $game_filter === 'all' ? 'active' : '' ?>">All</a>
        <?php foreach ($valid_games as $slug => $name): ?>
            <a href="<?= base_url('admin/?game=' . $slug) ?>" class="filter-tab <?= $game_filter === $slug ? 'active' : '' ?>"><?= $name ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Teams Table -->
    <div class="admin-section">
        <h2><i class="bi bi-people"></i> Teams (<?= count($teams) ?>)</h2>
        <?php if (empty($teams)): ?>
            <p class="no-data">No teams found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Game</th>
                            <th>Team Name</th>
                            <th>Members</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teams as $t): ?>
                            <tr id="team-row-<?= $t['id'] ?>">
                                <td><?= $t['id'] ?></td>
                                <td><?= htmlspecialchars($valid_games[$t['game']] ?? $t['game']) ?></td>
                                <td><strong><?= htmlspecialchars($t['team_name']) ?></strong></td>
                                <td class="members-cell">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="member-tag"><?= htmlspecialchars($t["member_$i"]) ?></span>
                                    <?php endfor; ?>
                                </td>
                                <td>
                                    <?php if ($t['payment_proof']): ?>
                                        <a href="<?= base_url('admin/view-proof.php?file=' . urlencode($t['payment_proof'])) ?>" target="_blank" class="proof-link">
                                            <i class="bi bi-file-earmark-image"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $t['status'] ?>" id="team-status-<?= $t['id'] ?>">
                                        <?= ucfirst($t['status']) ?>
                                    </span>
                                </td>
                                <td class="actions-cell" id="team-actions-<?= $t['id'] ?>">
                                    <?php if ($t['status'] === 'pending'): ?>
                                        <button class="btn-approve" onclick="doAction('team', <?= $t['id'] ?>, 'approve')">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                        <button class="btn-reject" onclick="doAction('team', <?= $t['id'] ?>, 'reject')">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn-delete" onclick="doAction('team', <?= $t['id'] ?>, 'delete')">
                                        <i class="bi bi-trash"></i> Delete
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
        <h2><i class="bi bi-person"></i> Solo Players (<?= count($solos) ?>)</h2>
        <?php if (empty($solos)): ?>
            <p class="no-data">No solo players found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Game</th>
                            <th>Player Name</th>
                            <th>Rank</th>
                            <th>Role</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solos as $s): ?>
                            <tr id="solo-row-<?= $s['id'] ?>">
                                <td><?= $s['id'] ?></td>
                                <td><?= htmlspecialchars($valid_games[$s['game']] ?? $s['game']) ?></td>
                                <td><strong><?= htmlspecialchars($s['player_name']) ?></strong></td>
                                <td><?= htmlspecialchars($s['rank_tier']) ?></td>
                                <td><?= htmlspecialchars($s['preferred_role'] ?? '—') ?></td>
                                <td>
                                    <?php if ($s['payment_proof']): ?>
                                        <a href="<?= base_url('admin/view-proof.php?file=' . urlencode($s['payment_proof'])) ?>" target="_blank" class="proof-link">
                                            <i class="bi bi-file-earmark-image"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $s['status'] ?>" id="solo-status-<?= $s['id'] ?>">
                                        <?= ucfirst($s['status']) ?>
                                    </span>
                                </td>
                                <td class="actions-cell" id="solo-actions-<?= $s['id'] ?>">
                                    <?php if ($s['status'] === 'pending'): ?>
                                        <button class="btn-approve" onclick="doAction('solo', <?= $s['id'] ?>, 'approve')">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                        <button class="btn-reject" onclick="doAction('solo', <?= $s['id'] ?>, 'reject')">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn-delete" onclick="doAction('solo', <?= $s['id'] ?>, 'delete')">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
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
</script>

</body>
</html>
