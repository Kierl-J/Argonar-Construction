<?php
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Contact & Support — Argonar Tournament';
require_once __DIR__ . '/includes/header.php';
?>

<div class="reg-container" style="max-width:800px;">
    <a href="<?= base_url() ?>" class="back-link"><i class="bi bi-arrow-left"></i> Back to Home</a>

    <div class="reg-card">
        <h2><i class="bi bi-headset"></i> Contact &amp; Support</h2>
        <p class="subtitle">Need help? Reach out to us through any of the channels below.</p>

        <!-- Contact Methods -->
        <div class="section-label">Get in Touch</div>

        <a href="https://m.me/argonarsoftwarepublishing" target="_blank" rel="noopener" class="contact-card">
            <div class="contact-card-icon" style="color:#1877f2;">
                <i class="bi bi-messenger"></i>
            </div>
            <div class="contact-card-body">
                <strong>Facebook Messenger</strong>
                <span>Chat with us directly — fastest way to get a response.</span>
            </div>
            <i class="bi bi-arrow-right"></i>
        </a>

        <a href="https://www.facebook.com/argonarsoftwarepublishing" target="_blank" rel="noopener" class="contact-card">
            <div class="contact-card-icon" style="color:#1877f2;">
                <i class="bi bi-facebook"></i>
            </div>
            <div class="contact-card-body">
                <strong>Facebook Page</strong>
                <span>Follow for announcements, updates, and results.</span>
            </div>
            <i class="bi bi-arrow-right"></i>
        </a>

        <div class="contact-card" style="cursor:default;">
            <div class="contact-card-icon" style="color:var(--danger);">
                <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div class="contact-card-body">
                <strong>Tournament Venue</strong>
                <span>Hide Out Cybernet Cafe, Brgy. Inayawan, Inayawan Central, Cebu City, 6000</span>
            </div>
        </div>

        <div class="contact-card" style="cursor:default;">
            <div class="contact-card-icon" style="color:#007bff;">
                <i class="bi bi-phone"></i>
            </div>
            <div class="contact-card-body">
                <strong>GCash Payment</strong>
                <span>0927 872 8916</span>
            </div>
        </div>

        <!-- FAQ -->
        <div class="section-label" style="margin-top:2rem;"><i class="bi bi-question-circle"></i> Frequently Asked Questions</div>

        <details class="faq-item">
            <summary>How do I register for the tournament?</summary>
            <div class="faq-answer">
                Go to the <a href="<?= base_url() ?>">home page</a>, pick your game, and click "Register Team" (if you have a full team) or "Solo Entry" (if you want to be matched with other players). Fill in the form, upload your GCash payment proof, and wait for admin approval.
            </div>
        </details>

        <details class="faq-item">
            <summary>What if I can't attend on tournament day?</summary>
            <div class="faq-answer">
                Let us know as soon as possible via <a href="https://m.me/argonarsoftwarepublishing" target="_blank" rel="noopener">Messenger</a>. If you don't show up without notice, your team forfeits the match. Registration fees are non-refundable once approved.
            </div>
        </details>

        <details class="faq-item">
            <summary>When is the tournament?</summary>
            <div class="faq-answer">
                The tournament date depends on how fast slots fill up. Once enough teams register for a game, the date will be announced at least 1 week in advance on our Facebook page and via Messenger. Check the home page for estimated dates.
            </div>
        </details>

        <details class="faq-item">
            <summary>How do I know if my payment is approved?</summary>
            <div class="faq-answer">
                After you register and upload your GCash payment proof, an admin will review it. Your status will change from "Pending" to "Approved" on the home page under Registered Participants. You can also message us on Messenger to check.
            </div>
        </details>

        <details class="faq-item">
            <summary>Can I register as a solo player?</summary>
            <div class="faq-answer">
                Yes! Click "Solo Entry" on the game you want to play. You'll be matched with other solo players based on your rank and preferred role. Once 5 solo players are matched, you'll form a team automatically.
            </div>
        </details>

        <details class="faq-item">
            <summary>Is the registration fee refundable?</summary>
            <div class="faq-answer">
                Registration fees are non-refundable once your payment has been approved. If your payment is still pending and you change your mind, contact us immediately via Messenger.
            </div>
        </details>

        <div style="margin-top:2rem; text-align:center;">
            <a href="https://m.me/argonarsoftwarepublishing" target="_blank" rel="noopener" class="btn-register" style="display:inline-flex; width:auto; padding:0.75rem 2rem;">
                <i class="bi bi-messenger"></i> Message Us on Messenger
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
