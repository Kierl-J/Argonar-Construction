<?php
require_once __DIR__ . '/../includes/db.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . base_url('admin/'));
    exit;
}

$valid_games = [
    'valorant'  => 'Valorant',
    'crossfire' => 'CrossFire',
    'dota2'     => 'Dota 2',
];

$game_filter = $_GET['game'] ?? '';
$message = '';
$msg_type = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Generate bracket
    if ($action === 'generate') {
        $gen_game = $_POST['game'] ?? '';
        if (isset($valid_games[$gen_game])) {
            // Delete existing matches for this game
            $del = $pdo->prepare("DELETE FROM matches WHERE game = ?");
            $del->execute([$gen_game]);

            // Get approved teams
            $stmt = $pdo->prepare("SELECT team_name FROM teams WHERE game = ? AND status = 'approved' ORDER BY RAND()");
            $stmt->execute([$gen_game]);
            $teams = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($teams) < 2) {
                $message = 'Need at least 2 approved teams to generate a bracket.';
                $msg_type = 'danger';
            } else {
                // Pad to next power of 2
                $count = count($teams);
                $size = 1;
                while ($size < $count) $size *= 2;

                // Fill with BYE
                while (count($teams) < $size) {
                    $teams[] = 'BYE';
                }

                // Create round 1 matches
                $round = 1;
                $match_order = 1;
                $insert = $pdo->prepare("INSERT INTO matches (game, round, match_order, team1_name, team2_name, status, winner, team1_score, team2_score) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0)");

                for ($i = 0; $i < count($teams); $i += 2) {
                    $t1 = $teams[$i];
                    $t2 = $teams[$i + 1];

                    // Auto-resolve BYE matches
                    if ($t2 === 'BYE') {
                        $insert->execute([$gen_game, $round, $match_order, $t1, $t2, 'completed', $t1]);
                    } elseif ($t1 === 'BYE') {
                        $insert->execute([$gen_game, $round, $match_order, $t1, $t2, 'completed', $t2]);
                    } else {
                        $insert->execute([$gen_game, $round, $match_order, $t1, $t2, 'pending', '']);
                    }
                    $match_order++;
                }

                // Create placeholder matches for subsequent rounds
                $matches_in_round = $size / 2;
                $round = 2;
                while ($matches_in_round > 1) {
                    $matches_in_round = $matches_in_round / 2;
                    for ($j = 1; $j <= $matches_in_round; $j++) {
                        $insert->execute([$gen_game, $round, $j, 'TBD', 'TBD', 'pending', '']);
                    }
                    $round++;
                }

                // Create finals
                if ($size > 2) {
                    // Finals already created in the loop above
                } else {
                    // Only 2 teams, round 1 is the finals — already created
                }

                $message = 'Bracket generated for ' . $valid_games[$gen_game] . ' with ' . $count . ' teams!';
                $msg_type = 'success';
                $game_filter = $gen_game;
            }
        }
    }

    // Update match result
    if ($action === 'update_match') {
        $match_id = (int)$_POST['match_id'];
        $team1_score = (int)$_POST['team1_score'];
        $team2_score = (int)$_POST['team2_score'];
        $winner = $_POST['winner'] ?? '';
        $status = $_POST['status'] ?? 'pending';

        $upd = $pdo->prepare("UPDATE matches SET team1_score = ?, team2_score = ?, winner = ?, status = ? WHERE id = ?");
        $upd->execute([$team1_score, $team2_score, $winner, $status, $match_id]);

        // If completed, advance winner to next round
        if ($status === 'completed' && $winner) {
            $match = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
            $match->execute([$match_id]);
            $m = $match->fetch();

            if ($m) {
                $next_round = $m['round'] + 1;
                $next_order = (int)ceil($m['match_order'] / 2);
                $slot = ($m['match_order'] % 2 === 1) ? 'team1_name' : 'team2_name';

                $next = $pdo->prepare("SELECT id FROM matches WHERE game = ? AND round = ? AND match_order = ?");
                $next->execute([$m['game'], $next_round, $next_order]);
                $next_match = $next->fetch();

                if ($next_match) {
                    $adv = $pdo->prepare("UPDATE matches SET $slot = ? WHERE id = ?");
                    $adv->execute([$winner, $next_match['id']]);
                }
            }
        }

        $message = 'Match updated!';
        $msg_type = 'success';
        $game_filter = $_POST['game'] ?? $game_filter;
    }

    // Finalize results
    if ($action === 'finalize') {
        $fin_game = $_POST['game'] ?? '';
        $season = $_POST['season'] ?? 'Season 1';

        if (isset($valid_games[$fin_game])) {
            // Delete existing results for this game/season
            $del = $pdo->prepare("DELETE FROM tournament_results WHERE game = ? AND season = ?");
            $del->execute([$fin_game, $season]);

            // Get finals match
            $finals = $pdo->prepare("SELECT * FROM matches WHERE game = ? ORDER BY round DESC, match_order ASC LIMIT 1");
            $finals->execute([$fin_game]);
            $final_match = $finals->fetch();

            if ($final_match && $final_match['winner']) {
                $winner_name = $final_match['winner'];
                $loser_name = ($final_match['winner'] === $final_match['team1_name']) ? $final_match['team2_name'] : $final_match['team1_name'];

                // Get semifinal losers for 3rd place
                $semis = $pdo->prepare("SELECT * FROM matches WHERE game = ? AND round = ? AND status = 'completed'");
                $semis->execute([$fin_game, $final_match['round'] - 1]);
                $semi_matches = $semis->fetchAll();

                $ins = $pdo->prepare("INSERT INTO tournament_results (game, season, placement, team_name, prize) VALUES (?, ?, ?, ?, ?)");
                $ins->execute([$fin_game, $season, 1, $winner_name, '']);
                $ins->execute([$fin_game, $season, 2, $loser_name, '']);

                $place = 3;
                foreach ($semi_matches as $sm) {
                    $semi_loser = ($sm['winner'] === $sm['team1_name']) ? $sm['team2_name'] : $sm['team1_name'];
                    if ($semi_loser && $semi_loser !== 'TBD' && $semi_loser !== 'BYE') {
                        $ins->execute([$fin_game, $season, $place, $semi_loser, '']);
                        $place++;
                    }
                }

                $message = 'Tournament results finalized for ' . $valid_games[$fin_game] . '!';
                $msg_type = 'success';
            } else {
                $message = 'Finals match has no winner yet.';
                $msg_type = 'danger';
            }
        }
        $game_filter = $fin_game;
    }
}

