<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/subscription.php';

$pageTitle = 'Privacy Policy';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-1">Privacy Policy</h4>
                <p class="text-muted small mb-4">Last updated: March 2026</p>

                <h6 class="fw-bold mt-4">1. Information We Collect</h6>
                <p class="text-muted small">When you create an account, we collect your name, email address, and optional company name. When you use the tools, we store the data you enter (project names, estimates, documents) associated with your account. We do not collect sensitive personal information beyond what is needed to provide the Service.</p>

                <h6 class="fw-bold mt-4">2. Payment Information</h6>
                <p class="text-muted small">Payments are processed by PayRex, our third-party payment processor. We do not store your credit card numbers, GCash, ShopeePay, or other payment credentials. PayRex handles all payment data securely under their own privacy policy.</p>

                <h6 class="fw-bold mt-4">3. How We Use Your Information</h6>
                <p class="text-muted small">We use your information to: (a) provide and maintain the Service; (b) process your subscriptions; (c) send in-app notifications about your account and subscription; (d) improve and optimize the Service. We do not sell, rent, or share your personal information with third parties for marketing purposes.</p>

                <h6 class="fw-bold mt-4">4. Analytics</h6>
                <p class="text-muted small">We use Google Analytics (GA4) to understand how visitors use our website. Google Analytics collects anonymized data such as pages visited, time spent, and device type. This data helps us improve the Service. You can opt out of Google Analytics by using the <a href="https://tools.google.com/dlpage/gaoptout" target="_blank">Google Analytics Opt-out Browser Add-on</a>.</p>

                <h6 class="fw-bold mt-4">5. Cookies</h6>
                <p class="text-muted small">We use session cookies to keep you logged in and maintain your preferences. These cookies are essential for the Service to function and expire when you close your browser or log out. Google Analytics may also set cookies for analytics purposes.</p>

                <h6 class="fw-bold mt-4">6. Data Storage & Security</h6>
                <p class="text-muted small">Your data is stored on secure servers. Passwords are hashed using industry-standard algorithms. All connections to argonar.co are encrypted via HTTPS/TLS. While we take reasonable measures to protect your data, no method of electronic transmission or storage is 100% secure.</p>

                <h6 class="fw-bold mt-4">7. Data Retention</h6>
                <p class="text-muted small">Your account data and project data are retained as long as your account is active. If you wish to delete your account and all associated data, contact us at <a href="mailto:support@argonar.co">support@argonar.co</a>.</p>

                <h6 class="fw-bold mt-4">8. Your Rights</h6>
                <p class="text-muted small">You have the right to: (a) access the personal data we hold about you; (b) request correction of inaccurate data; (c) request deletion of your account and data; (d) export your project data via the Excel export feature. To exercise these rights, contact us at <a href="mailto:support@argonar.co">support@argonar.co</a>.</p>

                <h6 class="fw-bold mt-4">9. Changes to This Policy</h6>
                <p class="text-muted small">We may update this privacy policy from time to time. We will notify users of significant changes via in-app notification.</p>

                <h6 class="fw-bold mt-4">10. Contact</h6>
                <p class="text-muted small">For privacy-related questions, contact us at <a href="mailto:support@argonar.co">support@argonar.co</a>.</p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
