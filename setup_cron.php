<?php
// One-time script to install the auto-renewal cron job
// DELETE AFTER USE

$scriptPath = __DIR__ . '/cron/renew.php';
$logPath = '/var/log/argonar-renew.log';
$cronLine = "0 * * * * php {$scriptPath} >> {$logPath} 2>&1";

// Check if cron already exists
$existing = shell_exec('crontab -l 2>/dev/null') ?? '';

if (str_contains($existing, 'cron/renew.php')) {
    echo "Cron job already installed.\n\n";
    echo "Current crontab:\n" . $existing;
    exit;
}

// Add the cron line
$newCrontab = rtrim($existing) . "\n" . $cronLine . "\n";
$tmpFile = tempnam(sys_get_temp_dir(), 'cron');
file_put_contents($tmpFile, $newCrontab);
$result = shell_exec("crontab {$tmpFile} 2>&1");
unlink($tmpFile);

if ($result === null || $result === '') {
    echo "OK - Cron job installed.\n";
    echo "Schedule: Every hour at :00\n";
    echo "Command: {$cronLine}\n\n";
    echo "Current crontab:\n" . shell_exec('crontab -l 2>/dev/null');
} else {
    echo "Error installing cron: {$result}\n";
}
