<?php
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Argonar Tournament';
$pageDescription = 'Join the Argonar Gaming Tournament! Valorant, CrossFire & Dota 2. ₱9,000 cash prize or free paragliding experience. Register your team or enter solo.';

// Count registered teams per game
$counts = [];
$stmt = $pdo->query("SELECT game, COUNT(*) as total FROM teams GROUP BY game");
while ($row = $stmt->fetch()) {
    $counts[$row['game']] = $row['total'];
}

// Count solo players waiting per game
$solo_counts = [];
$stmt = $pdo->query("SELECT game, COUNT(*) as total FROM solo_players GROUP BY game");
while ($row = $stmt->fetch()) {
    $solo_counts[$row['game']] = $row['total'];
}

// Get registered teams per game
$registered_teams = [];
$stmt = $pdo->query("SELECT game, team_name, team_logo, status, members_ranks, member_1, member_2, member_3, member_4, member_5 FROM teams ORDER BY created_at DESC");
while ($row = $stmt->fetch()) {
    $registered_teams[$row['game']][] = $row;
}

// Get solo players per game
$solo_players = [];
$stmt = $pdo->query("SELECT game, player_name, rank_tier, preferred_role, status FROM solo_players ORDER BY created_at DESC");
while ($row = $stmt->fetch()) {
    $solo_players[$row['game']][] = $row;
}

$games = [
    [
        'slug'    => 'valorant',
        'name'    => 'Valorant',
        'logo'    => 'images/valorant.png',
        'desc'    => '5v5 tactical shooter. Show your aim and strategy.',
        'banner'  => 'valorant',
        'date'    => '2026-04-19',
        'reg_deadline' => '2026-04-17',
    ],
    [
        'slug'    => 'crossfire',
        'name'    => 'CrossFire',
        'logo'    => 'images/crossfire.png',
        'desc'    => 'Classic FPS action on GameClub. Lock and load.',
        'banner'  => 'crossfire',
        'date'    => '2026-04-19',
        'reg_deadline' => '2026-04-17',
    ],
    [
        'slug'    => 'dota2',
        'name'    => 'Dota 2',
        'logo'    => 'images/dota.webp',
        'desc'    => '5v5 MOBA battle. Outplay, outfarm, outdraft.',
        'banner'  => 'dota2',
        'date'    => '2026-04-19',
        'reg_deadline' => '2026-04-17',
    ],
];

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width:1000px; margin:0 auto 1rem; padding:0 1rem; text-align:center;">
    <div style="background:rgba(124,58,237,0.08); border:1px solid rgba(124,58,237,0.25); border-radius:10px; padding:0.6rem 1rem; font-size:0.8rem; color:var(--text-muted);">
        <i class="bi bi-building" style="color:var(--accent-light);"></i>
        This event is officially organized by <strong style="color:var(--accent-light);">Argonar Software OPC</strong> and <strong style="color:var(--accent-light);">OCPD Oslob Cebu Paragliding</strong>.
        All rules, penalties, and final decisions are under the authority of the organizers.
    </div>
</div>

<div class="sponsors-bar">
    <div class="sponsor-block">
        <span class="sponsor-label">Presented by</span>
        <div class="sponsor-logo">
            <img src="<?= base_url('images/argonar-logo.svg') ?>" alt="Argonar Software OPC">
            <div class="sponsor-text">
                <strong>ARGONAR</strong>
                <span>SOFTWARE OPC</span>
            </div>
        </div>
    </div>
    <div class="sponsor-divider"></div>
    <div class="sponsor-block">
        <span class="sponsor-label">Venue hosted by</span>
        <div class="sponsor-logo">
            <img src="<?= base_url('images/hideout.jpg') ?>" alt="Hide Out Cybernet Cafe" class="venue-logo">
            <div class="sponsor-text">
                <strong>HIDE OUT</strong>
                <span>CYBERNET CAFE</span>
            </div>
        </div>
        <div class="venue-address">
            <i class="bi bi-geo-alt-fill"></i> Brgy. Inayawan, Inayawan Central, Cebu City, 6000
        </div>
    </div>
