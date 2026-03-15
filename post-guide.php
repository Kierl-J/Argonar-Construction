<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Facebook Post Guide — Argonar Tournament';
$pageDescription = 'Ready-to-copy Facebook post captions for promoting the Argonar Tournament.';
require_once __DIR__ . '/includes/header.php';
?>

<div class="reg-container" style="max-width:750px;">
    <a href="<?= base_url() ?>" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to dashboard
    </a>

    <div class="reg-card">
        <h2><i class="bi bi-megaphone-fill" style="color:#1877f2;"></i> Facebook Post Captions</h2>
        <p class="subtitle">Ready-to-copy captions for your tournament promotion posts. Just copy, paste, and post!</p>

        <!-- GENERAL POST -->
        <div class="section-label">General Tournament Announcement</div>
        <div class="guide-template" style="position:relative;">
            <button onclick="copyCaption(this)" class="btn-copy-link" style="position:absolute; top:0.5rem; right:0.5rem; font-size:0.7rem;">
                <i class="bi bi-clipboard"></i> Copy
            </button>
🎮🏆 ARGONAR GAMING TOURNAMENT IS HERE! 🏆🎮

Are you ready to prove you're the best? 🔥

📋 3 GAMES. 1 CHAMPION PER GAME. WINNER TAKES ALL.
🔫 Valorant
💥 CrossFire (GameClub)
⚔️ Dota 2

💰 PRIZE: ₱9,000 CASH or FREE PARAGLIDING EXPERIENCE for the whole team! 🪂
(Winners choose ONE — courtesy of OCPD Oslob Cebu Paragliding)

📝 HOW TO JOIN:
✅ Team Entry (5 players): ₱500
✅ Solo Entry: ₱100 — we'll match you with a team based on your skill level!
✅ Pay via GCash (0927 872 8916) or ON-SITE

📍 Venue: Hide Out Cybernet Cafe
📌 Brgy. Inayawan, Inayawan Central, Cebu City, 6000

🔗 REGISTER NOW: https://argonar.co

⚡ Double Elimination Format — you have to lose TWICE to be out!
🏆 16 teams max per game — slots are filling up FAST!

Don't have a team? No problem! Join solo and we'll build one for you 💪

Presented by Argonar Software OPC
Venue hosted by Hide Out Cybernet Cafe
Paragliding by OCPD — https://oslobcebuparagliding.com

#ArgonarTournament #GamingCebu #Esports #Valorant #Dota2 #CrossFire #CebuGaming #WinnerTakesAll #Paragliding #HideOutCafe #GameOn
        </div>

        <!-- VALORANT POST -->
        <div class="section-label"><i class="bi bi-crosshair"></i> Valorant Post</div>
        <div class="guide-template" style="position:relative;">
            <button onclick="copyCaption(this)" class="btn-copy-link" style="position:absolute; top:0.5rem; right:0.5rem; font-size:0.7rem;">
                <i class="bi bi-clipboard"></i> Copy
            </button>
🔫 VALORANT TOURNAMENT — ARGONAR GAMING 🔫

Calling all Agents! 🎯

Your aim, your strats, your team — it's time to prove it on the big stage. 5v5. Double elimination. No second chances in the losers bracket.

🏆 WINNER TAKES ALL
💰 ₱9,000 Cash OR 🪂 Free Paragliding Experience for the whole squad!

📋 DETAILS:
• Format: Double Elimination (Bo1 / Bo3 Grand Finals)
• Entry: ₱500 per team | ₱100 solo entry
• Max: 16 teams only — first come, first served!
• All agents allowed. Standard competitive settings.

🎮 No team? Register solo — we'll match you with players at your rank!
Iron to Radiant, everyone's welcome.

📍 Hide Out Cybernet Cafe — Brgy. Inayawan, Cebu City
💳 GCash: 0927 872 8916 | Or pay on-site

🔗 REGISTER: https://argonar.co/register.php?game=valorant
🔗 SOLO: https://argonar.co/matchmaking.php?game=valorant

Lock in. Clutch up. Win everything. 🏆

#Valorant #ValorantPH #ValorantCebu #ArgonarTournament #Esports #TacticalShooter #ClutchOrKick #GamingCebu #WinnerTakesAll
        </div>

        <!-- DOTA 2 POST -->
        <div class="section-label"><i class="bi bi-shield-shaded"></i> Dota 2 Post</div>
        <div class="guide-template" style="position:relative;">
            <button onclick="copyCaption(this)" class="btn-copy-link" style="position:absolute; top:0.5rem; right:0.5rem; font-size:0.7rem;">
                <i class="bi bi-clipboard"></i> Copy
            </button>
⚔️ DOTA 2 TOURNAMENT — ARGONAR GAMING ⚔️

Mga lodi ng Dota, tara na! 🔥

5v5. Captains Mode. Double Elimination. The classic MOBA battle returns — and this time, WINNER TAKES ALL.

🏆 PRIZE:
💰 ₱9,000 Cash OR 🪂 Free Paragliding for your whole team!
(Choose one — courtesy of OCPD Oslob Cebu)

📋 DETAILS:
• Format: Double Elimination (Bo1 / Bo3 Grand Finals)
• Mode: Captains Mode (CM)
• Entry: ₱500 per team | ₱100 solo entry
• Max: 16 teams — limited slots!
• All heroes allowed (current patch)

🎮 Solo player? Register and we'll match you by rank!
Herald to Immortal — Carry, Mid, Offlane, Support — pick your role.

📍 Hide Out Cybernet Cafe — Brgy. Inayawan, Cebu City
💳 GCash: 0927 872 8916 | Or pay on-site