// Fetch matches for selected game
$matches = [];
$rounds = [];
if ($game_filter && isset($valid_games[$game_filter])) {
    $stmt = $pdo->prepare("SELECT * FROM matches WHERE game = ? ORDER BY round ASC, match_order ASC");
    $stmt->execute([$game_filter]);
    $matches = $stmt->fetchAll();
    foreach ($matches as $m) {
        $rounds[$m['round']][] = $m;
    }
}

// Count approved teams per game
$team_counts = [];
foreach ($valid_games as $slug => $name) {
    $c = $pdo->prepare("SELECT COUNT(*) FROM teams WHERE game = ? AND status = 'approved'");
    $c->execute([$slug]);
    $team_counts[$slug] = $c->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bracket Management — Argonar Tournament</title>
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
            <h1><i class="bi bi-diagram-3"></i> Bracket Management</h1>
        </div>
        <div class="admin-header-actions">
            <a href="<?= base_url('admin/') ?>" class="btn-back-site"><i class="bi bi-arrow-left"></i> Dashboard</a>
            <a href="<?= base_url() ?>" class="btn-back-site"><i class="bi bi-house"></i> Site</a>
            <a href="<?= base_url('admin/logout.php') ?>" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert-custom alert-<?= $msg_type ?>" style="margin-bottom:1.5rem;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Game Filter Tabs -->
    <div class="filter-tabs">
        <?php foreach ($valid_games as $slug => $name): ?>
            <a href="<?= base_url('admin/brackets.php?game=' . $slug) ?>" class="filter-tab <?= $game_filter === $slug ? 'active' : '' ?>">
                <?= $name ?> (<?= $team_counts[$slug] ?> teams)
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($game_filter && isset($valid_games[$game_filter])): ?>
        <!-- Generate Bracket -->
        <div class="admin-section">
            <h2><i class="bi bi-shuffle"></i> Generate Bracket — <?= $valid_games[$game_filter] ?></h2>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1rem;">
                This will randomize <?= $team_counts[$game_filter] ?> approved teams into a single-elimination bracket.
                <?php if (!empty($rounds)): ?>
                    <strong style="color:var(--warning);">Warning: existing bracket will be replaced!</strong>
                <?php endif; ?>
            </p>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Generate new bracket? This will delete any existing matches for this game.');">
                <input type="hidden" name="action" value="generate">
                <input type="hidden" name="game" value="<?= $game_filter ?>">
                <button type="submit" class="btn-submit" style="width:auto; padding:0.6rem 1.5rem; margin-top:0;">
                    <i class="bi bi-shuffle"></i> Generate Bracket
                </button>
            </form>
        </div>

        <!-- Edit Matches -->
        <?php if (!empty($rounds)): ?>
            <div class="admin-section">
                <h2><i class="bi bi-pencil-square"></i> Match Results</h2>
                <?php foreach ($rounds as $round_num => $round_matches): ?>
                    <h3 style="font-size:1rem; font-weight:700; color:var(--accent-light); margin:1.5rem 0 0.75rem;">
                        Round <?= $round_num ?>
                    </h3>
                    <div class="table-responsive" style="margin-bottom:1rem;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Team 1</th>
                                    <th>Score</th>
                                    <th>Team 2</th>
                                    <th>Score</th>
                                    <th>Winner</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($round_matches as $m): ?>
                                    <tr>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="update_match">
                                            <input type="hidden" name="match_id" value="<?= $m['id'] ?>">
                                            <input type="hidden" name="game" value="<?= $game_filter ?>">
                                            <td><?= $m['match_order'] ?></td>
                                            <td><strong><?= htmlspecialchars($m['team1_name']) ?></strong></td>
                                            <td>
                                                <input type="number" name="team1_score" value="<?= $m['team1_score'] ?>" min="0" class="form-control" style="width:70px; padding:0.3rem 0.5rem;">
                                            </td>
                                            <td><strong><?= htmlspecialchars($m['team2_name']) ?></strong></td>
                                            <td>
                                                <input type="number" name="team2_score" value="<?= $m['team2_score'] ?>" min="0" class="form-control" style="width:70px; padding:0.3rem 0.5rem;">
                                            </td>
                                            <td>
                                                <select name="winner" class="form-select" style="width:auto; padding:0.3rem 0.5rem; font-size:0.85rem;">
                                                    <option value="">— None —</option>
                                                    <?php if ($m['team1_name'] && $m['team1_name'] !== 'TBD'): ?>
                                                        <option value="<?= htmlspecialchars($m['team1_name']) ?>" <?= $m['winner'] === $m['team1_name'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($m['team1_name']) ?>
                                                        </option>
                                                    <?php endif; ?>
                                                    <?php if ($m['team2_name'] && $m['team2_name'] !== 'TBD'): ?>
                                                        <option value="<?= htmlspecialchars($m['team2_name']) ?>" <?= $m['winner'] === $m['team2_name'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($m['team2_name']) ?>
                                                        </option>
                                                    <?php endif; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="status" class="form-select" style="width:auto; padding:0.3rem 0.5rem; font-size:0.85rem;">
                                                    <option value="pending" <?= $m['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="live" <?= $m['status'] === 'live' ? 'selected' : '' ?>>Live</option>
                                                    <option value="completed" <?= $m['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                </select>
                                            </td>
                                            <td>
                                                <button type="submit" class="btn-approve">
                                                    <i class="bi bi-check-lg"></i> Save
                                                </button>
                                            </td>
                                        </form>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Finalize Results -->
            <div class="admin-section">
                <h2><i class="bi bi-trophy"></i> Finalize Tournament Results</h2>
                <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1rem;">
                    Creates leaderboard entries from the bracket. The finals winner must be decided first.
                </p>
                <form method="POST" onsubmit="return confirm('Finalize results? This will create/replace leaderboard entries.');">
                    <input type="hidden" name="action" value="finalize">
                    <input type="hidden" name="game" value="<?= $game_filter ?>">
                    <div style="display:flex; gap:1rem; align-items:end; flex-wrap:wrap;">
                        <div>
                            <label class="form-label">Season</label>
                            <input type="text" name="season" value="Season 1" class="form-control" style="width:200px;">
                        </div>
                        <button type="submit" class="btn-submit" style="width:auto; padding:0.6rem 1.5rem; margin-top:0;">
                            <i class="bi bi-trophy"></i> Finalize Results
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="reg-card" style="text-align:center; padding:3rem 2rem;">
            <i class="bi bi-hand-index" style="font-size:2.5rem; color:var(--accent-light); display:block; margin-bottom:1rem;"></i>
            <h3>Select a Game</h3>
            <p style="color:var(--text-muted);">Choose a game tab above to manage its bracket.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