</div>

<div class="hero">
    <h1>Tournament Registration</h1>
    <p>Pick your game and join the tournament. Register as a team or enter solo and get matched by rank.</p>
    <div class="winner-banner">
        <i class="bi bi-trophy-fill"></i> Winner Takes All — One champion per game. No runner-up, no second place.
    </div>
    <div class="winner-banner" style="margin-top:0.5rem; background:rgba(124,58,237,0.1); border-color:rgba(124,58,237,0.3); color:var(--accent-light);">
        <i class="bi bi-diagram-3"></i> Double Elimination — Winners &amp; Losers bracket. You have to lose twice to be out.
    </div>
    <div class="prize-pick">
        <div class="prize-pick-title">The winning team picks ONE reward:</div>
        <div class="prize-options">
            <div class="prize-option">
                <div class="prize-icon"><i class="bi bi-cash-stack"></i></div>
                <div class="prize-amount">&#8369;9,000 Cash</div>
                <div class="prize-desc">Split among the team</div>
            </div>
            <div class="prize-or">OR</div>
            <a href="https://oslobcebuparagliding.com/" target="_blank" rel="noopener" class="prize-option prize-option-link">
                <div class="prize-icon"><i class="bi bi-wind"></i></div>
                <div class="prize-amount">Paragliding Experience</div>
                <div class="prize-desc">Free tickets for the whole team — by <strong>OCPD Oslob Cebu</strong></div>
                <div class="prize-desc" style="font-size:0.7rem; margin-top:0.3rem;">Tickets only. Travel &amp; logistics are on the winners.</div>
                <div class="prize-desc" style="font-size:0.7rem; margin-top:0.2rem; color:#38bdf8;"><i class="bi bi-globe"></i> oslobcebuparagliding.com</div>
            </a>
        </div>
        <div class="prize-note" style="color:var(--danger); font-weight:700; font-style:normal; font-size:0.9rem; margin-top:1.25rem; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); padding:0.6rem 1rem; border-radius:8px;">
            <i class="bi bi-exclamation-triangle-fill"></i> Winners must choose one. You cannot claim both.
        </div>
        <div class="prize-note">Cash prize is subject to change based on the organizer's decision and the number of registered participants.</div>

        <div class="paraglide-promo">
            <div class="paraglide-heading">This could be YOU and your team!</div>
            <div class="paraglide-gallery">
                <img src="<?= base_url('images/paragliding1.jpg') ?>" alt="Paragliding in Oslob Cebu">
                <img src="<?= base_url('images/paragliding2.jpg') ?>" alt="Paragliding experience">
            </div>
            <div class="paraglide-cta">
                <i class="bi bi-wind"></i> Win the tournament and soar above Oslob, Cebu — free paragliding tickets for your whole squad!
            </div>
        </div>
    </div>
</div>

<?php
$max_teams = 16;
// Solo players form teams of 5
function estimate_date($team_count, $solo_count, $fixed_date = null) {
    if ($fixed_date) {
        $date = date('F j, Y', strtotime($fixed_date));
        return "Tournament date: $date";
    }
    $total = $team_count + floor($solo_count / 5);
    if ($total >= 16) {
        $date = date('F j, Y', strtotime('+1 week'));
        return "Slots full! Target date: $date";
    }
    if ($total >= 12) {
        $date = date('F j', strtotime('+2 weeks'));
        return "Almost full — target date: $date";
    }
    if ($total >= 8) {
        $date = date('F j', strtotime('+3 weeks'));
        return "Filling up — estimated: $date";
    }
    if ($total >= 4) {
        $date = date('F j', strtotime('+4 weeks'));
        return "Building up — estimated: $date";
    }
    return 'Recruiting — date TBA once 8+ teams register';
}