🔗 REGISTER: https://argonar.co/register.php?game=dota2
🔗 SOLO: https://argonar.co/matchmaking.php?game=dota2

Outplay. Outfarm. Outdraft. Take the throne. 👑

#Dota2 #Dota2PH #Dota2Cebu #ArgonarTournament #MOBA #GGWellPlayed #CebuGaming #WinnerTakesAll #Esports
        </div>

        <!-- CROSSFIRE POST -->
        <div class="section-label"><i class="bi bi-bullseye"></i> CrossFire Post</div>
        <div class="guide-template" style="position:relative;">
            <button onclick="copyCaption(this)" class="btn-copy-link" style="position:absolute; top:0.5rem; right:0.5rem; font-size:0.7rem;">
                <i class="bi bi-clipboard"></i> Copy
            </button>
💥 CROSSFIRE TOURNAMENT — ARGONAR GAMING 💥

OG gamers, this one's for you! 🎯

CrossFire on GameClub — the classic FPS is back and better than ever. 5v5 Search & Destroy. Double elimination. Pure skill. No gimmicks.

🏆 WINNER TAKES ALL
💰 ₱9,000 Cash OR 🪂 Free Paragliding Experience for the whole squad!

📋 DETAILS:
• Format: Double Elimination (Bo1 / Bo3 Grand Finals)
• Mode: Search & Destroy (SnD)
• Entry: ₱500 per team | ₱100 solo entry
• Max: 16 teams — don't miss out!
• GameClub client required

🎮 Flying solo? We got you — register solo and we'll build your dream team based on your skill level!

📍 Hide Out Cybernet Cafe — Brgy. Inayawan, Cebu City
💳 GCash: 0927 872 8916 | Or pay on-site

🔗 REGISTER: https://argonar.co/register.php?game=crossfire
🔗 SOLO: https://argonar.co/matchmaking.php?game=crossfire

Lock and load. The battlefield awaits. 💀

#CrossFire #CrossFirePH #CFFPS #ArgonarTournament #GameClub #FPS #SearchAndDestroy #CebuGaming #WinnerTakesAll #OGGamers
        </div>

        <!-- SOLO MATCHMAKING POST -->
        <div class="section-label"><i class="bi bi-person-plus-fill"></i> Solo Entry Promo Post</div>
        <div class="guide-template" style="position:relative;">
            <button onclick="copyCaption(this)" class="btn-copy-link" style="position:absolute; top:0.5rem; right:0.5rem; font-size:0.7rem;">
                <i class="bi bi-clipboard"></i> Copy
            </button>
🎮 WALANG TEAM? NO PROBLEM! 🙌

Join the Argonar Tournament as a SOLO PLAYER — for just ₱100!

Here's how it works:
1️⃣ Register at https://argonar.co
2️⃣ Pick your game (Valorant, Dota 2, or CrossFire)
3️⃣ Select your rank and preferred role
4️⃣ We'll match you with players of similar skill level
5️⃣ Show up, play, and WIN! 🏆

💰 Your team can win ₱9,000 CASH or 🪂 FREE PARAGLIDING for the whole squad!

⚡ Your rank matters — we use it to build balanced teams so every match is competitive and fair.

Don't sit this one out. Your next squad is waiting! 💪

📍 Hide Out Cybernet Cafe — Cebu City
🔗 https://argonar.co

#ArgonarTournament #SoloQueue #FindYourTeam #GamingCebu #Valorant #Dota2 #CrossFire #Esports #NoTeamNoProblem
        </div>

        <!-- PARAGLIDING PROMO POST -->
        <div class="section-label"><i class="bi bi-wind"></i> Paragliding Prize Promo Post</div>
        <div class="guide-template" style="position:relative;">
            <button onclick="copyCaption(this)" class="btn-copy-link" style="position:absolute; top:0.5rem; right:0.5rem; font-size:0.7rem;">
                <i class="bi bi-clipboard"></i> Copy
            </button>
🪂 WIN A PARAGLIDING EXPERIENCE FOR YOUR WHOLE TEAM! 🪂

Yes, you read that right. 🔥

Win the Argonar Gaming Tournament and your team gets FREE PARAGLIDING TICKETS in Oslob, Cebu — courtesy of OCPD (Oslob Cebu Paragliding Development).

Soar above the mountains. See the ocean from the sky. With your squad. For FREE. 🏔️🌊

OR take home ₱9,000 CASH — your choice!

🎮 Games: Valorant | Dota 2 | CrossFire
💰 Entry: ₱500/team | ₱100/solo
📍 Hide Out Cybernet Cafe — Cebu City

🔗 Register: https://argonar.co
🌐 OCPD: https://oslobcebuparagliding.com

From gaming chairs to paragliders — only at Argonar Tournament. 🎮➡️🪂

#Paragliding #OslobCebu #OCPD #ArgonarTournament #GamingCebu #WinAndFly #FreeParagliding #Esports #AdventureAwaits
        </div>

        <div style="margin-top:2rem; text-align:center;">
            <a href="<?= base_url('meta-guide.php') ?>" class="btn-register" style="display:inline-flex; width:auto; padding:0.75rem 2rem;">
                <i class="bi bi-gear"></i> Meta Business Suite Guide
            </a>
        </div>
    </div>
</div>

<script>
function copyCaption(btn) {
    var template = btn.parentElement;
    var text = template.innerText.replace('Copy', '').trim();
    navigator.clipboard.writeText(text).then(function() {
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
        setTimeout(function() { btn.innerHTML = orig; }, 2000);
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
