<?php
/**
 * Argonar Construction - VPS Deployment Guide
 * Upload this file to your VPS and open it in the browser.
 * DELETE THIS FILE after deployment is complete.
 */

$steps = [
    [
        'title' => '1. Clone the Repository',
        'commands' => [
            'cd /var/www',
            'git clone https://github.com/Kierl-J/Argonar-Construction.git argonar',
            'cd argonar',
        ],
    ],
    [
        'title' => '2. Install PHP Dependencies (Composer)',
        'commands' => [
            '# If composer is not installed:',
            'apt update && apt install -y composer php-mysql php-xml php-zip php-mbstring php-gd',
            '',
            '# Then install project dependencies:',
            'cd /var/www/argonar',
            'composer install --no-dev',
        ],
    ],
    [
        'title' => '3. Create Database & Import Schema',
        'commands' => [
            'mysql -u root -p',
            '',
            '# Inside MySQL shell:',
            "CREATE DATABASE argonar_construction CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
            "CREATE USER 'argonar'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';",
            "GRANT ALL PRIVILEGES ON argonar_construction.* TO 'argonar'@'localhost';",
            "FLUSH PRIVILEGES;",
            "EXIT;",
            '',
            '# Import the schema:',
            'mysql -u argonar -p argonar_construction < /var/www/argonar/setup.sql',
        ],
    ],
    [
        'title' => '4. Update includes/db.php',
        'note' => 'Edit /var/www/argonar/includes/db.php and change these values:',
        'commands' => [
            'nano /var/www/argonar/includes/db.php',
            '',
            '# Change these lines:',
            "define('DB_USER', 'argonar');           // was 'root'",
            "define('DB_PASS', 'YOUR_STRONG_PASSWORD'); // was empty",
            "define('APP_URL', '/argonar');           // was '/Argonar Construction'",
            '',
            '# Update PayRex keys when ready:',
            "define('PAYREX_SECRET_KEY', 'sk_live_YOUR_KEY');",
            "define('PAYREX_WEBHOOK_SECRET', 'whsk_YOUR_KEY');",
        ],
    ],
    [
        'title' => '5. Update PayRex Checkout URLs',
        'note' => 'Edit /var/www/argonar/payment/checkout.php — change localhost URLs to your domain:',
        'commands' => [
            'nano /var/www/argonar/payment/checkout.php',
            '',
            '# Change these lines (around line 30):',
            "# FROM: 'success_url' => 'http://localhost' . APP_URL . '/payment/success.php?session_id={id}',",
            "# FROM: 'cancel_url'  => 'http://localhost' . APP_URL . '/payment/cancel.php',",
            '',
            "# TO:   'success_url' => 'https://argonar.co/argonar/payment/success.php?session_id={id}',",
            "# TO:   'cancel_url'  => 'https://argonar.co/argonar/payment/cancel.php',",
        ],
    ],
    [
        'title' => '6. Apache Config (Alias under existing site)',
        'note' => 'Add an alias so argonar.co/argonar points to the app:',
        'commands' => [
            'nano /etc/apache2/conf-available/argonar.conf',
            '',
            '# Paste this:',
            'Alias /argonar /var/www/argonar',
            '<Directory /var/www/argonar>',
            '    Options -Indexes +FollowSymLinks',
            '    AllowOverride All',
            '    Require all granted',
            '</Directory>',
            '',
            '# Enable it:',
            'a2enconf argonar',
            'a2enmod rewrite',
            'systemctl reload apache2',
        ],
    ],
    [
        'title' => '7. Set File Permissions',
        'commands' => [
            'chown -R www-data:www-data /var/www/argonar',
            'chmod -R 755 /var/www/argonar',
        ],
    ],
    [
        'title' => '8. Test & Verify',
        'commands' => [
            '# Visit in browser:',
            'https://argonar.co/argonar/index.php',
            '',
            '# Check pricing page:',
            'https://argonar.co/argonar/payment/pricing.php',
        ],
    ],
    [
        'title' => '9. PayRex Webhook URL',
        'note' => 'Set this in your PayRex dashboard under Webhooks:',
        'commands' => [
            'https://argonar.co/argonar/payment/webhook.php',
            '',
            '# Events to listen for:',
            '  payment_intent.succeeded',
        ],
    ],
    [
        'title' => '10. Cleanup',
        'commands' => [
            '# DELETE this guide file:',
            'rm /var/www/argonar/setup-guide.php',
        ],
    ],
];