// Determine countdown: pick the game with the most registrations
$best_game = null;
$best_total = 0;
$countdown_target = null;
$countdown_label = '';
foreach ($games as $g) {
    $tc = $counts[$g['slug']] ?? 0;
    $sc = $solo_counts[$g['slug']] ?? 0;
    $total = $tc + $sc;
    if ($total > $best_total) {
        $best_total = $total;
        $best_game = $g;
    }
}
if ($best_game && !empty($best_game['date']) && strtotime($best_game['date']) > time()) {
    $countdown_target = $best_game['date'];
    $countdown_label = $best_game['name'] . ' tournament starts in';
} elseif ($best_total >= 16) {
    $countdown_target = date('Y-m-d', strtotime('+1 week'));
    $countdown_label = 'Tournament starts in';
} elseif ($best_total >= 8) {
    $countdown_target = date('Y-m-d', strtotime('+3 weeks'));
    $countdown_label = 'Estimated tournament date';
}
?>

<div class="countdown-section" id="countdownSection">
    <?php if ($countdown_target): ?>
        <div class="countdown-heading"><?= $countdown_label ?></div>
        <div class="countdown-timer" data-target="<?= $countdown_target ?>">
            <div class="countdown-unit">
                <div class="countdown-number" id="cdDays">--</div>
                <div class="countdown-label">Days</div>
            </div>
            <div class="countdown-sep">:</div>
            <div class="countdown-unit">
                <div class="countdown-number" id="cdHours">--</div>
                <div class="countdown-label">Hours</div>
            </div>
            <div class="countdown-sep">:</div>
            <div class="countdown-unit">
                <div class="countdown-number" id="cdMins">--</div>
                <div class="countdown-label">Minutes</div>
            </div>
            <div class="countdown-sep">:</div>
            <div class="countdown-unit">
                <div class="countdown-number" id="cdSecs">--</div>
                <div class="countdown-label">Seconds</div>
            </div>
        </div>
    <?php else: ?>
        <div class="countdown-heading">Tournament Season</div>
        <div class="countdown-tba">
            <i class="bi bi-calendar-event"></i> Date TBA — <a href="#games">Register now!</a>
        </div>
        <div class="countdown-sub">Registration closing soon. Secure your slot before it fills up.</div>
    <?php endif; ?>
</div>

<div style="max-width:1000px; margin:0 auto 1.5rem; padding:0 1rem; display:flex; flex-direction:column; gap:0.75rem;">
    <div style="background:rgba(124,58,237,0.08); border:1px solid rgba(124,58,237,0.25); border-radius:12px; padding:1rem 1.5rem; display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
        <i class="bi bi-person-plus-fill" style="font-size:1.5rem; color:var(--accent-light);"></i>
        <div>
            <div style="font-weight:700; font-size:0.95rem; color:var(--text);">Don't have a team?</div>
            <div style="font-size:0.85rem; color:var(--text-muted);">Join as a solo entry! The system will pick a team for you based on your actual skill level. Just choose "Solo Entry" on any game below.</div>
        </div>
    </div>
    <div style="background:rgba(34,197,94,0.08); border:1px solid rgba(34,197,94,0.25); border-radius:12px; padding:1rem 1.5rem; display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
        <i class="bi bi-wallet2" style="font-size:1.5rem; color:var(--success);"></i>
        <div>
            <div style="font-weight:700; font-size:0.95rem; color:var(--text);">Register now, pay later!</div>
            <div style="font-size:0.85rem; color:var(--text-muted);">You can register your team or solo entry first and pay anytime before the deadline — via GCash, InstaPay QR, or on-site at the venue.</div>
        </div>
    </div>
</div>

