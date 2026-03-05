<?php
require __DIR__ . '/includes/db.php';

header('Content-Type: text/plain');
echo "=== Argonar Construction - Tool Health Check ===\n\n";

$ok = 0;
$fail = 0;

function check($name, $test) {
    global $ok, $fail;
    if ($test) { echo "[OK]   {$name}\n"; $ok++; }
    else       { echo "[FAIL] {$name}\n"; $fail++; }
}

// 1. Database connection
check('Database connection', $db instanceof PDO);

// 2. Core tables exist
$tables = ['users', 'subscriptions', 'boqs', 'boq_items', 'rebar_lists', 'rebar_items',
           'structural_estimates', 'structural_estimate_items',
           'architectural_estimates', 'architectural_estimate_items',
           'documents', 'document_items'];

foreach ($tables as $t) {
    try {
        $db->query("SELECT 1 FROM {$t} LIMIT 1");
        check("Table: {$t}", true);
    } catch (Exception $e) {
        check("Table: {$t}", false);
    }
}

// 3. auto_renew column exists
try {
    $db->query("SELECT auto_renew FROM subscriptions LIMIT 1");
    check('Column: subscriptions.auto_renew', true);
} catch (Exception $e) {
    check('Column: subscriptions.auto_renew', false);
}

// 4. Key files exist
$files = [
    'includes/db.php', 'includes/auth.php', 'includes/subscription.php',
    'includes/header.php', 'includes/footer.php',
    'index.php', 'login.php', 'logout.php',
    'boq/index.php', 'boq/create.php', 'boq/edit.php', 'boq/view.php', 'boq/delete.php', 'boq/export.php',
    'rebar/index.php', 'rebar/create.php', 'rebar/edit.php', 'rebar/view.php', 'rebar/delete.php', 'rebar/export.php',
    'structural/index.php', 'structural/create.php', 'structural/edit.php', 'structural/view.php', 'structural/delete.php', 'structural/export.php',
    'architectural/index.php', 'architectural/create.php', 'architectural/edit.php', 'architectural/view.php', 'architectural/delete.php', 'architectural/export.php',
    'documents/index.php', 'documents/create.php', 'documents/edit.php', 'documents/view.php', 'documents/delete.php', 'documents/export.php',
    'templates/index.php', 'templates/download.php',
    'payment/pricing.php', 'payment/checkout.php', 'payment/success.php', 'payment/webhook.php', 'payment/history.php', 'payment/toggle-renew.php',
    'cron/renew.php',
    'js/app.js', 'js/boq.js', 'js/rebar.js', 'js/structural.js', 'js/architectural.js', 'js/documents.js',
    'css/app.css',
    'robots.txt', 'sitemap.xml',
];

foreach ($files as $f) {
    check("File: {$f}", file_exists(__DIR__ . '/' . $f));
}

// 5. Template Excel files
$templates = ['boq_template.xlsx', 'cost_estimate_template.xlsx', 'project_schedule_template.xlsx', 'daily_report_template.xlsx', 'material_requisition_template.xlsx'];
foreach ($templates as $t) {
    check("Template: {$t}", file_exists(__DIR__ . '/templates/files/' . $t));
}

// 6. FB images
$images = ['profile_512.png', 'cover_1640x856.png', 'post_tools_1080.png'];
foreach ($images as $img) {
    check("Image: fb/{$img}", file_exists(__DIR__ . '/images/fb/' . $img));
}

// 7. Vendor/PayRex
check('Vendor: PayRex SDK', file_exists(__DIR__ . '/vendor/payrex/payrex-php/src/PayrexClient.php'));
check('Vendor: PhpSpreadsheet', file_exists(__DIR__ . '/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php'));

// 8. Config checks
check('Config: APP_NAME defined', defined('APP_NAME'));
check('Config: GA_MEASUREMENT_ID set', defined('GA_MEASUREMENT_ID') && GA_MEASUREMENT_ID !== '');
check('Config: PAYREX_SECRET_KEY set', defined('PAYREX_SECRET_KEY') && PAYREX_SECRET_KEY !== '');
check('Config: PLANS defined', defined('PLANS') && count(PLANS) === 2);

// 9. Test page loads (HTTP)
$baseUrl = 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
$pages = ['index.php', 'login.php', 'payment/pricing.php', 'boq/index.php', 'structural/index.php', 'architectural/index.php', 'documents/index.php', 'templates/index.php'];

foreach ($pages as $p) {
    $url = $baseUrl . '/' . $p;
    $headers = @get_headers($url);
    $status = $headers ? (int)substr($headers[0], 9, 3) : 0;
    // 200 or 302 (redirect to login) are both OK
    check("HTTP {$status}: {$p}", in_array($status, [200, 302]));
}

echo "\n=== Results: {$ok} passed, {$fail} failed ===\n";
echo $fail === 0 ? "ALL CHECKS PASSED!\n" : "SOME CHECKS FAILED - review above.\n";
echo "\nDELETE THIS FILE AFTER USE.\n";
