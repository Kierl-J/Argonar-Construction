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

// Rank tier numeric values for seeding
$rank_values = [
    'valorant'  => ['Iron' => 1, 'Bronze' => 2, 'Silver' => 3, 'Gold' => 4, 'Platinum' => 5, 'Diamond' => 6, 'Ascendant' => 7, 'Immortal' => 8, 'Radiant' => 9],
    'crossfire' => ['Trainee' => 1, 'Rookie' => 2, 'Soldier' => 3, 'Veteran' => 4, 'Hero' => 5, 'Legend' => 6, 'Master' => 7, 'Grandmaster' => 8],
    'dota2'     => ['Herald' => 1, 'Guardian' => 2, 'Crusader' => 3, 'Archon' => 4, 'Legend' => 5, 'Ancient' => 6, 'Divine' => 7, 'Immortal' => 8],
];

$game_filter = $_GET['game'] ?? '';
$message = '';
$msg_type = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Delete bracket
    if ($action === 'delete_bracket') {
        $del_game = $_POST['game'] ?? '';
        if (isset($valid_games[$del_game])) {
            $del = $pdo->prepare("DELETE FROM matches WHERE game = ?");
            $del->execute([$del_game]);
            $message = 'Bracket deleted for ' . $valid_games[$del_game] . '. (' . $del->rowCount() . ' matches removed)';
            $msg_type = 'success';
            $game_filter = $del_game;
        }
    }

    // Generate bracket
    if ($action === 'generate') {
        $gen_game = $_POST['game'] ?? '';
        if (isset($valid_games[$gen_game])) {
            // Delete existing matches for this game
            $del = $pdo->prepare("DELETE FROM matches WHERE game = ?");
            $del->execute([$gen_game]);

            // Get approved teams with member ranks for seeding
            $stmt = $pdo->prepare("SELECT id, team_name, members_ranks FROM teams WHERE game = ? AND status = 'approved'");
            $stmt->execute([$gen_game]);
            $raw_teams = $stmt->fetchAll();

            if (count($raw_teams) < 2) {
                $message = 'Need at least 2 approved teams to generate a bracket.';
                $msg_type = 'danger';
            } else {
                // Calculate average rank value per team for seeding
                $game_ranks = $rank_values[$gen_game] ?? [];
                $team_seeds = [];
                foreach ($raw_teams as $rt) {
                    $avg_rank = 0;
                    if (!empty($rt['members_ranks'])) {
                        $entries = explode('|', $rt['members_ranks']);
                        $total = 0;
                        $counted = 0;
                        foreach ($entries as $entry) {
                            $parts = explode(':', $entry, 2);
                            $rank = $parts[1] ?? '';
                            if (!empty($rank) && isset($game_ranks[$rank])) {
                                $total += $game_ranks[$rank];
                                $counted++;
                            }
                        }
                        $avg_rank = $counted > 0 ? $total / $counted : 0;
                    }
                    $team_seeds[] = ['name' => $rt['team_name'], 'avg_rank' => $avg_rank];
                }

                // Sort by avg_rank DESC (strongest first = seed 1)
                usort($team_seeds, function($a, $b) {
                    return $b['avg_rank'] <=> $a['avg_rank'];
                });

                // Standard bracket seeding: 1v16, 8v9, 5v12, 4v13, 3v14, 6v11, 7v10, 2v15
                $count = count($team_seeds);
                $size = 1;
                while ($size < $count) $size *= 2;

                // Pad with BYEs
                while (count($team_seeds) < $size) {
                    $team_seeds[] = ['name' => 'BYE', 'avg_rank' => 0];
                }

                // Generate standard seeding order for bracket positions
                function seedOrder($n) {
                    if ($n === 1) return [0];
                    $prev = seedOrder($n / 2);
                    $result = [];
                    foreach ($prev as $seed) {
                        $result[] = $seed;
                        $result[] = $n - 1 - $seed;
                    }
                    return $result;
                }
                $order = seedOrder($size);
                $teams = [];
                foreach ($order as $idx) {
                    $teams[] = $team_seeds[$idx]['name'];
                }

                $insert = $pdo->prepare("INSERT INTO matches (game, bracket_side, round, match_order, team1_name, team2_name, status, winner, team1_score, team2_score) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 0)");

                // --- WINNERS BRACKET ---
                $match_order = 1;
                for ($i = 0; $i < count($teams); $i += 2) {
                    $t1 = $teams[$i];
                    $t2 = $teams[$i + 1];
                    if ($t2 === 'BYE') {
                        $insert->execute([$gen_game, 'winners', 1, $match_order, $t1, $t2, 'completed', $t1]);
                    } elseif ($t1 === 'BYE') {
                        $insert->execute([$gen_game, 'winners', 1, $match_order, $t1, $t2, 'completed', $t2]);
                    } else {
                        $insert->execute([$gen_game, 'winners', 1, $match_order, $t1, $t2, 'pending', '']);
                    }
                    $match_order++;
                }

                // Winners bracket subsequent rounds
                $matches_in_round = $size / 2;
                $round = 2;
                while ($matches_in_round > 1) {
                    $matches_in_round = $matches_in_round / 2;
                    for ($j = 1; $j <= $matches_in_round; $j++) {
                        $insert->execute([$gen_game, 'winners', $round, $j, 'TBD', 'TBD', 'pending', '']);
                    }
                    $round++;
                }
                $winners_rounds = $round - 1;

                // --- LOSERS BRACKET ---
                // Losers bracket has roughly (winners_rounds - 1) * 2 rounds
                $losers_rounds = max(1, ($winners_rounds - 1) * 2);
                $lr_matches = $size / 4; // First losers round matches
                for ($lr = 1; $lr <= $losers_rounds; $lr++) {
                    $num = max(1, (int)ceil($lr_matches));
                    for ($j = 1; $j <= $num; $j++) {
                        $insert->execute([$gen_game, 'losers', $lr, $j, 'TBD', 'TBD', 'pending', '']);
                    }
                    // Every 2 rounds, halve the matches
                    if ($lr % 2 === 0) {
                        $lr_matches = $lr_matches / 2;
                    }
                    if ($lr_matches < 1) break;
                }

                // --- GRAND FINALS ---
                $insert->execute([$gen_game, 'grand', 1, 1, 'TBD', 'TBD', 'pending', '']);

                $message = 'Rank-seeded double elimination bracket generated for ' . $valid_games[$gen_game] . ' with ' . $count . ' teams!';
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

        $scheduled_at = !empty($_POST['scheduled_at']) ? $_POST['scheduled_at'] : null;

        $upd = $pdo->prepare("UPDATE matches SET team1_score = ?, team2_score = ?, winner = ?, status = ?, scheduled_at = ? WHERE id = ?");
        $upd->execute([$team1_score, $team2_score, $winner, $status, $scheduled_at, $match_id]);

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
$bracket_data = [];
if ($game_filter && isset($valid_games[$game_filter])) {
    $stmt = $pdo->prepare("SELECT * FROM matches WHERE game = ? ORDER BY bracket_side ASC, round ASC, match_order ASC");
    $stmt->execute([$game_filter]);
    $matches = $stmt->fetchAll();
    foreach ($matches as $m) {
        $side = $m['bracket_side'] ?? 'winners';
        $bracket_data[$side][$m['round']][] = $m;
    }
}
$rounds = $bracket_data; // for empty check

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
                This will seed <?= $team_counts[$game_filter] ?> approved teams by average rank into a double-elimination bracket (strongest vs weakest in round 1).
                <?php if (!empty($rounds)): ?>
                    <strong style="color:var(--warning);">Warning: existing bracket will be replaced!</strong>
                <?php endif; ?>
            </p>
            <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                <form method="POST" style="display:inline;" onsubmit="return confirm('Generate new bracket? This will delete any existing matches for this game.');">
                    <input type="hidden" name="action" value="generate">
                    <input type="hidden" name="game" value="<?= $game_filter ?>">
                    <button type="submit" class="btn-submit" style="width:auto; padding:0.6rem 1.5rem; margin-top:0;">
                        <i class="bi bi-shuffle"></i> Generate Bracket
                    </button>
                </form>
                <?php if (!empty($bracket_data)): ?>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete ALL brackets for <?= $valid_games[$game_filter] ?>? This cannot be undone.');">
                        <input type="hidden" name="action" value="delete_bracket">
                        <input type="hidden" name="game" value="<?= $game_filter ?>">
                        <button type="submit" class="btn-delete" style="padding:0.6rem 1.5rem; font-size:0.85rem;">
                            <i class="bi bi-trash"></i> Delete Bracket
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Edit Matches -->
        <?php if (!empty($bracket_data)): ?>
            <?php
            $side_labels = ['winners' => 'Winners Bracket', 'losers' => 'Losers Bracket', 'grand' => 'Grand Finals'];
            $side_icons = ['winners' => 'bi-trophy', 'losers' => 'bi-arrow-repeat', 'grand' => 'bi-star-fill'];
            ?>
            <?php foreach (['winners', 'losers', 'grand'] as $side):
                if (empty($bracket_data[$side])) continue;
            ?>
            <div class="admin-section">
                <h2><i class="bi <?= $side_icons[$side] ?>"></i> <?= $side_labels[$side] ?></h2>
                <?php foreach ($bracket_data[$side] as $round_num => $round_matches): ?>
                    <h3 style="font-size:1rem; font-weight:700; color:var(--accent-light); margin:1.5rem 0 0.75rem;">
                        <?= $side === 'grand' ? 'Grand Finals' : "Round $round_num" ?>
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
                                    <th>Schedule</th>
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
                                                <input type="datetime-local" name="scheduled_at"
                                                       value="<?= $m['scheduled_at'] ? date('Y-m-d\TH:i', strtotime($m['scheduled_at'])) : '' ?>"
                                                       class="form-control" style="width:175px; padding:0.3rem 0.5rem; font-size:0.8rem;">
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
            <?php endforeach; ?>

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