<div class="games-grid" id="games">
    <?php foreach ($games as $game):
        $tc = $counts[$game['slug']] ?? 0;
        $sc = $solo_counts[$game['slug']] ?? 0;
        $effective = $tc + floor($sc / 5);
        $pct = min(100, round(($effective / $max_teams) * 100));
        $slots_left = max(0, $max_teams - $effective);
        $date_est = estimate_date($tc, $sc, $game['date'] ?? null);
        $reg_deadline = $game['reg_deadline'] ?? null;
        $reg_closed = $reg_deadline && strtotime($reg_deadline . ' 23:59:59') < time();
    ?>
        <div class="game-card">
            <div class="game-banner">
                <img src="<?= base_url($game['logo']) ?>" alt="<?= $game['name'] ?>" class="game-logo">
                <div class="game-title"><?= $game['name'] ?></div>
            </div>
            <div class="game-body">
                <p class="desc"><?= $game['desc'] ?></p>

                <div class="slot-tracker">
                    <div class="slot-info">
                        <span><strong><?= $effective ?></strong> / <?= $max_teams ?> teams</span>
                        <span class="slots-left"><?= $slots_left ?> slot(s) left</span>
                    </div>
                    <div class="slot-bar">
                        <div class="slot-fill" style="width: <?= $pct ?>%"></div>
                    </div>
                    <div class="slot-date"><i class="bi bi-calendar-event"></i> <?= $date_est ?></div>
                    <?php if ($reg_deadline): ?>
                        <div class="slot-date" style="color:<?= $reg_closed ? 'var(--danger)' : '#f59e0b' ?>;">
                            <i class="bi bi-clock-fill"></i>
                            <?php if ($reg_closed): ?>
                                Registration closed
                            <?php else: ?>
                                Register by <?= date('F j, Y', strtotime($reg_deadline)) ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="game-stats">
                    <span class="teams-count">
                        <i class="bi bi-people-fill"></i> <?= $tc ?> team(s)
                    </span>
                    <span class="teams-count">
                        <i class="bi bi-person-fill"></i> <?= $sc ?> solo player(s)
                    </span>
                </div>
                <div class="game-actions">
                    <?php if ($reg_closed): ?>
                        <div class="btn-register" style="opacity:0.5; cursor:default;">
                            <i class="bi bi-lock-fill"></i> Registration Closed
                        </div>
                    <?php elseif ($slots_left > 0): ?>
                        <a href="<?= base_url('register.php') ?>?game=<?= $game['slug'] ?>" class="btn-register">
                            <i class="bi bi-people-fill"></i> Register Team <span class="btn-price">&#8369;500</span>
                        </a>
                        <a href="<?= base_url('matchmaking.php') ?>?game=<?= $game['slug'] ?>" class="btn-solo">
                            <i class="bi bi-person-fill"></i> Solo Entry <span class="btn-price">&#8369;100</span>
                        </a>
                    <?php else: ?>
                        <div class="btn-register" style="opacity:0.5; cursor:default;">
                            <i class="bi bi-lock-fill"></i> Registration Full
                        </div>
                    <?php endif; ?>
                </div>
                <div class="share-buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=https://argonar.co" target="_blank" rel="noopener" class="btn-share-fb" title="Share on Facebook">
                        <i class="bi bi-facebook"></i> Share
                    </a>
                    <a href="fb-messenger://share/?link=https://argonar.co" class="btn-share-msg" title="Send via Messenger">
                        <i class="bi bi-messenger"></i> Send
                    </a>
                    <button type="button" class="btn-copy-link" onclick="copyLink(this)" title="Copy link">
                        <i class="bi bi-link-45deg"></i> Copy Link
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="orgs-section">
    <h2>Participating Organizations</h2>
    <div class="orgs-grid">
        <a href="https://www.facebook.com/argonarsoftwarepublishing" target="_blank" rel="noopener" class="org-card">
            <img src="<?= base_url('images/argonar-logo.svg') ?>" alt="Argonar Software OPC" class="org-logo">
            <div class="org-info">
                <div class="org-name">Argonar Software OPC</div>
                <span class="org-link"><i class="bi bi-facebook"></i> Facebook Page</span>
            </div>
        </a>
        <a href="https://oslobcebuparagliding.com/" target="_blank" rel="noopener" class="org-card">
            <img src="<?= base_url('images/ocpd.jpg') ?>" alt="OCPD" class="org-logo">
            <div class="org-info">
                <div class="org-name">Oslob Cebu Paragliding Development Com</div>
                <span class="org-link"><i class="bi bi-globe"></i> oslobcebuparagliding.com</span>
                <span class="org-link" style="margin-top:0.1rem;"><i class="bi bi-facebook"></i> Facebook Page</span>
            </div>
        </a>
    </div>
