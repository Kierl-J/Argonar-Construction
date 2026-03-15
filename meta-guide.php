<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Meta Business Suite Guide — Argonar Tournament';
require_once __DIR__ . '/includes/header.php';
?>

<div class="reg-container" style="max-width:750px;">
    <a href="<?= base_url() ?>" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to dashboard
    </a>

    <div class="reg-card">
        <h2><i class="bi bi-facebook" style="color:#1877f2;"></i> Meta Business Suite Setup Guide</h2>
        <p class="subtitle">Automate your tournament posts on Facebook &amp; Instagram</p>

        <!-- Step 1 -->
        <div class="section-label">Step 1 — Access Meta Business Suite</div>
        <div class="guide-step">
            <div class="guide-number">1</div>
            <div class="guide-content">
                <p>Go to <strong>business.facebook.com</strong> or open the <strong>Meta Business Suite</strong> app on your phone.</p>
                <p class="guide-note">Make sure you're logged in as a Page admin or editor of your Facebook Page.</p>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="section-label">Step 2 — Connect Your Page</div>
        <div class="guide-step">
            <div class="guide-number">2</div>
            <div class="guide-content">
                <p>Select your Facebook Page (e.g., <strong>Argonar Software OPC</strong>) from the left sidebar.</p>
                <p class="guide-note">If you manage multiple pages, make sure you pick the correct one for the tournament.</p>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="section-label">Step 3 — Create a Post</div>
        <div class="guide-step">
            <div class="guide-number">3</div>
            <div class="guide-content">
                <p>Click <strong>"Create Post"</strong> from the home screen or go to <strong>Content &rarr; Create Post</strong>.</p>
                <p>Write your tournament announcement. Here's a sample:</p>
                <div class="guide-template">
                    <strong>Sample Post:</strong><br><br>
                    🎮 TOURNAMENT ALERT! 🏆<br><br>
                    Join the Argonar Gaming Tournament!<br>
                    🔫 Valorant | CrossFire | Dota 2<br><br>
                    📋 Register now: <strong>https://argonar.co</strong><br><br>
                    💰 Entry Fee:<br>
                    • Team (5 players): ₱500<br>
                    • Solo Matchmaking: ₱100/player<br><br>
                    📍 Venue: Hide Out Cybernet Cafe<br>
                    💳 GCash: 0927 872 8916<br><br>
                    Presented by Argonar Software OPC<br>
                    #ArgonarTournament #Valorant #Dota2 #CrossFire #Gaming #Esports
                </div>
            </div>
        </div>

        <!-- Step 4 -->
        <div class="section-label">Step 4 — Schedule the Post</div>
        <div class="guide-step">
            <div class="guide-number">4</div>
            <div class="guide-content">
                <p>Instead of clicking "Publish", click the <strong>dropdown arrow</strong> next to the publish button and select <strong>"Schedule Post"</strong>.</p>
                <p>Pick the <strong>date and time</strong> you want the post to go live.</p>
                <p class="guide-note">Best times to post for gaming communities: 6-9 PM weekdays, 12-3 PM weekends.</p>
            </div>
        </div>

        <!-- Step 5 -->
        <div class="section-label">Step 5 — Set Up Recurring Posts (Planner)</div>
        <div class="guide-step">
            <div class="guide-number">5</div>
            <div class="guide-content">
                <p>Go to <strong>Planner</strong> (calendar icon) in the left sidebar to see all your scheduled posts.</p>
                <p>Create multiple posts in advance for a full promotion schedule:</p>
                <div class="guide-schedule">
                    <div class="schedule-item">
                        <span class="schedule-day">Week 1</span>
                        <span>Announcement post — "Tournament is coming!"</span>
                    </div>
                    <div class="schedule-item">
                        <span class="schedule-day">Week 2</span>
                        <span>Registration reminder — "Slots are filling up!"</span>
                    </div>
                    <div class="schedule-item">
                        <span class="schedule-day">3 Days Before</span>
                        <span>Last call — "Final days to register!"</span>
                    </div>
                    <div class="schedule-item">
                        <span class="schedule-day">Event Day</span>
                        <span>Game day post — "Tournament starts NOW!"</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 6 -->
        <div class="section-label">Step 6 — Cross-Post to Instagram</div>
        <div class="guide-step">
            <div class="guide-number">6</div>
            <div class="guide-content">
                <p>When creating a post, toggle on <strong>"Instagram"</strong> to publish to both Facebook and Instagram at the same time.</p>
                <p class="guide-note">Your Instagram account must be linked to your Facebook Page via Meta Business Suite settings.</p>
            </div>
        </div>

        <!-- Step 7 -->
        <div class="section-label">Step 7 — Boost Posts (Optional)</div>
        <div class="guide-step">
            <div class="guide-number">7</div>
            <div class="guide-content">
                <p>To reach more people, click <strong>"Boost Post"</strong> on any published post.</p>
                <p>Set your target audience:</p>
                <ul style="color: var(--text-muted); font-size: 0.9rem; padding-left: 1.25rem; margin-top: 0.5rem;">
                    <li>Location: Your city/area</li>
                    <li>Age: 16-35</li>
                    <li>Interests: Gaming, Esports, Valorant, Dota 2, PC Gaming</li>
                </ul>
                <p class="guide-note">Even a small budget (₱100-500) can significantly increase reach.</p>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="section-label">Quick Links</div>
        <div class="guide-links">
            <a href="https://business.facebook.com" target="_blank" rel="noopener" class="guide-link-card">
                <i class="bi bi-box-arrow-up-right"></i>
                <div>
                    <strong>Meta Business Suite</strong>
                    <span>business.facebook.com</span>
                </div>
            </a>
            <a href="https://www.facebook.com/argonarsoftwarepublishing" target="_blank" rel="noopener" class="guide-link-card">
                <i class="bi bi-facebook"></i>
                <div>
                    <strong>Argonar Software OPC</strong>
                    <span>Facebook Page</span>
                </div>
            </a>
            <a href="<?= base_url() ?>" class="guide-link-card">
                <i class="bi bi-controller"></i>
                <div>
                    <strong>Tournament Registration</strong>
                    <span>argonar.co</span>
                </div>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