// Check environment if running on the server
$checks = [];
if (PHP_SAPI !== 'cli') {
    $checks['PHP Version'] = ['value' => PHP_VERSION, 'ok' => version_compare(PHP_VERSION, '8.2.0', '>=')];
    $checks['PDO MySQL'] = ['value' => extension_loaded('pdo_mysql') ? 'Loaded' : 'Missing', 'ok' => extension_loaded('pdo_mysql')];
    $checks['mbstring'] = ['value' => extension_loaded('mbstring') ? 'Loaded' : 'Missing', 'ok' => extension_loaded('mbstring')];
    $checks['zip'] = ['value' => extension_loaded('zip') ? 'Loaded' : 'Missing', 'ok' => extension_loaded('zip')];
    $checks['xml'] = ['value' => extension_loaded('xml') ? 'Loaded' : 'Missing', 'ok' => extension_loaded('xml')];
    $checks['curl'] = ['value' => extension_loaded('curl') ? 'Loaded' : 'Missing', 'ok' => extension_loaded('curl')];
    $checks['Composer vendor/'] = ['value' => is_dir(__DIR__ . '/vendor') ? 'Found' : 'Not found — run composer install', 'ok' => is_dir(__DIR__ . '/vendor')];

    $dbOk = false;
    try {
        if (file_exists(__DIR__ . '/includes/db.php')) {
            @include __DIR__ . '/includes/db.php';
            if (isset($db)) {
                $db->query('SELECT 1');
                $dbOk = true;
            }
        }
    } catch (Exception $e) {}
    $checks['Database Connection'] = ['value' => $dbOk ? 'Connected' : 'Failed — check db.php config', 'ok' => $dbOk];

    if ($dbOk) {
        try {
            $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $has = in_array('subscriptions', $tables);
            $checks['subscriptions table'] = ['value' => $has ? 'Exists' : 'Missing — run setup.sql', 'ok' => $has];
        } catch (Exception $e) {
            $checks['subscriptions table'] = ['value' => 'Check failed', 'ok' => false];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Argonar Construction - VPS Deployment Guide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', system-ui, sans-serif; }
        .guide-container { max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        .step-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1.5rem; overflow: hidden; }
        .step-header { background: #2C3E50; color: #fff; padding: 0.75rem 1.25rem; font-weight: 600; }
        .step-body { padding: 1.25rem; }
        .step-note { background: #fff3cd; border-left: 4px solid #ffc107; padding: 0.75rem 1rem; margin-bottom: 1rem; border-radius: 0 4px 4px 0; font-size: 0.9rem; }
        pre { background: #1a1a2e; color: #e0e0e0; padding: 1rem; border-radius: 8px; overflow-x: auto; font-size: 0.85rem; line-height: 1.6; }
        pre .comment { color: #6a9955; }
        .check-table td { padding: 0.4rem 0.75rem; font-size: 0.9rem; }
        .badge-ok { background: #27AE60; }
        .badge-fail { background: #E74C3C; }
        .warning-banner { background: #E74C3C; color: #fff; padding: 1rem; border-radius: 8px; text-align: center; margin-bottom: 2rem; font-weight: 600; }
    </style>
</head>
<body>
<div class="guide-container">
    <div class="text-center mb-4">
        <h3 class="fw-bold" style="color:#2C3E50;">Argonar Construction</h3>
        <p class="text-muted">VPS Deployment Guide</p>
    </div>

    <div class="warning-banner">
        DELETE THIS FILE (setup-guide.php) AFTER DEPLOYMENT!
    </div>

    <?php if (!empty($checks)): ?>
    <div class="step-card">
        <div class="step-header">Server Environment Checks</div>
        <div class="step-body">
            <table class="table check-table mb-0">
                <?php foreach ($checks as $name => $check): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($name) ?></td>
                    <td><?= htmlspecialchars($check['value']) ?></td>
                    <td class="text-end">
                        <span class="badge <?= $check['ok'] ? 'badge-ok' : 'badge-fail' ?>">
                            <?= $check['ok'] ? 'OK' : 'FIX' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php foreach ($steps as $step): ?>
    <div class="step-card">
        <div class="step-header"><?= htmlspecialchars($step['title']) ?></div>
        <div class="step-body">
            <?php if (!empty($step['note'])): ?>
            <div class="step-note"><?= htmlspecialchars($step['note']) ?></div>
            <?php endif; ?>
            <pre><?php
            foreach ($step['commands'] as $cmd) {
                if ($cmd === '') {
                    echo "\n";
                } elseif (str_starts_with($cmd, '#')) {
                    echo '<span class="comment">' . htmlspecialchars($cmd) . "</span>\n";
                } else {
                    echo htmlspecialchars($cmd) . "\n";
                }
            }
            ?></pre>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="text-center text-muted small mb-4">
        <p>Domain: <strong>argonar.co</strong> | Server: <strong>139.177.187.26</strong><br>
        Replace <strong>YOUR_STRONG_PASSWORD</strong> with a secure database password</p>
    </div>
</div>
</body>
</html>