</div>

<div class="registered-section">
    <h2>Registered Participants</h2>

    <?php foreach ($games as $game): ?>
        <?php
        $teams = $registered_teams[$game['slug']] ?? [];
        $solos = $solo_players[$game['slug']] ?? [];
        if (empty($teams) && empty($solos)) continue;
        ?>
        <div class="registered-game">
            <h3><i class="bi bi-controller"></i> <?= $game['name'] ?></h3>
            <div class="registered-list">
                <?php foreach ($teams as $team): ?>
                    <div class="registered-team <?= !empty($team['team_logo']) ? 'has-logo' : '' ?>">
                        <?php if (!empty($team['team_logo'])): ?>
                            <img src="<?= base_url($team['team_logo']) ?>" alt="" class="team-logo-img">
                        <?php endif; ?>
                        <div style="flex:1; min-width:0;">
                            <div class="team-name"><?= htmlspecialchars($team['team_name']) ?></div>
                            <div class="team-type"><i class="bi bi-people-fill"></i> Team</div>
                            <span class="team-status <?= $team['status'] ?>"><?= $team['status'] ?></span>
                            <div class="team-members-list">
                                <div class="team-member-row team-member-header">
                                    <span>Player</span>
                                    <span>Rank</span>
                                </div>
                                <?php
                                // Parse members_ranks (format: name:rank|name:rank|...)
                                $members_data = !empty($team['members_ranks']) ? explode('|', $team['members_ranks']) : [];
                                if (!empty($members_data) && $members_data[0] !== ':'):
                                    foreach ($members_data as $mi => $entry):
                                        $parts = explode(':', $entry, 2);
                                        $mname = $parts[0] ?? '';
                                        $mrank = $parts[1] ?? '';
                                        if (empty($mname)) continue;
                                ?>
                                    <div class="team-member-row">
                                        <span class="team-member-name"><?= $mi === 0 ? '<i class="bi bi-star-fill" style="color:#fbbf24; font-size:0.6rem;" title="Captain"></i> ' : '' ?><?= htmlspecialchars($mname) ?></span>
                                        <span class="team-member-rank"><?= !empty($mrank) ? htmlspecialchars($mrank) : '—' ?></span>
                                    </div>
                                <?php
                                    endforeach;
                                else:
                                    // Fallback to individual member columns
                                    for ($mi = 1; $mi <= 5; $mi++):
                                        if (empty($team["member_$mi"])) continue;
                                ?>
                                    <div class="team-member-row">
                                        <span class="team-member-name"><?= $mi === 1 ? '<i class="bi bi-star-fill" style="color:#fbbf24; font-size:0.6rem;" title="Captain"></i> ' : '' ?><?= htmlspecialchars($team["member_$mi"]) ?></span>
                                        <span class="team-member-rank">—</span>
                                    </div>
                                <?php
                                    endfor;
                                endif;
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php foreach ($solos as $solo): ?>
                    <div class="registered-team">
                        <div class="team-name"><?= htmlspecialchars($solo['player_name']) ?></div>
                        <div class="team-type"><i class="bi bi-person-fill"></i> Solo &middot; <?= htmlspecialchars($solo['rank_tier']) ?><?= !empty($solo['preferred_role']) ? ' &middot; ' . htmlspecialchars($solo['preferred_role']) : '' ?></div>
                        <span class="team-status <?= $solo['status'] ?>"><?= $solo['status'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($registered_teams) && empty($solo_players)): ?>
        <p class="no-teams" style="text-align:center;">No participants registered yet. Be the first!</p>
    <?php endif; ?>
