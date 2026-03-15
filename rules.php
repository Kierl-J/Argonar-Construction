<?php
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Tournament Rules — Argonar Tournament';
$pageDescription = 'Tournament rules — double elimination, game-specific rules for Valorant, CrossFire, and Dota 2.';
require_once __DIR__ . '/includes/header.php';
?>

<div class="reg-container" style="max-width:800px;">
    <a href="<?= base_url() ?>" class="back-link"><i class="bi bi-arrow-left"></i> Back to Home</a>

    <div class="reg-card">
        <h2><i class="bi bi-journal-check"></i> Tournament Rules</h2>
        <p class="subtitle">Read carefully before registering. All participants must follow these rules.</p>

        <!-- Format -->
        <div class="section-label">Tournament Format</div>
        <ol class="rules-list">
            <li class="rule-item"><strong>Double Elimination</strong> — Two brackets: Winners and Losers. You need to lose twice to be eliminated.</li>
            <li class="rule-item"><strong>Winners Bracket:</strong> Best of 1 (Bo1) per match.</li>
            <li class="rule-item"><strong>Losers Bracket:</strong> Best of 1 (Bo1) — lose here and you're out for good.</li>
            <li class="rule-item"><strong>Grand Finals:</strong> Best of 3 (Bo3). The losers bracket winner must win 2 sets to take the championship.</li>
            <li class="rule-item">Seeding and brackets will be announced before the tournament day.</li>
            <li class="rule-item">Maximum of <strong>16 teams</strong> per game.</li>
        </ol>

        <!-- Valorant -->
        <div class="section-label"><i class="bi bi-crosshair"></i> Valorant Rules</div>
        <ol class="rules-list">
            <li class="rule-item">5v5 standard competitive format.</li>
            <li class="rule-item">Map pick/ban will follow tournament standard (coin toss or knife round as decided by admin).</li>
            <li class="rule-item">All agents are allowed. No agent restrictions.</li>
            <li class="rule-item">Game settings must be default competitive settings (no custom modifications).</li>
            <li class="rule-item">Overtime rules follow default Valorant competitive OT.</li>
        </ol>

        <!-- CrossFire -->
        <div class="section-label"><i class="bi bi-bullseye"></i> CrossFire Rules</div>
        <ol class="rules-list">
            <li class="rule-item">5v5 Search &amp; Destroy (SnD) mode.</li>
            <li class="rule-item">12 rounds per half (first to 13 wins).</li>
            <li class="rule-item">All weapons are allowed unless banned by admin before the match.</li>
            <li class="rule-item">GameClub client must be used. No third-party launchers.</li>
            <li class="rule-item">Ping/lag issues must be reported before the match starts.</li>
        </ol>

        <!-- Dota 2 -->
        <div class="section-label"><i class="bi bi-shield-shaded"></i> Dota 2 Rules</div>
        <ol class="rules-list">
            <li class="rule-item">5v5 Captains Mode (CM) for all matches.</li>
            <li class="rule-item">All heroes in the current patch are allowed.</li>
            <li class="rule-item">Pauses are limited to 5 minutes total per team per game.</li>
            <li class="rule-item">Intentional disconnects or game-crashing exploits result in immediate disqualification.</li>
            <li class="rule-item">Lobby settings will be configured by the admin.</li>
        </ol>

        <!-- General Rules -->
        <div class="section-label"><i class="bi bi-exclamation-triangle"></i> General Rules</div>
        <ol class="rules-list">
            <li class="rule-item"><strong>Be on time.</strong> Teams have a 15-minute grace period. After that, it's a forfeit.</li>
            <li class="rule-item"><strong>No cheating or exploits.</strong> Any use of hacks, scripts, macros, or game exploits will result in immediate disqualification and a permanent ban from future events.</li>
            <li class="rule-item"><strong>Admin decisions are final.</strong> All rulings by tournament administrators are binding and non-negotiable.</li>
            <li class="rule-item"><strong>No-shows = forfeit.</strong> If your team fails to show up for a scheduled match, the opposing team advances automatically.</li>
            <li class="rule-item"><strong>Account sharing is prohibited.</strong> Each player must use their own game account.</li>
            <li class="rule-item"><strong>Substitute players:</strong> Each team may declare <strong>1 substitute player</strong> during registration. Substitutes must be declared before the tournament — undeclared substitutes are not allowed to play. Substitutes can only replace a team member, not add a 6th player.</li>
        </ol>

        <!-- Match Schedule -->
        <div class="section-label"><i class="bi bi-calendar-event"></i> Match Schedule</div>
        <ol class="rules-list">
            <li class="rule-item">Match schedules will be announced <strong>at least 24 hours</strong> before each round.</li>
            <li class="rule-item">Schedules will be posted on the official Facebook page and communicated via Messenger.</li>
            <li class="rule-item">If a reschedule is needed, both teams must agree and get admin approval at least 12 hours in advance.</li>
        </ol>

        <!-- Sportsmanship -->
        <div class="section-label"><i class="bi bi-hand-thumbs-up"></i> Sportsmanship</div>
        <ol class="rules-list">
            <li class="rule-item"><strong>Respect your opponents.</strong> Win or lose, show respect. Handshakes (or GGs) are expected.</li>
            <li class="rule-item"><strong>No toxicity.</strong> Trash talk that crosses into harassment, slurs, or personal attacks will not be tolerated.</li>
            <li class="rule-item"><strong>Violators will be banned</strong> from all future Argonar Tournament events.</li>
            <li class="rule-item">Report any issues or disputes to the admin immediately — do not engage in arguments with the opposing team.</li>
        </ol>

        <!-- Violations & Penalties -->
        <div class="section-label" style="color:var(--danger);"><i class="bi bi-shield-exclamation"></i> Violations &amp; Penalties</div>
        <ol class="rules-list">
            <li class="rule-item"><strong>Rank manipulation</strong> — Submitting a fake or intentionally lower rank to gain an unfair advantage in matchmaking or seeding will result in immediate disqualification.</li>
            <li class="rule-item"><strong>False or dishonest information</strong> — Providing incorrect player names, using someone else's identity, or submitting fraudulent payment proofs will result in disqualification and forfeiture of any prizes.</li>
            <li class="rule-item"><strong>Smurfing</strong> — Using alternate or lower-ranked accounts to bypass fair matchmaking is strictly prohibited.</li>
            <li class="rule-item"><strong>Match fixing</strong> — Any form of intentional losing, score manipulation, or collusion between teams will result in permanent bans for all involved players.</li>
            <li class="rule-item"><strong>Lying about skill level</strong> — Intentionally misrepresenting your rank or skill level to gain an unfair advantage in matchmaking or seeding is considered a violation and will be treated the same as rank manipulation.</li>
            <li class="rule-item"><strong>Complaints and reports</strong> — Any complaints from players, audiences, or other participants regarding unfair play, dishonesty, lying about skill level, or rule violations will be taken into consideration by the organizers when evaluating penalties. <a href="dispute.php" style="color:var(--accent-light);">File a complaint here</a>.</li>
            <li class="rule-item"><strong>Penalties are at the organizer's discretion.</strong> The severity of the penalty — including warnings, disqualification, prize forfeiture, or permanent bans — will be judged by <strong>Argonar Software OPC</strong> and <strong>OCPD</strong> based on the nature and severity of the violation.</li>
            <li class="rule-item"><strong>All decisions are final.</strong> Argonar Software OPC and OCPD, as the official organizers of this event, reserve the right to take any action deemed necessary to maintain the integrity and fairness of the tournament.</li>
        </ol>

        <!-- Prize Claiming -->
        <div class="section-label"><i class="bi bi-gift"></i> Prize Claiming</div>
        <ol class="rules-list">
            <li class="rule-item"><strong>Choose your prize.</strong> The winning team must choose <strong>one</strong> prize: either the cash prize or the paragliding tickets. Both cannot be claimed.</li>
            <li class="rule-item"><strong>Claim within 7 days.</strong> Winners must claim their chosen prize within 7 days after the tournament finals. Unclaimed prizes after the deadline may be forfeited.</li>
            <li class="rule-item"><strong>Cash prize</strong> will be distributed via GCash to the team captain, who is responsible for splitting it among team members.</li>
            <li class="rule-item"><strong>Paragliding tickets</strong> are courtesy of OCPD Oslob Cebu Paragliding. The prize covers tickets only — transportation, travel, and logistics are the responsibility of the winners.</li>
            <li class="rule-item"><strong>Verification may be required.</strong> The organizers may request ID or proof of identity when claiming the prize.</li>
            <li class="rule-item"><strong>Prize is non-transferable.</strong> The prize cannot be transferred to another team or individual.</li>
        </ol>

        <div style="margin-top:2rem; text-align:center;">
            <a href="<?= base_url() ?>" class="btn-register" style="display:inline-flex; width:auto; padding:0.75rem 2rem;">
                <i class="bi bi-arrow-left"></i> Back to Registration
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