</div>

<div class="terms-landing">
    <div class="terms-section">
        <div class="terms-title"><i class="bi bi-shield-check"></i> Terms &amp; Consent</div>
        <div class="terms-body">
            <p>By registering for this tournament, all participants agree to the following:</p>
            <ul>
                <li><strong>Media Release:</strong> You consent to being photographed, filmed, and/or recorded during the tournament. All media may be used for promotional, social media, and public purposes by the organizers.</li>
                <li><strong>Fair Play &amp; Integrity:</strong> You commit to playing with honesty and sportsmanship. Any form of cheating, rank manipulation, or unsportsmanlike behavior may result in disqualification.</li>
                <li><strong>Violations &amp; Penalties:</strong> Rank manipulation, submitting false information, smurfing, or any form of dishonesty will be subject to penalties — including disqualification and prize forfeiture — at the discretion of Argonar Software OPC and OCPD.</li>
                <li><strong>Pay Later Option:</strong> You may register now and pay before the deadline via GCash, InstaPay QR, or on-site at the venue. Unpaid registrations by the deadline may be forfeited.</li>
                <li><strong>Build Your Reputation:</strong> This tournament is your stage. Your performance, conduct, and teamwork build your credibility as a player in the community. Play with honor.</li>
            </ul>
            <!-- Violations & Penalties Warning -->
            <div style="margin-top:1.25rem; padding:1rem 1.25rem; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.3); border-radius:10px;">
                <div style="font-size:1rem; font-weight:800; color:var(--danger); margin-bottom:0.5rem;">
                    <i class="bi bi-shield-exclamation"></i> VIOLATIONS &amp; PENALTIES
                </div>
                <ul style="margin:0; padding-left:1.25rem; font-size:0.85rem; color:var(--text); line-height:1.7;">
                    <li><strong>Rank manipulation</strong> — Submitting a fake or lower rank will result in immediate disqualification.</li>
                    <li><strong>Dishonesty</strong> — False information, smurfing, or fraudulent submissions will lead to disqualification and prize forfeiture.</li>
                    <li><strong>Match fixing</strong> — Intentional losing, score manipulation, or collusion = permanent ban.</li>
                    <li><strong>Complaints &amp; reports</strong> — Any complaints from players, audiences, or other participants regarding unfair play or rule violations <strong>will be taken into consideration</strong> by the organizers when evaluating penalties.</li>
                </ul>
                <div style="margin-top:0.75rem; padding:0.6rem 0.75rem; background:rgba(239,68,68,0.1); border-radius:8px; font-size:0.8rem; font-weight:700; color:var(--danger); text-align:center;">
                    <i class="bi bi-exclamation-triangle-fill"></i> All penalties — including warnings, disqualification, and prize forfeiture — will be judged by the organizers. All decisions are final.
                </div>
            </div>

            <!-- Organizer -->
            <div style="margin-top:1rem; padding:0.85rem 1rem; background:rgba(124,58,237,0.1); border:1px solid rgba(124,58,237,0.3); border-radius:10px; text-align:center;">
                <div style="font-size:0.95rem; font-weight:800; color:var(--accent-light);">
                    <i class="bi bi-building"></i> This event is officially organized by
                </div>
                <div style="font-size:1.25rem; font-weight:800; margin-top:0.3rem; color:var(--text);">
                    ARGONAR SOFTWARE OPC &amp; OCPD
                </div>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">
                    All rules, penalties, and final decisions are under the authority of Argonar Software OPC and OCPD.
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
